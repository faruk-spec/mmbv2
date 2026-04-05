<?php use Core\View; ?>
<?php View::extend('notex:app'); ?>
<?php View::section('content'); ?>

<!-- Stats -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-value" style="color:var(--accent);"><?= $stats['total_notes'] ?></div>
        <div class="stat-label"><i class="fas fa-sticky-note" style="margin-right:5px;"></i> Total Notes</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:var(--cyan);"><?= $stats['pinned_notes'] ?></div>
        <div class="stat-label"><i class="fas fa-thumbtack" style="margin-right:5px;"></i> Pinned</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:var(--green);"><?= $stats['total_folders'] ?></div>
        <div class="stat-label"><i class="fas fa-folder" style="margin-right:5px;"></i> Folders</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:var(--accent2);"><?= $stats['total_tags'] ?></div>
        <div class="stat-label"><i class="fas fa-tag" style="margin-right:5px;"></i> Tags</div>
    </div>
</div>

<!-- Pinned Notes -->
<?php if (!empty($pinnedNotes)): ?>
<div class="card mb-4">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-thumbtack" style="color:var(--accent);"></i> Pinned Notes</div>
    </div>
    <div class="notes-grid">
        <?php foreach ($pinnedNotes as $note): ?>
        <a href="/projects/notex/notes/<?= $note['id'] ?>/edit" style="text-decoration:none;color:inherit;">
            <div class="note-card">
                <div class="note-card-accent" style="background:<?= View::e($note['color'] ?? '#ffd700') ?>;"></div>
                <div style="padding-left:8px;">
                    <div class="note-card-title">
                        <i class="fas fa-thumbtack pin-icon" style="margin-right:4px;"></i>
                        <?= View::e($note['title']) ?>
                    </div>
                    <div class="note-card-preview"><?= View::e(substr(strip_tags($note['content'] ?? ''), 0, 120)) ?></div>
                    <div class="note-card-meta">
                        <span><?= date('M d', strtotime($note['updated_at'] ?? $note['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Recent Notes + Folders -->
<div class="grid-2 mb-4">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-clock" style="color:var(--cyan);"></i> Recent Notes</div>
            <a href="/projects/notex/notes" class="btn btn-secondary btn-sm">View All</a>
        </div>
        <?php if (!empty($recentNotes)): ?>
        <div class="table-container">
            <table>
                <thead><tr><th>Title</th><th>Folder</th><th>Updated</th></tr></thead>
                <tbody>
                <?php foreach ($recentNotes as $note): ?>
                <tr onclick="location.href='/projects/notex/notes/<?= $note['id'] ?>/edit'" style="cursor:pointer;">
                    <td>
                        <?php if ($note['is_pinned']): ?>
                            <i class="fas fa-thumbtack pin-icon" style="margin-right:4px;"></i>
                        <?php endif; ?>
                        <?= View::e($note['title']) ?>
                    </td>
                    <td style="color:var(--text-secondary);font-size:12px;"><?= View::e($note['folder_name'] ?? '–') ?></td>
                    <td style="color:var(--text-secondary);font-size:12px;"><?= date('M d', strtotime($note['updated_at'] ?? $note['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p style="color:var(--text-secondary);text-align:center;padding:20px 0;">
            No notes yet. <a href="/projects/notex/create" style="color:var(--accent);">Create your first note</a>
        </p>
        <?php endif; ?>
    </div>

    <!-- Folders -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-folder" style="color:var(--accent);"></i> Folders</div>
            <a href="/projects/notex/folders" class="btn btn-secondary btn-sm">Manage</a>
        </div>
        <?php if (!empty($folders)): ?>
        <div style="display:flex;flex-direction:column;gap:8px;">
            <?php foreach ($folders as $folder): ?>
            <a href="/projects/notex/notes?folder=<?= $folder['id'] ?>" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--bg-secondary);border-radius:8px;text-decoration:none;color:inherit;transition:all 0.2s;" onmouseover="this.style.background='rgba(255,215,0,0.08)'" onmouseout="this.style.background='var(--bg-secondary)'">
                <i class="fas fa-folder" style="color:<?= View::e($folder['color']) ?>;"></i>
                <span style="flex:1;"><?= View::e($folder['name']) ?></span>
                <span style="color:var(--text-secondary);font-size:12px;"><?= $folder['note_count'] ?> notes</span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p style="color:var(--text-secondary);font-size:13px;">No folders yet.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Create -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-bolt" style="color:var(--accent2);"></i> Quick Note</div>
    </div>
    <a href="/projects/notex/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create New Note
    </a>
</div>

<?php View::end(); ?>
