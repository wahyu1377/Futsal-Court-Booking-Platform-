<?php
/**
 * Aturan harga slot Arena Sport.
 *
 * File ini adalah SATU-SATUNYA sumber kebenaran aturan harga slot.
 * Dipakai oleh halaman detail_jadwal.php (menampilkan harga) dan
 * checkout.php (menagih harga), supaya harga yang tampil dan yang
 * ditagih tidak pernah berbeda.
 *
 * Semua nominal diturunkan dari kolom `lapangan.harga_per_jam` di
 * database, bukan dari angka yang ditulis manual di halaman.
 */

// Jam berapa slot Prime Time dimulai dan berakhir (inklusif)
const PRIME_TIME_START = 15;
const PRIME_TIME_END   = 21;

// Pengali harga untuk slot Prime Time (1.2 = +20%)
const HARGA_PRIME_MULTIPLIER = 1.2;

/**
 * Ambil jam (0-23) dari teks slot, contoh: "08.00 - 09.00 WIB" -> 8.
 */
function jamDariSlot(string $slot): int
{
    if (preg_match('/^(\d{2})\.(\d{2})/', $slot, $match)) {
        return (int) $match[1];
    }
    return 0;
}

/**
 * True bila slot tersebut berada pada jam Prime Time.
 */
function isPrimeTime(int $jam): bool
{
    return $jam >= PRIME_TIME_START && $jam <= PRIME_TIME_END;
}

/**
 * Harga satu slot berdasarkan harga per jam lapangan di database.
 * Slot Prime Time memakai harga per jam dikali HARGA_PRIME_MULTIPLIER.
 */
function hargaSlot(int $hargaPerJam, int $jam): int
{
    if ($hargaPerJam <= 0) {
        return 0;
    }
    return isPrimeTime($jam)
        ? (int) round($hargaPerJam * HARGA_PRIME_MULTIPLIER)
        : $hargaPerJam;
}

/**
 * Harga satu sesi: harga per jam (termasuk Prime Time) dikali durasi sesi dalam jam.
 */
function hargaSesi(int $hargaPerJam, int $jam, int $durasiMenit): int
{
    return (int) round(hargaSlot($hargaPerJam, $jam) * $durasiMenit / 60);
}

// Biaya tambahan & layanan: dipakai checkout.php (tampilan) dan booking.php (tagihan)
const HARGA_ROMPI   = 30000;
const HARGA_WASIT   = 100000;
const BIAYA_LAYANAN = 2000;
const DP_PERSEN     = 50;      // untuk metode "Bayar di Tempat"

const METODE_BAYAR = [
    'online' => 'Bayar Online (DOKU)',
    'onsite' => 'DP, sisa di kasir',
];

function opsiTambahanTersedia(): array
{
    return [
        'rompi' => ['label' => 'Sewa Rompi Latihan',     'harga' => HARGA_ROMPI],
        'wasit' => ['label' => 'Jasa Wasit Pertandingan', 'harga' => HARGA_WASIT],
        'bola'  => ['label' => 'Bola Futsal Tambahan',    'harga' => 0],
    ];
}