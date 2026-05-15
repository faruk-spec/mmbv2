<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('styles'); ?>
<style>
/* ── Plans Payment page redesign ──────────────────────────────────────── */
.pay-wrap  { max-width: 960px; margin: 0 auto; }
.pay-grid  { display: grid; grid-template-columns: 1fr 300px; gap: 28px; align-items: start; }
.secure-brand-bar {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 22px;
}
.secure-brand-logo {
    max-width: 130px;
    max-height: 36px;
    object-fit: contain;
    background: #fff;
    border-radius: 8px;
    padding: 4px 6px;
}
.secure-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid rgba(0,255,136,.3);
    color: var(--green);
    background: rgba(0,255,136,.08);
    border-radius: 999px;
    padding: 6px 10px;
    font-size: .72rem;
    font-weight: 700;
}
.trust-methods {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 12px;
}
.trust-method-chip {
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
.checkout-assurance {
    margin-top: 14px;
    padding: 12px 14px;
    border-radius: 10px;
    border: 1px solid rgba(0,240,255,.18);
    background: rgba(0,240,255,.05);
    font-size: .78rem;
    color: var(--text-secondary);
    line-height: 1.6;
}
.checkout-assurance strong { color: var(--text-primary); }

/* Progress steps (same style as subscribe page) */
.pp-steps  { display: flex; align-items: center; justify-content: center; gap: 0; margin-bottom: 32px; }
.pp-step   { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; position: relative; text-align: center; }
.pp-step:not(:last-child)::after {
    content: ''; position: absolute; top: 17px; left: calc(50% + 18px);
    width: calc(100% - 36px); height: 2px; background: var(--border-color);
}
.pp-step.done:not(:last-child)::after   { background: var(--green); }
.pp-step.active:not(:last-child)::after { background: rgba(0,240,255,.3); }
.pp-dot {
    width: 34px; height: 34px; border-radius: 50%; position: relative; z-index: 1;
    display: flex; align-items: center; justify-content: center; font-size: .82rem;
    border: 2px solid var(--border-color); background: var(--bg-secondary); color: var(--text-secondary);
}
.pp-step.active .pp-dot { border-color: var(--cyan);  background: rgba(0,240,255,.1); color: var(--cyan); }
.pp-step.done   .pp-dot { border-color: var(--green); background: rgba(0,255,136,.1); color: var(--green); }
.pp-step-lbl { font-size: .72rem; color: var(--text-secondary); }
.pp-step.active .pp-step-lbl { color: var(--cyan); font-weight: 700; }

/* Cards */
.pay-card  { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 18px; overflow: hidden; }
.pay-ch    { padding: 22px 26px 18px; border-bottom: 1px solid var(--border-color);
             background: linear-gradient(135deg, rgba(153,69,255,.1), rgba(0,240,255,.07));
             display: flex; align-items: center; gap: 14px; }
.pay-ch-icon { width: 44px; height: 44px; border-radius: 12px;
               display: flex; align-items: center; justify-content: center;
               font-size: 1.2rem; flex-shrink: 0; }
.pay-cb    { padding: 24px 26px; }

/* Status badge */
.status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px;
                border-radius: 999px; font-size: .78rem; font-weight: 700; border: 1px solid transparent; }
.status-pending   { background: rgba(255,152,0,.12); color: #ff9800; border-color: rgba(255,152,0,.25); }
.status-paid      { background: rgba(0,255,136,.1);  color: var(--green); border-color: rgba(0,255,136,.2); }
.status-cancelled { background: rgba(255,60,60,.1);  color: var(--red);   border-color: rgba(255,60,60,.2); }
.status-default   { background: rgba(0,240,255,.1);  color: var(--cyan);  border-color: rgba(0,240,255,.2); }

/* Stat tiles row */
.stat-tiles { display: grid; grid-template-columns: repeat(3,1fr); gap: 14px; margin-bottom: 24px; }
.stat-tile  { background: var(--bg-secondary); border: 1px solid var(--border-color);
              border-radius: 14px; padding: 16px; }
.stat-tile-lbl { font-size: .68rem; color: var(--text-secondary); text-transform: uppercase;
                  letter-spacing: .06em; margin-bottom: 6px; }
.stat-tile-val { font-size: 1.1rem; font-weight: 800; }

/* QR panel */
.upi-panel { display: flex; gap: 24px; align-items: flex-start; flex-wrap: wrap; }
.upi-qr-box {
    padding: 12px; background: #fff; border-radius: 14px;
    display: inline-flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 20px rgba(0,0,0,.2);
}

/* Actions row */
.pay-actions { margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-color);
               display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.pay-btn-primary {
    padding: 12px 24px; background: linear-gradient(135deg, var(--cyan), var(--magenta));
    color: #06060a; border: none; border-radius: 11px; font-size: .9rem; font-weight: 700;
    cursor: pointer; font-family: inherit; text-decoration: none; display: inline-flex;
    align-items: center; gap: 7px; transition: opacity .2s, transform .15s;
}
.pay-btn-primary:hover { opacity: .88; transform: translateY(-1px); }
.pay-btn-secondary {
    padding: 12px 22px; background: var(--bg-secondary); border: 1.5px solid var(--border-color);
    border-radius: 11px; font-size: .88rem; font-weight: 600; color: var(--text-primary);
    cursor: pointer; font-family: inherit; text-decoration: none; display: inline-flex;
    align-items: center; gap: 7px; transition: border-color .2s;
}
.pay-btn-secondary:hover { border-color: var(--cyan); color: var(--cyan); }
.pay-btn-danger {
    padding: 12px 22px; background: rgba(255,60,60,.08); border: 1.5px solid rgba(255,60,60,.3);
    border-radius: 11px; font-size: .88rem; font-weight: 600; color: var(--red);
    cursor: pointer; font-family: inherit; text-decoration: none; display: inline-flex;
    align-items: center; gap: 7px; transition: background .2s;
}
.pay-btn-danger:hover { background: rgba(255,60,60,.14); }

/* Summary */
.order-row   { display: flex; justify-content: space-between; align-items: center;
               font-size: .87rem; padding: 9px 0; border-bottom: 1px solid var(--border-color); }
.order-row:last-child { border: none; }
.order-total { display: flex; justify-content: space-between; align-items: center;
               padding: 16px 0 0; font-size: 1.15rem; font-weight: 800; }

/* Success banner */
.paid-banner {
    display: flex; align-items: center; gap: 16px; padding: 18px 22px;
    background: rgba(0,255,136,.08); border: 1.5px solid rgba(0,255,136,.25);
    border-radius: 14px; margin-bottom: 24px;
}
.paid-banner-icon {
    width: 48px; height: 48px; border-radius: 50%; flex-shrink: 0;
    background: rgba(0,255,136,.15); display: flex; align-items: center;
    justify-content: center; font-size: 1.4rem; color: var(--green);
}
.paid-banner-copy { font-size: .82rem; color: var(--text-secondary); margin-top: 4px; line-height: 1.6; }
.paid-banner-meta {
    margin-top: 8px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.paid-meta-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 10px;
    border-radius: 999px;
    border: 1px solid rgba(0,255,136,.3);
    background: rgba(0,255,136,.12);
    color: var(--green);
    font-size: .72rem;
    font-weight: 700;
}

@media (max-width: 740px) {
    .pay-grid { grid-template-columns: 1fr; }
    .stat-tiles { grid-template-columns: 1fr 1fr; }
    .secure-brand-bar { flex-direction: column; align-items: flex-start; gap: 10px; }
    .pay-wrap { padding: 0; }
    .paid-banner { flex-direction: column; gap: 12px; }
    .paid-banner > a { margin-left: 0 !important; width: 100%; text-align: center; }
    .paid-banner-meta { flex-direction: column; gap: 6px; }
    .pay-ch { flex-wrap: wrap; gap: 10px; }
    .pay-actions { flex-direction: column; gap: 8px; }
    .pay-actions > * { width: 100%; justify-content: center; }
    .pp-steps { gap: 0; }
    .pp-step-lbl { font-size: .65rem; }
}
@media (max-width: 480px) {
    .stat-tiles { grid-template-columns: 1fr; }
    .pp-dot { width: 28px; height: 28px; font-size: .74rem; }
    .pay-cb { padding: 16px; }
    .pay-ch { padding: 14px 16px 12px; }
    .order-total { font-size: 1rem; }
    .upi-panel { flex-direction: column; align-items: center; }
}
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<?php
$invoiceLogoUrl = $invoiceSettings['invoice_logo_url'] ?? $invoiceSettings['invoice_logo'] ?? '';
$secureBrandName = $invoiceSettings['invoice_company_name'] ?? (defined('APP_NAME') ? APP_NAME : 'MMB Platform');
$isPendingPayment = in_array((string) ($payment['status'] ?? ''), ['pending', 'verification_pending'], true);
$isPaidPayment = ((string) ($payment['status'] ?? '')) === 'paid';
$transactionId = (string) ($payment['provider_order_id'] ?? $payment['reference'] ?? ('TXN-' . (int) ($payment['id'] ?? 0)));
?>

<?php if (Helpers::hasFlash('success')): ?>
<div class="alert alert-success" style="max-width:960px;margin:0 auto 16px;"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
<div class="alert alert-error" style="max-width:960px;margin:0 auto 16px;"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div class="pay-wrap">
    <a href="/plans" style="color:var(--text-secondary);font-size:.875rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;margin-bottom:28px;">
        <i class="fas fa-arrow-left"></i> Back to Plans
    </a>

    <div class="secure-brand-bar">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <?php if (!empty($invoiceLogoUrl)): ?>
            <img src="<?= View::e($invoiceLogoUrl) ?>" alt="Secure brand logo" class="secure-brand-logo">
            <?php endif; ?>
            <div>
                <div style="font-weight:800;font-size:1rem;">Secure payment powered by Cashfree Payments</div>
                <div style="font-size:.76rem;color:var(--text-secondary);">Your payment is processed securely with encrypted HTTPS channels.</div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <span class="secure-pill"><i class="fas fa-lock"></i> HTTPS Secure</span>
            <span class="secure-pill"><i class="fas fa-shield-alt"></i> PCI-DSS Compliant</span>
        </div>
    </div>

    <!-- Progress steps -->
    <div class="pp-steps">
        <div class="pp-step done">
            <div class="pp-dot"><i class="fas fa-check"></i></div>
            <span class="pp-step-lbl">Plan</span>
        </div>
        <div class="pp-step done">
            <div class="pp-dot"><i class="fas fa-check"></i></div>
            <span class="pp-step-lbl">Billing Info</span>
        </div>
        <div class="pp-step done">
            <div class="pp-dot"><i class="fas fa-check"></i></div>
            <span class="pp-step-lbl">Payment</span>
        </div>
        <div class="pp-step <?= $payment['status'] === 'paid' ? 'done' : 'active' ?>">
            <div class="pp-dot"><i class="fas fa-<?= $payment['status'] === 'paid' ? 'check' : 'clock' ?>"></i></div>
            <span class="pp-step-lbl">Confirm</span>
        </div>
    </div>

    <?php
    $statusClass = match($payment['status']) {
        'paid'   => 'status-paid',
        'cancelled', 'failed', 'refunded' => 'status-cancelled',
        'pending', 'verification_pending'  => 'status-pending',
        default  => 'status-default',
    };
    $statusIcon = match($payment['status']) {
        'paid'   => 'fa-check-circle',
        'cancelled', 'failed' => 'fa-times-circle',
        'refunded' => 'fa-undo',
        default  => 'fa-clock',
    };
    ?>

    <?php if ($isPaidPayment): ?>
    <div class="paid-banner">
        <div class="paid-banner-icon"><i class="fas fa-check-circle"></i></div>
        <div>
            <div style="font-weight:800;font-size:1.05rem;color:var(--green);">Payment Successful!</div>
            <div class="paid-banner-copy">
                Your <strong><?= View::e($payment['plan_name']) ?></strong> subscription is now active.
                Payment secured by Cashfree Payments.
            </div>
            <div class="paid-banner-meta">
                <span class="paid-meta-chip"><i class="fas fa-receipt"></i> Transaction ID: <?= View::e($transactionId) ?></span>
                <span class="paid-meta-chip"><i class="fas fa-envelope"></i> Confirmation sent via email/SMS with invoice PDF</span>
            </div>
        </div>
        <a href="/plans/payment/<?= (int) $payment['id'] ?>/invoice"
           style="margin-left:auto;padding:9px 18px;background:rgba(0,255,136,.15);border:1px solid rgba(0,255,136,.3);border-radius:9px;color:var(--green);font-size:.82rem;font-weight:700;text-decoration:none;white-space:nowrap;">
            <i class="fas fa-file-invoice"></i> Download Invoice
        </a>
        <a href="/support"
           style="padding:9px 18px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:9px;color:var(--text-primary);font-size:.82rem;font-weight:700;text-decoration:none;white-space:nowrap;">
            <i class="fas fa-life-ring"></i> Need Help?
        </a>
    </div>
    <?php endif; ?>

    <!-- Stat tiles -->
    <div class="stat-tiles">
        <div class="stat-tile">
            <div class="stat-tile-lbl">Amount</div>
            <div class="stat-tile-val" style="color:var(--cyan);"><?= View::e($payment['currency']) ?> <?= number_format((float) $payment['amount'], 2) ?></div>
        </div>
        <div class="stat-tile">
            <div class="stat-tile-lbl">Gateway</div>
            <div class="stat-tile-val" style="font-size:.95rem;"><?= strtoupper(View::e($payment['gateway'])) ?></div>
        </div>
        <div class="stat-tile">
            <div class="stat-tile-lbl">Date</div>
            <div class="stat-tile-val" style="font-size:.92rem;"><?= date('M j, Y', strtotime($payment['created_at'])) ?></div>
        </div>
    </div>

    <div class="pay-grid">
        <!-- Left: payment action -->
        <div>
            <div class="pay-card">
                <div class="pay-ch">
                    <?php if ($payment['gateway'] === 'upi'): ?>
                    <div class="pay-ch-icon" style="background:rgba(0,240,255,.12);color:var(--cyan);">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <div>
                        <div style="font-weight:800;font-size:1.05rem;">UPI / QR Payment</div>
                        <div style="font-size:.78rem;color:var(--text-secondary);">Scan with any UPI app to pay the exact amount</div>
                    </div>
                    <?php elseif ($payment['gateway'] === 'cashfree'): ?>
                    <div class="pay-ch-icon" style="background:rgba(153,69,255,.15);color:var(--purple);">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div>
                        <div style="font-weight:800;font-size:1.05rem;">Cashfree Checkout</div>
                        <div style="font-size:.78rem;color:var(--text-secondary);">Complete payment via Cashfree's secure gateway</div>
                    </div>
                    <?php else: ?>
                    <div class="pay-ch-icon" style="background:rgba(0,255,136,.1);color:var(--green);">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div>
                        <div style="font-weight:800;font-size:1.05rem;">Manual Review</div>
                        <div style="font-size:.78rem;color:var(--text-secondary);">Admin will review and activate your subscription</div>
                    </div>
                    <?php endif; ?>
                    <div style="margin-left:auto;">
                        <span class="status-badge <?= $statusClass ?>">
                            <i class="fas <?= $statusIcon ?>"></i>
                            <?= View::e(str_replace('_', ' ', ucfirst($payment['status']))) ?>
                        </span>
                    </div>
                </div>
                <div class="pay-cb">
                    <?php if ($payment['gateway'] === 'upi' && !empty($payment['payment_payload'])): ?>
                    <div class="upi-panel">
                        <div>
                            <div class="upi-qr-box" id="upiQrCode"></div>
                            <div style="text-align:center;margin-top:8px;font-size:.72rem;color:var(--text-secondary);">
                                <i class="fas fa-mobile-alt"></i> Scan to pay
                            </div>
                        </div>
                        <div style="flex:1;min-width:180px;">
                            <div style="font-size:.8rem;color:var(--text-secondary);margin-bottom:10px;">Or copy the UPI payment string:</div>
                            <code style="display:block;padding:12px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:10px;word-break:break-all;font-size:.78rem;"><?= View::e($payment['payment_payload']) ?></code>
                            <div style="margin-top:12px;padding:12px;background:rgba(255,152,0,.08);border:1px solid rgba(255,152,0,.2);border-radius:10px;font-size:.8rem;color:#ff9800;">
                                <i class="fas fa-exclamation-triangle" style="margin-right:5px;"></i>
                                Pay exactly <strong><?= View::e($payment['currency']) ?> <?= number_format((float) $payment['amount'], 2) ?></strong> to avoid delays.
                            </div>
                        </div>
                    </div>
                    <?php elseif ($payment['gateway'] === 'cashfree'): ?>
                        <?php if ($isPendingPayment && !empty($payment['payment_url'])): ?>
                        <a href="<?= View::e($payment['payment_url']) ?>" rel="noopener noreferrer" class="pay-btn-primary">
                            <i class="fas fa-external-link-alt"></i> Open Cashfree Checkout
                        </a>
                        <?php elseif ($isPendingPayment && !empty($payment['provider_payment_session_id'])): ?>
                        <button type="button" id="cashfreePayBtn" class="pay-btn-primary">
                            <i class="fas fa-external-link-alt"></i> Open Cashfree Checkout
                        </button>
                        <?php elseif ($isPaidPayment): ?>
                        <div style="padding:14px;background:rgba(0,255,136,.08);border:1px solid rgba(0,255,136,.25);border-radius:10px;font-size:.84rem;color:var(--green);">
                            <i class="fas fa-check-circle" style="margin-right:6px;"></i>
                            Payment already completed. Cashfree checkout has been closed for this transaction.
                        </div>
                        <?php else: ?>
                        <div style="padding:14px;background:rgba(255,60,60,.08);border:1px solid rgba(255,60,60,.2);border-radius:10px;font-size:.84rem;color:var(--red);">
                            <i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>
                            Cashfree session unavailable. Please
                            <a href="/plans" style="color:inherit;text-decoration:underline;">retry from the plans page</a>.
                        </div>
                        <?php endif; ?>
                        <?php if ($isPendingPayment && (!empty($payment['payment_url']) || !empty($payment['provider_payment_session_id']))): ?>
                        <div style="margin-top:14px;">
                            <a href="/plans/payment/<?= (int) $payment['id'] ?>/return" class="pay-btn-secondary">
                                <i class="fas fa-sync"></i> Check Payment Status
                            </a>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                    <div style="padding:16px;background:rgba(0,240,255,.05);border:1px solid rgba(0,240,255,.15);border-radius:12px;font-size:.88rem;color:var(--text-secondary);line-height:1.7;">
                        <i class="fas fa-info-circle" style="color:var(--cyan);margin-right:6px;"></i>
                        Your subscription request has been submitted. An admin will review it and activate your plan shortly. You will receive an email notification once it is processed.
                    </div>
                    <?php endif; ?>

                    <div class="checkout-assurance">
                        <strong><i class="fas fa-lock"></i> Secure checkout via Cashfree Payments</strong><br>
                        <span><i class="fas fa-shield-alt"></i> PCI-DSS compliant · 256-bit encryption · HTTPS secure</span><br>
                        <span><i class="fas fa-credit-card"></i> UPI · Cards · Net Banking · Wallets</span>
                    </div>

                    <div class="trust-methods" aria-label="Accepted payment methods">
                        <span class="trust-method-chip"><i class="fas fa-mobile-alt"></i> UPI</span>
                        <span class="trust-method-chip"><i class="fas fa-credit-card"></i> Cards</span>
                        <span class="trust-method-chip"><i class="fas fa-university"></i> Net Banking</span>
                        <span class="trust-method-chip"><i class="fas fa-wallet"></i> Wallets</span>
                    </div>

                    <!-- Actions -->
                    <div class="pay-actions">
                        <?php if (in_array($payment['status'], ['pending', 'verification_pending'], true) && in_array($payment['gateway'], ['upi', 'request'], true)): ?>
                        <form method="POST" action="/plans/payment/<?= (int) $payment['id'] ?>/confirm" style="margin:0;">
                            <?= \Core\Security::csrfField() ?>
                            <button type="submit" class="pay-btn-primary">
                                <i class="fas fa-<?= $payment['gateway'] === 'upi' ? 'paper-plane' : 'check' ?>"></i>
                                <?= $payment['gateway'] === 'upi' ? "I've Paid \xe2\x80\x94 Submit for Verification" : 'Confirm Subscription Request' ?>
                            </button>
                        </form>
                        <?php elseif ($payment['status'] === 'paid' && !empty($payment['subscription_id'])): ?>
                        <a href="/plans/payment/<?= (int) $payment['id'] ?>/invoice" class="pay-btn-primary">
                            <i class="fas fa-file-invoice"></i> View Invoice
                        </a>
                        <?php if (!empty($canCancel['allowed'])): ?>
                        <a href="/plans/payment/<?= (int) $payment['id'] ?>/cancel" class="pay-btn-secondary">
                            <i class="fas fa-ban"></i> Cancel Subscription
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($canRefund['allowed']) && ($payment['refund_status'] ?? 'none') === 'none'): ?>
                        <form method="POST" action="/plans/payment/<?= (int) $payment['id'] ?>/refund" style="margin:0;">
                            <?= \Core\Security::csrfField() ?>
                            <button type="submit" class="pay-btn-danger"
                                    onclick="return confirm('Requesting a refund will immediately cancel your subscription and revoke access. Your refund will be reviewed by our team. Are you sure?')">
                                <i class="fas fa-undo"></i> Request Refund
                            </button>
                        </form>
                        <?php elseif (($payment['refund_status'] ?? 'none') !== 'none'): ?>
                        <span class="pay-btn-secondary" style="cursor:default;">
                            <i class="fas fa-info-circle"></i> Refund: <?= View::e(ucfirst($payment['refund_status'])) ?>
                        </span>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: order summary -->
        <div>
            <div class="pay-card">
                <div class="pay-ch">
                    <div class="pay-ch-icon" style="background:rgba(153,69,255,.15);color:var(--purple);">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <div style="font-weight:800;font-size:1rem;">Payment Details</div>
                        <div style="font-size:.78rem;color:var(--text-secondary);"><?= View::e($payment['plan_name']) ?></div>
                    </div>
                </div>
                <div class="pay-cb">
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Plan</span>
                        <span style="font-weight:700;"><?= View::e($payment['plan_name']) ?></span>
                    </div>
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Invoice</span>
                        <span style="font-size:.8rem;font-family:monospace;"><?= View::e($payment['invoice_no']) ?></span>
                    </div>
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Reference</span>
                        <span style="font-size:.78rem;font-family:monospace;"><?= View::e($payment['reference']) ?></span>
                    </div>
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Gateway</span>
                        <span><?= strtoupper(View::e($payment['gateway'])) ?></span>
                    </div>
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Status</span>
                        <span class="status-badge <?= $statusClass ?>" style="padding:3px 8px;font-size:.7rem;">
                            <?= View::e(str_replace('_', ' ', ucfirst($payment['status']))) ?>
                        </span>
                    </div>
                    <?php if (!empty($payment['billing_cycle'])): ?>
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Billing</span>
                        <span><?= View::e($payment['billing_cycle']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Subtotal</span>
                        <span><?= View::e($payment['currency']) ?> <?= number_format((float) $payment['amount'], 2) ?></span>
                    </div>
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Processing Fee</span>
                        <span><?= View::e($payment['currency']) ?> 0.00</span>
                    </div>
                    <div class="order-total">
                        <span>Total</span>
                        <span style="color:var(--cyan);"><?= View::e($payment['currency']) ?> <?= number_format((float) $payment['amount'], 2) ?></span>
                    </div>
                    <div style="margin-top:12px;padding:10px 12px;border-radius:10px;background:rgba(0,240,255,.05);border:1px solid rgba(0,240,255,.14);font-size:.76rem;color:var(--text-secondary);line-height:1.6;">
                        <div><i class="fas fa-receipt" style="color:var(--cyan);margin-right:6px;"></i>No extra fees applied.</div>
                        <div style="margin-top:5px;"><i class="fas fa-undo" style="color:var(--cyan);margin-right:6px;"></i>Refund and cancellation terms apply based on your selected plan policy.</div>
                    </div>
                </div>
            </div>

            <?php if ($payment['status'] === 'paid' && !empty($payment['subscription_id'])): ?>
            <div style="margin-top:16px;padding:14px 18px;background:rgba(0,255,136,.06);border:1px solid rgba(0,255,136,.15);border-radius:14px;font-size:.8rem;color:var(--text-secondary);">
                <i class="fas fa-shield-alt" style="color:var(--green);margin-right:6px;"></i>
                Subscription active. Thank you for your payment!
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<?php if ($payment['gateway'] === 'upi' && !empty($payment['payment_payload'])): ?>
<script src="/assets/js/qrcode.js"></script>
<script>
new QRCode(document.getElementById('upiQrCode'), {
    text: <?= json_encode($payment['payment_payload']) ?>,
    width: 160,
    height: 160,
    colorDark: '#000000',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.H
});
</script>
<?php elseif ($payment['gateway'] === 'cashfree' && !empty($payment['provider_payment_session_id']) && ($paymentSettings['payment_cashfree_enabled'] ?? '0') === '1'): ?>
<script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
<script>
document.getElementById('cashfreePayBtn')?.addEventListener('click', function () {
    const fallbackUrl = <?= json_encode($payment['payment_url'] ?? '') ?>;
    try {
        if (typeof Cashfree !== 'function') {
            if (fallbackUrl) window.location.href = fallbackUrl;
            return;
        }
        const cashfree = Cashfree({ mode: <?= json_encode(($paymentSettings['payment_cashfree_sandbox'] ?? '1') === '1' ? 'sandbox' : 'production') ?> });
        const result = cashfree.checkout({
            paymentSessionId: <?= json_encode($payment['provider_payment_session_id']) ?>,
            redirectTarget: '_self'
        });
        if (result && typeof result.then === 'function') {
            result.catch(function () { if (fallbackUrl) window.location.href = fallbackUrl; });
        }
    } catch (e) {
        if (fallbackUrl) window.location.href = fallbackUrl;
    }
});
</script>
<?php endif; ?>
<?php View::endSection(); ?>
