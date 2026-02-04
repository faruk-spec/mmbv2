<?php
/**
 * Debug Session Creation Error
 * This script helps identify why session creation is failing with 500 error
 */

// Define BASE_PATH constant (required by Database class)
define('BASE_PATH', dirname(dirname(__DIR__)));

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Debugging Session Creation ===\n\n";

// Check 1: Can we load core files?
echo "1. Checking core files...\n";
$coreFiles = [
    '../../core/Database.php',
    '../../core/Auth.php',
    '../../core/Security.php',
    '../../config/database.php'
];

foreach ($coreFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "   ✓ Found: $file\n";
    } else {
        echo "   ✗ Missing: $file\n";
    }
}

// Check 2: Can we connect to database?
echo "\n2. Testing database connection...\n";
try {
    require_once __DIR__ . '/../../core/Database.php';
    require_once __DIR__ . '/../../config/database.php';
    
    $db = Core\Database::getInstance();
    echo "   ✓ Database connection successful\n";
    
    // Check 3: Does whatsapp_sessions table exist?
    echo "\n3. Checking whatsapp_sessions table...\n";
    try {
        $result = $db->query("DESCRIBE whatsapp_sessions");
        echo "   ✓ whatsapp_sessions table exists\n";
        echo "   Columns:\n";
        foreach ($result as $row) {
            echo "     - " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } catch (\Exception $e) {
        echo "   ✗ whatsapp_sessions table error: " . $e->getMessage() . "\n";
    }
    
    // Check 4: Can we check user authentication?
    echo "\n4. Checking user authentication...\n";
    try {
        require_once __DIR__ . '/../../core/Auth.php';
        $user = Core\Auth::user();
        if ($user) {
            echo "   ✓ User is authenticated\n";
            echo "   User ID: " . $user['id'] . "\n";
            echo "   Username: " . $user['username'] . "\n";
        } else {
            echo "   ✗ User is NOT authenticated\n";
            echo "   This would cause session creation to fail\n";
        }
    } catch (\Exception $e) {
        echo "   ✗ Auth check error: " . $e->getMessage() . "\n";
    }
    
    // Check 5: Can we query existing sessions?
    echo "\n5. Checking existing sessions...\n";
    try {
        if (isset($user) && $user) {
            $sessions = $db->fetchAll("SELECT id, session_name, status FROM whatsapp_sessions WHERE user_id = ? LIMIT 5", [$user['id']]);
            echo "   ✓ Found " . count($sessions) . " existing sessions\n";
            foreach ($sessions as $session) {
                echo "     - Session " . $session['id'] . ": " . $session['session_name'] . " (" . $session['status'] . ")\n";
            }
        } else {
            echo "   ⚠ Skipped (no authenticated user)\n";
        }
    } catch (\Exception $e) {
        echo "   ✗ Query error: " . $e->getMessage() . "\n";
    }
    
    // Check 6: Test INSERT query (without actually inserting)
    echo "\n6. Testing INSERT query syntax...\n";
    try {
        $testSessionId = bin2hex(random_bytes(16));
        $testName = "Test Session";
        
        // Prepare the query (don't execute)
        $stmt = $db->prepare("
            INSERT INTO whatsapp_sessions (
                user_id, session_id, session_name, status, created_at
            ) VALUES (?, ?, ?, 'initializing', NOW())
        ");
        
        echo "   ✓ INSERT query prepared successfully\n";
        echo "   Query would insert: user_id=" . ($user['id'] ?? 'N/A') . ", session_id=$testSessionId, session_name=$testName\n";
        
    } catch (\Exception $e) {
        echo "   ✗ INSERT query error: " . $e->getMessage() . "\n";
    }
    
} catch (\Exception $e) {
    echo "   ✗ Fatal error: " . $e->getMessage() . "\n";
    echo "   Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== Debug Complete ===\n";
echo "\nIf all checks passed, the issue might be:\n";
echo "1. CSRF token validation failing\n";
echo "2. User session expired during request\n";
echo "3. Web server configuration issue\n";
echo "4. PHP-FPM configuration issue\n";
echo "\nCheck PHP error logs for more details:\n";
echo "- /var/log/php-fpm/error.log\n";
echo "- /var/log/nginx/error.log or /var/log/apache2/error.log\n";
