<?php
/**
 * Penyaji gambar lapangan (endpoint statis).
 *
 * Dipakai semua halaman untuk menampilkan foto lapangan:
 *   frontend/gambar.php?f=champion-futsal.jpg
 *   frontend/gambar.php?f=owner/20261022-ab12cd34.jpg
 *
 * Sumber gambar (berurutan):
 *   1) database (tabel gambar_unggahan) -> foto hasil unggahan owner,
 *      bisa dibuka dari server mana pun karena tersimpan di database bersama.
 *   2) folder aset (frontend/assets/img/lapangan/) -> foto bawaan galeri.
 *   3) 404 kalau file-nya benar-benar tidak ada.
 *
 * Nama file dicari dengan tolerate huruf besar/kecil supaya perbedaan
 * "smash-futsal.jpg" dan "smash-Futsal.jpg" tidak lagi menjadi masalah di
 * server Linux (server produksi) seperti di Windows.
 */
require_once __DIR__ . '/../backend/core/gambar.php';

$nama = isset($_GET['f']) ? trim((string) $_GET['f']) : '';

if (!gambarNamaAman($nama)) {
    http_response_code(400);
    exit('Parameter f tidak valid.');
}

/** Kirim isi file gambar ke browser. */
function gambarKirim(string $binari, string $mime): void
{
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . strlen($binari));
    // Nama file tidak pernah berubah, jadi boleh di-cache lama
    header('Cache-Control: public, max-age=604800');
    header('X-Content-Type-Options: nosniff');
    echo $binari;
    exit;
}

// 1) Coba dari database (foto unggahan owner)
$dariDb = gambarAmbilDb($nama);
if ($dariDb !== null) {
    gambarKirim($dariDb['binari'], $dariDb['mime']);
}

// 2) Coba dari folder aset (foto bawaan galeri)
$file = gambarCariFile($nama);
if ($file !== null) {
    $info = @getimagesize($file);
    $mime = (string) ($info['mime'] ?? 'image/jpeg');
    $binari = @file_get_contents($file);
    if ($binari !== false && $binari !== '') {
        gambarKirim($binari, $mime);
    }
}

// 3) File-nya memang tidak ada di mana pun
http_response_code(404);
exit('Gambar tidak ditemukan.');
