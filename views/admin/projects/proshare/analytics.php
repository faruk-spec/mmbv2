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
    const ctx = document.getElementById('trafficChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($trafficData, 'date')) ?>,
            datasets: [
                {
                    label: 'Uploads',
                    data: <?= json_encode(array_column($trafficData, 'uploads')) ?>,
                    borderColor: '#00ff88',
                    backgroundColor: 'rgba(0, 255, 136, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Downloads',
                    data: <?= json_encode(array_column($trafficData, 'downloads')) ?>,
                    borderColor: '#4facfe',
                    backgroundColor: 'rgba(79, 172, 254, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
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
        <div class="stat-value"><?= number_format($stats['active_users_30d']) ?></div>
        <div class="stat-label">Active Users (30d)</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['total_downloads']) ?></div>
        <div class="stat-label">Total Downloads</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['total_uploads']) ?></div>
        <div class="stat-label">Total Uploads</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['avg_downloads_per_file'], 1) ?></div>
        <div class="stat-label">Avg Downloads/File</div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">📊 Traffic Overview (Last 30 Days)</h3>
    </div>
    <div class="card-body">
        <div class="chart-container">
            <canvas id="trafficChart"></canvas>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">📥 Most Downloaded Files</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($mostDownloaded)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Filename</th>
                            <th>Size</th>
                            <th>Downloads</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($mostDownloaded, 0, 10) as $file): ?>
                            <tr>
                                <td><?= htmlspecialchars($file['original_name'] ?? $file['filename']) ?></td>
                                <td><?= number_format(($file['size'] ?? 0) / 1024 / 1024, 2) ?> MB</td>
                                <td><?= number_format($file['download_count']) ?></td>
                                <td><?= date('Y-m-d H:i', strtotime($file['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-secondary">No download data available.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">�� Most Active Users (Last 30 Days)</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($mostActive)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Activity Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($mostActive, 0, 10) as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['user_name'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($user['email'] ?? 'N/A') ?></td>
                                <td><?= number_format($user['activity_count']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-secondary">No activity data available.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">✅ Active Users (Last 30 Days)</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($activeUsers)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Last Login</th>
                            <th>Activity Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($activeUsers, 0, 10) as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= date('Y-m-d H:i', strtotime($user['last_login_at'])) ?></td>
                                <td><?= number_format($user['activity_count']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-secondary">No active users in the last 30 days.</p>
        <?php endif; ?>
    </div>
</div>

<?php View::endSection(); ?>
