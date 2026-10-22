-- ============================================================================
-- Arena Sport — skema database (MySQL / MariaDB)
--
-- File ini adalah PADANAN dari skema PostgreSQL yang dipakai di Supabase,
-- yaitu source of truth proyek. Tujuannya: hasil import MySQL = hasil migrasi
-- Supabase, sehingga aplikasi tidak perlu berbeda treatment per database.
--
-- Perbedaan yang memang disengaja (karena tipe berbeda antar DB):
--   - bigint / identity  -> INT UNSIGNED AUTO_INCREMENT
--   - BOOLEAN            -> TINYINT(1)
--   - TIMESTAMPTZ        -> TIMESTAMP
--   - TIME               -> TIME
--   - VARCHAR tanpa batas-> VARCHAR dengan batas (sama dengan PG)
--
-- Data awal di bawah hanya data contoh. Password akun demo:
--   user  : user123
--   owner : owner123
--
-- Import lewat phpMyAdmin: Databases > Import > pilih file ini > Go
-- Import lewat CLI    : mysql -u root -p arena_sport < arena_sport.sql
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- Bersihkan tabel lama (biar file ini bisa di-import berulang)
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `lapangan`;
DROP TABLE IF EXISTS `owners`;
DROP TABLE IF EXISTS `users`;

-- ---------------------------------------------------------------------------
-- Tabel users — akun pemain (peminat lapangan)
-- Catatan: tidak ada kolom `role`. Role ditentukan dari tabel tempat akun
-- ditemukan (users = user, owners = owner), sama seperti di PostgreSQL.
-- ---------------------------------------------------------------------------
CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `failed_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_key` (`email`),
  UNIQUE KEY `users_phone_key` (`phone`),
  UNIQUE KEY `users_google_id_key` (`google_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Tabel owners — akun pemilik venue (dikelola terpisah dari users)
-- ---------------------------------------------------------------------------
CREATE TABLE `owners` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `failed_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `owners_email_key` (`email`),
  UNIQUE KEY `owners_phone_key` (`phone`),
  UNIQUE KEY `owners_google_id_key` (`google_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Tabel lapangan — data venue /court
-- ---------------------------------------------------------------------------
CREATE TABLE `lapangan` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner_id` int(10) UNSIGNED DEFAULT NULL,
  `nama` varchar(120) NOT NULL,
  `jenis` varchar(30) NOT NULL,
  `kecamatan` varchar(80) NOT NULL,
  `kota` varchar(80) NOT NULL,
  `harga_per_jam` int(10) UNSIGNED NOT NULL,
  `rating` decimal(2,1) NOT NULL DEFAULT 0.0,
  `jumlah_ulasan` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `foto` varchar(500) DEFAULT NULL,
  `fasilitas` varchar(255) DEFAULT NULL,
  `keterangan` varchar(120) DEFAULT NULL,
  `tersedia_hari_ini` tinyint(1) NOT NULL DEFAULT 1,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `jam_buka` time DEFAULT NULL,
  `jam_tutup` time DEFAULT NULL,
  `durasi_sesi` int(11) NOT NULL DEFAULT 60,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lapangan_owner_id` (`owner_id`),
  KEY `idx_lapangan_aktif` (`aktif`),
  KEY `idx_lapangan_jenis` (`jenis`),
  CONSTRAINT `lapangan_owner_id_fkey`
    FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `lapangan_harga_per_jam_check` CHECK (`harga_per_jam` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ===========================================================================
-- DATA CONTOH
-- ===========================================================================

-- Akun pemain (password: user123)
INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password_hash`, `failed_attempts`, `locked_until`, `created_at`) VALUES
(1, 'Aditya', 'tes@gmail.com', NULL, '$2y$10$69327WLyvATwmAZyqsUaDOtfPCGw6BjHc9k4cUEfp2Cw6htHIV0cC', 0, NULL, '2026-10-16 00:58:23'),
(2, 'Aji', NULL, '083875211788', '$2y$10$0Y9f9X9sIzjxsN5VZmWuD.p.5VgAgXhsXYE5wX7DFL4HbvaKe8qiK', 0, NULL, '2026-10-16 01:20:06');

-- Akun pemilik venue (password: owner123)
INSERT INTO `owners` (`id`, `full_name`, `email`, `phone`, `password_hash`, `failed_attempts`, `locked_until`, `created_at`) VALUES
(1, 'Owner Demo', 'owner@arenasport.id', NULL, '$2y$10$YO4yal3SdWIJOQjAygnbNOf6T.RCYCoxLnM1PXHCpybiuR8Ct0uwG', 0, NULL, '2026-10-16 00:58:23');

-- Data lapangan contoh
INSERT INTO `lapangan` (`id`, `owner_id`, `nama`, `jenis`, `kecamatan`, `kota`, `harga_per_jam`, `rating`, `jumlah_ulasan`, `foto`, `fasilitas`, `keterangan`, `tersedia_hari_ini`, `aktif`, `jam_buka`, `jam_tutup`, `durasi_sesi`, `created_at`) VALUES
(1, NULL, 'Champion Futsal Arena Cilandak', 'futsal', 'Cilandak Barat', 'Jakarta Selatan', 150000, 4.8, 124, 'champion-futsal.jpg', 'Rumput Sintetis,Indoor AC', 'Peralatan Lengkap', 1, 1, '08:00', '22:00', 60, '2026-10-16 01:09:45'),
(2, NULL, 'Smash Futsal Kuningan', 'futsal', 'Setiabudi Kuningan', 'Jakarta Selatan', 75000, 4.9, 88, 'smash-Futsal.jpg', 'Karpet Vinyl BWF,Parkir Luas', 'Standar BWF Pro', 1, 1, '08:00', '22:00', 60, '2026-10-16 01:09:45'),
(3, NULL, 'Garuda Futsal Pondok Indah', 'futsal', 'Pondok Indah', 'Jakarta Selatan', 130000, 4.6, 57, 'garuda-futsal.jpg', 'Vinyl,Outdoor', 'Kantin & Musala', 0, 1, '08:00', '22:00', 60, '2026-10-16 01:09:45');

-- Auto increment dilanjutkan dari data contoh
ALTER TABLE `users` AUTO_INCREMENT = 3;
ALTER TABLE `owners` AUTO_INCREMENT = 2;
ALTER TABLE `lapangan` AUTO_INCREMENT = 4;
