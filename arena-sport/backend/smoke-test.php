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
// 5. Ringkasan
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
