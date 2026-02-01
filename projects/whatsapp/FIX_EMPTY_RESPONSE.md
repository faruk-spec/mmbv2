# Fix Empty Response from Bridge Server

## Problem

When running `./test-integration.sh`, you see:
```
✗ Endpoint failed
Response: (empty or connection error)
```

This means the bridge server is not responding properly.

## Root Causes & Solutions

### 1. Chrome Dependencies Missing (Most Common)

**Symptoms:**
- Bridge server starts but crashes when generating QR
- Error logs show "Failed to launch browser" or "libatk-1.0.so.0: cannot open shared object file"

**Solution:**
```bash
cd /www/wwwroot/mmbtech.online/projects/whatsapp/whatsapp-bridge
sudo ./install-chrome-deps.sh
```

**Manual Installation (if script fails):**

For Ubuntu/Debian:
```bash
sudo apt-get update
sudo apt-get install -y \
    ca-certificates \
    fonts-liberation \
    libappindicator3-1 \
    libasound2 \
    libatk-bridge2.0-0 \
    libatk1.0-0 \
    libc6 \
    libcairo2 \
    libcups2 \
    libdbus-1-3 \
    libexpat1 \
    libfontconfig1 \
    libgbm1 \
    libgcc1 \
    libglib2.0-0 \
    libgtk-3-0 \
    libnspr4 \
    libnss3 \
    libpango-1.0-0 \
    libpangocairo-1.0-0 \
    libstdc++6 \
    libx11-6 \
    libx11-xcb1 \
    libxcb1 \
    libxcomposite1 \
    libxcursor1 \
    libxdamage1 \
    libxext6 \
    libxfixes3 \
    libxi6 \
    libxrandr2 \
    libxrender1 \
    libxss1 \
    libxtst6 \
    lsb-release \
    wget \
    xdg-utils
```

For CentOS/RHEL/Rocky Linux:
```bash
sudo yum install -y \
    alsa-lib \
    atk \
    cups-libs \
    gtk3 \
    ipa-gothic-fonts \
    libXcomposite \
    libXcursor \
    libXdamage \
    libXext \
    libXi \
    libXrandr \
    libXScrnSaver \
    libXtst \
    pango \
    xorg-x11-fonts-100dpi \
    xorg-x11-fonts-75dpi \
    xorg-x11-fonts-cyrillic \
    xorg-x11-fonts-misc \
    xorg-x11-fonts-Type1 \
    xorg-x11-utils
```

### 2. Server Not Running

**Check if server is running:**
```bash
ps aux | grep "node server.js"
curl http://127.0.0.1:3000/api/health
```

**Start the server:**
```bash
cd /www/wwwroot/mmbtech.online/projects/whatsapp/whatsapp-bridge
node server.js
```

**Expected output:**
```
WhatsApp Bridge running on http://127.0.0.1:3000
```

**Keep it running in background:**
```bash
cd /www/wwwroot/mmbtech.online/projects/whatsapp/whatsapp-bridge
nohup node server.js > bridge.log 2>&1 &
```

Or with PM2:
```bash
npm install -g pm2
pm2 start server.js --name whatsapp-bridge
pm2 save
pm2 startup  # Follow the instructions
```

### 3. NPM Packages Not Installed

**Install packages:**
```bash
cd /www/wwwroot/mmbtech.online/projects/whatsapp/whatsapp-bridge
npm install
```

**Expected packages:**
- whatsapp-web.js
- express
- body-parser
- qrcode

### 4. Port Already in Use

**Check if port 3000 is in use:**
```bash
lsof -i :3000
# or
netstat -tlnp | grep 3000
```

**Kill existing process:**
```bash
kill -9 <PID>
```

**Or change port in server.js:**
```javascript
const PORT = 3001;  // Change from 3000
```

### 5. Firewall Blocking

**Check if localhost/127.0.0.1 is accessible:**
```bash
curl http://127.0.0.1:3000/api/health
```

If this fails, check firewall:
```bash
# Allow port 3000 (if needed)
sudo ufw allow 3000
# or
sudo firewall-cmd --add-port=3000/tcp --permanent
sudo firewall-cmd --reload
```

## Testing After Fix

### 1. Test Health Endpoint
```bash
curl http://127.0.0.1:3000/api/health
```

**Expected response:**
```json
{
  "success": true,
  "status": "running",
  "message": "WhatsApp Bridge is operational",
  "timestamp": "2026-02-01T11:00:00.000Z"
}
```

### 2. Test QR Generation
```bash
curl -X POST http://127.0.0.1:3000/api/generate-qr \
  -H "Content-Type: application/json" \
  -d '{"sessionId":"test123","userId":1}'
```

**Expected response (success):**
```json
{
  "success": true,
  "qr": "data:image/png;base64,iVBOR...",
  "qr_text": "Scan this QR code with WhatsApp",
  "sessionId": "test123"
}
```

**Expected response (Chrome missing):**
```json
{
  "success": false,
  "message": "Chrome/Puppeteer dependencies are missing",
  "help": "Run: sudo ./install-chrome-deps.sh...",
  "technicalError": "Failed to launch the browser process..."
}
```

### 3. Run Full Test Script
```bash
cd /www/wwwroot/mmbtech.online/projects/whatsapp
./test-integration.sh
```

**Expected output (all working):**
```
1. Checking if bridge server is running... ✓ Bridge server is running
2. Checking Node.js installation... ✓ Node.js v18.20.8 installed
3. Checking npm packages... ✓ package.json found
   ✓ npm packages installed
4. Checking PHP configuration... ✓ PHP 8.3.27
5. Checking database tables... ⊘ Skipped
6. Testing bridge API endpoints...
   - Testing /api/health... ✓ Health check passed
   - Testing /api/generate-qr... ✓ Endpoint working
     QR code generated successfully
```

### 4. Test in Browser

1. Go to: `https://mmbtech.online/projects/whatsapp/sessions`
2. Click "Create Session"
3. Enter a session name
4. **Should see**: Success message, session appears immediately
5. Click "View QR" on the session
6. **Should see**: Real WhatsApp QR code (not placeholder)
7. **Check browser console** (F12): Should show no JSON errors

## Still Not Working?

### Check Server Logs

**If running in foreground:**
- Check the terminal where `node server.js` is running
- Look for error messages

**If running in background:**
```bash
# With nohup
cat /www/wwwroot/mmbtech.online/projects/whatsapp/whatsapp-bridge/bridge.log

# With PM2
pm2 logs whatsapp-bridge
```

### Common Error Messages

**"Error: Failed to launch the browser process"**
→ Install Chrome dependencies (see section 1)

**"ECONNREFUSED"**
→ Server not running or wrong port (see section 2)

**"MODULE_NOT_FOUND"**
→ Run `npm install` (see section 3)

**"EADDRINUSE"**
→ Port already in use (see section 4)

### Get More Help

1. **Check server logs** for specific errors
2. **Run test script** to see which step fails
3. **Test endpoints manually** with curl commands above
4. **Check browser console** for JSON errors

### Report Issue

If still not working, collect this info:

```bash
# 1. Test script output
cd /www/wwwroot/mmbtech.online/projects/whatsapp
./test-integration.sh > test-output.txt 2>&1

# 2. Server status
ps aux | grep node

# 3. Health check
curl http://127.0.0.1:3000/api/health

# 4. Try QR generation
curl -X POST http://127.0.0.1:3000/api/generate-qr \
  -H "Content-Type: application/json" \
  -d '{"sessionId":"test123","userId":1}' \
  --max-time 20

# 5. Check server logs
# (from wherever you're running it)
```

## Quick Fix Checklist

- [ ] Chrome dependencies installed (`sudo ./install-chrome-deps.sh`)
- [ ] NPM packages installed (`npm install`)
- [ ] Server is running (`node server.js` or `pm2 start`)
- [ ] Port 3000 is available (`lsof -i :3000`)
- [ ] Health check works (`curl http://127.0.0.1:3000/api/health`)
- [ ] QR generation works (`curl` command above)
- [ ] Test script passes (`./test-integration.sh`)
- [ ] Browser test works (create session, view QR)

If all checkboxes are checked, everything should be working! ✅
