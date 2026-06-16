<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    set_flash('Laptop tidak ditemukan.', 'danger');
    header('Location: index.php');
    exit;
}

$laptop = get_laptop($conn, $id);
if ($laptop === null) {
    set_flash('Laptop tidak ditemukan.', 'danger');
    header('Location: index.php');
    exit;
}

// Score breakdown (display only — mirrors fn_calculate_score, not recalculating final score)
$cpu_pts      = (int)$laptop['cpu_tier'] * 10;
$ram          = (int)$laptop['ram_gb'];
$ram_pts      = $ram >= 32 ? 25 : ($ram >= 16 ? 20 : ($ram >= 8 ? 10 : 5));
$storage      = (int)$laptop['storage_gb'];
$storage_pts  = $storage >= 1000 ? 15 : ($storage >= 512 ? 12 : ($storage >= 256 ? 7 : 3));
$cond_pts     = ((int)$laptop['condition'] - 1) * 5;
$warranty_pts = $laptop['has_warranty'] ? 5 : 0;
$year         = (int)$laptop['release_year'];
$age_pts      = $year < 2018 ? -5 : ($year < 2020 ? -2 : 0);

// Dynamic price score (mirrors MySQL ratio logic)
$spec_score_only = $cpu_pts + $ram_pts + $storage_pts + $cond_pts + $warranty_pts + $age_pts;
$price           = (int)$laptop['price'];
$price_pts       = 0;
if ($spec_score_only > 0 && $price > 0) {
    $ratio = $price / $spec_score_only;
    if      ($ratio <= 50)  $price_pts = 15;
    elseif  ($ratio <= 85)  $price_pts = 10;
    elseif  ($ratio <= 120) $price_pts = 5;
}

$title = h($laptop['brand']) . ' ' . h($laptop['model']);
require_once 'includes/header_public.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active"><?= h($laptop['brand']) ?> <?= h($laptop['model']) ?></li>
    </ol>
</nav>

<?php if (!empty($laptop['image_path'])): ?>
<div class="mb-4">
    <img src="<?= BASE_URL . h($laptop['image_path']) ?>"
         alt="<?= h($laptop['brand'] . ' ' . $laptop['model']) ?>"
         class="img-fluid rounded shadow-sm"
         style="max-height:350px; width:100%; object-fit:cover;">
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left: Specs table -->
    <div class="col-md-7">
        <div class="card">
            <div class="card-header fw-bold">Spesifikasi</div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr><th class="w-40 bg-light">Model</th><td><?= h($laptop['model']) ?></td></tr>
                        <tr><th class="bg-light">Brand</th><td><?= h($laptop['brand']) ?></td></tr>
                        <tr><th class="bg-light">Tahun Rilis</th><td><?= (int)$laptop['release_year'] ?></td></tr>
                        <tr><th class="bg-light">CPU Tier</th><td><?= h(cpu_label($laptop['cpu_tier'])) ?></td></tr>
                        <tr><th class="bg-light">RAM</th><td><?= (int)$laptop['ram_gb'] ?> GB</td></tr>
                        <tr><th class="bg-light">Storage</th><td><?= (int)$laptop['storage_gb'] ?> GB</td></tr>
                        <tr><th class="bg-light">Kondisi</th><td><?= h(condition_label($laptop['condition'])) ?></td></tr>
                        <tr><th class="bg-light">Garansi</th><td><?= $laptop['has_warranty'] ? 'Ada' : 'Tidak' ?></td></tr>
                        <tr><th class="bg-light">Harga</th><td class="fw-bold"><?= h(format_price($laptop['price'])) ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right: Score card -->
    <div class="col-md-5">
        <div class="card score-preview-panel">
            <div class="card-body text-center">
                <p class="text-muted mb-1 small text-uppercase fw-semibold">Value Score</p>
                <div class="score-display text-primary"><?= (int)$laptop['value_score'] ?></div>
                <span class="badge fs-6 bg-<?= h(verdict_class($laptop['verdict'])) ?> mt-2 mb-3">
                    <?= h($laptop['verdict']) ?>
                </span>
            </div>
            <div class="card-body pt-0">
                <p class="fw-semibold mb-2">Score Breakdown</p>

                <?php
                $bars = [
                    ['CPU Tier',  $cpu_pts,     30],
                    ['RAM',       $ram_pts,      25],
                    ['Storage',   $storage_pts,  15],
                    ['Kondisi',   $cond_pts,     15],
                    ['Garansi',   $warranty_pts,  5],
                    ['Age',       $age_pts,       5],
                    ['Harga',     $price_pts,    15],
                ];
                foreach ($bars as [$label, $pts, $max]):
                    $pct   = $max > 0 ? max(0, min(100, (int)round($pts / $max * 100))) : 0;
                    $color = $pts < 0 ? 'danger' : ($pct >= 66 ? 'success' : ($pct >= 33 ? 'warning' : 'danger'));
                    if ($pts < 0) $pct = abs((int)round($pts / $max * 100));
                ?>
                <div class="mb-2">
                    <div class="d-flex justify-content-between small">
                        <span><?= h($label) ?></span>
                        <span><?= $pts > 0 ? '+' : '' ?><?= (int)$pts ?> / <?= (int)$max ?></span>
                    </div>
                    <div class="progress" style="height:8px">
                        <div class="progress-bar bg-<?= $color ?>" style="width:<?= $pct ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
