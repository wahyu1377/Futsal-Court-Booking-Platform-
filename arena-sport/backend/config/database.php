<?php
/**
 * Konfigurasi koneksi database.
 *
 * Sumber kredensial (berurutan prioritas):
 *   1. .env di root repository  -> dipakai bersama, tidak pernah ter-commit
 *   2. backend/config/database.local.php -> hanya untuk mesin lokal, sudah di-.gitignore
 *
 * Catatan keamanan: jangan pernah memakai role "postgres" untuk aplikasi.
 * Role postgres di Supabase punya BYPASSRLS + CREATEROLE, jadi kalau
 * kredensialnya bocor, seluruh data bisa dibaca tanpa batas RLS.
 * Gunakan role non-privileged (contoh: arena_app).
 */
require_once __DIR__ . '/env.php';

const MAX_ATTEMPTS = 5;   // batas salah password
const LOCK_MINUTES = 15;  // lama akun dikunci

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;

    $cfg = db_config();

    // Susun DSN sesuai driver-nya (pgsql untuk Supabase, mysql untuk lokal)
    if ($cfg['driver'] === 'mysql') {
        $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};charset=utf8mb4";
    } else {
        $dsn = "pgsql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};sslmode={$cfg['sslmode']}";
    }

    try {
        $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        // Jangan tampilkan detail ke browser (bisa membocorkan host/user)
        error_log('DB connect gagal: ' . $e->getMessage());
        if (PHP_SAPI === 'cli') {
            // Jalur CLI (smoke test / artisan-like script): cukup pesan ringkas
            fwrite(STDERR, 'DB connect gagal: ' . $e->getMessage() . PHP_EOL);
            exit(1);
        }
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Server database tidak dapat dihubungi.']);
        exit;
    }
    // Samakan zona waktu sesi database dengan zona waktu aplikasi
    if ($cfg['driver'] === 'pgsql') {
        $pdo->exec("SET TIME ZONE 'Asia/Jakarta'");
    }
    date_default_timezone_set('Asia/Jakarta');

    return $pdo;
}

/**
 * Gabungkan konfigurasi dari .env dan database.local.php, lalu normalisasi.
 */
function db_config(): array {
    $cfg = [];

    // 1. File lokal (opsional) - hanya dibaca bila ada
    $fileLokal = __DIR__ . '/database.local.php';
    if (is_file($fileLokal)) {
        $cfg = (array) require $fileLokal;
    }

    // 2. .env punya prioritas lebih tinggi (dipakai bersama antar anggota tim)
    $petaEnv = [
        'driver'  => 'DB_DRIVER',
        'host'    => 'DB_HOST',
        'port'    => 'DB_PORT',
        'name'    => 'DB_NAME',
        'user'    => 'DB_USER',
        'pass'    => 'DB_PASS',
        'sslmode' => 'DB_SSLMODE',
    ];
    foreach ($petaEnv as $kunci => $namaEnv) {
        $nilai = env($namaEnv);
        if ($nilai !== null && $nilai !== '') {
            $cfg[$kunci] = $nilai;
        }
    }

    $host = (string) ($cfg['host'] ?? 'localhost');
    $port = (string) ($cfg['port'] ?? '');

    // Deteksi driver bila tidak disebut eksplisit
    $driver = strtolower((string) ($cfg['driver'] ?? ''));
    if ($driver === '') {
        $driver = ($port === '5432' || str_contains($host, 'supabase')) ? 'pgsql' : 'mysql';
    }
    if ($port === '') {
        $port = $driver === 'pgsql' ? '5432' : '3306';
    }

    return [
        'driver'  => $driver,
        'host'    => $host,
        'port'    => $port,
        'name'    => (string) ($cfg['name'] ?? ($driver === 'pgsql' ? 'postgres' : 'arena_sport')),
        'user'    => (string) ($cfg['user'] ?? 'root'),
        'pass'    => (string) ($cfg['pass'] ?? ''),
        'sslmode' => (string) ($cfg['sslmode'] ?? 'require'),
    ];
}
