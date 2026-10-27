<?php
$pageTitle = 'Reset Kata Sandi';

// Token diambil dari URL (?token=...). Nilainya nanti dikirim lewat API,
// bukan dipakai langsung di query database.
$token = isset($_GET['token']) && is_string($_GET['token']) ? trim($_GET['token']) : '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta name="referrer" content="no-referrer">
  <?php include __DIR__ . '/../../components/head.php'; ?>
</head>

<body>
  <main class="auth-wrap">
    <div class="auth-box">

      <div class="text-center mb-4">
        <div class="brand-logo mx-auto mb-3">
          <img src="../../assets/img/logo.png" alt="Arena Sport">
        </div>
        <span class="badge-pill soft mb-3"><i class="bi bi-key-fill"></i>KATA SANDI BARU</span>
        <h1 class="title mt-2">Buat Kata Sandi Baru</h1>
        <p class="subtitle mb-0">Masukkan kata sandi baru untuk akun Arena Sport Anda.</p>
      </div>

      <div class="auth-card">
        <div id="alertBox" class="alert-auth d-none mb-3" role="alert">
          <strong id="alertTitle"></strong><span id="alertText"></span>
        </div>

        <!-- Status proses pengecekan token (loading / gagal / berhasil) -->
        <div id="tokenStatus" class="text-center py-3">
          <div class="spinner-border text-success" role="status" aria-hidden="true"></div>
          <p class="text-secondary small mb-0 mt-2">Memeriksa tautan reset...</p>
        </div>

        <form id="resetForm" class="d-none"
              data-api="../../../backend/api/auth/reset-password.php"
              data-token="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>" novalidate>
          <div class="mb-3">
            <label class="form-label" for="password">Kata Sandi Baru</label>
            <div class="input-icon"><i class="bi bi-lock lead"></i>
              <input type="password" id="password" class="form-control" placeholder="Minimal 8 karakter, ada huruf &amp; angka"
                autocomplete="new-password">
              <button type="button" class="toggle" data-toggle="password" aria-label="Tampilkan kata sandi"><i
                  class="bi bi-eye"></i></button>
            </div>
            <div class="invalid-feedback"></div>
          </div>

          <div class="mb-4">
            <label class="form-label" for="password_confirm">Konfirmasi Kata Sandi</label>
            <div class="input-icon"><i class="bi bi-arrow-repeat lead"></i>
              <input type="password" id="password_confirm" class="form-control" placeholder="Ulangi kata sandi baru"
                autocomplete="new-password">
              <button type="button" class="toggle" data-toggle="password_confirm" aria-label="Tampilkan kata sandi"><i
                  class="bi bi-eye"></i></button>
            </div>
            <div class="invalid-feedback"></div>
          </div>

          <button type="submit" id="btnSubmit" class="btn btn-main">Simpan Kata Sandi Baru <i
              class="bi bi-check-lg ms-1"></i></button>
        </form>

        <p class="text-center mt-4 mb-0 text-secondary small">
          Tautan tidak berlaku? <a href="lupa-password.php" class="link-green">Minta tautan baru</a>
        </p>
      </div><!-- /auth-card -->

    </div><!-- /auth-box -->
  </main>

  <?php include __DIR__ . '/../../components/scripts.php'; ?>
  <script src="../../assets/js/auth-reset.js"></script>
</body>

</html>
