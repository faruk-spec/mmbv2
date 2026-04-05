<?php use Core\View; use Core\Security; ?>
<?php View::extend('notex:app'); ?>
<?php View::section('content'); ?>

<div class="card" style="max-width:800px;">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-plus-circle" style="color:var(--accent);"></i> New Note</div>
    </div>

    <form method="POST" action="/projects/notex/create">
        <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">

        <div class="form-group">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-input" placeholder="Note title…" value="Untitled Note">
        </div>

        <div class="form-group">
            <label class="form-label">Content</label>
            <textarea name="content" class="form-input" placeholder="Write your note here… Supports plain text and basic markdown."></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Folder</label>
                <select name="folder_id" class="form-input">
                    <option value="">No folder</option>
                    <?php foreach ($folders as $folder): ?>
                    <option value="<?= $folder['id'] ?>"><?= View::e($folder['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Note Color</label>
                <input type="color" name="color" class="form-input" value="#ffd700" style="height:44px;padding:4px 8px;cursor:pointer;">
            </div>
        </div>

        <?php if (!empty($tags)): ?>
        <div class="form-group">
            <label class="form-label">Tags</label>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                <?php foreach ($tags as $tag): ?>
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:6px 12px;background:var(--bg-secondary);border-radius:20px;font-size:13px;">
                    <input type="checkbox" name="tag_ids[]" value="<?= $tag['id'] ?>" style="accent-color:var(--accent);">
                    <span style="color:<?= View::e($tag['color']) ?>;">● </span><?= View::e($tag['name']) ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                <input type="checkbox" name="is_pinned" value="1" style="width:16px;height:16px;accent-color:var(--accent);">
                <span><i class="fas fa-thumbtack" style="color:var(--accent);margin-right:4px;"></i> Pin this note</span>
            </label>
        </div>

        <div style="display:flex;gap:12px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Note</button>
            <a href="/projects/notex/notes" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php View::end(); ?>
