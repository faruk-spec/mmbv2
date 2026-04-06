<?php use Core\View; ?>
<?php View::extend('linkshortner:app'); ?>
<?php View::section('content'); ?>

<style>
    .ls-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.875rem;
        margin-bottom: 1.25rem;
    }
    .ls-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 0.625rem;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.2s;
    }
    .ls-stat-card:hover { border-color: rgba(0,212,255,0.3); transform: translateY(-0.0625rem); }
    .ls-stat-icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.125rem;
        flex-shrink: 0;
    }
    .ls-stat-info { flex: 1; min-width: 0; }
    .ls-stat-value { font-size: 1.625rem; font-weight: 700; line-height: 1; }
    .ls-stat-label { font-size: var(--font-xs); color: var(--text-secondary); margin-top: 0.25rem; }
    .ls-quick-shorten {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 0.625rem;
        padding: 1.25rem;
        margin-bottom: 1.25rem;
    }
    .ls-shorten-row {
        display: flex;
        gap: 0.625rem;
        flex-wrap: wrap;
    }
    .ls-shorten-row input {
        flex: 1;
        min-width: 12.5rem;
        padding: 0.6875rem 1rem;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        color: var(--text-primary);
        font-family: inherit;
        font-size: var(--font-sm);
        transition: border-color 0.2s;
    }
    .ls-shorten-row input:focus { outline: none; border-color: var(--ls-accent); box-shadow: 0 0 0 3px rgba(0,212,255,0.1); }
    .ls-content-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .ls-link-code {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        color: var(--ls-accent);
        font-weight: 600;
        font-size: var(--font-sm);
        text-decoration: none;
    }
    .ls-link-code:hover { text-decoration: underline; }
    @media (max-width: 64rem) {
        .ls-stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 48rem) {
        .ls-stats-grid { grid-template-columns: repeat(2, 1fr); }
        .ls-content-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 30rem) {
        .ls-stats-grid { grid-template-columns: 1fr 1fr; gap: 0.625rem; }
        .ls-stat-card { padding: 0.875rem; gap: 0.625rem; }
        .ls-stat-value { font-size: 1.25rem; }
        .ls-stat-icon { width: 2.25rem; height: 2.25rem; font-size: 0.875rem; }
    }
</style>

<!-- Quick Shorten - prominently at top -->
<div class="ls-quick-shorten">
    <div style="font-size:var(--font-sm);font-weight:600;margin-bottom:0.75rem;display:flex;align-items:center;gap:0.5rem;">
        <i class="fas fa-compress-alt" style="color:var(--ls-accent);"></i> Quick Shorten
    </div>
    <form action="/projects/linkshortner/create" method="POST">
        <input type="hidden" name="_token" value="<?= \Core\Security::generateCsrfToken() ?>">
        <div class="ls-shorten-row">
            <input type="url" name="original_url"
                   placeholder="https://example.com/your-very-long-url-here" required>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-compress-alt"></i> Shorten
            </button>
            <a href="/projects/linkshortner/create" class="btn btn-secondary">
                <i class="fas fa-sliders-h"></i> Advanced
            </a>
        </div>
    </form>
</div>

<!-- Stats -->
<div class="ls-stats-grid">
    <div class="ls-stat-card">
        <div class="ls-stat-icon" style="background:rgba(0,212,255,0.12);">
            <i class="fas fa-link" style="color:var(--ls-accent);"></i>
        </div>
        <div class="ls-stat-info">
            <div class="ls-stat-value" style="color:var(--ls-accent);"><?= number_format($stats['total_links']) ?></div>
            <div class="ls-stat-label">Total Links</div>
        </div>
    </div>
    <div class="ls-stat-card">
        <div class="ls-stat-icon" style="background:rgba(0,255,136,0.12);">
            <i class="fas fa-mouse-pointer" style="color:var(--green);"></i>
        </div>
        <div class="ls-stat-info">
            <div class="ls-stat-value" style="color:var(--green);"><?= number_format($stats['total_clicks']) ?></div>
            <div class="ls-stat-label">Total Clicks</div>
        </div>
    </div>
    <div class="ls-stat-card">
        <div class="ls-stat-icon" style="background:rgba(255,170,0,0.12);">
            <i class="fas fa-check-circle" style="color:var(--orange);"></i>
        </div>
        <div class="ls-stat-info">
            <div class="ls-stat-value" style="color:var(--orange);"><?= number_format($stats['active_links']) ?></div>
            <div class="ls-stat-label">Active Links</div>
        </div>
    </div>
    <div class="ls-stat-card">
        <div class="ls-stat-icon" style="background:rgba(255,46,196,0.12);">
            <i class="fas fa-calendar-day" style="color:var(--accent2);"></i>
        </div>
        <div class="ls-stat-info">
            <div class="ls-stat-value" style="color:var(--accent2);"><?= number_format($stats['links_today']) ?></div>
            <div class="ls-stat-label">Created Today</div>
        </div>
    </div>
</div>

<!-- Two-column content -->
<div class="ls-content-grid">
    <!-- Recent Links -->
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <div style="font-weight:600;display:flex;align-items:center;gap:0.5rem;font-size:var(--font-sm);">
                <i class="fas fa-clock" style="color:var(--ls-accent);"></i> Recent Links
            </div>
            <a href="/projects/linkshortner/links" class="btn btn-secondary btn-sm">View All</a>
        </div>
        <?php if (!empty($recentLinks)): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Short Link</th>
                        <th>Title / URL</th>
                        <th>Clicks</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recentLinks as $link): ?>
                <tr onclick="window.open('/l/<?= View::e($link['code']) ?>','_blank')"
                    tabindex="0"
                    onkeydown="if(event.key==='Enter'||event.key===' ')window.open('/l/<?= View::e($link['code']) ?>','_blank')"
                    style="cursor:pointer;">
                    <td>
                        <a href="/l/<?= View::e($link['code']) ?>" target="_blank" class="ls-link-code"
                           onclick="event.stopPropagation();">
                            /l/<?= View::e($link['code']) ?>
                        </a>
                    </td>
                    <td style="max-width:10rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:var(--font-xs);color:var(--text-secondary);">
                        <?= View::e($link['title'] ?: $link['original_url']) ?>
                    </td>
                    <td style="font-size:var(--font-xs);"><?= number_format($link['total_clicks']) ?></td>
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
        <div class="empty-state" style="padding:2rem;">
            <div style="font-size:2rem;color:var(--text-secondary);opacity:0.4;margin-bottom:0.75rem;"><i class="fas fa-link"></i></div>
            <p style="color:var(--text-secondary);font-size:var(--font-sm);">
                No links yet. <a href="/projects/linkshortner/create" style="color:var(--ls-accent);">Create your first link</a>
            </p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Top Links -->
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <div style="font-weight:600;display:flex;align-items:center;gap:0.5rem;font-size:var(--font-sm);">
                <i class="fas fa-trophy" style="color:var(--orange);"></i> Top Links
            </div>
            <a href="/projects/linkshortner/analytics" class="btn btn-secondary btn-sm">Analytics</a>
        </div>
        <?php if (!empty($topLinks)): ?>
        <div style="display:flex;flex-direction:column;gap:0.5rem;">
            <?php foreach ($topLinks as $i => $link): ?>
            <div style="display:flex;align-items:center;gap:0.75rem;padding:0.625rem 0.75rem;background:var(--bg-secondary);border-radius:0.5rem;">
                <div style="width:1.5rem;height:1.5rem;border-radius:50%;background:<?= $i===0 ? 'rgba(255,170,0,0.2)' : 'rgba(255,255,255,0.06)' ?>;display:flex;align-items:center;justify-content:center;font-size:var(--font-xs);font-weight:700;color:<?= $i===0 ? 'var(--orange)' : 'var(--text-secondary)' ?>;flex-shrink:0;">
                    <?= $i + 1 ?>
                </div>
                <div style="flex:1;min-width:0;">
                    <a href="/l/<?= View::e($link['code']) ?>" target="_blank" class="ls-link-code">/l/<?= View::e($link['code']) ?></a>
                    <div style="font-size:var(--font-xs);color:var(--text-secondary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        <?= View::e($link['title'] ?: $link['original_url']) ?>
                    </div>
                </div>
                <div style="font-size:var(--font-sm);font-weight:600;color:var(--orange);flex-shrink:0;">
                    <?= number_format($link['total_clicks']) ?>
                    <span style="font-size:var(--font-xs);color:var(--text-secondary);font-weight:400;"> clicks</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state" style="padding:2rem;">
            <div style="font-size:2rem;color:var(--text-secondary);opacity:0.4;margin-bottom:0.75rem;"><i class="fas fa-chart-bar"></i></div>
            <p style="color:var(--text-secondary);font-size:var(--font-sm);">No click data yet.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php View::end(); ?>
