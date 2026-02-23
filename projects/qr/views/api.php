<?php
/**
 * QR Generator — API Access
 * Included via projects/qr/routes/web.php → QRApiUserController::index()
 * Wrapped in layout.php.
 */
use Core\Security;
use Core\Auth;

$csrfToken  = Security::generateCsrfToken();
$currentUser = Auth::user();
?>

<!-- ── Page heading ──────────────────────────────────────────────────────── -->
<div style="margin-bottom:24px;">
    <h1 style="font-size:1.5rem;font-weight:700;margin-bottom:6px;display:flex;align-items:center;gap:10px;">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
            <polyline points="16 18 22 12 16 6"/>
            <polyline points="8 6 2 12 8 18"/>
        </svg>
        QR API Access
    </h1>
    <p style="color:var(--text-secondary);font-size:.9rem;">
        Manage your API keys and integrate QR code generation into your own applications.
    </p>
</div>

<!-- Flash messages -->
<?php foreach (['success','error'] as $t):
    $msg = $_SESSION['flash_' . $t] ?? null;
    unset($_SESSION['flash_' . $t]);
    if (!$msg) continue; ?>
<div style="margin-bottom:14px;padding:10px 14px;border-radius:8px;font-size:.85rem;
    <?= $t === 'success' ? 'background:rgba(0,255,136,.1);border:1px solid var(--green);color:var(--green);'
                         : 'background:rgba(255,107,107,.1);border:1px solid var(--red);color:var(--red);' ?>">
    <?= $t === 'success' ? '✓' : '✗' ?> <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endforeach; ?>

<!-- New key reveal -->
<?php if (!empty($newKey)): ?>
<div style="margin-bottom:20px;padding:14px 18px;border-radius:10px;background:rgba(0,240,255,.07);border:1px solid var(--cyan);">
    <div style="font-size:.8rem;font-weight:700;color:var(--cyan);margin-bottom:8px;text-transform:uppercase;letter-spacing:.06em;">
        ⚡ Your new API key — copy it now, it won't be shown again
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <code id="newKeyCode" style="flex:1;background:rgba(0,0,0,.4);padding:10px 14px;border-radius:6px;font-size:.85rem;word-break:break-all;border:1px solid rgba(0,240,255,.3);"><?= htmlspecialchars($newKey, ENT_QUOTES, 'UTF-8') ?></code>
        <button onclick="copyKey('newKeyCode',this)" style="padding:8px 16px;background:var(--cyan);color:#000;border:none;border-radius:6px;font-weight:700;font-size:.8rem;cursor:pointer;white-space:nowrap;">Copy</button>
    </div>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

<!-- ── LEFT: API Keys ──────────────────────────────────────────────────── -->
<div>
    <!-- Generate key card -->
    <div class="card" style="border-radius:10px;border:1px solid var(--border-color);margin-bottom:20px;overflow:hidden;">
        <div style="padding:12px 16px;background:linear-gradient(135deg,rgba(0,240,255,.1),rgba(255,46,196,.1));border-bottom:1px solid var(--border-color);">
            <h3 style="margin:0;font-size:.9rem;font-weight:700;display:flex;align-items:center;gap:8px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                    <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                </svg>
                Generate New Key
            </h3>
        </div>
        <div style="padding:16px;">
            <form method="POST" action="/projects/qr/api">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:6px;color:var(--text-secondary);">Key Name / Description</label>
                <input type="text" name="name" required maxlength="80" placeholder="e.g. My App Integration"
                    style="width:100%;padding:9px 12px;border-radius:7px;border:1px solid var(--border-color);background:var(--bg-secondary);color:var(--text-primary);font-size:.85rem;margin-bottom:12px;">
                <p style="font-size:.75rem;color:var(--text-secondary);margin-bottom:12px;">
                    Keys are scoped to <code style="background:rgba(0,240,255,.1);padding:1px 5px;border-radius:4px;">qr:read</code>
                    <code style="background:rgba(0,240,255,.1);padding:1px 5px;border-radius:4px;">qr:write</code>
                    <code style="background:rgba(0,240,255,.1);padding:1px 5px;border-radius:4px;">qr:delete</code>
                    — limited to your own QR codes.
                </p>
                <button type="submit" style="width:100%;padding:10px;background:linear-gradient(135deg,var(--cyan),var(--purple));border:none;border-radius:8px;color:#000;font-weight:700;font-size:.85rem;cursor:pointer;">
                    Generate API Key
                </button>
            </form>
        </div>
    </div>

    <!-- Existing keys -->
    <div class="card" style="border-radius:10px;border:1px solid var(--border-color);overflow:hidden;">
        <div style="padding:12px 16px;background:linear-gradient(135deg,rgba(0,240,255,.1),rgba(255,46,196,.1));border-bottom:1px solid var(--border-color);">
            <h3 style="margin:0;font-size:.9rem;font-weight:700;display:flex;align-items:center;gap:8px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <line x1="9" y1="9" x2="15" y2="9"/>
                    <line x1="9" y1="15" x2="15" y2="15"/>
                </svg>
                Your API Keys (<?= count($keys) ?>/10)
            </h3>
        </div>
        <div style="padding:16px;">
            <?php if (empty($keys)): ?>
                <p style="text-align:center;color:var(--text-secondary);font-size:.85rem;padding:20px 0;">
                    No API keys yet. Generate your first key above.
                </p>
            <?php else: ?>
                <?php foreach ($keys as $k): ?>
                <div style="padding:12px;border-radius:8px;border:1px solid var(--border-color);margin-bottom:10px;<?= $k['is_active'] ? '' : 'opacity:.5;' ?>">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:8px;">
                        <div>
                            <div style="font-weight:700;font-size:.85rem;"><?= htmlspecialchars($k['name'], ENT_QUOTES, 'UTF-8') ?></div>
                            <div style="font-size:.75rem;color:var(--text-secondary);">
                                Created <?= date('M j, Y', strtotime($k['created_at'])) ?>
                                <?php if ($k['last_used_at']): ?>
                                 · Last used <?= date('M j, Y', strtotime($k['last_used_at'])) ?>
                                <?php endif; ?>
                                · <?= number_format((int)$k['request_count']) ?> requests
                            </div>
                        </div>
                        <span style="font-size:.72rem;padding:3px 8px;border-radius:12px;white-space:nowrap;<?= $k['is_active']
                            ? 'background:rgba(0,255,136,.1);color:var(--green);border:1px solid var(--green);'
                            : 'background:rgba(255,107,107,.1);color:var(--red);border:1px solid var(--red);' ?>">
                            <?= $k['is_active'] ? 'Active' : 'Revoked' ?>
                        </span>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <code id="key_<?= $k['id'] ?>" style="flex:1;background:rgba(0,0,0,.3);padding:6px 10px;border-radius:5px;font-size:.75rem;border:1px solid var(--border-color);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= substr(htmlspecialchars($k['api_key'], ENT_QUOTES, 'UTF-8'), 0, 12) ?>••••••••••••••••••••••••••
                        </code>
                        <?php if ($k['is_active']): ?>
                        <button onclick="copyKey('key_<?= $k['id'] ?>',this,'<?= htmlspecialchars($k['api_key'], ENT_QUOTES, 'UTF-8') ?>')"
                            style="padding:5px 10px;background:rgba(0,240,255,.1);border:1px solid rgba(0,240,255,.3);color:var(--cyan);border-radius:5px;font-size:.75rem;cursor:pointer;white-space:nowrap;">
                            Copy
                        </button>
                        <button onclick="revokeKey(<?= $k['id'] ?>,this)"
                            style="padding:5px 10px;background:rgba(255,107,107,.1);border:1px solid rgba(255,107,107,.3);color:var(--red);border-radius:5px;font-size:.75rem;cursor:pointer;white-space:nowrap;">
                            Revoke
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── RIGHT: API Documentation ─────────────────────────────────────────── -->
<div class="card" style="border-radius:10px;border:1px solid var(--border-color);overflow:hidden;">
    <div style="padding:12px 16px;background:linear-gradient(135deg,rgba(153,69,255,.15),rgba(0,240,255,.08));border-bottom:1px solid var(--border-color);">
        <h3 style="margin:0;font-size:.9rem;font-weight:700;display:flex;align-items:center;gap:8px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
            </svg>
            QR API Documentation
        </h3>
    </div>

    <div style="padding:16px;font-size:.82rem;max-height:580px;overflow-y:auto;">

        <!-- Base URL -->
        <div style="margin-bottom:14px;padding:10px;background:rgba(0,0,0,.3);border-radius:7px;border:1px solid var(--border-color);">
            <span style="color:var(--text-secondary);font-size:.75rem;font-weight:600;">BASE URL</span>
            <div style="display:flex;align-items:center;gap:8px;margin-top:4px;">
                <code id="baseUrlCode" style="flex:1;color:var(--cyan);word-break:break-all;"><?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?></code>
            </div>
        </div>

        <!-- Auth -->
        <div style="margin-bottom:16px;">
            <h4 style="font-size:.8rem;font-weight:700;color:var(--cyan);margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em;">Authentication</h4>
            <p style="color:var(--text-secondary);line-height:1.6;">Send your API key in the <code style="background:rgba(0,240,255,.1);padding:1px 5px;border-radius:3px;">Authorization</code> header:</p>
            <div style="position:relative;margin-top:6px;">
                <code id="authExample" style="display:block;background:rgba(0,0,0,.5);padding:8px 10px;border-radius:6px;border:1px solid var(--border-color);color:var(--green);white-space:pre-wrap;word-break:break-all;">Authorization: Bearer mmb_your_api_key_here</code>
                <button onclick="copyEl('authExample',this)" style="position:absolute;top:6px;right:6px;padding:3px 8px;font-size:.7rem;background:rgba(0,240,255,.15);border:1px solid rgba(0,240,255,.3);color:var(--cyan);border-radius:4px;cursor:pointer;">Copy</button>
            </div>
        </div>

        <!-- Endpoints table -->
        <h4 style="font-size:.8rem;font-weight:700;color:var(--cyan);margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em;">Endpoints</h4>
        <div style="overflow-x:auto;margin-bottom:16px;">
            <table style="width:100%;border-collapse:collapse;font-size:.78rem;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <th style="text-align:left;padding:6px 8px;color:var(--text-secondary);font-weight:600;">Method</th>
                        <th style="text-align:left;padding:6px 8px;color:var(--text-secondary);font-weight:600;">Endpoint</th>
                        <th style="text-align:left;padding:6px 8px;color:var(--text-secondary);font-weight:600;">Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $endpoints = [
                        ['GET',    '/api/qr',          'List your QR codes (paginated)'],
                        ['POST',   '/api/qr',          'Create a new QR code'],
                        ['GET',    '/api/qr/{code}',   'Get a single QR by short_code or id'],
                        ['DELETE', '/api/qr/{code}',   'Delete a QR code'],
                    ];
                    $methodColors = ['GET'=>'var(--green)','POST'=>'var(--cyan)','DELETE'=>'var(--red)','PUT'=>'var(--orange)'];
                    foreach ($endpoints as [$method, $path, $desc]):
                    ?>
                    <tr style="border-bottom:1px solid rgba(255,255,255,.05);">
                        <td style="padding:6px 8px;">
                            <span style="font-weight:700;color:<?= $methodColors[$method] ?? 'var(--text-primary)' ?>;"><?= $method ?></span>
                        </td>
                        <td style="padding:6px 8px;"><code style="background:rgba(0,0,0,.3);padding:2px 6px;border-radius:4px;color:var(--text-primary);"><?= htmlspecialchars($path, ENT_QUOTES, 'UTF-8') ?></code></td>
                        <td style="padding:6px 8px;color:var(--text-secondary);"><?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Curl examples -->
        <h4 style="font-size:.8rem;font-weight:700;color:var(--cyan);margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em;">Examples</h4>

        <?php
        $examples = [
            [
                'title' => 'List QR codes',
                'id'    => 'ex1',
                'code'  => "curl -H \"Authorization: Bearer YOUR_API_KEY\" \\\n  {$baseUrl}/api/qr?page=1&limit=20",
            ],
            [
                'title' => 'Create a URL QR code',
                'id'    => 'ex2',
                'code'  => "curl -X POST -H \"Authorization: Bearer YOUR_API_KEY\" \\\n  -H \"Content-Type: application/json\" \\\n  -d '{\"type\":\"url\",\"content\":\"https://example.com\",\"label\":\"My QR\"}' \\\n  {$baseUrl}/api/qr",
            ],
            [
                'title' => 'Create a dynamic QR code',
                'id'    => 'ex3',
                'code'  => "curl -X POST -H \"Authorization: Bearer YOUR_API_KEY\" \\\n  -H \"Content-Type: application/json\" \\\n  -d '{\"type\":\"url\",\"content\":\"https://example.com\",\"dynamic\":true}' \\\n  {$baseUrl}/api/qr",
            ],
            [
                'title' => 'Delete a QR code',
                'id'    => 'ex4',
                'code'  => "curl -X DELETE -H \"Authorization: Bearer YOUR_API_KEY\" \\\n  {$baseUrl}/api/qr/abc123",
            ],
        ];
        foreach ($examples as $ex): ?>
        <div style="margin-bottom:12px;">
            <div style="font-size:.75rem;font-weight:600;color:var(--text-secondary);margin-bottom:4px;"><?= htmlspecialchars($ex['title'], ENT_QUOTES, 'UTF-8') ?></div>
            <div style="position:relative;">
                <code id="<?= $ex['id'] ?>" style="display:block;background:rgba(0,0,0,.5);padding:8px 10px;border-radius:6px;border:1px solid var(--border-color);color:var(--green);white-space:pre;font-size:.73rem;overflow-x:auto;"><?= htmlspecialchars($ex['code'], ENT_QUOTES, 'UTF-8') ?></code>
                <button onclick="copyEl('<?= $ex['id'] ?>',this)" style="position:absolute;top:6px;right:6px;padding:3px 8px;font-size:.7rem;background:rgba(0,240,255,.15);border:1px solid rgba(0,240,255,.3);color:var(--cyan);border-radius:4px;cursor:pointer;">Copy</button>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Response format -->
        <h4 style="font-size:.8rem;font-weight:700;color:var(--cyan);margin:16px 0 8px;text-transform:uppercase;letter-spacing:.05em;">Response Format</h4>
        <div style="position:relative;">
            <code id="respEx" style="display:block;background:rgba(0,0,0,.5);padding:8px 10px;border-radius:6px;border:1px solid var(--border-color);color:var(--green);white-space:pre;font-size:.73rem;">// Success
{"success":true,"data":{...}}

// Error
{"success":false,"error":"message"}</code>
            <button onclick="copyEl('respEx',this)" style="position:absolute;top:6px;right:6px;padding:3px 8px;font-size:.7rem;background:rgba(0,240,255,.15);border:1px solid rgba(0,240,255,.3);color:var(--cyan);border-radius:4px;cursor:pointer;">Copy</button>
        </div>

        <!-- POST body fields -->
        <h4 style="font-size:.8rem;font-weight:700;color:var(--cyan);margin:16px 0 8px;text-transform:uppercase;letter-spacing:.05em;">POST /api/qr — Body Fields</h4>
        <table style="width:100%;border-collapse:collapse;font-size:.77rem;">
            <thead>
                <tr style="border-bottom:1px solid var(--border-color);">
                    <th style="text-align:left;padding:5px 8px;color:var(--text-secondary);">Field</th>
                    <th style="text-align:left;padding:5px 8px;color:var(--text-secondary);">Type</th>
                    <th style="text-align:left;padding:5px 8px;color:var(--text-secondary);">Description</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $fields = [
                    ['content',          'string', 'Required. QR content (URL, text, etc.)'],
                    ['type',             'string', 'QR type: url, text, email, phone, sms, wifi, vcard, location, whatsapp, social, app_store, crypto, menu (default: url)'],
                    ['dynamic',          'bool',   'true = dynamic QR (requires plan feature)'],
                    ['label',            'string', 'Optional label / note for this QR'],
                    ['size',             'int',    'Size in pixels (100–1000, default 300)'],
                    ['fg_color',         'string', 'Foreground colour hex (default #000000)'],
                    ['bg_color',         'string', 'Background colour hex (default #FFFFFF)'],
                    ['error_correction', 'string', 'L / M / Q / H (default M)'],
                ];
                foreach ($fields as [$f,$t,$d]): ?>
                <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                    <td style="padding:5px 8px;"><code style="background:rgba(0,0,0,.3);padding:1px 5px;border-radius:3px;"><?= $f ?></code></td>
                    <td style="padding:5px 8px;color:var(--orange);"><?= $t ?></td>
                    <td style="padding:5px 8px;color:var(--text-secondary);"><?= $d ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div><!-- /doc scroll area -->
</div>

</div><!-- /grid -->

<script>
function copyKey(elId, btn, rawVal) {
    const text = rawVal || document.getElementById(elId).textContent.trim();
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.textContent;
        btn.textContent = 'Copied!';
        setTimeout(() => { btn.textContent = orig; }, 2000);
    });
}
function copyEl(elId, btn) {
    const text = document.getElementById(elId).textContent.trim();
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.textContent;
        btn.textContent = '✓';
        setTimeout(() => { btn.textContent = orig; }, 2000);
    });
}
function revokeKey(keyId, btn) {
    if (!confirm('Revoke this API key? Any applications using it will lose access immediately.')) return;
    btn.disabled = true;
    btn.textContent = 'Revoking…';
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    fetch('/projects/qr/api/revoke', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: '_csrf_token=' + encodeURIComponent(csrf) + '&key_id=' + keyId
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            location.reload();
        } else {
            alert(d.error || 'Failed to revoke key.');
            btn.disabled = false;
            btn.textContent = 'Revoke';
        }
    })
    .catch(() => {
        alert('Network error. Please try again.');
        btn.disabled = false;
        btn.textContent = 'Revoke';
    });
}
</script>

<?php if (isset($title)) { /* Suppress unused-variable warning */ } ?>
