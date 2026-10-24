<?php
require __DIR__ . '/../../../backend/core/helper.php';   // sekaligus memulai session

// Hanya lewat tombol (POST), supaya tidak bisa dipicu dari link atau gambar di situs lain
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 3600,
        'path'     => $p['path'],
        'domain'   => $p['domain'],
        'secure'   => $p['secure'],
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
session_destroy();

header('Location: login.php');
exit;