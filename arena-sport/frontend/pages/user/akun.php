<?php $pageTitle = 'Akun Saya - Arena Sport'; ?>
<?php include '../../components/header.php'; ?>
<?php include '../../components/navbar.php'; ?>

<main class="max-w-lg mx-auto px-4 pb-6">

    <!-- Header akun -->
    <div class="flex items-center justify-between mb-5">
        <h1 class="text-lg font-bold text-secondary">Akun Saya</h1>
        <button type="button"
                class="relative w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors"
                aria-label="Notifikasi">
            <i class="bi bi-bell text-secondary text-xl"></i>
            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
        </button>
    </div>

    <!-- Profil pengguna -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 mb-4">
        <div class="flex items-center gap-4">
            <div class="relative shrink-0">
                <img src="../../assets/img/lapangan-futsal-02.webp"
                     alt="Bambang Pamungkas"
                     class="w-20 h-20 rounded-2xl object-cover border-4 border-primary-light">
                <span class="absolute -bottom-1 -right-1 bg-primary w-6 h-6 rounded-full flex items-center justify-center border-2 border-white">
                    <i class="bi bi-check-lg text-white text-[10px]"></i>
                </span>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="font-bold text-secondary text-base truncate">Bambang Pamungkas</h2>
                <p class="text-xs text-gray-400 truncate">bambang.p@gmail.com</p>
                <p class="text-xs text-gray-400">0812-3456-7890</p>
            </div>
        </div>
        <button type="button"
                class="mt-4 w-full flex items-center justify-center gap-2 border border-gray-200 text-gray-600 text-sm font-semibold py-2.5 rounded-xl hover:border-primary hover:text-primary transition-colors">
            <i class="bi bi-pencil-square"></i> Edit Profil
        </button>
    </div>

    <!-- Arena Points -->
    <div class="bg-gradient-to-r from-primary to-primary-dark rounded-3xl p-5 mb-4 text-white relative overflow-hidden">
        <div class="relative z-10">
            <span class="text-xs font-bold uppercase tracking-wider opacity-80 bg-white/20 px-3 py-1 rounded-full">
                🥈 Tier Atlet Silver
            </span>
            <div class="flex items-end justify-between mt-3 mb-2">
                <div>
                    <p class="text-xs opacity-70">Arena Points Anda</p>
                    <p class="text-3xl font-bold">120 <span class="text-base font-normal opacity-80">Poin</span></p>
                </div>
                <i class="bi bi-trophy-fill text-4xl opacity-30"></i>
            </div>
            <!-- Progress bar -->
            <div class="w-full bg-white/20 rounded-full h-2 mb-2">
                <div class="bg-white rounded-full h-2" style="width: 80%"></div>
            </div>
            <p class="text-xs opacity-70">Kumpulkan 30 poin lagi untuk klaim <strong>1 jam main gratis!</strong> (120 / 150 pts)</p>
        </div>
        <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full"></div>
    </div>

    <!-- Menu aktivitas -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-4">
        <div class="px-4 pt-4 pb-2">
            <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest">Aktivitas Saya</p>
        </div>
        <div class="divide-y divide-gray-50">

            <a href="tiket.php" class="flex items-center gap-4 px-4 py-3.5 hover:bg-gray-50 transition-colors">
                <div class="w-10 h-10 bg-primary-light rounded-xl flex items-center justify-center shrink-0">
                    <i class="bi bi-receipt text-primary"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-secondary">Riwayat Pesanan</p>
                    <p class="text-xs text-gray-400">Lihat jadwal booking lalu &amp; aktif</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs bg-primary text-white font-bold px-2 py-0.5 rounded-full">2 Aktif</span>
                    <i class="bi bi-chevron-right text-gray-300 text-sm"></i>
                </div>
            </a>

            <a href="#" class="flex items-center gap-4 px-4 py-3.5 hover:bg-gray-50 transition-colors">
                <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center shrink-0">
                    <i class="bi bi-hourglass-split text-amber-500"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-secondary">Daftar Tunggu (Waiting List)</p>
                    <p class="text-xs text-gray-400">Pantau slot lapangan penuh</p>
                </div>
                <i class="bi bi-chevron-right text-gray-300 text-sm shrink-0"></i>
            </a>

            <a href="#" class="flex items-center gap-4 px-4 py-3.5 hover:bg-gray-50 transition-colors">
                <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center shrink-0">
                    <i class="bi bi-heart-fill text-red-400"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-secondary">Lapangan Favorit</p>
                    <p class="text-xs text-gray-400">3 venue tersimpan</p>
                </div>
                <i class="bi bi-chevron-right text-gray-300 text-sm shrink-0"></i>
            </a>

        </div>
    </div>

    <!-- Menu pengaturan -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-4">
        <div class="px-4 pt-4 pb-2">
            <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest">Pengaturan &amp; Bantuan</p>
        </div>
        <div class="divide-y divide-gray-50">

            <a href="#" class="flex items-center gap-4 px-4 py-3.5 hover:bg-gray-50 transition-colors">
                <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
                    <i class="bi bi-credit-card text-blue-500"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-secondary">Metode Pembayaran Tersimpan</p>
                    <p class="text-xs text-gray-400">QRIS, Virtual Account, Kartu</p>
                </div>
                <i class="bi bi-chevron-right text-gray-300 text-sm shrink-0"></i>
            </a>

            <a href="#" class="flex items-center gap-4 px-4 py-3.5 hover:bg-gray-50 transition-colors">
                <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center shrink-0">
                    <i class="bi bi-gear text-gray-500"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-secondary">Setelan</p>
                    <p class="text-xs text-gray-400">Konfigurasi keamanan, notifikasi, dan preferensi akun</p>
                </div>
                <i class="bi bi-chevron-right text-gray-300 text-sm shrink-0"></i>
            </a>

            <a href="#" class="flex items-center gap-4 px-4 py-3.5 hover:bg-gray-50 transition-colors">
                <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center shrink-0">
                    <i class="bi bi-question-circle text-purple-500"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-secondary">Bantuan</p>
                    <p class="text-xs text-gray-400">FAQ, kebijakan privasi, &amp; hubungi admin</p>
                </div>
                <i class="bi bi-chevron-right text-gray-300 text-sm shrink-0"></i>
            </a>

        </div>
    </div>

    <!-- Tombol keluar — trigger Bootstrap Modal konfirmasi -->
    <button type="button"
            class="w-full flex items-center justify-center gap-2 border border-red-200 text-red-500 font-semibold py-3 rounded-xl text-sm hover:bg-red-50 transition-colors"
            data-bs-toggle="modal" data-bs-target="#modalKeluar">
        <i class="bi bi-box-arrow-right"></i> Keluar dari Akun
    </button>

    <!-- Footer versi app -->
    <p class="text-center text-xs text-gray-400 mt-6">
        Arena Sport App v2.4.1 (Build 2024)<br>
        <em>Satu sentuhan menuju lapangan juara.</em>
    </p>

</main>

<!-- Modal konfirmasi keluar — Bootstrap Modal -->
<div class="modal fade" id="modalKeluar" tabindex="-1" aria-labelledby="modalKeluarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mx-4">
        <div class="modal-content rounded-3xl border-0 shadow-xl">
            <div class="modal-body p-6 text-center">
                <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="bi bi-box-arrow-right text-red-500 text-2xl"></i>
                </div>
                <h5 class="font-bold text-secondary text-base mb-1" id="modalKeluarLabel">Keluar dari Akun?</h5>
                <p class="text-sm text-gray-500 mb-5">Anda harus login kembali untuk mengakses reservasi.</p>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button"
                            class="border border-gray-200 text-gray-600 font-semibold py-2.5 rounded-xl text-sm hover:bg-gray-50 transition-colors"
                            data-bs-dismiss="modal">
                        Batal
                    </button>
                    <a href="../../pages/auth/login.php"
                       class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors text-center">
                        Ya, Keluar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../components/bottom_nav.php'; ?>
<?php include '../../components/footer.php'; ?>
