<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1>Site Settings</h1>
        <p style="color: var(--text-secondary);">Configure platform settings</p>
    </div>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div class="grid grid-3">
    <div style="grid-column: span 2;">
        <div class="card">
            <form method="POST" action="/admin/settings">
                <?= \Core\Security::csrfField() ?>
                
                <div class="form-group">
                    <label class="form-label">Site Name</label>
                    <input type="text" name="site_name" class="form-input" 
                           value="<?= View::e($settings['site_name'] ?? APP_NAME) ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Site Description</label>
                    <textarea name="site_description" class="form-input" rows="3"><?= View::e($settings['site_description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Contact Email</label>
                    <input type="email" name="contact_email" class="form-input" 
                           value="<?= View::e($settings['contact_email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" name="registration_enabled" value="1" 
                               <?= ($settings['registration_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span>Enable User Registration</span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>
    
    <div>
        <div class="card">
            <h4 style="margin-bottom: 15px;">Quick Actions</h4>
            
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <a href="/admin/settings/maintenance" class="btn btn-secondary" style="width: 100%;">
                    <i class="fas fa-tools"></i> Maintenance Mode
                </a>
            </div>
        </div>
        
        <div class="card mt-2">
            <h4 style="margin-bottom: 15px;">System Info</h4>
            
            <div style="font-size: 14px;">
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="color: var(--text-secondary);">PHP Version</span>
                    <span><?= PHP_VERSION ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="color: var(--text-secondary);">Platform Version</span>
                    <span><?= APP_VERSION ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                    <span style="color: var(--text-secondary);">Server</span>
                    <span><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
