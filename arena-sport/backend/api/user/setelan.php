<?php
/**
 * API setelan pengguna (preferensi notifikasi).
 *
 *   GET             -> preferensi notifikasi pengguna
 *   POST aksi=simpan -> simpan preferensi notifikasi
 *
 * Data disimpan di tabel user_settings (lihat backend/sql/006_user_settings.sql).
 * Kalau tabelnya belum ada, API tetap menjawab dengan nilai default dan
 * flag 'tersimpan_server' => false, sehingga halaman bisa mundur ke localStorage.
 */
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/skema.php';

if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'user') {
    respond(false, 'Akses ditolak. API ini khusus akun pengguna.', [], 403);
}

$pdo     = db();
$idUser  = (int) $_SESSION['user_id'];

// Cek tabel user_settings (di-cache per request)
$tabelAda = tabelAda($pdo, 'user_settings');

/** Nilai bawaan kalau pengaturan belum pernah disimpan. */
const PREFS_DEFAULT_USER = [
    'notifikasi_pesanan' => true,
    'notifikasi_email'   => false,
    'notifikasi_wa'      => true,
];

/** Baca preferensi pengguna dari database. */
function bacaPrefsUser(PDO $pdo, int $idUser): array
{
    try {
        $st = $pdo->prepare('SELECT notifikasi_pesanan, notifikasi_email, notifikasi_wa FROM user_settings WHERE user_id = ?');
        $st->execute([$idUser]);
        $baris = $st->fetch();
        if (!$baris) {
            return PREFS_DEFAULT_USER;
        }
        return [
            'notifikasi_pesanan' => (bool) $baris['notifikasi_pesanan'],
            'notifikasi_email'   => (bool) $baris['notifikasi_email'],
            'notifikasi_wa'      => (bool) $baris['notifikasi_wa'],
        ];
    } catch (PDOException $e) {
        error_log('Gagal membaca user_settings: ' . $e->getMessage());
        return PREFS_DEFAULT_USER;
    }
}

/* ---------------------------------------------------------------
   GET -> preferensi saat ini
   --------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    respond(true, 'OK', [
        'prefs'           => $tabelAda ? bacaPrefsUser($pdo, $idUser) : PREFS_DEFAULT_USER,
        'tersimpan_server' => $tabelAda,
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Metode tidak diizinkan.', [], 405);
}

// Payload: JSON atau FormData
$data = $_POST;
if (!$data) {
    $json = json_decode(file_get_contents('php://input'), true);
    if (is_array($json)) {
        $data = $json;
    }
}

$aksi = (string) ($data['aksi'] ?? '');
if ($aksi !== 'simpan') {
    respond(false, 'Aksi tidak dikenal.', [], 422);
}

// Terapkan nilai bawaan dulu, lalu timpa dengan yang dikirim (divalidasi per field)
$prefs = PREFS_DEFAULT_USER;
foreach (array_keys(PREFS_DEFAULT_USER) as $kunci) {
    if (array_key_exists($kunci, $data)) {
        $nilai = filter_var($data[$kunci], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($nilai === null) {
            respond(false, 'Nilai preferensi tidak valid: ' . $kunci, [], 422);
        }
        $prefs[$kunci] = $nilai;
    }
}

if (!$tabelAda) {
    // Migrasi belum dijalankan: beri tahu, halaman akan menyimpan di perangkat
    respond(false, 'Penyimpanan preferensi di server belum siap. Jalankan migrasi 006_user_settings.sql.', [
        'prefs'            => $prefs,
        'tersimpan_server' => false,
    ], 501);
}

// Simpan (baris user_settings dibuat bila belum ada)
// Catatan: nilai boolean harus di-bind eksplisit sebagai PDO::PARAM_BOOL,
// kalau tidak PDO mengirimnya sebagai string (''/ '1') dan Postgres menolaknya.
//
// Sintaks upsert berbeda per driver:
//   PostgreSQL -> ON CONFLICT ... DO UPDATE
//   MySQL      -> ON DUPLICATE KEY UPDATE
if (db_config()['driver'] === 'mysql') {
    $st = $pdo->prepare(
        "INSERT INTO user_settings (user_id, notifikasi_pesanan, notifikasi_email, notifikasi_wa, updated_at)
         VALUES (?, ?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE
            notifikasi_pesanan = VALUES(notifikasi_pesanan),
            notifikasi_email   = VALUES(notifikasi_email),
            notifikasi_wa      = VALUES(notifikasi_wa),
            updated_at         = NOW()"
    );
} else {
    $st = $pdo->prepare(
        "INSERT INTO user_settings (user_id, notifikasi_pesanan, notifikasi_email, notifikasi_wa, updated_at)
         VALUES (?, ?, ?, ?, NOW())
         ON CONFLICT (user_id) DO UPDATE
            SET notifikasi_pesanan = EXCLUDED.notifikasi_pesanan,
                notifikasi_email   = EXCLUDED.notifikasi_email,
                notifikasi_wa      = EXCLUDED.notifikasi_wa,
                updated_at         = NOW()"
    );
}
$st->bindValue(1, $idUser, PDO::PARAM_INT);
$st->bindValue(2, $prefs['notifikasi_pesanan'], PDO::PARAM_BOOL);
$st->bindValue(3, $prefs['notifikasi_email'], PDO::PARAM_BOOL);
$st->bindValue(4, $prefs['notifikasi_wa'], PDO::PARAM_BOOL);
$st->execute();

respond(true, 'Preferensi notifikasi tersimpan.', [
    'prefs'            => $prefs,
    'tersimpan_server' => true,
]);
