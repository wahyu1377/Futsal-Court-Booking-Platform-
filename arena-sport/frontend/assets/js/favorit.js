(() => {
  const list = document.getElementById('courtList');
  const info = document.getElementById('favInfo');
  const API = list.dataset.api, IMG = list.dataset.img, DETAIL = list.dataset.detail;

  const kosong = `
    <div class="col-12 empty">
      <i class="bi bi-bookmark-heart fs-1 d-block mb-2"></i>
      <strong class="d-block mb-1">Belum ada lapangan favorit</strong>
      Ketuk ikon hati pada lapangan untuk menyimpannya di sini.
      <div class="mt-3"><a href="beranda.php" class="btn btn-main">Cari Lapangan</a></div>
    </div>`;

  function setInfo() {
    const n = list.querySelectorAll('.court-card').length;
    info.textContent = n ? `${n} lapangan tersimpan` : '';
  }

  async function load() {
    list.innerHTML = [1, 2, 3].map(() => '<div class="col-12 col-md-6 col-xl-4"><div class="skeleton"></div></div>').join('');
    try {
      const r = await (await fetch(API, { credentials: 'same-origin' })).json();
      if (!r.success) throw new Error(r.message);
      list.innerHTML = r.data.length ? r.data.map(c => renderCard(c, IMG, DETAIL)).join('') : kosong;
      setInfo();
    } catch (err) {
      console.error('Gagal memuat favorit:', err);
      info.textContent = '';
      list.innerHTML = '<div class="col-12 empty">Gagal memuat data.<br><small>' + esc(err.message) + '</small></div>';
    }
  }

  // Hati dimatikan di halaman ini: kartunya langsung hilang dari daftar
  document.addEventListener('fav:changed', e => {
    if (e.detail.aktif) return;
    list.querySelector(`.fav-btn[data-id="${e.detail.id}"]`)?.closest('.col-12')?.remove();
    if (!list.querySelector('.court-card')) list.innerHTML = kosong;
    setInfo();
  });

  load();
})();