<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Abuse Reports</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/mail">Mail Server</a></li>
                    <li class="breadcrumb-item active">Abuse Reports</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $stats['pending'] ?? 0 ?></h3>
                        <p>Pending Reports</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $stats['investigating'] ?? 0 ?></h3>
                        <p>Under Investigation</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $stats['resolved'] ?? 0 ?></h3>
                        <p>Resolved</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3><?= $stats['dismissed'] ?? 0 ?></h3>
                        <p>Dismissed</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#pending">
                            Pending <span class="badge badge-warning"><?= $stats['pending'] ?? 0 ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#investigating">
                            Investigating <span class="badge badge-info"><?= $stats['investigating'] ?? 0 ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#resolved">
                            Resolved <span class="badge badge-success"><?= $stats['resolved'] ?? 0 ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#dismissed">
                            Dismissed <span class="badge badge-secondary"><?= $stats['dismissed'] ?? 0 ?></span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Pending Reports -->
                    <div class="tab-pane fade show active" id="pending">
                        <?php 
                        $pendingReports = array_filter($reports ?? [], fn($r) => $r['status'] === 'pending');
                        if (empty($pendingReports)): 
                        ?>
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                <h4>No Pending Reports</h4>
                                <p class="text-muted">All abuse reports have been reviewed.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($pendingReports as $report): ?>
                                <?php include __DIR__ . '/_abuse_report_card.php'; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Investigating Reports -->
                    <div class="tab-pane fade" id="investigating">
                        <?php 
                        $investigatingReports = array_filter($reports ?? [], fn($r) => $r['status'] === 'investigating');
                        if (empty($investigatingReports)): 
                        ?>
                            <div class="text-center py-5">
                                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                                <h4>No Reports Under Investigation</h4>
                            </div>
                        <?php else: ?>
                            <?php foreach ($investigatingReports as $report): ?>
                                <?php include __DIR__ . '/_abuse_report_card.php'; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Resolved Reports -->
                    <div class="tab-pane fade" id="resolved">
                        <?php 
                        $resolvedReports = array_filter($reports ?? [], fn($r) => $r['status'] === 'resolved');
                        if (empty($resolvedReports)): 
                        ?>
                            <div class="text-center py-5">
                                <i class="fas fa-check fa-4x text-muted mb-3"></i>
                                <h4>No Resolved Reports</h4>
                            </div>
                        <?php else: ?>
                            <?php foreach ($resolvedReports as $report): ?>
                                <?php include __DIR__ . '/_abuse_report_card.php'; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Dismissed Reports -->
                    <div class="tab-pane fade" id="dismissed">
                        <?php 
                        $dismissedReports = array_filter($reports ?? [], fn($r) => $r['status'] === 'dismissed');
                        if (empty($dismissedReports)): 
                        ?>
                            <div class="text-center py-5">
                                <i class="fas fa-times fa-4x text-muted mb-3"></i>
                                <h4>No Dismissed Reports</h4>
                            </div>
                        <?php else: ?>
                            <?php foreach ($dismissedReports as $report): ?>
                                <?php include __DIR__ . '/_abuse_report_card.php'; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php View::endSection(); ?>
