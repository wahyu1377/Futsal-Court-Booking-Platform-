-- ============================================================================
-- 005_password_reset.mysql.sql  (MySQL / MariaDB lokal - XAMPP)
--
-- Padanan PostgreSQL dari 005_password_reset.sql, untuk database lokal.
-- Perbedaan: id pakai AUTO_INCREMENT (bukan IDENTITY), kolom waktu pakai
-- DATETIME (TIMESTAMP di MySQL punya batas tahun 2038), dan RLS tidak ada
-- (MySQL tidak punya Row Level Security - hak akses pakai GRANT saja).
--
-- CARA JALANKAN:
--   phpMyAdmin > pilih database arena_sport > tab SQL > tempel file ini > Run
--
-- Script aman dijalankan berulang kali (IF NOT EXISTS).
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 1. Tabel token reset
--   (token_hash, expires_at, used_at, created_at punya arti sama seperti di
--     versi PostgreSQL - lihat komentar di 005_password_reset.sql)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS password_reset (
    id          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    token_hash  VARCHAR(64)    NOT NULL,
    role        VARCHAR(10)    NOT NULL,
    account_id  INT UNSIGNED   NOT NULL,
    email_kirim VARCHAR(150)   NOT NULL,
    ip_address  VARCHAR(45)    DEFAULT NULL,
    expires_at  DATETIME       NOT NULL,
    used_at     DATETIME       DEFAULT NULL,
    created_at  DATETIME       NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_password_reset_token (token_hash),
    KEY idx_password_reset_akun (role, account_id),
    KEY idx_password_reset_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 2. Hak akses (pola sama seperti tabel lain di MySQL)
--    Ganti nama pengguna & host sesuai setup lokal Anda.
-- ---------------------------------------------------------------------------
-- GRANT SELECT, INSERT, UPDATE, DELETE ON arena_sport.password_reset TO 'arena_app'@'localhost';

-- ---------------------------------------------------------------------------
-- 3. VERIFIKASI (jalankan terpisah bila ingin melihat hasilnya)
-- ---------------------------------------------------------------------------
-- DESCRIBE password_reset;
-- SHOW INDEX FROM password_reset;
