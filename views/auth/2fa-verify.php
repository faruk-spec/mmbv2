<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="max-width: 400px; margin: 40px auto;">
    <div class="card">
        <h1 style="text-align: center; margin-bottom: 30px; font-size: 1.8rem;">Verify 2FA</h1>
        
        <?php if (Helpers::hasFlash('error')): ?>
            <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
        <?php endif; ?>
        
        <p style="color: var(--text-secondary); text-align: center; margin-bottom: 20px;">
            Enter the 6-digit code from your authenticator app to continue.
        </p>
        
        <form method="POST" action="/2fa/verify">
            <?= \Core\Security::csrfField() ?>
            
            <div class="form-group">
                <input type="text" name="code" class="form-input" maxlength="6" 
                       placeholder="000000" required autofocus
                       style="text-align: center; font-size: 2rem; letter-spacing: 12px; padding: 20px;">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Verify</button>
        </form>
    </div>
</div>
<?php View::endSection(); ?>
