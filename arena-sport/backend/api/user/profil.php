<?php
require __DIR__ . '/../../core/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(false, 'Metode tidak diizinkan.', [], 405);
if (!isLoggedIn())                         respond(false, 'Silakan masuk terlebih dahulu.', ['need_login' => true], 401);
if ($_SESSION['role'] !== 'user')          respond(false, 'Fitur ini khusus akun pengguna.', [], 403);

const AVATAR_DIR  = __DIR__ . '/../../../frontend/assets/img/avatars/';
const MAX_BYTES   = 2 * 1024 * 1024;    // batas file yang diterima server
const MAX_PIXELS  = 25000000;           // cegah gambar raksasa yang menghabiskan memori
const AVATAR_SIZE = 400;                // ukuran akhir (px)

// Hapus file lama, hanya kalau namanya sesuai pola buatan sistem ini
function hapusFileAvatar(?string $nama): void {
    if ($nama && preg_match('/^u\d+_[a-f0-9]{16}\.jpg$/', $nama) && is_file(AVATAR_DIR . $nama)) {
        @unlink(AVATAR_DIR . $nama);
    }
}

$pdo    = db();
$userId = (int)$_SESSION['user_id'];

$st = $pdo->prepare('SELECT avatar FROM users WHERE id = ?');
$st->execute([$userId]);
$lama = $st->fetchColumn();
if ($lama === false) respond(false, 'Akun tidak ditemukan.', [], 404);

$aksi = $_POST['aksi'] ?? '';

// ===== HAPUS FOTO =====
if ($aksi === 'hapus') {
    $pdo->prepare('UPDATE users SET avatar = NULL WHERE id = ?')->execute([$userId]);
    hapusFileAvatar($lama);
    respond(true, 'Foto profil dihapus.', ['avatar' => null]);
}
if ($aksi !== 'ganti') respond(false, 'Aksi tidak dikenal.', [], 422);

// ===== GANTI FOTO =====
$f = $_FILES['foto'] ?? null;
if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) respond(false, 'Pilih foto terlebih dahulu.', [], 422);
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
if ($w < 100 || $h < 100)       respond(false, 'Foto terlalu kecil (minimal 100×100 px).', [], 422);
if ($w * $h > MAX_PIXELS)       respond(false, 'Resolusi foto terlalu besar.', [], 413);

$src = match ($mime) {
    'image/jpeg' => @imagecreatefromjpeg($f['tmp_name']),
    'image/png'  => @imagecreatefrompng($f['tmp_name']),
    'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($f['tmp_name']) : false,
};
if (!$src) respond(false, 'Foto tidak dapat diproses. Coba foto lain.', [], 422);

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

hapusFileAvatar($lama);                    // foto lama dibuang setelah yang baru aman
respond(true, 'Foto profil berhasil diperbarui.', ['avatar' => $nama]);