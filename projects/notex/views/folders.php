<?php use Core\View; use Core\Security; ?>
<?php View::extend('notex:app'); ?>
<?php View::section('content'); ?>

<!-- Folders List -->
<div class="card mb-4">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-folder" style="color:var(--accent);"></i> Folders</div>
    </div>

    <?php if (!empty($folders)): ?>
    <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:20px;">
        <?php foreach ($folders as $folder): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:var(--bg-secondary);border-radius:10px;">
            <i class="fas fa-folder" style="color:<?= View::e($folder['color']) ?>;font-size:1.2rem;"></i>
            <div style="flex:1;">
                <div style="font-weight:500;"><?= View::e($folder['name']) ?></div>
                <div style="color:var(--text-secondary);font-size:12px;"><?= $folder['note_count'] ?> notes</div>
            </div>
            <a href="/projects/notex/notes?folder=<?= $folder['id'] ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-eye"></i> View
            </a>
            <form method="POST" action="/projects/notex/folders/<?= $folder['id'] ?>/delete" onsubmit="return confirm('Delete folder? Notes will not be deleted.');" style="display:inline;">
                <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p style="color:var(--text-secondary);margin-bottom:20px;">No folders yet.</p>
    <?php endif; ?>

    <!-- Create folder form -->
    <div style="border-top:1px solid var(--border-color);padding-top:18px;">
        <div class="card-title" style="margin-bottom:14px;"><i class="fas fa-plus"></i> New Folder</div>
        <form method="POST" action="/projects/notex/folders/create" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
            <div class="form-group" style="margin:0;flex:1;min-width:160px;">
                <label class="form-label">Folder Name</label>
                <input type="text" name="name" class="form-input" placeholder="e.g. Work" required>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Color</label>
                <input type="color" name="color" class="form-input" value="#ffd700" style="height:44px;width:60px;padding:4px;cursor:pointer;">
            </div>
            <button type="submit" class="btn btn-primary" style="align-self:flex-end;margin-bottom:18px;"><i class="fas fa-folder-plus"></i> Create</button>
        </form>
    </div>
</div>

<?php View::end(); ?>
