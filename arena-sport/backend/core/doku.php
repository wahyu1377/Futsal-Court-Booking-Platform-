<?php
/**
 * Integrasi DOKU Checkout (sandbox / produksi), hanya memakai cURL.
 *
 *  - dokuBuatSesi()          membuat halaman pembayaran DOKU untuk satu pesanan
 *  - dokuVerifikasiNotifikasi() memeriksa tanda tangan notifikasi dari DOKU
 *  - sinkronStatusDoku()     menanyakan status ke DOKU (cadangan bila notifikasi tidak sampai)
 *
 * Nomor invoice di DOKU = kode booking (BKG-XXXXXX), jadi satu pesanan = satu invoice.
 */
require_once __DIR__ . '/booking.php';

function dokuConfig(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $file = __DIR__ . '/../config/doku.local.php';
        if (!is_file($file)) {
            throw new RuntimeException('Berkas backend/config/doku.local.php belum dibuat.');
        }
        $cfg = require $file;
        foreach (['client_id', 'secret_key', 'app_url'] as $k) {
            if (empty($cfg[$k]) || !is_string($cfg[$k])) {
                throw new RuntimeException("Konfigurasi DOKU '$k' belum diisi.");
            }
        }
    }
    return $cfg;
}

function dokuUrlDasar(array $c): string
{
    return ($c['env'] ?? 'sandbox') === 'production' ? 'https://api.doku.com' : 'https://api-sandbox.doku.com';
}

function uuidV4(): string
{
    $d    = random_bytes(16);
    $d[6] = chr((ord($d[6]) & 0x0f) | 0x40);
    $d[8] = chr((ord($d[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
}

/**
 * Signature DOKU (Non-SNAP):
 *   HMACSHA256= base64( HMAC-SHA256( komponen, secret_key ) )
 * komponen = Client-Id, Request-Id, Request-Timestamp, Request-Target, dan Digest
 * (Digest = base64 dari SHA-256 body; hanya ada bila request punya body).
 */
function dokuSignature(string $clientId, string $secret, string $requestId, string $timestamp, string $target, ?string $body): string
{
    $komponen = "Client-Id:$clientId\nRequest-Id:$requestId\nRequest-Timestamp:$timestamp\nRequest-Target:$target";
    if ($body !== null) {
        $komponen .= "\nDigest:" . base64_encode(hash('sha256', $body, true));
    }
    return 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $komponen, $secret, true));
}

/** Kirim request ke DOKU. Mengembalikan ['status' => http, 'body' => array, 'raw' => string]. */
function dokuRequest(string $method, string $target, ?array $body = null): array
{
    $c         = dokuConfig();
    $requestId = uuidV4();
    $timestamp = gmdate('Y-m-d\TH:i:s\Z');
    $json      = $body !== null ? json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;

    $ch = curl_init(dokuUrlDasar($c) . $target);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Client-Id: ' . $c['client_id'],
            'Request-Id: ' . $requestId,
            'Request-Timestamp: ' . $timestamp,
            'Signature: ' . dokuSignature($c['client_id'], $c['secret_key'], $requestId, $timestamp, $target, $json),
        ],
    ]);
    if ($json !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);   // string yang sama dengan yang dihitung Digest-nya
    }

    $raw    = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($raw === false) {
        error_log('DOKU cURL error: ' . curl_error($ch));
        throw new RuntimeException('Tidak dapat menghubungi DOKU.');
    }
    return ['status' => $status, 'body' => json_decode($raw, true) ?: [], 'raw' => $raw];
}

/** Data pelanggan untuk DOKU, diambil dari akun yang login. */
function dokuPelanggan(PDO $pdo, int $userId): array
{
    $st = $pdo->prepare('SELECT full_name, email, phone FROM users WHERE id = ?');
    $st->execute([$userId]);
    $u = $st->fetch() ?: [];

    $nama = trim(preg_replace('/[^\p{L}\p{N} .\-]/u', '', (string) ($u['full_name'] ?? 'Pelanggan')));
    $p    = ['id' => 'USR-' . $userId, 'name' => mb_substr($nama !== '' ? $nama : 'Pelanggan', 0, 60)];
    if (!empty($u['email'])) {
        $p['email'] = $u['email'];
    }
    if (!empty($u['phone'])) {
        $p['phone'] = preg_replace('/^0/', '62', $u['phone']);   // 0812... -> 62812...
    }
    return $p;
}

/**
 * Buat halaman pembayaran DOKU untuk pesanan $b (hasil ambilBooking), simpan URL-nya,
 * lalu kembalikan URL itu. Jumlah yang ditagih = bayar_sekarang (penuh, atau DP).
 */
function dokuBuatSesi(PDO $pdo, array $b, int $userId): string
{
    $c      = dokuConfig();
    $bayar  = (int) ($b['rincian']['bayar_sekarang'] ?? $b['total']);
    $judul  = ($b['metode'] === 'onsite' ? 'DP ' : '') . 'Booking ' . $b['lapangan_nama'] . ' ' . $b['tanggal_label'];
    $judul  = mb_substr(trim(preg_replace('/[^\p{L}\p{N} \-]/u', '', $judul)), 0, 60);

    $body = [
        'order' => [
            'amount'         => $bayar,
            'invoice_number' => $b['kode'],
            'currency'       => 'IDR',
            'callback_url'   => rtrim($c['app_url'], '/') . '/frontend/pages/user/tiket.php?kode=' . rawurlencode($b['kode']),
            'auto_redirect'  => true,
            'line_items'     => [['name' => $judul, 'price' => $bayar, 'quantity' => 1]],
        ],
        'payment'  => ['payment_due_date' => HOLD_MENIT],   // menit, sama dengan masa tahan slot
        'customer' => dokuPelanggan($pdo, $userId),
    ];
    if (!empty($c['notify_url'])) {
        $body['additional_info'] = ['override_notification_url' => $c['notify_url']];
    }

    $r   = dokuRequest('POST', '/checkout/v1/payment', $body);
    $url = $r['body']['response']['payment']['url'] ?? null;
    if ($r['status'] !== 200 || !is_string($url) || $url === '') {
        error_log('DOKU checkout gagal (HTTP ' . $r['status'] . '): ' . $r['raw']);
        throw new RuntimeException('DOKU menolak permintaan pembayaran (HTTP ' . $r['status'] . ').');
    }

    $pdo->prepare('UPDATE booking SET payment_url = ? WHERE id = ?')->execute([$url, (int) $b['id']]);
    return $url;
}

/** Status transaksi dari DOKU (Check Status API), atau null bila belum ada / gagal. */
function dokuCekStatus(string $invoice): ?array
{
    $r = dokuRequest('GET', '/orders/v1/status/' . rawurlencode($invoice));
    return $r['status'] === 200 ? $r['body'] : null;
}

/** Nominal dari DOKU harus sama dengan tagihan kita (kosong dianggap cocok). */
function jumlahCocok(mixed $amount, int $diharapkan): bool
{
    if ($amount === null || $amount === '') {
        return true;
    }
    return (int) round((float) $amount) === $diharapkan;
}

/**
 * Verifikasi notifikasi dari DOKU: tanda tangan dihitung dari header + isi body mentah,
 * dengan Request-Target = path URL notifikasi kita.
 */
function dokuVerifikasiNotifikasi(string $rawBody): bool
{
    $c         = dokuConfig();
    $clientId  = $_SERVER['HTTP_CLIENT_ID'] ?? '';
    $requestId = $_SERVER['HTTP_REQUEST_ID'] ?? '';
    $timestamp = $_SERVER['HTTP_REQUEST_TIMESTAMP'] ?? '';
    $signature = $_SERVER['HTTP_SIGNATURE'] ?? '';

    if ($clientId !== $c['client_id'] || $requestId === '' || $timestamp === '' || $signature === '') {
        return false;
    }

    $target = !empty($c['notify_url'])
        ? (string) parse_url($c['notify_url'], PHP_URL_PATH)
        : (string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

    $harapan = dokuSignature($clientId, $c['secret_key'], $requestId, $timestamp, $target, $rawBody);
    return hash_equals($harapan, $signature);
}

/**
 * Tanyakan status ke DOKU; bila sudah SUCCESS tandai pesanan dibayar.
 * Dipakai sebagai cadangan (mis. saat dites di localhost yang tidak bisa dijangkau DOKU).
 */
function sinkronStatusDoku(PDO $pdo, array $b): bool
{
    if (($b['status'] ?? '') !== 'menunggu' || empty($b['payment_url'])) {
        return false;
    }
    try {
        $r = dokuCekStatus($b['kode']);
    } catch (Throwable $e) {
        error_log('Cek status DOKU gagal: ' . $e->getMessage());
        return false;
    }
    if (!$r || strtoupper((string) ($r['transaction']['status'] ?? '')) !== 'SUCCESS') {
        return false;
    }

    $harus = (int) ($b['rincian']['bayar_sekarang'] ?? $b['total']);
    if (!jumlahCocok($r['order']['amount'] ?? null, $harus)) {
        error_log('Nominal DOKU tidak cocok untuk ' . $b['kode']);
        return false;
    }
    return tandaiDibayar($pdo, (int) $b['id']);
}