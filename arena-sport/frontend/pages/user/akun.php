<?php
declare(strict_types=1);

require __DIR__ . '/../../../backend/core/auth.php';
require_once __DIR__ . '/../../../backend/core/booking.php';
require_once __DIR__ . '/../../components/helper_profil.php';   // e(), avatarSrc(), punyaAvatar()
requireLogin('user');   // wajib login; owner otomatis diarahkan ke dashboard-nya

// 08123456789012 -> 0812-3456-7890
function formatTelepon(?string $hp): ?string
{
    if ($hp === null || $hp === '') return null;
    return rtrim(chunk_split($hp, 4, '-'), '-');
}

/* ---------------------------------------------------------
   DATA PROFIL dari database, berdasarkan akun yang login
   --------------------------------------------------------- */
$st = db()->prepare('SELECT full_name, email, phone, avatar FROM users WHERE id = ?');
$st->execute([(int) $_SESSION['user_id']]);
$row = $st->fetch();

if (!$row) {   // akun sudah dihapus dari database: akhiri session
    $_SESSION = [];
    session_destroy();
    header('Location: ../auth/login.php');
    exit;
}


// TODO: isi setelah tabel booking & favorit dibuat, contoh:
//   SELECT COUNT(*) FROM booking WHERE user_id = ? AND status = 'aktif'
$pesananAktif = hitungBookingAktif(db(), (int) $_SESSION['user_id']);

//   SELECT COUNT(*) FROM favorit WHERE user_id = ?
$st = db()->prepare('SELECT COUNT(*) FROM favorit WHERE user_id = ?');
$st->execute([(int) $_SESSION['user_id']]);
$favorit = (int) $st->fetchColumn();

// TODO: ubah jadi true kalau email sudah diverifikasi (belum ada fitur verifikasi)
$terverifikasi = false;

$kontak = array_filter([$row['email'], formatTelepon($row['phone'])]);

$user = [
    'nama'          => (string) $row['full_name'],
    'kontak'        => $kontak ? implode(' • ', $kontak) : 'Belum ada kontak',
    'avatar'        => avatarSrc((string) $row['full_name'], $row['avatar']),
    'pesanan_aktif' => $pesananAktif,
    'favorit'       => $favorit,
];


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

// Navigasi bawah (ikon & isi_nav disatukan di components/nav-bottom-user.php)
$halamanAktif = 'akun';

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

<?php include dirname(__DIR__, 2) . '/components/clarity.php'; ?>

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
  <link href="../../assets/css/style.css" rel="stylesheet">
  <link href="../../assets/css/akun.css" rel="stylesheet">
  <link href="../../assets/css/bottom-nav.css" rel="stylesheet">
</head>
<body class="app">

<header class="app-header">
  <div class="app-header__inner">
    <img class="app-header__logo" src="../../assets/img/logo.png" alt="Arena Sport">
    <h1>Akun Saya</h1>
    <button type="button" class="icon-btn" aria-label="Notifikasi">
      <i class="bi bi-bell"></i><span class="dot"></span>
    </button>
    <img class="avatar-sm" src="<?= e($user['avatar']) ?>" alt="">
  </div>
</header>

<main class="page">
  <div class="page__stack">

    <section class="card-app profile">
      <div class="profile__avatar-wrap">
        <img class="profile__avatar" src="<?= e($user['avatar']) ?>" alt="<?= e($user['nama']) ?>">
        <?php if ($terverifikasi): ?>
          <span class="profile__verified" title="Akun terverifikasi"><i class="bi bi-patch-check-fill"></i></span>
        <?php endif; ?>
      </div>
      <h2><?= e($user['nama']) ?></h2>
      <p class="profile__contact"><?= e($user['kontak']) ?></p>
      <a href="edit-profil.php" class="btn-outline-pill"><i class="bi bi-pencil"></i> Edit Profil</a>
    </section>

    <h3 class="section-label">Aktivitas Saya</h3>
    <?php renderMenu($menuAktivitas); ?>

    <h3 class="section-label">Pengaturan &amp; Bantuan</h3>
    <?php renderMenu($menuPengaturan); ?>
    <?php renderMenu($menuBantuan, 'menu--flat'); ?>

    <form action="../auth/logout.php" method="post">
      <button type="submit" class="btn-logout"><i class="bi bi-box-arrow-right"></i> Keluar dari Akun</button>
    </form>

    <footer class="app-footer">
      <small>Arena Sport App v2.4.1 (Build <?= date('Y') ?>)<br><em>Satu sentuhan menuju lapangan juara.</em></small>
    </footer>

  </div>
</main>

<?php include __DIR__ . '/../../components/nav-bottom-user.php'; ?>

</body>
</html>