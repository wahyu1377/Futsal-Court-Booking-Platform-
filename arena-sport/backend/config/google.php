<?php
/**
 * Konfigurasi Google OAuth (Google Identity Services / "Masuk dengan Google").
 *
 * Client ID bisa diisi lewat .env (GOOGLE_CLIENT_ID) supaya tidak perlu
 * mengubah kode kalau client ID diganti. Kalau .env kosong, dipakai nilai
 * bawaan di bawah.
 *
 * PENTING - sisi server saja TIDAK cukup untuk membuat tombol Google
 * berfungsi. Di Google Cloud Console, client ID ini harus punya
 * "Authorized JavaScript origins" yang memuat ORIGIN aplikasi (tanpa path,
 * tanpa garis miring di akhir). Port termasuk bagian dari origin, jadi
 * `http://localhost` TIDAK sama dengan `http://localhost:8000`.
 *
 * Cek origin yang harus didaftarkan lewat:
 *   - `php arena-sport/backend/smoke-test.php` bagian "Google OAuth".
 */
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/../core/url.php';

/** Client ID bawaan (aman dipublikasikan - client ID bukan rahasia). */
const GOOGLE_CLIENT_ID_DEFAULT = '956268908707-a76n5ma1iboa0mok5boqsvqrg6c3d1vh.apps.googleusercontent.com';

/**
 * Client ID yang dipakai aplikasi.
 * Sumber: .env (GOOGLE_CLIENT_ID) -> nilai bawaan.
 */
function googleClientId(): string
{
    static $id = null;
    if ($id === null) {
        $dariEnv = trim((string) (env('GOOGLE_CLIENT_ID') ?: ''));
        $id = $dariEnv !== '' ? $dariEnv : GOOGLE_CLIENT_ID_DEFAULT;
    }
    return $id;
}

/** True kalau client ID diambil dari .env (bukan nilai bawaan). */
function googleClientIdDariEnv(): bool
{
    return trim((string) (env('GOOGLE_CLIENT_ID') ?: '')) !== '';
}

/**
 * Origin aplikasi (scheme + host + port), TANPA path.
 *
 * Ini yang harus diisi di "Authorized JavaScript origins" pada Google Cloud
 * Console. Nilai diambil dari APP_URL di .env; kalau kosong, ditebak dari
 * request yang sedang berjalan.
 */
function googleOriginHarusDaftar(): string
{
    $dasar = appUrlDasar();
    if ($dasar === '') {
        return '';
    }
    $bagian = parse_url($dasar);
    if (!isset($bagian['scheme'], $bagian['host'])) {
        return '';
    }

    $origin = $bagian['scheme'] . '://' . $bagian['host'];
    // Port hanya ditampilkan kalau memang dipakai (bukan default http/https).
    if (isset($bagian['port'])) {
        $default = ($bagian['scheme'] === 'https' && $bagian['port'] == 443)
            || ($bagian['scheme'] === 'http' && $bagian['port'] == 80);
        if (!$default) {
            $origin .= ':' . $bagian['port'];
        }
    }
    return $origin;
}
