<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

$load_chartjs = true;

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

<!-- Charts row -->
<div class="row g-4 mt-2">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header fw-bold">Distribusi Brand (Jumlah Listing)</div>
            <div class="card-body">
                <canvas id="brandPieChart" height="280"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header fw-bold">Rata-rata Score per Brand</div>
            <div class="card-body">
                <canvas id="avgScoreChart" height="280"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const PIE_COLORS = [
        '#7aa2f7','#9ece6a','#e0af68','#f7768e','#bb9af7',
        '#ff9e64','#7dcfff','#73daca','#2ac3de','#b4f9f8'
    ];
    const LABEL_COLOR = '#c0caf5';
    const GRID_COLOR  = '#2f3549';

    fetch('dashboard_charts.php')
        .then(function (r) { return r.json(); })
        .then(function (data) {

            new Chart(document.getElementById('brandPieChart'), {
                type: 'pie',
                data: {
                    labels: data.brands.labels,
                    datasets: [{
                        data: data.brands.data,
                        backgroundColor: PIE_COLORS.slice(0, data.brands.labels.length),
                        borderColor: '#1a1b26',
                        borderWidth: 2,
                    }]
                },
                options: {
                    plugins: {
                        legend: { labels: { color: LABEL_COLOR } }
                    }
                }
            });

            new Chart(document.getElementById('avgScoreChart'), {
                type: 'bar',
                data: {
                    labels: data.avgScores.labels,
                    datasets: [{
                        label: 'Avg Score',
                        data: data.avgScores.data,
                        backgroundColor: 'rgba(122, 162, 247, 0.7)',
                        borderColor: '#7aa2f7',
                        borderWidth: 1,
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true, max: 100,
                            ticks: { color: LABEL_COLOR },
                            grid:  { color: GRID_COLOR  }
                        },
                        x: {
                            ticks: { color: LABEL_COLOR },
                            grid:  { color: GRID_COLOR  }
                        }
                    },
                    plugins: {
                        legend: { labels: { color: LABEL_COLOR } }
                    }
                }
            });

        });
}());
</script>

<?php require_once '../includes/footer.php'; ?>
