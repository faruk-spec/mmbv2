<?php use Core\View; use Core\Security; ?>
<?php View::extend('notex:app'); ?>
<?php View::section('content'); ?>

<style>
    .nx-editor-layout {
        display: grid;
        grid-template-columns: 1fr 18rem;
        gap: 1rem;
        align-items: start;
    }
    .nx-editor-main { display: flex; flex-direction: column; gap: 0.75rem; }
    .nx-editor-sidebar { display: flex; flex-direction: column; gap: 0.75rem; }
    .nx-field-title {
        width: 100%;
        padding: 0.75rem 1rem;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        color: var(--text-primary);
        font-family: inherit;
        font-size: 1.1rem;
        font-weight: 600;
        transition: border-color 0.2s;
    }
    .nx-field-title:focus { outline: none; border-color: var(--nx-accent); box-shadow: 0 0 0 3px rgba(245,158,11,0.1); }
    .nx-field-content {
        width: 100%;
        min-height: 28rem;
        padding: 1rem;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        color: var(--text-primary);
        font-family: inherit;
        font-size: var(--font-sm);
        line-height: 1.8;
        resize: vertical;
        transition: border-color 0.2s;
    }
    .nx-field-content:focus { outline: none; border-color: var(--nx-accent); box-shadow: 0 0 0 3px rgba(245,158,11,0.1); }
    .nx-sidebar-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 0.625rem;
        padding: 1rem;
    }
    .nx-sidebar-card-title {
        font-size: var(--font-xs);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
        margin-bottom: 0.75rem;
    }
    .nx-pin-toggle {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        cursor: pointer;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        background: var(--bg-secondary);
        transition: background 0.2s;
    }
    .nx-pin-toggle:hover { background: rgba(245,158,11,0.1); }
    .nx-tag-chip {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.3125rem 0.625rem;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 1.25rem;
        font-size: var(--font-xs);
        cursor: pointer;
        transition: border-color 0.2s;
        user-select: none;
    }
    .nx-tag-chip input[type=checkbox] { display: none; }
    .nx-tag-chip.checked { border-color: var(--nx-accent); background: rgba(245,158,11,0.1); }
    @media (max-width: 56rem) {
        .nx-editor-layout { grid-template-columns: 1fr; }
        .nx-editor-sidebar { order: -1; }
        .nx-field-content { min-height: 14rem; }
    }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
    <h2 style="font-size:var(--font-xl);font-weight:700;display:flex;align-items:center;gap:0.5rem;">
        <i class="fas fa-plus-circle" style="color:var(--nx-accent);"></i> New Note
    </h2>
    <a href="/projects/notex/notes" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<form method="POST" action="/projects/notex/create">
    <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
    <div class="nx-editor-layout">
        <!-- Main Column -->
        <div class="nx-editor-main">
            <input type="text" name="title" class="nx-field-title"
                   placeholder="Note title…" value="Untitled Note">
            <textarea name="content" class="nx-field-content"
                      placeholder="Write your note here… Supports plain text and basic markdown."></textarea>
            <div style="display:flex;gap:0.625rem;flex-wrap:wrap;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Note
                </button>
                <a href="/projects/notex/notes" class="btn btn-secondary">Cancel</a>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="nx-editor-sidebar">
            <!-- Folder -->
            <div class="nx-sidebar-card">
                <div class="nx-sidebar-card-title"><i class="fas fa-folder" style="margin-right:0.3rem;"></i> Folder</div>
                <select name="folder_id" class="form-input form-select" style="font-size:var(--font-sm);">
                    <option value="">No folder</option>
                    <?php foreach ($folders as $folder): ?>
                    <option value="<?= $folder['id'] ?>"
                        <?= (isset($preselectedFolder) && $preselectedFolder == $folder['id']) ? 'selected' : '' ?>>
                        <?= View::e($folder['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Note Color -->
            <div class="nx-sidebar-card">
                <div class="nx-sidebar-card-title"><i class="fas fa-palette" style="margin-right:0.3rem;"></i> Note Color</div>
                <input type="color" name="color" class="form-input"
                       value="#f59e0b" style="height:2.75rem;padding:0.25rem 0.5rem;cursor:pointer;width:100%;">
            </div>

            <!-- Pin -->
            <div class="nx-sidebar-card">
                <div class="nx-sidebar-card-title"><i class="fas fa-thumbtack" style="margin-right:0.3rem;"></i> Options</div>
                <label class="nx-pin-toggle">
                    <input type="checkbox" name="is_pinned" value="1" style="width:1rem;height:1rem;accent-color:var(--nx-accent);">
                    <i class="fas fa-thumbtack" style="color:var(--nx-accent);font-size:0.8125rem;"></i>
                    <span style="font-size:var(--font-sm);">Pin this note</span>
                </label>
            </div>

            <?php if (!empty($tags)): ?>
            <!-- Tags -->
            <div class="nx-sidebar-card">
                <div class="nx-sidebar-card-title"><i class="fas fa-tags" style="margin-right:0.3rem;"></i> Tags</div>
                <div style="display:flex;flex-wrap:wrap;gap:0.375rem;">
                    <?php foreach ($tags as $tag): ?>
                    <label class="nx-tag-chip">
                        <input type="checkbox" name="tag_ids[]" value="<?= $tag['id'] ?>"
                               onchange="this.closest('label').classList.toggle('checked', this.checked)">
                        <span style="color:<?= View::e($tag['color']) ?>;font-size:0.625rem;">●</span>
                        <?= View::e($tag['name']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<?php View::end(); ?>
