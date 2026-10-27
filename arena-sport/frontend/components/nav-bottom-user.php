<?php
/**
 * Navigasi bawah untuk halaman-halaman pengguna.
 *
 * Dipakai oleh: akun.php, edit-profil.php, setelan.php, bantuan.php
 * (frontend/pages/user/).
 *
 * Variabel yang dipakai:
 *   $halamanAktif - key menu yang sedang aktif ('beranda' | 'favorit' |
 *                   'pesanan' | 'akun')
 */
require_once __DIR__ . '/helper_profil.php';   // e()

$halamanAktif = $halamanAktif ?? '';

// Ikon sama persis dengan navbar.php (halaman beranda, favorit, tiket).
$navBottomUser = [
    'beranda' => ['bi-dribbble',          'Beranda', 'beranda.php'],
    'favorit' => ['bi-bookmark',          'Favorit', 'favorit.php'],
    'pesanan' => ['bi-ticket-perforated', 'Pesanan', 'tiket.php'],
    'akun'    => ['bi-person-circle',     'Akun',    'akun.php'],
];
?>
<nav class="bottom-nav" aria-label="Navigasi utama">
  <div class="bottom-nav__list">
    <?php foreach ($navBottomUser as $key => [$ikon, $label, $href]): ?>
      <a class="bottom-nav__link<?= $key === $halamanAktif ? ' is-active' : '' ?>"
         href="<?= e($href) ?>"<?= $key === $halamanAktif ? ' aria-current="page"' : '' ?>>
        <i class="bi <?= e($ikon) ?>"></i><span><?= e($label) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</nav>
