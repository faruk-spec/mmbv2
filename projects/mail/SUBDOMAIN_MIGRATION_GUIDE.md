# Subdomain Migration Guide (mail.mymultibranch.com)

## Will Moving to Subdomain Fix the Issues?

**Short Answer: NO** - Moving to a subdomain won't fix the current issues if the code isn't working on the main domain.

**Why:** The "access denied" errors are code logic issues, not domain/URL issues. The same code will have the same bugs regardless of domain.

## What WILL Fix the Issues

Since you've already tried deployment, cache clearing, and configuration fixes, the problem is likely:

### 1. Code Version Mismatch

The code on your server might be different from this repository.

**Verify:**
```bash
cd /path/to/mmbv2
git log --oneline -5
```

**Should show:**
```
f438738 Add comprehensive diagnostic tools
0b7b364 Add SQL troubleshooting and fix scripts
d7c245e Add troubleshooting and fix scripts
00b6daa Improve lastInsertId() method
d9e1da0 Fix: Add missing lastInsertId() method
```

If you don't see these commits, the code isn't deployed.

### 2. Autoloader / Namespace Issues

If controllers aren't being loaded correctly:

**Check:**
```bash
cd /path/to/mmbv2
# Regenerate autoloader if using Composer
composer dump-autoload

# Or check if autoloader exists
ls -la vendor/autoload.php
```

### 3. Route Not Registered

The mail routes might not be loaded by the main router.

**Check main routes file:**
```bash
grep -r "projects/mail/routes" .
```

**The main routes file should include:**
```php
// Load mail project routes
require_once BASE_PATH . '/projects/mail/routes/web.php';
```

### 4. Base Controller Parent Call Issue

I noticed earlier that DomainController calls `parent::__construct()` but BaseController has no constructor. While PHP handles this gracefully, let me verify this isn't causing issues.

**Check if this causes problems:**
```bash
php -r "
class Base {}
class Child extends Base {
    public function __construct() {
        parent::__construct();  // This is OK in PHP
    }
}
\$c = new Child();
echo 'No error';
"
```

## If You Still Want Subdomain Setup

Moving to mail.mymultibranch.com can be beneficial for:
- **Isolation** - Separate server/configuration
- **SSL** - Easier certificate management
- **Scaling** - Dedicated resources

### Subdomain Setup Steps

#### 1. DNS Configuration

Add DNS record:
```
Type: A
Name: mail
Value: YOUR_SERVER_IP
TTL: 3600
```

Or CNAME:
```
Type: CNAME
Name: mail
Value: mymultibranch.com
TTL: 3600
```

#### 2. Web Server Configuration

**For Apache:**

Create `/etc/apache2/sites-available/mail.mymultibranch.com.conf`:
```apache
<VirtualHost *:80>
    ServerName mail.mymultibranch.com
    ServerAdmin admin@mymultibranch.com
    
    DocumentRoot /path/to/mmbv2/public
    
    <Directory /path/to/mmbv2/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Rewrite all requests to projects/mail
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_URI} !^/projects/mail
    RewriteRule ^(.*)$ /projects/mail/$1 [L,QSA]
    
    ErrorLog ${APACHE_LOG_DIR}/mail_error.log
    CustomLog ${APACHE_LOG_DIR}/mail_access.log combined
</VirtualHost>
```

Enable site:
```bash
sudo a2ensite mail.mymultibranch.com
sudo systemctl reload apache2
```

**For Nginx:**

Create `/etc/nginx/sites-available/mail.mymultibranch.com`:
```nginx
server {
    listen 80;
    server_name mail.mymultibranch.com;
    root /path/to/mmbv2/public;
    index index.php;
    
    # Rewrite all requests to projects/mail
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP-FPM configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    access_log /var/log/nginx/mail_access.log;
    error_log /var/log/nginx/mail_error.log;
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/mail.mymultibranch.com /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### 3. SSL Certificate

```bash
# Install certbot if not already
sudo apt install certbot python3-certbot-apache  # For Apache
# OR
sudo apt install certbot python3-certbot-nginx   # For Nginx

# Get certificate
sudo certbot --apache -d mail.mymultibranch.com  # For Apache
# OR
sudo certbot --nginx -d mail.mymultibranch.com   # For Nginx
```

#### 4. Update Application Configuration

Update mail routes to work with subdomain:

**Option A: Keep /projects/mail prefix** (easier, no changes needed)
- Access as: mail.mymultibranch.com/projects/mail/subscriber/domains

**Option B: Remove /projects/mail prefix** (cleaner URLs)
- Update `projects/mail/routes/web.php`, change:
  ```php
  $baseUrl = '/projects/mail';  // Old
  $baseUrl = '';  // New - for subdomain
  ```
- Access as: mail.mymultibranch.com/subscriber/domains

#### 5. Update Links in Views

If you chose Option B, update any hardcoded links in views:
```bash
cd projects/mail/views
# Find all links with /projects/mail
grep -r "/projects/mail" .

# Replace with relative URLs or subdomain-aware URLs
```

## Recommended Action Plan

**Don't move to subdomain yet.** First, fix the current issues:

### Step 1: Verify Code is Actually Deployed

```bash
# Check file modification time
ls -la /path/to/mmbv2/core/Database.php
# Should be recent (last few hours)

# Check if lastInsertId exists
grep -n "function lastInsertId" /path/to/mmbv2/core/Database.php
# Should show line 186 or similar
```

### Step 2: Run Diagnostic Script

```bash
cd /path/to/mmbv2
php projects/mail/migrations/verify_mail_setup.php
```

**Send me the output.** This will tell us exactly what's wrong.

### Step 3: Check Actual Error

Instead of "access denied", get the ACTUAL PHP error:

**Enable error display temporarily:**

In `/path/to/mmbv2/public/index.php`, add at top:
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
```

Then visit the page and see the REAL error message.

### Step 4: Check Routes Are Loaded

Create test file `/path/to/mmbv2/test_routes.php`:
```php
<?php
require_once __DIR__ . '/bootstrap.php';

// Check if mail routes are registered
$router = new Core\Router();
echo "Router class exists: " . (class_exists('Core\Router') ? "YES" : "NO") . "\n";

// Try to check registered routes
if (method_exists($router, 'getRoutes')) {
    $routes = $router->getRoutes();
    echo "Total routes: " . count($routes) . "\n";
    
    foreach ($routes as $route) {
        if (strpos($route['pattern'], 'mail') !== false) {
            echo "Mail route: " . $route['pattern'] . "\n";
        }
    }
}

// Check if controllers exist
$controllers = [
    'Mail\DomainController',
    'Mail\SubscriberController',
    'Mail\AliasController',
];

foreach ($controllers as $class) {
    echo "$class exists: " . (class_exists($class) ? "YES" : "NO") . "\n";
}
```

Run: `php test_routes.php`

## Summary

1. **Don't move to subdomain yet** - Won't fix code bugs
2. **Run diagnostic script** and send output
3. **Enable error display** to see real error
4. **Verify code is deployed** by checking file dates and content
5. **Check routes are loaded** with test script

**Only move to subdomain if:**
- Current issues are fixed
- You want better isolation/scaling
- You want cleaner URLs

Send me the output of the diagnostic script and the actual error messages, and I can help fix the real problem.
