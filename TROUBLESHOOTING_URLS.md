# Common URL Issues and Solutions

## Problem: URLs Still Not Working After Deployment

If you're still getting 404 errors or "Page Not Found" for mail project URLs after deployment, follow these troubleshooting steps:

### 1. Check Web Server Configuration

#### For Apache:
```bash
# Check if mod_rewrite is enabled
apache2ctl -M | grep rewrite

# If not listed, enable it:
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### For Nginx:
Check your site configuration has URL rewriting:
```nginx
location / {
    try_files $uri $uri/ /index.php?url=$uri&$query_string;
}
```

### 2. Verify .htaccess is Working

Create a test file:
```bash
cd /path/to/project
echo "RewriteTest" > .htaccess_test
```

Try accessing: `http://your-domain.com/.htaccess_test`

- If you can access it → `.htaccess` is NOT being read (AllowOverride issue)
- If you get 404 or 403 → Good, `.htaccess` is working

Remove test file:
```bash
rm .htaccess_test
```

#### Fix AllowOverride:
Edit your Apache VirtualHost configuration:
```apache
<Directory "/path/to/your/project">
    AllowOverride All
    Require all granted
</Directory>
```

Then restart Apache:
```bash
sudo systemctl restart apache2
```

### 3. Check Routes are Loaded

Add debug output to see if routes are being registered:

```bash
# Temporarily add to projects/mail/index.php before require routes
echo "Loading routes...\n";
require_once PROJECT_PATH . '/routes/web.php';
echo "Routes loaded!\n";
```

Access any mail URL and check if you see the debug output.

### 4. Verify Project Entry Point

The mail project should be accessed through the main index.php, not directly.

**Correct URLs:**
- `http://your-domain.com/projects/mail/subscriber/domains`
- `http://your-domain.com/projects/mail/subscriber/aliases`

**Incorrect:**
- `http://your-domain.com/projects/mail/index.php/subscriber/domains` ❌

### 5. Check Error Logs

```bash
# Apache error log
tail -f /var/log/apache2/error.log

# Nginx error log  
tail -f /var/log/nginx/error.log

# PHP-FPM error log
tail -f /var/log/php8.2-fpm.log

# Application error log
tail -f storage/logs/error.log
```

Look for:
- Route not found errors
- Controller not found errors
- Class autoloading errors
- Database connection errors

### 6. Test URL Routing

Add debug to main index.php to see what URI is being resolved:

```php
// Add after $app = new App(); in index.php
error_log("REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set'));
error_log("Resolved URI: " . $uri);
```

Then check error logs when accessing a mail URL.

### 7. Verify Database Connection

If URLs load but show errors:

```bash
cd /path/to/project
php projects/mail/migrations/verify_mail_setup.php
```

This will check:
- Database connectivity
- Required tables exist
- Subscriber records exist
- Active subscriptions

### 8. Clear All Caches

```bash
# Clear PHP OPcache
sudo systemctl restart php-fpm
# Or for Apache with mod_php
sudo systemctl restart apache2

# Clear browser cache
# Use Ctrl+Shift+R or Cmd+Shift+R

# Clear any application cache
rm -rf storage/cache/*
```

### 9. Check File Permissions

```bash
# Set correct permissions
chmod -R 755 projects/mail
chmod -R 777 storage
```

### 10. Test with Query String URL

If pretty URLs don't work, try query string format:
```
http://your-domain.com/?url=projects/mail/subscriber/domains
```

If this works, the issue is definitely with URL rewriting configuration.

## Quick Diagnostic Command

Run this comprehensive check:

```bash
cd /path/to/project

echo "=== URL Routing Diagnostic ==="
echo ""
echo "1. Check .htaccess exists:"
ls -la .htaccess
echo ""
echo "2. Check .htaccess content:"
head -20 .htaccess
echo ""
echo "3. Check routes file exists:"
ls -la projects/mail/routes/web.php
echo ""
echo "4. Check controllers exist:"
ls -la projects/mail/controllers/
echo ""
echo "5. Test PHP:"
php -v
echo ""
echo "6. Check Apache modules (if Apache):"
apache2ctl -M | grep rewrite
echo ""
echo "7. Run verification:"
php projects/mail/migrations/verify_mail_setup.php
```

## Still Not Working?

If none of the above work:

1. **Check deployment log:**
   ```bash
   cat storage/logs/deployment_*.log | tail -50
   ```

2. **Re-run deployment:**
   ```bash
   bash scripts/deploy_mail.sh
   ```

3. **Check if code is actually deployed:**
   ```bash
   grep "error_log.*DomainController" projects/mail/controllers/DomainController.php
   ```
   Should show debug logging statements.

4. **Test a simple route:**
   Add to `routes/web.php`:
   ```php
   $router->get('/test-route', function() {
       echo "Routes are working!";
   });
   ```
   
   Then access: `http://your-domain.com/test-route`

## Getting Help

When reporting URL issues, include:

1. Exact URL you're trying to access
2. Error message (404, 500, blank page, etc.)
3. Contents of error logs
4. Output of: `bash scripts/verify_deployment.sh`
5. Web server type (Apache/Nginx) and version
6. PHP version: `php -v`

## Common Error Messages

### "404 Not Found"
→ URL rewriting not working. Check steps 1-2 above.

### "Page Not Found" (custom error page)
→ Route not registered or controller method missing.

### "Access Denied"
→ No subscriber record. Run `verify_mail_setup.php` and follow instructions.

### "Class not found"
→ Autoloader issue. Check file permissions and clear OPcache.

### "Database connection failed"
→ Check `config/database.php` credentials.

### Blank/white page
→ PHP error with display_errors=Off. Check error logs.
