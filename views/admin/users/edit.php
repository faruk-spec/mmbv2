<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div style="margin-bottom: 30px;">
    <a href="/admin/users" style="color: var(--text-secondary);">&larr; Back to Users</a>
</div>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>

<div class="grid grid-2" style="gap: 24px; align-items: start;">
    <!-- Main user form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-edit"></i> Edit User</h3>
        </div>
        <form method="POST" action="/admin/users/<?= $editUser['id'] ?>/edit">
            <?= \Core\Security::csrfField() ?>
            <!-- Role selector: changing the role takes immediate effect and overrides
                 per-feature permissions (admin / super_admin gain full access automatically). -->
            <div class="form-group">
                <label class="form-label" for="role">Role</label>
                <select id="role" name="role" class="form-input">
                    <option value="user"          <?= $editUser['role'] === 'user'          ? 'selected' : '' ?>>User</option>
                    <option value="project_admin" <?= $editUser['role'] === 'project_admin' ? 'selected' : '' ?>>Project Admin</option>
                    <option value="admin"         <?= $editUser['role'] === 'admin'         ? 'selected' : '' ?>>Admin</option>
                    <option value="super_admin"   <?= $editUser['role'] === 'super_admin'   ? 'selected' : '' ?>>Super Admin</option>
                    <?php if (!empty($customRoles)): ?>
                        <optgroup label="Custom Roles">
                        <?php foreach ($customRoles as $cr): ?>
                            <option value="<?= View::e($cr['slug']) ?>" <?= $editUser['role'] === $cr['slug'] ? 'selected' : '' ?>>
                                <?= View::e($cr['name']) ?>
                            </option>
                        <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
                <small style="color: var(--text-secondary); display: block; margin-top: 4px;">
                    <i class="fas fa-info-circle"></i>
                    Roles <strong>admin</strong> and <strong>super_admin</strong> grant full panel access and override any
                    per-feature permissions. Custom roles combine with
                    <a href="/admin/admin-access/<?= $editUser['id'] ?>/edit" style="color: var(--cyan);">Admin Access</a>
                    permissions (both apply simultaneously).
                </small>
            </div>

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
                <label class="form-label" for="status">Account Status</label>
                <select id="status" name="status" class="form-input">
                    <option value="active"   <?= $editUser['status'] === 'active'   ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $editUser['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="banned"   <?= $editUser['status'] === 'banned'   ? 'selected' : '' ?>>Banned</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="/admin/users" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <!-- Right panel: QR Plan, Feature Overrides, Admin Access, Danger Zone -->
    <div>
        <!-- QR Subscription Plan -->
        <?php if (!empty($qrPlans)): ?>
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-qrcode"></i> QR Subscription Plan</h3>
            </div>
            <?php if ($userQrPlan): ?>
                <p style="margin-bottom: 12px; font-size: 14px; color: var(--text-secondary);">
                    Current plan: <strong style="color: var(--cyan);"><?= View::e($userQrPlan['plan_name']) ?></strong>
                </p>
            <?php else: ?>
                <p style="margin-bottom: 12px; font-size: 14px; color: var(--text-secondary);">
                    No QR plan assigned.
                </p>
            <?php endif; ?>
            <form method="POST" action="/admin/qr/roles/assign-plan">
                <?= \Core\Security::csrfField() ?>
                <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
                <div style="display: flex; gap: 10px; align-items: flex-end;">
                    <div style="flex: 1;">
                        <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">Assign Plan</label>
                        <select name="plan_id" class="form-input">
                            <option value="">— Select plan —</option>
                            <?php foreach ($qrPlans as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= ($userQrPlan && $userQrPlan['plan_id'] == $p['id']) ? 'selected' : '' ?>>
                                    <?= View::e($p['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm" style="white-space:nowrap;">
                        <i class="fas fa-check"></i> Assign
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Admin Access Permissions shortcut -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-shield"></i> Admin Permissions</h3>
            </div>
            <p style="font-size: 14px; color: var(--text-secondary); margin-bottom: 16px;">
                Grant this user access to specific admin panel features and modules.
            </p>
            <a href="/admin/admin-access/<?= $editUser['id'] ?>/edit" class="btn btn-secondary btn-sm">
                <i class="fas fa-shield-alt"></i> Manage Admin Access
            </a>
        </div>

        <!-- Link to feature overrides -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-toggle-on"></i> Feature Overrides</h3>
            </div>
            <p style="font-size: 14px; color: var(--text-secondary); margin-bottom: 16px;">
                Set custom per-feature access for this user, overriding their role and plan defaults.
            </p>
            <a href="/admin/qr/roles" class="btn btn-secondary btn-sm">
                <i class="fas fa-users-cog"></i> Manage Feature Overrides
            </a>
        </div>

        <!-- Danger zone -->
        <div class="card mt-3" style="border-color: var(--red);">
            <div class="card-header">
                <h3 class="card-title" style="color: var(--red);"><i class="fas fa-exclamation-triangle"></i> Danger Zone</h3>
            </div>
            <form method="POST" action="/admin/users/<?= $editUser['id'] ?>/delete"
                  onsubmit="return confirm('Are you sure you want to delete this user? This cannot be undone.');">
                <?= \Core\Security::csrfField() ?>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Delete User
                </button>
            </form>
        </div>
    </div>
</div>

<?php View::endSection(); ?>



