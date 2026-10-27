<?php
/**
 * API profil owner.
 *
 * Dipakai oleh halaman "Akun Owner" (frontend/pages/owner/akun.php) dan
 * "Edit Profil Owner" (frontend/pages/owner/edit-profil.php).
 *
 *   GET                      -> data profil owner yang sedang login
 *   POST aksi=update         -> ubah nama lengkap / email / nomor WhatsApp
 *   POST aksi=ganti          -> ganti foto profil (field foto)
 *   POST aksi=hapus          -> hapus foto profil
 *   POST aksi=ganti_password -> ubah kata sandi owner
 *
 * Data boleh dikirim sebagai JSON (application/json) maupun FormData.
 */
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/skema.php';

if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'owner') {
    respond(false, 'Akses ditolak. API ini khusus akun owner.', [], 403);
}

$pdo     = db();
$idOwner = (int) $_SESSION['user_id'];

/* ---------------------------------------------------------------
   Pemeriksaan skema (lihat core/skema.php) supaya halaman tetap aman
   dipanggil sebelum migrasi 004_owner_akun.sql dijalankan.
   --------------------------------------------------------------- */

// Folder & aturan upload foto profil owner
const AVATAR_DIR_OWNER = __DIR__ . '/../../../frontend/assets/img/avatars/';
const AVATAR_MAX_BYTES = 2 * 1024 * 1024;    // batas ukuran file yang diterima server
const AVATAR_MAX_PIXEL = 25000000;           // cegah gambar raksasa yang menghabiskan memori
const AVATAR_SIZE      = 400;                // ukuran akhir (px)

// Hapus file lama, hanya kalau namanya sesuai pola buatan sistem ini
function hapusFileAvatarOwner(?string $nama): void
{
    if ($nama && preg_match('/^o\d+_[a-f0-9]{16}\.jpg$/', $nama) && is_file(AVATAR_DIR_OWNER . $nama)) {
        @unlink(AVATAR_DIR_OWNER . $nama);
    }
}

// Ambil data owner. Kalau barisnya hilang (akun dihapus), sesi tidak berlaku lagi.
$adaAvatar = kolomAda($pdo, 'owners', 'avatar');

$sqlProfil = 'SELECT id, full_name, email, phone' . ($adaAvatar ? ', avatar' : '') . ' FROM owners WHERE id = ?';
$st = $pdo->prepare($sqlProfil);
$st->execute([$idOwner]);
$profil = $st->fetch();

if (!$profil) {
    $_SESSION = [];
    session_destroy();
    respond(false, 'Akun owner tidak ditemukan. Silakan masuk kembali.', ['need_login' => true], 401);
}

/* ---------------------------------------------------------------
   Ringkasan profil (dipakai oleh halaman akun)
   --------------------------------------------------------------- */
function ringkasanOwner(PDO $pdo, int $idOwner): array
{
    $st = $pdo->prepare('SELECT COUNT(*) FROM lapangan WHERE owner_id = ?');
    $st->execute([$idOwner]);
    $jumlahLapangan = (int) $st->fetchColumn();

    // Pesanan yang masih berjalan: menunggu pembayaran atau sudah dibayar
    $st = $pdo->prepare(
        "SELECT COUNT(*) FROM booking b
         JOIN lapangan l ON l.id = b.lapangan_id AND l.owner_id = ?
         WHERE b.status IN ('menunggu', 'dibayar')"
    );
    $st->execute([$idOwner]);
    $pesananAktif = (int) $st->fetchColumn();

    // Total pesanan sepanjang waktu (untuk keterangan di halaman akun)
    $st = $pdo->prepare(
        'SELECT COUNT(*) FROM booking b
         JOIN lapangan l ON l.id = b.lapangan_id AND l.owner_id = ?'
    );
    $st->execute([$idOwner]);
    $pesananTotal = (int) $st->fetchColumn();

    return [
        'lapangan'    => $jumlahLapangan,
        'pesanan_aktif' => $pesananAktif,
        'pesanan_total' => $pesananTotal,
    ];
}

/* ---------------------------------------------------------------
   GET -> kirim data profil
   --------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    respond(true, 'OK', [
        'profil' => [
            'nama'     => (string) $profil['full_name'],
            'email'    => (string) ($profil['email'] ?? ''),
            'telepon'  => (string) ($profil['phone'] ?? ''),
            'avatar'   => $adaAvatar ? ($profil['avatar'] ?: null) : null,
            'ringkasan' => ringkasanOwner($pdo, $idOwner),
        ],
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Metode tidak diizinkan.', [], 405);
}

// Ambil payload: FormData (POST) lebih dulu, kalau kosong baru coba JSON.
$data = $_POST;
if (!$data) {
    $json = json_decode(file_get_contents('php://input'), true);
    if (is_array($json)) {
        $data = $json;
    }
}

$aksi = (string) ($data['aksi'] ?? '');

/* ---------------------------------------------------------------
   aksi = update  (nama / email / telepon)
   --------------------------------------------------------------- */
if ($aksi === 'update') {
    $nama     = trim((string) ($data['nama'] ?? ''));
    $email    = trim((string) ($data['email'] ?? ''));
    $telepon  = trim((string) ($data['telepon'] ?? ''));

    // Nama
    if (mb_strlen($nama) < 3 || mb_strlen($nama) > 80) {
        respond(false, 'Nama lengkap harus antara 3 sampai 80 karakter.', [], 422);
    }

    // Email: wajib, format benar, dan belum dipakai akun lain
    if ($email === '') {
        respond(false, 'Email tidak boleh kosong.', [], 422);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(false, 'Format email tidak valid.', [], 422);
    }
    $email = strtolower($email);

    $cek = $pdo->prepare('SELECT id FROM owners WHERE LOWER(email) = LOWER(?) AND id <> ?');
    $cek->execute([$email, $idOwner]);
    if ($cek->fetch()) {
        respond(false, 'Email tersebut sudah dipakai akun owner lain.', [], 409);
    }

    // Email yang sama di tabel users akan membuat login ambigu (cari akun selalu(users dulu, owners kemudian)
    $cek = $pdo->prepare('SELECT id FROM users WHERE LOWER(email) = LOWER(?)');
    $cek->execute([$email]);
    if ($cek->fetch()) {
        respond(false, 'Email tersebut sudah dipakai akun pengguna.', [], 409);
    }

    // Nomor WhatsApp: hanya angka, format Indonesia
    $teleponAngka = preg_replace('/\s+|\-/', '', $telepon) ?? '';
    if ($teleponAngka !== '') {
        // +62xxxxxxxx / 62xxxxxxxx -> 0xxxxxxxx
        if (preg_match('/^\+?62/', $teleponAngka)) {
            $teleponAngka = '0' . substr($teleponAngka, 3);
        }
        if (!preg_match('/^0[0-9]{8,12}$/', $teleponAngka)) {
            respond(false, 'Nomor WhatsApp tidak valid. Contoh: 081234567890.', [], 422);
        }
    }

    $pdo->prepare('UPDATE owners SET full_name = ?, email = ?, phone = ? WHERE id = ?')
        ->execute([$nama, $email, $teleponAngka !== '' ? $teleponAngka : null, $idOwner]);

    // Nama di header ikut berubah tanpa perlu login ulang
    $_SESSION['user_name'] = $nama;

    respond(true, 'Profil berhasil diperbarui.', [
        'profil' => [
            'nama'    => $nama,
            'email'   => $email,
            'telepon' => $teleponAngka,
        ],
    ]);
}

/* ---------------------------------------------------------------
   aksi = ganti / hapus  (foto profil owner)
   --------------------------------------------------------------- */
if ($aksi === 'ganti' || $aksi === 'hapus') {
    // Kolom owners.avatar belum ada -> migrasi 004_owner_akun.sql belum dijalankan
    if (!$adaAvatar) {
        respond(false, 'Fitur foto profil owner belum siap. Jalankan migrasi 004_owner_akun.sql di database.', [], 501);
    }

    $namaLama = $profil['avatar'] ?: null;

    // ===== HAPUS FOTO =====
    if ($aksi === 'hapus') {
        $pdo->prepare('UPDATE owners SET avatar = NULL WHERE id = ?')->execute([$idOwner]);
        hapusFileAvatarOwner($namaLama);
        respond(true, 'Foto profil dihapus.', ['avatar' => null]);
    }

    // ===== GANTI FOTO =====
    $f = $_FILES['foto'] ?? null;
    if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) {
        respond(false, 'Pilih foto terlebih dahulu.', [], 422);
    }
    if (in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) || $f['size'] > AVATAR_MAX_BYTES) {
        respond(false, 'Ukuran foto maksimal 2 MB.', [], 413);
    }
    if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
        respond(false, 'Unggahan gagal. Coba lagi.', [], 400);
    }

    // Jenis file ditentukan dari ISI file, bukan dari nama atau ekstensi kiriman browser
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $info = @getimagesize($f['tmp_name']);
    if (!$info || !in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        respond(false, 'Format foto harus JPG, PNG, atau WebP.', [], 415);
    }
    [$w, $h] = $info;
    if ($w < 100 || $h < 100) {
        respond(false, 'Foto terlalu kecil (minimal 100×100 px).', [], 422);
    }
    if ($w * $h > AVATAR_MAX_PIXEL) {
        respond(false, 'Resolusi foto terlalu besar.', [], 413);
    }

    $src = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($f['tmp_name']),
        'image/png'  => @imagecreatefrompng($f['tmp_name']),
        'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($f['tmp_name']) : false,
    };
    if (!$src) {
        respond(false, 'Foto tidak dapat diproses. Coba foto lain.', [], 422);
    }

    // Potong persegi dari tengah, kecilkan ke 400x400
    $sisi = min($w, $h);
    $dst  = imagecreatetruecolor(AVATAR_SIZE, AVATAR_SIZE);
    imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));   // PNG transparan jadi latar putih
    imagecopyresampled($dst, $src, 0, 0, intdiv($w - $sisi, 2), intdiv($h - $sisi, 2), AVATAR_SIZE, AVATAR_SIZE, $sisi, $sisi);

    if (!is_dir(AVATAR_DIR_OWNER) && !mkdir(AVATAR_DIR_OWNER, 0775, true)) {
        error_log('Folder avatar owner tidak bisa dibuat: ' . AVATAR_DIR_OWNER);
        respond(false, 'Server belum siap menyimpan foto.', [], 500);
    }

    // Nama file dibuat server (acak), tidak memakai nama dari pengguna
    $nama = sprintf('o%d_%s.jpg', $idOwner, bin2hex(random_bytes(8)));
    if (!imagejpeg($dst, AVATAR_DIR_OWNER . $nama, 85)) {
        error_log('Gagal menulis avatar owner: ' . AVATAR_DIR_OWNER . $nama);
        respond(false, 'Foto gagal disimpan.', [], 500);
    }

    try {
        $pdo->prepare('UPDATE owners SET avatar = ? WHERE id = ?')->execute([$nama, $idOwner]);
    } catch (PDOException $e) {
        @unlink(AVATAR_DIR_OWNER . $nama);     // jangan tinggalkan file yatim
        throw $e;
    }

    hapusFileAvatarOwner($namaLama);         // foto lama dibuang setelah yang baru aman
    respond(true, 'Foto profil berhasil diperbarui.', ['avatar' => $nama]);
}

/* ---------------------------------------------------------------
   aksi = ganti_password
   --------------------------------------------------------------- */
if ($aksi === 'ganti_password') {
    $lama      = (string) ($data['password_lama'] ?? '');
    $baru      = (string) ($data['password_baru'] ?? '');
    $konfirmasi = (string) ($data['password_konfirmasi'] ?? '');

    if ($lama === '' || $baru === '') {
        respond(false, 'Kata sandi lama dan baru wajib diisi.', [], 422);
    }
    if (mb_strlen($baru) < 8) {
        respond(false, 'Kata sandi baru minimal 8 karakter.', [], 422);
    }
    if ($baru !== $konfirmasi) {
        respond(false, 'Konfirmasi kata sandi tidak cocok.', [], 422);
    }

    $st = $pdo->prepare('SELECT password_hash FROM owners WHERE id = ?');
    $st->execute([$idOwner]);
    $hash = (string) $st->fetchColumn();

    if ($hash === '' || !password_verify($lama, $hash)) {
        respond(false, 'Kata sandi lama salah.', [], 422);
    }

    // Hash baru + reset penguncian akun
    $pdo->prepare('UPDATE owners SET password_hash = ?, failed_attempts = 0, locked_until = NULL WHERE id = ?')
        ->execute([password_hash($baru, PASSWORD_DEFAULT), $idOwner]);

    respond(true, 'Kata sandi berhasil diubah.', []);
}

respond(false, 'Aksi tidak dikenal.', [], 422);
