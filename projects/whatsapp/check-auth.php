<?php
/**
 * Authentication Debugging Tool
 * Run from command line or access via web to check authentication status
 * 
 * CLI: php check-auth.php
 * Web: https://your-domain.com/projects/whatsapp/check-auth.php
 */

// Bootstrap the application
define('BASE_PATH', dirname(dirname(__DIR__)));

// Load the project's autoloader
require_once BASE_PATH . '/core/Autoloader.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use Core\Auth;
use Core\Database;

// Determine if running from CLI or web
$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    header('Content-Type: application/json');
}

// Check authentication
$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'environment' => $isCli ? 'CLI' : 'Web',
    'checks' => []
];

// 1. Check if session is started
$result['checks']['session_started'] = session_status() === PHP_SESSION_ACTIVE;
$result['checks']['session_id'] = session_id() ?: 'No session';

// 2. Check session variables
$result['checks']['session_has_user_id'] = isset($_SESSION['user_id']);
$result['checks']['session_user_id'] = $_SESSION['user_id'] ?? null;

// 3. Check cookies
$result['checks']['cookies_enabled'] = !empty($_COOKIE);
$result['checks']['session_cookie'] = isset($_COOKIE[session_name()]);
$result['checks']['remember_token_cookie'] = isset($_COOKIE['remember_token']);

// 4. Check Auth::check()
$result['checks']['auth_check'] = Auth::check();

// 5. Try to get user
$user = null;
try {
    $user = Auth::user();
    $result['checks']['auth_user_success'] = true;
    $result['checks']['user_found'] = !empty($user);
    
    if ($user) {
        $result['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'status' => $user['status']
        ];
    }
} catch (\Exception $e) {
    $result['checks']['auth_user_success'] = false;
    $result['checks']['auth_user_error'] = $e->getMessage();
}

// 6. Check database connection
try {
    $db = Database::getInstance();
    $result['checks']['database_connected'] = true;
    
    // If we have a user ID, verify it exists in database
    if (isset($_SESSION['user_id'])) {
        $dbUser = $db->fetch("SELECT id, name, email, role, status FROM users WHERE id = ?", [$_SESSION['user_id']]);
        $result['checks']['user_exists_in_db'] = !empty($dbUser);
        
        if ($dbUser) {
            $result['database_user'] = $dbUser;
            $result['checks']['user_active'] = $dbUser['status'] === 'active';
        }
    }
} catch (\Exception $e) {
    $result['checks']['database_connected'] = false;
    $result['checks']['database_error'] = $e->getMessage();
}

// 7. Check subscription (if authenticated)
if ($user && !empty($user['id'])) {
    try {
        $subscription = $db->fetch("
            SELECT * FROM whatsapp_subscriptions 
            WHERE user_id = ? AND status = 'active'
            ORDER BY end_date DESC LIMIT 1
        ", [$user['id']]);
        
        $result['checks']['has_subscription'] = !empty($subscription);
        if ($subscription) {
            $result['subscription'] = [
                'plan_name' => $subscription['plan_name'],
                'status' => $subscription['status'],
                'sessions_limit' => $subscription['sessions_limit'],
                'start_date' => $subscription['start_date'],
                'end_date' => $subscription['end_date']
            ];
        }
    } catch (\Exception $e) {
        $result['checks']['subscription_check_error'] = $e->getMessage();
    }
}

// 8. PHP Session Configuration
$result['php_config'] = [
    'session.save_handler' => ini_get('session.save_handler'),
    'session.save_path' => ini_get('session.save_path'),
    'session.use_cookies' => ini_get('session.use_cookies'),
    'session.cookie_lifetime' => ini_get('session.cookie_lifetime'),
    'session.cookie_path' => ini_get('session.cookie_path'),
    'session.cookie_domain' => ini_get('session.cookie_domain'),
    'session.cookie_secure' => ini_get('session.cookie_secure'),
    'session.cookie_httponly' => ini_get('session.cookie_httponly'),
];

// 9. Server Information
$result['server'] = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
    'http_host' => $_SERVER['HTTP_HOST'] ?? 'N/A',
];

// 10. Overall Status
$isAuthenticated = $result['checks']['auth_check'] && 
                   $result['checks']['user_found'] && 
                   ($result['checks']['user_active'] ?? false);

$result['overall_status'] = $isAuthenticated ? 'AUTHENTICATED' : 'NOT AUTHENTICATED';

// 11. Diagnosis
$result['diagnosis'] = [];

if (!$result['checks']['session_started']) {
    $result['diagnosis'][] = '❌ Session not started - PHP session initialization failed';
}

if (!$result['checks']['session_has_user_id']) {
    $result['diagnosis'][] = '❌ No user_id in session - User not logged in or session expired';
}

if ($result['checks']['session_has_user_id'] && !$result['checks']['user_exists_in_db']) {
    $result['diagnosis'][] = '❌ User ID in session but user not found in database - Possible deleted user';
}

if (isset($result['checks']['user_active']) && !$result['checks']['user_active']) {
    $result['diagnosis'][] = '❌ User account is not active - Status: ' . ($result['database_user']['status'] ?? 'unknown');
}

if (!$result['checks']['session_cookie']) {
    $result['diagnosis'][] = '⚠️  No session cookie found - Browser may not be sending cookies';
}

if ($isAuthenticated) {
    $result['diagnosis'][] = '✅ User is properly authenticated';
    
    if (!isset($result['checks']['has_subscription']) || !$result['checks']['has_subscription']) {
        $result['diagnosis'][] = '⚠️  No active subscription found - May have limited access';
    } else {
        $result['diagnosis'][] = '✅ Has active subscription: ' . $result['subscription']['plan_name'];
    }
}

// 12. Recommendations
$result['recommendations'] = [];

if (!$isAuthenticated) {
    $result['recommendations'][] = 'Log in via web browser at: /auth/login';
    $result['recommendations'][] = 'Clear browser cookies and try again';
    $result['recommendations'][] = 'Check PHP session.save_path permissions: ' . ini_get('session.save_path');
}

if ($isCli) {
    $result['recommendations'][] = 'Note: CLI scripts cannot use web sessions. Use web interface for testing authentication.';
}

// Output results
if ($isCli) {
    // Format for CLI
    echo "\n";
    echo "==========================================\n";
    echo "   Authentication Status Check\n";
    echo "==========================================\n\n";
    
    echo "Environment: " . $result['environment'] . "\n";
    echo "Timestamp: " . $result['timestamp'] . "\n\n";
    
    echo "=== Session Status ===\n";
    echo "Session Started: " . ($result['checks']['session_started'] ? '✓' : '✗') . "\n";
    echo "Session ID: " . $result['checks']['session_id'] . "\n";
    echo "User ID in Session: " . ($result['checks']['session_has_user_id'] ? '✓ (' . $result['checks']['session_user_id'] . ')' : '✗') . "\n\n";
    
    echo "=== Authentication ===\n";
    echo "Auth::check(): " . ($result['checks']['auth_check'] ? '✓' : '✗') . "\n";
    echo "User Found: " . ($result['checks']['user_found'] ? '✓' : '✗') . "\n";
    
    if (isset($result['user'])) {
        echo "\nUser Details:\n";
        echo "  ID: " . $result['user']['id'] . "\n";
        echo "  Name: " . $result['user']['name'] . "\n";
        echo "  Email: " . $result['user']['email'] . "\n";
        echo "  Role: " . $result['user']['role'] . "\n";
        echo "  Status: " . $result['user']['status'] . "\n";
    }
    
    if (isset($result['subscription'])) {
        echo "\nSubscription:\n";
        echo "  Plan: " . $result['subscription']['plan_name'] . "\n";
        echo "  Status: " . $result['subscription']['status'] . "\n";
        echo "  Sessions Limit: " . $result['subscription']['sessions_limit'] . "\n";
    }
    
    echo "\n=== Overall Status ===\n";
    echo $result['overall_status'] . "\n\n";
    
    if (!empty($result['diagnosis'])) {
        echo "=== Diagnosis ===\n";
        foreach ($result['diagnosis'] as $diag) {
            echo $diag . "\n";
        }
        echo "\n";
    }
    
    if (!empty($result['recommendations'])) {
        echo "=== Recommendations ===\n";
        foreach ($result['recommendations'] as $rec) {
            echo "• " . $rec . "\n";
        }
    }
    
    echo "\n==========================================\n\n";
} else {
    // Output as JSON for web
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

// Exit with appropriate code
exit($isAuthenticated ? 0 : 1);
