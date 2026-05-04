<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<?php include BASE_PATH . '/views/partials/auth-branding.php'; ?>
<div class="auth-page-wrap">
    <div class="auth-card">
        <div class="auth-logo-wrap">
            <?php if ($authLogo ?? false): ?>
                <img src="<?= View::e($authLogo) ?>" alt="<?= View::e($authSiteName ?? '') ?>" class="auth-logo-img">
            <?php else: ?>
                <div class="auth-logo-icon" style="font-size:2rem;">📱</div>
            <?php endif; ?>
        </div>
        <h2 style="text-align:center;margin-bottom:6px;">One more step</h2>
        <p style="text-align:center;color:var(--text-secondary);font-size:.9rem;margin-bottom:20px;">Add your phone number to complete your account setup.</p>

        <?php if (Helpers::hasFlash('error')): ?>
            <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
        <?php endif; ?>

        <form method="POST" action="/complete-profile">
            <?= \Core\Security::csrfField() ?>
            <input type="hidden" name="next" value="<?= View::e($next) ?>">
            <div class="form-group">
                <label class="form-label" for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-input" placeholder="+91 9876543210" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;padding:13px 20px;font-size:.95rem;margin-top:8px;">Continue</button>
        </form>
        <p style="text-align:center;margin-top:16px;font-size:.85rem;color:var(--text-secondary);">
            <a href="/dashboard" style="color:var(--cyan);">Skip for now</a>
        </p>
    </div>
</div>
<?php View::endSection(); ?>
