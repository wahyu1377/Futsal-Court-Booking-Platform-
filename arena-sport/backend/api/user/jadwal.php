<?php
/**
 * GET backend/api/user/jadwal.php?id=<lapangan>&tanggal=YYYY-MM-DD
 * Publik (tamu boleh melihat jadwal); yang dikunci login hanya checkout.
 */
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../core/response.php';
require __DIR__ . '/../../core/jadwal.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(false, 'Metode tidak diizinkan.', [], 405);
}

$id      = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$tanggal = is_string($_GET['tanggal'] ?? null) ? $_GET['tanggal'] : '';

if (!$id || $id < 1) {
    respond(false, 'Lapangan tidak valid.', [], 422);
}
if (!tanggalBisaDipesan($tanggal)) {
    respond(false, 'Tanggal tidak valid atau di luar 7 hari ke depan.', [], 422);
}

$pdo      = db();
$lapangan = ambilLapangan($pdo, $id);
if (!$lapangan) {
    respond(false, 'Lapangan tidak ditemukan.', [], 404);
}

respond(true, 'OK', [
    'tanggal' => $tanggal,
    'slots'   => jadwalSlot($pdo, $lapangan, $tanggal),
]);