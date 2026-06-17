<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$use_case_id = (int)($_GET['use_case_id'] ?? 0);
$page        = max(1, (int)($_GET['page'] ?? 1));

$allowed_sorts = [
    'score_desc' => 'value_score DESC',
    'price_asc'  => 'price ASC',
    'price_desc' => 'price DESC',
    'newest'     => 'listed_at DESC',
];
$sort     = array_key_exists($_GET['sort'] ?? '', $allowed_sorts) ? $_GET['sort'] : 'score_desc';
$sort_sql = $allowed_sorts[$sort];

$total       = count_laptops($conn, $use_case_id);
$total_pages = max(1, (int) ceil($total / LAPTOPS_PER_PAGE));
$page        = min($page, $total_pages);
$offset      = ($page - 1) * LAPTOPS_PER_PAGE;

$use_cases = get_use_cases($conn);
$laptops   = get_laptops_paginated($conn, $use_case_id, $sort_sql, LAPTOPS_PER_PAGE, $offset);

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
        <a href="index.php?sort=<?= h($sort) ?>"
           class="btn btn-sm <?= $use_case_id === 0 ? 'btn-primary' : 'btn-outline-primary' ?>">
            Semua
        </a>
        <?php foreach ($use_cases as $uc): ?>
        <a href="index.php?use_case_id=<?= (int)$uc['id'] ?>&sort=<?= h($sort) ?>"
           class="btn btn-sm <?= $use_case_id === (int)$uc['id'] ? 'btn-primary' : 'btn-outline-primary' ?>">
            <?= h($uc['name']) ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Sort + count bar -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small"><?= (int)$total ?> laptop ditemukan</span>
    <div class="d-flex align-items-center gap-2">
        <label for="sortSelect" class="text-muted small mb-0">Urutkan:</label>
        <select id="sortSelect" class="form-select form-select-sm" style="width:auto">
            <option value="score_desc" <?= $sort === 'score_desc' ? 'selected' : '' ?>>Score Tertinggi</option>
            <option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected' : '' ?>>Harga Termurah</option>
            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Harga Termahal</option>
            <option value="newest"     <?= $sort === 'newest'     ? 'selected' : '' ?>>Terbaru</option>
        </select>
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
            <?php if (!empty($l['image_path'])): ?>
            <img src="<?= BASE_URL . h($l['image_path']) ?>"
                 alt="<?= h($l['brand']) ?> <?= h($l['model']) ?>"
                 class="card-img-top"
                 style="height:180px; object-fit:cover;">
            <?php else: ?>
            <div class="bg-light d-flex align-items-center justify-content-center"
                 style="height:180px;">
                <span class="text-muted small">Foto tidak tersedia</span>
            </div>
            <?php endif; ?>
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

<?php if ($total_pages > 1): ?>
<?php
$_base = [];
if ($use_case_id > 0) $_base['use_case_id'] = $use_case_id;
if ($sort !== 'score_desc') $_base['sort'] = $sort;
$mk_page_url = function (int $p) use ($_base): string {
    $params = $_base;
    $params['page'] = $p;
    return 'index.php?' . http_build_query($params);
};
?>
<nav class="mt-4" aria-label="Navigasi halaman">
    <ul class="pagination justify-content-center flex-wrap">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= h($mk_page_url($page - 1)) ?>">&laquo;</a>
        </li>
        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
            <a class="page-link" href="<?= h($mk_page_url($p)) ?>"><?= (int)$p ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= h($mk_page_url($page + 1)) ?>">&raquo;</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<script>
document.getElementById('sortSelect').addEventListener('change', function () {
    var url = new URL(window.location.href);
    url.searchParams.set('sort', this.value);
    url.searchParams.delete('page');
    window.location.href = url.toString();
});
</script>

<?php require_once 'includes/footer.php'; ?>
