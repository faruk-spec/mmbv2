<?php use Core\View; use Core\Security; ?>
<?php View::extend('notex:app'); ?>
<?php View::section('content'); ?>

<div style="max-width:40rem;">
<div class="card" style="margin-bottom:1rem;">
    <div style="font-size:var(--font-md);font-weight:600;margin-bottom:1.25rem;display:flex;align-items:center;gap:0.5rem;">
        <i class="fas fa-cog" style="color:var(--nx-accent);"></i> Preferences
    </div>
    <form method="POST" action="/projects/notex/settings">
        <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">

        <div class="form-group">
            <label class="form-label">Default Note Color</label>
            <input type="color" name="default_color" class="form-input"
                   value="<?= View::e($settings['default_color'] ?? '#f59e0b') ?>"
                   style="height:2.75rem;padding:0.25rem 0.5rem;cursor:pointer;">
        </div>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:0.625rem;cursor:pointer;">
                <input type="checkbox" name="auto_save" value="1"
                       <?= ($settings['auto_save'] ?? 1) ? 'checked' : '' ?>
                       style="width:1rem;height:1rem;accent-color:var(--nx-accent);">
                <span style="font-size:var(--font-sm);">Enable auto-save</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Preferences</button>
    </form>
</div>

<!-- Tags Management -->
<div class="card">
    <div style="font-size:var(--font-md);font-weight:600;margin-bottom:1.125rem;display:flex;align-items:center;gap:0.5rem;">
        <i class="fas fa-tags" style="color:var(--cyan);"></i> Tags
    </div>

    <?php if (!empty($tags)): ?>
    <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:1.25rem;">
        <?php foreach ($tags as $tag): ?>
        <form method="POST" action="/projects/notex/settings" style="display:inline;">
            <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
            <input type="hidden" name="delete_tag" value="<?= $tag['id'] ?>">
            <button type="submit" onclick="return confirm('Delete tag?')"
                    style="display:flex;align-items:center;gap:0.375rem;padding:0.3125rem 0.75rem;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:1.25rem;cursor:pointer;font-family:inherit;font-size:var(--font-xs);color:var(--text-primary);transition:border-color 0.2s;"
                    title="Click to delete">
                <span style="color:<?= View::e($tag['color']) ?>;font-size:0.625rem;">●</span>
                <?= View::e($tag['name']) ?>
                <i class="fas fa-times" style="font-size:0.625rem;color:var(--red);"></i>
            </button>
        </form>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p style="color:var(--text-secondary);margin-bottom:1rem;font-size:var(--font-sm);">No tags yet.</p>
    <?php endif; ?>

    <form method="POST" action="/projects/notex/settings"
          style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end;">
        <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
        <div class="form-group" style="margin:0;flex:1;min-width:8.75rem;">
            <label class="form-label">New Tag</label>
            <input type="text" name="new_tag" class="form-input" placeholder="e.g. Work">
        </div>
        <div class="form-group" style="margin:0;">
            <label class="form-label">Color</label>
            <input type="color" name="new_tag_color" class="form-input" value="#00d4ff"
                   style="height:2.75rem;width:3.75rem;padding:0.25rem;cursor:pointer;">
        </div>
        <div style="padding-bottom:1.125rem;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Tag</button>
        </div>
    </form>
</div>
</div>

<?php View::end(); ?>
