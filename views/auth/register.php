<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('auth'); ?>

<?php View::section('content'); ?>
<?php include BASE_PATH . '/views/partials/auth-branding.php'; ?>
<div class="auth-page-wrap">
    <div class="auth-card">
        <!-- Logo -->
        <div class="auth-logo-wrap">
            <?php if ($authLogo): ?>
                <img src="<?= View::e($authLogo) ?>" alt="<?= View::e($authSiteName) ?>" class="auth-logo-img">
            <?php else: ?>
                <div class="auth-logo-icon"><?= mb_strtoupper(mb_substr($authSiteName, 0, 1)) ?></div>
            <?php endif; ?>
        </div>

        <?php if ($authTagline): ?>
            <p class="auth-tagline"><?= View::e($authTagline) ?></p>
        <?php endif; ?>
        <div class="auth-security-note"><i class="fas fa-lock" aria-hidden="true"></i> HTTPS Secure Signup</div>

        <?php if (Helpers::hasFlash('error')): ?>
            <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
        <?php endif; ?>

        <?php $oauthMode = 'register'; include BASE_PATH . '/views/partials/oauth-buttons.php'; ?>

        <form method="POST" action="/register">
            <?= \Core\Security::csrfField() ?>

            <div class="form-group">
                <label class="form-label" for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-input"
                       value="<?= View::old('name') ?>" placeholder="John Doe" required minlength="2" maxlength="50">
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
                <label class="form-label" for="phone">Phone Number <span class="form-label-note">(optional)</span></label>
                <input type="tel" id="phone" name="phone" class="form-input"
                       value="<?= View::old('phone') ?>" placeholder="+91 9876543210">
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
                       class="form-input" placeholder="Repeat password" required minlength="8">
            </div>

            <?php
            try {
                $_captchaDb2 = \Core\Database::getInstance();
                $_captchaRegRow = $_captchaDb2->fetch("SELECT value FROM settings WHERE `key` = 'captcha_on_register'");
                $_showRegCaptcha = \Core\Captcha::isEnabled() && $_captchaRegRow && $_captchaRegRow['value'] === '1';
            } catch (\Exception $e) { $_showRegCaptcha = false; }
            ?>
            <?php if (!empty($_showRegCaptcha)): ?>
            <?php \Core\Captcha::generate(); ?>
            <div class="form-group">
                <label class="form-label">Security Check</label>
                <div class="captcha-row">
                    <img src="/captcha" id="regCaptchaImg" alt="CAPTCHA"
                         class="captcha-image" title="Click to refresh">
                    <button type="button" id="registerCaptchaRefresh" class="captcha-refresh">
                        Refresh
                    </button>
                </div>
                <input type="text" name="captcha_answer" class="form-input"
                       placeholder="Enter the answer" autocomplete="off" required>
                <?php if (View::hasError('captcha_answer')): ?>
                    <div class="form-error"><?= View::error('captcha_answer') ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary auth-btn-block">Create Account</button>
        </form>

        <p class="auth-footer-copy">
            Already have an account? <a href="/login" class="auth-link-strong">Sign In</a>
        </p>
    </div>
</div>
<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
(function() {
    var form = document.querySelector('form[action="/register"]');
    if (!form) return;

    var captchaImg = document.getElementById('regCaptchaImg');
    var refreshBtn = document.getElementById('registerCaptchaRefresh');
    function refreshCaptcha() {
        if (captchaImg) {
            captchaImg.src = '/captcha?t=' + Date.now();
        }
    }
    if (captchaImg) {
        captchaImg.addEventListener('click', refreshCaptcha);
    }
    if (refreshBtn) {
        refreshBtn.addEventListener('click', refreshCaptcha);
    }

    form.addEventListener('submit', function(e) {
        var pw = document.getElementById('password');
        var pw2 = document.getElementById('password_confirmation');
        if (pw && pw2 && pw.value !== pw2.value) {
            e.preventDefault();
            var errEl = document.getElementById('pw-mismatch-error');
            if (!errEl) {
                errEl = document.createElement('div');
                errEl.id = 'pw-mismatch-error';
                errEl.style.cssText = 'color:#ff6b6b;font-size:13px;margin-top:6px;';
                pw2.parentNode.insertBefore(errEl, pw2.nextSibling);
            }
            errEl.textContent = 'Passwords do not match. Please try again.';
            pw2.focus();
        }
    });
})();
<?php View::endSection(); ?>
