(() => {
  const $ = id => document.getElementById(id);
  const root = $('epRoot');
  const API = root.dataset.api;
  const SIZE = 512;                       // foto dikecilkan ke 512x512 di browser sebelum dikirim
  const MAX_RAW = 10 * 1024 * 1024;       // foto asli maksimal 10 MB
  const TYPES = ['image/jpeg', 'image/png', 'image/webp'];

  const fileInput = $('fileInput'), preview = $('previewImg');
  const btnPilih = $('btnPilih'), btnSimpan = $('btnSimpan'), btnHapus = $('btnHapus');
  let blob = null, objectUrl = null;

  function msg(text, ok = false) {
    const a = $('epAlert');
    a.textContent = text;
    a.className = 'ep-alert ' + (ok ? 'ok' : 'err');
  }
  function clearMsg() { $('epAlert').className = 'ep-alert d-none'; }

  function busy(on) {
    [btnPilih, btnSimpan, btnHapus].forEach(b => { if (b) b.disabled = on; });
    if (!on) btnSimpan.disabled = !blob;
    btnSimpan.innerHTML = on
      ? '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...'
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

  async function kirim(fd, pesanOk) {
    busy(true);
    try {
      const res = await fetch(API, { method: 'POST', body: fd, credentials: 'same-origin' });
      const r = await res.json();
      if (r.need_login) { location.href = '../auth/login.php'; return; }
      if (!r.success) { msg(r.message); return; }
      msg(pesanOk, true);
      setTimeout(() => { location.href = 'akun.php'; }, 900);
    } catch {
      msg('Tidak dapat terhubung ke server. Coba lagi.');
    } finally {
      busy(false);
    }
  }

  btnPilih.addEventListener('click', () => fileInput.click());

  fileInput.addEventListener('change', async () => {
    const f = fileInput.files[0];
    fileInput.value = '';                 // supaya foto yang sama bisa dipilih ulang
    if (!f) return;
    if (!TYPES.includes(f.type)) return msg('Format foto harus JPG, PNG, atau WebP.');
    if (f.size > MAX_RAW) return msg('Ukuran foto terlalu besar (maksimal 10 MB).');

    try { blob = await cropSquare(f); }
    catch { blob = null; btnSimpan.disabled = true; return msg('Foto tidak dapat dibaca. Coba foto lain.'); }

    if (objectUrl) URL.revokeObjectURL(objectUrl);
    objectUrl = URL.createObjectURL(blob);
    preview.src = objectUrl;              // pratinjau persis seperti hasil yang akan disimpan
    btnSimpan.disabled = false;
    clearMsg();
  });

  btnSimpan.addEventListener('click', () => {
    if (!blob) return;
    const fd = new FormData();
    fd.append('aksi', 'ganti');
    fd.append('foto', blob, 'foto.jpg');
    kirim(fd, 'Foto profil berhasil diperbarui.');
  });

  if (btnHapus) btnHapus.addEventListener('click', () => {
    if (!confirm('Hapus foto profil? Avatar akan kembali berupa inisial nama.')) return;
    const fd = new FormData();
    fd.append('aksi', 'hapus');
    kirim(fd, 'Foto profil dihapus.');
  });
})();