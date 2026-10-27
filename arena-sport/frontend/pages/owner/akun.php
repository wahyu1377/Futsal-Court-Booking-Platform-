<?php
declare(strict_types=1);

/**
 * Halaman Akun Owner.
 *
 * Desainnya sengaja dibuat mirip halaman "Akun Saya" milik pengguna
 * (frontend/pages/user/akun.php) supaya konsisten, hanya isi menunya yang
 * berbeda: pesanan masuk, edit profil, daftar lapangan, setelan, bantuan.
 *
 * Semua angka pada halaman ini diambil langsung dari database:
 *   - jumlah lapangan  -> lapangan WHERE owner_id = ?
 *   - jumlah pesanan    -> booking JOIN lapangan WHERE owner_id = ?
 */
require __DIR__ . '/../../../backend/core/auth.php';
require_once __DIR__ . '/../../../backend/core/skema.php';   // kolomAda()
require_once __DIR__ . '/../../components/helper_profil.php';   // e(), avatarSrc()
requireLogin('owner');

$idOwner = (int) $_SESSION['user_id'];
$pdo     = db();

// 08123456789012 -> 0812-3456-7890
function formatTeleponOwner(?string $hp): ?string
{
    if ($hp === null || $hp === '') {
        return null;
    }
    return rtrim(chunk_split($hp, 4, '-'), '-');
}

/* ---------------------------------------------------------
   DATA PROFIL dari database, berdasarkan akun owner yang login
   --------------------------------------------------------- */
// Kolom owners.avatar hanya ada setelah migrasi 004_owner_akun.sql dijalankan
$adaAvatar = kolomAda($pdo, 'owners', 'avatar');

$sqlProfil = 'SELECT full_name, email, phone' . ($adaAvatar ? ', avatar' : '') . ' FROM owners WHERE id = ?';
$st = $pdo->prepare($sqlProfil);
$st->execute([$idOwner]);
$row = $st->fetch();

if (!$row) {   // akun sudah dihapus dari database: akhiri session
    $_SESSION = [];
    session_destroy();
    header('Location: ../auth/login.php');
    exit;
}

$nama   = (string) $row['full_name'];
$email  = (string) ($row['email'] ?? '');
$telepon = formatTeleponOwner($row['phone'] ?? null);

// Foto profil: pakai file yang tersimpan, kalau belum ada otomatis inisial nama
$avatar = avatarSrc($nama, $adaAvatar ? ($row['avatar'] ?? null) : null);

/* ---------------------------------------------------------
   RINGKASAN dari database
   --------------------------------------------------------- */
$st = $pdo->prepare('SELECT COUNT(*) FROM lapangan WHERE owner_id = ?');
$st->execute([$idOwner]);
$jumlahLapangan = (int) $st->fetchColumn();

$st = $pdo->prepare(
    "SELECT COUNT(*) FROM booking b
     JOIN lapangan l ON l.id = b.lapangan_id AND l.owner_id = ?
     WHERE b.status IN ('menunggu', 'dibayar')"
);
$st->execute([$idOwner]);
$pesananMasuk = (int) $st->fetchColumn();

$st = $pdo->prepare(
    'SELECT COUNT(*) FROM booking b
     JOIN lapangan l ON l.id = b.lapangan_id AND l.owner_id = ?'
);
$st->execute([$idOwner]);
$pesananTotal = (int) $st->fetchColumn();

$kontak = array_filter([$email, $telepon]);

/* ---------------------------------------------------------
   MENU akun owner
   --------------------------------------------------------- */
$menuAktivitas = [
    ['bi-inbox-fill',  'tint-green',  'Pesanan Masuk', 'Pemesanan pengguna untuk lapangan Anda', 'dashboard.php#pesananOwner',
        $pesananMasuk > 0 ? $pesananMasuk . ' Aktif' : null],
    ['bi-grid-3x3-gap-fill', 'tint-indigo', 'Daftar Lapangan Saya', $jumlahLapangan . ' lapangan disewakan', 'dashboard.php#lapanganSaya',
        $jumlahLapangan > 0 ? (string) $jumlahLapangan : null],
];
$menuAkun = [
    ['bi-pencil-square', 'tint-indigo', 'Edit Profil', 'Ubah nama, email, dan nomor WhatsApp', 'edit-profil.php', null],
];
$menuPengaturan = [
    ['bi-gear', 'tint-indigo', 'Setelan', 'Keamanan akun dan preferensi notifikasi', 'setelan.php', null],
];
$menuBantuan = [
    ['bi-question-circle', 'tint-green', 'Bantuan', 'FAQ, kebijakan privasi, & hubungi admin', 'bantuan.php', null],
];

// Navigasi bawah khusus owner (ikon & isi_nav disatukan di components/nav-bottom-owner.php)
$halamanAktif = 'akun';

// Render satu grup menu (pola sama dengan halaman akun pengguna)
function renderMenuOwner(array $items, string $extraClass = ''): void
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
  <title>Akun Owner - Arena Sport</title>

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
    <h1>Akun Owner</h1>
    <a href="dashboard.php" class="icon-btn" aria-label="Kembali ke Dashboard">
      <i class="bi bi-speedometer2"></i>
    </a>
    <img class="avatar-sm" src="<?= e($avatar) ?>" alt="">
  </div>
</header>

<main class="page">
  <div class="page__stack">

    <section class="card-app profile">
      <div class="profile__avatar-wrap">
        <img class="profile__avatar" src="<?= e($avatar) ?>" alt="<?= e($nama) ?>">
      </div>
      <h2><?= e($nama) ?></h2>
      <p class="profile__contact"><?= e($kontak ? implode(' • ', $kontak) : 'Belum ada kontak') ?></p>
      <a href="edit-profil.php" class="btn-outline-pill"><i class="bi bi-pencil"></i> Edit Profil</a>
    </section>

    <h3 class="section-label">Bisnis Saya</h3>
    <?php renderMenuOwner($menuAktivitas); ?>

    <h3 class="section-label">Akun</h3>
    <?php renderMenuOwner($menuAkun); ?>

    <h3 class="section-label">Pengaturan &amp; Bantuan</h3>
    <?php renderMenuOwner($menuPengaturan); ?>
    <?php renderMenuOwner($menuBantuan, 'menu--flat'); ?>

    <form action="../auth/logout.php" method="post">
      <button type="submit" class="btn-logout"><i class="bi bi-box-arrow-right"></i> Keluar dari Akun</button>
    </form>

    <footer class="app-footer">
      <small>Arena Sport Owner v1.0 (Build <?= date('Y') ?>)<br><em>Kelola lapangan Anda dengan mudah.</em></small>
    </footer>

  </div>
</main>

<?php include __DIR__ . '/../../components/nav-bottom-owner.php'; ?>

</body>
</html>
