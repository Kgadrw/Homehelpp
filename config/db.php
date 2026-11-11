<?php
// ============================================================
//  Home Help — Database Connection
//  File: config/db.php
//  DevGuide §1.3 — using mysqli as specified
//  HomeHelp Ltd · Rwanda 🇷🇼
// ============================================================

require_once __DIR__ . '/env.php';

// Use 127.0.0.1 (not "localhost") so mysqli uses TCP and respects the port.
// Portable MariaDB runs on 3307 because Windows MySQL80 already uses 3306.
if (!defined('DB_HOST')) define('DB_HOST', hh_env('DB_HOST', '127.0.0.1'));
if (!defined('DB_PORT')) define('DB_PORT', (int)hh_env('DB_PORT', '3307'));
if (!defined('DB_USER')) define('DB_USER', hh_env('DB_USER', 'homehelp_user_help'));
if (!defined('DB_PASS')) define('DB_PASS', hh_env('DB_PASS', ''));
if (!defined('DB_NAME')) define('DB_NAME', hh_env('DB_NAME', 'homehelp_dhomehelpb'));

mysqli_report(MYSQLI_REPORT_OFF);

$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    error_log('DB Connection failed: ' . $conn->connect_error . ' @ ' . DB_HOST . ':' . DB_PORT);
    http_response_code(500);
    die('<p style="font-family:sans-serif;text-align:center;padding:40px;">
        Database is not reachable on ' . htmlspecialchars(DB_HOST) . ':' . (int)DB_PORT . '.<br>
        Start portable MariaDB, then refresh.
    </p>');
}

$conn->set_charset('utf8mb4');
