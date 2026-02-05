<?php use Core\View; use Core\Helpers; use Core\Auth; $currentUser = Auth::user(); ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<!-- Flash Messages -->
<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success animate-fade-in mb-lg">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span><?= View::e(Helpers::getFlash('success')) ?></span>
    </div>
<?php endif; ?>

<!-- Dashboard Stats Row -->
<div class="dashboard-stats-grid animate-fade-in">
    <div class="stat-widget">
        <div class="stat-icon" style="background: rgba(0, 217, 255, 0.1);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value" data-target="<?= count($projects ?? []) ?>">0</div>
            <div class="stat-label">Total Applications</div>
        </div>
        <div class="stat-trend positive">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
            </svg>
            <span>Active</span>
        </div>
    </div>

    <div class="stat-widget" style="animation-delay: 0.1s;">
        <div class="stat-icon" style="background: rgba(0, 102, 255, 0.1);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value" data-target="0">0</div>
            <div class="stat-label">Recent Activity</div>
        </div>
        <div class="stat-trend neutral">
            <span>Today</span>
        </div>
    </div>

    <div class="stat-widget" style="animation-delay: 0.2s;">
        <div class="stat-icon" style="background: rgba(0, 255, 136, 0.1);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value">1</div>
            <div class="stat-label">Active Sessions</div>
        </div>
        <div class="stat-trend positive">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            <span>Online</span>
        </div>
    </div>

    <div class="stat-widget" style="animation-delay: 0.3s;">
        <div class="stat-icon" style="background: rgba(153, 69, 255, 0.1);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value">-</div>
            <div class="stat-label">Storage Used</div>
        </div>
        <div class="stat-trend neutral">
            <span>N/A</span>
        </div>
    </div>
</div>

<!-- Main Dashboard Grid -->
<div class="dashboard-grid">
    <!-- Left Column: Main Content -->
    <div class="dashboard-main">
        <!-- Quick Actions Widget -->
        <div class="widget animate-fade-in" style="animation-delay: 0.1s;">
            <div class="widget-header">
                <h3 class="widget-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    Quick Actions
                </h3>
            </div>
            <div class="widget-body">
                <div class="quick-actions">
                    <a href="/profile" class="quick-action-item">
                        <div class="quick-action-icon" style="background: rgba(0, 217, 255, 0.1);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <div class="quick-action-content">
                            <div class="quick-action-title">Edit Profile</div>
                            <div class="quick-action-desc">Update your information</div>
                        </div>
                    </a>
                    <a href="/security" class="quick-action-item">
                        <div class="quick-action-icon" style="background: rgba(0, 255, 136, 0.1);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <div class="quick-action-content">
                            <div class="quick-action-title">Security Settings</div>
                            <div class="quick-action-desc">Manage your security</div>
                        </div>
                    </a>
                    <a href="/activity" class="quick-action-item">
                        <div class="quick-action-icon" style="background: rgba(0, 102, 255, 0.1);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                            </svg>
                        </div>
                        <div class="quick-action-content">
                            <div class="quick-action-title">Activity Log</div>
                            <div class="quick-action-desc">View your activity</div>
                        </div>
                    </a>
                    <a href="/settings" class="quick-action-item">
                        <div class="quick-action-icon" style="background: rgba(153, 69, 255, 0.1);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2">
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M12 1v6m0 6v6M5.64 5.64l4.24 4.24m4.24 4.24l4.24 4.24M1 12h6m6 0h6M5.64 18.36l4.24-4.24m4.24-4.24l4.24-4.24"/>
                            </svg>
                        </div>
                        <div class="quick-action-content">
                            <div class="quick-action-title">Settings</div>
                            <div class="quick-action-desc">Configure preferences</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Applications Widget -->
        <div class="widget animate-fade-in" style="animation-delay: 0.2s;">
            <div class="widget-header">
                <h3 class="widget-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    Your Applications
                </h3>
                <a href="/browse" class="widget-action">View All</a>
            </div>
            <div class="widget-body">
                <?php if (empty($projects)): ?>
                    <div class="empty-state">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--text-tertiary)" stroke-width="1.5">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                        </svg>
                        <p class="text-secondary">No applications available</p>
                    </div>
                <?php else: ?>
                    <div class="apps-grid">
                        <?php foreach ($projects as $key => $project): ?>
                            <a href="<?= $project['url'] ?>" class="app-card">
                                <div class="app-icon" style="background: <?= $project['color'] ?>20;">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="<?= $project['color'] ?>" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                                    </svg>
                                </div>
                                <div class="app-info">
                                    <div class="app-name"><?= View::e($project['name']) ?></div>
                                    <div class="app-desc"><?= View::e($project['description']) ?></div>
                                </div>
                                <div class="app-action">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M5 12h14m-7-7l7 7-7 7"/>
                                    </svg>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Sidebar Widgets -->
    <div class="dashboard-sidebar">
        <!-- Account Status Widget -->
        <div class="widget animate-fade-in" style="animation-delay: 0.3s;">
            <div class="widget-header">
                <h3 class="widget-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    Account Status
                </h3>
            </div>
            <div class="widget-body">
                <div class="account-info">
                    <div class="account-avatar">
                        <div style="width: 64px; height: 64px; background: linear-gradient(135deg, var(--cyan), var(--blue)); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; font-size: var(--font-size-2xl); font-weight: var(--font-bold); color: white;">
                            <?= strtoupper(substr($currentUser['username'], 0, 1)) ?>
                        </div>
                    </div>
                    <div class="account-details">
                        <div class="account-name"><?= View::e($currentUser['username']) ?></div>
                        <div class="account-email"><?= View::e($currentUser['email']) ?></div>
                        <div class="account-role">
                            <span class="badge badge-info"><?= View::e($currentUser['role'] ?? 'User') ?></span>
                        </div>
                    </div>
                </div>
                <div class="account-stats">
                    <div class="account-stat-item">
                        <span class="stat-label">Member Since</span>
                        <span class="stat-value"><?= date('M Y', strtotime($currentUser['created_at'] ?? 'now')) ?></span>
                    </div>
                    <div class="account-stat-item">
                        <span class="stat-label">Last Login</span>
                        <span class="stat-value">Just now</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tips Widget -->
        <div class="widget animate-fade-in" style="animation-delay: 0.4s;">
            <div class="widget-header">
                <h3 class="widget-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    Quick Tips
                </h3>
            </div>
            <div class="widget-body">
                <div class="tips-list">
                    <div class="tip-item">
                        <div class="tip-icon">💡</div>
                        <div class="tip-text">Enable 2FA for better security</div>
                    </div>
                    <div class="tip-item">
                        <div class="tip-icon">🚀</div>
                        <div class="tip-text">Complete your profile to unlock features</div>
                    </div>
                    <div class="tip-item">
                        <div class="tip-icon">📊</div>
                        <div class="tip-text">Check activity log regularly</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status Widget -->
        <div class="widget animate-fade-in" style="animation-delay: 0.5s;">
            <div class="widget-header">
                <h3 class="widget-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                    System Status
                </h3>
            </div>
            <div class="widget-body">
                <div class="status-list">
                    <div class="status-item">
                        <span class="status-indicator" style="background: var(--green);"></span>
                        <span class="status-label">All Systems Operational</span>
                    </div>
                    <div class="status-item">
                        <span class="status-indicator" style="background: var(--green);"></span>
                        <span class="status-label">API Services Online</span>
                    </div>
                    <div class="status-item">
                        <span class="status-indicator" style="background: var(--green);"></span>
                        <span class="status-label">Database Connected</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Dashboard Stats Grid */
.dashboard-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: var(--space-lg);
    margin-bottom: var(--space-xl);
}

.stat-widget {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--space-lg);
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
    transition: all var(--transition);
    animation: fadeIn 0.5s ease-out forwards;
    opacity: 0;
}

.stat-widget:hover {
    border-color: var(--border-color-strong);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: var(--font-size-3xl);
    font-weight: var(--font-bold);
    color: var(--text-primary);
    margin-bottom: var(--space-xs);
}

.stat-label {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    font-weight: var(--font-medium);
}

.stat-trend {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    font-size: var(--font-size-xs);
    font-weight: var(--font-medium);
}

.stat-trend.positive { color: var(--green); }
.stat-trend.negative { color: var(--red); }
.stat-trend.neutral { color: var(--text-tertiary); }

/* Dashboard Grid Layout */
.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: var(--space-xl);
    margin-bottom: var(--space-xl);
}

@media (max-width: 1200px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

/* Widget Styles */
.widget {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: all var(--transition);
    animation: fadeIn 0.5s ease-out forwards;
    opacity: 0;
    margin-bottom: var(--space-xl);
}

.widget:hover {
    border-color: var(--border-color-strong);
    box-shadow: var(--shadow-sm);
}

.widget-header {
    padding: var(--space-lg);
    border-bottom: 1px solid var(--divider-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.widget-title {
    font-size: var(--font-size-lg);
    font-weight: var(--font-semibold);
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: var(--space-sm);
}

.widget-action {
    font-size: var(--font-size-sm);
    color: var(--cyan);
    text-decoration: none;
    font-weight: var(--font-medium);
    transition: color var(--transition-fast);
}

.widget-action:hover {
    color: var(--blue);
}

.widget-body {
    padding: var(--space-lg);
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--space-md);
}

.quick-action-item {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    padding: var(--space-md);
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    text-decoration: none;
    transition: all var(--transition-fast);
}

.quick-action-item:hover {
    background: var(--bg-elevated);
    border-color: var(--cyan);
    transform: translateY(-2px);
    box-shadow: 0 0 0 1px var(--cyan);
}

.quick-action-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.quick-action-content {
    flex: 1;
    min-width: 0;
}

.quick-action-title {
    font-size: var(--font-size-sm);
    font-weight: var(--font-semibold);
    color: var(--text-primary);
    margin-bottom: var(--space-xs);
}

.quick-action-desc {
    font-size: var(--font-size-xs);
    color: var(--text-secondary);
}

/* Applications Grid */
.apps-grid {
    display: grid;
    gap: var(--space-sm);
}

.app-card {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    padding: var(--space-md);
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    text-decoration: none;
    transition: all var(--transition-fast);
}

.app-card:hover {
    background: var(--bg-elevated);
    border-color: var(--cyan);
    transform: translateX(4px);
}

.app-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.app-info {
    flex: 1;
    min-width: 0;
}

.app-name {
    font-size: var(--font-size-md);
    font-weight: var(--font-semibold);
    color: var(--text-primary);
    margin-bottom: var(--space-xs);
}

.app-desc {
    font-size: var(--font-size-xs);
    color: var(--text-secondary);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.app-action {
    color: var(--text-tertiary);
    transition: all var(--transition-fast);
}

.app-card:hover .app-action {
    color: var(--cyan);
    transform: translateX(2px);
}

/* Account Info */
.account-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    margin-bottom: var(--space-lg);
}

.account-avatar {
    margin-bottom: var(--space-md);
}

.account-details {
    width: 100%;
}

.account-name {
    font-size: var(--font-size-lg);
    font-weight: var(--font-bold);
    color: var(--text-primary);
    margin-bottom: var(--space-xs);
}

.account-email {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin-bottom: var(--space-sm);
    word-break: break-word;
}

.account-role {
    margin-top: var(--space-sm);
}

.account-stats {
    display: grid;
    gap: var(--space-md);
    padding-top: var(--space-lg);
    border-top: 1px solid var(--divider-color);
}

.account-stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.account-stat-item .stat-label {
    font-size: var(--font-size-xs);
    color: var(--text-secondary);
}

.account-stat-item .stat-value {
    font-size: var(--font-size-sm);
    font-weight: var(--font-semibold);
    color: var(--text-primary);
}

/* Tips List */
.tips-list {
    display: grid;
    gap: var(--space-md);
}

.tip-item {
    display: flex;
    align-items: flex-start;
    gap: var(--space-md);
    padding: var(--space-md);
    background: var(--bg-secondary);
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
    transition: all var(--transition-fast);
}

.tip-item:hover {
    background: var(--bg-elevated);
    border-color: var(--border-color-strong);
}

.tip-icon {
    font-size: var(--font-size-xl);
    flex-shrink: 0;
}

.tip-text {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    line-height: var(--leading-normal);
}

/* Status List */
.status-list {
    display: grid;
    gap: var(--space-md);
}

.status-item {
    display: flex;
    align-items: center;
    gap: var(--space-md);
}

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: var(--radius-full);
    flex-shrink: 0;
}

.status-label {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: var(--space-3xl);
}

.empty-state svg {
    margin: 0 auto var(--space-lg);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .dashboard-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
    
    .apps-grid {
        gap: var(--space-md);
    }
    
    .dashboard-sidebar {
        order: -1;
    }
}

@media (max-width: 480px) {
    .dashboard-stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Animated Counter for Stats
document.addEventListener('DOMContentLoaded', function() {
    const statValues = document.querySelectorAll('.stat-value[data-target]');
    
    statValues.forEach(stat => {
        const target = parseInt(stat.getAttribute('data-target'));
        const duration = 1000; // 1 second
        const step = target / (duration / 16); // 60fps
        let current = 0;
        
        const updateCounter = () => {
            current += step;
            if (current < target) {
                stat.textContent = Math.floor(current);
                requestAnimationFrame(updateCounter);
            } else {
                stat.textContent = target;
            }
        };
        
        // Start animation after a small delay
        setTimeout(updateCounter, 300);
    });
});
</script>
<?php View::endSection(); ?>
