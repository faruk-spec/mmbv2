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
                                <?php elseif ($file['status'] === 'inactive'): ?>
                                    <span class="badge badge-warning">Inactive</span>
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
                                    <button onclick="ecoQrOpen('<?= View::e((defined('APP_URL') ? APP_URL : '') . '/projects/proshare/preview/' . $file['short_code']) ?>')" class="btn btn-secondary" style="padding: 6px 10px; font-size: 0.8rem; color:#00f0ff;" title="Generate QR">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                    <button onclick="openPasswordModal('<?= View::e($file['short_code']) ?>', <?= $file['password'] ? 'true' : 'false' ?>)" class="btn btn-secondary" style="padding: 6px 10px; font-size: 0.8rem;" title="Password Settings">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <button onclick="toggleStatus('<?= View::e($file['short_code']) ?>', '<?= View::e($file['status']) ?>', this)" class="btn <?= $file['status'] === 'active' ? 'btn-warning' : 'btn-success' ?>" style="padding: 6px 10px; font-size: 0.8rem;" title="<?= $file['status'] === 'active' ? 'Deactivate' : 'Activate' ?>">
                                        <i class="fas <?= $file['status'] === 'active' ? 'fa-pause' : 'fa-play' ?>"></i>
                                    </button>
                                    <?php if ($file['status'] === 'active' || $file['status'] === 'inactive'): ?>
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
                                    <button onclick="ecoQrOpen('<?= View::e((defined('APP_URL') ? APP_URL : '') . '/t/' . $text['short_code']) ?>')" class="btn btn-secondary" style="padding: 6px 10px; font-size: 0.8rem; color:#00f0ff;" title="Generate QR">
                                        <i class="fas fa-qrcode"></i>
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
    
    const _csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    
    function deleteFile(shortCode, btn) {
        if (!confirm('Delete this file? This cannot be undone.')) return;
        btn.disabled = true;
        const fd = new FormData();
        if (_csrfToken) fd.append('_csrf_token', _csrfToken);
        
        fetch('/projects/proshare/files/delete/' + shortCode, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: fd
        })
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
        const fd = new FormData();
        if (_csrfToken) fd.append('_csrf_token', _csrfToken);
        
        fetch('/projects/proshare/text/delete/' + shortCode, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: fd
        })
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

    // Toggle active / inactive status
    function toggleStatus(shortCode, currentStatus, btn) {
        const isActive = currentStatus === 'active';
        const msg = isActive ? 'Deactivate this file?' : 'Activate this file?';
        if (!confirm(msg)) return;
        btn.disabled = true;
        const fd = new FormData();
        fd.append('action', 'toggle_status');
        if (_csrfToken) fd.append('_csrf_token', _csrfToken);

        fetch('/projects/proshare/files/update/' + shortCode, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: fd
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const newStatus = data.status;
                    // Update badge
                    const row = btn.closest('tr');
                    const badge = row.querySelector('.badge');
                    if (badge) {
                        badge.className = 'badge ' + (newStatus === 'active' ? 'badge-success' : 'badge-warning');
                        badge.textContent = newStatus === 'active' ? 'Active' : 'Inactive';
                    }
                    // Update button
                    if (newStatus === 'active') {
                        btn.className = btn.className.replace('btn-success', 'btn-warning');
                        btn.innerHTML = '<i class="fas fa-pause"></i>';
                        btn.title = 'Deactivate';
                        btn.onclick = () => toggleStatus(shortCode, 'active', btn);
                    } else {
                        btn.className = btn.className.replace('btn-warning', 'btn-success');
                        btn.innerHTML = '<i class="fas fa-play"></i>';
                        btn.title = 'Activate';
                        btn.onclick = () => toggleStatus(shortCode, 'inactive', btn);
                    }
                    btn.disabled = false;
                    showToast(newStatus === 'active' ? 'File activated.' : 'File deactivated.');
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                    btn.disabled = false;
                }
            })
            .catch(() => { alert('Error updating file'); btn.disabled = false; });
    }

    // Password modal
    let _pwShortCode = null;
    function openPasswordModal(shortCode, hasPassword) {
        _pwShortCode = shortCode;
        document.getElementById('pwEnableCheck').checked = hasPassword;
        document.getElementById('pwInput').value = '';
        document.getElementById('pwFields').style.display = hasPassword ? 'block' : 'none';
        document.getElementById('passwordModal').style.display = 'flex';
    }
    function closePasswordModal() {
        document.getElementById('passwordModal').style.display = 'none';
    }
    document.addEventListener('DOMContentLoaded', () => {
        const pwCheck = document.getElementById('pwEnableCheck');
        if (pwCheck) {
            pwCheck.addEventListener('change', () => {
                document.getElementById('pwFields').style.display = pwCheck.checked ? 'block' : 'none';
            });
        }
        document.getElementById('pwSaveBtn')?.addEventListener('click', () => {
            const enable   = document.getElementById('pwEnableCheck').checked;
            const password = document.getElementById('pwInput').value;
            const fd = new FormData();
            fd.append('action', 'update_password');
            fd.append('enable_password', enable ? '1' : '0');
            if (enable) fd.append('password', password);
            if (_csrfToken) fd.append('_csrf_token', _csrfToken);

            fetch('/projects/proshare/files/update/' + _pwShortCode, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: fd
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        closePasswordModal();
                        showToast(data.password_enabled ? 'Password protection enabled.' : 'Password protection removed.');
                        // Update lock icon in table
                        const rows = document.querySelectorAll('tbody tr');
                        rows.forEach(row => {
                            const keyBtn = row.querySelector('button[title="Password Settings"]');
                            if (keyBtn && keyBtn.getAttribute('onclick')?.includes("'" + _pwShortCode + "'")) {
                                const lockIcon = row.querySelector('.fa-lock');
                                if (data.password_enabled && !lockIcon) {
                                    const nameCell = row.querySelector('td:first-child');
                                    nameCell?.insertAdjacentHTML('beforeend', ' <i class="fas fa-lock" style="color: var(--orange); margin-left: 6px; font-size: 0.8rem;" title="Password protected"></i>');
                                } else if (!data.password_enabled && lockIcon) {
                                    lockIcon.remove();
                                }
                            }
                        });
                    } else {
                        alert('Error: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(() => alert('Error updating password'));
        });
    });
</script>

<!-- Password Modal -->
<div id="passwordModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9998; align-items:center; justify-content:center;">
    <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:28px; width:100%; max-width:420px; box-shadow:0 8px 40px rgba(0,0,0,0.5);">
        <h3 style="margin:0 0 1.25rem; color:var(--text-primary); font-size:1.05rem;">
            <i class="fas fa-key" style="color:var(--cyan); margin-right:8px;"></i> Password Settings
        </h3>
        <label style="display:flex; align-items:center; gap:10px; cursor:pointer; margin-bottom:1rem;">
            <input type="checkbox" id="pwEnableCheck" style="width:16px;height:16px;accent-color:var(--cyan);">
            <span>Enable password protection</span>
        </label>
        <div id="pwFields" style="display:none; margin-bottom:1.25rem;">
            <label style="display:block; margin-bottom:6px; font-size:0.875rem; color:var(--text-muted);">New Password</label>
            <input type="password" id="pwInput" placeholder="Enter new password" style="width:100%; padding:10px 14px; background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; color:var(--text-primary); font-size:0.9rem;">
        </div>
        <div style="display:flex; gap:10px; justify-content:flex-end;">
            <button onclick="closePasswordModal()" class="btn btn-secondary" style="padding:8px 18px;">Cancel</button>
            <button id="pwSaveBtn" class="btn btn-primary" style="padding:8px 18px;">Save</button>
        </div>
    </div>
</div>

<?php View::endSection(); ?>

<!-- Include QR Modal -->
<?php if (file_exists(BASE_PATH . '/views/partials/eco-qr-modal.php')): ?>
    <?php require BASE_PATH . '/views/partials/eco-qr-modal.php'; ?>
<?php endif; ?>
