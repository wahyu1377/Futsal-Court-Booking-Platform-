<?php
/**
 * Pengiriman email sederhana, tanpa library eksternal (tanpa Composer).
 *
 * Dua mode, dipilih lewat MAIL_MODE di .env:
 *   - 'log'  : email tidak benar-benar dikirim. Isi email ditulis ke
 *              backend/logs/mail.log, dan tautannya dikembalikan ke pemanggil
 *              supaya fitur reset password tetap bisa didemokan di lokal.
 *   - 'smtp' : email benar-benar dikirim lewat server SMTP (contoh: Gmail
 *              smtp.gmail.com port 587 + App Password).
 *
 * Batas-batas yang sudah Rendah & jelas:
 *   - Tidak ada library pihak ketiga, murni cURL-less socket PHP.
 *   - Password SMTP hanya dibaca dari .env (tidak pernah disimpan di kode).
 */
require_once __DIR__ . '/../config/env.php';

const MAIL_LOG_DIR = __DIR__ . '/../logs';

/**
 * Konfigurasi mailer, dibaca dari .env / environment variable.
 */
function mailConfig(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = [
            'mode'      => strtolower((string) (env('MAIL_MODE') ?: 'log')),
            'host'      => (string) (env('MAIL_HOST') ?: 'smtp.gmail.com'),
            'port'      => (int) (env('MAIL_PORT') ?: 587),
            'user'      => (string) (env('MAIL_USER') ?: ''),
            'pass'      => (string) (env('MAIL_PASS') ?: ''),
            'from'      => (string) (env('MAIL_FROM') ?: 'no-reply@arenasport.id'),
            'from_name' => (string) (env('MAIL_FROM_NAME') ?: 'Arena Sport'),
        ];
    }
    return $cfg;
}

/**
 * Kirim email. Mengembalikan true kalau email dianggap berhasil dikirim.
 *
 * Mode 'log' selalu mengembalikan true: email tidak benar-benar dikirim,
 * isinya ditulis ke backend/logs/mail.log supaya fitur tetap bisa dicoba
 * saat masih developing.
 */
function kirimEmail(string $ke, string $subjek, string $isiHtml): bool
{
    $cfg = mailConfig();

    if ($cfg['mode'] === 'smtp') {
        return kirimViaSmtp($cfg, $ke, $subjek, $isiHtml);
    }

    // Mode log: simpan isi email ke berkas log, tidak mengirim apa pun.
    $berkas = MAIL_LOG_DIR . '/mail.log';
    if (!is_dir(MAIL_LOG_DIR)) {
        @mkdir(MAIL_LOG_DIR, 0777, true);
    }
    $isi = sprintf(
        "==== %s ====\nKe: %s\nSubjek: %s\n\n%s\n\n",
        date('Y-m-d H:i:s'),
        $ke,
        $subjek,
        strip_tags(str_replace(['<br>', '<br/>', '</p>'], "\n", $isiHtml))
    );
    @file_put_contents($berkas, $isi, FILE_APPEND);

    return true;
}

/**
 * Kirim email lewat SMTP mentah (tanpa library).
 * Mengembalikan true bila server menerima pesan.
 */
function kirimViaSmtp(array $cfg, string $ke, string $subjek, string $isiHtml): bool
{
    if ($cfg['user'] === '' || $cfg['pass'] === '') {
        error_log('Mailer: MAIL_USER/MAIL_PASS belum diisi, email tidak dikirim.');
        return false;
    }

    $target = ($cfg['port'] === 465 ? 'ssl://' : '') . $cfg['host'] . ':' . $cfg['port'];
    $errno  = 0;
    $errstr = '';
    $koneksi = @stream_socket_client($target, $errno, $errstr, 20);
    if (!$koneksi) {
        error_log("Mailer: gagal konek SMTP ($target): $errstr");
        return false;
    }
    stream_set_timeout($koneksi, 20);

    $baca = static function () use ($koneksi): string {
        $baris = '';
        while (($h = fgets($koneksi, 515)) !== false) {
            $baris .= $h;
            // Respons SMTP multi-baris diakhiri baris dengan 4 digit + spasi
            if (strlen($h) < 4 || $h[3] === ' ') {
                break;
            }
        }
        return $baris;
    };
    $kirim = static function (string $baris) use ($koneksi): void {
        fwrite($koneksi, $baris . "\r\n");
    };

    $baca();                                   // banner greeting

    // EHLO dulu, lalu STARTTLS kalau portnya 587 (bukan 465 yang sudah SSL)
    $kirim('EHLO arena-sport.id');
    $ehlo = $baca();

    if ($cfg['port'] !== 465 && stripos($ehlo, 'STARTTLS') !== false) {
        $kirim('STARTTLS');
        $baca();
        if (!stream_socket_enable_crypto($koneksi, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            error_log('Mailer: gagal mengaktifkan TLS.');
            fclose($koneksi);
            return false;
        }
        $kirim('EHLO arena-sport.id');
        $baca();
    }

    // Autentikasi: coba AUTH LOGIN dulu (umum di Gmail).
    // Kalau server tidak mendukungnya, pakai AUTH PLAIN.
    $kirim('AUTH LOGIN');
    $respons = $baca();

    if (stripos($respons, '334') !== false) {
        // Challenged: kirim nama pengguna lalu password (base64)
        $kirim(base64_encode($cfg['user']));
        $baca();
        $kirim(base64_encode($cfg['pass']));
        $respons = $baca();
    } else {
        // Server menolak AUTH LOGIN -> coba AUTH PLAIN sekali saja
        $kirim('AUTH PLAIN ' . base64_encode("\0" . $cfg['user'] . "\0" . $cfg['pass']));
        $respons = $baca();
    }

    if (stripos($respons, '235') === false) {
        fclose($koneksi);
        error_log('Mailer: autentikasi SMTP gagal: ' . trim($respons));
        return false;
    }

    $kirim('MAIL FROM:<' . $cfg['from'] . '>');
    $baca();
    $kirim('RCPT TO:<' . $ke . '>');
    $baca();
    $kirim('DATA');
    $baca();

    $isi = (string) preg_replace('/\r\n|\r|\n/', "\r\n", $isiHtml);
    $kirim("From: {$cfg['from_name']} <{$cfg['from']}>\r\n"
         . "To: <{$ke}>\r\n"
         . "Subject: {$subjek}\r\n"
         . "MIME-Version: 1.0\r\n"
         . "Content-Type: text/html; charset=UTF-8\r\n"
         . "X-Mailer: Arena Sport\r\n"
         . "\r\n"
         . $isi);
    $kirim('.');

    $respons = $baca();
    $kirim('QUIT');
    fclose($koneksi);

    if (stripos($respons, '250') === false) {
        error_log('Mailer: server menolak email: ' . trim($respons));
        return false;
    }
    return true;
}
