<?php
/**
 * Auth Guard
 * Call require_login() di awal setiap halaman admin.
 * Kalau belum login, redirect ke login.php.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token once per session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_token(): string
{
    return $_SESSION['csrf_token'] ?? '';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        set_flash('Permintaan tidak valid (CSRF). Coba lagi.', 'danger');
        header('Location: ' . get_base_path() . 'admin/dashboard.php');
        exit;
    }
}

function require_login(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . get_base_path() . 'login.php');
        exit;
    }
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Detect base path relative to document root.
 * Handles calls from both root and admin/ subdirectory.
 */
function get_base_path(): string
{
    // Kalau dipanggil dari admin/, naik satu level
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if (str_contains($script, '/admin/')) {
        return '../';
    }
    return '';
}
