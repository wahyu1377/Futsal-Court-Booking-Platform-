<?php
/**
 * Halaman Pembayaran (langkah 3) - Arena Sport
 * Path: frontend/pages/user/bayar.php
 *
 * Dipakai untuk melanjutkan pembayaran lewat DOKU (mis. setelah kembali dari halaman DOKU
 * atau bila halaman DOKU belum sempat dibuat). Nama sengaja bukan "pembayaran.php":
 * file itu sudah dipakai menu "Metode Pembayaran Tersimpan" di halaman Akun.
 */
require_once dirname(__DIR__, 3) . '/backend/core/auth.php';
require_once dirname(__DIR__, 3) . '/backend/core/doku.php';
require_once dirname(__DIR__, 2) . '/components/helper_profil.php';   // e()

requireLogin('user');
$user = currentUser();
$pdo  = db();
bersihkanKedaluwarsa($pdo);

$kode = is_string($_GET['kode'] ?? null) ? strtoupper(trim($_GET['kode'])) : '';
$b    = $kode !== '' ? ambilBooking($pdo, $kode, (int) $user['id']) : null;

if (!$b) {
    header('Location: tiket.php');
    exit;
}

// Mungkin sudah dibayar tetapi notifikasi belum sampai: tanyakan ke DOKU dulu
if ($b['status_tampil'] === 'menunggu' && sinkronStatusDoku($pdo, $b)) {
    $b = ambilBooking($pdo, $kode, (int) $user['id']);
}
// Sudah dibayar / dibatalkan / kedaluwarsa: tidak ada lagi yang perlu dibayar
if ($b['status_tampil'] !== 'menunggu') {
    header('Location: tiket.php?kode=' . rawurlencode($kode));
    exit;
}

$r          = $b['rincian'];
$metode     = (string) ($b['metode'] ?? '');
$bayarKini  = (int) ($r['bayar_sekarang'] ?? $b['total']);
$sisaTempat = (int) ($r['sisa_di_tempat'] ?? 0);
$sisaDetik  = (int) $b['sisa_detik'];

try {
    $sandbox = (dokuConfig()['env'] ?? 'sandbox') !== 'production';
} catch (Throwable $e) {
    $sandbox = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php /* Microsoft Clarity (pelacakan halaman) */ ?>
    <?php include dirname(__DIR__, 2) . '/components/clarity.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Pembayaran - Arena Sport</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
      theme: { extend: {
        colors: {
          'primary': '#006948', 'primary-container': '#00855d', 'on-primary': '#ffffff', 'primary-fixed': '#85f8c4',
          'surface': '#f8f9ff', 'surface-lowest': '#ffffff', 'surface-container-low': '#eff4ff',
          'surface-container': '#e5eeff', 'surface-container-high': '#dce9ff',
          'on-surface': '#0b1c30', 'on-surface-variant': '#3d4a42', 'secondary': '#565e74',
        },
        fontFamily: { sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'] }
      } }
    };
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; display: inline-block; vertical-align: middle; }
        ::-webkit-scrollbar { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .animate-spin { animation: spin 1s linear infinite; }
    </style>
</head>
<body class="bg-surface font-sans text-sm text-on-surface min-h-screen">

    <header class="fixed top-0 w-full z-50 bg-surface/80 backdrop-blur-xl shadow-sm">
        <div class="h-16 px-4 flex items-center gap-3">
            <a href="tiket.php" aria-label="Ke daftar pesanan" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-surface-container-high transition-colors">
                <span class="material-symbols-outlined text-2xl">arrow_back</span>
            </a>
            <img alt="Logo" class="h-7 w-auto object-contain" src="../../assets/img/logo.png">
            <h1 class="font-bold">Pembayaran</h1>
        </div>
    </header>

    <main class="pt-20 pb-10 px-4 max-w-lg mx-auto flex flex-col gap-4">

        <!-- HITUNG MUNDUR -->
        <div class="bg-primary text-on-primary rounded-xl p-5 text-center shadow-sm">
            <p class="text-xs opacity-80">Selesaikan pembayaran dalam</p>
            <p class="text-4xl font-extrabold tracking-tight my-1" id="hitungMundur">--:--</p>
            <p class="text-xs opacity-80">Setelah waktu habis, jadwal dilepas untuk pemain lain.</p>
        </div>

        <!-- RINGKASAN PESANAN -->
        <div class="bg-surface-lowest rounded-xl shadow-sm p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] text-secondary uppercase font-bold tracking-wider">Kode Booking</span>
                <span class="font-extrabold tracking-wider"><?= e($b['kode']) ?></span>
            </div>
            <h2 class="text-base font-extrabold"><?= e($b['lapangan_nama']) ?></h2>
            <div class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined text-primary text-base">calendar_today</span><?= e($b['tanggal_label']) ?>
            </div>
            <div class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined text-primary text-base">schedule</span><?= e($b['jam_label']) ?>
            </div>
        </div>

        <!-- RINCIAN BIAYA -->
        <div class="bg-surface-lowest rounded-xl shadow-sm p-4 flex flex-col gap-1.5 text-xs">
            <div class="flex justify-between text-secondary"><span>Sewa Lapangan (<?= e(formatDurasi((int) ($r['durasi_menit'] ?? 0))) ?>)</span><span class="text-on-surface font-semibold"><?= e(rupiahTeks((int) ($r['sewa'] ?? 0))) ?></span></div>
            <?php foreach (($r['opsi'] ?? []) as $o): ?>
            <div class="flex justify-between text-secondary"><span><?= e($o['label']) ?></span><span class="text-on-surface font-semibold"><?= (int) $o['harga'] > 0 ? e(rupiahTeks((int) $o['harga'])) : 'Gratis' ?></span></div>
            <?php endforeach; ?>
            <div class="flex justify-between text-secondary"><span>Biaya Layanan</span><span class="text-on-surface font-semibold"><?= e(rupiahTeks((int) ($r['layanan'] ?? 0))) ?></span></div>
            <div class="h-px bg-surface-container-high my-1"></div>
            <div class="flex justify-between text-secondary"><span>Total Tagihan</span><span class="text-on-surface font-semibold"><?= e(rupiahTeks((int) $b['total'])) ?></span></div>
            <?php if ($metode === 'onsite'): ?>
            <div class="flex justify-between text-secondary"><span>Sisa dibayar di kasir</span><span class="text-on-surface font-semibold"><?= e(rupiahTeks($sisaTempat)) ?></span></div>
            <?php endif; ?>
            <div class="flex justify-between items-baseline mt-1">
                <span class="text-[10px] text-secondary font-bold uppercase tracking-wider"><?= $metode === 'onsite' ? 'DP yang dibayar sekarang' : 'Bayar sekarang' ?></span>
                <span class="text-xl font-extrabold text-primary"><?= e(rupiahTeks($bayarKini)) ?></span>
            </div>
        </div>

        <!-- BAYAR LEWAT DOKU -->
        <div class="bg-surface-lowest rounded-xl shadow-sm p-4 flex flex-col gap-3">
            <h3 class="font-bold flex items-center gap-2"><span class="material-symbols-outlined text-primary">lock</span> Pembayaran Aman via DOKU</h3>
            <p class="text-xs text-secondary">Anda akan diarahkan ke halaman DOKU untuk memilih metode pembayaran (QRIS, Virtual Account, dan lainnya).</p>
            <?php if ($sandbox): ?>
            <p class="text-[11px] text-amber-900 bg-amber-50 border border-amber-200 rounded-lg p-2.5"><strong>Mode sandbox.</strong> Ini lingkungan uji DOKU, tidak ada uang sungguhan yang berpindah.</p>
            <?php endif; ?>

            <p id="pesan" class="hidden text-xs font-semibold rounded-xl p-3" role="alert"></p>

            <button type="button" id="btnDoku" class="w-full h-12 bg-primary hover:bg-primary-container text-on-primary rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-md active:scale-[0.98] transition-all">
                Bayar Sekarang <span class="material-symbols-outlined text-base">arrow_forward</span>
            </button>
            <button type="button" id="btnCek" class="w-full py-2.5 text-xs font-semibold text-primary hover:bg-surface-container-low rounded-xl transition-colors">Saya sudah membayar, cek status</button>
        </div>

        <?php if (PEMBAYARAN_SIMULASI): ?>
        <button type="button" id="btnSimulasi" class="w-full py-2.5 text-xs font-semibold text-amber-800 bg-amber-50 rounded-xl">Simulasikan pembayaran berhasil (pengujian)</button>
        <?php endif; ?>

        <button type="button" id="btnBatal" class="w-full py-2 text-xs font-semibold text-red-700 hover:bg-red-50 rounded-xl transition-colors">Batalkan Pesanan</button>
    </main>

    <script>
    (function () {
        'use strict';
        var API  = '../../../backend/api/user/booking.php';
        var KODE = <?= json_encode($b['kode']) ?>;
        var akhir = Date.now() + <?= $sisaDetik ?> * 1000;     // dihitung dari jam server, bukan jam perangkat
        var $ = function (id) { return document.getElementById(id); };
        var tombol = ['btnDoku', 'btnCek', 'btnBatal', 'btnSimulasi'];

        function tick() {
            var s = Math.max(0, Math.round((akhir - Date.now()) / 1000));
            $('hitungMundur').textContent = String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
            if (s <= 0) { clearInterval(timer); location.reload(); }   // server akan menandainya kedaluwarsa
        }
        var timer = setInterval(tick, 1000);
        tick();

        function tampil(teks, ok) {
            var p = $('pesan');
            p.textContent = teks;
            p.className = 'text-xs font-semibold rounded-xl p-3 ' + (ok ? 'text-green-800 bg-green-50' : 'text-red-700 bg-red-50');
        }
        function kunci(on) { tombol.forEach(function (id) { if ($(id)) $(id).disabled = on; }); }

        function kirim(aksi, btn, teksTunggu) {
            var asli = btn.innerHTML;
            $('pesan').className = 'hidden';
            kunci(true);
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-xl">progress_activity</span> ' + teksTunggu;

            fetch(API, {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'same-origin',
                body: JSON.stringify({ aksi: aksi, kode: KODE })
            })
            .then(function (res) { return res.text().then(function (t) { return { status: res.status, text: t }; }); })
            .then(function (x) {
                var r;
                try { r = JSON.parse(x.text); }
                catch (e) { console.error('Balasan bukan JSON (HTTP ' + x.status + '):', x.text); throw new Error('Server mengirim balasan tidak valid (HTTP ' + x.status + '). Lihat Console.'); }
                if (r.need_login) { location.href = '../auth/login.php'; return; }
                if (!r.success) { if (r.redirect) { location.href = r.redirect; return; } throw new Error(r.message); }
                if (r.dibayar === false) { tampil(r.message, false); btn.innerHTML = asli; kunci(false); return; }
                location.href = r.redirect;
            })
            .catch(function (err) {
                tampil(err.message || 'Terjadi kesalahan. Coba lagi.', false);
                btn.innerHTML = asli;
                kunci(false);
            });
        }

        $('btnDoku').addEventListener('click', function () { kirim('bayar_online', this, 'Membuka DOKU...'); });
        $('btnCek').addEventListener('click', function () { kirim('cek', this, 'Memeriksa...'); });
        if ($('btnSimulasi')) $('btnSimulasi').addEventListener('click', function () { kirim('bayar', this, 'Memproses...'); });
        $('btnBatal').addEventListener('click', function () {
            if (confirm('Batalkan pesanan ini? Jadwal akan dilepas.')) kirim('batal', this, 'Membatalkan...');
        });
    })();
    </script>
</body>
</html>