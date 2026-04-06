<?php use Core\View; use Core\Security; ?>
<?php View::extend('notex:app'); ?>
<?php View::section('content'); ?>

<style>
    .nx-notes-toolbar {
        display: flex;
        gap: 0.625rem;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
        align-items: center;
    }
    .nx-notes-toolbar .search-wrap {
        flex: 1;
        min-width: 10rem;
        position: relative;
    }
    .nx-notes-toolbar .search-wrap i {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-secondary);
        font-size: 0.8125rem;
        pointer-events: none;
    }
    .nx-notes-toolbar .search-wrap input {
        width: 100%;
        padding: 0.625rem 0.875rem 0.625rem 2.25rem;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        color: var(--text-primary);
        font-family: inherit;
        font-size: var(--font-sm);
    }
    .nx-notes-toolbar select {
        padding: 0.625rem 0.875rem;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        color: var(--text-primary);
        font-family: inherit;
        font-size: var(--font-sm);
    }
    .note-card-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .note-card-link .note-card {
        cursor: pointer;
        position: relative;
    }
    .note-card-link .note-card:hover {
        border-color: rgba(245,158,11,0.4);
        transform: translateY(-0.125rem);
        box-shadow: 0 0.375rem 1.25rem rgba(0,0,0,0.2);
    }
    .note-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 0.75rem;
        padding-top: 0.625rem;
        border-top: 1px solid var(--border-color);
    }
    .note-card-actions-row {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        z-index: 2;
        display: flex;
        gap: 0.25rem;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .note-card-link:hover .note-card-actions-row { opacity: 1; }
    .note-card-delete {
        position: static;
        z-index: auto;
        opacity: 1;
        transition: none;
    }
    .note-card-link:hover .note-card-delete { opacity: 1; }
    @media (max-width: 48rem) {
        .nx-notes-toolbar { flex-direction: column; align-items: stretch; }
        .nx-notes-toolbar .search-wrap { min-width: 0; }
        .note-card-actions-row { opacity: 1; }
    }
</style>

<!-- Search & Filter bar -->
<form method="GET" action="/projects/notex/notes" class="nx-notes-toolbar">
    <div class="search-wrap">
        <i class="fas fa-search"></i>
        <input type="text" name="q" value="<?= View::e($search ?? '') ?>" placeholder="Search notes…">
    </div>
    <select name="folder">
        <option value="">All Folders</option>
        <?php foreach ($folders as $folder): ?>
        <option value="<?= $folder['id'] ?>" <?= ($currentFolder ?? null) == $folder['id'] ? 'selected' : '' ?>><?= View::e($folder['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
    <?php if ($search || $currentFolder): ?>
    <a href="/projects/notex/notes" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
    <?php endif; ?>
    <a href="/projects/notex/create<?= $currentFolder ? '?folder=' . (int)$currentFolder : '' ?>" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> New Note
    </a>
</form>

<?php if (!empty($notes)): ?>
<div class="notes-grid">
    <?php foreach ($notes as $note): ?>
    <a href="/projects/notex/notes/<?= $note['id'] ?>/edit" class="note-card-link">
        <div class="note-card" id="nc-<?= $note['id'] ?>">
            <div class="note-card-accent" style="background:<?= View::e($note['color'] ?? '#ffd700') ?>;"></div>
            <!-- Actions: pin + delete -->
            <div class="note-card-actions-row" onclick="event.stopPropagation();event.preventDefault();">
                <button type="button"
                        class="btn btn-secondary btn-sm btn-icon pin-btn"
                        data-note-id="<?= $note['id'] ?>"
                        data-pinned="<?= $note['is_pinned'] ? '1' : '0' ?>"
                        title="<?= $note['is_pinned'] ? 'Unpin' : 'Pin' ?>"
                        style="color:<?= $note['is_pinned'] ? 'var(--nx-accent)' : 'var(--text-secondary)' ?>;">
                    <i class="fas fa-thumbtack" style="font-size:0.6875rem;"></i>
                </button>
                <form method="POST" action="/projects/notex/notes/<?= $note['id'] ?>/delete"
                      onsubmit="return confirm('Move to trash?');" class="note-card-delete">
                    <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
                    <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Delete">
                        <i class="fas fa-trash" style="font-size:0.6875rem;"></i>
                    </button>
                </form>
            </div>
            <div style="padding-left:0.625rem;">
                <div class="note-card-title">
                    <?php if ($note['is_pinned']): ?>
                        <i class="fas fa-thumbtack pin-icon nc-pin-icon" style="margin-right:0.25rem;font-size:0.75rem;"></i>
                    <?php endif; ?>
                    <?= View::e($note['title']) ?>
                </div>
                <div class="note-card-preview"><?= View::e(substr(strip_tags($note['content'] ?? ''), 0, 150)) ?></div>
                <div class="note-card-footer">
                    <span style="font-size:var(--font-xs);color:var(--text-secondary);">
                        <?= $note['folder_name'] ? '<i class="fas fa-folder" style="margin-right:0.1875rem;"></i>' . View::e($note['folder_name']) : '&nbsp;' ?>
                    </span>
                    <span style="font-size:var(--font-xs);color:var(--text-secondary);">
                        <?= date('M d', strtotime($note['updated_at'] ?? $note['created_at'])) ?>
                    </span>
                </div>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state">
    <div class="empty-icon"><i class="fas fa-sticky-note"></i></div>
    <p style="color:var(--text-secondary);margin-bottom:1rem;">No notes found.</p>
    <a href="/projects/notex/create" class="btn btn-primary"><i class="fas fa-plus"></i> Create Note</a>
</div>
<?php endif; ?>

<script>
(function() {
    var csrfToken = document.querySelector('meta[name="csrf-token"]')
        ? document.querySelector('meta[name="csrf-token"]').content : '';

    document.querySelectorAll('.pin-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var noteId = this.dataset.noteId;
            var self = this;
            fetch('/projects/notex/notes/' + noteId + '/pin', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: '_token=' + encodeURIComponent(csrfToken)
            }).then(function(r) { return r.json(); }).then(function(data) {
                if (data.success) {
                    self.dataset.pinned = data.pinned ? '1' : '0';
                    self.title = data.pinned ? 'Unpin' : 'Pin';
                    self.style.color = data.pinned ? 'var(--nx-accent)' : 'var(--text-secondary)';
                    // Update pin icon in card title
                    var card = document.getElementById('nc-' + noteId);
                    if (card) {
                        var titlePin = card.querySelector('.nc-pin-icon');
                        if (data.pinned && !titlePin) {
                            var t = card.querySelector('.note-card-title');
                            if (t) {
                                var ic = document.createElement('i');
                                ic.className = 'fas fa-thumbtack pin-icon nc-pin-icon';
                                ic.style.marginRight = '0.25rem';
                                ic.style.fontSize = '0.75rem';
                                t.insertBefore(ic, t.firstChild);
                            }
                        } else if (!data.pinned && titlePin) {
                            titlePin.remove();
                        }
                    }
                }
            }).catch(function() {});
        });
    });
})();
</script>

<?php View::end(); ?>
