<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<style>
.whatsapp-admin-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card-admin {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

.stat-card-admin:hover {
    border-color: #25D366;
    transform: translateY(-2px);
}

.stat-value-admin {
    font-size: 2.5rem;
    font-weight: 700;
    color: #25D366;
    margin-bottom: 8px;
}

.stat-label-admin {
    font-size: 0.875rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

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
</style>

<div style="max-width: 1400px; margin: 0 auto;">
    <!-- Page Header -->
    <div style="margin-bottom: 30px;">
        <h1 style="font-size: 2rem; margin-bottom: 8px; display: flex; align-items: center; gap: 12px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="#25D366">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            WhatsApp API - Admin Overview
        </h1>
        <p style="color: var(--text-secondary); font-size: 0.95rem;">Monitor and manage WhatsApp API automation platform</p>
    </div>

    <!-- Statistics Grid -->
    <div class="whatsapp-admin-stats">
        <div class="stat-card-admin">
            <div class="stat-value-admin"><?= $stats['totalSessions'] ?></div>
            <div class="stat-label-admin">Total Sessions</div>
        </div>
        <div class="stat-card-admin">
            <div class="stat-value-admin"><?= $stats['activeSessions'] ?></div>
            <div class="stat-label-admin">Active Sessions</div>
        </div>
        <div class="stat-card-admin">
            <div class="stat-value-admin"><?= $stats['totalMessages'] ?></div>
            <div class="stat-label-admin">Total Messages</div>
        </div>
        <div class="stat-card-admin">
            <div class="stat-value-admin"><?= $stats['messagesToday'] ?></div>
            <div class="stat-label-admin">Messages Today</div>
        </div>
        <div class="stat-card-admin">
            <div class="stat-value-admin"><?= $stats['totalUsers'] ?></div>
            <div class="stat-label-admin">Total Users</div>
        </div>
        <div class="stat-card-admin">
            <div class="stat-value-admin"><?= $stats['apiCallsToday'] ?></div>
            <div class="stat-label-admin">API Calls Today</div>
        </div>
    </div>

    <!-- Quick Links -->
    <div style="display: flex; gap: 16px; margin-bottom: 30px; flex-wrap: wrap;">
        <a href="/admin/whatsapp/sessions" style="background: #25D366; color: white; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
            Manage Sessions
        </a>
        <a href="/admin/whatsapp/messages" style="background: #0088cc; color: white; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            View Messages
        </a>
        <a href="/admin/whatsapp/users" style="background: #9945ff; color: white; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
            </svg>
            Manage Users
        </a>
        <a href="/admin/whatsapp/api-logs" style="background: #ff6b6b; color: white; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            API Logs
        </a>
    </div>

    <!-- Recent Sessions -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
                Recent Sessions
            </h3>
            <a href="/admin/whatsapp/sessions" style="color: #25D366; font-size: 0.875rem; text-decoration: none;">View All →</a>
        </div>
        <div class="admin-card-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Session Name</th>
                        <th>User</th>
                        <th>Phone Number</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentSessions)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                                No sessions yet
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentSessions as $session): ?>
                            <tr>
                                <td><strong><?= View::e($session['session_name']) ?></strong></td>
                                <td><?= View::e($session['username']) ?></td>
                                <td><?= View::e($session['phone_number'] ?? 'Not connected') ?></td>
                                <td>
                                    <span class="status-badge-admin status-<?= $session['status'] ?>">
                                        <?= ucfirst($session['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y H:i', strtotime($session['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Messages -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                Recent Messages
            </h3>
            <a href="/admin/whatsapp/messages" style="color: #25D366; font-size: 0.875rem; text-decoration: none;">View All →</a>
        </div>
        <div class="admin-card-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Session</th>
                        <th>Recipient</th>
                        <th>Message</th>
                        <th>Direction</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentMessages)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                                No messages yet
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentMessages as $message): ?>
                            <tr>
                                <td><?= View::e($message['username']) ?></td>
                                <td><?= View::e($message['session_name']) ?></td>
                                <td><?= View::e($message['recipient']) ?></td>
                                <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= View::e(substr($message['message'], 0, 50)) ?><?= strlen($message['message']) > 50 ? '...' : '' ?>
                                </td>
                                <td>
                                    <span style="color: <?= $message['direction'] === 'outgoing' ? '#25D366' : '#0088cc' ?>">
                                        <?= ucfirst($message['direction']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, H:i', strtotime($message['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php View::endSection(); ?>
