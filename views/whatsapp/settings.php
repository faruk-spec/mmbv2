<?php use Core\View; use Core\Helpers; use Core\Auth; use Core\Security; $currentUser = Auth::user(); ?>
<?php View::extend('whatsapp:app'); ?>

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
            <i class="fas fa-cog" style="color: #25D366; font-size: 2rem;"></i>
            Settings
        </h1>
        <p style="color: var(--text-secondary); font-size: 0.95rem;">Manage your WhatsApp API settings</p>
    </div>

    <!-- API Key Section -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i class="fas fa-key" style="color: #25D366;"></i>
                API Key
            </h3>
        </div>

        <div class="form-group">
            <label class="form-label">Your API Key</label>
            <?php if ($apiKey): ?>
                <div class="api-key-display">
                    <div class="api-key-box" id="apiKeyBox"><?= View::e($apiKey) ?></div>
                    <button class="btn-action btn-secondary" onclick="copyApiKey()" title="Copy to clipboard">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            <?php else: ?>
                <div class="api-key-box" style="color: var(--text-secondary); font-style: italic;">
                    No API key generated yet
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 16px;">
                <button class="btn-action btn-primary" onclick="generateNewApiKey()">
                    <i class="fas fa-sync" style="margin-right: 6px;"></i>
                    <?= $apiKey ? 'Regenerate API Key' : 'Generate API Key' ?>
                </button>
            </div>

            <div class="info-box">
                <i class="fas fa-info-circle" style="color: #25D366;"></i>
                <strong>Important:</strong> Use this API key to authenticate your requests. Include it in the Authorization header: <code>Authorization: Bearer YOUR_API_KEY</code>
            </div>
        </div>
    </div>

    <!-- Webhook Settings -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i class="fas fa-webhook" style="color: #25D366;"></i>
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
                <i class="fas fa-info-circle" style="color: #25D366;"></i>
                Webhook URL will receive POST requests when you receive new messages or when session status changes.
            </div>
        </div>
    </div>

    <!-- Usage Statistics -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i class="fas fa-chart-bar" style="color: #25D366;"></i>
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
            <i class="fas fa-book" style="margin-right: 6px;"></i>
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
        body: `action=generate_api_key&csrf_token=<?= Security::generateCsrfToken() ?>`
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
        body: `action=update_webhook&webhook_url=${encodeURIComponent(webhookUrl)}&csrf_token=<?= Security::generateCsrfToken() ?>`
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
