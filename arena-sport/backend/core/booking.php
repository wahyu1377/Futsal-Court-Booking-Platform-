<?php
/**
 * Pesanan (booking) Arena Sport.
 *
 * Alur status:
 *   menunggu --(bayar)--> dibayar --(jam main lewat, dihitung saat dibaca)--> selesai
 *   menunggu --(batal)--> dibatalkan
 *   menunggu --(15 menit habis)--> kedaluwarsa
 *
 * Slot ditahan lewat tabel booking_slot (aktif = TRUE). Constraint EXCLUDE di
 * database menolak dua pesanan aktif yang jamnya bertabrakan, jadi dua orang
 * yang menekan "bayar" bersamaan tidak mungkin sama-sama berhasil.
 */

require_once __DIR__ . '/jadwal.php';    // juga memuat harga.php

const HOLD_MENIT            = 15;   // pesanan yang belum dibayar menahan slot selama ini
const MAKS_PESANAN_MENUNGGU = 3;    // batas pesanan belum dibayar per pengguna (cegah menimbun slot)

// Mode simulasi: bayar.php menampilkan tombol "Simulasikan Pembayaran Berhasil".
// WAJIB diubah ke false begitu payment gateway terpasang.
const PEMBAYARAN_SIMULASI = false;

function rupiahTeks(int $n): string
{
    return 'Rp ' . number_format($n, 0, ',', '.');
}

/** "2026-10-26" -> "Sen, 26 Okt 2026" */
function tanggalIndo(string $ymd): string
{
    $hari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    $bln  = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $d    = DateTimeImmutable::createFromFormat('!Y-m-d', $ymd);
    if (!$d) return $ymd;
    return $hari[(int) $d->format('w')] . ', ' . $d->format('j') . ' ' . $bln[(int) $d->format('n')] . ' ' . $d->format('Y');
}

/** Teks dan warna (tone) untuk tiap status. */
function infoStatus(string $status): array
{
    return match ($status) {
        'menunggu'    => ['teks' => 'Menunggu Pembayaran', 'tone' => 'kuning'],
        'dibayar'     => ['teks' => 'Terkonfirmasi',       'tone' => 'hijau'],
        'selesai'     => ['teks' => 'Selesai',             'tone' => 'abu'],
        'dibatalkan'  => ['teks' => 'Dibatalkan',          'tone' => 'merah'],
        'kedaluwarsa' => ['teks' => 'Kedaluwarsa',         'tone' => 'abu'],
        default       => ['teks' => ucfirst($status),      'tone' => 'abu'],
    };
}

/** BKG-XXXXXX (tanpa huruf dan angka yang mudah tertukar: I, O, 0, 1). */
function kodeBooking(): string
{
    $abjad = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $kode  = '';
    for ($i = 0; $i < 6; $i++) {
        $kode .= $abjad[random_int(0, strlen($abjad) - 1)];
    }
    return 'BKG-' . $kode;
}

/** 570 -> "09:30:00" (untuk kolom time di database). */
function waktuDb(int $menit): string
{
    return sprintf('%02d:%02d:00', intdiv($menit, 60), $menit % 60);
}

/** Pesanan menunggu yang batas waktunya habis -> kedaluwarsa, slotnya dilepas. */
function bersihkanKedaluwarsa(PDO $pdo): void
{
    $pdo->exec(
        "WITH lewat AS (
            UPDATE booking SET status = 'kedaluwarsa'
            WHERE status = 'menunggu' AND expires_at <= now()
            RETURNING id
         )
         UPDATE booking_slot SET aktif = FALSE WHERE booking_id IN (SELECT id FROM lewat)"
    );
}

/**
 * Hitung semua biaya di server. Total dari browser tidak pernah dipercaya.
 * Null bila ada opsi atau metode pembayaran yang tidak dikenal.
 */
function hitungRincian(array $slotValid, array $opsiIds, string $metode): ?array
{
    if (!isset(METODE_BAYAR[$metode])) {
        return null;
    }

    $katalog = opsiTambahanTersedia();
    $opsi    = [];
    foreach ($opsiIds as $id) {
        if (!is_string($id) || !isset($katalog[$id])) {
            return null;
        }
        $opsi[$id] = ['id' => $id, 'label' => $katalog[$id]['label'], 'harga' => $katalog[$id]['harga']];
    }

    $sewa     = (int) array_sum(array_column($slotValid, 'harga'));
    $menit    = (int) array_sum(array_column($slotValid, 'menit'));
    $tambahan = (int) array_sum(array_column($opsi, 'harga'));
    $total    = $sewa + $tambahan + BIAYA_LAYANAN;
    $bayar    = $metode === 'onsite' ? (int) round($total * DP_PERSEN / 100) : $total;

    return [
        'sewa'           => $sewa,
        'durasi_menit'   => $menit,
        'opsi'           => array_values($opsi),
        'layanan'        => BIAYA_LAYANAN,
        'total'          => $total,
        'bayar_sekarang' => $bayar,
        'sisa_di_tempat' => $total - $bayar,
    ];
}

/**
 * Simpan pesanan + tahan slotnya dalam satu transaksi.
 * Melempar PDOException dengan kode 23P01 bila slot bentrok (ditangani pemanggil).
 */
function buatBooking(PDO $pdo, int $userId, array $lapangan, string $tanggal, array $slotValid, array $rincian, string $metode): array
{
    for ($coba = 0; $coba < 3; $coba++) {
        $kode = kodeBooking();
        try {
            $pdo->beginTransaction();

            $st = $pdo->prepare(
                "INSERT INTO booking (kode, user_id, lapangan_id, tanggal, status, total, metode, rincian, expires_at)
                 VALUES (?, ?, ?, ?, 'menunggu', ?, ?, ?::jsonb, now() + INTERVAL '" . HOLD_MENIT . " minutes')
                 RETURNING id"
            );
            $st->execute([$kode, $userId, (int) $lapangan['id'], $tanggal, $rincian['total'], $metode, json_encode($rincian)]);
            $id = (int) $st->fetchColumn();

            $ins = $pdo->prepare(
                'INSERT INTO booking_slot (booking_id, lapangan_id, tanggal, jam_mulai, jam_selesai) VALUES (?, ?, ?, ?, ?)'
            );
            foreach ($slotValid as $s) {
                $ins->execute([$id, (int) $lapangan['id'], $tanggal,
                               waktuDb($s['mulai_menit']), waktuDb($s['mulai_menit'] + $s['menit'])]);
            }

            $pdo->commit();
            return ['id' => $id, 'kode' => $kode];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            // 23505 = kode kembar (sangat jarang): buat kode baru lalu coba lagi
            if ($e->getCode() === '23505' && stripos($e->getMessage(), 'kode') !== false) {
                continue;
            }
            throw $e;
        }
    }
    throw new RuntimeException('Gagal membuat kode booking.');
}

// ============================================================
// MEMBACA PESANAN
// ============================================================
const SQL_BOOKING = "
    SELECT b.id, b.kode, b.status, b.total, b.metode, b.rincian, b.tanggal, b.created_at, b.payment_url,
           l.id AS lapangan_id, l.nama AS lapangan_nama, l.jenis, l.kecamatan, l.kota, l.foto,
           MIN(bs.jam_mulai)   AS jam_mulai,
           MAX(bs.jam_selesai) AS jam_selesai,
           CASE WHEN b.status = 'menunggu' AND b.expires_at IS NOT NULL
                THEN GREATEST(0, EXTRACT(EPOCH FROM (b.expires_at - now()))::int) END AS sisa_detik,
           CASE WHEN b.status = 'dibayar'
                 AND (b.tanggal + MAX(bs.jam_selesai)) < (now() AT TIME ZONE 'Asia/Jakarta')
                THEN 'selesai' ELSE b.status END AS status_tampil
    FROM booking b
    JOIN lapangan l ON l.id = b.lapangan_id
    LEFT JOIN booking_slot bs ON bs.booking_id = b.id";

function olahBooking(array $r): array
{
    $r['rincian']       = json_decode((string) ($r['rincian'] ?? ''), true) ?: [];
    $r['tanggal_label'] = tanggalIndo((string) $r['tanggal']);
    $r['status_info']   = infoStatus((string) $r['status_tampil']);
    $r['sisa_detik']    = $r['sisa_detik'] !== null ? (int) $r['sisa_detik'] : null;
    $r['jam_label']     = $r['jam_mulai'] !== null
        ? teksJam(menitDariWaktu($r['jam_mulai'], 0)) . ' - ' . teksJam(menitDariWaktu($r['jam_selesai'], 0)) . ' WIB'
        : '-';
    return $r;
}

/** Satu pesanan milik satu pengguna berdasarkan kode, atau null. */
function ambilBooking(PDO $pdo, string $kode, int $userId): ?array
{
    $st = $pdo->prepare(SQL_BOOKING . ' WHERE b.kode = ? AND b.user_id = ? GROUP BY b.id, l.id');
    $st->execute([$kode, $userId]);
    $r = $st->fetch();
    return $r ? olahBooking($r) : null;
}

/** Semua pesanan pengguna, terbaru dulu. */
function daftarBooking(PDO $pdo, int $userId): array
{
    $st = $pdo->prepare(SQL_BOOKING . ' WHERE b.user_id = ? GROUP BY b.id, l.id ORDER BY b.created_at DESC LIMIT 100');
    $st->execute([$userId]);
    return array_map('olahBooking', $st->fetchAll());
}

/** Label tiap slot milik satu pesanan, berurutan: ["11.00 - 12.00 WIB", ...] */
function slotBooking(PDO $pdo, int $bookingId): array
{
    $st = $pdo->prepare('SELECT jam_mulai, jam_selesai FROM booking_slot WHERE booking_id = ? ORDER BY jam_mulai');
    $st->execute([$bookingId]);
    return array_map(
        fn($r) => labelSlot(menitDariWaktu($r['jam_mulai'], 0), menitDariWaktu($r['jam_selesai'], 0)),
        $st->fetchAll()
    );
}

/** Jumlah pesanan yang masih berjalan (menunggu bayar atau terkonfirmasi dan belum lewat). */
function hitungBookingAktif(PDO $pdo, int $userId): int
{
    $st = $pdo->prepare(
        "SELECT COUNT(*) FROM (
            SELECT b.id
            FROM booking b JOIN booking_slot bs ON bs.booking_id = b.id
            WHERE b.user_id = ?
              AND ((b.status = 'menunggu' AND b.expires_at > now()) OR b.status = 'dibayar')
            GROUP BY b.id
            HAVING b.status = 'menunggu'
                OR (b.tanggal + MAX(bs.jam_selesai)) >= (now() AT TIME ZONE 'Asia/Jakarta')
         ) x"
    );
    $st->execute([$userId]);
    return (int) $st->fetchColumn();
}

// ============================================================
// MENGUBAH STATUS
// ============================================================

/**
 * menunggu -> dibayar. Hanya berhasil bila batas waktu belum habis.
 * Fungsi inilah yang nanti dipanggil webhook payment gateway.
 */
function tandaiDibayar(PDO $pdo, int $bookingId): bool
{
    $st = $pdo->prepare(
        "UPDATE booking SET status = 'dibayar', dibayar_at = now(), expires_at = NULL
         WHERE id = ? AND status = 'menunggu' AND expires_at > now()"
    );
    $st->execute([$bookingId]);
    return $st->rowCount() === 1;
}

/** menunggu -> dibatalkan, slot dilepas. Pesanan yang sudah dibayar tidak bisa dibatalkan di sini. */
function batalkanBooking(PDO $pdo, int $bookingId, int $userId): bool
{
    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare(
            "UPDATE booking SET status = 'dibatalkan', dibatalkan_at = now()
             WHERE id = ? AND user_id = ? AND status = 'menunggu'"
        );
        $st->execute([$bookingId, $userId]);
        $ok = $st->rowCount() === 1;

        if ($ok) {
            $pdo->prepare('UPDATE booking_slot SET aktif = FALSE WHERE booking_id = ?')->execute([$bookingId]);
        }
        $pdo->commit();
        return $ok;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}