<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">📜 User Activity</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <label>Select User:</label>
            <select name="user_id" onchange="this.form.submit()" class="form-control" style="max-width: 400px;">
                <option value="">-- Select User --</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= ($selectedUser && $selectedUser['id'] == $user['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($selectedUser): ?>
            <h4>Activity for <?= htmlspecialchars($selectedUser['name']) ?></h4>
            <p><strong>Total Activities:</strong> <?= $totalCount ?></p>

            <?php if (!empty($activities)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Action</th>
                                <th>Resource Type</th>
                                <th>Resource ID</th>
                                <th>IP Address</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $activity): ?>
                                <tr>
                                    <td><?= $activity['id'] ?></td>
                                    <td><?= htmlspecialchars($activity['action']) ?></td>
                                    <td><?= htmlspecialchars($activity['resource_type']) ?></td>
                                    <td><?= $activity['resource_id'] ?? 'N/A' ?></td>
                                    <td><?= htmlspecialchars($activity['ip_address'] ?? 'N/A') ?></td>
                                    <td><?= date('Y-m-d H:i:s', strtotime($activity['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?user_id=<?= $selectedUser['id'] ?>&page=<?= $i ?>" class="btn <?= $i == $currentPage ? 'btn-primary' : 'btn-secondary' ?> btn-sm"><?= $i ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-secondary">No activity found for this user.</p>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-secondary">Please select a user to view their activity.</p>
        <?php endif; ?>
    </div>
</div>

<?php View::endSection(); ?>
