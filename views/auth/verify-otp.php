<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="max-width:420px;margin:60px auto;">
    <div class="card">
        <div style="text-align:center;margin-bottom:28px;">
            <div style="font-size:42px;margin-bottom:12px;">📧</div>
            <h1 style="font-size:1.6rem;margin:0 0 8px;">Verify Your Email</h1>
            <?php if ($email): ?>
            <p style="color:var(--text-secondary);font-size:14px;margin:0;">
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
            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:block;font-weight:600;margin-bottom:8px;">Verification Code</label>
                <input
                    type="text"
                    name="otp"
                    class="form-control"
                    placeholder="Enter 6-digit code"
                    maxlength="6"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    autocomplete="one-time-code"
                    autofocus
                    required
                    style="font-size:1.4rem;letter-spacing:6px;text-align:center;padding:14px;"
                >
                <small style="color:var(--text-secondary);display:block;margin-top:6px;">
                    Check your inbox and spam folder. The code may expire — use Resend if needed.
                </small>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">
                Verify &amp; Continue
            </button>
        </form>

        <div style="text-align:center;margin-top:20px;">
            <form method="POST" action="/verify-otp/resend" style="display:inline;">
                <?= Security::csrfField() ?>
                <button type="submit" class="btn btn-secondary" style="font-size:13px;">
                    Resend Code
                </button>
            </form>
        </div>

        <div style="text-align:center;margin-top:16px;font-size:13px;color:var(--text-secondary);">
            Wrong account? <a href="/register">Register again</a>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
