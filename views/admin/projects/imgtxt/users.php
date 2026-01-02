<?php
/**
 * ImgTxt Admin - User Management
 * Professional UI with AdminLTE components
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
                    <i class="fas fa-users text-primary"></i>
                    <?= $title ?? 'User Management' ?>
                </h1>
                <p class="text-muted mb-0">Manage ImgTxt users and view their statistics</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin"><i class="fas fa-home"></i> Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/imgtxt">ImgTxt</a></li>
                    <li class="breadcrumb-item active">Users</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Summary Info Boxes with Gradient -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= number_format(count($users)) ?></h3>
                        <p><strong>Total Users</strong></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="small-box-footer">&nbsp;</div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= number_format($activeUsersCount) ?></h3>
                        <p><strong>Active Users</strong></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="small-box-footer">&nbsp;</div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= number_format($totalJobsAll) ?></h3>
                        <p><strong>Total Jobs</strong></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="small-box-footer">&nbsp;</div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= number_format($avgSuccessRate, 1) ?>%</h3>
                        <p><strong>Avg Success Rate</strong></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="small-box-footer">&nbsp;</div>
                </div>
            </div>
        </div>

        <!-- User Table Card -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users mr-2"></i>
                    <strong>All ImgTxt Users</strong>
                </h3>
                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 300px;">
                        <input type="text" id="searchUsers" class="form-control float-right" placeholder="Search users...">
                        <div class="input-group-append">
                            <button class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0" style="max-height: 600px;">
                <table class="table table-head-fixed table-hover text-nowrap">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 60px;" class="text-center">#</th>
                            <th style="width: 250px;"><i class="fas fa-user mr-1"></i> User</th>
                            <th style="width: 120px;" class="text-center"><i class="fas fa-tasks mr-1"></i> Total Jobs</th>
                            <th style="width: 120px;" class="text-center"><i class="fas fa-check-circle mr-1"></i> Completed</th>
                            <th style="width: 120px;" class="text-center"><i class="fas fa-times-circle mr-1"></i> Failed</th>
                            <th style="width: 130px;" class="text-center"><i class="fas fa-percentage mr-1"></i> Success Rate</th>
                            <th style="width: 180px;"><i class="far fa-clock mr-1"></i> Last Activity</th>
                            <th style="width: 120px;" class="text-center"><i class="fas fa-signal mr-1"></i> Status</th>
                            <th style="width: 200px;" class="text-center"><i class="fas fa-cog mr-1"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                                    No users found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $counter = 1; foreach ($users as $user): 
                                $isActive = (strtotime($user['last_activity'] ?? 'now') > strtotime('-30 days'));
                                $successRate = $user['total_jobs'] > 0 ? ($user['completed_jobs'] / $user['total_jobs']) * 100 : 0;
                                $rateClass = $successRate >= 80 ? 'success' : ($successRate >= 50 ? 'warning' : 'danger');
                            ?>
                            <tr class="user-row">
                                <td class="text-center text-muted"><?= $counter++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <div class="avatar-circle bg-gradient-<?= ['primary', 'info', 'success', 'warning', 'danger'][array_rand(['primary', 'info', 'success', 'warning', 'danger'])] ?>" 
                                                 style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px;">
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
                                    <span class="badge badge-info badge-lg"><?= number_format($user['total_jobs']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success badge-lg"><?= number_format($user['completed_jobs']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-danger badge-lg"><?= number_format($user['failed_jobs']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-<?= $rateClass ?> badge-lg">
                                        <?= number_format($successRate, 1) ?>%
                                    </span>
                                </td>
                                <td>
                                    <i class="far fa-clock mr-1"></i>
                                    <?php if ($user['last_activity']): ?>
                                        <?= date('M d, Y H:i', strtotime($user['last_activity'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Never</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($isActive): ?>
                                        <span class="badge badge-success badge-lg">
                                            <i class="fas fa-circle fa-pulse mr-1"></i> Active
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary badge-lg">
                                            <i class="fas fa-circle mr-1"></i> Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="/admin/projects/imgtxt/activity?user_id=<?= $user['id'] ?>" 
                                           class="btn btn-sm btn-info" 
                                           title="View user activity">
                                            <i class="fas fa-stream"></i> Activity
                                        </a>
                                        <a href="/admin/projects/imgtxt/jobs?user_id=<?= $user['id'] ?>" 
                                           class="btn btn-sm btn-primary" 
                                           title="View user jobs">
                                            <i class="fas fa-tasks"></i> Jobs
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                <div class="float-left">
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle"></i>
                        Showing <?= count($users) ?> users total
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchUsers');
    const tableBody = document.getElementById('userTableBody');
    const rows = tableBody.getElementsByClassName('user-row');
    
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const text = row.textContent || row.innerText;
                
                if (text.toLowerCase().indexOf(filter) > -1) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
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
