<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
header('Content-Type: application/json');

$cpu     = (int)($_GET['cpu_tier'] ?? 0);
$ram     = (int)($_GET['ram_gb'] ?? 0);
$storage = (int)($_GET['storage_gb'] ?? 0);
$cond    = (int)($_GET['condition'] ?? 0);
$warr    = (int)($_GET['has_warranty'] ?? 0);
$year    = (int)($_GET['release_year'] ?? 0);
$price   = (int)($_GET['price'] ?? 0);

$sql = "SELECT fn_calculate_score(?,?,?,?,?,?,?) AS score,
               fn_get_verdict(fn_calculate_score(?,?,?,?,?,?,?)) AS verdict,
               fn_score_breakdown(?,?,?,?,?,?,?) AS breakdown";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iiiiiiiiiiiiiiiiiiiii',
    $cpu,$ram,$storage,$cond,$warr,$year,$price,
    $cpu,$ram,$storage,$cond,$warr,$year,$price,
    $cpu,$ram,$storage,$cond,$warr,$year,$price);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'score'     => (int)$row['score'],
    'verdict'   => $row['verdict'],
    'breakdown' => json_decode($row['breakdown'], true),
]);
