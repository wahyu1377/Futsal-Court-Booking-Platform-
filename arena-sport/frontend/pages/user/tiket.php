<?php $pageTitle = 'Tiket Reservasi - Arena Sport'; ?>
<?php include '../../components/header.php'; ?>

<div class="max-w-lg mx-auto px-4 pb-8">

    <!-- Header -->
    <div class="flex items-center gap-3 h-14">
        <a href="beranda.php" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
            <i class="bi bi-arrow-left text-lg text-secondary"></i>
        </a>
        <h1 class="font-bold text-secondary text-sm">Tiket Reservasi</h1>
    </div>

    <!-- Banner sukses -->
    <div class="bg-gradient-to-br from-primary to-primary-dark rounded-3xl p-6 text-white text-center mb-6 relative overflow-hidden">
        <div class="relative z-10">
            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="bi bi-check-lg text-white text-3xl"></i>
            </div>
            <h2 class="text-lg font-bold mb-1">Reservasi Berhasil Dikonfirmasi!</h2>
            <p class="text-sm opacity-80 mb-2">Status: <strong>Instant Confirmed</strong></p>
            <p class="text-xs opacity-70 leading-relaxed">
                Pembayaran telah diverifikasi otomatis via QRIS. Akses lapangan Anda sudah siap.
            </p>
        </div>
        <div class="absolute -right-8 -top-8 w-36 h-36 bg-white/10 rounded-full"></div>
        <div class="absolute -left-4 -bottom-10 w-28 h-28 bg-white/10 rounded-full"></div>
    </div>

    <!-- Kartu tiket — QR Code -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-4">
        <!-- Header kartu -->
        <div class="bg-gray-50 border-b border-dashed border-gray-200 px-5 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Kode Reservasi</p>
                    <p class="text-lg font-bold text-secondary font-mono tracking-wider">BK-20241022-8849</p>
                </div>
                <button type="button"
                        id="btnSalinKode"
                        class="flex items-center gap-1.5 text-xs text-primary border border-primary/30 bg-primary-light px-3 py-1.5 rounded-lg hover:bg-primary hover:text-white transition-colors">
                    <i class="bi bi-clipboard"></i> Salin
                </button>
            </div>
            <p class="text-xs text-primary font-medium mt-1">
                <i class="bi bi-check-circle-fill"></i> Status Pembayaran: Lunas (QRIS)
            </p>
        </div>

        <!-- QR Code -->
        <div class="flex flex-col items-center py-6 px-5 border-b border-dashed border-gray-200">
            <img src="../../assets/img/lapangan-futsal-03.webp"
                 alt="QR Tiket BK-20241022-8849"
                 class="w-48 h-48 rounded-2xl object-cover border-4 border-primary/20 mb-3">
            <p class="text-xs text-gray-500 text-center">
                <i class="bi bi-qr-code text-primary"></i>
                Tunjukkan QR ini kepada resepsionis saat kedatangan.
            </p>
        </div>

        <!-- Detail venue -->
        <div class="px-5 py-4">
            <h3 class="font-bold text-secondary mb-1">Mega Futsal &amp; Mini Soccer</h3>
            <p class="text-xs text-gray-400 flex items-center gap-1 mb-4">
                <i class="bi bi-geo-alt-fill text-primary"></i>
                Jl. TB Simatupang No. 18, Jakarta Selatan
            </p>

            <!-- Tabel detail -->
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="bg-gray-50 rounded-xl p-3">
                    <p class="text-xs text-gray-400 mb-1">Jadwal Main</p>
                    <p class="font-semibold text-secondary text-xs">Selasa, 22 Okt 2024</p>
                    <p class="font-bold text-secondary">19.00 – 21.00 WIB</p>
                    <p class="text-xs text-gray-400">Durasi 2 Jam</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3">
                    <p class="text-xs text-gray-400 mb-1">Penyewa</p>
                    <p class="font-bold text-secondary">Bambang Pamungkas</p>
                    <p class="text-xs text-gray-500">Garuda FC</p>
                </div>
            </div>

            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                <p class="text-xs text-gray-400">Total Terbayar</p>
                <p class="font-bold text-secondary text-base">Rp 392.000 <span class="text-xs font-normal text-primary">(Lunas)</span></p>
            </div>
        </div>
    </div>

    <!-- Foto fasilitas -->
    <div class="relative rounded-2xl overflow-hidden mb-4">
        <img src="../../assets/img/lapangan-futsal-outdoor.webp"
             alt="Fasilitas Pro"
             class="w-full h-36 object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end p-4">
            <p class="text-white text-xs font-medium">
                <i class="bi bi-stars text-amber-400"></i>
                Fasilitas Pro: Ruang Ganti Ber-AC, Shower Air Hangat &amp; Loker Digital
            </p>
        </div>
    </div>

    <!-- Instruksi kedatangan -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
        <h2 class="font-bold text-secondary text-sm mb-3 flex items-center gap-2">
            <i class="bi bi-list-check text-primary"></i> Instruksi Kedatangan
        </h2>
        <ol class="space-y-3">
            <li class="flex items-start gap-3 text-sm text-gray-600">
                <span class="w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">1</span>
                Hadir minimal <strong>15 menit</strong> sebelum kick-off untuk proses registrasi ulang dan pemanasan tim.
            </li>
            <li class="flex items-start gap-3 text-sm text-gray-600">
                <span class="w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">2</span>
                Wajib mengenakan <strong>sepatu olahraga sol karet datar (non-marking)</strong> khusus lapangan vinyl indoor.
            </li>
            <li class="flex items-start gap-3 text-sm text-gray-600">
                <span class="w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">3</span>
                Tunjukkan QR tiket kepada staf resepsionis lobi untuk pembagian kunci ruang ganti.
            </li>
        </ol>
    </div>

    <!-- Peta lokasi -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-5">
        <div class="px-5 pt-4 pb-2">
            <h2 class="font-bold text-secondary text-sm flex items-center gap-2">
                <i class="bi bi-map text-primary"></i> Lokasi Venue
            </h2>
            <p class="text-xs text-gray-400">Jarak 2.4 km · trafik lancar (±6 menit)</p>
        </div>
        <iframe src="https://www.google.com/maps?q=Jl.+TB+Simatupang+18+Jakarta+Selatan&output=embed"
                class="w-full h-44 border-0" title="Peta lokasi Mega Futsal" loading="lazy">
        </iframe>
        <div class="px-5 py-3">
            <p class="text-xs font-semibold text-secondary">Mega Futsal &amp; Mini Soccer</p>
            <p class="text-xs text-gray-400">Dekat Stasiun MRT Fatmawati</p>
        </div>
    </div>

    <!-- Tombol aksi -->
    <div class="grid grid-cols-1 gap-3">
        <a href="../../assets/img/lapangan-futsal-01.webp" download
           class="flex items-center justify-center gap-2 bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-xl text-sm transition-colors">
            <i class="bi bi-download"></i> Unduh Tiket / Simpan Gambar
        </a>
        <a href="https://maps.google.com/?q=Jl.+TB+Simatupang+18+Jakarta+Selatan"
           target="_blank" rel="noopener"
           class="flex items-center justify-center gap-2 border border-primary text-primary font-semibold py-3 rounded-xl text-sm hover:bg-primary-light transition-colors">
            <i class="bi bi-map-fill"></i> Petunjuk Arah (Google Maps)
        </a>
        <a href="beranda.php"
           class="flex items-center justify-center gap-2 border border-gray-200 text-gray-600 font-semibold py-3 rounded-xl text-sm hover:bg-gray-50 transition-colors">
            <i class="bi bi-house"></i> Kembali ke Beranda
        </a>
    </div>

</div>

<script>
    /* Fungsionalitas salin kode reservasi */
    document.getElementById('btnSalinKode').addEventListener('click', function () {
        navigator.clipboard.writeText('BK-20241022-8849').then(() => {
            this.innerHTML = '<i class="bi bi-check-lg"></i> Tersalin!';
            setTimeout(() => {
                this.innerHTML = '<i class="bi bi-clipboard"></i> Salin';
            }, 2000);
        });
    });
</script>

<?php include '../../components/footer.php'; ?>
