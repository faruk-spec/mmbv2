<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('admin/layout'); ?>

<?php View::section('content'); ?>
<div class="admin-content">
    <div class="page-header">
        <h1>Edit OAuth Provider</h1>
        <p>Configure <?= View::e($provider['display_name']) ?> OAuth settings</p>
    </div>
    
    <?php if (Helpers::hasFlash('error')): ?>
        <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
    <?php endif; ?>
    
    <div class="card">
        <form method="POST" action="/admin/oauth/<?= $provider['id'] ?>/edit">
            <?= Security::csrfField() ?>
            
            <div class="form-group">
                <label class="form-label">Provider Name</label>
                <input type="text" class="form-input" value="<?= View::e($provider['display_name']) ?>" disabled>
                <small class="form-help">Provider name cannot be changed</small>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="client_id">Client ID *</label>
                <input type="text" id="client_id" name="client_id" class="form-input" 
                       value="<?= View::e($provider['client_id'] ?? '') ?>" required>
                <small class="form-help">Obtain this from your <?= View::e($provider['display_name']) ?> developer console</small>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="client_secret">Client Secret *</label>
                <input type="password" id="client_secret" name="client_secret" class="form-input" 
                       value="<?= View::e($provider['client_secret'] ?? '') ?>" required>
                <small class="form-help">Keep this secret and secure</small>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="redirect_uri">Redirect URI</label>
                <input type="url" id="redirect_uri" name="redirect_uri" class="form-input" 
                       value="<?= View::e($provider['redirect_uri'] ?? '') ?>" 
                       placeholder="<?= View::e(defined('APP_URL') ? APP_URL : 'https://yourdomain.com') ?>/auth/google/callback">
                <small class="form-help">Leave blank to use default: <?= View::e(defined('APP_URL') ? APP_URL : 'https://yourdomain.com') ?>/auth/google/callback</small>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="scopes">OAuth Scopes</label>
                <input type="text" id="scopes" name="scopes" class="form-input" 
                       value="<?= View::e($provider['scopes'] ?? 'openid email profile') ?>">
                <small class="form-help">Space-separated list of OAuth scopes</small>
            </div>
            
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="is_enabled" <?= $provider['is_enabled'] ? 'checked' : '' ?>>
                    <span>Enable this OAuth provider</span>
                </label>
            </div>
            
            <div class="alert alert-info" style="margin-top: 20px;">
                <h4>Setup Instructions for Google OAuth:</h4>
                <ol>
                    <li>Go to <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a></li>
                    <li>Create a new project or select existing one</li>
                    <li>Navigate to APIs & Services → Credentials</li>
                    <li>Go to Credentials → Create Credentials → OAuth 2.0 Client ID</li>
                    <li>Add authorized redirect URI: <code><?= View::e(defined('APP_URL') ? APP_URL : 'https://yourdomain.com') ?>/auth/google/callback</code></li>
                    <li>Copy Client ID and Client Secret and paste them above</li>
                </ol>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="/admin/oauth" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php View::endSection(); ?>
