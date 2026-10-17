<?php $pageTitle = 'Masuk'; ?>
<!DOCTYPE html>
<html lang="id">
<head><?php include __DIR__ . '/../../components/head.php'; ?></head>
<body>
<main class="auth-wrap"><div class="auth-box">

  <div class="text-center mb-4">
    <div class="brand-logo mx-auto mb-3">
      <img src="../../assets/img/logo.png" alt="Arena Sport">
    </div>
    <span class="badge-pill mb-3"><i class="bi bi-lightning-charge-fill"></i>BOOKING CEPAT • REALTIME</span>
    <h1 class="title mt-2">Selamat Datang Kembali!</h1>
    <p class="subtitle mb-0">Masuk untuk reservasi lapangan futsal &amp; badminton favoritmu.</p>
  </div>

  <div class="auth-card">
    <div id="alertBox" class="alert-auth d-none mb-3" role="alert">
      <strong id="alertTitle"></strong><span id="alertText"></span>
    </div>

    <form id="loginForm" data-api="../../backend/api/auth/login.php" novalidate>
      <div class="mb-3">
        <label class="form-label" for="identifier">Email atau Nomor HP</label>
        <div class="input-icon"><i class="bi bi-envelope lead"></i>
          <input type="text" id="identifier" class="form-control" placeholder="contoh: atlet@arenasport.id" autocomplete="username"></div>
        <div class="invalid-feedback"></div>
      </div>
      <div class="mb-3">
        <div class="d-flex justify-content-between">
          <label class="form-label" for="password">Kata Sandi</label>
          <a href="#" class="link-green small">Lupa Kata Sandi?</a>
        </div>
        <div class="input-icon"><i class="bi bi-lock lead"></i>
          <input type="password" id="password" class="form-control" placeholder="••••••••" autocomplete="current-password">
          <button type="button" class="toggle" data-toggle="password" aria-label="Tampilkan kata sandi"><i class="bi bi-eye"></i></button></div>
        <div class="invalid-feedback"></div>
      </div>
      <div class="form-check mb-4 small">
        <input class="form-check-input" type="checkbox" id="remember">
        <label class="form-check-label text-secondary" for="remember">Ingat saya di perangkat ini</label>
      </div>
      <button type="submit" id="btnSubmit" class="btn btn-main">Masuk Sekarang <i class="bi bi-arrow-right ms-1"></i></button>
    </form>

    <div class="divider">atau masuk dengan</div>
    <button type="button" class="btn btn-google"><i class="bi bi-google me-2"></i>Google</button>
  </div>


  <p class="text-center mt-4 mb-2 text-secondary">Belum punya akun? <a href="register.php" class="link-green">Daftar Sekarang</a></p>
  <p class="text-center foot"><a href="#" class="text-reset text-decoration-none">Pusat Bantuan</a> &nbsp;•&nbsp; <a href="#" class="text-reset text-decoration-none">Syarat &amp; Ketentuan</a></p>

</div></main>
<?php include __DIR__ . '/../../components/scripts.php'; ?>
</body>
</html>