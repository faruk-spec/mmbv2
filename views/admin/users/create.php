<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="margin-bottom: 30px;">
    <a href="/admin/users" style="color: var(--text-secondary);">&larr; Back to Users</a>
    <h1 style="margin-top: 10px;">Create User</h1>
</div>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div class="card" style="max-width: 600px;">
    <form method="POST" action="/admin/users/create">
        <?= \Core\Security::csrfField() ?>
        
        <div class="form-group">
            <label class="form-label" for="name">Full Name</label>
            <input type="text" id="name" name="name" class="form-input" 
                   value="<?= View::old('name') ?>" required>
            <?php if (View::hasError('name')): ?>
                <div class="form-error"><?= View::error('name') ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="form-input" 
                   value="<?= View::old('email') ?>" required>
            <?php if (View::hasError('email')): ?>
                <div class="form-error"><?= View::error('email') ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" name="password" class="form-input" 
                   required minlength="8">
            <?php if (View::hasError('password')): ?>
                <div class="form-error"><?= View::error('password') ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="role">Role</label>
            <select id="role" name="role" class="form-input" required>
                <option value="user">User</option>
                <option value="project_admin">Project Admin</option>
                <option value="admin">Admin</option>
                <option value="super_admin">Super Admin</option>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="status">Status</label>
            <select id="status" name="status" class="form-input">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 15px;">
            <button type="submit" class="btn btn-primary">Create User</button>
            <a href="/admin/users" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php View::endSection(); ?>
