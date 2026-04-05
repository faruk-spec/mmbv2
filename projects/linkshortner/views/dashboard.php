<?php use Core\View; ?>
<?php View::extend('linkshortner:app'); ?>
<?php View::section('content'); ?>

<!-- Stats -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-value" style="color:var(--accent);"><?= $stats['total_links'] ?></div>
        <div class="stat-label"><i class="fas fa-link" style="margin-right:5px;"></i> Total Links</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:var(--green);"><?= number_format($stats['total_clicks']) ?></div>
        <div class="stat-label"><i class="fas fa-mouse-pointer" style="margin-right:5px;"></i> Total Clicks</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:var(--orange);"><?= $stats['active_links'] ?></div>
        <div class="stat-label"><i class="fas fa-check-circle" style="margin-right:5px;"></i> Active Links</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:var(--accent2);"><?= $stats['links_today'] ?></div>
        <div class="stat-label"><i class="fas fa-calendar-day" style="margin-right:5px;"></i> Created Today</div>
    </div>
</div>

<div class="grid-2 mb-4">
    <!-- Recent Links -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-clock" style="color:var(--accent);"></i> Recent Links</div>
            <a href="/projects/linkshortner/links" class="btn btn-secondary btn-sm">View All</a>
        </div>
        <?php if (!empty($recentLinks)): ?>
        <div class="table-container">
            <table>
                <thead><tr><th>Code</th><th>Title / URL</th><th>Clicks</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($recentLinks as $link): ?>
                <tr>
                    <td>
                        <a href="/l/<?= View::e($link['code']) ?>" target="_blank" style="color:var(--accent);font-weight:600;">/l/<?= View::e($link['code']) ?></a>
                    </td>
                    <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        <?= View::e($link['title'] ?: $link['original_url']) ?>
                    </td>
                    <td><?= number_format($link['total_clicks']) ?></td>
                    <td>
                        <?php if ($link['status'] === 'active'): ?>
                            <span class="badge badge-success">Active</span>
                        <?php elseif ($link['status'] === 'expired'): ?>
                            <span class="badge badge-danger">Expired</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Disabled</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p style="color:var(--text-secondary);text-align:center;padding:20px 0;">No links yet. <a href="/projects/linkshortner/create" style="color:var(--accent);">Create your first link</a></p>
        <?php endif; ?>
    </div>

    <!-- Top Links -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-trophy" style="color:var(--orange);"></i> Top Links</div>
            <a href="/projects/linkshortner/analytics" class="btn btn-secondary btn-sm">Full Analytics</a>
        </div>
        <?php if (!empty($topLinks)): ?>
        <div class="table-container">
            <table>
                <thead><tr><th>Code</th><th>Title</th><th>Clicks</th></tr></thead>
                <tbody>
                <?php foreach ($topLinks as $link): ?>
                <tr>
                    <td><a href="/l/<?= View::e($link['code']) ?>" target="_blank" style="color:var(--accent);">/l/<?= View::e($link['code']) ?></a></td>
                    <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= View::e($link['title'] ?: $link['original_url']) ?></td>
                    <td style="color:var(--orange);font-weight:600;"><?= number_format($link['total_clicks']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p style="color:var(--text-secondary);text-align:center;padding:20px 0;">No click data yet.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Create -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-bolt" style="color:var(--accent2);"></i> Quick Shorten</div>
    </div>
    <form action="/projects/linkshortner/create" method="POST" style="display:flex;gap:12px;flex-wrap:wrap;">
        <input type="hidden" name="_token" value="<?= \Core\Security::generateCsrfToken() ?>">
        <input type="url" name="original_url" placeholder="https://example.com/very-long-url" required
               style="flex:1;min-width:240px;padding:11px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-family:inherit;font-size:14px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-compress-alt"></i> Shorten</button>
    </form>
</div>

<?php View::end(); ?>
