<?php
/** Navigasi owner: bottom bar di HP, sidebar di desktop (>=1024px). Variabel: $assets, $halamanAktif */
$menuMitra = [
    'beranda'   => ['bi-grid',       'Beranda',   'beranda-mitra.php'],
    'jadwal'    => ['bi-calendar3',  'Jadwal',    'jadwal.php'],
    'profil'    => ['bi-shop',       'Profil',    'profil.php'],
];
?>
<nav aria-label="Navigasi mitra"
     class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur
            lg:inset-y-0 lg:right-auto lg:w-64 lg:border-r lg:border-t-0 lg:pb-0">
  <div class="hidden h-[72px] items-center px-6 lg:flex">
    <img src="<?= e($assets) ?>/img/logo.png" alt="Arena Sport" class="h-9 w-auto">
  </div>
  <ul class="grid grid-cols-3 lg:grid-cols-1 lg:gap-1 lg:px-3 lg:pt-2">
    <?php foreach ($menuMitra as $kunci => [$ikon, $label, $href]):
        $aktif = $kunci === $halamanAktif; ?>
      <li>
        <a href="<?= e($href) ?>" <?= $aktif ? 'aria-current="page"' : '' ?>
           class="flex flex-col items-center gap-1 py-3 text-xs font-semibold transition-colors
                  lg:flex-row lg:gap-3 lg:rounded-xl lg:px-4 lg:text-sm
                  <?= $aktif ? 'text-brand-600 lg:bg-brand-50' : 'text-slate-500 hover:text-brand-700 lg:hover:bg-slate-50' ?>">
          <i class="bi <?= e($ikon) ?> text-2xl leading-none lg:text-xl"></i>
          <span><?= e($label) ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>
