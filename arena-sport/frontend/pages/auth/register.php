<?php $pageTitle = 'Registrasi - Arena Sport'; ?>
<?php include '../../components/header.php'; ?>

<div class="min-h-screen flex flex-col justify-between max-w-lg mx-auto px-4 py-8">

    <!-- Logo -->
    <div class="text-center mb-6">
        <a href="../user/beranda.php">
            <img src="../../assets/img/logo.png" alt="Arena Sport" class="h-10 mx-auto">
        </a>
    </div>

    <main class="flex-1">

        <!-- Badge -->
        <span class="inline-flex items-center gap-1.5 bg-primary-light text-primary text-xs font-semibold px-3 py-1 rounded-full mb-4">
            <span class="w-2 h-2 bg-primary rounded-full animate-pulse"></span>
            PENDAFTARAN ATLET BARU
        </span>

        <h1 class="text-2xl font-bold text-secondary mb-1">Daftar Akun Baru</h1>
        <p class="text-gray-500 text-sm mb-6">Lengkapi data diri Anda untuk mulai reservasi lapangan.</p>

        <!-- Form registrasi -->
        <form action="login.php" method="get" class="space-y-4">

            <!-- Nama lengkap -->
            <div>
                <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" id="nama" name="nama"
                       placeholder="Bambang Pamungkas"
                       class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition"
                       required>
            </div>

            <!-- Email / HP -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email atau Nomor HP</label>
                <input type="text" id="email" name="email"
                       placeholder="atlet@arenasport.id"
                       class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition"
                       required>
            </div>

            <!-- Kata sandi -->
            <div>
                <label for="pw" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
                <input type="password" id="pw" name="password"
                       placeholder="Minimal 8 karakter aman"
                       minlength="8"
                       class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition"
                       required>
            </div>

            <!-- Konfirmasi kata sandi -->
            <div>
                <label for="pw2" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Kata Sandi</label>
                <input type="password" id="pw2" name="password2"
                       placeholder="Ulangi kata sandi Anda"
                       class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition"
                       required>
            </div>

            <!-- Persetujuan syarat -->
            <div class="flex items-start gap-2">
                <input type="checkbox" id="setuju" name="setuju"
                       class="w-4 h-4 mt-0.5 accent-primary rounded shrink-0" required>
                <label for="setuju" class="text-sm text-gray-600">
                    Saya setuju dengan
                    <a href="#" class="text-primary font-medium hover:underline">Syarat &amp; Ketentuan</a>
                    serta
                    <a href="#" class="text-primary font-medium hover:underline">Kebijakan Privasi</a>
                    Arena Sport.
                </label>
            </div>

            <!-- Tombol daftar -->
            <button type="submit"
                    class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-xl transition-colors text-sm">
                Daftar Sekarang →
            </button>

        </form>

        <!-- Divider -->
        <div class="flex items-center gap-3 my-4">
            <hr class="flex-1 border-gray-200">
            <span class="text-xs text-gray-400">atau daftar lebih cepat dengan</span>
            <hr class="flex-1 border-gray-200">
        </div>

        <!-- Tombol Google -->
        <button type="button"
                class="w-full border border-gray-300 rounded-xl py-3 text-sm font-medium text-gray-700 flex items-center justify-center gap-2 hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84z"/></svg>
            Google
        </button>

        <!-- Sudah punya akun -->
        <p class="text-center text-sm text-gray-500 mt-6">
            Sudah punya akun?
            <a href="login.php" class="text-primary font-bold hover:underline">Masuk di sini</a>
        </p>

        <!-- SSL badge -->
        <p class="text-center text-xs text-gray-400 mt-4 flex items-center justify-center gap-1">
            <i class="bi bi-shield-lock-fill text-primary"></i>
            ENKRIPSI STANDAR ARENA SPORT 256-BIT SSL
        </p>

    </main>

</div>

<?php include '../../components/footer.php'; ?>
