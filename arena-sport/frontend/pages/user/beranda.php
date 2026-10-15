<?php $pageTitle = 'Beranda - Arena Sport'; ?>
<?php include '../../components/header.php'; ?>
<?php include '../../components/navbar.php'; ?>

<main class="max-w-lg mx-auto px-4 pb-6">

    <!-- Salam + lokasi -->
    <div class="flex items-center justify-between mb-4">
        <div>
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Lokasi Kamu</p>
            <!-- Dropdown lokasi — pakai Bootstrap Dropdown -->
            <div class="dropdown">
                <button class="btn btn-sm btn-link p-0 text-secondary font-semibold text-base dropdown-toggle"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-geo-alt-fill text-primary"></i> Jakarta Selatan
                </button>
                <ul class="dropdown-menu shadow-lg border-0 rounded-xl">
                    <li><a class="dropdown-item text-sm" href="#">Jakarta Selatan</a></li>
                    <li><a class="dropdown-item text-sm" href="#">Jakarta Barat</a></li>
                    <li><a class="dropdown-item text-sm" href="#">Jakarta Pusat</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Search bar -->
    <form action="hasil_pencarian.php" method="get" class="mb-5">
        <div class="flex items-center bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <i class="bi bi-search text-gray-400 text-lg pl-4 pr-2 shrink-0"></i>
            <input type="search" name="q"
                   placeholder="Cari lapangan, olahraga, atau lokasi..."
                   class="flex-1 py-3 pr-4 text-sm bg-transparent outline-none text-gray-700 placeholder-gray-400">
            <button type="submit"
                    class="bg-primary hover:bg-primary-dark text-white text-sm font-semibold px-5 py-3 transition-colors shrink-0">
                Cari
            </button>
        </div>
    </form>

    <!-- Filter kategori olahraga -->
    <div class="flex gap-2 overflow-x-auto pb-1 mb-5 scrollbar-hide">
        <a href="hasil_pencarian.php"
           class="shrink-0 px-4 py-1.5 rounded-full bg-primary text-white text-xs font-semibold shadow-sm">
            Semua (12)
        </a>
        <a href="hasil_pencarian.php?olahraga=futsal"
           class="shrink-0 px-4 py-1.5 rounded-full bg-white border border-gray-200 text-gray-600 text-xs font-medium hover:border-primary hover:text-primary transition-colors">
            ⚽ Futsal (4)
        </a>
        <a href="hasil_pencarian.php?olahraga=badminton"
           class="shrink-0 px-4 py-1.5 rounded-full bg-white border border-gray-200 text-gray-600 text-xs font-medium hover:border-primary hover:text-primary transition-colors">
            🏸 Badminton (3)
        </a>
        <a href="hasil_pencarian.php?olahraga=basket"
           class="shrink-0 px-4 py-1.5 rounded-full bg-white border border-gray-200 text-gray-600 text-xs font-medium hover:border-primary hover:text-primary transition-colors">
            🏀 Basket
        </a>
    </div>

    <!-- Banner promo -->
    <div class="bg-gradient-to-r from-primary to-primary-dark rounded-2xl p-5 mb-6 text-white relative overflow-hidden">
        <div class="relative z-10">
            <span class="text-xs font-bold uppercase tracking-wider opacity-80 bg-white/20 px-2 py-0.5 rounded-full">
                🔥 Promo Main Malam
            </span>
            <h2 class="text-xl font-bold mt-2 mb-1">Diskon 20%<br>Sewa Lapangan</h2>
            <p class="text-sm opacity-80 mb-4">Berlaku tiap hari pukul 21:00 – 24:00 WIB untuk slot indoor.</p>
            <!-- Tombol klaim promo — trigger Bootstrap Modal -->
            <button type="button"
                    class="bg-white text-primary font-semibold text-sm px-5 py-2 rounded-xl hover:bg-gray-100 transition-colors"
                    data-bs-toggle="modal" data-bs-target="#modalPromo">
                Klaim Voucher
            </button>
        </div>
        <!-- Dekorasi lingkaran -->
        <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full"></div>
        <div class="absolute -right-2 -bottom-8 w-24 h-24 bg-white/10 rounded-full"></div>
    </div>

    <!-- Kartu lapangan rekomendasi -->
    <div class="flex items-center justify-between mb-3">
        <div>
            <h2 class="text-base font-bold text-secondary">Rekomendasi Terdekat</h2>
            <p class="text-xs text-gray-400">Berdasarkan lokasi kamu saat ini</p>
        </div>
        <a href="hasil_pencarian.php" class="text-xs text-primary font-semibold hover:underline">Lihat Semua</a>
    </div>

    <div class="space-y-4">

        <!-- Kartu lapangan 1 -->
        <article class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
            <div class="relative">
                <img src="../../assets/img/lapangan-futsal-01.webp"
                     alt="Champion Futsal Arena Cilandak"
                     class="w-full h-44 object-cover">
                <span class="absolute top-3 left-3 bg-white/90 text-primary text-xs font-bold px-2.5 py-1 rounded-full shadow-sm">
                    Indoor AC
                </span>
                <button type="button"
                        class="absolute top-3 right-3 bg-white/90 w-8 h-8 rounded-full flex items-center justify-center shadow-sm hover:text-red-500 transition-colors"
                        aria-label="Simpan ke favorit">
                    <i class="bi bi-heart text-gray-400"></i>
                </button>
            </div>
            <div class="p-4">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <h3 class="font-bold text-secondary text-sm leading-snug">Champion Futsal Arena Cilandak</h3>
                    <span class="shrink-0 flex items-center gap-1 text-xs text-amber-500 font-semibold">
                        <i class="bi bi-star-fill"></i> 4.8
                    </span>
                </div>
                <p class="text-xs text-gray-400 mb-1">
                    <i class="bi bi-geo-alt text-primary"></i> 1.2 km · Cilandak Barat
                </p>
                <div class="flex flex-wrap gap-1.5 mb-3">
                    <span class="text-[11px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Futsal</span>
                    <span class="text-[11px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Rumput Sintetis</span>
                    <span class="text-[11px] bg-primary-light text-primary px-2 py-0.5 rounded-full font-medium">Tersedia Hari Ini</span>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wide">Tarif Sewa</p>
                        <p class="text-base font-bold text-secondary">Rp150.000 <span class="text-xs font-normal text-gray-400">/ jam</span></p>
                    </div>
                    <a href="detail_jadwal.php"
                       class="bg-primary hover:bg-primary-dark text-white text-xs font-semibold px-4 py-2 rounded-xl transition-colors">
                        Pilih Lapangan
                    </a>
                </div>
            </div>
        </article>

        <!-- Kartu lapangan 2 -->
        <article class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
            <div class="relative">
                <img src="../../assets/img/lapangan-futsal-03.webp"
                     alt="Smash Badminton Hall Kuningan"
                     class="w-full h-44 object-cover">
                <span class="absolute top-3 left-3 bg-white/90 text-primary text-xs font-bold px-2.5 py-1 rounded-full shadow-sm">
                    Standar BWF
                </span>
                <button type="button"
                        class="absolute top-3 right-3 bg-white/90 w-8 h-8 rounded-full flex items-center justify-center shadow-sm hover:text-red-500 transition-colors"
                        aria-label="Simpan ke favorit">
                    <i class="bi bi-heart text-gray-400"></i>
                </button>
            </div>
            <div class="p-4">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <h3 class="font-bold text-secondary text-sm leading-snug">Smash Badminton Hall Kuningan</h3>
                    <span class="shrink-0 flex items-center gap-1 text-xs text-amber-500 font-semibold">
                        <i class="bi bi-star-fill"></i> 4.9
                    </span>
                </div>
                <p class="text-xs text-gray-400 mb-1">
                    <i class="bi bi-geo-alt text-primary"></i> 2.5 km · Setiabudi Kuningan
                </p>
                <div class="flex flex-wrap gap-1.5 mb-3">
                    <span class="text-[11px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Badminton</span>
                    <span class="text-[11px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Karpet Vinyl BWF</span>
                    <span class="text-[11px] bg-primary-light text-primary px-2 py-0.5 rounded-full font-medium">Tersedia Hari Ini</span>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wide">Tarif Sewa</p>
                        <p class="text-base font-bold text-secondary">Rp75.000 <span class="text-xs font-normal text-gray-400">/ jam</span></p>
                    </div>
                    <a href="detail_jadwal.php"
                       class="bg-primary hover:bg-primary-dark text-white text-xs font-semibold px-4 py-2 rounded-xl transition-colors">
                        Pilih Lapangan
                    </a>
                </div>
            </div>
        </article>

    </div>

</main>

<!-- Modal Klaim Promo — Bootstrap Modal -->
<div class="modal fade" id="modalPromo" tabindex="-1" aria-labelledby="modalPromoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mx-4">
        <div class="modal-content rounded-3xl border-0 shadow-xl">
            <div class="modal-body p-6 text-center">
                <div class="text-4xl mb-3">🎉</div>
                <h5 class="font-bold text-secondary text-lg mb-1" id="modalPromoLabel">Voucher Berhasil Diklaim!</h5>
                <p class="text-sm text-gray-500 mb-4">Gunakan kode <span class="font-bold text-primary">MALAM20</span> saat checkout untuk diskon 20%.</p>
                <button type="button"
                        class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-2.5 rounded-xl transition-colors text-sm"
                        data-bs-dismiss="modal">
                    Mengerti, Terima Kasih!
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../../components/bottom_nav.php'; ?>
<?php include '../../components/footer.php'; ?>
