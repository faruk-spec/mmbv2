<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('auth'); ?>

<?php View::section('content'); ?>
<div class="auth-narrow">
    <div class="auth-simple-card">
        <h1 class="auth-title auth-center">Forgot Password</h1>
        
        <?php if (Helpers::hasFlash('success')): ?>
            <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
        <?php endif; ?>
        
        <?php if (Helpers::hasFlash('error')): ?>
            <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
        <?php endif; ?>
        
        <p class="auth-subtext">
            Enter your email address and we'll send you a link to reset your password.
        </p>
        
        <form method="POST" action="/forgot-password">
            <?= \Core\Security::csrfField() ?>
            
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input" 
                       placeholder="you@example.com" required>
            </div>
            
            <button type="submit" class="btn btn-primary auth-btn-block">Send Reset Link</button>
        </form>
        
        <p class="auth-footer-copy">
            Remember your password? <a href="/login">Sign In</a>
        </p>
    </div>
</div>
<?php View::endSection(); ?>
