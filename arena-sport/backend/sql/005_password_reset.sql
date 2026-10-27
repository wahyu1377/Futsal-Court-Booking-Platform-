-- ============================================================================
-- 005_password_reset.sql  (PostgreSQL / Supabase)
--
-- Menambahkan tabel untuk fitur "Lupa Kata Sandi":
--   - Menyimpan token reset dalam bentuk hash (token asli hanya ada di email)
--   - Satu tabel untuk dua jenis akun (users & owners), dibedakan kolom `role`
--
-- CARA MENJALANKAN:
--   A. Supabase Dashboard > SQL Editor > New query > tempel file ini > Run
--   B. Dibuat oleh script PHP memakai kredensial role postgres (admin).
--   Role aplikasi (arena_app) TIDAK bisa menjalankan DDL, jadi wajib admin.
--
-- Script aman dijalankan berulang kali (IF NOT EXISTS / DROP POLICY IF EXISTS).
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 1. Tabel token reset
--
-- token_hash  : SHA-256 dari token acak. Token asli (64 hex) hanya disimpan di
--               email, jadi bocornya isi tabel tidak cukup untuk login.
-- role        : 'user' atau 'owner' (akun bisa berada di salah satu dari dua
--               tabel, jadi tidak ada foreign key ke users/owners).
-- expires_at  : diisi dari PHP (waktu lokal Asia/Jakarta), bukan dari NOW(),
--               supaya perhitungan sama persis di PostgreSQL dan MySQL.
-- used_at     : diisi saat token dipakai. NULL = token masih berlaku.
-- created_at  : diisi dari PHP juga, dipakai untuk cooldown & rate limit.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS password_reset (
    id          BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    token_hash  VARCHAR(64)  NOT NULL UNIQUE,
    role        VARCHAR(10)  NOT NULL,
    account_id  BIGINT       NOT NULL,
    email_kirim VARCHAR(150) NOT NULL,   -- email tujuan, disimpan saat request
    ip_address  VARCHAR(45)  DEFAULT NULL,
    expires_at  TIMESTAMP    NOT NULL,
    used_at     TIMESTAMP    DEFAULT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------------------
-- 2. Index pendukung
--    - token_hash: UNIQUE sudah membuat index, ini untuk mempercepat lookup
--    - (role, account_id): untuk mencari token milik satu akun
--    - (ip_address): untuk rate limit per IP
-- ---------------------------------------------------------------------------
CREATE INDEX IF NOT EXISTS idx_password_reset_akun ON password_reset (role, account_id);
CREATE INDEX IF NOT EXISTS idx_password_reset_ip    ON password_reset (ip_address);

-- ---------------------------------------------------------------------------
-- 3. Hak akses untuk role aplikasi (pola sama seperti owner_settings)
-- ---------------------------------------------------------------------------
GRANT SELECT, INSERT, UPDATE, DELETE ON password_reset TO arena_app;

-- Sequence identity dipakai saat INSERT, jadi arena_app butuh hak atasnya.
-- (Sequence internal tabel TIDAK otomatis boleh dipakai role lain.)
DO $$
BEGIN
    IF EXISTS (
        SELECT 1
          FROM pg_class c
          JOIN pg_namespace n ON n.oid = c.relnamespace
         WHERE c.relname = 'password_reset_id_seq'
           AND n.nspname = 'public'
    ) THEN
        EXECUTE 'GRANT USAGE, SELECT ON SEQUENCE public.password_reset_id_seq TO arena_app';
    END IF;
END $$;

-- ---------------------------------------------------------------------------
-- 4. Row Level Security: aktif + policy untuk arena_app
-- ---------------------------------------------------------------------------
ALTER TABLE password_reset ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS app_access_password_reset ON password_reset;
CREATE POLICY app_access_password_reset
    ON password_reset
    FOR ALL
    TO arena_app
    USING (true)
    WITH CHECK (true);

-- ---------------------------------------------------------------------------
-- 5. VERIFIKASI (jalankan terpisah bila ingin melihat hasilnya)
-- ---------------------------------------------------------------------------
-- SELECT table_name FROM information_schema.tables WHERE table_name = 'password_reset';
--
-- SELECT column_name, data_type FROM information_schema.columns
--  WHERE table_name = 'password_reset' ORDER BY ordinal_position;
--
-- Hak akses & RLS:
-- SELECT policyname, cmd FROM pg_policies WHERE tablename = 'password_reset';
