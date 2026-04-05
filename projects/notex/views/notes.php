<?php use Core\View; use Core\Security; ?>
<?php View::extend('notex:app'); ?>
<?php View::section('content'); ?>

<!-- Search & Filter bar -->
<form method="GET" action="/projects/notex/notes" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
    <input type="text" name="q" value="<?= View::e($search ?? '') ?>" placeholder="Search notes…"
           style="flex:1;min-width:180px;padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-family:inherit;font-size:14px;">
    <select name="folder" style="padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-family:inherit;font-size:14px;">
        <option value="">All Folders</option>
        <?php foreach ($folders as $folder): ?>
        <option value="<?= $folder['id'] ?>" <?= ($currentFolder ?? null) == $folder['id'] ? 'selected' : '' ?>><?= View::e($folder['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i></button>
    <?php if ($search || $currentFolder): ?>
    <a href="/projects/notex/notes" class="btn btn-secondary"><i class="fas fa-times"></i> Clear</a>
    <?php endif; ?>
    <a href="/projects/notex/create" class="btn btn-primary"><i class="fas fa-plus"></i> New Note</a>
</form>

<?php if (!empty($notes)): ?>
<div class="notes-grid">
    <?php foreach ($notes as $note): ?>
    <div class="note-card">
        <div class="note-card-accent" style="background:<?= View::e($note['color'] ?? '#ffd700') ?>;"></div>
        <div style="padding-left:8px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:8px;">
                <a href="/projects/notex/notes/<?= $note['id'] ?>/edit" class="note-card-title" style="text-decoration:none;color:inherit;flex:1;">
                    <?php if ($note['is_pinned']): ?><i class="fas fa-thumbtack pin-icon" style="margin-right:4px;"></i><?php endif; ?>
                    <?= View::e($note['title']) ?>
                </a>
            </div>
            <div class="note-card-preview"><?= View::e(substr(strip_tags($note['content'] ?? ''), 0, 150)) ?></div>
            <div class="note-card-meta">
                <span><?= $note['folder_name'] ? '<i class="fas fa-folder" style="margin-right:3px;font-size:11px;"></i>' . View::e($note['folder_name']) : '&nbsp;' ?></span>
                <span><?= date('M d', strtotime($note['updated_at'] ?? $note['created_at'])) ?></span>
            </div>
            <div class="note-card-actions" style="margin-top:10px;">
                <a href="/projects/notex/notes/<?= $note['id'] ?>/edit" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i></a>
                <form method="POST" action="/projects/notex/notes/<?= $note['id'] ?>/delete" onsubmit="return confirm('Move to trash?');" style="display:inline;">
                    <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div style="text-align:center;padding:60px 0;color:var(--text-secondary);">
    <i class="fas fa-sticky-note" style="font-size:3rem;opacity:0.3;display:block;margin-bottom:16px;"></i>
    <p>No notes found.</p>
    <a href="/projects/notex/create" class="btn btn-primary" style="margin-top:16px;"><i class="fas fa-plus"></i> Create Note</a>
</div>
<?php endif; ?>

<?php View::end(); ?>
