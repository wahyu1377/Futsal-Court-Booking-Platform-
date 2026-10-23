<?php
declare(strict_types=1);

/**
 * Data dashboard owner (Beranda Mitra).
 *
 * STATUS: masih DATA CONTOH. Skema database (arena_sport.sql) belum saya lihat,
 * jadi query PDO belum ditulis agar tidak menebak nama tabel/kolom.
 * Saat skema sudah ada, ganti isi ambil() dengan query PDO, TAPI pertahankan
 * bentuk array yang dikembalikan supaya halaman tidak perlu diubah.
 * Semua query wajib memakai prepared statement dan difilter $mitraId.
 */
final class DashboardMitra
{
    public static function ambil(int $mitraId): array
    {
        return [
            'mitra' => [
                'nama'   => 'Jonathan',
                'status' => 'Mitra Super Aktif',
                'brand'  => 'Arena Sport',
            ],
            'ringkasan' => [
                'pesanan_hari_ini'   => 12,
                'selisih_kemarin'    => 3,        // + naik, - turun
                'menunggu_bayar'     => 3,
                'pendapatan_bulan'   => 4_500_000,
                'persen_bulan_lalu'  => 18.4,     // + naik, - turun
            ],
            'reservasi_terkini' => [
                'nama'       => 'Bambang Pamungkas',
                'lapangan'   => 'Lap A',
                'jam'        => '19.00 - 20.00 WIB',
                'total'      => 180_000,
                'status'     => 'Lunas',
                'metode'     => 'QRIS',
                'waktu'      => '2 mnt lalu',
                'kode_tiket' => 'BK-20241022-8849',
            ],
            'lapangan' => [
                [
                    'id' => 1, 'nama' => 'Champion Futsal Arena – Lap A',
                    'jenis' => 'Vinyl FIFA', 'tipe' => 'Indoor', 'ikon' => 'bi-dribbble',
                    'zona' => 'Zona Hall Barat', 'standar' => 'Standar Turnamen',
                    'harga_per_jam' => 150_000, 'slot_tersedia' => 4,
                    'foto' => 'lapangan-futsal.jpg',
                ],
                [
                    'id' => 2, 'nama' => 'Champion Sport Hall – Badminton',
                    'jenis' => 'Karpet Pro', 'tipe' => 'Indoor', 'ikon' => 'bi-person-arms-up',
                    'zona' => 'Zona Hall Timur', 'standar' => 'Matras Yonex',
                    'harga_per_jam' => 85_000, 'slot_tersedia' => 2,
                    'foto' => 'lapangan-badminton.jpg',
                ],
            ],
        ];
    }
}
