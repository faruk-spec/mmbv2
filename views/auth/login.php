<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="max-width: 400px; margin: 40px auto;">
    <div class="card">
        <h1 style="text-align: center; margin-bottom: 30px; font-size: 1.8rem;">Sign In</h1>
        
        <?php if (Helpers::hasFlash('error')): ?>
            <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
        <?php endif; ?>
        
        <?php if (Helpers::hasFlash('success')): ?>
            <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="/login">
            <?= \Core\Security::csrfField() ?>
            
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input" 
                       value="<?= View::old('email') ?>" placeholder="you@example.com" required>
                <?php if (View::hasError('email')): ?>
                    <div class="form-error"><?= View::error('email') ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" 
                       placeholder="••••••••" required>
                <?php if (View::hasError('password')): ?>
                    <div class="form-error"><?= View::error('password') ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                <label class="form-checkbox">
                    <input type="checkbox" name="remember">
                    <span>Remember me</span>
                </label>
                <a href="/forgot-password" style="font-size: 14px;">Forgot password?</a>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Sign In</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; color: var(--text-secondary);">
            Don't have an account? <a href="/register">Register</a>
        </p>
    </div>
</div>
<?php View::endSection(); ?>
