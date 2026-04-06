<?php use Core\View; ?>
<?php View::extend('linkshortner:app'); ?>
<?php View::section('content'); ?>

<style>
    /* ── Page Header ── */
    .ls-page-header { margin-bottom: 1.5rem; }
    .ls-page-header h1 {
        font-size: var(--font-2xl); font-weight: 700; line-height: 1.2; margin-bottom: 0.25rem;
        background: linear-gradient(135deg, var(--ls-accent), #7c3aed);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .ls-page-header p { font-size: var(--font-sm); color: var(--text-secondary); }

    /* ── Quick Shorten ── */
    .ls-quick-shorten {
        background: var(--bg-card); border: 1px solid var(--border-color);
        border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1.25rem;
    }
    .ls-shorten-row {
        display: flex; gap: 0.625rem; flex-wrap: wrap;
    }
    .ls-shorten-row input {
        flex: 1; min-width: 12.5rem; padding: 0.6875rem 1rem;
        background: var(--bg-secondary); border: 1px solid var(--border-color);
        border-radius: 0.5rem; color: var(--text-primary); font-family: inherit;
        font-size: var(--font-sm); transition: border-color 0.2s;
    }
    .ls-shorten-row input:focus { outline: none; border-color: var(--ls-accent); box-shadow: 0 0 0 3px rgba(0,212,255,0.1); }

    /* ── Stats ── */
    .ls-stats-grid {
        display: grid; grid-template-columns: repeat(4, 1fr);
        gap: 0.875rem; margin-bottom: 1.25rem;
    }
    .ls-stat-card {
        background: var(--bg-card); border: 1px solid var(--border-color);
        border-radius: 0.625rem; padding: 1.125rem;
        display: flex; align-items: center; gap: 0.875rem; transition: all 0.2s;
    }
    .ls-stat-card:hover { border-color: rgba(0,212,255,0.25); transform: translateY(-0.0625rem); }
    .ls-stat-icon {
        width: 2.75rem; height: 2.75rem; border-radius: 0.5rem; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 1.125rem;
    }
    .ls-stat-value { font-size: 1.625rem; font-weight: 700; line-height: 1; }
    .ls-stat-label { font-size: var(--font-xs); color: var(--text-secondary); margin-top: 0.1875rem; }

    /* ── Quick Actions ── */
    .ls-quick-row {
        display: grid; grid-template-columns: repeat(4, 1fr);
        gap: 0.875rem; margin-bottom: 1.25rem;
    }
    .ls-quick-card {
        display: flex; flex-direction: column; align-items: flex-start; gap: 0.5rem;
        padding: 1.25rem 1.125rem; border-radius: 0.75rem;
        text-decoration: none; color: white; transition: all 0.2s ease;
    }
    .ls-quick-card:hover { transform: translateY(-0.1875rem); filter: brightness(1.08); box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.25); }
    .ls-quick-icon { font-size: 1.75rem; line-height: 1; }
    .ls-quick-label { font-weight: 700; font-size: var(--font-sm); }
    .ls-quick-desc { font-size: var(--font-xs); opacity: 0.8; }

    /* ── Content grid ── */
    .ls-content-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

    .ls-section-header {
        display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.875rem;
    }
    .ls-section-title { font-size: var(--font-md); font-weight: 600; display: flex; align-items: center; gap: 0.5rem; }

    .ls-link-code {
        display: inline-flex; align-items: center; gap: 0.3rem;
        color: var(--ls-accent); font-weight: 600; font-size: var(--font-sm); text-decoration: none;
    }
    .ls-link-code:hover { text-decoration: underline; }

    @media (max-width: 64rem) {
        .ls-stats-grid { grid-template-columns: repeat(2, 1fr); }
        .ls-quick-row { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 48rem) {
        .ls-stats-grid { grid-template-columns: repeat(2, 1fr); }
        .ls-quick-row { grid-template-columns: repeat(2, 1fr); }
        .ls-content-grid { grid-template-columns: 1fr; }
        .ls-stat-value { font-size: 1.25rem; }
        .ls-stat-icon { width: 2.25rem; height: 2.25rem; font-size: 0.875rem; }
    }
</style>

<!-- Page Header -->
<div class="ls-page-header">
    <h1>LinkShortner</h1>
    <p>Create short, trackable links — fast and easy</p>
</div>

<!-- Quick Shorten -->
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
        <div>
            <div class="ls-stat-value" style="color:var(--ls-accent);"><?= number_format($stats['total_links']) ?></div>
            <div class="ls-stat-label">Total Links</div>
        </div>
    </div>
    <div class="ls-stat-card">
        <div class="ls-stat-icon" style="background:rgba(0,255,136,0.12);">
            <i class="fas fa-mouse-pointer" style="color:var(--green);"></i>
        </div>
        <div>
            <div class="ls-stat-value" style="color:var(--green);"><?= number_format($stats['total_clicks']) ?></div>
            <div class="ls-stat-label">Total Clicks</div>
        </div>
    </div>
    <div class="ls-stat-card">
        <div class="ls-stat-icon" style="background:rgba(255,170,0,0.12);">
            <i class="fas fa-check-circle" style="color:var(--orange);"></i>
        </div>
        <div>
            <div class="ls-stat-value" style="color:var(--orange);"><?= number_format($stats['active_links']) ?></div>
            <div class="ls-stat-label">Active Links</div>
        </div>
    </div>
    <div class="ls-stat-card">
        <div class="ls-stat-icon" style="background:rgba(255,46,196,0.12);">
            <i class="fas fa-calendar-day" style="color:var(--accent2);"></i>
        </div>
        <div>
            <div class="ls-stat-value" style="color:var(--accent2);"><?= number_format($stats['links_today']) ?></div>
            <div class="ls-stat-label">Created Today</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="ls-quick-row">
    <a href="/projects/linkshortner/create" class="ls-quick-card"
       style="background:linear-gradient(135deg,#0891b2,#06b6d4);box-shadow:0 4px 16px rgba(8,145,178,0.3);">
        <div class="ls-quick-icon"><i class="fas fa-plus-circle"></i></div>
        <div class="ls-quick-label">New Link</div>
        <div class="ls-quick-desc">Create with options</div>
    </a>
    <a href="/projects/linkshortner/links" class="ls-quick-card"
       style="background:linear-gradient(135deg,#7c3aed,#a855f7);box-shadow:0 4px 16px rgba(124,58,237,0.3);">
        <div class="ls-quick-icon"><i class="fas fa-list"></i></div>
        <div class="ls-quick-label">All Links</div>
        <div class="ls-quick-desc">Manage &amp; edit</div>
    </a>
    <a href="/projects/linkshortner/analytics" class="ls-quick-card"
       style="background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 4px 16px rgba(245,158,11,0.3);">
        <div class="ls-quick-icon"><i class="fas fa-chart-bar"></i></div>
        <div class="ls-quick-label">Analytics</div>
        <div class="ls-quick-desc">Click stats &amp; reports</div>
    </a>
    <a href="/projects/linkshortner/settings" class="ls-quick-card"
       style="background:linear-gradient(135deg,#059669,#10b981);box-shadow:0 4px 16px rgba(5,150,105,0.3);">
        <div class="ls-quick-icon"><i class="fas fa-cog"></i></div>
        <div class="ls-quick-label">Settings</div>
        <div class="ls-quick-desc">Preferences</div>
    </a>
</div>

<!-- Two-column content -->
<div class="ls-content-grid">
    <!-- Recent Links -->
    <div class="card">
        <div class="ls-section-header">
            <div class="ls-section-title">
                <i class="fas fa-clock" style="color:var(--ls-accent);"></i> Recent Links
            </div>
            <a href="/projects/linkshortner/links" class="btn btn-secondary btn-sm">View All</a>
        </div>
        <?php if (!empty($recentLinks)): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>Short</th><th>Title / URL</th><th>Clicks</th><th>Status</th></tr>
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
                    <td style="max-width:9rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:var(--font-xs);color:var(--text-secondary);">
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
        <div style="text-align:center;padding:2rem 0;color:var(--text-secondary);">
            <i class="fas fa-link" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:0.75rem;"></i>
            No links yet. <a href="/projects/linkshortner/create" style="color:var(--ls-accent);">Create your first link</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Top Links -->
    <div class="card">
        <div class="ls-section-header">
            <div class="ls-section-title">
                <i class="fas fa-trophy" style="color:var(--orange);"></i> Top Links
            </div>
            <a href="/projects/linkshortner/analytics" class="btn btn-secondary btn-sm">Analytics</a>
        </div>
        <?php if (!empty($topLinks)): ?>
        <div style="display:flex;flex-direction:column;gap:0.5rem;">
            <?php foreach ($topLinks as $i => $link): ?>
            <div style="display:flex;align-items:center;gap:0.75rem;padding:0.625rem 0.75rem;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:0.5rem;">
                <div style="width:1.625rem;height:1.625rem;border-radius:50%;background:<?= $i===0 ? 'rgba(255,170,0,0.2)' : 'rgba(255,255,255,0.06)' ?>;display:flex;align-items:center;justify-content:center;font-size:var(--font-xs);font-weight:700;color:<?= $i===0 ? 'var(--orange)' : 'var(--text-secondary)' ?>;flex-shrink:0;">
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
        <div style="text-align:center;padding:2rem 0;color:var(--text-secondary);">
            <i class="fas fa-chart-bar" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:0.75rem;"></i>
            No click data yet.
        </div>
        <?php endif; ?>
    </div>
</div>

<?php View::end(); ?>
