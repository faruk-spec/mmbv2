<?php
$csrfToken = \Core\Security::generateCsrfToken();
$ws = $widgetSettings ?? [];
ob_start();
?>
<div style="margin-bottom:1.2rem;">
    <h2 style="margin:0;font-size:1.25rem;"><i class="fas fa-plug" style="color:var(--hp-primary);margin-right:.4rem;"></i> Integrations</h2>
</div>

<!-- API Keys -->
<div class="card" style="margin-bottom:1.2rem;">
    <h3 style="margin:0 0 .75rem;font-size:1rem;"><i class="fas fa-key" style="margin-right:.4rem;color:var(--hp-accent);"></i> API Keys</h3>
    <form method="POST" action="/projects/helpdeskpro/integrations/api-keys/create" style="display:flex;gap:.6rem;align-items:flex-end;flex-wrap:wrap;margin-bottom:1rem;">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <div style="flex:1;min-width:180px;">
            <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Key Name *</label>
            <input type="text" name="name" placeholder="e.g. My Integration" required maxlength="100">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Generate Key</button>
    </form>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Key (masked)</th>
                <th>Last Used</th>
                <th>Status</th>
                <th>Revoke</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($apiKeys)): ?>
            <tr><td colspan="5" style="color:var(--text-secondary);text-align:center;padding:1rem;">No API keys yet.</td></tr>
        <?php else: ?>
            <?php foreach ($apiKeys as $key): ?>
            <tr>
                <td><strong><?= htmlspecialchars($key['name']) ?></strong></td>
                <td style="font-family:monospace;font-size:.82rem;color:var(--text-secondary);">
                    <?= htmlspecialchars(substr((string)$key['api_key'], 0, 8)) ?>••••••••••••••••••••<?= htmlspecialchars(substr((string)$key['api_key'], -4)) ?>
                </td>
                <td style="color:var(--text-secondary);font-size:.82rem;"><?= !empty($key['last_used_at']) ? htmlspecialchars(date('M d, Y', strtotime($key['last_used_at']))) : '—' ?></td>
                <td>
                    <span class="badge" style="<?= $key['is_active'] ? 'background:rgba(16,185,129,.12);color:#34d399;' : 'background:rgba(107,114,128,.12);color:#9ca3af;' ?>">
                        <?= $key['is_active'] ? 'Active' : 'Revoked' ?>
                    </span>
                </td>
                <td>
                    <?php if ($key['is_active']): ?>
                    <form method="POST" action="/projects/helpdeskpro/integrations/api-keys/revoke/<?= (int)$key['id'] ?>" onsubmit="return confirm('Revoke key?');" style="display:inline;">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <button type="submit" class="btn" style="background:rgba(239,68,68,.12);color:#f87171;border-color:rgba(239,68,68,.3);padding:.25rem .5rem;font-size:.8rem;"><i class="fas fa-ban"></i> Revoke</button>
                    </form>
                    <?php else: ?>
                    <span style="color:var(--text-secondary);font-size:.82rem;">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Widget Config -->
<div class="card" style="margin-bottom:1.2rem;">
    <h3 style="margin:0 0 .75rem;font-size:1rem;"><i class="fas fa-comment-dots" style="margin-right:.4rem;color:var(--hp-accent);"></i> Chat Widget</h3>
    <form method="POST" action="/projects/helpdeskpro/integrations/widget/save">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <div class="grid g2" style="margin-bottom:.75rem;">
            <div>
                <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Widget Title</label>
                <input type="text" name="widget_title" value="<?= htmlspecialchars($ws['widget_title'] ?? 'Support') ?>" maxlength="100">
            </div>
            <div>
                <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Greeting Text</label>
                <input type="text" name="greeting_text" value="<?= htmlspecialchars($ws['greeting_text'] ?? 'Hi! How can we help you today?') ?>" maxlength="255">
            </div>
            <div>
                <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Primary Color</label>
                <div style="display:flex;gap:.5rem;align-items:center;">
                    <input type="color" name="primary_color" value="<?= htmlspecialchars($ws['primary_color'] ?? '#3b82f6') ?>" style="width:3rem;padding:.15rem;height:2.2rem;cursor:pointer;flex:none;">
                    <input type="text" name="primary_color_text" value="<?= htmlspecialchars($ws['primary_color'] ?? '#3b82f6') ?>" style="font-family:monospace;" placeholder="#3b82f6">
                </div>
            </div>
            <div>
                <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Position</label>
                <select name="position">
                    <option value="bottom-right" <?= ($ws['position'] ?? 'bottom-right') === 'bottom-right' ? 'selected' : '' ?>>Bottom Right</option>
                    <option value="bottom-left" <?= ($ws['position'] ?? '') === 'bottom-left' ? 'selected' : '' ?>>Bottom Left</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
    </form>
    <div style="margin-top:1rem;padding:.75rem;background:rgba(255,255,255,.04);border-radius:.5rem;border:1px solid var(--border);">
        <p style="margin:0 0 .4rem;font-size:.82rem;color:var(--text-secondary);">Embed Code</p>
        <code style="font-size:.8rem;color:var(--hp-accent);word-break:break-all;">
            &lt;script src="<?= htmlspecialchars((defined('APP_URL') ? APP_URL : '') . '/projects/helpdeskpro/widget.js') ?>" data-position="<?= htmlspecialchars($ws['position'] ?? 'bottom-right') ?>" data-color="<?= htmlspecialchars($ws['primary_color'] ?? '#3b82f6') ?>"&gt;&lt;/script&gt;
        </code>
    </div>
</div>

<!-- Webhooks -->
<div class="card">
    <h3 style="margin:0 0 .75rem;font-size:1rem;"><i class="fas fa-webhook" style="margin-right:.4rem;color:var(--hp-accent);"></i> Webhooks</h3>
    <form method="POST" action="/projects/helpdeskpro/integrations/webhooks/create" style="margin-bottom:1rem;">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <div class="grid g2" style="margin-bottom:.6rem;">
            <div>
                <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Name *</label>
                <input type="text" name="name" placeholder="e.g. Slack Alerts" required maxlength="100">
            </div>
            <div>
                <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">URL *</label>
                <input type="url" name="url" placeholder="https://hooks.example.com/..." required maxlength="500">
            </div>
        </div>
        <div style="margin-bottom:.75rem;">
            <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.4rem;">Events</label>
            <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                <?php foreach (['ticket.created', 'ticket.updated', 'chat.started', 'chat.closed'] as $evt): ?>
                <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.88rem;">
                    <input type="checkbox" name="events[]" value="<?= htmlspecialchars($evt) ?>" style="width:auto;">
                    <?= htmlspecialchars($evt) ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Webhook</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>URL</th>
                <th>Events</th>
                <th>Status</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($webhooks)): ?>
            <tr><td colspan="5" style="color:var(--text-secondary);text-align:center;padding:1rem;">No webhooks configured.</td></tr>
        <?php else: ?>
            <?php foreach ($webhooks as $wh): ?>
            <tr>
                <td><strong><?= htmlspecialchars($wh['name']) ?></strong></td>
                <td style="font-size:.8rem;color:var(--text-secondary);word-break:break-all;max-width:200px;"><?= htmlspecialchars(mb_substr((string)$wh['url'], 0, 60)) ?>…</td>
                <td style="font-size:.8rem;">
                    <?php $events = json_decode((string)($wh['events'] ?? '[]'), true) ?: []; ?>
                    <?php foreach ($events as $e): ?>
                    <span class="badge badge-open" style="margin-right:.2rem;"><?= htmlspecialchars($e) ?></span>
                    <?php endforeach; ?>
                </td>
                <td><span class="badge" style="<?= $wh['is_active'] ? 'background:rgba(16,185,129,.12);color:#34d399;' : 'background:rgba(107,114,128,.12);color:#9ca3af;' ?>"><?= $wh['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                <td>
                    <form method="POST" action="/projects/helpdeskpro/integrations/webhooks/delete/<?= (int)$wh['id'] ?>" onsubmit="return confirm('Delete webhook?');" style="display:inline;">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <button type="submit" class="btn" style="background:rgba(239,68,68,.12);color:#f87171;border-color:rgba(239,68,68,.3);padding:.25rem .5rem;font-size:.8rem;"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
require PROJECT_PATH . '/views/layout.php';
