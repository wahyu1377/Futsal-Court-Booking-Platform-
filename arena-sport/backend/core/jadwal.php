<?php
/**
 * Jadwal slot lapangan Arena Sport (membaca database).
 *
 * Sumber tunggal untuk:
 *  - pages/user/detail_jadwal.php  -> menampilkan slot beserta statusnya
 *  - api/user/jadwal.php           -> slot untuk tanggal yang dipilih (tanpa reload)
 *  - pages/user/checkout.php       -> memvalidasi ulang slot yang dikirim lewat URL
 *
 * Aturan per lapangan diambil dari kolom lapangan.jam_buka, jam_tutup, dan
 * durasi_sesi (menit). Status slot diambil dari tabel booking + booking_slot.
 * Semua waktu memakai zona Asia/Jakarta (WIB).
 */

require_once __DIR__ . '/harga.php';

// Dipakai bila kolom di tabel lapangan kosong atau tidak masuk akal
const JADWAL_BUKA_DEFAULT   = 8 * 60;    // 08.00
const JADWAL_TUTUP_DEFAULT  = 22 * 60;   // 22.00
const JADWAL_DURASI_DEFAULT = 60;        // menit

// Tanggal yang bisa dipesan: hari ini + 6 hari berikutnya
const JADWAL_HARI_KE_DEPAN = 7;

function zonaWIB(): DateTimeZone
{
    return new DateTimeZone('Asia/Jakarta');
}

/** Lapangan aktif berdasarkan id, atau null bila tidak ada. */
function ambilLapangan(PDO $pdo, int $id): ?array
{
    $st = $pdo->prepare('SELECT * FROM lapangan WHERE id = ? AND aktif = TRUE');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

/** "08:30:00" -> 510 (menit sejak tengah malam). */
function menitDariWaktu(?string $waktu, int $default): int
{
    if ($waktu !== null && preg_match('/^(\d{1,2}):(\d{2})/', $waktu, $m)) {
        return ((int) $m[1]) * 60 + (int) $m[2];
    }
    return $default;
}

/** 510 -> "08.30" */
function teksJam(int $menit): string
{
    return sprintf('%02d.%02d', intdiv($menit, 60), $menit % 60);
}

/** Label slot, formatnya sama seperti sebelumnya: "08.00 - 09.00 WIB". */
function labelSlot(int $mulai, int $selesai): string
{
    return teksJam($mulai) . ' - ' . teksJam($selesai) . ' WIB';
}

/** 90 -> "1 Jam 30 Menit", 120 -> "2 Jam", 45 -> "45 Menit" */
function formatDurasi(int $menit): string
{
    $j = intdiv($menit, 60);
    $m = $menit % 60;
    if ($j > 0 && $m > 0) return "$j Jam $m Menit";
    if ($j > 0)           return "$j Jam";
    return "$m Menit";
}

/** Jam buka, jam tutup, dan durasi sesi (semua dalam menit) milik satu lapangan. */
function aturanJadwal(array $lapangan): array
{
    $buka   = menitDariWaktu($lapangan['jam_buka']  ?? null, JADWAL_BUKA_DEFAULT);
    $tutup  = menitDariWaktu($lapangan['jam_tutup'] ?? null, JADWAL_TUTUP_DEFAULT);
    $durasi = (int) ($lapangan['durasi_sesi'] ?? 0);

    if ($tutup <= $buka) {                       // jam tidak masuk akal: pakai bawaan
        $buka  = JADWAL_BUKA_DEFAULT;
        $tutup = JADWAL_TUTUP_DEFAULT;
    }
    if ($durasi <= 0 || $durasi > $tutup - $buka) {
        $durasi = JADWAL_DURASI_DEFAULT;
    }
    return ['buka' => $buka, 'tutup' => $tutup, 'durasi' => $durasi];
}

/** 7 tanggal yang bisa dipilih, dimulai dari hari ini (WIB). */
function daftarTanggal(): array
{
    $hari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];   // indeks 0 = Minggu
    $awal = new DateTimeImmutable('today', zonaWIB());
    $out  = [];

    for ($i = 0; $i < JADWAL_HARI_KE_DEPAN; $i++) {
        $d     = $awal->modify("+$i day");
        $out[] = [
            'val'  => $d->format('Y-m-d'),
            'hari' => $hari[(int) $d->format('w')],
            'tgl'  => $d->format('d'),
        ];
    }
    return $out;
}

/** True bila tanggal sah (Y-m-d) dan berada dalam jendela 7 hari ke depan. */
function tanggalBisaDipesan(string $tanggal): bool
{
    $tz = zonaWIB();
    $d  = DateTimeImmutable::createFromFormat('!Y-m-d', $tanggal, $tz);
    if (!$d || $d->format('Y-m-d') !== $tanggal) {
        return false;
    }
    $hariIni = new DateTimeImmutable('today', $tz);
    $selisih = (int) $hariIni->diff($d)->format('%r%a');
    return $selisih >= 0 && $selisih < JADWAL_HARI_KE_DEPAN;
}

/**
 * Seluruh slot satu lapangan pada satu tanggal, lengkap dengan status:
 *  - available : bisa dipilih
 *  - booked    : sudah dipesan (atau sedang ditahan pesanan yang belum dibayar)
 *  - lewat     : jam mulainya sudah lewat (hanya untuk hari ini)
 */
function jadwalSlot(PDO $pdo, array $lapangan, string $tanggal): array
{
    $aturan = aturanJadwal($lapangan);
    $now    = new DateTimeImmutable('now', zonaWIB());
    $hariIni      = $now->format('Y-m-d');
    $menitSekarang = ((int) $now->format('H')) * 60 + (int) $now->format('i');
    $hargaPerJam  = (int) ($lapangan['harga_per_jam'] ?? 0);

    // Rentang waktu yang sedang terpakai pada tanggal itu
    $st = $pdo->prepare(
        "SELECT bs.jam_mulai, bs.jam_selesai
         FROM booking_slot bs
         JOIN booking b ON b.id = bs.booking_id
         WHERE bs.lapangan_id = ? AND bs.tanggal = ? AND bs.aktif = TRUE
           AND (b.status IN ('dibayar', 'selesai')
                OR (b.status = 'menunggu' AND b.expires_at > now()))"
    );
    $st->execute([(int) $lapangan['id'], $tanggal]);

    $terpakai = [];
    foreach ($st->fetchAll() as $r) {
        $terpakai[] = [menitDariWaktu($r['jam_mulai'], 0), menitDariWaktu($r['jam_selesai'], 0)];
    }

    $slots = [];
    for ($mulai = $aturan['buka']; $mulai + $aturan['durasi'] <= $aturan['tutup']; $mulai += $aturan['durasi']) {
        $akhir  = $mulai + $aturan['durasi'];
        $status = 'available';

        foreach ($terpakai as [$s, $e]) {
            if ($mulai < $e && $akhir > $s) {        // rentangnya bertabrakan
                $status = 'booked';
                break;
            }
        }
        if ($status === 'available' && $tanggal === $hariIni && $mulai <= $menitSekarang) {
            $status = 'lewat';
        }

        $jam     = intdiv($mulai, 60);
        $slots[] = [
            'jam'         => labelSlot($mulai, $akhir),
            'mulai'       => $jam,                    // jam mulai (0-23), untuk aturan harga
            'mulai_menit' => $mulai,
            'menit'       => $aturan['durasi'],
            'harga'       => hargaSesi($hargaPerJam, $jam, $aturan['durasi']),
            'prime'       => isPrimeTime($jam),
            'status'      => $status,
        ];
    }
    return $slots;
}

/**
 * Validasi slot yang dikirim user (lewat query string).
 *
 * Mengembalikan daftar slot valid yang sudah terurut berdasarkan jam mulai,
 * atau null bila ada yang tidak valid.
 *
 * Aturan:
 *  1. Slot harus ada di jadwal lapangan itu pada tanggal tersebut.
 *  2. Statusnya harus "available" (bukan booked atau lewat).
 *  3. Slot tidak boleh dikirim dua kali.
 */
function validasiSlot(PDO $pdo, array $lapangan, string $tanggal, array $slotDiminta): ?array
{
    $daftar = [];
    foreach (jadwalSlot($pdo, $lapangan, $tanggal) as $s) {
        $daftar[$s['jam']] = $s;
    }

    $hasil = [];
    foreach ($slotDiminta as $slot) {
        if (!is_scalar($slot)) {               // tidak boleh array/objek dari query string
            return null;
        }
        $slot  = trim((string) $slot);
        $cocok = $daftar[$slot] ?? null;

        if ($cocok === null || $cocok['status'] !== 'available') {
            return null;
        }
        if (isset($hasil[$slot])) {            // dikirim dua kali
            return null;
        }
        $hasil[$slot] = $cocok;
    }

    if (empty($hasil)) {
        return null;
    }

    $hasil = array_values($hasil);
    usort($hasil, fn($a, $b) => $a['mulai_menit'] <=> $b['mulai_menit']);
    return $hasil;
}