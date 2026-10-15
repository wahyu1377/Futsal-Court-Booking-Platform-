<?php $pageTitle = 'Checkout Booking - Arena Sport'; ?>
<?php include '../../components/header.php'; ?>

<div class="max-w-lg mx-auto">

    <!-- Header navigasi atas -->
    <div class="sticky top-0 z-40 bg-white border-b border-gray-100 shadow-sm">
        <div class="flex items-center gap-3 px-4 h-14">
            <a href="detail_jadwal.php" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
                <i class="bi bi-arrow-left text-lg text-secondary"></i>
            </a>
            <h1 class="font-bold text-secondary text-sm">Checkout Booking</h1>
        </div>
    </div>

    <!-- Stepper progress -->
    <div class="flex items-center px-6 py-3 bg-white border-b border-gray-100">
        <div class="flex items-center gap-1 text-xs text-gray-400">
            <span class="w-5 h-5 rounded-full bg-primary-light text-primary flex items-center justify-center font-bold text-[10px]">✓</span>
            Kontak
        </div>
        <div class="flex-1 h-px bg-primary mx-2"></div>
        <div class="flex items-center gap-1 text-xs text-primary font-semibold">
            <span class="w-5 h-5 rounded-full bg-primary text-white flex items-center justify-center font-bold text-[10px]">2</span>
            Detail
        </div>
        <div class="flex-1 h-px bg-gray-200 mx-2"></div>
        <div class="flex items-center gap-1 text-xs text-gray-400">
            <span class="w-5 h-5 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center font-bold text-[10px]">3</span>
            Bayar
        </div>
    </div>

    <main class="px-4 pb-44 space-y-4 mt-4">

        <!-- Kartu ringkasan lapangan -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <img src="../../assets/img/lapangan-futsal-01.webp"
                 alt="Champion Futsal Arena"
                 class="w-full h-40 object-cover">
            <div class="p-4">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">#BKG-9921</span>
                    <span class="text-xs text-gray-500 font-medium uppercase tracking-wide">Futsal Vinyl Pro A</span>
                </div>
                <h2 class="font-bold text-secondary text-base mb-2">Champion Futsal Arena</h2>
                <div class="flex flex-col gap-1 text-sm text-gray-600">
                    <p class="flex items-center gap-2">
                        <i class="bi bi-calendar3 text-primary shrink-0"></i> Selasa, 22 Okt 2026
                    </p>
                    <p class="flex items-center gap-2">
                        <i class="bi bi-clock text-primary shrink-0"></i> 19.00 – 21.00 WIB
                        <span class="text-xs text-gray-400">· Durasi: 2 Jam</span>
                    </p>
                </div>
            </div>
        </div>

        <form action="tiket.php" method="get" class="space-y-4">

            <!-- Data kontak — accordion collapsed Bootstrap -->
            <div class="accordion" id="accordionKontak">
                <div class="accordion-item border border-gray-200 rounded-2xl overflow-hidden">
                    <h2 class="accordion-header">
                        <button class="accordion-button text-sm font-semibold text-secondary bg-white rounded-2xl"
                                type="button" data-bs-toggle="collapse" data-bs-target="#collapseKontak" aria-expanded="true">
                            <i class="bi bi-person-check text-primary me-2"></i>
                            1. Data Kontak Penyewa
                            <span class="ms-auto text-xs text-primary bg-primary-light font-bold px-2.5 py-1 rounded-full me-2">Tervalidasi ✓</span>
                        </button>
                    </h2>
                    <div id="collapseKontak" class="accordion-collapse collapse show">
                        <div class="accordion-body text-sm text-gray-600 pt-0">
                            <p class="font-semibold text-secondary">Bambang Pamungkas · Garuda FC</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Opsi tambahan -->
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <p class="text-sm font-semibold text-secondary mb-3 flex items-center gap-2">
                    <i class="bi bi-bag-plus text-primary"></i>
                    2. Opsi Tambahan
                </p>
                <div class="space-y-3">

                    <!-- Rompi -->
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="opsi[]" value="rompi" checked
                               class="w-4 h-4 mt-1 accent-primary rounded shrink-0">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-secondary">Sewa Rompi Latihan <span class="text-primary font-bold">+Rp 30.000</span></p>
                            <p class="text-xs text-gray-400">12 pcs Rompi Neon/Oranye berstandar</p>
                        </div>
                    </label>
                    <hr class="border-gray-100">

                    <!-- Wasit -->
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="opsi[]" value="wasit"
                               class="w-4 h-4 mt-1 accent-primary rounded shrink-0">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-secondary">Jasa Wasit Pertandingan <span class="text-primary font-bold">+Rp 100.000</span></p>
                            <p class="text-xs text-gray-400">2 Jam, Berlisensi Askot Futsal</p>
                        </div>
                    </label>
                    <hr class="border-gray-100">

                    <!-- Bola gratis -->
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="opsi[]" value="bola" checked
                               class="w-4 h-4 mt-1 accent-primary rounded shrink-0">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-secondary">
                                Bola Futsal Tambahan
                                <span class="ml-1 text-[11px] bg-primary-light text-primary font-bold px-2 py-0.5 rounded-full">GRATIS</span>
                            </p>
                            <p class="text-xs text-gray-400">2 buah bola match standar FIFA</p>
                        </div>
                    </label>

                </div>
            </div>

            <!-- Metode pembayaran -->
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <p class="text-sm font-semibold text-secondary mb-3 flex items-center gap-2">
                    <i class="bi bi-credit-card text-primary"></i>
                    3. Metode Pembayaran
                </p>
                <div class="space-y-3">

                    <!-- QRIS -->
                    <label class="block cursor-pointer">
                        <input type="radio" name="bayar" value="qris" checked class="hidden peer">
                        <div class="flex items-start gap-3 px-4 py-3 rounded-2xl border border-gray-200 transition-all peer-checked:border-primary peer-checked:bg-primary-light">
                            <i class="bi bi-qr-code text-primary text-xl shrink-0 mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-secondary">QRIS / Instan
                                    <span class="ml-1 text-[11px] bg-primary text-white font-bold px-2 py-0.5 rounded-full">Otomatis Terverifikasi</span>
                                </p>
                                <p class="text-xs text-gray-400">BCA, Mandiri, GoPay, ShopeePay, OVO</p>
                            </div>
                        </div>
                    </label>

                    <!-- Virtual Account -->
                    <label class="block cursor-pointer">
                        <input type="radio" name="bayar" value="va" class="hidden peer">
                        <div class="flex items-start gap-3 px-4 py-3 rounded-2xl border border-gray-200 transition-all peer-checked:border-primary peer-checked:bg-primary-light">
                            <i class="bi bi-bank text-gray-400 text-xl shrink-0 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-semibold text-secondary">Virtual Account Bank</p>
                                <p class="text-xs text-gray-400">BCA, Mandiri, BRI, BNI (Bebas Biaya Admin)</p>
                            </div>
                        </div>
                    </label>

                    <!-- Bayar di lapangan -->
                    <label class="block cursor-pointer">
                        <input type="radio" name="bayar" value="lapangan" class="hidden peer">
                        <div class="flex items-start gap-3 px-4 py-3 rounded-2xl border border-gray-200 transition-all peer-checked:border-primary peer-checked:bg-primary-light">
                            <i class="bi bi-cash-coin text-gray-400 text-xl shrink-0 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-semibold text-secondary">Pembayaran di Lapangan
                                    <span class="ml-1 text-[11px] bg-amber-100 text-amber-600 font-bold px-2 py-0.5 rounded-full">DP 50%</span>
                                </p>
                                <p class="text-xs text-gray-400">DP via transfer, pelunasan sisa di kasir arena</p>
                            </div>
                        </div>
                    </label>

                </div>
            </div>

            <!-- Jaminan booking -->
            <div class="flex items-center gap-3 bg-primary-light border border-primary/20 rounded-2xl px-4 py-3">
                <i class="bi bi-shield-check-fill text-primary text-2xl shrink-0"></i>
                <div>
                    <p class="text-sm font-bold text-secondary">Jaminan Booking 100% Sah</p>
                    <p class="text-xs text-gray-500">Jadwal Anda langsung terkunci secara resmi di sistem Arena.</p>
                </div>
            </div>

            <!-- Rincian biaya — sticky bottom -->
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-40">
                <div class="max-w-lg mx-auto px-4 py-4">
                    <!-- Tabel biaya -->
                    <div class="space-y-1 mb-3">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Sewa Lapangan (2 Jam)</span>
                            <span>Rp 360.000</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Biaya Tambahan Rompi</span>
                            <span>Rp 30.000</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Biaya Layanan Aplikasi</span>
                            <span>Rp 2.000</span>
                        </div>
                        <hr class="border-gray-200 my-1">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide">Total Tagihan</p>
                                <p class="text-xl font-bold text-secondary">Rp 392.000</p>
                                <p class="text-[10px] text-gray-400">Termasuk PPN</p>
                            </div>
                            <button type="submit"
                                    class="bg-primary hover:bg-primary-dark text-white font-semibold px-6 py-3 rounded-xl text-sm transition-colors">
                                Lanjut Bayar →
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </form>

    </main>

</div>

<?php include '../../components/footer.php'; ?>
