<?php
require __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/google.php';
require __DIR__ . '/../../core/helper.php';

$d = input();
$credential = $d['credential'] ?? '';
$role = $d['role'] ?? null;

if (!is_string($credential) || $credential === '') respond(false, 'Token Google tidak ditemukan.', [], 422);
if ($role !== null && !in_array($role, ['user', 'owner'], true)) respond(false, 'Jenis akun tidak valid.', [], 422);

/**
 * Verifikasi ID token ke Google. Mengembalikan data akun yang sudah terverifikasi.
 * Versi ini memakai endpoint tokeninfo (cukup untuk pengembangan).
 * Untuk produksi, ganti isinya dengan google/apiclient (lihat bagian "Sebelum online").
 */
function verifyGoogleToken(string $idToken): array {
    $ch = curl_init('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken));
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10]);
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($res === false) {
        error_log('Verifikasi Google gagal: ' . curl_error($ch));
        respond(false, 'Tidak dapat menghubungi Google. Coba lagi.', [], 502);
    }

    $p        = json_decode($res, true);
    $issOk    = in_array($p['iss'] ?? '', ['accounts.google.com', 'https://accounts.google.com'], true);
    $verified = filter_var($p['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

    if ($code !== 200 || !is_array($p)
        || ($p['aud'] ?? '') !== googleClientId()      // token harus untuk aplikasi ini
        || !$issOk
        || (int)($p['exp'] ?? 0) < time()
        || empty($p['sub']) || empty($p['email']) || !$verified) {
        respond(false, 'Token Google tidak valid.', [], 401);
    }
    return $p;
}

function loginAs(array $acc, string $role): never {
    session_regenerate_id(true);
    $_SESSION['user_id']   = $acc['id'];
    $_SESSION['user_name'] = $acc['full_name'];
    $_SESSION['role']      = $role;
    respond(true, 'Login berhasil.', ['role' => $role]);
}

$g     = verifyGoogleToken($credential);
$sub   = $g['sub'];
$email = strtolower($g['email']);
$pdo   = db();

// 1. Akun yang sudah pernah dihubungkan dengan Google ini -> langsung masuk
foreach (['user', 'owner'] as $r) {
    $st = $pdo->prepare('SELECT * FROM ' . tableFor($r) . ' WHERE google_id = ?');
    $st->execute([$sub]);
    if ($acc = $st->fetch()) loginAs($acc, $r);
}

// 2. Email sudah dipakai akun yang daftar manual -> tolak (alasan ada di catatan bawah)
[$ada] = findAccount($pdo, 'email', $email);
if ($ada) {
    respond(false, 'Email ini sudah terdaftar dengan kata sandi. Silakan masuk memakai email dan kata sandi.', [], 409);
}

// 3. Belum punya akun: minta pengguna memilih jenis akun dulu
if (!$role) respond(true, 'Pilih jenis akun.', ['need_role' => true]);

$name = trim($g['name'] ?? '');
if ($name === '') $name = strstr($email, '@', true);
$name = mb_substr($name, 0, 100);

try {
    // Catatan: id akun diambil lewat lastInsertId(), bukan "RETURNING id".
    // Alasannya: RETURNING hanya ada di PostgreSQL, sedangkan lastInsertId()
    // jalan di PostgreSQL maupun MySQL (pola yang sama dipakai di
    // api/owner/lapangan.php).
    $st = $pdo->prepare(
        'INSERT INTO ' . tableFor($role) . ' (full_name, email, google_id) VALUES (?, ?, ?)'
    );
    $st->execute([$name, $email, $sub]);
    $idBaru = (int) $pdo->lastInsertId();

    $st = $pdo->prepare('SELECT id, full_name FROM ' . tableFor($role) . ' WHERE id = ?');
    $st->execute([$idBaru]);
    $akunBaru = $st->fetch();
    if (!$akunBaru) {
        respond(false, 'Akun gagal dibuat. Coba lagi sebentar lagi.', [], 500);
    }
    loginAs($akunBaru, $role);
} catch (PDOException $e) {
    if ($e->getCode() === '23505') respond(false, 'Akun sudah terdaftar. Coba masuk lagi.', [], 409);
    error_log('Google signup gagal: ' . $e->getMessage());
    respond(false, 'Terjadi kesalahan server.', [], 500);
}