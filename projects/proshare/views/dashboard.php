<?php use Core\View; ?>
<?php View::extend('proshare:app'); ?>

<?php View::section('content'); ?>

<!-- Quick Actions -->
<div class="grid grid-2 mb-3">
    <a href="/projects/proshare/upload" class="btn btn-primary" style="padding: 20px; font-size: 1.1rem;">
        <i class="fas fa-cloud-upload-alt"></i>
        Upload Files
    </a>
    <a href="/projects/proshare/text" class="btn btn-secondary" style="padding: 20px; font-size: 1.1rem;">
        <i class="fas fa-file-alt"></i>
        Share Text
    </a>
</div>

<!-- Statistics -->
<div class="grid grid-4 mb-3">
    <div class="stat-card">
        <div class="stat-value" style="color: var(--cyan);"><?= $stats['total_files'] ?? 0 ?></div>
        <div class="stat-label">Total Files</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value" style="color: var(--magenta);"><?= $stats['total_texts'] ?? 0 ?></div>
        <div class="stat-label">Text Shares</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value" style="color: var(--green);"><?= $stats['total_downloads'] ?? 0 ?></div>
        <div class="stat-label">Total Downloads</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value" style="color: var(--orange);"><?= $stats['active_shares'] ?? 0 ?></div>
        <div class="stat-label">Active Shares</div>
    </div>
</div>

<!-- Recent Files -->
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-file"></i> Recent Files
        </h3>
    </div>
    
    <?php if (!empty($recentFiles)): ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Size</th>
                        <th>Status</th>
                        <th>Downloads</th>
                        <th>Expires</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentFiles as $file): ?>
                        <tr>
                            <td>
                                <i class="fas fa-file" style="color: var(--cyan); margin-right: 8px;"></i>
                                <?= View::e($file['original_name']) ?>
                            </td>
                            <td><?= number_format($file['size'] / 1024 / 1024, 2) ?> MB</td>
                            <td>
                                <?php if ($file['status'] === 'active'): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php elseif ($file['status'] === 'expired'): ?>
                                    <span class="badge badge-danger">Expired</span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><?= ucfirst($file['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $file['downloads'] ?>
                                <?php if ($file['max_downloads']): ?>
                                    / <?= $file['max_downloads'] ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($file['expires_at']): ?>
                                    <?= date('M d, Y H:i', strtotime($file['expires_at'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">Never</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/projects/proshare/preview/<?= $file['short_code'] ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>No Files Yet</h3>
            <p class="text-muted">Start sharing files with your team</p>
            <a href="/projects/proshare/upload" class="btn btn-primary mt-2">
                <i class="fas fa-cloud-upload-alt"></i> Upload Your First File
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Recent Text Shares -->
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-file-alt"></i> Recent Text Shares
        </h3>
    </div>
    
    <?php if (!empty($recentTexts)): ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentTexts as $text): ?>
                        <tr>
                            <td>
                                <i class="fas fa-file-alt" style="color: var(--magenta); margin-right: 8px;"></i>
                                <?= View::e($text['title'] ?: 'Untitled') ?>
                            </td>
                            <td>
                                <?php if ($text['status'] === 'active'): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php elseif ($text['status'] === 'expired'): ?>
                                    <span class="badge badge-danger">Expired</span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><?= ucfirst($text['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $text['views'] ?>
                                <?php if ($text['max_views']): ?>
                                    / <?= $text['max_views'] ?>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y H:i', strtotime($text['created_at'])) ?></td>
                            <td>
                                <a href="/t/<?= $text['short_code'] ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;" target="_blank">
                                    <i class="fas fa-external-link-alt"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-file-alt"></i>
            <h3>No Text Shares Yet</h3>
            <p class="text-muted">Share text, code, or notes securely</p>
            <a href="/projects/proshare/text" class="btn btn-primary mt-2">
                <i class="fas fa-plus"></i> Create Your First Text Share
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Notifications -->
<?php if (!empty($notifications)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-bell"></i> Recent Notifications
        </h3>
    </div>
    
    <div style="padding: 0;">
        <?php foreach ($notifications as $notification): ?>
            <div style="padding: 15px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 15px;">
                <div style="width: 40px; height: 40px; background: rgba(0, 240, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <?php if ($notification['type'] === 'download'): ?>
                        <i class="fas fa-download" style="color: var(--cyan);"></i>
                    <?php elseif ($notification['type'] === 'expiry_warning'): ?>
                        <i class="fas fa-clock" style="color: var(--orange);"></i>
                    <?php elseif ($notification['type'] === 'security_alert'): ?>
                        <i class="fas fa-shield-alt" style="color: var(--red);"></i>
                    <?php else: ?>
                        <i class="fas fa-bell" style="color: var(--cyan);"></i>
                    <?php endif; ?>
                </div>
                <div style="flex: 1;">
                    <div><?= View::e($notification['message']) ?></div>
                    <div class="text-muted" style="font-size: 0.85rem; margin-top: 4px;">
                        <?= date('M d, Y H:i', strtotime($notification['created_at'])) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div style="padding: 15px 24px; text-align: center;">
        <a href="/projects/proshare/notifications" class="btn btn-secondary">
            View All Notifications
        </a>
    </div>
</div>
<?php endif; ?>

<?php View::endSection(); ?>
