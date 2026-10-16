<?php
declare(strict_types=1);
session_start();

/* ---------------------------------------------------------
   DATA — sementara hardcode. Ganti dengan query database
   (PDO + prepared statement) berdasarkan $_SESSION['user_id'].
   --------------------------------------------------------- */
$user = [
    'nama'          => 'Bambang Pamungkas',
    'email'         => 'bambang.p@gmail.com',
    'telepon'       => '0812-3456-7890',
    'avatar'        => 'img/avatar.jpg',
    'tier'          => 'Silver',
    'poin'          => 120,
    'target_poin'   => 150,
    'pesanan_aktif' => 2,
    'favorit'       => 3,
];

// Helper escape output (cegah XSS)
function e(string|int $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

// Hitung progress poin
$sisaPoin = max(0, $user['target_poin'] - $user['poin']);
$persen   = $user['target_poin'] > 0
    ? min(100, (int) round($user['poin'] / $user['target_poin'] * 100))
    : 0;

// Menu: tiap item = [ikon, warna tint, judul, deskripsi, link, badge]
$menuAktivitas = [
    ['bi-ticket-perforated', 'tint-green',  'Riwayat Pesanan', 'Lihat jadwal booking lalu & aktif', 'tiket.php',
        $user['pesanan_aktif'] > 0 ? $user['pesanan_aktif'] . ' Aktif' : null],
    ['bi-clock',             'tint-indigo', 'Daftar Tunggu (Waiting List)', 'Pantau slot lapangan penuh', 'waiting-list.php', null],
    ['bi-heart-fill',        'tint-red',    'Lapangan Favorit', $user['favorit'] . ' venue tersimpan', 'favorit.php', null],
];
$menuPengaturan = [
    ['bi-wallet2', 'tint-indigo', 'Metode Pembayaran Tersimpan', 'QRIS, Virtual Account, Kartu', 'pembayaran.php', null],
    ['bi-gear',    'tint-indigo', 'Setelan', 'Konfigurasi keamanan, notifikasi, dan preferensi akun', 'setelan.php', null],
];
$menuBantuan = [
    ['bi-question-circle', 'tint-green', 'Bantuan', 'FAQ, kebijakan privasi, & hubungi admin', 'bantuan.php', null],
];

// Navigasi bawah
$halamanAktif = 'akun';
$nav = [
    'beranda' => ['bi-dribbble',          'Beranda', 'beranda.php'],
    'favorit' => ['bi-bookmark',          'Favorit', 'favorit.php'],
    'pesanan' => ['bi-ticket-perforated', 'Pesanan', 'tiket.php'],
    'akun'    => ['bi-person-circle',     'Akun',    'akun.php'],
];

// Render satu grup menu
function renderMenu(array $items, string $extraClass = ''): void
{
    echo '<ul class="menu ' . e($extraClass) . '">';
    foreach ($items as [$ikon, $tint, $judul, $desc, $href, $badge]) {
        echo '<li class="menu__item"><a class="menu__link" href="' . e($href) . '">';
        echo '<span class="menu__icon ' . e($tint) . '"><i class="bi ' . e($ikon) . '"></i></span>';
        echo '<span class="menu__text"><strong class="menu__title">' . e($judul) . '</strong>';
        echo '<small class="menu__desc">' . e($desc) . '</small></span>';
        if ($badge) {
            echo '<mark class="badge-pill">' . e($badge) . '</mark>';
        }
        echo '<i class="bi bi-chevron-right menu__chev" aria-hidden="true"></i></a></li>';
    }
    echo '</ul>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Akun Saya - Arena Sport</title>

  <!-- Bootstrap 5 + Bootstrap Icons + font Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- CSS kita HARUS setelah Bootstrap -->
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="app">

<header class="app-header">
  <div class="app-header__inner">
    <img class="app-header__logo" src="img/logo.png" alt="Arena Sport">
    <h1>Akun Saya</h1>
    <button type="button" class="icon-btn" aria-label="Notifikasi">
      <i class="bi bi-bell"></i><span class="dot"></span>
    </button>
    <img class="avatar-sm" src="<?= e($user['avatar']) ?>" alt="">
  </div>
</header>

<main class="page">
  <div class="page__grid">

    <!-- Kolom kiri: profil + poin -->
    <div class="page__aside page__col">
      <section class="card-app profile">
        <div class="profile__avatar-wrap">
          <img class="profile__avatar" src="<?= e($user['avatar']) ?>" alt="<?= e($user['nama']) ?>">
          <span class="profile__verified" title="Akun terverifikasi"><i class="bi bi-patch-check-fill"></i></span>
        </div>
        <h2><?= e($user['nama']) ?></h2>
        <p class="profile__contact"><?= e($user['email']) ?> • <?= e($user['telepon']) ?></p>
        <a href="edit-profil.php" class="btn-outline-pill"><i class="bi bi-pencil"></i> Edit Profil</a>
      </section>

      <section class="points">
        <small class="points__tier">TIER ATLET <?= e(strtoupper($user['tier'])) ?></small>
        <h2>Arena Points Anda: <?= e($user['poin']) ?> Poin</h2>
        <p><?= $sisaPoin > 0
            ? 'Kumpulkan ' . e($sisaPoin) . ' poin lagi untuk klaim 1 jam main gratis!'
            : 'Selamat! Anda bisa klaim 1 jam main gratis.' ?></p>
        <div class="points__bar">
          <div class="points__track" role="progressbar" aria-valuenow="<?= e($user['poin']) ?>"
               aria-valuemin="0" aria-valuemax="<?= e($user['target_poin']) ?>">
            <div class="points__fill" style="width: <?= e($persen) ?>%"></div>
          </div>
          <span class="points__count"><i class="bi bi-trophy-fill"></i> <?= e($user['poin']) ?> / <?= e($user['target_poin']) ?> pts</span>
        </div>
      </section>
    </div>

    <!-- Kolom kanan: menu -->
    <div class="page__col">
      <h3 class="section-label">Aktivitas Saya</h3>
      <?php renderMenu($menuAktivitas); ?>

      <h3 class="section-label">Pengaturan &amp; Bantuan</h3>
      <?php renderMenu($menuPengaturan); ?>
      <?php renderMenu($menuBantuan, 'menu--flat'); ?>

      <form action="logout.php" method="post">
        <button type="submit" class="btn-logout"><i class="bi bi-box-arrow-right"></i> Keluar dari Akun</button>
      </form>

      <footer class="app-footer">
        <small>Arena Sport App v2.4.1 (Build <?= date('Y') ?>)<br><em>Satu sentuhan menuju lapangan juara.</em></small>
      </footer>
    </div>

  </div>
</main>

<nav class="bottom-nav" aria-label="Navigasi utama">
  <div class="bottom-nav__list">
    <?php foreach ($nav as $key => [$ikon, $label, $href]): ?>
      <a class="bottom-nav__link<?= $key === $halamanAktif ? ' is-active' : '' ?>"
         href="<?= e($href) ?>"<?= $key === $halamanAktif ? ' aria-current="page"' : '' ?>>
        <i class="bi <?= e($ikon) ?>"></i><span><?= e($label) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</nav>

</body>
</html>
