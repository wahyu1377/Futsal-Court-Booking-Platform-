<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../backend/config/app.php';
require_once __DIR__ . '/../../../backend/config/helpers.php';
require_once __DIR__ . '/../../../backend/config/models/DashboardMitra.php';

require_owner();

$mitraId = (int) ($_SESSION['user_id'] ?? 0);
$data    = DashboardMitra::ambil($mitraId);

$assets       = '../../assets';   // dari frontend/pages/venue/ ke frontend/assets/
$halamanAktif = 'beranda';

$r  = $data['ringkasan'];
$rt = $data['reservasi_terkini'];

/** Tanda panah + angka untuk tren, mis. "+3" / "-2" */
function tren(int|float $n, string $akhiran = ''): string
{
    return ($n >= 0 ? '+' : '−') . rtrim(rtrim(number_format(abs($n), 1, ',', '.'), '0'), ',') . $akhiran;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Beranda Mitra - Arena Sport</title>
  <?php include __DIR__ . '/../../components/head-assets.php'; ?>
</head>
<body class="mitra font-sans">

<?php include __DIR__ . '/../../components/header-mitra.php'; ?>

<main class="pb-28 lg:ml-64 lg:pb-10">
  <div class="mx-auto max-w-6xl space-y-5 px-4 py-4">

    <!-- Sapaan -->
    <section class="flex items-center justify-between gap-4 rounded-3xl bg-white p-5 shadow-card">
      <div class="min-w-0">
        <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1 text-sm font-semibold text-brand-700">
          <i class="bi bi-patch-check"></i> <?= e($data['mitra']['brand']) ?> – Mitra
        </span>
        <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-ink">Halo, <?= e($data['mitra']['nama']) ?>!</h2>
        <p class="mt-1 text-slate-500"><?= e($data['mitra']['status']) ?> • <?= e($data['mitra']['brand']) ?></p>
      </div>
      <div class="relative shrink-0">
        <img src="<?= e($assets) ?>/img/avatar.jpg" alt="" class="h-24 w-24 rounded-full border-4 border-brand-100 object-cover object-top shadow-card">
        <span class="absolute bottom-0 right-0 grid h-7 w-7 place-items-center rounded-full border-4 border-white bg-brand-600 text-white">
          <i class="bi bi-circle-fill text-[7px]"></i>
        </span>
      </div>
    </section>

    <!-- CTA -->
    <a href="../owner/tambah_lapangan.php"
       class="flex items-center justify-center gap-3 rounded-2xl bg-brand-800 px-4 py-5 text-xl font-bold text-white shadow-[0_8px_20px_rgba(6,95,70,.25)] transition hover:brightness-110">
      <i class="bi bi-plus-circle text-2xl"></i> Tambah Lapangan Baru
    </a>

    <!-- Ringkasan -->
    <section class="grid grid-cols-3 gap-3" aria-label="Ringkasan hari ini">
      <article class="rounded-2xl bg-white p-3 shadow-card sm:p-5">
        <div class="flex items-start justify-between gap-1">
          <p class="text-xs font-medium leading-4 text-slate-500 sm:text-sm">Pesanan Hari Ini</p>
          <i class="bi bi-dribbble text-lg text-brand-700"></i>
        </div>
        <p class="mt-2 text-3xl font-extrabold text-ink"><?= e($r['pesanan_hari_ini']) ?></p>
        <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-brand-50 px-2 py-1 text-[11px] font-bold text-brand-700 sm:text-xs">
          <i class="bi bi-graph-up-arrow"></i> <?= e(tren($r['selisih_kemarin'])) ?> vs kemarin
        </span>
      </article>

      <article class="rounded-2xl bg-white p-3 shadow-card sm:p-5">
        <div class="flex items-start justify-between gap-1">
          <p class="text-xs font-medium leading-4 text-slate-500 sm:text-sm">Menunggu Bayar</p>
          <i class="bi bi-calendar2-x text-lg text-red-600"></i>
        </div>
        <p class="mt-2 text-3xl font-extrabold text-ink"><?= e($r['menunggu_bayar']) ?></p>
        <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-1 text-[11px] font-bold text-red-700 sm:text-xs">
          <i class="bi bi-exclamation-lg"></i> Perlu konfirmasi
        </span>
      </article>

      <article class="rounded-2xl bg-white p-3 shadow-card sm:p-5">
        <div class="flex items-start justify-between gap-1">
          <p class="text-xs font-medium leading-4 text-slate-500 sm:text-sm">Bulan Ini</p>
          <i class="bi bi-cash-stack text-lg text-brand-700"></i>
        </div>
        <p class="mt-2 text-xl font-extrabold text-ink sm:text-3xl"><?= e(rupiahRingkas($r['pendapatan_bulan'])) ?></p>
        <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-brand-50 px-2 py-1 text-[11px] font-bold text-brand-700 sm:text-xs">
          <i class="bi bi-arrow-<?= $r['persen_bulan_lalu'] >= 0 ? 'up' : 'down' ?>"></i> <?= e(tren($r['persen_bulan_lalu'], '%')) ?>
        </span>
      </article>
    </section>

    <!-- Reservasi terkini -->
    <section class="flex gap-4 rounded-3xl bg-tint p-4 shadow-card" aria-label="Reservasi terkini">
      <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-white/70 text-2xl text-brand-700"><i class="bi bi-bell"></i></span>
      <div class="min-w-0 flex-1">
        <div class="flex items-start justify-between gap-2">
          <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Reservasi Terkini</p>
          <p class="shrink-0 text-sm font-semibold text-slate-500"><?= e($rt['waktu']) ?></p>
        </div>
        <h3 class="mt-1 text-xl font-bold text-ink"><?= e($rt['nama']) ?></h3>
        <p class="text-slate-500"><?= e($rt['lapangan']) ?> (<?= e($rt['jam']) ?>) • <?= e(rupiah($rt['total'])) ?></p>
        <div class="mt-2 flex items-center justify-between gap-2">
          <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-sm font-semibold text-brand-700">
            <i class="bi bi-check-circle"></i> <?= e($rt['status']) ?> / <?= e($rt['metode']) ?>
          </span>
          <a href="../user/tiket.php?kode=<?= e(rawurlencode($rt['kode_tiket'])) ?>" class="text-sm font-semibold text-slate-600 underline underline-offset-4 hover:text-brand-700">Lihat Tiket</a>
        </div>
      </div>
    </section>

    <!-- Daftar lapangan -->
    <section aria-labelledby="judul-lapangan">
      <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <h2 id="judul-lapangan" class="text-2xl font-extrabold tracking-tight text-ink">Daftar Lapangan Anda</h2>
        <div class="flex items-center gap-3">
          <span class="rounded-full bg-tint px-3 py-1 text-sm font-semibold text-slate-600"><?= count($data['lapangan']) ?> Aktif</span>
          <a href="venue.php" class="inline-flex items-center gap-1 font-bold text-brand-700 hover:text-brand-800">Kelola Semua <i class="bi bi-chevron-right"></i></a>
        </div>
      </div>

      <div class="grid gap-5 md:grid-cols-2">
        <?php foreach ($data['lapangan'] as $l):
            $buka = $l['slot_tersedia'] > 0; ?>
          <article class="rounded-3xl bg-white shadow-card">
            <div class="relative overflow-hidden rounded-t-3xl">
              <img src="<?= e($assets) ?>/img/<?= e($l['foto']) ?>" alt="Foto <?= e($l['nama']) ?>" class="block aspect-[2/1] w-full object-cover">
              <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1 text-sm font-semibold text-ink shadow-sm">
                <i class="bi <?= e($l['ikon']) ?> text-brand-700"></i> <?= e($l['jenis']) ?> • <?= e($l['tipe']) ?>
              </span>
              <span class="absolute right-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1 text-sm font-semibold shadow-sm <?= $buka ? 'text-brand-700' : 'text-red-600' ?>">
                <i class="bi bi-circle-fill text-[8px]"></i>
                <?= $buka ? 'Buka (' . e($l['slot_tersedia']) . ' Slot Tersedia)' : 'Penuh' ?>
              </span>
            </div>

            <div class="p-5">
              <div class="flex items-start justify-between gap-3">
                <h3 class="min-w-0 text-lg font-bold leading-snug text-ink"><?= e($l['nama']) ?></h3>
                <div class="shrink-0 text-right">
                  <p class="text-lg font-extrabold text-brand-600"><?= e(rupiah($l['harga_per_jam'])) ?></p>
                  <p class="text-sm italic text-slate-500">/ jam</p>
                </div>
              </div>
              <p class="mt-1 flex items-center gap-1.5 text-slate-500">
                <i class="bi bi-geo-alt"></i> <?= e($l['zona']) ?> • <?= e($l['standar']) ?>
              </p>

              <div class="mt-4 flex items-center gap-2">
                <a href="jadwal.php?lapangan=<?= e($l['id']) ?>"
                   class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-tint py-3 font-bold text-ink transition hover:bg-[#D5DEF5]">
                  <i class="bi bi-calendar3"></i> Atur Jadwal
                </a>
                <div class="dropdown">
                  <button type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Opsi <?= e($l['nama']) ?>"
                          class="grid h-12 w-12 place-items-center rounded-xl bg-tint text-xl text-ink transition hover:bg-[#D5DEF5]">
                    <i class="bi bi-three-dots-vertical"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="edit-lapangan.php?id=<?= e($l['id']) ?>"><i class="bi bi-pencil me-2"></i>Edit lapangan</a></li>
                    <li><a class="dropdown-item" href="tarif.php?lapangan=<?= e($l['id']) ?>"><i class="bi bi-tag me-2"></i>Atur tarif</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-slash-circle me-2"></i>Nonaktifkan</a></li>
                  </ul>
                </div>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

  </div>
</main>

<?php include __DIR__ . '/../../components/nav-mitra.php'; ?>

<!-- Bootstrap JS: hanya untuk komponen dropdown -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
