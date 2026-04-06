<?php use Core\View; use Core\Security; ?>
<?php View::extend('notex:app'); ?>
<?php View::section('content'); ?>

<style>
    .nx-folders-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    /* Windows 11-style folder grid */
    .nx-folder-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(9rem, 1fr));
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }
    .nx-folder-tile {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 1.25rem 0.75rem 0.875rem;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 0.625rem;
        cursor: pointer;
        transition: all 0.15s ease;
        text-decoration: none;
        color: inherit;
        position: relative;
        text-align: center;
        gap: 0.5rem;
    }
    .nx-folder-tile:hover {
        background: var(--bg-secondary);
        border-color: rgba(245,158,11,0.4);
        transform: translateY(-0.125rem);
        box-shadow: 0 0.25rem 1rem rgba(0,0,0,0.2);
    }
    .nx-folder-tile:hover .nx-folder-delete { opacity: 1; }
    .nx-folder-icon {
        font-size: 2.5rem;
        line-height: 1;
        filter: drop-shadow(0 2px 6px rgba(0,0,0,0.3));
    }
    .nx-folder-name {
        font-size: var(--font-sm);
        font-weight: 500;
        word-break: break-word;
        line-height: 1.3;
    }
    .nx-folder-count {
        font-size: var(--font-xs);
        color: var(--text-secondary);
    }
    .nx-folder-delete {
        position: absolute;
        top: 0.375rem;
        right: 0.375rem;
        opacity: 0;
        transition: opacity 0.15s;
    }
    .nx-folder-delete button {
        background: rgba(255,71,87,0.15);
        border: 1px solid rgba(255,107,107,0.4);
        border-radius: 0.375rem;
        color: var(--red);
        cursor: pointer;
        padding: 0.1875rem 0.375rem;
        font-size: 0.625rem;
        line-height: 1;
        transition: background 0.2s;
    }
    .nx-folder-delete button:hover { background: rgba(255,71,87,0.3); }
    /* Create folder form */
    .nx-create-folder-form {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        align-items: flex-end;
    }
    @media (max-width: 36rem) {
        .nx-folder-grid { grid-template-columns: repeat(auto-fill, minmax(7rem, 1fr)); }
        .nx-folder-icon { font-size: 2rem; }
    }
    @media (max-width: 48rem) {
        .nx-folder-delete { opacity: 1; }
    }
</style>

<div class="nx-folders-header">
    <h2 style="font-size:var(--font-xl);font-weight:700;display:flex;align-items:center;gap:0.5rem;">
        <i class="fas fa-folder" style="color:var(--nx-accent);"></i> Folders
    </h2>
    <a href="/projects/notex/notes" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> All Notes
    </a>
</div>

<?php if (!empty($folders)): ?>
<div class="nx-folder-grid">
    <?php foreach ($folders as $folder): ?>
    <a href="/projects/notex/notes?folder=<?= $folder['id'] ?>" class="nx-folder-tile">
        <!-- Delete button (stop propagation) -->
        <div class="nx-folder-delete" onclick="event.preventDefault();event.stopPropagation();">
            <form method="POST" action="/projects/notex/folders/<?= $folder['id'] ?>/delete"
                  onsubmit="return confirm('Delete folder? Notes will not be deleted.');">
                <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
                <button type="submit" title="Delete folder"><i class="fas fa-times"></i></button>
            </form>
        </div>
        <div class="nx-folder-icon">
            <i class="fas fa-folder" style="color:<?= View::e($folder['color']) ?>;"></i>
        </div>
        <div class="nx-folder-name"><?= View::e($folder['name']) ?></div>
        <div class="nx-folder-count"><?= $folder['note_count'] ?> note<?= $folder['note_count'] != 1 ? 's' : '' ?></div>
    </a>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state" style="padding:3rem 1rem;">
    <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
    <p style="color:var(--text-secondary);">No folders yet. Create one below.</p>
</div>
<?php endif; ?>

<!-- Create folder -->
<div class="card">
    <div style="font-size:var(--font-sm);font-weight:600;margin-bottom:0.875rem;display:flex;align-items:center;gap:0.375rem;">
        <i class="fas fa-folder-plus" style="color:var(--nx-accent);"></i> New Folder
    </div>
    <form method="POST" action="/projects/notex/folders/create" class="nx-create-folder-form">
        <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
        <div class="form-group" style="margin:0;flex:1;min-width:10rem;">
            <label class="form-label">Folder Name</label>
            <input type="text" name="name" class="form-input" placeholder="e.g. Work" required>
        </div>
        <div class="form-group" style="margin:0;">
            <label class="form-label">Color</label>
            <input type="color" name="color" class="form-input" value="#f59e0b"
                   style="height:2.75rem;width:3.75rem;padding:0.25rem;cursor:pointer;">
        </div>
        <div style="padding-bottom:1.125rem;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-folder-plus"></i> Create
            </button>
        </div>
    </form>
</div>

<?php View::end(); ?>
