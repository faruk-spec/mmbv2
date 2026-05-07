<?php
use Core\View;
View::extend('whatsapp:app');
View::section('content');
?>
<div style="padding:4px 0 20px;">
    <h2 style="font-size:1.5rem;font-weight:700;margin-bottom:4px;color:var(--whatsapp-green);">
        <i class="fas fa-book" style="margin-right:8px;"></i>API Documentation
    </h2>
    <p style="color:var(--text-secondary);font-size:.9rem;">Complete reference for the WhatsApp REST API.</p>
</div>

<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;margin-bottom:20px;">
    <h3 style="font-size:1rem;font-weight:700;margin-bottom:12px;color:var(--text-primary);">Authentication</h3>
    <p style="color:var(--text-secondary);font-size:.9rem;margin-bottom:10px;">Include your API key in every request via the <code style="background:var(--bg-secondary);padding:2px 6px;border-radius:4px;color:var(--whatsapp-green);">X-Api-Key</code> header.</p>
    <pre style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;padding:14px;font-size:.82rem;color:var(--text-primary);overflow-x:auto;">X-Api-Key: your_api_key_here</pre>
</div>

<?php foreach ($endpoints as $ep): ?>
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;margin-bottom:16px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
        <span style="font-size:.78rem;font-weight:700;padding:4px 10px;border-radius:6px;background:<?= ($ep['method']??'')=='GET'?'rgba(0,255,136,.15)':'rgba(153,69,255,.15)' ?>;color:<?= ($ep['method']??'')=='GET'?'var(--whatsapp-green)':'#9945ff' ?>;"><?= View::e($ep['method'] ?? 'GET') ?></span>
        <code style="font-size:.9rem;color:var(--text-primary);"><?= View::e($ep['endpoint'] ?? '') ?></code>
    </div>
    <h4 style="font-size:.95rem;font-weight:700;color:var(--text-primary);margin-bottom:6px;"><?= View::e($ep['name'] ?? '') ?></h4>
    <p style="color:var(--text-secondary);font-size:.85rem;margin-bottom:12px;"><?= View::e($ep['description'] ?? '') ?></p>
    <?php if (!empty($ep['parameters'])): ?>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.82rem;">
            <thead>
                <tr style="color:var(--text-secondary);">
                    <th style="text-align:left;padding:6px 10px;border-bottom:1px solid var(--border-color);">Parameter</th>
                    <th style="text-align:left;padding:6px 10px;border-bottom:1px solid var(--border-color);">Type</th>
                    <th style="text-align:left;padding:6px 10px;border-bottom:1px solid var(--border-color);">Required</th>
                    <th style="text-align:left;padding:6px 10px;border-bottom:1px solid var(--border-color);">Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ep['parameters'] as $p): ?>
                <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                    <td style="padding:6px 10px;color:var(--whatsapp-green);font-family:monospace;"><?= View::e($p['name'] ?? '') ?></td>
                    <td style="padding:6px 10px;color:var(--text-secondary);"><?= View::e($p['type'] ?? '') ?></td>
                    <td style="padding:6px 10px;"><?= !empty($p['required']) ? '<span style="color:var(--whatsapp-green);font-size:.75rem;font-weight:700;">Yes</span>' : '<span style="color:var(--text-secondary);font-size:.75rem;">No</span>' ?></td>
                    <td style="padding:6px 10px;color:var(--text-secondary);"><?= View::e($p['description'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php View::end(); ?>
