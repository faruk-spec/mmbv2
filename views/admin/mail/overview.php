<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<!-- Mail Server Overview Dashboard -->
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Total Subscribers -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="small-box bg-gradient-primary">
                <div class="inner">
                    <h3><?= $stats['total_subscribers'] ?? 0 ?></h3>
                    <p>Total Subscribers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="/admin/projects/mail/subscribers" class="small-box-footer">
                    View All <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Active Subscriptions -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="small-box bg-gradient-success">
                <div class="inner">
                    <h3><?= $stats['active_subscriptions'] ?? 0 ?></h3>
                    <p>Active Subscriptions</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="/admin/projects/mail/subscribers" class="small-box-footer">
                    Manage <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Total Domains -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="small-box bg-gradient-info">
                <div class="inner">
                    <h3><?= $stats['verified_domains'] ?? 0 ?> / <?= $stats['total_domains'] ?? 0 ?></h3>
                    <p>Verified Domains</p>
                </div>
                <div class="icon">
                    <i class="fas fa-globe"></i>
                </div>
                <a href="/admin/projects/mail/domains" class="small-box-footer">
                    View Domains <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Total Mailboxes -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="small-box bg-gradient-warning">
                <div class="inner">
                    <h3><?= $stats['active_mailboxes'] ?? 0 ?> / <?= $stats['total_mailboxes'] ?? 0 ?></h3>
                    <p>Active Mailboxes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <a href="/admin/projects/mail/subscribers" class="small-box-footer">
                    View All <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Email Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="info-box bg-gradient-cyan">
                <span class="info-box-icon"><i class="fas fa-paper-plane"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Emails Today</span>
                    <span class="info-box-number"><?= number_format($stats['emails_today'] ?? 0) ?></span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="info-box bg-gradient-purple">
                <span class="info-box-icon"><i class="fas fa-envelope"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Emails This Month</span>
                    <span class="info-box-number"><?= number_format($stats['emails_this_month'] ?? 0) ?></span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="info-box bg-gradient-success">
                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Revenue This Month</span>
                    <span class="info-box-number">$<?= number_format($stats['revenue_this_month'] ?? 0, 2) ?></span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="info-box bg-gradient-danger">
                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending Abuse Reports</span>
                    <span class="info-box-number"><?= $stats['pending_abuse_reports'] ?? 0 ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Plan Distribution & Recent Activity -->
    <div class="row">
        <!-- Plan Distribution Chart -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Subscription Plan Distribution
                    </h3>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="planDistributionChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <?php foreach ($planDistribution as $plan): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge badge-<?= getPlanBadgeColor($plan['plan_name']) ?> px-3 py-2">
                                <?= View::e($plan['plan_name']) ?>
                            </span>
                            <strong><?= $plan['count'] ?? 0 ?> subscribers</strong>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Subscribers -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-plus mr-2"></i>
                        Recent Subscribers
                    </h3>
                    <div class="card-tools">
                        <a href="/admin/projects/mail/subscribers" class="btn btn-tool">
                            <i class="fas fa-list"></i> View All
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Account Name</th>
                                <th>Plan</th>
                                <th>Status</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentSubscribers)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p>No subscribers yet</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($recentSubscribers as $subscriber): ?>
                                <tr>
                                    <td>
                                        <a href="/admin/projects/mail/subscribers/<?= $subscriber['id'] ?>">
                                            <strong><?= View::e($subscriber['account_name']) ?></strong>
                                        </a>
                                        <br>
                                        <small class="text-muted"><?= View::e($subscriber['email']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= getPlanBadgeColor($subscriber['plan_name']) ?>">
                                            <?= View::e($subscriber['plan_name']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= getStatusBadgeColor($subscriber['status']) ?>">
                                            <?= ucfirst($subscriber['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?= date('M d, Y', strtotime($subscriber['created_at'])) ?></small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Abuse Reports -->
    <?php if (!empty($recentAbuse)): ?>
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card card-danger card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Pending Abuse Reports
                    </h3>
                    <div class="card-tools">
                        <a href="/admin/projects/mail/abuse" class="btn btn-tool btn-sm">
                            <i class="fas fa-list"></i> View All Reports
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Target</th>
                                <th>Description</th>
                                <th>Reported</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentAbuse as $report): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-warning">
                                        <?= ucfirst($report['report_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($report['mailbox_email']): ?>
                                        <i class="fas fa-envelope"></i> <?= View::e($report['mailbox_email']) ?>
                                    <?php elseif ($report['domain_name']): ?>
                                        <i class="fas fa-globe"></i> <?= View::e($report['domain_name']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= View::e(substr($report['report_description'], 0, 80)) ?>
                                    <?= strlen($report['report_description']) > 80 ? '...' : '' ?>
                                </td>
                                <td>
                                    <small><?= date('M d, Y H:i', strtotime($report['created_at'])) ?></small>
                                </td>
                                <td>
                                    <a href="/admin/projects/mail/abuse?report=<?= $report['id'] ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Review
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Chart.js for Plan Distribution Chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Plan Distribution Pie Chart
const ctx = document.getElementById('planDistributionChart');
if (ctx) {
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: [<?php echo implode(',', array_map(function($p) { return "'" . addslashes($p['plan_name']) . "'"; }, $planDistribution)); ?>],
            datasets: [{
                data: [<?php echo implode(',', array_column($planDistribution, 'count')); ?>],
                backgroundColor: [
                    'rgba(0, 240, 255, 0.8)',   // Cyan - Free
                    'rgba(153, 69, 255, 0.8)',  // Purple - Starter
                    'rgba(255, 170, 0, 0.8)',   // Orange - Business
                    'rgba(0, 255, 136, 0.8)'    // Green - Developer
                ],
                borderColor: [
                    'rgba(0, 240, 255, 1)',
                    'rgba(153, 69, 255, 1)',
                    'rgba(255, 170, 0, 1)',
                    'rgba(0, 255, 136, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#e8eefc',
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });
}
</script>

<?php View::endSection(); ?>

<?php
// Helper functions for badge colors
function getPlanBadgeColor($planName) {
    $colors = [
        'Free' => 'secondary',
        'Starter' => 'info',
        'Business' => 'warning',
        'Developer' => 'success'
    ];
    return $colors[$planName] ?? 'primary';
}

function getStatusBadgeColor($status) {
    $colors = [
        'active' => 'success',
        'suspended' => 'danger',
        'cancelled' => 'secondary',
        'grace_period' => 'warning'
    ];
    return $colors[$status] ?? 'info';
}
?>
