// ============================================================
// Halaman "Lupa Kata Sandi" & "Reset Kata Sandi".
//
// Script ini memakai ulang helper dari auth.js ($, post, loading,
// showAlert, setInvalid, clearErrors) sehingga gaya tampilan dan
// cara handling error konsisten dengan halaman login & daftar.
//
// Semua interaksi memakai Vanilla JS - tidak ada handler inline di HTML.
// ============================================================

// Helper GET JSON (auth.js hanya menyediakan POST).
async function getJson(url) {
  const res = await fetch(url, {
    method: 'GET',
    headers: { 'Accept': 'application/json' },
    credentials: 'same-origin'
  });
  return res.json().catch(() => ({ success: false, message: 'Respon server tidak valid.' }));
}

// ------------------------------------------------------------
// HALAMAN: Lupa Kata Sandi (permintaan tautan reset)
// ------------------------------------------------------------
const forgotForm = document.getElementById('forgotForm');

if (forgotForm) {
  const btnSubmit = document.getElementById('btnSubmit');
  const demoBox = document.getElementById('demoBox');
  const demoLink = document.getElementById('demoLink');

  // Cegah pengiriman ulang saat cooldown masih berjalan.
  let cooldownDetik = 0;
  let timerCooldown = null;

  function mulaiCooldown(detik) {
    cooldownDetik = detik;
    if (timerCooldown) clearInterval(timerCooldown);

    const labelAwal = btnSubmit.innerHTML;
    const terapkan = () => {
      if (cooldownDetik <= 0) {
        clearInterval(timerCooldown);
        timerCooldown = null;
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = labelAwal;
        return;
      }
      btnSubmit.disabled = true;
      btnSubmit.innerHTML =
        `<span class="spinner-border spinner-border-sm me-2"></span>Tunggu ${cooldownDetik} detik...`;
      cooldownDetik -= 1;
    };

    terapkan();
    timerCooldown = setInterval(terapkan, 1000);
  }

  forgotForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearErrors(forgotForm);
    demoBox.classList.add('d-none');

    const identifier = $('#identifier').value.trim();
    if (!identifier) {
      setInvalid('identifier', 'Email atau nomor HP wajib diisi.');
      return;
    }

    const labelTombol = btnSubmit.innerHTML;
    let cooldown = 0;
    loading(btnSubmit, true);

    try {
      const r = await post(forgotForm.dataset.api, { identifier });

      if (r.success) {
        showAlert('Permintaan Diterima', r.message, true);
        // Mode demo (MAIL_MODE=log): tautan ditampilkan langsung di layar.
        if (r.tautan_demo && demoLink) {
          demoLink.href = r.tautan_demo;
          demoLink.textContent = r.tautan_demo;
          demoBox.classList.remove('d-none');
        }
        cooldown = r.cooldown || 0;
      } else {
        showAlert('Gagal Mengirim Tautan', r.message);
        if (r.errors) {
          Object.entries(r.errors).forEach(([k, v]) => setInvalid(k, v));
        }
      }
    } catch {
      showAlert('Koneksi Gagal', 'Tidak dapat terhubung ke server.');
    }

    // Tombol dikembalikan ke kondisi normal dulu, baru cooldown dipasang
    // (kalau cooldown dipasang lebih dulu, label tombol tertimpa).
    loading(btnSubmit, false, labelTombol);
    if (cooldown > 0) mulaiCooldown(cooldown);
  });
}

// ------------------------------------------------------------
// HALAMAN: Reset Kata Sandi (cek token lalu ganti kata sandi)
// ------------------------------------------------------------
const resetForm = document.getElementById('resetForm');

if (resetForm) {
  const tokenStatus = document.getElementById('tokenStatus');
  const btnSubmit = document.getElementById('btnSubmit');
  const token = resetForm.dataset.token || '';

  // Helper kecil: sembunyikan spinner & tampilkan pesan status
  function statusGagal(pesan) {
    tokenStatus.classList.remove('d-none');
    tokenStatus.innerHTML =
      `<p class="text-secondary small mb-0">${pesan}</p>`;
  }

  // 1. Cek apakah token masih berlaku sebelum form dibuka
  function cekToken() {
    if (!token) {
      statusGagal('Token reset tidak ditemukan. Silakan minta tautan baru.');
      showAlert('Tautan Tidak Berlaku', 'Token reset tidak ditemukan. Silakan minta tautan baru.');
      return;
    }

    getJson(`${resetForm.dataset.api}?token=${encodeURIComponent(token)}`)
      .then((r) => {
        if (r.success && r.valid) {
          tokenStatus.classList.add('d-none');
          resetForm.classList.remove('d-none');
          return;
        }
        statusGagal('Silakan minta tautan reset yang baru.');
        showAlert('Tautan Tidak Berlaku', r.message || 'Tautan reset kata sandi sudah tidak berlaku.');
      })
      .catch(() => {
        statusGagal('Tidak dapat menghubungi server.');
        showAlert('Koneksi Gagal', 'Tidak dapat terhubung ke server.');
      });
  }

  cekToken();

  // 2. Validasi kata sandi baru di sisi klien (server tetap memvalidasi ulang)
  const validators = {
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

  function validateField(id) {
    const msg = validators[id]();
    if (msg) {
      setInvalid(id, msg);
    } else {
      setValid(id);
    }
    return !msg;
  }

  Object.keys(validators).forEach((id) => {
    const el = document.getElementById(id);
    el.addEventListener('input', () => {
      el.dataset.touched = '1';
      validateField(id);
      if (id === 'password' && $('#password_confirm').dataset.touched) {
        validateField('password_confirm');
      }
    });
    el.addEventListener('blur', () => {
      if (el.dataset.touched) validateField(id);
    });
  });

  // 3. Kirim kata sandi baru ke server
  resetForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearErrors(resetForm);

    const results = Object.keys(validators).map((id) => [id, validateField(id)]);
    const firstBad = results.find(([, ok]) => !ok);
    if (firstBad) {
      document.getElementById(firstBad[0]).focus();
      return;
    }

    const labelTombol = btnSubmit.innerHTML;
    loading(btnSubmit, true);

    try {
      const r = await post(resetForm.dataset.api, {
        token: token,
        password: $('#password').value,
        password_confirm: $('#password_confirm').value
      });

      if (r.success) {
        showAlert('Berhasil', r.message, true);
        btnSubmit.disabled = true;
        setTimeout(() => {
          location.href = r.redirect || 'login.php?reset=1';
        }, 1500);
        return;
      }

      showAlert('Gagal Memperbarui', r.message);
      if (r.errors) {
        Object.entries(r.errors).forEach(([k, v]) => setInvalid(k, v));
      }
      // Token yang sudah dipakai / kedaluwarsa: form tidak bisa lagi dipakai.
      if (r.valid === false) {
        resetForm.classList.add('d-none');
        statusGagal('Silakan minta tautan reset yang baru.');
      }
    } catch {
      showAlert('Koneksi Gagal', 'Tidak dapat terhubung ke server.');
    }

    loading(btnSubmit, false, labelTombol);
  });
}
