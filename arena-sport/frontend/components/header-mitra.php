<?php
/** Header halaman owner. Variabel: $assets, $data['mitra'] */
?>
<?php include __DIR__ . '/clarity.php'; ?>
<header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur lg:ml-64">
  <div class="mx-auto flex h-[72px] max-w-6xl items-center gap-3 px-4">
    <img src="<?= e($assets) ?>/img/logo.png" alt="Arena Sport" class="h-7 w-auto lg:hidden">
    <div class="mr-auto min-w-0 leading-tight">
      <p class="text-xs font-bold uppercase tracking-wider text-brand-700">Mitra Arena</p>
      <h1 class="truncate text-xl font-bold text-ink">Beranda Mitra</h1>
    </div>
    <button type="button" class="relative grid h-11 w-11 place-items-center rounded-full text-2xl text-ink hover:bg-slate-100" aria-label="Notifikasi">
      <i class="bi bi-bell"></i>
      <span class="absolute right-2.5 top-2.5 h-2.5 w-2.5 rounded-full border-2 border-white bg-brand-600"></span>
    </button>
    
         class="h-11 w-11 rounded-full border-2 border-brand-600 object-cover object-top">
  </div>
</header>
