<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<style>
.admin-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    margin-bottom: 24px;
    overflow: hidden;
}

.admin-card-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.admin-card-title {
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.admin-card-body {
    padding: 0;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: rgba(37, 211, 102, 0.1);
    padding: 12px 24px;
    text-align: left;
    font-size: 0.875rem;
    font-weight: 600;
    border-bottom: 2px solid var(--border-color);
}

.data-table td {
    padding: 16px 24px;
    border-bottom: 1px solid var(--border-color);
    font-size: 0.875rem;
}

.data-table tr:hover {
    background: rgba(37, 211, 102, 0.05);
}

.status-badge-admin {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active {
    background: rgba(37, 211, 102, 0.2);
    color: #25D366;
}

.status-inactive {
    background: rgba(255, 107, 107, 0.2);
    color: #ff6b6b;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    padding: 20px 24px;
    border-top: 1px solid var(--border-color);
}

.pagination-btn {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
    background: #25D366;
    color: white;
    border-color: #25D366;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-info {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.webhook-url {
    max-width: 250px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-family: monospace;
    font-size: 0.8rem;
}
</style>

<div style="max-width: 1600px; margin: 0 auto;">
    <!-- Page Header -->
    <div style="margin-bottom: 30px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="/admin/whatsapp" style="color: var(--text-secondary); text-decoration: none; font-size: 0.875rem;">← Back to Overview</a>
        </div>
        <h1 style="font-size: 2rem; margin-bottom: 8px; display: flex; align-items: center; gap: 12px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#9945ff" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            WhatsApp User Management
        </h1>
        <p style="color: var(--text-secondary); font-size: 0.95rem;">View user settings and WhatsApp usage statistics</p>
    </div>

    <!-- Users Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                WhatsApp Users
            </h3>
            <span style="color: var(--text-secondary); font-size: 0.875rem;">
                Total: <?= $totalUsers ?? 0 ?>
            </span>
        </div>
        <div class="admin-card-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Total Sessions</th>
                        <th>API Key Status</th>
                        <th>Webhook URL</th>
                        <th>Last Activity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                                No users found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><code><?= View::e($user['user_id']) ?></code></td>
                                <td><strong><?= View::e($user['username']) ?></strong></td>
                                <td><?= View::e($user['email']) ?></td>
                                <td>
                                    <span style="background: rgba(37, 211, 102, 0.1); padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                        <?= $user['total_sessions'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($user['api_key'])): ?>
                                        <span class="status-badge-admin status-active">Active</span>
                                    <?php else: ?>
                                        <span class="status-badge-admin status-inactive">Not Set</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($user['webhook_url'])): ?>
                                        <div class="webhook-url" title="<?= View::e($user['webhook_url']) ?>">
                                            <?= View::e($user['webhook_url']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: var(--text-secondary); font-style: italic;">Not configured</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($user['last_activity'])): ?>
                                        <?= date('M d, Y H:i', strtotime($user['last_activity'])) ?>
                                    <?php else: ?>
                                        <span style="color: var(--text-secondary);">Never</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if (!empty($users) && $totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?= $currentPage - 1 ?>" class="pagination-btn">← Previous</a>
                    <?php else: ?>
                        <button class="pagination-btn" disabled>← Previous</button>
                    <?php endif; ?>
                    
                    <span class="pagination-info">
                        Page <?= $currentPage ?> of <?= $totalPages ?>
                    </span>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?= $currentPage + 1 ?>" class="pagination-btn">Next →</a>
                    <?php else: ?>
                        <button class="pagination-btn" disabled>Next →</button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php View::endSection(); ?>
