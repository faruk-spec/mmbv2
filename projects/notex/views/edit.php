<?php use Core\View; use Core\Security; ?>
<?php View::extend('notex:app'); ?>
<?php View::section('content'); ?>

<div style="max-width:900px;">
<div class="card mb-3">
    <div class="card-header" style="margin-bottom:16px;">
        <div class="card-title"><i class="fas fa-edit" style="color:var(--accent);"></i> <?= View::e($note['title']) ?></div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <form method="POST" action="/projects/notex/notes/<?= $note['id'] ?>/pin" style="display:inline;">
                <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
                <button type="submit" class="btn btn-secondary btn-sm" title="<?= $note['is_pinned'] ? 'Unpin' : 'Pin' ?>">
                    <i class="fas fa-thumbtack" style="color:<?= $note['is_pinned'] ? 'var(--accent)' : 'inherit' ?>;"></i>
                </button>
            </form>
            <form method="POST" action="/projects/notex/notes/<?= $note['id'] ?>/delete" onsubmit="return confirm('Move to trash?');" style="display:inline;">
                <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </div>

    <form method="POST" action="/projects/notex/notes/<?= $note['id'] ?>/update">
        <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">

        <div class="form-group">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-input" value="<?= View::e($note['title']) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Content</label>
            <textarea name="content" class="form-input" style="min-height:350px;"><?= View::e($note['content'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Folder</label>
                <select name="folder_id" class="form-input">
                    <option value="">No folder</option>
                    <?php foreach ($folders as $folder): ?>
                    <option value="<?= $folder['id'] ?>" <?= $note['folder_id'] == $folder['id'] ? 'selected' : '' ?>><?= View::e($folder['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Note Color</label>
                <input type="color" name="color" class="form-input" value="<?= View::e($note['color'] ?? '#ffd700') ?>" style="height:44px;padding:4px 8px;cursor:pointer;">
            </div>
        </div>

        <?php if (!empty($allTags)): ?>
        <div class="form-group">
            <label class="form-label">Tags</label>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                <?php foreach ($allTags as $tag): ?>
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:6px 12px;background:var(--bg-secondary);border-radius:20px;font-size:13px;">
                    <input type="checkbox" name="tag_ids[]" value="<?= $tag['id'] ?>"
                           <?= in_array($tag['id'], $noteTagIds ?? []) ? 'checked' : '' ?>
                           style="accent-color:var(--accent);">
                    <span style="color:<?= View::e($tag['color']) ?>;">● </span><?= View::e($tag['name']) ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                <input type="checkbox" name="is_pinned" value="1"
                       <?= $note['is_pinned'] ? 'checked' : '' ?>
                       style="width:16px;height:16px;accent-color:var(--accent);">
                <span><i class="fas fa-thumbtack" style="color:var(--accent);margin-right:4px;"></i> Pin this note</span>
            </label>
        </div>

        <div style="display:flex;gap:12px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
            <a href="/projects/notex/notes" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>

<!-- Share Note -->
<div class="card mb-3">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-share-alt" style="color:var(--cyan);"></i> Share Note</div>
    </div>
    <form method="POST" action="/projects/notex/notes/<?= $note['id'] ?>/share" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
        <div class="form-group" style="margin:0;min-width:120px;">
            <label class="form-label">Access</label>
            <select name="access" class="form-input">
                <option value="view">View only</option>
                <option value="edit">Can edit</option>
            </select>
        </div>
        <div class="form-group" style="margin:0;min-width:180px;">
            <label class="form-label">Expires</label>
            <input type="datetime-local" name="expires_at" class="form-input">
        </div>
        <button type="submit" class="btn btn-secondary" style="align-self:flex-end;margin-bottom:18px;"><i class="fas fa-link"></i> Generate Link</button>
    </form>
    <?php if ($note['share_token']): ?>
    <div style="margin-top:10px;padding:12px;background:var(--bg-secondary);border-radius:8px;font-size:13px;">
        Current share link: <a href="/projects/notex/shared/<?= View::e($note['share_token']) ?>" style="color:var(--cyan);">/shared/<?= View::e($note['share_token']) ?></a>
    </div>
    <?php endif; ?>
</div>

<!-- Version history -->
<?php if (!empty($versions)): ?>
<div class="card">
    <div class="card-title" style="margin-bottom:14px;"><i class="fas fa-history" style="color:var(--accent2);"></i> Version History</div>
    <div class="table-container">
        <table>
            <thead><tr><th>#</th><th>Saved At</th></tr></thead>
            <tbody>
            <?php foreach ($versions as $i => $ver): ?>
            <tr>
                <td><?= count($versions) - $i ?></td>
                <td><?= date('M d, Y H:i', strtotime($ver['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
</div>

<?php View::end(); ?>
