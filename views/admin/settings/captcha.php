<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
    <div>
        <h1>Captcha Settings</h1>
        <p style="color:var(--text-secondary);">Configure math-based CAPTCHA for login &amp; registration forms.</p>
    </div>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div class="grid grid-3">
    <div style="grid-column:span 2;">
        <div class="card">
            <form method="POST" action="/admin/settings/captcha">
                <?= \Core\Security::csrfField() ?>

                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" name="captcha_enabled" value="1"
                            <?= ($settings['captcha_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span>Enable CAPTCHA globally</span>
                    </label>
                    <p style="color:var(--text-secondary);font-size:12px;margin-top:6px;">
                        Master switch. When off, CAPTCHA is disabled everywhere regardless of the options below.
                    </p>
                </div>

                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" name="captcha_on_login" value="1"
                            <?= ($settings['captcha_on_login'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span>Show CAPTCHA on Login form</span>
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" name="captcha_on_register" value="1"
                            <?= ($settings['captcha_on_register'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span>Show CAPTCHA on Register form</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">Save Captcha Settings</button>
            </form>
        </div>
    </div>

    <div>
        <div class="card">
            <h3 style="margin-bottom:14px;">Preview</h3>
            <p style="color:var(--text-secondary);font-size:13px;margin-bottom:14px;">
                A sample CAPTCHA challenge as users will see it:
            </p>
            <img src="/captcha" id="captchaPreview" alt="CAPTCHA preview"
                 style="border-radius:6px;width:100%;max-width:220px;display:block;">
            <button type="button" onclick="document.getElementById('captchaPreview').src='/captcha?t='+Date.now()"
                    class="btn btn-secondary" style="margin-top:12px;font-size:12px;padding:6px 14px;">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <hr style="margin:18px 0;border-color:var(--border-color);">
            <p style="font-size:12px;color:var(--text-secondary);">
                <i class="fas fa-info-circle" style="color:var(--cyan);"></i>
                Uses a simple arithmetic challenge (no Google reCAPTCHA).
                The answer is stored server-side in the user's session.
            </p>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
