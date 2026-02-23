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

<!-- Generate Key for User Modal -->
<div id="genKeyModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.7);align-items:center;justify-content:center;">
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:24px;width:min(480px,94vw);position:relative;">
        <button onclick="closeGenModal()" style="position:absolute;top:12px;right:14px;background:none;border:none;color:var(--text-secondary);font-size:1.3rem;cursor:pointer;line-height:1;">×</button>
        <h3 style="margin:0 0 16px;font-size:1rem;font-weight:700;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-key" style="color:var(--cyan);"></i> Generate API Key for User
        </h3>
        <div id="genKeyResult" style="display:none;margin-bottom:14px;padding:10px 14px;border-radius:8px;background:rgba(0,255,136,.08);border:1px solid var(--green);font-size:.82rem;color:var(--green);">
        </div>
        <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:5px;color:var(--text-secondary);">Select User</label>
        <input type="text" id="userSearch" placeholder="Search by name or email…" oninput="searchUsers(this.value)"
            style="width:100%;padding:9px 12px;border-radius:7px;border:1px solid var(--border-color);background:var(--bg-secondary);color:var(--text-primary);font-size:.83rem;margin-bottom:6px;box-sizing:border-box;">
        <select id="userSelect" size="4" style="width:100%;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:7px;color:var(--text-primary);font-size:.83rem;margin-bottom:12px;padding:4px;">
            <?php foreach ($allUsers ?? [] as $u): ?>
            <option value="<?= (int)$u['id'] ?>" data-label="<?= View::e($u['name']) ?> (<?= View::e($u['email']) ?>)">
                <?= View::e($u['name']) ?> — <?= View::e($u['email']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:5px;color:var(--text-secondary);">Key Name</label>
        <input type="text" id="genKeyName" placeholder="e.g. Admin Generated Key" maxlength="80"
            style="width:100%;padding:9px 12px;border-radius:7px;border:1px solid var(--border-color);background:var(--bg-secondary);color:var(--text-primary);font-size:.83rem;margin-bottom:14px;box-sizing:border-box;">
        <div id="newGeneratedKey" style="display:none;margin-bottom:14px;">
            <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:5px;color:var(--cyan);">⚡ Generated Key — copy now, shown only once</label>
            <div style="display:flex;gap:8px;align-items:center;">
                <code id="newKeyVal" style="flex:1;background:rgba(0,0,0,.4);padding:8px 12px;border-radius:6px;font-size:.8rem;word-break:break-all;border:1px solid rgba(0,240,255,.3);color:var(--green);"></code>
                <button onclick="copyGenKey()" style="padding:7px 14px;background:var(--cyan);color:#000;border:none;border-radius:6px;font-weight:700;font-size:.78rem;cursor:pointer;white-space:nowrap;">Copy</button>
            </div>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button onclick="closeGenModal()" style="padding:9px 18px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:7px;color:var(--text-secondary);font-size:.83rem;cursor:pointer;">Cancel</button>
            <button onclick="submitGenKey()" style="padding:9px 18px;background:linear-gradient(135deg,var(--cyan),var(--purple));border:none;border-radius:7px;color:#000;font-weight:700;font-size:.83rem;cursor:pointer;">Generate Key</button>
        </div>
    </div>
</div>

<!-- Keys table -->
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;overflow:hidden;">
    <div style="padding:14px 18px;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <h3 style="margin:0;font-size:.95rem;font-weight:700;">All QR API Keys</h3>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <input type="text" id="searchKeys" placeholder="Search name / email…"
                oninput="filterKeys(this.value)"
                style="padding:7px 12px;border-radius:7px;border:1px solid var(--border-color);background:var(--bg-secondary);color:var(--text-primary);font-size:.82rem;width:200px;">
            <button onclick="openGenModal()" style="padding:7px 14px;background:linear-gradient(135deg,var(--cyan),var(--purple));border:none;border-radius:7px;color:#000;font-weight:700;font-size:.82rem;cursor:pointer;white-space:nowrap;">
                <i class="fas fa-plus"></i> Generate for User
            </button>
        </div>
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
                        <div style="display:flex;align-items:center;gap:6px;">
                        <code id="masked-<?= $k['id'] ?>" style="background:rgba(0,0,0,.3);padding:3px 8px;border-radius:5px;font-size:.75rem;">
                            <?= substr(View::e($k['api_key']), 0, 12) ?>••••••
                        </code>
                        <button onclick="copyMaskedKey('<?= htmlspecialchars(substr($k['api_key'], 0, 12), ENT_QUOTES) ?>••••••')"
                            title="Copy masked key" style="padding:3px 7px;background:rgba(0,240,255,.08);border:1px solid rgba(0,240,255,.25);color:var(--cyan);border-radius:5px;font-size:.72rem;cursor:pointer;white-space:nowrap;">
                            <i class="fas fa-copy"></i>
                        </button>
                        </div>
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
function copyMaskedKey(val) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(val).then(() => showToast('Copied to clipboard!', 'success'))
            .catch(() => showToast('Copy failed.', 'error'));
    } else {
        const ta = document.createElement('textarea');
        ta.value = val; document.body.appendChild(ta); ta.select();
        document.execCommand('copy') ? showToast('Copied!', 'success') : showToast('Copy failed.', 'error');
        document.body.removeChild(ta);
    }
}

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
            showToast(d.error || 'Failed to revoke.', 'error');
            btn.disabled = false;
            btn.textContent = 'Revoke';
        }
    });
}

// ── Generate for user modal ───────────────────────────────────────────────
function openGenModal() {
    document.getElementById('genKeyModal').style.display = 'flex';
    document.getElementById('genKeyName').value = '';
    document.getElementById('newGeneratedKey').style.display = 'none';
    document.getElementById('genKeyResult').style.display = 'none';
}
function closeGenModal() {
    document.getElementById('genKeyModal').style.display = 'none';
}

function searchUsers(q) {
    q = q.toLowerCase();
    const sel = document.getElementById('userSelect');
    Array.from(sel.options).forEach(opt => {
        opt.style.display = opt.getAttribute('data-label').toLowerCase().includes(q) ? '' : 'none';
    });
}

function submitGenKey() {
    const userId = document.getElementById('userSelect').value;
    const name   = document.getElementById('genKeyName').value.trim();
    if (!userId) { showToast('Please select a user.', 'error'); return; }
    if (!name)   { showToast('Key name is required.', 'error'); return; }

    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    fetch('/admin/qr/api-keys/generate', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: '_csrf_token=' + encodeURIComponent(csrf)
            + '&user_id=' + encodeURIComponent(userId)
            + '&name='    + encodeURIComponent(name)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            document.getElementById('newKeyVal').textContent = d.api_key;
            document.getElementById('newGeneratedKey').style.display = 'block';
            const res = document.getElementById('genKeyResult');
            res.textContent = '✓ ' + (d.message || 'Key generated successfully.');
            res.style.display = 'block';
        } else {
            showToast(d.error || 'Failed to generate key.', 'error');
        }
    })
    .catch(() => showToast('Network error.', 'error'));
}

function copyGenKey() {
    const val = document.getElementById('newKeyVal').textContent.trim();
    navigator.clipboard.writeText(val).then(() => showToast('Copied!', 'success'));
}

function showToast(msg, type) {
    const t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:99999;padding:10px 18px;border-radius:8px;font-size:.85rem;font-weight:600;pointer-events:none;'
        + (type === 'success'
            ? 'background:rgba(0,255,136,.15);border:1px solid var(--green);color:var(--green);'
            : 'background:rgba(255,107,107,.15);border:1px solid var(--red);color:var(--red);');
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}
</script>

<?php View::end(); ?>
