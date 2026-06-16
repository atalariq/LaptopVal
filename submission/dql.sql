-- ============================================================
-- DQL — Used Laptop Deal Evaluator
-- Atalariq Barra Hadinugraha
-- UAS PPW1 + Praktikum Basis Data, 2026
-- ============================================================
-- Berisi query SELECT yang dipakai aplikasi, dikelompokkan
-- berdasarkan halaman/fitur. Jalankan setelah ddl.sql + seed.sql.
-- ============================================================

USE laptop_evaluator;

-- ============================================================
-- Q1 — Semua laptop (halaman index.php, tampilan default)
-- JOIN: laptops + brands + evaluations, urut skor tertinggi
-- ============================================================
SELECT
    l.id,
    l.model,
    b.name          AS brand,
    l.price,
    l.ram_gb,
    l.storage_gb,
    l.`condition`,
    l.has_warranty,
    l.cpu_tier,
    l.release_year,
    l.image_path,
    e.value_score,
    e.verdict
FROM laptops l
JOIN brands b      ON l.brand_id  = b.id
JOIN evaluations e ON e.laptop_id = l.id
ORDER BY e.value_score DESC;

-- ============================================================
-- Q2 — Laptop sesuai use case "Programming"
-- JOIN 4 tabel: tambah use_cases; filter dengan kriteria minimum
-- ============================================================
SELECT
    l.id,
    l.model,
    b.name          AS brand,
    l.price,
    l.ram_gb,
    l.storage_gb,
    l.`condition`,
    l.has_warranty,
    l.cpu_tier,
    l.release_year,
    l.image_path,
    e.value_score,
    e.verdict
FROM laptops l
JOIN brands b      ON l.brand_id  = b.id
JOIN evaluations e ON e.laptop_id = l.id
JOIN use_cases u   ON u.id        = (SELECT id FROM use_cases WHERE name = 'Programming')
WHERE l.ram_gb     >= u.min_ram_gb
  AND l.cpu_tier   >= u.min_cpu_tier
  AND l.storage_gb >= u.min_storage
ORDER BY e.value_score DESC;

-- ============================================================
-- Q3 — Rata-rata skor per brand (admin/dashboard.php)
-- GROUP BY + HAVING + ROUND + agregasi COUNT
-- ============================================================
SELECT
    b.name                          AS brand,
    ROUND(AVG(e.value_score), 1)    AS avg_score,
    COUNT(*)                        AS total_listings
FROM brands b
JOIN laptops l     ON l.brand_id  = b.id
JOIN evaluations e ON e.laptop_id = l.id
GROUP BY b.id, b.name
HAVING COUNT(*) > 0
ORDER BY avg_score DESC;

-- ============================================================
-- Q4 — Detail satu laptop (detail.php?id=X)
-- Membawa semua kolom yang dibutuhkan untuk breakdown skor
-- ============================================================
SELECT
    l.id,
    l.model,
    b.name          AS brand,
    l.brand_id,
    l.price,
    l.ram_gb,
    l.storage_gb,
    l.`condition`,
    l.has_warranty,
    l.cpu_tier,
    l.release_year,
    l.listed_at,
    l.image_path,
    e.value_score,
    e.verdict,
    e.evaluated_at
FROM laptops l
JOIN brands b      ON l.brand_id  = b.id
JOIN evaluations e ON e.laptop_id = l.id
WHERE l.id = 2;     -- ganti angka untuk laptop lain

-- ============================================================
-- Q5 — Top 5 laptop best value (admin/dashboard.php)
-- Menggunakan view v_best_value_laptops
-- ============================================================
SELECT model, brand, value_score, verdict, price
FROM v_best_value_laptops
LIMIT 5;

-- ============================================================
-- Q6 — Statistik dashboard (3 stat card)
-- ============================================================
-- Total laptop
SELECT COUNT(*) AS total_laptops FROM laptops;

-- Total brand yang punya listing
SELECT COUNT(DISTINCT brand_id) AS total_brands FROM laptops;

-- Rata-rata skor seluruh laptop
SELECT ROUND(AVG(value_score), 1) AS avg_score_all FROM evaluations;

-- ============================================================
-- Q7 — Scoring rules (admin/scoring_rules.php)
-- Dikelompokkan per faktor, urut poin tertinggi dulu
-- ============================================================
SELECT
    factor,
    value_key,
    points,
    description
FROM scoring_rules
ORDER BY factor, points DESC;

-- ============================================================
-- Q8 — Demo stored function: hitung skor satu laptop
-- Contoh: CPU=High, RAM=16, Storage=512, Kondisi=Baik,
--         Garansi=Ya, Tahun=2023, Harga=Rp6.000.000
-- ============================================================
SELECT fn_calculate_score(3, 16, 512, 3, TRUE, 2023, 6000) AS score_preview,
       fn_get_verdict(
           fn_calculate_score(3, 16, 512, 3, TRUE, 2023, 6000)
       )                                                    AS verdict_preview;

-- ============================================================
-- Q9 — Distribusi verdict (analitik)
-- Berapa laptop per kategori Great Deal / Fair / Overpriced / Avoid
-- ============================================================
SELECT
    verdict,
    COUNT(*)                                              AS jumlah,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM evaluations), 1) AS persen
FROM evaluations
GROUP BY verdict
ORDER BY jumlah DESC;

-- ============================================================
-- Q10 — Laptop paling worth it per brand
-- Subquery: MAX(value_score) per brand
-- ============================================================
SELECT
    b.name      AS brand,
    l.model,
    e.value_score,
    e.verdict,
    l.price
FROM laptops l
JOIN brands b      ON l.brand_id  = b.id
JOIN evaluations e ON e.laptop_id = l.id
WHERE e.value_score = (
    SELECT MAX(e2.value_score)
    FROM laptops l2
    JOIN evaluations e2 ON e2.laptop_id = l2.id
    WHERE l2.brand_id = l.brand_id
)
ORDER BY e.value_score DESC;
