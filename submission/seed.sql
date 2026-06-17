-- ============================================================
-- SEED DATA — Used Laptop Deal Evaluator
-- Atalariq Barra Hadinugraha
-- UAS PPW1 + Praktikum Basis Data, 2026
-- ============================================================
-- Jalankan SETELAH ddl.sql
-- Evaluasi (tabel evaluations) diisi otomatis oleh trigger
-- saat INSERT ke laptops — tidak perlu insert manual.
-- ============================================================

USE laptop_evaluator;

-- ============================================================
-- USERS (admin)
-- ============================================================
-- password: admin123 (bcrypt hash)
INSERT INTO users (username, password) VALUES
('admin', '$2y$12$P/XQAk37eP4NewTRfefQPO19jg1RtDl5d7y/JozhvQ3jvupiIbYZS');

-- ============================================================
-- BRANDS
-- ============================================================
INSERT INTO brands (name, notes) VALUES
('Lenovo',  'ThinkPad series populer untuk laptop bekas'),
('Asus',    'ROG dan VivoBook'),
('Acer',    'Aspire dan Swift'),
('HP',      'EliteBook dan Pavilion'),
('Dell',    'Latitude dan Inspiron'),
('Apple',   'MacBook Air dan Pro'),
('Toshiba', 'Dynabook, banyak unit ex-Jepang');

-- ============================================================
-- USE CASES (kebutuhan minimum per jenis pemakaian)
-- ============================================================
INSERT INTO use_cases (name, min_ram_gb, min_cpu_tier, min_storage) VALUES
('Tugas Kuliah',    4,  1, 256),
('Office / Admin',  8,  1, 256),
('Edit Video',     16,  2, 512),
('Programming',     8,  2, 256),
('Desain Grafis',  16,  2, 512);

-- ============================================================
-- SCORING RULES (referensi poin — ditampilkan di UI)
-- ============================================================
INSERT INTO scoring_rules (factor, value_key, points, description) VALUES
-- CPU Tier (max 30)
('cpu_tier', '1',   10, 'Low-end CPU (Celeron, Pentium, i3 lama)'),
('cpu_tier', '2',   20, 'Mid-range CPU (i5, Ryzen 5)'),
('cpu_tier', '3',   30, 'High-end CPU (i7+, Ryzen 7+)'),
-- RAM (max 25)
('ram_gb',   '<8',   5, 'RAM di bawah 8 GB'),
('ram_gb',   '8',   10, 'RAM 8 GB'),
('ram_gb',   '16',  20, 'RAM 16 GB'),
('ram_gb',   '>=32', 25, 'RAM 32 GB atau lebih'),
-- Storage (max 15)
('storage_gb', '<256',   3, 'Storage di bawah 256 GB'),
('storage_gb', '256',    7, 'Storage 256 GB'),
('storage_gb', '512',   12, 'Storage 512 GB'),
('storage_gb', '>=1000', 15, 'Storage 1 TB atau lebih'),
-- Kondisi (max 15)
('condition', '1',  0, 'Buruk, banyak lecet dan dent, performa ikut turun'),
('condition', '2',  5, 'Cukup, ada bekas pemakaian tapi masih wajar'),
('condition', '3', 10, 'Baik, cuma lecet halus dan semua fungsi normal'),
('condition', '4', 15, 'Mulus, kondisinya hampir kayak baru'),
-- Garansi (max 5)
('has_warranty', 'true',  5, 'Masih ada garansi'),
('has_warranty', 'false', 0, 'Tanpa garansi'),
-- Age penalty
('release_year', '<2018',  -5, 'Laptop keluaran sebelum 2018'),
('release_year', '<2020',  -2, 'Laptop keluaran 2018–2019'),
('release_year', '>=2020',  0, 'Laptop keluaran 2020 ke atas'),
-- Price score (dinamis: rasio harga / spec_score)
('price_score', 'rasio≤50',   15, 'Harga sangat murah untuk speknya'),
('price_score', 'rasio≤85',   10, 'Harga sesuai dengan speknya'),
('price_score', 'rasio≤120',   5, 'Harga sedikit mahal untuk speknya'),
('price_score', 'rasio>120',   0, 'Harga mahal untuk speknya');

-- ============================================================
-- LAPTOPS
-- Evaluasi diisi OTOMATIS oleh trg_after_laptop_insert.
-- ============================================================
INSERT INTO laptops
    (brand_id, model, release_year, cpu_tier, ram_gb, storage_gb, `condition`, has_warranty, price, created_by)
VALUES
--  brand  model                        tahun  cpu  ram  storage  kondisi  garansi  harga(ribu)  creator
    (1,    'ThinkPad X260',             2017,   2,   8,    256,     3,    FALSE,   2800,  1),
    (1,    'ThinkPad T480',             2019,   2,  16,    512,     4,    FALSE,   5200,  1),
    (2,    'VivoBook S14 S433',         2021,   2,   8,    512,     3,    TRUE,    5500,  1),
    (3,    'Aspire 5 A515-56',          2021,   1,   4,    256,     2,    FALSE,   2500,  1),
    (4,    'EliteBook 840 G5',          2018,   2,  16,    256,     3,    FALSE,   4000,  1),
    (5,    'Latitude 5490',             2018,   2,   8,    256,     2,    FALSE,   3200,  1),
    (6,    'MacBook Air M1',            2020,   3,   8,    256,     4,    TRUE,    8500,  1),
    (2,    'ROG Strix G15',             2022,   3,  16,   1000,     3,    TRUE,    9000,  1),
    (7,    'Dynabook R73/B',            2016,   1,   4,    128,     1,    FALSE,   1200,  1),
    (4,    'Pavilion 14-dv0514TX',      2021,   2,   8,    512,     3,    FALSE,   4500,  1);
-- Catatan: 10 laptop di atas otomatis memunculkan 10 baris di tabel evaluations.
