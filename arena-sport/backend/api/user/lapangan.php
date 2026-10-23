<?php
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../core/response.php';

require __DIR__ . '/../../core/auth.php';   // untuk tahu siapa yang login

// id pengguna yang login (0 = tamu atau owner). Sudah di-cast ke int, aman dipakai langsung di SQL
$uid = (isLoggedIn() && ($_SESSION['role'] ?? '') === 'user') ? (int)$_SESSION['user_id'] : 0;
$hanyaFavorit = ($_GET['favorit'] ?? '') === '1';

$jenis     = strtolower(trim($_GET['jenis'] ?? ''));
$q         = trim($_GET['q'] ?? '');
$minHarga  = ($_GET['min_harga'] ?? '') !== '' ? max(0, (int)$_GET['min_harga']) : null;
$maxHarga  = ($_GET['max_harga'] ?? '') !== '' ? max(0, (int)$_GET['max_harga']) : null;
$ratingMin = max(0, min(5, (float)($_GET['rating_min'] ?? 0)));
$tersedia  = ($_GET['tersedia'] ?? '') === '1';
$fasilitas = array_values(array_filter(array_map('trim', explode(',', $_GET['fasilitas'] ?? ''))));

// Daftar urutan yang diizinkan (jangan pernah memasukkan input langsung ke ORDER BY)
$urutan = [
    'rating'   => 'rating DESC, jumlah_ulasan DESC',
    'termurah' => 'harga_per_jam ASC',
    'termahal' => 'harga_per_jam DESC',
    'ulasan'   => 'jumlah_ulasan DESC',
];
$orderBy = $urutan[$_GET['urut'] ?? 'rating'] ?? $urutan['rating'];

// Mode halaman Favorit: terakhir disimpan tampil paling atas
if ($hanyaFavorit && !isset($_GET['urut'])) {
    $orderBy = "(SELECT f.created_at FROM favorit f WHERE f.lapangan_id = lapangan.id AND f.user_id = $uid) DESC NULLS LAST";
}

$where  = ['aktif = TRUE'];
$params = [];

if ($hanyaFavorit) {
    $where[] = $uid > 0
        ? "EXISTS (SELECT 1 FROM favorit f WHERE f.lapangan_id = lapangan.id AND f.user_id = $uid)"
        : 'FALSE';   // tamu atau owner: tidak ada favorit
}

if ($jenis !== '') { $where[] = 'jenis = ?'; $params[] = $jenis; }

if ($q !== '') {
    $where[] = '(nama ILIKE ? OR jenis ILIKE ? OR kecamatan ILIKE ? OR kota ILIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}
if ($minHarga !== null) { $where[] = 'harga_per_jam >= ?'; $params[] = $minHarga; }
if ($maxHarga !== null) { $where[] = 'harga_per_jam <= ?'; $params[] = $maxHarga; }
if ($ratingMin > 0)     { $where[] = 'rating >= ?';        $params[] = $ratingMin; }
if ($tersedia)          { $where[] = 'tersedia_hari_ini = TRUE'; }

// Semua fasilitas yang dipilih harus dimiliki lapangan
foreach ($fasilitas as $fas) {
    $where[]  = "CONCAT(',', REPLACE(fasilitas, ', ', ','), ',') ILIKE ?";
    $params[] = '%,' . addcslashes($fas, '%_\\') . ',%';
}

$pdo = db();
$st = $pdo->prepare(
    "SELECT id, nama, jenis, kecamatan, kota, harga_per_jam, rating, jumlah_ulasan,
            foto, fasilitas, keterangan, tersedia_hari_ini,
            EXISTS (SELECT 1 FROM favorit f WHERE f.lapangan_id = lapangan.id AND f.user_id = $uid) AS favorit
     FROM lapangan WHERE " . implode(' AND ', $where) . "
     ORDER BY $orderBy LIMIT 30"
);
$st->execute($params);

// Jumlah per kategori untuk chip (tidak terpengaruh filter)
$counts = $pdo->query("SELECT jenis, COUNT(*) AS c FROM lapangan WHERE aktif = TRUE GROUP BY jenis ORDER BY c DESC")
              ->fetchAll(PDO::FETCH_KEY_PAIR);

// Pilihan untuk panel filter: daftar fasilitas unik + rentang tarif
$opsi = [];
$rows = $pdo->query("SELECT fasilitas FROM lapangan WHERE aktif = TRUE AND fasilitas IS NOT NULL AND fasilitas <> ''")
            ->fetchAll(PDO::FETCH_COLUMN);
foreach ($rows as $row) {
    foreach (explode(',', $row) as $f) {
        $f = trim($f);
        if ($f !== '') $opsi[$f] = true;
    }
}
$opsi = array_keys($opsi);
sort($opsi);
$range = $pdo->query("SELECT MIN(harga_per_jam) AS mn, MAX(harga_per_jam) AS mx FROM lapangan WHERE aktif = TRUE")->fetch();

respond(true, 'OK', [
    'data'    => $st->fetchAll(),
    'counts'  => $counts,
    'filters' => ['fasilitas' => $opsi, 'harga_min' => (int)$range['mn'], 'harga_max' => (int)$range['mx']],
]);