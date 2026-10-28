<?php
require __DIR__ . '/../../../backend/core/auth.php';
requireLogin('user');          // tamu diarahkan ke login, owner ke dashboard-nya
$user = currentUser();
$pageTitle = 'Favorit';
$activeNav = 'favorit';
$extraJs   = ['../../assets/js/kartu.js', '../../assets/js/fav-toggle.js', '../../assets/js/favorit.js'];
?>
<?php include '../../components/header.php'; ?>
<?php include '../../components/navbar.php'; ?>

<main class="container py-3">

  <div class="section-head mb-3">
    <h2>Lapangan Favorit</h2>
    <small class="text-secondary" id="favInfo">Memuat...</small>
  </div>

  <div class="row g-3" id="courtList"
       data-api="../../../backend/api/user/lapangan.php?favorit=1"
       data-img="../../gambar.php?f="
       data-detail="detail_jadwal.php"></div>

</main>

<?php include '../../components/footer.php'; ?>