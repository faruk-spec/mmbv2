<?php use Core\View; use Core\Helpers; use Core\Auth; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
.role-picker { display: flex; flex-direction: column; gap: 6px; }
.role-chip {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 12px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    cursor: pointer;
    transition: var(--transition);
    background: var(--bg-secondary);
    user-select: none;
}
.role-chip:hover { border-color: var(--cyan); background: rgba(59,130,246,.04); }
.role-chip.checked { border-color: var(--cyan); background: rgba(59,130,246,.07); }
.role-chip input[type=checkbox] { display: none; }
.role-chip-dot {
    width: 16px; height: 16px; border-radius: 4px;
    border: 2px solid var(--border-color);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; transition: var(--transition);
}
.role-chip.checked .role-chip-dot {
    background: var(--cyan); border-color: var(--cyan);
}
.role-chip.checked .role-chip-dot::after {
    content: '';
    width: 5px; height: 9px;
    border: 2px solid #000;
    border-top: none; border-left: none;
    transform: rotate(45deg) translate(-1px,-1px);
    display: block;
}
.role-chip-badge {
    font-size: 10px; padding: 2px 6px; border-radius: 10px;
    background: rgba(153,69,255,.15); color: var(--purple, #8b5cf6);
    margin-left: auto; white-space: nowrap;
}
.role-chip-badge.system { background: rgba(59,130,246,.12); color: var(--cyan); }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>

<?php
// Parse the current roles from the comma-separated field
$currentRoles = array_filter(array_map('trim', explode(',', $editUser['role'] ?? 'user')));
?>

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
        <form method="POST" action="/admin/users/<?= $editUser['id'] ?>/edit" id="editUserForm">
            <?= \Core\Security::csrfField() ?>

            <!-- Multi-role picker -->
            <div class="form-group">
                <label class="form-label">Roles <span style="color:var(--text-secondary);font-weight:400;font-size:12px;">(select one or more)</span></label>
                <div class="role-picker" id="rolePicker">
                    <?php
                    $systemRoleOptions = [
                        'user'          => ['label' => 'User',          'desc' => 'Standard platform user'],
                        'project_admin' => ['label' => 'Project Admin', 'desc' => 'Manages projects & content'],
                        'admin'         => ['label' => 'Admin',         'desc' => 'Full panel access'],
                        'super_admin'   => ['label' => 'Super Admin',   'desc' => 'Unrestricted access'],
                    ];
                    foreach ($systemRoleOptions as $slug => $meta):
                        $checked = in_array($slug, $currentRoles, true);
                    ?>
                    <label class="role-chip <?= $checked ? 'checked' : '' ?>" data-slug="<?= $slug ?>">
                        <input type="checkbox" name="roles[]" value="<?= $slug ?>" <?= $checked ? 'checked' : '' ?>>
                        <span class="role-chip-dot"></span>
                        <span style="flex:1;">
                            <span style="font-weight:600;font-size:14px;"><?= $meta['label'] ?></span>
                            <span style="display:block;font-size:11px;color:var(--text-secondary);margin-top:1px;"><?= $meta['desc'] ?></span>
                        </span>
                        <span class="role-chip-badge system">system</span>
                    </label>
                    <?php endforeach; ?>

                    <?php if (!empty($customRoles)): ?>
                    <div style="height:1px;background:var(--border-color);margin:4px 0;"></div>
                    <?php foreach ($customRoles as $cr):
                        $checked = in_array($cr['slug'], $currentRoles, true);
                    ?>
                    <label class="role-chip <?= $checked ? 'checked' : '' ?>" data-slug="<?= View::e($cr['slug']) ?>">
                        <input type="checkbox" name="roles[]" value="<?= View::e($cr['slug']) ?>" <?= $checked ? 'checked' : '' ?>>
                        <span class="role-chip-dot"></span>
                        <span style="flex:1;">
                            <span style="font-weight:600;font-size:14px;"><?= View::e($cr['name']) ?></span>
                            <span style="display:block;font-size:11px;color:var(--text-secondary);margin-top:1px;">Custom role</span>
                        </span>
                        <span class="role-chip-badge">custom</span>
                    </label>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <small style="color:var(--text-secondary);display:block;margin-top:6px;">
                    <i class="fas fa-info-circle"></i>
                    <strong>admin</strong> / <strong>super_admin</strong> grant full panel access. Custom roles are additive — they grant the permissions defined on the role; individual
                    <a href="/admin/admin-access/<?= $editUser['id'] ?>/edit" style="color:var(--cyan);">Admin Access</a>
                    overrides take precedence over role defaults.
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

<?php View::section('scripts'); ?>
<script>
(function () {
    // Toggle checked state on role chips
    document.querySelectorAll('#rolePicker .role-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            var cb = chip.querySelector('input[type=checkbox]');
            cb.checked = !cb.checked;
            chip.classList.toggle('checked', cb.checked);
        });
    });

    // Ensure at least one role is always selected before submit
    var form = document.getElementById('editUserForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            var checked = form.querySelectorAll('input[name="roles[]"]:checked');
            if (checked.length === 0) {
                e.preventDefault();
                alert('Please select at least one role.');
            }
        });
    }
}());
</script>
<?php View::endSection(); ?>



