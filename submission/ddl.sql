-- ============================================================
-- DDL — Used Laptop Deal Evaluator
-- Atalariq Barra Hadinugraha
-- UAS PPW1 + Praktikum Basis Data, 2026
-- ============================================================
-- Mencakup: CREATE TABLE, STORED FUNCTION, TRIGGER, VIEW
-- ============================================================

DROP DATABASE IF EXISTS laptop_evaluator;
CREATE DATABASE laptop_evaluator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE laptop_evaluator;

-- ============================================================
-- TABEL
-- ============================================================

-- T1. users — akun admin aplikasi
CREATE TABLE users (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    username   VARCHAR(50) UNIQUE NOT NULL,
    password   VARCHAR(255) NOT NULL,           -- bcrypt hash
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- T2. brands — merek laptop
CREATE TABLE brands (
    id    INT PRIMARY KEY AUTO_INCREMENT,
    name  VARCHAR(50) UNIQUE NOT NULL,
    notes TEXT
);

-- T3. laptops — data fisik laptop bekas
CREATE TABLE laptops (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    brand_id     INT NOT NULL,
    model        VARCHAR(100) NOT NULL,
    release_year YEAR NOT NULL,
    cpu_tier     TINYINT NOT NULL  COMMENT '1=low (Celeron/i3), 2=mid (i5/R5), 3=high (i7+/R7+)',
    ram_gb       TINYINT NOT NULL,
    storage_gb   SMALLINT NOT NULL,
    `condition`  TINYINT NOT NULL  COMMENT '1=buruk, 2=cukup, 3=baik, 4=mulus',
    has_warranty BOOLEAN DEFAULT FALSE,
    price        INT NOT NULL      COMMENT 'ribuan IDR — e.g. 3500 = Rp 3.500.000',
    image_path   VARCHAR(255) NULL,
    created_by   INT NULL,
    updated_by   INT NULL,
    listed_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES brands(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- T4. scoring_rules — referensi poin per faktor (ditampilkan di UI)
CREATE TABLE scoring_rules (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    factor      VARCHAR(50) NOT NULL,
    value_key   VARCHAR(50) NOT NULL,
    points      TINYINT NOT NULL,
    description VARCHAR(100)
);

-- T5. use_cases — kebutuhan minimum per jenis pemakaian
CREATE TABLE use_cases (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    name         VARCHAR(50) NOT NULL,
    min_ram_gb   TINYINT NOT NULL,
    min_cpu_tier TINYINT NOT NULL,
    min_storage  SMALLINT NOT NULL
);

-- T6. evaluations — skor & verdict hasil kalkulasi (diisi otomatis oleh trigger)
CREATE TABLE evaluations (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    laptop_id    INT UNIQUE NOT NULL,
    value_score  TINYINT NOT NULL COMMENT '0–100',
    verdict      VARCHAR(20) NOT NULL,
    evaluated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (laptop_id) REFERENCES laptops(id) ON DELETE CASCADE
);

-- ============================================================
-- STORED FUNCTIONS
-- ============================================================

DELIMITER //

-- F1. fn_get_verdict — konversi skor 0-100 ke label verdict
CREATE FUNCTION fn_get_verdict(score TINYINT)
RETURNS VARCHAR(20) DETERMINISTIC
BEGIN
    IF score >= 75 THEN RETURN 'Great Deal';
    ELSEIF score >= 55 THEN RETURN 'Fair';
    ELSEIF score >= 35 THEN RETURN 'Overpriced';
    ELSE RETURN 'Avoid';
    END IF;
END //

-- F2. fn_calculate_score — kalkulasi skor value-for-money laptop bekas
--   Faktor: CPU tier (max 30) + RAM (max 25) + Storage (max 15)
--         + Kondisi (max 15) + Garansi (max 5) + Age penalty (-5/-2/0)
--         + Price ratio (dynamic, max 15)
--   Total maksimum = 110, di-clamp ke 100.
CREATE FUNCTION fn_calculate_score(
    p_cpu_tier     TINYINT,
    p_ram_gb       TINYINT,
    p_storage_gb   SMALLINT,
    p_condition    TINYINT,
    p_warranty     BOOLEAN,
    p_release_year YEAR,
    p_price        INT
)
RETURNS TINYINT DETERMINISTIC
BEGIN
    DECLARE score      INT DEFAULT 0;
    DECLARE spec_score INT DEFAULT 0;   -- skor sebelum komponen harga

    -- CPU tier: 1→10, 2→20, 3→30
    SET score = score + (p_cpu_tier * 10);

    -- RAM
    IF p_ram_gb >= 32 THEN SET score = score + 25;
    ELSEIF p_ram_gb >= 16 THEN SET score = score + 20;
    ELSEIF p_ram_gb >= 8  THEN SET score = score + 10;
    ELSE SET score = score + 5;
    END IF;

    -- Storage
    IF p_storage_gb >= 1000 THEN SET score = score + 15;
    ELSEIF p_storage_gb >= 512 THEN SET score = score + 12;
    ELSEIF p_storage_gb >= 256 THEN SET score = score + 7;
    ELSE SET score = score + 3;
    END IF;

    -- Kondisi: (1→0), (2→5), (3→10), (4→15)
    SET score = score + ((p_condition - 1) * 5);

    -- Garansi
    IF p_warranty THEN SET score = score + 5; END IF;

    -- Age penalty
    IF p_release_year < 2018 THEN SET score = score - 5;
    ELSEIF p_release_year < 2020 THEN SET score = score - 2;
    END IF;

    -- Price score: dinamis berdasarkan rasio harga-per-poin-spek
    --   ratio = price (ribuan IDR) / spec_score
    SET spec_score = score;
    IF spec_score > 0 THEN
        IF (p_price / spec_score) <= 50  THEN SET score = score + 15;
        ELSEIF (p_price / spec_score) <= 85  THEN SET score = score + 10;
        ELSEIF (p_price / spec_score) <= 120 THEN SET score = score + 5;
        END IF;
    END IF;

    RETURN LEAST(GREATEST(score, 0), 100);
END //

-- ============================================================
-- TRIGGERS
-- ============================================================

-- TRG1. Auto-insert evaluasi saat laptop baru ditambahkan
CREATE TRIGGER trg_after_laptop_insert
AFTER INSERT ON laptops
FOR EACH ROW
BEGIN
    DECLARE v_score TINYINT;
    SET v_score = fn_calculate_score(
        NEW.cpu_tier, NEW.ram_gb, NEW.storage_gb,
        NEW.`condition`, NEW.has_warranty, NEW.release_year, NEW.price
    );
    INSERT INTO evaluations (laptop_id, value_score, verdict)
    VALUES (NEW.id, v_score, fn_get_verdict(v_score));
END //

-- TRG2. Auto-update evaluasi saat data laptop diubah
CREATE TRIGGER trg_after_laptop_update
AFTER UPDATE ON laptops
FOR EACH ROW
BEGIN
    DECLARE v_score TINYINT;
    SET v_score = fn_calculate_score(
        NEW.cpu_tier, NEW.ram_gb, NEW.storage_gb,
        NEW.`condition`, NEW.has_warranty, NEW.release_year, NEW.price
    );
    UPDATE evaluations
    SET value_score  = v_score,
        verdict      = fn_get_verdict(v_score),
        evaluated_at = NOW()
    WHERE laptop_id = NEW.id;
END //

DELIMITER ;

-- ============================================================
-- VIEWS
-- ============================================================

-- V1. v_laptop_evaluations — JOIN utama: laptops + brands + evaluations + creator
CREATE VIEW v_laptop_evaluations AS
SELECT l.id, l.model, b.name AS brand, l.price,
       l.cpu_tier, l.ram_gb, l.storage_gb, l.`condition`, l.has_warranty,
       l.release_year, l.listed_at, l.brand_id, l.image_path,
       l.created_by, usr.username AS created_by_name,
       e.value_score, e.verdict, e.evaluated_at
FROM laptops l
JOIN brands b ON l.brand_id = b.id
JOIN evaluations e ON e.laptop_id = l.id
LEFT JOIN users usr ON l.created_by = usr.id;

-- V2. v_best_value_laptops — filter hanya laptop worth buying (Great Deal / Fair)
CREATE VIEW v_best_value_laptops AS
SELECT * FROM v_laptop_evaluations
WHERE verdict IN ('Great Deal', 'Fair')
ORDER BY value_score DESC;
