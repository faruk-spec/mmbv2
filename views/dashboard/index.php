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

<!-- Enhanced Welcome Banner -->
<div class="dashboard-welcome animate-fade-in">
    <div class="welcome-content">
        <div class="welcome-text">
            <h1 class="welcome-title">
                <?php 
                $hour = date('H');
                $greeting = $hour < 12 ? 'Good Morning' : ($hour < 18 ? 'Good Afternoon' : 'Good Evening');
                echo $greeting . ', ' . View::e($currentUser['username']);
                ?>
                <span class="wave">👋</span>
            </h1>
            <p class="welcome-subtitle">
                Here's your dashboard overview. You have <strong><?= count($projects ?? []) ?> applications</strong> ready to use.
            </p>
        </div>
        <div class="welcome-avatar">
            <div class="avatar-circle">
                <span><?= strtoupper(substr($currentUser['username'], 0, 2)) ?></span>
            </div>
            <div class="status-badge" title="Online">
                <span class="pulse"></span>
            </div>
        </div>
    </div>
    <div class="welcome-stats-mini">
        <div class="mini-stat">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            <span>Last login: Just now</span>
        </div>
        <div class="mini-stat">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <span>Account secure</span>
        </div>
        <div class="mini-stat">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            <span><?= ucfirst($currentUser['role'] ?? 'User') ?></span>
        </div>
    </div>
</div>

<!-- Dashboard Stats Row - Enhanced -->
<div class="dashboard-stats-grid animate-fade-in">
    <div class="stat-widget stat-gradient-cyan">
        <div class="stat-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value" data-target="<?= count($projects ?? []) ?>">0</div>
            <div class="stat-label">Applications</div>
        </div>
        <div class="stat-chart">
            <div class="mini-bar" style="height: 60%"></div>
            <div class="mini-bar" style="height: 80%"></div>
            <div class="mini-bar" style="height: 100%"></div>
            <div class="mini-bar" style="height: 75%"></div>
        </div>
    </div>

    <div class="stat-widget stat-gradient-blue" style="animation-delay: 0.1s;">
        <div class="stat-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value" data-target="24">0</div>
            <div class="stat-label">Activities Today</div>
        </div>
        <div class="stat-trend positive">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
            </svg>
            <span>+12%</span>
        </div>
    </div>

    <div class="stat-widget stat-gradient-green" style="animation-delay: 0.2s;">
        <div class="stat-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value">98.5%</div>
            <div class="stat-label">Uptime</div>
        </div>
        <div class="stat-trend positive">
            <span>Excellent</span>
        </div>
    </div>

    <div class="stat-widget stat-gradient-purple" style="animation-delay: 0.3s;">
        <div class="stat-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value">2.4 GB</div>
            <div class="stat-label">Storage Used</div>
        </div>
        <div class="storage-progress">
            <div class="progress-bar">
                <div class="progress-fill" style="width: 24%"></div>
            </div>
            <span class="progress-text">24% of 10 GB</span>
        </div>
    </div>
</div>

<!-- Main Dashboard Grid - Three Columns -->
<div class="dashboard-grid-three">
    <!-- Left Column: Main Content (50%) -->
    <div class="dashboard-main">
        <!-- Applications Widget - Enhanced -->
        <div class="widget animate-fade-in" style="animation-delay: 0.1s;">
            <div class="widget-header">
                <h3 class="widget-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    Your Applications
                </h3>
                <a href="/browse" class="widget-action">
                    View All
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14m-7-7l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <div class="widget-body">
                <?php if (empty($projects)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--text-tertiary)" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                            </svg>
                        </div>
                        <h4>No Applications Yet</h4>
                        <p class="text-secondary">Get started by browsing available applications</p>
                        <a href="/browse" class="btn btn-primary btn-sm">Browse Apps</a>
                    </div>
                <?php else: ?>
                    <div class="apps-grid-enhanced">
                        <?php foreach ($projects as $key => $project): ?>
                            <div class="app-card-enhanced">
                                <div class="app-card-header" style="background: linear-gradient(135deg, <?= $project['color'] ?>20, <?= $project['color'] ?>05);">
                                    <div class="app-icon-large" style="background: <?= $project['color'] ?>; box-shadow: 0 4px 12px <?= $project['color'] ?>40;">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                                        </svg>
                                    </div>
                                    <button class="favorite-btn" data-app="<?= $key ?>">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="app-card-body">
                                    <h4 class="app-name"><?= View::e($project['name']) ?></h4>
                                    <p class="app-desc"><?= View::e($project['description']) ?></p>
                                    <div class="app-meta">
                                        <span class="meta-item">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                            </svg>
                                            Last used: 2h ago
                                        </span>
                                    </div>
                                </div>
                                <div class="app-card-footer">
                                    <a href="<?= $project['url'] ?>" class="app-launch-btn" style="background: <?= $project['color'] ?>;">
                                        <span>Launch</span>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M5 12h14m-7-7l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Activity Timeline Widget - NEW -->
        <div class="widget animate-fade-in" style="animation-delay: 0.2s;">
            <div class="widget-header">
                <h3 class="widget-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    Recent Activity
                </h3>
                <a href="/activity" class="widget-action">View All</a>
            </div>
            <div class="widget-body">
                <div class="activity-timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker" style="background: var(--cyan);"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Logged in successfully</div>
                            <div class="timeline-time">Just now</div>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker" style="background: var(--green);"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Profile updated</div>
                            <div class="timeline-time">2 hours ago</div>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker" style="background: var(--blue);"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Application launched</div>
                            <div class="timeline-time">5 hours ago</div>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker" style="background: var(--purple);"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Security settings changed</div>
                            <div class="timeline-time">Yesterday</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Middle Column: Quick Actions & Info (25%) -->
    <div class="dashboard-sidebar-left">
        <!-- Quick Actions Widget - Enhanced -->
        <div class="widget animate-fade-in" style="animation-delay: 0.3s;">
            <div class="widget-header">
                <h3 class="widget-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    Quick Actions
                </h3>
            </div>
            <div class="widget-body">
                <div class="quick-actions-vertical">
                    <a href="/profile" class="quick-action-card">
                        <div class="action-icon" style="background: linear-gradient(135deg, var(--cyan), var(--blue));">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <div class="action-content">
                            <div class="action-title">Edit Profile</div>
                            <div class="action-desc">Update info</div>
                        </div>
                    </a>
                    <a href="/security" class="quick-action-card">
                        <div class="action-icon" style="background: linear-gradient(135deg, var(--green), #00cc66);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                        </div>
                        <div class="action-content">
                            <div class="action-title">Security</div>
                            <div class="action-desc">Settings</div>
                        </div>
                    </a>
                    <a href="/activity" class="quick-action-card">
                        <div class="action-icon" style="background: linear-gradient(135deg, var(--purple), #7733ff);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                            </svg>
                        </div>
                        <div class="action-content">
                            <div class="action-title">Activity Log</div>
                            <div class="action-desc">View history</div>
                        </div>
                    </a>
                    <a href="/settings" class="quick-action-card">
                        <div class="action-icon" style="background: linear-gradient(135deg, var(--orange), #ff9900);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M12 1v6m0 6v6M5.64 5.64l4.24 4.24m4.24 4.24l4.24 4.24M1 12h6m6 0h6M5.64 18.36l4.24-4.24m4.24-4.24l4.24-4.24"/>
                            </svg>
                        </div>
                        <div class="action-content">
                            <div class="action-title">Settings</div>
                            <div class="action-desc">Preferences</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Notifications Widget - NEW -->
        <div class="widget animate-fade-in" style="animation-delay: 0.4s;">
            <div class="widget-header">
                <h3 class="widget-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    Notifications
                    <span class="notification-badge">3</span>
                </h3>
            </div>
            <div class="widget-body">
                <div class="notifications-list">
                    <div class="notification-item unread">
                        <div class="notification-icon" style="background: rgba(0, 217, 255, 0.1);">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                            </svg>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">System Update</div>
                            <div class="notification-text">New features available</div>
                            <div class="notification-time">5 min ago</div>
                        </div>
                    </div>
                    <div class="notification-item unread">
                        <div class="notification-icon" style="background: rgba(0, 255, 136, 0.1);">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">Backup Complete</div>
                            <div class="notification-text">All data secured</div>
                            <div class="notification-time">1 hour ago</div>
                        </div>
                    </div>
                    <div class="notification-item">
                        <div class="notification-icon" style="background: rgba(153, 69, 255, 0.1);">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">Security Alert</div>
                            <div class="notification-text">New login detected</div>
                            <div class="notification-time">2 hours ago</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: User Info & System (25%) -->
    <div class="dashboard-sidebar-right">
        <!-- Account Status Widget - Enhanced -->
        <div class="widget animate-fade-in" style="animation-delay: 0.5s;">
            <div class="widget-header">
                <h3 class="widget-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    Account
                </h3>
            </div>
            <div class="widget-body">
                <div class="account-info-enhanced">
                    <div class="account-avatar-large">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--cyan), var(--blue)); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: var(--font-bold); color: white; box-shadow: 0 8px 24px rgba(0, 217, 255, 0.3);">
                            <?= strtoupper(substr($currentUser['username'], 0, 1)) ?>
                        </div>
                    </div>
                    <div class="account-details-enhanced">
                        <div class="account-name-large"><?= View::e($currentUser['username']) ?></div>
                        <div class="account-email-enhanced"><?= View::e($currentUser['email']) ?></div>
                        <div class="account-role-enhanced">
                            <span class="badge badge-primary"><?= View::e(ucfirst($currentUser['role'] ?? 'User')) ?></span>
                        </div>
                    </div>
                </div>
                <div class="account-stats-enhanced">
                    <div class="stat-row">
                        <span class="stat-label">Member Since</span>
                        <span class="stat-value-right"><?= date('M Y', strtotime($currentUser['created_at'] ?? 'now')) ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Total Logins</span>
                        <span class="stat-value-right">156</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Last Active</span>
                        <span class="stat-value-right">Just now</span>
                    </div>
                </div>
                <a href="/profile" class="btn btn-secondary btn-sm" style="width: 100%; margin-top: var(--space-md);">
                    Manage Account
                </a>
            </div>
        </div>

        <!-- Quick Tips Widget - Enhanced -->
        <div class="widget animate-fade-in" style="animation-delay: 0.6s;">
            <div class="widget-header">
                <h3 class="widget-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    Tips & Help
                </h3>
            </div>
            <div class="widget-body">
                <div class="tips-list-enhanced">
                    <div class="tip-item-enhanced">
                        <div class="tip-icon-large">💡</div>
                        <div class="tip-content">
                            <div class="tip-title">Enable 2FA</div>
                            <div class="tip-text">Add extra security to your account</div>
                            <a href="/security" class="tip-link">Set up now →</a>
                        </div>
                    </div>
                    <div class="tip-item-enhanced">
                        <div class="tip-icon-large">🚀</div>
                        <div class="tip-content">
                            <div class="tip-title">Complete Profile</div>
                            <div class="tip-text">Unlock all features</div>
                            <a href="/profile" class="tip-link">Complete →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status Widget - Enhanced -->
        <div class="widget animate-fade-in" style="animation-delay: 0.7s;">
            <div class="widget-header">
                <h3 class="widget-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                    System Health
                </h3>
            </div>
            <div class="widget-body">
                <div class="system-health">
                    <div class="health-score">
                        <div class="health-circle">
                            <svg width="80" height="80" viewBox="0 0 80 80">
                                <circle cx="40" cy="40" r="35" fill="none" stroke="var(--bg-secondary)" stroke-width="8"/>
                                <circle cx="40" cy="40" r="35" fill="none" stroke="var(--green)" stroke-width="8" stroke-dasharray="220" stroke-dashoffset="22" stroke-linecap="round" transform="rotate(-90 40 40)"/>
                            </svg>
                            <div class="health-score-text">98%</div>
                        </div>
                        <div class="health-label">System Health</div>
                    </div>
                    <div class="status-list-enhanced">
                        <div class="status-item-enhanced">
                            <div class="status-indicator-large" style="background: var(--green);"></div>
                            <span>All Systems Operational</span>
                        </div>
                        <div class="status-item-enhanced">
                            <div class="status-indicator-large" style="background: var(--green);"></div>
                            <span>API Online</span>
                        </div>
                        <div class="status-item-enhanced">
                            <div class="status-indicator-large" style="background: var(--green);"></div>
                            <span>Database Connected</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Enhanced Welcome Banner */
.dashboard-welcome {
    background: linear-gradient(135deg, rgba(0, 217, 255, 0.08) 0%, rgba(0, 102, 255, 0.08) 100%);
    border: 1px solid rgba(0, 217, 255, 0.2);
    border-radius: var(--radius-xl);
    padding: var(--space-2xl);
    margin-bottom: var(--space-xl);
    animation: fadeIn 0.5s ease-out;
}

.welcome-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-lg);
}

.welcome-title {
    font-size: var(--font-size-3xl);
    font-weight: var(--font-bold);
    color: var(--text-primary);
    margin-bottom: var(--space-xs);
    display: flex;
    align-items: center;
    gap: var(--space-md);
}

.wave {
    display: inline-block;
    animation: wave 2s ease-in-out infinite;
}

@keyframes wave {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(20deg); }
    75% { transform: rotate(-20deg); }
}

.welcome-subtitle {
    font-size: var(--font-size-md);
    color: var(--text-secondary);
}

.welcome-avatar {
    position: relative;
}

.avatar-circle {
    width: 72px;
    height: 72px;
    background: linear-gradient(135deg, var(--cyan), var(--blue));
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--font-size-xl);
    font-weight: var(--font-bold);
    color: white;
    box-shadow: 0 8px 24px rgba(0, 217, 255, 0.3);
}

.status-badge {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 20px;
    height: 20px;
    background: var(--green);
    border: 3px solid var(--bg-primary);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
}

.pulse {
    width: 8px;
    height: 8px;
    background: white;
    border-radius: var(--radius-full);
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

.welcome-stats-mini {
    display: flex;
    gap: var(--space-xl);
    flex-wrap: wrap;
}

.mini-stat {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
}

/* Enhanced Stats Grid */
.dashboard-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: var(--space-lg);
    margin-bottom: var(--space-xl);
}

.stat-widget {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--space-xl);
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
    transition: all var(--transition);
    animation: fadeIn 0.5s ease-out forwards;
    opacity: 0;
    position: relative;
    overflow: hidden;
}

.stat-widget::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--cyan), var(--blue));
    opacity: 0;
    transition: opacity var(--transition);
}

.stat-widget:hover::before {
    opacity: 1;
}

.stat-widget:hover {
    border-color: var(--border-color-strong);
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.stat-gradient-cyan { border-top-color: var(--cyan); }
.stat-gradient-blue { border-top-color: var(--blue); }
.stat-gradient-green { border-top-color: var(--green); }
.stat-gradient-purple { border-top-color: var(--purple); }

.stat-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, rgba(0, 217, 255, 0.1), rgba(0, 102, 255, 0.1));
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--cyan);
}

.stat-value {
    font-size: var(--font-size-4xl);
    font-weight: var(--font-bold);
    color: var(--text-primary);
    line-height: 1;
}

.stat-label {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    font-weight: var(--font-medium);
}

.stat-chart {
    display: flex;
    align-items: flex-end;
    gap: 4px;
    height: 30px;
    margin-top: auto;
}

.mini-bar {
    flex: 1;
    background: linear-gradient(180deg, var(--cyan), var(--blue));
    border-radius: 2px;
    opacity: 0.6;
    transition: all var(--transition-fast);
}

.stat-widget:hover .mini-bar {
    opacity: 1;
}

.stat-trend {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    font-size: var(--font-size-xs);
    font-weight: var(--font-semibold);
    margin-top: auto;
}

.stat-trend.positive { color: var(--green); }

.storage-progress {
    margin-top: auto;
}

.progress-bar {
    width: 100%;
    height: 6px;
    background: var(--bg-secondary);
    border-radius: var(--radius-full);
    overflow: hidden;
    margin-bottom: var(--space-xs);
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--purple), #9933ff);
    border-radius: var(--radius-full);
    transition: width var(--transition);
}

.progress-text {
    font-size: var(--font-size-xs);
    color: var(--text-tertiary);
}

/* Three Column Grid Layout */
.dashboard-grid-three {
    display: grid;
    grid-template-columns: 1fr 320px 320px;
    gap: var(--space-xl);
    margin-bottom: var(--space-xl);
}

@media (max-width: 1400px) {
    .dashboard-grid-three {
        grid-template-columns: 1fr 280px;
    }
    .dashboard-sidebar-left {
        order: 2;
    }
    .dashboard-sidebar-right {
        display: none;
    }
}

@media (max-width: 1024px) {
    .dashboard-grid-three {
        grid-template-columns: 1fr;
    }
    .dashboard-sidebar-right {
        display: block;
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
    box-shadow: var(--shadow-md);
}

.widget-header {
    padding: var(--space-lg) var(--space-xl);
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
    transition: all var(--transition-fast);
    display: flex;
    align-items: center;
    gap: var(--space-xs);
}

.widget-action:hover {
    color: var(--blue);
    gap: var(--space-sm);
}

.widget-body {
    padding: var(--space-xl);
}

.notification-badge {
    background: var(--red);
    color: white;
    font-size: 10px;
    font-weight: var(--font-bold);
    padding: 2px 6px;
    border-radius: var(--radius-full);
    margin-left: var(--space-xs);
}

/* Enhanced Application Cards */
.apps-grid-enhanced {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--space-lg);
}

.app-card-enhanced {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: all var(--transition);
}

.app-card-enhanced:hover {
    border-color: var(--border-color-strong);
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.app-card-header {
    padding: var(--space-xl);
    position: relative;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.app-icon-large {
    width: 64px;
    height: 64px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
}

.favorite-btn {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    color: var(--text-secondary);
    width: 36px;
    height: 36px;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.favorite-btn:hover {
    background: var(--red);
    border-color: var(--red);
    color: white;
    transform: scale(1.1);
}

.app-card-body {
    padding: 0 var(--space-xl) var(--space-lg);
}

.app-name {
    font-size: var(--font-size-lg);
    font-weight: var(--font-semibold);
    color: var(--text-primary);
    margin-bottom: var(--space-xs);
}

.app-desc {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin-bottom: var(--space-md);
    line-height: var(--leading-relaxed);
}

.app-meta {
    display: flex;
    gap: var(--space-md);
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    font-size: var(--font-size-xs);
    color: var(--text-tertiary);
}

.app-card-footer {
    padding: var(--space-lg) var(--space-xl);
    border-top: 1px solid var(--divider-color);
}

.app-launch-btn {
    width: 100%;
    padding: var(--space-md);
    border: none;
    border-radius: var(--radius-md);
    color: white;
    font-weight: var(--font-semibold);
    font-size: var(--font-size-sm);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-sm);
    transition: all var(--transition-fast);
    text-decoration: none;
}

.app-launch-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
}

/* Activity Timeline */
.activity-timeline {
    display: flex;
    flex-direction: column;
    gap: var(--space-lg);
}

.timeline-item {
    display: flex;
    gap: var(--space-md);
    align-items: flex-start;
    position: relative;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 7px;
    top: 24px;
    bottom: -var(--space-lg);
    width: 2px;
    background: var(--divider-color);
}

.timeline-marker {
    width: 16px;
    height: 16px;
    border-radius: var(--radius-full);
    border: 3px solid var(--bg-card);
    flex-shrink: 0;
}

.timeline-content {
    flex: 1;
}

.timeline-title {
    font-size: var(--font-size-sm);
    font-weight: var(--font-medium);
    color: var(--text-primary);
    margin-bottom: var(--space-xs);
}

.timeline-time {
    font-size: var(--font-size-xs);
    color: var(--text-tertiary);
}

/* Quick Actions - Vertical */
.quick-actions-vertical {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
}

.quick-action-card {
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

.quick-action-card:hover {
    background: var(--bg-elevated);
    border-color: var(--border-color-strong);
    transform: translateX(4px);
}

.action-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.action-content {
    flex: 1;
}

.action-title {
    font-size: var(--font-size-sm);
    font-weight: var(--font-semibold);
    color: var(--text-primary);
    margin-bottom: 2px;
}

.action-desc {
    font-size: var(--font-size-xs);
    color: var(--text-secondary);
}

/* Notifications */
.notifications-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
}

.notification-item {
    display: flex;
    gap: var(--space-md);
    padding: var(--space-md);
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    transition: all var(--transition-fast);
}

.notification-item.unread {
    background: rgba(0, 217, 255, 0.05);
    border-color: rgba(0, 217, 255, 0.2);
}

.notification-item:hover {
    background: var(--bg-elevated);
    border-color: var(--border-color-strong);
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.notification-content {
    flex: 1;
}

.notification-title {
    font-size: var(--font-size-sm);
    font-weight: var(--font-semibold);
    color: var(--text-primary);
    margin-bottom: var(--space-xs);
}

.notification-text {
    font-size: var(--font-size-xs);
    color: var(--text-secondary);
    margin-bottom: var(--space-xs);
}

.notification-time {
    font-size: var(--font-size-xs);
    color: var(--text-tertiary);
}

/* Account Info - Enhanced */
.account-info-enhanced {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    margin-bottom: var(--space-xl);
}

.account-avatar-large {
    margin-bottom: var(--space-lg);
}

.account-name-large {
    font-size: var(--font-size-xl);
    font-weight: var(--font-bold);
    color: var(--text-primary);
    margin-bottom: var(--space-xs);
}

.account-email-enhanced {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin-bottom: var(--space-md);
    word-break: break-word;
}

.account-stats-enhanced {
    width: 100%;
    padding-top: var(--space-lg);
    border-top: 1px solid var(--divider-color);
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
}

.stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stat-value-right {
    font-size: var(--font-size-sm);
    font-weight: var(--font-semibold);
    color: var(--text-primary);
}

/* Tips - Enhanced */
.tips-list-enhanced {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
}

.tip-item-enhanced {
    display: flex;
    gap: var(--space-md);
    padding: var(--space-md);
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    transition: all var(--transition-fast);
}

.tip-item-enhanced:hover {
    background: var(--bg-elevated);
    border-color: var(--border-color-strong);
}

.tip-icon-large {
    font-size: 32px;
    flex-shrink: 0;
}

.tip-content {
    flex: 1;
}

.tip-title {
    font-size: var(--font-size-sm);
    font-weight: var(--font-semibold);
    color: var(--text-primary);
    margin-bottom: var(--space-xs);
}

.tip-text {
    font-size: var(--font-size-xs);
    color: var(--text-secondary);
    margin-bottom: var(--space-sm);
}

.tip-link {
    font-size: var(--font-size-xs);
    color: var(--cyan);
    text-decoration: none;
    font-weight: var(--font-medium);
    transition: color var(--transition-fast);
}

.tip-link:hover {
    color: var(--blue);
}

/* System Health */
.system-health {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--space-xl);
}

.health-score {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--space-md);
}

.health-circle {
    position: relative;
}

.health-score-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: var(--font-size-2xl);
    font-weight: var(--font-bold);
    color: var(--green);
}

.health-label {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    font-weight: var(--font-medium);
}

.status-list-enhanced {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
}

.status-item-enhanced {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
}

.status-indicator-large {
    width: 12px;
    height: 12px;
    border-radius: var(--radius-full);
    flex-shrink: 0;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: var(--space-3xl);
}

.empty-icon {
    margin-bottom: var(--space-lg);
}

.empty-state h4 {
    font-size: var(--font-size-lg);
    font-weight: var(--font-semibold);
    color: var(--text-primary);
    margin-bottom: var(--space-sm);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .dashboard-welcome {
        padding: var(--space-lg);
    }
    
    .welcome-content {
        flex-direction: column;
        text-align: center;
        gap: var(--space-lg);
    }
    
    .welcome-title {
        font-size: var(--font-size-2xl);
        justify-content: center;
    }
    
    .welcome-stats-mini {
        justify-content: center;
    }
    
    .dashboard-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .apps-grid-enhanced {
        grid-template-columns: 1fr;
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
        const duration = 1500;
        const step = target / (duration / 16);
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
        
        setTimeout(updateCounter, 400);
    });
    
    // Favorite button functionality
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    favoriteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.toggle('active');
        });
    });
});
</script>
<?php View::endSection(); ?>
