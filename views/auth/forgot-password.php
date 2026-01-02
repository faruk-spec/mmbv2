<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="max-width: 400px; margin: 40px auto;">
    <div class="card">
        <h1 style="text-align: center; margin-bottom: 30px; font-size: 1.8rem;">Forgot Password</h1>
        
        <?php if (Helpers::hasFlash('success')): ?>
            <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
        <?php endif; ?>
        
        <?php if (Helpers::hasFlash('error')): ?>
            <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
        <?php endif; ?>
        
        <p style="color: var(--text-secondary); margin-bottom: 20px; text-align: center;">
            Enter your email address and we'll send you a link to reset your password.
        </p>
        
        <form method="POST" action="/forgot-password">
            <?= \Core\Security::csrfField() ?>
            
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input" 
                       placeholder="you@example.com" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Send Reset Link</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; color: var(--text-secondary);">
            Remember your password? <a href="/login">Sign In</a>
        </p>
    </div>
</div>
<?php View::endSection(); ?>
