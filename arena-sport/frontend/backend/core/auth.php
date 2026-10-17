<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helper.php';   // sekaligus memulai session

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

// Untuk halaman yang wajib login; $role opsional ('user' atau 'owner')
function requireLogin(?string $role = null): void {
    if (!isLoggedIn()) { header('Location: ../auth/login.php'); exit; }
    if ($role && $_SESSION['role'] !== $role) {
        header('Location: ' . ($_SESSION['role'] === 'owner' ? '../owner/dashboard.php' : '../user/beranda.php'));
        exit;
    }
}

// Untuk halaman publik: data pengguna kalau login, atau null kalau tamu
function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id'   => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'role' => $_SESSION['role'],
    ];
}