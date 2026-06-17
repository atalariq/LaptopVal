<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

$filename = 'laptops-export-' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');
// UTF-8 BOM so Excel opens it correctly
fwrite($out, "\xEF\xBB\xBF");

fputcsv($out, [
    'ID', 'Model', 'Brand', 'Harga (ribu IDR)', 'RAM (GB)', 'Storage (GB)',
    'CPU Tier', 'Kondisi', 'Garansi', 'Tahun Rilis', 'Score', 'Verdict', 'Evaluated At',
]);

$sql = "SELECT id, model, brand, price, ram_gb, storage_gb,
               cpu_tier, `condition`, has_warranty,
               release_year, value_score, verdict, evaluated_at
        FROM v_laptop_evaluations
        ORDER BY value_score DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    fputcsv($out, [
        $row['id'],
        $row['model'],
        $row['brand'],
        $row['price'],
        $row['ram_gb'],
        $row['storage_gb'],
        $row['cpu_tier'],
        (int)$row['condition'],
        $row['has_warranty'] ? 'Ya' : 'Tidak',
        $row['release_year'],
        $row['value_score'],
        $row['verdict'],
        $row['evaluated_at'],
    ]);
}
fclose($out);
exit;
