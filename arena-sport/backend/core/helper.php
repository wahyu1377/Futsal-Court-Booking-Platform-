<?php
require_once __DIR__ . '/response.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// Ambil body JSON dari request POST
function input(): array {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(false, 'Metode tidak diizinkan.', [], 405);
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

// Pisahkan input menjadi ['email', nilai] atau ['phone', nilai]
function parseIdentifier(string $id): array {
    $id = trim($id);
    if (filter_var($id, FILTER_VALIDATE_EMAIL)) return ['email', strtolower($id)];

    $phone = preg_replace('/[\s\-]/', '', $id);
    if (preg_match('/^\+?[0-9]+$/', $phone)) {
        $phone = preg_replace('/^(\+62|62)/', '0', $phone);   // +62812.. -> 0812..
        if (preg_match('/^08[0-9]{8,11}$/', $phone)) {        // total 10-13 digit
            return ['phone', $phone];
        }
    }
    return [null, null];
}

// Nama tabel sesuai role
function tableFor(string $role): string {
    return $role === 'owner' ? 'owners' : 'users';
}

// Cari akun di tabel users, lalu owners.
// Mengembalikan [baris, 'user'|'owner'], atau [null, null] kalau tidak ada.
function findAccount(PDO $pdo, string $type, string $value): array {
    foreach (['user', 'owner'] as $role) {
        $table = tableFor($role);               // nama tabel dari daftar tetap, aman
        $st = $pdo->prepare("SELECT * FROM $table WHERE $type = ?");   // $type hanya 'email' atau 'phone'
        $st->execute([$value]);
        if ($row = $st->fetch()) return [$row, $role];
    }
    return [null, null];
}