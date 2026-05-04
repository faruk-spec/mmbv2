<?php use Core\View; use Core\Auth; use Core\Security; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<div style="max-width:600px;margin:0 auto;">
    <a href="/projects/resumex/plans" style="color:var(--text-secondary);font-size:.875rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;"
       onmouseover="this.style.color='var(--cyan)'" onmouseout="this.style.color='var(--text-secondary)'">
        <i class="fas fa-arrow-left"></i> Back to Plans
    </a>

    <?php
    $isFree    = (float)($plan['price'] ?? 0) === 0.0;
    $planColor = '#9945ff';
    $cur       = htmlspecialchars($plan['currency'] ?? 'USD');
    $upiId     = $settings['payment_upi_id'] ?? '';
    $hasUpi    = !empty($upiId);
    $canUseCashfree = ($settings['payment_cashfree_enabled'] ?? '0') === '1'
        && !empty($settings['payment_cashfree_app_id'] ?? '')
        && !empty($settings['payment_cashfree_secret'] ?? '');
    $defaultPaymentMethod = $settings['payment_method'] ?? 'request';
    ?>

    <div style="background:var(--bg-card);border:2px solid <?= $planColor ?>;border-radius:14px;overflow:hidden;margin-bottom:24px;">
        <div style="background:linear-gradient(135deg,<?= $planColor ?>22,<?= $planColor ?>08);padding:24px;">
            <div style="font-weight:700;font-size:1.1rem;color:<?= $planColor ?>;"><?= htmlspecialchars($plan['name']) ?></div>
            <div style="font-size:1.6rem;font-weight:800;margin:10px 0 4px;">
                <?= $isFree ? 'Free' : ($cur.'&nbsp;'.number_format((float)$plan['price'],2)) ?>
                <?php if (!$isFree): ?>
                <span style="font-size:.75rem;font-weight:400;color:var(--text-secondary);">/ <?= htmlspecialchars($plan['billing_cycle']) ?></span>
                <?php endif; ?>
            </div>
            <div style="font-size:.82rem;color:var(--text-secondary);">Max resumes: <?= $plan['max_resumes'] == 0 ? '&#8734; Unlimited' : (int)$plan['max_resumes'] ?></div>
        </div>
    </div>

    <?php if ($existing && $existing['plan_slug'] === $plan['slug']): ?>
    <div style="background:rgba(0,255,136,.08);border:1px solid var(--green);border-radius:10px;padding:20px;text-align:center;">
        <i class="fas fa-check-circle" style="color:var(--green);font-size:2rem;margin-bottom:10px;"></i>
        <div style="font-weight:700;color:var(--green);margin-bottom:6px;">You're already on this plan!</div>
        <a href="/projects/resumex/plans" style="display:inline-block;margin-top:12px;padding:8px 20px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:6px;color:var(--text-primary);text-decoration:none;font-size:.875rem;">&larr; Back to Plans</a>
    </div>
    <?php else: ?>

    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:24px;">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:6px;"><?= $isFree ? 'Activate Free Plan' : 'Subscribe to Plan' ?></h2>
        <p style="color:var(--text-secondary);font-size:.85rem;margin-bottom:20px;">
            <?php if ($isFree): ?>
                This plan is free &mdash; click below to activate it instantly.
            <?php else: ?>
                Choose a payment method below and complete your subscription payment flow.
            <?php endif; ?>
        </p>

        <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;padding:14px 16px;margin-bottom:16px;font-size:.85rem;">
            <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                <span style="color:var(--text-secondary);">Account</span>
                <span><?= htmlspecialchars(Auth::user()['email'] ?? '') ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <span style="color:var(--text-secondary);">Plan</span>
                <span style="color:<?= $planColor ?>;font-weight:600;"><?= htmlspecialchars($plan['name']) ?></span>
            </div>
        </div>

        <form method="POST" action="/projects/resumex/plans/<?= urlencode($plan['slug']) ?>">
            <?= \Core\Security::csrfField() ?>
            <?php if (!$isFree): ?>
            <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:20px;">
                <?php if ($hasUpi): ?>
                <label style="display:flex;align-items:center;gap:10px;padding:12px;border:1px solid var(--border-color);border-radius:10px;background:var(--bg-secondary);cursor:pointer;">
                    <input type="radio" name="payment_method" value="upi" <?= $defaultPaymentMethod === 'upi' ? 'checked' : '' ?>>
                    <span><strong>UPI QR</strong><br><span style="font-size:.78rem;color:var(--text-secondary);">Pay exact amount using <?= htmlspecialchars($upiId) ?></span></span>
                </label>
                <?php endif; ?>
                <?php if ($canUseCashfree): ?>
                <label style="display:flex;align-items:center;gap:10px;padding:12px;border:1px solid var(--border-color);border-radius:10px;background:var(--bg-secondary);cursor:pointer;">
                    <input type="radio" name="payment_method" value="cashfree" <?= $defaultPaymentMethod === 'cashfree' ? 'checked' : '' ?>>
                    <span><strong>Cashfree Checkout</strong><br><span style="font-size:.78rem;color:var(--text-secondary);">Hosted secure checkout</span></span>
                </label>
                <?php endif; ?>
                <label style="display:flex;align-items:center;gap:10px;padding:12px;border:1px solid var(--border-color);border-radius:10px;background:var(--bg-secondary);cursor:pointer;">
                    <input type="radio" name="payment_method" value="request" <?= (!$hasUpi && !$canUseCashfree) || $defaultPaymentMethod === 'request' ? 'checked' : '' ?>>
                    <span><strong>Manual Review</strong><br><span style="font-size:.78rem;color:var(--text-secondary);">Submit request for admin approval</span></span>
                </label>
            </div>
            <?php endif; ?>
            <div style="display:flex;gap:12px;">
                <button type="submit" style="flex:1;padding:12px;background:linear-gradient(135deg,<?= $planColor ?>,<?= $planColor ?>bb);border:none;border-radius:8px;color:#fff;font-size:.9rem;font-weight:700;cursor:pointer;transition:opacity .2s;"
                        onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                    <?= $isFree ? '&#10003; Activate Now' : '&rarr; Continue to Payment' ?>
                </button>
                <a href="/projects/resumex/plans" style="padding:12px 20px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);text-decoration:none;font-size:.85rem;display:inline-flex;align-items:center;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php View::endSection(); ?>
