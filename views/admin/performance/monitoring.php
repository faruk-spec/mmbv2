<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content performance-monitoring">
    <div class="page-header">
        <h1><?= $title ?></h1>
        <div class="header-actions">
            <button id="refreshBtn" class="btn btn-primary">
                <i class="fa fa-refresh"></i> Refresh
            </button>
            <button id="autoRefreshToggle" class="btn btn-success active" data-active="true">
                <i class="fa fa-circle pulse"></i> Live
            </button>
        </div>
    </div>
    
    <!-- Server Status Cards -->
    <div class="stats-grid">
        <div class="stat-card gradient-purple">
            <div class="stat-icon">
                <i class="fa fa-server"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">System Load (1min)</div>
                <div class="stat-value"><?= number_format($serverInfo['load_1min'], 2) ?></div>
                <div class="stat-trend">
                    5min: <?= number_format($serverInfo['load_5min'], 2) ?> | 
                    15min: <?= number_format($serverInfo['load_15min'], 2) ?>
                </div>
            </div>
        </div>
        
        <div class="stat-card gradient-blue">
            <div class="stat-icon">
                <i class="fa fa-memory"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Memory Usage</div>
                <div class="stat-value"><?= $serverInfo['memory_usage_formatted'] ?></div>
                <div class="stat-trend">Peak: <?= $serverInfo['memory_peak_formatted'] ?></div>
            </div>
        </div>
        
        <div class="stat-card gradient-green">
            <div class="stat-icon">
                <i class="fa fa-hdd-o"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Disk Usage</div>
                <div class="stat-value"><?= $serverInfo['disk_used_percent'] ?>%</div>
                <div class="stat-trend"><?= $serverInfo['disk_used'] ?> / <?= $serverInfo['disk_total'] ?></div>
            </div>
        </div>
        
        <div class="stat-card gradient-orange">
            <div class="stat-icon">
                <i class="fa fa-database"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">DB Connections</div>
                <div class="stat-value"><?= $dbStats['connections'] ?></div>
                <div class="stat-trend">Slow Queries: <?= $dbStats['slow_queries'] ?></div>
            </div>
        </div>
    </div>
    
    <!-- Performance Charts -->
    <div class="dashboard-grid">
        <!-- System Resources -->
        <div class="chart-card full-width">
            <div class="chart-header">
                <h3>System Resources</h3>
            </div>
            <div class="resource-bars">
                <div class="resource-item">
                    <div class="resource-label">
                        <span>CPU Load (1min)</span>
                        <span class="resource-value"><?= number_format($serverInfo['load_1min'], 2) ?></span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= min(100, $serverInfo['load_1min'] * 25) ?>%; background: linear-gradient(90deg, #667eea, #764ba2);"></div>
                    </div>
                </div>
                
                <div class="resource-item">
                    <div class="resource-label">
                        <span>Memory Usage</span>
                        <span class="resource-value"><?= $serverInfo['memory_usage_formatted'] ?></span>
                    </div>
                    <div class="progress-bar">
                        <?php 
                        $memoryPercent = ($serverInfo['memory_usage'] / ($serverInfo['memory_peak'] ?: 1)) * 100;
                        ?>
                        <div class="progress-fill" style="width: <?= min(100, $memoryPercent) ?>%; background: linear-gradient(90deg, #4facfe, #00f2fe);"></div>
                    </div>
                </div>
                
                <div class="resource-item">
                    <div class="resource-label">
                        <span>Disk Space</span>
                        <span class="resource-value"><?= $serverInfo['disk_used_percent'] ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $serverInfo['disk_used_percent'] ?>%; background: linear-gradient(90deg, #43e97b, #38f9d7);"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Server Information -->
        <div class="chart-card half-width">
            <div class="chart-header">
                <h3>Server Information</h3>
            </div>
            <div class="info-list">
                <div class="info-item">
                    <span class="info-label">PHP Version</span>
                    <span class="info-value"><?= htmlspecialchars($serverInfo['php_version']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Server Software</span>
                    <span class="info-value"><?= htmlspecialchars($serverInfo['server_software']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Memory Limit</span>
                    <span class="info-value"><?= $serverInfo['memory_limit'] ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Max Execution Time</span>
                    <span class="info-value"><?= $serverInfo['max_execution_time'] ?>s</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Upload Max Size</span>
                    <span class="info-value"><?= $serverInfo['upload_max_filesize'] ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Post Max Size</span>
                    <span class="info-value"><?= $serverInfo['post_max_size'] ?></span>
                </div>
            </div>
        </div>
        
        <!-- Database Performance -->
        <div class="chart-card half-width">
            <div class="chart-header">
                <h3>Database Performance</h3>
            </div>
            <div class="info-list">
                <div class="info-item">
                    <span class="info-label">Total Queries</span>
                    <span class="info-value"><?= number_format($dbStats['total_queries']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Slow Queries</span>
                    <span class="info-value"><?= number_format($dbStats['slow_queries']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Active Connections</span>
                    <span class="info-value"><?= $dbStats['connections'] ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Avg Response Time</span>
                    <span class="info-value"><?= $avgResponseTime ?>s</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.performance-monitoring {
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.page-header h1 {
    margin: 0;
    font-size: 28px;
    color: #333;
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

.stat-card.gradient-purple {
    --gradient-start: #667eea;
    --gradient-end: #764ba2;
}

.stat-card.gradient-blue {
    --gradient-start: #4facfe;
    --gradient-end: #00f2fe;
}

.stat-card.gradient-green {
    --gradient-start: #43e97b;
    --gradient-end: #38f9d7;
}

.stat-card.gradient-orange {
    --gradient-start: #f093fb;
    --gradient-end: #f5576c;
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
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.chart-header h3 {
    margin: 0;
    font-size: 18px;
    color: #333;
}

.resource-bars {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.resource-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.resource-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 14px;
    color: #555;
    font-weight: 500;
}

.resource-value {
    color: #333;
    font-weight: 600;
}

.progress-bar {
    height: 12px;
    background: #f0f0f0;
    border-radius: 6px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    transition: width 0.3s ease;
    border-radius: 6px;
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
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

<script>
// Auto-refresh functionality
let autoRefresh = true;
let refreshInterval = null;

document.getElementById('refreshBtn').addEventListener('click', () => {
    location.reload();
});

document.getElementById('autoRefreshToggle').addEventListener('click', (e) => {
    autoRefresh = !autoRefresh;
    e.target.classList.toggle('active');
    e.target.dataset.active = autoRefresh;
    
    if (autoRefresh) {
        startAutoRefresh();
    } else {
        stopAutoRefresh();
    }
});

function startAutoRefresh() {
    refreshInterval = setInterval(() => {
        if (autoRefresh) {
            location.reload();
        }
    }, 30000); // Refresh every 30 seconds
}

function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
}

// Start auto-refresh by default
startAutoRefresh();
</script>

<?php View::endSection(); ?>
