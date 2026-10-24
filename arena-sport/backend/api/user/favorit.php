<?php
require __DIR__ . '/../../core/auth.php';

$d = input();   // hanya menerima POST (JSON)

if (!isLoggedIn())                respond(false, 'Masuk dulu untuk menyimpan favorit.', ['need_login' => true], 401);
if ($_SESSION['role'] !== 'user') respond(false, 'Fitur favorit khusus akun pengguna.', [], 403);

$lapanganId = filter_var($d['lapangan_id'] ?? null, FILTER_VALIDATE_INT);
if (!$lapanganId || $lapanganId < 1) respond(false, 'Lapangan tidak valid.', [], 422);
$aktif = filter_var($d['aktif'] ?? true, FILTER_VALIDATE_BOOLEAN);   // true = simpan, false = hapus

$pdo    = db();
$userId = (int)$_SESSION['user_id'];    // selalu dari session, bukan dari kiriman browser

if ($aktif) {
    $cek = $pdo->prepare('SELECT 1 FROM lapangan WHERE id = ? AND aktif::int = 1');
    $cek->execute([$lapanganId]);
    if (!$cek->fetchColumn()) respond(false, 'Lapangan tidak ditemukan.', [], 404);

    $pdo->prepare('INSERT INTO favorit (user_id, lapangan_id) VALUES (?, ?) ON CONFLICT DO NOTHING')
        ->execute([$userId, $lapanganId]);
} else {
    $pdo->prepare('DELETE FROM favorit WHERE user_id = ? AND lapangan_id = ?')
        ->execute([$userId, $lapanganId]);
}

$total = $pdo->prepare('SELECT COUNT(*) FROM favorit WHERE user_id = ?');
$total->execute([$userId]);

respond(true, $aktif ? 'Ditambahkan ke favorit.' : 'Dihapus dari favorit.',
        ['aktif' => $aktif, 'total' => (int)$total->fetchColumn()]);