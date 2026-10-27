<?php
/**
 * backend/api/auth/reset-password.php
 *
 *   GET  ?token=xxxx
 *        -> cek token masih berlaku, balas {valid: true|false, message}
 *   POST (JSON) {token, password, password_confirm}
 *        -> ganti kata sandi dengan yang baru
 *
 * Token yang dipakai langsung dimatikan, jadi tidak bisa dipakai dua kali.
 */
require_once __DIR__ . '/../../core/password_reset.php';

if (!resetSiap()) {
    respond(false, 'Fitur lupa kata sandi belum aktif. Jalankan migrasi 005_password_reset.sql di database.', [], 503);
}

$metode = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

// ---------------------------------------------------------------------------
// GET: cek apakah token masih berlaku
// ---------------------------------------------------------------------------
if ($metode === 'GET') {
    $token = is_string($_GET['token'] ?? null) ? trim($_GET['token']) : '';
    if ($token === '') {
        respond(false, 'Token reset tidak ditemukan.', ['valid' => false], 400);
    }

    $pdo  = db();
    $baris = resetCariToken($pdo, $token);
    if (!$baris) {
        respond(false, 'Tautan reset kata sandi sudah tidak berlaku. Silakan minta tautan baru.', ['valid' => false], 410);
    }

    respond(true, 'Tautan valid.', ['valid' => true, 'expired_at' => $baris['expires_at']]);
}

// ---------------------------------------------------------------------------
// POST: ganti kata sandi
// ---------------------------------------------------------------------------
if ($metode !== 'POST') {
    respond(false, 'Metode tidak diizinkan.', [], 405);
}

$d      = input();
$token  = is_string($d['token'] ?? null) ? trim($d['token']) : '';
$pass   = is_string($d['password'] ?? null) ? $d['password'] : '';
$passU  = is_string($d['password_confirm'] ?? null) ? $d['password_confirm'] : '';

$errors = [];
if ($token === '') {
    $errors['token'] = 'Token reset tidak ditemukan.';
}

// Aturan kekuatan sama persis dengan halaman daftar (register.php)
if ($pass === '') {
    $errors['password'] = 'Kata sandi wajib diisi.';
} elseif (strlen($pass) < 8 || strlen($pass) > 64) {
    $errors['password'] = 'Kata sandi harus 8-64 karakter.';
} elseif (!preg_match('/[A-Za-z]/', $pass) || !preg_match('/\d/', $pass)) {
    $errors['password'] = 'Kata sandi harus mengandung huruf dan angka.';
}

if ($passU === '') {
    $errors['password_confirm'] = 'Konfirmasi kata sandi wajib diisi.';
} elseif ($pass !== $passU) {
    $errors['password_confirm'] = 'Konfirmasi kata sandi tidak sama.';
}

if ($errors) {
    respond(false, 'Data belum valid.', ['errors' => $errors], 422);
}

$pdo   = db();
$baris = resetCariToken($pdo, $token);
if (!$baris) {
    respond(false, 'Tautan reset kata sandi sudah tidak berlaku. Silakan minta tautan baru.', ['valid' => false], 410);
}

// Jangan biarkan kata sandi baru sama dengan yang lama.
$tabel = tableFor((string) $baris['role']);
$st = $pdo->prepare("SELECT password_hash FROM $tabel WHERE id = ?");
$st->execute([(int) $baris['account_id']]);
$hashLama = (string) $st->fetchColumn();
if ($hashLama !== '' && password_verify($pass, $hashLama)) {
    respond(false, 'Kata sandi baru harus berbeda dari kata sandi lama.', ['errors' => ['password' => 'Kata sandi baru harus berbeda dari kata sandi lama.']], 422);
}

if (!resetTerapkanPassword($pdo, $token, (string) $baris['role'], (int) $baris['account_id'], $pass)) {
    respond(false, 'Kata sandi gagal diperbarui. Coba lagi sebentar lagi.', [], 500);
}

respond(true, 'Kata sandi berhasil diperbarui. Silakan masuk dengan kata sandi baru.', ['redirect' => 'login.php?reset=1']);
