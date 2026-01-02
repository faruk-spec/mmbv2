<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="margin-bottom: 30px;">
    <a href="/admin/logs" style="color: var(--text-secondary);">&larr; Back to Logs</a>
    <h1 style="margin-top: 10px;">Activity Logs</h1>
</div>

<div class="card mb-3">
    <form method="GET" action="/admin/logs/activity" style="display: flex; gap: 15px; flex-wrap: wrap;">
        <select name="action" class="form-input" style="max-width: 200px;">
            <option value="">All Actions</option>
            <?php foreach ($actions as $action): ?>
                <option value="<?= View::e($action['action']) ?>" <?= $currentAction === $action['action'] ? 'selected' : '' ?>>
                    <?= View::e($action['action']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <input type="number" name="user_id" class="form-input" style="max-width: 150px;" 
               placeholder="User ID" value="<?= View::e($currentUserId) ?>">
        
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="/admin/logs/activity" class="btn btn-secondary">Reset</a>
    </form>
</div>

<div class="card">
    <?php if (empty($logs)): ?>
        <p style="color: var(--text-secondary); text-align: center; padding: 30px;">No activity logs found</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>IP Address</th>
                    <th>Date</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 500;"><?= View::e($log['name'] ?? 'Unknown') ?></div>
                            <div style="font-size: 12px; color: var(--text-secondary);"><?= View::e($log['email'] ?? '') ?></div>
                        </td>
                        <td><span class="badge badge-info"><?= View::e($log['action']) ?></span></td>
                        <td style="font-family: monospace;"><?= View::e($log['ip_address']) ?></td>
                        <td><?= Helpers::formatDate($log['created_at'], 'M d, Y H:i') ?></td>
                        <td>
                            <?php if (!empty($log['data'])): ?>
                                <small style="color: var(--text-secondary);"><?= View::e(Helpers::truncate($log['data'], 30)) ?></small>
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
                    <a href="?page=<?= $pagination['current'] - 1 ?>&action=<?= urlencode($currentAction) ?>&user_id=<?= urlencode($currentUserId) ?>" class="btn btn-sm btn-secondary">Previous</a>
                <?php endif; ?>
                
                <span style="padding: 8px 16px; color: var(--text-secondary);">
                    Page <?= $pagination['current'] ?> of <?= $pagination['total'] ?>
                </span>
                
                <?php if ($pagination['current'] < $pagination['total']): ?>
                    <a href="?page=<?= $pagination['current'] + 1 ?>&action=<?= urlencode($currentAction) ?>&user_id=<?= urlencode($currentUserId) ?>" class="btn btn-sm btn-secondary">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php View::endSection(); ?>
