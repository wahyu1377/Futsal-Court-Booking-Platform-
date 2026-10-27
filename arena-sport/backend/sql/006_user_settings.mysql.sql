-- ============================================================================
-- 006_user_settings.mysql.sql  (MySQL / MariaDB lokal - XAMPP)
--
-- Padanan PostgreSQL dari 006_user_settings.sql, untuk database lokal.
-- Perbedaan: updated_at pakai DATETIME, dan RLS tidak ada (MySQL tidak punya
-- Row Level Security - hak akses pakai GRANT saja).
--
-- CARA JALANKAN:
--   phpMyAdmin > pilih database arena_sport > tab SQL > tempel file ini > Run
--
-- Script aman dijalankan berulang kali (IF NOT EXISTS).
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 1. Tabel preferensi notifikasi pengguna
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS user_settings (
    user_id            INT UNSIGNED NOT NULL,
    notifikasi_pesanan TINYINT(1)  NOT NULL DEFAULT 1,
    notifikasi_email   TINYINT(1)  NOT NULL DEFAULT 0,
    notifikasi_wa      TINYINT(1)  NOT NULL DEFAULT 1,
    updated_at         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    CONSTRAINT fk_user_settings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 2. Hak akses (pola sama seperti tabel lain di MySQL)
--    Ganti nama pengguna & host sesuai setup lokal Anda.
-- GRANT SELECT, INSERT, UPDATE, DELETE ON arena_sport.user_settings TO 'arena_app'@'localhost';

-- ---------------------------------------------------------------------------
-- 3. VERIFIKASI (jalankan terpisah bila ingin melihat hasilnya)
-- ---------------------------------------------------------------------------
-- DESCRIBE user_settings;
