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
                <span class="wave-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                        <path d="M17 18a5 5 0 0 0-10 0"/>
                        <line x1="12" y1="2" x2="12" y2="9"/>
                        <line x1="4.22" y1="4.22" x2="9.17" y2="9.17"/>
                        <line x1="1" y1="12" x2="8" y2="12"/>
                        <line x1="4.22" y1="19.78" x2="9.17" y2="14.83"/>
                    </svg>
                </span>
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

        <!-- NEW: Quick Search Widget -->
        <div class="widget animate-fade-in" style="animation-delay: 0.35s;">
            <div class="widget-header">
                <h3 class="widget-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    Quick Search
                </h3>
            </div>
            <div class="widget-body">
                <div style="position: relative;">
                    <input type="text" placeholder="Search applications, files..." 
                           style="width: 100%; padding: var(--space-md) var(--space-lg) var(--space-md) 40px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: var(--font-size-sm);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-tertiary)" stroke-width="2" 
                         style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%);">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                </div>
                <div style="margin-top: var(--space-md); font-size: var(--font-size-xs); color: var(--text-tertiary);">
                    <kbd style="background: var(--bg-secondary); padding: 2px 6px; border-radius: 4px; border: 1px solid var(--border-color);">Ctrl</kbd> + 
                    <kbd style="background: var(--bg-secondary); padding: 2px 6px; border-radius: 4px; border: 1px solid var(--border-color);">K</kbd> 
                    for quick search
                </div>
            </div>
        </div>

        <!-- NEW: Bookmarks/Favorites Widget -->
        <div class="widget animate-fade-in" style="animation-delay: 0.4s;">
            <div class="widget-header">
                <h3 class="widget-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                        <path d="m19 21-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                    </svg>
                    Bookmarks
                </h3>
            </div>
            <div class="widget-body">
                <div class="quick-actions-vertical">
                    <a href="#" class="quick-action-card">
                        <div class="action-icon" style="background: linear-gradient(135deg, var(--cyan), var(--blue));">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                            </svg>
                        </div>
                        <div class="action-content">
                            <div class="action-title">Documentation</div>
                            <div class="action-desc">User guides</div>
                        </div>
                    </a>
                    <a href="#" class="quick-action-card">
                        <div class="action-icon" style="background: linear-gradient(135deg, var(--green), #00cc66);">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                        </div>
                        <div class="action-content">
                            <div class="action-title">Support</div>
                            <div class="action-desc">Get help</div>
                        </div>
                    </a>
                    <a href="#" class="quick-action-card">
                        <div class="action-icon" style="background: linear-gradient(135deg, var(--purple), #7733ff);">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <polyline points="16 18 22 12 16 6"/>
                                <polyline points="8 6 2 12 8 18"/>
                            </svg>
                        </div>
                        <div class="action-content">
                            <div class="action-title">API Docs</div>
                            <div class="action-desc">Integration</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- NEW: Storage Usage Widget -->
        <div class="widget animate-fade-in" style="animation-delay: 0.45s;">
            <div class="widget-header">
                <h3 class="widget-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    </svg>
                    Storage
                </h3>
            </div>
            <div class="widget-body">
                <div style="margin-bottom: var(--space-lg);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-sm);">
                        <span style="font-size: var(--font-size-sm); color: var(--text-secondary);">2.4 GB used</span>
                        <span style="font-size: var(--font-size-sm); color: var(--text-secondary);">10 GB total</span>
                    </div>
                    <div class="progress-bar" style="height: 8px;">
                        <div class="progress-fill" style="width: 24%; background: linear-gradient(90deg, var(--cyan), var(--blue));"></div>
                    </div>
                </div>
                <div style="display: grid; gap: var(--space-sm);">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: var(--space-sm);">
                            <div style="width: 8px; height: 8px; background: var(--cyan); border-radius: var(--radius-full);"></div>
                            <span style="font-size: var(--font-size-xs); color: var(--text-secondary);">Documents</span>
                        </div>
                        <span style="font-size: var(--font-size-xs); color: var(--text-primary); font-weight: var(--font-semibold);">1.2 GB</span>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: var(--space-sm);">
                            <div style="width: 8px; height: 8px; background: var(--purple); border-radius: var(--radius-full);"></div>
                            <span style="font-size: var(--font-size-xs); color: var(--text-secondary);">Media</span>
                        </div>
                        <span style="font-size: var(--font-size-xs); color: var(--text-primary); font-weight: var(--font-semibold);">800 MB</span>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: var(--space-sm);">
                            <div style="width: 8px; height: 8px; background: var(--green); border-radius: var(--radius-full);"></div>
                            <span style="font-size: var(--font-size-xs); color: var(--text-secondary);">Other</span>
                        </div>
                        <span style="font-size: var(--font-size-xs); color: var(--text-primary); font-weight: var(--font-semibold);">400 MB</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications Widget - Enhanced -->
        <div class="widget animate-fade-in" style="animation-delay: 0.5s;">
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
                        <div class="tip-icon-large">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                                <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                            </svg>
                        </div>
                        <div class="tip-content">
                            <div class="tip-title">Enable 2FA</div>
                            <div class="tip-text">Add extra security to your account</div>
                            <a href="/security" class="tip-link">Set up now →</a>
                        </div>
                    </div>
                    <div class="tip-item-enhanced">
                        <div class="tip-icon-large">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                        </div>
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
