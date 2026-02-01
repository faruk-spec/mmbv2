const { Client, LocalAuth } = require('whatsapp-web.js');
const express = require('express');
const bodyParser = require('body-parser');
const QRCode = require('qrcode');

const app = express();
app.use(bodyParser.json());

// Store active WhatsApp clients per user session
const clients = {};

// Health check endpoint
app.get('/api/health', (req, res) => {
    res.json({ 
        success: true, 
        status: 'running',
        message: 'WhatsApp Bridge is operational',
        timestamp: new Date().toISOString()
    });
});

// Health check with POST (for consistency)
app.post('/api/health', (req, res) => {
    res.json({ 
        success: true, 
        status: 'running',
        message: 'WhatsApp Bridge is operational',
        timestamp: new Date().toISOString()
    });
});

// Generate QR Code for session
app.post('/api/generate-qr', async (req, res) => {
    const { sessionId, userId } = req.body;
    
    console.log(`[${new Date().toISOString()}] QR generation request:`, { sessionId, userId });
    
    if (!sessionId || !userId) {
        console.error('Missing required fields:', { sessionId, userId });
        return res.status(400).json({ 
            success: false, 
            message: 'Missing sessionId or userId',
            received: { sessionId: !!sessionId, userId: !!userId }
        });
    }

    try {
        console.log(`Creating WhatsApp client for session ${sessionId}...`);
        
        // Create new WhatsApp client for this session
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
                    '--disable-gpu'
                ]
            }
        });

        let qrCodeData = null;
        let clientError = null;

        // When QR code is generated
        client.on('qr', async (qr) => {
            console.log(`✓ QR Code generated for session ${sessionId}`);
            try {
                qrCodeData = await QRCode.toDataURL(qr);
                console.log(`✓ QR Code converted to data URL for session ${sessionId}`);
            } catch (err) {
                console.error(`Error converting QR to data URL:`, err);
                qrCodeData = null;
            }
        });

        // When authenticated
        client.on('authenticated', () => {
            console.log(`✓ Session ${sessionId} authenticated`);
        });

        // When ready
        client.on('ready', () => {
            console.log(`✓ Session ${sessionId} is ready`);
            clients[sessionId] = { client, userId, connected: true };
        });

        // When disconnected
        client.on('disconnected', () => {
            console.log(`Session ${sessionId} disconnected`);
            delete clients[sessionId];
        });
        
        // Catch initialization errors
        client.on('auth_failure', (msg) => {
            console.error(`Auth failure for session ${sessionId}:`, msg);
            clientError = 'Authentication failed';
        });

        // Initialize client
        console.log(`Initializing client for session ${sessionId}...`);
        await client.initialize();

        // Wait for QR code (max 15 seconds)
        console.log(`Waiting for QR code for session ${sessionId}...`);
        for (let i = 0; i < 30; i++) {
            if (qrCodeData) {
                console.log(`✓ Returning QR code for session ${sessionId}`);
                return res.json({ 
                    success: true, 
                    qr: qrCodeData,
                    qr_text: 'Scan this QR code with WhatsApp',
                    sessionId: sessionId,
                    generated_at: new Date().toISOString()
                });
            }
            if (clientError) {
                console.error(`Client error for session ${sessionId}:`, clientError);
                throw new Error(clientError);
            }
            await new Promise(resolve => setTimeout(resolve, 500));
        }

        console.error(`QR code generation timeout for session ${sessionId}`);
        res.status(408).json({ 
            success: false, 
            message: 'QR code generation timeout. Please try again.',
            sessionId: sessionId
        });

    } catch (error) {
        console.error(`[${new Date().toISOString()}] Error generating QR for session ${sessionId}:`, error.message);
        
        // Provide helpful error messages based on error type
        let userMessage = error.message;
        let helpText = '';
        
        if (error.message.includes('Failed to launch') || error.message.includes('cannot open shared object')) {
            userMessage = 'Chrome/Puppeteer dependencies are missing';
            helpText = 'Run: sudo ./install-chrome-deps.sh in the whatsapp-bridge directory. See CHROME_SETUP.md for details.';
        } else if (error.message.includes('ECONNREFUSED')) {
            userMessage = 'Cannot connect to Chrome';
            helpText = 'Chrome may not be installed or Puppeteer may need configuration.';
        } else if (error.message.includes('timeout')) {
            userMessage = 'QR code generation timed out';
            helpText = 'This can happen if WhatsApp servers are slow. Try again in a moment.';
        }
        
        res.status(500).json({ 
            success: false, 
            message: userMessage,
            help: helpText,
            technicalError: error.message,
            sessionId: sessionId
        });
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
