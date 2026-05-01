<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content">
    <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
        <div>
            <h1>⚙️ Upload Scan Settings</h1>
            <p class="page-subtitle" style="margin:0;color:#666;font-size:14px;">Configure ClamAV antivirus scanning, alert emails, and scan mode</p>
        </div>
        <a href="/admin/security" class="btn btn-secondary">← Back to Security Center</a>
    </div>

    <?php if ($flash = Helpers::getFlash('success')): ?>
    <div class="alert alert-success" style="background:#d4edda;color:#155724;padding:12px 18px;border-radius:8px;margin-bottom:20px;">
        ✅ <?= htmlspecialchars($flash) ?>
    </div>
    <?php endif; ?>
    <?php if ($flash = Helpers::getFlash('error')): ?>
    <div class="alert alert-danger" style="background:#f8d7da;color:#721c24;padding:12px 18px;border-radius:8px;margin-bottom:20px;">
        ❌ <?= htmlspecialchars($flash) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/admin/security/update-settings">
        <?= \Core\Security::csrfField() ?>

        <!-- ClamAV Settings -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h3>🦠 Antivirus (ClamAV)</h3>
                <p>Settings for the ClamAV virus scanner that runs on every uploaded file.</p>
            </div>

            <div class="form-group">
                <label class="form-label">ClamAV Enabled</label>
                <div class="toggle-row">
                    <label class="toggle">
                        <input type="checkbox" name="upload_clamav_enabled" value="1"
                            <?= ($settings['upload_clamav_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Scan every uploaded file with ClamAV</span>
                </div>
                <small class="form-hint">Disable only if ClamAV is not installed on your server.</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="upload_clamav_command">ClamAV Scan Command</label>
                <input type="text" id="upload_clamav_command" name="upload_clamav_command"
                    class="form-control"
                    value="<?= htmlspecialchars($settings['upload_clamav_command'] ?? 'clamscan --no-summary --stdout') ?>"
                    placeholder="clamscan --no-summary --stdout">
                <small class="form-hint">
                    Use <code>clamscan --no-summary --stdout</code> (works without a daemon — recommended for most servers).<br>
                    Use <code>clamdscan --no-summary --stdout</code> if you have the ClamAV daemon running (faster).
                </small>
            </div>

            <div class="form-group">
                <label class="form-label">Scan Mode</label>
                <div class="radio-group">
                    <label class="radio-option <?= ($settings['upload_scan_mode'] ?? 'enforce') === 'enforce' ? 'selected' : '' ?>">
                        <input type="radio" name="upload_scan_mode" value="enforce"
                            <?= ($settings['upload_scan_mode'] ?? 'enforce') === 'enforce' ? 'checked' : '' ?>>
                        <div class="radio-content">
                            <span class="radio-title">🔒 Enforce (Recommended)</span>
                            <span class="radio-desc">Block the upload if a virus is detected OR if ClamAV cannot run. Safe choice.</span>
                        </div>
                    </label>
                    <label class="radio-option <?= ($settings['upload_scan_mode'] ?? '') === 'passive' ? 'selected' : '' ?>">
                        <input type="radio" name="upload_scan_mode" value="passive"
                            <?= ($settings['upload_scan_mode'] ?? '') === 'passive' ? 'checked' : '' ?>>
                        <div class="radio-content">
                            <span class="radio-title">⚠️ Passive (Log only)</span>
                            <span class="radio-desc">Log the scan result but allow the file through even if ClamAV fails to run. Only use if you are testing.</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Alert Email Settings -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h3>📧 Security Alert Emails</h3>
                <p>These email addresses receive an alert whenever a dangerous upload is detected or blocked.</p>
            </div>

            <div class="form-group">
                <label class="form-label" for="security_alert_emails">Alert Email Addresses</label>
                <textarea id="security_alert_emails" name="security_alert_emails" class="form-control"
                    rows="3" placeholder="admin@example.com, security@example.com"
                ><?= htmlspecialchars($settings['security_alert_emails'] ?? '') ?></textarea>
                <small class="form-hint">
                    Comma-separated list of email addresses. Leave blank to send alerts to all admin accounts.
                    All admin bell notifications are always sent regardless of this setting.
                </small>
            </div>
        </div>

        <!-- What Triggers Alerts -->
        <div class="settings-card info-card">
            <div class="settings-card-header">
                <h3>ℹ️ What Triggers Alerts</h3>
            </div>
            <ul class="info-list-plain">
                <li>🚨 <strong>Virus / malware detected</strong> — ClamAV found a known threat (e.g. EICAR test, ransomware)</li>
                <li>🚫 <strong>Blocked file type</strong> — Extension or MIME type is on the block list (e.g. .php, .exe)</li>
                <li>🔍 <strong>Script content detected</strong> — File content contains embedded PHP/JS/HTML code</li>
                <li>🖼 <strong>Invalid image</strong> — File claims to be an image but is not a valid image</li>
                <li>📏 <strong>File too large</strong> — Exceeds the configured size limit</li>
            </ul>
            <p style="margin-top:10px;color:#555;font-size:13px;">
                ✅ Safe uploads appear in the <strong>Upload Scan Log</strong> on the Security Center with a green "Clean" badge.
            </p>
        </div>

        <div style="display:flex;gap:12px;margin-top:10px;">
            <button type="submit" class="btn btn-primary">💾 Save Settings</button>
            <a href="/admin/security" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<style>
.settings-card {
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.settings-card-header h3 {
    margin: 0 0 6px;
    font-size: 18px;
    color: #333;
}
.settings-card-header p {
    margin: 0 0 20px;
    color: #666;
    font-size: 14px;
}
.form-group {
    margin-bottom: 20px;
}
.form-label {
    display: block;
    font-weight: 600;
    font-size: 14px;
    color: #333;
    margin-bottom: 8px;
}
.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    box-sizing: border-box;
}
.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102,126,234,0.15);
}
.form-hint {
    display: block;
    margin-top: 6px;
    font-size: 12px;
    color: #888;
}
.form-hint code {
    background: #f1f1f1;
    padding: 1px 5px;
    border-radius: 4px;
    font-size: 12px;
}
/* Toggle */
.toggle-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 6px;
}
.toggle {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
    flex-shrink: 0;
}
.toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}
.toggle-slider {
    position: absolute;
    inset: 0;
    background: #ccc;
    border-radius: 26px;
    cursor: pointer;
    transition: .3s;
}
.toggle-slider:before {
    content: "";
    position: absolute;
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background: white;
    border-radius: 50%;
    transition: .3s;
}
.toggle input:checked + .toggle-slider {
    background: #43e97b;
}
.toggle input:checked + .toggle-slider:before {
    transform: translateX(24px);
}
.toggle-label {
    font-size: 14px;
    color: #444;
}
/* Radio options */
.radio-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.radio-option {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 16px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    cursor: pointer;
    transition: border-color .2s;
}
.radio-option.selected,
.radio-option:has(input:checked) {
    border-color: #667eea;
    background: #f5f7ff;
}
.radio-option input[type=radio] {
    margin-top: 3px;
    accent-color: #667eea;
}
.radio-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.radio-title {
    font-weight: 600;
    font-size: 14px;
    color: #333;
}
.radio-desc {
    font-size: 13px;
    color: #666;
}
/* Info card */
.info-card {
    background: #f8f9ff;
    border: 1px solid #e0e7ff;
}
.info-list-plain {
    padding-left: 0;
    list-style: none;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.info-list-plain li {
    font-size: 14px;
    color: #444;
}
</style>
<?php View::endSection(); ?>
