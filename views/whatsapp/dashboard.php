<?php use Core\View; use Core\Helpers; use Core\Auth; $currentUser = Auth::user(); ?>
<?php View::extend('whatsapp:app'); ?>

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
            <i class="fab fa-whatsapp" style="color: #25D366; font-size: 2rem;"></i>
            WhatsApp API Automation
        </h1>
        <p style="color: var(--text-secondary); font-size: 0.95rem;">Manage your WhatsApp sessions and automation</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock" style="color: #25D366; font-size: 24px;"></i>
            </div>
            <div class="stat-value"><?= $stats['totalSessions'] ?? 0 ?></div>
            <div class="stat-label">Total Sessions</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-signal" style="color: #25D366; font-size: 24px;"></i>
            </div>
            <div class="stat-value"><?= $stats['activeSessions'] ?? 0 ?></div>
            <div class="stat-label">Active Sessions</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-comment-dots" style="color: #25D366; font-size: 24px;"></i>
            </div>
            <div class="stat-value"><?= $stats['messagesToday'] ?? 0 ?></div>
            <div class="stat-label">Messages Today</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line" style="color: #25D366; font-size: 24px;"></i>
            </div>
            <div class="stat-value"><?= $stats['apiCallsToday'] ?? 0 ?></div>
            <div class="stat-label">API Calls Today</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="whatsapp-card">
        <div class="whatsapp-card-header">
            <h3 class="whatsapp-card-title">
                <i class="fas fa-bolt" style="color: #25D366;"></i>
                Quick Actions
            </h3>
        </div>
        <div class="whatsapp-card-body">
            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                <a href="/projects/whatsapp/sessions" class="btn-whatsapp">
                    <i class="fas fa-plus"></i>
                    New Session
                </a>
                <a href="/projects/whatsapp/messages" class="btn-whatsapp" style="background: #0088cc;">
                    <i class="fas fa-paper-plane"></i>
                    Send Message
                </a>
                <a href="/projects/whatsapp/subscription" class="btn-whatsapp" style="background: #ffaa00;">
                    <i class="fas fa-crown"></i>
                    Subscription
                </a>
                <a href="/projects/whatsapp/api-docs" class="btn-whatsapp" style="background: #9945ff;">
                    <i class="fas fa-book"></i>
                    API Docs
                </a>
                <a href="/projects/whatsapp/settings" class="btn-whatsapp" style="background: #ff6b6b;">
                    <i class="fas fa-cog"></i>
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
                    <i class="fas fa-mobile-alt" style="color: #25D366;"></i>
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
                    <i class="fas fa-comments" style="color: #25D366;"></i>
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
