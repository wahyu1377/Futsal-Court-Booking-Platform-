// Dipakai bersama oleh beranda dan halaman favorit
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const cap = s => s.charAt(0).toUpperCase() + s.slice(1);
const rupiah = n => 'Rp' + Number(n).toLocaleString('id-ID');

function renderCard(c, IMG, DETAIL) {
  const tags = [cap(c.jenis), ...(c.fasilitas ? c.fasilitas.split(',').map(t => t.trim()).filter(Boolean) : [])].slice(0, 3);
  const foto = c.foto ? `<img src="${esc(IMG + c.foto)}" alt="${esc(c.nama)}" loading="lazy" onerror="this.remove()">` : '';
  const avail = c.tersedia_hari_ini == 1
    ? '<span class="avail">Tersedia Hari Ini</span>'
    : '<span class="avail off">Penuh Hari Ini</span>';
  const fav = !!c.favorit;
  return `
  <div class="col-12 col-md-6 col-xl-4">
    <article class="court-card">
      <div class="court-img">
        ${foto}
        <button type="button" class="fav-btn${fav ? ' is-on' : ''}" data-id="${Number(c.id)}"
                aria-pressed="${fav}" aria-label="${fav ? 'Hapus dari favorit' : 'Tambah ke favorit'}">
          <i class="bi ${fav ? 'bi-heart-fill' : 'bi-heart'}"></i>
        </button>
        <div class="court-tags">${tags.map(t => `<span>${esc(t)}</span>`).join('')}</div>
        <div class="court-rating"><i class="bi bi-star-fill"></i> ${Number(c.rating).toFixed(1)} <small>(${Number(c.jumlah_ulasan)})</small></div>
      </div>
      <div class="court-body">
        <h3>${esc(c.nama)}</h3>
        <div class="court-loc mb-2"><i class="bi bi-geo-alt me-1"></i>${esc(c.kecamatan)}, ${esc(c.kota)}</div>
        <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
          ${avail}${c.keterangan ? `<span class="court-loc">${esc(c.keterangan)}</span>` : ''}
        </div>
        <div class="d-flex justify-content-between align-items-end mt-auto">
          <div>
            <div class="price-label">TARIF SEWA</div>
            <div class="price">${rupiah(c.harga_per_jam)} <small>/ jam</small></div>
          </div>
          <a href="${DETAIL}?id=${Number(c.id)}" class="btn btn-main">Pilih Lapangan <i class="bi bi-chevron-right ms-1"></i></a>
        </div>
      </div>
    </article>
  </div>`;
}