<?php
/**
 * Mail Project Verification Script
 * Run this to diagnose why you're getting "Access Denied" errors
 * 
 * Usage: php verify_mail_setup.php
 */

// Try to load bootstrap
$bootstrapPaths = [
    __DIR__ . '/../../bootstrap.php',
    __DIR__ . '/../../index.php',
    __DIR__ . '/../../../bootstrap.php',
];

$bootstrapLoaded = false;
foreach ($bootstrapPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $bootstrapLoaded = true;
        break;
    }
}

if (!$bootstrapLoaded) {
    die("ERROR: Could not find bootstrap.php. Run this from projects/mail/migrations/ directory.\n");
}

use Core\Auth;
use Core\Database;

echo "=== Mail Project Verification ===\n\n";

// 1. Check Auth
echo "1. Authentication:\n";
try {
    $isLoggedIn = Auth::check();
    echo "   Logged in: " . ($isLoggedIn ? "YES" : "NO") . "\n";
    
    if ($isLoggedIn) {
        $userId = Auth::id();
        echo "   User ID: $userId\n";
        $user = Auth::user();
        if ($user) {
            echo "   User email: " . ($user->email ?? 'N/A') . "\n";
            echo "   User name: " . ($user->name ?? 'N/A') . "\n";
            echo "   User role: " . ($user->role ?? 'N/A') . "\n";
        }
    } else {
        echo "   NOTE: Cannot check subscriber without logged-in user.\n";
        echo "   To test, login first then run this script.\n";
    }
} catch (\Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

// 2. Check Database
echo "\n2. Database Connection:\n";
try {
    $db = Database::getInstance();
    echo "   Connection: OK\n";
    
    // Test query
    $result = $db->fetch("SELECT 1 as test");
    echo "   Query test: " . ($result ? "OK" : "FAILED") . "\n";
} catch (\Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
    echo "   Check database configuration in config/database.php\n";
    exit(1);
}

// 3. Check Database Methods
echo "\n3. Database Methods:\n";
$requiredMethods = ['lastInsertId', 'fetch', 'fetchAll', 'query', 'insert'];
foreach ($requiredMethods as $method) {
    $exists = method_exists($db, $method);
    echo "   $method(): " . ($exists ? "OK" : "MISSING") . "\n";
    if (!$exists && $method === 'lastInsertId') {
        echo "      ERROR: This method is required! Update to latest code.\n";
    }
}

// 4. Check Tables
echo "\n4. Database Tables:\n";
$tables = [
    'mail_subscribers' => 'Required - stores subscriber accounts',
    'mail_subscriptions' => 'Required - stores active subscriptions',
    'mail_subscription_plans' => 'Required - stores available plans',
    'mail_domains' => 'Required - stores custom domains',
    'mail_mailboxes' => 'Required - stores email accounts',
    'mail_aliases' => 'Required - stores email aliases',
    'mail_billing_history' => 'Required - stores billing transactions',
    'mail_folders' => 'Optional - stores mailbox folders',
    'mail_messages' => 'Optional - stores email messages',
];

$missingTables = [];
foreach ($tables as $table => $description) {
    try {
        $exists = $db->fetch("SHOW TABLES LIKE ?", [$table]);
        if ($exists) {
            echo "   ✓ $table\n";
        } else {
            echo "   ✗ $table - MISSING\n";
            echo "      $description\n";
            $missingTables[] = $table;
        }
    } catch (\Exception $e) {
        echo "   ✗ $table - ERROR: " . $e->getMessage() . "\n";
        $missingTables[] = $table;
    }
}

if (!empty($missingTables)) {
    echo "\n   ERROR: Missing tables detected!\n";
    echo "   Run migrations: cd projects/mail/migrations && ./run_migrations.sh\n";
}

// 5. Check Subscription Plans
echo "\n5. Subscription Plans:\n";
try {
    $plans = $db->fetchAll("SELECT id, plan_name, is_active FROM mail_subscription_plans WHERE is_active = 1");
    if (empty($plans)) {
        echo "   ERROR: No active subscription plans found!\n";
        echo "   Run: mysql -u root -p testuser < projects/mail/schema.sql\n";
    } else {
        echo "   Found " . count($plans) . " active plan(s):\n";
        foreach ($plans as $plan) {
            echo "      - Plan ID {$plan['id']}: {$plan['plan_name']}\n";
        }
    }
} catch (\Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

// 6. Check Subscriber (only if logged in)
if (isset($isLoggedIn) && $isLoggedIn && isset($userId)) {
    echo "\n6. Subscriber Check for User ID $userId:\n";
    
    try {
        $subscriber = $db->fetch(
            "SELECT s.*, sub.status as subscription_status, sub.plan_id, sp.plan_name
             FROM mail_subscribers s 
             LEFT JOIN mail_subscriptions sub ON s.id = sub.subscriber_id 
             LEFT JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
             WHERE s.mmb_user_id = ?",
            [$userId]
        );
        
        if ($subscriber) {
            echo "   ✓ Subscriber found!\n";
            echo "      Subscriber ID: " . $subscriber['id'] . "\n";
            echo "      Account name: " . $subscriber['account_name'] . "\n";
            echo "      Subscriber status: " . $subscriber['status'] . "\n";
            echo "      Subscription status: " . ($subscriber['subscription_status'] ?? 'NONE') . "\n";
            echo "      Plan: " . ($subscriber['plan_name'] ?? 'NONE') . "\n";
            
            // Check for issues
            $issues = [];
            if ($subscriber['status'] !== 'active') {
                $issues[] = "Subscriber status is '{$subscriber['status']}' (should be 'active')";
            }
            if (!$subscriber['subscription_status']) {
                $issues[] = "No subscription found - create one!";
            } elseif ($subscriber['subscription_status'] !== 'active') {
                $issues[] = "Subscription status is '{$subscriber['subscription_status']}' (should be 'active')";
            }
            
            if (!empty($issues)) {
                echo "\n   ⚠ Issues found:\n";
                foreach ($issues as $issue) {
                    echo "      - $issue\n";
                }
                echo "\n   Fix subscription status:\n";
                echo "   UPDATE mail_subscriptions SET status='active' WHERE subscriber_id={$subscriber['id']};\n";
            } else {
                echo "\n   ✅ Subscriber setup is correct!\n";
            }
        } else {
            echo "   ✗ Subscriber NOT found!\n";
            echo "      This is why you get 'Access Denied' errors!\n\n";
            echo "   Quick fix:\n";
            echo "   1. Edit projects/mail/migrations/fix_access_denied.sql\n";
            echo "   2. Set: SET @mmb_user_id = $userId;\n";
            echo "   3. Run: mysql -u root -p testuser < fix_access_denied.sql\n";
        }
        
        // Check domains
        if ($subscriber) {
            $domainCount = $db->fetch(
                "SELECT COUNT(*) as count FROM mail_domains WHERE subscriber_id = ?",
                [$subscriber['id']]
            );
            echo "\n   Domains: " . ($domainCount['count'] ?? 0) . "\n";
            
            $mailboxCount = $db->fetch(
                "SELECT COUNT(*) as count FROM mail_mailboxes WHERE subscriber_id = ?",
                [$subscriber['id']]
            );
            echo "   Mailboxes: " . ($mailboxCount['count'] ?? 0) . "\n";
            
            if ($mailboxCount['count'] == 0) {
                echo "      NOTE: Create a mailbox at /projects/mail/subscriber/users/add\n";
            }
        }
        
    } catch (\Exception $e) {
        echo "   ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "\n6. Subscriber Check: SKIPPED (not logged in)\n";
    echo "   Login to check your subscriber status.\n";
}

// 7. Check Controllers
echo "\n7. Controller Files:\n";
$controllers = [
    'projects/mail/controllers/DomainController.php',
    'projects/mail/controllers/AliasController.php',
    'projects/mail/controllers/SubscriberController.php',
    'projects/mail/controllers/WebmailController.php',
];

foreach ($controllers as $controller) {
    $path = __DIR__ . '/../../' . $controller;
    if (file_exists($path)) {
        echo "   ✓ " . basename($controller) . "\n";
        
        // Check for syntax errors
        $output = shell_exec("php -l \"$path\" 2>&1");
        if (strpos($output, 'No syntax errors') === false) {
            echo "      ERROR: Syntax error detected!\n";
            echo "      " . trim($output) . "\n";
        }
    } else {
        echo "   ✗ " . basename($controller) . " - NOT FOUND\n";
    }
}

// 8. PHP Configuration
echo "\n8. PHP Configuration:\n";
echo "   PHP Version: " . PHP_VERSION . "\n";
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    echo "      WARNING: PHP 7.4+ required for best compatibility\n";
}

echo "   short_open_tag: " . (ini_get('short_open_tag') ? 'On' : 'Off') . "\n";
if (!ini_get('short_open_tag')) {
    echo "      WARNING: Views use <?= which requires short_open_tag=On\n";
}

echo "   opcache.enable: " . (ini_get('opcache.enable') ? 'On' : 'Off') . "\n";
if (ini_get('opcache.enable')) {
    echo "      NOTE: Clear opcache if you deployed new code\n";
}

// Summary
echo "\n=== Summary ===\n";

$allGood = true;
if (!empty($missingTables)) {
    echo "❌ Missing database tables - run migrations\n";
    $allGood = false;
}

if (isset($subscriber) && $subscriber) {
    if ($subscriber['status'] === 'active' && $subscriber['subscription_status'] === 'active') {
        echo "✅ Subscriber and subscription are active\n";
    } else {
        echo "⚠️  Subscriber found but status issues detected\n";
        $allGood = false;
    }
} elseif (isset($isLoggedIn) && $isLoggedIn) {
    echo "❌ No subscriber record - run fix_access_denied.sql\n";
    $allGood = false;
}

if ($allGood && isset($isLoggedIn) && $isLoggedIn) {
    echo "\n✅ Everything looks good!\n";
    echo "If you still get errors:\n";
    echo "1. Clear PHP opcache: sudo systemctl restart php-fpm\n";
    echo "2. Clear browser cache\n";
    echo "3. Check error logs: tail -f /var/log/apache2/error.log\n";
} else {
    echo "\nFix the issues above and re-run this script.\n";
}

echo "\n=== End of Verification ===\n";
?>
