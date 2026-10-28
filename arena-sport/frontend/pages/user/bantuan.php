<?php
declare(strict_types=1);

/**
 * Halaman Bantuan Pengguna.
 *
 * Isi & tampilan sama persis dengan halaman Bantuan Owner
 * (frontend/pages/owner/bantuan.php): FAQ (Bootstrap accordion) + kontak admin.
 * Tidak ada data dinamis dari database, jadi ringan.
 */
require __DIR__ . '/../../../backend/core/auth.php';
require_once __DIR__ . '/../../components/helper_profil.php';   // e()
requireLogin('user');

$halamanAktif = 'akun';

// Daftar FAQ pengguna
$faq = [
    ['Bagaimana cara memesan lapangan?',
     'Buka menu <b>Beranda</b>, pilih lapangan yang Anda suka, lalu tentukan <b>tanggal</b> dan <b>jam</b> yang tersedia. Klik <b>Pesan Sekarang</b> dan selesaikan pembayaran.'],
    ['Berapa lama slot saya ditahan setelah memesan?',
     'Slot ditahan selama <b>15 menit</b> atau sampai pembayaran selesai, mana yang terjadi lebih dulu. Kalau pembayarannya terlambat, pesanan otomatis kedaluwarsa dan slot dilepas kembali.'],
    ['Bagaimana cara membayar?',
     'Pembayaran dilakukan lewat QRIS, Virtual Account, atau kartu debit/kredit. Anda akan diarahkan ke halaman pembayaran DOKU yang aman, lalu pesanan otomatis aktif setelah pembayaran berhasil.'],
    ['Apakah saya bisa membatalkan pesanan?',
     'Bisa, selama status pesanan masih <b>Menunggu Pembayaran</b>. Buka menu <b>Pesanan</b>, pilih pesanan yang ingin dibatalkan, lalu tekan tombol pembatalan. Slot yang sudah dibayar tidak bisa dibatalkan secara mandiri.'],
    ['Bagaimana cara mengganti kata sandi?',
     'Buka <b>Akun &rarr; Setelan &rarr; Keamanan</b>, lalu isi kata sandi lama dan kata sandi baru (minimal 8 karakter). Kalau lupa kata sandi, pakai tautan <b>Lupa Kata Sandi?</b> di halaman masuk.'],
    ['Bagaimana cara mengganti foto profil?',
     'Buka <b>Akun &rarr; Edit Profil</b>, tekan <b>Pilih Foto Baru</b>, lalu <b>Simpan Foto</b>. Foto otomatis dipotong persegi dari bagian tengah.'],
    ['Kenapa email saya tidak bisa disimpan?',
     'Email harus unik dan belum dipakai akun pengguna maupun owner lain. Kalau muncul pesan "sudah dipakai", pakai email lain atau hubungi admin.'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <?php /* Microsoft Clarity (pelacakan halaman) */ ?>
  <?php include dirname(__DIR__, 2) . '/components/clarity.php'; ?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bantuan - Arena Sport</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="../../assets/css/style.css" rel="stylesheet">
  <link href="../../assets/css/akun.css" rel="stylesheet">
  <link href="../../assets/css/bottom-nav.css" rel="stylesheet">
</head>
<body class="app">

<header class="app-header">
  <div class="app-header__inner">
    <a href="akun.php" class="icon-btn" aria-label="Kembali ke Akun"><i class="bi bi-arrow-left"></i></a>
    <h1>Bantuan</h1>
  </div>
</header>

<main class="page">
  <div class="page__stack">

    <section class="card-app op-form">
      <h3 class="op-form__title"><i class="bi bi-question-circle me-1"></i> FAQ</h3>

      <div class="accordion op-accordion" id="faqAccordion">
        <?php foreach ($faq as $i => [$judul, $isi]): ?>
          <div class="accordion-item">
            <h2 class="accordion-header" id="faqHead<?= $i ?>">
              <button class="accordion-button<?= $i === 0 ? '' : ' collapsed' ?>" type="button"
                      data-bs-toggle="collapse" data-bs-target="#faqBody<?= $i ?>"
                      aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>" aria-controls="faqBody<?= $i ?>">
                <?= e($judul) ?>
              </button>
            </h2>
            <div id="faqBody<?= $i ?>" class="accordion-collapse collapse<?= $i === 0 ? ' show' : '' ?>"
                 aria-labelledby="faqHead<?= $i ?>" data-bs-parent="#faqAccordion">
              <div class="accordion-body"><?= $isi ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="card-app op-form">
      <h3 class="op-form__title"><i class="bi bi-headset me-1"></i> Hubungi Admin</h3>
      <p class="op-hint">
        Masih bingung? Hubungi tim Arena Sport lewat WhatsApp pada jam kerja
        (Senin&ndash;Sabtu, 09.00&ndash;17.00 WIB). Admin akan membantu pemecahan masalah
        pemesanan, pembayaran, dan perubahan data akun Anda.
      </p>
      <ul class="op-list">
        <li><span>Email</span><strong>admin@arenasport.id</strong></li>
        <li><span>WhatsApp</span><strong>0812-0000-0000</strong></li>
        <li><span>Jam layanan</span><strong>09.00&ndash;17.00 WIB</strong></li>
      </ul>
    </section>

  </div>
</main>

<?php include __DIR__ . '/../../components/nav-bottom-user.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
