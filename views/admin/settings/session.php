<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content">
    <div class="page-header">
        <h1>Session & Security Settings</h1>
        <p>Configure session timeout, security policies, and authentication settings</p>
    </div>
    
    <?php if (Helpers::hasFlash('success')): ?>
        <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
    <?php endif; ?>
    
    <?php if (Helpers::hasFlash('error')): ?>
        <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h3>Session Configuration</h3>
        </div>
        
        <form method="POST" action="/admin/settings/session">
            <?= Security::csrfField() ?>
            
            <div class="form-group">
                <label class="form-label" for="default_session_timeout">Default Session Timeout (minutes)</label>
                <input type="number" id="default_session_timeout" name="default_session_timeout" 
                       class="form-input" min="5" max="10080" step="5"
                       value="<?= View::e($settings['default_session_timeout'] ?? '120') ?>">
                <small class="form-help">How long users stay logged in without activity (5 min to 7 days). Default: 120 minutes (2 hours)</small>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="remember_me_duration">Remember Me Duration (days)</label>
                <input type="number" id="remember_me_duration" name="remember_me_duration" 
                       class="form-input" min="1" max="365"
                       value="<?= View::e($settings['remember_me_duration'] ?? '30') ?>">
                <small class="form-help">How long "Remember Me" tokens remain valid (1 to 365 days). Default: 30 days</small>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="max_concurrent_sessions">Max Concurrent Sessions Per User</label>
                <input type="number" id="max_concurrent_sessions" name="max_concurrent_sessions" 
                       class="form-input" min="1" max="50"
                       value="<?= View::e($settings['max_concurrent_sessions'] ?? '5') ?>">
                <small class="form-help">Maximum number of active sessions allowed per user. Default: 5</small>
            </div>
            
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="auto_logout_enabled" value="1" 
                           <?= ($settings['auto_logout_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <span>Enable Auto-Logout on Inactivity</span>
                </label>
                <small class="form-help">Automatically log out users when session expires</small>
            </div>
            
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="session_ip_validation" value="1" 
                           <?= ($settings['session_ip_validation'] ?? '0') === '1' ? 'checked' : '' ?>>
                    <span>Validate IP Address on Session</span>
                </label>
                <small class="form-help">Terminate session if IP address changes (more secure but may cause issues with dynamic IPs)</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Save Session Settings</button>
        </form>
    </div>
    
    <div class="card" style="margin-top: 20px;">
        <div class="card-header">
            <h3>Security Policies</h3>
        </div>
        
        <form method="POST" action="/admin/settings/security-policy">
            <?= Security::csrfField() ?>
            
            <div class="form-group">
                <label class="form-label" for="max_failed_login_attempts">Max Failed Login Attempts</label>
                <input type="number" id="max_failed_login_attempts" name="max_failed_login_attempts" 
                       class="form-input" min="3" max="20"
                       value="<?= View::e($settings['max_failed_login_attempts'] ?? '5') ?>">
                <small class="form-help">Number of failed login attempts before account lockout. Default: 5</small>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="account_lockout_duration">Account Lockout Duration (minutes)</label>
                <input type="number" id="account_lockout_duration" name="account_lockout_duration" 
                       class="form-input" min="5" max="1440"
                       value="<?= View::e($settings['account_lockout_duration'] ?? '15') ?>">
                <small class="form-help">How long to lock account after max failed attempts. Default: 15 minutes</small>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password_min_length">Minimum Password Length</label>
                <input type="number" id="password_min_length" name="password_min_length" 
                       class="form-input" min="6" max="128"
                       value="<?= View::e($settings['password_min_length'] ?? '8') ?>">
                <small class="form-help">Minimum number of characters required for passwords. Default: 8</small>
            </div>
            
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="require_email_verification" value="1" 
                           <?= ($settings['require_email_verification'] ?? '0') === '1' ? 'checked' : '' ?>>
                    <span>Require Email Verification for New Accounts</span>
                </label>
            </div>
            
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="force_password_change" value="1" 
                           <?= ($settings['force_password_change'] ?? '0') === '1' ? 'checked' : '' ?>>
                    <span>Force Password Change Every 90 Days</span>
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary">Save Security Policies</button>
        </form>
    </div>
    
    <div class="card" style="margin-top: 20px;">
        <div class="card-header">
            <h3>Session Statistics</h3>
        </div>
        <div class="card-body">
            <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div class="stat-box">
                    <div class="stat-label">Active Sessions</div>
                    <div class="stat-value"><?= number_format($stats['active_sessions'] ?? 0) ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Total Sessions Today</div>
                    <div class="stat-value"><?= number_format($stats['sessions_today'] ?? 0) ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Expired Sessions (Last 24h)</div>
                    <div class="stat-value"><?= number_format($stats['expired_sessions'] ?? 0) ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Average Session Duration</div>
                    <div class="stat-value"><?= $stats['avg_duration'] ?? 'N/A' ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-box {
    background: var(--bg-secondary);
    padding: 20px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
}
.stat-label {
    font-size: 0.9rem;
    color: var(--text-secondary);
    margin-bottom: 8px;
}
.stat-value {
    font-size: 2rem;
    font-weight: 600;
    color: var(--cyan);
}
</style>
<?php View::endSection(); ?>
