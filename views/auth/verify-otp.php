<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('auth'); ?>

<?php View::section('content'); ?>
<div class="auth-narrow">
    <div class="otp-card">
        <div class="auth-center auth-stack-sm">
            <div class="auth-hero-icon">📧</div>
            <h1 class="auth-title">Verify Your Email</h1>
            <?php if ($email): ?>
            <p class="auth-subtext">
                We sent a 6-digit code to <strong><?= View::e($email) ?></strong>
            </p>
            <?php endif; ?>
        </div>

        <?php if (Helpers::hasFlash('error')): ?>
            <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
        <?php endif; ?>
        <?php if (Helpers::hasFlash('info')): ?>
            <div class="alert alert-info"><?= View::e(Helpers::getFlash('info')) ?></div>
        <?php endif; ?>
        <?php if (Helpers::hasFlash('success')): ?>
            <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
        <?php endif; ?>

        <form method="POST" action="/verify-otp">
            <?= Security::csrfField() ?>
            <div class="form-group">
                <label class="auth-label">Verification Code</label>
                <input
                    type="text"
                    name="otp"
                    class="auth-code-input"
                    placeholder="Enter 6-digit code"
                    maxlength="6"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    autocomplete="one-time-code"
                    autofocus
                    required
                >
                <small class="auth-code-note">
                    Check your inbox and spam folder. The code expires in <strong>5 minutes</strong> — use Resend if needed.
                </small>
            </div>
            <button type="submit" class="btn btn-primary auth-btn-block">
                Verify &amp; Continue
            </button>
        </form>

        <div class="auth-center auth-resend-wrap">
            <form method="POST" action="/verify-otp/resend">
                <?= Security::csrfField() ?>
                <button type="submit" class="btn btn-secondary">
                    Resend Code
                </button>
            </form>
        </div>

        <div class="auth-footer-copy">
            Wrong account? <a href="/register">Register again</a>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
