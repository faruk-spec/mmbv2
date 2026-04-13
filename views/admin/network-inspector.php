<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="padding: 20px;">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="font-size:1.4rem;font-weight:700;margin:0 0 4px;">Network Inspector</h1>
            <p style="color:var(--text-secondary);font-size:.875rem;margin:0;">
                API request/response log — super_admin + debug mode only. Sensitive fields are redacted.
            </p>
        </div>
        <?php if (!empty($entries)): ?>
        <form method="POST" action="/admin/network-inspector/clear"
              onsubmit="return confirm('Clear all log entries?');">
            <?= \Core\Security::csrfField() ?>
            <button type="submit" style="padding:8px 18px;background:rgba(255,107,107,.15);border:1px solid rgba(255,107,107,.4);border-radius:8px;color:#ff6b6b;cursor:pointer;font-size:.875rem;">
                Clear Log
            </button>
        </form>
        <?php endif; ?>
    </div>

    <?php if (Helpers::hasFlash('success')): ?>
        <div style="background:rgba(0,255,136,.1);border:1px solid var(--green);border-radius:8px;padding:12px 14px;margin-bottom:16px;color:var(--green);font-size:.875rem;">
            <?= View::e(Helpers::getFlash('success')) ?>
        </div>
    <?php endif; ?>
    <?php if (Helpers::hasFlash('error')): ?>
        <div style="background:rgba(255,107,107,.1);border:1px solid rgba(255,107,107,.4);border-radius:8px;padding:12px 14px;margin-bottom:16px;color:#ff6b6b;font-size:.875rem;">
            <?= View::e(Helpers::getFlash('error')) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($entries)): ?>
        <div style="text-align:center;padding:60px 20px;color:var(--text-secondary);">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:16px;opacity:.4;">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <p style="margin:0;">No network activity logged yet.</p>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:.825rem;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border-color);text-align:left;">
                        <th style="padding:10px 12px;color:var(--text-secondary);font-weight:600;">Timestamp</th>
                        <th style="padding:10px 12px;color:var(--text-secondary);font-weight:600;">Method</th>
                        <th style="padding:10px 12px;color:var(--text-secondary);font-weight:600;">URL</th>
                        <th style="padding:10px 12px;color:var(--text-secondary);font-weight:600;">Status</th>
                        <th style="padding:10px 12px;color:var(--text-secondary);font-weight:600;">Time (s)</th>
                        <th style="padding:10px 12px;color:var(--text-secondary);font-weight:600;">Response Preview</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry): ?>
                    <?php
                        $status = (int) ($entry['status'] ?? 0);
                        $statusColor = $status >= 500 ? '#ff6b6b' : ($status >= 400 ? '#ffc107' : ($status >= 300 ? '#00b4d8' : '#00ff88'));
                        $methodColors = ['GET' => '#00f0ff', 'POST' => '#a855f7', 'PUT' => '#f97316', 'PATCH' => '#f59e0b', 'DELETE' => '#ef4444'];
                        $methodColor = $methodColors[$entry['method'] ?? ''] ?? 'var(--text-secondary)';
                    ?>
                    <tr style="border-bottom:1px solid rgba(255,255,255,.05);">
                        <td style="padding:10px 12px;white-space:nowrap;color:var(--text-secondary);"><?= View::e($entry['timestamp'] ?? '') ?></td>
                        <td style="padding:10px 12px;">
                            <span style="background:rgba(0,0,0,.3);border:1px solid <?= $methodColor ?>;color:<?= $methodColor ?>;border-radius:4px;padding:2px 8px;font-family:monospace;font-size:.75rem;font-weight:700;">
                                <?= View::e($entry['method'] ?? '') ?>
                            </span>
                        </td>
                        <td style="padding:10px 12px;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= View::e($entry['url'] ?? '') ?>">
                            <?= View::e($entry['url'] ?? '') ?>
                        </td>
                        <td style="padding:10px 12px;">
                            <span style="color:<?= $statusColor ?>;font-weight:600;"><?= View::e((string) $status) ?></span>
                        </td>
                        <td style="padding:10px 12px;font-family:monospace;"><?= View::e(number_format((float) ($entry['response_time'] ?? 0), 3)) ?></td>
                        <td style="padding:10px 12px;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-family:monospace;font-size:.75rem;color:var(--text-secondary);" title="<?= View::e($entry['response_body'] ?? '') ?>">
                            <?= View::e($entry['response_body'] ?? '—') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p style="margin-top:12px;color:var(--text-secondary);font-size:.8rem;">
            Showing <?= count($entries) ?> entr<?= count($entries) === 1 ? 'y' : 'ies' ?> (newest first, max 100).
        </p>
    <?php endif; ?>

</div>
<?php View::endSection(); ?>
