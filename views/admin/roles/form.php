<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="margin-bottom: 30px;">
    <a href="/admin/roles" style="color: var(--text-secondary);">&larr; Back to Roles</a>
    <h1 style="margin-top: 10px;"><?= View::e($title) ?></h1>
</div>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div class="card" style="max-width: 600px;">
    <form method="POST" action="<?= View::e($action) ?>">
        <?= \Core\Security::csrfField() ?>

        <div class="form-group">
            <label class="form-label" for="name">Role Name <span style="color: var(--danger);">*</span></label>
            <input type="text" id="name" name="name" class="form-input"
                   value="<?= View::e($role['name'] ?? '') ?>" required maxlength="100">
        </div>

        <div class="form-group">
            <label class="form-label" for="slug">Slug <span style="color: var(--danger);">*</span></label>
            <input type="text" id="slug" name="slug" class="form-input"
                   value="<?= View::e($role['slug'] ?? '') ?>"
                   <?= (isset($role) && $role && $role['is_system']) ? 'readonly' : 'required' ?>
                   placeholder="e.g. moderator" maxlength="100">
            <?php if (isset($role) && $role && $role['is_system']): ?>
                <div style="margin-top: 6px; font-size: 13px; color: var(--text-secondary);">
                    Slugs for system roles cannot be changed.
                </div>
            <?php else: ?>
                <div style="margin-top: 6px; font-size: 13px; color: var(--text-secondary);">
                    Lowercase letters, numbers and underscores only. Used as the internal identifier.
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="description">Description</label>
            <textarea id="description" name="description" class="form-input" rows="3" maxlength="500"><?= View::e($role['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Color</label>
            <div style="display: flex; align-items: center; gap: 12px;">
                <input type="color" id="color" name="color"
                       style="width: 60px; height: 40px; padding: 2px 4px; cursor: pointer; border: 1px solid var(--border); border-radius: 6px; background: transparent;"
                       value="<?= View::e($role['color'] ?? '#9945ff') ?>">
                <input type="text" id="colorHex" class="form-input" style="max-width: 120px;"
                       value="<?= View::e($role['color'] ?? '#9945ff') ?>" maxlength="7"
                       placeholder="#rrggbb"
                       oninput="syncColorPicker(this.value)">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="status">Status</label>
            <select id="status" name="status" class="form-input">
                <option value="active"   <?= (($role['status'] ?? 'active') === 'active')   ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= (($role['status'] ?? '')        === 'inactive') ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="sort_order">Sort Order</label>
            <input type="number" id="sort_order" name="sort_order" class="form-input" style="max-width: 120px;"
                   value="<?= (int) ($role['sort_order'] ?? 0) ?>" min="0">
        </div>

        <div style="display: flex; gap: 15px; margin-top: 10px;">
            <button type="submit" class="btn btn-primary"><?= ($role ? 'Update Role' : 'Create Role') ?></button>
            <a href="/admin/roles" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
    function syncColorPicker(hex) {
        if (/^#[0-9a-fA-F]{6}$/.test(hex)) {
            document.getElementById('color').value = hex;
        }
    }

    document.getElementById('color').addEventListener('input', function () {
        document.getElementById('colorHex').value = this.value;
    });

    <?php if (!isset($role) || !$role || !$role['is_system']): ?>
    // Auto-generate slug from name for new / custom roles
    var slugEdited = <?= ($role && !empty($role['slug'])) ? 'true' : 'false' ?>;
    document.getElementById('slug').addEventListener('input', function () { slugEdited = true; });
    document.getElementById('name').addEventListener('input', function () {
        if (!slugEdited) {
            document.getElementById('slug').value = this.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '');
        }
    });
    <?php endif; ?>
</script>
<?php View::endSection(); ?>
