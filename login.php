<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (is_logged_in()) {
    header('Location: admin/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        set_flash('Permintaan tidak valid (CSRF). Coba lagi.', 'danger');
        header('Location: login.php');
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        set_flash('Username dan password wajib diisi.', 'danger');
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: admin/dashboard.php');
            exit;
        } else {
            set_flash('Username atau password salah.', 'danger');
        }
    }
}

$title = 'Login';
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
    <title><?= h($title . ' — ' . APP_NAME) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="<?= BASE_URL ?>favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="<?= BASE_URL ?>favicon/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>favicon/apple-touch-icon.png">
    <link rel="manifest" href="<?= BASE_URL ?>favicon/site.webmanifest">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body class="login-bg">
<div class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow" style="width: 100%; max-width: 400px;">
        <div class="card-body p-4">
            <h4 class="card-title mb-1 fw-bold"><?= h(APP_NAME) ?></h4>
            <p class="text-muted mb-4 small">Admin Panel</p>

            <?php $flash = get_flash(); if (!empty($flash)): ?>
            <div class="alert alert-<?= h($flash['type']) ?>" role="alert">
                <?= h($flash['msg']) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="login.php" novalidate>
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username"
                           value="<?= h($_POST['username'] ?? '') ?>" required autofocus>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Masuk</button>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
