<?php
/**
 * Settings View
 */
?>

<div class="glass-card">
    <h3 class="section-title" style="margin-bottom: var(--space-xl);">
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
    
    <!-- Settings Tabs -->
    <div class="settings-tabs" style="margin-bottom: var(--space-xl);">
        <button class="settings-tab active" onclick="switchTab('defaults')" id="tab-defaults">
            <i class="fas fa-qrcode"></i> Defaults
        </button>
        <button class="settings-tab" onclick="switchTab('design')" id="tab-design">
            <i class="fas fa-paint-brush"></i> Design
        </button>
        <button class="settings-tab" onclick="switchTab('logo')" id="tab-logo">
            <i class="fas fa-image"></i> Logo
        </button>
        <button class="settings-tab" onclick="switchTab('advanced')" id="tab-advanced">
            <i class="fas fa-sliders-h"></i> Advanced
        </button>
        <button class="settings-tab" onclick="switchTab('preferences')" id="tab-preferences">
            <i class="fas fa-cog"></i> Preferences
        </button>
        <button class="settings-tab" onclick="switchTab('notifications')" id="tab-notifications">
            <i class="fas fa-bell"></i> Notifications
        </button>
    </div>
    
    <form method="POST" action="/projects/qr/settings" style="max-width: 50rem;">
        
        <!-- Tab Content: Defaults -->
        <div class="tab-content active" id="content-defaults">
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
        </div><!-- End Defaults Tab -->
        
        <!-- Tab Content: Design Defaults -->
        <div class="tab-content" id="content-design">
        <div class="settings-section">
            <h4 class="settings-heading">
                <i class="fas fa-paint-brush"></i> Design Defaults
            </h4>
            <p class="settings-description">
                Set default design styles for QR code generation. These will be applied automatically.
            </p>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Corner Style</label>
                    <select name="default_corner_style" class="form-select">
                        <option value="square" <?= ($settings['default_corner_style'] ?? 'square') == 'square' ? 'selected' : '' ?>>Square</option>
                        <option value="extra-rounded" <?= ($settings['default_corner_style'] ?? '') == 'extra-rounded' ? 'selected' : '' ?>>Extra Rounded</option>
                        <option value="dot" <?= ($settings['default_corner_style'] ?? '') == 'dot' ? 'selected' : '' ?>>Dot</option>
                    </select>
                    <small class="form-help">Style for the outer corner elements of the QR code</small>
                </div>
                
                <div class="form-group">
                    <label>Dot Style</label>
                    <select name="default_dot_style" class="form-select">
                        <option value="dots" <?= ($settings['default_dot_style'] ?? 'square') == 'dots' ? 'selected' : '' ?>>Dots</option>
                        <option value="rounded" <?= ($settings['default_dot_style'] ?? 'square') == 'rounded' ? 'selected' : '' ?>>Rounded</option>
                        <option value="square" <?= ($settings['default_dot_style'] ?? 'square') == 'square' ? 'selected' : '' ?>>Square</option>
                        <option value="classy" <?= ($settings['default_dot_style'] ?? '') == 'classy' ? 'selected' : '' ?>>Classy</option>
                        <option value="classy-rounded" <?= ($settings['default_dot_style'] ?? '') == 'classy-rounded' ? 'selected' : '' ?>>Classy Rounded</option>
                    </select>
                    <small class="form-help">Pattern style for the QR code dots</small>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Marker Border Style</label>
                    <select name="default_marker_border_style" class="form-select">
                        <option value="square" <?= ($settings['default_marker_border_style'] ?? 'square') == 'square' ? 'selected' : '' ?>>Square</option>
                        <option value="extra-rounded" <?= ($settings['default_marker_border_style'] ?? '') == 'extra-rounded' ? 'selected' : '' ?>>Extra Rounded</option>
                        <option value="dot" <?= ($settings['default_marker_border_style'] ?? '') == 'dot' ? 'selected' : '' ?>>Dot</option>
                    </select>
                    <small class="form-help">Style for the border of corner markers</small>
                </div>
                
                <div class="form-group">
                    <label>Marker Center Style</label>
                    <select name="default_marker_center_style" class="form-select">
                        <option value="square" <?= ($settings['default_marker_center_style'] ?? 'square') == 'square' ? 'selected' : '' ?>>Square</option>
                        <option value="extra-rounded" <?= ($settings['default_marker_center_style'] ?? '') == 'extra-rounded' ? 'selected' : '' ?>>Extra Rounded</option>
                        <option value="dot" <?= ($settings['default_marker_center_style'] ?? '') == 'dot' ? 'selected' : '' ?>>Dot</option>
                    </select>
                    <small class="form-help">Style for the center of corner markers</small>
                </div>
            </div>
        </div>
        </div><!-- End Design Tab -->
        
        <!-- Tab Content: Logo Defaults -->
        <div class="tab-content" id="content-logo">
        <div class="settings-section">
            <h4 class="settings-heading">
                <i class="fas fa-image"></i> Logo Defaults
            </h4>
            <p class="settings-description">
                Configure default settings for logos in QR codes.
            </p>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Default Logo Color</label>
                    <input type="color" name="default_logo_color" 
                           value="<?= $settings['default_logo_color'] ?? '#9945ff' ?>" 
                           class="form-control color-input">
                    <small class="form-help">Default color for logo elements</small>
                </div>
                
                <div class="form-group">
                    <label>Default Logo Size</label>
                    <input type="range" name="default_logo_size" 
                           value="<?= $settings['default_logo_size'] ?? 0.30 ?>" 
                           min="0.1" max="0.5" step="0.01" 
                           class="form-control" 
                           oninput="this.nextElementSibling.textContent = this.value">
                    <small class="form-help">Size: <span><?= $settings['default_logo_size'] ?? 0.30 ?></span> (0.1 = 10%, 0.5 = 50%)</small>
                </div>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="default_logo_remove_bg" 
                           <?= ($settings['default_logo_remove_bg'] ?? 0) ? 'checked' : '' ?>>
                    <span>Remove background behind logo by default</span>
                </label>
                <small class="form-help">Clear the background behind the logo for better visibility</small>
            </div>
        </div>
        </div><!-- End Logo Tab -->
        
        <!-- Tab Content: Advanced Defaults -->
        <div class="tab-content" id="content-advanced">
        <div class="settings-section">
            <h4 class="settings-heading">
                <i class="fas fa-sliders-h"></i> Advanced Defaults
            </h4>
            <p class="settings-description">
                Advanced QR code generation settings for power users.
            </p>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="default_gradient_enabled" id="gradientEnabled"
                           <?= ($settings['default_gradient_enabled'] ?? 0) ? 'checked' : '' ?>>
                    <span>Enable gradient by default</span>
                </label>
                <small class="form-help">Apply a gradient effect to the QR code foreground</small>
            </div>
            
            <div class="form-group" id="gradientColorGroup" style="<?= ($settings['default_gradient_enabled'] ?? 0) ? '' : 'display: none;' ?>">
                <label>Default Gradient Color</label>
                <input type="color" name="default_gradient_color" 
                       value="<?= $settings['default_gradient_color'] ?? '#9945ff' ?>" 
                       class="form-control color-input">
                <small class="form-help">End color for the gradient effect</small>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="default_transparent_bg" 
                           <?= ($settings['default_transparent_bg'] ?? 0) ? 'checked' : '' ?>>
                    <span>Transparent background by default</span>
                </label>
                <small class="form-help">Generate QR codes with transparent backgrounds</small>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="default_custom_marker_color" id="customMarkerColor"
                           <?= ($settings['default_custom_marker_color'] ?? 0) ? 'checked' : '' ?>>
                    <span>Enable custom marker color by default</span>
                </label>
                <small class="form-help">Use a different color for corner markers</small>
            </div>
            
            <div class="form-group" id="markerColorGroup" style="<?= ($settings['default_custom_marker_color'] ?? 0) ? '' : 'display: none;' ?>">
                <label>Default Marker Color</label>
                <input type="color" name="default_marker_color" 
                       value="<?= $settings['default_marker_color'] ?? '#9945ff' ?>" 
                       class="form-control color-input">
                <small class="form-help">Color for the corner markers when custom color is enabled</small>
            </div>
        </div>
        </div><!-- End Advanced Tab -->
        
        <!-- Tab Content: Preferences -->
        <div class="tab-content" id="content-preferences">
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
        </div><!-- End Preferences Tab -->
        
        <!-- Tab Content: Notifications -->
        <div class="tab-content" id="content-notifications">
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
        </div><!-- End Notifications Tab -->
        
        <div class="form-actions" style="margin-top: var(--space-xl); padding-top: var(--space-xl); border-top: 1px solid rgba(255,255,255,0.1);">
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Save Settings
            </button>
            <button type="reset" class="btn-secondary">
                <i class="fas fa-undo"></i> Reset
            </button>
        </div>
    </form>
</div>

<script>
// Tab switching
function switchTab(tabName) {
    // Hide all tab content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active from all tabs
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.add('active');
    document.getElementById('tab-' + tabName).classList.add('active');
}
</script>

<style>
/* Settings Tabs */
.settings-tabs {
    display: flex;
    gap: var(--space-sm);
    border-bottom: 2px solid var(--border-color);
    flex-wrap: wrap;
}

.settings-tab {
    padding: var(--space-md) var(--space-lg);
    background: none;
    border: none;
    color: var(--text-secondary);
    font-size: var(--font-sm);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    display: flex;
    align-items: center;
    gap: var(--space-sm);
}

.settings-tab:hover {
    color: var(--text-primary);
    background: rgba(255, 255, 255, 0.05);
}

.settings-tab.active {
    color: var(--purple);
    border-bottom-color: var(--purple);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(0.5rem); }
    to { opacity: 1; transform: translateY(0); }
}

.settings-section {
    margin-bottom: 2.5rem; /* 40px to rem */
    padding-bottom: var(--space-xl);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.settings-section:last-child {
    border-bottom: none;
}

.settings-heading {
    color: var(--text-primary);
    font-size: var(--font-lg);
    margin-bottom: var(--space-sm);
    display: flex;
    align-items: center;
    gap: var(--space-sm);
}

.settings-description {
    color: var(--text-secondary);
    margin-bottom: var(--space-lg);
    font-size: var(--font-sm);
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(15.625rem, 1fr)); /* 250px to rem */
    gap: var(--space-lg);
    margin-bottom: var(--space-md);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
}

.form-group label {
    color: var(--text-primary);
    font-weight: 500;
    font-size: var(--font-sm);
}

.form-help {
    color: var(--text-secondary);
    font-size: var(--font-xs);
    margin-top: 0.25rem; /* 4px to rem */
}

.color-input {
    height: 3.125rem; /* 50px to rem */
    cursor: pointer;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    cursor: pointer;
    color: var(--text-primary);
    font-weight: 400;
}

.checkbox-label input[type="checkbox"] {
    width: 1.125rem; /* 18px to rem */
    height: 1.125rem;
    cursor: pointer;
}

.api-key-display {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
}

.api-key-input {
    display: flex;
    gap: var(--space-sm);
    align-items: center;
}

.api-key-input .btn-secondary {
    white-space: nowrap;
}

.form-actions {
    display: flex;
    gap: var(--space-md);
}

/* Responsive Styles */
@media (max-width: 48rem) { /* 768px to rem */
    .settings-tabs {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .settings-tab {
        padding: var(--space-sm) var(--space-md);
        font-size: 0.8125rem; /* 13px to rem */
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .api-key-input {
        flex-direction: column;
    }
    
    .api-key-input .form-control,
    .api-key-input .btn-secondary {
        width: 100%;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn-primary,
    .form-actions .btn-secondary {
        width: 100%;
    }
}
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

// Toggle gradient color input based on gradient enabled
document.getElementById('gradientEnabled')?.addEventListener('change', function() {
    document.getElementById('gradientColorGroup').style.display = this.checked ? 'block' : 'none';
});

// Toggle marker color input based on custom marker color
document.getElementById('customMarkerColor')?.addEventListener('change', function() {
    document.getElementById('markerColorGroup').style.display = this.checked ? 'block' : 'none';
});

// ── Toast helper ───────────────────────────────────────────────────────────
function showSettingsToast(msg, type) {
    const t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:99999;padding:10px 18px;border-radius:8px;font-size:.85rem;font-weight:600;pointer-events:none;transition:opacity .3s;'
        + (type === 'success'
            ? 'background:rgba(0,255,136,.15);border:1px solid #00ff88;color:#00ff88;'
            : 'background:rgba(255,107,107,.15);border:1px solid #ff6b6b;color:#ff6b6b;');
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 3200);
}


</script>
