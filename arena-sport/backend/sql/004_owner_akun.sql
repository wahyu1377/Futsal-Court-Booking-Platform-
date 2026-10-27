-- ============================================================================
-- 004_owner_akun.sql
-- Menambahkan 2 kemampuan untuk "Halaman Akun Owner":
--   1. owners.avatar      -> foto profil owner (dipakai akun.php & edit-profil.php)
--   2. owner_settings     -> preferensi notifikasi owner (dipakai setelan.php)
--
-- CARA MENJALANKAN (pilih salah satu):
--   A. Salin seluruh isi file ini ke Supabase Dashboard > SQL Editor > New query
--      > Run. Tidak perlu memberikan password ke siapa pun.
--   B. Dibuat oleh script PHP memakai kredensial role postgres (admin).
--      Role aplikasi (arena_app) TIDAK bisa menjalankan DDL, jadi wajib admin.
--
-- Script aman dijalankan berulang kali (IF NOT EXISTS / DROP POLICY IF EXISTS).
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 1. Kolom foto profil owner
--    Hak akses arena_app sudah ada di level tabel (INSERT/UPDATE), jadi kolom
--    baru otomatis bisa ditulis tanpa GRANT tambahan.
-- ---------------------------------------------------------------------------
ALTER TABLE owners
    ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) DEFAULT NULL;

-- ---------------------------------------------------------------------------
-- 2. Tabel preferensi notifikasi owner
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS owner_settings (
    owner_id           BIGINT PRIMARY KEY REFERENCES owners(id) ON DELETE CASCADE,
    notifikasi_pesanan BOOLEAN NOT NULL DEFAULT TRUE,   -- ada pesanan masuk baru
    notifikasi_email   BOOLEAN NOT NULL DEFAULT FALSE,  -- ringkasan harian via email
    notifikasi_wa      BOOLEAN NOT NULL DEFAULT TRUE,   -- pembaruan pesanan via WhatsApp
    updated_at         TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Hak akses untuk role aplikasi (pola sama seperti tabel owners / favorit)
GRANT SELECT, INSERT, UPDATE, DELETE ON owner_settings TO arena_app;

-- Row Level Security: aktif + policy untuk arena_app (sama seperti tabel lain)
ALTER TABLE owner_settings ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS app_access_owner_settings ON owner_settings;
CREATE POLICY app_access_owner_settings
    ON owner_settings
    FOR ALL
    TO arena_app
    USING (true)
    WITH CHECK (true);

-- ---------------------------------------------------------------------------
-- 3. Verifikasi (jalankan terpisah bila ingin melihat hasilnya)
-- ---------------------------------------------------------------------------
-- SELECT column_name FROM information_schema.columns
--  WHERE table_name = 'owners' AND column_name = 'avatar';
--
-- SELECT table_name FROM information_schema.tables
--  WHERE table_name = 'owner_settings';
