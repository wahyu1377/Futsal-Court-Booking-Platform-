-- ============================================================================
-- Housekeeping database — Arena Sport
--
-- CARA JALANKAN:
--   Supabase Dashboard > SQL Editor > New query > tempel file ini > Run
--   (SQL Editor jalan sebagai role `postgres`, pemilik tabelnya)
--
-- TUJUAN:
--   Membuang index duplikat pada tabel `lapangan`.
--   Saat ini ada dua index dengan kolom yang sama persis:
--     - idx_lapangan_owner_id   (dipakai — nama konsisten dengan idx_lapangan_aktif/jenis)
--     - lapangan_owner_id_idx   (duplikat — sisa migrasi dari dump MySQL)
--
-- CATATAN:
--   - Statement ini idempoten (aman dijalankan berulang kali).
--   - Tidak menghapus data, hanya index.
--   - Index yang dipertahankan tetap menutup query:
--       SELECT ... FROM lapangan WHERE owner_id = ?
--     Foreign key `lapangan_owner_id_fkey` tidak memerlukan index tambahan.
--   - Role `arena_app` TIDAK bisa menjalankan skrip ini ("must be owner of index").
-- ============================================================================


-- ---------------------------------------------------------------------------
-- LANGKAH 1 — Hapus index duplikat (yang dipertahankan: idx_lapangan_owner_id)
-- ---------------------------------------------------------------------------
DROP INDEX IF EXISTS public.lapangan_owner_id_idx;


-- ---------------------------------------------------------------------------
-- LANGKAH 2 — Pastikan index yang dipakai benar-benar ada
-- ---------------------------------------------------------------------------
CREATE INDEX IF NOT EXISTS idx_lapangan_owner_id ON public.lapangan (owner_id);


-- ---------------------------------------------------------------------------
-- LANGKAH 3 — Pastikan index pendukung filter inti tetap ada
-- ---------------------------------------------------------------------------
CREATE INDEX IF NOT EXISTS idx_lapangan_aktif ON public.lapangan (aktif);
CREATE INDEX IF NOT EXISTS idx_lapangan_jenis ON public.lapangan (jenis);


-- ===========================================================================
-- VERIFIKASI — jalankan terpisah, hasil harus hanya menunjukkan 4 index:
--   idx_lapangan_aktif, idx_lapangan_jenis, idx_lapangan_owner_id, lapangan_pkey
-- ===========================================================================
-- SELECT indexname, indexdef FROM pg_indexes
--  WHERE schemaname = 'public' AND tablename = 'lapangan'
--  ORDER BY indexname;

-- Cek cepat: tidak boleh ada dua index dengan kolom yang sama
-- SELECT indexdef, COUNT(*) FROM pg_indexes
--  WHERE schemaname = 'public' AND tablename = 'lapangan'
--  GROUP BY indexdef HAVING COUNT(*) > 1;
