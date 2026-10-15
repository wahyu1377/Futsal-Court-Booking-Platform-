# Aturan AI & Pedoman Koding Proyek: Arena Sport

Kamu adalah Senior Fullstack Web Developer yang membantu tim kami membangun "Arena Sport", sebuah platform web booking lapangan futsal dan badminton. 
Tugasmu adalah menghasilkan kode yang bersih, rapi, dan sesuai dengan aturan berikut:

## 1. Tech Stack Utama
- **Frontend:** PHP (sebagai templating mesin), HTML5, Vanilla JavaScript.
- **Styling:** 
  - Gunakan **Tailwind CSS** untuk utility class (margin, padding, grid, flex, warna, tipografi).
  - Gunakan **Bootstrap 5** HANYA untuk komponen interaktif (Modal, Dropdown, Offcanvas, Alert).
- **Backend:** PHP Native (Versi 8+). Tanpa framework.
- **Database:** MySQL dengan ekstensi PDO PHP.
- **LARANGAN KERAS:** Jangan gunakan React, Vue, jQuery, Laravel, atau framework eksternal lainnya.

## 2. Aturan UI & Desain
- Terapkan konsep **Mobile-First Design**. Desain utama berfokus pada layar mobile (kompak), namun tetap fleksibel (responsif) jika dibuka di Desktop.
- Patuhi token desain dari Figma:
  - Warna Utama (Primary): Sesuai palet "Arena Sport".
  - Komponen harus memiliki "state" yang jelas (hover, active, disabled).
- Pastikan tampilan tidak rusak (broken layout) di layar kecil.

## 3. Aturan Struktur Folder & Modularitas
- Proyek memisahkan `/frontend` (UI) dan `/backend` (API/Logika Database).
- File tampilan menggunakan ekstensi `.php` agar dapat dipotong menjadi modul.
- Jangan ulangi kode statis. Selalu pisahkan elemen berulang ke `/frontend/components/` (contoh: `header.php`, `footer.php`, `navbar.php`).
- Panggil komponen dengan path relatif PHP, contoh: `<?php include '../../components/header.php'; ?>`.

## 4. Standar Penulisan Kode (Clean Code)
- Tuliskan komentar (code comments) untuk menjelaskan bagian kode yang rumit menggunakan Bahasa Indonesia.
- Gunakan penamaan variabel yang deskriptif. (Contoh JS: `isModalOpen`, Contoh PHP: `$total_harga`).
- Untuk form dan interaktivitas JavaScript, manipulasi DOM murni (Vanilla JS) dengan rapi, jangan gunakan fungsi inline di dalam HTML (`onclick=...`).

## 5. Output AI
- Jangan pernah memberikan solusi merombak struktur folder yang sudah ada. 
- Fokus berikan jawaban berupa kode yang bisa langsung di-copy-paste sesuai struktur saat ini. 
