<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<?php
// Detect if the current user is an oauth_only (Google-only) user
try {
    $db = \Core\Database::getInstance();
    $currentUser2fa = $db->fetch("SELECT oauth_only FROM users WHERE id = ?", [\Core\Auth::id()]);
    $isOauthOnly2fa = $currentUser2fa && !empty($currentUser2fa['oauth_only']);
} catch (\Exception $e) {
    $isOauthOnly2fa = false;
}
?>
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
                <div style="width: 80px; height: 80px; background: rgba(34, 197, 94, 0.1); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
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
                    
                    <?php if ($isOauthOnly2fa): ?>
                        <div style="background: rgba(255,193,7,.1); border: 1px solid rgba(255,193,7,.35); border-radius: 8px; padding: 12px 14px; margin-bottom: 16px; font-size: .875rem; color: rgba(255,193,7,1); text-align: left;">
                            Since you sign in with Google and haven't set a password, enter one of your saved backup codes to disable 2FA.
                            You can also <a href="/security" style="color: var(--cyan);">set a password</a> first, then use it instead.
                        </div>
                        <div class="form-group">
                            <label class="form-label">Backup code</label>
                            <input type="text" name="backup_code" class="form-input" required
                                   placeholder="xxxxxxxx" style="font-family: monospace; letter-spacing: 2px;">
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label class="form-label">Enter your password to disable 2FA</label>
                            <input type="password" name="password" class="form-input" required>
                        </div>
                    <?php endif; ?>
                    
                    <button type="submit" class="btn btn-danger" style="width: 100%;">Disable 2FA</button>
                </form>
            </div>
        <?php else: ?>
            <div style="padding: 20px 0;">
                <h2 style="margin-bottom: 15px; text-align: center;">Set Up 2FA</h2>
                <p style="color: var(--text-secondary); margin-bottom: 30px; text-align: center;">
                    Add an extra layer of security to your account by enabling two-factor authentication.
                </p>
                
                <?php if (isset($secret) && isset($provisioningUri)): ?>
                    <div style="background: var(--bg-secondary); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                    <h3 style="font-size: 1rem; margin-bottom: 15px;">Step 1: Scan QR Code</h3>
                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 15px;">
                        Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.)
                    </p>
                    <div style="text-align: center; padding: 20px; background: white; border-radius: 10px; margin-bottom: 15px;">
                        <div id="qrCanvas" style="display: inline-block;"></div>
                    </div>
                    <script src="https://unpkg.com/qr-code-styling@1.6.0-rc.1/lib/qr-code-styling.js"></script>
                    <script>
                    (function() {
                        var uri = <?= json_encode($provisioningUri) ?>;
                        function renderQR() {
                            var container = document.getElementById('qrCanvas');
                            if (!container || typeof QRCodeStyling === 'undefined') {
                                setTimeout(renderQR, 100);
                                return;
                            }
                            try {
                                var qr = new QRCodeStyling({
                                    width: 220,
                                    height: 220,
                                    data: uri,
                                    dotsOptions: { color: '#000000', type: 'square' },
                                    backgroundOptions: { color: '#ffffff' },
                                    qrOptions: { errorCorrectionLevel: 'M' }
                                });
                                qr.append(container);
                            } catch(e) {
                                container.innerHTML =
                                    '<p style="color:#666;font-size:0.85rem;">Could not render QR code. Please use the manual code below.</p>';
                            }
                        }
                        if (document.readyState === 'loading') {
                            document.addEventListener('DOMContentLoaded', renderQR);
                        } else {
                            renderQR();
                        }
                    })();
                    </script>
                    
                    <h3 style="font-size: 1rem; margin-bottom: 10px;">Or enter this code manually:</h3>
                    <div style="background: var(--bg-primary); padding: 15px; border-radius: 8px; font-family: monospace; font-size: 1.1rem; text-align: center; letter-spacing: 2px; word-break: break-all;">
                        <?= View::e($secret) ?>
                    </div>
                </div>
                
                <form method="POST" action="/2fa/enable">
                    <?= \Core\Security::csrfField() ?>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label" style="display: block; margin-bottom: 10px; font-weight: 600;">
                            Step 2: Enter the 6-digit code from your app
                        </label>
                        <input type="text" name="code" class="form-input" maxlength="6" 
                               placeholder="000000" required
                               style="text-align: center; font-size: 1.5rem; letter-spacing: 10px; font-family: monospace;">
                        <small style="display: block; margin-top: 8px; color: var(--text-secondary);">
                            Enter the code shown in your authenticator app to verify setup
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <path d="M9 12l2 2 4-4"/>
                        </svg>
                        Enable 2FA
                    </button>
                </form>
                <?php else: ?>
                <div style="padding: 20px; text-align: center; color: var(--text-secondary);">
                    <p>Unable to generate 2FA setup. Please try again.</p>
                    <a href="/security" style="color: var(--cyan);">Return to Security Settings</a>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 20px; text-align: center;">
            <a href="/security" style="color: var(--text-secondary);">&larr; Back to Security Settings</a>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
