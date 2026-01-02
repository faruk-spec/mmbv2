<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">📋 User Activity Logs</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <label>Filter by User:</label>
            <select name="user_id" onchange="this.form.submit()" class="form-control" style="max-width: 400px;">
                <option value="">-- All Users --</option>
                <?php foreach ($allUsers as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= ($selectedUserId == $user['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <p><strong>Total Logs:</strong> <?= $totalCount ?></p>

        <?php if (!empty($logs)): ?>
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
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= $log['id'] ?></td>
                                <td>
                                    <?php if (isset($log['user_name'])): ?>
                                        <?= htmlspecialchars($log['user_name']) ?><br>
                                        <small class="text-secondary"><?= htmlspecialchars($log['email'] ?? '') ?></small>
                                    <?php else: ?>
                                        System
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($log['action']) ?></td>
                                <td><?= htmlspecialchars($log['resource_type']) ?> #<?= $log['resource_id'] ?? 'N/A' ?></td>
                                <td><?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></td>
                                <td><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?><?= $selectedUserId ? '&user_id=' . $selectedUserId : '' ?>" class="btn <?= $i == $currentPage ? 'btn-primary' : 'btn-secondary' ?> btn-sm"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-secondary">No activity logs found.</p>
        <?php endif; ?>
    </div>
</div>

<?php View::endSection(); ?>
