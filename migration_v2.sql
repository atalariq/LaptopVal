-- ============================================================
-- Migration v2: dynamic price scoring + image_path column
-- Apply to existing containers WITHOUT rebuilding (no data loss).
--
-- Via phpMyAdmin: paste into SQL tab and run.
-- Via CLI inside container:
--   docker exec -i laptop_db mysql -uroot -proot laptop_evaluator < migration_v2.sql
-- ============================================================

USE laptop_evaluator;

-- 1. Add image_path column (IF NOT EXISTS requires MySQL 8.0)
ALTER TABLE laptops
    ADD COLUMN IF NOT EXISTS image_path VARCHAR(255) NULL AFTER price;

-- 2. Replace fn_calculate_score with dynamic price version
DROP FUNCTION IF EXISTS fn_calculate_score;

DELIMITER //
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

    SET score = score + (p_cpu_tier * 10);

    IF p_ram_gb >= 32 THEN SET score = score + 25;
    ELSEIF p_ram_gb >= 16 THEN SET score = score + 20;
    ELSEIF p_ram_gb >= 8 THEN SET score = score + 10;
    ELSE SET score = score + 5;
    END IF;

    IF p_storage_gb >= 1000 THEN SET score = score + 15;
    ELSEIF p_storage_gb >= 512 THEN SET score = score + 12;
    ELSEIF p_storage_gb >= 256 THEN SET score = score + 7;
    ELSE SET score = score + 3;
    END IF;

    SET score = score + ((p_condition - 1) * 5);

    IF p_warranty THEN SET score = score + 5; END IF;

    IF p_release_year < 2018 THEN SET score = score - 5;
    ELSEIF p_release_year < 2020 THEN SET score = score - 2;
    END IF;

    SET spec_score = score;
    IF spec_score > 0 THEN
        IF (p_price / spec_score) <= 50 THEN SET score = score + 15;
        ELSEIF (p_price / spec_score) <= 85 THEN SET score = score + 10;
        ELSEIF (p_price / spec_score) <= 120 THEN SET score = score + 5;
        END IF;
    END IF;

    RETURN LEAST(GREATEST(score, 0), 100);
END //
DELIMITER ;

-- 3. Recreate views (add image_path to v_laptop_evaluations)
DROP VIEW IF EXISTS v_best_value_laptops;
DROP VIEW IF EXISTS v_laptop_evaluations;

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

-- 4. Update scoring_rules price entries
DELETE FROM scoring_rules WHERE factor = 'price_score';
INSERT INTO scoring_rules (factor, value_key, points, description) VALUES
('price_score', 'rasio≤50',   15, 'Harga sangat murah untuk speknya'),
('price_score', 'rasio≤85',   10, 'Harga sesuai dengan speknya'),
('price_score', 'rasio≤120',   5, 'Harga sedikit mahal untuk speknya'),
('price_score', 'rasio>120',   0, 'Harga mahal untuk speknya');

-- 5. Recalculate all evaluations with new formula
UPDATE evaluations e
JOIN laptops l ON e.laptop_id = l.id
SET
    e.value_score  = fn_calculate_score(
                         l.cpu_tier, l.ram_gb, l.storage_gb,
                         l.`condition`, l.has_warranty, l.release_year, l.price),
    e.verdict      = fn_get_verdict(
                         fn_calculate_score(
                             l.cpu_tier, l.ram_gb, l.storage_gb,
                             l.`condition`, l.has_warranty, l.release_year, l.price)),
    e.evaluated_at = NOW();
