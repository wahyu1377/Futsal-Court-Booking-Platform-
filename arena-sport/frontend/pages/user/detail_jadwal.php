<?php
/**
 * Halaman Detail & Jadwal - Arena Sport
 * Arena Sport Pulse Design System
 *
 * Path: frontend/pages/user/detail_jadwal.php
 *
 * Halaman publik: tamu boleh melihat lapangan dan jadwal, tetapi hanya
 * pengguna yang login (role user) yang bisa lanjut ke checkout.
 */

$pageTitle = 'Detail & Jadwal';

require_once dirname(__DIR__, 3) . '/backend/core/auth.php';      // database + session + currentUser()
require_once dirname(__DIR__, 3) . '/backend/core/harga.php';
require_once dirname(__DIR__, 3) . '/backend/core/jadwal.php';
require_once dirname(__DIR__, 2) . '/components/helper_profil.php'; // e(), avatarSrc()

$user      = currentUser();                            // null kalau tamu
$bisaPesan = $user !== null && $user['role'] === 'user';

function halamanPesan(int $kode, string $judul, string $isi): never
{
    http_response_code($kode);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= htmlspecialchars($judul) ?> - Arena Sport</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-sm p-8 max-w-sm w-full text-center">
            <h1 class="text-lg font-bold text-slate-900 mb-2"><?= htmlspecialchars($judul) ?></h1>
            <p class="text-sm text-slate-500 mb-6"><?= htmlspecialchars($isi) ?></p>
            <a href="beranda.php" class="inline-block w-full bg-emerald-700 text-white font-bold rounded-xl py-3 text-sm">Kembali ke Beranda</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ============================================================
// AMBIL DATA DARI DATABASE
// ============================================================
$lapanganId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$lapanganId || $lapanganId < 1) {
    halamanPesan(404, 'Lapangan tidak ditemukan', 'Alamat halaman ini tidak lengkap. Silakan pilih lapangan dari beranda.');
}

$tanggalList = daftarTanggal();
$tanggalPilih = is_string($_GET['tanggal'] ?? null) ? $_GET['tanggal'] : $tanggalList[0]['val'];
if (!tanggalBisaDipesan($tanggalPilih)) {
    $tanggalPilih = $tanggalList[0]['val'];
}

try {
    $pdo      = db();
    $lapangan = ambilLapangan($pdo, $lapanganId);
    if (!$lapangan) {
        halamanPesan(404, 'Lapangan tidak ditemukan', 'Lapangan ini sudah tidak aktif atau tidak ada.');
    }

    // Status slot dari tabel booking, untuk tanggal yang dipilih
    $slots = jadwalSlot($pdo, $lapangan, $tanggalPilih);

    // Status favorit (hanya untuk akun pengguna)
    $sudahFavorit = false;
    if ($bisaPesan) {
        $st = $pdo->prepare('SELECT 1 FROM favorit WHERE user_id = ? AND lapangan_id = ?');
        $st->execute([(int) $user['id'], (int) $lapangan['id']]);
        $sudahFavorit = (bool) $st->fetchColumn();
    }
} catch (PDOException $e) {
    error_log('Detail lapangan gagal: ' . $e->getMessage());
    halamanPesan(500, 'Terjadi kesalahan', 'Data lapangan tidak dapat dimuat. Coba lagi beberapa saat lagi.');
}

// ============================================================
// OLAH DATA UNTUK TAMPILAN
// ============================================================
$fasilitasList = array_values(array_filter(array_map('trim', explode(',', (string) $lapangan['fasilitas']))));

$lingkungan = null;   // "Indoor" / "Outdoor" hanya ditampilkan kalau tercatat di fasilitas
if (preg_match('/\b(indoor|outdoor)\b/i', implode(' ', $fasilitasList), $m)) {
    $lingkungan = ucfirst(strtolower($m[1]));
}

// Foto utama dari kolom lapangan.foto (folder yang sama dengan beranda)
$fotoUrl = null;
$foto    = (string) ($lapangan['foto'] ?? '');
if ($foto !== '' && !str_contains($foto, '..') && !str_contains($foto, '\\')) {
    $fotoUrl = '../../assets/img/lapangan/' . implode('/', array_map('rawurlencode', explode('/', $foto)));
}

$bulanNama = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$bulanDari = fn(string $ymd): string => $bulanNama[(int) substr($ymd, 5, 2)] . ' ' . substr($ymd, 0, 4);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php /* Microsoft Clarity (pelacakan halaman) */ ?>
    <?php include dirname(__DIR__, 2) . '/components/clarity.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?= e($lapangan['nama']) ?> - Arena Sport</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Material Symbols (halaman ini) + Bootstrap Icons (tombol favorit) -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

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
        html, body { width: 100vw; margin: 0; padding: 0; overscroll-behavior: none; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            vertical-align: middle;
        }
        ::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* Tanggal terpilih: warna hijau ikut ke tanggal yang dipilih (tanpa JS) */
        .date-option:has(input:checked) { background-color: #006948; color: #ffffff; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.10); }
        .date-option:has(input:checked) .date-day { color: #85f8c4; }
        .date-option:has(input:checked) .date-dot { opacity: 1; }

        /* Slot terpilih (tanpa JS tambahan) */
        .slot-card .ic-on { display: none; }
        .slot-card:has(input:checked) { background-color: #006948; color: #ffffff; box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.15); }
        .slot-card:has(input:checked) .ic-off { display: none; }
        .slot-card:has(input:checked) .ic-on { display: inline-block; color: #ffffff; }
        .slot-card:has(input:checked) .slot-time { color: #ffffff; }
        .slot-card:has(input:checked) .slot-price { color: #85f8c4; }

        /* Tombol favorit */
        .fav-btn { width: 40px; height: 40px; border: 0; border-radius: 9999px; background: rgba(255,255,255,.95);
            color: #0b1c30; font-size: 1.15rem; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 8px rgba(11,28,48,.18); transition: transform .15s, color .15s; cursor: pointer; }
        .fav-btn:hover { transform: scale(1.08); }
        .fav-btn:active { transform: scale(.92); }
        .fav-btn.is-on { color: #e0245e; }
        .mini-toast { position: fixed; left: 50%; bottom: 150px; transform: translateX(-50%); z-index: 60; background: #0b1c30;
            color: #fff; padding: .65rem 1.1rem; border-radius: 9999px; font-weight: 600; font-size: .85rem;
            box-shadow: 0 8px 20px rgba(11,28,48,.3); max-width: 90vw; text-align: center; }
    </style>
</head>
<body class="bg-surface font-sans text-sm text-on-surface flex flex-col min-h-screen">

    <!-- HEADER -->
    <header class="fixed top-0 w-full z-50 bg-surface/80 backdrop-blur-xl shadow-sm">
        <div class="h-16 px-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button id="btnBack" type="button" aria-label="Kembali" class="w-10 h-10 flex items-center justify-center rounded-full text-on-surface hover:bg-surface-container-high transition-colors">
                    <span class="material-symbols-outlined text-2xl">arrow_back</span>
                </button>
                <img alt="Logo" class="h-7 w-auto object-contain" src="../../assets/img/logo.png">
                <h1 class="font-bold text-on-surface"><?= e($pageTitle) ?></h1>
            </div>
            <div class="flex items-center gap-1">
                <?php if ($user): ?>
                <a href="<?= $user['role'] === 'owner' ? '../owner/akun.php' : 'akun.php' ?>" class="w-10 h-10 flex items-center justify-center rounded-full" title="<?= e($user['name']) ?>">
                    <img src="<?= e(avatarSrc($user['name'], $user['avatar'] ?? null)) ?>" alt="Akun <?= e($user['name']) ?>" class="w-9 h-9 rounded-full object-cover border-2 border-white shadow-sm">
                </a>
                <?php else: ?>
                <a href="../auth/login.php" class="bg-primary text-on-primary text-xs font-bold px-4 py-2 rounded-full">Masuk</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="flex flex-col relative w-full pt-16 pb-36 bg-surface min-h-screen">
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
            <div class="relative w-full aspect-video bg-gradient-to-br from-primary-fixed/40 to-surface-container-high overflow-hidden">
                <?php if ($fotoUrl): ?>
                <img src="<?= e($fotoUrl) ?>" alt="<?= e($lapangan['nama']) ?>" class="w-full h-full object-cover" onerror="this.remove()">
                <?php endif; ?>
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent pointer-events-none"></div>
                <div class="absolute top-3 left-3 right-3 flex items-start justify-between">
                    <div class="flex gap-1.5">
                        <span class="bg-white/90 backdrop-blur-sm text-slate-700 text-[10px] px-2.5 py-1 rounded-full uppercase tracking-wider font-semibold">
                            <?= e(ucfirst($lapangan['jenis'])) ?>
                        </span>
                        <?php if ($lingkungan): ?>
                        <span class="bg-primary/90 backdrop-blur-sm text-white text-[10px] px-2.5 py-1 rounded-full font-bold">
                            <?= e($lingkungan) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="fav-btn<?= $sudahFavorit ? ' is-on' : '' ?>" data-id="<?= (int) $lapangan['id'] ?>"
                            aria-pressed="<?= $sudahFavorit ? 'true' : 'false' ?>"
                            aria-label="<?= $sudahFavorit ? 'Hapus dari favorit' : 'Tambah ke favorit' ?>">
                        <i class="bi <?= $sudahFavorit ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                    </button>
                </div>
            </div>

            <!-- VENUE INFO -->
            <div class="px-4 py-4 bg-surface-lowest">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <div>
                        <h2 class="text-lg font-extrabold text-on-surface leading-tight tracking-tight"><?= e($lapangan['nama']) ?></h2>
                        <?php if (!empty($lapangan['keterangan'])): ?>
                        <span class="text-xs text-primary font-bold px-2.5 py-0.5 rounded-full bg-primary/10 mt-1 inline-block"><?= e($lapangan['keterangan']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-1 bg-amber-50 px-2 py-1 rounded-full shrink-0">
                        <span class="material-symbols-outlined text-amber-500 text-base">star</span>
                        <span class="text-sm font-bold text-amber-900"><?= number_format((float) $lapangan['rating'], 1) ?></span>
                    </div>
                </div>
                <div class="flex items-start gap-1.5 text-secondary mb-2">
                    <span class="material-symbols-outlined text-primary text-base shrink-0 mt-0.5">location_on</span>
                    <p class="text-xs text-on-surface-variant leading-relaxed"><?= e($lapangan['kecamatan']) ?>, <?= e($lapangan['kota']) ?></p>
                </div>
                <div class="text-xs text-secondary">(<?= (int) $lapangan['jumlah_ulasan'] ?> ulasan pemain)</div>
            </div>

            <!-- ACCORDION FASILITAS -->
            <div class="mx-4 mt-2 bg-surface-lowest rounded-xl shadow-sm overflow-hidden">
                <button type="button" class="w-full px-4 py-3 flex items-center justify-between bg-surface-container-low text-left transition-colors" id="accordion-toggle">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">info</span>
                        <span class="text-sm font-bold text-on-surface">Fasilitas &amp; Info</span>
                    </div>
                    <span class="material-symbols-outlined text-secondary transition-transform duration-200" id="accordion-arrow">keyboard_arrow_down</span>
                </button>
                <div class="hidden px-4 py-4 bg-surface-lowest" id="accordion-content">
                    <?php if (strtolower((string) $lapangan['jenis']) === 'futsal'): ?>
                    <div class="flex items-center gap-2 p-2.5 bg-amber-50 rounded-lg text-amber-900 mb-3">
                        <span class="material-symbols-outlined text-amber-600 shrink-0">do_not_step</span>
                        <span class="text-xs leading-tight">Wajib menggunakan <strong>sepatu futsal rubber sole</strong></span>
                    </div>
                    <?php endif; ?>
                    <span class="text-[10px] text-secondary uppercase font-bold tracking-wider block mb-2">Fasilitas</span>
                    <?php if ($fasilitasList): ?>
                    <div class="grid grid-cols-2 gap-2">
                        <?php foreach ($fasilitasList as $fas): ?>
                        <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                            <span class="material-symbols-outlined text-primary text-base">check_circle</span>
                            <span><?= e($fas) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-xs text-secondary">Belum ada data fasilitas.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- PILIH TANGGAL -->
            <div class="mx-4 mt-4 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">calendar_month</span>
                        <h3 class="text-base font-extrabold text-on-surface">Pilih Tanggal Main</h3>
                    </div>
                    <span class="text-xs font-semibold text-primary bg-primary/10 px-3 py-1 rounded-full" id="bulanLabel"><?= e($bulanDari($tanggalPilih)) ?></span>
                </div>
                <div class="flex items-center gap-2 overflow-x-auto py-1 hide-scrollbar">
                    <?php foreach ($tanggalList as $t): ?>
                    <label class="date-option flex flex-col items-center justify-center min-w-[52px] py-3 px-2 rounded-xl cursor-pointer transition-all active:scale-95 bg-surface-lowest shadow-sm text-on-surface hover:bg-surface-container-low">
                        <input type="radio" name="tanggal" value="<?= e($t['val']) ?>" form="bookingForm"
                               data-bulan="<?= e($bulanDari($t['val'])) ?>"
                               <?= $t['val'] === $tanggalPilih ? 'checked' : '' ?> class="hidden">
                        <span class="date-day text-[10px] font-semibold uppercase tracking-wider text-secondary"><?= e($t['hari']) ?></span>
                        <span class="text-base font-bold leading-none mt-1"><?= e($t['tgl']) ?></span>
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
                    <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-slate-200"></span><span>Penuh / lewat</span></div>
                </div>

                <form action="checkout.php" method="get" id="bookingForm">
                    <input type="hidden" name="id" value="<?= (int) $lapangan['id'] ?>">
                    <div class="grid grid-cols-3 gap-2" id="slotList" aria-live="polite"></div>
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

            <?php if ($bisaPesan): ?>
            <button type="submit" form="bookingForm" id="btnLanjut"
                class="w-full h-12 bg-primary hover:bg-primary-container text-on-primary rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-md active:scale-95 transition-all">
                Lanjut Pemesanan
                <span class="material-symbols-outlined text-base">arrow_forward</span>
            </button>
            <?php elseif ($user === null): ?>
            <a href="../auth/login.php"
                class="w-full h-12 bg-primary hover:bg-primary-container text-on-primary rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-md">
                Masuk untuk Memesan
                <span class="material-symbols-outlined text-base">login</span>
            </a>
            <?php else: ?>
            <button type="button" disabled
                class="w-full h-12 bg-slate-200 text-slate-500 rounded-xl text-xs font-bold cursor-not-allowed">
                Akun owner tidak bisa memesan
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script>
    (function () {
        'use strict';

        var API         = '../../../backend/api/user/jadwal.php';
        var LAPANGAN_ID = <?= (int) $lapangan['id'] ?>;
        var SLOT_AWAL   = <?= json_encode($slots, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

        var $      = function (id) { return document.getElementById(id); };
        var list   = $('slotList');
        var total  = $('totalHarga');
        var durasi = $('durasiNum');
        var reqId  = 0;

        function esc(s) {
            return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }
        function rupiah(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); }
        function fmtDurasi(m) {
            var j = Math.floor(m / 60), s = m % 60;
            if (j > 0 && s > 0) return j + ' Jam ' + s + ' Menit';
            if (j > 0) return j + ' Jam';
            return s + ' Menit';
        }
        function toast(msg) {
            var t = document.createElement('div');
            t.className = 'mini-toast';
            t.setAttribute('role', 'status');
            t.textContent = msg;
            document.body.appendChild(t);
            setTimeout(function () { t.remove(); }, 2600);
        }

        // ---------- Slot ----------
        function slotHtml(s) {
            var kunci = s.status !== 'available';
            var info  = s.status === 'lewat' ? 'Lewat' : (kunci ? 'Penuh' : rupiah(s.harga));
            var cls   = kunci ? 'bg-slate-100 opacity-60' : 'bg-surface-lowest shadow-sm hover:shadow-md cursor-pointer';

            return '<label class="slot-card rounded-xl p-3 text-center transition-all ' + cls + '"' +
                   (kunci ? ' style="pointer-events:none"' : '') + '>' +
                '<input type="checkbox" name="slot[]" value="' + esc(s.jam) + '" class="hidden cb-slot"' +
                    ' data-nominal="' + Number(s.harga) + '" data-menit="' + Number(s.menit) + '"' + (kunci ? ' disabled' : '') + '>' +
                '<div class="flex flex-col items-center gap-1">' +
                    (kunci
                        ? '<span class="material-symbols-outlined text-lg text-slate-400">lock</span>'
                        : '<span class="material-symbols-outlined text-lg text-primary ic-off">schedule</span>' +
                          '<span class="material-symbols-outlined text-lg ic-on">check_circle</span>') +
                    '<span class="slot-time text-xs font-bold ' + (kunci ? 'text-slate-400 line-through' : 'text-on-surface') + '">' + esc(s.jam) + '</span>' +
                    '<span class="slot-price text-[10px] ' + (kunci ? 'text-slate-400' : 'text-secondary') + '">' + esc(info) + '</span>' +
                    (s.prime && !kunci ? '<span class="text-[9px] bg-amber-100 text-amber-700 font-bold px-1.5 py-0.5 rounded-full mt-0.5">Prime Time</span>' : '') +
                '</div></label>';
        }

        function pesanGrid(teks, merah) {
            list.innerHTML = '<p class="col-span-3 text-center text-xs py-6 ' + (merah ? 'text-red-600' : 'text-secondary') + '">' + esc(teks) + '</p>';
        }

        function hitung() {
            var sum = 0, menit = 0;
            list.querySelectorAll('.cb-slot:checked').forEach(function (c) {
                sum   += parseInt(c.dataset.nominal, 10) || 0;
                menit += parseInt(c.dataset.menit, 10) || 0;
            });
            total.textContent  = rupiah(sum);
            durasi.textContent = fmtDurasi(menit);
        }

        function render(slots) {
            if (!slots.length) pesanGrid('Belum ada jadwal untuk lapangan ini.', false);
            else list.innerHTML = slots.map(slotHtml).join('');
            hitung();
        }

        list.addEventListener('change', hitung);

        // ---------- Ganti tanggal: muat ulang slot dari server ----------
        function muatTanggal(tgl, bulan) {
            var my = ++reqId;
            if (bulan) $('bulanLabel').textContent = bulan;
            pesanGrid('Memuat jadwal...', false);
            hitung();

            fetch(API + '?id=' + LAPANGAN_ID + '&tanggal=' + encodeURIComponent(tgl), { credentials: 'same-origin' })
                .then(function (res) { return res.json(); })
                .then(function (r) {
                    if (my !== reqId) return;               // ada permintaan yang lebih baru
                    if (!r.success) throw new Error(r.message);
                    render(r.slots);
                    history.replaceState(null, '', '?id=' + LAPANGAN_ID + '&tanggal=' + encodeURIComponent(tgl));
                })
                .catch(function (err) {
                    if (my !== reqId) return;
                    pesanGrid('Gagal memuat jadwal. ' + (err.message || ''), true);
                });
        }

        document.querySelectorAll('input[name="tanggal"]').forEach(function (r) {
            r.addEventListener('change', function () { muatTanggal(r.value, r.dataset.bulan); });
        });

        // Cegah lanjut tanpa memilih slot
        $('bookingForm').addEventListener('submit', function (e) {
            if (!list.querySelector('.cb-slot:checked')) {
                e.preventDefault();
                toast('Pilih minimal satu jam main.');
            }
        });

        // ---------- Tombol kembali & accordion ----------
        $('btnBack').addEventListener('click', function () {
            if (window.history.length > 1) window.history.back();
            else window.location.href = 'beranda.php';
        });

        var accToggle = $('accordion-toggle'), accContent = $('accordion-content'), accArrow = $('accordion-arrow');
        accToggle.addEventListener('click', function () {
            var tersembunyi = accContent.classList.toggle('hidden');
            accArrow.classList.toggle('rotate-180', !tersembunyi);
        });

        // Tampilan awal memakai data yang sudah dibawa server
        render(SLOT_AWAL);
    })();
    </script>
    <script src="../../assets/js/fav-toggle.js"></script>
</body>
</html>