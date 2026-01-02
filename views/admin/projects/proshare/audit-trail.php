<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">📖 Audit Trail</h3>
        <div class="card-actions">
            <a href="/admin/projects/proshare/audit-trail/export?format=csv" class="btn btn-sm btn-primary">
                <i class="fas fa-download"></i> Export CSV
            </a>
            <a href="/admin/projects/proshare/audit-trail/export?format=json" class="btn btn-sm btn-secondary">
                <i class="fas fa-download"></i> Export JSON
            </a>
        </div>
    </div>
    <div class="card-body">
        <p><strong>Total Audit Logs:</strong> <?= number_format($totalCount) ?></p>

        <?php if (!empty($auditLogs)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Resource</th>
                            <th>IP Address</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($auditLogs as $log): ?>
                            <tr>
                                <td><?= $log['id'] ?></td>
                                <td>
                                    <?= htmlspecialchars($log['user_name'] ?? 'System') ?><br>
                                    <small class="text-secondary"><?= htmlspecialchars($log['email'] ?? '') ?></small>
                                </td>
                                <td><span class="badge badge-info"><?= htmlspecialchars($log['action']) ?></span></td>
                                <td><?= htmlspecialchars($log['resource_type']) ?> #<?= $log['resource_id'] ?? 'N/A' ?></td>
                                <td><?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></td>
                                <td><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination mt-3">
                    <?php for ($i = 1; $i <= min($totalPages, 20); $i++): ?>
                        <a href="?page=<?= $i ?>" class="btn <?= $i == $currentPage ? 'btn-primary' : 'btn-secondary' ?> btn-sm"><?= $i ?></a>
                    <?php endfor; ?>
                    <?php if ($totalPages > 20): ?>
                        <span class="text-secondary">... <?= $totalPages ?> pages total</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-secondary">No audit logs found.</p>
        <?php endif; ?>
    </div>
</div>

<?php View::endSection(); ?>
