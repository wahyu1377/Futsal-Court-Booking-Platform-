<?php
/**
 * Logika reset kata sandi.
 *
 * Alur:
 *   1. User minta tautan reset (api/auth/lupa-password.php)
 *      -> buat token acak, simpan HASH-nya ke tabel password_reset
 *      -> kirim tautan ke email akun
 *   2. User membuka tautan (api/auth/reset-password.php)
 *      -> cek token: masih berlaku? belum dipakai?
 *   3. User mengesahkan kata sandi baru
 *      -> update password_hash, tandai token dipakai, buka kunci akun
 *
 * Keamanan:
 *   - Token asli (64 hex) hanya ada di email. Yang disimpan di database
 *     adalah SHA-256-nya, jadi bocornya isi tabel tidak cukup untuk login.
 *   - Token sekali pakai (setelah dipakai, token lama dimatikan).
 *   - Ada cooldown + rate limit supaya tidak bisa dipakai membanjiri email.
 *   - Respons API tidak pernah membocorkan apakah email terdaftar.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helper.php';
require_once __DIR__ . '/skema.php';
require_once __DIR__ . '/url.php';
require_once __DIR__ . '/mailer.php';

const RESET_MASA_TAHAN_MENIT = 60;   // masa berlaku tautan reset
const RESET_COOLDOWN_DETIK    = 60;   // jeda minimum antar permintaan
const RESET_MAKS_PER_JAM      = 5;    // maksimal permintaan per akun per jam
const RESET_MAKS_IP_PER_JAM   = 10;   // maksimal permintaan per IP per jam

/**
 * True kalau tabel password_reset sudah tersedia di database.
 * Kalau belum (migrasi belum dijalankan), fitur ini dinonaktifkan dengan
 * pesan yang jelas, bukan membuat halaman error.
 */
function resetSiap(): bool
{
    static $siap = null;
    if ($siap === null) {
        $siap = tabelAda(db(), 'password_reset');
    }
    return $siap;
}

/**
 * Hash dari token. Dipakai saat menyimpan dan saat mencari token.
 */
function resetHash(string $token): string
{
    return hash('sha256', $token);
}

/**
 * Buat token acak yang aman (64 karakter hex).
 */
function resetTokenBaru(): string
{
    return bin2hex(random_bytes(32));
}

/**
 * Alamat email akun, atau string kosong kalau akun tidak punya email.
 */
function resetAlamatAkun(PDO $pdo, string $role, int $accountId): string
{
    $tabel = tableFor($role);
    $st = $pdo->prepare("SELECT email FROM $tabel WHERE id = ?");
    $st->execute([$accountId]);
    return strtolower(trim((string) $st->fetchColumn()));
}

/**
 * Sisa cooldown (detik) untuk sebuah akun, atau 0 kalau boleh request lagi.
 */
function resetCooldownSisa(PDO $pdo, string $role, int $accountId): int
{
    $st = $pdo->prepare(
        "SELECT created_at FROM password_reset
          WHERE role = ? AND account_id = ?
          ORDER BY created_at DESC LIMIT 1"
    );
    $st->execute([$role, $accountId]);
    $terakhir = $st->fetchColumn();
    if (!$terakhir) {
        return 0;
    }
    $selisih = time() - strtotime((string) $terakhir);
    return max(0, RESET_COOLDOWN_DETIK - (int) $selisih);
}

/**
 * Jumlah permintaan reset dalam 1 jam terakhir untuk akun / IP tertentu.
 */
function resetJumlahPerJam(PDO $pdo, string $role, int $accountId, ?string $ip): int
{
    $batas = date('Y-m-d H:i:s', time() - 3600);

    $st = $pdo->prepare(
        "SELECT COUNT(*) FROM password_reset
          WHERE role = ? AND account_id = ? AND created_at >= ?"
    );
    $st->execute([$role, $accountId, $batas]);
    $jumlahAkun = (int) $st->fetchColumn();

    if ($ip === null || $ip === '') {
        return $jumlahAkun;
    }

    $st = $pdo->prepare(
        "SELECT COUNT(*) FROM password_reset
          WHERE ip_address = ? AND created_at >= ?"
    );
    $st->execute([$ip, $batas]);
    return max($jumlahAkun, (int) $st->fetchColumn());
}

/**
 * Simpan token baru ke database.
 * Mengembalikan ['ok' => bool, 'token' => ?string].
 */
function resetSimpanToken(PDO $pdo, string $role, int $accountId, string $email, ?string $ip, string $token): bool
{
    $st = $pdo->prepare(
        "INSERT INTO password_reset
            (token_hash, role, account_id, email_kirim, ip_address, expires_at, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $st->execute([
        resetHash($token),
        $role,
        $accountId,
        $email,
        $ip,
        date('Y-m-d H:i:s', time() + RESET_MASA_TAHAN_MENIT * 60),
        date('Y-m-d H:i:s'),
    ]);
    return true;
}

/**
 * Cari token yang masih berlaku.
 * Mengembalikan baris database, atau null.
 */
function resetCariToken(PDO $pdo, string $token): ?array
{
    $st = $pdo->prepare(
        "SELECT id, role, account_id, expires_at, used_at
           FROM password_reset
          WHERE token_hash = ?"
    );
    $st->execute([resetHash($token)]);
    $baris = $st->fetch();
    if (!$baris) {
        return null;
    }
    if (!empty($baris['used_at'])) {
        return null;                     // token sudah pernah dipakai
    }
    if (strtotime((string) $baris['expires_at']) <= time()) {
        return null;                     // token kedaluwarsa
    }
    return $baris;
}

/**
 * Terapkan kata sandi baru.
 *
 * - Update password_hash di tabel sesuai role
 * - Buka kunci akun (failed_attempts = 0, locked_until = NULL)
 * - Tandai token ini dipakai, dan matikan token lain milik akun yang sama
 *
 * Mengembalikan true kalau berhasil.
 */
function resetTerapkanPassword(PDO $pdo, string $token, string $role, int $accountId, string $passwordBaru): bool
{
    $tabel = tableFor($role);

    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare(
            "UPDATE $tabel
                SET password_hash = ?, failed_attempts = 0, locked_until = NULL
              WHERE id = ?"
        );
        $st->execute([password_hash($passwordBaru, PASSWORD_DEFAULT), $accountId]);

        // Tandai token ini sudah dipakai
        $st = $pdo->prepare('UPDATE password_reset SET used_at = ? WHERE token_hash = ?');
        $st->execute([date('Y-m-d H:i:s'), resetHash($token)]);

        // Matikan token lain milik akun yang sama (cukup satu token aktif)
        $st = $pdo->prepare(
            'DELETE FROM password_reset
              WHERE role = ? AND account_id = ? AND used_at IS NULL'
        );
        $st->execute([$role, $accountId]);

        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Reset password gagal: ' . $e->getMessage());
        return false;
    }
}

/**
 * Buang token yang sudah kedaluwarsa / sudah lama tidak dipakai.
 * Dipanggil sesekali supaya tabel tidak menumpuk.
 */
function resetBersihkan(PDO $pdo): void
{
    $batas = date('Y-m-d H:i:s', time() - 86400);
    $st = $pdo->prepare('DELETE FROM password_reset WHERE created_at < ?');
    $st->execute([$batas]);
}

/**
 * Pembersihan Token yang jalan sesekali (bukan setiap request), supaya
 * tabel token tidak menumpuk tanpa menambah beban query di setiap permintaan.
 */
function resetBersihkanSesekali(PDO $pdo): void
{
    if (random_int(1, 20) !== 1) {
        return;
    }
    try {
        resetBersihkan($pdo);
    } catch (Throwable $e) {
        // Kegagalan bersih-bersih tidak boleh mengganggu alur reset.
        error_log('Bersihkan token reset gagal: ' . $e->getMessage());
    }
}

/**
 * Susun isi email reset kata sandi (HTML).
 */
function resetEmailHtml(string $nama, string $tautan): string
{
    $namaHtml = htmlspecialchars($nama, ENT_QUOTES, 'UTF-8');
    $tautanHtml = htmlspecialchars($tautan, ENT_QUOTES, 'UTF-8');

    return '<div style="font-family:Arial,Helvetica,sans-serif;max-width:520px;margin:0 auto;padding:24px;'
        . 'background:#f6f8ff;border-radius:16px;">'
        . '<h2 style="color:#006a4a;margin:0 0 12px;">Reset Kata Sandi</h2>'
        . '<p style="color:#0d1b2e;margin:0 0 16px;">Halo ' . $namaHtml . ',</p>'
        . '<p style="color:#0d1b2e;margin:0 0 16px;">Kami menerima permintaan reset kata sandi untuk akun Arena Sport Anda. '
        . 'Klik tombol di bawah untuk membuat kata sandi baru.</p>'
        . '<p style="margin:0 0 16px;"><a href="' . $tautanHtml . '" '
        . 'style="display:inline-block;background:#006a4a;color:#ffffff;font-weight:bold;'
        . 'padding:12px 22px;border-radius:12px;text-decoration:none;">Buat Kata Sandi Baru</a></p>'
        . '<p style="color:#5f6b7a;font-size:13px;margin:0 0 8px;">Kalau tombol tidak bisa diklik, salin tautan ini ke browser:</p>'
        . '<p style="margin:0 0 16px;word-break:break-all;"><a href="' . $tautanHtml . '" style="color:#006a4a;">'
        . $tautanHtml . '</a></p>'
        . '<p style="color:#5f6b7a;font-size:13px;margin:0 0 8px;">Tautan ini berlaku selama '
        . RESET_MASA_TAHAN_MENIT . ' menit dan hanya bisa dipakai satu kali.</p>'
        . '<p style="color:#5f6b7a;font-size:13px;margin:0;">Kalau Anda tidak meminta reset kata sandi, '
        . 'abaikan saja email ini. Kata sandi Anda tidak berubah.</p>'
        . '</div>';
}
