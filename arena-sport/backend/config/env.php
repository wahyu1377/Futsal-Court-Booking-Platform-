<?php
/**
 * Pembacaan konfigurasi dari file .env.
 *
 * Tujuannya: kredensial database TIDAK ikut ter-commit ke git.
 * File .env dicari di beberapa lokasi (sesuai urutan):
 *   1. root repository  -> di luar document root, jadi tidak bisa diunduh lewat HTTP
 *   2. folder arena-sport
 *   3. folder kerja saat ini
 *
 * Format .env (satu variabel per baris):
 *   DB_DRIVER=pgsql
 *   DB_HOST=aws-0-ap-south-1.pooler.supabase.com
 *   DB_PORT=5432
 *   DB_NAME=postgres
 *   DB_USER=arena_app.whmqodgxzjpbumtilrgr
 *   DB_PASS=rahasia
 *   DB_SSLMODE=require
 */

/**
 * Ambil nilai konfigurasi dari .env, dengan nilai cadangan bila tidak ada.
 *
 * Urutan prioritas:
 *   1. Environment variable asli (getenv) - berguna untuk testing & hosting
 *   2. File .env
 */
function env(string $key, ?string $default = null): ?string
{
    static $values = null;

    // Environment variable asli selalu menang (mis. saat smoke test ganti driver).
    // Nilai kosong pun dihormati, karena MySQL lokal bisa memakai password kosong.
    $dariSistem = getenv($key);
    if ($dariSistem !== false) {
        return $dariSistem;
    }

    if ($values === null) {
        $values = [];

        // Lokasi kandidat .env (root repo didahulukan karena di luar document root)
        $candidates = [
            dirname(__DIR__, 3) . '/.env',   // root repository
            dirname(__DIR__, 2) . '/.env',   // folder arena-sport
            getcwd() . '/.env',
        ];

        foreach ($candidates as $file) {
            if (!is_readable($file)) {
                continue;
            }

            $baris = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($baris as $line) {
                $line = trim($line);

                // Lewati komentar dan baris kosong
                if ($line === '' || $line[0] === '#') {
                    continue;
                }

                // Format wajib KEY=VALUE
                if (strpos($line, '=') === false) {
                    continue;
                }

                [$nama, $nilai] = explode('=', $line, 2);
                $nilai = trim($nilai);

                // Buang tanda kutip pembungkus bila ada
                if (strlen($nilai) >= 2
                    && ($nilai[0] === '"' || $nilai[0] === "'")
                    && $nilai[strlen($nilai) - 1] === $nilai[0]) {
                    $nilai = substr($nilai, 1, -1);
                }

                $values[trim($nama)] = $nilai;
            }
            break; // cukup file .env pertama yang ditemukan
        }
    }

    return $values[$key] ?? $default;
}
