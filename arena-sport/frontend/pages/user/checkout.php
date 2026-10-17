<?php
/**
 * Halaman Checkout Booking - Arena Sport
 * Arena Sport Pulse Design System
 *
 * Path: frontend/pages/user/checkout.php
 */

// Konfigurasi halaman
$pageTitle = 'Checkout Booking';

// ============================================================
// AMBIL DATA DARI DETAIL JADWAL
// ============================================================
$lapanganId = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$slotsTerpilih = isset($_GET['slot']) ? $_GET['slot'] : [];
$tanggalPilih = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// ============================================================
// KONFIGURASI HARGA
// ============================================================
$HARGA_NORMAL = 150000;
$HARGA_PRIME_TIME = 180000;
$HARGA_ROMPI = 30000;
$HARGA_WASIT = 100000;
$BIAYA_LAYANAN = 2000;

// ============================================================
// HITUNG DURASI DAN TOTAL
// ============================================================
$jumlahSlot = count($slotsTerpilih);
$totalHarga = 0;
$jamMulai = '';
$jamSelesai = '';

foreach ($slotsTerpilih as $index => $slot) {
    preg_match('/^(\d{2})\.(\d{2})/', $slot, $matches);
    $jam = isset($matches[1]) ? (int)$matches[1] : 0;

    // Tentukan harga berdasarkan jam
    if ($jam >= 15 && $jam < 21) {
        $hargaSlot = $HARGA_PRIME_TIME;
    } else {
        $hargaSlot = $HARGA_NORMAL;
    }

    $totalHarga += $hargaSlot;

    if ($index === 0) $jamMulai = $slot;
    if ($index === count($slotsTerpilih) - 1) $jamSelesai = $slot;
}

// Format jam display
$jamDisplay = '';
if ($jamMulai && $jamSelesai) {
    $jamAwal = explode(' - ', $jamMulai)[0] . ' WIB';
    $jamAkhir = explode(' - ', $jamSelesai)[1] ?? '';
    $jamDisplay = $jamAwal . ' - ' . $jamAkhir . ' WIB';
}

// Format tanggal
$hariIndonesia = ['Sun'=>'Min','Mon'=>'Sen','Tue'=>'Sel','Wed'=>'Rab','Thu'=>'Kam','Fri'=>'Jum','Sat'=>'Sab'];
$bulanIndonesia = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
$tanggalObj = DateTime::createFromFormat('Y-m-d', $tanggalPilih);
$tanggalDisplay = date('d M Y');
if ($tanggalObj) {
    $hari = $hariIndonesia[$tanggalObj->format('D')] ?? '';
    $tanggalDisplay = $hari . ', ' . $tanggalObj->format('j') . ' ' . $bulanIndonesia[(int)$tanggalObj->format('n')] . ' ' . $tanggalObj->format('Y');
}

// Generate Booking ID
$bookingId = 'BKG-' . strtoupper(substr(md5(uniqid()), 0, 4));

// ============================================================
// AMBIL DATA LAPANGAN DARI DATABASE
// ============================================================
require_once dirname(__DIR__, 3) . '/backend/config/database.php';

try {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM lapangan WHERE id = ? AND aktif = 1");
    $stmt->execute([$lapanganId]);
    $lapangan = $stmt->fetch();

    if (!$lapangan) {
        $lapangan = [
            'nama' => 'Champion Futsal Arena',
            'jenis' => 'futsal'
        ];
    }
} catch (Exception $e) {
    $lapangan = [
        'nama' => 'Champion Futsal Arena',
        'jenis' => 'futsal'
    ];
}

// ============================================================
// OPSI TAMBAHAN
// ============================================================
$opsiTambahan = [
    ['id'=>'rompi','label'=>'Sewa Rompi Latihan','desc'=>'12 pcs Rompi Neon/Oranye','harga'=>$HARGA_ROMPI,'icon'=>'checkroom'],
    ['id'=>'wasit','label'=>'Jasa Wasit Pertandingan','desc'=>'2 Jam Berlisensi Askot','harga'=>$HARGA_WASIT,'icon'=>'sports'],
    ['id'=>'bola','label'=>'Bola Futsal Tambahan','desc'=>'2 buah bola match FIFA','harga'=>0,'icon'=>'sports_soccer'],
];

// ============================================================
// METODE PEMBAYARAN
// ============================================================
$metodeBayar = [
    ['value'=>'qris','icon'=>'qr_code_scanner','label'=>'QRIS / Instan','badge'=>'Otomatis','type'=>'success','desc'=>'BCA, Mandiri, GoPay, OVO'],
    ['value'=>'va','icon'=>'account_balance','label'=>'Virtual Account','badge'=>null,'type'=>'','desc'=>'BCA, Mandiri, BRI, BNI'],
    ['value'=>'onsite','icon'=>'storefront','label'=>'Bayar di Tempat','badge'=>'DP 50%','type'=>'warning','desc'=>'DP via transfer, lunasi di kasir'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?= htmlspecialchars($pageTitle) ?> - Arena Sport</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'primary': '#006948',
            'primary-dark': '#005840',
            'primary-container': '#00855d',
            'on-primary': '#ffffff',
            'on-primary-container': '#f5fff7',
            'primary-fixed': '#85f8c4',
            'surface': '#f8f9ff',
            'surface-lowest': '#ffffff',
            'surface-container-low': '#eff4ff',
            'surface-container': '#e5eeff',
            'surface-container-high': '#dce9ff',
            'on-surface': '#0b1c30',
            'on-surface-variant': '#3d4a42',
            'secondary': '#565e74',
          },
          fontFamily: {
            sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
          }
        }
      }
    };
    </script>

    <style>
        html, body {
            width: 100vw;
            margin: 0;
            padding: 0;
            overscroll-behavior: none;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            vertical-align: middle;
        }
        ::-webkit-scrollbar { display: none; }
        @keyframes pulse-dot { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .animate-pulse-dot { animation: pulse-dot 2s infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .animate-spin { animation: spin 1s linear infinite; }
    </style>
</head>
<body class="bg-surface font-sans text-sm text-on-surface flex flex-col min-h-screen">

    <!-- HEADER -->
    <header class="fixed top-0 w-full z-50 bg-surface/80 backdrop-blur-xl shadow-sm">
        <div class="h-16 px-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button id="btnBack" class="w-10 h-10 flex items-center justify-center rounded-full text-on-surface hover:bg-surface-container-high transition-colors">
                    <span class="material-symbols-outlined text-2xl">arrow_back</span>
                </button>
                <img alt="Logo" class="h-7 w-auto object-contain" src="../../assets/img/logo.png">
                <h1 class="font-bold text-on-surface">Checkout</h1>
            </div>
            <div class="flex items-center gap-1">
                <img src="../../assets/img/Foto profil.png" alt="Profil" class="w-9 h-9 rounded-full object-cover border-2 border-white shadow-sm" onerror="this.style.display='none'">
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="flex flex-col relative w-full pt-16 pb-36 bg-surface min-h-screen">
        <div class="flex flex-col w-full px-4 pt-4 pb-6 gap-4">

            <!-- STEPPER -->
            <div class="w-full bg-surface-lowest rounded-xl p-4 shadow-sm">
                <div class="flex items-center justify-between relative">
                    <div class="absolute left-8 right-8 top-4 h-[2px] bg-surface-container-high z-0"></div>
                    <div class="absolute left-8 w-1/2 top-4 h-[2px] bg-primary z-0"></div>

                    <div class="relative z-10 flex flex-col items-center gap-1">
                        <div class="w-7 h-7 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-sm">
                            <span class="material-symbols-outlined text-sm">check</span>
                        </div>
                        <span class="text-[10px] text-primary font-bold">1. Kontak</span>
                    </div>

                    <div class="relative z-10 flex flex-col items-center gap-1">
                        <div class="w-7 h-7 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-sm">
                            <span class="material-symbols-outlined text-xs animate-pulse-dot">fiber_manual_record</span>
                        </div>
                        <span class="text-[10px] text-on-surface font-bold">2. Detail</span>
                    </div>

                    <div class="relative z-10 flex flex-col items-center gap-1">
                        <div class="w-7 h-7 rounded-full bg-surface-container text-secondary flex items-center justify-center">
                            <span class="material-symbols-outlined text-xs">radio_button_unchecked</span>
                        </div>
                        <span class="text-[10px] text-secondary">3. Bayar</span>
                    </div>
                </div>
            </div>

            <!-- BOOKING SUMMARY CARD -->
            <div class="w-full bg-surface-lowest rounded-xl overflow-hidden shadow-sm">
                <div class="relative w-full h-36 bg-cover bg-center" style="background-image: url('../../assets/img/lapangan1.jpeg');">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                    <div class="absolute top-3 right-3">
                        <span class="bg-primary/90 backdrop-blur-sm text-white text-[10px] px-2.5 py-1 rounded-full uppercase tracking-wider font-bold flex items-center gap-1 shadow">
                            <span class="material-symbols-outlined text-xs">verified</span> #<?= $bookingId ?>
                        </span>
                    </div>
                    <div class="absolute bottom-3 left-4 right-4 text-white">
                        <span class="text-[10px] text-primary-fixed uppercase tracking-wider font-bold">Futsal Vinyl Pro A</span>
                        <h2 class="text-base font-bold truncate"><?= htmlspecialchars($lapangan['nama']) ?></h2>
                    </div>
                </div>

                <div class="p-4 flex flex-col gap-2 bg-surface-container-low">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">calendar_today</span>
                        <span class="text-sm font-semibold text-on-surface"><?= $tanggalDisplay ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">schedule</span>
                            <span class="text-sm text-on-surface-variant"><?= $jamDisplay ?: 'Pilih jadwal' ?></span>
                        </div>
                        <span class="text-[10px] text-on-surface-variant bg-surface-container px-2.5 py-0.5 rounded-full font-bold">
                            Durasi: <?= $jumlahSlot ?> Jam
                        </span>
                    </div>
                </div>
            </div>

            <!-- ACCORDION SECTIONS -->
            <div class="flex flex-col gap-3">

                <!-- Data Kontak (sembunyikan default) -->
                <div class="bg-surface-lowest rounded-xl shadow-sm overflow-hidden" id="acc-kontak">
                    <button class="w-full p-4 flex items-center justify-between text-left hover:bg-surface-container-low transition-colors" id="btn-kontak">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                                <span class="material-symbols-outlined text-lg">person</span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-on-surface">Data Kontak</span>
                                    <span class="bg-primary/10 text-primary text-[10px] px-2 py-0.5 rounded-full font-bold">Tervalidasi</span>
                                </div>
                                <p class="text-xs text-secondary truncate">Bambang Pamungkas - Garuda FC</p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-secondary text-xl transition-transform duration-200" id="icon-kontak">expand_more</span>
                    </button>
                    <div class="px-4 pb-4 hidden" id="content-kontak">
                        <div class="p-3 bg-surface-container-low rounded-lg space-y-1 text-sm text-on-surface">
                            <div class="flex justify-between"><span class="text-secondary">Nama</span><span class="font-semibold">Bambang Pamungkas</span></div>
                            <div class="flex justify-between"><span class="text-secondary">WhatsApp</span><span class="font-semibold">0812-3456-7890</span></div>
                            <div class="flex justify-between"><span class="text-secondary">Tim</span><span class="font-semibold">Garuda FC</span></div>
                        </div>
                    </div>
                </div>

                <!-- Opsi Tambahan -->
                <div class="bg-surface-lowest rounded-xl shadow-sm overflow-hidden" id="acc-tambahan">
                    <button class="w-full p-4 flex items-center justify-between text-left hover:bg-surface-container-low transition-colors" id="btn-tambahan">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                                <span class="material-symbols-outlined text-lg">sports_soccer</span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-on-surface">Opsi Tambahan</span>
                                    <span class="bg-surface-container text-on-surface-variant text-[10px] px-2 py-0.5 rounded-full font-bold" id="addon-badge">1 Dipilih</span>
                                </div>
                                <p class="text-xs text-secondary">Peralatan & kelengkapan match</p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-secondary text-xl rotate-180 transition-transform duration-200" id="icon-tambahan">expand_more</span>
                    </button>
                    <div class="px-4 pb-4 flex flex-col gap-2" id="content-tambahan">
                        <?php foreach ($opsiTambahan as $opsi): ?>
                        <label class="flex items-start gap-3 p-3 rounded-lg bg-surface-container-low hover:bg-surface-container cursor-pointer transition-colors">
                            <input type="checkbox" name="opsi[]" value="<?= $opsi['id'] ?>" class="mt-0.5 w-4 h-4 rounded text-primary focus:ring-0 accent-primary cursor-pointer"
                                <?= ($opsi['id'] === 'bola') ? 'checked' : '' ?>
                                id="check-<?= $opsi['id'] ?>">
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center gap-2">
                                    <span class="text-sm font-bold text-on-surface flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-primary text-base"><?= $opsi['icon'] ?></span>
                                        <?= $opsi['label'] ?>
                                    </span>
                                    <?php if ($opsi['harga'] > 0): ?>
                                    <span class="text-primary font-bold text-xs">+Rp <?= number_format($opsi['harga'], 0, ',', '.') ?></span>
                                    <?php else: ?>
                                    <span class="bg-primary-fixed text-on-primary-fixed text-[10px] px-2 py-0.5 rounded-full font-bold uppercase">Gratis</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-secondary mt-0.5"><?= $opsi['desc'] ?></p>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div class="bg-surface-lowest rounded-xl shadow-sm overflow-hidden" id="acc-bayar">
                    <button class="w-full p-4 flex items-center justify-between text-left hover:bg-surface-container-low transition-colors" id="btn-bayar">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                                <span class="material-symbols-outlined text-lg">payments</span>
                            </div>
                            <div>
                                <span class="text-sm font-bold text-on-surface">Metode Pembayaran</span>
                                <p class="text-xs text-secondary">Pilih metode pelunasan</p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-secondary text-xl rotate-180 transition-transform duration-200" id="icon-bayar">expand_more</span>
                    </button>
                    <div class="px-4 pb-4 flex flex-col gap-2" id="content-bayar">
                        <?php foreach ($metodeBayar as $metode):
                            $badgeClass = match($metode['type']) {
                                'success' => 'bg-primary/10 text-primary',
                                'warning' => 'bg-amber-50 text-amber-700',
                                default => 'bg-surface-container text-on-surface-variant',
                            };
                        ?>
                        <label class="flex items-start gap-3 p-3 rounded-lg bg-surface-container-low hover:bg-surface-container cursor-pointer transition-all">
                            <input type="radio" name="bayar" value="<?= $metode['value'] ?>" class="mt-0.5 w-4 h-4 text-primary focus:ring-0 accent-primary cursor-pointer"
                                <?= ($metode['value'] === 'qris') ? 'checked' : '' ?>
                                id="bayar-<?= $metode['value'] ?>">
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center gap-2">
                                    <span class="text-sm font-bold text-on-surface flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-primary text-base"><?= $metode['icon'] ?></span>
                                        <?= $metode['label'] ?>
                                    </span>
                                    <?php if ($metode['badge']): ?>
                                    <span class="<?= $badgeClass ?> text-[10px] px-2 py-0.5 rounded-full font-bold"><?= $metode['badge'] ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-secondary mt-0.5"><?= $metode['desc'] ?></p>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="p-4 bg-primary/5 border border-primary/20 rounded-xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-surface-lowest flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-primary text-xl">security</span>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-on-surface">Booking 100% Terjamin</h4>
                    <p class="text-xs text-secondary">Jadwal terkunci resmi di sistem Arena</p>
                </div>
            </div>
        </div>
    </main>

    <!-- STICKY BOTTOM -->
    <div class="fixed bottom-0 left-0 right-0 bg-surface-lowest shadow-[0_-4px_20px_rgba(0,0,0,0.08)] z-40">
        <div class="max-w-lg mx-auto px-4 pt-3 pb-4 flex flex-col gap-2">
            <div class="flex flex-col gap-1">
                <div class="flex justify-between items-center text-xs text-secondary">
                    <span>Sewa Lapangan (<?= $jumlahSlot ?> Jam)</span>
                    <span class="text-on-surface font-semibold">Rp <?= number_format($totalHarga, 0, ',', '.') ?></span>
                </div>
                <div class="flex justify-between items-center text-xs text-secondary hidden" id="row-rompi">
                    <span>Rompi Latihan</span>
                    <span class="text-on-surface font-semibold">Rp <?= number_format($HARGA_ROMPI, 0, ',', '.') ?></span>
                </div>
                <div class="flex justify-between items-center text-xs text-secondary hidden" id="row-wasit">
                    <span>Jasa Wasit</span>
                    <span class="text-on-surface font-semibold">Rp <?= number_format($HARGA_WASIT, 0, ',', '.') ?></span>
                </div>
                <div class="flex justify-between items-center text-xs text-secondary">
                    <span>Biaya Layanan</span>
                    <span class="text-on-surface font-semibold">Rp <?= number_format($BIAYA_LAYANAN, 0, ',', '.') ?></span>
                </div>
                <div class="h-px bg-surface-container-high my-1"></div>
                <div class="flex justify-between items-baseline">
                    <div>
                        <span class="text-[10px] text-secondary font-bold uppercase tracking-wider">Total Tagihan</span>
                        <span class="text-lg font-extrabold text-on-surface" id="grand-total">Rp <?= number_format($totalHarga + $BIAYA_LAYANAN, 0, ',', '.') ?></span>
                    </div>
                    <span class="bg-primary/10 text-primary text-[10px] px-2 py-1 rounded-full font-bold">Termasuk PPN</span>
                </div>
            </div>
            <button type="button" class="w-full h-12 bg-primary hover:bg-primary-container text-on-primary rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-md active:scale-[0.98] transition-all" id="btn-pay">
                Lanjut Bayar
                <span class="material-symbols-outlined text-base">arrow_forward</span>
            </button>
            <button type="button" class="w-full py-2 flex items-center justify-center gap-1 text-secondary hover:text-on-surface text-xs font-semibold transition-colors" id="btn-back">
                <span class="material-symbols-outlined text-lg">chevron_left</span>
                Kembali ke Jadwal
            </button>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script>
    (function() {
        'use strict';

        // ============================================================
        // UTILITY
        // ============================================================
        function $(id) { return document.getElementById(id); }

        // ============================================================
        // TOMBOL KEMBALI
        // ============================================================
        $('btnBack').onclick = function() { history.back(); };
        $('btn-back').onclick = function() { history.back(); };

        // ============================================================
        // ACCORDION
        // ============================================================
        function setupAccordion(btnId, contentId, iconId) {
            var btn = $(btnId);
            var content = $(contentId);
            var icon = $(iconId);
            if (!btn || !content || !icon) return;
            btn.onclick = function() {
                var isHidden = content.classList.contains('hidden');
                if (isHidden) {
                    content.classList.remove('hidden');
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    content.classList.add('hidden');
                    icon.style.transform = 'rotate(0deg)';
                }
            };
        }

        // Setup accordions (semua terbuka default KECUALI kontak)
        setupAccordion('btn-tambahan', 'content-tambahan', 'icon-tambahan');
        setupAccordion('btn-bayar', 'content-bayar', 'icon-bayar');

        // ============================================================
        // HITUNG TOTAL
        // ============================================================
        var BASE_PITCH = <?= $totalHarga ?>;
        var SERVICE_FEE = <?= $BIAYA_LAYANAN ?>;
        var HARGA_ROMPI = <?= $HARGA_ROMPI ?>;
        var HARGA_WASIT = <?= $HARGA_WASIT ?>;

        function hitungTotal() {
            var total = BASE_PITCH + SERVICE_FEE;
            var selectedCount = 1;

            var rompi = $('check-rompi');
            var wasit = $('check-wasit');
            var bola = $('check-bola');
            var rowRompi = $('row-rompi');
            var rowWasit = $('row-wasit');
            var grandTotal = $('grand-total');
            var addonBadge = $('addon-badge');

            if (rompi && rompi.checked) {
                total += HARGA_ROMPI;
                selectedCount++;
                rowRompi.classList.remove('hidden');
            } else if (rowRompi) {
                rowRompi.classList.add('hidden');
            }

            if (wasit && wasit.checked) {
                total += HARGA_WASIT;
                selectedCount++;
                rowWasit.classList.remove('hidden');
            } else if (rowWasit) {
                rowWasit.classList.add('hidden');
            }

            if (bola && bola.checked) selectedCount++;

            if (addonBadge) addonBadge.textContent = selectedCount + ' Dipilih';
            if (grandTotal) grandTotal.textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        // Event listener untuk checkbox opsi tambahan
        ['rompi', 'wasit', 'bola'].forEach(function(id) {
            var el = $('check-' + id);
            if (el) el.addEventListener('change', hitungTotal);
        });

        // ============================================================
        // TOMBOL BAYAR
        // ============================================================
        $('btn-pay').onclick = function() {
            var btn = this;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-xl">progress_activity</span><span>Memproses...</span>';
            btn.disabled = true;

            setTimeout(function() {
                btn.innerHTML = '<span>Berhasil!</span><span class="material-symbols-outlined text-xl">done_all</span>';
                setTimeout(function() {
                    window.location.href = 'tiket.php';
                }, 1000);
            }, 800);
        };

        // Initialize
        hitungTotal();
    })();
    </script>
</body>
</html>
