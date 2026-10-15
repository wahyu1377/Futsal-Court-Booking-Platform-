<?php $pageTitle = 'Tambah Lapangan - Arena Sport'; ?>
<?php include '../../components/header.php'; ?>

<div class="max-w-lg mx-auto">

    <!-- Header navigasi atas -->
    <div class="sticky top-0 z-40 bg-white border-b border-gray-100 shadow-sm">
        <div class="flex items-center gap-3 px-4 h-14">
            <a href="../../pages/user/akun.php"
               class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
                <i class="bi bi-arrow-left text-lg text-secondary"></i>
            </a>
            <h1 class="font-bold text-secondary text-sm">Tambah Lapangan</h1>
        </div>
    </div>

    <!-- Stepper progress -->
    <div class="flex items-center px-6 py-3 bg-white border-b border-gray-100">
        <div class="flex items-center gap-1 text-xs text-primary font-semibold">
            <span class="w-5 h-5 rounded-full bg-primary text-white flex items-center justify-center font-bold text-[10px]">1</span>
            Info Dasar
        </div>
        <div class="flex-1 h-px bg-gray-200 mx-2"></div>
        <div class="flex items-center gap-1 text-xs text-gray-400">
            <span class="w-5 h-5 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center font-bold text-[10px]">2</span>
            Fasilitas &amp; Harga
        </div>
        <div class="flex-1 h-px bg-gray-200 mx-2"></div>
        <div class="flex items-center gap-1 text-xs text-gray-400">
            <span class="w-5 h-5 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center font-bold text-[10px]">3</span>
            Jadwal &amp; Foto
        </div>
    </div>

    <main class="px-4 py-5 pb-32">

        <form action="#" method="post" class="space-y-5">

            <!-- Nama lapangan / venue -->
            <div>
                <label for="nama" class="block text-sm font-semibold text-secondary mb-1.5">
                    Nama Lapangan / Venue <span class="text-red-500">*</span>
                </label>
                <input type="text" id="nama" name="nama"
                       placeholder="contoh: Champion Futsal Arena"
                       class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition"
                       required>
            </div>

            <!-- Jenis olahraga -->
            <div>
                <label for="olahraga" class="block text-sm font-semibold text-secondary mb-1.5">
                    Jenis Olahraga <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select id="olahraga" name="olahraga"
                            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-primary transition">
                        <option value="">Pilih jenis olahraga</option>
                        <option value="futsal">⚽ Futsal</option>
                        <option value="mini-soccer">🥅 Mini Soccer</option>
                        <option value="badminton">🏸 Badminton</option>
                        <option value="basket">🏀 Basket</option>
                    </select>
                    <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>
            </div>

            <!-- Jenis lantai -->
            <div>
                <label class="block text-sm font-semibold text-secondary mb-2">Jenis Lantai</label>
                <div class="flex flex-wrap gap-2">
                    <?php
                    $lantai = ['Vinyl', 'Rumput Sintetis', 'Parket', 'Karpet'];
                    foreach ($lantai as $l):
                    ?>
                    <label class="cursor-pointer">
                        <input type="radio" name="lantai" value="<?= strtolower(str_replace(' ', '-', $l)) ?>" class="hidden peer">
                        <span class="block px-4 py-2 rounded-xl border border-gray-200 text-sm text-gray-600 transition-all
                            peer-checked:border-primary peer-checked:bg-primary-light peer-checked:text-primary peer-checked:font-semibold
                            hover:border-primary hover:text-primary">
                            <?= $l ?>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Alamat lengkap -->
            <div>
                <label for="alamat" class="block text-sm font-semibold text-secondary mb-1.5">
                    Alamat Lengkap <span class="text-red-500">*</span>
                </label>
                <textarea id="alamat" name="alamat" rows="3"
                          placeholder="contoh: Jl. TB Simatupang No. 18, Cilandak Barat"
                          class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition"
                          required></textarea>
            </div>

            <!-- Kota / kecamatan -->
            <div>
                <label for="kota" class="block text-sm font-semibold text-secondary mb-1.5">Kota / Kecamatan</label>
                <input type="text" id="kota" name="kota"
                       placeholder="contoh: Jakarta Selatan"
                       class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition">
            </div>

            <!-- Deskripsi -->
            <div>
                <label for="deskripsi" class="block text-sm font-semibold text-secondary mb-1.5">Deskripsi Venue</label>
                <textarea id="deskripsi" name="deskripsi" rows="4"
                          placeholder="Jelaskan keunggulan venue Anda, fasilitas, atau info penting lainnya..."
                          class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition"></textarea>
                <p class="text-xs text-gray-400 mt-1">Maksimal 500 karakter</p>
            </div>

            <!-- Tombol aksi — sticky bottom -->
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-40">
                <div class="max-w-lg mx-auto px-4 py-4 flex gap-3">
                    <a href="../../pages/user/akun.php"
                       class="flex-1 flex items-center justify-center border border-gray-200 text-gray-600 font-semibold py-3 rounded-xl text-sm hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                            class="flex-1 bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-xl text-sm transition-colors">
                        Lanjut → Fasilitas &amp; Harga
                    </button>
                </div>
            </div>

        </form>

    </main>

</div>

<?php include '../../components/footer.php'; ?>
