<?php
/**
 * Definisi slot jadwal lapangan Arena Sport.
 *
 * Sumber tunggal untuk dua hal sekaligus:
 *  - pages/user/detail_jadwal.php -> menampilkan daftar slot beserta statusnya
 *  - pages/user/checkout.php      -> memvalidasi slot yang dikirim user lewat URL
 *
 * Tujuannya: slot yang tampil terkunci di halaman jadwal otomatis juga
 * ditolak oleh server, sehingga status "sudah dipesan" tidak bisa dilewati
 * hanya dengan mengedit address bar.
 *
 * CATATAN: status slot di bawah masih data mockup. Saat Module 7 berjalan,
 * status ini diambil dari tabel `jadwal_slot` di database; fungsi-fungsi di
 * file ini tinggal menjadi adapter, tidak ada lagi daftar slot manual.
 */

// Jam operasional: 08.00 - 22.00 (slot terakhir dimulai pukul 21.00)
const SLOT_JAM_MULAI = 8;
const SLOT_JAM_AKHIR = 21;

// Slot yang sudah terpakai (data mockup) -> tampil terkunci dan ditolak server
const SLOT_TERBLOKIR = [
    '11.00 - 12.00 WIB',
    '13.00 - 14.00 WIB',
    '17.00 - 18.00 WIB',
];

/**
 * Daftar seluruh slot yang bisa dipilih, lengkap dengan statusnya.
 */
function daftarSlot(): array
{
    $slots = [];

    for ($jam = SLOT_JAM_MULAI; $jam <= SLOT_JAM_AKHIR; $jam++) {
        $jamTeks  = sprintf('%02d.00', $jam);
        $jamTeks2 = sprintf('%02d.00', $jam + 1);
        $label    = $jamTeks . ' - ' . $jamTeks2 . ' WIB';

        $slots[] = [
            'jam'    => $label,
            'mulai'  => $jam,
            'status' => in_array($label, SLOT_TERBLOKIR, true) ? 'booked' : 'available',
        ];
    }

    return $slots;
}

/**
 * Validasi slot yang dikirim user (lewat query string).
 *
 * Return daftar slot valid yang sudah dinormalisasi dan terurut berdasarkan
 * jam mulai, atau null bila ada yang tidak valid.
 *
 * Aturan:
 *  1. Slot harus ada di daftar slot operasional.
 *  2. Status slot harus "available" (slot terkunci tidak boleh dipilih).
 *  3. Slot tidak boleh dikirim dua kali.
 */
function validasiSlot(array $slotDiminta): ?array
{
    $daftar = daftarSlot();
    $hasil  = [];

    foreach ($slotDiminta as $slot) {
        // Nilai harus berupa teks biasa (tidak boleh array/objek dari query string)
        if (!is_scalar($slot)) {
            return null;
        }

        $slot = trim((string) $slot);

        // Cari slot yang cocok persis di daftar operasional
        $cocok = null;
        foreach ($daftar as $s) {
            if ($s['jam'] === $slot) {
                $cocok = $s;
                break;
            }
        }

        // Slot di luar jam operasional, atau slot yang sudah dikunci
        if ($cocok === null || $cocok['status'] !== 'available') {
            return null;
        }

        // Slot yang sama dikirim dua kali
        if (isset($hasil[$cocok['jam']])) {
            return null;
        }

        $hasil[$cocok['jam']] = $cocok;
    }

    // Tidak ada slot yang dipilih
    if (empty($hasil)) {
        return null;
    }

    // Urutkan berdasarkan jam mulai supaya jamMulai & jamSelesai selalu berurutan
    $hasil = array_values($hasil);
    usort($hasil, fn($a, $b) => $a['mulai'] <=> $b['mulai']);

    return $hasil;
}
