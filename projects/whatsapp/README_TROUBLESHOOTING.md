# 🚨 Still Not Working After Making Changes?

## Quick Fix (Run This First!)

If you've made all changes but it's still not working, run this ONE command:

```bash
cd /www/wwwroot/mmbtech.online/projects/whatsapp
./emergency-fix.sh
```

**This will:**
- ✅ Stop all processes
- ✅ Install missing dependencies
- ✅ Restart everything properly
- ✅ Test if it's working
- ✅ Tell you exactly what's still broken (if anything)

---

## Need More Details? Run Diagnostics

```bash
cd /www/wwwroot/mmbtech.online/projects/whatsapp
./complete-diagnostics.sh
```

**This will check:**
- Bridge server status
- PHP connectivity
- Database connection
- Configuration files
- All dependencies

**Saves time by pinpointing the exact problem!**

---

## Still Broken? Follow The Guide

Open this file and follow step-by-step:

```bash
cat /www/wwwroot/mmbtech.online/projects/whatsapp/STILL_NOT_WORKING.md
```

Or view online:
`https://mmbtech.online/projects/whatsapp/STILL_NOT_WORKING.md`

**Covers:**
- All common issues
- Specific fixes for each
- How to test properly
- What to do if nothing works

---

## Summary of Available Tools

### 1. `emergency-fix.sh` - One-Command Fix
**Use when:** Everything is broken, need to reset
```bash
./emergency-fix.sh
```

### 2. `complete-diagnostics.sh` - Find The Problem
**Use when:** Need to know what's broken
```bash
./complete-diagnostics.sh
```

### 3. `restart-bridge.sh` - Restart Bridge Server
**Use when:** Just need to restart the bridge
```bash
./restart-bridge.sh
```

### 4. `diagnose-bridge.sh` - Quick Bridge Check
**Use when:** Check if bridge is working
```bash
./diagnose-bridge.sh
```

### 5. `bridge-health.php` - Web Health Check
**Use when:** Check from browser
```
https://mmbtech.online/projects/whatsapp/bridge-health.php
```

---

## Most Common Issues (Quick Fixes)

### Issue: "Bridge server not responding"
```bash
# Nuclear option - kill and restart everything
pkill -9 -f "node.*server.js"
cd /www/wwwroot/mmbtech.online/projects/whatsapp/whatsapp-bridge
npm start &
sleep 10
curl http://127.0.0.1:3000/api/health
```

### Issue: "PHP can't connect" 
```bash
# Install PHP cURL extension
apt-get install php-curl
service php-fpm restart
```

### Issue: "Changes not applied"
```bash
# Pull latest code and restart everything
cd /www/wwwroot/mmbtech.online
git pull origin copilot/fix-json-error-and-navbar
service php-fpm restart
cd projects/whatsapp && ./restart-bridge.sh
```

### Issue: "Getting 500 errors"
```bash
# Check database and restart PHP
service php-fpm restart
tail -f /var/log/php-fpm/error.log
```

### Issue: "Getting 400 errors"
```bash
# Bridge server issue - restart it
cd /www/wwwroot/mmbtech.online/projects/whatsapp
./restart-bridge.sh
```

---

## How to Test If It's Working

### Test 1: Bridge Server Health
```bash
curl http://127.0.0.1:3000/api/health
```
**Expected:** `{"success":true,"status":"running"}`

### Test 2: PHP Connectivity
```bash
php -r 'echo file_get_contents("http://127.0.0.1:3000/api/health");'
```
**Expected:** `{"success":true,"status":"running"}`

### Test 3: Web Health Check
Visit: `https://mmbtech.online/projects/whatsapp/bridge-health.php`
**Expected:** `"overall_status": "SUCCESS"`

### Test 4: Create Session
1. Go to: `https://mmbtech.online/projects/whatsapp/sessions`
2. Click "New Session"
3. Should create without 500 error

### Test 5: View QR Code
1. Click "Scan QR" on any session
2. Should show QR code within 10-15 seconds
3. Should NOT show 400 error

---

## When to Use Each Script

| Situation | Use This Script |
|-----------|----------------|
| Everything is broken | `emergency-fix.sh` |
| Need to know what's broken | `complete-diagnostics.sh` |
| Just restart bridge | `restart-bridge.sh` |
| Quick bridge check | `diagnose-bridge.sh` |
| Check from browser | Visit `bridge-health.php` |
| Made code changes | `git pull && service php-fpm restart` |
| Bridge won't start | `pkill -9 -f "node.*server.js"` then `npm start` |

---

## Success Indicators

✅ **You know it's working when:**
1. `complete-diagnostics.sh` says "ALL CHECKS PASSED"
2. `curl http://127.0.0.1:3000/api/health` returns success
3. `bridge-health.php` shows `"overall_status": "SUCCESS"`
4. Can create sessions without 500 error
5. Can view QR codes without 400 error
6. No red errors in browser console (F12)

---

## Need More Help?

Run diagnostics and share the output:
```bash
cd /www/wwwroot/mmbtech.online/projects/whatsapp
./complete-diagnostics.sh > diagnostics.txt
cat diagnostics.txt
```

Also share:
1. PHP error log: `tail -100 /var/log/php-fpm/error.log`
2. Bridge log: `tail -100 whatsapp-bridge/bridge-server.log`
3. Browser console errors (press F12, screenshot Console tab)
4. Exact error message you're seeing

---

## Documentation

- **Complete Guide:** `PRODUCTION_DEPLOYMENT.md`
- **Troubleshooting:** `STILL_NOT_WORKING.md`
- **Error Fixes:** `ERROR_FIXES_SUMMARY.md`
- **Main Guide:** `WHATSAPP_FIXES_SUMMARY.md`

All documentation is in: `/www/wwwroot/mmbtech.online/projects/whatsapp/`

---

**TL;DR: Run `./emergency-fix.sh` first. If still broken, run `./complete-diagnostics.sh` to see what's wrong.**
