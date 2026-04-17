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

.status-connected {
    background: rgba(37, 211, 102, 0.2);
    color: #25D366;
}

.status-disconnected {
    background: rgba(255, 107, 107, 0.2);
    color: #ff6b6b;
}

.status-initializing {
    background: rgba(255, 170, 0, 0.2);
    color: #ffaa00;
}

.btn-delete {
    background: #ff6b6b;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-delete:hover {
    background: #ff5252;
    transform: translateY(-1px);
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

<div style="max-width: 1400px; margin: 0 auto;">
    <!-- Page Header -->
    <div style="margin-bottom: 30px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="/admin/whatsapp" style="color: var(--text-secondary); text-decoration: none; font-size: 0.875rem;">← Back to Overview</a>
        </div>
        <h1 style="font-size: 2rem; margin-bottom: 8px; display: flex; align-items: center; gap: 12px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
            WhatsApp Sessions Management
        </h1>
        <p style="color: var(--text-secondary); font-size: 0.95rem;">View and manage all WhatsApp sessions from all users</p>
    </div>

    <!-- Sessions Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                All WhatsApp Sessions
            </h3>
            <span style="color: var(--text-secondary); font-size: 0.875rem;">
                Total: <?= $totalSessions ?? 0 ?>
            </span>
        </div>
        <div class="admin-card-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Session ID</th>
                        <th>Session Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sessions)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                                No sessions found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sessions as $session): ?>
                            <tr>
                                <td><code><?= View::e($session['id']) ?></code></td>
                                <td><strong><?= View::e($session['session_name']) ?></strong></td>
                                <td><?= View::e($session['username']) ?></td>
                                <td><?= View::e($session['email']) ?></td>
                                <td><?= View::e($session['phone_number'] ?? 'Not connected') ?></td>
                                <td>
                                    <span class="status-badge-admin status-<?= $session['status'] ?>">
                                        <?= ucfirst($session['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y H:i', strtotime($session['created_at'])) ?></td>
                                <td>
                                    <button class="btn-delete" onclick="deleteSession('<?= View::e($session['id']) ?>', '<?= View::e($session['session_name']) ?>')">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if (!empty($sessions) && $totalPages > 1): ?>
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

<script>
function deleteSession(sessionId, sessionName) {
    if (!confirm(`Are you sure you want to delete session "${sessionName}"?\n\nThis action cannot be undone.`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('session_id', sessionId);
    formData.append('csrf_token', '<?= Core\Security::generateCsrfToken() ?>');
    
    fetch('/admin/whatsapp/sessions/delete', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Check content type before parsing
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return response.text().then(text => {
                throw new Error('Server returned HTML instead of JSON: ' + text.substring(0, 100));
            });
        }
    })
    .then(data => {
        if (data.success) {
            alert('Session deleted successfully');
            window.location.reload();
        } else {
            alert('Failed to delete session: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        alert('Error deleting session: ' + error.message);
    });
}
</script>

<?php View::endSection(); ?>
