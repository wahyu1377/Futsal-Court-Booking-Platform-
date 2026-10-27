// Tujuan redirect setelah login (relatif dari frontend/pages/auth/)
const REDIRECT = { user: '../user/beranda.php', owner: '../owner/dashboard.php' };
const $ = (s) => document.querySelector(s);

// Tombol mata (lihat/sembunyikan password)
document.querySelectorAll('[data-toggle]').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = document.getElementById(btn.dataset.toggle);
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
  });
});

// Ambil (atau buat) kotak pesan error di bawah field
function errBox(el) {
  const wrap = el.closest('.input-icon') || el;
  let box = wrap.parentElement.querySelector('.field-error');
  if (!box) {
    box = document.createElement('div');
    box.className = 'field-error';
    box.setAttribute('role', 'alert');
    wrap.insertAdjacentElement('afterend', box);
  }
  return box;
}

function setInvalid(id, msg) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.remove('is-valid');
  el.classList.add('is-invalid');
  errBox(el).textContent = msg;
}

function setValid(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.remove('is-invalid');
  el.classList.add('is-valid');
  errBox(el).textContent = '';
}

function clearErrors(form) {
  form.querySelectorAll('.is-invalid, .is-valid').forEach(e => e.classList.remove('is-invalid', 'is-valid'));
  form.querySelectorAll('.field-error').forEach(e => e.textContent = '');
  $('#alertBox').classList.add('d-none');
}
function showAlert(title, text, ok = false) {
  $('#alertBox').classList.toggle('ok', ok);
  $('#alertTitle').textContent = title;
  $('#alertText').textContent = text;
  $('#alertBox').classList.remove('d-none');
}
async function post(url, data) {
  const res = await fetch(url, {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin', body: JSON.stringify(data)
  });
  return res.json().catch(() => ({ success: false, message: 'Respon server tidak valid.' }));
}
function loading(btn, on, label) {
  btn.disabled = on;
  btn.innerHTML = on ? '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...' : label;
}

// ===== LOGIN =====
const loginForm = $('#loginForm');
if (loginForm) loginForm.addEventListener('submit', async e => {
  e.preventDefault();
  clearErrors(loginForm);
  const btn = $('#btnSubmit'), label = btn.innerHTML;
  const identifier = $('#identifier').value.trim(), password = $('#password').value;
  if (!identifier) return setInvalid('identifier', 'Email atau nomor HP wajib diisi.');
  if (!password) return setInvalid('password', 'Kata sandi wajib diisi.');

  loading(btn, true);
  try {
    const r = await post(loginForm.dataset.api, { identifier, password });
    if (r.success) { location.href = REDIRECT[r.role] || REDIRECT.user; return; }
    showAlert('Autentikasi Gagal', r.message);
    $('#identifier').classList.add('is-invalid');
    setInvalid('password', r.attempts_left !== undefined
      ? `Kata sandi tidak sesuai. Sisa ${r.attempts_left} percobaan.` : r.message);
  } catch { showAlert('Koneksi Gagal', 'Tidak dapat terhubung ke server.'); }
  loading(btn, false, label);
});

// ===== REGISTER =====
const regForm = $('#registerForm');
if (regForm) {
  const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
  const nameRe = /^[\p{L}][\p{L}\s.'-]*$/u;

  // Ubah +62/62 jadi 0, lalu cek: diawali 08, total 10-13 digit
  function normalizePhone(v) {
    const p = v.replace(/[\s-]/g, '');
    if (!/^\+?\d+$/.test(p)) return null;
    const n = p.replace(/^(\+62|62)/, '0');
    return /^08\d{8,11}$/.test(n) ? n : null;
  }

  // Tiap validator mengembalikan pesan error, atau '' kalau valid
  const validators = {
    full_name() {
      const v = $('#full_name').value.trim();
      if (!v) return 'Nama lengkap wajib diisi.';
      if (v.length < 3) return 'Nama lengkap minimal 3 karakter.';
      if (v.length > 50) return 'Nama lengkap maksimal 50 karakter.';
      if (!nameRe.test(v)) return "Nama hanya boleh huruf, spasi, titik, apostrof, dan strip.";
      return '';
    },
    identifier() {
      const v = $('#identifier').value.trim();
      if (!v) return 'Email atau nomor HP wajib diisi.';
      if (v.includes('@')) return emailRe.test(v) ? '' : 'Format email tidak valid.';
      return normalizePhone(v) ? '' : 'Nomor HP harus 10-13 digit, diawali 08 atau +62.';
    },
    password() {
      const v = $('#password').value;
      if (!v) return 'Kata sandi wajib diisi.';
      if (v.length < 8) return 'Kata sandi minimal 8 karakter.';
      if (v.length > 64) return 'Kata sandi maksimal 64 karakter.';
      if (!/[A-Za-z]/.test(v) || !/\d/.test(v)) return 'Kata sandi harus mengandung huruf dan angka.';
      return '';
    },
    password_confirm() {
      const v = $('#password_confirm').value;
      if (!v) return 'Konfirmasi kata sandi wajib diisi.';
      return v === $('#password').value ? '' : 'Konfirmasi kata sandi tidak sama.';
    }
  };

  // Tampilkan merah kalau salah, hapus merah (jadi hijau) kalau sudah benar
 function validateField(id) {
  const msg = validators[id]();
  if (msg) setInvalid(id, msg);
  else setValid(id);
  return !msg;
}

  Object.keys(validators).forEach(id => {
    const el = $('#' + id);
    el.addEventListener('input', () => {
      el.dataset.touched = '1';
      validateField(id);
      // Kalau password diubah, cek ulang konfirmasinya juga
      if (id === 'password' && $('#password_confirm').dataset.touched) validateField('password_confirm');
    });
    el.addEventListener('blur', () => { if (el.dataset.touched) validateField(id); });
  });

  $('#terms').addEventListener('change', () => $('#alertBox').classList.add('d-none'));

  regForm.addEventListener('submit', async e => {
    e.preventDefault();
    $('#alertBox').classList.add('d-none');
    const btn = $('#btnSubmit'), label = btn.innerHTML;

    // Validasi semua field, lalu fokus ke yang pertama salah
    const results = Object.keys(validators).map(id => [id, validateField(id)]);
    const firstBad = results.find(([, ok]) => !ok);
    if (!$('#terms').checked) showAlert('Persetujuan diperlukan', 'Centang syarat & ketentuan untuk melanjutkan.');
    if (firstBad) { $('#' + firstBad[0]).focus(); return; }
    if (!$('#terms').checked) return;

    const data = {
      role: document.querySelector('input[name="role"]:checked').value,
      full_name: $('#full_name').value.trim(),
      identifier: $('#identifier').value.trim(),
      password: $('#password').value,
      password_confirm: $('#password_confirm').value,
      terms: true
    };

    loading(btn, true);
    try {
      const r = await post(regForm.dataset.api, data);
      if (r.success) {
        showAlert('Berhasil', r.message, true);
        setTimeout(() => location.href = 'login.php', 1200);
        return;
      }
      if (r.errors) Object.entries(r.errors).forEach(([k, v]) => setInvalid(k, v));
      else showAlert('Pendaftaran Gagal', r.message);
    } catch { showAlert('Koneksi Gagal', 'Tidak dapat terhubung ke server.'); }
    loading(btn, false, label);
  });
}

// ===== LOGIN / DAFTAR DENGAN GOOGLE =====
const gBox = $('#googleBtn');
if (gBox) {
  const roleModalEl = $('#roleModal');                       // hanya ada di halaman login
  const roleModal = roleModalEl ? new bootstrap.Modal(roleModalEl) : null;
  let pendingCredential = null;

  async function sendGoogle(credential, role) {
    $('#alertBox').classList.add('d-none');
    const payload = { credential };
    if (role) payload.role = role;
    try {
      const r = await post(gBox.dataset.api, payload);
      if (r.success && r.need_role && roleModal) {            // akun baru: tanya jenis akun
        pendingCredential = credential;
        roleModal.show();
        return;
      }
      if (r.success) { location.href = REDIRECT[r.role] || REDIRECT.user; return; }
      showAlert('Masuk dengan Google gagal', r.message);
    } catch { showAlert('Koneksi Gagal', 'Tidak dapat terhubung ke server.'); }
  }

  // Status pustaka Google & pengaman kalau pustakanya gagal dimuat.
  //
  // Catatan: error "origin is not allowed" datang dari dalam iframe milik
  // Google, jadi tidak bisa dibaca dari script halaman ini. Untuk kasus itu,
  // cek Google Cloud Console -> Authorized JavaScript origins (lihat petunjuk
  // di bawah tombol pada halaman login).
  let googleSiap = false;
  const cekGoogleSiap = setTimeout(() => {
    if (!googleSiap && !document.querySelector('#googleBtn iframe')) {
      showAlert('Tombol Google Tidak Muncul', 'Pustaka Google tidak bisa dimuat. Periksa koneksi internet atau ad-blocker Anda.');
    }
  }, 8000);

  // Dipanggil otomatis oleh skrip Google setelah selesai dimuat
  window.onGoogleLibraryLoad = () => {
    googleSiap = true;
    clearTimeout(cekGoogleSiap);

    if (!window.google || !google.accounts || !google.accounts.id) {
      showAlert('Tombol Google Tidak Muncul', 'Pustaka Google tidak tersedia. Muat ulang halaman atau matikan ad-blocker.');
      return;
    }

    google.accounts.id.initialize({
      client_id: gBox.dataset.clientId,
      callback: resp => {
        const radio = document.querySelector('input[name="role"]:checked');   // ada di halaman daftar
        sendGoogle(resp.credential, radio ? radio.value : null);
      }
    });
    google.accounts.id.renderButton(gBox, {
      type: 'standard', theme: 'outline', size: 'large', locale: 'id',
      text: gBox.dataset.text || 'continue_with', logo_alignment: 'center',
      width: Math.min(400, Math.round(gBox.getBoundingClientRect().width)) || 320
    });
  };

  // Pilihan di modal -> kirim ulang token bersama jenis akun yang dipilih
  document.querySelectorAll('[data-role-pick]').forEach(btn => btn.addEventListener('click', () => {
    roleModal.hide();
    if (pendingCredential) sendGoogle(pendingCredential, btn.dataset.rolePick);
  }));
}