/* =========================================================
   ARENA SPORT - JS halaman owner
   Menangani dua halaman:
     1. dashboard.php       -> daftar lapangan + hapus + kartu notifikasi
     2. tambah_lapangan.php -> form 3 langkah (tambah / ubah)
   Semua request memakai fetch + prepared statement di sisi server.
   ========================================================= */

// Cegah XSS: data lapangan berasal dari owner, jadi selalu di-escape
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const cap = s => String(s ?? '').charAt(0).toUpperCase() + String(s ?? '').slice(1);
const rupiah = n => 'Rp' + Number(n || 0).toLocaleString('id-ID');

// Nilai boolean dari PostgreSQL (TRUE/FALSE) atau 1/0
const toBool = v => v === true || v === 1 || v === '1' || v === 't' || v === 'true';

// Daftar foto pada satu lapangan (dipisah koma di database)
const daftarFoto = c => (c ? String(c).split(',').map(s => s.trim()).filter(Boolean) : []);

// Jam dari PostgreSQL (HH:MM:SS) jadi HH:MM
const jamSingkat = v => v ? String(v).slice(0, 5) : '';

/* =========================================================
   KARTU NOTIFIKASI (muncul setelah aksi berhasil)
   ========================================================= */
function tampilNotifikasi(judul, pesan, ikon = 'bi-check-lg') {
  const kartu = document.getElementById('notifCard');
  if (!kartu) return;

  const kelasIkon = ikon.includes('trash') ? 'notif-ikon notif-ikon-danger' : 'notif-ikon';
  document.getElementById('notifIkon').className = kelasIkon;
  document.getElementById('notifIkon').innerHTML = `<i class="bi ${ikon}"></i>`;
  document.getElementById('notifJudul').textContent = judul;
  document.getElementById('notifPesan').textContent = pesan;

  kartu.classList.remove('d-none');
  // Paksa animasi masuk ulang setiap kali dipanggil
  void kartu.offsetWidth;
  kartu.classList.add('tampil');

  // Sembunyikan otomatis setelah 5 detik
  clearTimeout(kartu.dataset.timer);
  kartu.dataset.timer = setTimeout(sembunyiNotifikasi, 5000);
}

function sembunyiNotifikasi() {
  const kartu = document.getElementById('notifCard');
  if (!kartu) return;
  kartu.classList.remove('tampil');
  setTimeout(() => kartu.classList.add('d-none'), 300);
}

// Tombol tutup pada kartu notifikasi
document.addEventListener('click', e => {
  if (e.target.closest('[data-tutup-notif]')) sembunyiNotifikasi();
});

/* =========================================================
   BAGIAN 1 - DASHBOARD (daftar lapangan)
   ========================================================= */
const listEl = document.getElementById('ownerList');

if (listEl) {
  const API = listEl.dataset.api;
  const IMG = listEl.dataset.img;

  let idHapus = 0;
  const modalHapus = new bootstrap.Modal(document.getElementById('modalHapus'));

  // Foto bisa berupa file di folder aset, file hasil unggahan owner, atau URL dari internet
  // Nama file di-encode karena foto dilayani lewat frontend/gambar.php?f=...
  const urlFoto = nama => /^https?:\/\//i.test(nama) ? nama : IMG + encodeURIComponent(nama);

  function carouselFoto(foto, nama) {
    // Satu foto -> gambar tunggal, lebih dari satu -> carousel dengan titik indicator
    if (foto.length <= 1) {
      return foto.length
        ? `<img src="${esc(urlFoto(foto[0]))}" alt="${esc(nama)}" loading="lazy" onerror="this.remove()">`
        : '';
    }
    return `
      <div class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
        <div class="carousel-inner">
          ${foto.map((f, i) => `
            <div class="carousel-item ${i === 0 ? 'active' : ''}">
              <img src="${esc(urlFoto(f))}" alt="${esc(nama)}" loading="lazy" onerror="this.remove()">
            </div>`).join('')}
        </div>
        <div class="carousel-indicators">
          ${foto.map((f, i) => `<button type="button" data-bs-target=".carousel" data-bs-slide-to="${i}"
            class="${i === 0 ? 'active' : ''}" aria-label="Foto ${i + 1}"></button>`).join('')}
        </div>
      </div>`;
  }

  function kartuLapangan(c) {
    const foto = daftarFoto(c.foto);
    const tags = [cap(c.jenis), ...(c.fasilitas ? c.fasilitas.split(',').map(t => t.trim()).filter(Boolean) : [])].slice(0, 3);
    const statusAktif = toBool(c.aktif)
      ? '<span class="status aktif">Aktif</span>'
      : '<span class="status nonaktif">Nonaktif</span>';

    // Rating hanya ditampilkan bila sudah ada nilai dari pelanggan
    const rating = Number(c.rating) > 0
      ? `<div class="court-rating"><i class="bi bi-star-fill"></i> ${Number(c.rating).toFixed(1)} <small>(${Number(c.jumlah_ulasan)})</small></div>`
      : '';

    const jam = (c.jam_buka && c.jam_tutup)
      ? `<span class="court-loc"><i class="bi bi-clock me-1"></i>${esc(jamSingkat(c.jam_buka))} - ${esc(jamSingkat(c.jam_tutup))}</span>`
      : '';

    return `
    <div class="col-12 col-md-6 col-xl-4">
      <article class="court-card">
        <div class="court-img">
          ${carouselFoto(foto, c.nama)}
          <div class="court-tags">${tags.map(t => `<span>${esc(t)}</span>`).join('')}</div>
          ${statusAktif}
          ${rating}
        </div>
        <div class="court-body">
          <h3>${esc(c.nama)}</h3>
          <div class="court-loc mb-2"><i class="bi bi-geo-alt me-1"></i>${esc(c.kecamatan)}, ${esc(c.kota)}</div>
          <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
            ${toBool(c.tersedia_hari_ini)
              ? '<span class="avail">Tersedia Hari Ini</span>'
              : '<span class="avail off">Penuh Hari Ini</span>'}
            ${jam}
            ${c.durasi_sesi ? `<span class="court-loc">${Number(c.durasi_sesi)} menit/sesi</span>` : ''}
          </div>
          <div class="price-label mb-1">TARIF SEWA</div>
          <div class="price mb-3">${rupiah(c.harga_per_jam)} <small>/ jam</small></div>
          <div class="d-flex gap-2 mt-auto">
            <a href="tambah_lapangan.php?id=${Number(c.id)}" class="btn btn-main btn-sm flex-grow-1 text-center">
              <i class="bi bi-pencil me-1"></i> Ubah
            </a>
            <button type="button" class="btn btn-sm btn-lembut" data-hapus="${Number(c.id)}"
                    data-nama="${esc(c.nama)}" aria-label="Hapus lapangan">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </article>
    </div>`;
  }

  function perbaruiStatistik(list) {
    const total = list.length;
    const aktif = list.filter(l => toBool(l.aktif)).length;
    const rata = total ? list.reduce((a, l) => a + Number(l.harga_per_jam || 0), 0) / total : 0;

    document.getElementById('statTotal').textContent = total;
    document.getElementById('statAktif').textContent = aktif;
    document.getElementById('statTarif').textContent = rupiah(Math.round(rata));
  }

  async function muatDaftar() {
    listEl.innerHTML = [1, 2].map(() =>
      '<div class="col-12 col-md-6 col-xl-4"><div class="skeleton"></div></div>').join('');

    try {
      const res = await fetch(API, { headers: { 'Accept': 'application/json' } });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      const list = json.data || [];
      perbaruiStatistik(list);

      listEl.innerHTML = list.length
        ? list.map(kartuLapangan).join('')
        : `<div class="col-12 empty">
             <i class="bi bi-dribbble fs-1 d-block mb-2"></i>
             Belum ada lapangan yang Anda kelola.
             <div class="mt-3"><a href="tambah_lapangan.php" class="btn btn-main">Tambah Lapangan</a></div>
           </div>`;
    } catch (err) {
      console.error('Gagal memuat lapangan:', err);
      listEl.innerHTML = `<div class="col-12 empty">Gagal memuat data.<br><small>${esc(err.message)}</small></div>`;
    }
  }

  // Tombol hapus -> buka modal konfirmasi
  listEl.addEventListener('click', e => {
    const btn = e.target.closest('[data-hapus]');
    if (!btn) return;

    idHapus = Number(btn.dataset.hapus);
    document.getElementById('modalHapusTeks').textContent =
      `Lapangan "${btn.dataset.nama}" akan dihapus permanen dan tidak bisa dikembalikan.`;
    modalHapus.show();
  });

  // Konfirmasi hapus
  document.getElementById('btnHapusYa').addEventListener('click', async () => {
    const btn = document.getElementById('btnHapusYa');
    btn.disabled = true;
    btn.textContent = 'Menghapus...';

    try {
      const res = await fetch(API, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: idHapus })
      });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      modalHapus.hide();
      await muatDaftar();
      tampilNotifikasi('Lapangan berhasil dihapus', 'Lapangan telah dihapus dari daftar Anda.', 'bi-trash');
    } catch (err) {
      console.error('Gagal menghapus lapangan:', err);
      alert('Gagal menghapus lapangan: ' + err.message);
    } finally {
      btn.disabled = false;
      btn.textContent = 'Ya, Hapus';
    }
  });

  muatDaftar();

  // Notifikasi yang dikirim dari halaman form (setelah simpan)
  const notifTersimpan = sessionStorage.getItem('notifArena');
  if (notifTersimpan) {
    sessionStorage.removeItem('notifArena');
    const info = JSON.parse(notifTersimpan);
    tampilNotifikasi(info.judul, info.pesan, info.ikon || 'bi-check-lg');
  }
}

/* =========================================================
   BAGIAN 2 - FORM TAMBAH / UBAH LAPANGAN
   ========================================================= */
const formEl = document.getElementById('formLapangan');

if (formEl) {
  const API = formEl.dataset.api;
  const IMG = formEl.dataset.img;
  const idEdit = Number(formEl.dataset.id || 0);

  let langkah = 1;
  const TOTAL_LANGKAH = 3;

  // Pilihan diambil dari server supaya selalu sama dengan API
  let opsiJenis = [];
  let opsiFasilitas = [];
  let opsiFoto = [];
  let durasiOpsi = [60, 90, 120];
  let dataAwal = null;

  // Foto yang dipilih owner: nama file galeri, nama file unggahan, atau URL
  let fotoTerpilih = [];

  // Foto bisa file lokal (folder aset), file unggahan owner, atau URL dari internet
  // Nama file di-encode karena foto dilayani lewat frontend/gambar.php?f=...
  const urlFoto = nama => /^https?:\/\//i.test(nama) ? nama : IMG + encodeURIComponent(nama);

  /* ---------- Bangun pilihan ---------- */
  function bangunJenis(terpilih) {
    const wrap = document.getElementById('wrapJenis');
    // Untuk sementara hanya ada satu jenis: futsal (tidak bisa diganti owner)
    wrap.innerHTML = opsiJenis.map(j => `
      <label class="pilihan">
        <input type="radio" name="jenisPilih" value="${esc(j)}" ${j === terpilih ? 'checked' : ''} disabled>
        <span>${esc(cap(j))}</span>
      </label>`).join('');
    document.getElementById('jenis').value = opsiJenis[0] || 'futsal';
  }

  function bangunFasilitas(terpilih) {
    const wrap = document.getElementById('wrapFasilitas');
    wrap.innerHTML = opsiFasilitas.map(f => `
      <label class="pilihan">
        <input type="checkbox" name="fasilitas" value="${esc(f)}" ${terpilih.includes(f) ? 'checked' : ''}>
        <span>${esc(f)}</span>
      </label>`).join('');
  }

  function bangunDurasi(terpilih) {
    const wrap = document.getElementById('wrapDurasi');
    wrap.innerHTML = durasiOpsi.map(d => `
      <label class="pilihan">
        <input type="radio" name="durasi_sesi" value="${d}" ${Number(terpilih) === d ? 'checked' : ''}>
        <span>${d} menit</span>
      </label>`).join('');
  }

  function bangunGaleri() {
    const wrap = document.getElementById('wrapFoto');
    wrap.innerHTML = opsiFoto.length
      ? opsiFoto.map(f => `
        <div class="col-6 col-sm-4 col-lg-3">
          <div class="foto-pilihan ${fotoTerpilih.includes(f) ? 'terpilih' : ''}" data-foto="${esc(f)}"
               role="button" tabindex="0" aria-pressed="${fotoTerpilih.includes(f)}">
            <img src="${esc(urlFoto(f))}" alt="${esc(f)}" loading="lazy" onerror="this.remove()">
            <span>${esc(f)}</span>
          </div>
        </div>`).join('')
      : '<small class="text-secondary">Belum ada foto yang tersedia di folder aset.</small>';
  }

  // Daftar foto yang sudah dipilih (beserta tombol hapus)
  function bangunDaftarTerpilih() {
    const wrap = document.getElementById('wrapTerpilih');
    document.getElementById('jumlahFoto').textContent = `(${fotoTerpilih.length})`;

    wrap.innerHTML = fotoTerpilih.length
      ? fotoTerpilih.map((f, i) => `
        <div class="col-6 col-sm-4 col-lg-3">
          <div class="foto-terpilih">
            <img src="${esc(urlFoto(f))}" alt="${esc(f)}" loading="lazy" onerror="this.remove()">
            <button type="button" class="hapus-foto" data-hapus-foto="${i}" aria-label="Hapus foto">
              <i class="bi bi-x-lg"></i>
            </button>
            <span class="keterangan-foto">${esc(f)}</span>
          </div>
        </div>`).join('')
      : '<small class="text-secondary">Belum ada foto dipilih.</small>';
  }

  function tambahFoto(nilai) {
    if (!nilai || fotoTerpilih.includes(nilai)) return;
    fotoTerpilih.push(nilai);
    bangunDaftarTerpilih();
    bangunGaleri();
    document.getElementById('err-foto').textContent = '';
  }

  function hapusFoto(nilai) {
    fotoTerpilih = fotoTerpilih.filter(f => f !== nilai);
    bangunDaftarTerpilih();
    bangunGaleri();
  }

  /* ---------- Ganti sumber foto ---------- */
  document.querySelectorAll('input[name="sumber_foto"]').forEach(radio => {
    radio.addEventListener('change', () => {
      const galeri = radio.value === 'galeri';
      document.getElementById('opsiGaleri').classList.toggle('d-none', !galeri);
      document.getElementById('opsiSendiri').classList.toggle('d-none', galeri);
    });
  });

  /* ---------- Pilih foto dari galeri bawaan ---------- */
  document.getElementById('wrapFoto').addEventListener('click', e => {
    const item = e.target.closest('[data-foto]');
    if (!item) return;

    const nama = item.dataset.foto;
    if (fotoTerpilih.includes(nama)) {
      hapusFoto(nama);
    } else {
      tambahFoto(nama);
    }
  });

  /* ---------- Unggah foto dari perangkat ---------- */
  document.getElementById('fileFoto').addEventListener('change', async e => {
    const input = e.target;
    if (!input.files || !input.files.length) return;

    const dataForm = new FormData();
    [...input.files].forEach(f => dataForm.append('foto[]', f));

    try {
      const res = await fetch(API, { method: 'POST', body: dataForm });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      (json.files || []).forEach(tambahFoto);
      if (json.errors && json.errors.length) {
        alert(json.errors.join('\n'));
      }
    } catch (err) {
      console.error('Gagal mengunggah foto:', err);
      alert('Gagal mengunggah foto: ' + err.message);
    } finally {
      input.value = '';
    }
  });

  /* ---------- Tambah foto lewat URL ---------- */
  document.getElementById('btnTambahUrl').addEventListener('click', () => {
    const input = document.getElementById('urlFoto');
    const url = input.value.trim();

    if (!url) {
      document.getElementById('err-foto').textContent = 'Masukkan URL foto terlebih dahulu.';
      return;
    }
    if (!/^https?:\/\//i.test(url)) {
      document.getElementById('err-foto').textContent = 'URL foto harus diawali http:// atau https://';
      return;
    }

    tambahFoto(url);
    input.value = '';
  });

  /* ---------- Hapus foto dari daftar ---------- */
  document.getElementById('wrapTerpilih').addEventListener('click', e => {
    const btn = e.target.closest('[data-hapus-foto]');
    if (!btn) return;

    const nama = fotoTerpilih[Number(btn.dataset.hapusFoto)];
    hapusFoto(nama);
  });

  /* ---------- Navigasi langkah ---------- */
  function tampilLangkah(baru) {
    langkah = Math.min(Math.max(baru, 1), TOTAL_LANGKAH);

    document.querySelectorAll('.langkah-panel').forEach(p => {
      p.classList.toggle('aktif', Number(p.dataset.panel) === langkah);
    });

    document.querySelectorAll('.stepper .step').forEach(s => {
      const no = Number(s.dataset.step);
      s.classList.toggle('aktif', no === langkah);
      s.classList.toggle('done', no < langkah);
    });

    const btn = document.getElementById('btnLangkah');
    btn.textContent = langkah === TOTAL_LANGKAH
      ? (idEdit > 0 ? 'Simpan Perubahan' : 'Simpan Lapangan')
      : 'Lanjut';
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  // Validasi per langkah, supaya pengguna tahu bagian mana yang belum benar
  function validasiLangkah(sekarang) {
    let valid = true;
    const bersihkan = () => document.querySelectorAll('.field-error').forEach(e => e.textContent = '');

    if (sekarang === 1) {
      bersihkan();
      const nama = document.getElementById('nama').value.trim();
      if (nama.length < 3) {
        document.getElementById('err-nama').textContent = 'Nama lapangan minimal 3 karakter.';
        valid = false;
      }
      if (!document.getElementById('kecamatan').value.trim()) {
        document.getElementById('err-kecamatan').textContent = 'Kecamatan wajib diisi.';
        valid = false;
      }
      if (!document.getElementById('kota').value.trim()) {
        document.getElementById('err-kota').textContent = 'Kota wajib diisi.';
        valid = false;
      }
    }

    if (sekarang === 2) {
      bersihkan();
      const harga = document.getElementById('harga_per_jam').value;
      if (harga === '' || Number(harga) < 0) {
        document.getElementById('err-harga_per_jam').textContent = 'Tarif per jam wajib diisi.';
        valid = false;
      }
      const buka = document.getElementById('jam_buka').value;
      const tutup = document.getElementById('jam_tutup').value;
      if (!buka) {
        document.getElementById('err-jam_buka').textContent = 'Jam buka wajib diisi.';
        valid = false;
      }
      if (!tutup) {
        document.getElementById('err-jam_tutup').textContent = 'Jam tutup wajib diisi.';
        valid = false;
      }
      if (buka && tutup && buka >= tutup) {
        document.getElementById('err-jam_tutup').textContent = 'Jam tutup harus lebih besar dari jam buka.';
        valid = false;
      }
      if (!document.querySelector('input[name="durasi_sesi"]:checked')) {
        document.getElementById('err-durasi_sesi').textContent = 'Pilih durasi sewa per sesi.';
        valid = false;
      }
    }

    if (sekarang === 3) {
      bersihkan();
      if (fotoTerpilih.length < 1) {
        document.getElementById('err-foto').textContent = 'Minimal satu foto lapangan wajib dipilih.';
        valid = false;
      }
    }

    return valid;
  }

  document.getElementById('btnLangkah').addEventListener('click', () => {
    if (langkah < TOTAL_LANGKAH) {
      if (!validasiLangkah(langkah)) return;
      tampilLangkah(langkah + 1);
      return;
    }
    kirimForm();
  });

  /* ---------- Kirim form ---------- */
  async function kirimForm() {
    const btn = document.getElementById('btnLangkah');
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    const fasilitasTerpilih = [...document.querySelectorAll('input[name="fasilitas"]:checked')].map(i => i.value);
    const durasiTerpilih = (document.querySelector('input[name="durasi_sesi"]:checked') || {}).value || '';

    const data = {
      nama: document.getElementById('nama').value.trim(),
      jenis: document.getElementById('jenis').value,
      kecamatan: document.getElementById('kecamatan').value.trim(),
      kota: document.getElementById('kota').value.trim(),
      harga_per_jam: document.getElementById('harga_per_jam').value,
      fasilitas: fasilitasTerpilih.join(','),
      keterangan: document.getElementById('keterangan').value.trim(),
      foto: fotoTerpilih,
      jam_buka: document.getElementById('jam_buka').value,
      jam_tutup: document.getElementById('jam_tutup').value,
      durasi_sesi: durasiTerpilih,
      tersedia_hari_ini: document.getElementById('tersedia_hari_ini').checked,
      aktif: document.getElementById('aktif').checked
    };

    if (idEdit > 0) data.id = idEdit;

    try {
      const res = await fetch(API, {
        method: idEdit > 0 ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const json = await res.json();

      if (!json.success) {
        // Tampilkan pesan error per field dari server
        document.querySelectorAll('.field-error').forEach(e => e.textContent = '');
        if (json.errors) {
          Object.entries(json.errors).forEach(([field, pesan]) => {
            const target = document.getElementById('err-' + field);
            if (target) target.textContent = pesan;
          });
        }
        return;
      }

      // Notifikasi tampil di dashboard setelah halaman dialihkan
      sessionStorage.setItem('notifArena', JSON.stringify({
        judul: idEdit > 0 ? 'Lapangan berhasil diperbarui' : 'Lapangan berhasil ditambahkan!',
        pesan: idEdit > 0
          ? 'Perubahan data lapangan Anda sudah tersimpan.'
          : 'Data lapangan Anda sudah tersimpan dan tampil di halaman pencarian.',
        ikon: 'bi-check-lg'
      }));
      window.location.href = 'dashboard.php';
    } catch (err) {
      console.error('Gagal menyimpan lapangan:', err);
      alert('Gagal menyimpan lapangan: ' + err.message);
    } finally {
      btn.disabled = false;
      btn.textContent = idEdit > 0 ? 'Simpan Perubahan' : 'Simpan Lapangan';
    }
  }

  /* ---------- Muat pilihan & isi form ---------- */
  async function muatForm() {
    try {
      const res = await fetch(API, { headers: { 'Accept': 'application/json' } });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      opsiJenis = json.jenis_opsi || [];
      opsiFasilitas = json.fasilitas_opsi || [];
      opsiFoto = json.foto_opsi || [];
      durasiOpsi = json.durasi_opsi || durasiOpsi;

      // Mode ubah: ambil data lapangan sesuai id
      if (idEdit > 0) {
        dataAwal = (json.data || []).find(l => Number(l.id) === idEdit);
        if (!dataAwal) {
          alert('Lapangan tidak ditemukan.');
          return;
        }
      }

      const awal = dataAwal || {};
      const fasilitasAwal = awal.fasilitas
        ? awal.fasilitas.split(',').map(t => t.trim()).filter(Boolean) : [];

      // Foto yang sudah tersimpan jadi daftar awal (bisa galeri, unggahan, atau URL)
      fotoTerpilih = daftarFoto(awal.foto);

      bangunJenis(awal.jenis || '');
      bangunFasilitas(fasilitasAwal);
      bangunDurasi(awal.durasi_sesi || 60);
      bangunGaleri();
      bangunDaftarTerpilih();

      document.getElementById('nama').value = awal.nama || '';
      document.getElementById('kecamatan').value = awal.kecamatan || '';
      document.getElementById('kota').value = awal.kota || '';
      document.getElementById('harga_per_jam').value = awal.harga_per_jam ?? '';
      document.getElementById('keterangan').value = awal.keterangan || '';
      document.getElementById('jam_buka').value = jamSingkat(awal.jam_buka) || '08:00';
      document.getElementById('jam_tutup').value = jamSingkat(awal.jam_tutup) || '22:00';
      document.getElementById('tersedia_hari_ini').checked = dataAwal ? toBool(awal.tersedia_hari_ini) : true;
      document.getElementById('aktif').checked = dataAwal ? toBool(awal.aktif) : true;
    } catch (err) {
      console.error('Gagal memuat data form:', err);
      alert('Gagal memuat data: ' + err.message);
    }
  }

  muatForm();
}
