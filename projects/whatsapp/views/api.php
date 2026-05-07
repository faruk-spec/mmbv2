<?php
use Core\View;
use Core\Security;
View::extend('whatsapp:app');
View::section('content');
?>
<div style="padding:4px 0 20px;">
    <h2 style="font-size:1.5rem;font-weight:700;margin-bottom:4px;color:var(--whatsapp-green);">
        <i class="fas fa-key" style="margin-right:8px;"></i>API &amp; Analytics
    </h2>
    <p style="color:var(--text-secondary);font-size:.9rem;">Manage your API key and monitor usage.</p>
</div>

<?php if (!empty($newKey)): ?>
<div style="background:rgba(0,255,136,.08);border:1px solid rgba(0,255,136,.4);border-radius:10px;padding:16px 20px;margin-bottom:20px;">
    <div style="font-weight:700;color:var(--whatsapp-green);margin-bottom:6px;"><i class="fas fa-check-circle" style="margin-right:6px;"></i>New API Key Generated</div>
    <div style="display:flex;align-items:center;gap:10px;">
        <code id="newApiKey" style="flex:1;word-break:break-all;font-size:.85rem;color:var(--text-primary);"><?= View::e($newKey) ?></code>
        <button onclick="navigator.clipboard.writeText('<?= View::e($newKey) ?>')" style="padding:5px 10px;background:transparent;border:1px solid var(--border-color);border-radius:6px;color:var(--text-secondary);cursor:pointer;font-size:.75rem;"><i class="fas fa-copy"></i></button>
    </div>
    <small style="color:#ffaa00;font-size:.78rem;display:block;margin-top:8px;"><i class="fas fa-exclamation-triangle" style="margin-right:4px;"></i>Copy this key now — it will not be shown again.</small>
</div>
<?php endif; ?>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:24px;">
    <?php
    $statCards = [
        ['label'=>'Total Requests','value'=>number_format($totalRequests??0),'icon'=>'fa-chart-bar','color'=>'var(--whatsapp-green)'],
        ['label'=>'Requests Today','value'=>number_format($requestsToday??0),'icon'=>'fa-calendar-day','color'=>'#9945ff'],
        ['label'=>'Last Request','value'=>$lastRequestAt ? date('M j, H:i', strtotime($lastRequestAt)) : 'Never','icon'=>'fa-clock','color'=>'#ffaa00'],
    ];
    foreach ($statCards as $c): ?>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:16px;text-align:center;">
        <div style="font-size:1.3rem;color:<?= $c['color'] ?>;margin-bottom:6px;"><i class="fas <?= $c['icon'] ?>"></i></div>
        <div style="font-size:1.1rem;font-weight:800;color:<?= $c['color'] ?>"><?= $c['value'] ?></div>
        <div style="font-size:.75rem;color:var(--text-secondary);margin-top:2px;"><?= $c['label'] ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- API Key Card -->
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;margin-bottom:24px;">
    <h3 style="font-size:.95rem;font-weight:700;margin-bottom:14px;color:var(--text-primary);">Your API Key</h3>
    <?php if ($activeKey): ?>
    <div style="display:flex;align-items:center;gap:10px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;padding:10px 14px;margin-bottom:12px;">
        <code style="flex:1;font-size:.82rem;color:var(--whatsapp-green);word-break:break-all;"><?= View::e(substr($activeKey['api_key'], 0, 20) . '••••••••••••••••') ?></code>
        <span style="font-size:.72rem;padding:3px 10px;border-radius:20px;background:rgba(0,255,136,.12);color:var(--whatsapp-green);">Active</span>
    </div>
    <?php else: ?>
    <p style="color:var(--text-secondary);font-size:.9rem;margin-bottom:12px;">No active API key. Generate one below.</p>
    <?php endif; ?>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <form method="POST" action="/projects/whatsapp/api/generate">
            <?= Security::csrfField() ?>
            <button type="submit" style="padding:9px 18px;background:var(--whatsapp-green);color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:.85rem;">
                <i class="fas fa-sync" style="margin-right:6px;"></i><?= $activeKey ? 'Regenerate Key' : 'Generate Key' ?>
            </button>
        </form>
        <?php if ($activeKey): ?>
        <form method="POST" action="/projects/whatsapp/api/revoke" onsubmit="return confirm('Revoke all API keys?')">
            <?= Security::csrfField() ?>
            <button type="submit" style="padding:9px 18px;background:transparent;border:1px solid rgba(255,100,100,.4);color:#ff6464;border-radius:8px;font-weight:600;cursor:pointer;font-size:.85rem;">
                <i class="fas fa-ban" style="margin-right:6px;"></i>Revoke Key
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Activity -->
<?php if (!empty($recentLogs)): ?>
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
    <h3 style="font-size:.95rem;font-weight:700;margin-bottom:14px;color:var(--text-primary);">Recent API Activity</h3>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.82rem;">
            <thead>
                <tr style="color:var(--text-secondary);">
                    <th style="text-align:left;padding:6px 10px;border-bottom:1px solid var(--border-color);">Endpoint</th>
                    <th style="text-align:left;padding:6px 10px;border-bottom:1px solid var(--border-color);">Method</th>
                    <th style="text-align:left;padding:6px 10px;border-bottom:1px solid var(--border-color);">IP</th>
                    <th style="text-align:left;padding:6px 10px;border-bottom:1px solid var(--border-color);">Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentLogs as $log): ?>
                <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                    <td style="padding:7px 10px;color:var(--whatsapp-green);"><?= View::e($log['endpoint'] ?? '—') ?></td>
                    <td style="padding:7px 10px;color:var(--text-secondary);"><?= View::e($log['method'] ?? '—') ?></td>
                    <td style="padding:7px 10px;color:var(--text-secondary);"><?= View::e($log['ip_address'] ?? '—') ?></td>
                    <td style="padding:7px 10px;color:var(--text-secondary);"><?= date('M j, H:i', strtotime($log['created_at'] ?? 'now')) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php View::end(); ?>
