<?php use Core\View; ?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>
<div class="container-fluid">
    <div class="page-header" style="margin-bottom:24px;">
        <h1 style="font-size:1.5rem;font-weight:700;"><i class="fas fa-chart-bar" style="color:#00d4ff;margin-right:10px;"></i> Analytics</h1>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
        <!-- Device Stats -->
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
            <h3 style="font-size:1rem;font-weight:600;margin-bottom:16px;"><i class="fas fa-mobile-alt" style="color:#00d4ff;margin-right:8px;"></i> Device Breakdown</h3>
            <?php if (!empty($deviceStats)):
                $totalDev = array_sum(array_column($deviceStats, 'cnt')); ?>
            <?php foreach ($deviceStats as $row): ?>
            <div style="margin-bottom:12px;">
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
                    <span><?= View::e(ucfirst($row['device'] ?? 'Unknown')) ?></span>
                    <span><?= number_format($row['cnt']) ?> (<?= $totalDev > 0 ? round($row['cnt'] / $totalDev * 100) : 0 ?>%)</span>
                </div>
                <div style="height:6px;background:var(--bg-secondary);border-radius:3px;">
                    <div style="width:<?= $totalDev > 0 ? round($row['cnt'] / $totalDev * 100) : 0 ?>%;height:100%;background:#00d4ff;border-radius:3px;"></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?><p style="color:var(--text-secondary);font-size:13px;">No data.</p><?php endif; ?>
        </div>

        <!-- Country Stats -->
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
            <h3 style="font-size:1rem;font-weight:600;margin-bottom:16px;"><i class="fas fa-globe" style="color:#f59e0b;margin-right:8px;"></i> Top Countries</h3>
            <?php if (!empty($countryStats)): ?>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:14px;">
                    <thead><tr>
                        <th style="text-align:left;padding:8px 10px;color:var(--text-secondary);font-size:12px;border-bottom:1px solid var(--border-color);">Country</th>
                        <th style="text-align:left;padding:8px 10px;color:var(--text-secondary);font-size:12px;border-bottom:1px solid var(--border-color);">Clicks</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($countryStats as $row): ?>
                    <tr><td style="padding:8px 10px;border-bottom:1px solid rgba(255,255,255,0.04);"><?= View::e($row['country'] ?: 'Unknown') ?></td><td style="padding:8px 10px;border-bottom:1px solid rgba(255,255,255,0.04);"><?= number_format($row['cnt']) ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?><p style="color:var(--text-secondary);font-size:13px;">No data.</p><?php endif; ?>
        </div>
    </div>

    <!-- Daily clicks chart -->
    <?php if (!empty($dailyClicks)): ?>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <h3 style="font-size:1rem;font-weight:600;margin-bottom:16px;"><i class="fas fa-chart-line" style="color:#22c55e;margin-right:8px;"></i> Daily Clicks – Last 30 Days</h3>
        <div style="display:flex;align-items:flex-end;gap:3px;height:100px;">
        <?php $maxC = max(array_column($dailyClicks, 'cnt') ?: [1]); ?>
        <?php foreach ($dailyClicks as $row): ?>
            <div title="<?= $row['day'] ?>: <?= $row['cnt'] ?>"
                 style="flex:1;min-width:4px;background:#00d4ff;border-radius:2px 2px 0 0;height:<?= round($row['cnt'] / $maxC * 100) ?>px;opacity:0.7;"></div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php View::end(); ?>
