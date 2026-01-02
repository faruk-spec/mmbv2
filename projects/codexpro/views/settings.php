<?php
/**
 * CodeXPro - Settings View
 */
use Core\View;
use Core\Auth;

$user = Auth::user();
$pageTitle = 'Settings';
$currentPage = 'settings';

ob_start();
?>

<div class="page-header">
    <h1><i class="fas fa-cog"></i> Editor Settings</h1>
</div>

<div class="settings-container">
    <form id="settingsForm" onsubmit="saveSettings(event)">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <div class="settings-section">
            <h2>Appearance</h2>
            
            <div class="form-group">
                <label for="theme">Editor Theme</label>
                <select name="theme" id="theme" class="form-control">
                    <option value="dark" <?= ($settings['theme'] ?? 'dark') === 'dark' ? 'selected' : '' ?>>Dark</option>
                    <option value="light" <?= ($settings['theme'] ?? 'dark') === 'light' ? 'selected' : '' ?>>Light</option>
                    <option value="monokai" <?= ($settings['theme'] ?? 'dark') === 'monokai' ? 'selected' : '' ?>>Monokai</option>
                    <option value="dracula" <?= ($settings['theme'] ?? 'dark') === 'dracula' ? 'selected' : '' ?>>Dracula</option>
                </select>
            </div>

            <div class="form-group">
                <label for="font_size">Font Size</label>
                <input type="number" name="font_size" id="font_size" 
                       value="<?= htmlspecialchars($settings['font_size'] ?? 14) ?>" 
                       min="10" max="24" class="form-control">
                <small>Size in pixels (10-24)</small>
            </div>
        </div>

        <div class="settings-section">
            <h2>Editor Behavior</h2>
            
            <div class="form-group">
                <label for="tab_size">Tab Size</label>
                <select name="tab_size" id="tab_size" class="form-control">
                    <option value="2" <?= ($settings['tab_size'] ?? 2) == 2 ? 'selected' : '' ?>>2 spaces</option>
                    <option value="4" <?= ($settings['tab_size'] ?? 2) == 4 ? 'selected' : '' ?>>4 spaces</option>
                    <option value="8" <?= ($settings['tab_size'] ?? 2) == 8 ? 'selected' : '' ?>>8 spaces</option>
                </select>
            </div>

            <div class="form-group">
                <label for="key_bindings">Key Bindings</label>
                <select name="key_bindings" id="key_bindings" class="form-control">
                    <option value="default" <?= ($settings['key_bindings'] ?? 'default') === 'default' ? 'selected' : '' ?>>Default</option>
                    <option value="vim" <?= ($settings['key_bindings'] ?? 'default') === 'vim' ? 'selected' : '' ?>>Vim</option>
                    <option value="emacs" <?= ($settings['key_bindings'] ?? 'default') === 'emacs' ? 'selected' : '' ?>>Emacs</option>
                </select>
            </div>

            <div class="form-group checkbox-group">
                <label>
                    <input type="checkbox" name="auto_save" id="auto_save" 
                           <?= !empty($settings['auto_save']) ? 'checked' : '' ?>>
                    Enable Auto-Save
                </label>
                <small>Automatically save changes every 3 seconds</small>
            </div>

            <div class="form-group checkbox-group">
                <label>
                    <input type="checkbox" name="auto_preview" id="auto_preview" 
                           <?= !empty($settings['auto_preview']) ? 'checked' : '' ?>>
                    Enable Live Preview
                </label>
                <small>Update preview automatically as you type</small>
            </div>
        </div>

        <div class="settings-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Settings
            </button>
            <button type="button" onclick="resetSettings()" class="btn btn-secondary">
                <i class="fas fa-undo"></i> Reset to Defaults
            </button>
        </div>
    </form>
</div>

<div id="saveMessage" class="alert alert-success" style="display: none;">
    Settings saved successfully!
</div>

<script>
function saveSettings(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const submitBtn = e.target.querySelector('button[type="submit"]');
    
    // Disable button and show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    fetch('/projects/codexpro/settings', {
        method: 'POST',
        body: formData
    })
    .then(r => {
        if (!r.ok) {
            throw new Error('Network response was not ok');
        }
        return r.json();
    })
    .then(data => {
        if (data.success) {
            // Use showNotification from layout if available, otherwise use alert
            if (typeof showNotification === 'function') {
                showNotification('Settings saved successfully!', 'success');
            } else {
                const msg = document.getElementById('saveMessage');
                msg.textContent = 'Settings saved successfully!';
                msg.style.display = 'block';
                setTimeout(() => {
                    msg.style.display = 'none';
                }, 3000);
            }
        } else {
            if (typeof showNotification === 'function') {
                showNotification('Error saving settings: ' + (data.error || 'Unknown error'), 'error');
            } else {
                alert('Error saving settings: ' + (data.error || 'Unknown error'));
            }
        }
    })
    .catch(err => {
        console.error('Save settings error:', err);
        if (typeof showNotification === 'function') {
            showNotification('Error saving settings. Please try again.', 'error');
        } else {
            alert('Error saving settings. Please try again.');
        }
    })
    .finally(() => {
        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Settings';
    });
}

function resetSettings() {
    if (confirm('Reset all settings to defaults?')) {
        document.getElementById('theme').value = 'dark';
        document.getElementById('font_size').value = '14';
        document.getElementById('tab_size').value = '2';
        document.getElementById('key_bindings').value = 'default';
        document.getElementById('auto_save').checked = true;
        document.getElementById('auto_preview').checked = true;
    }
}
</script>

<style>
.settings-container {
    max-width: 800px;
    margin: 0 auto;
}

.settings-section {
    background: rgba(26, 26, 46, 0.6);
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid rgba(6, 182, 212, 0.2);
}

.settings-section h2 {
    color: #06b6d4;
    font-size: 1.3rem;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid rgba(6, 182, 212, 0.3);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    color: #e2e8f0;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.form-group small {
    display: block;
    color: #94a3b8;
    margin-top: 0.25rem;
    font-size: 0.875rem;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    background: rgba(15, 15, 35, 0.8);
    border: 1px solid rgba(6, 182, 212, 0.3);
    border-radius: 8px;
    color: #fff;
    font-size: 1rem;
    transition: all 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #06b6d4;
    box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
}

.checkbox-group label {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.checkbox-group input[type="checkbox"] {
    margin-right: 0.75rem;
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.settings-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.settings-actions .btn {
    min-width: 180px;
}

.alert {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    background: rgba(16, 185, 129, 0.9);
    color: #fff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    z-index: 9999;
    animation: slideIn 0.3s;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Mobile responsive styles */
@media (max-width: 768px) {
    .settings-container {
        padding: 0 15px;
    }
    
    .settings-section {
        padding: 1.5rem;
    }
    
    .settings-section h2 {
        font-size: 1.1rem;
    }
    
    .settings-actions {
        flex-direction: column;
    }
    
    .settings-actions .btn {
        width: 100%;
        min-width: unset;
        justify-content: center;
    }
    
    .alert {
        right: 10px;
        left: 10px;
        width: auto;
    }
}

@media (max-width: 480px) {
    .settings-section {
        padding: 1rem;
    }
    
    .form-control {
        font-size: 0.9rem;
        padding: 0.65rem;
    }
    
    .settings-actions .btn {
        padding: 12px 16px;
        font-size: 0.95rem;
    }
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
