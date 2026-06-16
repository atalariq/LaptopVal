<?php
/**
 * Application Configuration — EXAMPLE
 * Copy this to config.php and adjust credentials for your environment.
 * config.php is gitignored.
 */

// Database
// - Docker setup: use 'db' (the MySQL service name), pass 'root'
// - Native/XAMPP: use 'localhost', adjust user/pass
define('DB_HOST', 'db');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'laptop_evaluator');

// App
define('APP_NAME', 'Used Laptop Deal Evaluator');
define('APP_TAGLINE', 'Kriteria beli laptop bekas yang eksplisit dan konsisten.');

// Base URL
define('BASE_URL', '/');
