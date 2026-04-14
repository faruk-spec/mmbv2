<?php use Core\View; use Core\Auth; ?>
<?php $pageTitle = 'Settings'; ?>
<?php View::extend('mail'); ?>
<?php View::section('content'); ?>

<div style="margin-bottom:16px;">
    <h2 style="margin:0;font-size:18px;font-weight:600;">Mail Settings</h2>
    <p class="text-muted" style="font-size:13px;margin-top:4px;">Your account mail preferences</p>
</div>

<!-- Active mail provider (read-only for regular users) -->
<div class="mail-card" style="margin-bottom:16px;">
    <h3 style="font-size:15px;margin-bottom:14px;"><i class="fas fa-server" style="color:#667eea;"></i> Active Mail Provider</h3>
    <?php if ($provider): ?>
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <tr>
            <td style="padding:8px 0;color:#64748b;width:160px;">Provider Type</td>
            <td style="padding:8px 0;"><strong><?= htmlspecialchars(strtoupper($provider['provider_type'] ?? 'SMTP'), ENT_QUOTES, 'UTF-8') ?></strong></td>
        </tr>
        <tr>
            <td style="padding:8px 0;color:#64748b;">SMTP Host</td>
            <td style="padding:8px 0;"><code><?= htmlspecialchars($provider['smtp_host'] ?? '—', ENT_QUOTES, 'UTF-8') ?></code></td>
        </tr>
        <tr>
            <td style="padding:8px 0;color:#64748b;">From Email</td>
            <td style="padding:8px 0;"><?= htmlspecialchars($provider['from_email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <td style="padding:8px 0;color:#64748b;">IMAP Sync</td>
            <td style="padding:8px 0;">
                <?php if ($provider['is_imap_enabled']): ?>
                    <span style="color:#6ee7b7;"><i class="fas fa-check-circle"></i> Enabled (<?= htmlspecialchars($provider['imap_host'] ?? '', ENT_QUOTES, 'UTF-8') ?>)</span>
                <?php else: ?>
                    <span style="color:#64748b;"><i class="fas fa-times-circle"></i> Disabled</span>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <p style="font-size:12px;color:#475569;margin-top:10px;">
        <i class="fas fa-info-circle"></i>
        Mail provider settings are managed by your platform administrator.
    </p>
    <?php else: ?>
    <div style="text-align:center;padding:24px;color:#64748b;">
        <i class="fas fa-exclamation-triangle" style="font-size:24px;margin-bottom:8px;color:#f59e0b;"></i>
        <p>No mail provider configured. Please ask an administrator to set up the mail configuration.</p>
        <?php if (Auth::isAdmin()): ?>
        <a href="/admin/mail/config" style="color:#667eea;font-size:13px;">Go to Admin Mail Config →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- IMAP sync trigger -->
<?php if ($provider && $provider['is_imap_enabled']): ?>
<div class="mail-card" style="margin-bottom:16px;">
    <h3 style="font-size:15px;margin-bottom:12px;"><i class="fas fa-sync-alt" style="color:#6ee7b7;"></i> Inbox Sync</h3>
    <p style="font-size:13px;color:#94a3b8;margin-bottom:14px;">Manually trigger an inbox sync to fetch new emails from your provider.</p>
    <button class="btn btn-primary" onclick="mailSyncNow()">
        <i class="fas fa-sync-alt" id="mailSettingsSyncIcon"></i> Sync Now
    </button>
    <span id="mailSettingsSyncResult" style="margin-left:12px;font-size:13px;"></span>
</div>
<script>
function mailSyncNow() {
    const icon   = document.getElementById('mailSettingsSyncIcon');
    const result = document.getElementById('mailSettingsSyncResult');
    icon.classList.add('fa-spin');
    result.textContent = 'Syncing…';
    mailPostAction('/mail/sync', {}, d => {
        icon.classList.remove('fa-spin');
        result.textContent = d.synced > 0
            ? `✓ Synced ${d.synced} new message(s)`
            : '✓ No new messages';
        result.style.color = '#6ee7b7';
    });
}
</script>
<?php endif; ?>

<!-- Account info -->
<div class="mail-card">
    <h3 style="font-size:15px;margin-bottom:14px;"><i class="fas fa-user" style="color:#a78bfa;"></i> Account</h3>
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <tr>
            <td style="padding:8px 0;color:#64748b;width:160px;">Name</td>
            <td><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <td style="padding:8px 0;color:#64748b;">Email</td>
            <td><?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
    </table>
    <div style="margin-top:14px;">
        <a href="/dashboard/settings" class="btn btn-secondary btn-sm">
            <i class="fas fa-cog"></i> Platform Account Settings
        </a>
        <?php if (Auth::isAdmin()): ?>
        <a href="/admin/mail/config" class="btn btn-secondary btn-sm" style="margin-left:8px;">
            <i class="fas fa-cogs"></i> Admin Mail Config
        </a>
        <?php endif; ?>
    </div>
</div>

<?php View::endSection(); ?>
