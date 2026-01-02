<?php
/**
 * ImgTxt Admin - Statistics & Analytics
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
                    <i class="fas fa-chart-line text-info"></i>
                    <?= $title ?? 'Statistics & Analytics' ?>
                </h1>
                <p class="text-muted mb-0">Comprehensive ImgTxt analytics and insights</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin"><i class="fas fa-home"></i> Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/imgtxt">ImgTxt</a></li>
                    <li class="breadcrumb-item active">Statistics</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Overall Statistics -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-gradient-info">
                    <div class="inner">
                        <h3><?= number_format($overallStats['total_jobs']) ?></h3>
                        <p>Total OCR Jobs</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-image"></i>
                    </div>
                    <a href="/admin/projects/imgtxt/jobs" class="small-box-footer">
                        View all jobs <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-gradient-success">
                    <div class="inner">
                        <h3><?= number_format($overallStats['completed_jobs']) ?></h3>
                        <p>Completed Jobs</p>
                        <div class="progress progress-xs">
                            <div class="progress-bar bg-light" style="width: <?= $overallStats['success_rate'] ?>%"></div>
                        </div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <a href="/admin/projects/imgtxt/jobs?status=completed" class="small-box-footer">
                        <?= number_format($overallStats['success_rate'], 1) ?>% success rate <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-gradient-danger">
                    <div class="inner">
                        <h3><?= number_format($overallStats['failed_jobs']) ?></h3>
                        <p>Failed Jobs</p>
                        <div class="progress progress-xs">
                            <div class="progress-bar bg-light" style="width: <?= $overallStats['failure_rate'] ?>%"></div>
                        </div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <a href="/admin/projects/imgtxt/jobs?status=failed" class="small-box-footer">
                        <?= number_format($overallStats['failure_rate'], 1) ?>% failure rate <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-gradient-warning">
                    <div class="inner">
                        <h3><?= number_format($overallStats['active_users']) ?></h3>
                        <p>Active Users (30d)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="/admin/projects/imgtxt/users" class="small-box-footer">
                        View users <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card card-success card-outline">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-2"></i>
                            Success & Failure Rates
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 text-center border-right">
                                <div class="description-block">
                                    <h2 class="text-success font-weight-bold"><?= number_format($overallStats['success_rate'], 1) ?>%</h2>
                                    <p class="text-muted mb-0">Success Rate</p>
                                    <span class="badge badge-success">
                                        <i class="fas fa-arrow-up"></i> Completed: <?= number_format($overallStats['completed_jobs']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="description-block">
                                    <h2 class="text-danger font-weight-bold"><?= number_format($overallStats['failure_rate'], 1) ?>%</h2>
                                    <p class="text-muted mb-0">Failure Rate</p>
                                    <span class="badge badge-danger">
                                        <i class="fas fa-arrow-down"></i> Failed: <?= number_format($overallStats['failed_jobs']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card card-info card-outline">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-tachometer-alt mr-2"></i>
                            Average Processing Time
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <div class="display-4 text-info font-weight-bold">
                            <i class="fas fa-stopwatch"></i>
                            <?= number_format($avgProcessingTime, 2) ?>s
                        </div>
                        <p class="text-muted mt-2">Per completed OCR job</p>
                        <div class="row mt-3">
                            <div class="col-12">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Based on <?= number_format($overallStats['completed_jobs']) ?> completed jobs
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 30-Day Trend -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-primary card-outline">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-chart-area mr-2"></i>
                            30-Day Activity Trend
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-primary">Last 30 Days</span>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="trendChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Language Usage & Hourly Distribution -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card card-warning card-outline">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-language mr-2"></i>
                            Language Usage Statistics
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th><i class="fas fa-globe mr-1"></i> Language</th>
                                    <th class="text-center"><i class="fas fa-tasks mr-1"></i> Total</th>
                                    <th class="text-center"><i class="fas fa-check mr-1"></i> Completed</th>
                                    <th class="text-center"><i class="fas fa-percent mr-1"></i> Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($languageStats as $lang): ?>
                                    <?php 
                                    $rate = $lang['count'] > 0 ? round(($lang['completed'] / $lang['count']) * 100, 1) : 0;
                                    $badgeClass = $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger');
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars(strtoupper($lang['language'])) ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?= number_format($lang['count']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-success"><?= number_format($lang['completed']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-<?= $badgeClass ?>"><?= $rate ?>%</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($languageStats)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">
                                            <i class="fas fa-info-circle"></i> No data available
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card card-danger card-outline">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-clock mr-2"></i>
                            24-Hour Activity Distribution
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="hourlyChart" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Users -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-success card-outline">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-trophy mr-2 text-warning"></i>
                            Top 10 Users by Job Count
                        </h3>
                        <div class="card-tools">
                            <a href="/admin/projects/imgtxt/users" class="btn btn-tool btn-sm">
                                <i class="fas fa-external-link-alt"></i> View All Users
                            </a>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 60px;" class="text-center"><i class="fas fa-medal"></i> Rank</th>
                                    <th><i class="fas fa-user mr-1"></i> User</th>
                                    <th><i class="fas fa-envelope mr-1"></i> Email</th>
                                    <th class="text-center"><i class="fas fa-tasks mr-1"></i> Job Count</th>
                                    <th class="text-center"><i class="fas fa-chart-bar mr-1"></i> Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topUsers as $index => $user): ?>
                                    <?php 
                                    $maxJobs = !empty($topUsers) ? $topUsers[0]['job_count'] : 1;
                                    $percentage = ($user['job_count'] / $maxJobs) * 100;
                                    $medalClass = $index < 3 ? ['text-warning', 'text-secondary', 'text-orange'][$index] : 'text-muted';
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                            <span class="font-weight-bold <?= $medalClass ?>">
                                                <?php if ($index === 0): ?>
                                                    <i class="fas fa-trophy"></i>
                                                <?php elseif ($index === 1): ?>
                                                    <i class="fas fa-medal"></i>
                                                <?php elseif ($index === 2): ?>
                                                    <i class="fas fa-award"></i>
                                                <?php else: ?>
                                                    <?= $index + 1 ?>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 30px; height: 30px; font-size: 12px; font-weight: bold;">
                                                    <?= strtoupper(substr($user['user_name'], 0, 1)) ?>
                                                </div>
                                                <strong><?= htmlspecialchars($user['user_name']) ?></strong>
                                            </div>
                                        </td>
                                        <td><small><?= htmlspecialchars($user['email']) ?></small></td>
                                        <td class="text-center">
                                            <span class="badge badge-primary badge-lg"><?= number_format($user['job_count']) ?></span>
                                        </td>
                                        <td>
                                            <div class="progress progress-xs">
                                                <div class="progress-bar bg-primary" style="width: <?= $percentage ?>%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($topUsers)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">
                                            <i class="fas fa-info-circle"></i> No users found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// 30-Day Trend Chart
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($dailyStats, 'date')) ?>,
        datasets: [{
            label: 'Total Jobs',
            data: <?= json_encode(array_column($dailyStats, 'total')) ?>,
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            tension: 0.3
        }, {
            label: 'Completed',
            data: <?= json_encode(array_column($dailyStats, 'completed')) ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.3
        }, {
            label: 'Failed',
            data: <?= json_encode(array_column($dailyStats, 'failed')) ?>,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Hourly Distribution Chart
const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
new Chart(hourlyCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($hourlyStats, 'hour')) ?>,
        datasets: [{
            label: 'Job Count',
            data: <?= json_encode(array_column($hourlyStats, 'count')) ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.6)',
            borderColor: 'rgb(75, 192, 192)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php View::endSection(); ?>
