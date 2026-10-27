<?php
/**
 * Contoh konfigurasi DOKU untuk mesin ini.
 *
 * Salin berkas ini menjadi doku.local.php di folder yang sama, lalu sesuaikan.
 * doku.local.php sudah masuk .gitignore - jangan pernah di-commit.
 *
 * Letak: arena-sport/backend/config/doku.local.php
 *
 * Kredensial (client_id & secret_key) TIDAK ditulis di sini, tapi di .env:
 *   DOKU_CLIENT_ID=...
 *   DOKU_SECRET_KEY=...
 * Alasannya: .env sudah masuk .gitignore, dan rahasianya tidak ikut tersalin
 * kalau berkasnya di-commit. Ambil nilainya dari DOKU Back Office -> Integration -> API Keys.
 */
return [
    'env'        => 'sandbox',   // 'sandbox' atau 'production'

    // Alamat dasar folder arena-sport di browser (tanpa garis miring di akhir).
    //   - PHP built-in server dari root repo : http://localhost:8000/arena-sport
    //   - XAMPP (folder arena-sport di htdocs): http://localhost/arena-sport
    //   - Produksi (Wasmer)                  : https://arena-sport.wasmer.app/arena-sport
    'app_url'    => 'http://localhost:8000/arena-sport',

    // URL PUBLIK HTTPS ke backend/api/payment/doku_notify.php.
    // Kosongkan saat masih di localhost (DOKU tidak bisa menjangkau localhost).
    //
    // Untuk produksi (sudah diverifikasi terhadap domain yang aktif):
    //   https://arena-sport.wasmer.app/arena-sport/backend/api/payment/doku_notify.php
    //
    // PENTING: nilai ini harus sama persis dengan URL yang didaftarkan di
    // DOKU Back Office, karena dipakai untuk menghitung ulang tanda tangan
    // notifikasi. Bedanya satu karakter pun membuat notifikasi ditolak.
    //
    // Catatan awalan /arena-sport: Wasmer saat ini memakai root folder
    // repository. Kalau root directory Wasmer diubah ke folder arena-sport,
    // awalan ini ikut hilang.
    'notify_url' => '',
];
