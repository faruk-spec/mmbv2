<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<h1 style="margin-bottom: 30px;">Activity Log</h1>

<div class="card">
    <?php if (empty($activity)): ?>
        <p style="color: var(--text-secondary); text-align: center; padding: 30px;">No activity recorded yet</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>IP Address</th>
                    <th>Date</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activity as $log): ?>
                    <tr>
                        <td>
                            <span class="badge badge-info"><?= View::e($log['action']) ?></span>
                        </td>
                        <td><?= View::e($log['ip_address']) ?></td>
                        <td><?= Helpers::formatDate($log['created_at'], 'M d, Y H:i') ?></td>
                        <td>
                            <?php if (!empty($log['data'])): ?>
                                <small style="color: var(--text-secondary);"><?= View::e(Helpers::truncate($log['data'], 50)) ?></small>
                            <?php else: ?>
                                <small style="color: var(--text-secondary);">-</small>
                            <?php endif; ?>
                        </td>
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
