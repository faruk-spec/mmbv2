<?php
/**
 * CodeXPro – Settings View (ConvertX-style)
 */
use Core\View;
$currentPage = 'settings';
$title       = 'Settings';

ob_start();
?>

<div class="page-header">
    <h1>Editor Settings</h1>
    <p>Customise CodeXPro to match your workflow</p>
</div>

<form id="settingsForm" onsubmit="saveSettings(event)" style="max-width:720px;">
    <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">

    <!-- Appearance -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-palette" style="color:var(--cx-primary);"></i> Appearance
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.25rem;">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label"><i class="fas fa-moon" style="color:var(--cx-primary);margin-right:.3rem;"></i> Editor Theme</label>
                <select name="theme" id="theme" class="form-control">
                    <option value="dark"    <?= ($settings['theme'] ?? 'dark') === 'dark'    ? 'selected' : '' ?>>Dark</option>
                    <option value="light"   <?= ($settings['theme'] ?? 'dark') === 'light'   ? 'selected' : '' ?>>Light</option>
                    <option value="monokai" <?= ($settings['theme'] ?? 'dark') === 'monokai' ? 'selected' : '' ?>>Monokai</option>
                    <option value="dracula" <?= ($settings['theme'] ?? 'dark') === 'dracula' ? 'selected' : '' ?>>Dracula</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label"><i class="fas fa-text-height" style="color:var(--cx-primary);margin-right:.3rem;"></i> Font Size (px)</label>
                <input type="number" name="font_size" id="font_size"
                       value="<?= (int)($settings['font_size'] ?? 14) ?>"
                       min="10" max="24" class="form-control">
                <small style="font-size:.73rem;color:var(--text-secondary);margin-top:.25rem;display:block;">Range: 10–24 px</small>
            </div>
        </div>
    </div>

    <!-- Editor Behaviour -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-keyboard" style="color:var(--cx-primary);"></i> Editor Behaviour
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.25rem;margin-bottom:1.5rem;">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label"><i class="fas fa-indent" style="color:var(--cx-primary);margin-right:.3rem;"></i> Tab Size</label>
                <select name="tab_size" id="tab_size" class="form-control">
                    <option value="2" <?= ($settings['tab_size'] ?? 2) == 2 ? 'selected' : '' ?>>2 spaces</option>
                    <option value="4" <?= ($settings['tab_size'] ?? 2) == 4 ? 'selected' : '' ?>>4 spaces</option>
                    <option value="8" <?= ($settings['tab_size'] ?? 2) == 8 ? 'selected' : '' ?>>8 spaces</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label"><i class="fas fa-keyboard" style="color:var(--cx-primary);margin-right:.3rem;"></i> Key Bindings</label>
                <select name="key_bindings" id="key_bindings" class="form-control">
                    <option value="default" <?= ($settings['key_bindings'] ?? 'default') === 'default' ? 'selected' : '' ?>>Default</option>
                    <option value="vim"     <?= ($settings['key_bindings'] ?? 'default') === 'vim'     ? 'selected' : '' ?>>Vim</option>
                    <option value="emacs"   <?= ($settings['key_bindings'] ?? 'default') === 'emacs'   ? 'selected' : '' ?>>Emacs</option>
                </select>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:.875rem;">
            <label class="toggle-label">
                <input type="checkbox" name="auto_save" id="auto_save" <?= !empty($settings['auto_save']) ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
                <span class="toggle-text">
                    <span style="font-weight:600;color:var(--text-primary);font-size:.875rem;">Auto-Save</span>
                    <small style="display:block;color:var(--text-secondary);font-size:.73rem;">Save changes automatically every 3 seconds</small>
                </span>
            </label>
            <label class="toggle-label">
                <input type="checkbox" name="auto_preview" id="auto_preview" <?= !empty($settings['auto_preview']) ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
                <span class="toggle-text">
                    <span style="font-weight:600;color:var(--text-primary);font-size:.875rem;">Live Preview</span>
                    <small style="display:block;color:var(--text-secondary);font-size:.73rem;">Update preview automatically as you type</small>
                </span>
            </label>
        </div>
    </div>

    <!-- Actions -->
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;padding-bottom:2rem;">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-floppy-disk"></i> Save Settings
        </button>
        <button type="button" onclick="resetSettings()" class="btn btn-secondary">
            <i class="fas fa-rotate-left"></i> Reset to Defaults
        </button>
    </div>
</form>

<script>
function saveSettings(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
    fetch('/projects/codexpro/settings', { method: 'POST', body: new FormData(e.target) })
        .then(r => {
            if (!r.ok) throw new Error('Network error');
            return r.json();
        })
        .then(data => {
            if (data.success) {
                showNotification('Settings saved successfully!', 'success');
            } else {
                showNotification('Error: ' + (data.error || 'Unknown'), 'error');
            }
        })
        .catch(err => showNotification('Error saving settings. Try again.', 'error'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-floppy-disk"></i> Save Settings';
        });
}

function resetSettings() {
    if (!confirm('Reset all settings to defaults?')) return;
    document.getElementById('theme').value        = 'dark';
    document.getElementById('font_size').value    = '14';
    document.getElementById('tab_size').value     = '2';
    document.getElementById('key_bindings').value = 'default';
    document.getElementById('auto_save').checked  = true;
    document.getElementById('auto_preview').checked = true;
    showNotification('Settings reset. Hit Save to apply.', 'success');
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
