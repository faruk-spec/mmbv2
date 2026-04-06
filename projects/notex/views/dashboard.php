<?php use Core\View; ?>
<?php View::extend('notex:app'); ?>
<?php View::section('content'); ?>

<style>
    /* ── Page Header ── */
    .nx-page-header {
        margin-bottom: 1.5rem;
    }
    .nx-page-header h1 {
        font-size: var(--font-2xl);
        font-weight: 700;
        background: linear-gradient(135deg, var(--nx-accent), var(--cyan));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1.2;
        margin-bottom: 0.25rem;
    }
    .nx-page-header p { font-size: var(--font-sm); color: var(--text-secondary); }

    /* ── Stats ── */
    .nx-dash-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.875rem;
        margin-bottom: 1.25rem;
    }
    .nx-dash-stat {
        background: var(--bg-card); border: 1px solid var(--border-color);
        border-radius: 0.625rem; padding: 1.125rem;
        display: flex; align-items: center; gap: 0.875rem;
        transition: all 0.2s;
    }
    .nx-dash-stat:hover { border-color: rgba(245,158,11,0.3); transform: translateY(-0.0625rem); }
    .nx-dash-stat-icon {
        width: 2.75rem; height: 2.75rem; border-radius: 0.5rem; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 1.125rem;
    }
    .nx-dash-stat-value { font-size: 1.625rem; font-weight: 700; line-height: 1; }
    .nx-dash-stat-label { font-size: var(--font-xs); color: var(--text-secondary); margin-top: 0.1875rem; }

    /* ── Quick Actions ── */
    .nx-quick-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.875rem;
        margin-bottom: 1.25rem;
    }
    .nx-quick-card {
        display: flex; flex-direction: column; align-items: flex-start; gap: 0.5rem;
        padding: 1.25rem 1.125rem; border-radius: 0.75rem;
        text-decoration: none; color: white;
        transition: all 0.2s ease;
    }
    .nx-quick-card:hover { transform: translateY(-0.1875rem); filter: brightness(1.08); box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.25); }
    .nx-quick-icon { font-size: 1.75rem; line-height: 1; }
    .nx-quick-label { font-weight: 700; font-size: var(--font-sm); }
    .nx-quick-desc { font-size: var(--font-xs); opacity: 0.8; }

    /* ── Pinned ── */
    .nx-section-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 0.875rem;
    }
    .nx-section-title {
        font-size: var(--font-md); font-weight: 600;
        display: flex; align-items: center; gap: 0.5rem;
    }

    /* ── Content grid ── */
    .nx-dash-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

    @media (max-width: 64rem) {
        .nx-dash-stats { grid-template-columns: repeat(2, 1fr); }
        .nx-quick-row { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 48rem) {
        .nx-dash-stats { grid-template-columns: repeat(2, 1fr); }
        .nx-quick-row { grid-template-columns: repeat(2, 1fr); }
        .nx-dash-grid { grid-template-columns: 1fr; }
        .nx-dash-stat-value { font-size: 1.25rem; }
        .nx-dash-stat-icon { width: 2.25rem; height: 2.25rem; font-size: 0.875rem; }
    }
</style>

<!-- Page Header -->
<div class="nx-page-header">
    <h1>NoteX</h1>
    <p>Private notes with folders, tags, and cloud sync</p>
</div>

<!-- Stats -->
<div class="nx-dash-stats">
    <div class="nx-dash-stat">
        <div class="nx-dash-stat-icon" style="background:rgba(245,158,11,0.15);">
            <i class="fas fa-sticky-note" style="color:var(--nx-accent);"></i>
        </div>
        <div>
            <div class="nx-dash-stat-value" style="color:var(--nx-accent);"><?= $stats['total_notes'] ?></div>
            <div class="nx-dash-stat-label">Total Notes</div>
        </div>
    </div>
    <div class="nx-dash-stat">
        <div class="nx-dash-stat-icon" style="background:rgba(0,240,255,0.12);">
            <i class="fas fa-thumbtack" style="color:var(--cyan);"></i>
        </div>
        <div>
            <div class="nx-dash-stat-value" style="color:var(--cyan);"><?= $stats['pinned_notes'] ?></div>
            <div class="nx-dash-stat-label">Pinned</div>
        </div>
    </div>
    <div class="nx-dash-stat">
        <div class="nx-dash-stat-icon" style="background:rgba(0,255,136,0.12);">
            <i class="fas fa-folder" style="color:var(--green);"></i>
        </div>
        <div>
            <div class="nx-dash-stat-value" style="color:var(--green);"><?= $stats['total_folders'] ?></div>
            <div class="nx-dash-stat-label">Folders</div>
        </div>
    </div>
    <div class="nx-dash-stat">
        <div class="nx-dash-stat-icon" style="background:rgba(255,46,196,0.12);">
            <i class="fas fa-tags" style="color:var(--accent2);"></i>
        </div>
        <div>
            <div class="nx-dash-stat-value" style="color:var(--accent2);"><?= $stats['total_tags'] ?></div>
            <div class="nx-dash-stat-label">Tags</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="nx-quick-row">
    <a href="/projects/notex/create" class="nx-quick-card"
       style="background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 4px 16px rgba(245,158,11,0.3);">
        <div class="nx-quick-icon"><i class="fas fa-plus-circle"></i></div>
        <div class="nx-quick-label">New Note</div>
        <div class="nx-quick-desc">Write something new</div>
    </a>
    <a href="/projects/notex/notes" class="nx-quick-card"
       style="background:linear-gradient(135deg,#0891b2,#06b6d4);box-shadow:0 4px 16px rgba(8,145,178,0.3);">
        <div class="nx-quick-icon"><i class="fas fa-list-ul"></i></div>
        <div class="nx-quick-label">All Notes</div>
        <div class="nx-quick-desc">Browse & search</div>
    </a>
    <a href="/projects/notex/folders" class="nx-quick-card"
       style="background:linear-gradient(135deg,#7c3aed,#a855f7);box-shadow:0 4px 16px rgba(124,58,237,0.3);">
        <div class="nx-quick-icon"><i class="fas fa-folder-open"></i></div>
        <div class="nx-quick-label">Folders</div>
        <div class="nx-quick-desc">Organise your notes</div>
    </a>
    <a href="/projects/notex/settings" class="nx-quick-card"
       style="background:linear-gradient(135deg,#059669,#10b981);box-shadow:0 4px 16px rgba(5,150,105,0.3);">
        <div class="nx-quick-icon"><i class="fas fa-cog"></i></div>
        <div class="nx-quick-label">Settings</div>
        <div class="nx-quick-desc">Tags & preferences</div>
    </a>
</div>

<!-- Pinned Notes (if any) -->
<?php if (!empty($pinnedNotes)): ?>
<div style="margin-bottom:1.25rem;">
    <div class="nx-section-header">
        <div class="nx-section-title">
            <i class="fas fa-thumbtack" style="color:var(--nx-accent);"></i> Pinned Notes
        </div>
        <a href="/projects/notex/notes" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <div class="notes-grid">
        <?php foreach ($pinnedNotes as $note): ?>
        <a href="/projects/notex/notes/<?= $note['id'] ?>/edit" style="text-decoration:none;color:inherit;">
            <div class="note-card">
                <div class="note-card-accent" style="background:<?= View::e($note['color'] ?? '#f59e0b') ?>;"></div>
                <div style="padding-left:0.5rem;">
                    <div class="note-card-title">
                        <i class="fas fa-thumbtack" style="color:var(--nx-accent);font-size:0.75rem;margin-right:0.25rem;"></i>
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
<div class="nx-dash-grid">
    <!-- Recent Notes -->
    <div class="card">
        <div class="nx-section-header">
            <div class="nx-section-title">
                <i class="fas fa-clock" style="color:var(--cyan);"></i> Recent Notes
            </div>
            <a href="/projects/notex/notes" class="btn btn-secondary btn-sm">View All</a>
        </div>
        <?php if (!empty($recentNotes)): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>Title</th><th>Folder</th><th>Updated</th></tr>
                </thead>
                <tbody>
                <?php foreach ($recentNotes as $note): ?>
                <tr onclick="location.href='/projects/notex/notes/<?= $note['id'] ?>/edit'" style="cursor:pointer;"
                    tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' ')location.href='/projects/notex/notes/<?= $note['id'] ?>/edit'">
                    <td>
                        <?php if ($note['is_pinned']): ?>
                            <i class="fas fa-thumbtack" style="color:var(--nx-accent);font-size:0.6875rem;margin-right:0.25rem;"></i>
                        <?php endif; ?>
                        <?= View::e($note['title']) ?>
                    </td>
                    <td style="color:var(--text-secondary);font-size:var(--font-xs);"><?= View::e($note['folder_name'] ?? '–') ?></td>
                    <td style="color:var(--text-secondary);font-size:var(--font-xs);white-space:nowrap;"><?= date('M d', strtotime($note['updated_at'] ?? $note['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:2rem 0;color:var(--text-secondary);">
            <i class="fas fa-sticky-note" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:0.75rem;"></i>
            No notes yet. <a href="/projects/notex/create" style="color:var(--nx-accent);">Create your first note</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Folders -->
    <div class="card">
        <div class="nx-section-header">
            <div class="nx-section-title">
                <i class="fas fa-folder" style="color:var(--nx-accent);"></i> Folders
            </div>
            <a href="/projects/notex/folders" class="btn btn-secondary btn-sm">Manage</a>
        </div>
        <?php if (!empty($folders)): ?>
        <div style="display:flex;flex-direction:column;gap:0.5rem;">
            <?php foreach ($folders as $folder): ?>
            <a href="/projects/notex/notes?folder=<?= $folder['id'] ?>"
               style="display:flex;align-items:center;gap:0.75rem;padding:0.625rem 0.75rem;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:0.5rem;text-decoration:none;color:inherit;transition:all 0.15s;">
                <i class="fas fa-folder" style="color:<?= View::e($folder['color']) ?>;font-size:1.125rem;"></i>
                <span style="flex:1;font-size:var(--font-sm);"><?= View::e($folder['name']) ?></span>
                <span style="color:var(--text-secondary);font-size:var(--font-xs);"><?= $folder['note_count'] ?> notes</span>
                <i class="fas fa-chevron-right" style="color:var(--text-secondary);font-size:0.625rem;"></i>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p style="color:var(--text-secondary);font-size:var(--font-sm);text-align:center;padding:1.5rem 0;">
            No folders yet. <a href="/projects/notex/folders" style="color:var(--nx-accent);">Create one</a>
        </p>
        <?php endif; ?>
    </div>
</div>

<?php View::end(); ?>
