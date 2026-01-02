<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="max-width: 400px; margin: 40px auto;">
    <div class="card">
        <h1 style="text-align: center; margin-bottom: 30px; font-size: 1.8rem;">Reset Password</h1>
        
        <?php if (Helpers::hasFlash('error')): ?>
            <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="/reset-password">
            <?= \Core\Security::csrfField() ?>
            <input type="hidden" name="token" value="<?= View::e($token) ?>">
            
            <div class="form-group">
                <label class="form-label" for="password">New Password</label>
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
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Reset Password</button>
        </form>
    </div>
</div>
<?php View::endSection(); ?>
