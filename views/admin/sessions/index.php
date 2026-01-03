<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('admin/layout'); ?>

<?php View::section('content'); ?>
<div class="admin-content">
    <div class="page-header">
        <h1>Active Sessions</h1>
        <p>Monitor and manage user sessions</p>
    </div>
    
    <?php if (Helpers::hasFlash('success')): ?>
        <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3>Active User Sessions</h3>
            <div style="display: flex; gap: 10px;">
                <form method="GET" action="/admin/sessions" style="display: flex; gap: 10px;">
                    <input type="text" name="search" class="form-input" 
                           placeholder="Search by user or IP..." 
                           value="<?= View::e($search ?? '') ?>" 
                           style="min-width: 250px;">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
                <form method="POST" action="/admin/sessions/cleanup" 
                      onsubmit="return confirm('Clean up all expired sessions?');">
                    <?= Security::csrfField() ?>
                    <button type="submit" class="btn btn-secondary">Cleanup Expired</button>
                </form>
            </div>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>IP Address</th>
                    <th>Device</th>
                    <th>Browser</th>
                    <th>Last Activity</th>
                    <th>Expires</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sessions)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No active sessions found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sessions as $session): ?>
                        <?php 
                            $deviceInfo = json_decode($session['device_info'], true);
                            $expiresAt = strtotime($session['expires_at']);
                            $isExpiringSoon = ($expiresAt - time()) < 600; // Less than 10 minutes
                        ?>
                        <tr>
                            <td>
                                <strong><?= View::e($session['user_name']) ?></strong>
                                <br>
                                <small style="color: var(--text-secondary);"><?= View::e($session['user_email']) ?></small>
                            </td>
                            <td><code><?= View::e($session['ip_address']) ?></code></td>
                            <td><?= View::e($deviceInfo['device'] ?? 'Unknown') ?></td>
                            <td>
                                <?= View::e($deviceInfo['browser'] ?? 'Unknown') ?>
                                <br>
                                <small style="color: var(--text-secondary);"><?= View::e($deviceInfo['platform'] ?? '') ?></small>
                            </td>
                            <td>
                                <?= Helpers::timeAgo(strtotime($session['last_activity_at'])) ?>
                            </td>
                            <td>
                                <span class="<?= $isExpiringSoon ? 'text-warning' : '' ?>">
                                    <?= Helpers::timeAgo($expiresAt) ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="/admin/sessions/<?= $session['id'] ?>/revoke" 
                                      style="display: inline;" 
                                      onsubmit="return confirm('Revoke this session? The user will be logged out.');">
                                    <?= Security::csrfField() ?>
                                    <button type="submit" class="btn btn-sm btn-danger">Revoke</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if (!empty($pagination) && $pagination['total'] > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $pagination['total']; $i++): ?>
                <?php if ($i == $pagination['current']): ?>
                    <span class="page-current"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="page-link"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="card" style="margin-top: 20px;">
        <div class="card-header">
            <h3>Quick Actions</h3>
        </div>
        <div class="card-body">
            <a href="/admin/sessions/login-history" class="btn btn-secondary">
                View Login History
            </a>
            <a href="/admin/oauth/connections" class="btn btn-secondary">
                View OAuth Connections
            </a>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
