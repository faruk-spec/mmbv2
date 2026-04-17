<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
/* Theme-aware styles */
.admin-content {
    max-width: none;
    width: 100%;
}

.content-header h1 {
    color: var(--text-primary);
    margin-bottom: 10px;
}

.text-muted {
    color: var(--text-secondary) !important;
}

.settings-form, .content-section {
    background: var(--bg-card);
    padding: 30px;
    border-radius: 12px;
    border: 1px solid var(--border-color);
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    color: var(--text-primary);
    font-weight: 500;
    margin-bottom: 8px;
    display: block;
}

.form-group input, .form-group select, .form-group textarea {
    width: 100%;
    padding: 10px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    color: var(--text-primary);
}

.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    outline: none;
    border-color: var(--cyan);
}

.btn-save, .btn-primary {
    background: linear-gradient(135deg, var(--cyan), var(--magenta));
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
}

.btn-save:hover, .btn-primary:hover {
    opacity: 0.9;
}

.alert {
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.alert-success {
    background: rgba(0, 255, 136, 0.1);
    color: var(--green);
    border: 1px solid var(--green);
}

.alert-danger {
    background: rgba(255, 107, 107, 0.1);
    color: var(--red);
    border: 1px solid var(--red);
}
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div class="admin-content">
    <div class="content-header">
        <h1>ProShare Settings</h1>
        <p class="text-muted">Configure security and file sharing settings</p>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?>">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="settings-card">
        <form method="POST" action="/admin/projects/proshare/settings">
            <input type="hidden" name="_csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <!-- File Upload Settings -->
            <div class="settings-section">
                <h3>File Upload Settings</h3>
                <div class="form-group">
                    <label for="max_file_size">Maximum File Size (MB)</label>
                    <input type="number" id="max_file_size" name="max_file_size" 
                           value="<?= $settings['max_file_size'] ?? 100 ?>" 
                           min="1" max="1000" class="form-control">
                    <small class="form-text">Maximum size for file uploads</small>
                </div>

                <div class="form-group">
                    <label for="default_expiry">Default Link Expiry (hours)</label>
                    <select id="default_expiry" name="default_expiry" class="form-control">
                        <option value="1" <?= ($settings['default_expiry'] ?? 24) == 1 ? 'selected' : '' ?>>1 Hour</option>
                        <option value="6" <?= ($settings['default_expiry'] ?? 24) == 6 ? 'selected' : '' ?>>6 Hours</option>
                        <option value="12" <?= ($settings['default_expiry'] ?? 24) == 12 ? 'selected' : '' ?>>12 Hours</option>
                        <option value="24" <?= ($settings['default_expiry'] ?? 24) == 24 ? 'selected' : '' ?>>24 Hours</option>
                        <option value="72" <?= ($settings['default_expiry'] ?? 24) == 72 ? 'selected' : '' ?>>3 Days</option>
                        <option value="168" <?= ($settings['default_expiry'] ?? 24) == 168 ? 'selected' : '' ?>>7 Days</option>
                        <option value="720" <?= ($settings['default_expiry'] ?? 24) == 720 ? 'selected' : '' ?>>30 Days</option>
                    </select>
                </div>
            </div>

            <!-- Security Features -->
            <div class="settings-section">
                <h3>Security Features</h3>
                <div class="form-group">
                    <label class="toggle-label">
                        <input type="checkbox" name="password_protection_enabled" 
                               <?= ($settings['password_protection_enabled'] ?? true) ? 'checked' : '' ?>>
                        <span class="toggle-switch"></span>
                        Password Protection
                    </label>
                    <small class="form-text">Allow users to password-protect their shares</small>
                </div>

                <div class="form-group">
                    <label class="toggle-label">
                        <input type="checkbox" name="self_destruct_enabled" 
                               <?= ($settings['self_destruct_enabled'] ?? true) ? 'checked' : '' ?>>
                        <span class="toggle-switch"></span>
                        Self-Destruct Messages
                    </label>
                    <small class="form-text">Allow files/texts to self-destruct after first view</small>
                </div>

                <div class="form-group">
                    <label class="toggle-label">
                        <input type="checkbox" name="compression_enabled" 
                               <?= ($settings['compression_enabled'] ?? true) ? 'checked' : '' ?>>
                        <span class="toggle-switch"></span>
                        File Compression
                    </label>
                    <small class="form-text">Automatically compress files before storage</small>
                </div>

                <div class="form-group">
                    <label class="toggle-label">
                        <input type="checkbox" name="anonymous_sharing_enabled" 
                               <?= ($settings['anonymous_sharing_enabled'] ?? true) ? 'checked' : '' ?>>
                        <span class="toggle-switch"></span>
                        Anonymous Sharing
                    </label>
                    <small class="form-text">Allow users to share without creating an account</small>
                </div>
            </div>

            <!-- Storage Settings -->
            <div class="settings-section">
                <h3>Storage Settings</h3>
                <div class="form-group">
                    <label for="storage_limit_gb">Storage Limit per User (GB)</label>
                    <input type="number" id="storage_limit_gb" name="storage_limit_gb" 
                           value="<?= $settings['storage_limit_gb'] ?? 5 ?>" 
                           min="1" max="100" class="form-control">
                    <small class="form-text">Maximum storage allowed per user</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Settings</button>
                <a href="/admin/projects/proshare" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.settings-card {
    background: #2d3250;
    border-radius: 12px;
    padding: 30px;
    margin-top: 20px;
}

.settings-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.settings-section:last-of-type {
    border-bottom: none;
}

.settings-section h3 {
    color: #4facfe;
    margin-bottom: 20px;
    font-size: 1.2em;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    color: #e0e0e0;
    margin-bottom: 8px;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    background: #1a1d2e;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    color: #fff;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #4facfe;
    box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1);
}

.form-text {
    display: block;
    margin-top: 5px;
    color: #888;
    font-size: 12px;
}

.toggle-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    color: #e0e0e0;
}

.toggle-label input[type="checkbox"] {
    display: none;
}

.toggle-switch {
    position: relative;
    width: 50px;
    height: 26px;
    background: #1a1d2e;
    border-radius: 13px;
    margin-right: 12px;
    transition: background 0.3s ease;
}

.toggle-switch::before {
    content: '';
    position: absolute;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #fff;
    top: 2px;
    left: 2px;
    transition: transform 0.3s ease;
}

.toggle-label input[type="checkbox"]:checked + .toggle-switch {
    background: #4facfe;
}

.toggle-label input[type="checkbox"]:checked + .toggle-switch::before {
    transform: translateX(24px);
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn {
    padding: 12px 30px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: #fff;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);
}

.btn-secondary {
    background: #2d3250;
    color: #fff;
    border: 1px solid rgba(255,255,255,0.1);
}

.btn-secondary:hover {
    background: #3d4260;
}

@media (max-width: 768px) {
    .settings-card {
        padding: 20px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
}
</style>
<?php View::endSection(); ?>
