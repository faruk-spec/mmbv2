<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: linear-gradient(135deg, var(--bg-card), var(--bg-secondary));
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 5px;
        color: var(--cyan);
    }
    .stat-label {
        color: var(--text-secondary);
        font-size: 14px;
    }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['uploads']) ?></div>
        <div class="stat-label">📤 Total Uploads</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['downloads']) ?></div>
        <div class="stat-label">📥 Total Downloads</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['deletes']) ?></div>
        <div class="stat-label">🗑️ Total Deletes</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['shares']) ?></div>
        <div class="stat-label">🔗 Total Shares</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">📋 File Activity Logs</h3>
        <div class="card-actions">
            <a href="?action=all" class="btn btn-sm <?= $currentAction === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
            <a href="?action=upload" class="btn btn-sm <?= $currentAction === 'upload' ? 'btn-primary' : 'btn-secondary' ?>">Uploads</a>
            <a href="?action=download" class="btn btn-sm <?= $currentAction === 'download' ? 'btn-primary' : 'btn-secondary' ?>">Downloads</a>
            <a href="?action=delete" class="btn btn-sm <?= $currentAction === 'delete' ? 'btn-primary' : 'btn-secondary' ?>">Deletes</a>
            <a href="?action=share" class="btn btn-sm <?= $currentAction === 'share' ? 'btn-primary' : 'btn-secondary' ?>">Shares</a>
        </div>
    </div>
    <div class="card-body">
        <p><strong>Total Activities:</strong> <?= number_format($totalCount) ?></p>

        <?php if (!empty($activities)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Resource</th>
                            <th>IP Address</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($activity['user_name'] ?? 'System') ?><br>
                                    <small class="text-secondary"><?= htmlspecialchars($activity['email'] ?? '') ?></small>
                                </td>
                                <td><?= htmlspecialchars($activity['action']) ?></td>
                                <td><?= htmlspecialchars($activity['resource_type']) ?> #<?= $activity['resource_id'] ?? 'N/A' ?></td>
                                <td><?= htmlspecialchars($activity['ip_address'] ?? 'N/A') ?></td>
                                <td><?= date('Y-m-d H:i:s', strtotime($activity['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination mt-3">
                    <?php for ($i = 1; $i <= min($totalPages, 10); $i++): ?>
                        <a href="?action=<?= $currentAction ?>&page=<?= $i ?>" class="btn <?= $i == $currentPage ? 'btn-primary' : 'btn-secondary' ?> btn-sm"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-secondary">No file activities found.</p>
        <?php endif; ?>
    </div>
</div>

<?php View::endSection(); ?>
