<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('styles'); ?>
<style>
/* ── Subscribe / Checkout redesign ─────────────────────────────────────── */
.sub-wrap  { max-width: 60rem; margin: 0 auto; }
.sub-grid  { display: grid; grid-template-columns: 1fr 20rem; gap: 1rem; align-items: start; }

/* Progress bar */
.chk-steps { display: flex; align-items: center; justify-content: center; gap: 0; margin-bottom: 36px; }
.chk-step  { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; position: relative; text-align: center; }
.chk-step:not(:last-child)::after {
    content: ''; position: absolute; top: 17px; left: calc(50% + 18px);
    width: calc(100% - 36px); height: 2px; background: var(--border-color);
}
.chk-step.done:not(:last-child)::after   { background: var(--green); }
.chk-step.active:not(:last-child)::after { background: rgba(0,240,255,.3); }
.chk-step-dot {
    width: 34px; height: 34px; border-radius: 50%; position: relative; z-index: 1;
    display: flex; align-items: center; justify-content: center; font-size: .82rem;
    border: 2px solid var(--border-color); background: var(--bg-secondary); color: var(--text-secondary);
}
.chk-step.active .chk-step-dot { border-color: var(--cyan);  background: rgba(0,240,255,.1); color: var(--cyan); }
.chk-step.done   .chk-step-dot { border-color: var(--green); background: rgba(0,255,136,.1); color: var(--green); }
.chk-step-lbl { font-size: .72rem; color: var(--text-secondary); }
.chk-step.active .chk-step-lbl { color: var(--cyan); font-weight: 700; }

/* Cards */
.sub-card  { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 18px; overflow: hidden; }
.sub-ch    { padding: 22px 26px 18px; border-bottom: 1px solid var(--border-color);
             background: linear-gradient(135deg, rgba(153,69,255,.1), rgba(0,240,255,.07));
             display: flex; align-items: center; gap: 14px; }
.sub-ch-icon { width: 44px; height: 44px; border-radius: 12px;
               background: rgba(0,240,255,.12); display: flex; align-items: center;
               justify-content: center; color: var(--cyan); font-size: 1.15rem; flex-shrink: 0; }
.sub-cb    { padding: 24px 26px; }

/* Merchant bar */
.merchant-bar {
    background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px;
    padding: 18px 24px; display: flex; align-items: center; justify-content: space-between;
    gap: 14px; flex-wrap: wrap; margin-bottom: 26px;
}
.merchant-avatar {
    width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0;
    background: linear-gradient(135deg, var(--cyan), var(--purple));
    display: flex; align-items: center; justify-content: center; color: #06060a; font-weight: 800; font-size: 1.3rem;
}
.merchant-logo {
    width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0;
    object-fit: cover; border: 1px solid var(--border-color); background: #fff;
}
.merchant-trust {
    display: inline-flex; align-items: center; gap: 8px; margin-top: 4px;
    font-size: .72rem; color: var(--text-secondary);
}

/* Payment method tiles */
.pay-method-tile {
    display: flex; align-items: center; gap: 16px; padding: 16px 18px;
    border: 2px solid var(--border-color); border-radius: 14px;
    background: var(--bg-secondary); cursor: pointer;
    transition: border-color .2s, background .2s, box-shadow .2s; margin-bottom: 12px;
    position: relative; user-select: none;
}
.pay-method-tile:has(input:checked) {
    border-color: var(--cyan); background: rgba(0,240,255,.05);
    box-shadow: 0 0 0 4px rgba(0,240,255,.08);
}
.pay-method-tile:hover { border-color: rgba(0,240,255,.4); }
.pay-method-tile input[type=radio] { position: absolute; opacity: 0; pointer-events: none; }
.pay-method-radio {
    width: 20px; height: 20px; border: 2px solid var(--border-color); border-radius: 50%;
    flex-shrink: 0; display: flex; align-items: center; justify-content: center; transition: border-color .2s;
}
.pay-method-tile:has(input:checked) .pay-method-radio { border-color: var(--cyan); background: var(--cyan); }
.pay-method-tile:has(input:checked) .pay-method-radio::after {
    content: ''; width: 8px; height: 8px; background: #06060a; border-radius: 50%;
}
.pay-method-icon {
    width: 42px; height: 42px; border-radius: 10px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
}
.pay-method-logo {
    width: 2.4rem; height: 2.4rem; border-radius: .625rem; flex-shrink: 0;
    object-fit: contain; border: 1px solid var(--border-color); background: #fff; padding: 4px;
}
.pay-method-icon.upi      { background: rgba(0,240,255,.12); color: var(--cyan); }
.pay-method-icon.cashfree { background: rgba(153,69,255,.15); color: var(--purple); }
.pay-method-icon.manual   { background: rgba(0,255,136,.1);  color: var(--green); }
.pay-method-label { font-weight: 700; font-size: .95rem; margin-bottom: 2px; }
.pay-method-desc  { font-size: .76rem; color: var(--text-secondary); }
.pay-method-badge {
    margin-left: auto; padding: 3px 10px; border-radius: 20px;
    font-size: .68rem; font-weight: 700; flex-shrink: 0;
}

/* CTA */
.chk-cta { margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-color); }
.chk-cta-btn {
    width: 100%; padding: 14px; background: linear-gradient(135deg, var(--cyan), var(--magenta));
    color: #06060a; border: none; border-radius: 12px; font-size: .97rem; font-weight: 800;
    cursor: pointer; font-family: inherit; transition: opacity .2s, transform .15s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.chk-cta-btn:hover { opacity: .88; transform: translateY(-1px); }

/* Order summary */
.order-row   { display: flex; justify-content: space-between; align-items: center; font-size: .87rem; padding: 9px 0; border-bottom: 1px solid var(--border-color); }
.order-row:last-child { border: none; }
.order-total { display: flex; justify-content: space-between; align-items: center; padding: 16px 0 0; font-size: 1.15rem; font-weight: 800; }

/* Feature list */
.plan-features { list-style: none; padding: 0; margin: 14px 0 0; }
.plan-features li { display: flex; align-items: center; gap: 8px; font-size: .82rem;
                    color: var(--text-secondary); padding: 4px 0; }
.plan-features li i { color: var(--green); font-size: .75rem; }

/* Guarantees */
.trust-bar {
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
    padding: 12px 16px; background: rgba(0,240,255,.04);
    border: 1px solid rgba(0,240,255,.1); border-radius: 10px; margin-top: 16px;
    font-size: .74rem; color: var(--text-secondary);
}
.trust-bar i { color: var(--cyan); }
.pay-methods-inline {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
}
.pay-method-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 999px;
    border: 1px solid var(--border-color);
    background: var(--bg-secondary);
    color: var(--text-secondary);
    font-size: .72rem;
    font-weight: 600;
}

@media (max-width: 740px) {
    .sub-grid { grid-template-columns: 1fr; }
    .merchant-bar { flex-direction: column; align-items: flex-start; gap: 10px; }
    .sub-wrap { padding: 0; }
    .chk-steps { gap: 0; }
    .chk-step-lbl { font-size: .65rem; }
}
@media (max-width: 480px) {
    .chk-step-dot { width: 28px; height: 28px; font-size: .74rem; }
    .sub-cb { padding: 16px; }
    .sub-ch { padding: 14px 16px 12px; }
    .chk-cta-btn { font-size: .88rem; padding: 12px; }
    .pay-method-tile { padding: 12px 14px; }
    .order-total { font-size: 1rem; }
}
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<?php
$invoiceLogoUrl = $invoiceSettings['invoice_logo_url'] ?? $invoiceSettings['invoice_logo'] ?? '';
$appLogoUrl = $appMeta['logo_url'] ?? '';
$resolveLogoUrl = static function (?string $url): string {
    $logo = trim((string) $url);
    if ($logo === '') {
        return '';
    }
    if ($logo[0] === '/' || str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://') || str_starts_with($logo, '//')) {
        return $logo;
    }
    return '/' . ltrim($logo, '/');
};
$appLogoUrl = $resolveLogoUrl($appLogoUrl);
$invoiceLogoUrl = $resolveLogoUrl($invoiceLogoUrl);
$upiGatewayLogo = $resolveLogoUrl($paymentSettings['payment_upi_logo'] ?? '');
$cashfreeGatewayLogo = $resolveLogoUrl($paymentSettings['payment_cashfree_logo'] ?? '');
$manualGatewayLogo = $resolveLogoUrl($paymentSettings['payment_manual_review_logo'] ?? '');
if ($appLogoUrl === '') {
    $iconCandidate = (string) ($appMeta['icon'] ?? '');
    if (preg_match('#^(https?://|/)#', $iconCandidate) === 1) {
        $appLogoUrl = $iconCandidate;
    }
}
?>
<div class="sub-wrap">
    <?php if (\Core\Helpers::hasFlash('error')): ?>
    <div class="alert alert-error" style="margin-bottom:14px;"><?= View::e(\Core\Helpers::getFlash('error')) ?></div>
    <?php endif; ?>
    <?php if (\Core\Helpers::hasFlash('success')): ?>
    <div class="alert alert-success" style="margin-bottom:14px;"><?= View::e(\Core\Helpers::getFlash('success')) ?></div>
    <?php endif; ?>

    <!-- Back link -->
    <a href="<?= View::e($appMeta['url'] ?? '/plans') ?>"
       style="color:var(--text-secondary);font-size:.875rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;margin-bottom:28px;">
        <i class="fas fa-arrow-left"></i> Back to Plans
    </a>

    <!-- Progress steps -->
    <div class="chk-steps">
        <div class="chk-step done">
            <div class="chk-step-dot"><i class="fas fa-check"></i></div>
            <span class="chk-step-lbl">Plan</span>
        </div>
        <div class="chk-step done">
            <div class="chk-step-dot"><i class="fas fa-check"></i></div>
            <span class="chk-step-lbl">Billing Info</span>
        </div>
        <div class="chk-step active">
            <div class="chk-step-dot"><i class="fas fa-credit-card"></i></div>
            <span class="chk-step-lbl">Payment</span>
        </div>
        <div class="chk-step">
            <div class="chk-step-dot"><i class="fas fa-check-circle"></i></div>
            <span class="chk-step-lbl">Confirm</span>
        </div>
    </div>

    <!-- Merchant header bar -->
    <div class="merchant-bar">
        <div style="display:flex;align-items:center;gap:14px;">
            <?php if (!empty($appLogoUrl)): ?>
            <img src="<?= View::e($appLogoUrl) ?>" alt="<?= View::e($appMeta['name'] ?? ucfirst($app)) ?> logo" class="merchant-logo">
            <?php else: ?>
            <div class="merchant-avatar"><?= strtoupper(substr($appMeta['name'] ?? $app, 0, 1)) ?></div>
            <?php endif; ?>
            <div>
                <div style="font-weight:800;font-size:1.1rem;"><?= View::e($appMeta['name'] ?? ucfirst($app)) ?></div>
                <div style="font-size:.78rem;color:var(--text-secondary);">Secure Checkout &mdash; <?= View::e($plan['name']) ?></div>
                <div class="merchant-trust">
                    <i class="fas fa-lock" style="color:var(--green);"></i> HTTPS Secure
                    <span>·</span>
                    <i class="fas fa-shield-alt" style="color:var(--cyan);"></i> PCI-DSS Compliant
                </div>
            </div>
        </div>
        <div style="text-align:right;">
            <?php if (!empty($invoiceLogoUrl)): ?>
            <div style="margin-bottom:8px;">
                <img src="<?= View::e($invoiceLogoUrl) ?>" alt="Secure brand logo" style="max-width:120px;max-height:34px;object-fit:contain;">
            </div>
            <?php endif; ?>
            <div style="font-size:.75rem;color:var(--text-secondary);margin-bottom:3px;">Total Due</div>
            <div style="font-size:1.5rem;font-weight:900;color:var(--cyan);">
                <?= View::e($plan['currency'] ?? 'USD') ?> <?= number_format((float)($plan['price'] ?? 0), 2) ?>
            </div>
            <?php if (!empty($existing['plan_name'])): ?>
            <div style="margin-top:4px;">
                <span style="padding:3px 10px;border-radius:999px;background:rgba(0,255,136,.1);color:var(--green);font-size:.7rem;font-weight:700;border:1px solid rgba(0,255,136,.2);">
                    <i class="fas fa-check-circle" style="margin-right:3px;"></i>Current: <?= View::e($existing['plan_name']) ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Downgrade warning banner -->
    <?php if (!empty($isDowngrade)): ?>
    <div style="background:rgba(255,170,0,.12);border:1px solid rgba(255,170,0,.35);border-radius:12px;padding:16px 20px;margin-bottom:24px;display:flex;align-items:flex-start;gap:14px;">
        <div style="flex-shrink:0;width:38px;height:38px;border-radius:10px;background:rgba(255,170,0,.15);display:flex;align-items:center;justify-content:center;color:#ffaa00;font-size:1.1rem;">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div style="flex:1;">
            <div style="font-weight:700;font-size:.95rem;color:#ffaa00;margin-bottom:4px;">Downgrade Not Recommended</div>
            <div style="font-size:.82rem;color:var(--text-secondary);line-height:1.6;">
                You currently have an active <strong style="color:var(--text-primary);"><?= View::e($existing['plan_name'] ?? 'higher-tier plan') ?></strong> which includes more features.
                Switching to this lower-tier plan will reduce your limits and features.
                To downgrade, you must first cancel your current plan.
            </div>
            <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap;">
                <a href="/plans/payment/<?= htmlspecialchars((string) ($existing['payment_id'] ?? '')) ?>"
                   style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:8px;background:linear-gradient(135deg,var(--purple),var(--cyan));color:#06060a;font-weight:700;font-size:.82rem;text-decoration:none;">
                    <i class="fas fa-shield-alt"></i> Keep Current Plan
                </a>
                <a href="/plans"
                   style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:8px;border:1px solid var(--border-color);color:var(--text-secondary);font-size:.82rem;text-decoration:none;">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="sub-grid">
        <!-- Left: payment method -->
        <div>
            <div class="sub-card">
                <div class="sub-ch">
                    <div class="sub-ch-icon"><i class="fas fa-shield-alt"></i></div>
                    <div>
                        <div style="font-weight:800;font-size:1.05rem;">Choose Payment Method</div>
                        <div style="font-size:.78rem;color:var(--text-secondary);">Select how you'd like to complete this payment</div>
                    </div>
                </div>
                <div class="sub-cb">
                    <form method="POST" action="/plans/project/<?= urlencode($app) ?>/<?= urlencode($plan['slug'] ?? $plan['id']) ?>">
                        <?= \Core\Security::csrfField() ?>
                        <?php
                        $defaultPaymentMethod = $paymentSettings['payment_method'] ?? 'request';
                        $canUseManualReview   = ($paymentSettings['payment_manual_review_enabled'] ?? '1') === '1';
                        $cashfreeEnabled = ($paymentSettings['payment_cashfree_enabled'] ?? '0') === '1'
                            && !empty($paymentSettings['payment_cashfree_app_id'])
                            && !empty($paymentSettings['payment_cashfree_secret']);
                        $hasUpi = !empty($paymentSettings['payment_upi_id']);

                        // Ensure the default pre-selection is one that is actually available.
                        if ($defaultPaymentMethod === 'cashfree' && !$cashfreeEnabled) {
                            $defaultPaymentMethod = $hasUpi ? 'upi' : 'request';
                        }
                        if ($defaultPaymentMethod === 'upi' && !$hasUpi) {
                            $defaultPaymentMethod = $cashfreeEnabled ? 'cashfree' : 'request';
                        }
                        if ($defaultPaymentMethod === 'request' && !$canUseManualReview) {
                            $defaultPaymentMethod = $hasUpi ? 'upi' : ($cashfreeEnabled ? 'cashfree' : '');
                        }
                        $anyMethodAvailable = $hasUpi || $cashfreeEnabled || $canUseManualReview;
                        ?>

                        <?php if (!$anyMethodAvailable): ?>
                        <div style="padding:18px;border-radius:10px;background:rgba(255,107,107,.08);border:1px solid rgba(255,107,107,.2);color:var(--red);font-size:.85rem;text-align:center;">
                            <i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>
                            No payment methods are configured. Please contact the administrator to set up payments.
                        </div>
                        <?php endif; ?>

                        <?php if ($hasUpi): ?>
                        <label class="pay-method-tile">
                            <input type="radio" name="payment_method" value="upi" <?= $defaultPaymentMethod === 'upi' ? 'checked' : '' ?>>
                            <div class="pay-method-radio"></div>
                            <?php if ($upiGatewayLogo !== ''): ?>
                            <img src="<?= View::e($upiGatewayLogo) ?>" alt="UPI logo" class="pay-method-logo">
                            <?php else: ?>
                            <div class="pay-method-icon upi"><i class="fas fa-qrcode"></i></div>
                            <?php endif; ?>
                            <div style="flex:1;">
                                <div class="pay-method-label">UPI / QR Code</div>
                                <div class="pay-method-desc">Scan QR or use UPI app to pay instantly</div>
                            </div>
                            <span class="pay-method-badge" style="background:rgba(0,240,255,.1);color:var(--cyan);border:1px solid rgba(0,240,255,.2);">Instant</span>
                        </label>
                        <?php endif; ?>

                        <?php if ($cashfreeEnabled): ?>
                        <label class="pay-method-tile">
                            <input type="radio" name="payment_method" value="cashfree" <?= $defaultPaymentMethod === 'cashfree' ? 'checked' : '' ?>>
                            <div class="pay-method-radio"></div>
                            <?php if ($cashfreeGatewayLogo !== ''): ?>
                            <img src="<?= View::e($cashfreeGatewayLogo) ?>" alt="Cashfree logo" class="pay-method-logo">
                            <?php else: ?>
                            <div class="pay-method-icon cashfree"><i class="fas fa-bolt"></i></div>
                            <?php endif; ?>
                            <div style="flex:1;">
                                <div class="pay-method-label">Cashfree Payments</div>
                                <div class="pay-method-desc">Cards, UPI, Netbanking via Cashfree</div>
                            </div>
                            <span class="pay-method-badge" style="background:rgba(153,69,255,.12);color:var(--purple);border:1px solid rgba(153,69,255,.2);">Popular</span>
                        </label>
                        <?php endif; ?>

                        <?php if ($canUseManualReview): ?>
                        <label class="pay-method-tile">
                            <input type="radio" name="payment_method" value="request" <?= $defaultPaymentMethod === 'request' ? 'checked' : '' ?>>
                            <div class="pay-method-radio"></div>
                            <?php if ($manualGatewayLogo !== ''): ?>
                            <img src="<?= View::e($manualGatewayLogo) ?>" alt="Manual review logo" class="pay-method-logo">
                            <?php else: ?>
                            <div class="pay-method-icon manual"><i class="fas fa-clipboard-check"></i></div>
                            <?php endif; ?>
                            <div style="flex:1;">
                                <div class="pay-method-label">Manual Review</div>
                                <div class="pay-method-desc">Admin verifies and activates your plan</div>
                            </div>
                        </label>
                        <?php endif; ?>

                        <div class="chk-cta">
                            <?php if ($anyMethodAvailable): ?>
                            <button type="submit" class="chk-cta-btn">
                                <i class="fas fa-lock"></i>
                                Continue to Payment
                            </button>
                            <?php else: ?>
                            <button type="button" class="chk-cta-btn" disabled style="opacity:.5;cursor:not-allowed;">
                                <i class="fas fa-ban"></i>
                                Payment Unavailable
                            </button>
                            <?php endif; ?>
                            <div class="trust-bar">
                                <i class="fas fa-shield-alt"></i> <span>Secure payment powered by Cashfree Payments</span>
                                <span style="margin:0 6px;">·</span>
                                <i class="fas fa-lock"></i> <span>HTTPS Secure</span>
                                <span style="margin:0 6px;">·</span>
                                <i class="fas fa-id-card"></i> <span>PCI-DSS compliant</span>
                            </div>
                            <div class="pay-methods-inline" aria-label="Accepted methods">
                                <span class="pay-method-pill"><i class="fas fa-mobile-alt"></i> UPI</span>
                                <span class="pay-method-pill"><i class="fas fa-credit-card"></i> Cards</span>
                                <span class="pay-method-pill"><i class="fas fa-university"></i> Net Banking</span>
                                <span class="pay-method-pill"><i class="fas fa-wallet"></i> Wallets</span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: order summary -->
        <div>
            <div class="sub-card">
                <div class="sub-ch">
                    <div class="sub-ch-icon" style="background:rgba(153,69,255,.15);color:var(--purple);"><i class="fas fa-receipt"></i></div>
                    <div>
                        <div style="font-weight:800;font-size:1rem;">Order Summary</div>
                        <div style="font-size:.78rem;color:var(--text-secondary);"><?= View::e($plan['name']) ?></div>
                    </div>
                </div>
                <div class="sub-cb">
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Plan</span>
                        <span style="font-weight:700;"><?= View::e($plan['name']) ?></span>
                    </div>
                    <?php if (!empty($appMeta['name'])): ?>
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Product</span>
                        <span><?= View::e($appMeta['name']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Billing</span>
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
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Subtotal</span>
                        <span><?= View::e($plan['currency'] ?? 'USD') ?> <?= number_format((float) ($plan['price'] ?? 0), 2) ?></span>
                    </div>
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Processing fee</span>
                        <span><?= View::e($plan['currency'] ?? 'USD') ?> 0.00</span>
                    </div>
                    <div class="order-total">
                        <span>Total Due</span>
                        <span style="color:var(--cyan);"><?= View::e($plan['currency'] ?? 'USD') ?> <?= number_format((float) ($plan['price'] ?? 0), 2) ?></span>
                    </div>
                    <div style="margin-top:12px;padding:10px 12px;border-radius:10px;background:rgba(0,240,255,.05);border:1px solid rgba(0,240,255,.12);font-size:.76rem;color:var(--text-secondary);line-height:1.6;">
                        <div><i class="fas fa-receipt" style="color:var(--cyan);margin-right:6px;"></i>No extra fees applied.</div>
                        <div style="margin-top:4px;"><i class="fas fa-undo" style="color:var(--cyan);margin-right:6px;"></i>Refund and cancellation are handled according to your plan policy.</div>
                    </div>

                    <?php if (!empty($plan['features']) && is_array($plan['features'])): ?>
                    <hr style="border:none;border-top:1px solid var(--border-color);margin:16px 0 8px;">
                    <div style="font-size:.72rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.07em;margin-bottom:6px;">What's included</div>
                    <ul class="plan-features">
                        <?php foreach ($plan['features'] as $feat): ?>
                        <li><i class="fas fa-check"></i><?= View::e($feat) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                    <?php if (!empty($plan['description'])): ?>
                    <div style="margin-top:14px;padding:12px 14px;background:var(--bg-secondary);border-radius:10px;font-size:.8rem;color:var(--text-secondary);line-height:1.5;">
                        <?= nl2br(View::e($plan['description'])) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
