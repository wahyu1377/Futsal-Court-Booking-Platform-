<?php $pageTitle = 'Hasil Pencarian - Arena Sport'; ?>
<?php include '../../components/header.php'; ?>
<?php include '../../components/navbar.php'; ?>

<main class="max-w-lg mx-auto px-4 pb-6">

    <!-- Search bar -->
    <form action="hasil_pencarian.php" method="get" class="mb-4">
        <div class="flex items-center bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <i class="bi bi-search text-gray-400 text-lg pl-4 pr-2 shrink-0"></i>
            <input type="search" id="q" name="q"
                   value="Futsal Jakarta Selatan"
                   class="flex-1 py-3 pr-3 text-sm bg-transparent outline-none text-gray-700">
            <button type="submit"
                    class="bg-primary hover:bg-primary-dark text-white text-sm font-semibold px-5 py-3 transition-colors shrink-0">
                Cari
            </button>
        </div>
    </form>

    <!-- Filter aktif chips -->
    <div class="flex gap-2 overflow-x-auto pb-1 mb-3 scrollbar-hide">
        <span class="shrink-0 flex items-center gap-1 bg-primary-light text-primary text-xs font-semibold px-3 py-1 rounded-full">
            Cilandak <i class="bi bi-x-circle-fill cursor-pointer"></i>
        </span>
        <span class="shrink-0 flex items-center gap-1 bg-primary-light text-primary text-xs font-semibold px-3 py-1 rounded-full">
            Futsal <i class="bi bi-x-circle-fill cursor-pointer"></i>
        </span>
        <span class="shrink-0 flex items-center gap-1 bg-primary-light text-primary text-xs font-semibold px-3 py-1 rounded-full">
            Indoor AC <i class="bi bi-x-circle-fill cursor-pointer"></i>
        </span>
        <span class="shrink-0 flex items-center gap-1 bg-primary-light text-primary text-xs font-semibold px-3 py-1 rounded-full">
            Malam <i class="bi bi-x-circle-fill cursor-pointer"></i>
        </span>
    </div>

    <!-- Info hasil + sort + filter -->
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-600">
            <strong class="text-secondary">14</strong> lapangan ditemukan
        </p>
        <div class="flex items-center gap-2">
            <!-- Sort -->
            <select name="urutan" id="urutan"
                    class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white text-gray-600 focus:outline-none focus:ring-1 focus:ring-primary">
                <option value="jarak">Jarak Terdekat</option>
                <option value="harga">Harga Terendah</option>
                <option value="rating">Rating Tertinggi</option>
            </select>
            <!-- Tombol buka offcanvas filter -->
            <button type="button"
                    class="flex items-center gap-1.5 text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white text-gray-600 hover:border-primary hover:text-primary transition-colors"
                    data-bs-toggle="offcanvas" data-bs-target="#offcanvasFilter" aria-controls="offcanvasFilter">
                <i class="bi bi-sliders"></i> Filter (5)
            </button>
        </div>
    </div>

    <!-- Daftar kartu lapangan -->
    <div class="space-y-4">

        <!-- Kartu 1 -->
        <article class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
            <div class="relative">
                <img src="../../assets/img/lapangan-futsal-outdoor.webp"
                     alt="Mega Futsal & Mini Soccer"
                     class="w-full h-44 object-cover">
                <button type="button"
                        class="absolute top-3 right-3 bg-white/90 w-8 h-8 rounded-full flex items-center justify-center shadow-sm hover:text-red-500 transition-colors"
                        aria-label="Simpan ke favorit">
                    <i class="bi bi-heart text-gray-400"></i>
                </button>
                <span class="absolute bottom-3 left-3 bg-primary-light text-primary text-[11px] font-bold px-2.5 py-1 rounded-full">
                    Sisa 3 slot hari ini
                </span>
            </div>
            <div class="p-4">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <h2 class="font-bold text-secondary text-sm leading-snug">Mega Futsal &amp; Mini Soccer</h2>
                    <span class="shrink-0 flex items-center gap-1 text-xs text-amber-500 font-semibold">
                        <i class="bi bi-star-fill"></i> 4.9
                        <span class="text-gray-400 font-normal">(142)</span>
                    </span>
                </div>
                <p class="text-xs text-gray-400 mb-2">
                    <i class="bi bi-geo-alt text-primary"></i> 1.2 km · TB Simatupang, Cilandak
                </p>
                <div class="flex gap-1.5 mb-3 flex-wrap">
                    <span class="text-[11px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Indoor</span>
                    <span class="text-[11px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Parkir Luas</span>
                    <span class="text-[11px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Toilet Bersih</span>
                    <span class="text-[11px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Kantin</span>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wide">Tarif Sewa</p>
                        <p class="text-base font-bold text-secondary">Rp175.000 <span class="text-xs font-normal text-gray-400">/ jam</span></p>
                    </div>
                    <a href="detail_jadwal.php"
                       class="bg-primary hover:bg-primary-dark text-white text-xs font-semibold px-4 py-2 rounded-xl transition-colors">
                        Pesan Sekarang
                    </a>
                </div>
            </div>
        </article>

        <!-- Kartu 2 -->
        <article class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
            <div class="relative">
                <img src="../../assets/img/meadowbank-multisport-arena.webp"
                     alt="Tibeta Futsal Center"
                     class="w-full h-44 object-cover">
                <button type="button"
                        class="absolute top-3 right-3 bg-white/90 w-8 h-8 rounded-full flex items-center justify-center shadow-sm hover:text-red-500 transition-colors"
                        aria-label="Simpan ke favorit">
                    <i class="bi bi-heart text-gray-400"></i>
                </button>
            </div>
            <div class="p-4">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <h2 class="font-bold text-secondary text-sm leading-snug">Tibeta Futsal Center</h2>
                    <span class="shrink-0 flex items-center gap-1 text-xs text-amber-500 font-semibold">
                        <i class="bi bi-star-fill"></i> 4.7
                        <span class="text-gray-400 font-normal">(89)</span>
                    </span>
                </div>
                <p class="text-xs text-gray-400 mb-2">
                    <i class="bi bi-geo-alt text-primary"></i> 2.4 km · Cilandak Barat
                </p>
                <div class="flex gap-1.5 mb-3 flex-wrap">
                    <span class="text-[11px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Sintetis</span>
                    <span class="text-[11px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Loker Kunci</span>
                    <span class="text-[11px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Mushola</span>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wide">Tarif Sewa</p>
                        <p class="text-base font-bold text-secondary">Rp140.000 <span class="text-xs font-normal text-gray-400">/ jam</span></p>
                    </div>
                    <a href="detail_jadwal.php"
                       class="bg-primary hover:bg-primary-dark text-white text-xs font-semibold px-4 py-2 rounded-xl transition-colors">
                        Pesan Sekarang
                    </a>
                </div>
            </div>
        </article>

        <!-- Kartu 3 -->
        <article class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
            <div class="relative">
                <img src="../../assets/img/atap-lapangan-futsal.webp"
                     alt="Gelora Futsal Cilandak"
                     class="w-full h-44 object-cover">
                <button type="button"
                        class="absolute top-3 right-3 bg-white/90 w-8 h-8 rounded-full flex items-center justify-center shadow-sm hover:text-red-500 transition-colors"
                        aria-label="Simpan ke favorit">
                    <i class="bi bi-heart text-gray-400"></i>
                </button>
            </div>
            <div class="p-4">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <h2 class="font-bold text-secondary text-sm leading-snug">Gelora Futsal Cilandak</h2>
                    <span class="shrink-0 flex items-center gap-1 text-xs text-amber-500 font-semibold">
                        <i class="bi bi-star-fill"></i> 4.6
                        <span class="text-gray-400 font-normal">(64)</span>
                    </span>
                </div>
                <p class="text-xs text-gray-400 mb-2">
                    <i class="bi bi-geo-alt text-primary"></i> 3.1 km · Fatmawati, Cilandak
                </p>
                <div class="flex gap-1.5 mb-3 flex-wrap">
                    <span class="text-[11px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Vinyl Court</span>
                    <span class="text-[11px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Lampu LED</span>
                    <span class="text-[11px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">WiFi</span>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wide">Tarif Sewa</p>
                        <p class="text-base font-bold text-secondary">Rp160.000 <span class="text-xs font-normal text-gray-400">/ jam</span></p>
                    </div>
                    <a href="detail_jadwal.php"
                       class="bg-primary hover:bg-primary-dark text-white text-xs font-semibold px-4 py-2 rounded-xl transition-colors">
                        Pesan Sekarang
                    </a>
                </div>
            </div>
        </article>

    </div>

</main>

<!-- Offcanvas Filter — Bootstrap Offcanvas -->
<div class="offcanvas offcanvas-bottom rounded-t-3xl" tabindex="-1" id="offcanvasFilter" aria-labelledby="offcanvasFilterLabel"
     style="max-height: 85vh;">
    <div class="offcanvas-header border-b px-5 pt-5 pb-4">
        <h5 class="offcanvas-title font-bold text-secondary" id="offcanvasFilterLabel">Filter Pencarian</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
    </div>
    <div class="offcanvas-body px-5 overflow-y-auto">
        <form action="hasil_pencarian.php" method="get" class="space-y-5">

            <!-- Wilayah -->
            <div>
                <label for="wilayah" class="block text-sm font-semibold text-secondary mb-2">Wilayah</label>
                <select id="wilayah" name="wilayah"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="semua">Semua wilayah</option>
                    <option value="cilandak" selected>Cilandak</option>
                    <option value="kebayoran-baru">Kebayoran Baru</option>
                    <option value="pasar-minggu">Pasar Minggu</option>
                </select>
            </div>

            <!-- Jenis olahraga -->
            <div>
                <p class="text-sm font-semibold text-secondary mb-2">Jenis Olahraga</p>
                <div class="flex gap-2 flex-wrap">
                    <label class="flex items-center gap-2 border border-gray-200 rounded-xl px-3 py-2 text-sm cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary-light has-[:checked]:text-primary transition-colors">
                        <input type="radio" name="olahraga" value="semua" class="hidden"> Semua
                    </label>
                    <label class="flex items-center gap-2 border border-primary bg-primary-light text-primary rounded-xl px-3 py-2 text-sm cursor-pointer transition-colors">
                        <input type="radio" name="olahraga" value="futsal" checked class="hidden"> ⚽ Futsal
                    </label>
                    <label class="flex items-center gap-2 border border-gray-200 rounded-xl px-3 py-2 text-sm cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary-light has-[:checked]:text-primary transition-colors">
                        <input type="radio" name="olahraga" value="mini-soccer" class="hidden"> Mini Soccer
                    </label>
                </div>
            </div>

            <!-- Fasilitas -->
            <div>
                <p class="text-sm font-semibold text-secondary mb-2">Fasilitas Venue</p>
                <div class="space-y-2">
                    <?php
                    $fasilitas = [
                        ['ac', 'Indoor AC', true],
                        ['parkir', 'Parkir Luas', true],
                        ['kantin', 'Kantin Minuman', false],
                        ['mushola', 'Mushola Bersih', true],
                        ['loker', 'Loker & Shower Air Hangat', false],
                    ];
                    foreach ($fasilitas as [$val, $label, $checked]):
                    ?>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="fasilitas[]" value="<?= $val ?>" <?= $checked ? 'checked' : '' ?>
                               class="w-4 h-4 accent-primary rounded">
                        <span class="text-sm text-gray-700"><?= $label ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Waktu main -->
            <div>
                <p class="text-sm font-semibold text-secondary mb-2">Waktu Main</p>
                <div class="flex gap-2 flex-wrap">
                    <label class="flex items-center gap-2 border border-gray-200 rounded-xl px-3 py-2 text-sm cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary-light has-[:checked]:text-primary transition-colors">
                        <input type="radio" name="waktu" value="pagi" class="hidden"> 🌅 Pagi
                    </label>
                    <label class="flex items-center gap-2 border border-gray-200 rounded-xl px-3 py-2 text-sm cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary-light has-[:checked]:text-primary transition-colors">
                        <input type="radio" name="waktu" value="siang" class="hidden"> ☀️ Siang
                    </label>
                    <label class="flex items-center gap-2 border border-primary bg-primary-light text-primary rounded-xl px-3 py-2 text-sm cursor-pointer transition-colors">
                        <input type="radio" name="waktu" value="malam" checked class="hidden"> 🌙 Malam
                    </label>
                </div>
            </div>

            <!-- Tombol aksi -->
            <div class="flex gap-3 pt-2 pb-4">
                <button type="reset"
                        class="flex-1 border border-gray-300 text-gray-600 font-semibold py-3 rounded-xl text-sm hover:bg-gray-50 transition-colors">
                    Reset
                </button>
                <button type="submit"
                        class="flex-1 bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-xl text-sm transition-colors"
                        data-bs-dismiss="offcanvas">
                    Terapkan Filter
                </button>
            </div>

        </form>
    </div>
</div>

<?php include '../../components/bottom_nav.php'; ?>
<?php include '../../components/footer.php'; ?>
