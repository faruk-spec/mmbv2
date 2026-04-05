<?php use Core\View; ?>
<?php View::extend('linkshortner:app'); ?>
<?php View::section('content'); ?>

<!-- Overview Stats -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-value" style="color:var(--accent);"><?= number_format($totalClicks) ?></div>
        <div class="stat-label"><i class="fas fa-mouse-pointer" style="margin-right:5px;"></i> Total Clicks</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:var(--green);"><?= number_format($clicksToday) ?></div>
        <div class="stat-label"><i class="fas fa-calendar-day" style="margin-right:5px;"></i> Clicks Today</div>
    </div>
</div>

<div class="grid-2 mb-4">
    <!-- Device breakdown -->
    <div class="card">
        <div class="card-title" style="margin-bottom:16px;"><i class="fas fa-mobile-alt" style="color:var(--accent);"></i> Device Breakdown</div>
        <?php if (!empty($deviceStats)): ?>
        <?php
            $totalDev = array_sum(array_column($deviceStats, 'cnt'));
        ?>
        <?php foreach ($deviceStats as $row): ?>
        <div style="margin-bottom:12px;">
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
                <span><?= View::e(ucfirst($row['device'] ?? 'Unknown')) ?></span>
                <span><?= number_format($row['cnt']) ?> (<?= $totalDev > 0 ? round($row['cnt'] / $totalDev * 100) : 0 ?>%)</span>
            </div>
            <div style="height:6px;background:var(--bg-secondary);border-radius:3px;">
                <div style="width:<?= $totalDev > 0 ? round($row['cnt'] / $totalDev * 100) : 0 ?>%;height:100%;background:var(--accent);border-radius:3px;"></div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <p style="color:var(--text-secondary);font-size:13px;">No data yet.</p>
        <?php endif; ?>
    </div>

    <!-- Top Countries -->
    <div class="card">
        <div class="card-title" style="margin-bottom:16px;"><i class="fas fa-globe" style="color:var(--orange);"></i> Top Countries</div>
        <?php if (!empty($countryStats)): ?>
        <div class="table-container">
            <table>
                <thead><tr><th>Country</th><th>Clicks</th></tr></thead>
                <tbody>
                <?php foreach ($countryStats as $row): ?>
                <tr><td><?= View::e($row['country'] ?: 'Unknown') ?></td><td><?= number_format($row['cnt']) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p style="color:var(--text-secondary);font-size:13px;">No data yet.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Top Links -->
<div class="card mb-4">
    <div class="card-title" style="margin-bottom:16px;"><i class="fas fa-trophy" style="color:var(--orange);"></i> Top Links</div>
    <?php if (!empty($topLinks)): ?>
    <div class="table-container">
        <table>
            <thead><tr><th>Code</th><th>Title / URL</th><th>Clicks</th><th>Unique</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($topLinks as $link): ?>
            <tr>
                <td><a href="/l/<?= View::e($link['code']) ?>" target="_blank" style="color:var(--accent);">/l/<?= View::e($link['code']) ?></a></td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= View::e($link['title'] ?: $link['original_url']) ?></td>
                <td style="color:var(--orange);font-weight:600;"><?= number_format($link['total_clicks']) ?></td>
                <td><?= number_format($link['unique_clicks']) ?></td>
                <td><a href="/projects/linkshortner/analytics/<?= View::e($link['code']) ?>" class="btn btn-secondary btn-sm">Details</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <p style="color:var(--text-secondary);text-align:center;padding:20px 0;">No links yet.</p>
    <?php endif; ?>
</div>

<!-- Daily Clicks (simple text chart) -->
<?php if (!empty($dailyClicks)): ?>
<div class="card">
    <div class="card-title" style="margin-bottom:16px;"><i class="fas fa-chart-line" style="color:var(--green);"></i> Daily Clicks – Last 30 Days</div>
    <div style="display:flex;align-items:flex-end;gap:3px;height:80px;">
    <?php
        $maxClicks = max(array_column($dailyClicks, 'cnt') ?: [1]);
        $clicksByDay = array_column($dailyClicks, 'cnt', 'day');
    ?>
    <?php foreach ($dailyClicks as $row): ?>
        <div title="<?= $row['day'] ?>: <?= $row['cnt'] ?> clicks"
             style="flex:1;min-width:4px;background:var(--accent);border-radius:2px 2px 0 0;height:<?= round($row['cnt'] / $maxClicks * 80) ?>px;opacity:0.8;"></div>
    <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php View::end(); ?>
