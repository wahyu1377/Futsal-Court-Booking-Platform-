<?php
declare(strict_types=1);

/**
 * Halaman Setelan Pengguna.
 *
 * Tampilan & alurnya sama persis dengan halaman Setelan Owner
 * (frontend/pages/owner/setelan.php):
 *   - Keamanan        : ganti kata sandi (API user/profil.php, aksi=ganti_password)
 *   - Notifikasi      : preferensi kiriman (API user/setelan.php, tabel user_settings)
 *   - Informasi Akun  : dibaca dari tabel users
 *
 * Kalau tabel user_settings belum ada, preferensi disimpan di perangkat saja
 * (fallback localStorage) - halaman tetap bisa dipakai.
 */
require __DIR__ . '/../../../backend/core/auth.php';
require_once __DIR__ . '/../../components/helper_profil.php';   // e(), avatarSrc()
requireLogin('user');

$idUser = (int) $_SESSION['user_id'];
$pdo    = db();

$st = $pdo->prepare('SELECT full_name, email, phone, created_at FROM users WHERE id = ?');
$st->execute([$idUser]);
$row = $st->fetch();

if (!$row) {
    $_SESSION = [];
    session_destroy();
    header('Location: ../auth/login.php');
    exit;
}

$nama      = (string) $row['full_name'];
$email     = (string) ($row['email'] ?? '');
$telepon   = (string) ($row['phone'] ?? '');
$avatar    = avatarSrc($nama, null);
$bergabung = !empty($row['created_at'])
    ? date('j M Y', strtotime((string) $row['created_at']))
    : '-';

// Keamanan: kunci akun terakhir (dari tabel users)
$st = $pdo->prepare('SELECT locked_until, failed_attempts FROM users WHERE id = ?');
$st->execute([$idUser]);
$keamanan   = $st->fetch() ?: [];
$kunciSAMPAI = !empty($keamanan['locked_until']) && strtotime((string) $keamanan['locked_until']) > time()
    ? date('j M Y H:i', strtotime((string) $keamanan['locked_until']))
    : null;

$halamanAktif = 'akun';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <?php /* Microsoft Clarity (pelacakan halaman) */ ?>
  <?php include dirname(__DIR__, 2) . '/components/clarity.php'; ?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Setelan - Arena Sport</title>

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
    <h1>Setelan</h1>
  </div>
</header>

<main class="page">
  <div class="page__stack">
    <div id="setRoot" class="op-stack"
         data-api="../../../backend/api/user/profil.php"
         data-api-prefs="../../../backend/api/user/setelan.php"
         data-prefs-key="arena-sport-user-prefs">

      <div class="op-alert d-none" id="setAlert" role="alert"></div>

      <!-- Keamanan: ganti kata sandi -->
      <form id="formPassword" class="card-app op-form" novalidate>
        <h3 class="op-form__title"><i class="bi bi-shield-lock me-1"></i> Keamanan</h3>

        <label class="op-label" for="pwLama">Kata Sandi Lama</label>
        <input type="password" class="op-input" id="pwLama" name="password_lama" autocomplete="current-password" required>

        <label class="op-label" for="pwBaru">Kata Sandi Baru</label>
        <input type="password" class="op-input" id="pwBaru" name="password_baru" minlength="8" autocomplete="new-password" required>

        <label class="op-label" for="pwUlangi">Ulangi Kata Sandi Baru</label>
        <input type="password" class="op-input" id="pwUlangi" name="password_konfirmasi" minlength="8" autocomplete="new-password" required>

        <small class="op-hint">Minimal 8 karakter. Gunakan kombinasi huruf dan angka.</small>

        <button type="submit" class="op-btn" id="btnSimpanPw">
          <i class="bi bi-key me-1"></i> Perbarui Kata Sandi
        </button>
      </form>

      <!-- Notifikasi: disimpan di server (tabel user_settings) -->
      <section class="card-app op-form">
        <h3 class="op-form__title"><i class="bi bi-bell me-1"></i> Notifikasi</h3>

        <label class="op-switch">
          <input type="checkbox" id="notifPesanan" checked>
          <span class="op-switch__track"><span class="op-switch__dot"></span></span>
          <span class="op-switch__text">
            <strong>Kabari pesanan baru</strong>
            <small>Ingatkan saat ada pesanan masuk untuk tim Anda.</small>
          </span>
        </label>

        <label class="op-switch">
          <input type="checkbox" id="notifEmail">
          <span class="op-switch__track"><span class="op-switch__dot"></span></span>
          <span class="op-switch__text">
            <strong>Ringkasan harian via email</strong>
            <small>Rekap jadwal main dan riwayat booking Anda.</small>
          </span>
        </label>

        <label class="op-switch">
          <input type="checkbox" id="notifWa" checked>
          <span class="op-switch__track"><span class="op-switch__dot"></span></span>
          <span class="op-switch__text">
            <strong>Pembaruan pesanan via WhatsApp</strong>
            <small>Status pembayaran dan jadwal main dikirim ke nomor Anda.</small>
          </span>
        </label>

        <small class="op-hint" id="notifStatus">Preferensi notifikasi disimpan di server, jadi berlaku di semua perangkat.</small>
      </section>

      <!-- Informasi akun (hanya baca, dari tabel users) -->
      <section class="card-app op-form">
        <h3 class="op-form__title"><i class="bi bi-person-badge me-1"></i> Informasi Akun</h3>

        <div class="op-info">
          <img class="op-info__avatar" src="<?= e($avatar) ?>" alt="">
          <div>
            <strong><?= e($nama) ?></strong>
            <small><?= e($email !== '' ? $email : 'Email belum diisi') ?></small>
            <?php if ($telepon !== ''): ?>
              <small><?= e($telepon) ?></small>
            <?php endif; ?>
          </div>
        </div>

        <ul class="op-list">
          <li><span>Bergabung sejak</span><strong><?= e($bergabung) ?></strong></li>
          <li><span>Status akun</span><strong class="ok">Aktif</strong></li>
          <li>
            <span>Penguncian sementara</span>
            <strong><?= $kunciSAMPAI !== null ? e($kunciSAMPAI) : 'Tidak ada' ?></strong>
          </li>
        </ul>

        <a href="edit-profil.php" class="op-link-btn"><i class="bi bi-pencil"></i> Ubah data profil</a>
      </section>

    </div>
  </div>
</main>

<?php include __DIR__ . '/../../components/nav-bottom-user.php'; ?>

<script src="../../assets/js/setelan.js"></script>
</body>
</html>
