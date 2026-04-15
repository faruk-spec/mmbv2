<?php $pageTitle = 'Settings'; ?>
<div style="margin-bottom:16px;">
    <h2 style="margin:0;font-size:18px;font-weight:600;">Mail Settings</h2>
    <p class="text-muted" style="font-size:13px;margin-top:4px;">Your account mail preferences</p>
</div>

<!-- Current mail provider info (read-only for users) -->
<div class="card" style="margin-bottom:16px;">
    <h3 style="font-size:15px;margin-bottom:14px;"><i class="fas fa-server" style="color:var(--cyan,#667eea);"></i> Active Mail Provider</h3>
    <?php if ($provider): ?>
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <tr>
            <td style="padding:8px 0;color:#64748b;width:160px;">Provider Type</td>
            <td style="padding:8px 0;"><strong><?= htmlspecialchars(strtoupper($provider['provider_type'] ?? 'SMTP'), ENT_QUOTES) ?></strong></td>
        </tr>
        <tr>
            <td style="padding:8px 0;color:#64748b;">SMTP Host</td>
            <td style="padding:8px 0;"><code><?= htmlspecialchars($provider['smtp_host'] ?? '—', ENT_QUOTES) ?></code></td>
        </tr>
        <tr>
            <td style="padding:8px 0;color:#64748b;">From Email</td>
            <td style="padding:8px 0;"><?= htmlspecialchars($provider['from_email'] ?? '—', ENT_QUOTES) ?></td>
        </tr>
        <tr>
            <td style="padding:8px 0;color:#64748b;">IMAP Sync</td>
            <td style="padding:8px 0;">
                <?php if ($provider['is_imap_enabled']): ?>
                    <span style="color:#6ee7b7;"><i class="fas fa-check-circle"></i> Enabled (<?= htmlspecialchars($provider['imap_host'] ?? '', ENT_QUOTES) ?>)</span>
                <?php else: ?>
                    <span style="color:#64748b;"><i class="fas fa-times-circle"></i> Disabled</span>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <p style="font-size:12px;color:#475569;margin-top:10px;">
        <i class="fas fa-info-circle"></i>
        Mail settings are managed by the platform administrator. Contact your admin to change the mail provider.
    </p>
    <?php else: ?>
    <div style="text-align:center;padding:24px;color:#64748b;">
        <i class="fas fa-exclamation-triangle" style="font-size:24px;margin-bottom:8px;color:#f59e0b;"></i>
        <p>No mail provider configured. Please ask an administrator to set up the mail configuration.</p>
        <a href="<?= htmlspecialchars($appUrl ?? '', ENT_QUOTES) ?>/admin/mail/config" style="color:#667eea;font-size:13px;">Go to Admin Mail Config →</a>
    </div>
    <?php endif; ?>
</div>

<!-- Sync inbox -->
<?php if ($provider && $provider['is_imap_enabled']): ?>
<div class="card" style="margin-bottom:16px;">
    <h3 style="font-size:15px;margin-bottom:12px;"><i class="fas fa-sync-alt" style="color:#6ee7b7;"></i> Inbox Sync</h3>
    <p style="font-size:13px;color:#94a3b8;margin-bottom:14px;">Manually trigger an inbox sync to fetch new emails from your provider.</p>
    <button class="btn btn-primary" onclick="doSync()">
        <i class="fas fa-sync-alt" id="syncIcon"></i> Sync Now
    </button>
    <span id="syncResult" style="margin-left:12px;font-size:13px;"></span>
</div>
<script>
function doSync() {
    const icon   = document.getElementById('syncIcon');
    const result = document.getElementById('syncResult');
    icon.classList.add('fa-spin');
    result.textContent = 'Syncing…';
    fetch('/sync', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: '_csrf_token=' + encodeURIComponent(csrfToken)
    }).then(r => r.json()).then(d => {
        icon.classList.remove('fa-spin');
        result.textContent = d.synced > 0
            ? `✓ Synced ${d.synced} new message(s)`
            : '✓ No new messages';
        result.style.color = '#6ee7b7';
    }).catch(() => {
        icon.classList.remove('fa-spin');
        result.textContent = 'Sync failed. Check your IMAP settings.';
        result.style.color = '#fca5a5';
    });
}
</script>
<?php endif; ?>

<!-- Profile info (read-only) -->
<div class="card">
    <h3 style="font-size:15px;margin-bottom:14px;"><i class="fas fa-user" style="color:#a78bfa;"></i> Account</h3>
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <tr>
            <td style="padding:8px 0;color:#64748b;width:160px;">Name</td>
            <td><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES) ?></td>
        </tr>
        <tr>
            <td style="padding:8px 0;color:#64748b;">Email</td>
            <td><?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES) ?></td>
        </tr>
    </table>
    <div style="margin-top:14px;">
        <a href="<?= htmlspecialchars($appUrl ?? '', ENT_QUOTES) ?>/dashboard/settings" class="btn btn-secondary btn-sm">
            <i class="fas fa-cog"></i> Platform Settings
        </a>
    </div>
</div>
