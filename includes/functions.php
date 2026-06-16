<?php
/**
 * Helper functions — no scoring logic here, that lives in MySQL.
 */

// ── Output safety ────────────────────────────────────────────────────────────
function h(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// ── Flash messages ───────────────────────────────────────────────────────────
function set_flash(string $msg, string $type = 'success'): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

function get_flash(): array
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

// ── Price formatting ─────────────────────────────────────────────────────────
function format_price(int $price): string
{
    // price stored in thousands (e.g. 3500 = Rp 3.500.000)
    return 'Rp ' . number_format($price * 1000, 0, ',', '.');
}

// ── Verdict badge class ──────────────────────────────────────────────────────
function verdict_class(string $verdict): string
{
    return match($verdict) {
        'Great Deal' => 'success',
        'Fair'       => 'info',
        'Overpriced' => 'warning',
        'Avoid'      => 'danger',
        default      => 'secondary',
    };
}

// ── Condition label ──────────────────────────────────────────────────────────
function condition_label(int $cond): string
{
    return match($cond) {
        1 => 'Buruk',
        2 => 'Cukup',
        3 => 'Baik',
        4 => 'Mulus',
        default => '-',
    };
}

// ── CPU tier label ───────────────────────────────────────────────────────────
function cpu_label(int $tier): string
{
    return match($tier) {
        1 => 'Low',
        2 => 'Mid',
        3 => 'High',
        default => '-',
    };
}

// ── Queries ──────────────────────────────────────────────────────────────────

/**
 * Q1: All laptops with brand + evaluation (JOIN).
 * Optional filter by use_case_id (Q2).
 */
function get_laptops(mysqli $conn, int $use_case_id = 0): array
{
    if ($use_case_id > 0) {
        // Q2: JOIN + filter by use case requirements
        $sql = "SELECT l.id, l.model, b.name AS brand, l.price, l.ram_gb,
                       l.storage_gb, l.`condition`, l.has_warranty,
                       l.cpu_tier, l.release_year, l.listed_at, l.image_path,
                       e.value_score, e.verdict
                FROM laptops l
                JOIN brands b ON l.brand_id = b.id
                JOIN evaluations e ON e.laptop_id = l.id
                JOIN use_cases u ON u.id = ?
                WHERE l.ram_gb >= u.min_ram_gb
                  AND l.cpu_tier >= u.min_cpu_tier
                  AND l.storage_gb >= u.min_storage
                ORDER BY e.value_score DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $use_case_id);
    } else {
        // Q1: simple JOIN all laptops
        $sql = "SELECT l.id, l.model, b.name AS brand, l.price, l.ram_gb,
                       l.storage_gb, l.`condition`, l.has_warranty,
                       l.cpu_tier, l.release_year, l.listed_at, l.image_path,
                       e.value_score, e.verdict
                FROM laptops l
                JOIN brands b ON l.brand_id = b.id
                JOIN evaluations e ON e.laptop_id = l.id
                ORDER BY e.value_score DESC";
        $stmt = $conn->prepare($sql);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/** Single laptop detail with brand + evaluation. */
function get_laptop(mysqli $conn, int $id): array|null
{
    $sql = "SELECT l.id, l.model, b.name AS brand, l.brand_id, l.price,
                   l.ram_gb, l.storage_gb, l.`condition`, l.has_warranty,
                   l.cpu_tier, l.release_year, l.listed_at, l.image_path,
                   e.value_score, e.verdict, e.evaluated_at
            FROM laptops l
            JOIN brands b ON l.brand_id = b.id
            JOIN evaluations e ON e.laptop_id = l.id
            WHERE l.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ?: null;
}

/** All brands. */
function get_brands(mysqli $conn): array
{
    $result = $conn->query("SELECT id, name, notes FROM brands ORDER BY name");
    return $result->fetch_all(MYSQLI_ASSOC);
}

/** All use cases. */
function get_use_cases(mysqli $conn): array
{
    $result = $conn->query("SELECT id, name, min_ram_gb, min_cpu_tier, min_storage FROM use_cases ORDER BY name");
    return $result->fetch_all(MYSQLI_ASSOC);
}

/** All scoring rules. */
function get_scoring_rules(mysqli $conn): array
{
    $result = $conn->query("SELECT id, factor, value_key, points, description FROM scoring_rules ORDER BY factor, points DESC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Q3: Avg score per brand (GROUP BY + HAVING).
 */
function get_avg_score_by_brand(mysqli $conn): array
{
    $sql = "SELECT b.name, ROUND(AVG(e.value_score), 1) AS avg_score,
                   COUNT(*) AS total_listings
            FROM brands b
            JOIN laptops l ON l.brand_id = b.id
            JOIN evaluations e ON e.laptop_id = l.id
            GROUP BY b.id
            HAVING COUNT(*) > 0
            ORDER BY avg_score DESC";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

/** Total laptop count. */
function get_total_laptops(mysqli $conn): int
{
    return (int) $conn->query("SELECT COUNT(*) FROM laptops")->fetch_row()[0];
}

/** Top deal laptops (Great Deal or Fair) for dashboard. */
function get_best_laptops(mysqli $conn, int $limit = 5): array
{
    $sql = "SELECT model, brand, value_score, verdict, price
            FROM v_best_value_laptops
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
