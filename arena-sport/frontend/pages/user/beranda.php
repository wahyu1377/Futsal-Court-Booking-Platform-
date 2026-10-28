<?php
require __DIR__ . '/../../../backend/core/auth.php';
$user = currentUser();            // null kalau tamu
$pageTitle = 'Beranda';
$activeNav = 'beranda';
$extraJs   = ['../../assets/js/kartu.js', '../../assets/js/fav-toggle.js', '../../assets/js/beranda.js'];
?>
<?php include '../../components/header.php'; ?>
<?php include '../../components/navbar.php'; ?>

<main class="container py-3">

<div class="offcanvas offcanvas-end filter-panel" tabindex="-1" id="filterPanel" aria-labelledby="filterTitle">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title fw-bold" id="filterTitle">Filter Lapangan</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
  </div>

  <div class="offcanvas-body">
    <form id="filterForm" onsubmit="return false">

      <div class="mb-4">
        <label class="f-label" for="fUrut">Urutkan</label>
        <select id="fUrut" class="form-select f-field">
          <option value="rating">Rating tertinggi</option>
          <option value="termurah">Tarif termurah</option>
          <option value="termahal">Tarif termahal</option>
          <option value="ulasan">Ulasan terbanyak</option>
        </select>
      </div>

      <div class="mb-4">
        <label class="f-label">Tarif per jam (Rp)</label>
        <div class="d-flex gap-2 align-items-center">
          <input type="number" id="fMin" class="form-control f-field" min="0" step="5000" inputmode="numeric" placeholder="Terendah">
          <span class="text-secondary">-</span>
          <input type="number" id="fMax" class="form-control f-field" min="0" step="5000" inputmode="numeric" placeholder="Tertinggi">
        </div>
        <div class="f-err" id="fHargaErr"></div>
      </div>

      <div class="mb-4">
        <label class="f-label">Rating minimal</label>
        <div class="d-flex flex-wrap gap-2">
          <input type="radio" class="btn-check" name="fRating" id="r0" value="0" checked><label class="opt" for="r0">Semua</label>
          <input type="radio" class="btn-check" name="fRating" id="r4" value="4"><label class="opt" for="r4">4.0+</label>
          <input type="radio" class="btn-check" name="fRating" id="r45" value="4.5"><label class="opt" for="r45">4.5+</label>
          <input type="radio" class="btn-check" name="fRating" id="r48" value="4.8"><label class="opt" for="r48">4.8+</label>
        </div>
      </div>

      <div class="mb-4">
        <label class="f-label">Fasilitas</label>
        <div class="d-flex flex-wrap gap-2" id="fFasilitas"></div>
      </div>

      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" role="switch" id="fTersedia">
        <label class="form-check-label fw-bold" for="fTersedia">Hanya yang tersedia hari ini</label>
      </div>

    </form>
  </div>

  <div class="filter-foot d-flex gap-2">
    <button type="button" class="btn btn-reset" id="fReset">Reset</button>
    <button type="button" class="btn btn-main flex-grow-1" id="fApply">Tampilkan Hasil</button>
  </div>
</div>

  <!-- Lokasi -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <button class="loc-pill"><i class="bi bi-geo-alt-fill"></i>Jakarta Selatan<i class="bi bi-chevron-down small"></i></button>
  </div>

  <!-- Pencarian -->
  <div class="d-flex gap-2 mb-3">
    <div class="search-box">
      <i class="bi bi-search"></i>
      <input type="search" id="searchInput" placeholder="Cari nama lapangan, jenis olahraga, atau area" autocomplete="off">
    </div>
    <button type="button" class="filter-btn" data-bs-toggle="offcanvas" data-bs-target="#filterPanel" aria-label="Filter">
    <i class="bi bi-sliders"></i><span class="filter-dot d-none" id="filterCount">0</span>
</button>
  </div>

  <!-- Chip kategori (diisi JS) -->
  <div class="chips mb-3" id="chips"></div>

  <!-- Banner promo -->
  <section class="promo mb-4">
    <span class="lbl"><i class="bi bi-moon-stars"></i> PROMO MAIN MALAM</span>
    <h2>Diskon 20% Sewa Lapangan</h2>
    <p>Berlaku tiap hari pukul 21:00 - 24:00 WIB untuk slot indoor.</p>
    <a href="<?= $user ? '#' : '../auth/login.php' ?>" class="btn btn-mint"><i class="bi bi-ticket-perforated me-1"></i> Klaim Voucher</a>
  </section>

  <!-- Rekomendasi -->
  <div class="section-head d-flex justify-content-between align-items-end mb-3">
    <div>
      <h2>Rekomendasi Lapangan Terdekat</h2>
      <small class="text-secondary">Berdasarkan lokasi kamu saat ini</small>
    </div>
    <a href="pencarian.php" class="link-green">Lihat Semua <i class="bi bi-arrow-right"></i></a>
  </div>

  <!-- Daftar kartu (diisi JS) -->
    <div id="resultInfo" class="small text-secondary mb-2"></div>
    <div class="row g-3" id="courtList"
       data-api="../../../backend/api/user/lapangan.php"
       data-img="../../gambar.php?f="
       data-detail="detail_jadwal.php"></div>

</main>

<?php include '../../components/footer.php'; ?>