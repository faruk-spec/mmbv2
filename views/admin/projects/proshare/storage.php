<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
    }
    .chart-container {
        height: 300px;
        margin-top: 20px;
    }
</style>
<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js" integrity="sha384-bbDI8mCZLZIl99ttY1LlBdJ+F4C5SnKOL3LSvDFPsVlwlFn5XjPmC7z8cFWFcPSZ" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('storageChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($growthTrend, 'date')) ?>,
            datasets: [{
                label: 'Storage Used (MB)',
                data: <?= json_encode(array_column($growthTrend, 'size')) ?>,
                borderColor: '#00ff88',
                backgroundColor: 'rgba(0, 255, 136, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: '#e0e0e0' } }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    ticks: { color: '#e0e0e0' },
                    grid: { color: 'rgba(255, 255, 255, 0.1)' }
                },
                x: { 
                    ticks: { color: '#e0e0e0' },
                    grid: { color: 'rgba(255, 255, 255, 0.1)' }
                }
            }
        }
    });
});
</script>
<?php View::endSection(); ?>

<?php View::section('content'); ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['total_storage_gb'], 2) ?> GB</div>
        <div class="stat-label">Total Storage</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['total_files']) ?></div>
        <div class="stat-label">Total Files</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['total_users']) ?></div>
        <div class="stat-label">Total Users</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['avg_file_size_mb'], 2) ?> MB</div>
        <div class="stat-label">Avg File Size</div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">💾 Storage Per User</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($storagePerUser)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Total Storage</th>
                            <th>File Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($storagePerUser as $storage): ?>
                            <tr>
                                <td><?= htmlspecialchars($storage['user_name'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($storage['email'] ?? 'N/A') ?></td>
                                <td><?= number_format($storage['total_size'] / 1024 / 1024, 2) ?> MB</td>
                                <td><?= number_format($storage['file_count']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-secondary">No storage data available.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                <polyline points="17 6 23 6 23 12"></polyline>
            </svg>
            Storage Growth Trend (Last 30 Days)
        </h3>
    </div>
    <div class="card-body">
        <div class="chart-container">
            <canvas id="storageChart"></canvas>
        </div>
    </div>
</div>

<?php View::endSection(); ?>
