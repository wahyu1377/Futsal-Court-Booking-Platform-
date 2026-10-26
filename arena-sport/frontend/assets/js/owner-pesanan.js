(function () {
  'use strict';

  var root = document.getElementById('pesananOwner');
  if (!root) return;

  var API  = root.dataset.api;
  var $    = function (id) { return document.getElementById(id); };
  var list = $('poList'), info = $('poInfo'), filter = $('poFilter');
  var tab = 'aktif', lapangan = '', reqId = 0, filterTerisi = false;

  // Data berasal dari pengguna, jadi selalu di-escape sebelum masuk HTML
  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function rupiah(n) { return 'Rp' + Number(n).toLocaleString('id-ID'); }
  function rupiahRingkas(n) {
    n = Number(n);
    return n >= 1e6 ? 'Rp' + (n / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt' : rupiah(n);
  }

  function kartu(p) {
    var kontak = [];
    if (p.telepon) {
      kontak.push('<a href="tel:' + esc(p.telepon) + '"><i class="bi bi-telephone"></i> ' + esc(p.telepon) + '</a>');
      kontak.push('<a href="https://wa.me/' + esc(p.wa) + '" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> WhatsApp</a>');
    }
    if (p.email) {
      kontak.push('<a href="mailto:' + esc(p.email) + '"><i class="bi bi-envelope"></i> ' + esc(p.email) + '</a>');
    }

    var uang = '';
    if (p.status === 'menunggu') {
      uang = 'Menunggu pembayaran' + (p.sisa_menit != null ? ' (sisa ' + p.sisa_menit + ' menit)' : '');
    } else if (p.metode === 'onsite' && p.sisa_di_tempat > 0) {
      uang = 'DP ' + rupiah(p.bayar_sekarang) + ' dibayar online &bull; sisa ' + rupiah(p.sisa_di_tempat) + ' dilunasi di kasir';
    } else if (p.status !== 'dibatalkan') {
      uang = 'Dibayar lunas online';
    }

    var opsi = p.opsi && p.opsi.length ? ' &bull; ' + p.opsi.map(esc).join(', ') : '';

    return '<article class="po-card">' +
      '<div class="d-flex justify-content-between align-items-start gap-2">' +
        '<div class="po-utama">' +
          '<div class="po-judul">' + esc(p.lapangan_nama) + '</div>' +
          '<div class="po-meta"><i class="bi bi-calendar-event"></i> ' + esc(p.tanggal_label) + ' &bull; ' + esc(p.jam_label) + '</div>' +
        '</div>' +
        '<span class="po-badge tone-' + esc(p.tone) + '">' + esc(p.status_teks) + '</span>' +
      '</div>' +
      '<div class="po-baris"><i class="bi bi-person"></i> <strong>' + esc(p.pemesan) + '</strong>' +
        ' <span class="po-meta">&bull; ' + esc(p.durasi) + opsi + '</span></div>' +
      (kontak.length ? '<div class="po-kontak">' + kontak.join('') + '</div>' : '') +
      '<div class="po-bawah">' +
        '<span class="po-meta">' + esc(p.kode) + (uang ? ' &bull; ' + uang : '') + '</span>' +
        '<strong class="po-uang" title="Total dikurangi biaya layanan">' + rupiah(p.pendapatan) + '</strong>' +
      '</div>' +
    '</article>';
  }

  function isiFilter(daftar) {
    if (filterTerisi) return;
    filterTerisi = true;
    filter.innerHTML = '<option value="">Semua lapangan</option>' +
      daftar.map(function (l) { return '<option value="' + Number(l.id) + '">' + esc(l.nama) + '</option>'; }).join('');
    filter.value = lapangan;
  }

  function load(diam) {
    var my = ++reqId;
    if (!diam) {
      list.innerHTML = '<div class="po-skeleton"></div><div class="po-skeleton"></div>';
    }
    var url = API + '?tab=' + tab + (lapangan ? '&lapangan_id=' + encodeURIComponent(lapangan) : '');

    fetch(url, { credentials: 'same-origin' })
      .then(function (res) { return res.text().then(function (t) { return { status: res.status, text: t }; }); })
      .then(function (x) {
        var r;
        try { r = JSON.parse(x.text); }
        catch (e) {
          console.error('Balasan pesanan.php bukan JSON (HTTP ' + x.status + '):', x.text);
          throw new Error('Server mengirim balasan tidak valid (HTTP ' + x.status + ').');
        }
        if (my !== reqId) return;
        if (!r.success) throw new Error(r.message);

        $('statHariIni').textContent   = r.ringkasan.hari_ini;
        $('statMenunggu').textContent  = r.ringkasan.menunggu;
        $('statPendapatan').textContent = rupiahRingkas(r.ringkasan.pendapatan_bulan);
        isiFilter(r.lapangan);

        info.textContent = r.data.length ? r.data.length + ' pesanan' : '';
        list.innerHTML = r.data.length
          ? r.data.map(kartu).join('')
          : '<div class="po-kosong"><i class="bi bi-inbox"></i>' +
            (tab === 'aktif' ? 'Belum ada pesanan aktif.' : 'Belum ada riwayat pesanan.') + '</div>';
      })
      .catch(function (err) {
        if (my !== reqId) return;
        if (!diam) {
          info.textContent = '';
          list.innerHTML = '<div class="po-kosong text-danger">Gagal memuat pesanan.<br><small>' + esc(err.message) + '</small></div>';
        }
      });
  }

  root.querySelectorAll('.po-tab').forEach(function (b) {
    b.addEventListener('click', function () {
      tab = b.dataset.tab;
      root.querySelectorAll('.po-tab').forEach(function (x) {
        var on = x === b;
        x.classList.toggle('on', on);
        x.setAttribute('aria-selected', on ? 'true' : 'false');
      });
      load(false);
    });
  });
  filter.addEventListener('change', function () { lapangan = filter.value; load(false); });
  $('poRefresh').addEventListener('click', function () { load(false); });

  // Pesanan baru muncul sendiri: muat ulang tiap 60 detik selama tab terlihat
  setInterval(function () { if (!document.hidden) load(true); }, 60000);

  load(false);
})();