<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="max-width: 400px; margin: 40px auto;">
    <div class="card">
        <h1 style="text-align: center; margin-bottom: 30px; font-size: 1.8rem;">Create Account</h1>
        
        <?php if (Helpers::hasFlash('error')): ?>
            <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="/register">
            <?= \Core\Security::csrfField() ?>
            
            <div class="form-group">
                <label class="form-label" for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-input" 
                       value="<?= View::old('name') ?>" placeholder="John Doe" required>
                <?php if (View::hasError('name')): ?>
                    <div class="form-error"><?= View::error('name') ?></div>
                <?php endif; ?>
            </div>
            
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
                       placeholder="Min. 8 characters" required minlength="8">
                <?php if (View::hasError('password')): ?>
                    <div class="form-error"><?= View::error('password') ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" 
                       class="form-input" placeholder="Repeat password" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; color: var(--text-secondary);">
            Already have an account? <a href="/login">Sign In</a>
        </p>
    </div>
</div>
<?php View::endSection(); ?>
