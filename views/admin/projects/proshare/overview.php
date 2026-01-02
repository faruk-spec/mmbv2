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
    background: linear-gradient(135deg, var(--cyan), var(--magenta));
    padding: 25px;
    border-radius: 12px;
    color: white;
}
.stat-card h3 {
    font-size: 14px;
    margin: 0 0 10px 0;
    opacity: 0.9;
}
.stat-card .value {
    font-size: 32px;
    font-weight: bold;
    margin: 0;
}
.stat-card .trend {
    font-size: 12px;
    margin-top: 10px;
    opacity: 0.8;
}
.content-section {
    background: var(--bg-card);
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 20px;
    border: 1px solid var(--border-color);
}
.content-section h2 {
    color: var(--cyan);
    margin-top: 0;
}
.table-container {
    overflow-x: auto;
}
table {
    width: 100%;
    border-collapse: collapse;
}
table th {
    background: var(--bg-secondary);
    padding: 12px;
    text-align: left;
    color: var(--cyan);
    font-weight: 600;
    border-bottom: 2px solid var(--border-color);
}
table td {
    padding: 12px;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-primary);
}
table tr:hover {
    background: rgba(0, 240, 255, 0.05);
}
.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.status-active {
    background: rgba(0, 255, 136, 0.2);
    color: var(--green);
}
.status-expired {
    background: rgba(136, 136, 136, 0.2);
    color: var(--text-secondary);
}
.chart-container {
    height: 300px;
    margin-top: 20px;
}
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>

    <div class="admin-content">
        <h1>ProShare Overview</h1>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Files</h3>
                <p class="value"><?= number_format($stats['total_files'] ?? 0) ?></p>
                <p class="trend">↑ <?= $stats['files_trend'] ?? '+0' ?>% this week</p>
            </div>
            <div class="stat-card">
                <h3>Total Downloads</h3>
                <p class="value"><?= number_format($stats['total_downloads'] ?? 0) ?></p>
                <p class="trend">↑ <?= $stats['downloads_trend'] ?? '+0' ?>% this week</p>
            </div>
            <div class="stat-card">
                <h3>Text Shares</h3>
                <p class="value"><?= number_format($stats['total_texts'] ?? 0) ?></p>
                <p class="trend"><?= number_format($stats['text_views'] ?? 0) ?> views</p>
            </div>
            <div class="stat-card">
                <h3>Storage Used</h3>
                <p class="value"><?= $stats['storage_used'] ?? '0 GB' ?></p>
                <p class="trend"><?= $stats['storage_percent'] ?? '0' ?>% of limit</p>
            </div>
        </div>

        <!-- Recent Files -->
        <div class="content-section">
            <h2>Recent File Shares</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Filename</th>
                            <th>Size</th>
                            <th>Downloads</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Expires</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_files)): ?>
                            <?php foreach ($recent_files as $file): ?>
                                <tr>
                                    <td><?= htmlspecialchars($file['original_filename']) ?></td>
                                    <td><?= $file['size_formatted'] ?? '0 KB' ?></td>
                                    <td><?= $file['download_count'] ?? 0 ?> / <?= $file['max_downloads'] ?? '∞' ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $file['status'] ?>">
                                            <?= ucfirst($file['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($file['created_at'])) ?></td>
                                    <td><?= $file['expires_at'] ? date('M d, Y', strtotime($file['expires_at'])) : 'Never' ?></td>
                                    <td>
                                        <a href="/admin/projects/proshare/file/<?= $file['id'] ?>" class="btn-sm">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No files found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Activity Trend -->
        <div class="content-section">
            <h2>7-Day Activity Trend</h2>
            <div class="chart-container">
                <canvas id="activityChart"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Activity trend chart
        const ctx = document.getElementById('activityChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($trend_labels ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']) ?>,
                datasets: [{
                    label: 'Files Uploaded',
                    data: <?= json_encode($trend_files ?? [0, 0, 0, 0, 0, 0, 0]) ?>,
                    backgroundColor: 'rgba(79, 172, 254, 0.8)',
                }, {
                    label: 'Downloads',
                    data: <?= json_encode($trend_downloads ?? [0, 0, 0, 0, 0, 0, 0]) ?>,
                    backgroundColor: 'rgba(0, 242, 254, 0.8)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#e0e0e0'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#e0e0e0' },
                        grid: { color: '#2a2a3e' }
                    },
                    x: {
                        ticks: { color: '#e0e0e0' },
                        grid: { color: '#2a2a3e' }
                    }
                }
            }
        });
    </script>



<?php View::endSection(); ?>
