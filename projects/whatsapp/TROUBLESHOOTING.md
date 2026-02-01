# WhatsApp Platform - Troubleshooting Guide

## Common Issues and Solutions

### Issue 1: "Unexpected token '<'" Error When Creating Session

**Symptoms:**
- Error appears when clicking "Create Session"
- Session is created in database but error shown
- Session only appears after page refresh

**Cause:**
PHP warnings or errors being included in JSON response due to output buffering issues.

**Solution (Fixed in v2.0):**
Updated `SessionController.php` to properly clear all output buffers before sending JSON:
```php
// Clear ALL existing buffers
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();
```

**Verification:**
1. Create a new session
2. Should see "Session created successfully!" message
3. Session appears immediately without refresh
4. No error messages

---

### Issue 2: Placeholder QR Codes Despite Bridge Server Running

**Symptoms:**
- Bridge server is running on port 3000
- Still showing placeholder QR codes
- Message says "Start bridge server for real QR codes"

**Cause:**
Incorrect API endpoint or request format. Bridge expects POST to `/api/generate-qr` with JSON body.

**Solution (Fixed in v2.0):**
Updated `SessionController.php` to use correct API:
- Endpoint: `http://127.0.0.1:3000/api/generate-qr`
- Method: POST
- Body: `{"sessionId": "...", "userId": 123}`
- Response field: `qr` (not `qr_code`)

**Verification:**
1. Start bridge server: `cd projects/whatsapp/whatsapp-bridge && node server.js`
2. Verify server starts: "WhatsApp Bridge running on http://127.0.0.1:3000"
3. Create a session
4. Click "View QR" - should see real WhatsApp QR code
5. Bridge console should show: "QR Code generated for session ..."

---

### Issue 3: Bridge Server Not Generating QR Code

**Symptoms:**
- Bridge server starts successfully
- When requesting QR code, timeout occurs
- No QR code generated after 10 seconds

**Possible Causes:**

#### A. Node.js Dependencies Missing
```bash
cd projects/whatsapp/whatsapp-bridge
npm install
# Should install: whatsapp-web.js, express, body-parser, qrcode
```

#### B. Puppeteer/Chromium Not Installed
```bash
# Linux
sudo apt-get install -y chromium-browser

# Or install manually
cd projects/whatsapp/whatsapp-bridge
npx puppeteer install
```

#### C. Firewall Blocking Connections
```bash
# Check if port 3000 is accessible
curl http://127.0.0.1:3000/api/generate-qr
# Should return: "Cannot GET /api/generate-qr" (it's POST only)

# Test POST request
curl -X POST http://127.0.0.1:3000/api/generate-qr \
  -H "Content-Type: application/json" \
  -d '{"sessionId":"test123","userId":1}'
```

#### D. Chrome/Chromium Missing Dependencies (Linux)
```bash
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

---

### Issue 4: Session Status Not Updating

**Symptoms:**
- Session stuck in "initializing" state
- Status doesn't change to "connected" after scanning QR

**Cause:**
Database status not updated when WhatsApp client connects.

**Temporary Solution:**
The bridge currently stores connection status in memory. You need to implement a callback to update the database:

```javascript
// In server.js, when ready event fires:
client.on('ready', async () => {
    console.log(`Session ${sessionId} is ready`);
    clients[sessionId] = { client, userId, connected: true };
    
    // TODO: Call PHP API to update database status
    // Example:
    // await fetch('http://yoursite.com/projects/whatsapp/api/update-status', {
    //     method: 'POST',
    //     body: JSON.stringify({ sessionId, status: 'connected' })
    // });
});
```

---

### Issue 5: Multiple QR Requests Causing Issues

**Symptoms:**
- Clicking "View QR" multiple times causes errors
- Multiple WhatsApp clients initialized for same session

**Cause:**
Each QR request creates a new WhatsApp client instance.

**Solution:**
Check if client already exists before creating new one:

```javascript
app.post('/api/generate-qr', async (req, res) => {
    const { sessionId, userId } = req.body;
    
    // Check if client already exists
    if (clients[sessionId]) {
        return res.status(400).json({ 
            success: false, 
            message: 'Session already active. Please refresh the page.' 
        });
    }
    
    // ... rest of code
});
```

---

## Debugging Steps

### 1. Check PHP Logs
```bash
# Linux
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/nginx/error.log

# Check for "WhatsApp Bridge:" messages
grep "WhatsApp Bridge" /var/log/apache2/error.log
```

### 2. Check Bridge Server Logs
```bash
cd projects/whatsapp/whatsapp-bridge
node server.js
# Watch for QR generation messages
```

### 3. Test Bridge API Directly
```bash
# Test bridge is responding
curl -X POST http://127.0.0.1:3000/api/generate-qr \
  -H "Content-Type: application/json" \
  -d '{"sessionId":"test-session-123","userId":1}'

# Should return JSON with QR code in 10-15 seconds
```

### 4. Browser Developer Console
```javascript
// In browser console on sessions page:
// Test session creation
fetch('/projects/whatsapp/sessions/create', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'session_name=Test&csrf_token=' + document.querySelector('[name="csrf_token"]').value
}).then(r => r.json()).then(console.log);

// Test QR code fetch
fetch('/projects/whatsapp/sessions/qr?session_id=1')
    .then(r => r.json())
    .then(console.log);
```

---

## Performance Optimization

### 1. Reduce QR Generation Time
The bridge waits up to 10 seconds for QR code. You can reduce this if your server is fast:

```javascript
// In server.js, reduce wait iterations:
// Wait for QR code (max 5 seconds)
for (let i = 0; i < 10; i++) {  // was 20
    if (qrCodeData) {
        return res.json({ 
            success: true, 
            qr: qrCodeData,
            sessionId: sessionId
        });
    }
    await new Promise(resolve => setTimeout(resolve, 500));
}
```

### 2. Implement QR Caching
For sessions that haven't been scanned yet, cache the QR code:

```javascript
const qrCache = {};

app.post('/api/generate-qr', async (req, res) => {
    const { sessionId } = req.body;
    
    // Check cache first
    if (qrCache[sessionId] && Date.now() - qrCache[sessionId].timestamp < 30000) {
        return res.json({
            success: true,
            qr: qrCache[sessionId].qr,
            sessionId: sessionId
        });
    }
    
    // ... generate new QR ...
    
    // Cache for 30 seconds
    qrCache[sessionId] = {
        qr: qrCodeData,
        timestamp: Date.now()
    };
});
```

---

## Security Considerations

### 1. Restrict Bridge Access
The bridge server should only be accessible from localhost:

```javascript
// In server.js
app.listen(PORT, '127.0.0.1', () => {  // NOT '0.0.0.0'
    console.log(`WhatsApp Bridge running on http://127.0.0.1:${PORT}`);
});
```

### 2. Add API Authentication
Consider adding authentication to bridge endpoints:

```javascript
const API_KEY = process.env.BRIDGE_API_KEY || 'your-secret-key';

app.use((req, res, next) => {
    const apiKey = req.headers['x-api-key'];
    if (apiKey !== API_KEY) {
        return res.status(401).json({ success: false, message: 'Unauthorized' });
    }
    next();
});
```

Then update PHP to send the key:

```php
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n" .
                   "X-API-Key: your-secret-key\r\n",
        'content' => $postData,
        'timeout' => 15
    ]
]);
```

---

## Getting Help

If you're still experiencing issues:

1. Check the [QUICK_START.md](QUICK_START.md) guide
2. Review the [WHATSAPP_PRODUCTION_GUIDE.md](WHATSAPP_PRODUCTION_GUIDE.md)
3. Enable debug logging in both PHP and Node.js
4. Test each component independently
5. Check system requirements (Node.js 14+, PHP 7.4+, Chromium)
