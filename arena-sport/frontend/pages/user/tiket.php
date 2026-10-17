<?php
declare(strict_types=1);
session_start();

/* ---------------------------------------------------------
   DATA — sementara hardcode. Ganti dengan query database
   berdasarkan kode reservasi (mis. tiket.php?kode=BK-...),
   dan pastikan reservasi itu milik user yang sedang login.
   --------------------------------------------------------- */
$user = ['avatar' => 'img/avatar.jpg'];

$tiket = [
    'kode'       => 'BK-20241022-8849',
    'status'     => 'Instant Confirmed',
    'metode'     => 'QRIS',
    'qr'         => 'img/qr.png',
    'venue'      => 'Mega Futsal & Mini Soccer',
    'alamat'     => 'Jl. TB Simatupang No. 18, Jakarta Selatan',
    'tanggal'    => 'Selasa, 22 Okt 2024',
    'jam'        => '19.00 - 21.00 WIB (2 Jam)',
    'penyewa'    => 'Bambang Pamungkas',
    'tim'        => 'Garuda FC',
    'total'      => 392000,
    'fasilitas'  => ['img' => 'img/fasilitas.jpg',
                     'teks' => 'Fasilitas Pro: Ruang Ganti Ber-AC, Shower Air Hangat & Loker Digital'],
    'jarak'      => '2.4 km',
    'trafik'     => 'Trafik Lancar (6 Menit)',
    'patokan'    => 'Dekat Stasiun MRT Fatmawati',
    'pdf'        => 'img/tiket.pdf',
];

// Instruksi (HTML kecil hanya dari kode kita sendiri, bukan input user)
$instruksi = [
    'Hadir minimal <strong>15 menit</strong> sebelum kick-off untuk proses registrasi ulang dan pemanasan tim.',
    'Wajib mengenakan <strong>sepatu olahraga sol karet datar (non-marking)</strong> khusus lapangan vinyl indoor.',
    'Tunjukkan QR tiket di atas kepada staf resepsionis lobi untuk pembagian kunci ruang ganti.',
];

function e(string|int $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
function rupiah(int $n): string
{
    return 'Rp ' . number_format($n, 0, ',', '.');
}

$query    = rawurlencode($tiket['alamat']);
$mapEmbed = 'https://www.google.com/maps?q=' . $query . '&output=embed';
$mapLink  = 'https://maps.google.com/?q=' . $query;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tiket Reservasi - Arena Sport</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="../../assets/css/style.css" rel="stylesheet">
  <link href="../../assets/css/tiket.css" rel="stylesheet">
</head>
<body class="app tiket">

<header class="app-header app-header--ticket">
  <div class="app-header__inner">
    <a class="icon-btn" href="akun.php" aria-label="Kembali"><i class="bi bi-arrow-left"></i></a>
    <img class="app-header__logo" src="../../assets/img/logo.png" alt="Arena Sport">
    <h1>Tiket Reservasi / Sukses</h1>
    <button type="button" class="icon-btn" aria-label="Simpan"><i class="bi bi-bookmark"></i></button>
    <img class="avatar-sm" src="../../assets/img/avatar.jpg" alt="">
  </div>
</header>

<main class="page page--ticket">
  <div class="ticket-grid">

    <!-- Kiri: hero + tiket -->
    <div class="ticket-grid__left page__col">
      <section class="success">
        <div class="success__icon"><i class="bi bi-check-lg"></i></div>
        <span class="chip-status"><?= e(strtoupper($tiket['status'])) ?></span>
        <h2>Reservasi Berhasil Dikonfirmasi!</h2>
        <p>Pembayaran telah diverifikasi secara otomatis via <?= e($tiket['metode']) ?>. Akses lapangan Anda sudah siap.</p>
      </section>

      <section class="card-app ticket">
        <div class="ticket__top">
          <div class="ticket__row">
            <span class="label-caps">Kode Reservasi</span>
            <span class="chip-paid"><i class="bi bi-check-circle"></i> LUNAS (<?= e($tiket['metode']) ?>)</span>
          </div>
          <div class="code-box">
            <strong class="code-box__code" id="kode"><?= e($tiket['kode']) ?></strong>
            <button type="button" class="btn-copy" id="btnSalin"><i class="bi bi-copy"></i> <span>Salin</span></button>
          </div>
        </div>

        <div class="ticket__qr">
          <div class="ticket__qr-box"><img src="../../assets/img/qr.png" alt="QR Tiket"></div>
          <p>Tunjukkan QR ini kepada resepsionis saat kedatangan</p>
        </div>

        <div class="ticket__tear" aria-hidden="true"></div>

        <div class="ticket__venue">
          <div class="venue">
            <span class="menu__icon tint-indigo"><i class="bi bi-dribbble"></i></span>
            <div>
              <span class="label-caps">Venue &amp; Lapangan</span>
              <h3><?= e($tiket['venue']) ?></h3>
              <p><?= e($tiket['alamat']) ?></p>
            </div>
          </div>

          <div class="info-grid">
            <div class="info-tile">
              <span class="label-caps"><i class="bi bi-calendar3"></i> Jadwal Main</span>
              <strong><?= e($tiket['tanggal']) ?></strong>
              <span class="accent"><?= e($tiket['jam']) ?></span>
            </div>
            <div class="info-tile">
              <span class="label-caps"><i class="bi bi-person"></i> Penyewa</span>
              <strong><?= e($tiket['penyewa']) ?></strong>
              <span class="muted"><?= e($tiket['tim']) ?></span>
            </div>
          </div>

          <div class="total-bar">
            <span>Total Terbayar</span>
            <span><strong><?= e(rupiah($tiket['total'])) ?></strong> <small>(Lunas)</small></span>
          </div>
        </div>
      </section>
    </div>

    <!-- Kanan: fasilitas, instruksi, lokasi, aksi -->
    <div class="page__col">
      <figure class="facility">
        <img src="../../assets/img/fasilitas.jpg" alt="Fasilitas venue">
        <figcaption><i class="bi bi-shield-check"></i><span><?= e($tiket['fasilitas']['teks']) ?></span></figcaption>
      </figure>

      <details class="card-app fold" open>
        <summary>
          <span class="menu__icon tint-indigo"><i class="bi bi-info-circle"></i></span>
          <span class="fold__title">Instruksi Kedatangan <span>(<?= count($instruksi) ?> Poin)</span></span>
          <i class="bi bi-chevron-up fold__chev" aria-hidden="true"></i>
        </summary>
        <ol class="steps">
          <?php foreach ($instruksi as $langkah): ?>
            <li><?= $langkah ?></li>
          <?php endforeach; ?>
        </ol>
      </details>

      <details class="card-app fold" open>
        <summary>
          <span class="menu__icon tint-indigo"><i class="bi bi-map"></i></span>
          <span class="fold__title">Lokasi &amp; Petunjuk Arah <span>(<?= e($tiket['jarak']) ?>)</span></span>
          <i class="bi bi-chevron-up fold__chev" aria-hidden="true"></i>
        </summary>
        <div class="map">
          <iframe src="<?= e($mapEmbed) ?>" title="Peta lokasi" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          <span class="map__traffic"><?= e($tiket['trafik']) ?></span>
          <div class="map__place">
            <div>
              <strong><?= e($tiket['venue']) ?></strong>
              <small><?= e($tiket['patokan']) ?></small>
            </div>
            <a class="btn-route" href="<?= e($mapLink) ?>" target="_blank" rel="noopener"><i class="bi bi-cursor"></i> Rute</a>
          </div>
        </div>
      </details>

      <nav class="actions" aria-label="Aksi tiket">
        <a class="btn-main" href="<?= e($tiket['pdf']) ?>" download><i class="bi bi-download"></i> Unduh Tiket PDF / Simpan Gambar</a>
        <a class="btn-soft" href="<?= e($mapLink) ?>" target="_blank" rel="noopener"><i class="bi bi-cursor"></i> Petunjuk Arah (Buka Google Maps)</a>
        <a class="link-home" href="beranda.php"><i class="bi bi-house"></i> Kembali ke Beranda</a>
      </nav>
    </div>

  </div>
</main>

<script>
// Salin kode reservasi ke clipboard
document.getElementById('btnSalin').addEventListener('click', async function () {
  const label = this.querySelector('span');
  try {
    await navigator.clipboard.writeText(document.getElementById('kode').textContent.trim());
    label.textContent = 'Tersalin!';
  } catch (err) {
    label.textContent = 'Gagal';
  }
  setTimeout(() => (label.textContent = 'Salin'), 1800);
});
</script>
</body>
</html>
