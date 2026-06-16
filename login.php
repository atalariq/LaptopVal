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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($title . ' — ' . APP_NAME) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
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
