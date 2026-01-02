<?php
/**
 * ImgTxt Admin - Activity Logs
 * Professional Audit Trail with AdminLTE components
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
                    <i class="fas fa-stream text-primary"></i>
                    <?= $title ?? 'Activity Logs' ?>
                </h1>
                <p class="text-muted mb-0">Complete audit trail of all ImgTxt actions</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin"><i class="fas fa-home"></i> Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/imgtxt">ImgTxt</a></li>
                    <li class="breadcrumb-item active">Activity Logs</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Summary Info Box -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= number_format($totalCount) ?></h3>
                        <p><strong>Total Activity Logs</strong></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <div class="small-box-footer">
                        Showing page <?= $currentPage ?> of <?= $totalPages ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= count($logs) ?></h3>
                        <p><strong>Logs on This Page</strong></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="small-box-footer">&nbsp;</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $perPage ?></h3>
                        <p><strong>Per Page Limit</strong></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="small-box-footer">&nbsp;</div>
                </div>
            </div>
        </div>

        <!-- Activity Logs Table Card -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-2"></i>
                    <strong>Recent Activity</strong>
                </h3>
                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 300px;">
                        <input type="text" id="searchLogs" class="form-control float-right" placeholder="Search logs...">
                        <div class="input-group-append">
                            <button class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0" style="max-height: 700px;">
                <?php if (empty($logs)): ?>
                    <div class="alert alert-info m-4">
                        <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                        <strong>No activity logs found.</strong>
                    </div>
                <?php else: ?>
                    <table class="table table-head-fixed table-hover text-nowrap">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 60px;" class="text-center">#</th>
                                <th style="width: 180px;"><i class="far fa-clock mr-1"></i> Timestamp</th>
                                <th style="width: 250px;"><i class="fas fa-user mr-1"></i> User</th>
                                <th style="width: 180px;"><i class="fas fa-bolt mr-1"></i> Action</th>
                                <th><i class="fas fa-info-circle mr-1"></i> Details</th>
                                <th style="width: 100px;" class="text-center"><i class="fas fa-cog mr-1"></i> View</th>
                            </tr>
                        </thead>
                        <tbody id="logsTableBody">
                            <?php 
                            $counter = ($currentPage - 1) * $perPage + 1;
                            foreach ($logs as $log): 
                                $actionType = strtoupper($log['action']);
                                $badgeClass = 'secondary';
                                $iconClass = 'fa-circle';
                                
                                if (strpos($actionType, 'CREATE') !== false || strpos($actionType, 'UPLOAD') !== false) {
                                    $badgeClass = 'primary';
                                    $iconClass = 'fa-plus-circle';
                                } elseif (strpos($actionType, 'UPDATE') !== false || strpos($actionType, 'EDIT') !== false) {
                                    $badgeClass = 'warning';
                                    $iconClass = 'fa-edit';
                                } elseif (strpos($actionType, 'DELETE') !== false || strpos($actionType, 'REMOVE') !== false) {
                                    $badgeClass = 'danger';
                                    $iconClass = 'fa-trash';
                                } elseif (strpos($actionType, 'VIEW') !== false || strpos($actionType, 'READ') !== false) {
                                    $badgeClass = 'info';
                                    $iconClass = 'fa-eye';
                                } elseif (strpos($actionType, 'SUCCESS') !== false) {
                                    $badgeClass = 'success';
                                    $iconClass = 'fa-check-circle';
                                }
                            ?>
                            <tr class="log-row">
                                <td class="text-center text-muted"><?= $counter++ ?></td>
                                <td>
                                    <i class="far fa-calendar mr-1"></i>
                                    <?= date('M d, Y', strtotime($log['created_at'])) ?><br>
                                    <small class="text-muted">
                                        <i class="far fa-clock mr-1"></i>
                                        <?= date('H:i:s', strtotime($log['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="mr-2">
                                            <div class="avatar-circle bg-gradient-<?= ['primary', 'info', 'success'][array_rand(['primary', 'info', 'success'])] ?>" 
                                                 style="width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;">
                                                <?= strtoupper(substr($log['user_name'] ?? 'U', 0, 1)) ?>
                                            </div>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($log['user_name'] ?? 'Unknown') ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($log['user_email'] ?? 'N/A') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $badgeClass ?> badge-lg">
                                        <i class="fas <?= $iconClass ?> mr-1"></i>
                                        <?= htmlspecialchars($log['action']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        <?= htmlspecialchars(substr($log['details'] ?? 'No details', 0, 100)) ?>
                                        <?php if (strlen($log['details'] ?? '') > 100): ?>...<?php endif; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#logModal<?= $log['id'] ?>">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="card-footer clearfix">
                <ul class="pagination pagination-sm m-0 float-right">
                    <!-- First Page -->
                    <?php if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Previous Page -->
                    <?php if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $currentPage - 1 ?>">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Page Numbers -->
                    <?php 
                    $start = max(1, $currentPage - 2);
                    $end = min($totalPages, $currentPage + 2);
                    for ($i = $start; $i <= $end; $i++): 
                    ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <!-- Next Page -->
                    <?php if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $currentPage + 1 ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Last Page -->
                    <?php if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $totalPages ?>">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="float-left">
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle"></i>
                        Showing <?= min($totalCount, ($currentPage - 1) * $perPage + 1) ?> to <?= min($totalCount, $currentPage * $perPage) ?> of <?= number_format($totalCount) ?> logs
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Modals for Log Details -->
<?php foreach ($logs as $log): ?>
<div class="modal fade" id="logModal<?= $log['id'] ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    Activity Log Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-hashtag mr-2"></i>Log ID:</strong><br><?= htmlspecialchars($log['id']) ?></p>
                        <p><strong><i class="fas fa-user mr-2"></i>User:</strong><br><?= htmlspecialchars($log['user_name'] ?? 'Unknown') ?><br>
                           <small class="text-muted"><?= htmlspecialchars($log['user_email'] ?? 'N/A') ?></small></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-bolt mr-2"></i>Action:</strong><br><?= htmlspecialchars($log['action']) ?></p>
                        <p><strong><i class="far fa-clock mr-2"></i>Timestamp:</strong><br><?= date('F d, Y H:i:s', strtotime($log['created_at'])) ?></p>
                    </div>
                </div>
                <hr>
                <p><strong><i class="fas fa-align-left mr-2"></i>Details:</strong></p>
                <div class="alert alert-info">
                    <?= nl2br(htmlspecialchars($log['details'] ?? 'No details available')) ?>
                </div>
                <?php if (!empty($log['metadata'])): ?>
                <p><strong><i class="fas fa-code mr-2"></i>Metadata:</strong></p>
                <pre class="bg-light p-3 rounded"><code><?= htmlspecialchars(json_encode(json_decode($log['metadata']), JSON_PRETTY_PRINT)) ?></code></pre>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script>
// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchLogs');
    const tableBody = document.getElementById('logsTableBody');
    const rows = tableBody ? tableBody.getElementsByClassName('log-row') : [];
    
    if (searchInput && rows.length > 0) {
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
