<?php
declare(strict_types=1);

/**
 * Halaman Bantuan Owner.
 *
 * Isi: FAQ (Bootstrap accordion) + shortcut ke halaman lain.
 * Tidak ada data dinamis dari database, jadi ringan.
 */
require __DIR__ . '/../../../backend/core/auth.php';
require_once __DIR__ . '/../../components/helper_profil.php';   // e()
requireLogin('owner');

$halamanAktif = 'akun';

// Daftar FAQ owner
$faq = [
    ['Bagaimana cara menambahkan lapangan baru?',
     'Buka menu <b>Tambah Lapangan</b> di Dashboard Owner, isi nama, jenis, alamat, tarif per jam, jam buka-tutup, dan durasi sesi. Setelah tersimpan, lapangan langsung muncul di halaman Beranda dan bisa dipesan pengguna.'],
    ['Kenapa pesanan saya belum masuk ke daftar "Pesanan Masuk"?',
     'Pesanan baru muncul di tab <b>Aktif</b> pada Dashboard Owner maksimal beberapa detik setelah pengguna menekan tombol bayar. Kalau masih kosong, periksa dulu bahwa lapangan Anda berstatus aktif dan tariknya tidak penuh.'],
    ['Berapa lama slot lapangan saya dikunci setelah pesanan dibuat?',
     'Slot ditahan selama <b>15 menit</b> atau sampai pembayaran selesai, mana yang terjadi lebih dulu. Kalau pembayarannya terlambat, pesanan otomatis kedaluwarsa dan slot dilepas kembali.'],
    ['Bagaimana cara membatalkan pesanan?',
     'Anda tidak bisa membatalkan pesanan pengguna dari sisi owner. Pengguna bisa batalkan sendiri selama statusnya masih "Menunggu Pembayaran" lewat halaman Tiket.'],
    ['Apakah saya bisa mengganti kata sandi?',
     'Buka <b>Akun &rarr; Setelan &rarr; Keamanan</b>, lalu isi kata sandi lama dan kata sandi baru (minimal 8 karakter).'],
    ['Mengapa email saya tidak bisa disimpan?',
     'Email harus unik dan belum dipakai akun pengguna maupun owner lain. Kalau muncul pesan "sudah dipakai", pakai email lain atau hubungi admin.'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
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
      <h3 class="op-form__title"><i class="bi bi-question-circle me-1"></i> FAQ Owner</h3>

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
        pemesanan, pembayaran, dan data lapangan Anda.
      </p>
      <ul class="op-list">
        <li><span>Email</span><strong>admin@arenasport.id</strong></li>
        <li><span>WhatsApp</span><strong>0812-0000-0000</strong></li>
        <li><span>Jam layanan</span><strong>09.00&ndash;17.00 WIB</strong></li>
      </ul>
    </section>

  </div>
</main>

<?php include __DIR__ . '/../../components/nav-bottom-owner.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
