<?php use Core\View; use Core\Auth; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<style>
.notif-page-wrap { max-width:720px; margin:0 auto; }
.notif-page-item {
    display:flex; gap:14px; align-items:flex-start;
    padding:14px 16px; border-radius:10px; margin-bottom:8px;
    background:var(--bg-card); border:1px solid var(--border-color);
    transition:background .2s;
}
.notif-page-item.unread { border-left:3px solid var(--cyan); }
.notif-page-item.read   { opacity:.75; }
.notif-page-dot {
    width:9px; height:9px; border-radius:50%; flex-shrink:0; margin-top:5px;
    background:var(--cyan);
}
.notif-page-item.read .notif-page-dot { background:var(--text-secondary); }
.notif-page-body { flex:1; min-width:0; }
.notif-page-msg  { color:var(--text-primary); font-size:14px; line-height:1.5; }
.notif-page-time { color:var(--text-secondary); font-size:11px; margin-top:4px; }
.notif-type-badge {
    font-size:10px; padding:2px 7px; border-radius:20px; font-weight:600;
    text-transform:uppercase; letter-spacing:.4px; flex-shrink:0; align-self:flex-start;
}
.type-info    { background:rgba(0,240,255,.1);  color:var(--cyan); }
.type-success { background:rgba(0,255,136,.1);  color:var(--green); }
.type-warning { background:rgba(255,170,0,.1);  color:var(--orange); }
.type-error   { background:rgba(255,107,107,.1);color:var(--red); }
.notif-page-empty { text-align:center; padding:60px 20px; color:var(--text-secondary); }
.notif-page-empty i { font-size:3rem; margin-bottom:16px; display:block; opacity:.4; }
</style>

<div class="notif-page-wrap">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <h1 style="font-size:1.4rem;margin:0;">
            <i class="fas fa-bell" style="color:var(--cyan);margin-right:8px;"></i>
            All Notifications
        </h1>
        <a href="/dashboard" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="notif-page-empty">
            <i class="fas fa-bell-slash"></i>
            <p style="font-size:16px;">No notifications yet</p>
            <p style="font-size:13px;">You're all caught up! Notifications about your activity will appear here.</p>
        </div>
    <?php else: ?>
        <p style="font-size:13px;color:var(--text-secondary);margin-bottom:16px;">
            <?= count($notifications) ?> notification<?= count($notifications) !== 1 ? 's' : '' ?> &mdash; all marked as read
        </p>
        <?php foreach ($notifications as $n): ?>
            <?php
            $typeClass = match($n['type'] ?? 'info') {
                'success' => 'type-success',
                'warning' => 'type-warning',
                'error'   => 'type-error',
                default   => 'type-info',
            };
            $readClass = $n['is_read'] ? 'read' : 'unread';
            ?>
            <div class="notif-page-item <?= $readClass ?>">
                <div class="notif-page-dot"></div>
                <div class="notif-page-body">
                    <div class="notif-page-msg"><?= View::e($n['message']) ?></div>
                    <?php if (!empty($n['data']) && is_array($n['data'])): ?>
                        <div style="font-size:11px;color:var(--text-secondary);margin-top:4px;">
                            <?php foreach ($n['data'] as $k => $v): ?>
                                <span style="margin-right:10px;"><strong><?= View::e($k) ?>:</strong> <?= View::e(is_scalar($v) ? $v : json_encode($v)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="notif-page-time">
                        <i class="fas fa-clock" style="margin-right:4px;"></i>
                        <?= View::e(date('M j, Y g:i A', strtotime($n['created_at']))) ?>
                    </div>
                </div>
                <span class="notif-type-badge <?= $typeClass ?>"><?= View::e($n['type'] ?? 'info') ?></span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php View::endSection(); ?>
