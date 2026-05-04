<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<?php if (Helpers::hasFlash('success')): ?>
<div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
<div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

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
                <form method="POST" action="/plans/payment/<?= (int) $payment['id'] ?>/cancel" style="margin:0;">
                    <?= \Core\Security::csrfField() ?>
                    <button type="submit" class="btn btn-secondary" onclick="return confirm('Cancel this subscription?')">Cancel Subscription</button>
                </form>
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
