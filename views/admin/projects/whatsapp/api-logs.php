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
            <a href="/admin/whatsapp" style="color: var(--text-secondary); text-decoration: none; font-size: 0.875rem;">&laquo; Back to Overview</a>
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

    <!-- User filter + top users sidebar layout -->
    <div style="display:grid;grid-template-columns:1fr 280px;gap:20px;align-items:start;">
    <div>

    <!-- Active user filter banner -->
    <?php if ($filterUser): ?>
    <div style="margin-bottom:16px;padding:10px 16px;border-radius:8px;background:rgba(37,211,102,.08);border:1px solid #25D366;display:flex;align-items:center;justify-content:space-between;font-size:.86rem;">
        <span>Showing logs for <strong><?= htmlspecialchars($filterUser['name'], ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars($filterUser['email'], ENT_QUOTES, 'UTF-8') ?>)</span>
        <a href="/admin/whatsapp/api-logs" style="color:#ff6b6b;text-decoration:none;font-weight:600;">Clear filter</a>
    </div>
    <?php endif; ?>

    <!-- API Logs Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                API Request Logs
            </h3>
            <span style="color: var(--text-secondary); font-size: 0.875rem;">
                Total: <?= number_format($totalLogs ?? 0) ?>
            </span>
        </div>
        <div class="admin-card-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <th>User</th>
                        <th>API Key</th>
                        <th>Endpoint</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Latency</th>
                        <th>IP Address</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                                No API logs found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><code><?= htmlspecialchars((string) $log['id'], ENT_QUOTES, 'UTF-8') ?></code></td>
                                <td>
                                    <a href="/admin/whatsapp/api-logs?user_id=<?= (int) $log['user_id'] ?>" style="color:var(--text-primary);text-decoration:none;" title="Filter by this user">
                                        <?= htmlspecialchars($log['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                    <div style="font-size:.75rem;color:var(--text-secondary);"><?= htmlspecialchars($log['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($log['api_key_prefix'])): ?>
                                        <code style="font-size:.75rem;"><?= htmlspecialchars($log['api_key_prefix'], ENT_QUOTES, 'UTF-8') ?>••••</code>
                                    <?php else: ?>
                                        <span style="color:var(--text-secondary);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code style="font-size: 0.8rem;"><?= htmlspecialchars($log['endpoint'] ?? '', ENT_QUOTES, 'UTF-8') ?></code>
                                </td>
                                <td>
                                    <span class="method-badge method-<?= strtolower($log['method'] ?? 'post') ?>">
                                        <?= strtoupper(htmlspecialchars($log['method'] ?? 'POST', ENT_QUOTES, 'UTF-8')) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $sc = (int) ($log['status_code'] ?? 0);
                                    $scClass = $sc >= 500 ? 'status-5xx' : ($sc >= 400 ? 'status-4xx' : ($sc >= 300 ? 'status-3xx' : 'status-2xx'));
                                    ?>
                                    <?php if ($sc > 0): ?>
                                        <span class="status-code-badge <?= $scClass ?>"><?= $sc ?></span>
                                    <?php else: ?>
                                        <span style="color:var(--text-secondary);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($log['response_time']) && $log['response_time'] > 0): ?>
                                        <span style="font-size:.8rem;"><?= (int)$log['response_time'] ?>ms</span>
                                    <?php else: ?>
                                        <span style="color:var(--text-secondary);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code style="font-size: 0.8rem;"><?= htmlspecialchars($log['ip_address'] ?? '', ENT_QUOTES, 'UTF-8') ?></code>
                                </td>
                                <td><?= isset($log['created_at']) ? date('M d, Y H:i', strtotime($log['created_at'])) : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if (!empty($logs) && ($totalPages ?? 1) > 1): ?>
                <div class="pagination">
                    <?php $pageQs = $filterUserId ? '&user_id=' . $filterUserId : ''; ?>
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?= $currentPage - 1 . $pageQs ?>" class="pagination-btn">&laquo; Previous</a>
                    <?php else: ?>
                        <button class="pagination-btn" disabled>&laquo; Previous</button>
                    <?php endif; ?>
                    
                    <span class="pagination-info">
                        Page <?= $currentPage ?> of <?= $totalPages ?>
                    </span>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?= $currentPage + 1 . $pageQs ?>" class="pagination-btn">Next &raquo;</a>
                    <?php else: ?>
                        <button class="pagination-btn" disabled>Next &raquo;</button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    </div><!-- /main column -->

    <!-- Sidebar: Top users by API usage -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title" style="font-size:.9rem;">Top Users by API Usage</h3>
            </div>
            <div class="admin-card-body" style="padding:0;">
                <?php if (empty($topUsers)): ?>
                    <p style="padding:16px;color:var(--text-secondary);font-size:.84rem;">No data available.</p>
                <?php else: ?>
                <?php $maxCount = max(array_column($topUsers, 'total')) ?: 1; ?>
                <div style="padding:8px 0;">
                    <?php foreach ($topUsers as $tu): ?>
                    <a href="/admin/whatsapp/api-logs?user_id=<?= (int) $tu['id'] ?>" style="display:block;padding:8px 16px;text-decoration:none;color:var(--text-primary);transition:.15s;<?= ($filterUserId === (int) $tu['id']) ? 'background:rgba(37,211,102,.08);' : '' ?>">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:3px;">
                            <span style="font-size:.82rem;font-weight:600;"><?= htmlspecialchars($tu['name'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span style="font-size:.78rem;color:#25D366;font-weight:700;"><?= number_format((int) $tu['total']) ?></span>
                        </div>
                        <div style="background:var(--border-color);height:3px;border-radius:2px;overflow:hidden;">
                            <div style="background:#25D366;height:100%;width:<?= round(($tu['total'] / $maxCount) * 100) ?>%;"></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    </div><!-- /grid layout -->
</div>

<?php View::endSection(); ?>
