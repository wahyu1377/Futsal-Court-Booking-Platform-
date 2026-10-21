<?php
/**
 * Halaman Detail & Jadwal - Arena Sport
 * Arena Sport Pulse Design System
 *
 * Path: frontend/pages/user/detail_jadwal.php
 */

// Konfigurasi halaman
$pageTitle = 'Detail & Jadwal';

// ============================================================
// AMBIL DATA DARI DATABASE
// ============================================================
require_once dirname(__DIR__, 3) . '/backend/config/database.php';
require_once dirname(__DIR__, 3) . '/backend/core/harga.php';
require_once dirname(__DIR__, 3) . '/backend/core/jadwal.php';

$lapanganId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

try {
    $pdo = db();

    // Ambil data lapangan
    $stmt = $pdo->prepare("SELECT * FROM lapangan WHERE id = ? AND aktif = 1");
    $stmt->execute([$lapanganId]);
    $lapangan = $stmt->fetch();

    if (!$lapangan) {
        // Fallback jika lapangan tidak ditemukan
        $lapangan = [
            'id' => 1,
            'nama' => 'Champion Futsal Arena',
            'jenis' => 'futsal',
            'kecamatan' => 'Cilandak Barat',
            'kota' => 'Jakarta Selatan',
            'harga_per_jam' => 150000,
            'rating' => 4.8,
            'jumlah_ulasan' => 124,
            'fasilitas' => 'Indoor AC,WiFi,Locker',
            'keterangan' => 'Peralatan Lengkap'
        ];
    }

    // Format fasilitas jadi array
    $fasilitasList = $lapangan['fasilitas'] ? explode(',', $lapangan['fasilitas']) : [];

} catch (Exception $e) {
    // Fallback jika database error
    $lapangan = [
        'id' => 1,
        'nama' => 'Champion Futsal Arena',
        'jenis' => 'futsal',
        'kecamatan' => 'Cilandak Barat',
        'kota' => 'Jakarta Selatan',
        'harga_per_jam' => 150000,
        'rating' => 4.8,
        'jumlah_ulasan' => 124,
        'fasilitas' => 'Indoor AC,WiFi,Locker',
        'keterangan' => 'Peralatan Lengkap'
    ];
    $fasilitasList = ['Indoor AC', 'WiFi', 'Locker'];
}

// ============================================================
// DATA SLOT JAM (8 jam operasional)
// ============================================================
// Harga slot dihitung dari `lapangan.harga_per_jam` lewat aturan tunggal di
// backend/core/harga.php, supaya harga yang tampil di sini sama persis dengan
// yang ditagih di checkout.php.
$hargaPerJam = (int) ($lapangan['harga_per_jam'] ?? 150000);

// Slot dan statusnya berasal dari backend/core/jadwal.php, sumber yang sama
// dengan yang dipakai validasi server di checkout.php. Jadi slot yang tampil
// terkunci di sini pasti juga ditolak kalau dikirim lewat URL.
//
// Label "Prime Time" hanya untuk tampilan, mengikuti desain awal.
$labelPrimeTime = ['15.00 - 16.00 WIB', '16.00 - 17.00 WIB'];

$slots = [];
foreach (daftarSlot() as $slot) {
    $slots[] = [
        'jam'    => $slot['jam'],
        'harga'  => hargaSlot($hargaPerJam, $slot['mulai']),
        'label'  => in_array($slot['jam'], $labelPrimeTime, true) ? 'Prime Time' : null,
        'status' => $slot['status'],
    ];
}

// ============================================================
// DATA TANGGAL (7 hari ke depan)
// ============================================================
$tanggalList = [];
for ($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime("+$i days"));
    $hari = date('D', strtotime("+$i days"));
    $hariIndonesia = ['Sun'=>'Min','Mon'=>'Sen','Tue'=>'Sel','Wed'=>'Rab','Thu'=>'Kam','Fri'=>'Jum','Sat'=>'Sab'];
    $tanggalList[] = [
        'val' => $date,
        'hari' => $hariIndonesia[$hari],
        'tgl' => date('d', strtotime("+$i days")),
        'aktif' => ($i === 2) // Tanggal ke-3 aktif default
    ];
}

// ============================================================
// FUNGSI HELPER
// ============================================================
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
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

        /* Pilihan tanggal main: warna hijau ikut ke tanggal yang sedang dipilih.
           Dipakai :has() supaya tidak perlu JavaScript untuk memindahkan gaya. */
        .date-option:has(input:checked) {
            background-color: #006948;
            color: #ffffff;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.10);
        }
        .date-option:has(input:checked) .date-day { color: #85f8c4; }
        .date-option:has(input:checked) .date-dot { opacity: 1; }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .animate-pulse-dot {
            animation: pulse-dot 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="bg-surface font-sans text-sm text-on-surface flex flex-col min-h-screen">

    <!-- ============================================================
         HEADER - Fixed Top Navigation
         ============================================================ -->
    <header class="fixed top-0 w-full z-50 bg-surface/80 backdrop-blur-xl shadow-sm">
        <div class="h-16 px-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button id="btnBack" class="w-10 h-10 flex items-center justify-center rounded-full text-on-surface hover:bg-surface-container-high transition-colors">
                    <span class="material-symbols-outlined text-2xl">arrow_back</span>
                </button>
                <img alt="Logo" class="h-7 w-auto object-contain" src="../../assets/img/logo.png">
                <h1 class="font-bold text-on-surface"><?= htmlspecialchars($pageTitle) ?></h1>
            </div>
            <div class="flex items-center gap-1">
                <button class="w-10 h-10 flex items-center justify-center rounded-full">
                    <img src="../../assets/img/Foto profil.png" alt="Profil" class="w-9 h-9 rounded-full object-cover border-2 border-white shadow-sm" onerror="this.style.display='none'">
                </button>
            </div>
        </div>
    </header>

    <!-- ============================================================
         MAIN CONTENT
         ============================================================ -->
    <main class="flex flex-col relative w-full pt-16 pb-32 bg-surface min-h-screen">
        <div class="flex flex-col w-full">

            <!-- STEPPER -->
            <div class="px-4 py-3 bg-surface-lowest shadow-sm">
                <div class="flex items-center justify-between relative max-w-sm mx-auto">
                    <div class="absolute top-3 left-6 right-6 h-[2px] bg-surface-container-high z-0"></div>
                    <div class="absolute top-3 left-6 w-1/2 h-[2px] bg-primary z-0"></div>

                    <div class="relative z-10 flex flex-col items-center gap-1">
                        <div class="w-7 h-7 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-sm">
                            <span class="material-symbols-outlined text-sm">check</span>
                        </div>
                        <span class="text-[10px] text-primary font-bold uppercase tracking-wider">1. Rincian</span>
                    </div>

                    <div class="relative z-10 flex flex-col items-center gap-1">
                        <div class="w-7 h-7 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-sm ring-4 ring-primary-fixed/40">
                            <span class="text-xs font-bold">2</span>
                        </div>
                        <span class="text-[10px] text-on-surface font-extrabold uppercase tracking-wider">2. Jadwal</span>
                    </div>

                    <div class="relative z-10 flex flex-col items-center gap-1">
                        <div class="w-7 h-7 rounded-full bg-surface-container text-secondary flex items-center justify-center">
                            <span class="text-xs font-bold">3</span>
                        </div>
                        <span class="text-[10px] text-secondary uppercase tracking-wider">3. Bayar</span>
                    </div>
                </div>
            </div>

            <!-- HERO IMAGE -->
            <div class="relative w-full aspect-video bg-surface-container-high overflow-hidden">
                <?php
                $gambarList = ['lapangan1.jpeg', 'lapangan2.jpeg', 'lapangan3.jpeg'];
                $gambarRandom = $gambarList[array_rand($gambarList)];
                ?>
                <img src="../../assets/img/<?= $gambarRandom ?>" alt="<?= htmlspecialchars($lapangan['nama']) ?>" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                <div class="absolute top-3 left-3 right-3 flex items-center justify-between">
                    <div class="flex gap-1.5">
                        <span class="bg-white/90 backdrop-blur-sm text-slate-700 text-[10px] px-2.5 py-1 rounded-full uppercase tracking-wider font-semibold">
                            <?= ucfirst($lapangan['jenis']) ?>
                        </span>
                        <span class="bg-primary/90 backdrop-blur-sm text-white text-[10px] px-2.5 py-1 rounded-full font-bold">
                            Indoor
                        </span>
                    </div>
                </div>
                <div class="absolute bottom-3 right-3">
                    <span class="bg-black/60 text-white text-[10px] px-2 py-1 rounded-full flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs">photo_camera</span> 1/3
                    </span>
                </div>
            </div>

            <!-- VENUE INFO -->
            <div class="px-4 py-4 bg-surface-lowest">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <div>
                        <h2 class="text-lg font-extrabold text-on-surface leading-tight tracking-tight"><?= htmlspecialchars($lapangan['nama']) ?></h2>
                        <span class="text-xs text-primary font-bold px-2.5 py-0.5 rounded-full bg-primary/10 mt-1 inline-block">Court A</span>
                    </div>
                    <div class="flex items-center gap-1 bg-amber-50 px-2 py-1 rounded-full">
                        <span class="material-symbols-outlined text-amber-500 text-base">star</span>
                        <span class="text-sm font-bold text-amber-900"><?= number_format($lapangan['rating'], 1) ?></span>
                    </div>
                </div>
                <div class="flex items-start gap-1.5 text-secondary mb-2">
                    <span class="material-symbols-outlined text-primary text-base shrink-0 mt-0.5">location_on</span>
                    <p class="text-xs text-on-surface-variant leading-relaxed"><?= htmlspecialchars($lapangan['kecamatan']) ?>, <?= htmlspecialchars($lapangan['kota']) ?></p>
                </div>
                <div class="flex items-center gap-2 text-xs text-secondary">
                    <span class="inline-flex items-center text-primary font-semibold gap-0.5">
                        <span class="material-symbols-outlined text-xs">verified</span> Terverifikasi
                    </span>
                    <span>(<?= $lapangan['jumlah_ulasan'] ?> ulasan pemain)</span>
                </div>
            </div>

            <!-- ACCORDION SPESIFIKASI -->
            <div class="mx-4 mt-2 bg-surface-lowest rounded-xl shadow-sm overflow-hidden">
                <button class="w-full px-4 py-3 flex items-center justify-between bg-surface-container-low text-left transition-colors" id="accordion-toggle">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">info</span>
                        <span class="text-sm font-bold text-on-surface">Spesifikasi & Fasilitas</span>
                    </div>
                    <span class="material-symbols-outlined text-secondary transition-transform duration-200" id="accordion-arrow">keyboard_arrow_down</span>
                </button>
                <div class="hidden px-4 pb-4 bg-surface-lowest" id="accordion-content">
                    <!-- Spesifikasi -->
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div class="p-2.5 rounded-lg bg-surface-container-low flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">layers</span>
                            <div class="flex flex-col">
                                <span class="text-[10px] text-secondary">Lantai</span>
                                <span class="text-xs text-on-surface font-semibold">6mm Vinyl</span>
                            </div>
                        </div>
                        <div class="p-2.5 rounded-lg bg-surface-container-low flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">lightbulb</span>
                            <div class="flex flex-col">
                                <span class="text-[10px] text-secondary">Penerangan</span>
                                <span class="text-xs text-on-surface font-semibold">800 Lux LED</span>
                            </div>
                        </div>
                    </div>
                    <!-- Warning -->
                    <div class="flex items-center gap-2 p-2.5 bg-amber-50 rounded-lg text-amber-900 mb-3">
                        <span class="material-symbols-outlined text-amber-600 shrink-0">do_not_step</span>
                        <span class="text-xs leading-tight">Wajib menggunakan <strong>sepatu futsal rubber sole</strong></span>
                    </div>
                    <!-- Fasilitas -->
                    <span class="text-[10px] text-secondary uppercase font-bold tracking-wider block mb-2">Fasilitas</span>
                    <div class="grid grid-cols-2 gap-2">
                        <?php foreach ($fasilitasList as $fas): ?>
                        <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                            <span class="material-symbols-outlined text-primary text-base">check_circle</span>
                            <span><?= htmlspecialchars(trim($fas)) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- PILIH TANGGAL -->
            <div class="mx-4 mt-4 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">calendar_month</span>
                        <h3 class="text-base font-extrabold text-on-surface">Pilih Tanggal Main</h3>
                    </div>
                    <span class="text-xs font-semibold text-primary bg-primary/10 px-3 py-1 rounded-full"><?= date('F Y') ?></span>
                </div>
                <div class="flex items-center gap-2 overflow-x-auto py-1 hide-scrollbar">
                    <?php foreach ($tanggalList as $t): ?>
                    <label class="date-option flex flex-col items-center justify-center min-w-[52px] py-3 px-2 rounded-xl cursor-pointer transition-all active:scale-95 bg-surface-lowest shadow-sm text-on-surface hover:bg-surface-container-low">
                        <input type="radio" name="tanggal" value="<?= $t['val'] ?>" form="bookingForm" <?= !empty($t['aktif']) ? 'checked' : '' ?> class="hidden peer">
                        <span class="date-day text-[10px] font-semibold uppercase tracking-wider text-secondary"><?= $t['hari'] ?></span>
                        <span class="text-base font-bold leading-none mt-1"><?= $t['tgl'] ?></span>
                        <span class="date-dot w-1.5 h-1.5 bg-primary-fixed rounded-full mt-1 opacity-0"></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- SLOT JADWAL -->
            <div class="mx-4 mt-4 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">schedule</span>
                        <h3 class="text-base font-extrabold text-on-surface">Slot Jadwal</h3>
                    </div>
                    <span class="text-[10px] text-secondary">WIB</span>
                </div>
                <div class="flex items-center gap-4 text-[10px] text-secondary">
                    <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-surface-lowest border border-slate-200"></span><span>Tersedia</span></div>
                    <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-primary"></span><span>Terpilih</span></div>
                    <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-slate-200"></span><span>Penuh</span></div>
                </div>

                <form action="checkout.php" method="get" id="bookingForm">
                <input type="hidden" name="id" value="<?= $lapangan['id'] ?>">
                <div class="grid grid-cols-3 gap-2" id="slotList">
                    <?php foreach ($slots as $index => $s):
                        $booked = $s['status'] === 'booked';
                        $harga = formatRupiah($s['harga']);
                    ?>
                    <label class="slot-card rounded-xl p-3 text-center transition-all cursor-pointer
                        <?php if($booked): ?>bg-slate-100 cursor-not-allowed opacity-60
                        <?php else: ?>bg-surface-lowest shadow-sm hover:shadow-md border border-transparent<?php endif; ?>"
                        <?php if($booked): ?>style="pointer-events: none;"<?php endif; ?>>

                        <input type="checkbox" name="slot[]" value="<?= htmlspecialchars($s['jam']) ?>"
                            <?= $booked ? 'disabled' : '' ?>
                            class="hidden peer cb-slot"
                            data-nominal="<?= $s['harga'] ?>"
                            data-index="<?= $index ?>">

                        <div class="flex flex-col items-center gap-1">
                            <span class="material-symbols-outlined text-lg <?= $booked ? 'text-slate-400' : 'text-primary' ?>">
                                <?php if($booked): ?>lock<?php else: ?>schedule<?php endif; ?>
                            </span>
                            <span class="text-xs font-bold <?= $booked ? 'text-slate-400 line-through' : 'text-on-surface' ?>">
                                <?= $s['jam'] ?>
                            </span>
                            <span class="text-[10px] <?= $booked ? 'text-slate-400' : 'text-secondary' ?>">
                                <?= $harga ?>
                            </span>
                            <?php if(!empty($s['label'])): ?>
                            <span class="text-[9px] bg-amber-100 text-amber-700 font-bold px-1.5 py-0.5 rounded-full mt-0.5"><?= $s['label'] ?></span>
                            <?php endif; ?>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
                </form>
            </div>
        </div>
    </main>

    <!-- FLOATING BAR -->
    <div class="fixed bottom-0 left-0 right-0 z-40 bg-surface-lowest shadow-[0_-4px_20px_rgba(0,0,0,0.08)]">
        <div class="max-w-lg mx-auto px-4 pt-3 pb-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <div class="flex flex-col">
                    <span class="text-[10px] text-secondary uppercase font-semibold tracking-wider">Durasi Sewa</span>
                    <span class="text-sm text-on-surface font-bold flex items-center gap-1">
                        <span class="material-symbols-outlined text-primary text-sm">timelapse</span>
                        <span id="durasiNum">0 Jam</span>
                    </span>
                </div>
                <div class="flex flex-col items-end">
                    <span class="text-[10px] text-secondary uppercase font-semibold tracking-wider">Total</span>
                    <span class="text-lg text-primary font-extrabold leading-none" id="totalHarga">Rp 0</span>
                </div>
            </div>
            <button type="submit" form="bookingForm" id="btnLanjut"
                class="w-full h-12 bg-primary hover:bg-primary-container text-on-primary rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-md active:scale-95 transition-all">
                Lanjut Pemesanan
                <span class="material-symbols-outlined text-base">arrow_forward</span>
            </button>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script>
    (function(){
        // Tombol Kembali
        var btnBack = document.getElementById('btnBack');
        if (btnBack) {
            btnBack.addEventListener('click', function() {
                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    window.location.href = 'beranda.php';
                }
            });
        }

        // Accordion
        var accordionToggle = document.getElementById('accordion-toggle');
        var accordionContent = document.getElementById('accordion-content');
        var accordionArrow = document.getElementById('accordion-arrow');
        if (accordionToggle && accordionContent && accordionArrow) {
            accordionToggle.addEventListener('click', function() {
                var isHidden = accordionContent.classList.contains('hidden');
                if (isHidden) {
                    accordionContent.classList.remove('hidden');
                    accordionArrow.classList.add('rotate-180');
                } else {
                    accordionContent.classList.add('hidden');
                    accordionArrow.classList.remove('rotate-180');
                }
            });
        }

        // Slot Selection
        var cbs = document.querySelectorAll('.cb-slot:not(:disabled)');
        var total = document.getElementById('totalHarga');
        var durasi = document.getElementById('durasiNum');

        function hitung() {
            var sum = 0, count = 0;
            cbs.forEach(function(c) {
                if (c.checked) {
                    sum += parseInt(c.dataset.nominal);
                    count++;
                }
            });
            if (total) total.textContent = 'Rp ' + sum.toLocaleString('id-ID');
            if (durasi) durasi.textContent = count > 0 ? count + ' Jam' : '0 Jam';
            updateVisuals();
        }

        function updateVisuals() {
            var allSlots = document.querySelectorAll('.slot-card');
            allSlots.forEach(function(card) {
                var cb = card.querySelector('.cb-slot');
                if (cb && !cb.disabled) {
                    var icon = card.querySelector('.material-symbols-outlined');
                    var timeSlot = card.querySelector('.text-xs.font-bold');
                    var priceSlot = card.querySelector('.text-\\[10px\\]');

                    if (cb.checked) {
                        card.classList.remove('bg-surface-lowest', 'shadow-sm', 'border-transparent');
                        card.classList.add('bg-primary', 'text-on-primary', 'shadow-lg');
                        if (icon) {
                            icon.classList.remove('text-primary');
                            icon.classList.add('text-on-primary');
                            icon.textContent = 'check_circle';
                        }
                        if (timeSlot) {
                            timeSlot.classList.remove('text-on-surface');
                            timeSlot.classList.add('text-on-primary');
                        }
                        if (priceSlot) {
                            priceSlot.classList.remove('text-secondary');
                            priceSlot.classList.add('text-primary-fixed');
                        }
                    } else {
                        card.classList.remove('bg-primary', 'text-on-primary', 'shadow-lg');
                        card.classList.add('bg-surface-lowest', 'shadow-sm');
                        if (icon) {
                            icon.classList.remove('text-on-primary');
                            icon.classList.add('text-primary');
                            icon.textContent = 'schedule';
                        }
                        if (timeSlot) {
                            timeSlot.classList.remove('text-on-primary');
                            timeSlot.classList.add('text-on-surface');
                        }
                        if (priceSlot) {
                            priceSlot.classList.remove('text-primary-fixed');
                            priceSlot.classList.add('text-secondary');
                        }
                    }
                }
            });
        }

        cbs.forEach(function(c) {
            c.addEventListener('change', hitung);
        });

        // Initial calculation
        hitung();
    })();
    </script>
</body>
</html>
