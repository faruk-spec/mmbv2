<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="max-width: 600px; margin: 40px auto;">
    <div class="card">
        <div style="text-align: center; padding: 20px;">
            <div style="width: 80px; height: 80px; background: rgba(0, 255, 136, 0.1); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="M9 12l2 2 4-4"/>
                </svg>
            </div>
            <h1 style="color: var(--green); margin-bottom: 15px;">2FA Enabled!</h1>
            <p style="color: var(--text-secondary); margin-bottom: 30px;">
                Save these backup codes in a safe place. You can use them to access your account if you lose your authenticator device.
            </p>
        </div>
        
        <div style="background: var(--bg-secondary); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                <h3 style="font-size: 1rem; margin: 0;">Backup Codes</h3>
                <button onclick="copyBackupCodes()" class="btn btn-sm" style="padding: 6px 12px; font-size: 0.875rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                    Copy All
                </button>
            </div>
            
            <div class="alert alert-warning" style="margin-bottom: 15px; font-size: 0.9rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                Each code can only be used once. This is the only time you'll see these codes!
            </div>
            
            <div id="backup-codes" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; font-family: monospace; font-size: 1rem;">
                <?php foreach ($backupCodes as $code): ?>
                    <div style="background: var(--bg-primary); padding: 12px; border-radius: 8px; text-align: center; border: 1px solid var(--border-color);">
                        <?= View::e($code) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div style="text-align: center;">
            <a href="/security" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Continue to Security Settings
            </a>
        </div>
    </div>
</div>

<script>
function copyBackupCodes() {
    const codes = <?= json_encode($backupCodes) ?>;
    const text = codes.join('\n');
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Backup codes copied to clipboard!');
        }).catch(() => {
            fallbackCopy(text);
        });
    } else {
        fallbackCopy(text);
    }
}

function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    try {
        document.execCommand('copy');
        alert('Backup codes copied to clipboard!');
    } catch (err) {
        alert('Failed to copy. Please copy manually.');
    }
    document.body.removeChild(textarea);
}
</script>
<?php View::endSection(); ?>
