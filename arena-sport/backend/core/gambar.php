<?php
/**
 * Penyimpanan & pengambilan gambar lapangan.
 *
 * Kenapa file di server saja tidak cukup:
 *   Folder unggahan owner (frontend/assets/img/lapangan/owner/) sengaja tidak
 *   di-commit ke git, sedangkan database dipakai bersama antar anggota tim.
 *   Akibatnya foto yang diunggah di satu komputer TIDAK muncul di server lain
 *   (deploy produksi pun tidak membawanya) -> foto jadi 404 di halaman.
 *
 * Solusi:
 *   File hasil unggahan disimpan DUA kali:
 *     1) di database (tabel `gambar_unggahan`, isi file disimpan sebagai base64)
 *        -> bisa dibaca dari mana saja: laptop anggota tim, server lokal,
 *           sampai server produksi.
 *     2) di folder aset (sebagai cadangan & supaya pratinjau di server yang
 *        sama tetap cepat).
 *
 * Foto bawaan (galeri) tetap dibaca langsung dari folder aset.
 *
 * Catatan: tabel `gambar_unggahan` dibuat lewat file SQL di
 * backend/sql/007_gambar_unggahan.sql. Kalau tabelnya belum ada, semua
 * fungsi di bawah ini tetap aman (hanya menyimpan ke disk).
 */
require_once __DIR__ . '/../config/database.php';

/** Nama tabel penyimpan gambar unggahan. */
const GAMBAR_TABEL = 'gambar_unggahan';

/** Folder aset gambar lapangan (berisi foto bawaan + sub folder owner/). */
const GAMBAR_FOLDER_ASET = __DIR__ . '/../../frontend/assets/img/lapangan/';

/** Batas ukuran file unggahan: 3 MB. */
const GAMBAR_MAKS_BYTES = 3 * 1024 * 1024;

/**
 * Nama file aman? Melarang keluar dari folder aset (path traversal) dan
 * membolehkan satu sub folder (mis. "owner/20261022-ab12cd.jpg").
 */
function gambarNamaAman(string $nama): bool
{
    $nama = trim($nama);
    if ($nama === '' || str_contains($nama, '..') || str_contains($nama, '\\')) {
        return false;
    }
    // Boleh paling banyak satu garis miring (sub folder)
    return substr_count($nama, '/') <= 1;
}

/**
 * Ekstensi file dari mime type gambar.
 */
function gambarEkstensi(string $mime): ?string
{
    $peta = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];
    return $peta[$mime] ?? null;
}

/**
 * Perkecil gambar supaya hemat tempat di database.
 * Memakai GD bila tersedia; kalau tidak, file asli dipakai apa adanya.
 * Mengembalikan array [isi binary, mime type].
 */
function gambarPerkecil(string $binari, string $mime): array
{
    if (!function_exists('imagecreatetruecolor')) {
        return [$binari, $mime];   // GD tidak ada -> pakai file asli
    }

    $sumber = @imagecreatefromstring($binari);
    if ($sumber === false) {
        return [$binari, $mime];
    }

    $lebar  = imagesx($sumber);
    $tinggi = imagesy($sumber);
    $maks   = 1280;   // sisi terpanjang

    if ($lebar <= $maks && $tinggi <= $maks) {
        imagedestroy($sumber);
        return [$binari, $mime];
    }

    $skala = min($maks / $lebar, $maks / $tinggi);
    $wBaru = max(1, (int) round($lebar * $skala));
    $hBaru = max(1, (int) round($tinggi * $skala));

    $tujuan = imagecreatetruecolor($wBaru, $hBaru);

    if ($mime === 'image/png') {
        // PNG boleh transparan, jadi alpha-nya ikut dipertahankan
        imagealphablending($tujuan, false);
        imagesavealpha($tujuan, true);
    } else {
        // JPG/WebP tidak bisa transparan, jadi latar diisi putih
        $putih = imagecolorallocate($tujuan, 255, 255, 255);
        imagefilledrectangle($tujuan, 0, 0, $wBaru, $hBaru, $putih);
    }

    imagecopyresampled($tujuan, $sumber, 0, 0, 0, 0, $wBaru, $hBaru, $lebar, $tinggi);
    imagedestroy($sumber);

    ob_start();
    if ($mime === 'image/png') {
        imagepng($tujuan, null, 6);
        $mimeBaru = 'image/png';
    } elseif ($mime === 'image/webp') {
        imagewebp($tujuan, null, 80);
        $mimeBaru = 'image/webp';
    } else {
        imagejpeg($tujuan, null, 82);
        $mimeBaru = 'image/jpeg';
    }
    $hasil = (string) ob_get_clean();
    imagedestroy($tujuan);

    return [$hasil, $mimeBaru];
}

/**
 * Simpan isi file ke tabel gambar_unggahan.
 * Balikan: true kalau tersimpan, false kalau tabelnya belum ada / gagal.
 */
function gambarSimpanDb(string $nama, string $mime, string $binari): bool
{
    if (!gambarNamaAman($nama)) {
        return false;
    }

    try {
        $pdo = db();
        $data = base64_encode($binari);

        if (db_config()['driver'] === 'mysql') {
            $sql = 'INSERT INTO gambar_unggahan (nama, mime, data, ukuran) VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE mime = VALUES(mime), data = VALUES(data), ukuran = VALUES(ukuran)';
        } else {
            $sql = 'INSERT INTO gambar_unggahan (nama, mime, data, ukuran) VALUES (?, ?, ?, ?)
                    ON CONFLICT (nama) DO UPDATE SET mime = EXCLUDED.mime, data = EXCLUDED.data, ukuran = EXCLUDED.ukuran';
        }

        $st = $pdo->prepare($sql);
        $st->execute([$nama, $mime, $data, strlen($binari)]);
        return true;
    } catch (PDOException $e) {
        // Contoh: tabel belum dibuat di database -> unggahan tetap jalan lewat disk
        error_log('Gambar tidak tersimpan di database: ' . $e->getMessage());
        return false;
    }
}

/**
 * Ambil gambar dari database.
 * Balikan: array ['mime' => ..., 'binari' => ...] atau null.
 */
function gambarAmbilDb(string $nama): ?array
{
    if (!gambarNamaAman($nama)) {
        return null;
    }

    try {
        $pdo = db();
        $st  = $pdo->prepare('SELECT mime, data FROM ' . GAMBAR_TABEL . ' WHERE nama = ?');
        $st->execute([$nama]);
        $row = $st->fetch();
    } catch (PDOException $e) {
        error_log('Gambar tidak bisa dibaca dari database: ' . $e->getMessage());
        return null;
    }

    if (!$row) {
        return null;
    }

    $binari = base64_decode((string) $row['data'], true);
    if ($binari === false || $binari === '') {
        return null;
    }

    return ['mime' => (string) $row['mime'], 'binari' => $binari];
}

/**
 * Daftar nama file gambar yang tersimpan di database.
 */
function gambarDaftarDb(): array
{
    try {
        $pdo = db();
        $st  = $pdo->query('SELECT nama FROM ' . GAMBAR_TABEL . ' ORDER BY nama');
        return $st->fetchAll(PDO::FETCH_COLUMN) ?: [];
    } catch (PDOException $e) {
        error_log('Daftar gambar database tidak bisa dibaca: ' . $e->getMessage());
        return [];
    }
}

/**
 * Cari file gambar di folder aset (toleran huruf besar/kecil).
 *
 * Server produksi memakai Linux yang membedakan huruf besar/kecil, sedangkan
 * Windows tidak. Supaya nama file di database tidak harus persis sama,
 * pencarian dilakukan dua kali: nama persis dulu, lalu nama tanpa regard
 * huruf besar/kecil.
 *
 * Balikan: path file absolut, atau null kalau tidak ada.
 */
function gambarCariFile(string $nama): ?string
{
    if (!gambarNamaAman($nama)) {
        return null;
    }

    $folder = GAMBAR_FOLDER_ASET;
    $path   = $folder . $nama;
    if (is_file($path)) {
        return $path;
    }

    // Pencarian tolerant huruf besar/kecil
    $bagian = explode('/', $nama);
    $sub    = count($bagian) > 1 ? $bagian[0] : '';
    $dasar  = $bagian[count($bagian) - 1];

    $folderCari = $sub !== '' ? $folder . $sub . '/' : $folder;
    if (!is_dir($folderCari)) {
        return null;
    }

    foreach (scandir($folderCari) ?: [] as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        if (strcasecmp($file, $dasar) === 0 && is_file($folderCari . $file)) {
            return $folderCari . $file;
        }
    }

    return null;
}

/**
 * Simpan file foto hasil unggahan owner.
 * Mengembalikan nama file relatif (mis. "owner/20261022-ab12cd34.jpg"),
 * atau null kalau filenya tidak valid.
 */
function gambarSimpanUnggahan(array $file): ?string
{
    $tmp    = (string) ($file['tmp_name'] ?? '');
    $ukuran = (int) ($file['size'] ?? 0);

    if ($tmp === '' || $ukuran > GAMBAR_MAKS_BYTES) {
        return null;
    }

    $binari = @file_get_contents($tmp);
    if ($binari === false || $binari === '') {
        return null;
    }

    // Pastikan benar-benar file gambar (bukan sekadar ekstensi .jpg)
    $info = @getimagesize($tmp);
    if ($info === false || !isset($info['mime'])) {
        return null;
    }

    $ekstensi = gambarEkstensi((string) $info['mime']);
    if ($ekstensi === null) {
        return null;
    }
    $mime = (string) $info['mime'];

    // Perkecil supaya hemat tempat (mis. foto HP 4 MB -> sekitar 200 KB)
    [$binari, $mime] = gambarPerkecil($binari, $mime);

    // Nama file unik supaya tidak menimpa file lain & aman dari nama berbahaya
    $namaBaru = 'owner/' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ekstensi;

    // 1) Simpan ke database -> bisa dibuka dari server mana pun
    gambarSimpanDb($namaBaru, $mime, $binari);

    // 2) Simpan ke folder aset sebagai cadangan (kalau upload dari server itu)
    $folderPemilik = GAMBAR_FOLDER_ASET . 'owner/';
    if (!is_dir($folderPemilik)) {
        @mkdir($folderPemilik, 0775, true);
    }
    @file_put_contents($folderPemilik . basename($namaBaru), $binari);

    return $namaBaru;
}

/**
 * Simpan file gambar yang sudah ada di folder aset ke database.
 * Dipakai skrip impor agar foto lama ikut terbaca di server lain.
 * Balikan: true kalau tersimpan.
 */
function gambarImporFile(string $path, string $namaRelatif): bool
{
    $binari = @file_get_contents($path);
    if ($binari === false || $binari === '') {
        return false;
    }

    $info = @getimagesize($path);
    $mime = (string) ($info['mime'] ?? 'image/jpeg');
    if (!isset($info['mime']) || gambarEkstensi($mime) === null) {
        return false;
    }

    [$binari, $mime] = gambarPerkecil($binari, $mime);
    return gambarSimpanDb($namaRelatif, $mime, $binari);
}
