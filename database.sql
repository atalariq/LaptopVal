-- ============================================================
-- Used Laptop Deal Evaluator — Database Setup
-- MySQL 8.0+
-- ============================================================

DROP DATABASE IF EXISTS laptop_evaluator;
CREATE DATABASE laptop_evaluator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE laptop_evaluator;

-- ============================================================
-- TABLES
-- ============================================================

CREATE TABLE users (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    username   VARCHAR(50) UNIQUE NOT NULL,
    password   VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE brands (
    id    INT PRIMARY KEY AUTO_INCREMENT,
    name  VARCHAR(50) UNIQUE NOT NULL,
    notes TEXT
);

CREATE TABLE laptops (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    brand_id     INT NOT NULL,
    model        VARCHAR(100) NOT NULL,
    release_year YEAR NOT NULL,
    cpu_tier     TINYINT NOT NULL COMMENT '1=low, 2=mid, 3=high',
    ram_gb       TINYINT NOT NULL,
    storage_gb   SMALLINT NOT NULL,
    `condition`  TINYINT NOT NULL COMMENT '1=buruk, 2=cukup, 3=baik, 4=mulus',
    has_warranty BOOLEAN DEFAULT FALSE,
    price        INT NOT NULL COMMENT 'ribuan IDR, e.g. 3500 = Rp3.5jt',
    image_path   VARCHAR(255) NULL,
    listed_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES brands(id)
);

CREATE TABLE scoring_rules (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    factor      VARCHAR(50) NOT NULL,
    value_key   VARCHAR(50) NOT NULL,
    points      TINYINT NOT NULL,
    description VARCHAR(100)
);

CREATE TABLE use_cases (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    name         VARCHAR(50) NOT NULL,
    min_ram_gb   TINYINT NOT NULL,
    min_cpu_tier TINYINT NOT NULL,
    min_storage  SMALLINT NOT NULL
);

CREATE TABLE evaluations (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    laptop_id    INT UNIQUE NOT NULL,
    value_score  TINYINT NOT NULL COMMENT '0-100',
    verdict      VARCHAR(20) NOT NULL,
    evaluated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (laptop_id) REFERENCES laptops(id) ON DELETE CASCADE
);

-- ============================================================
-- FUNCTIONS
-- ============================================================

DELIMITER //

CREATE FUNCTION fn_get_verdict(score TINYINT)
RETURNS VARCHAR(20) DETERMINISTIC
BEGIN
    IF score >= 75 THEN RETURN 'Great Deal';
    ELSEIF score >= 55 THEN RETURN 'Fair';
    ELSEIF score >= 35 THEN RETURN 'Overpriced';
    ELSE RETURN 'Avoid';
    END IF;
END //

CREATE FUNCTION fn_calculate_score(
    p_cpu_tier    TINYINT,
    p_ram_gb      TINYINT,
    p_storage_gb  SMALLINT,
    p_condition   TINYINT,
    p_warranty    BOOLEAN,
    p_release_year YEAR,
    p_price       INT
)
RETURNS TINYINT DETERMINISTIC
BEGIN
    DECLARE score      INT DEFAULT 0;
    DECLARE spec_score INT DEFAULT 0;

    -- CPU tier: 1=10, 2=20, 3=30
    SET score = score + (p_cpu_tier * 10);

    -- RAM
    IF p_ram_gb >= 32 THEN SET score = score + 25;
    ELSEIF p_ram_gb >= 16 THEN SET score = score + 20;
    ELSEIF p_ram_gb >= 8 THEN SET score = score + 10;
    ELSE SET score = score + 5;
    END IF;

    -- Storage
    IF p_storage_gb >= 1000 THEN SET score = score + 15;
    ELSEIF p_storage_gb >= 512 THEN SET score = score + 12;
    ELSEIF p_storage_gb >= 256 THEN SET score = score + 7;
    ELSE SET score = score + 3;
    END IF;

    -- Condition: (1=0, 2=5, 3=10, 4=15)
    SET score = score + ((p_condition - 1) * 5);

    -- Warranty bonus
    IF p_warranty THEN SET score = score + 5; END IF;

    -- Age penalty
    IF p_release_year < 2018 THEN SET score = score - 5;
    ELSEIF p_release_year < 2020 THEN SET score = score - 2;
    END IF;

    -- Price score: dynamic based on price-per-spec-point ratio
    -- ratio = price (ribuan IDR) / spec_score
    SET spec_score = score;
    IF spec_score > 0 THEN
        IF (p_price / spec_score) <= 50 THEN SET score = score + 15;
        ELSEIF (p_price / spec_score) <= 85 THEN SET score = score + 10;
        ELSEIF (p_price / spec_score) <= 120 THEN SET score = score + 5;
        END IF;
    END IF;

    RETURN LEAST(GREATEST(score, 0), 100);
END //

-- ============================================================
-- TRIGGERS
-- ============================================================

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

CREATE VIEW v_laptop_evaluations AS
SELECT l.id, l.model, b.name AS brand, l.price,
       l.cpu_tier, l.ram_gb, l.storage_gb, l.`condition`, l.has_warranty,
       l.release_year, l.listed_at, l.brand_id, l.image_path,
       e.value_score, e.verdict, e.evaluated_at
FROM laptops l
JOIN brands b ON l.brand_id = b.id
JOIN evaluations e ON e.laptop_id = l.id;

CREATE VIEW v_best_value_laptops AS
SELECT * FROM v_laptop_evaluations
WHERE verdict IN ('Great Deal', 'Fair')
ORDER BY value_score DESC;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Admin user (password: admin123)
INSERT INTO users (username, password) VALUES
('admin', '$2y$12$P/XQAk37eP4NewTRfefQPO19jg1RtDl5d7y/JozhvQ3jvupiIbYZS');

-- Brands
INSERT INTO brands (name, notes) VALUES
('Lenovo',   'ThinkPad series populer untuk bekas'),
('Asus',     'ROG dan VivoBook'),
('Acer',     'Aspire dan Swift'),
('HP',       'EliteBook dan Pavilion'),
('Dell',     'Latitude dan Inspiron'),
('Apple',    'MacBook Air dan Pro'),
('Toshiba',  'Dynabook, banyak unit ex-Jepang');

-- Use cases
INSERT INTO use_cases (name, min_ram_gb, min_cpu_tier, min_storage) VALUES
('Tugas Kuliah',   4,  1, 256),
('Office / Admin',  8,  1, 256),
('Edit Video',     16,  2, 512),
('Programming',     8,  2, 256),
('Desain Grafis',  16,  2, 512);

-- Scoring rules (reference data, ditampilkan di UI untuk transparansi)
INSERT INTO scoring_rules (factor, value_key, points, description) VALUES
-- CPU
('cpu_tier', '1', 10, 'Low-end CPU (Celeron, Pentium, i3 lama)'),
('cpu_tier', '2', 20, 'Mid-range CPU (i5, Ryzen 5)'),
('cpu_tier', '3', 30, 'High-end CPU (i7+, Ryzen 7+)'),
-- RAM
('ram_gb', '<8',  5,  'RAM di bawah 8 GB'),
('ram_gb', '8',   10, 'RAM 8 GB'),
('ram_gb', '16',  20, 'RAM 16 GB'),
('ram_gb', '>=32', 25, 'RAM 32 GB atau lebih'),
-- Storage
('storage_gb', '<256',  3,  'Storage di bawah 256 GB'),
('storage_gb', '256',   7,  'Storage 256 GB'),
('storage_gb', '512',   12, 'Storage 512 GB'),
('storage_gb', '>=1000', 15, 'Storage 1 TB atau lebih'),
-- Condition
('condition', '1', 0,  'Buruk — banyak lecet/dent, performa menurun'),
('condition', '2', 5,  'Cukup — ada bekas pemakaian wajar'),
('condition', '3', 10, 'Baik — minor scratch, fungsi normal'),
('condition', '4', 15, 'Mulus — hampir seperti baru'),
-- Warranty
('has_warranty', 'true',  5, 'Masih ada garansi'),
('has_warranty', 'false', 0, 'Tanpa garansi'),
-- Age
('release_year', '<2018', -5, 'Laptop keluaran sebelum 2018'),
('release_year', '<2020', -2, 'Laptop keluaran 2018-2019'),
('release_year', '>=2020', 0, 'Laptop keluaran 2020 ke atas'),
-- Price (neutral)
('price_score', 'rasio≤50',   15, 'Harga sangat murah untuk speknya'),
('price_score', 'rasio≤85',   10, 'Harga sesuai dengan speknya'),
('price_score', 'rasio≤120',   5, 'Harga sedikit mahal untuk speknya'),
('price_score', 'rasio>120',   0, 'Harga mahal untuk speknya');

-- Sample laptops (evaluations auto-generated by trigger)
INSERT INTO laptops (brand_id, model, release_year, cpu_tier, ram_gb, storage_gb, `condition`, has_warranty, price) VALUES
(1, 'ThinkPad X260',        2017, 2, 8,  256,  3, FALSE, 2800),
(1, 'ThinkPad T480',        2019, 2, 16, 512,  4, FALSE, 5200),
(2, 'VivoBook S14 S433',    2021, 2, 8,  512,  3, TRUE,  5500),
(3, 'Aspire 5 A515-56',     2021, 1, 4,  256,  2, FALSE, 2500),
(4, 'EliteBook 840 G5',     2018, 2, 16, 256,  3, FALSE, 4000),
(5, 'Latitude 5490',        2018, 2, 8,  256,  2, FALSE, 3200),
(6, 'MacBook Air M1',       2020, 3, 8,  256,  4, TRUE,  8500),
(2, 'ROG Strix G15',        2022, 3, 16, 1000, 3, TRUE,  9000),
(7, 'Dynabook R73/B',       2016, 1, 4,  128,  1, FALSE, 1200),
(4, 'Pavilion 14-dv0514TX', 2021, 2, 8,  512,  3, FALSE, 4500);
