<?php
/**
 * DevZone – All Boards view
 */
$currentView = 'boards';
?>

<!-- Page header -->
<div class="page-header">
    <h1><i class="fas fa-columns" style="-webkit-text-fill-color:transparent;"></i> My Boards</h1>
    <p>All your Kanban boards in one place</p>
</div>

<!-- Toolbar -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
    <div style="font-size:.875rem;color:var(--text-secondary);">
        <span style="font-weight:600;color:var(--text-primary);"><?= count($boards ?? []) ?></span> board<?= count($boards ?? []) !== 1 ? 's' : '' ?>
    </div>
    <a href="/projects/devzone/boards/create" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> New Board
    </a>
</div>

<?php if (empty($boards)): ?>
<div class="card" style="text-align:center;padding:3.5rem 1.5rem;">
    <i class="fa-solid fa-columns" style="font-size:3rem;background:linear-gradient(135deg,var(--dz-primary),var(--dz-secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;display:block;margin-bottom:1rem;opacity:.7;"></i>
    <p style="color:var(--text-secondary);margin-bottom:1.25rem;">No boards yet. Create your first project board to get started.</p>
    <a href="/projects/devzone/boards/create" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Create First Board
    </a>
</div>

<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;">
    <?php foreach ($boards as $board): ?>
    <div class="card" style="margin-bottom:0;border-top:3px solid <?= htmlspecialchars($board['color'] ?? '#ff2ec4') ?>;display:flex;flex-direction:column;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;margin-bottom:.625rem;">
            <a href="/projects/devzone/boards/<?= (int)$board['id'] ?>" style="font-size:.95rem;font-weight:700;color:var(--text-primary);text-decoration:none;flex:1;">
                <?= htmlspecialchars($board['name']) ?>
            </a>
            <div style="display:flex;gap:.375rem;flex-shrink:0;">
                <a href="/projects/devzone/boards/<?= (int)$board['id'] ?>/edit" class="btn btn-secondary btn-sm" title="Edit board">
                    <i class="fa-solid fa-pen-to-square"></i>
                </a>
                <form method="POST" action="/projects/devzone/boards/<?= (int)$board['id'] ?>/delete"
                      onsubmit="return confirm('Delete board \'<?= addslashes(htmlspecialchars($board['name'])) ?>\'? This will also delete all tasks.');"
                      style="display:inline;">
                    <?= \Core\Security::csrfField() ?>
                    <button type="submit" class="btn btn-danger btn-sm" title="Delete board">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        <?php if (!empty($board['description'])): ?>
        <p style="font-size:.8rem;color:var(--text-secondary);margin-bottom:.75rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
            <?= htmlspecialchars($board['description']) ?>
        </p>
        <?php endif; ?>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;padding-top:.625rem;">
            <span style="font-size:.78rem;color:var(--text-secondary);">
                <i class="fas fa-tasks" style="color:<?= htmlspecialchars($board['color'] ?? '#ff2ec4') ?>;margin-right:.25rem;"></i>
                <?= (int)($board['task_count'] ?? 0) ?> task<?= (int)($board['task_count'] ?? 0) !== 1 ? 's' : '' ?>
            </span>
            <a href="/projects/devzone/boards/<?= (int)$board['id'] ?>" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-arrow-right"></i> Open
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
