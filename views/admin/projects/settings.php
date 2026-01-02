<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="margin-bottom: 30px;">
    <a href="/admin/projects/<?= $project['key'] ?>" style="color: var(--text-secondary);">&larr; Back to <?= View::e($project['name']) ?></a>
    <h1 style="margin-top: 10px;"><?= View::e($project['name']) ?> Settings</h1>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div class="card" style="max-width: 600px;">
    <form method="POST" action="/admin/projects/<?= $project['key'] ?>/settings">
        <?= \Core\Security::csrfField() ?>
        
        <div class="form-group">
            <label class="form-label">Project Name</label>
            <input type="text" name="name" class="form-input" value="<?= View::e($project['name']) ?>" disabled>
            <small style="color: var(--text-secondary);">Project name cannot be changed</small>
        </div>
        
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-input" rows="3"><?= View::e($project['description']) ?></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">Theme Color</label>
            <input type="color" name="color" class="form-input" value="<?= View::e($project['color']) ?>" style="height: 50px; padding: 5px;">
        </div>
        
        <div class="form-group">
            <label class="form-label">Database Name</label>
            <input type="text" name="database" class="form-input" value="<?= View::e($project['database']) ?>" disabled>
            <small style="color: var(--text-secondary);">Database cannot be changed after creation</small>
        </div>
        
        <div class="form-group">
            <label class="form-checkbox">
                <input type="checkbox" name="enabled" value="1" <?= $project['enabled'] ? 'checked' : '' ?>>
                <span>Project Enabled</span>
            </label>
        </div>
        
        <div style="display: flex; gap: 15px;">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="/admin/projects/<?= $project['key'] ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php View::endSection(); ?>
