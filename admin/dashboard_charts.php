<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
header('Content-Type: application/json');

// Single query gives brand name, avg score, and laptop count for both charts
$brand_data = get_avg_score_by_brand($conn);

echo json_encode([
    'brands' => [
        'labels' => array_column($brand_data, 'name'),
        'data'   => array_map('intval', array_column($brand_data, 'total_listings')),
    ],
    'avgScores' => [
        'labels' => array_column($brand_data, 'name'),
        'data'   => array_map('floatval', array_column($brand_data, 'avg_score')),
    ],
]);
