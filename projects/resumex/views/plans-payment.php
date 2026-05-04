<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<?php if (isset($_SESSION['_flash']['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($_SESSION['_flash']['success']) ?></div>
<?php unset($_SESSION['_flash']['success']); endif; ?>
<?php if (isset($_SESSION['_flash']['error'])): ?>
<div class="alert alert-error"><?= htmlspecialchars($_SESSION['_flash']['error']) ?></div>
<?php unset($_SESSION['_flash']['error']); endif; ?>

<div style="max-width:760px;margin:0 auto;">
    <a href="/projects/resumex/plans" style="color:var(--text-secondary);font-size:.875rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">
        <i class="fas fa-arrow-left"></i> Back to ResumeX Plans
    </a>

    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;overflow:hidden;">
        <div style="padding:24px;border-bottom:1px solid var(--border-color);background:linear-gradient(135deg,rgba(255,107,107,.14),rgba(153,69,255,.10));">
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
                    <div id="resumeUpiQrCode" style="padding:10px;background:#fff;border-radius:12px;"></div>
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
                <button type="button" id="resumeCashfreePayBtn" class="btn btn-primary">Open Cashfree Checkout</button>
                <?php else: ?>
                <p style="color:var(--red);font-size:.82rem;">
                    Cashfree session not available yet. Please retry from the
                    <a href="/projects/resumex/plans" style="color:inherit;text-decoration:underline;">plans page</a>.
                </p>
                <?php endif; ?>
                <a href="/projects/resumex/plans/payment/<?= (int) $payment['id'] ?>/return" class="btn btn-secondary" style="margin-left:10px;">Check Payment Status</a>
            </div>
            <?php else: ?>
            <div style="margin-bottom:24px;padding:16px;border:1px solid var(--border-color);border-radius:10px;background:var(--bg-secondary);">
                <h3 style="margin:0 0 10px;">Manual Review</h3>
                <p style="color:var(--text-secondary);font-size:.85rem;">Your request has been submitted. An admin will review and activate your ResumeX plan.</p>
            </div>
            <?php endif; ?>

            <?php if (in_array($payment['status'], ['pending', 'verification_pending'], true) && in_array($payment['gateway'], ['upi', 'request'], true)): ?>
            <form method="POST" action="/projects/resumex/plans/payment/<?= (int) $payment['id'] ?>/confirm">
                <?= \Core\Security::csrfField() ?>
                <button type="submit" class="btn btn-primary">
                    <?= $payment['gateway'] === 'upi' ? 'I Have Paid - Send for Verification' : 'Confirm Subscription Request' ?>
                </button>
            </form>
            <?php elseif ($payment['status'] === 'paid' && !empty($payment['subscription_id'])): ?>
            <a href="/projects/resumex/plans/invoice/<?= (int) $payment['subscription_id'] ?>" class="btn btn-primary">View Invoice</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<?php if ($payment['gateway'] === 'upi' && !empty($payment['payment_payload'])): ?>
<script src="/assets/js/qrcode.js"></script>
<script>
new QRCode(document.getElementById('resumeUpiQrCode'), {
    text: <?= json_encode($payment['payment_payload']) ?>,
    width: 180,
    height: 180,
    colorDark: '#000000',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.H
});
</script>
<?php elseif ($payment['gateway'] === 'cashfree' && !empty($payment['provider_payment_session_id']) && ($settings['payment_cashfree_enabled'] ?? '0') === '1'): ?>
<script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
<script>
document.getElementById('resumeCashfreePayBtn')?.addEventListener('click', function () {
    const fallbackUrl = <?= json_encode($payment['payment_url'] ?? '') ?>;

    try {
        if (typeof Cashfree !== 'function') {
            if (fallbackUrl) {
                window.location.href = fallbackUrl;
            }
            return;
        }

        const cashfree = Cashfree({mode: <?= json_encode(($settings['payment_cashfree_sandbox'] ?? '1') === '1' ? 'sandbox' : 'production') ?>});
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
