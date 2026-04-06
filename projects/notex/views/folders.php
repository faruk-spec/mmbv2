<?php use Core\View; use Core\Security; ?>
<?php View::extend('notex:app'); ?>
<?php View::section('content'); ?>

<?php $view = (isset($_GET['view']) && $_GET['view'] === 'list') ? 'list' : 'grid'; ?>

<style>
    /* ── Header ── */
    .nx-folders-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;
    }
    .nx-view-toggle {
        display: flex; gap: 0.25rem;
        background: var(--bg-secondary); border: 1px solid var(--border-color);
        border-radius: 0.5rem; padding: 0.1875rem;
    }
    .nx-view-toggle a {
        display: flex; align-items: center; justify-content: center;
        width: 2rem; height: 2rem; border-radius: 0.375rem;
        color: var(--text-secondary); text-decoration: none; font-size: 0.8125rem;
        transition: all 0.15s;
    }
    .nx-view-toggle a.active { background: var(--nx-accent); color: white; }
    .nx-view-toggle a:not(.active):hover { background: var(--bg-card); color: var(--text-primary); }

    /* ── Grid view ── */
    .nx-folder-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(9rem, 1fr));
        gap: 0.75rem; margin-bottom: 1.5rem;
    }
    .nx-folder-tile {
        display: flex; flex-direction: column; align-items: center;
        padding: 1.25rem 0.75rem 0.875rem;
        background: var(--bg-card); border: 1px solid var(--border-color);
        border-radius: 0.625rem; cursor: pointer; transition: all 0.15s ease;
        text-decoration: none; color: inherit; position: relative;
        text-align: center; gap: 0.5rem;
    }
    .nx-folder-tile:hover {
        background: var(--bg-secondary); border-color: rgba(245,158,11,0.4);
        transform: translateY(-0.125rem); box-shadow: 0 0.25rem 1rem rgba(0,0,0,0.2);
    }
    .nx-folder-tile:hover .nx-tile-actions { opacity: 1; }
    .nx-folder-icon { font-size: 2.5rem; line-height: 1; filter: drop-shadow(0 2px 6px rgba(0,0,0,0.3)); }
    .nx-folder-name { font-size: var(--font-sm); font-weight: 500; word-break: break-word; line-height: 1.3; }
    .nx-folder-count { font-size: var(--font-xs); color: var(--text-secondary); }
    .nx-tile-actions {
        position: absolute; top: 0.375rem; right: 0.375rem;
        opacity: 0; transition: opacity 0.15s;
        display: flex; gap: 0.25rem;
    }
    .nx-tile-btn {
        background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.15);
        border-radius: 0.375rem; color: var(--text-secondary);
        cursor: pointer; padding: 0.25rem 0.375rem; font-size: 0.625rem;
        line-height: 1; transition: all 0.15s;
    }
    .nx-tile-btn:hover { color: var(--text-primary); background: rgba(0,0,0,0.7); }
    .nx-tile-btn.danger { color: var(--red); }
    .nx-tile-btn.danger:hover { background: rgba(255,71,87,0.3); border-color: rgba(255,107,107,0.4); }

    /* ── List view ── */
    .nx-folder-list { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1.5rem; }
    .nx-folder-row {
        display: flex; align-items: center; gap: 0.875rem;
        padding: 0.75rem 1rem; background: var(--bg-card);
        border: 1px solid var(--border-color); border-radius: 0.625rem;
        transition: all 0.15s; position: relative;
    }
    .nx-folder-row:hover { border-color: rgba(245,158,11,0.3); background: var(--bg-secondary); }
    .nx-row-icon { font-size: 1.375rem; flex-shrink: 0; filter: drop-shadow(0 1px 4px rgba(0,0,0,0.3)); }
    .nx-row-info { flex: 1; min-width: 0; }
    .nx-row-name { font-size: var(--font-sm); font-weight: 500; }
    .nx-row-meta { font-size: var(--font-xs); color: var(--text-secondary); margin-top: 0.125rem; }
    .nx-row-actions { display: flex; gap: 0.375rem; flex-shrink: 0; }

    /* ── Rename modal ── */
    .nx-rename-backdrop {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.6); z-index: 200;
        align-items: center; justify-content: center; padding: 1rem;
    }
    .nx-rename-backdrop.open { display: flex; }
    .nx-rename-modal {
        background: var(--bg-card); border: 1px solid var(--border-color);
        border-radius: 0.75rem; padding: 1.5rem; width: 100%; max-width: 22rem;
    }
    .nx-rename-modal h3 { font-size: var(--font-md); font-weight: 600; margin-bottom: 1rem; }

    @media (max-width: 36rem) {
        .nx-folder-grid { grid-template-columns: repeat(auto-fill, minmax(7rem, 1fr)); }
        .nx-folder-icon { font-size: 2rem; }
        .nx-tile-actions { opacity: 1; }
    }
    @media (max-width: 48rem) { .nx-tile-actions { opacity: 1; } }
</style>

<!-- Header -->
<div class="nx-folders-header">
    <h2 style="font-size:var(--font-xl);font-weight:700;display:flex;align-items:center;gap:0.5rem;">
        <i class="fas fa-folder" style="color:var(--nx-accent);"></i> Folders
        <?php if (!empty($folders)): ?>
        <span style="font-size:var(--font-sm);font-weight:400;color:var(--text-secondary);">(<?= count($folders) ?>)</span>
        <?php endif; ?>
    </h2>
    <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
        <!-- View toggle -->
        <div class="nx-view-toggle">
            <a href="?view=grid" class="<?= $view === 'grid' ? 'active' : '' ?>" title="Grid view">
                <i class="fas fa-th"></i>
            </a>
            <a href="?view=list" class="<?= $view === 'list' ? 'active' : '' ?>" title="List view">
                <i class="fas fa-list"></i>
            </a>
        </div>
        <a href="/projects/notex/notes" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> All Notes
        </a>
    </div>
</div>

<?php if (!empty($folders)): ?>

<?php if ($view === 'grid'): ?>
<!-- ── Grid view ── -->
<div class="nx-folder-grid">
    <?php foreach ($folders as $folder): ?>
    <a href="/projects/notex/notes?folder=<?= $folder['id'] ?>" class="nx-folder-tile">
        <div class="nx-tile-actions" onclick="event.preventDefault();event.stopPropagation();">
            <button type="button" class="nx-tile-btn"
                    data-action="rename"
                    data-folder-id="<?= $folder['id'] ?>"
                    data-folder-name="<?= View::e($folder['name']) ?>"
                    title="Rename">
                <i class="fas fa-pencil-alt"></i>
            </button>
            <button type="button" class="nx-tile-btn danger"
                    data-action="delete"
                    data-folder-id="<?= $folder['id'] ?>"
                    data-folder-name="<?= View::e($folder['name']) ?>"
                    title="Delete">
                <i class="fas fa-times"></i>
            </button>
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
<!-- ── List view ── -->
<div class="nx-folder-list">
    <?php foreach ($folders as $folder): ?>
    <div class="nx-folder-row">
        <div class="nx-row-icon">
            <i class="fas fa-folder" style="color:<?= View::e($folder['color']) ?>;"></i>
        </div>
        <div class="nx-row-info">
            <div class="nx-row-name"><?= View::e($folder['name']) ?></div>
            <div class="nx-row-meta"><?= $folder['note_count'] ?> note<?= $folder['note_count'] != 1 ? 's' : '' ?></div>
        </div>
        <div class="nx-row-actions">
            <a href="/projects/notex/notes?folder=<?= $folder['id'] ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-eye"></i> View
            </a>
            <button type="button" class="btn btn-secondary btn-sm"
                    data-action="rename"
                    data-folder-id="<?= $folder['id'] ?>"
                    data-folder-name="<?= View::e($folder['name']) ?>">
                <i class="fas fa-pencil-alt"></i> Rename
            </button>
            <button type="button" class="btn btn-danger btn-sm"
                    data-action="delete"
                    data-folder-id="<?= $folder['id'] ?>"
                    data-folder-name="<?= View::e($folder['name']) ?>">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

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
    <form method="POST" action="/projects/notex/folders/create"
          style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end;">
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

<!-- Rename Modal -->
<div class="nx-rename-backdrop" id="renameBackdrop" onclick="closeRename()">
    <div class="nx-rename-modal" onclick="event.stopPropagation()">
        <h3><i class="fas fa-pencil-alt" style="color:var(--nx-accent);margin-right:0.5rem;"></i> Rename Folder</h3>
        <form method="POST" id="renameForm">
            <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
            <div class="form-group">
                <label class="form-label">New Name</label>
                <input type="text" name="name" id="renameInput" class="form-input" required
                       placeholder="Folder name…" autofocus>
            </div>
            <div style="display:flex;gap:0.5rem;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                <button type="button" class="btn btn-secondary" onclick="closeRename()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete forms (hidden, one per folder) -->
<?php foreach ($folders as $folder): ?>
<form method="POST" action="/projects/notex/folders/<?= $folder['id'] ?>/delete"
      id="deleteForm<?= $folder['id'] ?>" style="display:none;">
    <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
</form>
<?php endforeach; ?>

<script>
function openRename(id, currentName) {
    document.getElementById('renameForm').action = '/projects/notex/folders/' + id + '/rename';
    document.getElementById('renameInput').value = currentName;
    document.getElementById('renameBackdrop').classList.add('open');
    setTimeout(function() { document.getElementById('renameInput').focus(); }, 50);
}
function closeRename() {
    document.getElementById('renameBackdrop').classList.remove('open');
}
document.addEventListener('click', function(e) {
    var btn = e.target.closest('[data-action]');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    var action = btn.dataset.action;
    var folderId = btn.dataset.folderId;
    var folderName = btn.dataset.folderName;
    if (action === 'rename') {
        openRename(folderId, folderName);
    } else if (action === 'delete') {
        if (confirm('Delete folder "' + folderName + '"? Notes will not be deleted.')) {
            var form = document.getElementById('deleteForm' + folderId);
            if (form) form.submit();
        }
    }
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeRename();
});
</script>

<?php View::end(); ?>
