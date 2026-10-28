<?php
/**
 * Halaman Checkout Booking - Arena Sport
 * Arena Sport Pulse Design System
 *
 * Path: frontend/pages/user/checkout.php
 */

$pageTitle = 'Checkout Booking';

require_once dirname(__DIR__, 3) . '/backend/core/auth.php';          // database + session + currentUser()
require_once dirname(__DIR__, 3) . '/backend/core/harga.php';
require_once dirname(__DIR__, 3) . '/backend/core/jadwal.php';
require_once dirname(__DIR__, 3) . '/backend/core/booking.php';       // HOLD_MENIT dan fungsi pesanan
require_once dirname(__DIR__, 2) . '/components/helper_profil.php';   // e(), avatarSrc()

requireLogin('user');   // checkout hanya untuk pengguna yang sudah login
$user = currentUser();

// ============================================================
// DATA KONTAK: dari akun yang sedang login
// ============================================================
$stKontak = db()->prepare('SELECT full_name, email, phone FROM users WHERE id = ?');
$stKontak->execute([(int) $user['id']]);
$kontak   = $stKontak->fetch() ?: ['full_name' => $user['name'], 'email' => null, 'phone' => null];
$hpTampil = $kontak['phone'] ? rtrim(chunk_split($kontak['phone'], 4, '-'), '-') : null;   // 0812-3456-7890

// ============================================================
// AMBIL DATA DARI DETAIL JADWAL
// ============================================================
$lapanganId    = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
$slotsTerpilih = isset($_GET['slot']) ? (array) $_GET['slot'] : [];
$tanggalPilih  = is_string($_GET['tanggal'] ?? null) ? $_GET['tanggal'] : '';

// ============================================================
// VALIDASI INPUT (Cegah manipulasi dari address bar)
// ============================================================
// Lapangan, tanggal, dan slot dicek ulang di server memakai jadwal.php,
// sumber yang sama dengan halaman detail dan API booking.
$pesanError = '';
$lapangan   = null;
$slotValid  = null;

try {
    $pdo      = db();
    $lapangan = $lapanganId > 0 ? ambilLapangan($pdo, $lapanganId) : null;

    if (!$lapangan) {
        $pesanError = 'Lapangan tidak ditemukan atau sudah tidak aktif.';
    } elseif (!tanggalBisaDipesan($tanggalPilih)) {
        $pesanError = 'Tanggal main tidak valid atau di luar 7 hari ke depan.';
    } else {
        $slotValid = validasiSlot($pdo, $lapangan, $tanggalPilih, $slotsTerpilih);
        if ($slotValid === null) {
            $pesanError = 'Slot yang dipilih tidak valid, sudah dipesan, atau tidak tersedia. Silakan pilih ulang slot pada halaman jadwal.';
        }
    }
} catch (PDOException $e) {
    error_log('Checkout gagal: ' . $e->getMessage());
    $pesanError = 'Terjadi kesalahan pada server. Silakan coba lagi.';
}

if ($pesanError === '') {
    $slotsTerpilih = array_column($slotValid, 'jam');
}

// Tampilkan halaman error dan hentikan proses bila input tidak valid
if ($pesanError !== '') {
    http_response_code(400);
    ?>

    
    <!DOCTYPE html>
    <html lang="id">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>Slot tidak valid - Arena Sport</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    </head>
    <body class="bg-light">
      <main class="container py-5" style="max-width: 460px;">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center p-4">
            <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 2.5rem;"></i>
            <h1 class="h5 fw-bold mt-3 mb-2">Slot tidak valid</h1>
            <p class="text-secondary small mb-4"><?= htmlspecialchars($pesanError) ?></p>
            <a href="detail_jadwal.php?id=<?= (int) $lapanganId ?>" class="btn btn-primary w-100">
              <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Jadwal
            </a>
          </div>
        </div>
      </main>
    </body>
    </html>
    <?php
    exit;
}

// ============================================================
// BIAYA TAMBAHAN & BIAYA LAYANAN (satu sumber: harga.php)
// ============================================================
$HARGA_ROMPI   = HARGA_ROMPI;
$HARGA_WASIT   = HARGA_WASIT;
$BIAYA_LAYANAN = BIAYA_LAYANAN;

// ============================================================
// HITUNG DURASI DAN TOTAL
// ============================================================
// Harga tiap slot sudah dihitung di jadwal.php dari harga_per_jam lapangan
$jumlahSlot = count($slotValid);
$totalHarga = (int) array_sum(array_column($slotValid, 'harga'));
$durasiTeks = formatDurasi((int) array_sum(array_column($slotValid, 'menit')));

$jamAwal    = explode(' - ', $slotValid[0]['jam'])[0];
$jamAkhir   = preg_replace('/\s*WIB$/', '', explode(' - ', $slotValid[array_key_last($slotValid)]['jam'])[1] ?? '');
$jamDisplay = $jamAwal . ' - ' . $jamAkhir . ' WIB';

$hariIndonesia  = ['Sun'=>'Min','Mon'=>'Sen','Tue'=>'Sel','Wed'=>'Rab','Thu'=>'Kam','Fri'=>'Jum','Sat'=>'Sab'];
$bulanIndonesia = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
$tanggalObj     = DateTime::createFromFormat('Y-m-d', $tanggalPilih);
$tanggalDisplay = $hariIndonesia[$tanggalObj->format('D')] . ', ' . $tanggalObj->format('j') . ' '
                . $bulanIndonesia[(int) $tanggalObj->format('n')] . ' ' . $tanggalObj->format('Y');

// Foto lapangan dari database (folder yang sama dengan beranda dan halaman detail)
$fotoUrl = null;
$foto    = (string) ($lapangan['foto'] ?? '');
if ($foto !== '' && !str_contains($foto, '..') && !str_contains($foto, '\\')) {
    $fotoUrl = '../../assets/img/lapangan/' . implode('/', array_map('rawurlencode', explode('/', $foto)));
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
// Pilihan QRIS / Virtual Account / e-wallet dilakukan di halaman DOKU.
// Di sini pengguna hanya memilih bayar lunas atau DP.
// ============================================================
$metodeBayar = [
    ['value'=>'online','icon'=>'payments','label'=>'Bayar Lunas Online','badge'=>'DOKU','type'=>'success','desc'=>'Pilih QRIS, Virtual Account, atau metode lain di halaman DOKU'],
    ['value'=>'onsite','icon'=>'storefront','label'=>'DP ' . DP_PERSEN . '%, Sisa di Kasir','badge'=>'DP ' . DP_PERSEN . '%','type'=>'warning','desc'=>'DP dibayar online, sisanya dilunasi di kasir lapangan'],
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php /* Microsoft Clarity (pelacakan halaman) */ ?>
    <?php include dirname(__DIR__, 2) . '/components/clarity.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?= e($pageTitle) ?> - Arena Sport</title>

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
            'on-primary-fixed': '#002113',
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
                <button id="btnBack" type="button" aria-label="Kembali" class="w-10 h-10 flex items-center justify-center rounded-full text-on-surface hover:bg-surface-container-high transition-colors">
                    <span class="material-symbols-outlined text-2xl">arrow_back</span>
                </button>
                <img alt="Logo" class="h-7 w-auto object-contain" src="../../assets/img/logo.png">
                <h1 class="font-bold text-on-surface">Checkout</h1>
            </div>
            <div class="flex items-center gap-1">
                <a href="akun.php" class="w-10 h-10 flex items-center justify-center rounded-full" title="<?= e($user['name']) ?>">
                    <img src="<?= e(avatarSrc($user['name'], $user['avatar'] ?? null)) ?>" alt="Akun <?= e($user['name']) ?>" class="w-9 h-9 rounded-full object-cover border-2 border-white shadow-sm">
                </a>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="flex flex-col relative w-full pt-16 pb-44 bg-surface min-h-screen">
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
                <div class="relative w-full h-36 bg-cover bg-center bg-gradient-to-br from-primary-fixed/40 to-surface-container-high"
                     <?= $fotoUrl ? 'style="background-image: url(\'' . e($fotoUrl) . '\');"' : '' ?>>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                    <div class="absolute top-3 right-3">
                        <span class="bg-primary/90 backdrop-blur-sm text-white text-[10px] px-2.5 py-1 rounded-full uppercase tracking-wider font-bold flex items-center gap-1 shadow">
                            <span class="material-symbols-outlined text-xs">verified</span> Ringkasan
                        </span>
                    </div>
                    <div class="absolute bottom-3 left-4 right-4 text-white">
                        <span class="text-[10px] text-primary-fixed uppercase tracking-wider font-bold"><?= e($lapangan['jenis']) ?></span>
                        <h2 class="text-base font-bold truncate"><?= e($lapangan['nama']) ?></h2>
                    </div>
                </div>

                <div class="p-4 flex flex-col gap-2 bg-surface-container-low">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">calendar_today</span>
                        <span class="text-sm font-semibold text-on-surface"><?= e($tanggalDisplay) ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">schedule</span>
                            <span class="text-sm text-on-surface-variant"><?= e($jamDisplay) ?></span>
                        </div>
                        <span class="text-[10px] text-on-surface-variant bg-surface-container px-2.5 py-0.5 rounded-full font-bold">
                            Durasi: <?= e($durasiTeks) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- ACCORDION SECTIONS -->
            <div class="flex flex-col gap-3">

                <!-- Data Kontak (dari akun yang login) -->
                <div class="bg-surface-lowest rounded-xl shadow-sm overflow-hidden" id="acc-kontak">
                    <button type="button" class="w-full p-4 flex items-center justify-between text-left hover:bg-surface-container-low transition-colors" id="btn-kontak" aria-expanded="false">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-lg">person</span>
                            </div>
                            <div class="min-w-0">
                                <span class="text-sm font-bold text-on-surface">Data Kontak</span>
                                <p class="text-xs text-secondary truncate"><?= e($kontak['full_name']) ?><?= $hpTampil ? ' - ' . e($hpTampil) : '' ?></p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-secondary text-xl transition-transform duration-200" id="icon-kontak">expand_more</span>
                    </button>
                    <div class="px-4 pb-4 hidden" id="content-kontak">
                        <div class="p-3 bg-surface-container-low rounded-lg space-y-1 text-sm text-on-surface">
                            <div class="flex justify-between gap-3"><span class="text-secondary">Nama</span><span class="font-semibold text-right"><?= e($kontak['full_name']) ?></span></div>
                            <div class="flex justify-between gap-3"><span class="text-secondary">WhatsApp / HP</span><span class="font-semibold text-right"><?= $hpTampil ? e($hpTampil) : '<span class="text-secondary font-normal">Belum diisi</span>' ?></span></div>
                            <div class="flex justify-between gap-3"><span class="text-secondary">Email</span><span class="font-semibold text-right break-all"><?= $kontak['email'] ? e($kontak['email']) : '<span class="text-secondary font-normal">Belum diisi</span>' ?></span></div>
                        </div>
                        <p class="text-[11px] text-secondary mt-2">Data diambil dari akun Anda dan dipakai untuk konfirmasi pembayaran dan pemesanan.</p>
                    </div>
                </div>

                <!-- Opsi Tambahan -->
                <div class="bg-surface-lowest rounded-xl shadow-sm overflow-hidden" id="acc-tambahan">
                    <button type="button" class="w-full p-4 flex items-center justify-between text-left hover:bg-surface-container-low transition-colors" id="btn-tambahan">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                                <span class="material-symbols-outlined text-lg">sports_soccer</span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-on-surface">Opsi Tambahan</span>
                                    <span class="bg-surface-container text-on-surface-variant text-[10px] px-2 py-0.5 rounded-full font-bold" id="addon-badge">1 Dipilih</span>
                                </div>
                                <p class="text-xs text-secondary">Peralatan &amp; kelengkapan match</p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-secondary text-xl rotate-180 transition-transform duration-200" id="icon-tambahan">expand_more</span>
                    </button>
                    <div class="px-4 pb-4 flex flex-col gap-2" id="content-tambahan">
                        <?php foreach ($opsiTambahan as $opsi): ?>
                        <label class="flex items-start gap-3 p-3 rounded-lg bg-surface-container-low hover:bg-surface-container cursor-pointer transition-colors">
                            <input type="checkbox" name="opsi[]" value="<?= e($opsi['id']) ?>" class="mt-0.5 w-4 h-4 rounded text-primary focus:ring-0 accent-primary cursor-pointer"
                                <?= ($opsi['id'] === 'bola') ? 'checked' : '' ?>
                                id="check-<?= e($opsi['id']) ?>">
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center gap-2">
                                    <span class="text-sm font-bold text-on-surface flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-primary text-base"><?= e($opsi['icon']) ?></span>
                                        <?= e($opsi['label']) ?>
                                    </span>
                                    <?php if ($opsi['harga'] > 0): ?>
                                    <span class="text-primary font-bold text-xs">+Rp <?= number_format($opsi['harga'], 0, ',', '.') ?></span>
                                    <?php else: ?>
                                    <span class="bg-primary-fixed text-on-primary-fixed text-[10px] px-2 py-0.5 rounded-full font-bold uppercase">Gratis</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-secondary mt-0.5"><?= e($opsi['desc']) ?></p>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div class="bg-surface-lowest rounded-xl shadow-sm overflow-hidden" id="acc-bayar">
                    <button type="button" class="w-full p-4 flex items-center justify-between text-left hover:bg-surface-container-low transition-colors" id="btn-bayar">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                                <span class="material-symbols-outlined text-lg">payments</span>
                            </div>
                            <div>
                                <span class="text-sm font-bold text-on-surface">Metode Pembayaran</span>
                                <p class="text-xs text-secondary">Bayar lunas atau DP</p>
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
                            <input type="radio" name="bayar" value="<?= e($metode['value']) ?>" class="mt-0.5 w-4 h-4 text-primary focus:ring-0 accent-primary cursor-pointer"
                                <?= ($metode['value'] === 'online') ? 'checked' : '' ?>
                                id="bayar-<?= e($metode['value']) ?>">
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center gap-2">
                                    <span class="text-sm font-bold text-on-surface flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-primary text-base"><?= e($metode['icon']) ?></span>
                                        <?= e($metode['label']) ?>
                                    </span>
                                    <?php if ($metode['badge']): ?>
                                    <span class="<?= $badgeClass ?> text-[10px] px-2 py-0.5 rounded-full font-bold"><?= e($metode['badge']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-secondary mt-0.5"><?= e($metode['desc']) ?></p>
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
                    <h4 class="text-sm font-bold text-on-surface">Jadwal Ditahan Setelah Pesan</h4>
                    <p class="text-xs text-secondary">Jadwal Anda dikunci selama <?= (int) HOLD_MENIT ?> menit untuk menyelesaikan pembayaran.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- STICKY BOTTOM -->
    <div class="fixed bottom-0 left-0 right-0 bg-surface-lowest shadow-[0_-4px_20px_rgba(0,0,0,0.08)] z-40">
        <div class="max-w-lg mx-auto px-4 pt-3 pb-4 flex flex-col gap-2">
            <div class="flex flex-col gap-1">
                <div class="flex justify-between items-center text-xs text-secondary">
                    <span>Sewa Lapangan (<?= e($durasiTeks) ?>)</span>
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
                </div>
                <div class="flex justify-between items-center text-xs hidden" id="row-dp">
                    <span class="text-primary font-bold">DP dibayar sekarang (<?= (int) DP_PERSEN ?>%)</span>
                    <span class="text-primary font-extrabold" id="dp-amount"></span>
                </div>
            </div>

            <p id="pay-error" class="hidden text-xs font-semibold text-red-700 bg-red-50 rounded-xl p-3" role="alert"></p>

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
            var btn = $(btnId), content = $(contentId), icon = $(iconId);
            if (!btn || !content || !icon) return;
            btn.onclick = function() {
                var tersembunyi = content.classList.toggle('hidden');
                icon.style.transform = tersembunyi ? 'rotate(0deg)' : 'rotate(180deg)';
                btn.setAttribute('aria-expanded', tersembunyi ? 'false' : 'true');
            };
        }
        setupAccordion('btn-kontak', 'content-kontak', 'icon-kontak');     // sebelumnya belum dipasang
        setupAccordion('btn-tambahan', 'content-tambahan', 'icon-tambahan');
        setupAccordion('btn-bayar', 'content-bayar', 'icon-bayar');

        // ============================================================
        // HITUNG TOTAL (tampilan saja; tagihan sebenarnya dihitung ulang di server)
        // ============================================================
        var BASE_PITCH = <?= (int) $totalHarga ?>;
        var SERVICE_FEE = <?= (int) $BIAYA_LAYANAN ?>;
        var HARGA_ROMPI = <?= (int) $HARGA_ROMPI ?>;
        var HARGA_WASIT = <?= (int) $HARGA_WASIT ?>;
        var DP_PERSEN = <?= (int) DP_PERSEN ?>;

        function rp(n) { return 'Rp ' + n.toLocaleString('id-ID'); }

        function hitungTotal() {
            var total = BASE_PITCH + SERVICE_FEE;
            var selectedCount = 1;

            var rompi = $('check-rompi'), wasit = $('check-wasit'), bola = $('check-bola');
            var rowRompi = $('row-rompi'), rowWasit = $('row-wasit');

            if (rompi && rompi.checked) { total += HARGA_ROMPI; selectedCount++; rowRompi.classList.remove('hidden'); }
            else if (rowRompi) { rowRompi.classList.add('hidden'); }

            if (wasit && wasit.checked) { total += HARGA_WASIT; selectedCount++; rowWasit.classList.remove('hidden'); }
            else if (rowWasit) { rowWasit.classList.add('hidden'); }

            if (bola && bola.checked) selectedCount++;

            var metode = document.querySelector('input[name="bayar"]:checked');
            if (metode && metode.value === 'onsite') {
                $('dp-amount').textContent = rp(Math.round(total * DP_PERSEN / 100));
                $('row-dp').classList.remove('hidden');
            } else {
                $('row-dp').classList.add('hidden');
            }

            if ($('addon-badge')) $('addon-badge').textContent = selectedCount + ' Dipilih';
            if ($('grand-total')) $('grand-total').textContent = rp(total);
        }

        ['rompi', 'wasit', 'bola'].forEach(function(id) {
            var el = $('check-' + id);
            if (el) el.addEventListener('change', hitungTotal);
        });
        document.querySelectorAll('input[name="bayar"]').forEach(function(r) {
            r.addEventListener('change', hitungTotal);
        });

        // ============================================================
        // TOMBOL BAYAR: buat pesanan di server, lalu ke halaman pembayaran DOKU
        // ============================================================
        var BOOKING = <?= json_encode(['id' => (int) $lapangan['id'], 'tanggal' => $tanggalPilih, 'slot' => $slotsTerpilih],
                                      JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        var LABEL_BAYAR = 'Lanjut Bayar <span class="material-symbols-outlined text-base">arrow_forward</span>';

        $('btn-pay').onclick = function () {
            var btn = this, err = $('pay-error');
            err.classList.add('hidden');

            var opsi = [];
            ['rompi', 'wasit', 'bola'].forEach(function (id) {
                var el = $('check-' + id);
                if (el && el.checked) opsi.push(id);
            });
            var metode = document.querySelector('input[name="bayar"]:checked');

            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-xl">progress_activity</span><span>Memproses...</span>';

            fetch('../../../backend/api/user/booking.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'same-origin',
                body: JSON.stringify({ aksi: 'buat', lapangan_id: BOOKING.id, tanggal: BOOKING.tanggal,
                                       slot: BOOKING.slot, opsi: opsi, metode: metode ? metode.value : '' })
            })
            .then(function (res) { return res.text().then(function (t) { return { status: res.status, text: t }; }); })
            .then(function (x) {
                var r;
                try { r = JSON.parse(x.text); }
                catch (e) {
                    // Server mengirim HTML (biasanya pesan error PHP), bukan JSON
                    console.error('Balasan booking.php bukan JSON (HTTP ' + x.status + '):', x.text);
                    throw new Error('Server mengirim balasan tidak valid (HTTP ' + x.status + '). Buka Console (F12) untuk melihat isinya.');
                }
                if (r.need_login) { location.href = '../auth/login.php'; return; }
                if (!r.success) throw new Error(r.message);
                location.href = r.redirect;      // halaman DOKU (atau bayar.php bila halaman DOKU belum siap)
            })
            .catch(function (e) {
                err.textContent = e.message || 'Terjadi kesalahan. Coba lagi.';
                err.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = LABEL_BAYAR;
            });
        };

        hitungTotal();
    })();
    </script>
</body>
</html>