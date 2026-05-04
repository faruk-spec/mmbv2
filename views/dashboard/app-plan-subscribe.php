<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<div style="max-width:760px;margin:0 auto;">
    <a href="<?= View::e($appMeta['url'] ?? '/plans') ?>" style="color:var(--text-secondary);font-size:.875rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">
        <i class="fas fa-arrow-left"></i> Back
    </a>

    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:16px;padding:26px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:20px;">
            <div>
                <h1 style="font-size:1.35rem;font-weight:700;margin:0 0 8px;"><?= View::e($appMeta['name'] ?? ucfirst($app)) ?> Plan</h1>
                <p style="color:var(--text-secondary);font-size:.9rem;margin:0;">Review your plan and continue to payment.</p>
            </div>
            <?php if (!empty($existing['plan_name'])): ?>
            <span style="padding:5px 12px;border-radius:999px;background:rgba(0,255,136,.12);color:var(--green);font-size:.78rem;font-weight:700;">Current: <?= View::e($existing['plan_name']) ?></span>
            <?php endif; ?>
        </div>

        <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:12px;padding:18px;margin-bottom:20px;">
            <div style="font-size:1.1rem;font-weight:700;"><?= View::e($plan['name']) ?></div>
            <div style="font-size:1.5rem;font-weight:800;margin:8px 0;"><?= View::e($plan['currency'] ?? 'USD') ?> <?= number_format((float) ($plan['price'] ?? 0), 2) ?></div>
            <div style="color:var(--text-secondary);font-size:.85rem;">
                <?= View::e($plan['billing_cycle'] ?? (($plan['duration_days'] ?? 30) . ' days')) ?>
                <?php if (!empty($plan['cancel_days'])): ?> &middot; Cancel within <?= (int) $plan['cancel_days'] ?> day(s)<?php endif; ?>
                <?php if (!empty($plan['refund_days'])): ?> &middot; Refund within <?= (int) $plan['refund_days'] ?> day(s)<?php endif; ?>
            </div>
        </div>

        <form method="POST" action="/plans/project/<?= urlencode($app) ?>/<?= urlencode($plan['slug'] ?? $plan['id']) ?>">
            <?= \Core\Security::csrfField() ?>
            <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:18px;">
                <?php $defaultPaymentMethod = $paymentSettings['payment_method'] ?? 'request'; ?>
                <?php $canUseManualReview = ($paymentSettings['payment_manual_review_enabled'] ?? '1') === '1'; ?>
                <?php if (!empty($paymentSettings['payment_upi_id'])): ?>
                <label style="display:flex;align-items:center;gap:10px;padding:12px;border:1px solid var(--border-color);border-radius:10px;background:var(--bg-secondary);cursor:pointer;">
                    <input type="radio" name="payment_method" value="upi" <?= $defaultPaymentMethod === 'upi' ? 'checked' : '' ?>>
                    <span><strong>UPI QR</strong><br><span style="font-size:.78rem;color:var(--text-secondary);">Pay manually and submit for verification</span></span>
                </label>
                <?php endif; ?>
                <?php if (($paymentSettings['payment_cashfree_enabled'] ?? '0') === '1' && !empty($paymentSettings['payment_cashfree_app_id']) && !empty($paymentSettings['payment_cashfree_secret'])): ?>
                <label style="display:flex;align-items:center;gap:10px;padding:12px;border:1px solid var(--border-color);border-radius:10px;background:var(--bg-secondary);cursor:pointer;">
                    <input type="radio" name="payment_method" value="cashfree" <?= $defaultPaymentMethod === 'cashfree' ? 'checked' : '' ?>>
                    <span><strong>Cashfree</strong><br><span style="font-size:.78rem;color:var(--text-secondary);">Hosted checkout payment</span></span>
                </label>
                <?php endif; ?>
                <?php if ($canUseManualReview): ?>
                <label style="display:flex;align-items:center;gap:10px;padding:12px;border:1px solid var(--border-color);border-radius:10px;background:var(--bg-secondary);cursor:pointer;">
                    <input type="radio" name="payment_method" value="request" <?= $defaultPaymentMethod === 'request' || empty($paymentSettings['payment_upi_id']) ? 'checked' : '' ?>>
                    <span><strong>Manual Review</strong><br><span style="font-size:.78rem;color:var(--text-secondary);">Admin activates after verification</span></span>
                </label>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Continue to Payment</button>
        </form>
    </div>
</div>

<?php View::endSection(); ?>
