<?php
declare(strict_types=1);

require __DIR__ . '/../../../backend/core/auth.php';
require_once __DIR__ . '/../../components/helper_profil.php';
requireLogin('user');

$st = db()->prepare('SELECT full_name, email, phone, avatar FROM users WHERE id = ?');
$st->execute([(int) $_SESSION['user_id']]);
$row = $st->fetch();

if (!$row) {   // akun sudah dihapus dari database
    $_SESSION = [];
    session_destroy();
    header('Location: ../auth/login.php');
    exit;
}

$nama      = (string) $row['full_name'];
$kontak    = (string) ($row['email'] ?: $row['phone'] ?: '');
$punyaFoto = punyaAvatar($row['avatar']);
$avatar    = avatarSrc($nama, $row['avatar']);

$halamanAktif = 'akun';
$nav = [
    'beranda' => ['bi-dribbble',          'Beranda', 'beranda.php'],
    'favorit' => ['bi-bookmark',          'Favorit', 'favorit.php'],
    'pesanan' => ['bi-ticket-perforated', 'Pesanan', 'tiket.php'],
    'akun'    => ['bi-person-circle',     'Akun',    'akun.php'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Foto Profil - Arena Sport</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="../../assets/css/style.css" rel="stylesheet">
  <link href="../../assets/css/edit-profil.css" rel="stylesheet">
</head>
<body class="app">

<header class="app-header">
  <div class="app-header__inner">
    <a href="akun.php" class="icon-btn" aria-label="Kembali ke Akun"><i class="bi bi-arrow-left"></i></a>
    <h1>Edit Foto Profil</h1>
  </div>
</header>

<main class="page">
  <div class="ep-wrap" id="epRoot" data-api="../../../backend/api/user/profil.php">

    <section class="card-app ep-card">
      <div id="epAlert" class="ep-alert d-none" role="alert"></div>

      <div class="ep-preview">
        <img id="previewImg" src="<?= e($avatar) ?>" alt="Foto profil <?= e($nama) ?>">
      </div>

      <h2 class="ep-name"><?= e($nama) ?></h2>
      <p class="ep-contact"><?= e($kontak) ?></p>

      <input type="file" id="fileInput" accept="image/jpeg,image/png,image/webp" hidden>

      <div class="d-grid gap-2">
        <button type="button" id="btnPilih" class="ep-btn ep-btn--soft">
          <i class="bi bi-image me-1"></i> Pilih Foto Baru
        </button>
        <button type="button" id="btnSimpan" class="ep-btn ep-btn--main" disabled>
          <i class="bi bi-check2-circle me-1"></i> Simpan Foto
        </button>
        <?php if ($punyaFoto): ?>
          <button type="button" id="btnHapus" class="ep-btn ep-btn--danger">
            <i class="bi bi-trash3 me-1"></i> Hapus Foto
          </button>
        <?php endif; ?>
      </div>

      <p class="ep-hint">JPG, PNG, atau WebP. Foto otomatis dipotong persegi dari bagian tengah.</p>
    </section>

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

<script src="../../assets/js/edit-profil.js"></script>
</body>
</html>