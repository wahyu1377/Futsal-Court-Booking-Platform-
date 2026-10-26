<?php
/**
 * Format balasan JSON untuk semua API.
 * Error PHP tidak lagi dicetak sebagai HTML (itu yang membuat browser gagal membaca JSON).
 */
const APP_DEBUG = true;   // ubah ke false saat situs sudah online (pesan error detail disembunyikan)

ini_set('display_errors', '0');   // error tetap masuk log (xampp/apache/logs/error.log)

set_exception_handler(function (Throwable $e) {
    error_log((string) $e);
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode([
        'success' => false,
        'message' => APP_DEBUG ? $e->getMessage() : 'Terjadi kesalahan server.',
    ]);
    exit;
});

function respond(bool $ok, string $message, array $extra = [], int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => $ok, 'message' => $message], $extra));
    exit;
}