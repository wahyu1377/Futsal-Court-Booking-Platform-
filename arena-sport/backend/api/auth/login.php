<?php
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../core/helper.php';

$d = input();
[$type, $value] = parseIdentifier($d['identifier'] ?? '');
$pass = $d['password'] ?? '';
if (!$type || $pass === '') respond(false, 'Email/nomor HP dan kata sandi wajib diisi.', [], 422);

$pdo = db();
[$u, $role] = findAccount($pdo, $type, $value);   // $role = 'user' atau 'owner'
$table = $role ? tableFor($role) : null;

if ($u && empty($u['password_hash'])) {
    respond(false, 'Akun ini terdaftar lewat Google. Silakan masuk dengan tombol Google.', [], 403);
}

if ($u && $u['locked_until'] && strtotime($u['locked_until']) > time()) {
    respond(false, 'Akun diamankan sementara. Coba lagi dalam ' . LOCK_MINUTES . ' menit.', ['locked' => true], 423);
}

if (!$u || !password_verify($pass, $u['password_hash'])) {
    $left = MAX_ATTEMPTS - 1;
    if ($u) {
        $n = $u['failed_attempts'] + 1;
        $left = max(0, MAX_ATTEMPTS - $n);
        if ($n >= MAX_ATTEMPTS) {
            $pdo->prepare("UPDATE $table SET failed_attempts=0, locked_until=NOW() + INTERVAL '" . LOCK_MINUTES . " minutes' WHERE id=?")->execute([$u['id']]);
        } else {
            $pdo->prepare("UPDATE $table SET failed_attempts=? WHERE id=?")->execute([$n, $u['id']]);
        }
    }
    respond(false, 'Email atau kata sandi salah. Silakan periksa kembali data Anda.', ['attempts_left' => $left], 401);
}

$pdo->prepare("UPDATE $table SET failed_attempts=0, locked_until=NULL WHERE id=?")->execute([$u['id']]);
session_regenerate_id(true);
$_SESSION['user_id']   = $u['id'];
$_SESSION['user_name'] = $u['full_name'];
$_SESSION['role']      = $role;          // dari tabel tempat akun ditemukan

respond(true, 'Login berhasil.', ['role' => $role]);