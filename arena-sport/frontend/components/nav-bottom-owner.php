<?php
/**
 * Navigasi bawah untuk halaman-halaman owner.
 *
 * Dipakai oleh: akun.php, setelan.php, bantuan.php, edit-profil.php,
 * dan dashboard.php (frontend/pages/owner/).
 *
 * Variabel yang dipakai:
 *   $halamanAktif - key menu yang sedang aktif ('dashboard' | 'pesanan' |
 *                   'lapangan' | 'akun')
 */
require_once __DIR__ . '/helper_profil.php';   // e()

$halamanAktif = $halamanAktif ?? '';

// Ikon sengaja disamakan dengan halaman pengguna:
// Dashboard = bi-dribbble (sama dengan "Beranda"), Pesanan = bi-ticket-perforated.
$navBottomOwner = [
    'dashboard' => ['bi-dribbble',          'Dashboard', 'dashboard.php'],
    'pesanan'   => ['bi-ticket-perforated', 'Pesanan',   'dashboard.php#pesananOwner'],
    'lapangan'  => ['bi-grid-3x3-gap',      'Lapangan',  'dashboard.php#lapanganSaya'],
    'akun'      => ['bi-person-circle',     'Akun',      'akun.php'],
];
?>
<nav class="bottom-nav" aria-label="Navigasi utama">
  <div class="bottom-nav__list">
    <?php foreach ($navBottomOwner as $key => [$ikon, $label, $href]): ?>
      <a class="bottom-nav__link<?= $key === $halamanAktif ? ' is-active' : '' ?>"
         href="<?= e($href) ?>"<?= $key === $halamanAktif ? ' aria-current="page"' : '' ?>>
        <i class="bi <?= e($ikon) ?>"></i><span><?= e($label) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</nav>
