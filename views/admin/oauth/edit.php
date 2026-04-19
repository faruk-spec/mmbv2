<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('admin'); ?>

<?php
$providerName = strtolower($provider['name'] ?? '');
$defaultRedirect = (defined('APP_URL') ? APP_URL : 'https://yourdomain.com') . '/auth/' . $providerName . '/callback';
$providerDocs = [
    'google' => [
        'title' => 'Google OAuth',
        'url' => 'https://console.cloud.google.com/',
        'steps' => [
            'Go to Google Cloud Console',
            'Create a project or select an existing one',
            'Navigate to APIs & Services → Credentials',
            'Create an OAuth 2.0 Client ID',
            'Add the authorized redirect URI shown below',
            'Copy Client ID and Client Secret into this form'
        ]
    ],
    'github' => [
        'title' => 'GitHub OAuth',
        'url' => 'https://github.com/settings/developers',
        'steps' => [
            'Open GitHub Developer Settings',
            'Create a new OAuth App',
            'Set Homepage URL to your app domain',
            'Set Authorization callback URL to the redirect URI shown below',
            'Copy Client ID and Client Secret into this form',
            'Enable provider after credentials are saved'
        ]
    ],
    'apple' => [
        'title' => 'Apple Sign In',
        'url' => 'https://developer.apple.com/account/resources/identifiers/list',
        'steps' => [
            'Open Apple Developer Account and create/choose an Identifier',
            'Enable Sign in with Apple for the identifier',
            'Create a Services ID and configure return URLs',
            'Add the redirect URI shown below in the Return URLs list',
            'Create a client secret/JWT and store it in Client Secret',
            'Save credentials and enable provider'
        ]
    ],
    'microsoft' => [
        'title' => 'Microsoft OAuth (Entra ID / Azure AD)',
        'url' => 'https://portal.azure.com/#view/Microsoft_AAD_RegisteredApps/ApplicationsListBlade',
        'steps' => [
            'Go to Azure Portal → Microsoft Entra ID → App registrations',
            'Click "New registration"',
            'Set the Name and select Supported account types (Multitenant recommended)',
            'Under Redirect URI select "Web" and add the redirect URI shown below',
            'After creation, copy the Application (client) ID — this is your Client ID',
            'Go to Certificates & secrets → New client secret — this is your Client Secret',
            'Save credentials and enable provider'
        ]
    ]
];
$doc = $providerDocs[$providerName] ?? null;
?>

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
                       placeholder="<?= View::e($defaultRedirect) ?>">
                <small class="form-help">Leave blank to use default: <?= View::e($defaultRedirect) ?></small>
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
            
            <?php if ($doc): ?>
                <div class="alert alert-info" style="margin-top: 20px;">
                    <h4>Setup Instructions for <?= View::e($doc['title']) ?>:</h4>
                    <ol>
                        <li><a href="<?= View::e($doc['url']) ?>" target="_blank" rel="noopener noreferrer">Open provider console</a></li>
                        <?php foreach ($doc['steps'] as $step): ?>
                            <li><?= View::e($step) ?></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            <?php endif; ?>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="/admin/oauth" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php View::endSection(); ?>
