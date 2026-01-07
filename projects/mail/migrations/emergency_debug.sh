#!/bin/bash
# Emergency Debug Script for Mail Project Issues
# Run this to get comprehensive debugging information

echo "======================================"
echo "Mail Project Emergency Debug Script"
echo "======================================"
echo ""

# Check if running as root
if [ "$EUID" -eq 0 ]; then 
    echo "WARNING: Running as root. Some checks may not reflect actual user permissions."
fi

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../../.." && pwd)"

echo "Project Root: $PROJECT_ROOT"
echo ""

# 1. Check Git Status
echo "1. GIT STATUS"
echo "-------------"
cd "$PROJECT_ROOT"
echo "Current branch:"
git branch | grep "*"
echo ""
echo "Last 5 commits:"
git log --oneline -5
echo ""
echo "Uncommitted changes:"
git status --short
echo ""

# 2. Check PHP Version
echo "2. PHP VERSION"
echo "-------------"
php -v | head -1
echo "PHP modules:"
php -m | grep -E "(pdo|mysqli|opcache|json)" | sort
echo ""

# 3. Check Important Files
echo "3. FILE VERIFICATION"
echo "-------------------"

files_to_check=(
    "core/Database.php"
    "projects/mail/controllers/DomainController.php"
    "projects/mail/controllers/SubscriberController.php"
    "projects/mail/controllers/AliasController.php"
    "projects/mail/routes/web.php"
)

for file in "${files_to_check[@]}"; do
    filepath="$PROJECT_ROOT/$file"
    if [ -f "$filepath" ]; then
        echo "✓ $file"
        echo "  Modified: $(stat -c %y "$filepath" 2>/dev/null || stat -f "%Sm" "$filepath")"
        echo "  Size: $(stat -c %s "$filepath" 2>/dev/null || stat -f "%z" "$filepath") bytes"
    else
        echo "✗ $file - NOT FOUND"
    fi
done
echo ""

# 4. Check Database.php for lastInsertId
echo "4. DATABASE CLASS CHECK"
echo "----------------------"
if grep -q "function lastInsertId" "$PROJECT_ROOT/core/Database.php"; then
    echo "✓ lastInsertId() method EXISTS"
    grep -n "function lastInsertId" "$PROJECT_ROOT/core/Database.php"
else
    echo "✗ lastInsertId() method MISSING"
    echo "  This is a critical issue!"
fi
echo ""

# 5. Check PHP Syntax
echo "5. PHP SYNTAX CHECK"
echo "------------------"
for file in "${files_to_check[@]}"; do
    if [[ $file == *.php ]]; then
        filepath="$PROJECT_ROOT/$file"
        if [ -f "$filepath" ]; then
            result=$(php -l "$filepath" 2>&1)
            if echo "$result" | grep -q "No syntax errors"; then
                echo "✓ $file - OK"
            else
                echo "✗ $file - SYNTAX ERROR:"
                echo "  $result"
            fi
        fi
    fi
done
echo ""

# 6. Check Web Server
echo "6. WEB SERVER CHECK"
echo "------------------"
if systemctl is-active --quiet apache2; then
    echo "Apache2: RUNNING"
    echo "Config test:"
    apache2ctl configtest 2>&1 | head -5
elif systemctl is-active --quiet nginx; then
    echo "Nginx: RUNNING"
    echo "Config test:"
    nginx -t 2>&1
else
    echo "WARNING: Neither Apache2 nor Nginx is running"
fi
echo ""

# 7. Check PHP-FPM
echo "7. PHP-FPM CHECK"
echo "---------------"
if systemctl is-active --quiet php8.1-fpm 2>/dev/null; then
    echo "PHP 8.1-FPM: RUNNING"
elif systemctl is-active --quiet php8.2-fpm 2>/dev/null; then
    echo "PHP 8.2-FPM: RUNNING"
elif systemctl is-active --quiet php-fpm 2>/dev/null; then
    echo "PHP-FPM: RUNNING"
else
    echo "WARNING: PHP-FPM not running or not found"
fi
echo ""

# 8. Check OPcache
echo "8. OPCACHE STATUS"
echo "----------------"
opcache_status=$(php -r "echo ini_get('opcache.enable') ? 'Enabled' : 'Disabled';")
echo "OPcache: $opcache_status"
if [ "$opcache_status" = "Enabled" ]; then
    echo "WARNING: OPcache is enabled. Clear it after code deployment:"
    echo "  sudo systemctl restart php-fpm"
fi
echo ""

# 9. Check Database Connection
echo "9. DATABASE CONNECTION"
echo "---------------------"
db_config="$PROJECT_ROOT/config/database.php"
if [ -f "$db_config" ]; then
    echo "✓ Database config exists"
    # Try to extract database name (basic parsing)
    if grep -q "database" "$db_config"; then
        echo "  Config file appears valid"
    fi
else
    echo "✗ Database config NOT FOUND at: $db_config"
fi
echo ""

# 10. Check Permissions
echo "10. FILE PERMISSIONS"
echo "-------------------"
ls -la "$PROJECT_ROOT/projects/mail/controllers/DomainController.php" 2>/dev/null | head -1
ls -la "$PROJECT_ROOT/core/Database.php" 2>/dev/null | head -1
echo ""

# 11. Check Error Logs
echo "11. RECENT ERROR LOGS"
echo "--------------------"
echo "Last 10 lines of PHP error log:"
if [ -f /var/log/apache2/error.log ]; then
    echo "Apache error log:"
    tail -10 /var/log/apache2/error.log 2>/dev/null
elif [ -f /var/log/nginx/error.log ]; then
    echo "Nginx error log:"
    tail -10 /var/log/nginx/error.log 2>/dev/null
else
    echo "Could not find error logs"
fi
echo ""

if [ -f /var/log/php8.1-fpm/error.log ]; then
    echo "PHP-FPM error log:"
    tail -10 /var/log/php8.1-fpm/error.log 2>/dev/null
fi
echo ""

# 12. Check Autoloader
echo "12. AUTOLOADER CHECK"
echo "-------------------"
if [ -f "$PROJECT_ROOT/vendor/autoload.php" ]; then
    echo "✓ Composer autoloader exists"
    echo "  Modified: $(stat -c %y "$PROJECT_ROOT/vendor/autoload.php" 2>/dev/null || stat -f "%Sm" "$PROJECT_ROOT/vendor/autoload.php")"
else
    echo "✗ Composer autoloader NOT FOUND"
    echo "  Run: composer dump-autoload"
fi
echo ""

# 13. Check Routes File
echo "13. ROUTES CHECK"
echo "---------------"
if [ -f "$PROJECT_ROOT/projects/mail/routes/web.php" ]; then
    echo "✓ Mail routes file exists"
    echo "  Route count: $(grep -c "router->" "$PROJECT_ROOT/projects/mail/routes/web.php")"
    echo "  Base URL: $(grep "baseUrl" "$PROJECT_ROOT/projects/mail/routes/web.php" | head -1)"
else
    echo "✗ Mail routes file NOT FOUND"
fi
echo ""

# 14. Summary
echo "======================================"
echo "SUMMARY & RECOMMENDED ACTIONS"
echo "======================================"
echo ""

# Determine issues
issues=0

if ! grep -q "function lastInsertId" "$PROJECT_ROOT/core/Database.php" 2>/dev/null; then
    echo "❌ CRITICAL: lastInsertId() method missing in Database class"
    echo "   Action: Deploy latest code from copilot/fix-access-denied-issues branch"
    issues=$((issues + 1))
fi

if [ "$opcache_status" = "Enabled" ]; then
    echo "⚠️  OPcache is enabled"
    echo "   Action: sudo systemctl restart php-fpm && sudo systemctl restart apache2"
    issues=$((issues + 1))
fi

if [ $issues -eq 0 ]; then
    echo "✅ No critical issues found in this check"
    echo ""
    echo "If you still have access denied errors:"
    echo "1. Run: php $PROJECT_ROOT/projects/mail/migrations/verify_mail_setup.php"
    echo "2. Check browser console for JavaScript errors"
    echo "3. Try accessing in incognito mode"
    echo "4. Check if user is actually logged in"
else
    echo ""
    echo "Found $issues issue(s). Fix them and re-run this script."
fi

echo ""
echo "For detailed help, see:"
echo "- $PROJECT_ROOT/projects/mail/COMPLETE_FIX_GUIDE.md"
echo "- $PROJECT_ROOT/projects/mail/SUBDOMAIN_MIGRATION_GUIDE.md"
echo ""
echo "======================================"
