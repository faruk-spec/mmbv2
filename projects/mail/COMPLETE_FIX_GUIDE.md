# Complete Diagnostic and Fix Guide

## Issue Summary

You have subscriber data (subscriber_id=3, mmb_user_id=3, active subscription) but still getting "access denied" errors.

## Root Causes Identified

### 1. Code Not Deployed or Cached
The most likely issue is that the fixed code hasn't been deployed to the server or is cached.

**SOLUTION:**
```bash
# SSH to your server
cd /path/to/mmbv2

# Pull latest code
git fetch origin
git checkout copilot/fix-access-denied-issues
git pull origin copilot/fix-access-denied-issues

# Clear PHP opcache
sudo systemctl restart php-fpm
# OR
sudo systemctl restart apache2
# OR if using CLI
php -r "opcache_reset();"

# Clear application cache
rm -rf storage/cache/*
rm -rf storage/views/*
```

### 2. PHP Version or Configuration Issues

The "syntax error, unexpected variable $this" suggests PHP configuration issues.

**Check your PHP version:**
```bash
php -v
```

**Required:** PHP 7.4 or higher

**Check short_open_tag setting:**
```bash
php -i | grep short_open_tag
```

If `short_open_tag = Off`, you need to enable it OR change all `<?=` to `<?php echo` in views.

**Fix in php.ini:**
```ini
short_open_tag = On
```

Then restart PHP:
```bash
sudo systemctl restart php-fpm
```

### 3. Verify Database Connection

Test if the code can connect to database:

```php
// Create test file: test_db.php in root directory
<?php
require_once __DIR__ . '/bootstrap.php';

use Core\Auth;
use Core\Database;

// Check auth
echo "Auth check: " . (Auth::check() ? "YES" : "NO") . "\n";
echo "User ID: " . Auth::id() . "\n";

// Check database
$db = Database::getInstance();
$subscriber = $db->fetch(
    "SELECT s.*, sub.status FROM mail_subscribers s 
     JOIN mail_subscriptions sub ON s.id = sub.subscriber_id 
     WHERE s.mmb_user_id = ?",
    [Auth::id()]
);

echo "Subscriber found: " . ($subscriber ? "YES" : "NO") . "\n";
if ($subscriber) {
    print_r($subscriber);
}
?>
```

Run it:
```bash
php test_db.php
```

### 4. Verify Routes Are Correct

Check if routes file includes mail routes:

```bash
grep "projects/mail" routes/web.php
```

Should see routes like:
- `/projects/mail/subscriber/domains`
- `/projects/mail/subscriber/aliases`
- etc.

### 5. Check File Permissions

```bash
# Make sure web server can read files
chmod -R 755 projects/mail
chmod -R 644 projects/mail/views/*
chmod -R 644 projects/mail/controllers/*
```

## Step-by-Step Fix Procedure

### Step 1: Deploy Code

```bash
cd /path/to/mmbv2
git status
git fetch origin
git checkout copilot/fix-access-denied-issues
git pull origin copilot/fix-access-denied-issues
git log --oneline -5  # Verify you have latest commits
```

Latest commits should include:
- 0b7b364 Add SQL troubleshooting
- d9e1da0 Fix: Add missing lastInsertId()
- 3b2f803 Fix controller authentication

### Step 2: Clear All Caches

```bash
# PHP OPcache
sudo systemctl restart php-fpm  # or php8.1-fpm, php8.2-fpm etc
sudo systemctl restart apache2  # or nginx

# Application cache
rm -rf storage/cache/*
rm -rf storage/views/*
rm -rf storage/framework/cache/*

# Browser cache
# Clear your browser cache or open incognito window
```

### Step 3: Verify Database

```bash
mysql -u root -p testuser
```

```sql
-- Check subscriber exists
SELECT * FROM mail_subscribers WHERE mmb_user_id = 3;

-- Check subscription exists and is active
SELECT * FROM mail_subscriptions WHERE subscriber_id = 3 AND status = 'active';

-- If subscription is NOT active, fix it:
UPDATE mail_subscriptions SET status = 'active' WHERE subscriber_id = 3;
```

### Step 4: Test Access

1. Log out completely
2. Clear browser cache
3. Log back in
4. Try accessing `/projects/mail/subscriber/domains`

### Step 5: Check PHP Error Logs

If still not working, check logs:

```bash
# Apache error log
tail -f /var/log/apache2/error.log

# PHP-FPM error log
tail -f /var/log/php8.1-fpm/error.log

# Application log
tail -f storage/logs/laravel.log  # or wherever your app logs
```

## Quick Verification Script

Save this as `verify_mail_setup.php` in your root:

```php
<?php
require_once __DIR__ . '/bootstrap.php';

use Core\Auth;
use Core\Database;

echo "=== Mail Project Verification ===\n\n";

// 1. Check Auth
echo "1. Authentication:\n";
$isLoggedIn = Auth::check();
echo "   Logged in: " . ($isLoggedIn ? "YES" : "NO") . "\n";
if ($isLoggedIn) {
    $userId = Auth::id();
    echo "   User ID: $userId\n";
    $user = Auth::user();
    echo "   User email: " . ($user->email ?? 'N/A') . "\n";
} else {
    echo "   ERROR: Not logged in!\n";
    exit(1);
}

// 2. Check Database
echo "\n2. Database Connection:\n";
try {
    $db = Database::getInstance();
    echo "   Connection: OK\n";
} catch (\Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Check Subscriber
echo "\n3. Subscriber Check:\n";
$subscriber = $db->fetch(
    "SELECT s.*, sub.status as subscription_status, sub.plan_id 
     FROM mail_subscribers s 
     LEFT JOIN mail_subscriptions sub ON s.id = sub.subscriber_id 
     WHERE s.mmb_user_id = ?",
    [$userId]
);

if ($subscriber) {
    echo "   Subscriber found: YES\n";
    echo "   Subscriber ID: " . $subscriber['id'] . "\n";
    echo "   Account name: " . $subscriber['account_name'] . "\n";
    echo "   Status: " . $subscriber['status'] . "\n";
    echo "   Subscription status: " . ($subscriber['subscription_status'] ?? 'NONE') . "\n";
    echo "   Plan ID: " . ($subscriber['plan_id'] ?? 'NONE') . "\n";
    
    if ($subscriber['status'] !== 'active') {
        echo "   WARNING: Subscriber status is not 'active'!\n";
    }
    if (!$subscriber['subscription_status']) {
        echo "   ERROR: No subscription found!\n";
    } elseif ($subscriber['subscription_status'] !== 'active') {
        echo "   WARNING: Subscription status is not 'active'!\n";
    }
} else {
    echo "   Subscriber found: NO\n";
    echo "   ERROR: This is why you get 'Access Denied'!\n";
    echo "   Run fix_access_denied.sql to create subscription.\n";
}

// 4. Check Tables
echo "\n4. Database Tables:\n";
$tables = ['mail_subscribers', 'mail_subscriptions', 'mail_subscription_plans', 
           'mail_domains', 'mail_mailboxes', 'mail_aliases', 'mail_billing_history'];

foreach ($tables as $table) {
    $exists = $db->fetch("SHOW TABLES LIKE ?", [$table]);
    echo "   $table: " . ($exists ? "OK" : "MISSING") . "\n";
}

// 5. Check lastInsertId method
echo "\n5. Database Methods:\n";
if (method_exists($db, 'lastInsertId')) {
    echo "   lastInsertId(): OK\n";
} else {
    echo "   lastInsertId(): MISSING - This will cause errors!\n";
}

echo "\n=== Verification Complete ===\n";

if ($subscriber && $subscriber['subscription_status'] === 'active') {
    echo "✅ Everything looks good! If you still get errors, clear caches.\n";
} else {
    echo "❌ Issues found! Fix them and re-run this script.\n";
}
?>
```

Run it:
```bash
# As root or logged-in user
php verify_mail_setup.php

# Via web browser (create a route):
# Visit: https://test.mymultibranch.com/verify-mail
```

## Common Issues and Solutions

### Issue: "Access Denied" even with correct data
**Solution:** Code not deployed or cached. Deploy and clear caches.

### Issue: "Syntax error, unexpected variable $this"
**Solution:** PHP version < 7.4 or short_open_tag is Off. Update PHP or enable short_open_tag.

### Issue: "No active mailbox found"
**Solution:** After getting subscriber access, you need to create a mailbox at `/projects/mail/subscriber/users/add`

### Issue: "Table doesn't exist"
**Solution:** Migrations not run. Run: `cd projects/mail/migrations && ./run_migrations.sh`

### Issue: Blank pages
**Solution:** PHP error. Check error logs. Usually means code not deployed.

## Final Checklist

- [ ] Code deployed (verify with `git log`)
- [ ] PHP version >= 7.4
- [ ] short_open_tag = On
- [ ] All caches cleared (PHP opcache, application cache, browser cache)
- [ ] Web server restarted
- [ ] Database migrations run
- [ ] Subscriber data exists (mmb_user_id matches logged-in user)
- [ ] Subscription is 'active'
- [ ] PHP error logs checked
- [ ] Verification script run successfully

## Still Not Working?

If after following ALL steps above you still have issues:

1. Share the output of `verify_mail_setup.php`
2. Share the last 50 lines of PHP error log
3. Confirm PHP version: `php -v`
4. Confirm current user ID when logged in
5. Confirm git commit hash: `git rev-parse HEAD`

This will help diagnose the exact issue.
