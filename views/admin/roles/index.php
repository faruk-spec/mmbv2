<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1>User Roles</h1>
        <p style="color: var(--text-secondary);">Manage user roles and their metadata</p>
    </div>
    <div>
        <a href="/admin/roles/create" class="btn btn-primary">Add Role</a>
    </div>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div class="card">
    <?php if (empty($roles)): ?>
        <p style="color: var(--text-secondary); text-align: center; padding: 30px;">No roles found.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Users</th>
                    <th>Status</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $r): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 12px; height: 12px; border-radius: 50%; background: <?= View::e($r['color']) ?>; flex-shrink: 0;"></div>
                                <span style="font-weight: 500;"><?= View::e($r['name']) ?></span>
                            </div>
                        </td>
                        <td><code style="font-size: 13px;"><?= View::e($r['slug']) ?></code></td>
                        <td style="color: var(--text-secondary); font-size: 14px; max-width: 300px;"><?= View::e($r['description']) ?></td>
                        <td>
                            <?php if ($r['is_system']): ?>
                                <span class="badge badge-info"><?= (int) $r['user_count'] ?></span>
                            <?php else: ?>
                                <span style="color: var(--text-secondary);">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $r['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>">
                                <?= ucfirst($r['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($r['is_system']): ?>
                                <span class="badge badge-secondary">System</span>
                            <?php else: ?>
                                <span class="badge" style="background: rgba(153,69,255,0.15); color: var(--cyan);">Custom</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <a href="/admin/roles/<?= (int) $r['id'] ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                                <?php if (!$r['is_system']): ?>
                                    <form method="POST" action="/admin/roles/<?= (int) $r['id'] ?>/delete" style="display: inline;"
                                          onsubmit="return confirm('Delete role \'<?= View::e(addslashes($r['name'])) ?>\'? This cannot be undone.')">
                                        <?= \Core\Security::csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php View::endSection(); ?>
