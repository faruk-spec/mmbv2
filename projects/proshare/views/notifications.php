<?php use Core\View; ?>
<?php View::extend('proshare:app'); ?>

<?php View::section('content'); ?>

<div class="card mb-3">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">
                <i class="fas fa-bell"></i> Notifications
            </h3>
            <?php if (!empty($notifications) && count(array_filter($notifications, fn($n) => !$n['is_read'])) > 0): ?>
                <form method="POST" action="/projects/proshare/notifications/mark-read" style="display: inline;">
                    <button type="submit" name="mark_all" value="1" class="btn btn-secondary">
                        <i class="fas fa-check-double"></i> Mark All as Read
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (!empty($notifications)): ?>
        <div style="padding: 0;">
            <?php foreach ($notifications as $notification): ?>
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: start; gap: 15px; <?= !$notification['is_read'] ? 'background: rgba(0, 240, 255, 0.03);' : '' ?>">
                    <div style="width: 50px; height: 50px; background: <?= !$notification['is_read'] ? 'rgba(0, 240, 255, 0.1)' : 'rgba(136, 146, 166, 0.1)' ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <?php if ($notification['type'] === 'download'): ?>
                            <i class="fas fa-download" style="color: var(--cyan); font-size: 1.2rem;"></i>
                        <?php elseif ($notification['type'] === 'expiry_warning'): ?>
                            <i class="fas fa-clock" style="color: var(--orange); font-size: 1.2rem;"></i>
                        <?php elseif ($notification['type'] === 'security_alert'): ?>
                            <i class="fas fa-shield-alt" style="color: var(--red); font-size: 1.2rem;"></i>
                        <?php elseif ($notification['type'] === 'upload_complete'): ?>
                            <i class="fas fa-check-circle" style="color: var(--green); font-size: 1.2rem;"></i>
                        <?php else: ?>
                            <i class="fas fa-bell" style="color: var(--cyan); font-size: 1.2rem;"></i>
                        <?php endif; ?>
                    </div>
                    
                    <div style="flex: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                            <div>
                                <span style="font-weight: 600; color: var(--text-primary); <?= !$notification['is_read'] ? 'color: var(--cyan);' : '' ?>">
                                    <?php
                                    $types = [
                                        'download' => 'File Downloaded',
                                        'expiry_warning' => 'Expiry Warning',
                                        'security_alert' => 'Security Alert',
                                        'upload_complete' => 'Upload Complete'
                                    ];
                                    echo $types[$notification['type']] ?? 'Notification';
                                    ?>
                                </span>
                                <?php if (!$notification['is_read']): ?>
                                    <span class="badge badge-info" style="margin-left: 8px;">New</span>
                                <?php endif; ?>
                            </div>
                            <span class="text-muted" style="font-size: 0.85rem; white-space: nowrap;">
                                <?= date('M d, H:i', strtotime($notification['created_at'])) ?>
                            </span>
                        </div>
                        
                        <div style="color: var(--text-secondary); margin-bottom: 10px;">
                            <?= View::e($notification['message']) ?>
                        </div>
                        
                        <div style="display: flex; gap: 10px;">
                            <?php if (!$notification['is_read']): ?>
                                <form method="POST" action="/projects/proshare/notifications/mark-read" style="display: inline;">
                                    <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                    <button type="submit" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;">
                                        <i class="fas fa-check"></i> Mark as Read
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($notification['related_id']): ?>
                                <a href="/projects/proshare/dashboard" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;">
                                    <i class="fas fa-external-link-alt"></i> View Details
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-bell-slash"></i>
            <h3>No Notifications</h3>
            <p class="text-muted">You're all caught up!</p>
            <a href="/projects/proshare/dashboard" class="btn btn-primary mt-2">
                <i class="fas fa-home"></i> Back to Dashboard
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Notification Statistics -->
<?php if (!empty($notifications)): ?>
<div class="grid grid-4">
    <div class="stat-card">
        <div class="stat-value" style="color: var(--cyan);">
            <?= count($notifications) ?>
        </div>
        <div class="stat-label">Total Notifications</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value" style="color: var(--orange);">
            <?= count(array_filter($notifications, fn($n) => !$n['is_read'])) ?>
        </div>
        <div class="stat-label">Unread</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value" style="color: var(--cyan);">
            <?= count(array_filter($notifications, fn($n) => $n['type'] === 'download')) ?>
        </div>
        <div class="stat-label">Downloads</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value" style="color: var(--red);">
            <?= count(array_filter($notifications, fn($n) => $n['type'] === 'security_alert')) ?>
        </div>
        <div class="stat-label">Security Alerts</div>
    </div>
</div>
<?php endif; ?>

<?php View::endSection(); ?>
