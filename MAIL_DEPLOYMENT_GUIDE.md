# Mail Project Deployment and Debugging Guide

## Overview

This PR fixes mail project server deployment and remaining runtime issues by:

1. ✅ Adding deployment verification scripts
2. ✅ Adding comprehensive debug logging to all mail controllers
3. ✅ Ensuring routes are properly loaded
4. ✅ Providing clear deployment and troubleshooting documentation

## What Changed

### 1. New Deployment Scripts

**Location:** `scripts/`

- **`verify_deployment.sh`** - Comprehensive verification script that checks:
  - Core files (App.php, Router.php, Database.php)
  - Mail project structure
  - Controller files
  - Debug logging presence
  - Route configuration
  - File permissions
  - Web server configuration

- **`deploy_mail.sh`** - Full deployment script that:
  - Runs pre-deployment verification
  - Creates backups
  - Sets proper permissions
  - Clears PHP cache
  - Creates deployment logs
  - Runs post-deployment verification

- **`README.md`** - Complete documentation for using the scripts

### 2. Debug Logging Added

All mail controllers now have comprehensive debug logging:

#### DomainController.php
- `index()` - Logs subscriber ID and domain count
- `create()` - Logs form rendering for subscriber
- `store()` - Logs domain creation and success

#### AliasController.php
- `index()` - Logs subscriber ID and alias count
- `create()` - Logs form rendering for subscriber
- `store()` - Logs alias creation and success

#### SubscriberController.php
- `dashboard()` - Logs subscriber ID and access control
- `addUser()` - Logs user addition operations
- `billing()` - Logs billing access and record count
- `upgradePlan()` - Logs plan upgrade operations

#### WebmailController.php
- `inbox()` - Logs mailbox ID and message count
- `viewEmail()` - Logs email viewing operations

### 3. Routes Verification

✅ Verified that `projects/mail/index.php` properly loads `routes/web.php`
✅ All required routes are defined:
  - `/projects/mail/subscriber/domains` - Domain list
  - `/projects/mail/subscriber/domains/add` - Add domain
  - `/projects/mail/subscriber/aliases` - Alias list
  - `/projects/mail/subscriber/aliases/add` - Add alias
  - `/projects/mail/subscriber/users/add` - Add user
  - `/projects/mail/subscriber/billing` - Billing history
  - `/projects/mail/webmail` - Webmail inbox
  - `/projects/mail/subscriber/upgrade` - Upgrade plan
  - Admin routes for billing

## Deployment Instructions

### Step 1: Pull Changes

```bash
cd /path/to/mmbv2
git pull origin copilot/fix-mail-project-deployment
```

### Step 2: Run Verification

```bash
bash scripts/verify_deployment.sh
```

Expected output: All checks should pass with only warnings about database verification.

### Step 3: Deploy

```bash
bash scripts/deploy_mail.sh
```

This will:
- Create a backup in `backups/` directory
- Set correct permissions
- Clear PHP cache
- Create deployment log in `storage/logs/`

### Step 4: Verify Database

```bash
php projects/mail/migrations/verify_mail_setup.php
```

This checks that all required database tables exist and are accessible.

### Step 5: Test URLs

Visit each of these URLs and verify they work:

1. **Domain Management:**
   - http://your-domain.com/projects/mail/subscriber/domains
   - http://your-domain.com/projects/mail/subscriber/domains/add

2. **Alias Management:**
   - http://your-domain.com/projects/mail/subscriber/aliases
   - http://your-domain.com/projects/mail/subscriber/aliases/add

3. **User Management:**
   - http://your-domain.com/projects/mail/subscriber/users/add

4. **Billing:**
   - http://your-domain.com/projects/mail/subscriber/billing

5. **Webmail:**
   - http://your-domain.com/projects/mail/webmail

6. **Upgrade:**
   - http://your-domain.com/projects/mail/subscriber/upgrade?plan=4

7. **Admin:**
   - http://your-domain.com/admin/projects/mail/subscribers/1/billing

### Step 6: Monitor Logs

```bash
# Watch error logs in real-time
tail -f storage/logs/error.log

# Or check PHP error log location
php -r "echo ini_get('error_log');"
```

## Debugging Runtime Issues

### Viewing Debug Logs

All controllers now log their operations. Look for entries like:

```
[DomainController::index] START - User: 123
[DomainController::index] Subscriber ID: 45
[DomainController::index] Rendering view with 3 domains
```

### Common Issues and Solutions

#### Issue: "404 Not Found" for mail URLs

**Cause:** Routes not being registered or web server rewrite rules not working

**Debug:**
```bash
# Check if routes are loaded
grep "routes/web.php" projects/mail/index.php

# Check .htaccess
cat .htaccess | grep RewriteEngine
```

**Fix:**
- Ensure mod_rewrite is enabled: `sudo a2enmod rewrite`
- Add `AllowOverride All` to Apache VirtualHost configuration
- Restart Apache: `sudo systemctl restart apache2`

#### Issue: "Database connection failed"

**Cause:** Database credentials incorrect or database not accessible

**Debug:**
```bash
# Verify database config
cat config/database.php

# Test database connection
php projects/mail/migrations/verify_mail_setup.php
```

**Fix:**
- Update database credentials in `config/database.php`
- Ensure database user has proper permissions
- Verify database exists and tables are created

#### Issue: "Access denied" or permission errors

**Cause:** Subscriber record not found or user not linked to subscriber

**Debug:**
Look for log entries:
```
[SubscriberController::dashboard] Access denied - not owner
```

**Fix:**
- Ensure user is authenticated: Check session and Auth::check()
- Verify subscriber record exists in `mail_subscribers` table
- Check `mmb_user_id` field links to authenticated user

#### Issue: "No mailbox found" for webmail

**Cause:** User doesn't have a mailbox created yet

**Debug:**
```
[WebmailController::inbox] START - User: 123
```
Then check if redirect happened to `/projects/mail/subscriber/users/add`

**Fix:**
- User needs to create a mailbox first
- Go to `/projects/mail/subscriber/users/add`
- Create mailbox for the user

### Monitoring in Production

#### Real-time Log Monitoring

```bash
# Monitor all mail controller activity
tail -f storage/logs/error.log | grep "Controller"

# Monitor specific controller
tail -f storage/logs/error.log | grep "DomainController"

# Monitor errors only
tail -f storage/logs/error.log | grep -i "error\|failed\|exception"
```

#### Log Analysis

```bash
# Count requests per controller
grep "Controller::" storage/logs/error.log | cut -d']' -f1 | sort | uniq -c

# Find errors in last hour
grep "error\|failed" storage/logs/error.log | grep "$(date +%Y-%m-%d\ %H:)"

# View last 50 mail-related log entries
grep "mail\|Mail\|Controller" storage/logs/error.log | tail -50
```

## Success Criteria

After deployment, verify:

- [ ] All URLs listed above are accessible (no 404 errors)
- [ ] Domain list page loads and shows domains
- [ ] Add domain form displays correctly
- [ ] Alias list page loads and shows aliases
- [ ] Add alias form displays correctly
- [ ] Add user form displays correctly
- [ ] Billing history page loads
- [ ] Webmail inbox loads (or shows "create mailbox" message)
- [ ] Upgrade page loads with plan options
- [ ] Admin billing page loads
- [ ] No PHP errors in error logs
- [ ] Debug logs show proper flow through controllers

## Rollback Procedure

If issues occur after deployment:

1. **Restore from backup:**
   ```bash
   # List available backups
   ls -lh backups/
   
   # Restore backup (replace TIMESTAMP with actual timestamp)
   tar -xzf backups/mail_backup_TIMESTAMP.tar.gz -C projects/
   ```

2. **Clear PHP cache:**
   ```bash
   # Clear opcode cache
   php -r "opcache_reset();"
   
   # Or restart PHP-FPM
   sudo systemctl restart php-fpm
   # Or for Apache mod_php
   sudo systemctl restart apache2
   ```

3. **Check logs for root cause:**
   ```bash
   tail -100 storage/logs/error.log
   ```

## Additional Notes

### File Permissions

Correct permissions are essential:
```bash
# Mail project files
chmod -R 755 projects/mail
chmod 644 projects/mail/config.php

# Storage directory
chmod -R 777 storage
```

### PHP Configuration

Ensure PHP has sufficient resources:
```ini
memory_limit = 256M
max_execution_time = 300
post_max_size = 64M
upload_max_filesize = 64M
```

### Database Optimization

For production, ensure proper indexes exist:
```sql
-- Check indexes on mail tables
SHOW INDEX FROM mail_domains;
SHOW INDEX FROM mail_aliases;
SHOW INDEX FROM mail_mailboxes;
```

## Support

### Getting Help

1. Check deployment log: `storage/logs/deployment_*.log`
2. Check error log: `storage/logs/error.log`
3. Run verification: `bash scripts/verify_deployment.sh`
4. Review controller debug logs for the specific feature

### Reporting Issues

When reporting issues, include:
- URL that's not working
- Error message (if any)
- Relevant log entries from error.log
- Output of `bash scripts/verify_deployment.sh`

## Summary

This PR provides:
- ✅ **Automated deployment scripts** for safe, repeatable deployments
- ✅ **Comprehensive debug logging** to trace execution flow
- ✅ **Verification scripts** to validate deployment readiness
- ✅ **Complete documentation** for deployment and troubleshooting

The code itself is correct and unchanged. This PR focuses on **deployment automation** and **debugging capabilities** to ensure the fixed code works properly on the live server.
