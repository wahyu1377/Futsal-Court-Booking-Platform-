<?php
/**
 * Smoke test koneksi & skema database.
 *
 * Dipakai setelah mengganti DB_DRIVER atau setelah mengubah skema,
 * supaya masalah skema ketahuan dalam hitungan detik (bukan saat user komplain).
 *
 * Cara pakai (dari folder root repository):
 *   php arena-sport/backend/smoke-test.php
 *
 * Skrip ini hanya boleh dijalankan lewat CLI (bukan dari browser).
 */
declare(strict_types=1);

// Cegah skrip dibuka lewat browser (tidak ada yang perlu dilihat pengguna)
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Skrip ini hanya bisa dijalankan lewat command line.');
}

require __DIR__ . '/config/database.php';

$cfg  = db_config();
$skem = $cfg['driver'] === 'pgsql' ? 'public' : $cfg['name'];

$hasil   = [];   // daftar hasil pemeriksaan
$jumlahGagal = 0;

/**
 * Catat hasil satu pemeriksaan.
 */
function cek(string $label, bool $ok, string $detail = '', string $level = 'fail'): void
{
    global $hasil, $jumlahGagal;

    if ($ok) {
        $level = 'ok';
    } elseif ($level === 'fail') {
        $jumlahGagal++;
    }

    $hasil[] = ['label' => $label, 'level' => $level, 'detail' => $detail];
}

/**
 * Jalankan query, kembalikan pesan error kalau gagal (null kalau sukses).
 */
function cobaQuery(PDO $pdo, string $sql, array $params = []): ?string
{
    try {
        $st = $pdo->prepare($sql);
        $st->execute($params);
        return null;
    } catch (PDOException $e) {
        return $e->getMessage();
    }
}

/**
 * Ambil daftar nama kolom suatu tabel (kosong kalau tabelnya tidak ada).
 */
function daftarKolom(PDO $pdo, string $skem, string $tabel): array
{
    try {
        $st = $pdo->prepare(
            "SELECT column_name FROM information_schema.columns
              WHERE table_schema = ? AND table_name = ?
              ORDER BY column_name"
        );
        $st->execute([$skem, $tabel]);
        return $st->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Cek apakah sebuah tabel ada di database aktif.
 */
function tabelAda(PDO $pdo, string $skem, string $tabel): bool
{
    try {
        $st = $pdo->prepare(
            "SELECT COUNT(*) FROM information_schema.tables
              WHERE table_schema = ? AND table_name = ?"
        );
        $st->execute([$skem, $tabel]);
        return ((int) $st->fetchColumn()) > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// ---------------------------------------------------------------------------
// 1. Koneksi
// ---------------------------------------------------------------------------
echo "Smoke test Arena Sport\n";
echo str_repeat('-', 60) . "\n";

$pdo = null;
try {
    $pdo = db();
    cek('Koneksi database', true, 'driver=' . $cfg['driver'] . ', host=' . $cfg['host'] . ', db=' . $cfg['name']);
} catch (Throwable $e) {
    cek('Koneksi database', false, $e->getMessage());
}

if (!$pdo) {
    echo "\nRingkasan: 1 pemeriksaan gagal (koneksi). Perbaiki .env lalu jalankan ulang.\n";
    exit(1);
}

// ---------------------------------------------------------------------------
// 2. Tabel wajib
// ---------------------------------------------------------------------------
$tabelWajib = ['users', 'owners', 'lapangan'];
foreach ($tabelWajib as $tabel) {
    $ada = tabelAda($pdo, $skem, $tabel);
    cek("Tabel `$tabel` ada", $ada, $ada ? '' : "tidak ditemukan di schema `$skem`");
}

// ---------------------------------------------------------------------------
// 3. Kolom wajib per tabel
// ---------------------------------------------------------------------------
$kolomWajib = [
    'users'     => ['id', 'full_name', 'email', 'phone', 'password_hash', 'failed_attempts', 'locked_until', 'created_at'],
    'owners'    => ['id', 'full_name', 'email', 'phone', 'password_hash', 'failed_attempts', 'locked_until', 'created_at'],
    'lapangan'  => ['id', 'owner_id', 'nama', 'jenis', 'kecamatan', 'kota', 'harga_per_jam', 'rating',
                    'jumlah_ulasan', 'foto', 'fasilitas', 'keterangan', 'tersedia_hari_ini', 'aktif',
                    'jam_buka', 'jam_tutup', 'durasi_sesi', 'created_at'],
];

foreach ($kolomWajib as $tabel => $butuh) {
    if (!tabelAda($pdo, $skem, $tabel)) continue;

    $adaKolom = daftarKolom($pdo, $skem, $tabel);
    $hilang   = array_values(array_diff($butuh, $adaKolom));
    cek(
        "Kolom wajib tabel `$tabel` lengkap",
        empty($hilang),
        empty($hilang) ? count($butuh) . ' kolom ada' : 'kurang: ' . implode(', ', $hilang)
    );
}

// ---------------------------------------------------------------------------
// 4. Query inti aplikasi (replay persis seperti di controller)
// ---------------------------------------------------------------------------
// 4a. Pencarian (LOWER + LIKE, pengganti ILIKE)
$err = cobaQuery(
    $pdo,
    "SELECT id, nama FROM lapangan
      WHERE LOWER(nama) LIKE LOWER(?) OR LOWER(kota) LIKE LOWER(?)
      ORDER BY rating DESC LIMIT 5",
    ['%futsal%', '%futsal%']
);
cek('Query pencarian (LOWER/LIKE)', $err === null, $err ?? 'ok');

$err = cobaQuery(
    $pdo,
    "SELECT LOWER(CONCAT(',', REPLACE(fasilitas, ', ', ','), ',')) AS f
       FROM lapangan WHERE fasilitas IS NOT NULL LIMIT 1"
);
cek('Query filter fasilitas (CONCAT/REPLACE)', $err === null, $err ?? 'ok');

// 4b. Query agregat untuk chip kategori
$err = cobaQuery($pdo, "SELECT jenis, COUNT(*) AS c FROM lapangan WHERE aktif = TRUE GROUP BY jenis ORDER BY c DESC");
cek('Query agregat kategori', $err === null, $err ?? 'ok');

// 4c. Update lockout (WHERE id = -1 → 0 baris, tapi sintaks SQL tetap divalidasi)
$err = cobaQuery(
    $pdo,
    "UPDATE users SET failed_attempts = 0, locked_until = ? WHERE id = ?",
    [date('Y-m-d H:i:s', time() + 900), -1]
);
cek('Query lockout akun (UPDATE ... locked_until)', $err === null, $err ?? 'ok');

// 4d. Query reset lockout
$err = cobaQuery($pdo, "UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?", [-1]);
cek('Query reset lockout (locked_until = NULL)', $err === null, $err ?? 'ok');

// 4e. Tipe kolom boolean (kode bind memakai PDO::PARAM_BOOL)
try {
    $st = $pdo->prepare(
        "SELECT column_name, data_type FROM information_schema.columns
          WHERE table_schema = ? AND table_name = 'lapangan'
            AND column_name IN ('tersedia_hari_ini', 'aktif')"
    );
    $st->execute([$skem]);
    $tipe = $st->fetchAll(PDO::FETCH_KEY_PAIR);
    $tipeAkhir = [];
    foreach ($tipe as $kol => $t) {
        $tipeAkhir[] = "$kol=$t";
    }
    $salah = array_filter($tipe, fn($t) => !in_array(strtolower((string) $t), ['boolean', 'tinyint'], true));
    cek(
        'Tipe kolom boolean di `lapangan`',
        empty($salah),
        empty($salah) ? implode(', ', $tipeAkhir) : 'tipe tak terduga: ' . implode(', ', $salah)
    );
} catch (Throwable $e) {
    cek('Tipe kolom boolean di `lapangan`', false, $e->getMessage());
}

// 4f. lastInsertId (dipakai menggantikan RETURNING id)
try {
    $pdo->beginTransaction();
    $st = $pdo->prepare(
        "INSERT INTO lapangan
            (owner_id, nama, jenis, kecamatan, kota, harga_per_jam, rating, jumlah_ulasan,
             foto, fasilitas, keterangan, tersedia_hari_ini, aktif, jam_buka, jam_tutup, durasi_sesi)
         VALUES (?, ?, ?, ?, ?, ?, 0, 0, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $st->bindValue(1, null);
    $st->bindValue(2, 'Smoke Test Sementara');
    $st->bindValue(3, 'futsal');
    $st->bindValue(4, 'Uji Coba');
    $st->bindValue(5, 'Uji Coba');
    $st->bindValue(6, 1000);
    $st->bindValue(7, '');
    $st->bindValue(8, '');
    $st->bindValue(9, '');
    $st->bindValue(10, true, PDO::PARAM_BOOL);
    $st->bindValue(11, true, PDO::PARAM_BOOL);
    $st->bindValue(12, '08:00');
    $st->bindValue(13, '22:00');
    $st->bindValue(14, 60);
    $st->execute();
    $idUji = (int) $pdo->lastInsertId();
    $pdo->rollBack();

    cek(
        'Penyimpanan lapangan + lastInsertId()',
        $idUji > 0,
        $idUji > 0 ? "id sementara $idUji (rollback, data tidak tersimpan)" : 'lastInsertId() mengembalikan 0'
    );
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    cek(
        'Penyimpanan lapangan + lastInsertId()',
        false,
        $e->getMessage() . ' (kemungkinan kolom NOT NULL/FK, bukan masalah driver)',
        'warn'
    );
}

// ---------------------------------------------------------------------------
// 5. Tabel password_reset (fitur lupa kata sandi - migrasi 005)
// ---------------------------------------------------------------------------
// Tabel ini opsional: kalau belum dimigrasikan, fitur reset kata sandi
// tetap aman (API membalas pesan "belum aktif"), jadi levelnya 'warn', bukan fail.
$adaReset = tabelAda($pdo, $skem, 'password_reset');
cek(
    'Tabel `password_reset` ada',
    $adaReset,
    $adaReset ? '' : 'belum dimigrasikan - jalankan 005_password_reset.sql (fitur lupa kata sandi nonaktif)',
    'warn'
);

if ($adaReset) {
    $kolomReset = daftarKolom($pdo, $skem, 'password_reset');
    $butuhReset = ['id', 'token_hash', 'role', 'account_id', 'email_kirim', 'ip_address',
                   'expires_at', 'used_at', 'created_at'];
    $hilangReset = array_values(array_diff($butuhReset, $kolomReset));
    cek(
        'Kolom wajib tabel `password_reset` lengkap',
        empty($hilangReset),
        empty($hilangReset) ? count($butuhReset) . ' kolom ada' : 'kurang: ' . implode(', ', $hilangReset)
    );

    // Query inti fitur reset: simpan token (rollback) + cari token + tandai dipakai
    try {
        $pdo->beginTransaction();
        $st = $pdo->prepare(
            "INSERT INTO password_reset
                (token_hash, role, account_id, email_kirim, ip_address, expires_at, created_at)
             VALUES (?, 'user', -1, 'uji@coba.local', '127.0.0.1', ?, ?)"
        );
        $st->execute([
            hash('sha256', 'smoke-test-token'),
            date('Y-m-d H:i:s', time() + 3600),
            date('Y-m-d H:i:s'),
        ]);
        $err = cobaQuery($pdo, "SELECT id FROM password_reset WHERE token_hash = ?", [hash('sha256', 'smoke-test-token')]);
        $err = $err ?? cobaQuery($pdo, "UPDATE password_reset SET used_at = ? WHERE token_hash = ?",
            [date('Y-m-d H:i:s'), hash('sha256', 'smoke-test-token')]);
        $pdo->rollBack();

        cek(
            'Query token reset (insert + cari + tandai dipakai)',
            $err === null,
            $err ?? 'ok (rollback, data uji tidak tersimpan)'
        );
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        cek('Query token reset (insert + cari + tandai dipakai)', false, $e->getMessage());
    }
}

// ---------------------------------------------------------------------------
// 6. Tabel user_settings (fitur setelan pengguna - migrasi 006)
// ---------------------------------------------------------------------------
$adaSetelanUser = tabelAda($pdo, $skem, 'user_settings');
cek(
    'Tabel `user_settings` ada',
    $adaSetelanUser,
    $adaSetelanUser ? '' : 'belum dimigrasikan - jalankan 006_user_settings.sql (preferensi notifikasi user disimpan di perangkat saja)',
    'warn'
);

if ($adaSetelanUser) {
    $kolomSetelan = daftarKolom($pdo, $skem, 'user_settings');
    $hilangSetelan = array_values(array_diff(
        ['user_id', 'notifikasi_pesanan', 'notifikasi_email', 'notifikasi_wa', 'updated_at'],
        $kolomSetelan
    ));
    cek(
        'Kolom wajib tabel `user_settings` lengkap',
        empty($hilangSetelan),
        empty($hilangSetelan) ? '5 kolom ada' : 'kurang: ' . implode(', ', $hilangSetelan)
    );
}

// ---------------------------------------------------------------------------
// 7. Konfigurasi Google OAuth ("Masuk dengan Google")
//
// Tombol Google hanya bisa muncul kalau ORIGIN aplikasi sudah terdaftar di
// "Authorized JavaScript origins" pada Google Cloud Console. Origin = scheme +
// host + port (tanpa path). Contoh: http://localhost:8000
// ---------------------------------------------------------------------------
require_once __DIR__ . '/config/google.php';

$clientIdGoogle = googleClientId();
cek(
    'Client ID Google terpasang',
    $clientIdGoogle !== '',
    $clientIdGoogle . (googleClientIdDariEnv() ? ' (dari .env)' : ' (nilai bawaan)')
);

$originGoogle = googleOriginHarusDaftar();
cek(
    'Origin aplikasi bisa ditentukan',
    $originGoogle !== '',
    $originGoogle !== '' ? $originGoogle : 'isi APP_URL di .env supaya origin-nya jelas'
);

// Cek langsung ke Google: kalau origin tidak terdaftar, endpoint tombol
// membalas 403 dengan halaman modul GSI ("credential_button_library"), dan
// modul di dalamnya memunculkan "The given origin is not allowed for the
// given client ID" di console browser. Kalau 200, origin sudah benar.
if ($originGoogle !== '' && $clientIdGoogle !== '') {
    $urlTombol = 'https://accounts.google.com/gsi/button?type=standard&theme=outline'
        . '&size=large&text=continue_with&logo_alignment=center&width=392'
        . '&is_fedcm_supported=true&client_id=' . urlencode($clientIdGoogle) . '&hl=id';

    $ch = curl_init($urlTombol);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HTTPHEADER     => [
            'Origin: ' . $originGoogle,
            'Referer: ' . $originGoogle . '/',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: id-ID,id;q=0.9,en-US;q=0.8',
            'Sec-Fetch-Dest: iframe',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: cross-site',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',
        ],
    ]);
    $body = curl_exec($ch);
    $kode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        cek(
            'Origin Google terdaftar (cek langsung ke Google)',
            false,
            'tidak bisa menghubungi Google: ' . $err,
            'warn'
        );
    } elseif ($kode === 200) {
        cek(
            'Origin Google terdaftar (cek langsung ke Google)',
            true,
            $originGoogle . ' -> HTTP 200 (OK)'
        );
    } elseif (strpos((string) $body, 'credential_button_library') !== false) {
        // Halaman modul GSI memang diterima Google, tapi 403 karena origin ditolak.
        // Ini bukan masalah skema database, jadi levelnya 'warn' (tetap terlihat,
        // tapi tidak menggagalkan smoke test).
        cek(
            'Origin Google terdaftar (cek langsung ke Google)',
            false,
            $originGoogle . ' -> HTTP 403 (origin ditolak). Daftarkan origin ini persis di '
                . 'Google Cloud Console -> Credentials -> OAuth 2.0 Client ID -> Authorized JavaScript origins',
            'warn'
        );
    } else {
        cek(
            'Origin Google terdaftar (cek langsung ke Google)',
            false,
            $originGoogle . ' -> HTTP ' . $kode . ' (respon tidak dikenali, cek manual di console browser)',
            'warn'
        );
    }
}

// ---------------------------------------------------------------------------
// 8. Ringkasan
// ---------------------------------------------------------------------------
echo "\n";
$ikon = ['ok' => '[ OK ]', 'warn' => '[WARN]', 'fail' => '[FAIL]'];
foreach ($hasil as $h) {
    echo str_pad($ikon[$h['level']], 8) . $h['label'];
    if ($h['detail'] !== '') echo ' -> ' . $h['detail'];
    echo "\n";
}

$totalWarn = count(array_filter($hasil, fn($h) => $h['level'] === 'warn'));
echo str_repeat('-', 60) . "\n";
echo sprintf(
    "Total: %d pemeriksaan | gagal: %d | peringatan: %d\n",
    count($hasil),
    $jumlahGagal,
    $totalWarn
);

exit($jumlahGagal > 0 ? 1 : 0);
