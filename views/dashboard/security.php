<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<h1 style="margin-bottom: 30px;">Security Settings</h1>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Change Password</h3>
        </div>
        
        <form method="POST" action="/security/password">
            <?= \Core\Security::csrfField() ?>
            
            <div class="form-group">
                <label class="form-label" for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" 
                       class="form-input" required>
                <?php if (View::hasError('current_password')): ?>
                    <div class="form-error"><?= View::error('current_password') ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">New Password</label>
                <input type="password" id="password" name="password" 
                       class="form-input" required minlength="8">
                <?php if (View::hasError('password')): ?>
                    <div class="form-error"><?= View::error('password') ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" 
                       class="form-input" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Two-Factor Authentication</h3>
        </div>
        
        <div style="text-align: center; padding: 20px 0;">
            <?php if ($twoFactorEnabled): ?>
                <div style="width: 60px; height: 60px; background: rgba(0, 255, 136, 0.1); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center;">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="M9 12l2 2 4-4"/>
                    </svg>
                </div>
                <h4 style="color: var(--green); margin-bottom: 10px;">2FA Enabled</h4>
                <p style="color: var(--text-secondary); margin-bottom: 20px;">Your account is protected with two-factor authentication.</p>
                <a href="/2fa/setup" class="btn btn-secondary">Manage 2FA</a>
            <?php else: ?>
                <div style="width: 60px; height: 60px; background: rgba(255, 170, 0, 0.1); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center;">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <h4 style="color: var(--orange); margin-bottom: 10px;">2FA Disabled</h4>
                <p style="color: var(--text-secondary); margin-bottom: 20px;">Add an extra layer of security to your account.</p>
                <a href="/2fa/setup" class="btn btn-primary">Enable 2FA</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">Active Sessions</h3>
    </div>
    
    <?php if (empty($devices)): ?>
        <p style="color: var(--text-secondary); text-align: center; padding: 30px;">No active sessions found</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Device</th>
                    <th>IP Address</th>
                    <th>Created</th>
                    <th>Expires</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($devices as $device): 
                    $deviceInfo = json_decode($device['device_info'], true);
                ?>
                    <tr>
                        <td><?= View::e(Helpers::truncate($deviceInfo['browser'] ?? 'Unknown', 50)) ?></td>
                        <td><?= View::e($deviceInfo['ip'] ?? 'Unknown') ?></td>
                        <td><?= Helpers::formatDate($device['created_at']) ?></td>
                        <td><?= Helpers::formatDate($device['expires_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php View::endSection(); ?>
