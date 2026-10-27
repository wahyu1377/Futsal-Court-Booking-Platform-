<?php
/**
 * API profil pengguna.
 *
 * Dipakai oleh halaman "Akun Saya" (frontend/pages/user/akun.php),
 * "Edit Profil" (frontend/pages/user/edit-profil.php), dan
 * "Setelan" (frontend/pages/user/setelan.php).
 *
 *   GET                      -> data profil pengguna yang sedang login
 *   POST aksi=update         -> ubah nama lengkap / email / nomor WhatsApp
 *   POST aksi=ganti          -> ganti foto profil (field foto)
 *   POST aksi=hapus          -> hapus foto profil
 *   POST aksi=ganti_password -> ubah kata sandi pengguna
 *
 * Data boleh dikirim sebagai JSON (application/json) maupun FormData.
 */
require_once __DIR__ . '/../../core/auth.php';

if (!isLoggedIn()) {
    respond(false, 'Silakan masuk terlebih dahulu.', ['need_login' => true], 401);
}
if (($_SESSION['role'] ?? '') !== 'user') {
    respond(false, 'Akses ditolak. API ini khusus akun pengguna.', [], 403);
}

const AVATAR_DIR  = __DIR__ . '/../../../frontend/assets/img/avatars/';
const MAX_BYTES   = 2 * 1024 * 1024;    // batas file yang diterima server
const MAX_PIXELS  = 25000000;           // cegah gambar raksasa yang menghabiskan memori
const AVATAR_SIZE = 400;                // ukuran akhir (px)

// Hapus file lama, hanya kalau namanya sesuai pola buatan sistem ini
function hapusFileAvatar(?string $nama): void
{
    if ($nama && preg_match('/^u\d+_[a-f0-9]{16}\.jpg$/', $nama) && is_file(AVATAR_DIR . $nama)) {
        @unlink(AVATAR_DIR . $nama);
    }
}

$pdo    = db();
$userId = (int) $_SESSION['user_id'];

// Ambil data pengguna. Kalau barisnya hilang (akun dihapus), sesi tidak berlaku lagi.
$st = $pdo->prepare('SELECT id, full_name, email, phone, password_hash, avatar FROM users WHERE id = ?');
$st->execute([$userId]);
$profil = $st->fetch();

if (!$profil) {
    $_SESSION = [];
    session_destroy();
    respond(false, 'Akun tidak ditemukan. Silakan masuk kembali.', ['need_login' => true], 401);
}

/* ---------------------------------------------------------------
   GET -> kirim data profil
   --------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    respond(true, 'OK', [
        'profil' => [
            'nama'    => (string) $profil['full_name'],
            'email'   => (string) ($profil['email'] ?? ''),
            'telepon' => (string) ($profil['phone'] ?? ''),
            'avatar'  => $profil['avatar'] ?: null,
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
    $nama    = trim((string) ($data['nama'] ?? ''));
    $email   = trim((string) ($data['email'] ?? ''));
    $telepon = trim((string) ($data['telepon'] ?? ''));

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

    $cek = $pdo->prepare('SELECT id FROM users WHERE LOWER(email) = LOWER(?) AND id <> ?');
    $cek->execute([$email, $userId]);
    if ($cek->fetch()) {
        respond(false, 'Email tersebut sudah dipakai akun pengguna lain.', [], 409);
    }

    // Email yang sama di tabel owners akan membuat login ambigu
    // (cari akun selalu(users dulu, owners kemudian))
    $cek = $pdo->prepare('SELECT id FROM owners WHERE LOWER(email) = LOWER(?)');
    $cek->execute([$email]);
    if ($cek->fetch()) {
        respond(false, 'Email tersebut sudah dipakai akun owner.', [], 409);
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

    $pdo->prepare('UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?')
        ->execute([$nama, $email, $teleponAngka !== '' ? $teleponAngka : null, $userId]);

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
   aksi = ganti / hapus  (foto profil pengguna)
   --------------------------------------------------------------- */
if ($aksi === 'ganti' || $aksi === 'hapus') {
    $namaLama = $profil['avatar'] ?: null;

    // ===== HAPUS FOTO =====
    if ($aksi === 'hapus') {
        $pdo->prepare('UPDATE users SET avatar = NULL WHERE id = ?')->execute([$userId]);
        hapusFileAvatar($namaLama);
        respond(true, 'Foto profil dihapus.', ['avatar' => null]);
    }

    // ===== GANTI FOTO =====
    $f = $_FILES['foto'] ?? null;
    if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) {
        respond(false, 'Pilih foto terlebih dahulu.', [], 422);
    }
    if (in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) || $f['size'] > MAX_BYTES) {
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
    if ($w * $h > MAX_PIXELS) {
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

    if (!is_dir(AVATAR_DIR) && !mkdir(AVATAR_DIR, 0775, true)) {
        error_log('Folder avatar tidak bisa dibuat: ' . AVATAR_DIR);
        respond(false, 'Server belum siap menyimpan foto.', [], 500);
    }

    // Nama file dibuat server (acak), tidak memakai nama dari pengguna
    $nama = sprintf('u%d_%s.jpg', $userId, bin2hex(random_bytes(8)));
    if (!imagejpeg($dst, AVATAR_DIR . $nama, 85)) {
        error_log('Gagal menulis avatar: ' . AVATAR_DIR . $nama);
        respond(false, 'Foto gagal disimpan.', [], 500);
    }

    try {
        $pdo->prepare('UPDATE users SET avatar = ? WHERE id = ?')->execute([$nama, $userId]);
    } catch (PDOException $e) {
        @unlink(AVATAR_DIR . $nama);          // jangan tinggalkan file yatim
        throw $e;
    }

    hapusFileAvatar($namaLama);              // foto lama dibuang setelah yang baru aman
    respond(true, 'Foto profil berhasil diperbarui.', ['avatar' => $nama]);
}

/* ---------------------------------------------------------------
   aksi = ganti_password
   --------------------------------------------------------------- */
if ($aksi === 'ganti_password') {
    $lama       = (string) ($data['password_lama'] ?? '');
    $baru       = (string) ($data['password_baru'] ?? '');
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

    $hash = (string) $profil['password_hash'];
    if ($hash === '' || !password_verify($lama, $hash)) {
        respond(false, 'Kata sandi lama salah.', [], 422);
    }

    // Hash baru + reset penguncian akun
    $pdo->prepare('UPDATE users SET password_hash = ?, failed_attempts = 0, locked_until = NULL WHERE id = ?')
        ->execute([password_hash($baru, PASSWORD_DEFAULT), $userId]);

    respond(true, 'Kata sandi berhasil diubah.', []);
}

respond(false, 'Aksi tidak dikenal.', [], 422);
