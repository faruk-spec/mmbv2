<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: linear-gradient(135deg, var(--bg-card), var(--bg-secondary));
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 5px;
        color: var(--cyan);
    }
    .stat-label {
        color: var(--text-secondary);
        font-size: 14px;
        margin-bottom: 10px;
    }
    .progress-bar {
        background: var(--bg-secondary);
        height: 10px;
        border-radius: 5px;
        overflow: hidden;
        margin-top: 10px;
    }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--cyan), var(--green));
        transition: width 0.3s ease;
    }
    .progress-fill.warning {
        background: linear-gradient(90deg, var(--orange), var(--yellow));
    }
    .progress-fill.danger {
        background: linear-gradient(90deg, var(--red), var(--orange));
    }
    .metric-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid var(--border-color);
    }
    .log-entry {
        padding: 8px;
        background: var(--bg-secondary);
        margin-bottom: 5px;
        border-radius: 4px;
        font-size: 12px;
        font-family: monospace;
    }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">💻 CPU Usage</div>
        <div class="stat-value"><?= number_format($health['cpu_usage'], 2) ?>%</div>
        <div class="progress-bar">
            <div class="progress-fill <?= $health['cpu_usage'] > 80 ? 'danger' : ($health['cpu_usage'] > 60 ? 'warning' : '') ?>" 
                 style="width: <?= min($health['cpu_usage'], 100) ?>%"></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">🧠 Memory Usage</div>
        <div class="stat-value"><?= number_format($health['memory_usage']['percentage'], 2) ?>%</div>
        <small class="text-secondary">
            <?= number_format($health['memory_usage']['used'], 2) ?> GB / <?= number_format($health['memory_usage']['total'], 2) ?> GB
        </small>
        <div class="progress-bar">
            <div class="progress-fill <?= $health['memory_usage']['percentage'] > 80 ? 'danger' : ($health['memory_usage']['percentage'] > 60 ? 'warning' : '') ?>" 
                 style="width: <?= min($health['memory_usage']['percentage'], 100) ?>%"></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">💾 Disk Usage</div>
        <div class="stat-value"><?= number_format($health['disk_usage']['percentage'], 2) ?>%</div>
        <small class="text-secondary">
            <?= number_format($health['disk_usage']['used'], 2) ?> GB / <?= number_format($health['disk_usage']['total'], 2) ?> GB
        </small>
        <div class="progress-bar">
            <div class="progress-fill <?= $health['disk_usage']['percentage'] > 80 ? 'danger' : ($health['disk_usage']['percentage'] > 60 ? 'warning' : '') ?>" 
                 style="width: <?= min($health['disk_usage']['percentage'], 100) ?>%"></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">⏱️ System Uptime</div>
        <div style="font-size: 1.2rem; margin-top: 15px;">
            <?= htmlspecialchars($health['uptime']) ?>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
            Load Average
        </h3>
    </div>
    <div class="card-body">
        <div class="metric-row">
            <span>1 minute:</span>
            <strong><?= number_format($health['load_average'][0], 2) ?></strong>
        </div>
        <div class="metric-row">
            <span>5 minutes:</span>
            <strong><?= number_format($health['load_average'][1], 2) ?></strong>
        </div>
        <div class="metric-row">
            <span>15 minutes:</span>
            <strong><?= number_format($health['load_average'][2], 2) ?></strong>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">🗄️ Database Performance</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($dbPerf)): ?>
            <?php foreach ($dbPerf as $key => $stat): ?>
                <?php if ($stat): ?>
                    <div class="metric-row">
                        <span><?= htmlspecialchars($stat['Variable_name'] ?? $key) ?>:</span>
                        <strong><?= htmlspecialchars($stat['Value'] ?? 'N/A') ?></strong>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-secondary">Database performance metrics not available.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">📋 Recent Error Logs (Last 100 entries)</h3>
    </div>
    <div class="card-body">
        <div style="max-height: 400px; overflow-y: auto;">
            <?php if (!empty($errorLogs)): ?>
                <?php foreach ($errorLogs as $log): ?>
                    <div class="log-entry"><?= htmlspecialchars($log) ?></div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-secondary">No error logs found or log file doesn't exist.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php View::endSection(); ?>
