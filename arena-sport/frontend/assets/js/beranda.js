const $ = id => document.getElementById(id);
const list   = $('courtList');
const chips  = $('chips');
const search = $('searchInput');
const info   = $('resultInfo');
const panel  = $('filterPanel');
const badge  = $('filterCount');
const API = list.dataset.api, IMG = list.dataset.img, DETAIL = list.dataset.detail;
const card = c => renderCard(c, IMG, DETAIL);

const defaultFilter = () => ({ urut: 'rating', min: '', max: '', rating: '0', tersedia: false, fasilitas: [] });
let f = defaultFilter();      // filter dari panel
let jenis = '';               // dari chip kategori
let q = '';                   // dari kolom cari
let timer, reqId = 0, panelReady = false;

function renderChips(counts) {
  const total = Object.values(counts).reduce((a, b) => a + Number(b), 0);
  const items = [['', 'Semua', total], ...Object.entries(counts).map(([k, v]) => [k, cap(k), v])];
  chips.innerHTML = items.map(([k, label, n]) =>
    `<button type="button" class="chip ${k === jenis ? 'active' : ''}" data-jenis="${esc(k)}">${esc(label)} <span class="n">${n}</span></button>`
  ).join('');
}

function showLoading() {
  list.innerHTML = [1, 2, 3].map(() => '<div class="col-12 col-md-6 col-xl-4"><div class="skeleton"></div></div>').join('');
}

// ===== FILTER =====
function activeCount() {
  return Number(f.urut !== 'rating')
       + Number(f.min !== '' || f.max !== '')
       + Number(f.rating !== '0')
       + Number(f.tersedia)
       + f.fasilitas.length;
}

function updateBadge() {
  const n = activeCount();
  badge.textContent = n;
  badge.classList.toggle('d-none', n === 0);
}

// Isi panel dengan pilihan dari server (hanya sekali)
function setupPanel(opt) {
  if (panelReady) return;
  panelReady = true;
  const box = $('fFasilitas');
  box.innerHTML = opt.fasilitas.length
    ? opt.fasilitas.map((n, i) =>
        `<input type="checkbox" class="btn-check" id="fas${i}" value="${esc(n)}"><label class="opt" for="fas${i}">${esc(n)}</label>`).join('')
    : '<small class="text-secondary">Belum ada data fasilitas.</small>';
  $('fMin').placeholder = rupiah(opt.harga_min);
  $('fMax').placeholder = rupiah(opt.harga_max);
}

// Samakan isi form dengan filter yang sedang aktif
function syncForm() {
  $('fUrut').value = f.urut;
  $('fMin').value = f.min;
  $('fMax').value = f.max;
  const radio = document.querySelector(`input[name="fRating"][value="${f.rating}"]`);
  if (radio) radio.checked = true;
  $('fTersedia').checked = f.tersedia;
  document.querySelectorAll('#fFasilitas input').forEach(i => i.checked = f.fasilitas.includes(i.value));
  $('fHargaErr').textContent = '';
}

function buildParams() {
  const p = new URLSearchParams();
  if (jenis) p.set('jenis', jenis);
  if (q) p.set('q', q);
  if (f.min !== '') p.set('min_harga', f.min);
  if (f.max !== '') p.set('max_harga', f.max);
  if (f.rating !== '0') p.set('rating_min', f.rating);
  if (f.tersedia) p.set('tersedia', '1');
  if (f.fasilitas.length) p.set('fasilitas', f.fasilitas.join(','));
  p.set('urut', f.urut);
  return p.toString();
}

function resetFilters() {
  f = defaultFilter();
  syncForm();
  updateBadge();
  load();
}

$('fApply').addEventListener('click', () => {
  const min = $('fMin').value.trim(), max = $('fMax').value.trim();
  if (min !== '' && max !== '' && Number(min) > Number(max)) {
    $('fHargaErr').textContent = 'Tarif terendah tidak boleh lebih besar dari tarif tertinggi.';
    $('fMin').focus();
    return;
  }
  f = {
    urut: $('fUrut').value, min, max,
    rating: document.querySelector('input[name="fRating"]:checked').value,
    tersedia: $('fTersedia').checked,
    fasilitas: [...document.querySelectorAll('#fFasilitas input:checked')].map(i => i.value),
  };
  bootstrap.Offcanvas.getOrCreateInstance(panel).hide();
  updateBadge();
  load();
});

$('fReset').addEventListener('click', () => {
  bootstrap.Offcanvas.getOrCreateInstance(panel).hide();
  resetFilters();
});

// Kalau panel ditutup tanpa menekan "Tampilkan Hasil", batalkan perubahan di form
panel.addEventListener('hidden.bs.offcanvas', syncForm);

// ===== MUAT DATA =====
async function load() {
  const my = ++reqId;                 // abaikan respons lama kalau ada request yang lebih baru
  showLoading();
  try {
    const r = await (await fetch(`${API}?${buildParams()}`)).json();
    if (my !== reqId) return;
    if (!r.success) throw new Error(r.message);

    renderChips(r.counts);
    setupPanel(r.filters);

    const n = r.data.length;
    const aktif = activeCount() > 0 || q !== '' || jenis !== '';
    info.innerHTML = n
      ? `${n} lapangan ditemukan` + (activeCount() ? ' · <a href="#" id="clearFilter" class="link-green">Hapus filter</a>' : '')
      : '';

    list.innerHTML = n
      ? r.data.map(card).join('')
      : `<div class="col-12 empty">
           <i class="bi bi-search fs-1 d-block mb-2"></i>
           Tidak ada lapangan yang cocok dengan pencarian${aktif ? ' atau filter ini' : ''}.
           ${activeCount() ? '<div class="mt-3"><button type="button" id="clearFilter" class="btn btn-main">Hapus Filter</button></div>' : ''}
         </div>`;
  } catch (err) {
    if (my !== reqId) return;
    console.error('Gagal memuat lapangan:', err);
    info.textContent = '';
    list.innerHTML = '<div class="col-12 empty">Gagal memuat data.<br><small>' + esc(err.message) + '</small></div>';
  }
}

// Tombol "Hapus filter" dibuat ulang tiap render, jadi dipasang lewat delegasi
document.addEventListener('click', e => {
  if (e.target.closest('#clearFilter')) { e.preventDefault(); resetFilters(); }
});

chips.addEventListener('click', e => {
  const b = e.target.closest('.chip');
  if (!b) return;
  jenis = b.dataset.jenis;
  load();
});

// Pencarian: tunggu 350 ms setelah berhenti mengetik
search.addEventListener('input', () => {
  clearTimeout(timer);
  timer = setTimeout(() => { q = search.value.trim(); load(); }, 350);
});

updateBadge();
load();