<?php use Core\View; use Core\Security; ?>
<?php View::extend('proshare:app'); ?>

<?php View::section('content'); ?>

<?php
// Admin global settings with defaults
$adminMaxFileSize    = (int)($globalSettings['max_file_size'] ?? 524288000);     // bytes
$adminMaxFileSizeMb  = (int)round($adminMaxFileSize / 1048576);                  // MB
$adminDefaultExpiry  = (int)($globalSettings['default_expiry_hours'] ?? 24);
$showEmail           = (int)($globalSettings['enable_email_notifications'] ?? 1);
$showSms             = (int)($globalSettings['enable_sms_notifications'] ?? 1);
$adminAutoDelete     = (int)($globalSettings['default_auto_delete'] ?? 0);
$userCanChangeAD     = (int)($globalSettings['user_can_change_auto_delete'] ?? 1);

// Determine effective auto_delete (admin-forced if user cannot change)
$effectiveAutoDelete = $userCanChangeAD ? (int)($settings['auto_delete'] ?? $adminAutoDelete) : $adminAutoDelete;

// Available file size options: use admin-configured list (only up to admin max)
$rawSizeOptions = $globalSettings['user_file_size_options'] ?? '50,100,200,500';
$sizeOptionsMb  = array_filter(
    array_map('intval', explode(',', $rawSizeOptions)),
    fn($v) => $v > 0 && $v <= $adminMaxFileSizeMb
);
sort($sizeOptionsMb);
// Ensure admin max is always available as an option
if (!in_array($adminMaxFileSizeMb, $sizeOptionsMb) && $adminMaxFileSizeMb > 0) {
    $sizeOptionsMb[] = $adminMaxFileSizeMb;
    sort($sizeOptionsMb);
}
$userMaxFileSizeMb = (int)round((int)($settings['max_file_size'] ?? $adminMaxFileSize) / 1048576);
?>

<style>
.settings-section { padding: 1.5rem; border-bottom: 1px solid var(--border-color); }
.settings-section:last-of-type { border-bottom: none; }
.settings-section h4 { margin: 0 0 1.25rem; color: var(--text-primary); display: flex; align-items: center; gap: 8px; font-size: 0.95rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
.settings-section h4 i { color: var(--ps-primary); }
.toggle-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.04); }
.toggle-row:last-child { border-bottom: none; }
.toggle-label { display: flex; flex-direction: column; gap: 2px; }
.toggle-label span { font-size: 0.875rem; color: var(--text-primary); font-weight: 500; }
.toggle-label small { font-size: 0.75rem; color: var(--text-secondary); }
.ps-toggle { position: relative; width: 42px; height: 22px; flex-shrink: 0; }
.ps-toggle input { opacity: 0; width: 0; height: 0; position: absolute; }
.ps-toggle-slider { position: absolute; inset: 0; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 22px; cursor: pointer; transition: background 0.2s; }
.ps-toggle-slider::before { content: ''; position: absolute; width: 16px; height: 16px; left: 2px; top: 2px; background: var(--text-secondary); border-radius: 50%; transition: transform 0.2s, background 0.2s; }
.ps-toggle input:checked + .ps-toggle-slider { background: rgba(0,240,255,0.15); border-color: var(--ps-primary); }
.ps-toggle input:checked + .ps-toggle-slider::before { transform: translateX(20px); background: var(--ps-primary); }
.ps-toggle input:disabled + .ps-toggle-slider { opacity: 0.45; cursor: not-allowed; }
.settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 600px) {
    .settings-grid { grid-template-columns: 1fr; }
    .settings-section { padding: 1rem; }
    .toggle-row { gap: 8px; }
}
</style>

<div id="saveToast" style="position:fixed; top:1.5rem; right:1.5rem; z-index:9999; background:var(--bg-card); border:1px solid var(--border-color); border-radius:10px; padding:12px 20px; display:none; align-items:center; gap:10px; box-shadow:0 4px 20px rgba(0,0,0,0.3); font-size:0.875rem;">
    <i id="toastIcon" class="fas fa-check-circle" style="color:var(--green);"></i>
    <span id="toastMsg">Settings saved successfully!</span>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-cog"></i> Account Settings</h3>
    </div>
    
    <form id="settingsForm">
        <input type="hidden" name="_csrf_token" value="<?= Security::generateCsrfToken() ?>">
        
        <!-- Notifications -->
        <?php if ($showEmail || $showSms): ?>
        <div class="settings-section">
            <h4><i class="fas fa-bell"></i> Notifications</h4>
            
            <?php if ($showEmail): ?>
            <div class="toggle-row">
                <div class="toggle-label">
                    <span>Email Notifications</span>
                    <small>Alerts for downloads and expiry warnings</small>
                </div>
                <label class="ps-toggle">
                    <input type="checkbox" name="email_notifications" value="1" <?= ($settings['email_notifications'] ?? 1) ? 'checked' : '' ?>>
                    <span class="ps-toggle-slider"></span>
                </label>
            </div>
            <?php endif; ?>
            
            <?php if ($showSms): ?>
            <div class="toggle-row">
                <div class="toggle-label">
                    <span>SMS Notifications</span>
                    <small>Text alerts if SMS is configured</small>
                </div>
                <label class="ps-toggle">
                    <input type="checkbox" name="sms_notifications" value="1" <?= ($settings['sms_notifications'] ?? 0) ? 'checked' : '' ?>>
                    <span class="ps-toggle-slider"></span>
                </label>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Default Upload Settings -->
        <div class="settings-section">
            <h4><i class="fas fa-cloud-upload-alt"></i> Default Upload Settings</h4>
            
            <div class="settings-grid" style="margin-bottom: 1rem;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-size:0.8rem;">Default Link Expiry</label>
                    <select name="default_expiry" class="form-control">
                        <?php
                        $userExpiry = (int)($settings['default_expiry'] ?? $adminDefaultExpiry);
                        $expiryOptions = [1 => '1 Hour', 6 => '6 Hours', 24 => '24 Hours', 168 => '7 Days', 720 => '30 Days'];
                        foreach ($expiryOptions as $val => $label):
                        ?>
                        <option value="<?= $val ?>" <?= $userExpiry == $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-size:0.8rem;">Maximum File Size</label>
                    <select name="max_file_size" class="form-control">
                        <?php foreach ($sizeOptionsMb as $mb):
                            $bytes = $mb * 1048576;
                            $label = $mb >= 1024 ? round($mb / 1024, 1) . ' GB' : $mb . ' MB';
                        ?>
                        <option value="<?= $bytes ?>" <?= $userMaxFileSizeMb == $mb ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="toggle-row">
                <div class="toggle-label">
                    <span>Enable Compression</span>
                    <small>Automatically compress files to save bandwidth</small>
                </div>
                <label class="ps-toggle">
                    <input type="checkbox" name="enable_compression" value="1" <?= ($settings['enable_compression'] ?? 1) ? 'checked' : '' ?>>
                    <span class="ps-toggle-slider"></span>
                </label>
            </div>
            
            <div class="toggle-row">
                <div class="toggle-label">
                    <span>Enable Encryption</span>
                    <small>Encrypt file content at rest (coming soon)</small>
                </div>
                <label class="ps-toggle">
                    <input type="checkbox" name="enable_encryption" value="1" <?= ($settings['enable_encryption'] ?? 0) ? 'checked' : '' ?> disabled>
                    <span class="ps-toggle-slider" style="opacity:0.5; cursor:not-allowed;"></span>
                </label>
            </div>
        </div>
        
        <!-- Privacy & Security -->
        <div class="settings-section">
            <h4><i class="fas fa-shield-alt"></i> Privacy &amp; Security</h4>
            
            <div class="toggle-row">
                <div class="toggle-label">
                    <span>Auto-Delete Expired Files</span>
                    <small>Automatically delete files past their expiry date</small>
                </div>
                <?php if ($userCanChangeAD): ?>
                <label class="ps-toggle">
                    <input type="checkbox" name="auto_delete" value="1" <?= $effectiveAutoDelete ? 'checked' : '' ?>>
                    <span class="ps-toggle-slider"></span>
                </label>
                <?php else: ?>
                <div style="display:flex; flex-direction:column; align-items:flex-end; gap:4px;">
                    <label class="ps-toggle">
                        <input type="checkbox" name="auto_delete" value="1" <?= $effectiveAutoDelete ? 'checked' : '' ?> disabled>
                        <span class="ps-toggle-slider"></span>
                    </label>
                    <small style="font-size:0.72rem; color:var(--ps-danger);">You can't change this feature</small>
                </div>
                <input type="hidden" name="auto_delete" value="<?= $effectiveAutoDelete ?>">
                <?php endif; ?>
            </div>
            
            <div class="alert alert-info" style="margin-top: 1rem; margin-bottom: 0;">
                <i class="fas fa-info-circle"></i>
                <strong>Note:</strong> All files are protected with industry-standard security including password hashing, integrity verification, and access logging.
            </div>
        </div>
        
        <!-- Save Button -->
        <div class="settings-section" style="border-bottom:none;">
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button type="submit" id="saveBtn" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Settings
                </button>
                <a href="/projects/proshare/dashboard" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Account Statistics -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-chart-bar"></i> Account Statistics</h3>
    </div>
    
    <div style="padding: 1.25rem;">
        <div class="ps-grid ps-grid-4" style="gap: 0.75rem;">
            <div class="stat-card">
                <div class="stat-value" style="color: var(--cyan); font-size: 1.6rem;"><?= number_format($stats['total_files'] ?? 0) ?></div>
                <div class="stat-label">Files</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--magenta); font-size: 1.6rem;"><?= number_format($stats['total_texts'] ?? 0) ?></div>
                <div class="stat-label">Text Shares</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--green); font-size: 1.6rem;"><?= number_format($stats['total_downloads'] ?? 0) ?></div>
                <div class="stat-label">Downloads</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--orange); font-size: 1.6rem;"><?= number_format($stats['storage_used'] ?? 0, 2) ?></div>
                <div class="stat-label">MB Used</div>
            </div>
        </div>
    </div>
</div>

<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<script>
document.getElementById('settingsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const saveBtn = document.getElementById('saveBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
    
    try {
        const response = await fetch('/projects/proshare/settings', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: new FormData(this)
        });
        
        const text = await response.text();
        let data;
        try { data = JSON.parse(text); } catch (_) {
            console.error('Server response:', text);
            showToast('Unexpected server response — check console.', 'error');
            return;
        }
        
        if (data.success) {
            showToast(data.message || 'Settings saved successfully!', 'success');
        } else {
            showToast('Error: ' + (data.error || 'Failed to save settings'), 'error');
        }
    } catch (err) {
        showToast('Network error — please try again.', 'error');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Settings';
    }
});

function showToast(msg, type = 'success') {
    const toast = document.getElementById('saveToast');
    const icon  = document.getElementById('toastIcon');
    const msgEl = document.getElementById('toastMsg');
    msgEl.textContent = msg;
    icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
    icon.style.color = type === 'success' ? 'var(--green)' : 'var(--ps-danger)';
    toast.style.display = 'flex';
    setTimeout(() => { toast.style.display = 'none'; }, 4000);
}
</script>
<?php View::endSection(); ?>

