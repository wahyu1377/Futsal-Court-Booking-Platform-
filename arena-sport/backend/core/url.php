<?php
/**
 * Penentuan alamat dasar aplikasi (base URL).
 *
 * Dipakai untuk membentuk tautan yang dikirim ke user, misalnya tautan reset
 * kata sandi. Nilai ini harus bisa dipakai tanpa konfigurasi tambahan, karena
 * orang yang baru menyalin .env.example ke .env tidak boleh langsung error.
 *
 * Urutan sumber nilai:
 *   1. APP_URL di .env / environment variable  (eksplisit, menang)
 *   2. Tebakan dari request yang sedang berjalan
 *
 * Contoh:
 *   APP_URL=http://localhost:8000/arena-sport
 *   APP_URL=https://arena-sport.wasmer.app/arena-sport
 */
require_once __DIR__ . '/../config/env.php';

/**
 * Alamat dasar folder arena-sport, tanpa garis miring di akhir.
 * String kosong kalau tidak bisa ditebak (mis. dijalankan dari CLI).
 */
function appUrlDasar(): string
{
    $dariEnv = env('APP_URL');
    if ($dariEnv !== null && $dariEnv !== '') {
        return rtrim($dariEnv, '/');
    }
    return appUrlTebak();
}

/**
 * Tebak alamat dasar dari request yang sedang berjalan.
 *
 * Logikanya: ambil bagian path sebelum folder `backend/` atau `frontend/`.
 * Contoh SCRIPT_NAME:
 *   /arena-sport/backend/api/auth/lupa-password.php -> https://host/arena-sport
 *   /backend/api/auth/lupa-password.php             -> https://host
 */
function appUrlTebak(): string
{
    $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
    if ($host === '') {
        return '';   // dijalankan dari CLI, tidak ada request untuk ditebak
    }

    $https = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https'
        || (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';

    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $base   = '';
    foreach (['/backend/', '/frontend/'] as $penanda) {
        $posisi = strpos($script, $penanda);
        if ($posisi !== false) {
            $base = substr($script, 0, $posisi);
            break;
        }
    }

    return ($https ? 'https://' : 'http://') . $host . $base;
}
