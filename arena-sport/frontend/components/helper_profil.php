<?php
// Dipakai bersama oleh akun.php dan edit-profil.php
const AVATAR_PATH = __DIR__ . '/../assets/img/avatars/';   // lokasi di disk
const AVATAR_URL  = '../../assets/img/avatars/';           // alamat dari halaman di pages/user/

// Escape output (cegah XSS)
function e(string|int $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function punyaAvatar(?string $file): bool
{
    return $file !== null && $file !== '' && is_file(AVATAR_PATH . basename($file));
}

// Pakai foto kalau ada; kalau tidak, buat avatar inisial (hijau) otomatis
function avatarSrc(string $nama, ?string $file): string
{
    if (punyaAvatar($file)) {
        return AVATAR_URL . rawurlencode(basename($file));
    }
    $huruf = mb_strtoupper(mb_substr(trim($nama), 0, 1)) ?: '?';
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">'
         . '<rect width="100" height="100" fill="#006a4a"/>'
         . '<text x="50" y="50" dy=".35em" text-anchor="middle" font-family="Arial,sans-serif" '
         . 'font-size="46" font-weight="700" fill="#fff">' . htmlspecialchars($huruf, ENT_XML1) . '</text></svg>';
    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
}