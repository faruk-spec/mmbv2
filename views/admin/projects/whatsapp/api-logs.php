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

.status-code-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    font-family: monospace;
}

.status-2xx {
    background: rgba(37, 211, 102, 0.2);
    color: #25D366;
}

.status-3xx {
    background: rgba(0, 136, 204, 0.2);
    color: #0088cc;
}

.status-4xx {
    background: rgba(255, 170, 0, 0.2);
    color: #ffaa00;
}

.status-5xx {
    background: rgba(255, 107, 107, 0.2);
    color: #ff6b6b;
}

.method-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    font-family: monospace;
}

.method-get {
    background: rgba(0, 136, 204, 0.2);
    color: #0088cc;
}

.method-post {
    background: rgba(37, 211, 102, 0.2);
    color: #25D366;
}

.method-put {
    background: rgba(255, 170, 0, 0.2);
    color: #ffaa00;
}

.method-delete {
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

.response-time {
    font-family: monospace;
    font-weight: 600;
}

.response-time-fast {
    color: #25D366;
}

.response-time-medium {
    color: #ffaa00;
}

.response-time-slow {
    color: #ff6b6b;
}
</style>

<div style="max-width: 1600px; margin: 0 auto;">
    <!-- Page Header -->
    <div style="margin-bottom: 30px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="/admin/whatsapp" style="color: var(--text-secondary); text-decoration: none; font-size: 0.875rem;">← Back to Overview</a>
        </div>
        <h1 style="font-size: 2rem; margin-bottom: 8px; display: flex; align-items: center; gap: 12px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#ff6b6b" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
            </svg>
            WhatsApp API Logs
        </h1>
        <p style="color: var(--text-secondary); font-size: 0.95rem;">Monitor API request logs and performance metrics</p>
    </div>

    <!-- API Logs Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                API Request Logs
            </h3>
            <span style="color: var(--text-secondary); font-size: 0.875rem;">
                Total: <?= $totalLogs ?? 0 ?>
            </span>
        </div>
        <div class="admin-card-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <th>Username</th>
                        <th>Endpoint</th>
                        <th>Method</th>
                        <th>Status Code</th>
                        <th>Response Time</th>
                        <th>IP Address</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                                No API logs found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <?php
                                $statusCode = $log['status_code'];
                                $statusClass = 'status-2xx';
                                if ($statusCode >= 500) {
                                    $statusClass = 'status-5xx';
                                } elseif ($statusCode >= 400) {
                                    $statusClass = 'status-4xx';
                                } elseif ($statusCode >= 300) {
                                    $statusClass = 'status-3xx';
                                }
                                
                                $responseTime = $log['response_time'];
                                $timeClass = 'response-time-fast';
                                if ($responseTime > 1000) {
                                    $timeClass = 'response-time-slow';
                                } elseif ($responseTime > 500) {
                                    $timeClass = 'response-time-medium';
                                }
                            ?>
                            <tr>
                                <td><code><?= View::e($log['id']) ?></code></td>
                                <td><?= View::e($log['username']) ?></td>
                                <td>
                                    <code style="font-size: 0.8rem;"><?= View::e($log['endpoint']) ?></code>
                                </td>
                                <td>
                                    <span class="method-badge method-<?= strtolower($log['method']) ?>">
                                        <?= strtoupper(View::e($log['method'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-code-badge <?= $statusClass ?>">
                                        <?= $statusCode ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="response-time <?= $timeClass ?>">
                                        <?= number_format($responseTime, 0) ?>ms
                                    </span>
                                </td>
                                <td>
                                    <code style="font-size: 0.8rem;"><?= View::e($log['ip_address']) ?></code>
                                </td>
                                <td><?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if (!empty($logs) && $totalPages > 1): ?>
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
