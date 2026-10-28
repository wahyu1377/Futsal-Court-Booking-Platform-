-- ============================================================================
-- 007_gambar_unggahan.sql  (PostgreSQL / Supabase)
--
-- Menyimpan file foto lapangan yang diunggah owner ke dalam database.
--
-- Kenapa perlu tabel ini:
--   Folder frontend/assets/img/lapangan/owner/ sengaja tidak di-commit ke
--   git, sementara database dipakai bersama antar anggota tim. Foto yang
--   diunggah di satu komputer karena itu tidak muncul di server lain ->
--   404 di halaman (terutama di server produksi Wasmer yang Linux).
--
--   Dengan isi file disimpan di database, foto bisa dibaca dari mana saja:
--   laptop anggota tim, server lokal, sampai server produksi.
--
-- CARA MENJALANKAN:
--   A. Supabase Dashboard > SQL Editor > New query > tempel file ini > Run
--   B. Dibuat oleh script PHP memakai kredensial role postgres (admin).
--   Role aplikasi (arena_app) TIDAK bisa menjalankan DDL, jadi wajib admin.
--
-- Script aman dijalankan berulang kali (IF NOT EXISTS / DROP POLICY IF EXISTS).
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 1. Tabel penyimpan gambar unggahan
--    nama  : nama file relatif, sama dengan yang disimpan di lapangan.foto
--            (mis. "owner/20261022-ab12cd34.jpg")
--    data  : isi file dalam bentuk base64 (TEXT)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gambar_unggahan (
    nama      VARCHAR(255) PRIMARY KEY,
    mime      VARCHAR(60)  NOT NULL DEFAULT 'image/jpeg',
    data      TEXT         NOT NULL,
    ukuran    INT          NOT NULL DEFAULT 0,
    dibuat_at TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

-- Hak akses untuk role aplikasi (pola sama seperti tabel lain)
GRANT SELECT, INSERT, UPDATE, DELETE ON gambar_unggahan TO arena_app;

-- Row Level Security: aktif + policy untuk arena_app (sama seperti tabel lain)
ALTER TABLE gambar_unggahan ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS app_access_gambar_unggahan ON gambar_unggahan;
CREATE POLICY app_access_gambar_unggahan
    ON gambar_unggahan
    FOR ALL
    TO arena_app
    USING (true)
    WITH CHECK (true);

-- ---------------------------------------------------------------------------
-- 2. Menyamakan huruf besar/kecil pada data foto yang sudah ada
--    (file di disk bernama "smash-futsal.jpg", sedangkan database sempat
--     menyimpan "smash-Futsal.jpg" -> 404 di server Linux)
-- ---------------------------------------------------------------------------
UPDATE lapangan
   SET foto = LOWER(foto)
 WHERE foto <> LOWER(foto);

-- ---------------------------------------------------------------------------
-- 3. VERIFIKASI (jalankan terpisah bila ingin melihat hasilnya)
--
-- SELECT table_name FROM information_schema.tables WHERE table_name = 'gambar_unggahan';
--
-- SELECT nama, mime, ukuran FROM gambar_unggahan ORDER BY nama;
--
-- Hak akses & RLS:
-- SELECT policyname, cmd FROM pg_policies WHERE tablename = 'gambar_unggahan';
