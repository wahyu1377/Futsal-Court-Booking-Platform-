(() => {
  const API = '../../../backend/api/user/favorit.php';
  const LOGIN = '../auth/login.php';
  const busy = new Set();

  function toast(msg) {
    const t = document.createElement('div');
    t.className = 'mini-toast';
    t.setAttribute('role', 'status');
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 2600);
  }

  // Ubah tampilan semua tombol hati milik lapangan yang sama
  function paint(id, on) {
    document.querySelectorAll(`.fav-btn[data-id="${id}"]`).forEach(b => {
      b.classList.toggle('is-on', on);
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
      b.setAttribute('aria-label', on ? 'Hapus dari favorit' : 'Tambah ke favorit');
      const i = b.querySelector('i');
      if (i) i.className = 'bi ' + (on ? 'bi-heart-fill' : 'bi-heart');
    });
  }

  document.addEventListener('click', async e => {
    const btn = e.target.closest('.fav-btn');
    if (!btn) return;
    e.preventDefault();

    const id = Number(btn.dataset.id);
    if (!id || busy.has(id)) return;
    const want = btn.getAttribute('aria-pressed') !== 'true';

    busy.add(id);
    paint(id, want);                       // langsung berubah, tidak menunggu server
    try {
      const res = await fetch(API, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin', body: JSON.stringify({ lapangan_id: id, aktif: want })
      });
      const r = await res.json().catch(() => null);
      if (!r) throw new Error('Server tidak merespons dengan benar.');
      if (r.need_login) { paint(id, !want); location.href = LOGIN; return; }
      if (!r.success) throw new Error(r.message);

      toast(r.message);
      document.dispatchEvent(new CustomEvent('fav:changed', { detail: { id, aktif: want } }));
    } catch (err) {
      paint(id, !want);                    // gagal: kembalikan
      toast(err.message || 'Gagal menyimpan favorit.');
    } finally {
      busy.delete(id);
    }
  });

  // Kembali lewat tombol Back: muat ulang supaya status hati tidak basi
  window.addEventListener('pageshow', e => { if (e.persisted) location.reload(); });
})();