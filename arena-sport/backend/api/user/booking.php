<?php
/**
 * POST (JSON) backend/api/user/booking.php
 *   {aksi:'buat', lapangan_id, tanggal, slot:[...], opsi:[...], metode}   -> pesanan + halaman bayar DOKU
 *   {aksi:'bayar_online', kode}   -> URL halaman bayar DOKU (dibuat bila belum ada)
 *   {aksi:'cek', kode}            -> tanyakan status ke DOKU
 *   {aksi:'batal', kode}
 *   {aksi:'bayar', kode}          -> simulasi, hanya bila PEMBAYARAN_SIMULASI = true
 *
 * Id pengguna SELALU dari session, harga SELALU dihitung ulang di server.
 */
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/booking.php';
require_once __DIR__ . '/../../core/doku.php';

$d = input();   // hanya menerima POST

if (!isLoggedIn()) {
    respond(false, 'Silakan masuk terlebih dahulu untuk memesan.', ['need_login' => true], 401);
}
if ($_SESSION['role'] !== 'user') {
    respond(false, 'Hanya akun pengguna yang dapat memesan.', [], 403);
}

$userId = (int) $_SESSION['user_id'];
$pdo    = db();
$aksi   = is_string($d['aksi'] ?? null) ? $d['aksi'] : '';

bersihkanKedaluwarsa($pdo);

/** Pesan error untuk pengguna; detail teknis hanya tampil saat APP_DEBUG. */
function pesanDoku(Throwable $e): string
{
    return 'Halaman pembayaran DOKU belum bisa dibuat.' . (APP_DEBUG ? ' (' . $e->getMessage() . ')' : ' Coba lagi sebentar lagi.');
}

// ============================================================
// BUAT PESANAN
// ============================================================
if ($aksi === 'buat') {
    $lapanganId  = filter_var($d['lapangan_id'] ?? null, FILTER_VALIDATE_INT);
    $tanggal     = is_string($d['tanggal'] ?? null) ? $d['tanggal'] : '';
    $slotDiminta = is_array($d['slot'] ?? null) ? $d['slot'] : [];
    $opsiDiminta = is_array($d['opsi'] ?? null) ? $d['opsi'] : [];
    $metode      = is_string($d['metode'] ?? null) ? $d['metode'] : '';

    $lapangan = $lapanganId ? ambilLapangan($pdo, $lapanganId) : null;
    if (!$lapangan) {
        respond(false, 'Lapangan tidak ditemukan.', [], 404);
    }
    if (!tanggalBisaDipesan($tanggal)) {
        respond(false, 'Tanggal main tidak valid.', [], 422);
    }

    $slotValid = validasiSlot($pdo, $lapangan, $tanggal, $slotDiminta);
    if ($slotValid === null) {
        respond(false, 'Slot yang dipilih sudah dipesan atau tidak tersedia. Silakan pilih ulang di halaman jadwal.', ['slot_penuh' => true], 409);
    }

    $rincian = hitungRincian($slotValid, $opsiDiminta, $metode);
    if ($rincian === null) {
        respond(false, 'Opsi tambahan atau metode pembayaran tidak valid.', [], 422);
    }

    $st = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE user_id = ? AND status = 'menunggu' AND expires_at > now()");
    $st->execute([$userId]);
    if ((int) $st->fetchColumn() >= MAKS_PESANAN_MENUNGGU) {
        respond(false, 'Anda masih punya ' . MAKS_PESANAN_MENUNGGU . ' pesanan yang belum dibayar. Selesaikan atau batalkan dulu di halaman Pesanan.', [], 429);
    }

    $kodeBaru = null;
    for ($coba = 0; $coba < 2 && $kodeBaru === null; $coba++) {
        try {
            $kodeBaru = buatBooking($pdo, $userId, $lapangan, $tanggal, $slotValid, $rincian, $metode)['kode'];
        } catch (PDOException $e) {
            if ($e->getCode() !== '23P01') {
                throw $e;
            }
            bersihkanKedaluwarsa($pdo);          // slot bentrok: mungkin hanya hold yang baru kedaluwarsa
        }
    }
    if ($kodeBaru === null) {
        respond(false, 'Slot baru saja dipesan orang lain. Silakan pilih jam lain.', ['slot_penuh' => true], 409);
    }

    // Slot sudah ditahan. Sekarang minta halaman bayar ke DOKU dan arahkan pengguna ke sana.
    $b = ambilBooking($pdo, $kodeBaru, $userId);
    try {
        $url = dokuBuatSesi($pdo, $b, $userId);
    } catch (Throwable $e) {
        error_log('Buat sesi DOKU gagal: ' . $e->getMessage());
        // Pesanan tetap ada; pengguna bisa mencoba lagi dari halaman pembayaran
        respond(true, 'Pesanan dibuat, tetapi halaman pembayaran belum siap.', ['kode' => $kodeBaru, 'redirect' => 'bayar.php?kode=' . $kodeBaru]);
    }
    respond(true, 'Pesanan dibuat. Selesaikan pembayaran dalam ' . HOLD_MENIT . ' menit.', ['kode' => $kodeBaru, 'redirect' => $url]);
}

// ============================================================
// AKSI LAIN: semuanya butuh kode milik pengguna ini
// ============================================================
$kode = is_string($d['kode'] ?? null) ? strtoupper(trim($d['kode'])) : '';
$b    = $kode !== '' ? ambilBooking($pdo, $kode, $userId) : null;
if (!$b) {
    respond(false, 'Pesanan tidak ditemukan.', [], 404);
}

if ($aksi === 'bayar_online') {
    if ($b['status_tampil'] !== 'menunggu') {
        respond(false, 'Pesanan ini sudah tidak menunggu pembayaran.', ['redirect' => 'tiket.php?kode=' . $kode], 409);
    }
    $url = $b['payment_url'] ?: null;
    if (!$url) {
        try {
            $url = dokuBuatSesi($pdo, $b, $userId);
        } catch (Throwable $e) {
            error_log('Buat sesi DOKU gagal: ' . $e->getMessage());
            respond(false, pesanDoku($e), [], 502);
        }
    }
    respond(true, 'OK', ['redirect' => $url]);
}

if ($aksi === 'cek') {
    if (sinkronStatusDoku($pdo, $b)) {
        respond(true, 'Pembayaran diterima.', ['dibayar' => true, 'redirect' => 'tiket.php?kode=' . $kode]);
    }
    respond(true, 'Belum ada pembayaran yang tercatat. Jika sudah membayar, tunggu sebentar lalu coba lagi.', ['dibayar' => false]);
}

if ($aksi === 'batal') {
    // Jangan batalkan pesanan yang ternyata sudah dibayar (notifikasi bisa terlambat)
    if (sinkronStatusDoku($pdo, $b)) {
        respond(false, 'Pembayaran sudah kami terima, pesanan tidak bisa dibatalkan.', ['redirect' => 'tiket.php?kode=' . $kode], 409);
    }
    if (!batalkanBooking($pdo, (int) $b['id'], $userId)) {
        respond(false, 'Pesanan ini tidak bisa dibatalkan lagi.', [], 409);
    }
    respond(true, 'Pesanan dibatalkan.', ['redirect' => 'tiket.php']);
}

if ($aksi === 'bayar') {
    if (!PEMBAYARAN_SIMULASI) {
        respond(false, 'Pembayaran dilakukan lewat halaman DOKU.', [], 403);
    }
    if (!tandaiDibayar($pdo, (int) $b['id'])) {
        respond(false, 'Batas waktu pembayaran sudah habis atau pesanan sudah diproses.', [], 409);
    }
    respond(true, 'Pembayaran berhasil.', ['redirect' => 'tiket.php?kode=' . $kode]);
}

respond(false, 'Aksi tidak dikenal.', [], 422);