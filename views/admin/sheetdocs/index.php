<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="margin-bottom: 30px;">
    <h1>
        <i class="fas fa-file-alt" style="color: #00d4aa;"></i>
        SheetDocs Management
    </h1>
    <p style="color: var(--text-secondary);">Manage documents, sheets, and subscriptions</p>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>

<!-- Statistics Grid -->
<div class="grid grid-4" style="margin-bottom: 30px;">
    <div class="card">
        <h3 style="color: var(--text-secondary); font-size: 14px; margin-bottom: 8px;">Total Documents</h3>
        <div style="font-size: 32px; font-weight: 700; color: #00d4aa;">
            <?= number_format($stats['total_documents']) ?>
        </div>
    </div>
    
    <div class="card">
        <h3 style="color: var(--text-secondary); font-size: 14px; margin-bottom: 8px;">Total Spreadsheets</h3>
        <div style="font-size: 32px; font-weight: 700; color: #00d4aa;">
            <?= number_format($stats['total_sheets']) ?>
        </div>
    </div>
    
    <div class="card">
        <h3 style="color: var(--text-secondary); font-size: 14px; margin-bottom: 8px;">Active Users</h3>
        <div style="font-size: 32px; font-weight: 700; color: #00d4aa;">
            <?= number_format($stats['total_users']) ?>
        </div>
    </div>
    
    <div class="card">
        <h3 style="color: var(--text-secondary); font-size: 14px; margin-bottom: 8px;">Premium Subscribers</h3>
        <div style="font-size: 32px; font-weight: 700; color: #00d4aa;">
            <?= number_format($stats['paid_subscribers']) ?>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="grid grid-2" style="margin-bottom: 30px;">
    <div class="card">
        <h3 style="margin-bottom: 16px;">
            <i class="fas fa-share-alt"></i>
            Sharing Activity
        </h3>
        <div style="font-size: 24px; font-weight: 600; margin-bottom: 8px;">
            <?= number_format($stats['total_shares']) ?>
        </div>
        <p style="color: var(--text-secondary);">Total shares created</p>
    </div>
    
    <div class="card">
        <h3 style="margin-bottom: 16px;">
            <i class="fas fa-database"></i>
            Storage Usage
        </h3>
        <div style="font-size: 24px; font-weight: 600; margin-bottom: 8px;">
            <?= Helpers::formatBytes($stats['storage_used']) ?>
        </div>
        <p style="color: var(--text-secondary);">Total content stored</p>
    </div>
</div>

<!-- Quick Actions -->
<div class="card" style="margin-bottom: 30px;">
    <h2 style="margin-bottom: 20px;">Quick Actions</h2>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="/admin/projects/sheetdocs/documents" class="btn btn-primary">
            <i class="fas fa-file-alt"></i> Manage Documents
        </a>
        <a href="/admin/projects/sheetdocs/subscriptions" class="btn btn-primary">
            <i class="fas fa-crown"></i> Manage Subscriptions
        </a>
        <a href="/admin/projects/sheetdocs/activity" class="btn btn-secondary">
            <i class="fas fa-history"></i> Activity Logs
        </a>
        <a href="/projects/sheetdocs" class="btn btn-secondary" target="_blank">
            <i class="fas fa-external-link-alt"></i> Open SheetDocs
        </a>
    </div>
</div>

<!-- Subscription Overview -->
<?php if (!empty($subscriptionStats)): ?>
<div class="card">
    <h2 style="margin-bottom: 20px;">Subscription Overview</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Plan</th>
                <th>Status</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subscriptionStats as $sub): ?>
            <tr>
                <td>
                    <span class="badge" style="background: <?= $sub['plan'] === 'paid' ? '#00d4aa' : '#8892a6' ?>;">
                        <?= ucfirst($sub['plan']) ?>
                    </span>
                </td>
                <td><?= ucfirst($sub['status']) ?></td>
                <td><strong><?= $sub['count'] ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Recent Activity -->
<?php if (!empty($recentActivity)): ?>
<div class="card" style="margin-top: 30px;">
    <h2 style="margin-bottom: 20px;">Recent Activity</h2>
    <div style="max-height: 400px; overflow-y: auto;">
        <?php foreach (array_slice($recentActivity, 0, 10) as $activity): ?>
        <div style="padding: 12px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong style="color: #00d4aa;">User #<?= $activity['user_id'] ?></strong>
                <span style="color: var(--text-secondary);"><?= View::e($activity['action']) ?></span>
                <?php if ($activity['document_id']): ?>
                    <span style="color: var(--text-secondary);">• Doc #<?= $activity['document_id'] ?></span>
                <?php endif; ?>
            </div>
            <small style="color: var(--text-secondary);">
                <?= Helpers::timeAgo($activity['created_at']) ?>
            </small>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php View::endSection(); ?>
