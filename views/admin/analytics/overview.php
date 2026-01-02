<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content">
    <h1><?= $title ?></h1>
    
    <!-- Live Traffic Stats -->
    <div class="card mb-3">
        <h2>🔴 Live Traffic</h2>
        <div class="stats-grid">
            <div class="stat-card live">
                <h3>Active Users</h3>
                <p class="stat-value"><?= number_format($liveStats['active_users']) ?></p>
                <small>Last 5 minutes</small>
            </div>
            <div class="stat-card live">
                <h3>Events This Minute</h3>
                <p class="stat-value"><?= number_format($liveStats['current_minute']) ?></p>
                <small>Current minute</small>
            </div>
            <div class="stat-card live">
                <h3>Events This Hour</h3>
                <p class="stat-value"><?= number_format($liveStats['last_hour']['total_events'] ?? 0) ?></p>
                <small><?= number_format($liveStats['last_hour']['unique_users'] ?? 0) ?> users</small>
            </div>
            <div class="stat-card live">
                <h3>Unique IPs</h3>
                <p class="stat-value"><?= number_format($liveStats['last_hour']['unique_ips'] ?? 0) ?></p>
                <small>Last hour</small>
            </div>
        </div>
    </div>
    
    <!-- Conversion Stats -->
    <div class="card mb-3">
        <h2>Today's Conversions</h2>
        <div class="stats-grid">
            <div class="stat-card conversion">
                <h3>New Registrations</h3>
                <p class="stat-value"><?= number_format($conversionStats['registrations_today']) ?></p>
            </div>
            <div class="stat-card conversion">
                <h3>Logins</h3>
                <p class="stat-value"><?= number_format($conversionStats['logins_today']) ?></p>
            </div>
            <div class="stat-card conversion">
                <h3>Return Visits</h3>
                <p class="stat-value"><?= number_format($conversionStats['return_visits_today']) ?></p>
            </div>
        </div>
    </div>
    
    <!-- Overall Stats -->
    <div class="card mb-3">
        <h2>Overall Statistics</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Events</h3>
                <p class="stat-value"><?= number_format($stats["total_events"]) ?></p>
            </div>
            <div class="stat-card">
                <h3>Today</h3>
                <p class="stat-value"><?= number_format($stats["events_today"]) ?></p>
            </div>
            <div class="stat-card">
                <h3>This Week</h3>
                <p class="stat-value"><?= number_format($stats["events_week"]) ?></p>
            </div>
            <div class="stat-card">
                <h3>This Month</h3>
                <p class="stat-value"><?= number_format($stats["events_month"]) ?></p>
            </div>
            <div class="stat-card">
                <h3>Unique Users Today</h3>
                <p class="stat-value"><?= number_format($stats["unique_users_today"]) ?></p>
            </div>
        </div>
    </div>
    
    <!-- Recent Visitors -->
    <div class="card mb-3">
        <h2>Recent Visitors (Last 10 Minutes)</h2>
        <?php if (empty($recentVisitors)): ?>
            <p>No recent visitors.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>IP Address</th>
                        <th>Browser</th>
                        <th>Platform</th>
                        <th>Country</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentVisitors as $visitor): ?>
                    <tr>
                        <td><?= date('H:i:s', strtotime($visitor['created_at'])) ?></td>
                        <td><?= htmlspecialchars($visitor['user_name'] ?? 'Guest') ?></td>
                        <td><?= htmlspecialchars($visitor['ip_address']) ?></td>
                        <td><?= htmlspecialchars($visitor['browser'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($visitor['platform'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($visitor['country'] ?? 'N/A') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Top Events -->
    <div class="card">
        <h2>Top Events (Last 7 Days)</h2>
        <?php if (empty($topEvents)): ?>
            <p>No events recorded yet.</p>
        <?php else: ?>
            <table class="table">
                <thead><tr><th>Event Type</th><th>Count</th></tr></thead>
                <tbody>
                    <?php foreach ($topEvents as $event): ?>
                    <tr>
                        <td><?= htmlspecialchars($event["event_type"]) ?></td>
                        <td><?= number_format($event["count"]) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}
.stat-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}
.stat-card.live {
    border-left: 4px solid #28a745;
    background: #f0fdf4;
}
.stat-card.conversion {
    border-left: 4px solid #007bff;
    background: #f0f7ff;
}
.stat-card h3 {
    font-size: 14px;
    color: #666;
    margin: 0 0 10px 0;
}
.stat-card .stat-value {
    font-size: 32px;
    font-weight: bold;
    color: #333;
    margin: 0;
}
.stat-card small {
    font-size: 12px;
    color: #999;
}
.mb-3 {
    margin-bottom: 20px;
}

/* Auto-refresh indicator */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
.stat-card.live h3::before {
    content: '●';
    color: #28a745;
    margin-right: 5px;
    animation: pulse 2s infinite;
}
</style>

<script>
// Auto-refresh live stats every 30 seconds using AJAX (more efficient than full page reload)
// For now, we use full page reload as it's simpler and works across all browsers
// TODO: Implement AJAX refresh or WebSocket for real-time updates without full page reload
setTimeout(function() {
    // In future, replace with AJAX:
    // fetch('/admin/analytics/overview?ajax=1')
    //   .then(response => response.json())
    //   .then(data => updateLiveStats(data));
    location.reload();
}, 30000);
</script>

<?php View::endSection(); ?>
