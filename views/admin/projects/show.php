<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="margin-bottom: 30px;">
    <a href="/admin/projects" style="color: var(--text-secondary);">&larr; Back to Projects</a>
    <h1 style="margin-top: 10px;"><?= View::e($project['name']) ?></h1>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>

<div class="grid grid-3">
    <div style="grid-column: span 2;">
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Project Details</h3>
                <span class="badge <?= $project['enabled'] ? 'badge-success' : 'badge-danger' ?>">
                    <?= $project['enabled'] ? 'Active' : 'Disabled' ?>
                </span>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <div style="color: var(--text-secondary); font-size: 13px; margin-bottom: 5px;">Name</div>
                    <div style="font-weight: 500;"><?= View::e($project['name']) ?></div>
                </div>
                
                <div>
                    <div style="color: var(--text-secondary); font-size: 13px; margin-bottom: 5px;">URL</div>
                    <div><a href="<?= $project['url'] ?>"><?= $project['url'] ?></a></div>
                </div>
                
                <div>
                    <div style="color: var(--text-secondary); font-size: 13px; margin-bottom: 5px;">Database</div>
                    <div style="font-weight: 500;"><?= View::e($project['database']) ?></div>
                </div>
                
                <div>
                    <div style="color: var(--text-secondary); font-size: 13px; margin-bottom: 5px;">Color</div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 20px; height: 20px; background: <?= $project['color'] ?>; border-radius: 4px;"></span>
                        <span><?= $project['color'] ?></span>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <div style="color: var(--text-secondary); font-size: 13px; margin-bottom: 5px;">Description</div>
                <div><?= View::e($project['description']) ?></div>
            </div>
        </div>
    </div>
    
    <div>
        <div class="card">
            <h4 style="margin-bottom: 15px;">Quick Actions</h4>
            
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <a href="/admin/projects/<?= $project['key'] ?>/settings" class="btn btn-secondary" style="width: 100%;">
                    Settings
                </a>
                
                <form method="POST" action="/admin/projects/<?= $project['key'] ?>/toggle">
                    <?= \Core\Security::csrfField() ?>
                    <button type="submit" class="btn <?= $project['enabled'] ? 'btn-danger' : 'btn-primary' ?>" style="width: 100%;">
                        <?= $project['enabled'] ? 'Disable Project' : 'Enable Project' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
