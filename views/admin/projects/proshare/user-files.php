<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">📁 User Files</h3>
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
            <h4>Files for <?= htmlspecialchars($selectedUser['name']) ?></h4>
            <p><strong>Total Files:</strong> <?= $totalCount ?></p>

            <?php if (!empty($files)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Filename</th>
                                <th>Size</th>
                                <th>Downloads</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files as $file): ?>
                                <tr>
                                    <td><?= $file['id'] ?></td>
                                    <td><?= htmlspecialchars($file['original_name'] ?? $file['filename']) ?></td>
                                    <td><?= round(($file['size'] ?? 0) / 1024 / 1024, 2) ?> MB</td>
                                    <td><?= $file['downloads'] ?? 0 ?></td>
                                    <td><span class="badge badge-success"><?= $file['status'] ?? 'active' ?></span></td>
                                    <td><?= date('Y-m-d H:i', strtotime($file['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?user_id=<?= $selectedUser['id'] ?>&page=<?= $i ?>" class="btn <?= $i == $currentPage ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-secondary">No files found for this user.</p>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-secondary">Please select a user to view their files.</p>
        <?php endif; ?>
    </div>
</div>

<?php View::endSection(); ?>
