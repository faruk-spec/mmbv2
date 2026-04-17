<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1>User Management</h1>
        <p style="color: var(--text-secondary);">Manage system users</p>
    </div>
    <div>
        <a href="/admin/users/create" class="btn btn-primary">Add User</a>
    </div>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div class="card mb-3">
    <form method="GET" action="/admin/users" style="display: flex; gap: 15px; flex-wrap: wrap;">
        <input type="text" name="search" class="form-input" style="max-width: 250px;" 
               placeholder="Search users..." value="<?= View::e($search) ?>">
        
        <select name="role" class="form-input" style="max-width: 150px;">
            <option value="">All Roles</option>
            <option value="super_admin" <?= $role === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
            <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="project_admin" <?= $role === 'project_admin' ? 'selected' : '' ?>>Project Admin</option>
            <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>User</option>
        </select>
        
        <select name="status" class="form-input" style="max-width: 150px;">
            <option value="">All Status</option>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
        
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="/admin/users" class="btn btn-secondary">Reset</a>
    </form>
</div>

<div class="card">
    <?php if (empty($users)): ?>
        <p style="color: var(--text-secondary); text-align: center; padding: 30px;">No users found</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Verified</th>
                    <th>Auth Mode</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Last Login</th>
                    <th>Last IP</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; background: var(--cyan); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                    <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div style="font-weight: 500;"><?= View::e($u['name']) ?></div>
                                    <div style="font-size: 12px; color: var(--text-secondary);">ID: <?= (int) $u['id'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?= View::e($u['email']) ?></td>
                        <td>
                            <?php if (!empty($u['email_verified_at'])): ?>
                                <span class="badge badge-success">Verified</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= !empty($u['oauth_only']) ? 'badge-info' : 'badge-secondary' ?>">
                                <?= !empty($u['oauth_only']) ? 'Google SSO' : 'Password' ?>
                            </span>
                        </td>
                        <td>
                            <?php foreach (array_filter(array_map('trim', explode(',', $u['role']))) as $r): ?>
                                <span class="badge badge-info" style="margin-right:2px;"><?= View::e($r) ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td>
                            <span class="badge <?= $u['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>">
                                <?= ucfirst($u['status']) ?>
                            </span>
                        </td>
                        <td><?= Helpers::formatDate($u['created_at']) ?></td>
                        <td><?= $u['last_login_at'] ? Helpers::timeAgo($u['last_login_at']) : 'Never' ?></td>
                        <td style="font-family:monospace;font-size:12px;"><?= View::e($u['last_login_ip'] ?? '-') ?></td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <a href="/admin/users/<?= $u['id'] ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                                <form method="POST" action="/admin/users/<?= $u['id'] ?>/toggle" style="display: inline;">
                                    <?= \Core\Security::csrfField() ?>
                                    <button type="submit" class="btn btn-sm <?= $u['status'] === 'active' ? 'btn-danger' : 'btn-secondary' ?>">
                                        <?= $u['status'] === 'active' ? 'Disable' : 'Enable' ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if ($pagination['total'] > 1): ?>
            <div style="margin-top: 20px; display: flex; justify-content: center; gap: 10px;">
                <?php if ($pagination['current'] > 1): ?>
                    <a href="?page=<?= $pagination['current'] - 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>&status=<?= urlencode($status) ?>" class="btn btn-sm btn-secondary">Previous</a>
                <?php endif; ?>
                
                <span style="padding: 8px 16px; color: var(--text-secondary);">
                    Page <?= $pagination['current'] ?> of <?= $pagination['total'] ?>
                </span>
                
                <?php if ($pagination['current'] < $pagination['total']): ?>
                    <a href="?page=<?= $pagination['current'] + 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>&status=<?= urlencode($status) ?>" class="btn btn-sm btn-secondary">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php View::endSection(); ?>
