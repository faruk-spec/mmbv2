<?php use Core\View; use Core\Helpers; use Core\Auth; use Core\Security; $currentUser = Auth::user(); ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<style>
.settings-container {
    max-width: 900px;
    margin: 0 auto;
}

.settings-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
}

.settings-card-header {
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 20px;
}

.settings-card-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #25D366;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    font-size: 0.875rem;
}

.form-input {
    width: 100%;
    padding: 12px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-primary);
    color: var(--text-primary);
    font-size: 0.875rem;
    font-family: 'Courier New', monospace;
}

.api-key-display {
    display: flex;
    gap: 12px;
    align-items: center;
}

.api-key-box {
    flex: 1;
    padding: 12px;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
    word-break: break-all;
}

.btn-action {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.875rem;
}

.btn-primary {
    background: #25D366;
    color: white;
}

.btn-primary:hover {
    background: #20BA58;
}

.btn-secondary {
    background: var(--bg-primary);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}

.btn-secondary:hover {
    border-color: #25D366;
}

.btn-danger {
    background: #ff6b6b;
    color: white;
}

.btn-danger:hover {
    background: #ee5a5a;
}

.info-box {
    background: rgba(37, 211, 102, 0.1);
    border: 1px solid rgba(37, 211, 102, 0.3);
    border-radius: 8px;
    padding: 16px;
    margin-top: 16px;
    font-size: 0.875rem;
    line-height: 1.6;
}

.info-box svg {
    vertical-align: middle;
    margin-right: 8px;
}
</style>

<div class="settings-container">
    <div style="margin-bottom: 30px;">
        <h1 style="font-size: 2rem; margin-bottom: 8px; display: flex; align-items: center; gap: 12px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2">
                <circle cx="12" cy="12" r="3"/>
                <path d="M12 1v6m0 6v6"/>
                <path d="M21 12h-6m-6 0H3"/>
            </svg>
            Settings
        </h1>
        <p style="color: var(--text-secondary); font-size: 0.95rem;">Manage your WhatsApp API settings</p>
    </div>

    <!-- API Key Section -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                API Key
            </h3>
        </div>

        <div class="form-group">
            <label class="form-label">Your API Key</label>
            <?php if ($apiKey): ?>
                <div class="api-key-display">
                    <div class="api-key-box" id="apiKeyBox"><?= View::e($apiKey) ?></div>
                    <button class="btn-action btn-secondary" onclick="copyApiKey()" title="Copy to clipboard">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                        </svg>
                    </button>
                </div>
            <?php else: ?>
                <div class="api-key-box" style="color: var(--text-secondary); font-style: italic;">
                    No API key generated yet
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 16px;">
                <button class="btn-action btn-primary" onclick="generateNewApiKey()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 6px;">
                        <polyline points="23 4 23 10 17 10"/>
                        <polyline points="1 20 1 14 7 14"/>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                    </svg>
                    <?= $apiKey ? 'Regenerate API Key' : 'Generate API Key' ?>
                </button>
            </div>

            <div class="info-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                <strong>Important:</strong> Use this API key to authenticate your requests. Include it in the Authorization header: <code>Authorization: Bearer YOUR_API_KEY</code>
            </div>
        </div>
    </div>

    <!-- Webhook Settings -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
                Webhook Configuration
            </h3>
        </div>

        <div class="form-group">
            <label class="form-label">Webhook URL</label>
            <input type="url" class="form-input" id="webhookUrl" value="<?= View::e($webhookUrl) ?>" placeholder="https://your-domain.com/webhook" />
            <div style="margin-top: 12px;">
                <button class="btn-action btn-primary" onclick="updateWebhook()">
                    Save Webhook URL
                </button>
            </div>

            <div class="info-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                Webhook URL will receive POST requests when you receive new messages or when session status changes.
            </div>
        </div>
    </div>

    <!-- Usage Statistics -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2">
                    <line x1="12" y1="20" x2="12" y2="10"/>
                    <line x1="18" y1="20" x2="18" y2="4"/>
                    <line x1="6" y1="20" x2="6" y2="16"/>
                </svg>
                Usage Statistics
            </h3>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
            <div style="text-align: center; padding: 20px; background: rgba(37, 211, 102, 0.05); border-radius: 8px;">
                <div style="font-size: 2rem; font-weight: 700; color: #25D366; margin-bottom: 8px;">0</div>
                <div style="font-size: 0.875rem; color: var(--text-secondary);">API Calls Today</div>
            </div>
            <div style="text-align: center; padding: 20px; background: rgba(37, 211, 102, 0.05); border-radius: 8px;">
                <div style="font-size: 2rem; font-weight: 700; color: #25D366; margin-bottom: 8px;">0</div>
                <div style="font-size: 0.875rem; color: var(--text-secondary);">Messages Sent</div>
            </div>
        </div>
    </div>

    <!-- Documentation Link -->
    <div style="text-align: center; padding: 24px;">
        <p style="color: var(--text-secondary); margin-bottom: 16px;">Need help with the API?</p>
        <a href="/projects/whatsapp/api-docs" style="background: #9945ff; color: white; padding: 12px 32px; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-block;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 6px;">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            View API Documentation
        </a>
    </div>
</div>

<script>
function copyApiKey() {
    const apiKey = document.getElementById('apiKeyBox').textContent;
    navigator.clipboard.writeText(apiKey).then(() => {
        alert('API Key copied to clipboard!');
    });
}

function generateNewApiKey() {
    if (<?= $apiKey ? 'true' : 'false' ?>) {
        if (!confirm('This will invalidate your current API key. Continue?')) {
            return;
        }
    }
    
    fetch('/projects/whatsapp/settings/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=generate_api_key&csrf_token=<?= Security::generateCSRF() ?>`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('API Key generated successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function updateWebhook() {
    const webhookUrl = document.getElementById('webhookUrl').value;
    
    fetch('/projects/whatsapp/settings/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update_webhook&webhook_url=${encodeURIComponent(webhookUrl)}&csrf_token=<?= Security::generateCSRF() ?>`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Webhook URL updated successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    });
}
</script>

<?php View::endSection(); ?>
