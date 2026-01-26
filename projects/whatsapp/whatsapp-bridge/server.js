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
