<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

$total_laptops = get_total_laptops($conn);
$brands        = get_brands($conn);
$total_brands  = count($brands);

$avg_row = $conn->query("SELECT ROUND(AVG(value_score),1) AS avg FROM evaluations")->fetch_assoc();
$avg_score = $avg_row['avg'] ?? 0;

$top_laptops   = get_best_laptops($conn, 5);
$brand_scores  = get_avg_score_by_brand($conn);

$title = 'Dashboard';
require_once '../includes/header_admin.php';
?>

<h4 class="mb-4 fw-bold">Dashboard</h4>

<!-- Stat cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-center border-primary">
            <div class="card-body">
                <div class="display-4 fw-bold text-primary"><?= (int)$total_laptops ?></div>
                <div class="text-muted">Total Laptops</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-success">
            <div class="card-body">
                <div class="display-4 fw-bold text-success"><?= (int)$total_brands ?></div>
                <div class="text-muted">Total Brands</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-info">
            <div class="card-body">
                <div class="display-4 fw-bold text-info"><?= h((string)$avg_score) ?></div>
                <div class="text-muted">Rata-rata Score</div>
            </div>
        </div>
    </div>
</div>

<!-- Top 5 Laptops -->
<div class="card">
    <div class="card-header fw-bold">Top 5 Laptops by Score</div>
    <div class="card-body p-0">
        <?php if (empty($top_laptops)): ?>
        <p class="p-3 mb-0 text-muted">Belum ada data.</p>
        <?php else: ?>
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Model</th>
                    <th>Brand</th>
                    <th>Score</th>
                    <th>Verdict</th>
                    <th>Harga</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_laptops as $i => $l): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= h($l['model']) ?></td>
                    <td><?= h($l['brand']) ?></td>
                    <td><strong><?= (int)$l['value_score'] ?></strong></td>
                    <td>
                        <span class="badge bg-<?= h(verdict_class($l['verdict'])) ?>">
                            <?= h($l['verdict']) ?>
                        </span>
                    </td>
                    <td><?= h(format_price($l['price'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Avg Score per Brand (Q3) -->
<div class="card mt-4">
    <div class="card-header fw-bold">Rata-rata Score per Brand</div>
    <div class="card-body p-0">
        <?php if (empty($brand_scores)): ?>
        <p class="p-3 mb-0 text-muted">Belum ada data.</p>
        <?php else: ?>
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Brand</th>
                    <th>Avg Score</th>
                    <th>Listing</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($brand_scores as $i => $row): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= h($row['name']) ?></td>
                    <td><strong><?= h((string)$row['avg_score']) ?></strong></td>
                    <td><?= (int)$row['total_listings'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
