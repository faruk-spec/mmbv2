<?php use Core\View; use Core\Security; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="container-fluid">
    <div class="page-header" style="margin-bottom:24px;">
        <h1 style="font-size:1.6rem;font-weight:700;display:flex;align-items:center;gap:10px;">
            <i class="fas fa-link" style="color:#00d4ff;"></i> LinkShortner Overview
        </h1>
        <p style="color:var(--text-secondary);margin-top:4px;">URL Shortener & Analytics administration</p>
    </div>

    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:28px;">
        <?php
        $statItems = [
            ['label' => 'Total Links',   'value' => $stats['total_links'],   'color' => '#00d4ff', 'icon' => 'link'],
            ['label' => 'Total Clicks',  'value' => number_format($stats['total_clicks']),  'color' => '#22c55e', 'icon' => 'mouse-pointer'],
            ['label' => 'Active Links',  'value' => $stats['active_links'],  'color' => '#ffd700', 'icon' => 'check-circle'],
            ['label' => 'Total Users',   'value' => $stats['total_users'],   'color' => '#8b5cf6', 'icon' => 'users'],
            ['label' => 'Links Today',   'value' => $stats['links_today'],   'color' => '#f59e0b', 'icon' => 'calendar-day'],
        ];
        foreach ($statItems as $s): ?>
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
            <div style="font-size:1.8rem;font-weight:700;color:<?= $s['color'] ?>;"><?= $s['value'] ?></div>
            <div style="color:var(--text-secondary);font-size:13px;margin-top:4px;"><i class="fas fa-<?= $s['icon'] ?>" style="margin-right:5px;"></i><?= $s['label'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Top Links + Recent -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
            <h3 style="font-size:1rem;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:8px;"><i class="fas fa-trophy" style="color:#f59e0b;"></i> Top Links</h3>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:14px;">
                    <thead><tr>
                        <th style="text-align:left;padding:10px 12px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Code</th>
                        <th style="text-align:left;padding:10px 12px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">User</th>
                        <th style="text-align:left;padding:10px 12px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Clicks</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($topLinks as $link): ?>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                        <td style="padding:10px 12px;"><a href="/l/<?= View::e($link['code']) ?>" target="_blank" style="color:#00d4ff;">/l/<?= View::e($link['code']) ?></a></td>
                        <td style="padding:10px 12px;color:var(--text-secondary);">UID <?= $link['user_id'] ?></td>
                        <td style="padding:10px 12px;color:#f59e0b;font-weight:600;"><?= number_format($link['total_clicks']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
            <h3 style="font-size:1rem;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:8px;"><i class="fas fa-clock" style="color:#00d4ff;"></i> Recent Links</h3>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:14px;">
                    <thead><tr>
                        <th style="text-align:left;padding:10px 12px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Code</th>
                        <th style="text-align:left;padding:10px 12px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Status</th>
                        <th style="text-align:left;padding:10px 12px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Created</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($recentLinks as $link): ?>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                        <td style="padding:10px 12px;"><a href="/l/<?= View::e($link['code']) ?>" target="_blank" style="color:#00d4ff;">/l/<?= View::e($link['code']) ?></a></td>
                        <td style="padding:10px 12px;">
                            <?php if ($link['status'] === 'active'): ?><span style="background:rgba(34,197,94,0.15);color:#22c55e;padding:3px 10px;border-radius:20px;font-size:11px;">Active</span>
                            <?php else: ?><span style="background:rgba(239,68,68,0.15);color:#ef4444;padding:3px 10px;border-radius:20px;font-size:11px;"><?= ucfirst($link['status']) ?></span><?php endif; ?>
                        </td>
                        <td style="padding:10px 12px;color:var(--text-secondary);font-size:12px;"><?= date('M d, Y', strtotime($link['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php View::end(); ?>
