/**
 * WhatsApp Bridge — powered by Baileys (no browser / no Puppeteer).
 *
 * Baileys implements the WhatsApp Web protocol directly over WebSockets so
 * there is no dependency on Chrome and no net::ERR_TIMED_OUT from a browser
 * trying to load the WhatsApp Web page.
 *
 * API surface is identical to the previous whatsapp-web.js bridge so the PHP
 * back-end (SessionController) and the frontend require no changes.
 */

// Baileys ships a CJS build; the socket factory is the default export.
const baileys = require('@whiskeysockets/baileys');
const makeWASocket        = baileys.default || baileys.makeWASocket;
const useMultiFileAuthState = baileys.useMultiFileAuthState;
const DisconnectReason    = baileys.DisconnectReason;

const pino       = require('pino');
const rateLimit  = require('express-rate-limit');
const express    = require('express');
const bodyParser = require('body-parser');
const QRCode     = require('qrcode');
const fs         = require('fs');
const path       = require('path');

const app = express();
app.use(bodyParser.json());

// Rate-limiting: the bridge is intended to be called by the PHP back-end only
// (localhost), but we apply limits as a defence-in-depth measure.
// QR generation is intentionally generous (10/min) because Chrome/Puppeteer is
// gone and each Baileys connection is lightweight.
const qrLimiter = rateLimit({
    windowMs: 60 * 1000,
    max: 10,
    standardHeaders: true,
    legacyHeaders: false,
    message: { success: false, error_type: 'RATE_LIMITED', message: 'Too many QR requests. Please wait a moment.' },
});
const apiLimiter = rateLimit({
    windowMs: 60 * 1000,
    max: 60,
    standardHeaders: true,
    legacyHeaders: false,
    message: { success: false, error_type: 'RATE_LIMITED', message: 'Too many requests. Please wait a moment.' },
});

// ── Polling configuration ────────────────────────────────────────────────────
const POLLING_INTERVAL_MS     = 500;  // How often to check for QR / readiness
const MAX_QR_POLLING_ATTEMPTS = 60;   // 60 × 500 ms = 30 s
const QR_CACHE_TTL_MS         = 60000; // How long a cached QR stays valid (60 s)

// Auth credentials are stored per-session in this directory.
const AUTH_BASE_DIR = path.resolve(process.cwd(), '.baileys_auth');

// Per-session state: { sock, userId, connected, initializing, error, phoneNumber }
const clientStates = {};

// Per-session QR cache: { data (data-URL), expiry (ms timestamp) }
const qrCache = {};

// ── Helpers ──────────────────────────────────────────────────────────────────

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

/**
 * Validate sessionId: allow only alphanumeric characters, hyphens and
 * underscores to prevent path-traversal attacks.
 */
function validateSessionId(sessionId) {
    return /^[a-zA-Z0-9_-]+$/.test(sessionId);
}

/**
 * Return the auth directory for a session, asserting it stays inside
 * AUTH_BASE_DIR.  Uses path.relative() for a platform-agnostic traversal
 * check that handles both '\\' and '/' separators correctly.
 */
function getAuthDir(sessionId) {
    const authDir  = path.resolve(AUTH_BASE_DIR, sessionId);
    const relative = path.relative(AUTH_BASE_DIR, authDir);
    // If the relative path starts with '..' or is absolute it escaped the base.
    if (relative.startsWith('..') || path.isAbsolute(relative)) {
        throw new Error(`Path escape detected for sessionId "${sessionId}"`);
    }
    return authDir;
}

/**
 * Delete Baileys auth state so the next connection is forced to show a fresh
 * QR code rather than silently restoring stale credentials.
 */
function clearSessionAuth(sessionId) {
    if (!validateSessionId(sessionId)) {
        console.error(`Refusing to clear auth: invalid sessionId "${sessionId}"`);
        return;
    }
    try {
        const authDir = getAuthDir(sessionId);
        if (fs.existsSync(authDir)) {
            fs.rmSync(authDir, { recursive: true, force: true });
            console.log(`Cleared stale auth for session ${sessionId}`);
        }
    } catch (err) {
        console.error(`Failed to clear auth for session ${sessionId}:`, err.message);
    }
}

/**
 * Remove all event listeners from a socket and close it without logging out.
 * After this call the session is removed from clientStates and qrCache.
 */
function destroySession(sessionId) {
    const state = clientStates[sessionId];
    if (state && state.sock) {
        try {
            state.sock.ev.removeAllListeners(); // prevent close handler from firing
            state.sock.end(undefined);
        } catch (_) {}
    }
    delete clientStates[sessionId];
    delete qrCache[sessionId];
}

/**
 * Create a new Baileys WebSocket connection for a session.
 * The caller must have already cleared any stale clientStates / qrCache entries.
 * Auth credentials are read from (and persisted to) the per-session directory.
 */
async function createBaileysSocket(sessionId, userId) {
    const authDir = getAuthDir(sessionId);
    fs.mkdirSync(authDir, { recursive: true });

    const { state, saveCreds } = await useMultiFileAuthState(authDir);

    const sock = makeWASocket({
        auth: state,
        printQRInTerminal: false,
        // Suppress noisy internal logs; errors surface through the event system.
        logger: pino({ level: 'silent' }),
        // Identify as a recent Chrome desktop browser.
        // Can be overridden via the BAILEYS_BROWSER_VERSION env var.
        browser: ['WhatsApp Bridge', 'Chrome', process.env.BAILEYS_BROWSER_VERSION || '124.0.0'],
        // Do not fetch the full message history on reconnect.
        syncFullHistory: false,
    });

    clientStates[sessionId] = {
        sock,
        userId,
        connected: false,
        initializing: true,
        error: null,
        phoneNumber: null,
    };

    // Persist credentials whenever they change.
    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;

        // ── New QR code emitted ──────────────────────────────────────────────
        if (qr) {
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
        }

        // ── Successfully connected ───────────────────────────────────────────
        if (connection === 'open') {
            console.log(`✓ Session ${sessionId} connected`);
            if (clientStates[sessionId]) {
                // Baileys user id: "628xxx:YY@s.whatsapp.net" — extract the number.
                const phoneNumber = phoneFromBaileysId(sock.user?.id);
                clientStates[sessionId].connected    = true;
                clientStates[sessionId].initializing = false;
                clientStates[sessionId].phoneNumber  = phoneNumber;
            }
        }

        // ── Connection closed ────────────────────────────────────────────────
        if (connection === 'close') {
            const statusCode = lastDisconnect?.error?.output?.statusCode;
            console.log(`Session ${sessionId} closed (code ${statusCode})`);

            if (clientStates[sessionId]) {
                if (statusCode === DisconnectReason.loggedOut) {
                    console.log(`Session ${sessionId} logged out — clearing auth`);
                    clearSessionAuth(sessionId);
                    clientStates[sessionId].error = 'Logged out from WhatsApp. Please scan QR again.';
                } else {
                    const errMsg = lastDisconnect?.error?.message || 'Connection closed unexpectedly';
                    clientStates[sessionId].error = errMsg;
                }
                clientStates[sessionId].connected    = false;
                clientStates[sessionId].initializing = false;
            }
            delete qrCache[sessionId];
        }
    });
}

/**
 * Extract the plain phone number from a Baileys user id.
 *
 * Baileys user IDs have the form "628xxxxxxxxx:YY@s.whatsapp.net".
 * We want just the numeric part before the colon (the international number
 * without the country-code prefix "+" or any punctuation).
 */
function phoneFromBaileysId(uid) {
    if (!uid || typeof uid !== 'string') return null;
    // Strip everything from the colon or '@' onwards, whichever comes first.
    const atPart = uid.split('@')[0];       // "628xxx:YY"
    return atPart.split(':')[0] || null;     // "628xxx"
}

app.get('/api/health', (req, res) => {
    res.json({
        success: true,
        status: 'running',
        message: 'WhatsApp Bridge is operational (Baileys)',
        timestamp: new Date().toISOString(),
    });
});

app.post('/api/health', (req, res) => {
    res.json({
        success: true,
        status: 'running',
        message: 'WhatsApp Bridge is operational (Baileys)',
        timestamp: new Date().toISOString(),
    });
});

// Generate QR Code for a session
app.post('/api/generate-qr', qrLimiter, async (req, res) => {
    const { sessionId, userId } = req.body;

    console.log(`[${new Date().toISOString()}] QR generation request:`, { sessionId, userId });

    if (!sessionId || !userId) {
        return res.status(400).json({
            success: false,
            message: 'Missing sessionId or userId',
            received: { sessionId: !!sessionId, userId: !!userId },
        });
    }

    if (!validateSessionId(sessionId)) {
        return res.status(400).json({
            success: false,
            message: 'Invalid sessionId format',
        });
    }

    try {
        // ── Fast path: return a still-fresh cached QR ────────────────────────
        if (qrCache[sessionId] && qrCache[sessionId].expiry > Date.now()) {
            console.log(`Returning cached QR for session ${sessionId}`);
            return res.json({
                success: true,
                qr: qrCache[sessionId].data,
                sessionId,
                generated_at: new Date().toISOString(),
            });
        }

        // ── Dedup: if a socket is already initializing, wait for its QR ──────
        if (clientStates[sessionId] && clientStates[sessionId].initializing) {
            console.log(`Socket already initializing for session ${sessionId}, waiting for QR...`);
            for (let i = 0; i < MAX_QR_POLLING_ATTEMPTS; i++) {
                if (qrCache[sessionId] && qrCache[sessionId].expiry > Date.now()) {
                    return res.json({
                        success: true,
                        qr: qrCache[sessionId].data,
                        sessionId,
                        generated_at: new Date().toISOString(),
                    });
                }
                if (clientStates[sessionId]?.error) {
                    throw new Error(clientStates[sessionId].error);
                }
                await sleep(POLLING_INTERVAL_MS);
            }
            return res.status(408).json({
                success: false,
                error_type: 'QR_TIMEOUT',
                message: 'WhatsApp is taking longer than usual to load. Please click Retry.',
                sessionId,
            });
        }

        // ── Tear down any stale (non-initializing) socket ────────────────────
        if (clientStates[sessionId]) {
            console.log(`Destroying stale socket for session ${sessionId}...`);
            destroySession(sessionId);
        }

        // ── Clear stale credentials so a fresh QR is always shown ────────────
        clearSessionAuth(sessionId);

        // ── Create new Baileys socket ─────────────────────────────────────────
        console.log(`Creating new Baileys socket for session ${sessionId}...`);
        await createBaileysSocket(sessionId, userId);

        // ── Poll for QR code (Baileys fires it quickly — typically < 2 s) ────
        console.log(`Waiting for QR code for session ${sessionId}...`);
        for (let i = 0; i < MAX_QR_POLLING_ATTEMPTS; i++) {
            if (qrCache[sessionId] && qrCache[sessionId].expiry > Date.now()) {
                console.log(`✓ Returning QR code for session ${sessionId}`);
                return res.json({
                    success: true,
                    qr: qrCache[sessionId].data,
                    sessionId,
                    generated_at: new Date().toISOString(),
                });
            }
            if (clientStates[sessionId]?.error) {
                throw new Error(clientStates[sessionId].error);
            }
            await sleep(POLLING_INTERVAL_MS);
        }

        // ── Timeout ───────────────────────────────────────────────────────────
        console.error(`QR code generation timeout for session ${sessionId}`);
        if (clientStates[sessionId]) {
            clientStates[sessionId].initializing = false;
        }
        return res.status(408).json({
            success: false,
            error_type: 'QR_TIMEOUT',
            message: 'WhatsApp is taking longer than usual to load. Please click Retry.',
            sessionId,
        });

    } catch (error) {
        console.error(`[${new Date().toISOString()}] Error generating QR for session ${sessionId}:`, error.message);

        // Clean up on hard error
        if (clientStates[sessionId]) {
            destroySession(sessionId);
        }

        let userMessage = error.message;
        let helpText    = '';
        let errorType   = 'QR_GENERATION_ERROR';

        if (error.message.includes('ECONNREFUSED') || error.message.includes('ENOTFOUND')) {
            userMessage = 'Cannot connect to WhatsApp servers';
            helpText    = 'Ensure the server has outbound HTTPS/WSS access to WhatsApp servers.';
            errorType   = 'NETWORK_ERROR';
        } else if (error.message.includes('ETIMEDOUT') || error.message.toLowerCase().includes('timeout')) {
            userMessage = 'Connection to WhatsApp timed out';
            helpText    = 'Check firewall rules and DNS resolution for WhatsApp servers.';
            errorType   = 'NETWORK_ERROR';
        } else if (error.message.includes('Logged out')) {
            userMessage = error.message;
            errorType   = 'AUTH_FAILURE';
        }

        return res.status(500).json({
            success: false,
            error_type: errorType,
            message: userMessage,
            help: helpText,
            technicalError: error.message,
            sessionId,
        });
    }
});

// Check session status
app.post('/api/check-status', apiLimiter, (req, res) => {
    const { sessionId } = req.body;

    if (!sessionId) {
        return res.status(400).json({ success: false, message: 'Missing sessionId' });
    }

    const state = clientStates[sessionId];
    if (state && state.connected) {
        return res.json({
            success: true,
            connected: true,
            phoneNumber: state.phoneNumber || 'Connected',
        });
    }
    return res.json({ success: true, connected: false });
});

// Send a text message
app.post('/api/send-message', apiLimiter, async (req, res) => {
    const { sessionId, phoneNumber, message } = req.body;

    if (!sessionId || !phoneNumber || !message) {
        return res.status(400).json({ success: false, message: 'Missing required fields' });
    }

    const state = clientStates[sessionId];
    if (!state || !state.connected) {
        return res.status(400).json({ success: false, message: 'Session not connected' });
    }

    try {
        // Baileys JID for individual contacts: <digits>@s.whatsapp.net
        const jid = phoneNumber.replace(/[^0-9]/g, '') + '@s.whatsapp.net';
        await state.sock.sendMessage(jid, { text: message });
        return res.json({ success: true, message: 'Message sent successfully' });
    } catch (error) {
        console.error('Error sending message:', error);
        return res.status(500).json({ success: false, message: error.message });
    }
});

// Disconnect / log out a session
app.post('/api/disconnect', apiLimiter, async (req, res) => {
    const { sessionId } = req.body;

    if (!sessionId) {
        return res.status(400).json({ success: false, message: 'Missing sessionId' });
    }

    const state = clientStates[sessionId];
    if (state) {
        try {
            state.sock.ev.removeAllListeners();
            await state.sock.logout();
        } catch (_) {}
        delete clientStates[sessionId];
        delete qrCache[sessionId];
        clearSessionAuth(sessionId);
        return res.json({ success: true, message: 'Session disconnected' });
    }
    return res.json({ success: true, message: 'Session not found' });
});

// ── Start server ──────────────────────────────────────────────────────────────
const PORT = process.env.PORT || 3000;
const HOST = process.env.HOST || '0.0.0.0';

app.listen(PORT, HOST, () => {
    console.log(`WhatsApp Bridge (Baileys) running on http://${HOST}:${PORT}`);
    console.log(`Health check: http://localhost:${PORT}/api/health`);
    console.log(`Server started at: ${new Date().toISOString()}`);
});

