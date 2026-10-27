<?php
/**
 * POST (JSON) backend/api/auth/lupa-password.php
 *   {identifier: 'email atau nomor HP'}
 *
 * Membuat tautan reset kata sandi dan mengirimkannya ke email akun.
 *
 * CATATAN KEAMANAN: respons API SELALU sama, apa pun yang terjadi di
 * belakang layar. Jadi orang tidak bisa menebak email mana yang terdaftar.
 */
require_once __DIR__ . '/../../core/password_reset.php';

// Pesan yang sama untuk semua kondisi, supaya email terdaftar tidak bocor.
const PESAN_NETRAL = 'Jika email/nomor HP tersebut terdaftar, kami telah mengirim tautan reset kata sandi ke email Anda.';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Metode tidak diizinkan.', [], 405);
}

// Fitur ini butuh tabel password_reset. Kalau migrasi belum dijalankan,
// beri pesan jelas, bukan error 500.
if (!resetSiap()) {
    respond(false, 'Fitur lupa kata sandi belum aktif. Jalankan migrasi 005_password_reset.sql di database.', [], 503);
}

$d         = input();
$identifier = is_string($d['identifier'] ?? null) ? trim($d['identifier']) : '';
if ($identifier === '') {
    respond(false, 'Email atau nomor HP wajib diisi.', ['errors' => ['identifier' => 'Email atau nomor HP wajib diisi.']], 422);
}

// Samakan dengan form login: email atau nomor HP yang dinormalisasi.
[$tipe, $nilai] = parseIdentifier($identifier);
if (!$tipe) {
    respond(true, PESAN_NETRAL);
}

$pdo = db();

// Buang token lama sesekali (bukan setiap permintaan) supaya tabel tidak menumpuk.
resetBersihkanSesekali($pdo);

[$akun, $role] = findAccount($pdo, $tipe, $nilai);

if (!$akun) {
    // Akun tidak ada: tetap balas pesan netral.
    respond(true, PESAN_NETRAL);
}

// Akun yang dibuat lewat Google tidak punya kata sandi, jadi tidak bisa di-reset.
if (empty($akun['password_hash'])) {
    respond(false, 'Akun ini terdaftar lewat Google. Silakan masuk dengan tombol Google.', [], 403);
}

// Akun wajib punya email, karena email adalah satu-satunya cara pengiriman.
$email = resetAlamatAkun($pdo, $role, (int) $akun['id']);
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Akun ini tidak memiliki email yang valid, jadi tautan reset tidak bisa dikirim. Silakan hubungi admin.', [], 422);
}

$ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

// Cooldown: jangan terima permintaan beruntun dari akun yang sama.
$sisaCooldown = resetCooldownSisa($pdo, $role, (int) $akun['id']);
if ($sisaCooldown > 0) {
    respond(true, PESAN_NETRAL, ['cooldown' => $sisaCooldown]);
}

// Rate limit per akun dan per IP.
if (resetJumlahPerJam($pdo, $role, (int) $akun['id'], $ip) >= RESET_MAKS_PER_JAM) {
    respond(false, 'Terlalu banyak permintaan reset kata sandi. Coba lagi nanti.', [], 429);
}

// Buat token & simpan hash-nya.
$token = resetTokenBaru();
try {
    resetSimpanToken($pdo, $role, (int) $akun['id'], $email, $ip === '' ? null : $ip, $token);
} catch (Throwable $e) {
    error_log('Simpan token reset gagal: ' . $e->getMessage());
    respond(false, 'Tautan reset gagal dibuat. Coba lagi sebentar lagi.', [], 500);
}

// Susun tautan reset.
$tautan = appUrlDasar() . '/frontend/pages/auth/reset-password.php?token=' . $token;

// Kirim email.
$berhasilKirim = kirimEmail(
    $email,
    'Reset Kata Sandi Arena Sport',
    resetEmailHtml((string) $akun['full_name'], $tautan)
);

if (!$berhasilKirim) {
    error_log('Kirim email reset gagal untuk ' . $email);
    respond(false, 'Email reset kata sandi gagal dikirim. Coba lagi sebentar lagi.', [], 502);
}

// Mode 'log' tidak benar-benar mengirim email, jadi tautannya dikembalikan
// supaya fitur tetap bisa dicoba saat masih developing.
$ekstra = [];
if (mailConfig()['mode'] !== 'smtp') {
    $ekstra['tautan_demo'] = $tautan;
    $ekstra['mode_demo']   = true;
}

respond(true, PESAN_NETRAL, $ekstra);
