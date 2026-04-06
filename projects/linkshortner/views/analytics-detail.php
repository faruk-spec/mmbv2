<?php use Core\View; ?>
<?php View::extend('linkshortner:app'); ?>
<?php View::section('content'); ?>

<div class="card mb-4">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-chart-bar" style="color:var(--accent);"></i> <?= View::e($link['title'] ?: '/l/' . $link['code']) ?></div>
        <a href="/projects/linkshortner/links" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-bottom:20px;">
        <div>
            <div style="color:var(--text-secondary);font-size:12px;">Short URL</div>
            <a href="/l/<?= View::e($link['code']) ?>" target="_blank" style="color:var(--accent);font-weight:600;">/l/<?= View::e($link['code']) ?></a>
        </div>
        <div>
            <div style="color:var(--text-secondary);font-size:12px;">Destination</div>
            <a href="<?= View::e($link['original_url']) ?>" target="_blank" style="color:var(--text-secondary);font-size:13px;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;"><?= View::e($link['original_url']) ?></a>
        </div>
        <div>
            <div style="color:var(--text-secondary);font-size:12px;">Total Clicks</div>
            <div style="color:var(--orange);font-weight:700;font-size:1.4rem;"><?= number_format($link['total_clicks']) ?></div>
        </div>
        <div>
            <div style="color:var(--text-secondary);font-size:12px;">Status</div>
            <?php if ($link['status'] === 'active'): ?>
                <span class="badge badge-success">Active</span>
            <?php else: ?>
                <span class="badge badge-danger"><?= ucfirst($link['status']) ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="grid-2 mb-4">
    <!-- Device stats -->
    <div class="card">
        <div class="card-title" style="margin-bottom:14px;"><i class="fas fa-mobile-alt" style="color:var(--accent);"></i> Device Breakdown</div>
        <?php if (!empty($deviceStats)):
            $totalDev = array_sum(array_column($deviceStats, 'cnt')); ?>
        <?php foreach ($deviceStats as $row): ?>
        <div style="margin-bottom:12px;">
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
                <span><?= View::e(ucfirst($row['device'])) ?></span>
                <span><?= $row['cnt'] ?> (<?= $totalDev > 0 ? round($row['cnt'] / $totalDev * 100) : 0 ?>%)</span>
            </div>
            <div style="height:5px;background:var(--bg-secondary);border-radius:3px;">
                <div style="width:<?= $totalDev > 0 ? round($row['cnt'] / $totalDev * 100) : 0 ?>%;height:100%;background:var(--accent);border-radius:3px;"></div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?><p style="color:var(--text-secondary);font-size:13px;">No data.</p><?php endif; ?>
    </div>

    <!-- Country stats -->
    <div class="card">
        <div class="card-title" style="margin-bottom:14px;"><i class="fas fa-globe" style="color:var(--orange);"></i> Top Countries</div>
        <?php if (!empty($countryStats)): ?>
        <div class="table-container">
            <table>
                <thead><tr><th>Country</th><th>Clicks</th></tr></thead>
                <tbody>
                <?php foreach ($countryStats as $row): ?>
                <tr><td><?= View::e($row['country'] ?: 'Unknown') ?></td><td><?= $row['cnt'] ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?><p style="color:var(--text-secondary);font-size:13px;">No data.</p><?php endif; ?>
    </div>
</div>

<div class="grid-2 mb-4">
    <!-- Referrers -->
    <div class="card">
        <div class="card-title" style="margin-bottom:14px;"><i class="fas fa-external-link-alt" style="color:var(--accent2);"></i> Referrers</div>
        <?php if (!empty($referrerStats)): ?>
        <div class="table-container">
            <table>
                <thead><tr><th>Referrer</th><th>Clicks</th></tr></thead>
                <tbody>
                <?php foreach ($referrerStats as $row): ?>
                <tr><td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= View::e($row['referer']) ?></td><td><?= $row['cnt'] ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?><p style="color:var(--text-secondary);font-size:13px;">No referrer data.</p><?php endif; ?>
    </div>

    <!-- Recent clicks -->
    <div class="card">
        <div class="card-title" style="margin-bottom:14px;"><i class="fas fa-clock" style="color:var(--green);"></i> Recent Clicks</div>
        <?php if (!empty($recentClicks)): ?>
        <div class="table-container">
            <table>
                <thead><tr><th>IP</th><th>Device</th><th>Country</th><th>Time</th></tr></thead>
                <tbody>
                <?php foreach ($recentClicks as $click): ?>
                <tr>
                    <td style="font-size:12px;"><?= View::e(substr($click['ip_address'] ?? '', 0, 15)) ?></td>
                    <td><?= View::e(ucfirst($click['device'] ?? '')) ?></td>
                    <td><?= View::e($click['country'] ?? '–') ?></td>
                    <td style="font-size:12px;"><?= date('M d H:i', strtotime($click['clicked_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?><p style="color:var(--text-secondary);font-size:13px;">No clicks yet.</p><?php endif; ?>
    </div>
</div>

<?php View::end(); ?>
