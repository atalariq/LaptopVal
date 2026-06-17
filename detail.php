<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    include '404.php';
    exit;
}

$laptop = get_laptop($conn, $id);
if ($laptop === null) {
    include '404.php';
    exit;
}

// Score breakdown — single source of truth: MySQL fn_score_breakdown (display only)
$bstmt = $conn->prepare("SELECT fn_score_breakdown(?,?,?,?,?,?,?) AS b");
$bstmt->bind_param('iiiiiii',
    $laptop['cpu_tier'], $laptop['ram_gb'], $laptop['storage_gb'],
    $laptop['condition'], $laptop['has_warranty'], $laptop['release_year'], $laptop['price']);
$bstmt->execute();
$bd = json_decode($bstmt->get_result()->fetch_assoc()['b'], true);

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
<?php else: ?>
<div class="card-img-placeholder d-flex align-items-center justify-content-center rounded mb-4"
     style="height:200px; background: <?= h(brand_placeholder_color($laptop['brand'])) ?>;">
    <span class="fw-bold text-white" style="font-size:5rem;"><?= h(mb_strtoupper(mb_substr($laptop['brand'], 0, 1))) ?></span>
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
                        <tr><th class="bg-light">Terdaftar</th><td><?= h(date('d M Y', strtotime($laptop['listed_at']))) ?></td></tr>
                        <?php if (!empty($laptop['source_url'])): ?>
                        <tr><th class="bg-light">Sumber</th>
                            <td><a href="<?= h($laptop['source_url']) ?>" target="_blank" rel="noopener">Lihat sumber &#8599;</a></td></tr>
                        <?php endif; ?>
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
                    ['CPU Tier', $bd['cpu']['points'],       $bd['cpu']['max']],
                    ['RAM',      $bd['ram']['points'],       $bd['ram']['max']],
                    ['Storage',  $bd['storage']['points'],   $bd['storage']['max']],
                    ['Kondisi',  $bd['condition']['points'], $bd['condition']['max']],
                    ['Garansi',  $bd['warranty']['points'],  $bd['warranty']['max']],
                    ['Age',      $bd['age']['points'],       $bd['age']['max']],
                    ['Harga',    $bd['price']['points'],     $bd['price']['max']],
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
