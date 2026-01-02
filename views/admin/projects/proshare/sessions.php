<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                <line x1="8" y1="21" x2="16" y2="21"></line>
                <line x1="12" y1="17" x2="12" y2="21"></line>
            </svg>
            Session History
        </h3>
    </div>
    <div class="card-body">
        <p><strong>Total Sessions:</strong> <?= number_format($totalCount) ?></p>

        <?php if (!empty($devices)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Device</th>
                            <th>Browser</th>
                            <th>Platform</th>
                            <th>IP Address</th>
                            <th>Last Active</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($devices as $device): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($device['name'] ?? 'Unknown') ?><br>
                                    <small class="text-secondary"><?= htmlspecialchars($device['email'] ?? '') ?></small>
                                </td>
                                <td><?= htmlspecialchars($device['device_name'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($device['browser'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($device['platform'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($device['ip_address']) ?></td>
                                <td><?= date('Y-m-d H:i:s', strtotime($device['last_active_at'])) ?></td>
                                <td>
                                    <?php 
                                    $isOnline = strtotime($device['last_active_at']) > strtotime('-15 minutes');
                                    ?>
                                    <span class="badge <?= $isOnline ? 'badge-success' : 'badge-secondary' ?>">
                                        <?= $isOnline ? 'Online' : 'Offline' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination mt-3">
                    <?php for ($i = 1; $i <= min($totalPages, 10); $i++): ?>
                        <a href="?page=<?= $i ?>" class="btn <?= $i == $currentPage ? 'btn-primary' : 'btn-secondary' ?> btn-sm"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-secondary">No session data found.</p>
        <?php endif; ?>
    </div>
</div>

<?php View::endSection(); ?>
