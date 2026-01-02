<?php
/**
 * ImgTxt Admin - Activity Logs
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
        <!-- Summary Info -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-list-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Activity Logs</span>
                        <span class="info-box-number"><?= number_format($totalCount) ?></span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">
                            Showing page <?= $currentPage ?> of <?= $totalPages ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Logs Table -->
        <div class="card card-primary card-outline">
            <div class="card-header border-0">
                <h3 class="card-title">
                    <i class="fas fa-history mr-2"></i>
                    Recent Activity
                </h3>
                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" id="searchLogs" class="form-control" placeholder="Search logs...">
                        <div class="input-group-append">
                            <button class="btn btn-default"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <?php if (empty($logs)): ?>
                    <div class="alert alert-info m-3">
                        <i class="fas fa-info-circle"></i>
                        No activity logs found.
                    </div>
                <?php else: ?>
                    <table class="table table-hover table-striped" id="logsTable">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 60px;" class="text-center">#</th>
                                <th style="width: 180px;"><i class="far fa-clock mr-1"></i> Timestamp</th>
                                <th><i class="fas fa-user mr-1"></i> User</th>
                                <th style="width: 200px;"><i class="fas fa-bolt mr-1"></i> Action</th>
                                <th><i class="fas fa-info-circle mr-1"></i> Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td class="text-center">
                                        <small class="text-muted"><?= htmlspecialchars($log['id']) ?></small>
                                    </td>
                                    <td>
                                        <div class="text-sm">
                                            <i class="far fa-calendar-alt mr-1"></i>
                                            <?= date('M d, Y', strtotime($log['created_at'])) ?>
                                            <br>
                                            <i class="far fa-clock mr-1"></i>
                                            <?= date('H:i:s', strtotime($log['created_at'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar bg-success text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 30px; height: 30px; font-size: 12px; font-weight: bold;">
                                                <?= strtoupper(substr($log['user_name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars($log['user_name']) ?></strong>
                                                <?php if (!empty($log['email'])): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($log['email']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        // Format action name
                                        $actionName = str_replace('imgtxt_', '', $log['action']);
                                        $actionName = ucwords(str_replace('_', ' ', $actionName));
                                        
                                        // Badge color and icon based on action
                                        $badgeClass = 'secondary';
                                        $icon = 'fa-cog';
                                        if (strpos($log['action'], 'delete') !== false) {
                                            $badgeClass = 'danger';
                                            $icon = 'fa-trash';
                                        } elseif (strpos($log['action'], 'update') !== false) {
                                            $badgeClass = 'warning';
                                            $icon = 'fa-edit';
                                        } elseif (strpos($log['action'], 'create') !== false || strpos($log['action'], 'upload') !== false) {
                                            $badgeClass = 'success';
                                            $icon = 'fa-plus-circle';
                                        } elseif (strpos($log['action'], 'view') !== false || strpos($log['action'], 'read') !== false) {
                                            $badgeClass = 'info';
                                            $icon = 'fa-eye';
                                        }
                                        ?>
                                        <span class="badge badge-<?= $badgeClass ?> badge-lg">
                                            <i class="fas <?= $icon ?> mr-1"></i>
                                            <?= htmlspecialchars($actionName) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($log['metadata_decoded'])): ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#detailsModal<?= $log['id'] ?>">
                                                <i class="fas fa-eye"></i> View Details
                                            </button>
                                            
                                            <!-- Details Modal -->
                                            <div class="modal fade" id="detailsModal<?= $log['id'] ?>" tabindex="-1" role="dialog">
                                                <div class="modal-dialog modal-lg" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-primary">
                                                            <h5 class="modal-title">
                                                                <i class="fas fa-info-circle"></i> Activity Details #<?= $log['id'] ?>
                                                            </h5>
                                                            <button type="button" class="close text-white" data-dismiss="modal">
                                                                <span>&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <h6><strong>Action:</strong> <?= htmlspecialchars($actionName) ?></h6>
                                                            <h6><strong>User:</strong> <?= htmlspecialchars($log['user_name']) ?></h6>
                                                            <h6><strong>Time:</strong> <?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?></h6>
                                                            <hr>
                                                            <h6><strong>Metadata:</strong></h6>
                                                            <pre class="p-3 bg-light border rounded" style="max-height: 400px; overflow-y: auto;"><?= htmlspecialchars(json_encode($log['metadata_decoded'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="fas fa-minus"></i> No additional details
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <?php if ($totalPages > 1): ?>
                <div class="card-footer clearfix">
                    <div class="row align-items-center">
                        <div class="col-sm-6">
                            <div class="dataTables_info">
                                Showing page <?= $currentPage ?> of <?= $totalPages ?> 
                                (<?= number_format($totalCount) ?> total logs)
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <ul class="pagination pagination-sm m-0 float-right">
                                <?php if ($currentPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=1">
                                            <i class="fas fa-angle-double-left"></i>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $currentPage - 1 ?>">
                                            <i class="fas fa-angle-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($totalPages, $currentPage + 2);
                                
                                for ($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                    <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $currentPage + 1 ?>">
                                            <i class="fas fa-angle-right"></i>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $totalPages ?>">
                                            <i class="fas fa-angle-double-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
.badge-lg {
    padding: 0.4em 0.6em;
    font-size: 0.85em;
}
</style>

<script>
// Search functionality
document.getElementById('searchLogs')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const table = document.getElementById('logsTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    }
});
</script>

<?php View::endSection(); ?>
