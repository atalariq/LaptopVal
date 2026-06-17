<?php
// $title must be set by the including page
$page_title = isset($title) ? $title . ' — ' . APP_NAME : APP_NAME;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <script>
      (function () {
        var t = localStorage.getItem("theme");
        if (!t) {
          t = window.matchMedia("(prefers-color-scheme: light)").matches
            ? "light"
            : "dark";
        }
        document.documentElement.setAttribute("data-theme", t);
      })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= h(APP_TAGLINE) ?>">
    <meta property="og:title" content="<?= h(APP_NAME) ?>">
    <meta property="og:description" content="<?= h(APP_TAGLINE) ?>">
    <meta property="og:image" content="<?= BASE_URL ?>logo.svg">
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="<?= BASE_URL ?>favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="<?= BASE_URL ?>favicon/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>favicon/apple-touch-icon.png">
    <link rel="manifest" href="<?= BASE_URL ?>favicon/site.webmanifest">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>index.php">
            <img src="<?= BASE_URL ?>logo.svg" alt="" width="30" height="30" class="me-2 align-middle">
            <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPublic"
                aria-controls="navbarPublic" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarPublic">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>login.php">Admin Login</a>
                </li>
                <li class="nav-item d-flex align-items-center">
                    <button id="themeToggle" type="button" class="btn btn-sm btn-outline-secondary ms-2"
                            aria-label="Ganti tema">🌙</button>
                </li>
            </ul>
        </div>
    </div>
</nav>
<main class="container my-4">
