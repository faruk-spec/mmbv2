# WhatsApp Bridge Production Deployment Guide

## Issue: Bridge Server Running But PHP Can't Connect

### Problem Identified
Your bridge server IS running (confirmed by `EADDRINUSE` error), but PHP cannot connect to it. This is a common issue in production environments.

### Root Cause
The bridge server was listening on `127.0.0.1` only, which can cause connectivity issues in production environments where:
- PHP-FPM runs in a different context
- Network isolation is configured
- Security policies block localhost connections

### Solution Applied

#### 1. Bridge Server Configuration Updated
**Changed:** Bridge server now listens on `0.0.0.0` (all interfaces) instead of `127.0.0.1`

**File:** `projects/whatsapp/whatsapp-bridge/server.js`

```javascript
// Before:
app.listen(3000, '127.0.0.1', () => {
    console.log(`WhatsApp Bridge running on http://127.0.0.1:3000`);
});

// After:
const PORT = process.env.PORT || 3000;
const HOST = process.env.HOST || '0.0.0.0';
app.listen(PORT, HOST, () => {
    console.log(`WhatsApp Bridge running on http://${HOST}:${PORT}`);
    console.log(`Health check: http://localhost:${PORT}/api/health`);
});
```

**Benefits:**
- Accessible from all network interfaces
- Works with PHP-FPM in different contexts
- Compatible with Docker/containers
- Supports environment variable configuration

#### 2. PHP Connection Method Improved
**Added:** Dual connection method (curl + file_get_contents fallback)

**File:** `projects/whatsapp/controllers/SessionController.php`

**Changes:**
- ✅ Try `curl` first (more reliable in production)
- ✅ Fallback to `file_get_contents` if curl fails
- ✅ Better error logging
- ✅ More detailed error messages

```php
// Now tries curl first
if (function_exists('curl_init')) {
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    // ... curl options
    $response = curl_exec($ch);
    // ... handle response
}

// Falls back to file_get_contents if curl fails
if ($response === false) {
    $response = @file_get_contents($endpoint, false, $context);
}
```

#### 3. Diagnostic Tools Added

##### A. Shell Diagnostic Script
**File:** `projects/whatsapp/diagnose-bridge.sh`

**Usage:**
```bash
cd /www/wwwroot/mmbtech.online/projects/whatsapp
./diagnose-bridge.sh
```

**What it checks:**
1. ✅ Is port 3000 in use?
2. ✅ Is bridge server responding?
3. ✅ Can PHP file_get_contents connect?
4. ✅ Can PHP curl connect?
5. ✅ PHP configuration (allow_url_fopen, curl)
6. ✅ Process information

##### B. PHP Health Check Script
**File:** `projects/whatsapp/bridge-health.php`

**Access via web:**
```
https://mmbtech.online/projects/whatsapp/bridge-health.php
```

**Or via CLI:**
```bash
php projects/whatsapp/bridge-health.php
```

**Returns JSON with:**
- Test results for curl and file_get_contents
- Port reachability check
- Alternative host testing
- Configuration status
- Recommendations

##### C. Restart Script
**File:** `projects/whatsapp/restart-bridge.sh`

**Usage:**
```bash
cd /www/wwwroot/mmbtech.online/projects/whatsapp
./restart-bridge.sh
```

**What it does:**
1. Stops existing bridge server gracefully
2. Checks dependencies
3. Starts new server in background
4. Tests health endpoint
5. Shows status and logs location

## Deployment Steps

### Step 1: Stop Current Bridge Server
```bash
cd /www/wwwroot/mmbtech.online

# Find and kill the process
lsof -t -i:3000 | xargs kill

# Or use pkill
pkill -f "node.*server.js"
```

### Step 2: Deploy New Code
```bash
cd /www/wwwroot/mmbtech.online

# Pull latest changes
git pull origin your-branch-name

# Or copy the updated files manually
```

### Step 3: Start Bridge Server with New Configuration
```bash
cd /www/wwwroot/mmbtech.online/projects/whatsapp/whatsapp-bridge

# Start the server
npm start &

# Or use the restart script
cd /www/wwwroot/mmbtech.online/projects/whatsapp
./restart-bridge.sh
```

### Step 4: Verify Connection
```bash
# Test health endpoint
curl http://127.0.0.1:3000/api/health

# Or run diagnostics
./diagnose-bridge.sh

# Or check via web
https://mmbtech.online/projects/whatsapp/bridge-health.php
```

### Step 5: Test Session Creation
1. Go to WhatsApp sessions page
2. Create a new session
3. Try to view QR code
4. Should now work without errors

## Troubleshooting Guide

### Issue 1: "EADDRINUSE: address already in use"
**Cause:** Bridge server already running

**Solution:**
```bash
# Kill existing process
lsof -t -i:3000 | xargs kill

# Wait 2 seconds
sleep 2

# Start again
cd projects/whatsapp/whatsapp-bridge && npm start &
```

### Issue 2: PHP Can't Connect
**Possible Causes:**
1. `allow_url_fopen` disabled
2. PHP curl not installed
3. Firewall blocking connections
4. PHP-FPM restrictions

**Solution:**
```bash
# Check PHP configuration
php -i | grep allow_url_fopen
php -m | grep curl

# Install curl if missing
apt-get install php-curl
service php-fpm restart

# Check diagnostics
./diagnose-bridge.sh
```

### Issue 3: Bridge Server Not Responding
**Cause:** Server crashed or Chrome/Puppeteer issue

**Solution:**
```bash
# Check logs
tail -f projects/whatsapp/whatsapp-bridge/bridge-server.log

# Restart server
./restart-bridge.sh

# If Chrome issues, install dependencies
cd projects/whatsapp/whatsapp-bridge
./install-chrome-deps.sh
```

### Issue 4: Still Getting 400 Errors
**Cause:** Bridge server running but not generating QR codes

**Check:**
1. Chrome/Puppeteer dependencies installed
2. Sufficient memory available
3. WhatsApp servers accessible

**Solution:**
```bash
# Install Chrome dependencies
cd projects/whatsapp/whatsapp-bridge
sudo ./install-chrome-deps.sh

# Check server logs
tail -100 bridge-server.log

# Restart with clean state
pkill -f "node.*server.js"
rm -rf .wwebjs_auth  # Clear old sessions
npm start &
```

## Security Considerations

### Production Security
The bridge server now listens on `0.0.0.0`, which means it's accessible from all network interfaces.

**Recommended Security Measures:**

#### 1. Use Firewall Rules
```bash
# Allow only localhost
iptables -A INPUT -p tcp --dport 3000 -s 127.0.0.1 -j ACCEPT
iptables -A INPUT -p tcp --dport 3000 -j DROP
```

#### 2. Use Reverse Proxy (Nginx)
```nginx
location /whatsapp-bridge/ {
    proxy_pass http://127.0.0.1:3000/;
    proxy_set_header Host $host;
    
    # Restrict access
    allow 127.0.0.1;
    deny all;
}
```

#### 3. Use Environment Variable
```bash
# Set HOST to 127.0.0.1 in production if firewall is configured
export HOST=127.0.0.1
cd projects/whatsapp/whatsapp-bridge
npm start
```

## Performance Optimization

### Use PM2 for Process Management
```bash
# Install PM2
npm install -g pm2

# Start with PM2
cd /www/wwwroot/mmbtech.online/projects/whatsapp/whatsapp-bridge
pm2 start server.js --name whatsapp-bridge

# Enable startup on boot
pm2 startup
pm2 save

# Monitor
pm2 monit

# Logs
pm2 logs whatsapp-bridge
```

### Configure Resource Limits
```javascript
// In server.js, add:
process.setMaxListeners(20);

// For Puppeteer
const client = new Client({
    authStrategy: new LocalAuth({ clientId: sessionId }),
    puppeteer: { 
        headless: true,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-accelerated-2d-canvas',
            '--no-first-run',
            '--no-zygote',
            '--disable-gpu',
            '--max-old-space-size=512'  // Limit memory
        ]
    }
});
```

## Monitoring

### Check Server Status
```bash
# Is it running?
ps aux | grep "node.*server.js"

# Port status
lsof -i :3000

# Resource usage
ps aux | grep "node.*server.js" | awk '{print $2, $3, $4}'
```

### Log Monitoring
```bash
# Real-time logs
tail -f projects/whatsapp/whatsapp-bridge/bridge-server.log

# Recent errors
grep -i error projects/whatsapp/whatsapp-bridge/bridge-server.log | tail -20

# PHP logs
tail -f /var/log/php-fpm/error.log
```

### Health Check Cron
```bash
# Add to crontab
*/5 * * * * curl -s http://127.0.0.1:3000/api/health || /www/wwwroot/mmbtech.online/projects/whatsapp/restart-bridge.sh
```

## Admin Panel Configuration

### For ENTERPRISE Users
If you're on an ENTERPRISE plan, ensure the subscription is properly configured in the database:

```sql
-- Check current subscription
SELECT * FROM whatsapp_subscriptions WHERE user_id = YOUR_USER_ID;

-- Update or insert ENTERPRISE subscription
INSERT INTO whatsapp_subscriptions (user_id, sessions_limit, messages_limit, api_calls_limit, status, start_date, end_date)
VALUES (YOUR_USER_ID, 999, 999999, 999999, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR))
ON DUPLICATE KEY UPDATE 
    sessions_limit = 999,
    messages_limit = 999999,
    api_calls_limit = 999999,
    status = 'active',
    end_date = DATE_ADD(NOW(), INTERVAL 1 YEAR);
```

## Quick Reference Commands

```bash
# Start bridge server
cd /www/wwwroot/mmbtech.online/projects/whatsapp/whatsapp-bridge && npm start &

# Stop bridge server
lsof -t -i:3000 | xargs kill

# Restart bridge server
/www/wwwroot/mmbtech.online/projects/whatsapp/restart-bridge.sh

# Run diagnostics
/www/wwwroot/mmbtech.online/projects/whatsapp/diagnose-bridge.sh

# Check health via web
curl http://127.0.0.1:3000/api/health

# View logs
tail -f /www/wwwroot/mmbtech.online/projects/whatsapp/whatsapp-bridge/bridge-server.log

# Check PHP connectivity
php /www/wwwroot/mmbtech.online/projects/whatsapp/bridge-health.php
```

## Summary of Changes

### Files Modified
1. `projects/whatsapp/whatsapp-bridge/server.js` - Changed listen address to 0.0.0.0
2. `projects/whatsapp/controllers/SessionController.php` - Added curl support with fallback

### Files Created
1. `projects/whatsapp/diagnose-bridge.sh` - Shell diagnostic script
2. `projects/whatsapp/bridge-health.php` - PHP health check script
3. `projects/whatsapp/restart-bridge.sh` - Server restart script
4. `projects/whatsapp/PRODUCTION_DEPLOYMENT.md` - This guide

### Key Improvements
- ✅ Bridge server accessible from all interfaces
- ✅ Dual connection method (curl + file_get_contents)
- ✅ Comprehensive diagnostic tools
- ✅ Easy restart mechanism
- ✅ Better error logging
- ✅ Production-ready configuration

## Next Steps

1. **Deploy the changes** to your production server
2. **Restart the bridge server** using the new configuration
3. **Run diagnostics** to verify connectivity
4. **Test session creation** and QR code generation
5. **Monitor logs** for any issues

If you still encounter issues after following this guide, check:
- PHP error logs
- Bridge server logs
- Firewall settings
- SELinux policies (if enabled)

**Your bridge server is now configured for production use!** 🚀
