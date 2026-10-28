<?php
/**
 * Impor foto lapangan yang sudah ada di folder aset ke tabel gambar_unggahan.
 *
 * Dipakai sekali setelah tabel `gambar_unggahan` dibuat, supaya foto bawaan
 * (dan foto unggahan owner yang tersimpan di laptop) ikut bisa dibaca dari
 * server mana pun - termasuk server produksi.
 *
 * Cara menjalankan (dari root repository):
 *   php arena-sport/backend/impor-foto.php
 *
 * Foto yang sudah ada di database tidak ditimpa (dilewati).
 */
require_once __DIR__ . '/core/gambar.php';

$folderAset = GAMBAR_FOLDER_ASET;

if (!is_dir($folderAset)) {
    fwrite(STDERR, "Folder aset tidak ditemukan: $folderAset\n");
    exit(1);
}

// Kumpulkan semua file gambar: folder aset + sub folder owner/
$daftar = [];
foreach (scandir($folderAset) ?: [] as $file) {
    if ($file === '.' || $file === '..') continue;
    $path = $folderAset . $file;
    if (is_file($path) && in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true)) {
        $daftar[$file] = $path;
    }
}

$folderOwner = $folderAset . 'owner/';
if (is_dir($folderOwner)) {
    foreach (scandir($folderOwner) ?: [] as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $folderOwner . $file;
        if (is_file($path) && in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $daftar['owner/' . $file] = $path;
        }
    }
}

if (!$daftar) {
    echo "Tidak ada file gambar di folder aset.\n";
    exit(0);
}

$sudahAda = array_flip(gambarDaftarDb());
$berhasil  = 0;
$gagal     = 0;

foreach ($daftar as $namaRelatif => $path) {
    if (isset($sudahAda[$namaRelatif])) {
        echo "  lewati (sudah ada): $namaRelatif\n";
        continue;
    }
    if (gambarImporFile($path, $namaRelatif)) {
        echo "  tersimpan: $namaRelatif\n";
        $berhasil++;
    } else {
        echo "  GAGAL: $namaRelatif\n";
        $gagal++;
    }
}

echo "\nSelesai. Tersimpan: $berhasil, gagal: $gagal.\n";

if ($gagal > 0) {
    echo "Catatan: kalau semua gagal, pastikan tabel gambar_unggahan sudah dibuat\n";
    echo "(backend/sql/007_gambar_unggahan.sql) dan role aplikasi punya hak akses.\n";
}
