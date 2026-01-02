<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="margin-bottom: 30px;">
    <a href="/admin/security" style="color: var(--text-secondary);">&larr; Back to Security</a>
    <h1 style="margin-top: 10px;">Failed Login Attempts</h1>
</div>

<div class="card">
    <?php if (empty($failedLogins)): ?>
        <p style="color: var(--text-secondary); text-align: center; padding: 30px;">No failed login attempts recorded</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>IP Address</th>
                    <th>Attempted At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($failedLogins as $login): ?>
                    <tr>
                        <td><?= View::e($login['username']) ?></td>
                        <td style="font-family: monospace;"><?= View::e($login['ip_address']) ?></td>
                        <td><?= Helpers::formatDate($login['attempted_at'], 'M d, Y H:i:s') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if ($pagination['total'] > 1): ?>
            <div style="margin-top: 20px; display: flex; justify-content: center; gap: 10px;">
                <?php if ($pagination['current'] > 1): ?>
                    <a href="?page=<?= $pagination['current'] - 1 ?>" class="btn btn-sm btn-secondary">Previous</a>
                <?php endif; ?>
                
                <span style="padding: 8px 16px; color: var(--text-secondary);">
                    Page <?= $pagination['current'] ?> of <?= $pagination['total'] ?>
                </span>
                
                <?php if ($pagination['current'] < $pagination['total']): ?>
                    <a href="?page=<?= $pagination['current'] + 1 ?>" class="btn btn-sm btn-secondary">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php View::endSection(); ?>
