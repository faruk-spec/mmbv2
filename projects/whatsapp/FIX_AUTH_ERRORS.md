# Fix Authentication Errors (400/500)

## Problem
User is logged in and subscribed but getting:
- **400 Error** when viewing QR code: "User not authenticated"  
- **500 Error** when creating session

## Diagnosis Tool

### Step 1: Check Authentication Status

**From Web Browser:**
```
https://mmbtech.online/projects/whatsapp/check-auth.php
```

**From Command Line:**
```bash
cd /www/wwwroot/mmbtech.online/projects/whatsapp
php check-auth.php
```

This will show:
- ✅ Session status
- ✅ Authentication state
- ✅ User details
- ✅ Subscription status
- ✅ Specific issues detected

---

## Common Causes & Solutions

### Issue A: Session Cookie Not Being Sent

**Symptoms:**
- Browser shows you're logged in on other pages
- WhatsApp pages show "User not authenticated"  
- check-auth.php shows: "No session cookie found"

**Cause:** Browser not sending session cookie to WhatsApp subdirectory

**Fix:**
```bash
# Check PHP session configuration
php -r "echo ini_get('session.cookie_path');"

# Should return "/" for site-wide cookies
# If it returns something else, update php.ini:

# Edit PHP config
nano /www/server/php/83/etc/php.ini

# Find and set:
session.cookie_path = /
session.cookie_httponly = 1

# Restart PHP-FPM
systemctl restart php-fpm
```

### Issue B: Session Expired

**Symptoms:**
- Was logged in, suddenly getting "not authenticated"
- check-auth.php shows: "No user_id in session"

**Fix:**
1. Clear browser cookies for the site
2. Log out completely
3. Log back in
4. Test again

### Issue C: Cross-Domain/HTTPS Issues

**Symptoms:**  
- Works on HTTP, fails on HTTPS
- Works on www subdomain, fails on apex domain

**Fix:**
```bash
# Update session config for secure cookies
nano /www/server/php/83/etc/php.ini

# Set these:
session.cookie_secure = 1     # For HTTPS only
session.cookie_samesite = Lax # Or "None" if needed

# Restart
systemctl restart php-fpm
```

### Issue D: Session Save Path Permissions

**Symptoms:**
- check-auth.php shows: "Session not started"
- PHP error log shows session warnings

**Fix:**
```bash
# Check session save path
php -r "echo ini_get('session.save_path');"

# Check permissions (should be writable by www user)
ls -la /var/lib/php/sessions

# Fix permissions if needed
chown -R www-data:www-data /var/lib/php/sessions
chmod 1733 /var/lib/php/sessions

# Or on your system (adjust as needed):
chown -R www:www /tmp
```

### Issue E: PHP Version Mismatch

**Symptoms:**
- Works in some parts of site, not others
- Different PHP version for CLI vs web

**Fix:**
```bash
# Check web PHP version
echo "<?php phpinfo(); ?>" > /www/wwwroot/mmbtech.online/phpinfo.php
# Visit: https://mmbtech.online/phpinfo.php

# Check CLI PHP version
php -v

# If different, update CLI PHP symlink:
update-alternatives --config php

# Or use specific version:
/usr/bin/php8.3 check-auth.php
```

### Issue F: Auth Check Timing Issue

**Symptoms:**
- Sometimes works, sometimes doesn't
- Random "not authenticated" errors

**Fix:** The SessionController might be instantiated before session is fully loaded.

**Test this:**
```bash
# Check if index.php starts session before loading controllers
grep -n "session_start" /www/wwwroot/mmbtech.online/projects/whatsapp/index.php
```

If not found, session might be starting too late.

---

## Testing Your Fix

### Test 1: Authentication Check
```bash
cd /www/wwwroot/mmbtech.online/projects/whatsapp
php check-auth.php
```

**Expected:** 
```
Overall Status: AUTHENTICATED
✅ User is properly authenticated
✅ Has active subscription: ENTERPRISE
```

### Test 2: Web Access
Visit in browser (while logged in):
```
https://mmbtech.online/projects/whatsapp/check-auth.php
```

**Expected JSON:**
```json
{
    "overall_status": "AUTHENTICATED",
    "user": {
        "id": 3,
        "name": "Your Name",
        "email": "your@email.com"
    },
    "subscription": {
        "plan_name": "ENTERPRISE",
        "status": "active"
    }
}
```

### Test 3: Create Session
1. Open browser DevTools (F12)
2. Go to: https://mmbtech.online/projects/whatsapp/
3. Click "Create Session"
4. Watch Network tab

**Expected:**
- Status: 200 (not 500)
- Response: `{"success":true,"message":"Session created successfully"}`

### Test 4: View QR Code
1. Click "Scan QR" on any session
2. Watch Network tab

**Expected:**
- Status: 200 (not 400)
- Response: QR code data or "Bridge not running" message

---

## Advanced Debugging

### Check PHP Error Log
```bash
tail -50 /var/log/php-fpm/error.log
```

Look for:
- Session warnings
- Authentication errors
- Database connection errors

### Check Application Log
```bash
tail -50 /www/wwwroot/mmbtech.online/storage/logs/app.log
```

### Enable Detailed Logging

Edit: `/www/wwwroot/mmbtech.online/projects/whatsapp/controllers/SessionController.php`

Add at top of `getQRCode()` method:
```php
error_log("getQRCode called - User: " . ($this->user ? $this->user['id'] : 'null'));
error_log("Session status: " . session_status());
error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'none'));
```

### Test with curl
```bash
# Get your session cookie from browser
# Chrome: DevTools > Application > Cookies
# Copy PHPSESSID value

# Test with cookie
curl -X GET \
  -H "Cookie: PHPSESSID=your_session_id_here" \
  "https://mmbtech.online/projects/whatsapp/check-auth.php"
```

---

## Still Not Working?

### Gather This Information:

1. **Authentication check output:**
```bash
php check-auth.php > auth-status.txt
cat auth-status.txt
```

2. **Browser console errors:**
- F12 > Console tab
- Screenshot any errors

3. **Network request details:**
- F12 > Network tab
- Click failed request
- Screenshot Headers and Response

4. **PHP error log:**
```bash
tail -100 /var/log/php-fpm/error.log > php-errors.txt
```

5. **Session configuration:**
```bash
php -i | grep session > session-config.txt
```

### Report These Files:
- auth-status.txt
- Browser console screenshot
- Network request screenshot  
- php-errors.txt
- session-config.txt

---

## Quick Fix Checklist

Try these in order:

- [ ] Clear browser cookies and log in again
- [ ] Check `php check-auth.php` shows AUTHENTICATED
- [ ] Verify session.cookie_path = / in php.ini
- [ ] Restart PHP-FPM: `systemctl restart php-fpm`
- [ ] Test in incognito/private window
- [ ] Check session save path permissions
- [ ] Verify HTTPS certificate valid (no warnings)
- [ ] Test with different browser
- [ ] Check if other site features work (profile page, etc.)

---

## Success Indicators

You'll know it's fixed when:

✅ `check-auth.php` shows "AUTHENTICATED"
✅ Browser console shows no 400/500 errors  
✅ "Create Session" returns success
✅ "Scan QR" shows "Bridge not running" (not "not authenticated")
✅ QR modal opens without errors

The "Bridge not running" message is OK - that's a separate issue from authentication!
