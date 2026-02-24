<?php
/**
 * ConvertX Admin — Storage Monitor
 */
use Core\View;
View::extend('admin');
?>

<?php View::section('content'); ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-hdd text-primary"></i> ConvertX — Storage Monitor</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/convertx">ConvertX</a></li>
                    <li class="breadcrumb-item active">Storage</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php
        function fmtBytes(int $bytes): string {
            if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
            if ($bytes >= 1048576)    return number_format($bytes / 1048576, 2)    . ' MB';
            if ($bytes >= 1024)       return number_format($bytes / 1024, 2)       . ' KB';
            return $bytes . ' B';
        }
        ?>
        <!-- Disk usage summary -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= fmtBytes($diskUsed) ?></h3>
                        <p>Upload Storage Used</p>
                    </div>
                    <div class="icon"><i class="fas fa-upload"></i></div>
                    <div class="small-box-footer"><?= number_format($fileCount) ?> uploaded files</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= fmtBytes($outputUsed) ?></h3>
                        <p>Converted Output Storage</p>
                    </div>
                    <div class="icon"><i class="fas fa-download"></i></div>
                    <div class="small-box-footer"><?= number_format($outputCount) ?> output files</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= fmtBytes($diskUsed + $outputUsed) ?></h3>
                        <p>Total Storage Used</p>
                    </div>
                    <div class="icon"><i class="fas fa-hdd"></i></div>
                    <div class="small-box-footer"><?= number_format($fileCount + $outputCount) ?> total files</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3><?= number_format(count($userStats)) ?></h3>
                        <p>Active Converters</p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <div class="small-box-footer">Users with conversion jobs</div>
                </div>
            </div>
        </div>

        <!-- Per-user stats -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users"></i> Per-User Conversion Stats</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr><th>ID</th><th>Name</th><th>Email</th><th>Total Jobs</th><th>Completed</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($userStats)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No conversion activity yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($userStats as $u): ?>
                            <tr>
                                <td><?= (int)$u['id'] ?></td>
                                <td><?= htmlspecialchars($u['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
                                <td><span class="badge badge-info"><?= (int)$u['total_jobs'] ?></span></td>
                                <td><span class="badge badge-success"><?= (int)$u['completed'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
<?php View::endSection(); ?>
