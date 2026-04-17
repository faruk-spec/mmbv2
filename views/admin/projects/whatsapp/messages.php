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

.status-sent {
    background: rgba(37, 211, 102, 0.2);
    color: #25D366;
}

.status-failed {
    background: rgba(255, 107, 107, 0.2);
    color: #ff6b6b;
}

.status-pending {
    background: rgba(255, 170, 0, 0.2);
    color: #ffaa00;
}

.status-delivered {
    background: rgba(0, 136, 204, 0.2);
    color: #0088cc;
}

.direction-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.direction-outgoing {
    background: rgba(37, 211, 102, 0.2);
    color: #25D366;
}

.direction-incoming {
    background: rgba(0, 136, 204, 0.2);
    color: #0088cc;
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
</style>

<div style="max-width: 1600px; margin: 0 auto;">
    <!-- Page Header -->
    <div style="margin-bottom: 30px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="/admin/whatsapp" style="color: var(--text-secondary); text-decoration: none; font-size: 0.875rem;">← Back to Overview</a>
        </div>
        <h1 style="font-size: 2rem; margin-bottom: 8px; display: flex; align-items: center; gap: 12px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            WhatsApp Messages
        </h1>
        <p style="color: var(--text-secondary); font-size: 0.95rem;">View all WhatsApp messages from all users</p>
    </div>

    <!-- Messages Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                All WhatsApp Messages
            </h3>
            <span style="color: var(--text-secondary); font-size: 0.875rem;">
                Total: <?= $totalMessages ?? 0 ?>
            </span>
        </div>
        <div class="admin-card-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Message ID</th>
                        <th>Username</th>
                        <th>Session Name</th>
                        <th>Recipient</th>
                        <th>Message</th>
                        <th>Direction</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                                No messages found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                            <tr>
                                <td><code><?= View::e($message['id']) ?></code></td>
                                <td><?= View::e($message['username']) ?></td>
                                <td><strong><?= View::e($message['session_name']) ?></strong></td>
                                <td><?= View::e($message['recipient']) ?></td>
                                <td style="max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= View::e($message['message']) ?>">
                                    <?= View::e(substr($message['message'], 0, 80)) ?><?= strlen($message['message']) > 80 ? '...' : '' ?>
                                </td>
                                <td>
                                    <span class="direction-badge direction-<?= $message['direction'] ?>">
                                        <?= ucfirst($message['direction']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge-admin status-<?= $message['status'] ?>">
                                        <?= ucfirst($message['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y H:i:s', strtotime($message['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if (!empty($messages) && $totalPages > 1): ?>
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
