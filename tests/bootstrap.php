<?php
/**
 * PHPUnit Bootstrap File
 * 
 * Initializes the testing environment
 * Part of Phase 12: Testing & Quality Assurance
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Define testing environment
define('TESTING', true);

// Load autoloader if composer is installed
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Simple autoloader for testing
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $class = str_replace('\\', '/', $class);
    
    // Try different paths
    $paths = [
        BASE_PATH . '/' . strtolower($class) . '.php',
        BASE_PATH . '/' . $class . '.php',
    ];
    
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Create test configuration
if (!file_exists(BASE_PATH . '/config/database.php')) {
    // Use in-memory SQLite for tests
    $testDbConfig = [
        'host' => 'localhost',
        'port' => '3306',
        'database' => ':memory:',
        'username' => 'test',
        'password' => 'test',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ];
} else {
    $testDbConfig = require BASE_PATH . '/config/database.php';
}

// Define APP constants if not defined
if (!defined('APP_NAME')) {
    define('APP_NAME', 'MMB Test');
}
if (!defined('APP_URL')) {
    define('APP_URL', 'http://localhost');
}
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', true);
}

// Create necessary directories for testing
$testDirs = [
    BASE_PATH . '/storage/cache',
    BASE_PATH . '/storage/logs',
    BASE_PATH . '/storage/uploads',
    BASE_PATH . '/tests/results',
    BASE_PATH . '/tests/coverage',
];

foreach ($testDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Initialize test database helper
class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clean cache before each test
        $this->cleanCache();
    }
    
    protected function tearDown(): void
    {
        // Clean up after test
        $this->cleanCache();
        parent::tearDown();
    }
    
    protected function cleanCache(): void
    {
        $cacheDir = BASE_PATH . '/storage/cache';
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/*.cache');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }
    
    protected function createTempFile(string $content = '', string $extension = 'txt'): string
    {
        $tempDir = BASE_PATH . '/storage/uploads';
        $filename = uniqid('test_') . '.' . $extension;
        $filepath = $tempDir . '/' . $filename;
        file_put_contents($filepath, $content);
        return $filepath;
    }
    
    protected function deleteTempFile(string $filepath): void
    {
        if (file_exists($filepath)) {
            @unlink($filepath);
        }
    }
}
