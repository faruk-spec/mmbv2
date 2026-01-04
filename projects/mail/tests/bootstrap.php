<?php

/**
 * PHPUnit Bootstrap File
 * 
 * This file is executed before running tests.
 * It sets up the test environment and loads dependencies.
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load environment variables from .env.testing
if (file_exists(BASE_PATH . '/.env.testing')) {
    $lines = file(BASE_PATH . '/.env.testing', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// Auto-load test classes
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $prefix = 'Tests\\';
    $baseDir = __DIR__ . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Set timezone
date_default_timezone_set('UTC');

// Initialize test database
try {
    $pdo = new PDO(
        'mysql:host=' . getenv('DB_HOST'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
    );
    
    // Create test database if it doesn't exist
    $pdo->exec('CREATE DATABASE IF NOT EXISTS ' . getenv('DB_DATABASE'));
    
    echo "Test environment initialized successfully.\n";
    
} catch (PDOException $e) {
    echo "Failed to initialize test environment: " . $e->getMessage() . "\n";
    exit(1);
}
