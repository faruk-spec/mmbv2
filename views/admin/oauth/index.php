<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin/layout'); ?>

<?php View::section('content'); ?>
<div class="admin-content">
    <div class="page-header">
        <h1>OAuth Providers</h1>
        <p>Manage OAuth authentication providers for Google, GitHub, etc.</p>
    </div>
    
    <?php if (Helpers::hasFlash('success')): ?>
        <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
    <?php endif; ?>
    
    <?php if (Helpers::hasFlash('error')): ?>
        <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h3>Available Providers</h3>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Provider</th>
                    <th>Status</th>
                    <th>Client ID</th>
                    <th>Redirect URI</th>
                    <th>Scopes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($providers)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No providers found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($providers as $provider): ?>
                        <tr>
                            <td>
                                <strong><?= View::e($provider['display_name']) ?></strong>
                                <br>
                                <small style="color: var(--text-secondary);"><?= View::e($provider['name']) ?></small>
                            </td>
                            <td>
                                <?php if ($provider['is_enabled']): ?>
                                    <span class="badge badge-success">Enabled</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Disabled</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($provider['client_id']): ?>
                                    <code><?= View::e(substr($provider['client_id'], 0, 20)) ?>...</code>
                                <?php else: ?>
                                    <span style="color: var(--text-secondary);">Not configured</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small><?= View::e($provider['redirect_uri'] ?: 'Default') ?></small>
                            </td>
                            <td>
                                <small><?= View::e($provider['scopes']) ?></small>
                            </td>
                            <td>
                                <a href="/admin/oauth/<?= $provider['id'] ?>/edit" class="btn btn-sm btn-primary">
                                    Configure
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="card" style="margin-top: 20px;">
        <div class="card-header">
            <h3>Quick Actions</h3>
        </div>
        <div class="card-body">
            <a href="/admin/oauth/connections" class="btn btn-secondary">
                View OAuth Connections
            </a>
            <a href="/admin/sessions/login-history" class="btn btn-secondary">
                View Login History
            </a>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
