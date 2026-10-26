<?php
/**
 * Halaman Pesanan - Arena Sport
 * Path: frontend/pages/user/tiket.php
 *
 *   tiket.php               -> daftar pesanan (tab Aktif / Riwayat)
 *   tiket.php?kode=BKG-XXXX -> detail satu tiket
 */
require __DIR__ . '/../../../backend/core/auth.php';
require_once __DIR__ . '/../../../backend/core/booking.php';

requireLogin('user');
$user = currentUser();
$pdo  = db();
bersihkanKedaluwarsa($pdo);

$pageTitle = 'Pesanan';
$activeNav = 'pesanan';

$kode   = is_string($_GET['kode'] ?? null) ? strtoupper(trim($_GET['kode'])) : '';
$detail = null;
$slotDetail = [];

if ($kode !== '') {
    $detail = ambilBooking($pdo, $kode, (int) $user['id']);

    // Cadangan bila notifikasi DOKU belum sampai (mis. dites di localhost): tanyakan statusnya ke DOKU
    if ($detail && $detail['status_tampil'] === 'menunggu' && !empty($detail['payment_url'])) {
        require_once __DIR__ . '/../../../backend/core/doku.php';
        if (sinkronStatusDoku($pdo, $detail)) {
            $detail = ambilBooking($pdo, $kode, (int) $user['id']);
        }
    }

    if (!$detail) {
        http_response_code(404);
    } else {
        $slotDetail = slotBooking($pdo, (int) $detail['id']);
    }
} else {
    $semua    = daftarBooking($pdo, (int) $user['id']);
    $tab      = ($_GET['tab'] ?? '') === 'riwayat' ? 'riwayat' : 'aktif';
    $aktif    = array_values(array_filter($semua, fn($b) => in_array($b['status_tampil'], ['menunggu', 'dibayar'], true)));
    $riwayat  = array_values(array_filter($semua, fn($b) => !in_array($b['status_tampil'], ['menunggu', 'dibayar'], true)));
    // Yang akan datang paling dekat tampil paling atas
    usort($aktif, fn($a, $b) => [$a['tanggal'], $a['jam_mulai']] <=> [$b['tanggal'], $b['jam_mulai']]);
    $tampil   = $tab === 'riwayat' ? $riwayat : $aktif;
}
?>
<?php include '../../components/header.php'; ?>
<style>
    .tk-wrap{max-width:640px;margin:0 auto}
    .tk-tabs{display:flex;gap:.5rem;margin-bottom:1rem}
    .tk-tab{flex:1;text-align:center;padding:.65rem;border-radius:14px;background:#fff;color:#5f6b7a;font-weight:700;text-decoration:none}
    .tk-tab.on{background:#006a4a;color:#fff}
    .tk-tab small{opacity:.8;font-weight:600}
    .tk-card{display:block;background:#fff;border-radius:20px;padding:1rem 1.1rem;margin-bottom:.8rem;color:#0d1b2e;
        text-decoration:none;box-shadow:0 6px 18px rgba(13,27,46,.07)}
    .tk-card:hover{color:#0d1b2e;box-shadow:0 8px 22px rgba(13,27,46,.12)}
    .tk-badge{display:inline-block;border-radius:999px;padding:.2rem .7rem;font-size:.75rem;font-weight:800;white-space:nowrap}
    .tone-hijau{background:#d8f5e6;color:#005239}
    .tone-kuning{background:#fff1cc;color:#8a5a00}
    .tone-merah{background:#ffd9d6;color:#b3141b}
    .tone-abu{background:#eceff6;color:#5f6b7a}
    .tk-meta{color:#5f6b7a;font-size:.9rem}
    .tk-ticket{background:#fff;border-radius:24px;overflow:hidden;box-shadow:0 10px 30px rgba(13,27,46,.1)}
    .tk-head{background:linear-gradient(135deg,#0a2a2a,#0b3d33);color:#fff;padding:1.4rem}
    .tk-kode{font-size:1.6rem;font-weight:800;letter-spacing:.08em}
    .tk-body{padding:1.2rem 1.4rem}
    .tk-row{display:flex;justify-content:space-between;gap:1rem;padding:.35rem 0;font-size:.92rem}
    .tk-row span:first-child{color:#5f6b7a}
    .tk-row span:last-child{font-weight:700;text-align:right}
    .tk-sep{border-top:2px dashed #dfe5f3;margin:.9rem 0}
    .tk-slot{display:inline-block;background:#eef2ff;border-radius:10px;padding:.2rem .6rem;font-size:.82rem;font-weight:700;margin:0 .3rem .3rem 0}
    .tk-empty{text-align:center;color:#5f6b7a;padding:3rem 1rem}
    #tkPesan{display:none}
</style>
<?php include '../../components/navbar.php'; ?>

<main class="container py-3 tk-wrap">

<?php if ($kode !== ''): ?>
    <!-- ================= DETAIL TIKET ================= -->
    <a href="tiket.php" class="link-green d-inline-block mb-3"><i class="bi bi-arrow-left"></i> Semua Pesanan</a>

    <?php if (!$detail): ?>
        <div class="tk-empty">
            <i class="bi bi-ticket-perforated fs-1 d-block mb-2"></i>
            <strong class="d-block mb-1">Pesanan tidak ditemukan</strong>
            Kode booking tidak terdaftar di akun Anda.
        </div>
    <?php else:
        $r  = $detail['rincian'];
        $si = $detail['status_info'];
        $st = $detail['status_tampil'];
    ?>
        <article class="tk-ticket">
            <div class="tk-head">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <small style="opacity:.8">KODE BOOKING</small>
                    <span class="tk-badge tone-<?= e($si['tone']) ?>"><?= e($si['teks']) ?></span>
                </div>
                <div class="tk-kode"><?= e($detail['kode']) ?></div>
                <div style="opacity:.85"><?= e($detail['lapangan_nama']) ?></div>
            </div>

            <div class="tk-body">
                <div class="tk-row"><span>Tanggal</span><span><?= e($detail['tanggal_label']) ?></span></div>
                <div class="tk-row"><span>Jam</span><span><?= e($detail['jam_label']) ?></span></div>
                <div class="tk-row"><span>Lokasi</span><span><?= e($detail['kecamatan']) ?>, <?= e($detail['kota']) ?></span></div>
                <div class="pt-1">
                    <?php foreach ($slotDetail as $s): ?><span class="tk-slot"><?= e($s) ?></span><?php endforeach; ?>
                </div>

                <div class="tk-sep"></div>

                <div class="tk-row"><span>Sewa Lapangan (<?= e(formatDurasi((int) ($r['durasi_menit'] ?? 0))) ?>)</span><span><?= e(rupiahTeks((int) ($r['sewa'] ?? 0))) ?></span></div>
                <?php foreach (($r['opsi'] ?? []) as $o): ?>
                <div class="tk-row"><span><?= e($o['label']) ?></span><span><?= (int) $o['harga'] > 0 ? e(rupiahTeks((int) $o['harga'])) : 'Gratis' ?></span></div>
                <?php endforeach; ?>
                <div class="tk-row"><span>Biaya Layanan</span><span><?= e(rupiahTeks((int) ($r['layanan'] ?? 0))) ?></span></div>
                <div class="tk-row"><span><strong>Total</strong></span><span style="color:#006a4a"><?= e(rupiahTeks((int) $detail['total'])) ?></span></div>
                <?php if (($detail['metode'] ?? '') === 'onsite' && in_array($st, ['dibayar', 'selesai', 'menunggu'], true)): ?>
                <div class="tk-row"><span>Sisa dibayar di kasir</span><span><?= e(rupiahTeks((int) ($r['sisa_di_tempat'] ?? 0))) ?></span></div>
                <?php endif; ?>
                <div class="tk-row"><span>Metode</span><span><?= e(METODE_BAYAR[$detail['metode'] ?? ''] ?? '-') ?></span></div>

                <div class="tk-sep"></div>
                <p id="tkPesan" class="small text-danger fw-semibold" role="alert"></p>

                <?php if ($st === 'menunggu'): ?>
                    <p class="small text-secondary">Selesaikan pembayaran sebelum batas waktu habis, kalau tidak jadwal dilepas.</p>
                    <a href="bayar.php?kode=<?= e($detail['kode']) ?>" class="btn btn-main w-100 mb-2">Lanjut Bayar</a>
                    <button type="button" id="tkBatal" class="btn btn-reset w-100">Batalkan Pesanan</button>
                <?php elseif ($st === 'dibayar'): ?>
                    <p class="small text-secondary mb-0"><i class="bi bi-check-circle-fill text-success"></i>
                        Tunjukkan kode booking ini di kasir lapangan saat datang.
                        Untuk pembatalan atau perubahan jadwal, hubungi pengelola lapangan.</p>
                <?php else: ?>
                    <a href="detail_jadwal.php?id=<?= (int) $detail['lapangan_id'] ?>" class="btn btn-main w-100">Pesan Lagi</a>
                <?php endif; ?>
            </div>
        </article>
    <?php endif; ?>

<?php else: ?>
    <!-- ================= DAFTAR PESANAN ================= -->
    <div class="section-head mb-3"><h2>Pesanan Saya</h2></div>

    <div class="tk-tabs" role="tablist">
        <a href="tiket.php" class="tk-tab <?= $tab === 'aktif' ? 'on' : '' ?>" role="tab" aria-selected="<?= $tab === 'aktif' ? 'true' : 'false' ?>">Aktif <small>(<?= count($aktif) ?>)</small></a>
        <a href="tiket.php?tab=riwayat" class="tk-tab <?= $tab === 'riwayat' ? 'on' : '' ?>" role="tab" aria-selected="<?= $tab === 'riwayat' ? 'true' : 'false' ?>">Riwayat <small>(<?= count($riwayat) ?>)</small></a>
    </div>

    <?php if (!$tampil): ?>
        <div class="tk-empty">
            <i class="bi bi-ticket-perforated fs-1 d-block mb-2"></i>
            <strong class="d-block mb-1"><?= $tab === 'aktif' ? 'Belum ada pesanan aktif' : 'Belum ada riwayat pesanan' ?></strong>
            Pesanan Anda akan muncul di sini.
            <div class="mt-3"><a href="beranda.php" class="btn btn-main">Cari Lapangan</a></div>
        </div>
    <?php endif; ?>

    <?php foreach ($tampil as $b): $si = $b['status_info']; ?>
        <a href="tiket.php?kode=<?= e($b['kode']) ?>" class="tk-card">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                <strong><?= e($b['lapangan_nama']) ?></strong>
                <span class="tk-badge tone-<?= e($si['tone']) ?>"><?= e($si['teks']) ?></span>
            </div>
            <div class="tk-meta"><i class="bi bi-calendar-event me-1"></i><?= e($b['tanggal_label']) ?> &bull; <?= e($b['jam_label']) ?></div>
            <div class="d-flex justify-content-between align-items-end mt-2">
                <span class="tk-meta"><?= e($b['kode']) ?>
                    <?php if ($b['status_tampil'] === 'menunggu' && $b['sisa_detik'] !== null): ?>
                        &bull; sisa <?= (int) ceil($b['sisa_detik'] / 60) ?> menit
                    <?php endif; ?>
                </span>
                <strong style="color:#006a4a"><?= e(rupiahTeks((int) $b['total'])) ?></strong>
            </div>
        </a>
    <?php endforeach; ?>
<?php endif; ?>

</main>

<?php if ($detail && $detail['status_tampil'] === 'menunggu'): ?>
<script>
(function () {
    var btn = document.getElementById('tkBatal');
    if (!btn) return;
    btn.addEventListener('click', function () {
        if (!confirm('Batalkan pesanan ini? Jadwal akan dilepas.')) return;
        var pesan = document.getElementById('tkPesan');
        pesan.style.display = 'none';
        btn.disabled = true;
        fetch('../../../backend/api/user/booking.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'same-origin',
            body: JSON.stringify({ aksi: 'batal', kode: <?= json_encode($detail['kode']) ?> })
        })
        .then(function (res) { return res.json().catch(function () { return null; }); })
        .then(function (r) {
            if (!r) throw new Error('Server tidak merespons dengan benar.');
            if (!r.success) throw new Error(r.message);
            location.reload();
        })
        .catch(function (err) {
            pesan.textContent = err.message || 'Terjadi kesalahan. Coba lagi.';
            pesan.style.display = 'block';
            btn.disabled = false;
        });
    });
})();
</script>
<?php endif; ?>

<?php include '../../components/footer.php'; ?>