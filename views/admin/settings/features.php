<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-header">
    <h1><?= htmlspecialchars($title) ?></h1>
    <p>Enable or disable platform features</p>
</div>

<div class="card">
    <div class="card-header">
        <h2>Feature Flags</h2>
    </div>
    <div class="card-body">
        <?php if (empty($features)): ?>
            <p class="text-secondary">No feature flags configured yet.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Feature</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($features as $feature): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($feature['feature_name']) ?></strong></td>
                        <td><?= htmlspecialchars($feature['description'] ?? 'No description') ?></td>
                        <td>
                            <span class="badge <?= $feature['is_enabled'] ? 'badge-success' : 'badge-secondary' ?>">
                                <?= $feature['is_enabled'] ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </td>
                        <td>
                            <button 
                                class="btn btn-sm btn-primary toggle-feature" 
                                data-feature-id="<?= $feature['id'] ?>"
                                data-current-status="<?= $feature['is_enabled'] ?>">
                                <?= $feature['is_enabled'] ? 'Disable' : 'Enable' ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.toggle-feature').forEach(btn => {
    btn.addEventListener('click', async function() {
        const featureId = this.dataset.featureId;
        const currentStatus = this.dataset.currentStatus;
        
        if (!confirm('Are you sure you want to ' + (currentStatus == '1' ? 'disable' : 'enable') + ' this feature?')) {
            return;
        }
        
        try {
            const response = await fetch('/admin/settings/features/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: '_csrf_token=<?= $csrf_token ?>&feature_id=' + featureId
            });
            
            const data = await response.json();
            
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            alert('Error toggling feature');
        }
    });
});
</script>

<?php View::endSection(); ?>
