<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="max-width: 500px; margin: 40px auto;">
    <div class="card">
        <h1 style="text-align: center; margin-bottom: 30px;">Two-Factor Authentication</h1>
        
        <?php if (Helpers::hasFlash('success')): ?>
            <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
        <?php endif; ?>
        
        <?php if (Helpers::hasFlash('error')): ?>
            <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
        <?php endif; ?>
        
        <?php if ($twoFactorEnabled): ?>
            <div style="text-align: center; padding: 20px 0;">
                <div style="width: 80px; height: 80px; background: rgba(0, 255, 136, 0.1); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="M9 12l2 2 4-4"/>
                    </svg>
                </div>
                <h2 style="color: var(--green); margin-bottom: 15px;">2FA is Enabled</h2>
                <p style="color: var(--text-secondary); margin-bottom: 30px;">
                    Your account is protected with two-factor authentication.
                </p>
                
                <form method="POST" action="/2fa/disable">
                    <?= \Core\Security::csrfField() ?>
                    
                    <div class="form-group">
                        <label class="form-label">Enter your password to disable 2FA</label>
                        <input type="password" name="password" class="form-input" required>
                    </div>
                    
                    <button type="submit" class="btn btn-danger" style="width: 100%;">Disable 2FA</button>
                </form>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 20px 0;">
                <div style="width: 80px; height: 80px; background: rgba(255, 170, 0, 0.1); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <h2 style="margin-bottom: 15px;">Set Up 2FA</h2>
                <p style="color: var(--text-secondary); margin-bottom: 30px;">
                    Add an extra layer of security to your account by enabling two-factor authentication.
                </p>
                
                <form method="POST" action="/2fa/enable">
                    <?= \Core\Security::csrfField() ?>
                    
                    <div class="form-group">
                        <label class="form-label">Enter the 6-digit code from your authenticator app</label>
                        <input type="text" name="code" class="form-input" maxlength="6" 
                               placeholder="000000" style="text-align: center; font-size: 1.5rem; letter-spacing: 10px;">
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Enable 2FA</button>
                </form>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 20px; text-align: center;">
            <a href="/security" style="color: var(--text-secondary);">&larr; Back to Security Settings</a>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
