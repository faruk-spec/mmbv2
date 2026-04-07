<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>
<div class="admin-content">
    <div style="margin-bottom:30px;">
        <a href="/admin/pages" style="color:var(--text-secondary);"><i class="fas fa-arrow-left"></i> Back to Pages</a>
        <h1 style="margin-top:10px;">Create Page</h1>
    </div>

    <?php if (Helpers::hasFlash('error')): ?>
        <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="/admin/pages/create">
            <?= Security::csrfField() ?>

            <div class="form-group">
                <label class="form-label">Title <span style="color:var(--red)">*</span></label>
                <input type="text" name="title" class="form-input" required maxlength="255"
                       value="<?= View::old('title') ?>" oninput="autoSlug(this.value)">
                <?php if (View::hasError('title')): ?><div class="form-error"><?= View::error('title') ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label">Slug <span style="color:var(--red)">*</span></label>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="color:var(--text-secondary);">/pages/</span>
                    <input type="text" name="slug" id="slugInput" class="form-input" required maxlength="200"
                           value="<?= View::old('slug') ?>" pattern="[a-z0-9\-_\/]+" style="flex:1;">
                </div>
                <small class="form-help">Lowercase letters, numbers, hyphens, and slashes only.</small>
                <?php if (View::hasError('slug')): ?><div class="form-error"><?= View::error('slug') ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label">Content (HTML/CSS)</label>
                <textarea name="content" id="pageContent" class="form-input" rows="20"
                          style="font-family:monospace;font-size:13px;"><?= htmlspecialchars(View::old('content', '')) ?></textarea>
                <small class="form-help">You can use full HTML and inline CSS.</small>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div class="form-group">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" class="form-input" maxlength="255"
                           value="<?= View::old('meta_title') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-input" value="<?= View::old('sort_order', '0') ?>" min="0">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Meta Description</label>
                <textarea name="meta_description" class="form-input" rows="2" maxlength="500"><?= htmlspecialchars(View::old('meta_description', '')) ?></textarea>
            </div>

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:20px;">
                <label class="form-checkbox">
                    <input type="checkbox" name="show_navbar" value="1" <?= View::old('show_navbar', '1') ? 'checked' : '' ?>>
                    <span>Show Navbar</span>
                </label>
                <label class="form-checkbox">
                    <input type="checkbox" name="show_footer" value="1" <?= View::old('show_footer', '1') ? 'checked' : '' ?>>
                    <span>Show Footer</span>
                </label>
                <div class="form-group" style="margin:0;">
                    <label class="form-label" style="margin-bottom:5px;">Status</label>
                    <select name="status" class="form-input">
                        <option value="draft" <?= View::old('status', 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= View::old('status') === 'published' ? 'selected' : '' ?>>Published</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Page</button>
        </form>
    </div>
</div>
<script>
function autoSlug(title) {
    const slug = title.toLowerCase()
        .replace(/[^a-z0-9\s\-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-+|-+$/g, '');
    document.getElementById('slugInput').value = slug;
}
</script>
<?php View::endSection(); ?>
