<!-- Navbar atas: logo + lokasi + notifikasi -->
<nav class="fixed top-0 left-0 right-0 z-50 bg-white shadow-sm">
    <div class="max-w-lg mx-auto px-4 h-14 flex items-center justify-between">

        <!-- Logo -->
        <a href="/arena-sport/frontend/pages/user/beranda.php">
            <img src="/arena-sport/frontend/assets/img/logo.png" alt="Arena Sport" class="h-8 w-auto">
        </a>

        <!-- Kanan: notifikasi + avatar -->
        <div class="flex items-center gap-3">
            <button type="button" class="relative text-gray-500 hover:text-primary transition-colors" aria-label="Notifikasi">
                <i class="bi bi-bell text-xl"></i>
                <!-- Badge notifikasi -->
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">3</span>
            </button>

            <a href="/arena-sport/frontend/pages/user/akun.php">
                <img src="/arena-sport/frontend/assets/img/avatar-default.png" alt="Profil"
                     class="w-8 h-8 rounded-full object-cover border-2 border-primary">
            </a>
        </div>

    </div>
</nav>

<!-- Spacer agar konten tidak tertimpa navbar fixed -->
<div class="h-14"></div>
