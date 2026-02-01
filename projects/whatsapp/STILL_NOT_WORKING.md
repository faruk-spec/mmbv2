# WhatsApp Platform - Still Not Working? Troubleshooting Checklist

## If you've made all changes but it's still not working, follow this checklist:

### Step 1: Identify What's Actually Broken

First, determine EXACTLY what's not working:

- [ ] **Can't create sessions** (getting 500 error)
- [ ] **Can't view QR codes** (getting 400 error)
- [ ] **Bridge server won't start** (EADDRINUSE error)
- [ ] **QR codes not generating** (loading forever)
- [ ] **Sessions not appearing** (created but don't show up)
- [ ] **Other issue:** _____________________

### Step 2: Run Complete Diagnostics

```bash
cd /www/wwwroot/mmbtech.online/projects/whatsapp
./complete-diagnostics.sh
```

This will check:
- ✓ Bridge server status
- ✓ PHP connectivity
- ✓ Database connection
- ✓ Configuration files
- ✓ All dependencies

**Save the output** - it tells you exactly what's broken!

### Step 3: Common Issues and Fixes

#### Issue A: Bridge Server Not Accessible After Changes

**Symptoms:**
- Made changes but still getting "bridge server not responding"
- Health check fails

**Fix:**
```bash
# 1. STOP the old server completely
pkill -9 -f "node.*server.js"
sleep 2

# 2. Verify port is free
lsof -i :3000
# Should return nothing

# 3. Start with NEW configuration
cd /www/wwwroot/mmbtech.online/projects/whatsapp/whatsapp-bridge
npm start &

# 4. Wait 5 seconds and test
sleep 5
curl http://127.0.0.1:3000/api/health
```

**Expected Output:**
```json
{"success":true,"status":"running","message":"WhatsApp Bridge is operational"}
```

#### Issue B: PHP Can't Connect (Most Common)

**Symptoms:**
- Bridge server is running (confirmed by diagnostics)
- Health endpoint returns 200
- But QR codes still fail with 400 error

**Fix - Install PHP cURL:**
```bash
# For Ubuntu/Debian
apt-get update
apt-get install php-curl php-xml
service php-fpm restart

# For CentOS/RHEL
yum install php-curl php-xml
systemctl restart php-fpm

# Verify installation
php -m | grep curl
# Should show: curl
```

**Fix - Check PHP-FPM Configuration:**
```bash
# Find your php.ini file
php --ini

# Edit php.ini
nano /etc/php/8.1/fpm/php.ini  # Adjust version as needed

# Ensure these settings:
allow_url_fopen = On
extension=curl

# Restart PHP-FPM
service php-fpm restart
# Or
systemctl restart php-fpm
```

#### Issue C: Code Changes Not Applied

**Symptoms:**
- Made changes but behavior hasn't changed
- Still seeing old error messages

**Fix:**
```bash
# 1. Verify you're on the correct branch
cd /www/wwwroot/mmbtech.online
git branch
# Should show: * copilot/fix-json-error-and-navbar

# 2. Pull latest changes
git fetch origin
git pull origin copilot/fix-json-error-and-navbar

# 3. Restart PHP-FPM to clear OPcache
service php-fpm restart

# 4. Restart bridge server
cd projects/whatsapp
./restart-bridge.sh

# 5. Clear browser cache
# Open browser, press Ctrl+Shift+Delete, clear cache
```

#### Issue D: Database Table Missing

**Symptoms:**
- Sessions create but immediately fail
- Getting database-related errors

**Fix:**
```bash
# Check if tables exist
mysql -u your_user -p your_database -e "SHOW TABLES LIKE 'whatsapp%';"

# If whatsapp_sessions table is missing, create it:
mysql -u your_user -p your_database < projects/whatsapp/install.sql

# Or create manually:
mysql -u your_user -p your_database
```

```sql
CREATE TABLE IF NOT EXISTS whatsapp_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(255) NOT NULL,
    session_name VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'initializing',
    phone_number VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    disconnected_at TIMESTAMP NULL,
    last_activity TIMESTAMP NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
);
```

#### Issue E: Chrome/Puppeteer Dependencies Missing

**Symptoms:**
- Bridge server starts but crashes when generating QR
- Errors mentioning "Chrome" or "Puppeteer" in logs

**Fix:**
```bash
cd /www/wwwroot/mmbtech.online/projects/whatsapp/whatsapp-bridge

# Run the Chrome dependency installer
chmod +x install-chrome-deps.sh
sudo ./install-chrome-deps.sh

# Restart bridge server
cd ..
./restart-bridge.sh
```

#### Issue F: Firewall/Security Blocking

**Symptoms:**
- Everything works via command line
- Fails when accessed via web browser

**Fix:**
```bash
# Check if firewall is blocking localhost connections
iptables -L -n | grep 3000

# Temporarily disable firewall to test
systemctl stop firewalld  # CentOS/RHEL
ufw disable               # Ubuntu/Debian

# Test if it works now
# If yes, add firewall rule:
iptables -A INPUT -p tcp --dport 3000 -s 127.0.0.1 -j ACCEPT

# Re-enable firewall
systemctl start firewalld
ufw enable
```

#### Issue G: SELinux Blocking (CentOS/RHEL)

**Symptoms:**
- Works on Ubuntu but not on CentOS
- Getting "permission denied" in logs

**Fix:**
```bash
# Check SELinux status
getenforce

# Temporarily set to permissive mode
setenforce 0

# Test if it works now
# If yes, add SELinux policy or disable permanently:
nano /etc/selinux/config
# Set: SELINUX=disabled

# Reboot
reboot
```

### Step 4: Verify Each Component

Run these tests **in order** and note where it fails:

```bash
# Test 1: Bridge server running?
lsof -i :3000
# Expected: Should show node process

# Test 2: Health endpoint works?
curl http://127.0.0.1:3000/api/health
# Expected: {"success":true,"status":"running"}

# Test 3: PHP can connect via curl?
php -r 'echo file_get_contents("http://127.0.0.1:3000/api/health");'
# Expected: {"success":true,"status":"running"}

# Test 4: Database works?
php -r 'require_once "core/Database.php"; require_once "config/database.php"; $db = Core\Database::getInstance(); echo "OK\n";'
# Expected: OK

# Test 5: SessionController exists and has curl?
grep -n "curl_init" projects/whatsapp/controllers/SessionController.php
# Expected: Should show line numbers with curl_init

# Test 6: Bridge server listening on 0.0.0.0?
grep -n "0.0.0.0" projects/whatsapp/whatsapp-bridge/server.js
# Expected: Should show line with 0.0.0.0
```

**Record which test fails first** - that's your problem!

### Step 5: Test Via Web Interface

After fixing issues, test the actual functionality:

1. **Test Bridge Health (Web)**
   - Open: `https://mmbtech.online/projects/whatsapp/bridge-health.php`
   - Should show: `"overall_status": "SUCCESS"`

2. **Test Session Creation**
   - Go to: `https://mmbtech.online/projects/whatsapp/sessions`
   - Click "New Session"
   - Enter name: "Test Session"
   - Click "Create"
   - Should succeed without 500 error

3. **Test QR Code**
   - Click "Scan QR" on the test session
   - Should show QR code within 10-15 seconds
   - Should NOT show 400 error

4. **Check Browser Console**
   - Press F12 to open DevTools
   - Go to Console tab
   - Look for any red errors
   - Save the errors and report them

### Step 6: Enable Debug Logging

If still not working, enable detailed logging:

```bash
# 1. Enable PHP error logging
echo "error_reporting = E_ALL" >> /etc/php/8.1/fpm/php.ini
echo "display_errors = On" >> /etc/php/8.1/fpm/php.ini
echo "log_errors = On" >> /etc/php/8.1/fpm/php.ini
service php-fpm restart

# 2. View PHP errors in real-time
tail -f /var/log/php-fpm/error.log

# 3. View bridge server logs
tail -f /www/wwwroot/mmbtech.online/projects/whatsapp/whatsapp-bridge/bridge-server.log

# 4. In another terminal, try to create a session and watch both logs
```

### Step 7: Report the Issue

If you've tried everything and it's still not working, gather this information:

```bash
# Run this command and save the output:
cd /www/wwwroot/mmbtech.online/projects/whatsapp
./complete-diagnostics.sh > diagnostics-output.txt 2>&1

# Also gather these logs:
tail -100 /var/log/php-fpm/error.log > php-errors.txt
tail -100 whatsapp-bridge/bridge-server.log > bridge-errors.txt
```

Then provide:
1. ✓ Output of complete-diagnostics.sh
2. ✓ PHP error log (last 100 lines)
3. ✓ Bridge server log (last 100 lines)
4. ✓ Browser console errors (screenshot)
5. ✓ Exact error message you're seeing
6. ✓ Which step in this guide failed

## Quick Reference: Most Common Fixes

### 1. Bridge Server Not Starting
```bash
pkill -9 -f "node.*server.js"
cd /www/wwwroot/mmbtech.online/projects/whatsapp/whatsapp-bridge
npm install
npm start &
```

### 2. PHP Can't Connect
```bash
apt-get install php-curl
service php-fpm restart
```

### 3. Changes Not Applied
```bash
git pull origin copilot/fix-json-error-and-navbar
service php-fpm restart
cd projects/whatsapp && ./restart-bridge.sh
```

### 4. Still Broken After All This?
```bash
# Nuclear option - restart everything
cd /www/wwwroot/mmbtech.online

# Stop everything
pkill -9 -f "node.*server.js"
service php-fpm stop
service nginx stop

# Start everything
service nginx start
service php-fpm start
cd projects/whatsapp/whatsapp-bridge && npm start &

# Wait and test
sleep 10
curl http://127.0.0.1:3000/api/health
```

## Success Indicators

You know it's working when:
- ✓ `./complete-diagnostics.sh` shows "ALL CHECKS PASSED"
- ✓ `curl http://127.0.0.1:3000/api/health` returns success
- ✓ `bridge-health.php` shows `"overall_status": "SUCCESS"`
- ✓ Sessions create without 500 error
- ✓ QR codes display without 400 error
- ✓ No errors in browser console

## Still Need Help?

Run this command and share the output:
```bash
cd /www/wwwroot/mmbtech.online/projects/whatsapp
./complete-diagnostics.sh
```

This will tell us exactly what's still broken!
