<?php use Core\View; use Core\Security; ?>
<?php View::extend('proshare:app'); ?>

<?php View::section('content'); ?>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-cog"></i> Settings
        </h3>
    </div>
    
    <form method="POST" action="/projects/proshare/settings">
        <input type="hidden" name="csrf_token" value="<?= Security::generateToken() ?>">
        
        <!-- Notifications -->
        <div style="padding: 24px; border-bottom: 1px solid var(--border-color);">
            <h4 style="margin-bottom: 20px; color: var(--text-primary);">
                <i class="fas fa-bell"></i> Notifications
            </h4>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="email_notifications" value="1" <?= ($settings['email_notifications'] ?? 1) ? 'checked' : '' ?>>
                    <span>Email notifications for downloads and expiry warnings</span>
                </label>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="sms_notifications" value="1" <?= ($settings['sms_notifications'] ?? 0) ? 'checked' : '' ?>>
                    <span>SMS notifications (if configured)</span>
                </label>
            </div>
        </div>
        
        <!-- Default Upload Settings -->
        <div style="padding: 24px; border-bottom: 1px solid var(--border-color);">
            <h4 style="margin-bottom: 20px; color: var(--text-primary);">
                <i class="fas fa-cloud-upload-alt"></i> Default Upload Settings
            </h4>
            
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Default Link Expiry</label>
                    <select name="default_expiry" class="form-control">
                        <option value="1" <?= ($settings['default_expiry'] ?? 24) == 1 ? 'selected' : '' ?>>1 Hour</option>
                        <option value="6" <?= ($settings['default_expiry'] ?? 24) == 6 ? 'selected' : '' ?>>6 Hours</option>
                        <option value="24" <?= ($settings['default_expiry'] ?? 24) == 24 ? 'selected' : '' ?>>24 Hours</option>
                        <option value="168" <?= ($settings['default_expiry'] ?? 24) == 168 ? 'selected' : '' ?>>7 Days</option>
                        <option value="720" <?= ($settings['default_expiry'] ?? 24) == 720 ? 'selected' : '' ?>>30 Days</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Maximum File Size (MB)</label>
                    <select name="max_file_size" class="form-control">
                        <option value="52428800" <?= ($settings['max_file_size'] ?? 524288000) == 52428800 ? 'selected' : '' ?>>50 MB</option>
                        <option value="104857600" <?= ($settings['max_file_size'] ?? 524288000) == 104857600 ? 'selected' : '' ?>>100 MB</option>
                        <option value="209715200" <?= ($settings['max_file_size'] ?? 524288000) == 209715200 ? 'selected' : '' ?>>200 MB</option>
                        <option value="524288000" <?= ($settings['max_file_size'] ?? 524288000) == 524288000 ? 'selected' : '' ?>>500 MB</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="enable_compression" value="1" <?= ($settings['enable_compression'] ?? 1) ? 'checked' : '' ?>>
                    <span>Enable compression by default</span>
                </label>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="enable_encryption" value="1" <?= ($settings['enable_encryption'] ?? 0) ? 'checked' : '' ?>>
                    <span>Enable encryption by default</span>
                </label>
            </div>
        </div>
        
        <!-- Privacy & Security -->
        <div style="padding: 24px; border-bottom: 1px solid var(--border-color);">
            <h4 style="margin-bottom: 20px; color: var(--text-primary);">
                <i class="fas fa-shield-alt"></i> Privacy & Security
            </h4>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="auto_delete" value="1" <?= ($settings['auto_delete'] ?? 0) ? 'checked' : '' ?>>
                    <span>Automatically delete expired files</span>
                </label>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Note:</strong> All files are protected with industry-standard security measures including password hashing, integrity verification, and access logging.
            </div>
        </div>
        
        <!-- Save Button -->
        <div style="padding: 24px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Settings
            </button>
            <a href="/projects/proshare/dashboard" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<!-- Account Statistics -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-chart-bar"></i> Account Statistics
        </h3>
    </div>
    
    <div class="grid grid-4">
        <div class="stat-card">
            <div class="stat-value" style="color: var(--cyan);">
                <?= $stats['total_files'] ?? 0 ?>
            </div>
            <div class="stat-label">Total Files</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value" style="color: var(--magenta);">
                <?= $stats['total_texts'] ?? 0 ?>
            </div>
            <div class="stat-label">Text Shares</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value" style="color: var(--green);">
                <?= $stats['total_downloads'] ?? 0 ?>
            </div>
            <div class="stat-label">Total Downloads</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value" style="color: var(--orange);">
                <?= $stats['storage_used'] ?? '0' ?> MB
            </div>
            <div class="stat-label">Storage Used</div>
        </div>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/projects/proshare/settings', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Settings saved successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to save settings'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving settings');
    });
});
</script>

<?php View::endSection(); ?>
