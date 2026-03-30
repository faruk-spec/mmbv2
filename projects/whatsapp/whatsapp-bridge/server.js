/**
 * WhatsApp Bridge — powered by Baileys (no browser / no Puppeteer).
 *
 * Baileys implements the WhatsApp Web protocol directly over WebSockets so
 * there is no dependency on Chrome and no net::ERR_TIMED_OUT from a browser
 * trying to load the WhatsApp Web page.
 *
 * If the server cannot establish a TLS connection to WhatsApp's servers
 * (e.g. SSL handshake failure such as SSL_R_UNKNOWN_PROTOCOL), set the
 * WHATSAPP_PROXY_URL environment variable to route all WhatsApp traffic
 * through a proxy that CAN reach WhatsApp:
 *
 *   WHATSAPP_PROXY_URL=socks5://user:pass@proxy-host:1080
 *   WHATSAPP_PROXY_URL=http://user:pass@proxy-host:3128
 *
 * Run GET /api/connectivity-test to diagnose TLS/DNS issues without starting
 * a full Baileys session.
 */

// ── Baileys — use explicit destructuring for the CJS build ───────────────────
const {
    default: makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
} = require('@whiskeysockets/baileys');

const pino       = require('pino');
const rateLimit  = require('express-rate-limit');
const express    = require('express');
const bodyParser = require('body-parser');
const QRCode     = require('qrcode');
const tls        = require('tls');
const dns        = require('dns').promises;
const fs         = require('fs');
const path       = require('path');

const app = express();
app.use(bodyParser.json());

// ── Rate limiting ─────────────────────────────────────────────────────────────
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

// ── Configuration ─────────────────────────────────────────────────────────────
const POLLING_INTERVAL_MS     = 500;   // Poll every 500 ms while waiting for QR
const MAX_QR_POLLING_ATTEMPTS = 60;    // 60 × 500 ms = 30 s wait per request
const QR_CACHE_TTL_MS         = 60000; // Cached QR stays valid for 60 s

// Auth credentials stored per-session below this directory.
const AUTH_BASE_DIR = path.resolve(process.cwd(), '.baileys_auth');

// Per-session state map
const clientStates = {};

// Per-session QR cache: { data (data-URL), expiry (ms timestamp) }
const qrCache = {};

// ── Proxy agent ───────────────────────────────────────────────────────────────
/**
 * Build an HTTP or SOCKS proxy agent from WHATSAPP_PROXY_URL.
 *
 *   WHATSAPP_PROXY_URL=socks5://user:pass@host:port
 *   WHATSAPP_PROXY_URL=http://user:pass@host:port
 *
 * The agent is passed to makeWASocket so that ALL outgoing WhatsApp WebSocket
 * connections are routed through the proxy.  Use this when the server's
 * TLS stack cannot reach wss://web.whatsapp.com directly (e.g. SSL error
 * SSL_R_UNKNOWN_PROTOCOL reported by curl https://web.whatsapp.com).
 */
let proxyAgent = null;
(function initProxyAgent() {
    const proxyUrl = process.env.WHATSAPP_PROXY_URL;
    if (!proxyUrl) return;
    try {
        const parsed = new URL(proxyUrl);
        const isSocks = /^socks/i.test(parsed.protocol);
        if (isSocks) {
            const { SocksProxyAgent } = require('socks-proxy-agent');
            proxyAgent = new SocksProxyAgent(proxyUrl);
        } else {
            const { HttpsProxyAgent } = require('https-proxy-agent');
            proxyAgent = new HttpsProxyAgent(proxyUrl);
        }
        console.log(`[Proxy] Using ${parsed.protocol}//${parsed.hostname}:${parsed.port} for WhatsApp connections`);
    } catch (err) {
        console.error('[Proxy] Invalid WHATSAPP_PROXY_URL — ignoring:', err.message);
    }
})();

// ── Helper utilities ──────────────────────────────────────────────────────────

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

/** Allow only alphanumeric, hyphen, underscore — prevents path traversal. */
function validateSessionId(sessionId) {
    return typeof sessionId === 'string' && /^[a-zA-Z0-9_-]+$/.test(sessionId);
}

/**
 * Resolve the per-session auth directory, rejecting any path that escapes
 * AUTH_BASE_DIR (platform-agnostic: uses path.relative()).
 */
function getAuthDir(sessionId) {
    const authDir  = path.resolve(AUTH_BASE_DIR, sessionId);
    const relative = path.relative(AUTH_BASE_DIR, authDir);
    if (relative.startsWith('..') || path.isAbsolute(relative)) {
        throw new Error(`Path escape detected for sessionId "${sessionId}"`);
    }
    return authDir;
}

/**
 * Wipe the per-session Baileys auth directory so the next connection shows a
 * fresh QR instead of silently restoring stale credentials.
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
 * Remove all Baileys event listeners and close the WebSocket without logging
 * out.  Cleans up clientStates and qrCache for the session.
 */
function destroySession(sessionId) {
    const state = clientStates[sessionId];
    if (state && state.sock) {
        try {
            state.sock.ev.removeAllListeners();
            state.sock.end(undefined);
        } catch (_) {}
    }
    delete clientStates[sessionId];
    delete qrCache[sessionId];
}

/**
 * Extract the plain phone number from a Baileys user ID.
 * Baileys IDs look like "628xxxxxxxxx:YY@s.whatsapp.net".
 */
function phoneFromBaileysId(uid) {
    if (!uid || typeof uid !== 'string') return null;
    const atPart = uid.split('@')[0];   // "628xxx:YY"
    return atPart.split(':')[0] || null; // "628xxx"
}

/**
 * Classify an error message from Baileys / Node.js into a user-friendly type.
 */
function classifyError(errMsg) {
    if (!errMsg) return { errorType: 'UNKNOWN_ERROR', userMessage: 'Unknown error', helpText: '' };

    const SSL_PATTERNS    = ['ssl', 'tls', 'eproto', 'ssl_r_', 'unknown_protocol', 'ssl routines'];
    const NETWORK_REFUSED = ['econnrefused', 'enotfound'];
    const NETWORK_TIMEOUT = ['etimedout', 'timeout'];
    const m = errMsg.toLowerCase();

    if (SSL_PATTERNS.some(p => m.includes(p))) {
        return {
            errorType:   'SSL_ERROR',
            userMessage: 'TLS/SSL handshake failed when connecting to WhatsApp servers.',
            helpText: 'Run: curl https://web.whatsapp.com on the server. If you see an SSL error, ' +
                      'set WHATSAPP_PROXY_URL=socks5://user:pass@proxy:port before starting the bridge ' +
                      'to route WhatsApp traffic through a proxy that can reach WhatsApp.',
        };
    }
    if (NETWORK_REFUSED.some(p => m.includes(p))) {
        return {
            errorType:   'NETWORK_ERROR',
            userMessage: 'Cannot connect to WhatsApp servers.',
            helpText: 'Check DNS and outbound HTTPS/WSS access on the server, or set WHATSAPP_PROXY_URL.',
        };
    }
    if (NETWORK_TIMEOUT.some(p => m.includes(p))) {
        return {
            errorType:   'NETWORK_ERROR',
            userMessage: 'Connection to WhatsApp servers timed out.',
            helpText: 'The server may be blocked by a firewall. Try setting WHATSAPP_PROXY_URL.',
        };
    }
    if (m.includes('logged out')) {
        return { errorType: 'AUTH_FAILURE', userMessage: errMsg, helpText: '' };
    }
    return { errorType: 'QR_GENERATION_ERROR', userMessage: errMsg, helpText: '' };
}

// ── Baileys session factory ───────────────────────────────────────────────────

/**
 * Open a new Baileys WebSocket for a session.  The caller is responsible for
 * clearing any existing clientStates / qrCache entries first.
 */
async function createBaileysSocket(sessionId, userId) {
    const authDir = getAuthDir(sessionId);
    fs.mkdirSync(authDir, { recursive: true });

    const { state, saveCreds } = await useMultiFileAuthState(authDir);

    const sock = makeWASocket({
        auth: state,
        printQRInTerminal: false,
        // Use 'warn' so real errors (SSL, network) appear in the bridge log
        // without drowning them in Baileys' debug noise.
        logger: pino({ level: 'warn' }),
        // Identify as a recent Chrome desktop browser.
        browser: ['WhatsApp Bridge', 'Chrome', process.env.BAILEYS_BROWSER_VERSION || '124.0.0'],
        // Skip fetching full message history on reconnect.
        syncFullHistory: false,
        // Route through proxy if WHATSAPP_PROXY_URL is set.
        ...(proxyAgent ? { agent: proxyAgent } : {}),
    });

    clientStates[sessionId] = {
        sock,
        userId,
        connected:    false,
        initializing: true,
        error:        null,
        phoneNumber:  null,
    };

    // Persist Baileys credentials after any key rotation.
    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;

        // ── QR code emitted ──────────────────────────────────────────────────
        if (qr) {
            console.log(`✓ QR Code received for session ${sessionId}`);
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

        // ── Connected ────────────────────────────────────────────────────────
        if (connection === 'open') {
            console.log(`✓ Session ${sessionId} connected`);
            if (clientStates[sessionId]) {
                clientStates[sessionId].connected    = true;
                clientStates[sessionId].initializing = false;
                clientStates[sessionId].phoneNumber  = phoneFromBaileysId(sock.user?.id);
            }
        }

        // ── Closed ───────────────────────────────────────────────────────────
        if (connection === 'close') {
            const err       = lastDisconnect?.error;
            const statusCode = err?.output?.statusCode;
            const errMsg     = err?.message || 'Connection closed';
            console.error(`Session ${sessionId} closed — code ${statusCode}: ${errMsg}`);

            if (clientStates[sessionId]) {
                if (statusCode === DisconnectReason.loggedOut) {
                    clearSessionAuth(sessionId);
                    clientStates[sessionId].error = 'Logged out from WhatsApp. Please scan QR again.';
                } else {
                    // Expose the real error message so the polling loop can
                    // surface it immediately rather than waiting for timeout.
                    clientStates[sessionId].error = errMsg;
                }
                clientStates[sessionId].connected    = false;
                clientStates[sessionId].initializing = false;
            }
            delete qrCache[sessionId];
        }
    });
}

// ── HTTP API ──────────────────────────────────────────────────────────────────

app.get('/api/health', (req, res) => {
    res.json({
        success:   true,
        status:    'running',
        message:   'WhatsApp Bridge is operational (Baileys)',
        proxy:     proxyAgent ? 'configured' : 'none',
        timestamp: new Date().toISOString(),
    });
});

app.post('/api/health', (req, res) => {
    res.json({
        success:   true,
        status:    'running',
        message:   'WhatsApp Bridge is operational (Baileys)',
        proxy:     proxyAgent ? 'configured' : 'none',
        timestamp: new Date().toISOString(),
    });
});

/**
 * GET /api/connectivity-test
 *
 * Performs a DNS lookup and a direct TLS handshake against web.whatsapp.com
 * without starting a Baileys session.  Use this to quickly diagnose whether
 * the server can reach WhatsApp at all.
 */
app.get('/api/connectivity-test', async (req, res) => {
    const results = {
        dns: { success: false, address: null, error: null },
        tls: { success: false, protocol: null, cipher: null, error: null },
        proxy: {
            configured: !!proxyAgent,
            url: process.env.WHATSAPP_PROXY_URL
                ? process.env.WHATSAPP_PROXY_URL.replace(/:\/\/[^:@]*:[^@]*@/, '://***:***@') // redact user:pass
                : null,
        },
    };

    // DNS
    try {
        const addr = await dns.lookup('web.whatsapp.com');
        results.dns.success = true;
        results.dns.address = addr.address;
    } catch (err) {
        results.dns.error = err.message;
    }

    // TLS (direct — does NOT use the proxy agent)
    await new Promise(resolve => {
        const socket = tls.connect(
            { host: 'web.whatsapp.com', port: 443, servername: 'web.whatsapp.com', timeout: 10000 },
            () => {
                results.tls.success  = true;
                results.tls.protocol = socket.getProtocol();
                results.tls.cipher   = socket.getCipher()?.name ?? null;
                socket.destroy();
                resolve();
            }
        );
        socket.on('error', err => {
            results.tls.error = err.message;
            socket.destroy();
            resolve();
        });
        socket.on('timeout', () => {
            results.tls.error = 'Timed out after 10 s';
            socket.destroy();
            resolve();
        });
    });

    const ok = results.dns.success && results.tls.success;
    const recommendation = [];
    if (!results.dns.success) {
        recommendation.push('DNS failed — check that the server can resolve web.whatsapp.com.');
    }
    if (results.dns.success && !results.tls.success) {
        recommendation.push(
            'DNS works but TLS handshake failed.',
            'Likely cause: the VPS provider or firewall is blocking / intercepting HTTPS to WhatsApp.',
            'Fix: set WHATSAPP_PROXY_URL=socks5://user:pass@proxy-host:1080 (or http://...) ' +
            'in the bridge environment and restart the bridge.',
            'The proxy must be a server that CAN reach web.whatsapp.com.'
        );
    }

    return res.status(ok ? 200 : 503).json({
        success: ok,
        message: ok ? 'Server can reach WhatsApp — TLS handshake succeeded.' :
                      'Server CANNOT reach WhatsApp (see recommendation).',
        results,
        recommendation: recommendation.length ? recommendation : undefined,
    });
});

/**
 * POST /api/generate-qr
 * Body: { sessionId, userId }
 *
 * Starts a Baileys session (if none is running) and waits up to 30 s for a
 * QR code to be emitted.  Returns the QR as a base64 data-URL on success.
 */
app.post('/api/generate-qr', qrLimiter, async (req, res) => {
    const { sessionId, userId } = req.body;

    console.log(`[${new Date().toISOString()}] QR request:`, { sessionId, userId });

    if (!sessionId || !userId) {
        return res.status(400).json({
            success: false,
            message: 'Missing sessionId or userId',
            received: { sessionId: !!sessionId, userId: !!userId },
        });
    }
    if (!validateSessionId(sessionId)) {
        return res.status(400).json({ success: false, message: 'Invalid sessionId format' });
    }

    try {
        // ── Fast path: cached QR still valid ─────────────────────────────────
        if (qrCache[sessionId] && qrCache[sessionId].expiry > Date.now()) {
            console.log(`Returning cached QR for ${sessionId}`);
            return res.json({
                success:      true,
                qr:           qrCache[sessionId].data,
                sessionId,
                generated_at: new Date().toISOString(),
            });
        }

        // ── Dedup: socket is already initializing — wait for its QR ──────────
        if (clientStates[sessionId]?.initializing) {
            console.log(`Socket already initializing for ${sessionId}, polling for QR…`);
            for (let i = 0; i < MAX_QR_POLLING_ATTEMPTS; i++) {
                if (qrCache[sessionId]?.expiry > Date.now()) {
                    return res.json({
                        success: true, qr: qrCache[sessionId].data,
                        sessionId, generated_at: new Date().toISOString(),
                    });
                }
                if (clientStates[sessionId]?.error) {
                    throw new Error(clientStates[sessionId].error);
                }
                await sleep(POLLING_INTERVAL_MS);
            }
            return res.status(408).json({
                success: false, error_type: 'QR_TIMEOUT',
                message: 'WhatsApp is taking longer than usual to load. Please click Retry.',
                sessionId,
            });
        }

        // ── Tear down any stale socket ────────────────────────────────────────
        if (clientStates[sessionId]) {
            console.log(`Destroying stale socket for ${sessionId}…`);
            destroySession(sessionId);
        }

        // Clear credentials so we always get a fresh QR.
        clearSessionAuth(sessionId);

        // ── Open new Baileys socket ───────────────────────────────────────────
        console.log(`Creating Baileys socket for ${sessionId}…` +
            (proxyAgent ? ' (via proxy)' : ''));
        await createBaileysSocket(sessionId, userId);

        // ── Poll for QR (should arrive in < 5 s on a healthy connection) ─────
        console.log(`Waiting for QR for ${sessionId}…`);
        for (let i = 0; i < MAX_QR_POLLING_ATTEMPTS; i++) {
            if (qrCache[sessionId]?.expiry > Date.now()) {
                console.log(`✓ Returning QR for ${sessionId}`);
                return res.json({
                    success: true, qr: qrCache[sessionId].data,
                    sessionId, generated_at: new Date().toISOString(),
                });
            }
            // If connection.update fired 'close' we'll have an error set.
            if (clientStates[sessionId]?.error) {
                throw new Error(clientStates[sessionId].error);
            }
            await sleep(POLLING_INTERVAL_MS);
        }

        // ── Timeout ───────────────────────────────────────────────────────────
        console.error(`QR timeout for ${sessionId}`);
        if (clientStates[sessionId]) {
            clientStates[sessionId].initializing = false;
        }
        return res.status(408).json({
            success: false, error_type: 'QR_TIMEOUT',
            message: 'WhatsApp is taking longer than usual to load. Please click Retry.',
            hint: proxyAgent
                ? 'Proxy is configured but WhatsApp is still not responding. Check the proxy server.'
                : 'If the server has TLS/SSL issues reaching WhatsApp, set WHATSAPP_PROXY_URL.',
            sessionId,
        });

    } catch (error) {
        console.error(`[${new Date().toISOString()}] QR error for ${sessionId}:`, error.message);

        if (clientStates[sessionId]) {
            destroySession(sessionId);
        }

        const { errorType, userMessage, helpText } = classifyError(error.message);

        return res.status(500).json({
            success: false,
            error_type: errorType,
            message: userMessage,
            help: helpText || (
                !proxyAgent
                    ? 'Run GET /api/connectivity-test on the bridge to diagnose TLS issues.'
                    : 'Proxy is configured — check that the proxy server can reach WhatsApp.'
            ),
            technicalError: error.message,
            sessionId,
        });
    }
});

// Check whether a session is connected
app.post('/api/check-status', apiLimiter, (req, res) => {
    const { sessionId } = req.body;
    if (!sessionId) {
        return res.status(400).json({ success: false, message: 'Missing sessionId' });
    }
    const state = clientStates[sessionId];
    if (state?.connected) {
        return res.json({ success: true, connected: true, phoneNumber: state.phoneNumber || 'Connected' });
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
    if (!state?.connected) {
        return res.status(400).json({ success: false, message: 'Session not connected' });
    }
    try {
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
    console.log(`Health check:        GET  http://localhost:${PORT}/api/health`);
    console.log(`Connectivity check:  GET  http://localhost:${PORT}/api/connectivity-test`);
    console.log(`Server started at: ${new Date().toISOString()}`);
    if (!proxyAgent) {
        console.log('Tip: set WHATSAPP_PROXY_URL=socks5://... if the server has TLS issues reaching WhatsApp.');
    }
});

