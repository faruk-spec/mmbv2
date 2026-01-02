<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content">
    <div class="page-header">
        <h1><?= $title ?></h1>
        <button onclick="generateKey()" class="btn btn-primary">Generate New Key</button>
    </div>
    
    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>User</th>
                    <th>Permissions</th>
                    <th>Created</th>
                    <th>Last Used</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($keys as $key): ?>
                <tr>
                    <td><?= htmlspecialchars($key["name"]) ?></td>
                    <td><?= htmlspecialchars($key["user_name"] ?? "N/A") ?></td>
                    <td><code><?= htmlspecialchars(json_encode(json_decode($key["permissions"]))) ?></code></td>
                    <td><?= $key["created_at"] ?></td>
                    <td><?= $key["last_used_at"] ?? "Never" ?></td>
                    <td>
                        <?php if ($key["is_active"]): ?>
                            <span class="badge badge-success">Active</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Revoked</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button onclick="revokeKey(<?= $key["id"] ?>)" class="btn btn-sm btn-danger">Revoke</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php View::endSection(); ?>
