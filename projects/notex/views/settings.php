<?php use Core\View; use Core\Security; ?>
<?php View::extend('notex:app'); ?>
<?php View::section('content'); ?>

<div style="max-width:700px;">
<div class="card mb-4">
    <div class="card-title" style="margin-bottom:20px;"><i class="fas fa-cog" style="color:var(--accent);"></i> Preferences</div>
    <form method="POST" action="/projects/notex/settings">
        <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Default Note Color</label>
                <input type="color" name="default_color" class="form-input"
                       value="<?= View::e($settings['default_color'] ?? '#ffd700') ?>"
                       style="height:44px;padding:4px 8px;cursor:pointer;">
            </div>
            <div class="form-group">
                <label class="form-label">Theme</label>
                <select name="theme" class="form-input">
                    <option value="dark" <?= ($settings['theme'] ?? 'dark') === 'dark' ? 'selected' : '' ?>>Dark</option>
                    <option value="light" <?= ($settings['theme'] ?? 'dark') === 'light' ? 'selected' : '' ?>>Light</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                <input type="checkbox" name="auto_save" value="1"
                       <?= ($settings['auto_save'] ?? 1) ? 'checked' : '' ?>
                       style="width:16px;height:16px;accent-color:var(--accent);">
                <span>Enable auto-save</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Preferences</button>
    </form>
</div>

<!-- Tags Management -->
<div class="card">
    <div class="card-title" style="margin-bottom:18px;"><i class="fas fa-tags" style="color:var(--cyan);"></i> Tags</div>

    <?php if (!empty($tags)): ?>
    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px;">
        <?php foreach ($tags as $tag): ?>
        <form method="POST" action="/projects/notex/settings" style="display:inline;">
            <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
            <input type="hidden" name="delete_tag" value="<?= $tag['id'] ?>">
            <button type="submit" onclick="return confirm('Delete tag?')"
                    style="display:flex;align-items:center;gap:6px;padding:6px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:20px;cursor:pointer;font-family:inherit;font-size:13px;color:var(--text-primary);"
                    title="Click to delete">
                <span style="color:<?= View::e($tag['color']) ?>;">●</span>
                <?= View::e($tag['name']) ?>
                <i class="fas fa-times" style="font-size:10px;color:var(--red);"></i>
            </button>
        </form>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p style="color:var(--text-secondary);margin-bottom:16px;font-size:13px;">No tags yet.</p>
    <?php endif; ?>

    <form method="POST" action="/projects/notex/settings" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
            <label class="form-label">New Tag</label>
            <input type="text" name="new_tag" class="form-input" placeholder="e.g. Work">
        </div>
        <div class="form-group" style="margin:0;">
            <label class="form-label">Color</label>
            <input type="color" name="new_tag_color" class="form-input" value="#00d4ff" style="height:44px;width:60px;padding:4px;cursor:pointer;">
        </div>
        <button type="submit" class="btn btn-primary" style="align-self:flex-end;margin-bottom:18px;"><i class="fas fa-plus"></i> Add Tag</button>
    </form>
</div>
</div>

<?php View::end(); ?>
