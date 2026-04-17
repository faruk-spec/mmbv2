<?php use Core\View; use Core\Helpers; use Core\Auth; use Core\Security; $currentUser = Auth::user(); ?>
<?php View::extend('whatsapp:app'); ?>

<?php View::section('content'); ?>

<style>
.sessions-container {
    max-width: 1200px;
    margin: 0 auto;
}

.session-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.session-card:hover {
    border-color: #25D366;
    transform: translateY(-2px);
}

.session-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 16px;
}

.session-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 8px;
    color: #25D366;
}

.session-phone {
    font-size: 0.9rem;
    color: var(--text-secondary);
}

.session-actions {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.875rem;
}

.btn-disconnect {
    background: #ff6b6b;
    color: white;
}

.btn-view-qr {
    background: #0088cc;
    color: white;
}

.qr-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}

.qr-modal-content {
    background: var(--bg-secondary);
    border-radius: 16px;
    padding: 32px;
    max-width: 400px;
    text-align: center;
}

.qr-code-container {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin: 20px 0;
}
</style>

<div class="sessions-container">
    <div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 8px; display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-mobile-alt" style="color: #25D366; font-size: 2rem;"></i>
                WhatsApp Sessions
            </h1>
            <p style="color: var(--text-secondary); font-size: 0.95rem;">Manage your WhatsApp connections</p>
        </div>
        <button onclick="createNewSession()" class="btn-whatsapp" style="background: #25D366; color: white; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
            <i class="fas fa-plus" style="margin-right: 6px;"></i>
            New Session
        </button>
    </div>

    <?php if (empty($sessions)): ?>
        <div class="session-card" style="text-align: center; padding: 60px 24px;">
            <i class="fas fa-mobile-alt" style="font-size: 64px; color: var(--text-secondary); margin-bottom: 20px; opacity: 0.5;"></i>
            <h3 style="color: var(--text-secondary); margin-bottom: 12px;">No Sessions Yet</h3>
            <p style="color: var(--text-secondary); margin-bottom: 20px;">Create your first WhatsApp session to get started</p>
            <button onclick="createNewSession()" style="background: #25D366; color: white; padding: 12px 32px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                Create Session
            </button>
        </div>
    <?php else: ?>
        <?php foreach ($sessions as $session): ?>
            <div class="session-card" data-session-id="<?= $session['id'] ?>">
                <div class="session-header">
                    <div>
                        <div class="session-title"><?= View::e($session['session_name']) ?></div>
                        <div class="session-phone"><?= View::e($session['phone_number'] ?? 'Not connected yet') ?></div>
                        <div style="margin-top: 8px;">
                            <span class="status-badge status-<?= $session['status'] ?>" style="padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                                <?= ucfirst($session['status']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="session-actions">
                        <?php if ($session['status'] === 'initializing' || $session['status'] === 'disconnected'): ?>
                            <button onclick="viewQRCode(<?= $session['id'] ?>)" class="btn btn-view-qr">
                                <i class="fas fa-qrcode" style="margin-right: 4px;"></i>
                                Scan QR
                            </button>
                        <?php endif; ?>
                        <?php if ($session['status'] === 'connected'): ?>
                            <button onclick="logoutSession(<?= $session['id'] ?>)" class="btn" style="background: #ff9800; color: white;">
                                <i class="fas fa-sign-out-alt" style="margin-right: 4px;"></i>
                                Logout
                            </button>
                            <button onclick="disconnectSession(<?= $session['id'] ?>)" class="btn btn-disconnect">
                                <i class="fas fa-times" style="margin-right: 4px;"></i>
                                Disconnect
                            </button>
                        <?php endif; ?>
                        <button onclick="deleteSession(<?= $session['id'] ?>, '<?= View::e($session['session_name']) ?>')" class="btn" style="background: #dc3545; color: white;">
                            <i class="fas fa-trash" style="margin-right: 4px;"></i>
                            Delete
                        </button>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 4px;">Created</div>
                        <div style="font-size: 0.875rem; font-weight: 600;"><?= date('M d, Y', strtotime($session['created_at'])) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 4px;">Last Active</div>
                        <div style="font-size: 0.875rem; font-weight: 600;"><?= $session['last_activity'] ? date('M d, H:i', strtotime($session['last_activity'])) : 'Never' ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 4px;">Session ID</div>
                        <div style="font-size: 0.875rem; font-weight: 600;"><?= $session['id'] ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- QR Code Modal -->
<div id="qrModal" class="qr-modal">
    <div class="qr-modal-content">
        <h2 style="margin-bottom: 16px; color: #25D366;">
            <i class="fab fa-whatsapp" style="margin-right: 8px;"></i>
            Scan QR Code
        </h2>
        
        <!-- Integration Note (will be shown/hidden based on QR type) -->
        <div id="qrIntegrationNote" style="background: rgba(255, 170, 0, 0.1); border: 1px solid #ffaa00; border-radius: 8px; padding: 12px; margin-bottom: 16px; display: none;">
            <div style="display: flex; align-items: start; gap: 10px;">
                <i class="fas fa-info-circle" style="color: #ffaa00; margin-top: 2px;"></i>
                <div style="flex: 1; font-size: 0.85rem; color: #ffaa00; line-height: 1.6;">
                    <strong>Note:</strong> WhatsApp Web.js bridge server is not responding. Please ensure the bridge server is running. See <code>WHATSAPP_PRODUCTION_GUIDE.md</code> for setup instructions.
                </div>
            </div>
        </div>
        
        <p style="color: var(--text-secondary); margin-bottom: 20px; font-size: 0.9rem;">
            Open WhatsApp on your phone and scan this QR code
        </p>
        
        <!-- Status Badge -->
        <div id="qrStatus" style="margin-bottom: 15px;">
            <span class="badge" style="background: rgba(37, 211, 102, 0.2); color: #25D366; padding: 8px 16px; border-radius: 20px; font-size: 0.85rem;">
                <i class="fas fa-sync fa-spin" style="margin-right: 6px;"></i>
                <span id="qrStatusText">Loading...</span>
            </span>
        </div>
        
        <div class="qr-code-container" id="qrCodeContainer">
            <div style="width: 256px; height: 256px; margin: 0 auto; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #666;">
                <div style="text-align: center;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 48px; margin-bottom: 16px; color: #25D366;"></i>
                    <div>Generating QR Code...</div>
                </div>
            </div>
        </div>
        
        <!-- Instructions -->
        <div id="qrInstructions" style="margin-top: 20px; text-align: left; background: rgba(37, 211, 102, 0.1); padding: 16px; border-radius: 8px; border-left: 3px solid #25D366;">
            <div style="font-weight: 600; margin-bottom: 8px; color: #25D366;">How to connect:</div>
            <ol style="margin: 0; padding-left: 20px; font-size: 0.85rem; color: var(--text-secondary); line-height: 1.8;">
                <li>Open WhatsApp on your phone</li>
                <li>Tap <strong>Menu</strong> or <strong>Settings</strong></li>
                <li>Tap <strong>Linked Devices</strong></li>
                <li>Tap <strong>Link a Device</strong></li>
                <li>Point your phone at this screen to scan the QR code</li>
            </ol>
        </div>
        
        <!-- Timer -->
        <div id="qrTimer" style="margin-top: 15px; font-size: 0.9rem; color: var(--text-secondary);">
            <i class="fas fa-clock" style="margin-right: 6px;"></i>
            QR code expires in <span id="qrTimeRemaining">60</span>s
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 20px;">
            <button onclick="refreshQRCode()" style="flex: 1; padding: 12px; background: #25D366; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                <i class="fas fa-sync-alt" style="margin-right: 6px;"></i>
                Refresh QR
            </button>
            <button onclick="closeQRModal()" style="flex: 1; padding: 12px; background: var(--bg-primary); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 8px; font-weight: 600; cursor: pointer;">
                <i class="fas fa-times" style="margin-right: 6px;"></i>
                Close
            </button>
        </div>
    </div>
</div>

<!-- Create Session Modal -->
<div id="createSessionModal" class="qr-modal" style="display: none;">
    <div class="qr-modal-content">
        <h2 style="margin-bottom: 16px; color: #25D366;">Create New Session</h2>
        <p style="color: var(--text-secondary); margin-bottom: 20px; font-size: 0.9rem;">
            Enter a name for your WhatsApp session
        </p>
        <form id="createSessionForm" onsubmit="submitCreateSession(event)">
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.875rem;">Session Name</label>
                <input type="text" id="sessionNameInput" name="session_name" 
                       placeholder="e.g., My WhatsApp Session" 
                       required
                       style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); font-size: 0.875rem;">
            </div>
            <div style="display: flex; gap: 12px;">
                <button type="submit" style="flex: 1; padding: 12px; background: #25D366; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-plus" style="margin-right: 6px;"></i>
                    Create Session
                </button>
                <button type="button" onclick="closeCreateSessionModal()" style="flex: 1; padding: 12px; background: var(--bg-primary); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="qr-modal" style="display: none;">
    <div class="qr-modal-content" style="max-width: 450px;">
        <h2 style="margin-bottom: 16px; color: #ff6b6b;" id="confirmTitle">Confirm Action</h2>
        <p style="color: var(--text-secondary); margin-bottom: 20px; font-size: 0.9rem;" id="confirmMessage">
            Are you sure?
        </p>
        <div style="display: flex; gap: 12px;">
            <button onclick="confirmAction()" style="flex: 1; padding: 12px; background: #ff6b6b; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                <i class="fas fa-check" style="margin-right: 6px;"></i>
                Confirm
            </button>
            <button onclick="closeConfirmModal()" style="flex: 1; padding: 12px; background: var(--bg-primary); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 8px; font-weight: 600; cursor: pointer;">
                Cancel
            </button>
        </div>
    </div>
</div>

<script>
let confirmCallback = null;
let qrPollInterval = null;
let qrTimeoutInterval = null;
let currentSessionId = null;
let qrExpiresAt = null;
let realQRToastShown = false;

function showConfirmModal(title, message, callback) {
    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmMessage').textContent = message;
    confirmCallback = callback;
    document.getElementById('confirmModal').style.display = 'flex';
}

function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
    confirmCallback = null;
}

function confirmAction() {
    if (confirmCallback) {
        confirmCallback();
    }
    closeConfirmModal();
}

// Toast notification system
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 24px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 10000;
        animation: slideIn 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    `;
    
    const colors = {
        success: '#25D366',
        error: '#ff6b6b',
        warning: '#ffaa00',
        info: '#0088cc'
    };
    
    toast.style.background = colors[type] || colors.info;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}" style="margin-right: 8px;"></i>${message}`;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(400px); opacity: 0; }
    }
`;
document.head.appendChild(style);

</script>

<script>
function createNewSession() {
    document.getElementById('createSessionModal').style.display = 'flex';
    document.getElementById('sessionNameInput').value = 'WhatsApp Session ' + (new Date().getTime().toString().slice(-4));
    document.getElementById('sessionNameInput').focus();
}

function closeCreateSessionModal() {
    document.getElementById('createSessionModal').style.display = 'none';
}

function submitCreateSession(event) {
    event.preventDefault();
    
    const sessionName = document.getElementById('sessionNameInput').value.trim();
    const submitBtn = event.target.querySelector('button[type="submit"]');
    
    if (!sessionName) {
        showToast('Please enter a session name', 'error');
        return;
    }
    
    // Disable button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 6px;"></i> Creating...';
    
    fetch('/projects/whatsapp/sessions/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'session_name=' + encodeURIComponent(sessionName) + '&csrf_token=<?= Security::generateCsrfToken() ?>'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            closeCreateSessionModal();
            showToast('Session created successfully!', 'success');
            
            // Reload page to show the new session
            // Using a delay to allow toast to be visible
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            throw new Error(data.message || 'Failed to create session');
        }
    })
    .catch(error => {
        console.error('Create session error:', error);
        showToast('Error: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-plus" style="margin-right: 6px;"></i> Create Session';
    });
}

function viewQRCode(sessionId) {
    currentSessionId = sessionId;
    realQRToastShown = false; // Reset flag for new QR session
    document.getElementById('qrModal').style.display = 'flex';
    
    // Reset state
    clearInterval(qrPollInterval);
    clearInterval(qrTimeoutInterval);
    
    // Update status
    updateQRStatus('Loading QR code...', 'loading');
    
    // Load QR code
    loadQRCode(sessionId);
    
    // Start polling for status updates every 3 seconds
    qrPollInterval = setInterval(() => {
        checkSessionStatus(sessionId);
    }, 3000);
}

function loadQRCode(sessionId) {
    fetch('/projects/whatsapp/sessions/qr?session_id=' + sessionId)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (data.status === 'connected') {
                    updateQRStatus('Already connected!', 'success');
                    showToast('This session is already connected', 'success');
                    document.getElementById('qrCodeContainer').innerHTML = `
                        <div style="width: 256px; height: 256px; margin: 0 auto; background: rgba(37, 211, 102, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 2px solid #25D366;">
                            <div style="text-align: center; color: #25D366;">
                                <i class="fas fa-check-circle" style="font-size: 64px; margin-bottom: 16px;"></i>
                                <div style="font-weight: 600;">Connected!</div>
                                ${data.phone_number ? '<div style="font-size: 0.9rem; margin-top: 8px;">' + data.phone_number + '</div>' : ''}
                            </div>
                        </div>
                    `;
                    // Hide integration note for connected sessions
                    document.getElementById('qrIntegrationNote').style.display = 'none';
                    clearInterval(qrPollInterval);
                } else {
                    // Check if QR is real or placeholder based on message field
                    // The backend returns 'Real QR code generated' for real QRs
                    const isRealQR = data.message && data.message.toLowerCase().includes('real qr');
                    
                    // Show/hide integration note based on QR type
                    document.getElementById('qrIntegrationNote').style.display = isRealQR ? 'none' : 'block';
                    
                    // Display QR code
                    updateQRStatus(isRealQR ? 'Ready to scan' : 'Waiting for bridge...', isRealQR ? 'ready' : 'warning');
                    document.getElementById('qrCodeContainer').innerHTML = `
                        <img src="${data.qr_code}" alt="QR Code" style="width: 256px; height: 256px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);" />
                    `;
                    
                    // Set expiration timer
                    if (data.expires_at) {
                        qrExpiresAt = data.expires_at;
                        startExpirationTimer();
                    }
                    
                    // Show success toast for real QR only once per session
                    if (isRealQR && !realQRToastShown) {
                        showToast('Real QR code generated successfully!', 'success');
                        realQRToastShown = true;
                    }
                }
            } else {
                throw new Error(data.message || 'Failed to load QR code');
            }
        })
        .catch(error => {
            console.error('QR code error:', error);
            updateQRStatus('Error loading QR code', 'error');
            showToast('Error: ' + error.message, 'error');
            document.getElementById('qrCodeContainer').innerHTML = `
                <div style="width: 256px; height: 256px; margin: 0 auto; background: rgba(255, 107, 107, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 2px solid #ff6b6b;">
                    <div style="text-align: center; color: #ff6b6b; padding: 20px;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 16px;"></i>
                        <div style="font-weight: 600;">Failed to load</div>
                        <div style="font-size: 0.85rem; margin-top: 8px;">${error.message}</div>
                    </div>
                </div>
            `;
        });
}

function checkSessionStatus(sessionId) {
    fetch('/projects/whatsapp/sessions/status?session_id=' + sessionId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.session.status === 'connected') {
                updateQRStatus('Connected!', 'success');
                showToast('WhatsApp connected successfully!', 'success');
                clearInterval(qrPollInterval);
                clearInterval(qrTimeoutInterval);
                setTimeout(() => {
                    closeQRModal();
                    location.reload();
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Status check error:', error);
        });
}

function startExpirationTimer() {
    clearInterval(qrTimeoutInterval);
    
    qrTimeoutInterval = setInterval(() => {
        const now = Math.floor(Date.now() / 1000);
        const remaining = qrExpiresAt - now;
        
        if (remaining <= 0) {
            clearInterval(qrTimeoutInterval);
            document.getElementById('qrTimeRemaining').textContent = '0';
            updateQRStatus('QR code expired', 'error');
            showToast('QR code expired. Please refresh.', 'warning');
        } else {
            document.getElementById('qrTimeRemaining').textContent = remaining;
        }
    }, 1000);
}

function updateQRStatus(text, type) {
    const statusEl = document.getElementById('qrStatusText');
    const badge = statusEl.parentElement;
    
    statusEl.innerHTML = text;
    
    const icons = {
        loading: '<i class="fas fa-sync fa-spin" style="margin-right: 6px;"></i>',
        ready: '<i class="fas fa-qrcode" style="margin-right: 6px;"></i>',
        success: '<i class="fas fa-check-circle" style="margin-right: 6px;"></i>',
        warning: '<i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i>',
        error: '<i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>'
    };
    
    const colors = {
        loading: 'rgba(0, 136, 204, 0.2); color: #0088cc',
        ready: 'rgba(37, 211, 102, 0.2); color: #25D366',
        success: 'rgba(37, 211, 102, 0.3); color: #25D366',
        warning: 'rgba(255, 170, 0, 0.2); color: #ffaa00',
        error: 'rgba(255, 107, 107, 0.2); color: #ff6b6b'
    };
    
    badge.style.background = colors[type] || colors.loading;
    statusEl.innerHTML = icons[type] + text;
}

function refreshQRCode() {
    if (currentSessionId) {
        loadQRCode(currentSessionId);
    }
}

function closeQRModal() {
    document.getElementById('qrModal').style.display = 'none';
    clearInterval(qrPollInterval);
    clearInterval(qrTimeoutInterval);
    currentSessionId = null;
    qrExpiresAt = null;
}

function logoutSession(sessionId) {
    showConfirmModal(
        'Logout Session',
        'Are you sure you want to logout and end this session? This will delete all session data.',
        () => {
            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            fetch('/projects/whatsapp/sessions/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'session_id=' + sessionId + '&csrf_token=<?= Security::generateCsrfToken() ?>'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned non-JSON response');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast('Session logged out successfully!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    throw new Error(data.message || 'Failed to logout session');
                }
            })
            .catch(error => {
                console.error('Logout error:', error);
                showToast('Error: ' + error.message, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-sign-out-alt" style="margin-right: 4px;"></i> Logout';
            });
        }
    );
}

function disconnectSession(sessionId) {
    showConfirmModal(
        'Disconnect Session',
        'Are you sure you want to disconnect this session?',
        () => {
            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            fetch('/projects/whatsapp/sessions/disconnect', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'session_id=' + sessionId + '&csrf_token=<?= Security::generateCsrfToken() ?>'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned non-JSON response');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast('Session disconnected successfully!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    throw new Error(data.message || 'Failed to disconnect session');
                }
            })
            .catch(error => {
                console.error('Disconnect error:', error);
                showToast('Error: ' + error.message, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-times" style="margin-right: 4px;"></i> Disconnect';
            });
        }
    );
}

function deleteSession(sessionId, sessionName) {
    showConfirmModal(
        'Delete Session',
        `Are you sure you want to delete session "${sessionName}"? This action cannot be undone.`,
        function() {
            // Find the button that triggered this
            const sessionCard = document.querySelector(`.session-card[data-session-id="${sessionId}"]`);
            const btn = sessionCard ? sessionCard.querySelector('button[onclick*="deleteSession"]') : null;
            
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 4px;"></i> Deleting...';
            }
            
            fetch('/projects/whatsapp/sessions/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'session_id=' + sessionId + '&csrf_token=<?= Security::generateCsrfToken() ?>'
            })
            .then(response => {
                // Validate content type before parsing
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        throw new Error('Server returned non-JSON response: ' + text.substring(0, 100));
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast('Session deleted successfully', 'success');
                    // Remove the session card from DOM
                    if (sessionCard) {
                        sessionCard.style.animation = 'slideOut 0.3s ease';
                        setTimeout(() => {
                            sessionCard.remove();
                            // Check if there are no more sessions
                            const remainingSessions = document.querySelectorAll('.session-card');
                            if (remainingSessions.length === 0) {
                                location.reload();
                            }
                        }, 300);
                    } else {
                        setTimeout(() => location.reload(), 1000);
                    }
                } else {
                    throw new Error(data.message || 'Failed to delete session');
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                showToast('Error: ' + error.message, 'error');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-trash" style="margin-right: 4px;"></i> Delete';
                }
            });
        }
    );
}
</script>

<?php View::endSection(); ?>
