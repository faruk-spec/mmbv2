<?php
/**
 * Settings View
 */
?>

<div class="glass-card">
    <h3 class="section-title">
        <i class="fas fa-sliders-h"></i> QR Generator Settings
    </h3>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="/projects/qr/settings/update" style="max-width: 800px;">
        
        <!-- Default QR Code Settings -->
        <div class="settings-section">
            <h4 class="settings-heading">
                <i class="fas fa-qrcode"></i> Default QR Code Settings
            </h4>
            <p class="settings-description">
                These settings will be applied by default when generating new QR codes.
            </p>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Default Size (px)</label>
                    <input type="number" name="default_size" value="<?= $settings['default_size'] ?? 300 ?>" 
                           min="100" max="1000" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Error Correction</label>
                    <select name="default_error_correction" class="form-select">
                        <option value="L" <?= ($settings['default_error_correction'] ?? '') == 'L' ? 'selected' : '' ?>>Low (7%)</option>
                        <option value="M" <?= ($settings['default_error_correction'] ?? '') == 'M' ? 'selected' : '' ?>>Medium (15%)</option>
                        <option value="Q" <?= ($settings['default_error_correction'] ?? '') == 'Q' ? 'selected' : '' ?>>Quartile (25%)</option>
                        <option value="H" <?= ($settings['default_error_correction'] ?? 'H') == 'H' ? 'selected' : '' ?>>High (30%)</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Default Foreground Color</label>
                    <input type="color" name="default_foreground_color" 
                           value="<?= $settings['default_foreground_color'] ?? '#000000' ?>" 
                           class="form-control color-input">
                </div>
                
                <div class="form-group">
                    <label>Default Background Color</label>
                    <input type="color" name="default_background_color" 
                           value="<?= $settings['default_background_color'] ?? '#ffffff' ?>" 
                           class="form-control color-input">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Default Frame Style</label>
                    <select name="default_frame_style" class="form-select">
                        <option value="none" <?= ($settings['default_frame_style'] ?? 'none') == 'none' ? 'selected' : '' ?>>None</option>
                        <option value="square" <?= ($settings['default_frame_style'] ?? '') == 'square' ? 'selected' : '' ?>>Square</option>
                        <option value="rounded" <?= ($settings['default_frame_style'] ?? '') == 'rounded' ? 'selected' : '' ?>>Rounded</option>
                        <option value="banner" <?= ($settings['default_frame_style'] ?? '') == 'banner' ? 'selected' : '' ?>>Banner</option>
                        <option value="bubble" <?= ($settings['default_frame_style'] ?? '') == 'bubble' ? 'selected' : '' ?>>Bubble</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Default Download Format</label>
                    <select name="default_download_format" class="form-select">
                        <option value="png" <?= ($settings['default_download_format'] ?? 'png') == 'png' ? 'selected' : '' ?>>PNG</option>
                        <option value="svg" <?= ($settings['default_download_format'] ?? '') == 'svg' ? 'selected' : '' ?>>SVG</option>
                        <option value="pdf" <?= ($settings['default_download_format'] ?? '') == 'pdf' ? 'selected' : '' ?>>PDF</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- General Preferences -->
        <div class="settings-section">
            <h4 class="settings-heading">
                <i class="fas fa-cog"></i> General Preferences
            </h4>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="auto_save" <?= ($settings['auto_save'] ?? 1) ? 'checked' : '' ?>>
                    <span>Auto-save generated QR codes to history</span>
                </label>
                <small class="form-help">Automatically save all generated QR codes to your account.</small>
            </div>
        </div>
        
        <!-- Notification Settings -->
        <div class="settings-section">
            <h4 class="settings-heading">
                <i class="fas fa-bell"></i> Notification Settings
            </h4>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="email_notifications" id="emailNotifications" 
                           <?= ($settings['email_notifications'] ?? 0) ? 'checked' : '' ?>>
                    <span>Email me when QR codes are scanned</span>
                </label>
            </div>
            
            <div class="form-group" id="thresholdGroup" style="<?= ($settings['email_notifications'] ?? 0) ? '' : 'display: none;' ?>">
                <label>Notification Threshold (scans)</label>
                <input type="number" name="scan_notification_threshold" 
                       value="<?= $settings['scan_notification_threshold'] ?? 10 ?>" 
                       min="1" max="1000" class="form-control">
                <small class="form-help">Send notification after this many scans.</small>
            </div>
        </div>
        
        <!-- API Settings -->
        <div class="settings-section">
            <h4 class="settings-heading">
                <i class="fas fa-key"></i> API Settings
            </h4>
            <p class="settings-description">
                Generate an API key to access QR code generation programmatically.
            </p>
            
            <?php if (!empty($settings['api_key']) && $settings['api_enabled']): ?>
                <div class="api-key-display">
                    <div class="form-group">
                        <label>Your API Key</label>
                        <div class="api-key-input">
                            <input type="text" id="apiKeyDisplay" value="<?= $settings['api_key'] ?>" 
                                   readonly class="form-control" style="flex: 1;">
                            <button type="button" class="btn-secondary" onclick="copyApiKey()">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                        <small class="form-help" style="color: #ff9f40;">
                            <i class="fas fa-exclamation-triangle"></i> Keep this key secret! Do not share it publicly.
                        </small>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="button" class="btn-secondary" onclick="regenerateApiKey()">
                            <i class="fas fa-sync"></i> Regenerate Key
                        </button>
                        <button type="button" class="btn-danger" onclick="disableApi()">
                            <i class="fas fa-times"></i> Disable API
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <button type="button" class="btn-primary" onclick="generateApiKey()">
                    <i class="fas fa-plus"></i> Generate API Key
                </button>
            <?php endif; ?>
        </div>
        
        <div class="form-actions" style="margin-top: 30px; padding-top: 30px; border-top: 1px solid rgba(255,255,255,0.1);">
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Save Settings
            </button>
            <button type="reset" class="btn-secondary">
                <i class="fas fa-undo"></i> Reset
            </button>
        </div>
    </form>
</div>

<style>
.settings-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.settings-section:last-child {
    border-bottom: none;
}

.settings-heading {
    color: var(--text-primary);
    font-size: 18px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.settings-description {
    color: var(--text-secondary);
    margin-bottom: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    color: var(--text-primary);
    font-weight: 500;
    font-size: 14px;
}

.form-help {
    color: var(--text-secondary);
    font-size: 12px;
    margin-top: 4px;
}

.color-input {
    height: 50px;
    cursor: pointer;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    color: var(--text-primary);
    font-weight: 400;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.api-key-display {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.api-key-input {
    display: flex;
    gap: 10px;
}

.form-actions {
    display: flex;
    gap: 15px;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: rgba(46, 213, 115, 0.1);
    color: #2ed573;
    border: 1px solid rgba(46, 213, 115, 0.3);
}

.alert-error {
    background: rgba(255, 71, 87, 0.1);
    color: #ff4757;
    border: 1px solid rgba(255, 71, 87, 0.3);
}

/* Responsive Styles */
@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
    }
    
    .form-row .form-group {
        width: 100%;
    }
    
    .settings-section {
        padding: 20px;
    }
    
    form {
        max-width: 100% !important;
    }
}

@media (max-width: 480px) {
    .glass-card {
        padding: 20px;
    }
    
    .settings-heading {
        font-size: 18px;
    }
    
    .btn-primary {
        width: 100%;
    }
}
</style>

<script>
// Toggle threshold input based on email notifications
document.getElementById('emailNotifications')?.addEventListener('change', function() {
    document.getElementById('thresholdGroup').style.display = this.checked ? 'block' : 'none';
});

function copyApiKey() {
    const apiKeyInput = document.getElementById('apiKeyDisplay');
    apiKeyInput.select();
    document.execCommand('copy');
    alert('API key copied to clipboard!');
}

async function generateApiKey() {
    if (!confirm('Generate a new API key? This will allow programmatic access to your QR generator.')) {
        return;
    }
    
    try {
        const response = await fetch('/projects/qr/settings/generate-api-key', {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('API key generated successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to generate API key');
        }
    } catch (error) {
        alert('Error generating API key');
        console.error(error);
    }
}

async function regenerateApiKey() {
    if (!confirm('Regenerate API key? Your old key will stop working immediately!')) {
        return;
    }
    
    await generateApiKey();
}

async function disableApi() {
    if (!confirm('Disable API access? Your API key will stop working.')) {
        return;
    }
    
    try {
        const response = await fetch('/projects/qr/settings/disable-api', {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('API access disabled');
            location.reload();
        } else {
            alert(data.message || 'Failed to disable API');
        }
    } catch (error) {
        alert('Error disabling API');
        console.error(error);
    }
}
</script>
