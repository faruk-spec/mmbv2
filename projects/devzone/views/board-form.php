<?php
/**
 * DevZone – Create / Edit Board form
 */
$currentView = 'board-form';
$editing     = !empty($board['id']);
$board       = $board ?? [];
?>

<!-- Page header -->
<div class="page-header">
    <h1><i class="fas fa-columns" style="-webkit-text-fill-color:transparent;"></i>
        <?= $editing ? 'Edit Board' : 'New Board' ?>
    </h1>
    <p><?= $editing ? 'Update board settings' : 'Create a new Kanban board' ?></p>
</div>

<div style="max-width:600px;">
    <div class="card">
        <div class="card-header">
            <i class="fa-solid <?= $editing ? 'fa-pen-to-square' : 'fa-plus' ?>"></i>
            <?= $editing ? 'Edit' : 'Create' ?> Board
        </div>

        <form method="POST"
              action="<?= $editing ? '/projects/devzone/boards/' . (int)$board['id'] . '/update' : '/projects/devzone/boards/store' ?>">
            <?= \Core\Security::csrfField() ?>

            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-heading" style="color:var(--dz-primary);"></i> Board Name <span style="color:var(--dz-danger);">*</span>
                </label>
                <input type="text" name="name" class="form-control" required maxlength="100"
                       placeholder="e.g. Product Roadmap, Sprint 12…"
                       value="<?= htmlspecialchars($board['name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-align-left" style="color:var(--dz-primary);"></i> Description
                </label>
                <textarea name="description" class="form-control" rows="3" maxlength="500"
                          placeholder="What is this board for?"
                          style="resize:vertical;"><?= htmlspecialchars($board['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group" style="margin-bottom:var(--space-2xl);">
                <label class="form-label">
                    <i class="fa-solid fa-palette" style="color:var(--dz-primary);"></i> Board Color
                </label>
                <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <input type="color" name="color" id="boardColor"
                           value="<?= htmlspecialchars($board['color'] ?? '#ff2ec4') ?>"
                           style="width:56px;height:40px;padding:.15rem;border:1px solid var(--border-color);border-radius:.5rem;background:var(--bg-secondary);cursor:pointer;">
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                        <?php foreach (['#ff2ec4','#00f0ff','#9945ff','#ffaa00','#00ff88','#ff6b6b','#6366f1','#0891b2'] as $c): ?>
                        <button type="button" onclick="document.getElementById('boardColor').value='<?= $c ?>'"
                                style="width:28px;height:28px;border-radius:50%;background:<?= $c ?>;border:2px solid transparent;cursor:pointer;transition:border-color .15s;"
                                onmouseover="this.style.borderColor='#fff'" onmouseout="this.style.borderColor='transparent'"></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-<?= $editing ? 'floppy-disk' : 'plus' ?>"></i>
                    <?= $editing ? 'Save Changes' : 'Create Board' ?>
                </button>
                <a href="/projects/devzone/boards" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
            </div>
        </form>
    </div>
</div>
