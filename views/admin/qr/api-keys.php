<?php
/**
 * Admin — QR API Keys
 * Rendered by QRAdminController::qrApiKeys()
 */
use Core\View;
use Core\Security;
?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>

<div class="content-header" style="margin-bottom:20px;">
    <h1 style="font-size:1.4rem;font-weight:700;display:flex;align-items:center;gap:10px;">
        <i class="fas fa-key" style="color:var(--cyan);"></i>
        QR API Keys
    </h1>
    <p style="color:var(--text-secondary);font-size:.85rem;margin-top:4px;">
        View and revoke API keys that have QR code permissions. Users generate keys from their QR dashboard.
    </p>
</div>

<!-- Stats row -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
    <?php
    $stats = [
        ['Total Keys',    $totalKeys,     'fa-key',       'var(--cyan)'],
        ['Active Keys',   $activeKeys,    'fa-check-circle','var(--green)'],
        ['Total Requests',$totalRequests, 'fa-exchange-alt','var(--purple)'],
    ];
    foreach ($stats as [$label, $val, $icon, $color]):
    ?>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:16px;display:flex;align-items:center;gap:14px;">
        <div style="width:40px;height:40px;border-radius:8px;background:rgba(0,240,255,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas <?= $icon ?>" style="color:<?= $color ?>;font-size:1.1rem;"></i>
        </div>
        <div>
            <div style="font-size:1.4rem;font-weight:700;"><?= number_format($val) ?></div>
            <div style="font-size:.78rem;color:var(--text-secondary);"><?= $label ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Keys table -->
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;overflow:hidden;">
    <div style="padding:14px 18px;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <h3 style="margin:0;font-size:.95rem;font-weight:700;">All QR API Keys</h3>
        <input type="text" id="searchKeys" placeholder="Search name / email…"
            oninput="filterKeys(this.value)"
            style="padding:7px 12px;border-radius:7px;border:1px solid var(--border-color);background:var(--bg-secondary);color:var(--text-primary);font-size:.82rem;width:220px;">
    </div>

    <?php if (empty($keys)): ?>
    <div style="padding:40px;text-align:center;color:var(--text-secondary);">
        <i class="fas fa-key" style="font-size:2rem;margin-bottom:10px;display:block;opacity:.3;"></i>
        No QR API keys found. Users generate keys from <strong>QR Dashboard → API Access</strong>.
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table id="keysTable" style="width:100%;border-collapse:collapse;font-size:.83rem;">
            <thead>
                <tr style="background:rgba(0,0,0,.2);border-bottom:1px solid var(--border-color);">
                    <th style="padding:10px 14px;text-align:left;color:var(--text-secondary);font-weight:600;">Name</th>
                    <th style="padding:10px 14px;text-align:left;color:var(--text-secondary);font-weight:600;">User</th>
                    <th style="padding:10px 14px;text-align:left;color:var(--text-secondary);font-weight:600;">Key (masked)</th>
                    <th style="padding:10px 14px;text-align:left;color:var(--text-secondary);font-weight:600;">Requests</th>
                    <th style="padding:10px 14px;text-align:left;color:var(--text-secondary);font-weight:600;">Last Used</th>
                    <th style="padding:10px 14px;text-align:left;color:var(--text-secondary);font-weight:600;">Created</th>
                    <th style="padding:10px 14px;text-align:left;color:var(--text-secondary);font-weight:600;">Status</th>
                    <th style="padding:10px 14px;text-align:left;color:var(--text-secondary);font-weight:600;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($keys as $k): ?>
                <tr class="key-row" style="border-bottom:1px solid rgba(255,255,255,.05);<?= $k['is_active'] ? '' : 'opacity:.5;' ?>">
                    <td style="padding:10px 14px;font-weight:600;"><?= View::e($k['name']) ?></td>
                    <td style="padding:10px 14px;">
                        <div style="font-weight:600;"><?= View::e($k['user_name'] ?? '—') ?></div>
                        <div style="font-size:.75rem;color:var(--text-secondary);"><?= View::e($k['email'] ?? '') ?></div>
                    </td>
                    <td style="padding:10px 14px;">
                        <code style="background:rgba(0,0,0,.3);padding:3px 8px;border-radius:5px;font-size:.75rem;">
                            <?= substr(View::e($k['api_key']), 0, 12) ?>••••••
                        </code>
                    </td>
                    <td style="padding:10px 14px;"><?= number_format((int)$k['request_count']) ?></td>
                    <td style="padding:10px 14px;color:var(--text-secondary);">
                        <?= $k['last_used_at'] ? date('M j, Y H:i', strtotime($k['last_used_at'])) : '—' ?>
                    </td>
                    <td style="padding:10px 14px;color:var(--text-secondary);">
                        <?= date('M j, Y', strtotime($k['created_at'])) ?>
                    </td>
                    <td style="padding:10px 14px;">
                        <span style="font-size:.75rem;padding:3px 8px;border-radius:12px;
                            <?= $k['is_active']
                                ? 'background:rgba(0,255,136,.1);color:var(--green);border:1px solid var(--green);'
                                : 'background:rgba(255,107,107,.1);color:var(--red);border:1px solid var(--red);' ?>">
                            <?= $k['is_active'] ? 'Active' : 'Revoked' ?>
                        </span>
                    </td>
                    <td style="padding:10px 14px;">
                        <?php if ($k['is_active']): ?>
                        <button onclick="revokeKey(<?= $k['id'] ?>, this)"
                            style="padding:5px 12px;background:rgba(255,107,107,.1);border:1px solid rgba(255,107,107,.4);color:var(--red);border-radius:6px;font-size:.78rem;cursor:pointer;">
                            Revoke
                        </button>
                        <?php else: ?>
                        <span style="color:var(--text-secondary);font-size:.78rem;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
function filterKeys(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#keysTable .key-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

function revokeKey(keyId, btn) {
    if (!confirm('Revoke this API key? All requests using it will fail immediately.')) return;
    btn.disabled = true;
    btn.textContent = '…';
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    fetch('/admin/qr/api-keys/revoke', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: '_csrf_token=' + encodeURIComponent(csrf) + '&key_id=' + keyId
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            location.reload();
        } else {
            alert(d.error || 'Failed.');
            btn.disabled = false;
            btn.textContent = 'Revoke';
        }
    });
}
</script>

<?php View::end(); ?>
