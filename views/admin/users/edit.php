<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="margin-bottom: 30px;">
    <a href="/admin/users" style="color: var(--text-secondary);">&larr; Back to Users</a>
    <h1 style="margin-top: 10px;">Edit User</h1>
</div>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div class="card" style="max-width: 600px;">
    <form method="POST" action="/admin/users/<?= $editUser['id'] ?>/edit">
        <?= \Core\Security::csrfField() ?>
        
        <div class="form-group">
            <label class="form-label" for="name">Full Name</label>
            <input type="text" id="name" name="name" class="form-input" 
                   value="<?= View::e($editUser['name']) ?>" required>
            <?php if (View::hasError('name')): ?>
                <div class="form-error"><?= View::error('name') ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="form-input" 
                   value="<?= View::e($editUser['email']) ?>" required>
            <?php if (View::hasError('email')): ?>
                <div class="form-error"><?= View::error('email') ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="password">New Password</label>
            <input type="password" id="password" name="password" class="form-input" 
                   placeholder="Leave blank to keep current password">
            <small style="color: var(--text-secondary);">Leave blank to keep current password</small>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="role">Role</label>
            <select id="role" name="role" class="form-input" required>
                <option value="user" <?= $editUser['role'] === 'user' ? 'selected' : '' ?>>User</option>
                <option value="project_admin" <?= $editUser['role'] === 'project_admin' ? 'selected' : '' ?>>Project Admin</option>
                <option value="admin" <?= $editUser['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="super_admin" <?= $editUser['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="status">Status</label>
            <select id="status" name="status" class="form-input">
                <option value="active" <?= $editUser['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $editUser['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px;">
            <div style="display: flex; gap: 15px;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="/admin/users" class="btn btn-secondary">Cancel</a>
            </div>
            
            <form method="POST" action="/admin/users/<?= $editUser['id'] ?>/delete" 
                  onsubmit="return confirm('Are you sure you want to delete this user?');">
                <?= \Core\Security::csrfField() ?>
                <button type="submit" class="btn btn-danger">Delete User</button>
            </form>
        </div>
    </form>
</div>
<?php View::endSection(); ?>
