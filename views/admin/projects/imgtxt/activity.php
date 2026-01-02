<?php
/**
 * ImgTxt Admin - Comprehensive Activity Logs with Full OCR Job Details
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
                <p class="text-muted mb-0">Comprehensive OCR job activity with detailed metadata</p>
                <?php if ($filterUser): ?>
                    <div class="mt-2">
                        <span class="badge badge-info badge-lg">
                            <i class="fas fa-filter mr-1"></i>
                            Filtering by: <?= htmlspecialchars($filterUser['name']) ?> (<?= htmlspecialchars($filterUser['email']) ?>)
                        </span>
                        <a href="/admin/projects/imgtxt/activity" class="btn btn-sm btn-secondary ml-2">
                            <i class="fas fa-times"></i> Clear Filter
                        </a>
                    </div>
                <?php endif; ?>
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
        <!-- Summary Info Boxes -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= number_format($totalCount) ?></h3>
                        <p><strong>Total OCR Jobs</strong></p>
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
                        <p><strong>Jobs on This Page</strong></p>
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
                    <strong>OCR Job Details & Activity</strong>
                </h3>
                <div class="card-tools">
                    <div class="input-group input-group-sm activity-search-box">
                        <input type="text" id="searchLogs" class="form-control float-right" placeholder="Search jobs...">
                        <div class="input-group-append">
                            <button class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0 activity-table-container">
                <?php if (empty($logs)): ?>
                    <div class="alert alert-info m-4">
                        <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                        <strong>No OCR jobs found.</strong>
                    </div>
                <?php else: ?>
                    <table class="table table-head-fixed table-hover text-nowrap">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 80px;"><i class="fas fa-hashtag mr-1"></i> Job ID</th>
                                <th style="width: 200px;"><i class="fas fa-user mr-1"></i> User</th>
                                <th style="width: 180px;"><i class="fas fa-file-image mr-1"></i> File Name</th>
                                <th style="width: 100px;" class="text-center"><i class="fas fa-hdd mr-1"></i> Size</th>
                                <th style="width: 120px;" class="text-center"><i class="fas fa-file-code mr-1"></i> Format</th>
                                <th style="width: 120px;" class="text-center"><i class="fas fa-signal mr-1"></i> Status</th>
                                <th style="width: 100px;" class="text-center"><i class="fas fa-language mr-1"></i> Language</th>
                                <th style="width: 100px;" class="text-center"><i class="fas fa-tachometer-alt mr-1"></i> Confidence</th>
                                <th style="width: 120px;" class="text-center"><i class="fas fa-clock mr-1"></i> Processing</th>
                                <th style="width: 100px;" class="text-center"><i class="fas fa-sort-numeric-up mr-1"></i> Chars</th>
                                <th style="width: 100px;" class="text-center"><i class="fas fa-font mr-1"></i> Words</th>
                                <th style="width: 180px;"><i class="far fa-calendar mr-1"></i> Created</th>
                                <th style="width: 180px;"><i class="far fa-calendar-check mr-1"></i> Completed</th>
                                <th style="width: 150px;" class="text-center"><i class="fas fa-eye mr-1"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody id="logsTableBody">
                            <?php foreach ($logs as $log): 
                                $statusClass = [
                                    'completed' => 'success',
                                    'failed' => 'danger',
                                    'processing' => 'warning',
                                    'pending' => 'info'
                                ][$log['status']] ?? 'secondary';
                                
                                $confidenceClass = ($log['confidence'] ?? 0) >= 80 ? 'success' : (($log['confidence'] ?? 0) >= 50 ? 'warning' : 'danger');
                            ?>
                            <tr class="log-row">
                                <td>
                                    <span class="badge badge-secondary">#<?= $log['id'] ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="mr-2">
                                            <div class="avatar-circle bg-gradient-primary" 
                                                 style="width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">
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
                                    <i class="fas fa-image mr-1 text-primary"></i>
                                    <span title="<?= htmlspecialchars($log['original_filename']) ?>">
                                        <?= htmlspecialchars(mb_substr($log['original_filename'], 0, 25)) ?>
                                        <?= mb_strlen($log['original_filename']) > 25 ? '...' : '' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info"><?= $log['formatted_size'] ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-secondary"><?= htmlspecialchars($log['mime_type']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-<?= $statusClass ?> badge-lg">
                                        <?= ucfirst($log['status']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary"><?= htmlspecialchars($log['language_name'] ?? $log['language']) ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($log['confidence']): ?>
                                        <span class="badge badge-<?= $confidenceClass ?>">
                                            <?= number_format($log['confidence'], 1) ?>%
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($log['processing_time_ms']): ?>
                                        <span class="badge badge-info"><?= number_format($log['processing_time_ms']) ?> ms</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <strong><?= number_format($log['character_count']) ?></strong>
                                </td>
                                <td class="text-center">
                                    <strong><?= number_format($log['word_count']) ?></strong>
                                </td>
                                <td>
                                    <i class="far fa-calendar-alt mr-1 text-info"></i>
                                    <?= date('M d, Y', strtotime($log['created_at'])) ?><br>
                                    <small class="text-muted">
                                        <i class="far fa-clock mr-1"></i>
                                        <?= date('H:i:s', strtotime($log['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if ($log['updated_at']): ?>
                                        <i class="far fa-calendar-check mr-1 text-success"></i>
                                        <?= date('M d, Y', strtotime($log['updated_at'])) ?><br>
                                        <small class="text-muted">
                                            <i class="far fa-clock mr-1"></i>
                                            <?= date('H:i:s', strtotime($log['updated_at'])) ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info" 
                                            onclick="viewJobDetails(<?= htmlspecialchars(json_encode($log), ENT_QUOTES, 'UTF-8') ?>)"
                                            title="View full details">
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
                    <?php if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1<?= $filterUser ? '&user_id=' . $filterUser['id'] : '' ?>">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $currentPage - 1 ?><?= $filterUser ? '&user_id=' . $filterUser['id'] : '' ?>">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php 
                    $start = max(1, $currentPage - 2);
                    $end = min($totalPages, $currentPage + 2);
                    for ($i = $start; $i <= $end; $i++): 
                    ?>
                        <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?><?= $filterUser ? '&user_id=' . $filterUser['id'] : '' ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $currentPage + 1 ?><?= $filterUser ? '&user_id=' . $filterUser['id'] : '' ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $totalPages ?><?= $filterUser ? '&user_id=' . $filterUser['id'] : '' ?>">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="float-left">
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle"></i>
                        Showing <?= count($logs) ?> of <?= number_format($totalCount) ?> jobs
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Job Details Modal -->
<div class="modal fade" id="jobDetailsModal" tabindex="-1" role="dialog" style="display: none;">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    OCR Job Details
                </h5>
                <button type="button" class="close text-white" onclick="closeJobModal()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="fas fa-user mr-2"></i>User Information</h6>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th style="width: 40%;">User ID</th>
                                <td id="modal-user-id"></td>
                            </tr>
                            <tr>
                                <th>User Name</th>
                                <td id="modal-user-name"></td>
                            </tr>
                            <tr>
                                <th>User Email</th>
                                <td id="modal-user-email"></td>
                            </tr>
                        </table>
                        
                        <h6 class="text-primary mt-3"><i class="fas fa-file-image mr-2"></i>File Information</h6>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th style="width: 40%;">File Name</th>
                                <td id="modal-filename"></td>
                            </tr>
                            <tr>
                                <th>File Size</th>
                                <td id="modal-filesize"></td>
                            </tr>
                            <tr>
                                <th>MIME Type</th>
                                <td id="modal-mimetype"></td>
                            </tr>
                            <tr>
                                <th>File Path</th>
                                <td id="modal-filepath" class="text-muted small"></td>
                            </tr>
                        </table>
                        
                        <h6 class="text-primary mt-3"><i class="fas fa-image mr-2"></i>Uploaded Image</h6>
                        <div id="modal-image-container" class="text-center p-3 bg-light rounded">
                            <img id="modal-image" src="" alt="OCR Image" style="max-width: 100%; max-height: 400px; cursor: pointer;" onclick="openImageFullscreen(this.src)">
                            <p id="modal-no-image" class="text-muted mb-0" style="display: none;">
                                <i class="fas fa-times-circle"></i> Image not available
                            </p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="fas fa-cog mr-2"></i>Processing Information</h6>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th style="width: 40%;">Status</th>
                                <td id="modal-status"></td>
                            </tr>
                            <tr>
                                <th>Language</th>
                                <td id="modal-language"></td>
                            </tr>
                            <tr>
                                <th>Confidence Score</th>
                                <td id="modal-confidence"></td>
                            </tr>
                            <tr>
                                <th>Processing Time</th>
                                <td id="modal-processing-time"></td>
                            </tr>
                        </table>
                        
                        <h6 class="text-primary mt-3"><i class="fas fa-chart-bar mr-2"></i>Text Statistics</h6>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th style="width: 40%;">Character Count</th>
                                <td id="modal-char-count"></td>
                            </tr>
                            <tr>
                                <th>Word Count</th>
                                <td id="modal-word-count"></td>
                            </tr>
                        </table>
                        
                        <h6 class="text-primary mt-3"><i class="fas fa-clock mr-2"></i>Timestamps</h6>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th style="width: 40%;">Created At</th>
                                <td id="modal-created"></td>
                            </tr>
                            <tr>
                                <th>Completed At</th>
                                <td id="modal-completed"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <hr>
                
                <h6 class="text-primary"><i class="fas fa-align-left mr-2"></i>Extracted Text</h6>
                <div class="card bg-light">
                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                        <pre id="modal-extracted-text" class="mb-0" style="white-space: pre-wrap; word-wrap: break-word;"></pre>
                    </div>
                </div>
                
                <div id="modal-error-section" style="display: none;">
                    <h6 class="text-danger mt-3"><i class="fas fa-exclamation-triangle mr-2"></i>Error Message</h6>
                    <div class="alert alert-danger" id="modal-error-message"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeJobModal()">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Fullscreen Image Modal -->
<div id="fullscreenImageModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.95); overflow: auto;">
    <span onclick="closeFullscreenImage()" style="position: absolute; top: 20px; right: 35px; color: #f1f1f1; font-size: 40px; font-weight: bold; cursor: pointer;">&times;</span>
    <img id="fullscreenImage" src="" style="margin: auto; display: block; max-width: 95%; max-height: 95%; margin-top: 2%;">
</div>

<script>
// Search functionality
document.getElementById('searchLogs').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('.log-row');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// View job details function
function viewJobDetails(job) {
    // User Information
    document.getElementById('modal-user-id').textContent = job.user_id || 'N/A';
    document.getElementById('modal-user-name').textContent = job.user_name || 'Unknown';
    document.getElementById('modal-user-email').textContent = job.user_email || 'N/A';
    
    // File Information
    document.getElementById('modal-filename').textContent = job.original_filename || 'N/A';
    document.getElementById('modal-filesize').textContent = job.formatted_size || 'N/A';
    document.getElementById('modal-mimetype').textContent = job.mime_type || 'N/A';
    document.getElementById('modal-filepath').textContent = job.file_path || 'N/A';
    
    // Image Display
    const modalImage = document.getElementById('modal-image');
    const modalNoImage = document.getElementById('modal-no-image');
    if (job.file_path) {
        // Sanitize and validate file path to prevent path traversal
        const filePath = job.file_path.replace(/\.\./g, '').replace(/^\/+/, '');
        // Only allow paths starting with expected directories (uploads, storage, etc.)
        if (filePath.startsWith('uploads/') || filePath.startsWith('storage/')) {
            const imageUrl = '/' + filePath;
            modalImage.src = imageUrl;
            modalImage.style.display = 'block';
            modalNoImage.style.display = 'none';
        } else {
            // Invalid path - show no image message
            modalImage.style.display = 'none';
            modalNoImage.style.display = 'block';
        }
    } else {
        modalImage.style.display = 'none';
        modalNoImage.style.display = 'block';
    }
    
    // Processing Information
    const statusBadge = `<span class="badge badge-${getStatusClass(job.status)} badge-lg">${job.status}</span>`;
    document.getElementById('modal-status').innerHTML = statusBadge;
    document.getElementById('modal-language').textContent = job.language_name || job.language || 'N/A';
    
    if (job.confidence) {
        const confClass = job.confidence >= 80 ? 'success' : (job.confidence >= 50 ? 'warning' : 'danger');
        document.getElementById('modal-confidence').innerHTML = `<span class="badge badge-${confClass}">${job.confidence}%</span>`;
    } else {
        document.getElementById('modal-confidence').textContent = 'N/A';
    }
    
    if (job.processing_time_ms) {
        document.getElementById('modal-processing-time').textContent = `${job.processing_time_ms} ms`;
    } else {
        document.getElementById('modal-processing-time').textContent = 'N/A';
    }
    
    // Text Statistics
    document.getElementById('modal-char-count').textContent = (job.character_count || 0).toLocaleString();
    document.getElementById('modal-word-count').textContent = (job.word_count || 0).toLocaleString();
    
    // Timestamps
    document.getElementById('modal-created').textContent = formatDateTime(job.created_at);
    document.getElementById('modal-completed').textContent = job.updated_at ? formatDateTime(job.updated_at) : 'N/A';
    
    // Extracted Text
    document.getElementById('modal-extracted-text').textContent = job.extracted_text || 'No text extracted';
    
    // Error Message
    if (job.error_message) {
        document.getElementById('modal-error-section').style.display = 'block';
        document.getElementById('modal-error-message').textContent = job.error_message;
    } else {
        document.getElementById('modal-error-section').style.display = 'none';
    }
    
    // Show modal (without jQuery)
    showJobModal();
}

function getStatusClass(status) {
    const classes = {
        'completed': 'success',
        'failed': 'danger',
        'processing': 'warning',
        'pending': 'info'
    };
    return classes[status] || 'secondary';
}

function formatDateTime(datetime) {
    if (!datetime) return 'N/A';
    const date = new Date(datetime);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

// Modal control functions (without jQuery)
function showJobModal() {
    const modal = document.getElementById('jobDetailsModal');
    modal.style.display = 'block';
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    // Add backdrop
    if (!document.getElementById('modal-backdrop')) {
        const backdrop = document.createElement('div');
        backdrop.id = 'modal-backdrop';
        backdrop.className = 'modal-backdrop fade show';
        backdrop.onclick = function() { closeJobModal(); };
        document.body.appendChild(backdrop);
    }
}

function closeJobModal() {
    const modal = document.getElementById('jobDetailsModal');
    modal.style.display = 'none';
    modal.classList.remove('show');
    document.body.style.overflow = '';
    
    // Remove backdrop
    const backdrop = document.getElementById('modal-backdrop');
    if (backdrop) {
        backdrop.remove();
    }
}

// Fullscreen image functions
function openImageFullscreen(imageSrc) {
    const modal = document.getElementById('fullscreenImageModal');
    const img = document.getElementById('fullscreenImage');
    modal.style.display = 'block';
    img.src = imageSrc;
}

function closeFullscreenImage() {
    document.getElementById('fullscreenImageModal').style.display = 'none';
}

// Close fullscreen on ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeFullscreenImage();
        closeJobModal();
    }
});

// Initialize image error handler once on page load
document.addEventListener('DOMContentLoaded', function() {
    const modalImage = document.getElementById('modal-image');
    const modalNoImage = document.getElementById('modal-no-image');
    
    // Set error handler once to prevent memory leaks
    modalImage.onerror = function() {
        modalImage.style.display = 'none';
        modalNoImage.style.display = 'block';
    };
});

</script>

<style>
.avatar-circle {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 14px;
}

.badge-lg {
    font-size: 0.9rem;
    padding: 0.35rem 0.6rem;
}

.table-head-fixed thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background-color: #f8f9fa;
}

/* Activity page specific desktop styles */
.activity-search-box {
    width: 300px;
}

.activity-table-container {
    max-height: 800px;
}

/* Modal backdrop and styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1050;
    width: 100%;
    height: 100%;
    overflow-x: hidden;
    overflow-y: auto;
    outline: 0;
}

.modal.show {
    display: block !important;
}

.modal-dialog {
    position: relative;
    width: auto;
    margin: 1.75rem auto;
    pointer-events: none;
}

.modal-xl {
    max-width: 1140px;
}

.modal-content {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    pointer-events: auto;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid rgba(0,0,0,.2);
    border-radius: 0.3rem;
    outline: 0;
    color: #212529;
}

.modal-body {
    color: #212529;
}

.modal-body h6 {
    color: #007bff !important;
}

.modal-body .table {
    color: #212529;
}

.modal-body .table th {
    color: #495057;
    background-color: #f8f9fa;
}

.modal-body .table td {
    color: #212529;
}

.modal-body pre {
    color: #212529;
}

.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1040;
    width: 100vw;
    height: 100vh;
    background-color: #000;
}

.modal-backdrop.show {
    opacity: 0.5;
}

/* Mobile responsive overrides */
@media (max-width: 767px) {
    .activity-search-box {
        width: 100% !important;
    }
    
    .activity-table-container {
        max-height: 600px !important;
    }
    
    .modal-xl {
        max-width: 95%;
    }
}
</style>

<?php View::endSection(); ?>
