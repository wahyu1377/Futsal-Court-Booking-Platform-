<?php
/**
 * Form tambah / ubah lapangan untuk owner.
 *
 * - Tanpa parameter ?id=  -> mode tambah (CREATE)
 * - Dengan ?id=123        -> mode ubah (UPDATE), data diambil dari API
 *
 * Form dibagi 3 langkah supaya layar tetap ringkas di HP:
 *   1. Info Dasar        -> nama, jenis olahraga, lokasi
 *   2. Fasilitas & Harga -> fasilitas, tarif, jam operasional, durasi sesi
 *   3. Foto & Keterangan -> foto lapangan (minimal 1) dan keterangan
 */
require __DIR__ . '/../../../backend/core/auth.php';
requireLogin('owner');

$user        = currentUser();
$idEdit      = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$pageTitle   = $idEdit > 0 ? 'Ubah Lapangan' : 'Tambah Lapangan';
$useTailwind = true;
$extraJs     = '../../assets/js/owner-lapangan.js';
?>
<?php include '../../components/header.php'; ?>
<link href="../../assets/css/owner.css" rel="stylesheet">

<!-- Bar atas -->
<div class="owner-bar">
  <div class="container d-flex align-items-center gap-3 py-2">
    <a href="dashboard.php" class="icon-back" aria-label="Kembali ke dashboard">
      <i class="bi bi-arrow-left"></i>
    </a>
    <div>
      <div class="fw-bold" style="line-height:1.1"><?= $idEdit > 0 ? 'Ubah Lapangan' : 'Tambah Lapangan' ?></div>
      <small class="text-muted" style="font-size:.72rem"><?= htmlspecialchars($user['name'] ?? '') ?></small>
    </div>
  </div>
</div>

<!-- Stepper -->
<div class="stepper">
  <div class="step aktif" data-step="1"><span class="nomor">1</span> Info Dasar</div>
  <div class="garis"></div>
  <div class="step" data-step="2"><span class="nomor">2</span> Fasilitas &amp; Harga</div>
  <div class="garis"></div>
  <div class="step" data-step="3"><span class="nomor">3</span> Foto &amp; Keterangan</div>
</div>

<main class="container py-4" style="padding-bottom:7rem">

  <form id="formLapangan" novalidate
        data-api="../../../backend/api/owner/lapangan.php"
        data-id="<?= $idEdit > 0 ? $idEdit : 0 ?>"
        data-img="../../gambar.php?f=">

    <!-- ================= LANGKAH 1 : INFO DASAR ================= -->
    <section class="langkah-panel aktif" data-panel="1">

      <div class="mb-3">
        <label class="field-label" for="nama">Nama Lapangan / Venue <span class="wajib">*</span></label>
        <input type="text" id="nama" name="nama" class="field-input" maxlength="120"
               placeholder="contoh: Champion Futsal Arena Cilandak" autocomplete="off">
        <div class="field-error" id="err-nama"></div>
      </div>

      <!-- Jenis olahraga untuk sementara hanya Futsal (belum bisa dipilih owner) -->
      <div class="mb-3">
        <span class="field-label">Jenis Olahraga <span class="wajib">*</span></span>
        <div class="d-flex flex-wrap gap-2" id="wrapJenis"></div>
        <input type="hidden" id="jenis" name="jenis" value="futsal">
        <small class="text-muted">Untuk sementara platform hanya menyediakan arena futsal.</small>
        <div class="field-error" id="err-jenis"></div>
      </div>

      <div class="mb-3">
        <label class="field-label" for="kecamatan">Kecamatan <span class="wajib">*</span></label>
        <input type="text" id="kecamatan" name="kecamatan" class="field-input" maxlength="80"
               placeholder="contoh: Cilandak Barat" autocomplete="off">
        <div class="field-error" id="err-kecamatan"></div>
      </div>

      <div class="mb-3">
        <label class="field-label" for="kota">Kota <span class="wajib">*</span></label>
        <input type="text" id="kota" name="kota" class="field-input" maxlength="80"
               placeholder="contoh: Jakarta Selatan" autocomplete="off">
        <div class="field-error" id="err-kota"></div>
      </div>

    </section>

    <!-- ================= LANGKAH 2 : FASILITAS & HARGA ================= -->
    <section class="langkah-panel" data-panel="2">

      <div class="mb-3">
        <span class="field-label">Fasilitas</span>
        <div class="d-flex flex-wrap gap-2" id="wrapFasilitas"></div>
        <small class="text-muted">Boleh pilih lebih dari satu.</small>
        <div class="field-error" id="err-fasilitas"></div>
      </div>

      <div class="mb-3">
        <label class="field-label" for="harga_per_jam">Tarif per Jam (Rp) <span class="wajib">*</span></label>
        <input type="number" id="harga_per_jam" name="harga_per_jam" class="field-input" min="0" step="5000"
               inputmode="numeric" placeholder="contoh: 150000">
        <div class="field-error" id="err-harga_per_jam"></div>
      </div>

      <!-- Jam operasional -->
      <div class="mb-3">
        <span class="field-label">Jam Operasional <span class="wajib">*</span></span>
        <div class="row g-2">
          <div class="col-6">
            <input type="time" id="jam_buka" name="jam_buka" class="field-input" value="08:00">
            <small class="text-muted">Jam buka</small>
          </div>
          <div class="col-6">
            <input type="time" id="jam_tutup" name="jam_tutup" class="field-input" value="22:00">
            <small class="text-muted">Jam tutup</small>
          </div>
        </div>
        <div class="field-error" id="err-jam_buka"></div>
        <div class="field-error" id="err-jam_tutup"></div>
      </div>

      <!-- Durasi sewa per sesi -->
      <div class="mb-3">
        <span class="field-label">Durasi Sewa per Sesi <span class="wajib">*</span></span>
        <div class="d-flex flex-wrap gap-2" id="wrapDurasi"></div>
        <small class="text-muted">Contoh: sesi 60 menit berarti 1 jam per pemesanan.</small>
        <div class="field-error" id="err-durasi_sesi"></div>
      </div>

      <div class="d-flex flex-column gap-3">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="tersedia_hari_ini" checked>
          <label class="form-check-label fw-bold" for="tersedia_hari_ini">Tersedia hari ini</label>
        </div>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="aktif" checked>
          <label class="form-check-label fw-bold" for="aktif">Tampilkan lapangan ke pengguna</label>
        </div>
      </div>

      <div class="alert alert-light border small mt-3 mb-0">
        <i class="bi bi-info-circle me-1"></i>
        Rating dan jumlah ulasan diisi otomatis dari pelanggan, jadi tidak perlu diisi manual.
      </div>

    </section>

    <!-- ================= LANGKAH 3 : FOTO & KETERANGAN ================= -->
    <section class="langkah-panel" data-panel="3">

      <div class="mb-3">
        <span class="field-label">Foto Lapangan <span class="wajib">*</span></span>

        <!-- Pilih sumber foto -->
        <div class="d-flex flex-column gap-2 mb-3">
          <label class="pilihan-sumber aktif" id="sumberGaleri">
            <input type="radio" name="sumber_foto" value="galeri" checked>
            <span><i class="bi bi-images me-1"></i> Galeri Arena Sport
              <small>3 foto siap pakai, pilih salah satu atau lebih</small></span>
          </label>
          <label class="pilihan-sumber" id="sumberSendiri">
            <input type="radio" name="sumber_foto" value="sendiri">
            <span><i class="bi bi-cloud-arrow-up me-1"></i> Foto saya sendiri
              <small>Unggah file dari perangkat atau tempel URL foto</small></span>
          </label>
        </div>

        <!-- Opsi 1: pilih dari galeri bawaan -->
        <div id="opsiGaleri">
          <div class="row g-2" id="wrapFoto"></div>
        </div>

        <!-- Opsi 2: unggah file / tempel URL -->
        <div id="opsiSendiri" class="d-none">
          <div class="mb-3">
            <label class="field-label" for="fileFoto">Unggah foto dari perangkat</label>
            <input type="file" id="fileFoto" class="field-input" accept="image/jpeg,image/png,image/webp" multiple>
            <small class="text-muted">Format JPG, PNG, atau WEBP. Maksimal 3 MB per file.</small>
          </div>

          <div class="mb-2">
            <label class="field-label" for="urlFoto">Atau tempel URL foto</label>
            <div class="d-flex gap-2">
              <input type="url" id="urlFoto" class="field-input" placeholder="https://contoh.com/lapangan.jpg">
              <button type="button" class="btn btn-main text-nowrap" id="btnTambahUrl">Tambah</button>
            </div>
            <small class="text-muted">Gunakan link gambar yang bisa diakses publik (https://...).</small>
          </div>
        </div>

        <!-- Daftar foto yang dipilih -->
        <div class="mt-3">
          <span class="field-label">Foto dipilih <span id="jumlahFoto" class="text-muted">(0)</span></span>
          <div class="row g-2" id="wrapTerpilih"></div>
        </div>

        <div class="field-error" id="err-foto"></div>
      </div>

      <div class="mb-3">
        <label class="field-label" for="keterangan">Keterangan</label>
        <textarea id="keterangan" name="keterangan" class="field-input" rows="3" maxlength="120"
                  placeholder="contoh: Peralatan Lengkap, dekat halte bus"></textarea>
        <div class="field-error" id="err-keterangan"></div>
      </div>

      <div class="alert alert-light border small mb-0">
        <i class="bi bi-info-circle me-1"></i>
        Setelah disimpan, lapangan langsung bisa ditemukan pengguna di halaman pencarian.
      </div>

    </section>

  </form>
</main>

<!-- Bar aksi tetap -->
<div class="aksi-tetap">
  <div class="container d-flex gap-2">
    <a href="dashboard.php" class="btn btn-lembut flex-grow-1 text-center text-decoration-none">Batal</a>
    <button type="button" class="btn btn-main flex-grow-1" id="btnLangkah">Lanjut</button>
  </div>
</div>

<?php include '../../components/footer.php'; ?>
