<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content security-center">
    <div class="page-header">
        <div>
            <h1>🛡️ Security Center</h1>
            <p class="page-subtitle">Monitor and manage platform security</p>
        </div>
        <div class="header-actions">
            <button id="autoBlockBtn" class="btn btn-danger">
                <i class="fa fa-shield"></i> Auto-Block Threats
            </button>
            <button id="refreshBtn" class="btn btn-primary">
                <i class="fa fa-refresh"></i> Refresh
            </button>
            <button id="liveToggle" class="btn btn-success active" data-live="true">
                <i class="fa fa-circle pulse"></i> Live
            </button>
        </div>
    </div>
    
    <!-- Security Stats -->
    <div class="stats-grid">
        <div class="stat-card gradient-red">
            <div class="stat-icon">
                <i class="fa fa-ban"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Blocked IPs</div>
                <div class="stat-value" id="blockedIps"><?= $stats['blocked_ips'] ?></div>
                <a href="/admin/security/blocked-ips" class="stat-link">Manage →</a>
            </div>
        </div>
        
        <div class="stat-card gradient-orange">
            <div class="stat-icon">
                <i class="fa fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Failed Logins (Today)</div>
                <div class="stat-value" id="failedLoginsToday"><?= $stats['failed_logins_today'] ?></div>
                <div class="stat-trend">Last hour: <?= $stats['failed_logins_hour'] ?></div>
            </div>
        </div>
        
        <div class="stat-card gradient-purple">
            <div class="stat-icon">
                <i class="fa fa-user-secret"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Suspicious IPs</div>
                <div class="stat-value" id="suspiciousIps"><?= $stats['suspicious_ips'] ?></div>
                <div class="stat-trend">5+ attempts/hour</div>
            </div>
        </div>
        
        <div class="stat-card gradient-green">
            <div class="stat-icon">
                <i class="fa fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Active Sessions</div>
                <div class="stat-value" id="activeSessions"><?= $stats['active_sessions'] ?></div>
                <div class="stat-trend">Authenticated users</div>
            </div>
        </div>
    </div>
    
    <!-- Dashboard Grid -->
    <div class="dashboard-grid">
        <!-- Failed Login Trend -->
        <div class="chart-card full-width">
            <div class="chart-header">
                <h3>Failed Login Attempts (Last 24 Hours)</h3>
            </div>
            <div class="chart-container">
                <canvas id="failedLoginChart"></canvas>
            </div>
        </div>
        
        <!-- Suspicious IPs List -->
        <?php if (!empty($suspiciousIps)): ?>
        <div class="chart-card half-width">
            <div class="chart-header">
                <h3>⚠️ Suspicious IPs</h3>
                <span class="badge badge-warning"><?= count($suspiciousIps) ?></span>
            </div>
            <div class="suspicious-list">
                <?php foreach ($suspiciousIps as $ip): ?>
                <div class="suspicious-item">
                    <div class="ip-info">
                        <span class="ip-address"><?= htmlspecialchars($ip['ip_address']) ?></span>
                        <span class="attempt-count"><?= $ip['attempts'] ?> attempts</span>
                    </div>
                    <button onclick="blockIp('<?= htmlspecialchars($ip['ip_address']) ?>')" class="btn btn-sm btn-danger">
                        <i class="fa fa-ban"></i> Block
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="chart-card half-width">
            <div class="chart-header">
                <h3>✅ Suspicious IPs</h3>
            </div>
            <div class="empty-state">
                <i class="fa fa-shield"></i>
                <p>No suspicious activity detected</p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Top Targeted Usernames -->
        <div class="chart-card half-width">
            <div class="chart-header">
                <h3>Most Targeted Usernames</h3>
            </div>
            <?php if (!empty($topTargetedUsers)): ?>
            <div class="info-list">
                <?php foreach ($topTargetedUsers as $user): ?>
                <div class="info-item">
                    <span class="info-label"><?= htmlspecialchars($user['username']) ?></span>
                    <span class="info-value badge badge-orange"><?= $user['attempts'] ?> attempts</span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <p>No data available</p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Recent Failed Logins -->
        <div class="chart-card full-width">
            <div class="chart-header">
                <h3>Recent Failed Login Attempts</h3>
                <a href="/admin/security/failed-logins" class="btn btn-sm btn-secondary">View All</a>
            </div>
            <?php if (!empty($recentFailedLogins)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Username</th>
                            <th>IP Address</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentFailedLogins as $login): ?>
                        <tr>
                            <td><?= Helpers::timeAgo($login['attempted_at']) ?></td>
                            <td><?= htmlspecialchars($login['username']) ?></td>
                            <td><?= htmlspecialchars($login['ip_address']) ?></td>
                            <td>
                                <button onclick="blockIp('<?= htmlspecialchars($login['ip_address']) ?>')" class="btn btn-sm btn-danger">
                                    <i class="fa fa-ban"></i> Block
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fa fa-check-circle"></i>
                <p>No failed login attempts</p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="chart-card half-width">
            <div class="chart-header">
                <h3>Quick Actions</h3>
            </div>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <a href="/admin/security/blocked-ips" class="action-card">
                    <div class="action-icon" style="background: rgba(255, 107, 107, 0.1);">
                        <i class="fa fa-ban" style="color: #ff6b6b;"></i>
                    </div>
                    <div>
                        <div class="action-title">Blocked IPs</div>
                        <div class="action-desc">Manage IP blocking rules</div>
                    </div>
                </a>
                
                <a href="/admin/security/failed-logins" class="action-card">
                    <div class="action-icon" style="background: rgba(255, 170, 0, 0.1);">
                        <i class="fa fa-exclamation-triangle" style="color: #ffaa00;"></i>
                    </div>
                    <div>
                        <div class="action-title">Failed Logins</div>
                        <div class="action-desc">View all failed attempts</div>
                    </div>
                </a>
            </div>
        </div>
        
        <!-- Blocked IPs by Reason -->
        <div class="chart-card half-width">
            <div class="chart-header">
                <h3>Blocked IPs by Reason</h3>
            </div>
            <?php if (!empty($blockedByReason)): ?>
            <div class="info-list">
                <?php foreach ($blockedByReason as $item): ?>
                <div class="info-item">
                    <span class="info-label"><?= htmlspecialchars($item['reason'] ?: 'No reason') ?></span>
                    <span class="info-value"><?= $item['count'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <p>No blocked IPs</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Auto-Block Modal -->
<div id="autoBlockModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Auto-Block Configuration</h3>
            <button class="close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Failed Attempts Threshold</label>
                <input type="number" id="blockThreshold" class="form-control" value="5" min="1">
                <small>Block IPs with this many failed attempts in the last hour</small>
            </div>
            <div class="form-group">
                <label>Block Duration</label>
                <select id="blockDuration" class="form-control">
                    <option value="1 hour">1 Hour</option>
                    <option value="24 hours" selected>24 Hours</option>
                    <option value="7 days">7 Days</option>
                    <option value="30 days">30 Days</option>
                    <option value="permanent">Permanent</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close">Cancel</button>
            <button id="confirmAutoBlock" class="btn btn-danger">
                <i class="fa fa-shield"></i> Execute Auto-Block
            </button>
        </div>
    </div>
</div>

<style>
.security-center {
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.page-header h1 {
    margin: 0 0 5px 0;
    font-size: 28px;
    color: #333;
}

.page-subtitle {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
}

.stat-card.gradient-red {
    --gradient-start: #ff6b6b;
    --gradient-end: #ee5a6f;
}

.stat-card.gradient-orange {
    --gradient-start: #f093fb;
    --gradient-end: #f5576c;
}

.stat-card.gradient-purple {
    --gradient-start: #667eea;
    --gradient-end: #764ba2;
}

.stat-card.gradient-green {
    --gradient-start: #43e97b;
    --gradient-end: #38f9d7;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
    color: white;
}

.stat-content {
    flex: 1;
}

.stat-label {
    font-size: 13px;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.stat-value {
    font-size: 28px;
    font-weight: bold;
    color: #333;
    margin-bottom: 3px;
}

.stat-trend {
    font-size: 12px;
    color: #666;
}

.stat-link {
    font-size: 13px;
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
}

.stat-link:hover {
    text-decoration: underline;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.chart-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.chart-card.full-width {
    grid-column: span 2;
}

.chart-card.half-width {
    grid-column: span 1;
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.chart-header h3 {
    margin: 0;
    font-size: 18px;
    color: #333;
}

.badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.badge-orange {
    background: #ffe5e5;
    color: #ff6b6b;
}

.chart-container {
    height: 300px;
    position: relative;
}

.suspicious-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.suspicious-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: #fff3cd;
    border-radius: 8px;
    border-left: 4px solid #ffaa00;
}

.ip-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.ip-address {
    font-weight: 600;
    color: #333;
}

.attempt-count {
    font-size: 12px;
    color: #666;
}

.empty-state {
    padding: 40px 20px;
    text-align: center;
    color: #999;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.3;
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-size: 14px;
    color: #666;
}

.info-value {
    font-size: 14px;
    color: #333;
    font-weight: 600;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}

.table th {
    font-weight: 600;
    color: #666;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.action-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s;
}

.action-card:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.action-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.action-title {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 2px;
}

.action-desc {
    font-size: 12px;
    color: #666;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 500px;
    max-width: 90%;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #f0f0f0;
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
}

.modal-header .close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #999;
}

.modal-body {
    padding: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    font-size: 14px;
    color: #333;
}

.form-group small {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: #666;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #f0f0f0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.pulse {
    animation: pulse-animation 2s infinite;
}

@keyframes pulse-animation {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .chart-card.full-width,
    .chart-card.half-width {
        grid-column: span 1;
    }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Auto-refresh and live updates
let autoRefresh = true;
let refreshInterval = null;

document.getElementById('refreshBtn').addEventListener('click', () => {
    location.reload();
});

document.getElementById('liveToggle').addEventListener('click', (e) => {
    autoRefresh = !autoRefresh;
    e.target.classList.toggle('active');
    e.target.dataset.live = autoRefresh;
    
    if (autoRefresh) {
        startAutoRefresh();
    } else {
        stopAutoRefresh();
    }
});

function startAutoRefresh() {
    refreshInterval = setInterval(async () => {
        if (autoRefresh) {
            try {
                const response = await fetch('/admin/security/stats');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('blockedIps').textContent = data.stats.blocked_ips;
                    document.getElementById('failedLoginsToday').textContent = data.stats.failed_logins_today;
                    document.getElementById('activeSessions').textContent = data.stats.active_sessions;
                    // Update suspicious IPs if available
                }
            } catch (error) {
                console.error('Failed to refresh stats:', error);
            }
        }
    }, 30000); // Every 30 seconds
}

function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
}

// Failed login trend chart
<?php if (!empty($failedLoginTrend)): ?>
const failedLoginCtx = document.getElementById('failedLoginChart').getContext('2d');
new Chart(failedLoginCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(function($item) { return $item['hour'] . ':00'; }, $failedLoginTrend)) ?>,
        datasets: [{
            label: 'Failed Attempts',
            data: <?= json_encode(array_map(function($item) { return $item['count']; }, $failedLoginTrend)) ?>,
            backgroundColor: 'rgba(255, 107, 107, 0.5)',
            borderColor: '#ff6b6b',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
<?php endif; ?>

// Auto-block modal
document.getElementById('autoBlockBtn').addEventListener('click', () => {
    document.getElementById('autoBlockModal').classList.add('active');
});

document.querySelectorAll('#autoBlockModal .close').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('autoBlockModal').classList.remove('active');
    });
});

document.getElementById('confirmAutoBlock').addEventListener('click', async () => {
    const threshold = document.getElementById('blockThreshold').value;
    const duration = document.getElementById('blockDuration').value;
    
    try {
        const formData = new FormData();
        formData.append('threshold', threshold);
        formData.append('duration', duration);
        
        const response = await fetch('/admin/security/auto-block', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            document.getElementById('autoBlockModal').classList.remove('active');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Failed to execute auto-block');
    }
});

// Block IP function
async function blockIp(ip) {
    if (!confirm(`Are you sure you want to block IP ${ip}?`)) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('ip_address', ip);
        formData.append('reason', 'Manually blocked from security center');
        formData.append('duration', '24 hours');
        
        const response = await fetch('/admin/security/block-ip', {
            method: 'POST',
            body: formData
        });
        
        // Since this might redirect, check for success
        alert('IP blocked successfully');
        location.reload();
    } catch (error) {
        alert('Failed to block IP');
    }
}

// Start auto-refresh
startAutoRefresh();
</script>

<?php View::endSection(); ?>
