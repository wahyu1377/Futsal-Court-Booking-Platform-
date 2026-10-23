<?php
/**
 * Konfigurasi aplikasi.
 * 'local'      -> cek login owner DIMATIKAN supaya halaman bisa dilihat saat dev.
 * 'production' -> cek login owner WAJIB (lihat require_owner() di helpers.php).
 * WAJIB diganti ke 'production' sebelum deploy / sebelum dinilai.
 */
define('APP_ENV', 'local');
