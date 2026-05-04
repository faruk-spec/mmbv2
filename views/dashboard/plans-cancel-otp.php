<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<?php if (\Core\Helpers::hasFlash('success')): ?>
<div class="alert alert-success"><?= View::e(\Core\Helpers::getFlash('success')) ?></div>
<?php endif; ?>
<?php if (\Core\Helpers::hasFlash('error')): ?>
<div class="alert alert-error"><?= View::e(\Core\Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div style="max-width:540px;margin:40px auto;">
    <a href="/plans/payment/<?= (int) $payment['id'] ?>" style="color:var(--text-secondary);font-size:.875rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">
        <i class="fas fa-arrow-left"></i> Back to Payment
    </a>

    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;overflow:hidden;">
        <div style="padding:24px;border-bottom:1px solid var(--border-color);background:linear-gradient(135deg,rgba(231,76,60,.1),rgba(255,80,80,.06));">
            <h1 style="font-size:1.2rem;font-weight:700;margin:0 0 4px;color:var(--red, #e74c3c);">
                <i class="fas fa-times-circle" style="margin-right:8px;"></i>Cancel Subscription
            </h1>
            <p style="color:var(--text-secondary);font-size:.85rem;margin:0;">
                <?= View::e($payment['plan_name']) ?> &middot; <?= View::e($payment['currency']) ?> <?= number_format((float) $payment['amount'], 2) ?>
            </p>
        </div>

        <div style="padding:24px;">
            <div style="background:rgba(231,76,60,.07);border:1px solid rgba(231,76,60,.2);border-radius:10px;padding:16px;margin-bottom:24px;font-size:.88rem;color:var(--text-secondary);line-height:1.6;">
                <strong style="color:var(--text-primary);">Cancellation Policy</strong><br>
                &bull; Cancelled within <?= (int) $refundWindowDays ?> day<?= $refundWindowDays !== 1 ? 's' : '' ?> of activation: eligible for a full refund pending admin approval.<br>
                &bull; Cancelled after <?= (int) $refundWindowDays ?> day<?= $refundWindowDays !== 1 ? 's' : '' ?>: no refund, but access continues until the end of the paid period.
            </div>

            <p style="font-size:.9rem;margin-bottom:20px;">
                To confirm cancellation, first send a verification code to your registered email address, then enter it below.
            </p>

            <!-- Step 1: Send OTP -->
            <form method="POST" action="/plans/payment/<?= (int) $payment['id'] ?>/cancel/send-otp" style="margin-bottom:20px;">
                <?= \Core\Security::csrfField() ?>
                <button type="submit" class="btn btn-secondary" style="width:100%;">
                    <i class="fas fa-envelope" style="margin-right:6px;"></i>Send Verification Code to Email
                </button>
            </form>

            <!-- Step 2: Enter OTP and cancel -->
            <form method="POST" action="/plans/payment/<?= (int) $payment['id'] ?>/cancel">
                <?= \Core\Security::csrfField() ?>
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:.85rem;font-weight:600;margin-bottom:6px;">Verification Code</label>
                    <input type="text" name="cancel_otp" maxlength="6" pattern="[0-9]{6}" placeholder="Enter 6-digit code"
                        required
                        style="width:100%;padding:10px 14px;border:1px solid var(--border-color);border-radius:8px;background:var(--bg-secondary);color:var(--text-primary);font-size:1.1rem;letter-spacing:.2em;text-align:center;box-sizing:border-box;">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;background:#e74c3c;border-color:#c0392b;"
                    onclick="return confirm('Are you sure you want to cancel this subscription?')">
                    <i class="fas fa-times" style="margin-right:6px;"></i>Confirm Cancellation
                </button>
            </form>
        </div>
    </div>
</div>

<?php View::endSection(); ?>
