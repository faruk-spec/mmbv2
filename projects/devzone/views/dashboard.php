<?php
/**
 * DevZone Dashboard View
 */
$content = '';
ob_start();
?>

<h1 class="dz-page-title" style="background:linear-gradient(135deg,var(--dz-primary),var(--dz-accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
    <i class="fas fa-terminal" style="-webkit-text-fill-color:transparent;"></i> DevZone
</h1>
<p class="dz-page-subtitle">Developer collaboration &amp; project management</p>

<!-- Stats -->
<div class="dz-grid dz-grid-4" style="margin-bottom:1.5rem;">
    <div class="dz-card" style="margin-bottom:0;text-align:center;padding:1.5rem;">
        <div style="font-size:2rem;font-weight:800;color:var(--dz-primary);"><?= (int)($stats['boards'] ?? 0) ?></div>
        <div style="font-size:0.8rem;color:var(--text-secondary);margin-top:0.25rem;">Boards</div>
    </div>
    <div class="dz-card" style="margin-bottom:0;text-align:center;padding:1.5rem;">
        <div style="font-size:2rem;font-weight:800;color:var(--dz-accent);"><?= (int)($stats['tasks'] ?? 0) ?></div>
        <div style="font-size:0.8rem;color:var(--text-secondary);margin-top:0.25rem;">Active Tasks</div>
    </div>
    <div class="dz-card" style="margin-bottom:0;text-align:center;padding:1.5rem;">
        <div style="font-size:2rem;font-weight:800;color:var(--dz-warning);"><?= (int)($stats['due_soon'] ?? 0) ?></div>
        <div style="font-size:0.8rem;color:var(--text-secondary);margin-top:0.25rem;">Due This Week</div>
    </div>
    <div class="dz-card" style="margin-bottom:0;text-align:center;padding:1.5rem;">
        <div style="font-size:2rem;font-weight:800;color:var(--dz-success);"><?= (int)($stats['members'] ?? 0) ?></div>
        <div style="font-size:0.8rem;color:var(--text-secondary);margin-top:0.25rem;">Team Members</div>
    </div>
</div>

<!-- Boards -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:0.75rem;">
    <h2 style="font-size:1rem;font-weight:700;">My Boards</h2>
    <a href="/projects/devzone/boards/create" class="dz-btn dz-btn-primary">
        <i class="fas fa-plus"></i> New Board
    </a>
</div>

<?php if (!empty($boards)): ?>
<div class="dz-grid dz-grid-3">
    <?php foreach ($boards as $board): ?>
    <a href="/projects/devzone/boards/<?= (int)$board['id'] ?>" class="dz-card" style="margin-bottom:0;text-decoration:none;display:block;border-top:3px solid <?= htmlspecialchars($board['color'] ?? '#ff2ec4') ?>;">
        <div style="font-size:0.9rem;font-weight:700;color:var(--text-primary);margin-bottom:0.25rem;"><?= htmlspecialchars($board['name']) ?></div>
        <?php if (!empty($board['description'])): ?>
        <div style="font-size:0.78rem;color:var(--text-secondary);margin-bottom:0.75rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($board['description']) ?></div>
        <?php endif; ?>
        <div style="font-size:0.75rem;color:var(--text-secondary);">
            <i class="fas fa-tasks" style="color:<?= htmlspecialchars($board['color'] ?? '#ff2ec4') ?>;"></i>
            <?= (int)($board['task_count'] ?? 0) ?> tasks
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="dz-card" style="text-align:center;padding:3.5rem 1.5rem;">
    <i class="fas fa-columns" style="font-size:3rem;opacity:0.15;display:block;margin-bottom:1rem;"></i>
    <p style="color:var(--text-secondary);font-size:0.875rem;margin-bottom:1.25rem;">No boards yet. Create your first project board to get started.</p>
    <a href="/projects/devzone/boards/create" class="dz-btn dz-btn-primary">
        <i class="fas fa-plus"></i> Create First Board
    </a>
</div>
<?php endif; ?>

<!-- Coming Soon Features -->
<div class="dz-card" style="margin-top:1.5rem;background:linear-gradient(135deg,rgba(255,46,196,0.05),rgba(0,240,255,0.05));border-color:rgba(255,46,196,0.15);">
    <div style="font-size:0.875rem;font-weight:700;margin-bottom:0.875rem;display:flex;align-items:center;gap:0.5rem;">
        <i class="fas fa-rocket" style="color:var(--dz-primary);"></i> Coming Soon
    </div>
    <div class="dz-grid dz-grid-3">
        <div style="display:flex;align-items:center;gap:0.625rem;font-size:0.825rem;color:var(--text-secondary);padding:0.625rem;background:var(--bg-secondary);border-radius:0.5rem;">
            <i class="fas fa-comments" style="color:var(--dz-accent);width:1rem;"></i> Team Chat
        </div>
        <div style="display:flex;align-items:center;gap:0.625rem;font-size:0.825rem;color:var(--text-secondary);padding:0.625rem;background:var(--bg-secondary);border-radius:0.5rem;">
            <i class="fas fa-code-branch" style="color:var(--dz-primary);width:1rem;"></i> Code Review
        </div>
        <div style="display:flex;align-items:center;gap:0.625rem;font-size:0.825rem;color:var(--text-secondary);padding:0.625rem;background:var(--bg-secondary);border-radius:0.5rem;">
            <i class="fas fa-infinity" style="color:var(--dz-success);width:1rem;"></i> CI/CD Pipeline
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
