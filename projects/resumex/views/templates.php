<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<style>
.rxt-wrap {
    max-width: 1100px;
    margin: 0 auto;
    padding: 36px 24px 60px;
}
.rxt-back {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: var(--text-secondary);
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    margin-bottom: 32px;
    transition: color 0.2s;
}
.rxt-back:hover { color: var(--cyan); text-decoration: none; }
.rxt-header {
    text-align: center;
    margin-bottom: 40px;
}
.rxt-header h1 {
    font-size: clamp(1.8rem, 4vw, 2.4rem);
    font-weight: 800;
    letter-spacing: -0.5px;
    background: linear-gradient(135deg, var(--cyan) 0%, var(--purple) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0 0 10px;
}
.rxt-header p { color: var(--text-secondary); font-size: 0.95rem; margin: 0; }

.rxt-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
}
.rxt-card {
    border-radius: 14px;
    border: 1px solid var(--border-color);
    background: var(--bg-card);
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
    text-decoration: none;
    display: block;
}
.rxt-card:hover {
    transform: translateY(-4px);
    border-color: rgba(0,240,255,0.35);
    box-shadow: 0 12px 32px rgba(0,0,0,0.3);
    text-decoration: none;
}
.rxt-card-preview {
    height: 100px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    padding: 12px;
    gap: 5px;
    position: relative;
    overflow: hidden;
}
.rxt-preview-line {
    height: 5px;
    border-radius: 3px;
    flex-shrink: 0;
}
.rxt-preview-block {
    height: 28px;
    border-radius: 4px;
    flex-shrink: 0;
    opacity: 0.2;
}
.rxt-card-footer {
    padding: 10px 12px;
    border-top: 1px solid rgba(255,255,255,0.06);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
}
.rxt-card-name {
    font-size: 0.8rem;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: var(--text-primary);
}
.rxt-card-cat {
    font-size: 0.68rem;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 10px;
    background: rgba(255,255,255,0.06);
    color: var(--text-secondary);
    white-space: nowrap;
}
</style>

<div class="rxt-wrap">
    <a href="/projects/resumex" class="rxt-back">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
        Back to Dashboard
    </a>

    <div class="rxt-header">
        <h1>Resume Templates</h1>
        <p><?= count($allThemes) ?> professionally designed themes — pick one and start building.</p>
        <?php if (!empty($isAdmin)): ?>
            <div style="margin-top:14px;">
                <a href="/projects/resumex/templates/upload" style="display:inline-flex;align-items:center;gap:7px;padding:8px 18px;border-radius:8px;background:linear-gradient(135deg,var(--cyan),var(--purple));color:#fff;font-size:0.85rem;font-weight:600;text-decoration:none;transition:opacity 0.2s;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                    Upload / Manage Templates
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="rxt-grid">
        <?php foreach ($allThemes as $theme): ?>
        <a href="/projects/resumex/create" class="rxt-card" title="<?= htmlspecialchars($theme['name']) ?>">
            <div class="rxt-card-preview" style="background: <?= htmlspecialchars($theme['backgroundColor']) ?>;">
                <!-- Mock header block -->
                <div class="rxt-preview-line" style="background: <?= htmlspecialchars($theme['primaryColor']) ?>; width: 55%;"></div>
                <div class="rxt-preview-line" style="background: <?= htmlspecialchars($theme['secondaryColor']) ?>; width: 35%; opacity: 0.7;"></div>
                <!-- Mock content rows -->
                <div style="margin-top: 6px; display: flex; flex-direction: column; gap: 4px;">
                    <div class="rxt-preview-line" style="background: <?= htmlspecialchars($theme['textColor']) ?>; width: 80%; opacity: 0.3;"></div>
                    <div class="rxt-preview-line" style="background: <?= htmlspecialchars($theme['textColor']) ?>; width: 65%; opacity: 0.2;"></div>
                    <div class="rxt-preview-line" style="background: <?= htmlspecialchars($theme['textColor']) ?>; width: 72%; opacity: 0.2;"></div>
                </div>
                <!-- Bottom accent -->
                <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, <?= htmlspecialchars($theme['primaryColor']) ?>, <?= htmlspecialchars($theme['secondaryColor']) ?>);"></div>
            </div>
            <div class="rxt-card-footer" style="background: <?= htmlspecialchars($theme['surfaceColor']) ?>;">
                <span class="rxt-card-name" style="color: <?= htmlspecialchars($theme['textColor']) ?>;"><?= htmlspecialchars($theme['name']) ?></span>
                <span class="rxt-card-cat"><?= htmlspecialchars(ucfirst($theme['category'] ?? '')) ?></span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<?php View::end(); ?>
