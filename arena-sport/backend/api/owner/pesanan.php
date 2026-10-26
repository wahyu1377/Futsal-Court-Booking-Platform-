<?php
/**
 * GET backend/api/owner/pesanan.php?tab=aktif|riwayat&lapangan_id=<opsional>
 *
 * Pesanan masuk untuk lapangan milik owner yang sedang login (hanya baca).
 *  - tab "aktif"   : menunggu pembayaran + terkonfirmasi yang belum lewat jam mainnya
 *  - tab "riwayat" : selesai + dibatalkan  (pesanan kedaluwarsa tidak ditampilkan, hanya noise)
 *
 * Owner hanya melihat pesanan dari lapangan dengan owner_id miliknya.
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../core/helper.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/booking.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(false, 'Metode tidak diizinkan.', [], 405);
}
if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'owner') {
    respond(false, 'Akses ditolak. Hanya owner yang bisa melihat pesanan masuk.', [], 403);
}

$pdo     = db();
$idOwner = (int) $_SESSION['user_id'];
bersihkanKedaluwarsa($pdo);

$tab        = ($_GET['tab'] ?? '') === 'riwayat' ? 'riwayat' : 'aktif';
$lapanganId = filter_var($_GET['lapangan_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;

// ---------------------------------------------------------------- daftar pesanan
$params    = [$idOwner];
$filterLap = '';
if ($lapanganId > 0) {
    $filterLap = ' AND b.lapangan_id = ?';
    $params[]  = $lapanganId;
}
$statusTab = $tab === 'aktif' ? "('menunggu', 'dibayar')" : "('selesai', 'dibatalkan')";
$urutan    = $tab === 'aktif' ? 'x.tanggal ASC, x.jam_mulai ASC' : 'x.tanggal DESC, x.jam_mulai DESC';

$st = $pdo->prepare(
    "SELECT * FROM (
        SELECT b.id, b.kode, b.status, b.total, b.metode, b.rincian, b.tanggal, b.created_at,
               l.id AS lapangan_id, l.nama AS lapangan_nama,
               u.full_name AS pemesan, u.phone, u.email,
               MIN(bs.jam_mulai)   AS jam_mulai,
               MAX(bs.jam_selesai) AS jam_selesai,
               CASE WHEN b.status = 'menunggu' AND b.expires_at IS NOT NULL
                    THEN GREATEST(0, EXTRACT(EPOCH FROM (b.expires_at - now()))::int) END AS sisa_detik,
               CASE WHEN b.status = 'dibayar'
                     AND (b.tanggal + MAX(bs.jam_selesai)) < (now() AT TIME ZONE 'Asia/Jakarta')
                    THEN 'selesai' ELSE b.status END AS status_tampil
        FROM booking b
        JOIN lapangan l ON l.id = b.lapangan_id AND l.owner_id = ?
        JOIN users u ON u.id = b.user_id
        LEFT JOIN booking_slot bs ON bs.booking_id = b.id
        WHERE TRUE $filterLap
        GROUP BY b.id, l.id, u.id
     ) x
     WHERE x.status_tampil IN $statusTab
     ORDER BY $urutan
     LIMIT 100"
);
$st->execute($params);

$data = [];
foreach ($st->fetchAll() as $r) {
    $r       = olahBooking($r);                       // jam_label, tanggal_label, status_info, rincian (array)
    $rincian = $r['rincian'];
    $layanan = (int) ($rincian['layanan'] ?? 0);      // biaya layanan bukan pendapatan owner
    $hp      = (string) ($r['phone'] ?? '');

    $data[] = [
        'kode'           => $r['kode'],
        'lapangan_nama'  => $r['lapangan_nama'],
        'tanggal_label'  => $r['tanggal_label'],
        'jam_label'      => $r['jam_label'],
        'durasi'         => formatDurasi((int) ($rincian['durasi_menit'] ?? 0)),
        'pemesan'        => $r['pemesan'],
        'telepon'        => $hp !== '' ? $hp : null,
        'wa'             => $hp !== '' ? preg_replace('/^0/', '62', $hp) : null,   // untuk tautan WhatsApp
        'email'          => $r['email'] ?: null,
        'status'         => $r['status_tampil'],
        'status_teks'    => $r['status_info']['teks'],
        'tone'           => $r['status_info']['tone'],
        'metode'         => $r['metode'],
        'total'          => (int) $r['total'],
        'pendapatan'     => max(0, (int) $r['total'] - $layanan),
        'bayar_sekarang' => (int) ($rincian['bayar_sekarang'] ?? $r['total']),
        'sisa_di_tempat' => (int) ($rincian['sisa_di_tempat'] ?? 0),
        'opsi'           => array_values(array_map(fn($o) => (string) ($o['label'] ?? ''), $rincian['opsi'] ?? [])),
        'sisa_menit'     => $r['sisa_detik'] !== null ? (int) ceil($r['sisa_detik'] / 60) : null,
    ];
}

// ---------------------------------------------------------------- ringkasan (semua lapangan owner)
$sum = $pdo->prepare(
    "SELECT
        COUNT(*) FILTER (WHERE b.status = 'dibayar' AND b.tanggal = hari.d)                        AS hari_ini,
        COUNT(*) FILTER (WHERE b.status = 'menunggu' AND b.expires_at > now())                     AS menunggu,
        COALESCE(SUM(b.total - COALESCE((b.rincian->>'layanan')::int, 0))
                 FILTER (WHERE b.status = 'dibayar'
                           AND date_trunc('month', b.tanggal) = date_trunc('month', hari.d)), 0)   AS pendapatan_bulan
     FROM booking b
     JOIN lapangan l ON l.id = b.lapangan_id AND l.owner_id = ?
     CROSS JOIN (SELECT (now() AT TIME ZONE 'Asia/Jakarta')::date AS d) hari"
);
$sum->execute([$idOwner]);
$s = $sum->fetch() ?: [];

// ---------------------------------------------------------------- pilihan filter lapangan
$lap = $pdo->prepare('SELECT id, nama FROM lapangan WHERE owner_id = ? ORDER BY nama');
$lap->execute([$idOwner]);

respond(true, 'OK', [
    'tab'       => $tab,
    'data'      => $data,
    'ringkasan' => [
        'hari_ini'         => (int) ($s['hari_ini'] ?? 0),
        'menunggu'         => (int) ($s['menunggu'] ?? 0),
        'pendapatan_bulan' => (int) ($s['pendapatan_bulan'] ?? 0),
    ],
    'lapangan'  => $lap->fetchAll(),
]);