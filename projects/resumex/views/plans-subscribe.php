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
    $upiId     = $settings['upi_id'] ?? '';
    $hasUpi    = !empty($upiId);
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
                After subscribing, <?= $hasUpi ? 'complete payment via UPI QR, or your' : 'your' ?> request will be reviewed by an admin.
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

        <?php if (!$isFree && $hasUpi): ?>
        <div style="background:rgba(153,69,255,.06);border:1px solid rgba(153,69,255,.2);border-radius:10px;padding:16px;margin-bottom:20px;text-align:center;">
            <div style="font-size:.8rem;font-weight:700;color:var(--purple);margin-bottom:10px;text-transform:uppercase;letter-spacing:.05em;">
                <i class="fas fa-qrcode"></i> Pay via UPI
            </div>
            <div style="font-size:.82rem;color:var(--text-secondary);margin-bottom:12px;">
                Amount: <strong style="color:var(--text-primary);"><?= $cur ?>&nbsp;<?= number_format((float)$plan['price'],2) ?></strong>
                &middot; UPI ID: <code><?= htmlspecialchars($upiId) ?></code>
            </div>
            <?php
            $upiAmount  = number_format((float)$plan['price'], 2, '.', '');
            $upiPayload = 'upi://pay?pa=' . urlencode($upiId) . '&pn=ResumeX&am=' . $upiAmount . '&cu=' . urlencode($plan['currency'] ?? 'INR') . '&tn=' . urlencode('ResumeX ' . $plan['name'] . ' Plan');
            $qrUrl      = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' . urlencode($upiPayload);
            ?>
            <img src="<?= $qrUrl ?>" alt="UPI QR Code" style="border-radius:8px;border:3px solid rgba(153,69,255,.3);">
            <div style="font-size:.75rem;color:var(--text-secondary);margin-top:8px;">Scan with any UPI app</div>
        </div>
        <?php endif; ?>

        <form method="POST" action="/projects/resumex/plans/<?= urlencode($plan['slug']) ?>">
            <?= \Core\Security::csrfField() ?>
            <?php if (!$isFree && $hasUpi): ?>
            <input type="hidden" name="payment_method" value="upi">
            <?php endif; ?>
            <div style="display:flex;gap:12px;">
                <button type="submit" style="flex:1;padding:12px;background:linear-gradient(135deg,<?= $planColor ?>,<?= $planColor ?>bb);border:none;border-radius:8px;color:#fff;font-size:.9rem;font-weight:700;cursor:pointer;transition:opacity .2s;"
                        onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                    <?= $isFree ? '&#10003; Activate Now' : ($hasUpi ? '&#10003; I have paid' : '&rarr; Submit Request') ?>
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
