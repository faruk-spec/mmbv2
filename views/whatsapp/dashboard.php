<?php use Core\View; use Core\Helpers; use Core\Auth; $currentUser = Auth::user(); ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<style>
.whatsapp-dashboard {
    max-width: 1400px;
    margin: 0 auto;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    transition: transform 0.3s ease, border-color 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    border-color: #25D366;
}

.stat-icon {
    width: 48px;
    height: 48px;
    background: rgba(37, 211, 102, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #25D366;
    margin-bottom: 8px;
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.whatsapp-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    margin-bottom: 24px;
    overflow: hidden;
}

.whatsapp-card-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.whatsapp-card-title {
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.whatsapp-card-body {
    padding: 24px;
}

.btn-whatsapp {
    background: #25D366;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.btn-whatsapp:hover {
    background: #20BA58;
    transform: translateY(-1px);
    color: white;
}

.session-list {
    display: grid;
    gap: 16px;
}

.session-item {
    background: rgba(37, 211, 102, 0.05);
    border: 1px solid rgba(37, 211, 102, 0.2);
    border-radius: 10px;
    padding: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.session-info h4 {
    margin: 0 0 8px 0;
    font-size: 1rem;
    color: var(--text-primary);
}

.session-info p {
    margin: 0;
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
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

.recent-messages {
    max-height: 400px;
    overflow-y: auto;
}

.message-item {
    padding: 12px;
    border-bottom: 1px solid var(--border-color);
    transition: background 0.2s ease;
}

.message-item:hover {
    background: rgba(37, 211, 102, 0.05);
}

.message-item:last-child {
    border-bottom: none;
}

.message-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 6px;
}

.message-sender {
    font-weight: 600;
    color: #25D366;
    font-size: 0.9rem;
}

.message-time {
    font-size: 0.75rem;
    color: var(--text-secondary);
}

.message-text {
    font-size: 0.875rem;
    color: var(--text-primary);
}
</style>

<div class="whatsapp-dashboard">
    <!-- Page Header -->
    <div style="margin-bottom: 30px;">
        <h1 style="font-size: 2rem; margin-bottom: 8px; display: flex; align-items: center; gap: 12px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="#25D366">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            WhatsApp API Automation
        </h1>
        <p style="color: var(--text-secondary); font-size: 0.95rem;">Manage your WhatsApp sessions and automation</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
            </div>
            <div class="stat-value"><?= $stats['totalSessions'] ?? 0 ?></div>
            <div class="stat-label">Total Sessions</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
            </div>
            <div class="stat-value"><?= $stats['activeSessions'] ?? 0 ?></div>
            <div class="stat-label">Active Sessions</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <div class="stat-value"><?= $stats['messagesToday'] ?? 0 ?></div>
            <div class="stat-label">Messages Today</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
            </div>
            <div class="stat-value"><?= $stats['apiCallsToday'] ?? 0 ?></div>
            <div class="stat-label">API Calls Today</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="whatsapp-card">
        <div class="whatsapp-card-header">
            <h3 class="whatsapp-card-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                Quick Actions
            </h3>
        </div>
        <div class="whatsapp-card-body">
            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                <a href="/projects/whatsapp/sessions" class="btn-whatsapp">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    New Session
                </a>
                <a href="/projects/whatsapp/messages" class="btn-whatsapp" style="background: #0088cc;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    Send Message
                </a>
                <a href="/projects/whatsapp/api-docs" class="btn-whatsapp" style="background: #9945ff;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    API Docs
                </a>
                <a href="/projects/whatsapp/settings" class="btn-whatsapp" style="background: #ff6b6b;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M12 1v6m0 6v6m-9-9h6m6 0h6"/>
                    </svg>
                    Settings
                </a>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <!-- Active Sessions -->
        <div class="whatsapp-card">
            <div class="whatsapp-card-header">
                <h3 class="whatsapp-card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                    Active Sessions
                </h3>
                <a href="/projects/whatsapp/sessions" style="color: #25D366; font-size: 0.875rem; text-decoration: none;">View All →</a>
            </div>
            <div class="whatsapp-card-body">
                <?php if (empty($sessions)): ?>
                    <p style="text-align: center; color: var(--text-secondary); padding: 40px 20px;">
                        No active sessions. <a href="/projects/whatsapp/sessions" style="color: #25D366;">Create your first session</a>
                    </p>
                <?php else: ?>
                    <div class="session-list">
                        <?php foreach (array_slice($sessions, 0, 3) as $session): ?>
                            <div class="session-item">
                                <div class="session-info">
                                    <h4><?= View::e($session['session_name']) ?></h4>
                                    <p><?= View::e($session['phone_number'] ?? 'Not connected') ?></p>
                                </div>
                                <span class="status-badge status-<?= $session['status'] ?>">
                                    <?= ucfirst($session['status']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Messages -->
        <div class="whatsapp-card">
            <div class="whatsapp-card-header">
                <h3 class="whatsapp-card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    Recent Messages
                </h3>
                <a href="/projects/whatsapp/messages" style="color: #25D366; font-size: 0.875rem; text-decoration: none;">View All →</a>
            </div>
            <div class="whatsapp-card-body">
                <?php if (empty($recentMessages)): ?>
                    <p style="text-align: center; color: var(--text-secondary); padding: 40px 20px;">
                        No messages yet
                    </p>
                <?php else: ?>
                    <div class="recent-messages">
                        <?php foreach ($recentMessages as $message): ?>
                            <div class="message-item">
                                <div class="message-header">
                                    <span class="message-sender"><?= View::e($message['recipient']) ?></span>
                                    <span class="message-time"><?= date('M d, H:i', strtotime($message['created_at'])) ?></span>
                                </div>
                                <div class="message-text"><?= View::e(substr($message['message'], 0, 100)) ?><?= strlen($message['message']) > 100 ? '...' : '' ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php View::endSection(); ?>
