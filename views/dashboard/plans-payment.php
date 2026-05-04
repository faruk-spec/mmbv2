<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<style>
.pay-wrap { max-width: 900px; margin: 0 auto; }
.pay-grid { display: grid; grid-template-columns: 1fr 300px; gap: 24px; align-items: start; }
.pay-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; overflow: hidden; }
.pay-card-header { padding: 20px 24px; border-bottom: 1px solid var(--border-color); background: linear-gradient(135deg,rgba(153,69,255,.1),rgba(0,240,255,.07)); }
.pay-card-body { padding: 24px; }
.status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 14px; border-radius: 999px; font-size: .75rem; font-weight: 700; border: 1px solid transparent; }
.status-pending   { background: rgba(255,152,0,.12); color: #ff9800; border-color: rgba(255,152,0,.25); }
.status-paid      { background: rgba(0,255,136,.1); color: var(--green); border-color: rgba(0,255,136,.2); }
.status-cancelled { background: rgba(255,60,60,.1); color: var(--red); border-color: rgba(255,60,60,.2); }
.status-default   { background: rgba(0,240,255,.1); color: var(--cyan); border-color: rgba(0,240,255,.2); }
.stat-tile { background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px; }
.order-row { display: flex; justify-content: space-between; align-items: center; font-size: .86rem; padding: 8px 0; border-bottom: 1px solid var(--border-color); }
.order-row:last-child { border: none; }
.order-total { display: flex; justify-content: space-between; align-items: center; padding: 14px 0 0; font-size: 1.1rem; font-weight: 800; }
@media (max-width: 720px) {
    .pay-grid { grid-template-columns: 1fr; }
}
</style>

<?php if (Helpers::hasFlash('success')): ?>
<div class="alert alert-success" style="max-width:900px;margin:0 auto 16px;"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
<div class="alert alert-error" style="max-width:900px;margin:0 auto 16px;"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div class="pay-wrap">
    <a href="/plans" style="color:var(--text-secondary);font-size:.875rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">
        <i class="fas fa-arrow-left"></i> Back to Plans
    </a>

    <!-- Header card -->
    <div class="pay-card" style="margin-bottom:20px;">
        <div style="padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:14px;">
                <div style="width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,var(--purple),var(--cyan));display:flex;align-items:center;justify-content:center;color:#06060a;font-size:1.3rem;">
                    <i class="fas fa-receipt"></i>
                </div>
                <div>
                    <div style="font-weight:800;font-size:1.1rem;"><?= View::e($payment['plan_name']) ?> Payment</div>
                    <div style="font-size:.78rem;color:var(--text-secondary);">Invoice <?= View::e($payment['invoice_no']) ?> &middot; Ref <?= View::e($payment['reference']) ?></div>
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
            <span class="status-badge <?= $statusClass ?>">
                <i class="fas <?= $statusIcon ?>"></i>
                <?= View::e(str_replace('_', ' ', ucfirst($payment['status']))) ?>
            </span>
        </div>
    </div>

    <div class="pay-grid">
        <!-- Left: payment action -->
        <div>
            <!-- Stats row -->
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;">
                <div class="stat-tile">
                    <div style="font-size:.7rem;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Amount</div>
                    <div style="font-size:1.1rem;font-weight:800;color:var(--cyan);"><?= View::e($payment['currency']) ?> <?= number_format((float) $payment['amount'], 2) ?></div>
                </div>
                <div class="stat-tile">
                    <div style="font-size:.7rem;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Gateway</div>
                    <div style="font-size:1.1rem;font-weight:800;"><?= strtoupper(View::e($payment['gateway'])) ?></div>
                </div>
                <div class="stat-tile">
                    <div style="font-size:.7rem;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Date</div>
                    <div style="font-size:.95rem;font-weight:700;"><?= date('M j, Y', strtotime($payment['created_at'])) ?></div>
                </div>
            </div>

            <!-- Payment gateway section -->
            <div class="pay-card">
                <div class="pay-card-header">
                    <?php if ($payment['gateway'] === 'upi' && !empty($payment['payment_payload'])): ?>
                    <div style="font-weight:700;font-size:1rem;margin-bottom:2px;"><i class="fas fa-qrcode" style="margin-right:8px;color:var(--cyan);"></i>Scan &amp; Pay via UPI</div>
                    <div style="font-size:.78rem;color:var(--text-secondary);">Use any UPI app to pay the exact amount, then confirm below.</div>
                    <?php elseif ($payment['gateway'] === 'cashfree'): ?>
                    <div style="font-weight:700;font-size:1rem;margin-bottom:2px;"><i class="fas fa-bolt" style="margin-right:8px;color:var(--cyan);"></i>Cashfree Checkout</div>
                    <div style="font-size:.78rem;color:var(--text-secondary);">Continue to Cashfree to complete your payment securely.</div>
                    <?php else: ?>
                    <div style="font-weight:700;font-size:1rem;margin-bottom:2px;"><i class="fas fa-clipboard-check" style="margin-right:8px;color:var(--cyan);"></i>Manual Review</div>
                    <div style="font-size:.78rem;color:var(--text-secondary);">Your request is under review by the admin.</div>
                    <?php endif; ?>
                </div>
                <div class="pay-card-body">
                    <?php if ($payment['gateway'] === 'upi' && !empty($payment['payment_payload'])): ?>
                    <div style="display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap;">
                        <div>
                            <div id="upiQrCode" style="padding:10px;background:#fff;border-radius:12px;display:inline-block;"></div>
                            <div style="text-align:center;margin-top:8px;font-size:.75rem;color:var(--text-secondary);">Scan to pay</div>
                        </div>
                        <div style="flex:1;min-width:180px;">
                            <p style="color:var(--text-secondary);font-size:.85rem;margin-bottom:12px;">Or copy the UPI string:</p>
                            <code style="display:block;padding:10px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;word-break:break-all;font-size:.8rem;"><?= View::e($payment['payment_payload']) ?></code>
                        </div>
                    </div>
                    <?php elseif ($payment['gateway'] === 'cashfree'): ?>
                    <?php if (!empty($payment['payment_url'])): ?>
                    <a href="<?= View::e($payment['payment_url']) ?>" rel="noopener noreferrer" class="btn btn-primary" style="padding:12px 28px;">
                        <i class="fas fa-external-link-alt" style="margin-right:6px;"></i>Open Cashfree Checkout
                    </a>
                    <?php elseif (!empty($payment['provider_payment_session_id'])): ?>
                    <button type="button" id="cashfreePayBtn" class="btn btn-primary" style="padding:12px 28px;">
                        <i class="fas fa-external-link-alt" style="margin-right:6px;"></i>Open Cashfree Checkout
                    </button>
                    <?php else: ?>
                    <p style="color:var(--red);font-size:.82rem;margin:0;">
                        Cashfree session unavailable. Please retry from the
                        <a href="/plans" style="color:inherit;text-decoration:underline;">plans page</a>.
                    </p>
                    <?php endif; ?>
                    <?php if (!empty($payment['payment_url']) || !empty($payment['provider_payment_session_id'])): ?>
                    <div style="margin-top:14px;">
                        <a href="/plans/payment/<?= (int) $payment['id'] ?>/return" class="btn btn-secondary">
                            <i class="fas fa-sync" style="margin-right:6px;"></i>Check Payment Status
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php else: ?>
                    <p style="color:var(--text-secondary);font-size:.88rem;margin:0;line-height:1.6;">
                        An admin will review your subscription request and activate your plan. You'll receive a notification once it's processed.
                    </p>
                    <?php endif; ?>

                    <!-- Action buttons -->
                    <div style="margin-top:20px;padding-top:18px;border-top:1px solid var(--border-color);display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                        <?php if (in_array($payment['status'], ['pending', 'verification_pending'], true) && in_array($payment['gateway'], ['upi', 'request'], true)): ?>
                        <form method="POST" action="/plans/payment/<?= (int) $payment['id'] ?>/confirm" style="margin:0;">
                            <?= \Core\Security::csrfField() ?>
                            <button type="submit" class="btn btn-primary" style="padding:11px 22px;">
                                <i class="fas fa-<?= $payment['gateway'] === 'upi' ? 'paper-plane' : 'check' ?>" style="margin-right:6px;"></i>
                                <?= $payment['gateway'] === 'upi' ? 'I Have Paid — Send for Verification' : 'Confirm Subscription Request' ?>
                            </button>
                        </form>
                        <?php elseif ($payment['status'] === 'paid' && !empty($payment['subscription_id'])): ?>
                        <a href="/plans/payment/<?= (int) $payment['id'] ?>/invoice" class="btn btn-primary" style="padding:11px 22px;">
                            <i class="fas fa-file-invoice" style="margin-right:6px;"></i>View Invoice
                        </a>
                        <?php if (!empty($canCancel['allowed'])): ?>
                        <a href="/plans/payment/<?= (int) $payment['id'] ?>/cancel" class="btn btn-secondary" style="padding:11px 22px;">
                            <i class="fas fa-ban" style="margin-right:6px;"></i>Cancel Subscription
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($canRefund['allowed']) && ($payment['refund_status'] ?? 'none') === 'none'): ?>
                        <form method="POST" action="/plans/payment/<?= (int) $payment['id'] ?>/refund" style="margin:0;">
                            <?= \Core\Security::csrfField() ?>
                            <button type="submit" class="btn btn-secondary" style="padding:11px 22px;" onclick="return confirm('Request a refund for this payment?')">
                                <i class="fas fa-undo" style="margin-right:6px;"></i>Request Refund
                            </button>
                        </form>
                        <?php elseif (($payment['refund_status'] ?? 'none') !== 'none'): ?>
                        <span class="btn btn-secondary" style="cursor:default;padding:11px 22px;">
                            Refund: <?= View::e(ucfirst($payment['refund_status'])) ?>
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
                <div class="pay-card-header">
                    <div style="font-weight:700;font-size:1rem;margin-bottom:2px;"><i class="fas fa-list-ul" style="margin-right:8px;color:var(--purple);"></i>Order Summary</div>
                    <div style="font-size:.78rem;color:var(--text-secondary);"><?= View::e($payment['plan_name']) ?></div>
                </div>
                <div class="pay-card-body">
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Plan</span>
                        <span style="font-weight:600;"><?= View::e($payment['plan_name']) ?></span>
                    </div>
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Invoice</span>
                        <span style="font-size:.8rem;"><?= View::e($payment['invoice_no']) ?></span>
                    </div>
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Gateway</span>
                        <span><?= strtoupper(View::e($payment['gateway'])) ?></span>
                    </div>
                    <div class="order-row">
                        <span style="color:var(--text-secondary);">Status</span>
                        <span class="status-badge <?= $statusClass ?>" style="padding:3px 8px;font-size:.72rem;">
                            <?= View::e(str_replace('_', ' ', ucfirst($payment['status']))) ?>
                        </span>
                    </div>
                    <div class="order-total">
                        <span>Total</span>
                        <span style="color:var(--cyan);"><?= View::e($payment['currency']) ?> <?= number_format((float) $payment['amount'], 2) ?></span>
                    </div>
                </div>
            </div>
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

<div style="max-width:760px;margin:0 auto;">
    <a href="/plans" style="color:var(--text-secondary);font-size:.875rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">
        <i class="fas fa-arrow-left"></i> Back to Plans
    </a>

    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;overflow:hidden;">
        <div style="padding:24px;border-bottom:1px solid var(--border-color);background:linear-gradient(135deg,rgba(153,69,255,.12),rgba(0,240,255,.08));">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
                <div>
                    <h1 style="font-size:1.3rem;font-weight:700;margin:0 0 6px;"><?= View::e($payment['plan_name']) ?> Payment</h1>
                    <div style="color:var(--text-secondary);font-size:.85rem;">Invoice <?= View::e($payment['invoice_no']) ?> &middot; Ref <?= View::e($payment['reference']) ?></div>
                </div>
                <span style="padding:6px 12px;border-radius:999px;font-size:.78rem;font-weight:700;background:rgba(0,240,255,.12);color:var(--cyan);">
                    <?= View::e(str_replace('_', ' ', ucfirst($payment['status']))) ?>
                </span>
            </div>
        </div>

        <div style="padding:24px;">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:24px;">
                <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:10px;padding:14px;">
                    <div style="font-size:.72rem;color:var(--text-secondary);text-transform:uppercase;">Amount</div>
                    <div style="font-size:1.1rem;font-weight:700;"><?= View::e($payment['currency']) ?> <?= number_format((float) $payment['amount'], 2) ?></div>
                </div>
                <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:10px;padding:14px;">
                    <div style="font-size:.72rem;color:var(--text-secondary);text-transform:uppercase;">Gateway</div>
                    <div style="font-size:1.1rem;font-weight:700;"><?= strtoupper(View::e($payment['gateway'])) ?></div>
                </div>
                <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:10px;padding:14px;">
                    <div style="font-size:.72rem;color:var(--text-secondary);text-transform:uppercase;">Created</div>
                    <div style="font-size:1.1rem;font-weight:700;"><?= date('M j, Y H:i', strtotime($payment['created_at'])) ?></div>
                </div>
            </div>

            <?php if ($payment['gateway'] === 'upi' && !empty($payment['payment_payload'])): ?>
            <div style="display:grid;grid-template-columns:200px 1fr;gap:20px;align-items:center;margin-bottom:24px;">
                <div style="display:flex;justify-content:center;">
                    <div id="upiQrCode" style="padding:10px;background:#fff;border-radius:12px;"></div>
                </div>
                <div>
                    <h3 style="margin:0 0 10px;">Scan and pay via UPI</h3>
                    <p style="color:var(--text-secondary);font-size:.85rem;margin-bottom:10px;">Use any UPI app to pay the exact amount, then confirm below.</p>
                    <code style="display:block;padding:10px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;word-break:break-all;"><?= View::e($payment['payment_payload']) ?></code>
                </div>
            </div>
            <?php elseif ($payment['gateway'] === 'cashfree'): ?>
            <div style="margin-bottom:24px;padding:16px;border:1px solid var(--border-color);border-radius:10px;background:var(--bg-secondary);">
                <h3 style="margin:0 0 10px;">Cashfree Checkout</h3>
                <p style="color:var(--text-secondary);font-size:.85rem;">Continue to Cashfree to complete your payment securely.</p>
                <?php if (!empty($payment['payment_url'])): ?>
                <a href="<?= View::e($payment['payment_url']) ?>" rel="noopener noreferrer" class="btn btn-primary">Open Cashfree Checkout</a>
                <?php elseif (!empty($payment['provider_payment_session_id'])): ?>
                <button type="button" id="cashfreePayBtn" class="btn btn-primary">Open Cashfree Checkout</button>
                <?php else: ?>
                <p style="color:var(--red);font-size:.82rem;">
                    Cashfree session not available yet. Please retry from the
                    <a href="/plans" style="color:inherit;text-decoration:underline;">plans page</a>.
                </p>
                <?php endif; ?>
                <a href="/plans/payment/<?= (int) $payment['id'] ?>/return" class="btn btn-secondary" style="margin-left:10px;">Check Payment Status</a>
            </div>
            <?php else: ?>
            <div style="margin-bottom:24px;padding:16px;border:1px solid var(--border-color);border-radius:10px;background:var(--bg-secondary);">
                <h3 style="margin:0 0 10px;">Manual Review</h3>
                <p style="color:var(--text-secondary);font-size:.85rem;">Your request has been submitted. An admin will review and activate your subscription.</p>
            </div>
            <?php endif; ?>

            <?php if (in_array($payment['status'], ['pending', 'verification_pending'], true) && in_array($payment['gateway'], ['upi', 'request'], true)): ?>
            <form method="POST" action="/plans/payment/<?= (int) $payment['id'] ?>/confirm">
                <?= \Core\Security::csrfField() ?>
                <button type="submit" class="btn btn-primary">
                    <?= $payment['gateway'] === 'upi' ? 'I Have Paid - Send for Verification' : 'Confirm Subscription Request' ?>
                </button>
            </form>
            <?php elseif ($payment['status'] === 'paid' && !empty($payment['subscription_id'])): ?>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="/plans/payment/<?= (int) $payment['id'] ?>/invoice" class="btn btn-primary">View Invoice</a>
                <?php if (!empty($canCancel['allowed'])): ?>
                <a href="/plans/payment/<?= (int) $payment['id'] ?>/cancel" class="btn btn-secondary">Cancel Subscription</a>
                <?php endif; ?>
                <?php if (!empty($canRefund['allowed']) && ($payment['refund_status'] ?? 'none') === 'none'): ?>
                <form method="POST" action="/plans/payment/<?= (int) $payment['id'] ?>/refund" style="margin:0;">
                    <?= \Core\Security::csrfField() ?>
                    <button type="submit" class="btn btn-secondary" onclick="return confirm('Request a refund for this payment?')">Request Refund</button>
                </form>
                <?php elseif (($payment['refund_status'] ?? 'none') !== 'none'): ?>
                <span class="btn btn-secondary" style="cursor:default;">Refund: <?= View::e(ucfirst($payment['refund_status'])) ?></span>
                <?php endif; ?>
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
    width: 180,
    height: 180,
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
            if (fallbackUrl) {
                window.location.href = fallbackUrl;
            }
            return;
        }

        const cashfree = Cashfree({mode: <?= json_encode(($paymentSettings['payment_cashfree_sandbox'] ?? '1') === '1' ? 'sandbox' : 'production') ?>});
        const result = cashfree.checkout({
            paymentSessionId: <?= json_encode($payment['provider_payment_session_id']) ?>,
            redirectTarget: '_self'
        });

        if (result && typeof result.then === 'function') {
            result.catch(function () {
                if (fallbackUrl) {
                    window.location.href = fallbackUrl;
                }
            });
        }
    } catch (error) {
        if (fallbackUrl) {
            window.location.href = fallbackUrl;
        }
    }
});
</script>
<?php endif; ?>
<?php View::endSection(); ?>
