<?php
// ============================================================
//  Home Help — Database Connection
//  File: config/db.php
//  DevGuide §1.3 — using mysqli as specified
//  HomeHelp Ltd · Rwanda 🇷🇼
// ============================================================

require_once __DIR__ . '/env.php';

// Production/cPanel defaults. Override in project-root .env for local
// portable MariaDB (e.g. DB_HOST=127.0.0.1, DB_PORT=3307).
if (!defined('DB_HOST')) define('DB_HOST', hh_env('DB_HOST', 'localhost'));
if (!defined('DB_PORT')) define('DB_PORT', (int)hh_env('DB_PORT', '3306'));
if (!defined('DB_USER')) define('DB_USER', hh_env('DB_USER', 'homehelp_user_help'));
if (!defined('DB_PASS')) define('DB_PASS', hh_env('DB_PASS', ''));
if (!defined('DB_NAME')) define('DB_NAME', hh_env('DB_NAME', 'homehelp_dhomehelpb'));

if (!extension_loaded('mysqli') || !function_exists('mysqli_report')) {
    error_log('HomeHelp: PHP mysqli extension is not loaded');
    http_response_code(500);
    die('<p style="font-family:sans-serif;text-align:center;padding:40px;">
        The PHP <strong>mysqli</strong> extension is not enabled on this server.<br>
        In cPanel → Select PHP Version → Extensions, enable <code>mysqli</code>, then refresh.
    </p>');
}

mysqli_report(MYSQLI_REPORT_OFF);

$port = DB_PORT > 0 ? DB_PORT : ini_get('mysqli.default_port');
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)$port);

if ($conn->connect_error) {
    error_log('DB Connection failed: ' . $conn->connect_error . ' @ ' . DB_HOST . ':' . (int)$port);
    http_response_code(500);
    die('<p style="font-family:sans-serif;text-align:center;padding:40px;">
        Database is not reachable on ' . htmlspecialchars(DB_HOST) . ':' . (int)$port . '.<br>
        Check <code>.env</code> DB_* values (or cPanel MySQL credentials), then refresh.
    </p>');
}

$conn->set_charset('utf8mb4');
