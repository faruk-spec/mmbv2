<?php
use Core\View;
use Core\Security;
View::extend('whatsapp:app');
View::section('content');
?>
<div style="padding:4px 0 20px;">
    <h2 style="font-size:1.5rem;font-weight:700;margin-bottom:4px;color:var(--whatsapp-green);">
        <i class="fas fa-cog" style="margin-right:8px;"></i>Settings
    </h2>
    <p style="color:var(--text-secondary);font-size:.9rem;">Configure your WhatsApp integration settings.</p>
</div>

<div id="settingsMsg" style="display:none;margin-bottom:14px;"></div>

<!-- API Key -->
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;margin-bottom:20px;">
    <h3 style="font-size:.95rem;font-weight:700;margin-bottom:14px;color:var(--text-primary);">API Key</h3>
    <?php if (!empty($apiKey)): ?>
    <div style="display:flex;align-items:center;gap:10px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;padding:10px 14px;">
        <code id="apiKeyVal" style="flex:1;font-size:.85rem;color:var(--whatsapp-green);word-break:break-all;"><?= View::e($apiKey) ?></code>
        <button onclick="navigator.clipboard.writeText(document.getElementById('apiKeyVal').textContent)" style="padding:5px 10px;background:transparent;border:1px solid var(--border-color);border-radius:6px;color:var(--text-secondary);cursor:pointer;font-size:.75rem;" title="Copy">
            <i class="fas fa-copy"></i>
        </button>
    </div>
    <?php else: ?>
    <p style="color:var(--text-secondary);font-size:.9rem;">No API key configured. Visit the <a href="/projects/whatsapp/api" style="color:var(--whatsapp-green);">API & Analytics</a> page to generate one.</p>
    <?php endif; ?>
</div>

<!-- Webhook Settings -->
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
    <h3 style="font-size:.95rem;font-weight:700;margin-bottom:14px;color:var(--text-primary);">Webhook Settings</h3>
    <form id="settingsForm">
        <?= Security::csrfField() ?>
        <div style="margin-bottom:14px;">
            <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;">Webhook URL</label>
            <input type="url" name="webhook_url" value="<?= View::e($webhookUrl ?? '') ?>" placeholder="https://your-site.com/webhook"
                   style="width:100%;padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.9rem;">
            <small style="color:var(--text-secondary);font-size:.75rem;display:block;margin-top:4px;">Receive real-time notifications when messages arrive.</small>
        </div>
        <button type="submit" style="padding:10px 22px;background:var(--whatsapp-green);color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:.9rem;">
            <i class="fas fa-save" style="margin-right:6px;"></i>Save Settings
        </button>
    </form>
</div>

<script>
document.getElementById('settingsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const msg = document.getElementById('settingsMsg');
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    try {
        const resp = await fetch('/projects/whatsapp/settings/update', {
            method: 'POST',
            body: new FormData(e.target)
        });
        const data = await resp.json();
        msg.style.display = 'block';
        if (data.success) {
            msg.style.cssText = 'display:block;padding:10px 14px;background:rgba(0,255,136,.1);border:1px solid rgba(0,255,136,.4);border-radius:8px;color:var(--whatsapp-green);font-size:.9rem;margin-bottom:14px;';
            msg.innerHTML = '<i class="fas fa-check-circle" style="margin-right:6px;"></i>Settings saved successfully!';
        } else {
            msg.style.cssText = 'display:block;padding:10px 14px;background:rgba(255,100,100,.1);border:1px solid rgba(255,100,100,.4);border-radius:8px;color:#ff6464;font-size:.9rem;margin-bottom:14px;';
            msg.innerHTML = '<i class="fas fa-times-circle" style="margin-right:6px;"></i>' + (data.error || 'Failed to save.');
        }
    } catch(err) {
        msg.style.cssText = 'display:block;padding:10px 14px;background:rgba(255,100,100,.1);border:1px solid rgba(255,100,100,.4);border-radius:8px;color:#ff6464;font-size:.9rem;margin-bottom:14px;';
        msg.innerHTML = '<i class="fas fa-times-circle" style="margin-right:6px;"></i>Network error.';
    }
    btn.disabled = false;
});
</script>
<?php View::end(); ?>
