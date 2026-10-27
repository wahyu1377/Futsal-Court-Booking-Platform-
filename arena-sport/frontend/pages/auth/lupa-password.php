<?php $pageTitle = 'Lupa Kata Sandi'; ?>

<!DOCTYPE html>
<html lang="id">

<head><?php include __DIR__ . '/../../components/head.php'; ?></head>

<body>
  <main class="auth-wrap">
    <div class="auth-box">

      <div class="text-center mb-4">
        <div class="brand-logo mx-auto mb-3">
          <img src="../../assets/img/logo.png" alt="Arena Sport">
        </div>
        <span class="badge-pill soft mb-3"><i class="bi bi-shield-lock-fill"></i>PEMULIHAN AKUN</span>
        <h1 class="title mt-2">Lupa Kata Sandi?</h1>
        <p class="subtitle mb-0">Masukkan email atau nomor HP yang terdaftar. Kami akan mengirim tautan untuk membuat kata sandi baru.</p>
      </div>

      <div class="auth-card">
        <div id="alertBox" class="alert-auth d-none mb-3" role="alert">
          <strong id="alertTitle"></strong><span id="alertText"></span>
        </div>

        <form id="forgotForm" data-api="../../../backend/api/auth/lupa-password.php" novalidate>
          <div class="mb-3">
            <label class="form-label" for="identifier">Email atau Nomor HP</label>
            <div class="input-icon"><i class="bi bi-envelope lead"></i>
              <input type="text" id="identifier" class="form-control" placeholder="contoh: atlet@arenasport.id"
                autocomplete="username" inputmode="email">
            </div>
            <div class="invalid-feedback"></div>
          </div>

          <button type="submit" id="btnSubmit" class="btn btn-main">Kirim Tautan Reset <i
              class="bi bi-arrow-right ms-1"></i></button>
        </form>

        <!-- Hanya muncul di mode demo (MAIL_MODE=log): tautan ditampilkan di layar -->
        <div id="demoBox" class="alert-auth ok d-none mt-3 mb-0" role="status">
          <strong>Mode demo - email tidak benar-benar dikirim</strong>
          <span>Buka tautan ini untuk melanjutkan:</span>
          <a id="demoLink" class="link-green d-block mt-1 text-break" href="#">tautan reset</a>
        </div>

        <p class="text-center mt-4 mb-0 text-secondary small">
          Ingat kata sandinya? <a href="login.php" class="link-green">Kembali ke halaman masuk</a>
        </p>
      </div><!-- /auth-card -->

    </div><!-- /auth-box -->
  </main>

  <?php include __DIR__ . '/../../components/scripts.php'; ?>
  <script src="../../assets/js/auth-reset.js"></script>
</body>

</html>
