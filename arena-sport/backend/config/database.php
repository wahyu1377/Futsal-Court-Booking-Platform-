<?php
const MAX_ATTEMPTS = 5;   // batas salah password
const LOCK_MINUTES = 15;  // lama akun dikunci

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;

    $c = require __DIR__ . '/database.local.php';
    $dsn = "pgsql:host={$c['host']};port={$c['port']};dbname={$c['name']};sslmode=require";

    try {
        $pdo = new PDO($dsn, $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        // Jangan tampilkan detail ke browser (bisa membocorkan host/user)
        error_log('DB connect gagal: ' . $e->getMessage());
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Server database tidak dapat dihubungi.']);
        exit;
    }
    return $pdo;
}