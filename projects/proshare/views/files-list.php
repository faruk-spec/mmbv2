<?php use Core\View; ?>
<?php View::extend('proshare:app'); ?>

<?php View::section('content'); ?>

<div class="card mb-3">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">
                <i class="fas fa-folder"></i> My Files
            </h3>
            <a href="/projects/proshare/upload" class="btn btn-primary">
                <i class="fas fa-plus"></i> Upload New File
            </a>
        </div>
    </div>
    
    <?php if (!empty($files)): ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Size</th>
                        <th>Status</th>
                        <th>Downloads</th>
                        <th>Created</th>
                        <th>Expires</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($files as $file): ?>
                        <tr>
                            <td>
                                <i class="fas fa-file" style="color: var(--cyan); margin-right: 8px;"></i>
                                <?= View::e($file['original_name']) ?>
                                <?php if ($file['password']): ?>
                                    <i class="fas fa-lock" style="color: var(--orange); margin-left: 8px;" title="Password protected"></i>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($file['size'] / 1024 / 1024, 2) ?> MB</td>
                            <td>
                                <?php if ($file['status'] === 'active'): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php elseif ($file['status'] === 'expired'): ?>
                                    <span class="badge badge-danger">Expired</span>
                                <?php elseif ($file['status'] === 'deleted'): ?>
                                    <span class="badge badge-warning">Deleted</span>
                                <?php else: ?>
                                    <span class="badge badge-info"><?= ucfirst($file['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="color: var(--text-primary); font-weight: 600;"><?= $file['downloads'] ?></span>
                                <?php if ($file['max_downloads']): ?>
                                    <span class="text-muted">/ <?= $file['max_downloads'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y H:i', strtotime($file['created_at'])) ?></td>
                            <td>
                                <?php if ($file['expires_at']): ?>
                                    <?= date('M d, Y H:i', strtotime($file['expires_at'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">Never</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                    <a href="/projects/proshare/preview/<?= $file['short_code'] ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;" title="View/Preview">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/projects/proshare/download/<?= $file['short_code'] ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;" title="Download" download>
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button onclick="copyLink('<?= $file['short_code'] ?>')" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;" title="Copy Link">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <?php if ($file['status'] === 'active'): ?>
                                        <button onclick="deleteFile('<?= $file['short_code'] ?>')" class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85rem;" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>No Files Yet</h3>
            <p class="text-muted">Upload your first file to start sharing</p>
            <a href="/projects/proshare/upload" class="btn btn-primary mt-2">
                <i class="fas fa-cloud-upload-alt"></i> Upload Files
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- File Statistics -->
<?php if (!empty($files)): ?>
<div class="grid grid-3">
    <div class="stat-card">
        <div class="stat-value" style="color: var(--cyan);">
            <?= count($files) ?>
        </div>
        <div class="stat-label">Total Files</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value" style="color: var(--green);">
            <?= count(array_filter($files, fn($f) => $f['status'] === 'active')) ?>
        </div>
        <div class="stat-label">Active Files</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value" style="color: var(--magenta);">
            <?= number_format(array_sum(array_column($files, 'downloads'))) ?>
        </div>
        <div class="stat-label">Total Downloads</div>
    </div>
</div>
<?php endif; ?>

<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<script>
    function copyLink(shortCode) {
        const link = window.location.origin + '/s/' + shortCode;
        navigator.clipboard.writeText(link).then(() => {
            alert('Link copied to clipboard!');
        });
    }
    
    function deleteFile(shortCode) {
        if (!confirm('Are you sure you want to delete this file? This action cannot be undone.')) {
            return;
        }
        
        fetch('/projects/proshare/files/delete/' + shortCode, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  alert('File deleted successfully');
                  location.reload();
              } else {
                  alert('Error deleting file: ' + (data.error || 'Unknown error'));
              }
          })
          .catch(error => {
              console.error('Error:', error);
              alert('Error deleting file');
          });
    }
</script>
<?php View::endSection(); ?>
