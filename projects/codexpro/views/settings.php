<?php
/**
 * CodeXPro – Enhanced Settings View (ConvertX-style)
 */
use Core\View;
$currentPage = 'settings';
$title       = 'Settings';

ob_start();
?>

<div class="page-header">
    <h1>Editor Settings</h1>
    <p>Fine-tune every aspect of your CodeXPro environment</p>
</div>

<form id="settingsForm" onsubmit="saveSettings(event)" style="max-width:800px;">
    <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">

    <!-- ── Appearance ── -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-palette"></i> Appearance
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.25rem;">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label"><i class="fas fa-moon"></i> Editor Theme</label>
                <select name="theme" id="theme" class="form-control">
                    <option value="dark"    <?= ($settings['theme'] ?? 'dark') === 'dark'    ? 'selected' : '' ?>>Dark (Default)</option>
                    <option value="dracula" <?= ($settings['theme'] ?? 'dark') === 'dracula' ? 'selected' : '' ?>>Dracula</option>
                    <option value="monokai" <?= ($settings['theme'] ?? 'dark') === 'monokai' ? 'selected' : '' ?>>Monokai</option>
                    <option value="light"   <?= ($settings['theme'] ?? 'dark') === 'light'   ? 'selected' : '' ?>>Light</option>
                    <option value="nord"    <?= ($settings['theme'] ?? 'dark') === 'nord'    ? 'selected' : '' ?>>Nord</option>
                    <option value="material"<?= ($settings['theme'] ?? 'dark') === 'material' ? 'selected' : '' ?>>Material</option>
                    <option value="github"  <?= ($settings['theme'] ?? 'dark') === 'github'  ? 'selected' : '' ?>>GitHub</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label"><i class="fas fa-font"></i> Font Family</label>
                <select name="font_family" id="font_family" class="form-control">
                    <?php
                    $fonts = ['JetBrains Mono','Fira Code','Cascadia Code','Source Code Pro','Consolas','Courier New','monospace'];
                    $curFont = $settings['font_family'] ?? 'JetBrains Mono';
                    foreach ($fonts as $f): ?>
                    <option value="<?= htmlspecialchars($f) ?>" <?= $curFont === $f ? 'selected' : '' ?>><?= htmlspecialchars($f) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label"><i class="fas fa-text-height"></i> Font Size (px)</label>
                <input type="number" name="font_size" id="font_size"
                       value="<?= (int)($settings['font_size'] ?? 14) ?>"
                       min="10" max="28" class="form-control">
                <small style="font-size:.73rem;color:var(--text-secondary);margin-top:.25rem;display:block;">Range: 10–28 px</small>
            </div>
        </div>
    </div>

    <!-- ── Editor Behaviour ── -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-keyboard"></i> Editor Behaviour
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.25rem;margin-bottom:1.5rem;">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label"><i class="fas fa-indent"></i> Tab Size</label>
                <select name="tab_size" id="tab_size" class="form-control">
                    <option value="2" <?= ($settings['tab_size'] ?? 2) == 2 ? 'selected' : '' ?>>2 spaces</option>
                    <option value="4" <?= ($settings['tab_size'] ?? 2) == 4 ? 'selected' : '' ?>>4 spaces</option>
                    <option value="8" <?= ($settings['tab_size'] ?? 2) == 8 ? 'selected' : '' ?>>8 spaces</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label"><i class="fas fa-keyboard"></i> Key Bindings</label>
                <select name="key_bindings" id="key_bindings" class="form-control">
                    <option value="default" <?= ($settings['key_bindings'] ?? 'default') === 'default' ? 'selected' : '' ?>>Default</option>
                    <option value="vim"     <?= ($settings['key_bindings'] ?? 'default') === 'vim'     ? 'selected' : '' ?>>Vim</option>
                    <option value="emacs"   <?= ($settings['key_bindings'] ?? 'default') === 'emacs'   ? 'selected' : '' ?>>Emacs</option>
                </select>
            </div>
        </div>

        <!-- toggles row 1 -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin-bottom:1rem;">
            <label class="toggle-label">
                <input type="checkbox" name="auto_save" id="auto_save" <?= !empty($settings['auto_save']) ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
                <span class="toggle-text">
                    <span style="font-weight:600;color:var(--text-primary);font-size:.875rem;">Auto-Save</span>
                    <small style="display:block;color:var(--text-secondary);font-size:.73rem;">Save automatically every 3 seconds</small>
                </span>
            </label>
            <label class="toggle-label">
                <input type="checkbox" name="auto_preview" id="auto_preview" <?= !empty($settings['auto_preview']) ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
                <span class="toggle-text">
                    <span style="font-weight:600;color:var(--text-primary);font-size:.875rem;">Live Preview</span>
                    <small style="display:block;color:var(--text-secondary);font-size:.73rem;">Update preview as you type</small>
                </span>
            </label>
            <label class="toggle-label">
                <input type="checkbox" name="word_wrap" id="word_wrap" <?= !empty($settings['word_wrap']) ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
                <span class="toggle-text">
                    <span style="font-weight:600;color:var(--text-primary);font-size:.875rem;">Word Wrap</span>
                    <small style="display:block;color:var(--text-secondary);font-size:.73rem;">Wrap long lines in the editor</small>
                </span>
            </label>
        </div>

        <!-- toggles row 2 -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;">
            <label class="toggle-label">
                <input type="checkbox" name="line_numbers" id="line_numbers" <?= ($settings['line_numbers'] ?? 1) ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
                <span class="toggle-text">
                    <span style="font-weight:600;color:var(--text-primary);font-size:.875rem;">Line Numbers</span>
                    <small style="display:block;color:var(--text-secondary);font-size:.73rem;">Show line numbers in the gutter</small>
                </span>
            </label>
            <label class="toggle-label">
                <input type="checkbox" name="bracket_matching" id="bracket_matching" <?= ($settings['bracket_matching'] ?? 1) ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
                <span class="toggle-text">
                    <span style="font-weight:600;color:var(--text-primary);font-size:.875rem;">Bracket Matching</span>
                    <small style="display:block;color:var(--text-secondary);font-size:.73rem;">Highlight matching brackets</small>
                </span>
            </label>
            <label class="toggle-label">
                <input type="checkbox" name="auto_indent" id="auto_indent" <?= ($settings['auto_indent'] ?? 1) ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
                <span class="toggle-text">
                    <span style="font-weight:600;color:var(--text-primary);font-size:.875rem;">Auto Indent</span>
                    <small style="display:block;color:var(--text-secondary);font-size:.73rem;">Automatic indentation on new line</small>
                </span>
            </label>
        </div>
    </div>

    <!-- ── Visual Aids ── -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-eye"></i> Visual Aids
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;">
            <label class="toggle-label">
                <input type="checkbox" name="highlight_line" id="highlight_line" <?= ($settings['highlight_line'] ?? 1) ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
                <span class="toggle-text">
                    <span style="font-weight:600;color:var(--text-primary);font-size:.875rem;">Highlight Active Line</span>
                    <small style="display:block;color:var(--text-secondary);font-size:.73rem;">Highlight the line the cursor is on</small>
                </span>
            </label>
            <label class="toggle-label">
                <input type="checkbox" name="indent_guides" id="indent_guides" <?= ($settings['indent_guides'] ?? 1) ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
                <span class="toggle-text">
                    <span style="font-weight:600;color:var(--text-primary);font-size:.875rem;">Indent Guides</span>
                    <small style="display:block;color:var(--text-secondary);font-size:.73rem;">Show vertical indentation guide lines</small>
                </span>
            </label>
            <label class="toggle-label">
                <input type="checkbox" name="show_minimap" id="show_minimap" <?= !empty($settings['show_minimap']) ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
                <span class="toggle-text">
                    <span style="font-weight:600;color:var(--text-primary);font-size:.875rem;">Minimap</span>
                    <small style="display:block;color:var(--text-secondary);font-size:.73rem;">Show scrollable code minimap (future)</small>
                </span>
            </label>
        </div>
    </div>

    <!-- ── Actions ── -->
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;padding-bottom:2rem;">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-floppy-disk"></i> Save Settings
        </button>
        <button type="button" onclick="resetSettings()" class="btn btn-secondary">
            <i class="fas fa-rotate-left"></i> Reset to Defaults
        </button>
        <a href="/projects/codexpro/editor" class="btn btn-secondary">
            <i class="fas fa-pen-to-square"></i> Open Editor
        </a>
    </div>
</form>

<script>
function saveSettings(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
    fetch('/projects/codexpro/settings', { method: 'POST', body: new FormData(e.target) })
        .then(r => { if (!r.ok) throw new Error('Network error'); return r.json(); })
        .then(data => {
            if (data.success) {
                showNotification('Settings saved successfully!', 'success');
            } else {
                showNotification('Error: ' + (data.error || 'Unknown'), 'error');
            }
        })
        .catch(() => showNotification('Error saving settings. Try again.', 'error'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-floppy-disk"></i> Save Settings';
        });
}

function resetSettings() {
    if (!confirm('Reset all settings to defaults?')) return;
    document.getElementById('theme').value          = 'dark';
    document.getElementById('font_family').value    = 'JetBrains Mono';
    document.getElementById('font_size').value      = '14';
    document.getElementById('tab_size').value       = '2';
    document.getElementById('key_bindings').value   = 'default';
    document.getElementById('auto_save').checked    = true;
    document.getElementById('auto_preview').checked = true;
    document.getElementById('word_wrap').checked    = false;
    document.getElementById('line_numbers').checked = true;
    document.getElementById('bracket_matching').checked = true;
    document.getElementById('auto_indent').checked  = true;
    document.getElementById('highlight_line').checked = true;
    document.getElementById('indent_guides').checked = true;
    document.getElementById('show_minimap').checked  = false;
    showNotification('Settings reset to defaults. Click Save to apply.', 'success');
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
