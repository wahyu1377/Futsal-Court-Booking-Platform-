<?php $pageTitle = 'Detail & Jadwal - Arena Sport'; ?>
<?php include '../../components/header.php'; ?>

<div class="max-w-lg mx-auto">

    <!-- Header navigasi atas -->
    <div class="sticky top-0 z-40 bg-white border-b border-gray-100 shadow-sm">
        <div class="flex items-center justify-between px-4 h-14">
            <a href="hasil_pencarian.php" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
                <i class="bi bi-arrow-left text-lg text-secondary"></i>
            </a>
            <h1 class="font-bold text-secondary text-sm">Detail Lapangan</h1>
            <div class="flex gap-2">
                <button type="button" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors" aria-label="Bagikan">
                    <i class="bi bi-share text-secondary"></i>
                </button>
                <button type="button" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors" aria-label="Simpan ke favorit">
                    <i class="bi bi-heart text-secondary"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Stepper progress -->
    <div class="flex items-center px-6 py-3 bg-white border-b border-gray-100">
        <div class="flex items-center gap-1 text-xs text-gray-400">
            <span class="w-5 h-5 rounded-full bg-primary-light text-primary flex items-center justify-center font-bold text-[10px]">✓</span>
            Rincian
        </div>
        <div class="flex-1 h-px bg-primary mx-2"></div>
        <div class="flex items-center gap-1 text-xs text-primary font-semibold">
            <span class="w-5 h-5 rounded-full bg-primary text-white flex items-center justify-center font-bold text-[10px]">2</span>
            Jadwal
        </div>
        <div class="flex-1 h-px bg-gray-200 mx-2"></div>
        <div class="flex items-center gap-1 text-xs text-gray-400">
            <span class="w-5 h-5 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center font-bold text-[10px]">3</span>
            Bayar
        </div>
    </div>

    <main class="px-4 pb-36">

        <!-- Foto lapangan -->
        <div class="relative -mx-4 mb-4">
            <img src="../../assets/img/lapangan-futsal-01.webp"
                 alt="Champion Futsal Arena"
                 class="w-full h-56 object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
            <span class="absolute bottom-3 left-4 text-white text-xs font-semibold bg-primary/80 px-3 py-1 rounded-full">
                Futsal · Indoor Vinyl FIFA · Grade Turnamen
            </span>
        </div>

        <!-- Info venue -->
        <div class="mb-5">
            <div class="flex items-start justify-between gap-2 mb-1">
                <h2 class="text-xl font-bold text-secondary">Champion Futsal Arena</h2>
                <span class="shrink-0 flex items-center gap-1 text-sm text-amber-500 font-semibold">
                    <i class="bi bi-star-fill"></i> 4.8
                </span>
            </div>
            <p class="text-xs text-gray-400 mb-1">Court A · 124 ulasan terverifikasi · Venue Terverifikasi</p>
            <p class="text-xs text-gray-500 flex items-start gap-1.5">
                <i class="bi bi-geo-alt-fill text-primary shrink-0 mt-0.5"></i>
                Jl. TB Simatupang No. 18, Cilandak Barat, Jakarta Selatan
            </p>
        </div>

        <!-- Accordion spesifikasi — Bootstrap Accordion -->
        <div class="accordion mb-5" id="accordionSpek">
            <div class="accordion-item border border-gray-200 rounded-2xl overflow-hidden">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed text-sm font-semibold text-secondary bg-white rounded-2xl"
                            type="button" data-bs-toggle="collapse" data-bs-target="#collapseSpek" aria-expanded="false">
                        <i class="bi bi-clipboard-check text-primary me-2"></i> Spesifikasi &amp; Fasilitas Lapangan
                    </button>
                </h2>
                <div id="collapseSpek" class="accordion-collapse collapse" data-bs-parent="#accordionSpek">
                    <div class="accordion-body text-sm text-gray-600 space-y-4">
                        <div>
                            <p class="font-semibold text-secondary mb-2">Spesifikasi Lapangan</p>
                            <ul class="space-y-1">
                                <li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-primary text-xs"></i> Lantai 6mm Vinyl Standar Turnamen</li>
                                <li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-primary text-xs"></i> Penerangan 800 Lux Anti-Glare LED</li>
                            </ul>
                            <div class="mt-3 bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-700 flex gap-2">
                                <i class="bi bi-exclamation-triangle-fill shrink-0 mt-0.5"></i>
                                Wajib menggunakan <strong>sepatu futsal rubber sole / sol karet datar</strong> (tanpa pul bola).
                            </div>
                        </div>
                        <div>
                            <p class="font-semibold text-secondary mb-2">Fasilitas Penunjang</p>
                            <div class="grid grid-cols-2 gap-2">
                                <?php
                                $fasilitas = ['Locker Room Pribadi', 'Hot & Cold Shower', 'Kantin & Minuman Dingin', 'Mushola Ber-AC'];
                                foreach ($fasilitas as $f):
                                ?>
                                <div class="flex items-center gap-2 text-xs">
                                    <i class="bi bi-check-circle-fill text-primary"></i> <?= $f ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form pilih jadwal -->
        <form action="checkout.php" method="get">

            <!-- Pilih tanggal -->
            <div class="mb-5">
                <p class="text-sm font-semibold text-secondary mb-3">Pilih Tanggal Main</p>
                <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
                    <?php
                    $tanggal = [
                        ['2026-10-21', 'Rab', '21'],
                        ['2026-10-22', 'Kam', '22'],
                        ['2026-10-23', 'Jum', '23'],
                        ['2026-10-24', 'Sab', '24'],
                        ['2026-10-25', 'Min', '25'],
                    ];
                    foreach ($tanggal as [$val, $hari, $tgl]):
                        $isActive = $val === '2026-10-22';
                    ?>
                    <label class="shrink-0 cursor-pointer">
                        <input type="radio" name="tanggal" value="<?= $val ?>" <?= $isActive ? 'checked' : '' ?> class="hidden peer">
                        <div class="w-14 py-2.5 rounded-2xl border text-center transition-all
                            peer-checked:bg-primary peer-checked:border-primary peer-checked:text-white
                            border-gray-200 text-gray-500 hover:border-primary">
                            <p class="text-[10px] font-medium"><?= $hari ?></p>
                            <p class="text-base font-bold"><?= $tgl ?></p>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Pilih slot jam -->
            <div class="mb-5">
                <p class="text-sm font-semibold text-secondary mb-1">Pilih Slot Jam</p>
                <p class="text-xs text-gray-400 mb-3">Klik untuk memilih, pilih beberapa slot sekaligus untuk durasi lebih lama</p>
                <div class="space-y-2">
                    <?php
                    $slots = [
                        ['16', '16.00 – 17.00 WIB', 'Rp 150.000', false, false],
                        ['17', '17.00 – 18.00 WIB', 'Rp 150.000', false, false],
                        ['18', '18.00 – 19.00 WIB', 'Rp 180.000', true, false],
                        ['19', '19.00 – 20.00 WIB', 'Rp 180.000', false, true],
                        ['20', '20.00 – 21.00 WIB', 'Rp 180.000', false, true],
                        ['21', '21.00 – 22.00 WIB', 'Rp 180.000', false, false],
                    ];
                    foreach ($slots as [$val, $jam, $harga, $booked, $checked]):
                    ?>
                    <label class="block <?= $booked ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer' ?>">
                        <input type="checkbox" name="slot[]" value="<?= $val ?>"
                               <?= $checked ? 'checked' : '' ?>
                               <?= $booked ? 'disabled' : '' ?>
                               class="hidden peer">
                        <div class="flex items-center justify-between px-4 py-3 rounded-2xl border transition-all
                            <?= $booked ? 'bg-gray-50 border-gray-200' : 'border-gray-200 hover:border-primary peer-checked:border-primary peer-checked:bg-primary-light' ?>">
                            <div>
                                <p class="text-sm font-semibold <?= $booked ? 'text-gray-400' : 'text-secondary peer-checked:text-primary' ?>">
                                    <?= $jam ?>
                                    <?php if ($val === '19' || $val === '20'): ?>
                                    <span class="ml-1 text-[10px] bg-amber-100 text-amber-600 font-bold px-1.5 py-0.5 rounded-full">Prime Time</span>
                                    <?php endif; ?>
                                </p>
                                <p class="text-xs <?= $booked ? 'text-gray-400' : 'text-gray-500' ?>">
                                    <?= $booked ? 'Sudah dipesan' : $harga . ' / jam' ?>
                                </p>
                            </div>
                            <?php if ($booked): ?>
                            <span class="text-xs text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full">Penuh</span>
                            <?php elseif ($checked): ?>
                            <i class="bi bi-check-circle-fill text-primary text-lg"></i>
                            <?php else: ?>
                            <i class="bi bi-circle text-gray-300 text-lg"></i>
                            <?php endif; ?>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Ringkasan + tombol — sticky bottom -->
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-40">
                <div class="max-w-lg mx-auto px-4 py-4 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs text-gray-400">Durasi: 2 jam (19.00 – 21.00)</p>
                        <p class="text-lg font-bold text-secondary">Rp 360.000</p>
                    </div>
                    <button type="submit"
                            class="bg-primary hover:bg-primary-dark text-white font-semibold px-6 py-3 rounded-xl text-sm transition-colors shrink-0">
                        Lanjut Pemesanan
                    </button>
                </div>
            </div>

        </form>

    </main>

</div>

<?php include '../../components/footer.php'; ?>
