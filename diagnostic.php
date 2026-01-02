<?php
/**
 * Simple Diagnostic Script
 * Access this at /diagnostic.php to see what's happening
 * 
 * WARNING: This script should be deleted in production as it exposes server information.
 * Only use during debugging.
 */

// Simple check to prevent abuse - delete this file after debugging
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

// Check if debug mode is enabled
if (file_exists(BASE_PATH . '/config/app.php')) {
    require_once BASE_PATH . '/config/app.php';
    if (!defined('APP_DEBUG') || !APP_DEBUG) {
        http_response_code(403);
        die('Diagnostic script is disabled. Enable APP_DEBUG in config/app.php or delete this file.');
    }
}

echo "<!DOCTYPE html><html><head><title>Diagnostic</title></head><body>";
echo "<h1>Request Diagnostic</h1>";
echo "<pre>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'NOT SET') . "\n";
echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'NOT SET') . "\n";
echo "PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'NOT SET') . "\n";
echo "QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'NOT SET') . "\n";
echo "REDIRECT_URL: " . ($_SERVER['REDIRECT_URL'] ?? 'NOT SET') . "\n";
echo "\n\$_GET array:\n";
print_r($_GET);
echo "\n\$_SERVER (filtered for debugging):\n";
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_') === 0 || strpos($key, 'REQUEST_') === 0 || strpos($key, 'SCRIPT_') === 0 || strpos($key, 'REDIRECT_') === 0) {
        echo "$key: $value\n";
    }
}
echo "</pre>";

echo "<h2>Test Links</h2>";
echo "<ul>";
echo "<li><a href='/diagnostic.php'>Diagnostic (self)</a></li>";
echo "<li><a href='/login'>Login page</a></li>";
echo "<li><a href='/?url=login'>Login with query string</a></li>";
echo "<li><a href='/'>Home</a></li>";
echo "</ul>";

echo "</body></html>";
