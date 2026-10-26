<?php
/**
 * API CRUD lapangan untuk owner.
 *
 * Metode:
 *   GET    -> daftar lapangan milik owner yang sedang login (+ daftar pilihan foto & durasi sesi)
 *   POST   -> tambah lapangan baru
 *   PUT    -> ubah lapangan milik owner
 *   DELETE -> hapus lapangan milik owner
 *
 * Aturan bisnis:
 *   - Jenis olahraga sementara hanya "futsal".
 *   - Rating & jumlah ulasan TIDAK diisi owner (diberikan pelanggan nanti),
 *     jadi tidak pernah di-set lewat API ini.
 *   - Minimal satu foto wajib dipilih owner.
 *   - Semua query memakai parameter binding (prepared statement) dan selalu
 *     dibatasi owner_id supaya owner hanya bisa mengelola lapangan sendiri.
 */
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../core/helper.php';
require __DIR__ . '/../../core/auth.php';   // isLoggedIn() + session

// Hanya owner yang boleh mengakses API ini
if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'owner') {
    respond(false, 'Akses ditolak. Hanya owner yang bisa mengelola lapangan.', [], 403);
}

$pdo       = db();
$idOwner   = (int) $_SESSION['user_id'];
$metode    = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$folderFoto = __DIR__ . '/../../../frontend/assets/img/lapangan/';

/**
 * Jenis olahraga yang tersedia untuk Platform (sementara hanya futsal).
 */
const JENIS_OLAHRAGA = ['futsal'];

/**
 * Durasi sewa per sesi (dalam menit) yang bisa dipilih owner.
 */
const DURASI_OPSI = [60, 90, 120];

/**
 * Daftar fasilitas yang bisa dipilih owner (dipisah koma + spasi di database).
 */
const DAFTAR_FASILITAS = [
    'Indoor AC', 'Outdoor', 'Rumput Sintetis', 'Karpet Vinyl BWF', 'Vinyl',
    'Parkir Luas', 'Kantin', 'Musala', 'Peralatan Lengkap', 'CCTV', 'Toilet',
    'Mushola', 'Parkir Motor',
];

/**
 * Daftar file foto yang tersedia di folder aset (dipakai untuk pilihan foto).
 */
function opsiFoto(string $folder): array
{
    if (!is_dir($folder)) return [];

    $files = scandir($folder) ?: [];
    $foto  = [];

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $foto[] = $file;
        }
    }
    sort($foto);
    return $foto;
}

/**
 * Normalisasi daftar foto: menerima array atau string berisi nama file (dipisah koma).
 *
 * Setiap foto boleh berupa:
 *   - nama file di dalam folder aset   (contoh: champion-futsal.jpg)
 *   - nama file hasil unggahan owner  (contoh: owner/20261022-ab12cd.jpg)
 *   - URL foto dari internet          (contoh: https://.../lapangan.jpg)
 */
function normalisasiFoto($foto): array
{
    if (is_string($foto)) {
        $foto = $foto === '' ? [] : explode(',', $foto);
    }
    if (!is_array($foto)) return [];

    $bersih = [];
    foreach ($foto as $item) {
        $item = trim((string) $item);
        if ($item !== '' && !in_array($item, $bersih, true)) {
            $bersih[] = $item;
        }
    }
    return $bersih;
}

/**
 * Cek apakah satu foto valid: URL http/https atau nama file di dalam folder aset.
 */
function fotoValid(string $foto): bool
{
    // URL foto dari internet
    if (preg_match('#^https?://#i', $foto)) {
        return mb_strlen($foto) <= 500;
    }

    // Nama file: boleh punya sub folder (owner/xxx.jpg), tapi tidak boleh keluar dari folder aset
    if ($foto === '' || str_contains($foto, '..') || str_contains($foto, '\\')) {
        return false;
    }
    $ekstensi = strtolower(pathinfo($foto, PATHINFO_EXTENSION));
    return in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp'], true);
}

/**
 * Simpan file foto yang diunggah owner ke folder aset.
 * Mengembalikan nama file relatif (contoh: owner/20261022-ab12cd34.jpg).
 */
function simpanFotoUnggahan(array $file): ?string
{
    $folderAset    = __DIR__ . '/../../../frontend/assets/img/lapangan/';
    $folderPemilik = $folderAset . 'owner/';

    if (!is_dir($folderPemilik)) {
        @mkdir($folderPemilik, 0775, true);
    }

    // Validasi ukuran maksimal 3 MB
    if ((int) $file['size'] > 3 * 1024 * 1024) {
        return null;
    }

    // Validasi isi file benar-benar gambar
    $info = @getimagesize($file['tmp_name']);
    if ($info === false) {
        return null;
    }

    $petaEkstensi = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $ekstensi = $petaEkstensi[$info['mime']] ?? null;
    if ($ekstensi === null) {
        return null;
    }

    // Nama file unik supaya tidak menimpa file lain / aman dari nama berbahaya
    $namaBaru = 'owner/' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ekstensi;

    if (!move_uploaded_file($file['tmp_name'], $folderAset . $namaBaru)) {
        return null;
    }

    return $namaBaru;
}

/**
 * Validasi data lapangan dari request.
 * Mengembalikan array [data siap simpan, array error per field].
 */
function validasiLapangan(array $d): array
{
    $error = [];

    $nama       = trim((string) ($d['nama'] ?? ''));
    $kecamatan  = trim((string) ($d['kecamatan'] ?? ''));
    $kota       = trim((string) ($d['kota'] ?? ''));
    $fasilitas  = trim((string) ($d['fasilitas'] ?? ''));
    $keterangan = trim((string) ($d['keterangan'] ?? ''));
    $jamBuka    = trim((string) ($d['jam_buka'] ?? ''));
    $jamTutup   = trim((string) ($d['jam_tutup'] ?? ''));
    $foto       = normalisasiFoto($d['foto'] ?? []);

    // Nama lapangan
    if ($nama === '') {
        $error['nama'] = 'Nama lapangan wajib diisi.';
    } elseif (mb_strlen($nama) < 3 || mb_strlen($nama) > 120) {
        $error['nama'] = 'Nama lapangan harus 3-120 karakter.';
    }

    // Lokasi
    if ($kecamatan === '') {
        $error['kecamatan'] = 'Kecamatan wajib diisi.';
    } elseif (mb_strlen($kecamatan) > 80) {
        $error['kecamatan'] = 'Kecamatan maksimal 80 karakter.';
    }
    if ($kota === '') {
        $error['kota'] = 'Kota wajib diisi.';
    } elseif (mb_strlen($kota) > 80) {
        $error['kota'] = 'Kota maksimal 80 karakter.';
    }

    // Harga per jam (wajib angka bulat >= 0, sesuai CHECK di database)
    $harga = $d['harga_per_jam'] ?? '';
    if ($harga === '' || !is_numeric($harga)) {
        $error['harga_per_jam'] = 'Tarif per jam wajib diisi.';
    } elseif ((int) $harga < 0) {
        $error['harga_per_jam'] = 'Tarif per jam tidak boleh negatif.';
    } elseif ((int) $harga > 100000000) {
        $error['harga_per_jam'] = 'Tarif per jam terlalu besar.';
    }

    // Jam operasional (format HH:MM)
    foreach (['jam_buka' => $jamBuka, 'jam_tutup' => $jamTutup] as $kolom => $nilai) {
        if ($nilai === '') {
            $error[$kolom] = 'Jam operasional wajib diisi.';
        } elseif (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $nilai)) {
            $error[$kolom] = 'Format jam harus HH:MM (mis. 08:00).';
        }
    }
    if (!isset($error['jam_buka']) && !isset($error['jam_tutup']) && $jamBuka >= $jamTutup) {
        $error['jam_tutup'] = 'Jam tutup harus lebih besar dari jam buka.';
    }

    // Durasi sewa per sesi (menit)
    $durasi = (int) ($d['durasi_sesi'] ?? 0);
    if (!in_array($durasi, DURASI_OPSI, true)) {
        $error['durasi_sesi'] = 'Pilih durasi sewa per sesi.';
    }

    // Fasilitas & keterangan (pembatas sesuai panjang kolom database)
    if (mb_strlen($fasilitas) > 255) {
        $error['fasilitas'] = 'Fasilitas terlalu panjang.';
    }
    if (mb_strlen($keterangan) > 120) {
        $error['keterangan'] = 'Keterangan maksimal 120 karakter.';
    }

    // Minimal satu foto wajib ada (owner harus memasukkan foto lapangannya)
    if (count($foto) < 1) {
        $error['foto'] = 'Minimal satu foto lapangan wajib dipilih.';
    } else {
        foreach ($foto as $satu) {
            if (!fotoValid($satu)) {
                $error['foto'] = 'Format foto tidak valid. Gunakan foto dari galeri, unggah file gambar, atau URL http/https.';
                break;
            }
        }
        if (!isset($error['foto']) && mb_strlen(implode(',', $foto)) > 500) {
            $error['foto'] = 'Daftar foto terlalu panjang.';
        }
    }

    $data = [
        'nama'              => $nama,
        'jenis'             => JENIS_OLAHRAGA[0],   // sementara hanya futsal
        'kecamatan'         => $kecamatan,
        'kota'              => $kota,
        'harga_per_jam'     => (int) $harga,
        'fasilitas'         => $fasilitas,
        'keterangan'        => $keterangan,
        'foto'              => implode(',', $foto),
        'jam_buka'          => $jamBuka,
        'jam_tutup'         => $jamTutup,
        'durasi_sesi'       => $durasi,
        'tersedia_hari_ini' => !empty($d['tersedia_hari_ini']),
        'aktif'             => !empty($d['aktif']),
    ];

    return [$data, $error];
}

// ---------------------------------------------------------------------------
// POST (multipart/form-data) -> unggah file foto milik owner
// Diprose lebih dulu, sebelum parsing body JSON.
// ---------------------------------------------------------------------------
if ($metode === 'POST' && isset($_FILES['foto'])) {
    $files = $_FILES['foto'];

    // Bisa satu file (name="foto") atau banyak file (name="foto[]")
    $daftar = is_array($files['name'])
        ? array_map(null, $files['name'], $files['tmp_name'], $files['error'], $files['size'])
        : [[$files['name'], $files['tmp_name'], $files['error'], $files['size']]];

    $berhasil = [];
    $gagal    = [];

    foreach ($daftar as $file) {
        [$namaAsli, $tmp, $errorUpload, $ukuran] = $file;

        if ($errorUpload !== UPLOAD_ERR_OK) {
            $gagal[] = 'Gagal mengunggah ' . $namaAsli;
            continue;
        }

        $tersimpan = simpanFotoUnggahan(['tmp_name' => $tmp, 'size' => $ukuran]);
        if ($tersimpan === null) {
            $gagal[] = 'Foto "' . $namaAsli . '" tidak valid atau lebih besar dari 3 MB';
            continue;
        }

        $berhasil[] = $tersimpan;
    }

    respond(true, $berhasil ? 'Foto berhasil diunggah.' : 'Tidak ada foto yang berhasil diunggah.',
        ['files' => $berhasil, 'errors' => $gagal]);
}

// ---------------------------------------------------------------------------
// GET -> daftar lapangan milik owner
// ---------------------------------------------------------------------------
if ($metode === 'GET') {
    $st = $pdo->prepare(
        "SELECT id, nama, jenis, kecamatan, kota, harga_per_jam, rating, jumlah_ulasan,
                foto, fasilitas, keterangan, tersedia_hari_ini, aktif,
                jam_buka, jam_tutup, durasi_sesi, created_at
         FROM lapangan
         WHERE owner_id = ?
         ORDER BY created_at DESC, id DESC"
    );
    $st->execute([$idOwner]);
    $data = $st->fetchAll();

    respond(true, 'OK', [
        'data'          => $data,
        'foto_opsi'     => opsiFoto($folderFoto),
        'jenis_opsi'    => JENIS_OLAHRAGA,
        'fasilitas_opsi' => DAFTAR_FASILITAS,
        'durasi_opsi'   => DURASI_OPSI,
    ]);
}

// ---------------------------------------------------------------------------
// POST -> tambah lapangan baru
// ---------------------------------------------------------------------------
if ($metode === 'POST') {
    [$data, $error] = validasiLapangan(inputAny());
    if ($error) respond(false, 'Data belum valid.', ['errors' => $error], 422);

    try {
        $st = $pdo->prepare(
            "INSERT INTO lapangan
                (owner_id, nama, jenis, kecamatan, kota, harga_per_jam, rating, jumlah_ulasan,
                 foto, fasilitas, keterangan, tersedia_hari_ini, aktif, jam_buka, jam_tutup, durasi_sesi)
             VALUES (?, ?, ?, ?, ?, ?, 0, 0, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $st->bindValue(1, $idOwner);
        $st->bindValue(2, $data['nama']);
        $st->bindValue(3, $data['jenis']);
        $st->bindValue(4, $data['kecamatan']);
        $st->bindValue(5, $data['kota']);
        $st->bindValue(6, $data['harga_per_jam']);
        $st->bindValue(7, $data['foto']);
        $st->bindValue(8, $data['fasilitas']);
        $st->bindValue(9, $data['keterangan']);
        // Kolom boolean harus di-bind sebagai boolean, kalau tidak PostgreSQL menolak ""
        $st->bindValue(10, $data['tersedia_hari_ini'], PDO::PARAM_BOOL);
        $st->bindValue(11, $data['aktif'], PDO::PARAM_BOOL);
        $st->bindValue(12, $data['jam_buka']);
        $st->bindValue(13, $data['jam_tutup']);
        $st->bindValue(14, $data['durasi_sesi']);
        $st->execute();

        // Ambil id baru lewat lastInsertId() (bukan RETURNING id).
        // Alasannya: RETURNING hanya ada di PostgreSQL, sedangkan lastInsertId()
        // bekerja di MySQL maupun PostgreSQL.
        $idBaru = (int) $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log('Gagal tambah lapangan: ' . $e->getMessage());
        respond(false, 'Terjadi kesalahan server saat menyimpan lapangan.', [], 500);
    }

    respond(true, 'Lapangan berhasil ditambahkan.', ['id' => $idBaru]);
}

// ---------------------------------------------------------------------------
// PUT -> ubah lapangan milik owner
// ---------------------------------------------------------------------------
if ($metode === 'PUT') {
    $d = inputAny();
    $id = (int) ($d['id'] ?? 0);
    if ($id <= 0) respond(false, 'ID lapangan tidak valid.', [], 422);

    [$data, $error] = validasiLapangan($d);
    if ($error) respond(false, 'Data belum valid.', ['errors' => $error], 422);

    // Pastikan lapangan benar-benar milik owner yang sedang login
    $cek = $pdo->prepare("SELECT id FROM lapangan WHERE id = ? AND owner_id = ?");
    $cek->execute([$id, $idOwner]);
    if (!$cek->fetch()) {
        respond(false, 'Lapangan tidak ditemukan.', [], 404);
    }

    try {
        // rating & jumlah_ulasan sengaja tidak disentuh: nilainya dari pelanggan
        $st = $pdo->prepare(
            "UPDATE lapangan SET
                nama = ?, jenis = ?, kecamatan = ?, kota = ?, harga_per_jam = ?,
                foto = ?, fasilitas = ?, keterangan = ?,
                tersedia_hari_ini = ?, aktif = ?, jam_buka = ?, jam_tutup = ?, durasi_sesi = ?
             WHERE id = ? AND owner_id = ?"
        );
        $st->bindValue(1, $data['nama']);
        $st->bindValue(2, $data['jenis']);
        $st->bindValue(3, $data['kecamatan']);
        $st->bindValue(4, $data['kota']);
        $st->bindValue(5, $data['harga_per_jam']);
        $st->bindValue(6, $data['foto']);
        $st->bindValue(7, $data['fasilitas']);
        $st->bindValue(8, $data['keterangan']);
        // Kolom boolean harus di-bind sebagai boolean, kalau tidak PostgreSQL menolak ""
        $st->bindValue(9, $data['tersedia_hari_ini'], PDO::PARAM_BOOL);
        $st->bindValue(10, $data['aktif'], PDO::PARAM_BOOL);
        $st->bindValue(11, $data['jam_buka']);
        $st->bindValue(12, $data['jam_tutup']);
        $st->bindValue(13, $data['durasi_sesi']);
        $st->bindValue(14, $id);
        $st->bindValue(15, $idOwner);
        $st->execute();
    } catch (PDOException $e) {
        error_log('Gagal ubah lapangan: ' . $e->getMessage());
        respond(false, 'Terjadi kesalahan server saat menyimpan lapangan.', [], 500);
    }

    respond(true, 'Lapangan berhasil diperbarui.', ['id' => $id]);
}

// ---------------------------------------------------------------------------
// DELETE -> hapus lapangan milik owner
// ---------------------------------------------------------------------------
if ($metode === 'DELETE') {
    $d = inputAny();
    $id = (int) ($d['id'] ?? 0);
    if ($id <= 0) respond(false, 'ID lapangan tidak valid.', [], 422);

    // Ownership dicek lewat DELETE-nya sendiri, jadi tidak bisa hapus milik orang lain
       $st = $pdo->prepare("DELETE FROM lapangan WHERE id = ? AND owner_id = ?");
    try {
        $st->execute([$id, $idOwner]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23503') {   // masih dipakai tabel lain (pesanan)
            respond(false, 'Lapangan ini sudah punya riwayat pesanan sehingga tidak bisa dihapus. Matikan "Tampilkan lapangan ke pengguna" saja.', [], 409);
        }
        throw $e;
    }

    if ($st->rowCount() === 0) {
        respond(false, 'Lapangan tidak ditemukan.', [], 404);
    }

    respond(true, 'Lapangan berhasil dihapus.', ['id' => $id]);
}

respond(false, 'Metode tidak diizinkan.', [], 405);
