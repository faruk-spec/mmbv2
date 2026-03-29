const { Client, LocalAuth } = require('whatsapp-web.js');
const express = require('express');
const bodyParser = require('body-parser');
const QRCode = require('qrcode');
const fs = require('fs');
const path = require('path');

const app = express();
app.use(bodyParser.json());

// Polling configuration constants
const POLLING_INTERVAL_MS   = 500;   // How often to check for QR / readiness
const MAX_QR_POLLING_ATTEMPTS = 40;  // Max polls before giving up (40 × 500 ms = 20 s)
const QR_CACHE_TTL_MS       = 60000; // How long a generated QR stays valid (60 s)

// Per-session client state: { client, userId, connected, initializing, error }
const clientStates = {};

// Per-session QR cache: { data (dataURL), expiry (ms timestamp) }
const qrCache = {};

/**
 * Delete LocalAuth storage for a session so the next initialize() call is
 * forced to display a fresh QR code rather than restoring stale credentials.
 */
function clearSessionAuth(sessionId) {
    const authDir = path.join(process.cwd(), '.wwebjs_auth', `session-${sessionId}`);
    try {
        if (fs.existsSync(authDir)) {
            fs.rmSync(authDir, { recursive: true, force: true });
            console.log(`Cleared stale LocalAuth for session ${sessionId}`);
        }
    } catch (err) {
        console.error(`Failed to clear LocalAuth for session ${sessionId}:`, err.message);
    }
}

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
        // ── Fast path: return a still-fresh cached QR immediately ──────────
        if (qrCache[sessionId] && qrCache[sessionId].expiry > Date.now()) {
            console.log(`Returning cached QR for session ${sessionId}`);
            return res.json({
                success: true,
                qr: qrCache[sessionId].data,
                sessionId: sessionId,
                generated_at: new Date().toISOString()
            });
        }

        // ── Dedup: if a client is already initializing, wait for its QR ───
        if (clientStates[sessionId] && clientStates[sessionId].initializing) {
            console.log(`Client already initializing for session ${sessionId}, waiting for QR...`);
            for (let i = 0; i < MAX_QR_POLLING_ATTEMPTS; i++) {
                if (qrCache[sessionId] && qrCache[sessionId].expiry > Date.now()) {
                    return res.json({
                        success: true,
                        qr: qrCache[sessionId].data,
                        sessionId: sessionId,
                        generated_at: new Date().toISOString()
                    });
                }
                if (clientStates[sessionId] && clientStates[sessionId].error) {
                    throw new Error(clientStates[sessionId].error);
                }
                await new Promise(resolve => setTimeout(resolve, POLLING_INTERVAL_MS));
            }
            return res.status(408).json({
                success: false,
                message: 'QR code generation timeout. Please try again.',
                sessionId: sessionId
            });
        }

        // ── Destroy any stale (non-initializing) client for this session ───
        if (clientStates[sessionId] && clientStates[sessionId].client) {
            console.log(`Destroying stale client for session ${sessionId}...`);
            try {
                await clientStates[sessionId].client.destroy();
            } catch (e) {
                console.error(`Error destroying existing client:`, e.message);
            }
            delete clientStates[sessionId];
        }
        delete qrCache[sessionId];

        // ── Create fresh client ────────────────────────────────────────────
        console.log(`Creating new WhatsApp client for session ${sessionId}...`);

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

        clientStates[sessionId] = { client, userId, connected: false, initializing: true, error: null };

        // When QR code is generated
        client.on('qr', async (qr) => {
            console.log(`✓ QR Code generated for session ${sessionId}`);
            try {
                const dataUrl = await QRCode.toDataURL(qr);
                qrCache[sessionId] = { data: dataUrl, expiry: Date.now() + QR_CACHE_TTL_MS };
                console.log(`✓ QR Code cached for session ${sessionId}`);
            } catch (err) {
                console.error(`Error converting QR to data URL:`, err);
                if (clientStates[sessionId]) {
                    clientStates[sessionId].error = 'Failed to convert QR to image: ' + err.message;
                }
            }
        });

        // When authenticated
        client.on('authenticated', () => {
            console.log(`✓ Session ${sessionId} authenticated`);
        });

        // When ready
        client.on('ready', () => {
            console.log(`✓ Session ${sessionId} is ready`);
            if (clientStates[sessionId]) {
                clientStates[sessionId].connected = true;
                clientStates[sessionId].initializing = false;
            }
        });

        // When disconnected
        client.on('disconnected', () => {
            console.log(`Session ${sessionId} disconnected`);
            delete clientStates[sessionId];
            delete qrCache[sessionId];
        });
        
        // Auth failure: stale credentials — clear LocalAuth so next call gets a fresh QR
        client.on('auth_failure', (msg) => {
            console.error(`Auth failure for session ${sessionId}:`, msg);
            if (clientStates[sessionId]) {
                clientStates[sessionId].error = 'Authentication failed. Please try again.';
                clientStates[sessionId].initializing = false;
            }
            clearSessionAuth(sessionId);
            delete qrCache[sessionId];
        });

        // Start initialization WITHOUT awaiting — initialize() only resolves after
        // the user scans the QR and the session is fully authenticated.
        console.log(`Initializing client for session ${sessionId}...`);
        client.initialize().catch((err) => {
            console.error(`Initialization error for session ${sessionId}:`, err.message);
            if (clientStates[sessionId]) {
                clientStates[sessionId].error = err.message;
                clientStates[sessionId].initializing = false;
            }
        });

        // Wait up to 20 seconds (MAX_QR_POLLING_ATTEMPTS × POLLING_INTERVAL_MS) for the QR to appear
        console.log(`Waiting for QR code for session ${sessionId}...`);
        for (let i = 0; i < MAX_QR_POLLING_ATTEMPTS; i++) {
            if (qrCache[sessionId] && qrCache[sessionId].expiry > Date.now()) {
                console.log(`✓ Returning QR code for session ${sessionId}`);
                return res.json({ 
                    success: true, 
                    qr: qrCache[sessionId].data,
                    sessionId: sessionId,
                    generated_at: new Date().toISOString()
                });
            }
            if (clientStates[sessionId] && clientStates[sessionId].error) {
                throw new Error(clientStates[sessionId].error);
            }
            await new Promise(resolve => setTimeout(resolve, POLLING_INTERVAL_MS));
        }

        // Timeout — the background client keeps running; the QR may still arrive
        // and will be served immediately on the next request (qrCache fast path).
        console.error(`QR code generation timeout for session ${sessionId}`);
        if (clientStates[sessionId]) {
            clientStates[sessionId].initializing = false;
        }
        res.status(408).json({ 
            success: false, 
            message: 'QR code generation timeout. Please try again.',
            sessionId: sessionId
        });

    } catch (error) {
        console.error(`[${new Date().toISOString()}] Error generating QR for session ${sessionId}:`, error.message);
        
        // Clean up on hard error
        if (clientStates[sessionId] && clientStates[sessionId].initializing) {
            try { await clientStates[sessionId].client.destroy(); } catch (_) {}
            delete clientStates[sessionId];
        }
        
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

    const state = clientStates[sessionId];
    if (state && state.connected) {
        res.json({ 
            success: true, 
            connected: true,
            phoneNumber: state.client.info?.wid?.user || 'Connected'
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

    const state = clientStates[sessionId];
    if (!state || !state.connected) {
        return res.status(400).json({ success: false, message: 'Session not connected' });
    }

    try {
        // Format phone number (remove special chars, add country code if needed)
        const formattedNumber = phoneNumber.replace(/[^0-9]/g, '') + '@c.us';
        
        await state.client.sendMessage(formattedNumber, message);
        
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

    const state = clientStates[sessionId];
    if (state) {
        try {
            await state.client.destroy();
            delete clientStates[sessionId];
            delete qrCache[sessionId];
            res.json({ success: true, message: 'Session disconnected' });
        } catch (error) {
            res.status(500).json({ success: false, message: error.message });
        }
    } else {
        res.json({ success: true, message: 'Session not found' });
    }
});

// Start server
const PORT = process.env.PORT || 3000;
const HOST = process.env.HOST || '0.0.0.0'; // Listen on all interfaces for production

app.listen(PORT, HOST, () => {
    console.log(`WhatsApp Bridge running on http://${HOST}:${PORT}`);
    console.log(`Health check: http://localhost:${PORT}/api/health`);
    console.log(`Server started at: ${new Date().toISOString()}`);
});
