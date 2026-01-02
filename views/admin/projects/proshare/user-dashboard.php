<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            ProShare Users
        </h3>
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
