<?php
/**
 * Dashboard owner - daftar lapangan milik owner yang sedang login.
 *
 * Halaman ini memakai komponen header/footer yang sudah ada, dan data
 * lapangan diambil lewat API (backend/api/owner/lapangan.php) memakai
 * Vanilla JS supaya tidak perlu reload halaman saat menambah/mengubah data.
 */
require __DIR__ . '/../../../backend/core/auth.php';
requireLogin('owner');

$user        = currentUser();
$pageTitle   = 'Dashboard Owner';
$useTailwind = true;
$extraJs     = '../../assets/js/owner-lapangan.js';
?>
<?php include '../../components/header.php'; ?>
<link href="../../assets/css/owner.css" rel="stylesheet">

<!-- Bar atas -->
<div class="owner-bar">
  <div class="container d-flex align-items-center gap-3 py-2">
    <a href="../user/beranda.php" class="icon-back" aria-label="Kembali ke beranda">
      <i class="bi bi-arrow-left"></i>
    </a>
    <div class="lh-sm">
      <small class="text-muted" style="font-size:.72rem;font-weight:700;letter-spacing:.04em">ARENA SPORT</small>
      <div class="fw-bold" style="line-height:1.1">Dashboard Owner</div>
    </div>
    <div class="ms-auto d-flex align-items-center gap-2">
      <a href="tambah_lapangan.php" class="btn btn-main btn-sm d-inline-flex align-items-center gap-1">
        <i class="bi bi-plus-lg"></i><span class="d-none d-sm-inline">Tambah</span>
      </a>
      <a href="../../pages/user/akun.php" class="avatar" title="<?= htmlspecialchars($user['name'] ?? '') ?>">
        <?= htmlspecialchars(mb_strtoupper(mb_substr($user['name'] ?? 'O', 0, 1))) ?>
      </a>
    </div>
  </div>
</div>

<main class="container py-3">

  <!-- Ringkasan -->
  <div class="row g-3 mb-3">
    <div class="col-4">
      <div class="stat-card">
        <div class="label">LAPANGAN</div>
        <div class="value" id="statTotal">0</div>
      </div>
    </div>
    <div class="col-4">
      <div class="stat-card">
        <div class="label">AKTIF</div>
        <div class="value" id="statAktif">0</div>
      </div>
    </div>
    <div class="col-4">
      <div class="stat-card">
        <div class="label">RATA-RATA TARIF</div>
        <div class="value" id="statTarif">Rp0</div>
      </div>
    </div>
  </div>

  <!-- Judul + tombol tambah (desktop) -->
  <div class="d-flex justify-content-between align-items-end mb-3">
    <div>
      <h2 class="fw-bold mb-1" style="font-size:1.15rem">Lapangan Saya</h2>
      <small class="text-muted">Kelola data lapangan yang Anda sewakan</small>
    </div>
    <a href="tambah_lapangan.php" class="btn btn-main d-none d-sm-inline-flex align-items-center gap-1">
      <i class="bi bi-plus-lg"></i> Tambah Lapangan
    </a>
  </div>

  <!-- Daftar kartu lapangan (diisi oleh JS) -->
  <div id="ownerList" class="row g-3"
       data-api="../../../backend/api/owner/lapangan.php"
       data-img="../../assets/img/lapangan/"></div>

</main>

<!-- Modal konfirmasi hapus (komponen interaktif -> Bootstrap) -->
<div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border:0;border-radius:20px">
      <div class="modal-body text-center p-4">
        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
             style="width:56px;height:56px;border-radius:999px;background:#fdecec;color:#b3141b">
          <i class="bi bi-trash" style="font-size:1.5rem"></i>
        </div>
        <h5 class="fw-bold mb-1" id="modalHapusLabel">Hapus lapangan?</h5>
        <p class="text-secondary small mb-0" id="modalHapusTeks">
          Data lapangan akan dihapus permanen dan tidak bisa dikembalikan.
        </p>
      </div>
      <div class="modal-footer border-0 pt-0 px-4 pb-4">
        <button type="button" class="btn btn-lembut flex-grow-1" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger flex-grow-1 fw-bold" id="btnHapusYa">Ya, Hapus</button>
      </div>
    </div>
  </div>
</div>

<!-- Kartu notifikasi (muncul setelah tambah / ubah / hapus) -->
<div id="notifCard" class="notif-card d-none" role="status" aria-live="polite">
  <div class="notif-ikon" id="notifIkon"><i class="bi bi-check-lg"></i></div>
  <div class="notif-isi">
    <strong id="notifJudul">Berhasil</strong>
    <small id="notifPesan">Data lapangan sudah tersimpan.</small>
  </div>
  <button type="button" class="notif-tutup" data-tutup-notif aria-label="Tutup notifikasi">
    <i class="bi bi-x-lg"></i>
  </button>
</div>

<?php include '../../components/footer.php'; ?>
