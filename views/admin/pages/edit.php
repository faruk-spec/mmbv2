<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>
<div class="admin-content">
    <div style="margin-bottom:30px;">
        <a href="/admin/pages" style="color:var(--text-secondary);"><i class="fas fa-arrow-left"></i> Back to Pages</a>
        <h1 style="margin-top:10px;">Edit Page: <?= View::e($page['title']) ?></h1>
    </div>

    <?php if (Helpers::hasFlash('error')): ?>
        <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
    <?php endif; ?>
    <?php if (Helpers::hasFlash('success')): ?>
        <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="/admin/pages/<?= (int)$page['id'] ?>/update">
            <?= Security::csrfField() ?>

            <div class="form-group">
                <label class="form-label">Title <span style="color:var(--red)">*</span></label>
                <input type="text" name="title" class="form-input" required maxlength="255"
                       value="<?= View::e($page['title']) ?>">
                <?php if (View::hasError('title')): ?><div class="form-error"><?= View::error('title') ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label">Slug <span style="color:var(--red)">*</span></label>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="color:var(--text-secondary);">/pages/</span>
                    <input type="text" name="slug" class="form-input" required maxlength="200"
                           value="<?= View::e($page['slug']) ?>" pattern="[a-z0-9\-_\/]+" style="flex:1;">
                </div>
                <small class="form-help">Public URL: <a href="/pages/<?= View::e($page['slug']) ?>" target="_blank">/pages/<?= View::e($page['slug']) ?></a></small>
                <?php if (View::hasError('slug')): ?><div class="form-error"><?= View::error('slug') ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label">Content (HTML/CSS)</label>
                <textarea name="content" class="form-input" rows="20"
                          style="font-family:monospace;font-size:13px;"><?= htmlspecialchars($page['content'] ?? '') ?></textarea>
                <small class="form-help">You can use full HTML and inline CSS.</small>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div class="form-group">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" class="form-input" maxlength="255"
                           value="<?= View::e($page['meta_title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-input" min="0"
                           value="<?= (int)$page['sort_order'] ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Meta Description</label>
                <textarea name="meta_description" class="form-input" rows="2" maxlength="500"><?= htmlspecialchars($page['meta_description'] ?? '') ?></textarea>
            </div>

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:20px;">
                <label class="form-checkbox">
                    <input type="checkbox" name="show_navbar" value="1" <?= $page['show_navbar'] ? 'checked' : '' ?>>
                    <span>Show Navbar</span>
                </label>
                <label class="form-checkbox">
                    <input type="checkbox" name="show_footer" value="1" <?= $page['show_footer'] ? 'checked' : '' ?>>
                    <span>Show Footer</span>
                </label>
                <div class="form-group" style="margin:0;">
                    <label class="form-label" style="margin-bottom:5px;">Status</label>
                    <select name="status" class="form-input">
                        <option value="draft" <?= $page['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $page['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                    </select>
                </div>
            </div>

            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                <a href="/pages/<?= View::e($page['slug']) ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-eye"></i> Preview</a>
            </div>
        </form>
    </div>
</div>
<?php View::endSection(); ?>
