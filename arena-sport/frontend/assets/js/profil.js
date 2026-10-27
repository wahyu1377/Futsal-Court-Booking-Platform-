/* ============================================================
   profil.js
   Halaman Edit Profil (frontend/pages/user/edit-profil.php dan
   frontend/pages/owner/edit-profil.php)

   Script ini dipakai bersama oleh akun pengguna dan akun owner.
   Alamat API dibaca dari atribut data-api pada elemen #epRoot,
   jadi halaman tinggal menunjuk API yang sesuai.

   - Menyimpan nama / email / nomor WhatsApp  -> API (aksi=update)
   - Mengunggah & menghapus foto profil      -> API (aksi=ganti/hapus)
   ============================================================ */
(() => {
  const $ = id => document.getElementById(id);

  const root = $('epRoot');
  const form = $('opForm');
  const alertBox = $('opAlert');
  const btnSimpan = $('opSimpan');
  if (!root || !form) return;

  const API = root.dataset.api;

  // Tampilkan pesan error / sukses
  function pesan(text, ok = false) {
    alertBox.textContent = text;
    alertBox.className = 'op-alert ' + (ok ? 'ok' : 'err');
    alertBox.classList.remove('d-none');
  }
  function bersihkanPesan() {
    alertBox.className = 'op-alert d-none';
  }

  function sibuk(on) {
    btnSimpan.disabled = on;
    btnSimpan.innerHTML = on
      ? '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...'
      : '<i class="bi bi-check2-circle me-1"></i> Simpan Perubahan';
  }

  // Bersihkan nomor telepon: hanya angka, "+62"/"62" diubah jadi "0"
  function bersihkanTelepon(nilai) {
    return nilai.replace(/[^\d]/g, '').replace(/^(\+?62|62)/, '0');
  }

  /* ---------------------------------------------------------
     Simpan data akun
     --------------------------------------------------------- */
  form.addEventListener('submit', async (evt) => {
    evt.preventDefault();
    bersihkanPesan();

    const nama = $('opNama').value.trim();
    const email = $('opEmail').value.trim();
    const telepon = bersihkanTelepon($('opTelepon').value.trim());

    // Validasi ringan di sisi browser (server juga memvalidasi ulang)
    if (nama.length < 3) return pesan('Nama lengkap minimal 3 karakter.');
    if (!/^\S+@\S+\.\S+$/.test(email)) return pesan('Format email tidak valid.');
    if (telepon !== '' && !/^0[0-9]{8,12}$/.test(telepon)) {
      return pesan('Nomor WhatsApp tidak valid. Contoh: 081234567890.');
    }

    sibuk(true);
    try {
      const res = await fetch(API, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ aksi: 'update', nama, email, telepon }),
      });
      const hasil = await res.json();

      if (hasil.need_login) {
        location.href = '../auth/login.php';
        return;
      }
      if (!hasil.success) {
        pesan(hasil.message || 'Profil gagal disimpan.');
        return;
      }

      pesan('Profil berhasil diperbarui.', true);
      setTimeout(() => { location.href = 'akun.php'; }, 900);
    } catch {
      pesan('Tidak dapat terhubung ke server. Coba lagi.');
    } finally {
      sibuk(false);
    }
  });

  // Ketik ulang -> pesan lama dibuang
  form.addEventListener('input', bersihkanPesan);

  /* ---------------------------------------------------------
     Foto profil
     --------------------------------------------------------- */
  const fileInput = $('fileInput');
  const btnPilih = $('btnPilih');
  const btnSimpanFoto = $('btnSimpanFoto');
  const btnHapusFoto = $('btnHapusFoto');
  const preview = $('previewImg');
  if (!fileInput || !btnPilih || !btnSimpanFoto) return;

  const SIZE = 512;                       // foto dikecilkan di browser sebelum dikirim
  const MAX_RAW = 10 * 1024 * 1024;       // foto asli maksimal 10 MB
  const TYPES = ['image/jpeg', 'image/png', 'image/webp'];

  let blob = null, objectUrl = null;

  function sibukFoto(on) {
    [btnPilih, btnSimpanFoto, btnHapusFoto].forEach(b => { if (b) b.disabled = on; });
    if (!on) btnSimpanFoto.disabled = !blob;
    btnSimpanFoto.innerHTML = on
      ? '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...'
      : '<i class="bi bi-check2-circle me-1"></i> Simpan Foto';
  }

  // Potong persegi dari tengah, kecilkan, ubah ke JPEG
  async function cropSquare(file) {
    let bmp;
    try { bmp = await createImageBitmap(file, { imageOrientation: 'from-image' }); }   // hormati orientasi foto HP
    catch { bmp = await createImageBitmap(file); }

    const side = Math.min(bmp.width, bmp.height);
    const canvas = document.createElement('canvas');
    canvas.width = canvas.height = SIZE;
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#fff';               // PNG transparan -> latar putih
    ctx.fillRect(0, 0, SIZE, SIZE);
    ctx.drawImage(bmp, (bmp.width - side) / 2, (bmp.height - side) / 2, side, side, 0, 0, SIZE, SIZE);
    if (bmp.close) bmp.close();

    return new Promise((ok, fail) =>
      canvas.toBlob(b => b ? ok(b) : fail(new Error('toBlob gagal')), 'image/jpeg', 0.88));
  }

  async function kirimFoto(fd, pesanOk) {
    sibukFoto(true);
    try {
      const res = await fetch(API, { method: 'POST', body: fd, credentials: 'same-origin' });
      const hasil = await res.json();

      if (hasil.need_login) { location.href = '../auth/login.php'; return; }
      if (!hasil.success) { pesan(hasil.message || 'Foto gagal disimpan.'); return; }

      pesan(pesanOk, true);
      setTimeout(() => { location.href = 'akun.php'; }, 900);
    } catch {
      pesan('Tidak dapat terhubung ke server. Coba lagi.');
    } finally {
      sibukFoto(false);
    }
  }

  btnPilih.addEventListener('click', () => fileInput.click());

  fileInput.addEventListener('change', async () => {
    const f = fileInput.files[0];
    fileInput.value = '';                 // supaya foto yang sama bisa dipilih ulang
    if (!f) return;
    if (!TYPES.includes(f.type)) return pesan('Format foto harus JPG, PNG, atau WebP.');
    if (f.size > MAX_RAW) return pesan('Ukuran foto terlalu besar (maksimal 10 MB).');

    try { blob = await cropSquare(f); }
    catch { blob = null; btnSimpanFoto.disabled = true; return pesan('Foto tidak dapat dibaca. Coba foto lain.'); }

    if (objectUrl) URL.revokeObjectURL(objectUrl);
    objectUrl = URL.createObjectURL(blob);
    preview.src = objectUrl;              // pratinjau persis seperti hasil yang akan disimpan
    btnSimpanFoto.disabled = false;
    bersihkanPesan();
  });

  btnSimpanFoto.addEventListener('click', () => {
    if (!blob) return;
    const fd = new FormData();
    fd.append('aksi', 'ganti');
    fd.append('foto', blob, 'foto.jpg');
    kirimFoto(fd, 'Foto profil berhasil diperbarui.');
  });

  if (btnHapusFoto) btnHapusFoto.addEventListener('click', () => {
    if (!confirm('Hapus foto profil? Avatar akan kembali berupa inisial nama.')) return;
    const fd = new FormData();
    fd.append('aksi', 'hapus');
    kirimFoto(fd, 'Foto profil dihapus.');
  });
})();
