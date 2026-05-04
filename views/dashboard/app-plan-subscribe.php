<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<style>
.sub-wrap { max-width: 900px; margin: 0 auto; }
.sub-grid { display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start; }
.sub-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; overflow: hidden; }
.sub-card-header { padding: 20px 24px; border-bottom: 1px solid var(--border-color); background: linear-gradient(135deg,rgba(153,69,255,.1),rgba(0,240,255,.07)); display: flex; align-items: center; gap: 14px; }
.sub-card-body { padding: 24px; }
.pay-option { display: flex; align-items: center; gap: 14px; padding: 14px 16px; border: 2px solid var(--border-color); border-radius: 12px; background: var(--bg-secondary); cursor: pointer; transition: border-color .2s, background .2s; margin-bottom: 10px; }
.pay-option:has(input:checked) { border-color: var(--cyan); background: rgba(0,240,255,.05); }
.pay-option input[type=radio] { width: 18px; height: 18px; accent-color: var(--cyan); }
.pay-option-icon { width: 36px; height: 36px; border-radius: 8px; background: rgba(0,240,255,.12); display: flex; align-items: center; justify-content: center; color: var(--cyan); font-size: 1rem; flex-shrink: 0; }
.order-row { display: flex; justify-content: space-between; align-items: center; font-size: .88rem; padding: 8px 0; border-bottom: 1px solid var(--border-color); }
.order-row:last-child { border: none; }
.order-total { display: flex; justify-content: space-between; align-items: center; padding: 14px 0 0; font-size: 1.1rem; font-weight: 800; }
@media (max-width: 720px) {
    .sub-grid { grid-template-columns: 1fr; }
}
</style>

<div class="sub-wrap">
    <a href="<?= View::e($appMeta['url'] ?? '/plans') ?>" style="color:var(--text-secondary);font-size:.875rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">
        <i class="fas fa-arrow-left"></i> Back
    </a>

    <!-- Merchant header -->
    <div class="sub-card" style="margin-bottom:20px;">
        <div style="padding:18px 24px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:14px;">
                <?php if (!empty($appMeta['icon'])): ?>
                <img src="<?= View::e($appMeta['icon']) ?>" alt="" style="width:44px;height:44px;border-radius:10px;object-fit:cover;border:1px solid var(--border-color);">
                <?php else: ?>
                <div style="width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,var(--cyan),var(--purple));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.25rem;color:#06060a;">
                    <?= strtoupper(substr($appMeta['name'] ?? $app, 0, 1)) ?>
                </div>
                <?php endif; ?>
                <div>
                    <div style="font-weight:700;font-size:1.05rem;"><?= View::e($appMeta['name'] ?? ucfirst($app)) ?></div>
                    <div style="font-size:.78rem;color:var(--text-secondary);">Subscription Checkout</div>
                </div>
            </div>
            <?php if (!empty($existing['plan_name'])): ?>
            <span style="padding:5px 12px;border-radius:999px;background:rgba(0,255,136,.1);color:var(--green);font-size:.75rem;font-weight:700;border:1px solid rgba(0,255,136,.2);">
                <i class="fas fa-check-circle" style="margin-right:4px;"></i>Current: <?= View::e($existing['plan_name']) ?>
            </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="sub-grid">
        <!-- Left: payment method selector -->
        <div>
            <div class="sub-card">
                <div class="sub-card-header">
                    <div style="width:38px;height:38px;border-radius:10px;background:rgba(0,240,255,.12);display:flex;align-items:center;justify-content:center;color:var(--cyan);flex-shrink:0;">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:1rem;">Select Payment Method</div>
                        <div style="font-size:.78rem;color:var(--text-secondary);">Choose how you'd like to pay</div>
                    </div>
                </div>
                <div class="sub-card-body">
                    <form method="POST" action="/plans/project/<?= urlencode($app) ?>/<?= urlencode($plan['slug'] ?? $plan['id']) ?>">
                        <?= \Core\Security::csrfField() ?>
                        <?php
                        $defaultPaymentMethod = $paymentSettings['payment_method'] ?? 'request';
                        $canUseManualReview = ($paymentSettings['payment_manual_review_enabled'] ?? '1') === '1';
                        $cashfreeEnabled = ($paymentSettings['payment_cashfree_enabled'] ?? '0') === '1'
                            && !empty($paymentSettings['payment_cashfree_app_id'])
                            && !empty($paymentSettings['payment_cashfree_secret']);
                        $hasUpi = !empty($paymentSettings['payment_upi_id']);
                        ?>
                        <?php if ($hasUpi): ?>
                        <label class="pay-option">
                            <input type="radio" name="payment_method" value="upi" <?= $defaultPaymentMethod === 'upi' ? 'checked' : '' ?>>
                            <div class="pay-option-icon"><i class="fas fa-qrcode"></i></div>
                            <div>
                                <div style="font-weight:700;font-size:.92rem;">UPI / QR Code</div>
                                <div style="font-size:.76rem;color:var(--text-secondary);">Pay via UPI app and submit for verification</div>
                            </div>
                        </label>
                        <?php endif; ?>
                        <?php if ($cashfreeEnabled): ?>
                        <label class="pay-option">
                            <input type="radio" name="payment_method" value="cashfree" <?= $defaultPaymentMethod === 'cashfree' ? 'checked' : '' ?>>
                            <div class="pay-option-icon"><i class="fas fa-bolt"></i></div>
                            <div>
                                <div style="font-weight:700;font-size:.92rem;">Cashfree</div>
                                <div style="font-size:.76rem;color:var(--text-secondary);">Instant hosted checkout &mdash; cards, UPI, netbanking</div>
                            </div>
                        </label>
                        <?php endif; ?>
                        <?php if ($canUseManualReview): ?>
                        <label class="pay-option">
                            <input type="radio" name="payment_method" value="request" <?= (!$hasUpi && !$cashfreeEnabled) || $defaultPaymentMethod === 'request' ? 'checked' : '' ?>>
                            <div class="pay-option-icon"><i class="fas fa-clipboard-check"></i></div>
                            <div>
                                <div style="font-weight:700;font-size:.92rem;">Manual Review</div>
                                <div style="font-size:.76rem;color:var(--text-secondary);">Admin activates after verifying your request</div>
                            </div>
                        </label>
                        <?php endif; ?>

                        <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border-color);">
                            <button type="submit" class="btn btn-primary" style="width:100%;padding:13px;font-size:.97rem;font-weight:700;border-radius:12px;">
                                <i class="fas fa-lock" style="margin-right:6px;"></i>Continue to Payment
                            </button>
                            <p style="text-align:center;font-size:.74rem;color:var(--text-secondary);margin:10px 0 0;">
                                <i class="fas fa-shield-alt" style="margin-right:4px;"></i>Your payment information is secure
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: order summary -->
        <div>
            <div class="sub-card">
                <div class="sub-card-header">
                    <div style="width:38px;height:38px;border-radius:10px;background:rgba(153,69,255,.15);display:flex;align-items:center;justify-content:center;color:var(--purple);flex-shrink:0;">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:1rem;">Order Summary</div>
                        <div style="font-size:.78rem;color:var(--text-secondary);"><?= View::e($plan['name']) ?></div>
                    </div>
                </div>
                <div class="sub-card-body">
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Plan</span>
                        <span style="font-weight:600;"><?= View::e($plan['name']) ?></span>
                    </div>
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Billing cycle</span>
                        <span><?= View::e($plan['billing_cycle'] ?? (($plan['duration_days'] ?? 30) . ' days')) ?></span>
                    </div>
                    <?php if (!empty($plan['cancel_days'])): ?>
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Cancel window</span>
                        <span><?= (int) $plan['cancel_days'] ?> day(s)</span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($plan['refund_days'])): ?>
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Refund window</span>
                        <span><?= (int) $plan['refund_days'] ?> day(s)</span>
                    </div>
                    <?php endif; ?>
                    <div class="order-total">
                        <span>Total Due</span>
                        <span style="color:var(--cyan);"><?= View::e($plan['currency'] ?? 'USD') ?> <?= number_format((float) ($plan['price'] ?? 0), 2) ?></span>
                    </div>
                    <?php if (!empty($plan['description'])): ?>
                    <div style="margin-top:14px;padding:12px;background:var(--bg-secondary);border-radius:10px;font-size:.8rem;color:var(--text-secondary);">
                        <?= nl2br(View::e($plan['description'])) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php View::endSection(); ?>
