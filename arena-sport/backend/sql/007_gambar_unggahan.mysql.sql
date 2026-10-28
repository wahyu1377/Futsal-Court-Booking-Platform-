-- ============================================================================
-- 007_gambar_unggahan.mysql.sql  (MySQL / MariaDB lokal - XAMPP)
--
-- Padanan PostgreSQL dari 007_gambar_unggahan.sql, untuk database lokal.
-- Perbedaan: data memakai MEDIUMTEXT (base64) dan RLS tidak ada
-- (MySQL tidak punya Row Level Security - hak akses pakai GRANT saja).
--
-- CARA MENJALANKAN:
--   phpMyAdmin > pilih database arena_sport > tab SQL > tempel file ini > Run
--
-- Script aman dijalankan berulang kali (IF NOT EXISTS).
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 1. Tabel penyimpan gambar unggahan
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gambar_unggahan (
    nama      VARCHAR(255) NOT NULL,
    mime      VARCHAR(60)  NOT NULL DEFAULT 'image/jpeg',
    data      MEDIUMTEXT   NOT NULL,
    ukuran    INT          NOT NULL DEFAULT 0,
    dibuat_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (nama)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 2. Hak akses (pola sama seperti tabel lain di MySQL)
--    Ganti nama pengguna & host sesuai setup lokal Anda.
-- GRANT SELECT, INSERT, UPDATE, DELETE ON arena_sport.gambar_unggahan TO 'arena_app'@'localhost';

-- ---------------------------------------------------------------------------
-- 3. Menyamakan huruf besar/kecil pada data foto yang sudah ada
-- ---------------------------------------------------------------------------
UPDATE lapangan
   SET foto = LOWER(foto)
 WHERE foto <> LOWER(foto);

-- ---------------------------------------------------------------------------
-- 4. VERIFIKASI (jalankan terpisah bila ingin melihat hasilnya)
--
-- DESCRIBE gambar_unggahan;
