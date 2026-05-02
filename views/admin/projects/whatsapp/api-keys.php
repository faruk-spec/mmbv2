<?php use Core\View; use Core\Security; ?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>

<div class="content-header" style="margin-bottom:20px;">
    <h1 style="font-size:1.4rem;font-weight:700;display:flex;align-items:center;gap:10px;">
        <i class="fas fa-key" style="color:#25D366;"></i>
        WhatsApp API Keys
    </h1>
    <p style="color:var(--text-secondary);font-size:.85rem;margin-top:4px;">
        View, revoke, and generate WhatsApp API keys on behalf of users.
    </p>
</div>

<!-- Stats row -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
    <?php foreach ([
        ['Total Keys',     $totalKeys,     'fa-key',          'var(--cyan)'],
        ['Active Keys',    $activeKeys,    'fa-check-circle', '#25D366'],
        ['Total Requests', $totalRequests, 'fa-exchange-alt', 'var(--purple)'],
    ] as [$label, $val, $icon, $color]): ?>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:16px;display:flex;align-items:center;gap:14px;">
        <div style="width:40px;height:40px;border-radius:8px;background:rgba(37,211,102,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas <?= $icon ?>" style="color:<?= $color ?>;font-size:1.1rem;"></i>
        </div>
        <div>
            <div style="font-size:1.4rem;font-weight:700;"><?= number_format($val) ?></div>
            <div style="font-size:.78rem;color:var(--text-secondary);"><?= $label ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Active filter banner -->
<?php if (!empty($filterUser)): ?>
<div style="margin-bottom:16px;padding:10px 16px;border-radius:8px;background:rgba(37,211,102,.08);border:1px solid #25D366;display:flex;align-items:center;justify-content:space-between;font-size:.86rem;">
    <span>Filtering by user: <strong><?= htmlspecialchars($filterUser['name'], ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars($filterUser['email'], ENT_QUOTES, 'UTF-8') ?>)</span>
    <a href="/admin/whatsapp/api-keys" style="color:var(--red);text-decoration:none;font-weight:600;">Clear filter</a>
</div>
<?php endif; ?>

<!-- Generate API Key Modal -->
<div id="genKeyModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.7);align-items:center;justify-content:center;">
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:24px;width:min(480px,94vw);position:relative;">
        <button onclick="closeGenModal()" style="position:absolute;top:12px;right:14px;background:none;border:none;color:var(--text-secondary);font-size:1.3rem;cursor:pointer;line-height:1;">×</button>
        <h3 style="margin:0 0 16px;font-size:1rem;font-weight:700;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-key" style="color:#25D366;"></i> Generate API Key for User
        </h3>
        <div id="genKeyResult" style="display:none;margin-bottom:14px;padding:10px 14px;border-radius:8px;background:rgba(37,211,102,.08);border:1px solid #25D366;font-size:.82rem;color:#25D366;"></div>
        <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:5px;color:var(--text-secondary);">Search User</label>
        <input type="text" id="userSearch" placeholder="Search by name or email…" oninput="searchUsers(this.value)"
            style="width:100%;padding:9px 12px;border-radius:7px;border:1px solid var(--border-color);background:var(--bg-secondary);color:var(--text-primary);font-size:.83rem;margin-bottom:6px;box-sizing:border-box;">
        <select id="userSelect" size="5" style="width:100%;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:7px;color:var(--text-primary);font-size:.83rem;margin-bottom:14px;padding:4px;">
            <?php foreach ($allUsers ?? [] as $u): ?>
            <option value="<?= (int)$u['id'] ?>" data-label="<?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?>)">
                <?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?>
            </option>
            <?php endforeach; ?>
        </select>
        <div id="newGeneratedKey" style="display:none;margin-bottom:14px;">
            <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:5px;color:#25D366;">Generated Key — copy now, shown only once</label>
            <div style="display:flex;gap:8px;align-items:center;">
                <code id="newKeyVal" style="flex:1;background:rgba(0,0,0,.4);padding:8px 12px;border-radius:6px;font-size:.8rem;word-break:break-all;border:1px solid rgba(37,211,102,.3);color:#25D366;"></code>
                <button onclick="copyGenKey()" style="padding:7px 14px;background:#25D366;color:#fff;border:none;border-radius:6px;font-weight:700;font-size:.78rem;cursor:pointer;white-space:nowrap;">Copy</button>
            </div>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button onclick="closeGenModal()" style="padding:9px 18px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:7px;color:var(--text-secondary);font-size:.83rem;cursor:pointer;">Cancel</button>
            <button onclick="submitGenKey()" style="padding:9px 18px;background:#25D366;border:none;border-radius:7px;color:#fff;font-weight:700;font-size:.83rem;cursor:pointer;">Generate Key</button>
        </div>
    </div>
</div>

<!-- Per-user usage summary -->
<?php if (!empty($userUsage) && empty($filterUser)): ?>
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;margin-bottom:24px;overflow:hidden;">
    <div style="padding:14px 18px;border-bottom:1px solid var(--border-color);">
        <h3 style="margin:0;font-size:.95rem;font-weight:700;">API Usage by User <span style="font-size:.75rem;font-weight:400;color:var(--text-secondary);margin-left:6px;">(click user to filter)</span></h3>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.83rem;">
            <thead>
                <tr style="background:rgba(0,0,0,.2);border-bottom:1px solid var(--border-color);">
                    <th style="padding:10px 14px;text-align:left;color:var(--text-secondary);font-weight:600;">User</th>
                    <th style="padding:10px 14px;text-align:left;color:var(--text-secondary);font-weight:600;">Email</th>
                    <th style="padding:10px 14px;text-align:right;color:var(--text-secondary);font-weight:600;">Requests</th>
                    <th style="padding:10px 14px;text-align:right;color:var(--text-secondary);font-weight:600;">Success</th>
                    <th style="padding:10px 14px;text-align:right;color:var(--text-secondary);font-weight:600;">Errors</th>
                    <th style="padding:10px 14px;text-align:left;color:var(--text-secondary);font-weight:600;">Last Request</th>
                    <th style="padding:10px 14px;text-align:left;color:var(--text-secondary);font-weight:600;">Filter</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($userUsage as $uu): ?>
                <tr style="border-bottom:1px solid rgba(255,255,255,.05);">
                    <td style="padding:9px 14px;font-weight:600;"><?= htmlspecialchars($uu['user_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?></td>
                    <td style="padding:9px 14px;color:var(--text-secondary);"><?= htmlspecialchars($uu['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td style="padding:9px 14px;text-align:right;font-weight:700;color:var(--cyan);"><?= number_format((int)$uu['total_requests']) ?></td>
                    <td style="padding:9px 14px;text-align:right;color:#25D366;"><?= number_format((int)$uu['success_count']) ?></td>
                    <td style="padding:9px 14px;text-align:right;color:var(--red);"><?= number_format((int)$uu['error_count']) ?></td>
                    <td style="padding:9px 14px;color:var(--text-secondary);font-size:.78rem;">
                        <?= $uu['last_request'] ? date('M j, Y H:i', strtotime($uu['last_request'])) : '—' ?>
                    </td>
                    <td style="padding:9px 14px;">
                        <?php if ($uu['user_id']): ?>
                        <a href="?user_id=<?= (int)$uu['user_id'] ?>" style="font-size:.75rem;color:var(--cyan);text-decoration:none;">
                            <i class="fas fa-search"></i> Filter
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- All API Keys table -->
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;overflow:hidden;margin-bottom:28px;">
    <div style="padding:14px 18px;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <h3 style="margin:0;font-size:.95rem;font-weight:700;">
            <?= $filterUser ? 'API Keys for ' . htmlspecialchars($filterUser['name'], ENT_QUOTES, 'UTF-8') : 'All WhatsApp API Keys' ?>
        </h3>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <input type="text" id="searchKeys" placeholder="Search name / email…" oninput="filterKeys(this.value)"
                style="padding:7px 12px;border-radius:7px;border:1px solid var(--border-color);background:var(--bg-secondary);color:var(--text-primary);font-size:.82rem;width:200px;">
            <button onclick="openGenModal()" style="padding:7px 14px;background:#25D366;border:none;border-radius:7px;color:#fff;font-weight:700;font-size:.82rem;cursor:pointer;white-space:nowrap;">
                <i class="fas fa-plus"></i> Generate for User
            </button>
        </div>
    </div>
    <?php if (empty($keys)): ?>
    <div style="padding:40px;text-align:center;color:var(--text-secondary);">
        <i class="fas fa-key" style="font-size:2rem;margin-bottom:10px;display:block;opacity:.3;"></i>
        No WhatsApp API keys found. Users generate keys from the WhatsApp dashboard, or generate one above.
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table id="keysTable" style="width:100%;border-collapse:collapse;font-size:.83rem;">
            <thead>
                <tr style="background:rgba(0,0,0,.2);border-bottom:1px solid var(--border-color);">
                    <th style="padding:10px 14px;text-align:left;color:var(--text-secondary);font-weight:600;">User</th>
                    <th style="padding:10px 14px;text-align:left;color:var(--text-secondary);font-weight:600;">Key (masked)</th>
                    <th style="padding:10px 14px;text-align:left;color:var(--text-secondary);font-weight:600;">Status</th>
                    <th style="padding:10px 14px;text-align:left;color:var(--text-secondary);font-weight:600;">Created</th>
                    <th style="padding:10px 14px;text-align:left;color:var(--text-secondary);font-weight:600;">Last Used</th>
                    <th style="padding:10px 14px;text-align:left;color:var(--text-secondary);font-weight:600;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($keys as $k):
                    $isActive = ($k['status'] ?? '') === 'active';
                ?>
                <tr class="key-row" style="border-bottom:1px solid rgba(255,255,255,.05);<?= $isActive ? '' : 'opacity:.5;' ?>">
                    <td style="padding:9px 14px;">
                        <a href="?user_id=<?= (int)$k['user_id'] ?>" style="font-weight:600;color:var(--text-primary);text-decoration:none;"><?= htmlspecialchars($k['user_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></a>
                        <div style="font-size:.73rem;color:var(--text-secondary);"><?= htmlspecialchars($k['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                    </td>
                    <td style="padding:9px 14px;">
                        <code style="background:rgba(0,0,0,.3);padding:3px 8px;border-radius:5px;font-size:.75rem;">
                            <?= htmlspecialchars(substr($k['api_key'], 0, 10), ENT_QUOTES, 'UTF-8') ?>••••
                        </code>
                        <button onclick="filterByKeyId(<?= (int)$k['id'] ?>)"
                            title="Filter logs by this key"
                            style="margin-left:6px;padding:2px 8px;background:rgba(0,240,255,.08);border:1px solid rgba(0,240,255,.25);color:var(--cyan);border-radius:5px;font-size:.7rem;cursor:pointer;">
                            Logs
                        </button>
                    </td>
                    <td style="padding:9px 14px;">
                        <span style="font-size:.73rem;padding:3px 8px;border-radius:12px;
                            <?= $isActive
                                ? 'background:rgba(37,211,102,.12);color:#25D366;border:1px solid #25D366;'
                                : 'background:rgba(255,107,107,.12);color:var(--red);border:1px solid var(--red);' ?>">
                            <?= htmlspecialchars(ucfirst($k['status'] ?? 'unknown'), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td style="padding:9px 14px;color:var(--text-secondary);font-size:.78rem;">
                        <?= date('M j, Y', strtotime($k['created_at'])) ?>
                    </td>
                    <td style="padding:9px 14px;color:var(--text-secondary);font-size:.78rem;">
                        <?= $k['last_used_at'] ? date('M j, Y H:i', strtotime($k['last_used_at'])) : '—' ?>
                    </td>
                    <td style="padding:9px 14px;">
                        <?php if ($isActive): ?>
                        <button onclick="revokeKey(<?= (int)$k['id'] ?>, this)"
                            style="padding:4px 12px;background:rgba(255,107,107,.1);border:1px solid rgba(255,107,107,.4);color:var(--red);border-radius:6px;font-size:.75rem;cursor:pointer;">
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

<!-- Request Logs section -->
<?php
$recentLogs      = $recentLogs      ?? [];
$filterUserId    = $filterUserId    ?? null;
$filterKeyId     = $filterKeyId     ?? null;
$filterKeyPrefix = $filterKeyPrefix ?? '';
?>
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;overflow:hidden;">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--border-color);flex-wrap:wrap;gap:10px;">
        <h3 style="margin:0;font-size:.95rem;font-weight:700;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-list" style="color:#25D366;"></i>
            API Request Logs
            <?php if ($filterUserId || $filterKeyPrefix): ?>
                <span style="font-size:.75rem;font-weight:400;color:var(--cyan);">
                    (filtered<?= $filterUserId ? ' — user #' . (int)$filterUserId : '' ?><?= $filterKeyPrefix ? ' — key ' . htmlspecialchars($filterKeyPrefix, ENT_QUOTES, 'UTF-8') . '...' : '' ?>)
                </span>
            <?php endif; ?>
            <span style="font-size:.72rem;font-weight:400;color:var(--text-secondary);">(last 200)</span>
        </h3>
        <?php if ($filterUserId || $filterKeyPrefix): ?>
        <a href="/admin/whatsapp/api-keys" style="font-size:.8rem;color:var(--red);text-decoration:none;">Clear filter</a>
        <?php endif; ?>
    </div>
    <?php if (empty($recentLogs)): ?>
    <div style="padding:2rem;text-align:center;color:var(--text-secondary);font-size:.875rem;">
        No API request logs recorded yet. Requests are logged automatically when users call the WhatsApp API.
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.78rem;">
            <thead>
                <tr style="background:var(--bg-secondary);">
                    <th style="padding:.5rem .75rem;text-align:left;font-weight:600;color:var(--text-secondary);white-space:nowrap;">Time</th>
                    <th style="padding:.5rem .75rem;text-align:left;font-weight:600;color:var(--text-secondary);">User</th>
                    <th style="padding:.5rem .75rem;text-align:left;font-weight:600;color:var(--text-secondary);">Key Prefix</th>
                    <th style="padding:.5rem .75rem;text-align:left;font-weight:600;color:var(--text-secondary);">Endpoint</th>
                    <th style="padding:.5rem .75rem;text-align:left;font-weight:600;color:var(--text-secondary);">Method</th>
                    <th style="padding:.5rem .75rem;text-align:left;font-weight:600;color:var(--text-secondary);">Action</th>
                    <th style="padding:.5rem .75rem;text-align:center;font-weight:600;color:var(--text-secondary);">Status</th>
                    <th style="padding:.5rem .75rem;text-align:right;font-weight:600;color:var(--text-secondary);white-space:nowrap;">Latency</th>
                    <th style="padding:.5rem .75rem;text-align:left;font-weight:600;color:var(--text-secondary);">IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentLogs as $log):
                    $sc = (int)($log['status_code'] ?? 0);
                    $scColor = $sc >= 500 ? '#ef4444' : ($sc >= 400 ? '#f59e0b' : '#25D366');
                ?>
                <tr style="border-top:1px solid var(--border-color);">
                    <td style="padding:.45rem .75rem;white-space:nowrap;color:var(--text-secondary);"><?= htmlspecialchars($log['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td style="padding:.45rem .75rem;">
                        <a href="?user_id=<?= (int)$log['user_id'] ?>" style="color:#25D366;text-decoration:none;" title="Filter by user">
                            <?= htmlspecialchars($log['user_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <div style="font-size:.7rem;color:var(--text-secondary);"><?= htmlspecialchars($log['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                    </td>
                    <td style="padding:.45rem .75rem;font-family:monospace;color:var(--text-secondary);"><?= htmlspecialchars($log['api_key_prefix'] ?? '', ENT_QUOTES, 'UTF-8') ?>...</td>
                    <td style="padding:.45rem .75rem;font-family:monospace;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($log['endpoint'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($log['endpoint'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td style="padding:.45rem .75rem;font-family:monospace;color:#25D366;"><?= htmlspecialchars($log['method'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td style="padding:.45rem .75rem;color:var(--text-secondary);"><?= htmlspecialchars($log['action'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td style="padding:.45rem .75rem;text-align:center;font-weight:700;color:<?= $scColor ?>;"><?= $sc ?: '—' ?></td>
                    <td style="padding:.45rem .75rem;text-align:right;color:var(--text-secondary);"><?= number_format((int)($log['response_time'] ?? 0)) ?> ms</td>
                    <td style="padding:.45rem .75rem;font-family:monospace;color:var(--text-secondary);"><?= htmlspecialchars($log['ip_address'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
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

function filterByKeyId(keyId) {
    const params = new URLSearchParams(location.search);
    params.set('key_id', keyId);
    location.href = '/admin/whatsapp/api-keys?' + params.toString();
}

function revokeKey(keyId, btn) {
    if (!confirm('Revoke this API key? All requests using it will fail immediately.')) return;
    btn.disabled = true;
    btn.textContent = '…';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    fetch('/admin/whatsapp/api-keys/revoke', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: '_csrf_token=' + encodeURIComponent(csrf) + '&key_id=' + keyId
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) { location.reload(); }
        else { showToast(d.error || 'Failed to revoke.', 'error'); btn.disabled = false; btn.textContent = 'Revoke'; }
    })
    .catch(() => { showToast('Network error.', 'error'); btn.disabled = false; btn.textContent = 'Revoke'; });
}

function openGenModal() {
    document.getElementById('genKeyModal').style.display = 'flex';
    document.getElementById('newGeneratedKey').style.display = 'none';
    document.getElementById('genKeyResult').style.display = 'none';
}
function closeGenModal() { document.getElementById('genKeyModal').style.display = 'none'; }

function searchUsers(q) {
    q = q.toLowerCase();
    Array.from(document.getElementById('userSelect').options).forEach(opt => {
        opt.style.display = opt.getAttribute('data-label').toLowerCase().includes(q) ? '' : 'none';
    });
}

function submitGenKey() {
    const userId = document.getElementById('userSelect').value;
    if (!userId) { showToast('Please select a user.', 'error'); return; }
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const btn = document.querySelector('#genKeyModal button[onclick="submitGenKey()"]');
    btn.disabled = true;
    btn.textContent = 'Generating…';
    fetch('/admin/whatsapp/api-keys/generate', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: '_csrf_token=' + encodeURIComponent(csrf) + '&user_id=' + encodeURIComponent(userId)
    })
    .then(r => r.json())
    .then(d => {
        btn.disabled = false; btn.textContent = 'Generate Key';
        if (d.success) {
            document.getElementById('newKeyVal').textContent = d.api_key;
            document.getElementById('newGeneratedKey').style.display = 'block';
            const res = document.getElementById('genKeyResult');
            res.textContent = d.message || 'Key generated successfully.';
            res.style.display = 'block';
        } else {
            showToast(d.error || 'Failed to generate key.', 'error');
        }
    })
    .catch(() => { btn.disabled = false; btn.textContent = 'Generate Key'; showToast('Network error.', 'error'); });
}

function copyGenKey() {
    const val = document.getElementById('newKeyVal').textContent.trim();
    navigator.clipboard?.writeText(val).then(() => showToast('Copied!', 'success'))
        ?? showToast('Copy not supported.', 'error');
}

function showToast(msg, type) {
    const t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:99999;padding:10px 18px;border-radius:8px;font-size:.85rem;font-weight:600;pointer-events:none;'
        + (type === 'success'
            ? 'background:rgba(37,211,102,.15);border:1px solid #25D366;color:#25D366;'
            : 'background:rgba(255,107,107,.15);border:1px solid var(--red);color:var(--red);');
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}
</script>

<?php View::endSection(); ?>
