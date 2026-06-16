<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$use_case_id = (int)($_GET['use_case_id'] ?? 0);
$use_cases   = get_use_cases($conn);
$laptops     = get_laptops($conn, $use_case_id);

$title = 'Temukan Laptop Bekas Terbaik';
require_once 'includes/header_public.php';
?>

<!-- Hero -->
<section class="hero-section text-center">
    <h1 class="display-5 fw-bold"><?= h(APP_NAME) ?></h1>
    <p class="lead mb-0"><?= h(APP_TAGLINE) ?></p>
</section>

<!-- Use Case Filter -->
<div class="mb-3">
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <strong class="me-1">Filter:</strong>
        <a href="index.php" class="btn btn-sm <?= $use_case_id === 0 ? 'btn-primary' : 'btn-outline-primary' ?>">
            Semua
        </a>
        <?php foreach ($use_cases as $uc): ?>
        <a href="index.php?use_case_id=<?= (int)$uc['id'] ?>"
           class="btn btn-sm <?= $use_case_id === (int)$uc['id'] ? 'btn-primary' : 'btn-outline-primary' ?>">
            <?= h($uc['name']) ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Search -->
<div class="mb-4">
    <input type="text" id="searchInput" class="form-control" placeholder="Cari model atau brand...">
</div>

<!-- Laptop Cards -->
<?php if (empty($laptops)): ?>
<div class="alert alert-info">Tidak ada laptop yang ditemukan.</div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($laptops as $l): ?>
    <div class="col-md-4 card-laptop-wrapper" data-model="<?= h($l['model']) ?>" data-brand="<?= h($l['brand']) ?>">
        <div class="card card-laptop h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="card-title mb-0 fw-bold"><?= h($l['brand']) ?> <?= h($l['model']) ?></h6>
                    <span class="badge bg-<?= h(verdict_class($l['verdict'])) ?> ms-2 text-nowrap">
                        <?= h($l['verdict']) ?>
                    </span>
                </div>
                <p class="text-muted mb-2 fw-semibold"><?= h(format_price($l['price'])) ?></p>
                <div class="d-flex flex-wrap gap-1 mb-3">
                    <span class="badge bg-secondary"><?= h(cpu_label($l['cpu_tier'])) ?> CPU</span>
                    <span class="badge bg-secondary"><?= (int)$l['ram_gb'] ?>GB RAM</span>
                    <span class="badge bg-secondary"><?= (int)$l['storage_gb'] ?>GB Storage</span>
                    <span class="badge bg-secondary"><?= h(condition_label($l['condition'])) ?></span>
                </div>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Score: <strong><?= (int)$l['value_score'] ?></strong></span>
                    <a href="detail.php?id=<?= (int)$l['id'] ?>" class="btn btn-sm btn-outline-primary">
                        Lihat Detail &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
