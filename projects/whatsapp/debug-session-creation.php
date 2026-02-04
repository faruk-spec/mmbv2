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
    
    // Check 4: Can we query existing sessions?
    echo "\n4. Checking existing sessions (all users)...\n";
    try {
        $stmt = $db->query("SELECT id, user_id, session_name, status, qr_code IS NOT NULL as has_qr FROM whatsapp_sessions ORDER BY id DESC LIMIT 10");
        $sessions = $stmt->fetchAll();
        echo "   ✓ Found " . count($sessions) . " existing sessions\n";
        if (count($sessions) > 0) {
            echo "   Recent sessions:\n";
            foreach ($sessions as $session) {
                $qrStatus = $session['has_qr'] ? 'Has QR' : 'No QR';
                echo "     - ID " . $session['id'] . " (User " . $session['user_id'] . "): " . $session['session_name'] . " [" . $session['status'] . "] - " . $qrStatus . "\n";
            }
        }
    } catch (\Exception $e) {
        echo "   ✗ Query error: " . $e->getMessage() . "\n";
    }
    
    // Check 5: Test INSERT query syntax (without executing)
    echo "\n5. Testing Database query() method...\n";
    try {
        // Test that Database::query() method exists and works
        $testResult = $db->query("SELECT 1 as test");
        $row = $testResult->fetch();
        if ($row && $row['test'] == 1) {
            echo "   ✓ Database::query() method works correctly\n";
            echo "   ✓ INSERT would use: \$db->query(\$sql, [\$userId, \$sessionId, \$sessionName])\n";
        }
    } catch (\Exception $e) {
        echo "   ✗ Query test error: " . $e->getMessage() . "\n";
    }
    
    // Check 6: Test bridge connectivity
    echo "\n6. Testing bridge server connectivity...\n";
    try {
        $bridgeUrl = 'http://127.0.0.1:3000/api/health';
        $response = @file_get_contents($bridgeUrl);
        if ($response) {
            $data = json_decode($response, true);
            if ($data && isset($data['success']) && $data['success']) {
                echo "   ✓ Bridge server is running and healthy\n";
                echo "   Response: " . $data['message'] . "\n";
            } else {
                echo "   ⚠ Bridge server responded but returned unexpected data\n";
            }
        } else {
            echo "   ✗ Cannot connect to bridge server at $bridgeUrl\n";
            echo "   Make sure bridge is running: cd whatsapp-bridge && npm start\n";
        }
    } catch (\Exception $e) {
        echo "   ✗ Bridge test error: " . $e->getMessage() . "\n";
    }
    
    // Check 7: Check user authentication (informational only - not needed for CLI)
    echo "\n7. Checking web session authentication (informational)...\n";
    try {
        require_once __DIR__ . '/../../core/Auth.php';
        $user = Core\Auth::user();
        if ($user) {
            echo "   ✓ Web user is authenticated\n";
            echo "   User ID: " . $user['id'] . ", Username: " . $user['username'] . "\n";
        } else {
            echo "   ℹ No web session (expected for CLI script)\n";
            echo "   Note: Web authentication happens in browser, not CLI\n";
        }
    } catch (\Exception $e) {
        echo "   ⚠ Auth check: " . $e->getMessage() . "\n";
        echo "   Note: This is normal for CLI scripts\n";
    }
    
} catch (\Exception $e) {
    echo "   ✗ Fatal error: " . $e->getMessage() . "\n";
    echo "   Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== Debug Complete ===\n";
echo "\n📋 IMPORTANT: Understanding the QR Generation Flow\n";
echo "============================================================\n";
echo "1. Session Creation: Creates database record (status='initializing', qr_code=NULL)\n";
echo "2. User clicks 'Scan QR': Frontend calls /sessions/qr?session_id=X\n";
echo "3. PHP calls bridge: POST http://127.0.0.1:3000/api/generate-qr\n";
echo "4. Bridge generates QR using WhatsApp Web.js\n";
echo "5. QR returned to frontend and displayed\n";
echo "\n⚠️ NULL QR codes in database are NORMAL until user clicks 'Scan QR'!\n";
echo "\n📍 Next Steps:\n";
echo "If all checks passed:\n";
echo "  1. Log into web interface as a user\n";
echo "  2. Open browser console (F12)\n";
echo "  3. Click 'Create Session' - should succeed\n";
echo "  4. Click 'Scan QR' button\n";
echo "  5. Check browser console for errors\n";
echo "  6. Check bridge logs: tail -f whatsapp-bridge/bridge-server.log\n";
echo "\nIf 'Scan QR' fails with 400/500:\n";
echo "  - Check browser console for JavaScript errors\n";
echo "  - Verify user is logged in (check cookies/session)\n";
echo "  - Check PHP error log: tail -f /var/log/php-fpm/error.log\n";
echo "  - Check bridge logs for POST /api/generate-qr requests\n";
echo "\nFor more help: See GOOD_NEWS.md in this directory\n";
echo "============================================================\n";
