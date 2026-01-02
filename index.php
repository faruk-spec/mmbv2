<?php
/**
 * MyMultiBranch Platform - Root Index
 * 
 * This file exists as a fallback when DocumentRoot is set to the project root
 * instead of the /public directory. It redirects all requests to public/index.php.
 * 
 * For best security practices, set your DocumentRoot to:
 * /path/to/mymultibranch/public
 */

// Define base path before including public/index.php
// This ensures BASE_PATH is correct regardless of entry point
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

// Start output buffering if not already started
if (ob_get_level() === 0) {
    ob_start();
}

// Load autoloader
require_once BASE_PATH . '/core/Autoloader.php';

// Check if installation is needed
if (!file_exists(BASE_PATH . '/config/installed.lock')) {
    header('Location: /install/');
    exit;
}

// Load configuration
require_once BASE_PATH . '/config/app.php';

// Debug mode: show request info
// NOTE: Using reflection here for debugging purposes only. In production, APP_DEBUG should be false.
if (defined('APP_DEBUG') && APP_DEBUG && isset($_GET['_debug'])) {
    header('Content-Type: text/html; charset=UTF-8');
    
    // Load router to see registered routes - use fully qualified names
    $app = new \Core\App();
    
    // Use reflection to access protected router (for debugging only)
    $reflection = new ReflectionClass($app);
    $routerProperty = $reflection->getProperty('router');
    $routerProperty->setAccessible(true);
    $router = $routerProperty->getValue($app);
    
    // Get routes using reflection (for debugging only)
    $routerReflection = new ReflectionClass($router);
    $routesProperty = $routerReflection->getProperty('routes');
    $routesProperty->setAccessible(true);
    $routes = $routesProperty->getValue($router);
    
    echo "<h2>Server Debug Info</h2>";
    echo "<pre>";
    echo "Entry Point: ROOT index.php\n";
    echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set') . "\n";
    echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'not set') . "\n";
    echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'not set') . "\n";
    echo "PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'not set') . "\n";
    echo "QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'not set') . "\n";
    echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'not set') . "\n";
    echo "REDIRECT_URL: " . ($_SERVER['REDIRECT_URL'] ?? 'not set') . "\n";
    echo "REDIRECT_STATUS: " . ($_SERVER['REDIRECT_STATUS'] ?? 'not set') . "\n";
    echo "\nGET['url']: " . ($_GET['url'] ?? 'not set') . "\n";
    echo "\nBase Path: " . BASE_PATH . "\n";
    echo "Working Directory: " . getcwd() . "\n";
    
    // Calculate what URI would be resolved
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $uriPath = parse_url($uri, PHP_URL_PATH) ?? '/';
    echo "\nParsed URI Path: " . $uriPath . "\n";
    
    // Show what the resolveUri would return
    $reflectionMethod = $reflection->getMethod('resolveUri');
    $reflectionMethod->setAccessible(true);
    $resolvedUri = $reflectionMethod->invoke($app);
    echo "Resolved URI: " . $resolvedUri . "\n";
    
    // Check for mod_rewrite
    echo "\n--- Apache Module Detection ---\n";
    if (function_exists('apache_get_modules')) {
        $modules = apache_get_modules();
        echo "mod_rewrite: " . (in_array('mod_rewrite', $modules) ? 'ENABLED' : 'DISABLED') . "\n";
    } else {
        echo "Cannot detect Apache modules (likely using PHP-FPM)\n";
    }
    echo "</pre>";
    
    echo "<h3>Registered Routes</h3>";
    echo "<p>Total GET routes: " . count($routes['GET'] ?? []) . "</p>";
    echo "<pre>";
    if (isset($routes['GET'])) {
        foreach ($routes['GET'] as $path => $route) {
            $handler = is_string($route['handler']) ? $route['handler'] : 'Closure';
            echo "GET $path => $handler\n";
        }
    }
    echo "</pre>";
    
    echo "<h3>Test Routing</h3>";
    echo "<p><strong>Query string URLs (work without URL rewriting):</strong></p>";
    echo "<ul>";
    echo "<li><a href='?url=login'>Login</a></li>";
    echo "<li><a href='?url=register'>Register</a></li>";
    echo "<li><a href='?url=dashboard'>Dashboard</a></li>";
    echo "<li><a href='?url=install'>Install</a></li>";
    echo "</ul>";
    
    echo "<hr>";
    echo "<p><strong>Pretty URLs (require URL rewriting configuration):</strong></p>";
    echo "<ul>";
    echo "<li><a href='/login'>Login</a></li>";
    echo "<li><a href='/register'>Register</a></li>";
    echo "<li><a href='/dashboard'>Dashboard</a></li>";
    echo "</ul>";
    
    echo "<h3>Server Configuration</h3>";
    echo "<p style='color: #ff6b6b;'>⚠️ If pretty URLs don't work, configure your web server:</p>";
    
    echo "<h4>For Apache:</h4>";
    echo "<p>1. Enable mod_rewrite:</p>";
    echo "<pre style='background: #1a1a2e; color: #00f0ff; padding: 10px;'>sudo a2enmod rewrite\nsudo systemctl restart apache2</pre>";
    echo "<p>2. Add AllowOverride to your VirtualHost or apache2.conf:</p>";
    echo "<pre style='background: #1a1a2e; color: #00f0ff; padding: 10px;'>";
    echo "&lt;Directory \"/path/to/your/site\"&gt;\n";
    echo "    AllowOverride All\n";
    echo "    Require all granted\n";
    echo "&lt;/Directory&gt;";
    echo "</pre>";
    echo "<p>3. Restart Apache after changes.</p>";
    
    echo "<h4>For Nginx:</h4>";
    echo "<p>Go to Website Settings > URL Rewrite and add:</p>";
    echo "<pre style='background: #1a1a2e; color: #00f0ff; padding: 10px;'>";
    echo "location / {\n";
    echo "    try_files \$uri \$uri/ /index.php?url=\$uri&\$query_string;\n";
    echo "}";
    echo "</pre>";
    exit;
}

// Initialize application
use Core\App;

$app = new App();
$app->run();

// Flush output
ob_end_flush();
