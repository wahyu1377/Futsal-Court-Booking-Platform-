<?php
require_once __DIR__ . '/helper_profil.php';   // e(), avatarSrc()
$user      = $user ?? null;
$activeNav = $activeNav ?? '';
?>

<?php $activeNav = $activeNav ?? ''; ?>

<!-- BAR ATAS -->
<header class="topbar">
  <div class="container d-flex align-items-center justify-content-between gap-3">
    <a href="beranda.php" class="d-flex align-items-center gap-2 text-decoration-none">
      <img src="../../assets/img/logo.png" alt="Arena Sport" class="topbar-logo">
      <div class="lh-1 d-lg-none">
        <small class="brand-small">ARENA SPORT</small>
        <div class="fw-bold fs-5 text-dark"><?= htmlspecialchars($pageTitle ?? 'Beranda') ?></div>
      </div>
    </a>

    <!-- Menu desktop -->
    <nav class="d-none d-lg-flex gap-4 me-auto ms-4">
      <a class="top-link <?= $activeNav === 'beranda' ? 'active' : '' ?>" href="beranda.php">Beranda</a>
      <?php if ($user && $user['role'] === 'user'): ?>
        <a class="top-link <?= $activeNav === 'pesanan' ? 'active' : '' ?>" href="tiket.php">Pesanan</a>
      <?php endif; ?>
    </nav>

    <div class="d-flex align-items-center gap-3">
      <button class="icon-btn" aria-label="Notifikasi"><i class="bi bi-bell"></i></button>
      <?php if ($user): ?>
        <a href="<?= $user['role'] === 'owner' ? '../owner/dashboard.php' : 'akun.php' ?>" class="avatar" title="<?= e($user['name']) ?>">
        <img src="<?= e(avatarSrc($user['name'], $user['avatar'] ?? null)) ?>" alt="Akun <?= e($user['name']) ?>">
        </a>
      <?php else: ?>
        <a href="../auth/login.php" class="btn btn-sm btn-main-sm">Masuk</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<!-- NAVIGASI BAWAH (hanya HP) -->
<nav class="bottom-nav d-lg-none">
  <a href="beranda.php" class="<?= $activeNav === 'beranda' ? 'active' : '' ?>"><i class="bi bi-dribbble"></i><span>Beranda</span></a>
  <a href="#" class="<?= $activeNav === 'favorit' ? 'active' : '' ?>"><i class="bi bi-bookmark"></i><span>Favorit</span></a>
  <a href="<?= $user ? 'tiket.php' : '../auth/login.php' ?>" class="<?= $activeNav === 'pesanan' ? 'active' : '' ?>"><i class="bi bi-ticket-perforated"></i><span>Pesanan</span></a>
  <a href="<?= $user ? ($user['role'] === 'owner' ? '../owner/dashboard.php' : 'akun.php') : '../auth/login.php' ?>" class="<?= $activeNav === 'akun' ? 'active' : '' ?>"><i class="bi bi-person-circle"></i><span>Akun</span></a>
</nav>