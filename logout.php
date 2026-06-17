<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$token = $_GET['token'] ?? '';
if (!hash_equals(csrf_token(), $token)) {
    header('Location: index.php');
    exit;
}

session_destroy();
header('Location: login.php');
exit;
