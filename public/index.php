<?php
/**
 * MyMultiBranch Platform - Entry Point
 * 
 * @package MMB
 * @version 1.0.0
 */

// Define base path (may already be defined if accessed via root index.php)
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Start output buffering
ob_start();

// Autoloader
require_once BASE_PATH . '/core/Autoloader.php';

// Check if installation is needed
if (!file_exists(BASE_PATH . '/config/installed.lock')) {
    header('Location: /install/');
    exit;
}

// Load configuration
require_once BASE_PATH . '/config/app.php';

// Debug mode: show request info (remove in production)
if (defined('APP_DEBUG') && APP_DEBUG && isset($_GET['_debug'])) {
    header('Content-Type: text/html; charset=UTF-8');
    echo "<h2>Server Debug Info</h2>";
    echo "<pre>";
    echo "Entry Point: PUBLIC index.php\n";
    echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set') . "\n";
    echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'not set') . "\n";
    echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'not set') . "\n";
    echo "PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'not set') . "\n";
    echo "QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'not set') . "\n";
    echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'not set') . "\n";
    echo "\nGET['url']: " . ($_GET['url'] ?? 'not set') . "\n";
    echo "\nBase Path: " . BASE_PATH . "\n";
    echo "Working Directory: " . getcwd() . "\n";
    echo "Autoloader loaded from: " . BASE_PATH . '/core/Autoloader.php' . "\n";
    echo "</pre>";
    
    echo "<h3>Test Routing</h3>";
    echo "<p>If your .htaccess rewrites are NOT working, try these links:</p>";
    echo "<ul>";
    echo "<li><a href='/index.php?url=login'>Login (via query string)</a></li>";
    echo "<li><a href='/index.php?url=register'>Register (via query string)</a></li>";
    echo "<li><a href='/index.php?url=dashboard'>Dashboard (via query string)</a></li>";
    echo "</ul>";
    echo "<p>If .htaccess rewrites ARE working, these should work:</p>";
    echo "<ul>";
    echo "<li><a href='/login'>Login (pretty URL)</a></li>";
    echo "<li><a href='/register'>Register (pretty URL)</a></li>";
    echo "<li><a href='/dashboard'>Dashboard (pretty URL)</a></li>";
    echo "</ul>";
    exit;
}

// Initialize application
use Core\App;

$app = new App();
$app->run();

// Flush output
ob_end_flush();
