<?php
/**
 * Halaman Checkout Booking - Arena Sport
 * Menggunakan Design System Arena Sport Pulse
 *
 * Path: frontend/pages/user/checkout.php
 */

// Konfigurasi halaman
$pageTitle = 'Checkout Booking';

// Data Opsi Tambahan
$opsiTambahan = [
    [
        'id'     => 'rompi',
        'label'  => 'Sewa Rompi Latihan',
        'desc'   => '12 pcs Rompi Neon/Oranye berstandar',
        'harga'  => 30000,
        'checked' => true,
        'icon'   => 'checkroom',
    ],
    [
        'id'     => 'wasit',
        'label'  => 'Jasa Wasit Pertandingan',
        'desc'   => '2 Jam Berlisensi Askot Futsal',
        'harga'  => 100000,
        'checked' => false,
        'icon'   => 'sports',
    ],
    [
        'id'     => 'bola',
        'label'  => 'Bola Futsal Tambahan',
        'desc'   => '2 buah bola match standar FIFA',
        'harga'  => 0,
        'checked' => true,
        'icon'   => 'sports_soccer',
    ],
];

// Data Metode Pembayaran
$metodeBayar = [
    [
        'value'    => 'qris',
        'icon'     => 'qr_code_scanner',
        'label'    => 'QRIS / Instan',
        'badge'    => ['text' => 'Otomatis', 'type' => 'success'],
        'desc'     => 'BCA, Mandiri, GoPay, ShopeePay, OVO',
        'selected' => true,
    ],
    [
        'value'    => 'va',
        'icon'     => 'account_balance',
        'label'    => 'Virtual Account',
        'badge'    => null,
        'desc'     => 'BCA, Mandiri, BRI, BNI',
        'selected' => false,
    ],
    [
        'value'    => 'onsite',
        'icon'     => 'storefront',
        'label'    => 'Bayar di Tempat',
        'badge'    => ['text' => 'DP 50%', 'type' => 'warning'],
        'desc'     => 'DP via transfer, lunasi di kasir',
        'selected' => false,
    ],
];

// Data kontak (contoh - nanti dari database)
$kontak = [
    'nama' => 'Bambang Pamungkas',
    'wa' => '0812-3456-7890',
    'tim' => 'Garuda FC',
];

// Data booking
$booking = [
    'id' => 'BKG-9921',
    'lapangan' => 'Futsal Vinyl Pro A',
    'nama' => 'Champion Futsal Arena',
    'tanggal' => 'Kamis, 23 Okt 2026',
    'jam' => '15.00 - 17.00 WIB',
    'durasi' => '2 Jam',
    'harga_lapangan' => 360000,
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?= $pageTitle ?> - Arena Sport</title>

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
            // Arena Sport Pulse Design System
            'primary': '#006948',
            'primary-dark': '#005840',
            'primary-container': '#00855d',
            'on-primary': '#ffffff',
            'on-primary-container': '#f5fff7',
            'primary-fixed': '#85f8c4',
            'primary-fixed-dim': '#68dba9',
            'surface': '#f8f9ff',
            'surface-lowest': '#ffffff',
            'surface-container-low': '#eff4ff',
            'surface-container': '#e5eeff',
            'surface-container-high': '#dce9ff',
            'surface-container-highest': '#d3e4fe',
            'on-surface': '#0b1c30',
            'on-surface-variant': '#3d4a42',
            'secondary': '#565e74',
            'error': '#ba1a1a',
            'background': '#f8f9ff',
          },
          fontFamily: {
            sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
          },
          spacing: {
            'gutter-sm': '1rem',
            'space-xs': '0.25rem',
            'space-sm': '0.5rem',
            'space-md': '1rem',
            'space-lg': '1.5rem',
            'space-xl': '2rem',
          }
        }
      }
    };
    </script>

    <style>
        /* Base Styles */
        html, body {
            width: 100vw;
            margin: 0;
            padding: 0;
            overscroll-behavior: none;
        }

        body {
            min-height: max(884px, 100dvh);
        }

        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 0px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 0px); }

        /* Material Symbols */
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            vertical-align: middle;
        }

        /* Hide scrollbar */
        ::-webkit-scrollbar { display: none; }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Pulse animation for active step */
        @keyframes pulse-indicator {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.1); }
        }
        .animate-pulse-indicator {
            animation: pulse-indicator 2s ease-in-out infinite;
        }

        /* Spinner animation */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin {
            animation: spin 1s linear infinite;
        }
    </style>
</head>
<body class="bg-surface font-sans text-sm text-on-surface flex flex-col min-h-screen">

    <!-- ============================================================
         HEADER - Fixed Top Navigation
         ============================================================ -->
    <header class="fixed top-0 w-full z-50 pt-safe bg-surface/80 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
        <div class="h-16 px-4 flex items-center justify-between">

            <!-- Left: Back Button + Logo + Title -->
            <div class="flex items-center gap-3">
                <button id="btnBack" class="w-10 h-10 flex items-center justify-center rounded-full text-on-surface hover:bg-surface-container-high transition-colors" aria-label="Kembali">
                    <span class="material-symbols-outlined text-2xl">arrow_back</span>
                </button>
                <img alt="Arena Sport Logo" class="h-7 w-auto object-contain" src="../../assets/img/Logo.png">
                <h1 class="font-bold text-on-surface">Checkout</h1>
            </div>

            <!-- Right: Profile -->
            <div class="flex items-center gap-1">
                <button id="btnBookmark" class="w-10 h-10 flex items-center justify-center rounded-full text-on-surface hover:bg-surface-container-high transition-colors" aria-label="Profil">
                    <img src="../../assets/img/Foto profil.png" alt="Profil" class="w-9 h-9 rounded-full object-cover border-2 border-white shadow-sm">
                </button>
            </div>
        </div>
    </header>

    <!-- ============================================================
         MAIN CONTENT
         ============================================================ -->
    <main class="flex flex-col relative w-full pt-16 pb-36 bg-surface min-h-screen">
        <div class="flex flex-col w-full px-4 pt-4 pb-6 gap-4">

            <!-- Stepper Indicator -->
            <div class="w-full bg-surface-lowest rounded-xl p-4 shadow-sm">
                <div class="flex items-center justify-between relative">
                    <!-- Connector Line Background -->
                    <div class="absolute left-8 right-8 top-4 h-[2px] bg-surface-container-high z-0"></div>
                    <div class="absolute left-8 w-1/2 top-4 h-[2px] bg-primary z-0"></div>

                    <!-- Step 1: Kontak (Done) -->
                    <div class="relative z-10 flex flex-col items-center gap-1">
                        <div class="w-7 h-7 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-sm">
                            <span class="material-symbols-outlined text-sm">check</span>
                        </div>
                        <span class="text-[10px] text-primary font-bold">1. Kontak</span>
                    </div>

                    <!-- Step 2: Detail (Active) -->
                    <div class="relative z-10 flex flex-col items-center gap-1">
                        <div class="w-7 h-7 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-sm">
                            <span class="material-symbols-outlined text-xs animate-pulse-indicator">fiber_manual_record</span>
                        </div>
                        <span class="text-[10px] text-on-surface font-bold">2. Detail</span>
                    </div>

                    <!-- Step 3: Bayar (Upcoming) -->
                    <div class="relative z-10 flex flex-col items-center gap-1">
                        <div class="w-7 h-7 rounded-full bg-surface-container text-secondary flex items-center justify-center">
                            <span class="material-symbols-outlined text-xs">radio_button_unchecked</span>
                        </div>
                        <span class="text-[10px] text-secondary">3. Bayar</span>
                    </div>
                </div>
            </div>

            <!-- ============================================================
                 Booking Header Card
                 ============================================================ -->
            <div class="w-full bg-surface-lowest rounded-xl overflow-hidden shadow-sm flex flex-col">
                <!-- Hero Image with Overlay -->
                <div class="relative w-full h-36 bg-cover bg-center" style="background-image: url('../../assets/img/lapangan1.jpeg'); background-size: cover; background-position: center;">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>

                    <!-- Booking ID Badge -->
                    <div class="absolute top-3 right-3">
                        <span class="bg-primary/90 backdrop-blur-sm text-white text-[10px] px-2.5 py-1 rounded-full uppercase tracking-wider font-bold flex items-center gap-1 shadow">
                            <span class="material-symbols-outlined text-xs">verified</span> #<?= $booking['id'] ?>
                        </span>
                    </div>

                    <!-- venue Info -->
                    <div class="absolute bottom-3 left-4 right-4 text-white">
                        <span class="text-[10px] text-primary-fixed uppercase tracking-wider font-bold"><?= $booking['lapangan'] ?></span>
                        <h2 class="text-base font-bold truncate"><?= $booking['nama'] ?></h2>
                    </div>
                </div>

                <!-- Booking Details -->
                <div class="p-4 flex flex-col gap-2 bg-surface-container-low">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">calendar_today</span>
                        <span class="text-sm font-semibold text-on-surface"><?= $booking['tanggal'] ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">schedule</span>
                            <span class="text-sm text-on-surface-variant"><?= $booking['jam'] ?></span>
                        </div>
                        <span class="text-[10px] text-on-surface-variant bg-surface-container px-2.5 py-0.5 rounded-full font-bold">
                            Durasi: <?= $booking['durasi'] ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- ============================================================
                 Accordion Sections
                 ============================================================ -->
            <div class="flex flex-col gap-3">

                <!-- Accordion 1: Data Kontak (Collapsed) -->
                <div class="bg-surface-lowest rounded-xl shadow-sm overflow-hidden" id="accordion-kontak">
                    <button class="w-full p-4 flex items-center justify-between text-left hover:bg-surface-container-low transition-colors" id="acc-btn-kontak">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-lg">person</span>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-on-surface">Data Kontak</span>
                                    <span class="bg-primary/10 text-primary text-[10px] px-2 py-0.5 rounded-full font-bold">Tervalidasi</span>
                                </div>
                                <p class="text-xs text-secondary truncate"><?= $kontak['nama'] ?> - <?= $kontak['tim'] ?></p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-secondary text-xl transition-transform duration-200" id="acc-icon-kontak">expand_more</span>
                    </button>
                    <div class="hidden px-4 pb-4 pt-0 bg-surface-lowest" id="acc-content-kontak">
                        <div class="grid grid-cols-1 gap-2 p-3 rounded-lg bg-surface-container-low">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-secondary">Nama</span>
                                <span class="text-on-surface font-semibold"><?= $kontak['nama'] ?></span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-secondary">WhatsApp</span>
                                <span class="text-on-surface font-semibold flex items-center gap-1">
                                    <span class="material-symbols-outlined text-primary text-xs">call</span> <?= $kontak['wa'] ?>
                                </span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-secondary">Tim</span>
                                <span class="text-on-surface font-semibold"><?= $kontak['tim'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accordion 2: Opsi Tambahan (Expanded by default) -->
                <div class="bg-surface-lowest rounded-xl shadow-sm overflow-hidden" id="accordion-tambahan">
                    <button class="w-full p-4 flex items-center justify-between text-left hover:bg-surface-container-low transition-colors" id="acc-btn-tambahan">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-lg">sports_soccer</span>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-on-surface">Opsi Tambahan</span>
                                    <span class="bg-surface-container text-on-surface-variant text-[10px] px-2 py-0.5 rounded-full font-bold" id="addon-badge">2 Dipilih</span>
                                </div>
                                <p class="text-xs text-secondary">Peralatan & kelengkapan match</p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-secondary text-xl transform rotate-180 transition-transform duration-200" id="acc-icon-tambahan">expand_more</span>
                    </button>
                    <div class="px-4 pb-4 flex flex-col gap-2" id="acc-content-tambahan">

                        <?php foreach ($opsiTambahan as $opsi):
                            $hargaLabel = $opsi['harga'] > 0
                                ? '<span class="text-primary font-bold text-xs">+Rp ' . number_format($opsi['harga'], 0, ',', '.') . '</span>'
                                : '<span class="bg-primary-fixed text-on-primary-fixed text-[10px] px-2 py-0.5 rounded-full font-bold uppercase">Gratis</span>';
                        ?>
                        <label class="flex items-start gap-3 p-3 rounded-lg bg-surface-container-low hover:bg-surface-container transition-colors cursor-pointer">
                            <input type="checkbox"
                                   name="opsi[]"
                                   value="<?= $opsi['id'] ?>"
                                   <?= $opsi['checked'] ? 'checked' : '' ?>
                                   class="mt-0.5 w-4 h-4 rounded text-primary focus:ring-0 accent-primary cursor-pointer"
                                   id="check-<?= $opsi['id'] ?>">

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center gap-2">
                                    <span class="text-sm font-bold text-on-surface flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-primary text-base"><?= $opsi['icon'] ?></span>
                                        <?= $opsi['label'] ?>
                                    </span>
                                    <?= $hargaLabel ?>
                                </div>
                                <p class="text-xs text-secondary mt-0.5"><?= $opsi['desc'] ?></p>
                            </div>
                        </label>
                        <?php endforeach; ?>

                    </div>
                </div>

                <!-- Accordion 3: Metode Pembayaran (Expanded) -->
                <div class="bg-surface-lowest rounded-xl shadow-sm overflow-hidden" id="accordion-bayar">
                    <button class="w-full p-4 flex items-center justify-between text-left hover:bg-surface-container-low transition-colors" id="acc-btn-bayar">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-lg">payments</span>
                            </div>
                            <div class="min-w-0">
                                <span class="text-sm font-bold text-on-surface">Metode Pembayaran</span>
                                <p class="text-xs text-secondary">Pilih metode pelunasan</p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-secondary text-xl transform rotate-180 transition-transform duration-200" id="acc-icon-bayar">expand_more</span>
                    </button>
                    <div class="px-4 pb-4 flex flex-col gap-2" id="acc-content-bayar">

                        <?php foreach ($metodeBayar as $metode):
                            $badgeClass = match($metode['badge']['type'] ?? '') {
                                'success' => 'bg-primary/10 text-primary',
                                'warning' => 'bg-amber-50 text-amber-700',
                                default   => 'bg-surface-container text-on-surface-variant',
                            };
                        ?>
                        <label class="flex items-start gap-3 p-3 rounded-lg bg-surface-container-low hover:bg-surface-container cursor-pointer transition-all">
                            <input type="radio"
                                   name="payment-method"
                                   value="<?= $metode['value'] ?>"
                                   <?= $metode['selected'] ? 'checked' : '' ?>
                                   class="mt-0.5 w-4 h-4 text-primary focus:ring-0 accent-primary cursor-pointer"
                                   id="payment-<?= $metode['value'] ?>">

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-bold text-on-surface flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-primary text-base"><?= $metode['icon'] ?></span>
                                        <?= $metode['label'] ?>
                                    </span>
                                    <?php if ($metode['badge']): ?>
                                    <span class="<?= $badgeClass ?> text-[10px] px-2 py-0.5 rounded-full font-bold"><?= $metode['badge']['text'] ?></span>
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
                <div class="min-w-0">
                    <h4 class="text-sm font-bold text-on-surface">Booking 100% Terjamin</h4>
                    <p class="text-xs text-secondary">Jadwal terkunci resmi di sistem Arena</p>
                </div>
            </div>

        </div>
    </main>

    <!-- ============================================================
         STICKY BOTTOM PRICE BREAKDOWN & ACTION
         ============================================================ -->
    <div class="fixed bottom-0 left-0 right-0 bg-surface-lowest shadow-[0_-4px_20px_rgba(0,0,0,0.08)] pb-safe z-40">
        <div class="max-w-lg mx-auto px-4 pt-3 pb-4 flex flex-col gap-2">

            <!-- Price Breakdown -->
            <div class="flex flex-col gap-1">
                <div class="flex justify-between items-center text-xs text-secondary">
                    <span>Sewa Lapangan (<?= $booking['durasi'] ?>)</span>
                    <span class="text-on-surface font-semibold">Rp <?= number_format($booking['harga_lapangan'], 0, ',', '.') ?></span>
                </div>

                <!-- Rompi Row -->
                <div class="flex justify-between items-center text-xs text-secondary" id="row-rompi">
                    <span>Rompi Latihan</span>
                    <span class="text-on-surface font-semibold">Rp 30.000</span>
                </div>

                <!-- Wasit Row (Hidden by default) -->
                <div class="hidden justify-between items-center text-xs text-secondary" id="row-wasit">
                    <span>Jasa Wasit</span>
                    <span class="text-on-surface font-semibold">Rp 100.000</span>
                </div>

                <div class="flex justify-between items-center text-xs text-secondary">
                    <span>Biaya Layanan</span>
                    <span class="text-on-surface font-semibold">Rp 2.000</span>
                </div>

                <div class="h-px bg-surface-container-high my-1"></div>

                <!-- Total -->
                <div class="flex justify-between items-baseline">
                    <div class="flex flex-col">
                        <span class="text-[10px] text-secondary font-bold uppercase tracking-wider">Total Tagihan</span>
                        <span class="text-lg font-extrabold text-on-surface" id="grand-total">Rp 392.000</span>
                    </div>
                    <span class="bg-primary/10 text-primary text-[10px] px-2 py-1 rounded-full font-bold">
                        Termasuk PPN
                    </span>
                </div>
            </div>

            <!-- CTAs -->
            <div class="flex flex-col gap-2">
                <button type="button" class="w-full h-12 bg-primary hover:bg-primary-container text-on-primary rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-md active:scale-[0.98] transition-all" id="btn-pay">
                    Lanjut Bayar
                    <span class="material-symbols-outlined text-base">arrow_forward</span>
                </button>
                <button type="button" class="w-full py-2 flex items-center justify-center gap-1 text-secondary hover:text-on-surface text-xs font-semibold transition-colors" id="btn-back">
                    <span class="material-symbols-outlined text-lg">chevron_left</span>
                    <span>Kembali ke Jadwal</span>
                </button>
            </div>

        </div>
    </div>

    <!-- ============================================================
         JAVASCRIPT - Vanilla JS
         ============================================================ -->
    <script>
    (function() {
        'use strict';

        /* Tombol Kembali */
        var btnBack = document.getElementById('btn-back');
        if (btnBack) {
            btnBack.addEventListener('click', function() {
                window.location.href = 'detail_jadwal.php';
            });
        }

        var headerBack = document.getElementById('btnBack');
        if (headerBack) {
            headerBack.addEventListener('click', function() {
                window.location.href = 'detail_jadwal.php';
            });
        }

        /* Accordion Logic */
        function setupAccordion(btnId, contentId, iconId) {
            var btn = document.getElementById(btnId);
            var content = document.getElementById(contentId);
            var icon = document.getElementById(iconId);

            if (!btn || !content || !icon) return;

            btn.addEventListener('click', function() {
                var isHidden = content.classList.contains('hidden');
                if (isHidden) {
                    content.classList.remove('hidden');
                    icon.classList.add('rotate-180');
                } else {
                    content.classList.add('hidden');
                    icon.classList.remove('rotate-180');
                }
            });
        }

        setupAccordion('acc-btn-kontak', 'acc-content-kontak', 'acc-icon-kontak');
        setupAccordion('acc-btn-tambahan', 'acc-content-tambahan', 'acc-icon-tambahan');
        setupAccordion('acc-btn-bayar', 'acc-content-bayar', 'acc-icon-bayar');

        /* Dynamic Price Calculation */
        var BASE_PITCH = <?= $booking['harga_lapangan'] ?>;
        var SERVICE_FEE = 2000;

        var checkRompi = document.getElementById('check-rompi');
        var checkWasit = document.getElementById('check-wasit');
        var checkBola = document.getElementById('check-bola');

        var rowRompi = document.getElementById('row-rompi');
        var rowWasit = document.getElementById('row-wasit');
        var grandTotalEl = document.getElementById('grand-total');
        var addonBadge = document.getElementById('addon-badge');

        function calculateTotal() {
            var total = BASE_PITCH + SERVICE_FEE;
            var selectedCount = 0;

            if (checkRompi && checkRompi.checked) {
                total += 30000;
                selectedCount++;
                if (rowRompi) {
                    rowRompi.classList.remove('hidden');
                    rowRompi.classList.add('flex');
                }
            } else if (rowRompi) {
                rowRompi.classList.add('hidden');
                rowRompi.classList.remove('flex');
            }

            if (checkWasit && checkWasit.checked) {
                total += 100000;
                selectedCount++;
                if (rowWasit) {
                    rowWasit.classList.remove('hidden');
                    rowWasit.classList.add('flex');
                }
            } else if (rowWasit) {
                rowWasit.classList.add('hidden');
                rowWasit.classList.remove('flex');
            }

            if (checkBola && checkBola.checked) {
                selectedCount++;
            }

            if (addonBadge) {
                addonBadge.textContent = selectedCount + ' Dipilih';
            }

            if (grandTotalEl) {
                grandTotalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
            }
        }

        if (checkRompi) checkRompi.addEventListener('change', calculateTotal);
        if (checkWasit) checkWasit.addEventListener('change', calculateTotal);
        if (checkBola) checkBola.addEventListener('change', calculateTotal);

        /* Pay Button Micro-interaction */
        var btnPay = document.getElementById('btn-pay');
        if (btnPay) {
            btnPay.addEventListener('click', function() {
                btnPay.innerHTML = '<span class="material-symbols-outlined animate-spin text-xl">progress_activity</span><span>Memproses...</span>';
                btnPay.disabled = true;

                setTimeout(function() {
                    btnPay.innerHTML = '<span>Berhasil!</span><span class="material-symbols-outlined text-xl">done_all</span>';

                    setTimeout(function() {
                        window.location.href = 'tiket.php';
                    }, 1000);
                }, 800);
            });
        }

        /* Initialize */
        calculateTotal();

    })();
    </script>

</body>
</html>
