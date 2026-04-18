<?php use Core\View; use Core\Security; ?>
<?php View::extend('proshare:app'); ?>

<?php View::section('content'); ?>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-bell"></i> Notifications
        </h3>
        <?php if (!empty($notifications) && count(array_filter($notifications, fn($n) => !$n['is_read'])) > 0): ?>
            <button id="markAllBtn" class="btn btn-secondary" style="padding: 6px 14px; font-size: 0.8rem;">
                <i class="fas fa-check-double"></i> Mark All as Read
            </button>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($notifications)): ?>
        <div id="notificationsList" style="padding: 0;">
            <?php foreach ($notifications as $notification): ?>
                <div id="notif-<?= $notification['id'] ?>" style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: flex-start; gap: 15px; <?= !$notification['is_read'] ? 'background: rgba(0, 240, 255, 0.03);' : '' ?>">
                    <div style="width: 46px; height: 46px; background: <?= !$notification['is_read'] ? 'rgba(0, 240, 255, 0.1)' : 'rgba(136, 146, 166, 0.08)' ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <?php if ($notification['type'] === 'download'): ?>
                            <i class="fas fa-download" style="color: var(--cyan);"></i>
                        <?php elseif ($notification['type'] === 'expiry_warning'): ?>
                            <i class="fas fa-clock" style="color: var(--orange);"></i>
                        <?php elseif ($notification['type'] === 'security_alert'): ?>
                            <i class="fas fa-shield-alt" style="color: var(--ps-danger);"></i>
                        <?php elseif ($notification['type'] === 'upload_complete'): ?>
                            <i class="fas fa-check-circle" style="color: var(--green);"></i>
                        <?php else: ?>
                            <i class="fas fa-bell" style="color: var(--cyan);"></i>
                        <?php endif; ?>
                    </div>
                    
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px; gap: 10px; flex-wrap: wrap;">
                            <div>
                                <span style="font-weight: 600; color: <?= !$notification['is_read'] ? 'var(--cyan)' : 'var(--text-primary)' ?>;">
                                    <?php
                                    $types = [
                                        'download'       => 'File Downloaded',
                                        'expiry_warning' => 'Expiry Warning',
                                        'security_alert' => 'Security Alert',
                                        'upload_complete'=> 'Upload Complete',
                                    ];
                                    echo $types[$notification['type']] ?? 'Notification';
                                    ?>
                                </span>
                                <?php if (!$notification['is_read']): ?>
                                    <span class="badge badge-info" style="margin-left: 8px; font-size: 0.7rem; padding: 2px 8px;">New</span>
                                <?php endif; ?>
                            </div>
                            <span class="text-muted" style="font-size: 0.8rem; white-space: nowrap; flex-shrink: 0;">
                                <?= date('M d, H:i', strtotime($notification['created_at'])) ?>
                            </span>
                        </div>
                        
                        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 10px; word-break: break-word;">
                            <?= View::e($notification['message']) ?>
                        </div>
                        
                        <?php if (!$notification['is_read']): ?>
                            <button onclick="markRead(<?= $notification['id'] ?>, this)" class="btn btn-secondary" style="padding: 4px 12px; font-size: 0.8rem;">
                                <i class="fas fa-check"></i> Mark as Read
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-bell-slash"></i>
            <h3>No Notifications</h3>
            <p class="text-muted">You&rsquo;re all caught up!</p>
            <a href="/projects/proshare/dashboard" class="btn btn-primary mt-2">
                <i class="fas fa-home"></i> Back to Dashboard
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Notification Statistics -->
<?php if (!empty($notifications)): ?>
<div class="ps-grid ps-grid-4">
    <div class="stat-card">
        <div class="stat-value" style="color: var(--cyan);"><?= count($notifications) ?></div>
        <div class="stat-label">Total</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--orange);"><?= count(array_filter($notifications, fn($n) => !$n['is_read'])) ?></div>
        <div class="stat-label">Unread</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--cyan);"><?= count(array_filter($notifications, fn($n) => $n['type'] === 'download')) ?></div>
        <div class="stat-label">Downloads</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--ps-danger);"><?= count(array_filter($notifications, fn($n) => $n['type'] === 'security_alert')) ?></div>
        <div class="stat-label">Security Alerts</div>
    </div>
</div>
<?php endif; ?>

<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_csrf_token"]')?.value || '';
    
    async function markRead(id, btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        try {
            const fd = new FormData();
            fd.append('notification_id', id);
            if (csrfToken) fd.append('_csrf_token', csrfToken);
            
            const response = await fetch('/projects/proshare/notifications/mark-read', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: fd
            });
            const data = await response.json();
            
            if (data.success) {
                const notif = document.getElementById('notif-' + id);
                if (notif) {
                    notif.style.background = 'transparent';
                    const icon = notif.querySelector('.badge');
                    if (icon) icon.remove();
                    const titleEl = notif.querySelector('[style*="color"]');
                    if (titleEl) titleEl.style.color = 'var(--text-primary)';
                    btn.remove();
                    
                    // Update unread stats
                    updateUnreadCount();
                }
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check"></i> Mark as Read';
            }
        } catch (_) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Mark as Read';
        }
    }
    
    document.getElementById('markAllBtn')?.addEventListener('click', async function() {
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Marking…';
        
        const fd = new FormData();
        fd.append('mark_all', '1');
        if (csrfToken) fd.append('_csrf_token', csrfToken);
        
        try {
            const response = await fetch('/projects/proshare/notifications/mark-read', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: fd
            });
            const data = await response.json();
            if (data.success) {
                // Reload to show updated state
                location.reload();
            } else {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-check-double"></i> Mark All as Read';
            }
        } catch (_) {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-check-double"></i> Mark All as Read';
        }
    });
    
    function updateUnreadCount() {
        const unreadBadges = document.querySelectorAll('.badge.badge-info');
        const stat = document.querySelectorAll('.stat-card .stat-value')[1];
        if (stat) stat.textContent = unreadBadges.length;
        
        // Hide "Mark All" button if no unread
        if (unreadBadges.length === 0) {
            document.getElementById('markAllBtn')?.remove();
        }
    }
</script>
<?php View::endSection(); ?>
