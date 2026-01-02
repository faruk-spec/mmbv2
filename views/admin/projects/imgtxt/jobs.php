<?php
/**
 * ImgTxt Admin - Jobs Monitoring
 * Monitor and manage OCR processing jobs
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
                    <i class="fas fa-tasks text-primary"></i>
                    <?= $title ?? 'OCR Jobs Monitoring' ?>
                </h1>
                <p class="text-muted mb-0">Monitor and manage OCR processing jobs</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin"><i class="fas fa-home"></i> Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/imgtxt">ImgTxt</a></li>
                    <li class="breadcrumb-item active">Jobs</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filters -->
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter mr-2"></i>
                    Filters
                </h3>
            </div>
            <div class="card-body">
                <form method="GET" class="row">
                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label for="status">Filter by Status</label>
                            <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="completed" <?= ($filters['status'] ?? '') == 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="failed" <?= ($filters['status'] ?? '') == 'failed' ? 'selected' : '' ?>>Failed</option>
                                <option value="pending" <?= ($filters['status'] ?? '') == 'pending' ? 'selected' : '' ?>>Pending</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Jobs Table -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>
                    <strong>OCR Jobs</strong>
                </h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 80px;"><i class="fas fa-hashtag mr-1"></i> ID</th>
                            <th><i class="fas fa-file-image mr-1"></i> File Name</th>
                            <th class="text-center"><i class="fas fa-language mr-1"></i> Language</th>
                            <th class="text-center"><i class="fas fa-signal mr-1"></i> Status</th>
                            <th><i class="far fa-calendar mr-1"></i> Created</th>
                            <th class="text-center"><i class="fas fa-clock mr-1"></i> Duration</th>
                            <th class="text-center"><i class="fas fa-cog mr-1"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($jobs)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                                    No OCR jobs found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($jobs as $job): 
                                $statusClass = [
                                    'completed' => 'success',
                                    'failed' => 'danger',
                                    'processing' => 'warning',
                                    'pending' => 'info'
                                ][$job['status']] ?? 'secondary';
                            ?>
                                <tr style="cursor: pointer;" onclick="viewJobDetails(<?= $job['id'] ?>)" class="job-row">
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
                                            <span class="badge badge-info"><?= $job['processing_time'] ?>s</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center" onclick="event.stopPropagation();">
                                        <?php if ($job['status'] == 'failed'): ?>
                                            <button onclick="retryJob(<?= $job['id'] ?>)" class="btn btn-sm btn-warning" title="Retry">
                                                <i class="fas fa-redo"></i> Retry
                                            </button>
                                        <?php endif; ?>
                                        <button onclick="deleteJob(<?= $job['id'] ?>)" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if (isset($total_pages) && $total_pages > 1): ?>
            <div class="card-footer clearfix">
                <ul class="pagination pagination-sm m-0 float-right">
                    <?php 
                    $currentPageNum = isset($current_page) ? (int)$current_page : 1;
                    for ($i = 1; $i <= $total_pages; $i++): 
                    ?>
                        <li class="page-item <?= $currentPageNum == $i ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?><?= isset($filters['status']) ? '&status=' . htmlspecialchars($filters['status']) : '' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
.job-row:hover {
    background-color: #f8f9fa;
}
</style>

<script>
function viewJobDetails(jobId) {
    // Navigate to activity page with job filter - admin can view full details there
    window.location.href = `/admin/projects/imgtxt/activity?job_id=${jobId}`;
}

function retryJob(jobId) {
    if (confirm('Retry this OCR job?')) {
        const formData = new FormData();
        formData.append('job_id', jobId);
        formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
        
        fetch('/admin/projects/imgtxt/jobs/retry', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            location.reload();
        });
    }
}

function deleteJob(jobId) {
    if (confirm('Delete this OCR job? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('job_id', jobId);
        formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
        
        fetch('/admin/projects/imgtxt/jobs/delete', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            location.reload();
        });
    }
}
</script>

<?php View::endSection(); ?>
