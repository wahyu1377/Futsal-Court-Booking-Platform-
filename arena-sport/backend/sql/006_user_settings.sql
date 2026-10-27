-- ============================================================================
-- 006_user_settings.sql  (PostgreSQL / Supabase)
--
-- Menambahkan tabel preferensi notifikasi untuk akun PENGGUNA,
-- supaya halaman "Setelan" pengguna bisa menyimpan pengaturan di server
-- (sama seperti owner_settings untuk akun owner).
--
-- CARA MENJALANKAN:
--   A. Supabase Dashboard > SQL Editor > New query > tempel file ini > Run
--   B. Dibuat oleh script PHP memakai kredensial role postgres (admin).
--   Role aplikasi (arena_app) TIDAK bisa menjalankan DDL, jadi wajib admin.
--
-- Script aman dijalankan berulang kali (IF NOT EXISTS / DROP POLICY IF EXISTS).
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 1. Tabel preferensi notifikasi pengguna
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS user_settings (
    user_id            BIGINT PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    notifikasi_pesanan BOOLEAN NOT NULL DEFAULT TRUE,   -- kabari pesanan yang akan datang
    notifikasi_email   BOOLEAN NOT NULL DEFAULT FALSE,  -- ringkasan harian via email
    notifikasi_wa      BOOLEAN NOT NULL DEFAULT TRUE,   -- pembaruan pesanan via WhatsApp
    updated_at         TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Hak akses untuk role aplikasi (pola sama seperti owner_settings)
GRANT SELECT, INSERT, UPDATE, DELETE ON user_settings TO arena_app;

-- Row Level Security: aktif + policy untuk arena_app (sama seperti tabel lain)
ALTER TABLE user_settings ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS app_access_user_settings ON user_settings;
CREATE POLICY app_access_user_settings
    ON user_settings
    FOR ALL
    TO arena_app
    USING (true)
    WITH CHECK (true);

-- ---------------------------------------------------------------------------
-- 2. VERIFIKASI (jalankan terpisah bila ingin melihat hasilnya)
-- ---------------------------------------------------------------------------
-- SELECT table_name FROM information_schema.tables WHERE table_name = 'user_settings';
--
-- SELECT column_name, data_type FROM information_schema.columns
--  WHERE table_name = 'user_settings' ORDER BY ordinal_position;
--
-- Hak akses & RLS:
-- SELECT policyname, cmd FROM pg_policies WHERE tablename = 'user_settings';
