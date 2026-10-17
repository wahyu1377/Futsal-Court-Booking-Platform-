<?php $pageTitle = 'Daftar'; ?>
<!DOCTYPE html>
<html lang="id">
<head><?php include __DIR__ . '/../../components/head.php'; ?></head>
<body>
<main class="auth-wrap"><div class="auth-box">

  <div class="text-center mb-4">
    <div class="brand-logo mx-auto mb-3">
      <img src="../../assets/img/logo.png" alt="Arena Sport">
    </div>
    <span class="badge-pill soft mb-3">● PENDAFTARAN ATLET BARU</span>
    <h1 class="title mt-2">Daftar Akun Baru</h1>
    <p class="subtitle mb-0">Lengkapi data diri Anda untuk mulai reservasi lapangan.</p>
  </div>

  <div class="auth-card">
    <div id="alertBox" class="alert-auth d-none mb-3" role="alert">
      <strong id="alertTitle"></strong><span id="alertText"></span>
    </div>

    <form id="registerForm" data-api="../../backend/api/auth/register.php" novalidate>

        <div class="mb-4">
    <label class="form-label">Daftar sebagai</label>
    <div class="row g-2">
        <div class="col-6">
        <input type="radio" class="btn-check" name="role" id="role_user" value="user" checked>
        <label class="role-card" for="role_user">
            <i class="bi bi-person-check"></i>
            <strong>Pengguna</strong>
            <small>Cari &amp; booking lapangan</small>
        </label>
        </div>
        <div class="col-6">
        <input type="radio" class="btn-check" name="role" id="role_owner" value="owner">
        <label class="role-card" for="role_owner">
            <i class="bi bi-building"></i>
            <strong>Owner Lapangan</strong>
            <small>Sewakan &amp; kelola lapangan</small>
        </label>
        </div>
    </div>
    </div>

      <div class="mb-3">
        <label class="form-label" for="full_name">Nama Lengkap</label>
        <div class="input-icon"><i class="bi bi-person lead"></i>
          <input type="text" id="full_name" class="form-control" placeholder="Bambang Pamungkas" autocomplete="name" maxlength="50">
        <div class="invalid-feedback"></div>
      </div>
      <div class="mb-3">
        <label class="form-label" for="identifier">Email atau Nomor HP</label>
        <div class="input-icon"><i class="bi bi-envelope lead"></i>
          <input type="text" id="identifier" class="form-control" placeholder="atlet@arenasport.id atau 081234567890" autocomplete="username" inputmode="email">
        <div class="invalid-feedback"></div>
      </div>
      <div class="mb-3">
        <label class="form-label" for="password">Kata Sandi</label>
        <div class="input-icon"><i class="bi bi-lock lead"></i>
          <input type="password" id="password" class="form-control" placeholder="Minimal 8 karakter aman" autocomplete="new-password">
          <button type="button" class="toggle" data-toggle="password" aria-label="Tampilkan kata sandi"><i class="bi bi-eye"></i></button></div>
        <div class="invalid-feedback"></div>
      </div>
      <div class="mb-3">
        <label class="form-label" for="password_confirm">Konfirmasi Kata Sandi</label>
        <div class="input-icon"><i class="bi bi-arrow-repeat lead"></i>
          <input type="password" id="password_confirm" class="form-control" placeholder="Ulangi kata sandi Anda" autocomplete="new-password">
          <button type="button" class="toggle" data-toggle="password_confirm" aria-label="Tampilkan kata sandi"><i class="bi bi-eye"></i></button></div>
        <div class="invalid-feedback"></div>
      </div>
      <div class="form-check mb-4 small">
        <input class="form-check-input" type="checkbox" id="terms">
        <label class="form-check-label text-secondary" for="terms">Saya setuju dengan <a href="#" class="link-green">Syarat &amp; Ketentuan</a> serta <a href="#" class="link-green">Kebijakan Privasi</a> Arena Sport.</label>
      </div>
      <button type="submit" id="btnSubmit" class="btn btn-main">Daftar Sekarang <i class="bi bi-arrow-right ms-1"></i></button>
    </form>

    <div class="divider">atau daftar lebih cepat dengan</div>
    <button type="button" class="btn btn-google"><i class="bi bi-google me-2"></i>Google</button>
  </div>

  <p class="text-center mt-4 mb-2 text-secondary">Sudah punya akun? <a href="login.php" class="link-green">Masuk di sini</a></p>

</div></main>
<?php include __DIR__ . '/../../components/scripts.php'; ?>
</body>
</html>