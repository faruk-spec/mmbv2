<?php use Core\View; use Core\Helpers; use Core\GoogleOAuth; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div class="auth-page-wrap">
    <!-- Brand panel -->
    <div class="auth-brand-panel">
        <div class="auth-brand-logo"><?= APP_NAME ?></div>
        <div class="auth-brand-title">Welcome back</div>
        <div class="auth-brand-subtitle">Sign in to access your dashboard, manage applications, and track your projects — all in one place.</div>
        <div class="auth-brand-dots">
            <span class="auth-brand-dot active"></span>
            <span class="auth-brand-dot"></span>
            <span class="auth-brand-dot"></span>
        </div>
    </div>

    <!-- Form panel -->
    <div class="auth-form-panel">
        <div class="auth-form-inner">
            <div class="auth-form-heading">Sign In</div>
            <div class="auth-form-sub">Enter your credentials to continue</div>

            <?php if (Helpers::hasFlash('error')): ?>
                <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
            <?php endif; ?>
            <?php if (Helpers::hasFlash('success')): ?>
                <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
            <?php endif; ?>
            <?php if (Helpers::hasFlash('info')): ?>
                <div class="alert alert-info"><?= View::e(Helpers::getFlash('info')) ?></div>
            <?php endif; ?>

            <?php if (GoogleOAuth::isEnabled()): ?>
            <a href="/auth/google" class="auth-google-btn">
                <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                    <g fill="none" fill-rule="evenodd">
                        <path d="M17.64 9.205c0-.639-.057-1.252-.164-1.841H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
                        <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
                        <path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
                        <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
                    </g>
                </svg>
                Sign in with Google
            </a>
            <div class="auth-divider">or</div>
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
                    <a href="/forgot-password" style="font-size: 0.85rem;">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 0.95rem;">Sign In</button>
            </form>

            <p style="text-align: center; margin-top: 24px; color: var(--text-secondary); font-size: 0.88rem;">
                Don't have an account? <a href="/register">Register</a>
            </p>
        </div>
    </div>
</div>
<?php View::endSection(); ?>

