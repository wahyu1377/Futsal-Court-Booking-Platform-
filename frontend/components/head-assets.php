<?php
/**
 * Aset <head> bersama untuk halaman owner (dipakai lewat include di dalam <head>).
 * Stack sesuai README: Tailwind CSS (styling) + Bootstrap 5 (komponen: dropdown).
 * Variabel wajib dari halaman: $assets (path ke folder frontend/assets).
 *
 * CATATAN: Tailwind via CDN hanya untuk development. Untuk produksi, build dengan
 * Tailwind CLI -> assets/css/tailwind.css, lalu ganti <script> CDN dengan <link>.
 */
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Bootstrap dimuat DULU supaya preflight Tailwind (dimuat setelahnya) yang menang untuk elemen dasar -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<script src="https://cdn.tailwindcss.com/3.4.17"></script>
<script>
  tailwind.config = {
    theme: { extend: {
      fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
      colors: {
        brand: { 50:'#ECFDF5', 100:'#D1FAE5', 200:'#A7F3D0', 300:'#6EE7B7', 500:'#10B981',
                 600:'#059669', 700:'#047857', 800:'#065F46' },
        ink:   '#0F172A',
        tint:  '#E3E9F8',
        page:  '#F3F5FA'
      },
      boxShadow: { card: '0 1px 2px rgba(15,23,42,.04), 0 6px 20px rgba(15,23,42,.07)' }
    } }
  };
</script>
<link href="<?= e($assets) ?>/css/venue.css" rel="stylesheet">
