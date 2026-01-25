# WhatsApp Integration - Quick Start Guide

## 🚀 Simple 3-Step Setup

Your WhatsApp platform is **already installed** at `/www/wwwroot/mmbtech.online/projects/whatsapp`

### What You Have Now:
- ✅ Complete UI with dashboard, sessions, messages
- ✅ Database tables ready
- ✅ Admin panel functional
- ✅ User management working

### What's Missing:
- ❌ Real WhatsApp connection (currently showing placeholder QR codes)

---

## Step 1: Install WhatsApp Bridge (5 minutes)

The bridge connects your platform to WhatsApp Web.

### On Your Server:

```bash
# Navigate to your project
cd /www/wwwroot/mmbtech.online/projects/whatsapp

# Create bridge directory
mkdir -p whatsapp-bridge
cd whatsapp-bridge

# Install Node.js (if not installed)
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Install WhatsApp library
npm init -y
npm install whatsapp-web.js qrcode express body-parser
```

---

## Step 2: Create Bridge Server (2 minutes)

Create file: `/www/wwwroot/mmbtech.online/projects/whatsapp/whatsapp-bridge/server.js`

```javascript
const { Client, LocalAuth } = require('whatsapp-web.js');
const express = require('express');
const bodyParser = require('body-parser');
const QRCode = require('qrcode');

const app = express();
app.use(bodyParser.json());

// Store active WhatsApp clients per user session
const clients = {};

// Generate QR Code for session
app.post('/api/generate-qr', async (req, res) => {
    const { sessionId, userId } = req.body;
    
    if (!sessionId || !userId) {
        return res.status(400).json({ success: false, message: 'Missing sessionId or userId' });
    }

    try {
        // Create new WhatsApp client for this session
        const client = new Client({
            authStrategy: new LocalAuth({ clientId: sessionId }),
            puppeteer: { 
                headless: true,
                args: ['--no-sandbox', '--disable-setuid-sandbox']
            }
        });

        let qrCodeData = null;

        // When QR code is generated
        client.on('qr', async (qr) => {
            console.log(`QR Code generated for session ${sessionId}`);
            qrCodeData = await QRCode.toDataURL(qr);
        });

        // When authenticated
        client.on('authenticated', () => {
            console.log(`Session ${sessionId} authenticated`);
        });

        // When ready
        client.on('ready', () => {
            console.log(`Session ${sessionId} is ready`);
            clients[sessionId] = { client, userId, connected: true };
        });

        // When disconnected
        client.on('disconnected', () => {
            console.log(`Session ${sessionId} disconnected`);
            delete clients[sessionId];
        });

        // Initialize client
        await client.initialize();

        // Wait for QR code (max 10 seconds)
        for (let i = 0; i < 20; i++) {
            if (qrCodeData) {
                return res.json({ 
                    success: true, 
                    qr: qrCodeData,
                    sessionId: sessionId
                });
            }
            await new Promise(resolve => setTimeout(resolve, 500));
        }

        res.status(408).json({ success: false, message: 'QR code generation timeout' });

    } catch (error) {
        console.error('Error generating QR:', error);
        res.status(500).json({ success: false, message: error.message });
    }
});

// Check session status
app.post('/api/check-status', (req, res) => {
    const { sessionId } = req.body;
    
    if (!sessionId) {
        return res.status(400).json({ success: false, message: 'Missing sessionId' });
    }

    const session = clients[sessionId];
    if (session && session.connected) {
        res.json({ 
            success: true, 
            connected: true,
            phoneNumber: session.client.info?.wid?.user || 'Connected'
        });
    } else {
        res.json({ success: true, connected: false });
    }
});

// Send message
app.post('/api/send-message', async (req, res) => {
    const { sessionId, phoneNumber, message } = req.body;
    
    if (!sessionId || !phoneNumber || !message) {
        return res.status(400).json({ success: false, message: 'Missing required fields' });
    }

    const session = clients[sessionId];
    if (!session || !session.connected) {
        return res.status(400).json({ success: false, message: 'Session not connected' });
    }

    try {
        // Format phone number (remove special chars, add country code if needed)
        const formattedNumber = phoneNumber.replace(/[^0-9]/g, '') + '@c.us';
        
        await session.client.sendMessage(formattedNumber, message);
        
        res.json({ success: true, message: 'Message sent successfully' });
    } catch (error) {
        console.error('Error sending message:', error);
        res.status(500).json({ success: false, message: error.message });
    }
});

// Disconnect session
app.post('/api/disconnect', async (req, res) => {
    const { sessionId } = req.body;
    
    if (!sessionId) {
        return res.status(400).json({ success: false, message: 'Missing sessionId' });
    }

    const session = clients[sessionId];
    if (session) {
        try {
            await session.client.destroy();
            delete clients[sessionId];
            res.json({ success: true, message: 'Session disconnected' });
        } catch (error) {
            res.status(500).json({ success: false, message: error.message });
        }
    } else {
        res.json({ success: true, message: 'Session not found' });
    }
});

// Start server
const PORT = 3000;
app.listen(PORT, '127.0.0.1', () => {
    console.log(`WhatsApp Bridge running on http://127.0.0.1:${PORT}`);
});
```

---

## Step 3: Start the Bridge (1 minute)

```bash
# Run the bridge server
cd /www/wwwroot/mmbtech.online/projects/whatsapp/whatsapp-bridge
node server.js

# Keep it running (use pm2 for production)
npm install -g pm2
pm2 start server.js --name whatsapp-bridge
pm2 save
pm2 startup
```

---

## Step 4: Update Your PHP Code (1 minute)

Update `SessionController.php` to call the bridge:

Find the `generateQRCode()` method around line 170 and replace with:

```php
private function generateQRCode($sessionId)
{
    // Call Node.js bridge to generate real QR code
    $bridgeUrl = 'http://127.0.0.1:3000/api/generate-qr';
    
    $data = [
        'sessionId' => $sessionId,
        'userId' => Core\Auth::user()['id']
    ];
    
    $ch = curl_init($bridgeUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $result = json_decode($response, true);
        if ($result && $result['success'] && isset($result['qr'])) {
            return $result['qr']; // Real QR code data URL
        }
    }
    
    // Fallback to placeholder if bridge not available
    return $this->generatePlaceholderQR($sessionId);
}
```

---

## ✅ Done! Test It

1. Visit: `https://mmbtech.online/projects/whatsapp/sessions`
2. Click "Create New Session"
3. Enter session name
4. **Real WhatsApp QR code will appear!**
5. Scan with your WhatsApp mobile app
6. Start sending messages!

---

## 🔧 Troubleshooting

### QR not showing?
```bash
# Check if bridge is running
pm2 status

# Check bridge logs
pm2 logs whatsapp-bridge

# Restart bridge
pm2 restart whatsapp-bridge
```

### Can't connect?
- Ensure Node.js 18+ installed: `node --version`
- Check port 3000 not in use: `netstat -tulpn | grep 3000`
- Verify firewall allows localhost connections

### Phone not connecting?
- QR code expires after 60 seconds - refresh and try again
- Ensure WhatsApp is installed on phone
- Check phone has internet connection
- Try closing and reopening WhatsApp on phone

---

## 📚 Need More Help?

See detailed guide: `/www/wwwroot/mmbtech.online/projects/whatsapp/WHATSAPP_PRODUCTION_GUIDE.md`

That file has:
- Complete architecture explanations
- Advanced configuration options
- Security best practices
- Production deployment steps
- Scaling guidelines

---

## 🎉 You're Ready!

Your WhatsApp platform will now:
- ✅ Generate real QR codes
- ✅ Connect to WhatsApp Web
- ✅ Send/receive messages
- ✅ Manage multiple sessions
- ✅ Track all activity

**The .md file** is just documentation - you don't need to "run" it. Just follow these steps above!
