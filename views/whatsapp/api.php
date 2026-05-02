<?php
/**
 * WhatsApp — API & Analytics
 * Rendered via View::render('whatsapp/api') → wrapped in projects/whatsapp/views/layouts/app.php
 */
use Core\Security;
use Core\Auth;

$csrfToken      = Security::generateCsrfToken();
$currentUser    = $user ?? Auth::user();
$totalRequests  = $totalRequests  ?? 0;
$requestsToday  = $requestsToday  ?? 0;
$lastRequestAt  = $lastRequestAt  ?? null;
$dailyUsage     = $dailyUsage     ?? [];
$endpointStats  = $endpointStats  ?? [];
$recentLogs     = $recentLogs     ?? [];
$keys           = $keys           ?? [];
$activeKey      = $activeKey      ?? null;
$newKey         = $newKey         ?? null;
$baseUrl        = $baseUrl        ?? '';
$apiEndpoints   = $apiEndpoints   ?? [];
?>
<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('whatsapp:app'); ?>

<?php View::section('content'); ?>

<style>
/* ── API & Analytics page styles ───────────────────────────────────────── */
.wa-api-tabs          { display:flex;gap:0;border-bottom:2px solid var(--border-color);margin-bottom:24px; }
.wa-api-tab           { padding:10px 22px;font-size:.86rem;font-weight:600;cursor:pointer;border:none;
                        background:none;color:var(--text-secondary);border-bottom:2px solid transparent;
                        margin-bottom:-2px;transition:.15s; }
.wa-api-tab.active    { color:#25D366;border-bottom-color:#25D366; }
.wa-api-panel         { display:none; }
.wa-api-panel.active  { display:block; }
.wa-stat-row          { display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px; }
@media(max-width:640px){ .wa-stat-row{grid-template-columns:1fr 1fr;} }
.wa-stat-card         { background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:10px;padding:16px;text-align:center; }
.wa-stat-card .val    { font-size:1.6rem;font-weight:800;color:#25D366;line-height:1; }
.wa-stat-card .lbl    { font-size:.72rem;color:var(--text-secondary);margin-top:4px; }
.wa-card              { background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:10px;overflow:hidden;margin-bottom:20px; }
.wa-card-head         { padding:12px 16px;background:rgba(37,211,102,.07);border-bottom:1px solid var(--border-color);
                        font-size:.9rem;font-weight:700;display:flex;align-items:center;gap:8px; }
.wa-card-body         { padding:16px; }
.spark-bar-wrap       { display:flex;align-items:flex-end;gap:3px;height:60px;margin-top:8px; }
.spark-bar            { flex:1;background:#25D366;border-radius:3px 3px 0 0;opacity:.65;min-height:2px; }
.spark-bar:hover      { opacity:1; }
.spark-labels         { display:flex;justify-content:space-between;font-size:.65rem;color:var(--text-secondary);margin-top:4px; }
.wa-key-box           { display:flex;align-items:center;gap:10px;background:rgba(0,0,0,.35);
                        border:1px solid rgba(37,211,102,.3);border-radius:8px;padding:10px 14px; }
.wa-key-code          { flex:1;font-family:monospace;font-size:.8rem;word-break:break-all;color:#25D366; }
.wa-btn               { display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:8px;
                        font-size:.84rem;font-weight:600;cursor:pointer;border:none;transition:.15s; }
.wa-btn-green         { background:#25D366;color:#000; }
.wa-btn-green:hover   { background:#1ebe59; }
.wa-btn-danger        { background:rgba(255,107,107,.15);border:1px solid rgba(255,107,107,.4);color:#ff6b6b; }
.wa-btn-danger:hover  { background:rgba(255,107,107,.25); }
.wa-table             { width:100%;border-collapse:collapse;font-size:.82rem; }
.wa-table th          { text-align:left;padding:8px;color:var(--text-secondary);font-weight:600;border-bottom:1px solid var(--border-color); }
.wa-table td          { padding:8px;border-bottom:1px solid rgba(255,255,255,.04); }
.method-badge         { display:inline-block;padding:2px 7px;border-radius:4px;font-size:.72rem;font-weight:700;font-family:monospace; }
.mb-get               { background:rgba(0,136,204,.2);color:#0088cc; }
.mb-post              { background:rgba(37,211,102,.2);color:#25D366; }
#wa-api-toast         { display:none;position:fixed;bottom:24px;right:24px;z-index:99999;
                        padding:10px 18px;border-radius:8px;font-size:.85rem;font-weight:600;pointer-events:none; }
</style>

<div id="wa-api-toast"></div>

<div style="max-width:1100px;margin:0 auto;">

<!-- ── Page Heading ───────────────────────────────────────────────────────── -->
<div style="margin-bottom:24px;">
    <h1 style="font-size:1.5rem;font-weight:700;display:flex;align-items:center;gap:10px;">
        <i class="fas fa-chart-line" style="color:#25D366;"></i>
        WhatsApp API &amp; Analytics
    </h1>
    <p style="color:var(--text-secondary);font-size:.9rem;">
        Manage your API key and view request analytics for the WhatsApp automation API.
    </p>
</div>

<!-- Flash messages -->
<?php foreach (['success','error'] as $t):
    $msg = $_SESSION['flash_' . $t] ?? null;
    unset($_SESSION['flash_' . $t]);
    if (!$msg) continue; ?>
<div style="margin-bottom:14px;padding:10px 14px;border-radius:8px;font-size:.85rem;
    <?= $t === 'success' ? 'background:rgba(37,211,102,.1);border:1px solid #25D366;color:#25D366;'
                         : 'background:rgba(255,107,107,.1);border:1px solid #ff6b6b;color:#ff6b6b;' ?>">
    <?= $t === 'success' ? '✓' : '✗' ?> <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endforeach; ?>

<!-- New key reveal -->
<?php if (!empty($newKey)): ?>
<div style="margin-bottom:20px;padding:14px 18px;border-radius:10px;background:rgba(37,211,102,.07);border:1px solid #25D366;">
    <div style="font-size:.8rem;font-weight:700;color:#25D366;margin-bottom:8px;text-transform:uppercase;letter-spacing:.06em;">
        ⚡ Your new API key — copy it now, it won't be shown again
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <code id="newWaKeyCode" style="flex:1;background:rgba(0,0,0,.4);padding:10px 14px;border-radius:6px;font-size:.85rem;word-break:break-all;border:1px solid rgba(37,211,102,.3);color:#25D366;"><?= htmlspecialchars($newKey, ENT_QUOTES, 'UTF-8') ?></code>
        <button onclick="waCopyEl('newWaKeyCode',this)" class="wa-btn wa-btn-green">Copy</button>
    </div>
</div>
<?php endif; ?>

<!-- ── Tabs ───────────────────────────────────────────────────────────────── -->
<div class="wa-api-tabs">
    <button class="wa-api-tab active" onclick="waShowTab('wa-tab-keys',this)">🔑 API Key</button>
    <button class="wa-api-tab"        onclick="waShowTab('wa-tab-analytics',this)">📊 Usage &amp; Analytics</button>
    <button class="wa-api-tab"        onclick="waShowTab('wa-tab-docs',this)">📖 API Docs</button>
</div>

<!-- ═══════════ TAB 1: API Key ═══════════════════════════════════════════ -->
<div id="wa-tab-keys" class="wa-api-panel active">

    <!-- Active key display -->
    <div class="wa-card">
        <div class="wa-card-head"><i class="fas fa-key" style="color:#25D366;"></i> Your API Key</div>
        <div class="wa-card-body">
            <?php if ($activeKey): ?>
            <p style="font-size:.84rem;color:var(--text-secondary);margin-bottom:12px;">
                Use this key in the <code style="background:rgba(37,211,102,.1);padding:1px 5px;border-radius:3px;">X-Api-Key</code> header for all API requests.
            </p>
            <div class="wa-key-box">
                <code class="wa-key-code" id="activeKeyCode"><?= htmlspecialchars(substr($activeKey['api_key'], 0, 12) . str_repeat('•', 48), ENT_QUOTES, 'UTF-8') ?></code>
                <button onclick="toggleKeyVisibility(this, <?= htmlspecialchars(json_encode($activeKey['api_key']), ENT_QUOTES, 'UTF-8') ?>)" class="wa-btn wa-btn-green" style="white-space:nowrap;">Show</button>
                <button onclick="waCopyEl('activeKeyCode', this, <?= htmlspecialchars(json_encode($activeKey['api_key']), ENT_QUOTES, 'UTF-8') ?>)" class="wa-btn wa-btn-green" style="white-space:nowrap;">Copy</button>
            </div>
            <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap;">
                <button onclick="waGenerateKey(false)" class="wa-btn wa-btn-danger">
                    <i class="fas fa-sync-alt"></i> Regenerate Key
                </button>
                <button onclick="waRevokeKey()" class="wa-btn wa-btn-danger">
                    <i class="fas fa-ban"></i> Revoke Key
                </button>
            </div>
            <?php else: ?>
            <p style="color:var(--text-secondary);font-size:.85rem;margin-bottom:16px;">No active API key. Generate one to start using the WhatsApp API.</p>
            <button onclick="waGenerateKey(true)" class="wa-btn wa-btn-green">
                <i class="fas fa-plus"></i> Generate API Key
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Key history -->
    <?php if (count($keys) > 1): ?>
    <div class="wa-card">
        <div class="wa-card-head"><i class="fas fa-history" style="color:var(--text-secondary);"></i> Key History</div>
        <div class="wa-card-body" style="padding:0;">
            <table class="wa-table">
                <thead>
                    <tr>
                        <th style="padding:10px 16px;">Created</th>
                        <th style="padding:10px 16px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($keys as $k): ?>
                    <tr>
                        <td style="padding:10px 16px;color:var(--text-secondary);"><?= date('d M Y H:i', strtotime($k['created_at'])) ?></td>
                        <td style="padding:10px 16px;">
                            <?php if ($k['status'] === 'active'): ?>
                                <span style="background:rgba(37,211,102,.12);border:1px solid #25D366;color:#25D366;padding:2px 8px;border-radius:10px;font-size:.72rem;font-weight:700;">Active</span>
                            <?php else: ?>
                                <span style="background:rgba(100,100,100,.15);border:1px solid var(--border-color);color:var(--text-secondary);padding:2px 8px;border-radius:10px;font-size:.72rem;font-weight:700;">Inactive</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div><!-- /wa-tab-keys -->

<!-- ═══════════ TAB 2: Usage & Analytics ══════════════════════════════════ -->
<div id="wa-tab-analytics" class="wa-api-panel">

    <!-- Summary stats -->
    <div class="wa-stat-row">
        <div class="wa-stat-card">
            <div class="val"><?= number_format($totalRequests) ?></div>
            <div class="lbl">Total Requests</div>
        </div>
        <div class="wa-stat-card">
            <div class="val"><?= number_format($requestsToday) ?></div>
            <div class="lbl">Requests Today</div>
        </div>
        <div class="wa-stat-card">
            <div class="val"><?= $lastRequestAt ? date('d M', strtotime($lastRequestAt)) : '—' ?></div>
            <div class="lbl">Last Request</div>
        </div>
    </div>

    <!-- Daily chart -->
    <div class="wa-card">
        <div class="wa-card-head"><i class="fas fa-chart-bar" style="color:#25D366;"></i> API Requests — Last 14 Days</div>
        <div class="wa-card-body">
            <?php
            $days14 = [];
            for ($i = 13; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-{$i} days"));
                $days14[$d] = $dailyUsage[$d] ?? 0;
            }
            $maxVal = max(array_values($days14) ?: [1]);
            if ($maxVal === 0) $maxVal = 1;
            ?>
            <?php if (array_sum($days14) === 0): ?>
                <p style="color:var(--text-secondary);font-size:.85rem;text-align:center;padding:20px 0;">
                    No API requests in the last 14 days.
                </p>
            <?php else: ?>
            <div class="spark-bar-wrap">
                <?php foreach ($days14 as $d => $cnt): ?>
                <div class="spark-bar" style="height:<?= max(2, round(($cnt / $maxVal) * 100)) ?>%;" title="<?= $d ?>: <?= $cnt ?> requests"></div>
                <?php endforeach; ?>
            </div>
            <div class="spark-labels">
                <span><?= date('M d', strtotime('-13 days')) ?></span>
                <span>Today</span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Endpoint breakdown -->
    <?php if (!empty($endpointStats)): ?>
    <div class="wa-card">
        <div class="wa-card-head"><i class="fas fa-sitemap" style="color:#25D366;"></i> Requests by Endpoint</div>
        <div class="wa-card-body" style="padding:0;">
            <table class="wa-table">
                <thead>
                    <tr>
                        <th style="padding:10px 16px;">Endpoint</th>
                        <th style="text-align:right;padding:10px 16px;">Requests</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($endpointStats as $row): ?>
                    <tr>
                        <td style="padding:10px 16px;font-family:monospace;font-size:.8rem;"><?= htmlspecialchars($row['endpoint'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td style="padding:10px 16px;text-align:right;color:#25D366;font-weight:700;"><?= number_format((int) $row['cnt']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent logs -->
    <div class="wa-card">
        <div class="wa-card-head"><i class="fas fa-list" style="color:#25D366;"></i> Recent API Activity</div>
        <div class="wa-card-body" style="padding:0;">
            <?php if (empty($recentLogs)): ?>
                <p style="color:var(--text-secondary);font-size:.85rem;text-align:center;padding:20px;">No API requests recorded yet.</p>
            <?php else: ?>
            <table class="wa-table">
                <thead>
                    <tr>
                        <th style="padding:10px 16px;">Method</th>
                        <th style="padding:10px 16px;">Endpoint</th>
                        <th style="padding:10px 16px;">IP</th>
                        <th style="padding:10px 16px;">Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentLogs as $log): ?>
                    <tr>
                        <td style="padding:10px 16px;">
                            <span class="method-badge <?= strtolower($log['method']) === 'get' ? 'mb-get' : 'mb-post' ?>"><?= htmlspecialchars(strtoupper($log['method']), ENT_QUOTES, 'UTF-8') ?></span>
                        </td>
                        <td style="padding:10px 16px;font-family:monospace;font-size:.78rem;"><?= htmlspecialchars($log['endpoint'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td style="padding:10px 16px;color:var(--text-secondary);font-size:.78rem;"><?= htmlspecialchars($log['ip_address'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td style="padding:10px 16px;color:var(--text-secondary);font-size:.78rem;"><?= date('d M H:i', strtotime($log['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

</div><!-- /wa-tab-analytics -->

<!-- ═══════════ TAB 3: API Docs ══════════════════════════════════════════ -->
<div id="wa-tab-docs" class="wa-api-panel">

    <div class="wa-card">
        <div class="wa-card-head"><i class="fas fa-book" style="color:#25D366;"></i> WhatsApp API Documentation</div>
        <div class="wa-card-body">

            <!-- Auth -->
            <div style="margin-bottom:18px;">
                <h4 style="font-size:.85rem;font-weight:700;color:#25D366;margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em;">Authentication</h4>
                <p style="font-size:.84rem;color:var(--text-secondary);margin-bottom:8px;line-height:1.6;">
                    Pass your API key in the <code style="background:rgba(37,211,102,.1);padding:1px 5px;border-radius:3px;">X-Api-Key</code> header:
                </p>
                <div style="position:relative;">
                    <code id="waAuthEx" style="display:block;background:rgba(0,0,0,.5);padding:8px 12px;border-radius:6px;border:1px solid var(--border-color);color:#25D366;white-space:pre;font-size:.8rem;">X-Api-Key: your_whapi_key_here</code>
                    <button onclick="waCopyEl('waAuthEx',this)" style="position:absolute;top:6px;right:6px;padding:3px 8px;font-size:.7rem;background:rgba(37,211,102,.15);border:1px solid rgba(37,211,102,.3);color:#25D366;border-radius:4px;cursor:pointer;">Copy</button>
                </div>
            </div>

            <!-- Endpoints -->
            <h4 style="font-size:.85rem;font-weight:700;color:#25D366;margin-bottom:10px;text-transform:uppercase;letter-spacing:.05em;">Endpoints</h4>
            <div style="overflow-x:auto;margin-bottom:18px;">
                <table class="wa-table">
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th>Endpoint</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($apiEndpoints as $ep): ?>
                        <tr>
                            <td><span class="method-badge <?= strtolower($ep['method']) === 'get' ? 'mb-get' : 'mb-post' ?>"><?= htmlspecialchars($ep['method'], ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td><code style="background:rgba(0,0,0,.3);padding:2px 6px;border-radius:4px;font-size:.78rem;"><?= htmlspecialchars($ep['path'], ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td style="color:var(--text-secondary);"><?= htmlspecialchars($ep['desc'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Examples -->
            <h4 style="font-size:.85rem;font-weight:700;color:#25D366;margin-bottom:10px;text-transform:uppercase;letter-spacing:.05em;">Example: Send Message</h4>
            <?php $sendExample = "curl -X POST \\\n  -H \"X-Api-Key: YOUR_KEY\" \\\n  -H \"Content-Type: application/json\" \\\n  -d '{\"session_id\":1,\"recipient\":\"+1234567890\",\"message\":\"Hello!\"}' \\\n  {$baseUrl}/projects/whatsapp/api/send-message"; ?>
            <div style="position:relative;margin-bottom:16px;">
                <code id="waSendEx" style="display:block;background:rgba(0,0,0,.5);padding:10px 12px;border-radius:6px;border:1px solid var(--border-color);color:#25D366;white-space:pre;font-size:.78rem;overflow-x:auto;"><?= htmlspecialchars($sendExample, ENT_QUOTES, 'UTF-8') ?></code>
                <button onclick="waCopyEl('waSendEx',this)" style="position:absolute;top:6px;right:6px;padding:3px 8px;font-size:.7rem;background:rgba(37,211,102,.15);border:1px solid rgba(37,211,102,.3);color:#25D366;border-radius:4px;cursor:pointer;">Copy</button>
            </div>

            <h4 style="font-size:.85rem;font-weight:700;color:#25D366;margin-bottom:10px;text-transform:uppercase;letter-spacing:.05em;">Response Format</h4>
            <code style="display:block;background:rgba(0,0,0,.5);padding:10px 12px;border-radius:6px;border:1px solid var(--border-color);color:#25D366;white-space:pre;font-size:.78rem;">// Success
{"success":true,"data":{...}}

// Error
{"success":false,"error":"description"}</code>

        </div>
    </div>

</div><!-- /wa-tab-docs -->

</div><!-- /max-width container -->

<script>
function waShowTab(id, btn) {
    document.querySelectorAll('.wa-api-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.wa-api-tab').forEach(b => b.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    btn.classList.add('active');
}
function waToast(msg, type) {
    const t = document.getElementById('wa-api-toast');
    t.textContent = msg;
    t.style.cssText = 'display:block;position:fixed;bottom:24px;right:24px;z-index:99999;'
        + 'padding:10px 18px;border-radius:8px;font-size:.85rem;font-weight:600;pointer-events:none;'
        + (type === 'success'
            ? 'background:rgba(37,211,102,.15);border:1px solid #25D366;color:#25D366;'
            : 'background:rgba(255,107,107,.15);border:1px solid #ff6b6b;color:#ff6b6b;');
    clearTimeout(t._t);
    t._t = setTimeout(() => { t.style.display = 'none'; }, 3500);
}
function waCopyEl(id, btn, rawVal) {
    const text = rawVal || document.getElementById(id).textContent.trim();
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.textContent;
        btn.textContent = '✓ Copied';
        setTimeout(() => btn.textContent = orig, 2000);
    }).catch(() => waToast('Copy failed — please copy manually.', 'error'));
}
function toggleKeyVisibility(btn, rawKey) {
    const code = document.getElementById('activeKeyCode');
    if (btn.textContent === 'Show') {
        code.textContent = rawKey;
        btn.textContent = 'Hide';
    } else {
        code.textContent = rawKey.substring(0, 12) + '•'.repeat(48);
        btn.textContent = 'Show';
    }
}
function waGenerateKey(isFirst) {
    if (!isFirst && !confirm('This will invalidate your current key. Continue?')) return;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    fetch('/projects/whatsapp/api/generate', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: '_csrf_token=' + encodeURIComponent(csrf)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) { location.reload(); }
        else { waToast(d.error || 'Failed to generate key.', 'error'); }
    })
    .catch(() => waToast('Network error. Please try again.', 'error'));
}
function waRevokeKey() {
    if (!confirm('Revoke your API key? All API requests using it will stop working.')) return;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    fetch('/projects/whatsapp/api/revoke', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: '_csrf_token=' + encodeURIComponent(csrf)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) { location.reload(); }
        else { waToast(d.error || 'Failed to revoke key.', 'error'); }
    })
    .catch(() => waToast('Network error. Please try again.', 'error'));
}
</script>

<?php View::endSection(); ?>
