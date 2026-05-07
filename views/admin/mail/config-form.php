<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1 style="margin:0;"><?= View::e($title) ?></h1>
        <p style="color:var(--text-secondary);margin:4px 0 0;">Configure SMTP and IMAP credentials</p>
    </div>
    <a href="/admin/mail/config" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<?php if (Helpers::hasFlash('error')): ?>
<div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<?php
$isEdit   = $provider !== null;
$action   = $isEdit ? '/admin/mail/config/update' : '/admin/mail/config/store';
$p        = $provider ?? [];
?>

<form method="POST" action="<?= $action ?>" id="mailConfigForm">
    <?= Security::csrfField() ?>
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
    <?php endif; ?>

    <!-- Basic info -->
    <div class="card" style="margin-bottom:20px;">
        <h3 style="margin:0 0 16px;font-size:16px;"><i class="fas fa-info-circle" style="color:var(--cyan);"></i> Provider Details</h3>
        <div class="grid grid-2">
            <div class="form-group">
                <label class="form-label">Display Name <span style="color:var(--red);">*</span></label>
                <input type="text" name="name" class="form-input" required
                       value="<?= View::e($p['name'] ?? '') ?>" placeholder="e.g. Zoho Main, Gmail Transactional">
            </div>
            <div class="form-group">
                <label class="form-label">Provider Type</label>
                <select name="provider_type" class="form-input" id="providerType" onchange="applyPreset(this.value)">
                    <?php foreach (['smtp' => 'Custom SMTP', 'zoho' => 'Zoho Mail', 'gmail' => 'Gmail / Google Workspace', 'outlook' => 'Outlook / Office 365', 'custom' => 'Other'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($p['provider_type'] ?? 'smtp') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- SMTP -->
    <div class="card" style="margin-bottom:20px;">
        <h3 style="margin:0 0 16px;font-size:16px;"><i class="fas fa-paper-plane" style="color:var(--magenta);"></i> SMTP (Outgoing Mail)</h3>
        <div class="grid grid-2">
            <div class="form-group">
                <label class="form-label">SMTP Host</label>
                <input type="text" name="smtp_host" id="smtpHost" class="form-input"
                       value="<?= View::e($p['smtp_host'] ?? '') ?>" placeholder="smtp.zoho.com">
            </div>
            <div class="form-group">
                <label class="form-label">SMTP Port</label>
                <input type="number" name="smtp_port" id="smtpPort" class="form-input"
                       value="<?= (int)($p['smtp_port'] ?? 587) ?>" placeholder="587">
            </div>
            <div class="form-group">
                <label class="form-label">Username / Email</label>
                <input type="text" name="smtp_username" class="form-input"
                       value="<?= View::e($p['smtp_username'] ?? '') ?>" placeholder="you@yourdomain.com">
            </div>
            <div class="form-group">
                <label class="form-label">Password
                    <?php if (!empty($p['smtp_password_set'])): ?>
                        <span style="color:var(--green);font-size:12px;">(saved – leave blank to keep)</span>
                    <?php endif; ?>
                </label>
                <input type="password" name="smtp_password" class="form-input"
                       placeholder="<?= !empty($p['smtp_password_set']) ? '••••••••' : 'Enter password' ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Encryption</label>
                <select name="smtp_encryption" id="smtpEnc" class="form-input">
                    <?php foreach (['tls' => 'TLS (STARTTLS)', 'ssl' => 'SSL', 'none' => 'None'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($p['smtp_encryption'] ?? 'tls') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div style="margin-top:8px;">
            <button type="button" class="btn btn-secondary" onclick="testSmtp()">
                <i class="fas fa-plug"></i> Test SMTP Connection
            </button>
            <span id="smtpTestResult" style="margin-left:12px;font-size:13px;"></span>
        </div>
    </div>

    <!-- Sender identity -->
    <div class="card" style="margin-bottom:20px;">
        <h3 style="margin:0 0 16px;font-size:16px;"><i class="fas fa-id-badge" style="color:var(--orange);"></i> Sender Identity</h3>
        <div class="grid grid-3">
            <div class="form-group">
                <label class="form-label">From Name</label>
                <input type="text" name="from_name" class="form-input"
                       value="<?= View::e($p['from_name'] ?? (defined('APP_NAME') ? APP_NAME : '')) ?>" placeholder="MyApp">
            </div>
            <div class="form-group">
                <label class="form-label">From Email</label>
                <input type="email" name="from_email" class="form-input"
                       value="<?= View::e($p['from_email'] ?? '') ?>" placeholder="noreply@yourdomain.com">
            </div>
            <div class="form-group">
                <label class="form-label">Reply-To (optional)</label>
                <input type="email" name="reply_to" class="form-input"
                       value="<?= View::e($p['reply_to'] ?? '') ?>" placeholder="support@yourdomain.com">
            </div>
        </div>
    </div>

    <!-- IMAP -->
    <div class="card" style="margin-bottom:20px;">
        <h3 style="margin:0 0 16px;font-size:16px;display:flex;align-items:center;gap:10px;">
            <i class="fas fa-inbox" style="color:var(--purple);"></i> IMAP (Inbox Sync)
            <label style="display:flex;align-items:center;gap:6px;font-size:14px;font-weight:400;cursor:pointer;">
                <input type="checkbox" name="is_imap_enabled" value="1" id="imapEnabled"
                    <?= !empty($p['is_imap_enabled']) ? 'checked' : '' ?> onchange="toggleImapSection(this.checked)">
                Enable IMAP inbox sync
            </label>
        </h3>
        <div id="imapSection" style="<?= empty($p['is_imap_enabled']) ? 'opacity:.4;pointer-events:none;' : '' ?>">
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">IMAP Host</label>
                    <input type="text" name="imap_host" id="imapHost" class="form-input"
                           value="<?= View::e($p['imap_host'] ?? '') ?>" placeholder="imap.zoho.com">
                </div>
                <div class="form-group">
                    <label class="form-label">IMAP Port</label>
                    <input type="number" name="imap_port" id="imapPort" class="form-input"
                           value="<?= (int)($p['imap_port'] ?? 993) ?>" placeholder="993">
                </div>
                <div class="form-group">
                    <label class="form-label">IMAP Username</label>
                    <input type="text" name="imap_username" class="form-input"
                           value="<?= View::e($p['imap_username'] ?? '') ?>" placeholder="Same as SMTP usually">
                </div>
                <div class="form-group">
                    <label class="form-label">IMAP Password
                        <?php if (!empty($p['imap_password_set'])): ?>
                            <span style="color:var(--green);font-size:12px;">(saved – leave blank to keep)</span>
                        <?php endif; ?>
                    </label>
                    <input type="password" name="imap_password" class="form-input"
                           placeholder="<?= !empty($p['imap_password_set']) ? '••••••••' : 'Enter password' ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">IMAP Encryption</label>
                    <select name="imap_encryption" id="imapEnc" class="form-input">
                        <?php foreach (['ssl' => 'SSL (recommended)', 'tls' => 'TLS', 'none' => 'None'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($p['imap_encryption'] ?? 'ssl') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="button" class="btn btn-secondary" onclick="testImap()">
                <i class="fas fa-plug"></i> Test IMAP Connection
            </button>
            <span id="imapTestResult" style="margin-left:12px;font-size:13px;"></span>
        </div>
    </div>

    <div style="display:flex;gap:12px;">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> <?= $isEdit ? 'Update Provider' : 'Save Provider' ?>
        </button>
        <a href="/admin/mail/config" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

const presets = {
    zoho:    { host: 'smtp.zoho.com',    port: 587, enc: 'tls', imap_host: 'imap.zoho.com',    imap_port: 993, imap_enc: 'ssl' },
    gmail:   { host: 'smtp.gmail.com',   port: 587, enc: 'tls', imap_host: 'imap.gmail.com',   imap_port: 993, imap_enc: 'ssl' },
    outlook: { host: 'smtp.office365.com', port: 587, enc: 'tls', imap_host: 'outlook.office365.com', imap_port: 993, imap_enc: 'ssl' },
    smtp:    { host: '', port: 587, enc: 'tls', imap_host: '', imap_port: 993, imap_enc: 'ssl' },
    custom:  { host: '', port: 587, enc: 'tls', imap_host: '', imap_port: 993, imap_enc: 'ssl' },
};

function applyPreset(type) {
    const p = presets[type];
    if (!p) return;
    if (!document.getElementById('smtpHost').value) {
        document.getElementById('smtpHost').value = p.host;
        document.getElementById('smtpPort').value = p.port;
        document.getElementById('smtpEnc').value  = p.enc;
    }
    if (!document.getElementById('imapHost').value) {
        document.getElementById('imapHost').value = p.imap_host;
        document.getElementById('imapPort').value = p.imap_port;
        document.getElementById('imapEnc').value  = p.imap_enc;
    }
}

function toggleImapSection(enabled) {
    const sec = document.getElementById('imapSection');
    sec.style.opacity = enabled ? '1' : '.4';
    sec.style.pointerEvents = enabled ? '' : 'none';
}

function testSmtp() {
    const el   = document.getElementById('smtpTestResult');
    const form = document.getElementById('mailConfigForm');
    const data = new FormData(form);
    el.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing…';
    fetch('/admin/mail/config/test-smtp', {
        method: 'POST',
        headers: {'X-CSRF-Token': csrfToken},
        body: new URLSearchParams({
            _csrf_token:     csrfToken,
            id:              data.get('id') || '',
            smtp_host:       data.get('smtp_host'),
            smtp_port:       data.get('smtp_port'),
            smtp_encryption: data.get('smtp_encryption'),
            smtp_username:   data.get('smtp_username'),
            smtp_password:   data.get('smtp_password'),
        })
    }).then(r => r.json()).then(d => {
        el.innerHTML = d.success
            ? '<span style="color:var(--green);"><i class="fas fa-check"></i> ' + d.message + '</span>'
            : '<span style="color:var(--red);"><i class="fas fa-times"></i> ' + d.message + '</span>';
    });
}

function testImap() {
    const el   = document.getElementById('imapTestResult');
    const form = document.getElementById('mailConfigForm');
    const data = new FormData(form);
    el.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing…';
    fetch('/admin/mail/config/test-imap', {
        method: 'POST',
        headers: {'X-CSRF-Token': csrfToken},
        body: new URLSearchParams({
            _csrf_token:     csrfToken,
            imap_host:       data.get('imap_host'),
            imap_port:       data.get('imap_port'),
            imap_encryption: data.get('imap_encryption'),
            imap_username:   data.get('imap_username'),
            imap_password:   data.get('imap_password'),
        })
    }).then(r => r.json()).then(d => {
        el.innerHTML = d.success
            ? '<span style="color:var(--green);"><i class="fas fa-check"></i> ' + d.message + '</span>'
            : '<span style="color:var(--red);"><i class="fas fa-times"></i> ' + d.message + '</span>';
    });
}
</script>

<?php View::endSection(); ?>
