<?php
/**
 * BillX Admin — Overview Dashboard
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
                    <i class="fas fa-file-invoice text-warning"></i>
                    BillX — Overview
                </h1>
                <p class="text-muted mb-0">Bill generation statistics and recent activity</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin"><i class="fas fa-home"></i> Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects">Projects</a></li>
                    <li class="breadcrumb-item active">BillX</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <?php if (!$dbConnected): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>BillX database not connected.</strong>
            <a href="/admin/projects/database-setup/billx" class="alert-link ml-2">Configure database →</a>
        </div>
        <?php endif; ?>

        <!-- Quick Nav -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card card-outline card-warning">
                    <div class="card-body py-2">
                        <a href="/admin/projects/billx" class="btn btn-sm btn-warning mr-1"><i class="fas fa-tachometer-alt"></i> Overview</a>
                        <a href="/admin/projects/billx/bills" class="btn btn-sm btn-outline-secondary mr-1"><i class="fas fa-list"></i> All Bills</a>
                        <a href="/admin/projects/billx/settings" class="btn btn-sm btn-outline-secondary"><i class="fas fa-cog"></i> Settings</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= number_format((int)($stats['total'] ?? 0)) ?></h3>
                        <p>Total Bills</p>
                    </div>
                    <div class="icon"><i class="fas fa-file-invoice"></i></div>
                    <a href="/admin/projects/billx/bills" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= number_format((int)($stats['today'] ?? 0)) ?></h3>
                        <p>Today's Bills</p>
                    </div>
                    <div class="icon"><i class="fas fa-calendar-day"></i></div>
                    <a href="/admin/projects/billx/bills" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= number_format((int)($stats['this_month'] ?? 0)) ?></h3>
                        <p>This Month</p>
                    </div>
                    <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                    <a href="/admin/projects/billx/bills" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3><?= number_format((int)$activeUsers) ?></h3>
                        <p>Active Users</p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <a href="/admin/users" class="small-box-footer">Manage Users <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Bills by Type -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Bills by Type</h3>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($byType)): ?>
                        <p class="text-muted text-center p-3">No data available</p>
                        <?php else: ?>
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Bill Type</th>
                                    <th class="text-right">Count</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($byType as $row): ?>
                                <tr>
                                    <td>
                                        <span class="badge badge-secondary"><?= htmlspecialchars($row['bill_type'] ?? 'unknown') ?></span>
                                    </td>
                                    <td class="text-right"><?= number_format((int)$row['cnt']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Bills -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-clock mr-1"></i> Recent Bills</h3>
                        <div class="card-tools">
                            <a href="/admin/projects/billx/bills" class="btn btn-sm btn-outline-secondary">View All</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentBills)): ?>
                        <p class="text-muted text-center p-3">No bills yet</p>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Type</th>
                                        <th>Bill #</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($recentBills as $bill): ?>
                                <tr>
                                    <td><?= (int)$bill['id'] ?></td>
                                    <td><?= htmlspecialchars($bill['user_name'] ?? $bill['user_email'] ?? 'N/A') ?></td>
                                    <td><span class="badge badge-info"><?= htmlspecialchars($bill['bill_type'] ?? '') ?></span></td>
                                    <td><?= htmlspecialchars($bill['bill_number'] ?? '—') ?></td>
                                    <td><small><?= htmlspecialchars(substr($bill['created_at'] ?? '', 0, 16)) ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
<?php View::endSection(); ?>
