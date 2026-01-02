<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">👥 ProShare Users</h3>
    </div>
    <div class="card-body">
        <?php if (empty($users)): ?>
            <p class="text-secondary">No users have used ProShare yet.</p>
        <?php else: ?>
            <div class="grid grid-3">
                <?php foreach ($users as $user): ?>
                    <div class="card">
                        <div class="card-body">
                            <h4><?= htmlspecialchars($user['name']) ?></h4>
                            <p class="text-secondary"><?= htmlspecialchars($user['email']) ?></p>
                            <p class="text-secondary"><strong>User ID:</strong> #<?= $user['id'] ?></p>
                            <div class="mt-2" style="display: flex; gap: 10px;">
                                <a href="/admin/projects/proshare/user-files?user_id=<?= $user['id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-file"></i> View Files
                                </a>
                                <a href="/admin/projects/proshare/user-activity?user_id=<?= $user['id'] ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-history"></i> Activity
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php View::endSection(); ?>
