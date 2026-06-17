<?php
$page_title = isset($title) ? $title . ' — ' . APP_NAME : APP_NAME;
$script = basename($_SERVER['SCRIPT_NAME']);
$dir    = basename(dirname($_SERVER['SCRIPT_NAME']));

function admin_nav_link(string $href, string $label, string $script, string $dir): string {
    $file    = basename($href);
    $active  = ($dir === 'admin' && $script === $file) ? ' active' : '';
    return '<li class="nav-item"><a class="nav-link' . $active . '" href="' . $href . '">' . h($label) . '</a></li>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($page_title) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="<?= BASE_URL ?>favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="<?= BASE_URL ?>favicon/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>favicon/apple-touch-icon.png">
    <link rel="manifest" href="<?= BASE_URL ?>favicon/site.webmanifest">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <?php if (!empty($load_chartjs)): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <?php endif; ?>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>admin/dashboard.php">
            <img src="<?= BASE_URL ?>logo.svg" alt="" width="22" height="22" class="me-2 align-text-bottom">
            <?= h(APP_NAME) ?> <span class="badge bg-light text-primary ms-1" style="font-size:.65em">Admin</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin"
                aria-controls="navbarAdmin" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarAdmin">
            <ul class="navbar-nav me-auto">
                <?= admin_nav_link(BASE_URL . 'admin/dashboard.php',     'Dashboard',     $script, $dir) ?>
                <?= admin_nav_link(BASE_URL . 'admin/laptops.php',       'Laptops',       $script, $dir) ?>
                <?= admin_nav_link(BASE_URL . 'admin/brands.php',        'Brands',        $script, $dir) ?>
                <?= admin_nav_link(BASE_URL . 'admin/use_cases.php',     'Use Cases',     $script, $dir) ?>
                <?= admin_nav_link(BASE_URL . 'admin/scoring_rules.php', 'Scoring Rules', $script, $dir) ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['username'])): ?>
                <li class="nav-item">
                    <span class="nav-link text-white-50">Hi, <?= h($_SESSION['username']) ?></span>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>logout.php?token=<?= h(csrf_token()) ?>">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<main class="container-fluid my-4 px-4">
