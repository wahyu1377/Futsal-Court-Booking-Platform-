# Rencana Resolve PR #17 (`Fix-All-Fitur-V1`)

> **STATUS: Tahap 1–3 SELESAI dan sudah di-push ke `origin/Fix-All-Fitur-V1` (`9d041b1`).**
> Conflict PR #17 terhadap `main` sudah habis (`git merge-tree` bersih).
> Tahap 4 (Wasmer, `APP_ENV`, rotasi password, `google/apiclient`) masih tertunda.
> Dokumen ini dipakai sebagai catatan hasil dan rencana lanjutan.

> Branch: `Fix-All-Fitur-V1` @ `9d041b1` · base: `f6af2f6`

### Riwayat commit hasil eksekusi

| Commit | Isi |
|---|---|
| `4d5852f` | merge: resolve konflik `main` → `Fix-All-Fitur-V1` (4 conflict + hapus folder duplikat root) |
| `6480be1` | fix: pulihkan avatar mitra, tautan tambah lapangan, dan tag `register.php` |
| `9d041b1` | feat: isi dashboard mitra dari database dan samakan auth owner |

---

## 0. Peta Arsitektur (sudah diverifikasi ke repo + DB live Supabase)

| Lapisan | Penulis | File | Sumber data | Status |
|---|---|---|---|---|
| Dashboard owner (UI final) | Athallah15 | `arena-sport/frontend/pages/venue/beranda-mitra.php` | `DashboardMitra::ambil()` | ❌ **data dummy** |
| Dashboard owner (fungsional, sementara) | Anda | `arena-sport/frontend/pages/owner/dashboard.php` | `backend/api/owner/lapangan.php` | ✅ nyata (DB live) |
| CRUD | Anda | `arena-sport/frontend/pages/owner/tambah_lapangan.php` + `assets/js/owner-lapangan.js` | API POST/PUT/DELETE | ✅ nyata (DB live) |
| Entry point | Anda (`bcbc7a0`) | `index.php` (root) | → `arena-sport/frontend/pages/venue/beranda-mitra.php` | ✅ OK |

Jalur owner Anda lengkap: `navbar.php` (avatar → menu Akun → `../owner/dashboard.php`).

**Bridge Athena → CRUD Anda:** `venue/beranda-mitra.php` baris 58 → `tambah-lapangan.php` (hyphen).
File dengan nama persis itu **tidak pernah ada di branch mana pun** (diverifikasi `git rev-list --all` × `ls-tree`).
Yang ada & berfungsi: `owner/tambah_lapangan.php` (underscore, folder berbeda).

---

## 1. Penyebab Conflict — `main` dan PR #17 bercabang lalu diedit paralel

```
                  merge-base  f6af2f6
    "Migrasi database dari mysql lokal ke supabase" (Aditya, 22 Okt)
                            │
        ┌───────────────────┴───────────────────┐
        ▼                                       ▼
   main (e2b3298)                         PR #17 (bcbc7a0)
   + 54f8490 Athallah15 "Add owner dashboard files"  + 66ee93e Aditya  Login with Google
   + 4a34c02 Anda "CRUD lapangan"                       + 7d9fad1 Aditya  Sinkronisasi Profil
                                                        + 3434c0f Aditya  Edit Profil (Picture)
                                                        + ecf06b4 Aditya  Halaman Favorit
                                                        + c8bba95 Aditya  Fix konflik
                                                        + f6409fc Aditya  fix fitur (restrukturisasi)
                                                        + bcbc7a0 Anda     entry point + venue
```

Dua sisi **mengubah file yang sama** setelah bercabang → Git tidak bisa memilih sendiri → conflict.

### 1.1 Akar struktural: `f6409fc` (restrukturisasi) vs `54f8490` (addition)

- `54f8490` (Athallah15, di `main`) **menambah** folder di root: `backend/` (3 file) dan `frontend/` (venue pages).
- `f6409fc` (Aditya, di PR #17) **memindahkan** folder yang sama ke `arena-sport/` dengan cara *hapus + tambah lagi* (bukan rename).

Akibatnya Git melihat:
1. **add/add** untuk `index.php` → file ini **tidak ada** di merge-base, masing-masing sisi membuat sendiri.
2. **content conflict** untuk file yang *dipindah* (`frontend/components/header-mitra.php` → `arena-sport/...`), karena *delete+add* ≠ rename.

### 1.2 Temuan baru: merge `main` → PR #17 akan **mengembalikan `backend/` ganda di root**

Di merge-base `f6af2f6` **belum ada** `backend/`/`frontend/` di root.
Keduanya **baru** di `main` (via `54f8490`), sedangkan PR #17 sudah memindahkannya ke `arena-sport/`.

Verifikasi `git merge-tree --write-tree origin/main HEAD` → tree `e95f89a` berisi:

```
backend/          ← muncul lagi (path lama Athallah)   ← DUPLIKAT
frontend/         ← muncul lagi (path lama Athallah)   ← DUPLIKAT
arena-sport/
  backend/        ← versi konsolidasi (PR #17)          ← dipakai aplikasi
  frontend/       ← versi konsolidasi (PR #17)          ← dipakai aplikasi
```

**Ini persis masalah "backend double" yang Anda laporkan di awal** — akarnya restrukturisasi Aditya + timing PR Athallah.

### 1.3 Verifikasi: folder root hasil merge = **100% duplikat** (aman dihapus)

Hash blob di tree `e95f89a`:

| File di root | Hash | Padanan di `arena-sport/` | Hash | Sama? |
|---|---|---|---|---|
| `backend/config/app.php` | `520b92b4` | `backend/config/app.php` | `520b92b4` | ✅ |
| `backend/config/helpers.php` | `167a3277` | `backend/config/helpers.php` | `167a3277` | ✅ |
| `backend/models/DashboardMitra.php` | `3d087a99` | `backend/config/models/DashboardMitra.php` | `3d087a99` | ✅ |
| `frontend/components/head-assets.php` | `11204fba` | `frontend/components/head-assets.php` | `11204fba` | ✅ |
| `frontend/components/nav-mitra.php` | `acbe546e` | `frontend/components/nav-mitra.php` | `acbe546e` | ✅ |
| `frontend/assets/css/venue.css` | `d289b882` | `frontend/assets/css/venue.css` | `d289b882` | ✅ |
| `frontend/pages/venue/beranda-mitra.php` | `04ea009c` | `frontend/pages/venue/beranda-mitra.php` | `27e718fa` | ❌ (beda — versi Anda yang sudah di-fix) |
| `frontend/components/header-mitra.php` | `23badc86` | `frontend/components/header-mitra.php` | `990c8cb3` | ❌ (beda — PR #17 kehilangan `<img>`) |

→ Setelah merge, hapus `backend/` + `frontend/` di root. **Tidak ada pekerjaan tim yang hilang**; semuanya sudah ada di `arena-sport/`.

---

## 2. Rencana Resolve — 4 conflict

### 2.1 `index.php` — **add/add** → ambil versi PR #17

Isinya identik dengan versi Anda (`bcbc7a0`):
```php
<?php
// Titik masuk: buka http://localhost:8000 -> langsung ke dashboard owner
// Struktur aplikasi sudah disatukan ke folder arena-sport/, jadi path relatif
// ditulis dari root repo: arena-sport/frontend/pages/venue/beranda-mitra.php
header('Location: arena-sport/frontend/pages/venue/beranda-mitra.php');
exit;
```
Alasan: versi `main` (`frontend/pages/...`) mati begitu folder root dihapus.

### 2.2 `.gitignore` — **content** → ambil versi PR #17 (superset, nol kehilangan)

```gitignore
# Kredensial & konfigurasi lokal (jangan pernah di-commit)
.env
.env.local
arena-sport/backend/config/database.local.php

# Foto profil pengguna (diunggah lewat Edit Profil); .htaccess pelindungnya tetap ikut
arena-sport/frontend/assets/img/avatars/*
!arena-sport/frontend/assets/img/avatars/.htaccess

# Foto lapangan yang diunggah owner (jangan ikut di-commit)
arena-sport/frontend/assets/img/lapangan/owner/

# Log & berkas sementara
*.log
npm-debug.log*

# Berkas OS
.DS_Store
Thumbs.db
desktop.ini
```

### 2.3 `arena-sport/frontend/assets/js/beranda.js` — **content substantif** → penjahit manual

| Sisi | Pendekatan |
|---|---|
| `main` (Anda) | `function card(c)` inline, punya `fotoUtama` (foto pertama dari daftar dipisah koma), `urlFoto` (URL eksternal), rating kondisional |
| PR #17 (Aditya) | `card` diekstrak ke `kartu.js` → `renderCard(c, IMG, DETAIL)` + tombol favorit |

**Resolusi:**

1. `beranda.js` → versi PR #17 (`const card = c => renderCard(c, IMG, DETAIL);`).
   `esc`/`cap`/`rupiah` kini disediakan `kartu.js`, dan `kartu.js` **sudah dimuat lebih dulu**:
   `beranda.php` baris 6 → `$extraJs = ['../../assets/js/kartu.js', '../../assets/js/fav-toggle.js', '../../assets/js/beranda.js'];`
   `favorit.php` baris 7 → sama. Tidak ada urutan yang terbalik.
2. **`kartu.js` disunting** untuk membawa 2 perbaikan Anda (otomatis ikut halaman favorit):

```js
// Dipakai bersama oleh beranda dan halaman favorit
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const cap = s => s.charAt(0).toUpperCase() + s.slice(1);
const rupiah = n => 'Rp' + Number(n).toLocaleString('id-ID');

function renderCard(c, IMG, DETAIL) {
  const tags = [cap(c.jenis), ...(c.fasilitas ? c.fasilitas.split(',').map(t => t.trim()).filter(Boolean) : [])].slice(0, 3);

  // Foto pada database boleh lebih dari satu (dipisah koma), yang dipakai adalah foto utama
  const fotoUtama = String(c.foto || '').split(',').map(s => s.trim()).filter(Boolean)[0] || '';
  // Foto bisa file di folder aset atau URL dari internet
  const urlFoto = nama => /^https?:\/\//i.test(nama) ? nama : IMG + nama;
  const foto = fotoUtama ? `<img src="${esc(urlFoto(fotoUtama))}" alt="${esc(c.nama)}" loading="lazy" onerror="this.remove()">` : '';

  const avail = c.tersedia_hari_ini == 1
    ? '<span class="avail">Tersedia Hari Ini</span>'
    : '<span class="avail off">Penuh Hari Ini</span>';

  const fav = !!c.favorit;

  // Rating baru tampil kalau sudah ada nilai dari pelanggan
  const rating = Number(c.rating) > 0
    ? `<div class="court-rating"><i class="bi bi-star-fill"></i> ${Number(c.rating).toFixed(1)} <small>(${Number(c.jumlah_ulasan)})</small></div>`
    : '';

  return `
  <div class="col-12 col-md-6 col-xl-4">
    <article class="court-card">
      <div class="court-img">
        ${foto}
        <button type="button" class="fav-btn${fav ? ' is-on' : ''}" data-id="${Number(c.id)}"
                aria-pressed="${fav}" aria-label="${fav ? 'Hapus dari favorit' : 'Tambah ke favorit'}">
          <i class="bi ${fav ? 'bi-heart-fill' : 'bi-heart'}"></i>
        </button>
        <div class="court-tags">${tags.map(t => `<span>${esc(t)}</span>`).join('')}</div>
        ${rating}
      </div>
      <div class="court-body">
        <h3>${esc(c.nama)}</h3>
        <div class="court-loc mb-2"><i class="bi bi-geo-alt me-1"></i>${esc(c.kecamatan)}, ${esc(c.kota)}</div>
        <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
          ${avail}${c.keterangan ? `<span class="court-loc">${esc(c.keterangan)}</span>` : ''}
        </div>
        <div class="d-flex justify-content-between align-items-end mt-auto">
          <div>
            <div class="price-label">TARIF SEWA</div>
            <div class="price">${rupiah(c.harga_per_jam)} <small>/ jam</small></div>
          </div>
          <a href="${DETAIL}?id=${Number(c.id)}" class="btn btn-main">Pilih Lapangan <i class="bi bi-chevron-right ms-1"></i></a>
        </div>
      </div>
    </article>
  </div>`;
}
```

> Catatan: `c.tersedia_hari_ini == 1` — di PostgreSQL kolomnya `boolean`, API mengembalikannya sebagai `true/false`;
> `true == 1` benar di JS, jadi aman. Tidak diubah.

### 2.4 `arena-sport/frontend/pages/auth/login.php` — **content** → ambil versi PR #17 + rapikan tag

Sisi PR #17 jelas lebih lengkap (GSI asli, `#roleModal`, `require_once backend/config/google.php`, `auth.js` sudah拉手 `data-api`/`data-client-id`/`data-role-pick`).

**TAPI sisi PR #17 sendiri bermasalah** (bukan hasil conflict — sudah ada di `c8bba95` Aditya):
- `</form>` **yatim** di baris 65 (form sudah ditutup baris 56)
- `</div></main>` di baris 72 tidak seimbang (tag `</div>` penutup `auth-box` hilang)

Struktur yang benar:

```php
        </form>

        <div class="divider">atau masuk dengan</div>
        <div id="googleBtn" class="d-flex justify-content-center"
             data-api="../../../backend/api/auth/google.php"
             data-client-id="<?= htmlspecialchars(GOOGLE_CLIENT_ID) ?>"
             data-text="continue_with"></div>
      </div><!-- /auth-card -->

      <p class="text-center mt-4 mb-2 text-secondary">Belum punya akun? <a href="register.php" class="link-green">Daftar
          Sekarang</a></p>
      <p class="text-center foot"><a href="#" class="text-reset text-decoration-none">Pusat Bantuan</a> &nbsp;•&nbsp; <a
          href="#" class="text-reset text-decoration-none">Syarat &amp; Ketentuan</a></p>

    </div><!-- /auth-box -->
  </main>
```

(Teks `</form>` baris 65 dihapus; `</div>` penutup `auth-box` dikembalikan sebelum `</main>`.)

---

## 3. Perbaikan A/B/C/D (di luar conflict — silakan eksekusi)

| # | Aksi | Dampak | Risiko |
|---|---|---|---|
| **A** | Pulihkan tag `<img>` di `arena-sport/frontend/components/header-mitra.php` | Avatar mitra tampil, HTML valid | **Nol** |
| **B** | `venue/beranda-mitra.php` baris 58 → `../owner/tambah_lapangan.php` | Tombol "Tambah Lapangan" berfungsi | **Nol** |
| **C** | `require_owner()` → `requireLogin('owner')` + `currentUser()` | Satu mekanisme auth | Sedang (menyentuh kode Athena) |
| **D** | Isi `DashboardMitra::ambil()` dengan query PDO nyata | Dashboard mitra data asli | Sedang–besar (butuh skema) |

### A — `header-mitra.php` (baris 15–16)

```php
    <button type="button" class="relative grid h-11 w-11 place-items-center rounded-full text-2xl text-ink hover:bg-slate-100" aria-label="Notifikasi">
      <i class="bi bi-bell"></i>
      <span class="absolute right-2.5 top-2.5 h-2.5 w-2.5 rounded-full border-2 border-white bg-brand-600"></span>
    </button>
    <img src="<?= e($assets) ?>/img/avatar.jpg" alt="<?= e($data['mitra']['nama']) ?>"
         class="h-11 w-11 rounded-full border-2 border-brand-600 object-cover object-top">
  </div>
</header>
```
Asli utuh di `origin/main:frontend/components/header-mitra.php` (hash `23badc86`). Rusak oleh `f6409fc` (delete+re-add).

### B — link bridge di `venue/beranda-mitra.php`

Baris 58:
```php
    <a href="../owner/tambah_lapangan.php"
```
Perubahan lain di halaman ini (link mati lain) — **opsional, putuskan terpisah**:

| Baris | Link sekarang | Padanan yang ada | Rekomendasi |
|---|---|---|---|
| 58 | `tambah-lapangan.php` | `../owner/tambah_lapangan.php` | ✅ **wajib (B)** |
| 124 | `venue.php` | `../owner/dashboard.php` | arahkan ke dashboard owner |
| 156 | `jadwal.php?lapangan=` | `../owner/dashboard.php` | atau biarkan (jadwal sudah di-embed) |
| 166 | `edit-lapangan.php?id=` | `../owner/dashboard.php` | CRUD Anda sudah edit inline (PUT) |
| 167 | `tarif.php?lapangan=` | `../owner/dashboard.php` | tarif = `harga_per_jam`, sudah ada di CRUD |

> Catatan: `venue.php`, `jadwal.php`, `edit-lapangan.php`, `tarif.php`, `profil.php` **tidak pernah ada** di commit mana pun,
> di branch mana pun — 404-nya mendahului PR #17 dan sudah ada di `main`.
> Rekomendasi: **jangan buat file baru**, arahkan ke halaman yang sudah ada.

### C — samakan auth di `venue/beranda-mitra.php`

Sekarang:
```php
require_once __DIR__ . '/../../../backend/config/app.php';
require_once __DIR__ . '/../../../backend/config/helpers.php';
require_once __DIR__ . '/../../../backend/config/models/DashboardMitra.php';

require_owner();
```
`require_owner()` (Athalla, `backend/config/helpers.php`) **melewati cek saat `APP_ENV === 'local'`** dan redirect ke `../../index.php` yang salah.

Target (mengikuti `owner/dashboard.php`):
```php
require_once __DIR__ . '/../../../backend/core/auth.php';   // requireLogin() + currentUser() + session
require_once __DIR__ . '/../../../backend/config/helpers.php'; // e(), rupiah(), rupiahRingkas()
require_once __DIR__ . '/../../../backend/config/models/DashboardMitra.php';

requireLogin('owner');
$user = currentUser();
```
Catatan: `core/auth.php` me-`require` `core/helper.php` (session) — `config/helpers.php` juga `session_start()` tapi dijaga `session_status()`, jadi tidak bentrok.
`requireLogin()` me-redirect ke `../auth/login.php` — path relatif dari `pages/venue/` → `pages/auth/login.php` ✅.
`currentUser()` untuk role `owner` **tidak** membaca kolom `avatar` (kolom itu hanya di tabel `users`) → `avatar` = `null`, aman.

### D — `DashboardMitra::ambil()` jadi data nyata — **ADA KENDALA SKEMA**

**Temuan penting (sudah diverifikasi ke DB live Supabase):**

Tabel yang benar-benar ada: `favorit`, `lapangan`, `owners`, `users`.
**Tidak ada tabel `reservasi` / `pesanan` / `booking`.** `backend/api/user/booking.php` dan `jadwal.php` = **0 byte** (placeholder kosong).

Artinya bagian `ringkasan` (pesanan hari ini / menunggu bayar / pendapatan bulan) dan `reservasi_terkini`
**tidak punya sumber data saat ini** — butuh pembuatan tabel baru, itu pekerjaan terpisah
(termasuk migrasi SQL di `arena-sport/backend/sql/`).

Yang **bisa** diisi nyata sekarang (mapping ke skema nyata):

| Kunci output | Sumber nyata |
|---|---|
| `mitra.nama` | `owners.full_name` WHERE `id = $mitraId` |
| `mitra.brand` | `owners.email` (domain) atau tetap konstanta `'Arena Sport'` |
| `mitra.status` | konstanta/derived, mis. `'Mitra Arena Sport'` |
| `lapangan[]` | `SELECT ... FROM lapangan WHERE owner_id = ? AND aktif = TRUE` |
| `lapangan[].nama` | `lapangan.nama` |
| `lapangan[].jenis` | `lapangan.jenis` (→ `cap()`: "Futsal") |
| `lapangan[].tipe` | belum ada di skema → derivasi dari `fasilitas` (mis. ada "Indoor AC" → "Indoor") |
| `lapangan[].ikon` | derivasi dari `jenis` (futsal → `bi-dribbble`) |
| `lapangan[].zona` | `lapangan.kecamatan` (+ `kota`) |
| `lapangan[].standar` | `lapangan.keterangan` |
| `lapangan[].harga_per_jam` | `lapangan.harga_per_jam` |
| `lapangan[].slot_tersedia` | belum ada → derivasi dari `jam_buka`/`jam_tutup`/`durasi_sesi` |
| `lapangan[].foto` | `lapangan.foto` (boleh koma → ambil pertama, konsisten dgn `kartu.js`) |
| `ringkasan.*` | **belum ada sumber** → sementara tampilkan `0` / "Belum ada data" |
| `reservasi_terkini` | **belum ada sumber** → sembunyikan blok bila kosong |

Contoh kerangka (bentuk array dipertahankan agar halaman tidak perlu diubah — sesuai komentar di file Athena):

```php
public static function ambil(int $mitraId): array
{
    $pdo = db();

    // Data akun mitra
    $st = $pdo->prepare('SELECT full_name, email FROM owners WHERE id = ?');
    $st->execute([$mitraId]);
    $mitra = $st->fetch() ?: ['full_name' => 'Mitra', 'email' => ''];

    // Daftar lapangan milik mitra
    $st = $pdo->prepare(
        'SELECT id, nama, jenis, kecamatan, kota, harga_per_jam, foto, fasilitas,
                keterangan, tersedia_hari_ini, jam_buka, jam_tutup, durasi_sesi
         FROM lapangan
         WHERE owner_id = ? AND aktif = TRUE
         ORDER BY id'
    );
    $st->execute([$mitraId]);

    $lapangan = [];
    foreach ($st->fetchAll() as $r) {
        $fasilitas = array_filter(array_map('trim', explode(',', (string) $r['fasilitas'])));
        $mulai  = strtotime((string) $r['jam_buka'] ?: '08:00');
        $selesai = strtotime((string) $r['jam_tutup'] ?: '22:00');
        $durasi  = max(30, (int) $r['durasi_sesi']);
        $slot    = max(0, (int) floor(($selesai - $mulai) / 60 / ($durasi / 60)));

        $lapangan[] = [
            'id'             => (int) $r['id'],
            'nama'           => $r['nama'],
            'jenis'          => ucfirst((string) $r['jenis']),
            'tipe'           => in_array('Indoor AC', $fasilitas, true) ? 'Indoor' : 'Outdoor',
            'ikon'           => 'bi-dribbble',
            'zona'           => $r['kecamatan'],
            'standar'        => (string) $r['keterangan'],
            'harga_per_jam'  => (int) $r['harga_per_jam'],
            'slot_tersedia'  => $r['tersedia_hari_ini'] ? $slot : 0,
            'foto'           => trim(explode(',', (string) $r['foto'])[0]),
        ];
    }

    return [
        'mitra' => [
            'nama'   => $mitra['full_name'],
            'status' => 'Mitra Arena Sport',
            'brand'  => 'Arena Sport',
        ],
        'ringkasan' => [
            'pesanan_hari_ini'  => 0,
            'selisih_kemarin'   => 0,
            'menunggu_bayar'    => 0,
            'pendapatan_bulan'  => 0,
            'persen_bulan_lalu' => 0,
        ],
        'reservasi_terkini' => [],   // tabel reservasi belum ada
        'lapangan' => $lapangan,
    ];
}
```

> `DashboardMitra` berada di `backend/config/models/` → `db()` belum ter-load.
> Perlu `require_once __DIR__ . '/../database.php';` di dalam file (atau di caller).
> `database.php` aman dipanggil dari CLI maupun web.

**Saran urutan untuk D:** kerjakan **D-1 (daftar lapangan + identitas mitra)** dulu — langsung terlihat,
tidak butuh tabel baru. **D-2 (ringkasan pesanan & pendapatan)** menunggu tabel `reservasi` dibuat
(butuh SQL Editor Supabase dengan role `postgres`, lihat pola `backend/sql/003_housekeeping_index.sql`).

---

## 4. Urutan Eksekusi (disarankan)

```
Tahap 1 — Resolve conflict (wajib, tanpa perubahan fitur)
  1.1 git merge origin/main           → muncul 4 conflict
  1.2 .gitignore        : checkout --ours   (versi PR #17 = superset)
  1.3 index.php         : checkout --ours   (versi PR #17, path arena-sport/)
  1.4 beranda.js        : checkout --ours   (versi PR #17, delegasi ke kartu.js)
  1.5 kartu.js          : sunting → tambah fotoUtama/urlFoto + rating kondisional
  1.6 login.php         : checkout --ours   (versi PR #17) → buang </form> yatim, kembalikan </div>
  1.7 HAPUS root backend/ + frontend/  (duplikat, hash sudah diverifikasi)
  1.8 git commit  "merge: resolve konflik main -> Fix-All-Fitur-V1"

Tahap 2 — Perbaikan cepat
  2.1 header-mitra.php   : pulihkan tag <img>            (A)
  2.2 beranda-mitra.php  : link → ../owner/tambah_lapangan.php  (B)
  2.3 commit             : "fix: pulihkan avatar mitra dan tautan tambah lapangan"

Tahap 3 — Perbaikan struktural (setelah Athalla menyetujui)
  3.1 beranda-mitra.php  : requireLogin('owner') + currentUser()   (C)
  3.2 DashboardMitra::ambil() → query nyata, D-1                  (D-1)
  3.3 commit             : "refactor: samakan auth owner dan isi dashboard mitra dari database"

Tahap 4 — Terpisah / non-blocking (opsional)
  4.1 Wasmer Root Directory → arena-sport  (Anda tunda dulu)
  4.2 APP_ENV → production di arena-sport/backend/config/app.php
  4.3 Link mati lain (venue.php / jadwal.php / tarif.php / edit-lapangan.php)
```

---

## 5. Verifikasi (wajib sebelum commit)

| Cek | Cara | Target |
|---|---|---|
| Lint PHP semua file berubah | `php -l` per file | 0 error |
| Tidak ada marker conflict | `grep -rn '<<<<<<<\|>>>>>>>' arena-sport/ index.php` | 0 |
| Halaman bisa diakses | `php -S 0.0.0.0:8123` lalu `curl -s -o /dev/null -w "%{http_code}"` | root 302 → venue 200 |
| Body bersih | cek **isi respons**, bukan cuma status (Fatal Error bisa muncul dengan HTTP 200) | tidak ada "Fatal error"/"Warning"/"Parse error" |
| Aset venue | loop 17 aset `arena-sport/frontend/assets/...` | semua 200 |
| Smoke test | `php arena-sport/backend/smoke-test.php` | semua `[ OK ]` |
| Folder root bersih | `ls backend frontend` di root repo | tidak ada (konsolidasi) |
| Kartu beranda | buka `user/beranda.php`, cek multi-foto + rating kondisional | sesuai |
| Login Google | buka `auth/login.php`, cek tag valid + tombol GSI render | OK |

---

## 6. Yang BELUM dikerjakan di dokumen ini (di luar cakupan)

- **Wasmer** Root Directory → `arena-sport` + `APP_ENV` → `production` (Anda tunda)
- **Rotasi password** `postgres` Supabase (nilai lama masih ada di riwayat git `main`)
- **`google/apiclient`** untuk produksi + Authorized JS Origin `https://arena-sport.wasmer.app` & `http://localhost:3000`
- **Tabel `reservasi`** untuk menghidupkan `ringkasan` & `reservasi_terkini` (D-2)
- **`users.avatar` + tabel `favorit`** — ada di DB live tapi belum ada di `arena_sport.sql` (dump MySQL lama)
- Login Google versi produksi (`backend/api/auth/google.php` masih pakai endpoint `tokeninfo`)
