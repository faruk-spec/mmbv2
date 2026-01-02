<?php
/**
 * ImgTxt Admin - Statistics & Analytics
 * Professional Dashboard with AdminLTE components
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
        <!-- Overall Statistics - Small Boxes -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= number_format($overallStats['total_jobs']) ?></h3>
                        <p><strong>Total OCR Jobs</strong></p>
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
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= number_format($overallStats['completed_jobs']) ?></h3>
                        <p><strong>Completed Jobs</strong></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="small-box-footer">&nbsp;</div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= number_format($overallStats['failed_jobs']) ?></h3>
                        <p><strong>Failed Jobs</strong></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="small-box-footer">&nbsp;</div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= number_format($overallStats['active_users']) ?></h3>
                        <p><strong>Active Users</strong></p>
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

        <!-- Success/Failure Rates -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="info-box bg-gradient-success">
                    <span class="info-box-icon"><i class="fas fa-thumbs-up"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text"><strong>Success Rate</strong></span>
                        <span class="info-box-number"><?= number_format($overallStats['success_rate'], 2) ?>%</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: <?= $overallStats['success_rate'] ?>%"></div>
                        </div>
                        <span class="progress-description">
                            <?= number_format($overallStats['completed_jobs']) ?> of <?= number_format($overallStats['total_jobs']) ?> completed successfully
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box bg-gradient-warning">
                    <span class="info-box-icon"><i class="fas fa-thumbs-down"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text"><strong>Failure Rate</strong></span>
                        <span class="info-box-number"><?= number_format($overallStats['failure_rate'], 2) ?>%</span>
                        <div class="progress">
                            <div class="progress-bar bg-warning" style="width: <?= $overallStats['failure_rate'] ?>%"></div>
                        </div>
                        <span class="progress-description">
                            <?= number_format($overallStats['failed_jobs']) ?> of <?= number_format($overallStats['total_jobs']) ?> failed
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 30-Day Trends Chart -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-2"></i>
                    <strong>30-Day Activity Trends</strong>
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="trendsChart" style="height: 300px;"></canvas>
            </div>
        </div>

        <div class="row">
            <!-- Language Usage -->
            <div class="col-md-6">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-language mr-2"></i>
                            <strong>Language Usage</strong>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th><i class="fas fa-globe mr-1"></i> Language</th>
                                    <th class="text-center"><i class="fas fa-tasks mr-1"></i> Jobs</th>
                                    <th class="text-center"><i class="fas fa-percentage mr-1"></i> Success</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($languageUsage)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">
                                            No data available
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($languageUsage as $lang): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-info">
                                                <?= strtoupper($lang['language']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <strong><?= number_format($lang['count']) ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-<?= $lang['completion_rate'] >= 80 ? 'success' : ($lang['completion_rate'] >= 50 ? 'warning' : 'danger') ?>">
                                                <?= number_format($lang['completion_rate'], 1) ?>%
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top Users -->
            <div class="col-md-6">
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-trophy mr-2"></i>
                            <strong>Top 10 Users by Jobs</strong>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th><i class="fas fa-user mr-1"></i> User</th>
                                    <th class="text-center"><i class="fas fa-tasks mr-1"></i> Jobs</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($topUsers)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">
                                            No data available
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $rank = 1;
                                    foreach ($topUsers as $user): 
                                        $medal = $rank === 1 ? '🏆' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : ''));
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                            <?php if ($medal): ?>
                                                <span style="font-size: 1.5em;"><?= $medal ?></span>
                                            <?php else: ?>
                                                <span class="text-muted"><?= $rank ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="mr-2">
                                                    <div class="avatar-circle bg-gradient-primary" 
                                                         style="width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">
                                                        <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                                                    </div>
                                                </div>
                                                <div>
                                                    <strong><?= htmlspecialchars($user['name'] ?? 'Unknown') ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-primary badge-lg">
                                                <?= number_format($user['job_count']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php 
                                    $rank++;
                                    endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Processing Time -->
        <div class="row">
            <div class="col-md-12">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock mr-2"></i>
                            <strong>Processing Performance</strong>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="description-block border-right">
                                    <h5 class="description-header text-info">
                                        <i class="fas fa-hourglass-half"></i>
                                        <?= number_format($avgProcessingTime ?? 0, 2) ?>s
                                    </h5>
                                    <span class="description-text">Average Processing Time</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="description-block border-right">
                                    <h5 class="description-header text-success">
                                        <i class="fas fa-file-alt"></i>
                                        <?= number_format($overallStats['total_jobs']) ?>
                                    </h5>
                                    <span class="description-text">Total Documents Processed</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="description-block">
                                    <h5 class="description-header text-warning">
                                        <i class="fas fa-bolt"></i>
                                        <?= number_format($overallStats['total_jobs'] / max(30, 1)) ?>
                                    </h5>
                                    <span class="description-text">Avg Jobs per Day (30d)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 30-Day Trends Chart
    const trendsData = <?= json_encode($dailyTrends ?? []) ?>;
    const labels = trendsData.map(d => d.date);
    const totalData = trendsData.map(d => d.total);
    const completedData = trendsData.map(d => d.completed);
    const failedData = trendsData.map(d => d.failed);
    
    const ctx = document.getElementById('trendsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Jobs',
                        data: totalData,
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Completed',
                        data: completedData,
                        borderColor: 'rgb(40, 167, 69)',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Failed',
                        data: failedData,
                        borderColor: 'rgb(220, 53, 69)',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
});
</script>

<style>
.badge-lg {
    font-size: 0.9rem !important;
    padding: 0.4em 0.8em !important;
}
.avatar-circle {
    flex-shrink: 0;
}
</style>

<?php View::endSection(); ?>
