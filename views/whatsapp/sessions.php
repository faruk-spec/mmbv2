<?php use Core\View; use Core\Helpers; use Core\Auth; use Core\Security; $currentUser = Auth::user(); ?>
<?php View::extend('Projects\\WhatsApp', 'app'); ?>

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
                            <button onclick="disconnectSession(<?= $session['id'] ?>)" class="btn btn-disconnect">
                                <i class="fas fa-times" style="margin-right: 4px;"></i>
                                Disconnect
                            </button>
                        <?php endif; ?>
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
        <h2 style="margin-bottom: 16px; color: #25D366;">Scan QR Code</h2>
        <p style="color: var(--text-secondary); margin-bottom: 20px; font-size: 0.9rem;">
            Open WhatsApp on your phone and scan this QR code
        </p>
        <div class="qr-code-container" id="qrCodeContainer">
            <div style="width: 256px; height: 256px; margin: 0 auto; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #666;">
                Loading QR Code...
            </div>
        </div>
        <button onclick="closeQRModal()" style="width: 100%; padding: 12px; background: var(--bg-primary); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 20px;">
            Close
        </button>
    </div>
</div>

<script>
function createNewSession() {
    const sessionName = prompt('Enter session name:', 'WhatsApp Session');
    if (!sessionName) return;
    
    fetch('/projects/whatsapp/sessions/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'session_name=' + encodeURIComponent(sessionName) + '&csrf_token=<?= Security::generateCsrfToken() ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Session created successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Network error: ' + error);
    });
}

function viewQRCode(sessionId) {
    document.getElementById('qrModal').style.display = 'flex';
    
    fetch('/projects/whatsapp/sessions/qr?session_id=' + sessionId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('qrCodeContainer').innerHTML = `
                    <div style="width: 256px; height: 256px; margin: 0 auto; background: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; color: #666; padding: 20px; text-align: center;">
                        QR Code will appear here<br><br>
                        <small style="font-size: 0.75rem;">In production, this integrates with WhatsApp Web API</small>
                    </div>
                `;
            }
        });
}

function closeQRModal() {
    document.getElementById('qrModal').style.display = 'none';
}

function disconnectSession(sessionId) {
    if (!confirm('Are you sure you want to disconnect this session?')) return;
    
    fetch('/projects/whatsapp/sessions/disconnect', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'session_id=' + sessionId + '&csrf_token=<?= Security::generateCsrfToken() ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Session disconnected successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}
</script>

<?php View::endSection(); ?>
