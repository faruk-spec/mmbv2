<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

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

        <?php if (Helpers::hasFlash('error')): ?>
            <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
        <?php endif; ?>

        <?php if (Helpers::hasFlash('success')): ?>
            <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
        <?php endif; ?>

        <?php if (Helpers::hasFlash('info')): ?>
            <div class="alert alert-info"><?= View::e(Helpers::getFlash('info')) ?></div>
        <?php endif; ?>

        <?php $oauthMode = 'login'; include BASE_PATH . '/views/partials/oauth-buttons.php'; ?>

        <?php
        $loginRedirect = '';
        if (!empty($_GET['redirect'])) {
            $loginRedirect = '?redirect=' . urlencode($_GET['redirect']);
        } elseif (!empty($_GET['return'])) {
            $loginRedirect = '?return=' . urlencode($_GET['return']);
        }
        ?>
        <form method="POST" action="<?= htmlspecialchars('/login' . $loginRedirect) ?>">
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
                       placeholder="••••••••" required minlength="1">
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

            <?php
            try {
                $_captchaDb = \Core\Database::getInstance();
                $_captchaLoginRow = $_captchaDb->fetch("SELECT value FROM settings WHERE `key` = 'captcha_on_login'");
                $_showLoginCaptcha = \Core\Captcha::isEnabled() && $_captchaLoginRow && $_captchaLoginRow['value'] === '1';
            } catch (\Exception $e) { $_showLoginCaptcha = false; }
            ?>
            <?php if (!empty($_showLoginCaptcha)): ?>
            <?php \Core\Captcha::generate(); ?>
            <div class="form-group">
                <label class="form-label">Security Check</label>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
                    <img src="/captcha" id="loginCaptchaImg" alt="CAPTCHA"
                         style="border-radius:6px;border:1px solid var(--border-color);cursor:pointer;"
                         title="Click to refresh" onclick="this.src='/captcha?t='+Date.now()">
                    <button type="button" onclick="document.getElementById('loginCaptchaImg').src='/captcha?t='+Date.now()"
                            style="background:none;border:none;color:var(--cyan);cursor:pointer;font-size:13px;padding:0;">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <input type="text" name="captcha_answer" class="form-input"
                       placeholder="Enter the answer" autocomplete="off" required>
                <?php if (View::hasError('captcha_answer')): ?>
                    <div class="form-error"><?= View::error('captcha_answer') ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 8px; padding: 13px 20px; font-size: 0.95rem;">Sign In</button>
        </form>

        <p style="text-align: center; margin-top: 22px; color: var(--text-secondary); font-size: 0.9rem;">
            Don't have an account? <a href="/register" style="font-weight: 600;">Register</a>
        </p>
    </div>
</div>
<?php View::endSection(); ?>
