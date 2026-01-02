<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1>Project Management</h1>
        <p style="color: var(--text-secondary);">Manage all platform projects</p>
    </div>
</div>

<div class="grid grid-3">
    <?php foreach ($projects as $key => $project): ?>
        <div class="card">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                <div style="width: 50px; height: 50px; background: <?= $project['color'] ?>20; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="<?= $project['color'] ?>" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                    </svg>
                </div>
                <div>
                    <h3 style="color: <?= $project['color'] ?>;"><?= $project['name'] ?></h3>
                    <span class="badge <?= $project['enabled'] ? 'badge-success' : 'badge-danger' ?>">
                        <?= $project['enabled'] ? 'Active' : 'Disabled' ?>
                    </span>
                </div>
            </div>
            
            <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 20px;">
                <?= $project['description'] ?>
            </p>
            
            <div style="display: flex; gap: 10px;">
                <a href="/admin/projects/<?= $key ?>" class="btn btn-sm btn-secondary">View</a>
                <a href="/admin/projects/<?= $key ?>/settings" class="btn btn-sm btn-secondary">Settings</a>
                <form method="POST" action="/admin/projects/<?= $key ?>/toggle" style="display: inline;">
                    <?= \Core\Security::csrfField() ?>
                    <button type="submit" class="btn btn-sm <?= $project['enabled'] ? 'btn-danger' : 'btn-primary' ?>">
                        <?= $project['enabled'] ? 'Disable' : 'Enable' ?>
                    </button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php View::endSection(); ?>
