<?php
/**
 * Notifikasi pembayaran dari DOKU (HTTP Notification).
 * Diatur lewat 'notify_url' di doku.local.php, atau di DOKU Back Office.
 *
 * - Wajib lolos verifikasi tanda tangan; selain itu ditolak.
 * - Idempotent: DOKU bisa mengirim notifikasi yang sama lebih dari sekali.
 * - Balas HTTP 2xx supaya DOKU menganggap notifikasi sudah diterima.
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../core/response.php';
require_once __DIR__ . '/../../core/doku.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Metode tidak diizinkan.', [], 405);
}

$raw = file_get_contents('php://input') ?: '';
if (!dokuVerifikasiNotifikasi($raw)) {
    error_log('Notifikasi DOKU ditolak: tanda tangan tidak valid.');
    respond(false, 'Signature tidak valid.', [], 401);
}

$d       = json_decode($raw, true) ?: [];
$invoice = is_string($d['order']['invoice_number'] ?? null) ? $d['order']['invoice_number'] : '';
$status  = strtoupper((string) ($d['transaction']['status'] ?? ''));
if ($invoice === '') {
    respond(false, 'Data tidak lengkap.', [], 400);
}

$pdo = db();
$st  = $pdo->prepare('SELECT id, status, total, rincian FROM booking WHERE kode = ?');
$st->execute([$invoice]);
$b = $st->fetch();

if (!$b) {
    error_log("Notifikasi DOKU untuk invoice tidak dikenal: $invoice");
    respond(true, 'Invoice tidak dikenal.');          // 2xx supaya tidak dikirim ulang terus
}

if ($status === 'SUCCESS') {
    $rincian = json_decode((string) $b['rincian'], true) ?: [];
    $harus   = (int) ($rincian['bayar_sekarang'] ?? $b['total']);

    if (!jumlahCocok($d['order']['amount'] ?? null, $harus)) {
        error_log("Notifikasi DOKU $invoice: nominal tidak cocok (diharapkan $harus).");
        respond(true, 'Nominal tidak cocok.');
    }

    if (!tandaiDibayar($pdo, (int) $b['id'])) {
        $s = $pdo->prepare('SELECT status FROM booking WHERE id = ?');
        $s->execute([(int) $b['id']]);
        $sekarang = (string) $s->fetchColumn();
        if ($sekarang !== 'dibayar') {
            // Uang masuk tetapi pesanan sudah kedaluwarsa/dibatalkan: perlu ditangani manual (refund)
            error_log("PERLU DITANGANI: DOKU SUCCESS untuk $invoice, status pesanan '$sekarang'.");
        }
    }
}

respond(true, 'OK');