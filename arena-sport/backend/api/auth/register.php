<?php
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../core/helper.php';

$d = input();
$name = trim($d['full_name'] ?? '');
$pass = $d['password'] ?? '';
$role = $d['role'] ?? 'user';
[$type, $value] = parseIdentifier($d['identifier'] ?? '');

$errors = [];
if (mb_strlen($name) < 3 || mb_strlen($name) > 50)
    $errors['full_name'] = 'Nama lengkap harus 3-50 karakter.';
elseif (!preg_match("/^[\p{L}][\p{L}\s.'-]*$/u", $name))
    $errors['full_name'] = 'Nama hanya boleh huruf, spasi, titik, apostrof, dan strip.';

if (!$type)
    $errors['identifier'] = 'Masukkan email valid atau nomor HP 10-13 digit (08xxx).';

if (strlen($pass) < 8 || strlen($pass) > 64)
    $errors['password'] = 'Kata sandi harus 8-64 karakter.';
elseif (!preg_match('/[A-Za-z]/', $pass) || !preg_match('/\d/', $pass))
    $errors['password'] = 'Kata sandi harus mengandung huruf dan angka.';

if ($pass !== ($d['password_confirm'] ?? ''))
    $errors['password_confirm'] = 'Konfirmasi kata sandi tidak sama.';

if (!in_array($role, ['user', 'owner'], true))
    $errors['role'] = 'Pilih jenis akun yang valid.';

if (empty($d['terms']))
    $errors['terms'] = 'Anda harus menyetujui syarat & ketentuan.';

if ($errors) respond(false, 'Data belum valid.', ['errors' => $errors], 422);

$pdo = db();

// Email/HP tidak boleh dipakai di tabel mana pun (users maupun owners)
[$ada] = findAccount($pdo, $type, $value);
if ($ada) {
    respond(false, 'Akun sudah terdaftar.', ['errors' => ['identifier' => 'Email/nomor HP sudah terdaftar.']], 409);
}

$table = tableFor($role);
try {
    $pdo->prepare("INSERT INTO $table (full_name, $type, password_hash) VALUES (?, ?, ?)")
        ->execute([$name, $value, password_hash($pass, PASSWORD_DEFAULT)]);
} catch (PDOException $e) {
    if ($e->getCode() === '23505') {   // pelanggaran unique (kasus dua pendaftaran bersamaan)
        respond(false, 'Akun sudah terdaftar.', ['errors' => ['identifier' => 'Email/nomor HP sudah terdaftar.']], 409);
    }
    error_log('Register gagal: ' . $e->getMessage());
    respond(false, 'Terjadi kesalahan server.', [], 500);
}

respond(true, 'Pendaftaran berhasil! Silakan masuk.');