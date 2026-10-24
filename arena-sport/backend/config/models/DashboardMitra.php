<?php
declare(strict_types=1);

require_once __DIR__ . '/../database.php';

/**
 * Data dashboard owner (Beranda Mitra).
 *
 * Sumber data: tabel `owners` (akun mitra) dan `lapangan` (venue milik mitra)
 * pada database. Query memakai prepared statement dan selalu difilter $mitraId,
 * jadi satu owner tidak bisa melihat lapangan milik owner lain.
 *
 * CATATAN: bagian `ringkasan` (pesanan hari ini / menunggu bayar / pendapatan
 * bulan) dan `reservasi_terkini` BELUM punya sumber data, karena tabel
 * pemesanan/reservasi belum ada di database. Until it tersedia, nilainya
 * dikembalikan kosong dan halaman menyembunyikan blok terkait.
 * Bentuk array yang dikembalikan sengaja dipertahankan agar halaman
 * tidak perlu diubah struktur datanya.
 */
final class DashboardMitra
{
    /** Folder aset tempat foto lapangan disimpan (relatif dari folder frontend/assets). */
    private const FOLDER_FOTO = 'lapangan/';

    /**
     * Ambil seluruh data dashboard untuk satu mitra.
     *
     * @param int $mitraId ID akun owner (dari $_SESSION['user_id']).
     */
    public static function ambil(int $mitraId): array
    {
        $pdo = db();

        // ===== Identitas mitra =====
        $st = $pdo->prepare('SELECT full_name, email FROM owners WHERE id = ?');
        $st->execute([$mitraId]);
        $akun = $st->fetch();

        if (!$akun) {
            // Akun owner tidak ditemukan (mis. session sudah tidak berlaku).
            return self::kosong();
        }

        // ===== Daftar lapangan milik mitra =====
        $st = $pdo->prepare(
            'SELECT id, nama, jenis, kecamatan, kota, harga_per_jam, foto,
                    fasilitas, keterangan, tersedia_hari_ini,
                    jam_buka, jam_tutup, durasi_sesi
             FROM lapangan
             WHERE owner_id = ? AND aktif = TRUE
             ORDER BY nama'
        );
        $st->execute([$mitraId]);

        $lapangan = [];
        foreach ($st->fetchAll() as $r) {
            $fasilitas = array_filter(array_map('trim', explode(',', (string) $r['fasilitas'])));

            $lapangan[] = [
                'id'            => (int) $r['id'],
                'nama'          => (string) $r['nama'],
                'jenis'         => ucfirst((string) $r['jenis']),
                'tipe'          => self::tipeDari($fasilitas),
                'ikon'          => self::ikonDari((string) $r['jenis']),
                'zona'          => (string) $r['kecamatan'],
                'standar'       => (string) $r['keterangan'],
                'harga_per_jam' => (int) $r['harga_per_jam'],
                'slot_tersedia' => self::hitungSlot($r),
                'foto'          => self::fotoSrc((string) $r['foto']),
            ];
        }

        return [
            'mitra' => [
                'nama'   => (string) $akun['full_name'],
                'status' => 'Mitra Arena Sport',
                'brand'  => 'Arena Sport',
            ],
            'ringkasan' => [
                // Belum ada sumber data (tabel pemesanan belum ada di database).
                'pesanan_hari_ini'  => 0,
                'selisih_kemarin'   => 0,
                'menunggu_bayar'    => 0,
                'pendapatan_bulan'  => 0,
                'persen_bulan_lalu' => 0,
            ],
            'reservasi_terkini' => [],   // tabel reservasi belum ada
            'lapangan'           => $lapangan,
        ];
    }

    /**
     * Bentuk data kosong, dipakai kalau akun mitra tidak ditemukan.
     */
    private static function kosong(): array
    {
        return [
            'mitra' => [
                'nama'   => 'Mitra',
                'status' => 'Mitra Arena Sport',
                'brand'  => 'Arena Sport',
            ],
            'ringkasan' => [
                'pesanan_hari_ini'  => 0,
                'selisih_kemarin'   => 0,
                'menunggu_bayar'    => 0,
                'pendapatan_bulan'  => 0,
                'persen_bulan_lalu' => 0,
            ],
            'reservasi_terkini' => [],
            'lapangan'           => [],
        ];
    }

    /**
     * Tipe venue diturunkan dari fasilitas: kalau ada "Indoor" berarti indoor.
     *
     * @param array $fasilitas Daftar fasilitas (sudah dipisah koma).
     */
    private static function tipeDari(array $fasilitas): string
    {
        foreach ($fasilitas as $f) {
            if (stripos($f, 'indoor') !== false) return 'Indoor';
        }
        return 'Outdoor';
    }

    /**
     * Ikon Bootstrap sesuai jenis olahraga.
     */
    private static function ikonDari(string $jenis): string
    {
        return match (strtolower($jenis)) {
            'badminton' => 'bi-person-arms-up',
            default     => 'bi-dribbble',
        };
    }

    /**
     * Jumlah slot yang masih kosong hari ini, dihitung dari jam buka/tutup
     * dan durasi sesi. Kalau lapangan sudah Penuh, nilainya 0.
     *
     * @param array $r Baris lapangan dari database.
     */
    private static function hitungSlot(array $r): int
    {
        if (!$r['tersedia_hari_ini']) return 0;

        $buka  = strtotime((string) ($r['jam_buka']  ?: '08:00'));
        $tutup = strtotime((string) ($r['jam_tutup'] ?: '22:00'));
        if ($buka === false || $tutup === false || $tutup <= $buka) return 0;

        $durasiMenit = max(30, (int) $r['durasi_sesi']);
        return (int) floor(($tutup - $buka) / 60 / ($durasiMenit / 60));
    }

    /**
     * Ubah nama foto dari database menjadi path relatif dari folder
     * frontend/assets. Foto bisa berupa:
     *   - nama file di folder aset/lapangan   (contoh: champion-futsal.jpg)
     *   - nama file hasil unggahan owner       (contoh: owner/20261022-ab12cd.jpg)
     *   - URL foto dari internet               (dikembalikan apa adanya)
     *
     * Nilai balik dipakai halaman sebagai: <assets>/img/<hasil>.
     */
    private static function fotoSrc(string $foto): string
    {
        $foto = trim($foto);
        if ($foto === '') return '';

        // Foto boleh lebih dari satu (dipisah koma); yang dipakai adalah foto utama.
        $utama = trim(explode(',', $foto)[0]);
        if ($utama === '') return '';

        // URL dari internet dipakai apa adanya (tidak diberi prefix folder).
        if (preg_match('#^https?://#i', $utama)) return $utama;

        // Nama file biasa sudah berada di folder aset/lapangan.
        // Foto unggahan owner disimpan sebagai "owner/namafile".
        return self::FOLDER_FOTO . ltrim($utama, '/');
    }
}
