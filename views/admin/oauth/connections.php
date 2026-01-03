<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('admin/layout'); ?>

<?php View::section('content'); ?>
<div class="admin-content">
    <div class="page-header">
        <h1>OAuth Connections</h1>
        <p>View and manage user OAuth provider connections</p>
    </div>
    
    <?php if (Helpers::hasFlash('success')): ?>
        <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
    <?php endif; ?>
    
    <?php if (Helpers::hasFlash('error')): ?>
        <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h3>User OAuth Connections</h3>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Provider</th>
                    <th>Provider Email</th>
                    <th>Provider Name</th>
                    <th>Last Used</th>
                    <th>Connected</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($connections)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No OAuth connections found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($connections as $connection): ?>
                        <tr>
                            <td>
                                <strong><?= View::e($connection['user_name']) ?></strong>
                                <br>
                                <small style="color: var(--text-secondary);"><?= View::e($connection['user_email']) ?></small>
                            </td>
                            <td>
                                <span class="badge badge-primary"><?= View::e($connection['provider_name']) ?></span>
                            </td>
                            <td><?= View::e($connection['provider_email']) ?></td>
                            <td><?= View::e($connection['provider_name'] ?? '-') ?></td>
                            <td>
                                <?php if ($connection['last_used_at']): ?>
                                    <?= date('M d, Y H:i', strtotime($connection['last_used_at'])) ?>
                                <?php else: ?>
                                    Never
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($connection['created_at'])) ?></td>
                            <td>
                                <form method="POST" action="/admin/oauth/connections/<?= $connection['id'] ?>/revoke" 
                                      style="display: inline;" 
                                      onsubmit="return confirm('Are you sure you want to revoke this OAuth connection?');">
                                    <?= Security::csrfField() ?>
                                    <button type="submit" class="btn btn-sm btn-danger">Revoke</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if (!empty($pagination) && $pagination['total'] > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $pagination['total']; $i++): ?>
                <?php if ($i == $pagination['current']): ?>
                    <span class="page-current"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>" class="page-link"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php View::endSection(); ?>
