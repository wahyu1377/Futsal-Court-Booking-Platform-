<?php
declare(strict_types=1);

/**
 * Edit Profil Pengguna.
 *
 * Tampilan & alurnya sama persis dengan halaman Edit Profil Owner
 * (frontend/pages/owner/edit-profil.php):
 *   - Foto profil (pilih / simpan / hapus)
 *   - Formulir nama lengkap, email, dan nomor WhatsApp
 *
 * Penyimpanan lewat API backend/api/user/profil.php.
 */
require __DIR__ . '/../../../backend/core/auth.php';
require_once __DIR__ . '/../../../backend/core/skema.php';   // kolomAda()
require_once __DIR__ . '/../../components/helper_profil.php';   // e(), avatarSrc()
requireLogin('user');

$idUser = (int) $_SESSION['user_id'];
$pdo    = db();

// Kolom users.avatar dipakai untuk foto profil
$adaAvatar = kolomAda($pdo, 'users', 'avatar');

$sqlProfil = 'SELECT full_name, email, phone' . ($adaAvatar ? ', avatar' : '') . ' FROM users WHERE id = ?';
$st = $pdo->prepare($sqlProfil);
$st->execute([$idUser]);
$row = $st->fetch();

if (!$row) {   // akun sudah dihapus dari database: akhiri session
    $_SESSION = [];
    session_destroy();
    header('Location: ../auth/login.php');
    exit;
}

$nama      = (string) $row['full_name'];
$email     = (string) ($row['email'] ?? '');
$telepon   = (string) ($row['phone'] ?? '');
$avatar    = avatarSrc($nama, $adaAvatar ? ($row['avatar'] ?? null) : null);
$punyaFoto = $adaAvatar && punyaAvatar($row['avatar'] ?? null);

$halamanAktif = 'akun';
?>

<?php include dirname(__DIR__, 2) . '/components/clarity.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profil - Arena Sport</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="../../assets/css/style.css" rel="stylesheet">
  <link href="../../assets/css/akun.css" rel="stylesheet">
  <link href="../../assets/css/bottom-nav.css" rel="stylesheet">
</head>
<body class="app">

<header class="app-header">
  <div class="app-header__inner">
    <a href="akun.php" class="icon-btn" aria-label="Kembali ke Akun"><i class="bi bi-arrow-left"></i></a>
    <h1>Edit Profil</h1>
  </div>
</header>

<main class="page">
  <div class="page__stack">
    <div id="epRoot" class="op-stack" data-api="../../../backend/api/user/profil.php">

      <section class="card-app profile">
        <div class="profile__avatar-wrap">
          <img class="profile__avatar" id="previewImg" src="<?= e($avatar) ?>" alt="Foto profil <?= e($nama) ?>">
        </div>
        <h2><?= e($nama) ?></h2>
        <p class="profile__contact">Pengguna Arena Sport</p>
      </section>

      <div class="op-alert d-none" id="opAlert" role="alert"></div>

      <?php if ($adaAvatar): ?>
        <!-- Ubah foto profil -->
        <section class="card-app op-form">
          <h3 class="op-form__title"><i class="bi bi-camera me-1"></i> Foto Profil</h3>

          <input type="file" id="fileInput" accept="image/jpeg,image/png,image/webp" hidden>

          <div class="d-grid gap-2">
            <button type="button" id="btnPilih" class="op-btn op-btn--soft">
              <i class="bi bi-image me-1"></i> Pilih Foto Baru
            </button>
            <button type="button" id="btnSimpanFoto" class="op-btn" disabled>
              <i class="bi bi-check2-circle me-1"></i> Simpan Foto
            </button>
            <?php if ($punyaFoto): ?>
              <button type="button" id="btnHapusFoto" class="op-btn op-btn--danger">
                <i class="bi bi-trash3 me-1"></i> Hapus Foto
              </button>
            <?php endif; ?>
          </div>

          <small class="op-hint">JPG, PNG, atau WebP (maksimal 2 MB). Foto otomatis dipotong persegi dari bagian tengah.</small>
        </section>
      <?php else: ?>
        <div class="op-alert err">
          <i class="bi bi-exclamation-triangle me-1"></i>
          Fitur foto profil belum aktif di database.
        </div>
      <?php endif; ?>

      <form id="opForm" class="card-app op-form" novalidate>
        <h3 class="op-form__title">Informasi Akun</h3>

        <label class="op-label" for="opNama">Nama Lengkap</label>
        <input type="text" class="op-input" id="opNama" name="nama"
               value="<?= e($nama) ?>" maxlength="80" autocomplete="name" required>

        <label class="op-label" for="opEmail">Email</label>
        <input type="email" class="op-input" id="opEmail" name="email"
               value="<?= e($email) ?>" maxlength="120" autocomplete="email" required>

        <label class="op-label" for="opTelepon">Nomor WhatsApp</label>
        <input type="tel" class="op-input" id="opTelepon" name="telepon"
               value="<?= e($telepon) ?>" maxlength="15" inputmode="numeric" placeholder="081234567890">
        <small class="op-hint">Dipakai untuk pemberitahuan status pesanan. Boleh dikosongkan.</small>

        <button type="submit" class="op-btn" id="opSimpan">
          <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan
        </button>
      </form>

    </div>
  </div>
</main>

<?php include __DIR__ . '/../../components/nav-bottom-user.php'; ?>

<script src="../../assets/js/profil.js"></script>
</body>
</html>
