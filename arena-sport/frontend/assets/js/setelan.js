/* ============================================================
   setelan.js
   Halaman Setelan (frontend/pages/user/setelan.php dan
   frontend/pages/owner/setelan.php)

   Script ini dipakai bersama oleh akun pengguna dan akun owner.
   Alamat API dibaca dari atribut data-api & data-api-prefs pada #setRoot.

   - Ganti kata sandi           -> API profil (aksi=ganti_password)
   - Preferensi notifikasi     -> API setelan (tabel *_settings)
     Fallback ke localStorage kalau tabelnya belum tersedia.
   ============================================================ */
(() => {
  const $ = id => document.getElementById(id);

  const root = $('setRoot');
  const alertBox = $('setAlert');
  if (!root) return;

  const API = root.dataset.api;
  const API_PREFS = root.dataset.apiPrefs;
  // Kunci localStorage dibuat per jenis akun, jadi pengaturan owner dan
  // pengguna tidak saling menimpa di perangkat yang sama.
  const KUNCI_PREFS = root.dataset.prefsKey || 'arena-sport-prefs';

  function pesan(text, ok = false) {
    alertBox.textContent = text;
    alertBox.className = 'op-alert ' + (ok ? 'ok' : 'err');
    alertBox.classList.remove('d-none');
  }
  function bersihkanPesan() {
    alertBox.className = 'op-alert d-none';
  }

  /* ---------------------------------------------------------
     Preferensi notifikasi
     Sumber utama: database (owner_settings).
     Cadangan: localStorage perangkat ini.
     --------------------------------------------------------- */
  const prefsDefault = { notifikasi_pesanan: true, notifikasi_email: false, notifikasi_wa: true };

  const cbs = {
    notifikasi_pesanan: $('notifPesanan'),
    notifikasi_email: $('notifEmail'),
    notifikasi_wa: $('notifWa'),
  };

  // Baca cadangan dari perangkat (dipakai sebelum server merespons)
  function bacaLokal() {
    try {
      const mentah = localStorage.getItem(KUNCI_PREFS);
      if (!mentah) return { ...prefsDefault };
      return { ...prefsDefault, ...JSON.parse(mentah) };
    } catch {
      return { ...prefsDefault };
    }
  }

  function simpanLokal(prefs) {
    try {
      localStorage.setItem(KUNCI_PREFS, JSON.stringify(prefs));
    } catch {
      // localStorage penuh / diblokir browser: abaikan saja
    }
  }

  // Terapkan nilai ke checkbox
  function terapkan(prefs) {
    Object.entries(cbs).forEach(([kunci, cb]) => {
      if (cb && typeof prefs[kunci] === 'boolean') cb.checked = prefs[kunci];
    });
  }

  // Muat preferensi dari server (kalau gagal, pakai yang tersimpan di perangkat)
  async function muatPrefs() {
    terapkan(bacaLokal());
    try {
      const res = await fetch(API_PREFS, { credentials: 'same-origin' });
      const hasil = await res.json();
      if (!hasil.success) return;

      terapkan(hasil.prefs || prefsDefault);
      if (hasil.tersimpan_server) {
        simpanLokal(hasil.prefs || prefsDefault);
        const status = $('notifStatus');
        if (status) status.textContent = 'Preferensi notifikasi disimpan di server, jadi berlaku di semua perangkat.';
      }
    } catch {
      // offline / server tak terjangkau: andalkan nilai dari perangkat
    }
  }

  // Simpan preferensi ke server (dan ke perangkat sebagai cadangan)
  async function simpanPrefs() {
    const prefs = {
      notifikasi_pesanan: cbs.notifikasi_pesanan.checked,
      notifikasi_email: cbs.notifikasi_email.checked,
      notifikasi_wa: cbs.notifikasi_wa.checked,
    };
    simpanLokal(prefs);

    try {
      const res = await fetch(API_PREFS, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ aksi: 'simpan', ...prefs }),
      });
      const hasil = await res.json();

      if (!hasil.success) {
        // Server belum siap (tabel pengaturan belum ada): simpan di perangkat saja
        const status = $('notifStatus');
        if (status) status.textContent = 'Server belum siap menyimpan preferensi, jadi disimpan di perangkat ini saja.';
        return;
      }
      const status = $('notifStatus');
      if (status) status.textContent = 'Tersimpan di server, berlaku di semua perangkat.';
    } catch {
      const status = $('notifStatus');
      if (status) status.textContent = 'Tidak dapat terhubung ke server, preferensi disimpan di perangkat ini saja.';
    }
  }

  Object.values(cbs).forEach(cb => {
    if (cb) cb.addEventListener('change', simpanPrefs);
  });

  muatPrefs();

  /* ---------------------------------------------------------
     Ganti kata sandi
     --------------------------------------------------------- */
  const form = $('formPassword');
  const btn = $('btnSimpanPw');
  if (!form || !btn) return;

  function sibuk(on) {
    btn.disabled = on;
    btn.innerHTML = on
      ? '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...'
      : '<i class="bi bi-key me-1"></i> Perbarui Kata Sandi';
  }

  form.addEventListener('submit', async (evt) => {
    evt.preventDefault();
    bersihkanPesan();

    const lama = $('pwLama').value;
    const baru = $('pwBaru').value;
    const ulang = $('pwUlangi').value;

    if (lama === '' || baru === '') return pesan('Kata sandi lama dan baru wajib diisi.');
    if (baru.length < 8) return pesan('Kata sandi baru minimal 8 karakter.');
    if (baru !== ulang) return pesan('Konfirmasi kata sandi tidak cocok.');

    sibuk(true);
    try {
      const res = await fetch(API, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          aksi: 'ganti_password',
          password_lama: lama,
          password_baru: baru,
          password_konfirmasi: ulang,
        }),
      });
      const hasil = await res.json();

      if (hasil.need_login) {
        location.href = '../auth/login.php';
        return;
      }
      if (!hasil.success) {
        pesan(hasil.message || 'Kata sandi gagal diperbarui.');
        return;
      }

      pesan('Kata sandi berhasil diubah.', true);
      form.reset();
    } catch {
      pesan('Tidak dapat terhubung ke server. Coba lagi.');
    } finally {
      sibuk(false);
    }
  });
})();
