<?php
/**
 * Pemeriksaan skema database.
 *
 * Dipakai untuk fitur yang butuh kolom/tabel tambahan (mis. owners.avatar dan
 * owner_settings dari migrasi 004_owner_akun.sql). Kalau migrasinya belum
 * dijalankan, halaman & API tetap bisa dipakai - fiturnya hanya dinonaktifkan
 * dengan pesan yang jelas.
 *
 * Hasil pemeriksaan di-cache per request supaya tidak query berulang.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Nama schema tempat tabel aplikasi berada.
 *
 * PostgreSQL (Supabase) memakai schema 'public', sedangkan MySQL/MariaDB
 * memakai nama database itu sendiri sebagai schema.
 */
function skemaNama(): string
{
    static $skem = null;
    if ($skem === null) {
        $cfg = db_config();
        $skem = $cfg['driver'] === 'mysql' ? (string) $cfg['name'] : 'public';
    }
    return $skem;
}

/** True bila kolom ada di tabel tersebut. */
function kolomAda(PDO $pdo, string $tabel, string $kolom): bool
{
    static $cache = [];
    $kunci = "$tabel.$kolom";
    if (!isset($cache[$kunci])) {
        $st = $pdo->prepare(
            'SELECT 1 FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?'
        );
        $st->execute([skemaNama(), $tabel, $kolom]);
        $cache[$kunci] = (bool) $st->fetchColumn();
    }
    return $cache[$kunci];
}

/** True bila tabel ada di schema aktif. */
function tabelAda(PDO $pdo, string $tabel): bool
{
    static $cache = [];
    if (!isset($cache[$tabel])) {
        $st = $pdo->prepare(
            'SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ?'
        );
        $st->execute([skemaNama(), $tabel]);
        $cache[$tabel] = (bool) $st->fetchColumn();
    }
    return $cache[$tabel];
}
