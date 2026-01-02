<?php
/**
 * ImgTxt Admin - Overview Dashboard
 * Displays OCR statistics, recent jobs, and activity trends
 */
use Core\View;

View::extend('admin');
?>

<?php View::section('content'); ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-chart-line text-primary"></i>
                    <?= $title ?? 'ImgTxt Overview' ?>
                </h1>
                <p class="text-muted mb-0">OCR statistics, recent jobs, and activity trends</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin"><i class="fas fa-home"></i> Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/imgtxt">ImgTxt</a></li>
                    <li class="breadcrumb-item active">Overview</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= number_format($stats['total_jobs'] ?? 0) ?></h3>
                        <p><strong>Total OCR Jobs</strong></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="small-box-footer">
                        <i class="fas fa-arrow-up"></i> <?= $stats['jobs_trend'] ?? '+0' ?>% this week
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $stats['success_rate'] ?? '0' ?>%</h3>
                        <p><strong>Success Rate</strong></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="small-box-footer">
                        <?= ($stats['success_rate'] ?? 0) >= 90 ? '<i class="fas fa-check"></i> Excellent' : '<i class="fas fa-exclamation-triangle"></i> Needs attention' ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= number_format($stats['pending_jobs'] ?? 0) ?></h3>
                        <p><strong>Pending Jobs</strong></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="small-box-footer">
                        In queue
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= number_format($stats['failed_jobs'] ?? 0) ?></h3>
                        <p><strong>Failed Jobs</strong></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="small-box-footer">
                        <?= $stats['failure_rate'] ?? '0' ?>% failure rate
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Jobs -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-2"></i>
                    <strong>Recent OCR Jobs</strong>
                </h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead class="bg-light">
                        <tr>
                            <th><i class="fas fa-hashtag mr-1"></i> Job ID</th>
                            <th><i class="fas fa-file-image mr-1"></i> Filename</th>
                            <th class="text-center"><i class="fas fa-language mr-1"></i> Language</th>
                            <th class="text-center"><i class="fas fa-signal mr-1"></i> Status</th>
                            <th><i class="far fa-calendar mr-1"></i> Created</th>
                            <th class="text-center"><i class="fas fa-clock mr-1"></i> Processing Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_jobs)): ?>
                            <?php foreach ($recent_jobs as $job): 
                                $statusClass = [
                                    'completed' => 'success',
                                    'failed' => 'danger',
                                    'processing' => 'warning',
                                    'pending' => 'info'
                                ][$job['status']] ?? 'secondary';
                            ?>
                                <tr>
                                    <td><span class="badge badge-secondary">#<?= $job['id'] ?></span></td>
                                    <td>
                                        <i class="fas fa-image mr-1 text-primary"></i>
                                        <?= htmlspecialchars($job['filename']) ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-primary"><?= strtoupper($job['language']) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-<?= $statusClass ?>">
                                            <?= ucfirst($job['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y H:i', strtotime($job['created_at'])) ?></td>
                                    <td class="text-center">
                                        <?php if ($job['processing_time']): ?>
                                            <span class="badge badge-info"><?= $job['processing_time'] ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                                    No jobs found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Language Usage -->
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-language mr-2"></i>
                    <strong>Language Usage Statistics</strong>
                </h3>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 300px;">
                    <canvas id="languageChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Activity Trend -->
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-area mr-2"></i>
                    <strong>7-Day Activity Trend</strong>
                </h3>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 300px;">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Language usage chart
const langCtx = document.getElementById('languageChart').getContext('2d');
new Chart(langCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($language_stats ?? ['eng' => 100])) ?>,
        datasets: [{
            data: <?= json_encode(array_values($language_stats ?? [100])) ?>,
            backgroundColor: [
                '#00ff88', '#667eea', '#f5576c', '#ffc107', 
                '#00bcd4', '#9c27b0', '#ff5722', '#795548'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    color: '#212529'
                }
            }
        }
    }
});

// Activity trend chart
const actCtx = document.getElementById('activityChart').getContext('2d');
new Chart(actCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($trend_labels ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']) ?>,
        datasets: [{
            label: 'OCR Jobs',
            data: <?= json_encode($trend_data ?? [0, 0, 0, 0, 0, 0, 0]) ?>,
            borderColor: '#ffffff',
            backgroundColor: 'rgba(255, 255, 255, 0.2)',
            borderWidth: 3,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: '#ffffff'
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { color: '#ffffff' },
                grid: { color: 'rgba(255, 255, 255, 0.1)' }
            },
            x: {
                ticks: { color: '#ffffff' },
                grid: { color: 'rgba(255, 255, 255, 0.1)' }
            }
        }
    }
});
</script>

<?php View::endSection(); ?>
