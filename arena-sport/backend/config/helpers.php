<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Escape output HTML (cegah XSS). Pakai di SEMUA data yang dicetak. */
function e(string|int|float|null $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

/** 150000 -> "Rp 150.000" */
function rupiah(int $n): string
{
    return 'Rp ' . number_format($n, 0, ',', '.');
}

/** 4500000 -> "Rp 4,5 Jt", 85000 -> "Rp 85 Rb" */
function rupiahRingkas(int $n): string
{
    if ($n >= 1_000_000_000) { $v = $n / 1_000_000_000; $s = 'M'; }
    elseif ($n >= 1_000_000) { $v = $n / 1_000_000;     $s = 'Jt'; }
    elseif ($n >= 1_000)     { $v = $n / 1_000;         $s = 'Rb'; }
    else                     { return 'Rp ' . $n; }

    $angka = rtrim(rtrim(number_format($v, 1, ',', '.'), '0'), ',');
    return "Rp {$angka} {$s}";
}

/** Halaman khusus owner: wajib login dengan role 'owner'. */
function require_owner(): void
{
    if (defined('APP_ENV') && APP_ENV === 'local') {
        return; // mode dev: lewati
    }
    if (($_SESSION['role'] ?? '') !== 'owner') {
        header('Location: ../../index.php'); // TODO: arahkan ke halaman login tim
        exit;
    }
}
