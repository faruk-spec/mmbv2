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
    background: linear-gradient(135deg, var(--cyan), var(--purple));
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
.btn-sm {
    padding: 6px 12px;
    background: var(--purple);
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    transition: var(--transition);
    display: inline-block;
}
.btn-sm:hover {
    opacity: 0.8;
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
    <h1 style="color: var(--text-primary); margin-bottom: 20px;">CodeXPro Overview</h1>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Projects</h3>
            <p class="value"><?= number_format($stats['total_projects'] ?? 0) ?></p>
            <p class="trend">↑ <?= $stats['projects_trend'] ?? '+0' ?>% this week</p>
        </div>
        <div class="stat-card">
            <h3>Total Snippets</h3>
            <p class="value"><?= number_format($stats['total_snippets'] ?? 0) ?></p>
            <p class="trend">↑ <?= $stats['snippets_trend'] ?? '+0' ?>% this week</p>
        </div>
        <div class="stat-card">
            <h3>Active Users</h3>
            <p class="value"><?= number_format($stats['active_users'] ?? 0) ?></p>
            <p class="trend"><?= $stats['users_trend'] ?? '0' ?> this week</p>
        </div>
        <div class="stat-card">
            <h3>Storage Used</h3>
            <p class="value"><?= $stats['storage_used'] ?? '0 MB' ?></p>
            <p class="trend"><?= $stats['storage_percent'] ?? '0' ?>% of limit</p>
        </div>
    </div>

    <!-- Recent Projects -->
    <div class="content-section">
        <h2>Recent Projects</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Project Name</th>
                        <th>Owner</th>
                        <th>Created</th>
                        <th>Last Modified</th>
                        <th>Size</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_projects)): ?>
                        <?php foreach ($recent_projects as $project): ?>
                            <tr>
                                <td><?= htmlspecialchars($project['name']) ?></td>
                                <td><?= htmlspecialchars($project['owner_name'] ?? 'Unknown') ?></td>
                                <td><?= date('M d, Y', strtotime($project['created_at'])) ?></td>
                                <td><?= date('M d, Y', strtotime($project['updated_at'])) ?></td>
                                <td><?= $project['size_formatted'] ?? '0 KB' ?></td>
                                <td>
                                    <a href="/admin/projects/codexpro/project/<?= $project['id'] ?>" class="btn-sm">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-secondary);">No projects found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Activity Chart -->
    <div class="content-section">
        <h2>7-Day Activity Trend</h2>
        <div class="chart-container">
            <canvas id="activityChart"></canvas>
        </div>
    </div>
</div>
<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Activity trend chart
    const ctx = document.getElementById('activityChart');
    if (ctx) {
        const activityChart = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: <?= json_encode($trend_labels ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']) ?>,
                datasets: [{
                    label: 'Projects Created',
                    data: <?= json_encode($trend_data ?? [0, 0, 0, 0, 0, 0, 0]) ?>,
                    borderColor: getComputedStyle(document.documentElement).getPropertyValue('--green'),
                    backgroundColor: 'rgba(0, 255, 136, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: getComputedStyle(document.documentElement).getPropertyValue('--text-primary')
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: getComputedStyle(document.documentElement).getPropertyValue('--text-secondary')
                        },
                        grid: {
                            color: getComputedStyle(document.documentElement).getPropertyValue('--border-color')
                        }
                    },
                    x: {
                        ticks: {
                            color: getComputedStyle(document.documentElement).getPropertyValue('--text-secondary')
                        },
                        grid: {
                            color: getComputedStyle(document.documentElement).getPropertyValue('--border-color')
                        }
                    }
                }
            }
        });
    }
</script>
<?php View::endSection(); ?>
