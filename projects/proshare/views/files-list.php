<?php use Core\View; ?>
<?php View::extend('proshare:app'); ?>

<?php View::section('content'); ?>

<!-- Files Table -->
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-folder"></i> My Files
        </h3>
        <a href="/projects/proshare/upload" class="btn btn-primary" style="padding: 6px 14px; font-size: 0.8rem;">
            <i class="fas fa-plus"></i> Upload File
        </a>
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
                                    <i class="fas fa-lock" style="color: var(--orange); margin-left: 6px; font-size: 0.8rem;" title="Password protected"></i>
                                <?php endif; ?>
                                <?php if ($file['self_destruct']): ?>
                                    <i class="fas fa-fire" style="color: var(--ps-danger); margin-left: 4px; font-size: 0.8rem;" title="Self-destruct enabled"></i>
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
                                    <a href="/projects/proshare/preview/<?= $file['short_code'] ?>" class="btn btn-secondary" style="padding: 6px 10px; font-size: 0.8rem;" title="Preview">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button onclick="copyLink('<?= View::e($file['short_code']) ?>')" class="btn btn-secondary" style="padding: 6px 10px; font-size: 0.8rem;" title="Copy Link">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <?php if ($file['status'] === 'active'): ?>
                                        <button onclick="deleteFile('<?= View::e($file['short_code']) ?>', this)" class="btn btn-danger" style="padding: 6px 10px; font-size: 0.8rem;" title="Delete">
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

<!-- Text Shares Table -->
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-file-alt"></i> My Text Shares
        </h3>
        <a href="/projects/proshare/text" class="btn btn-primary" style="padding: 6px 14px; font-size: 0.8rem;">
            <i class="fas fa-plus"></i> New Text Share
        </a>
    </div>
    
    <?php if (!empty($texts)): ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Created</th>
                        <th>Expires</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($texts as $text): ?>
                        <tr>
                            <td>
                                <i class="fas fa-file-alt" style="color: var(--magenta); margin-right: 8px;"></i>
                                <?= View::e($text['title'] ?: 'Untitled') ?>
                                <?php if ($text['password']): ?>
                                    <i class="fas fa-lock" style="color: var(--orange); margin-left: 6px; font-size: 0.8rem;" title="Password protected"></i>
                                <?php endif; ?>
                                <?php if ($text['self_destruct']): ?>
                                    <i class="fas fa-fire" style="color: var(--ps-danger); margin-left: 4px; font-size: 0.8rem;" title="Self-destruct enabled"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($text['status'] === 'active'): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php elseif ($text['status'] === 'expired'): ?>
                                    <span class="badge badge-danger">Expired</span>
                                <?php elseif ($text['status'] === 'deleted'): ?>
                                    <span class="badge badge-warning">Deleted</span>
                                <?php else: ?>
                                    <span class="badge badge-info"><?= ucfirst($text['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="color: var(--text-primary); font-weight: 600;"><?= $text['views'] ?></span>
                                <?php if ($text['max_views']): ?>
                                    <span class="text-muted">/ <?= $text['max_views'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y H:i', strtotime($text['created_at'])) ?></td>
                            <td>
                                <?php if ($text['expires_at']): ?>
                                    <?= date('M d, Y H:i', strtotime($text['expires_at'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">Never</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                    <a href="/t/<?= View::e($text['short_code']) ?>" target="_blank" class="btn btn-secondary" style="padding: 6px 10px; font-size: 0.8rem;" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button onclick="copyTextLink('<?= View::e($text['short_code']) ?>', this)" class="btn btn-secondary" style="padding: 6px 10px; font-size: 0.8rem;" title="Copy Link">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <?php if ($text['status'] === 'active'): ?>
                                        <button onclick="deleteText('<?= View::e($text['short_code']) ?>', this)" class="btn btn-danger" style="padding: 6px 10px; font-size: 0.8rem;" title="Delete">
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
            <i class="fas fa-file-alt"></i>
            <h3>No Text Shares Yet</h3>
            <p class="text-muted">Share text, code, or notes securely</p>
            <a href="/projects/proshare/text" class="btn btn-primary mt-2">
                <i class="fas fa-plus"></i> Create Text Share
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Summary Statistics -->
<?php if (!empty($files) || !empty($texts)): ?>
<div class="ps-grid ps-grid-4">
    <div class="stat-card">
        <div class="stat-value" style="color: var(--cyan);"><?= count($files ?? []) ?></div>
        <div class="stat-label">Total Files</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--green);"><?= count(array_filter($files ?? [], fn($f) => $f['status'] === 'active')) ?></div>
        <div class="stat-label">Active Files</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--magenta);"><?= count($texts ?? []) ?></div>
        <div class="stat-label">Text Shares</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--orange);"><?= number_format(array_sum(array_column($files ?? [], 'downloads'))) ?></div>
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
            showToast('File link copied!');
        });
    }
    
    function copyTextLink(shortCode, btn) {
        const link = window.location.origin + '/t/' + shortCode;
        navigator.clipboard.writeText(link).then(() => {
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(() => { btn.innerHTML = orig; }, 2000);
        });
    }
    
    function deleteFile(shortCode, btn) {
        if (!confirm('Delete this file? This cannot be undone.')) return;
        btn.disabled = true;
        
        fetch('/projects/proshare/files/delete/' + shortCode, { method: 'POST' })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    btn.closest('tr').remove();
                    showToast('File deleted.');
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                    btn.disabled = false;
                }
            })
            .catch(() => { alert('Error deleting file'); btn.disabled = false; });
    }
    
    function deleteText(shortCode, btn) {
        if (!confirm('Delete this text share? This cannot be undone.')) return;
        btn.disabled = true;
        
        fetch('/projects/proshare/text/delete/' + shortCode, { method: 'POST' })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    btn.closest('tr').remove();
                    showToast('Text share deleted.');
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                    btn.disabled = false;
                }
            })
            .catch(() => { alert('Error deleting text share'); btn.disabled = false; });
    }
    
    function showToast(msg) {
        const t = document.createElement('div');
        t.textContent = msg;
        t.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;background:var(--bg-card);border:1px solid var(--border-color);color:var(--text-primary);padding:10px 18px;border-radius:8px;font-size:0.875rem;z-index:9999;box-shadow:0 4px 16px rgba(0,0,0,0.3);';
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 3000);
    }
</script>
<?php View::endSection(); ?>
