<?php
/**
 * Email Template: subscription-confirmed
 * Variables: $userName, $planName, $appName, $currency, $price, $billingCycle, $startedAt, $expiresAt, $invoiceUrl, $dashboardUrl
 */
$subject     = $subject     ?? 'Subscription Confirmed — ' . ($planName ?? 'Your Plan');
$userName    = $userName    ?? 'User';
$planName    = $planName    ?? 'Plan';
$appName     = $appName     ?? 'MMB Platform';
$currency    = $currency    ?? '';
$price       = $price       ?? null;
$billingCycle = $billingCycle ?? '';
$startedAt   = $startedAt   ?? date('F j, Y');
$expiresAt   = $expiresAt   ?? null;
$invoiceUrl  = $invoiceUrl  ?? '';
$dashboardUrl = $dashboardUrl ?? '/plans';
?>
<?php ob_start(); ?>
<p>Hi <?= htmlspecialchars($userName) ?>,</p>

<p>🎉 <strong>Your subscription is now active!</strong> Thank you for subscribing to the <strong><?= htmlspecialchars($planName) ?></strong> plan on <?= htmlspecialchars($appName) ?>.</p>

<table style="width:100%;border-collapse:collapse;margin:20px 0;font-size:14px;">
    <tr style="background:#f0f4ff;">
        <td style="padding:10px 14px;font-weight:700;color:#444;width:40%;">Plan</td>
        <td style="padding:10px 14px;"><?= htmlspecialchars($planName) ?></td>
    </tr>
    <?php if ($price !== null): ?>
    <tr>
        <td style="padding:10px 14px;font-weight:700;color:#444;border-top:1px solid #eee;">Amount</td>
        <td style="padding:10px 14px;border-top:1px solid #eee;">
            <?= (float)$price === 0.0 ? 'Free' : (htmlspecialchars($currency).' '.number_format((float)$price,2).($billingCycle ? ' / '.htmlspecialchars($billingCycle) : '')) ?>
        </td>
    </tr>
    <?php endif; ?>
    <tr style="background:#f0f4ff;">
        <td style="padding:10px 14px;font-weight:700;color:#444;border-top:1px solid #eee;">Started</td>
        <td style="padding:10px 14px;border-top:1px solid #eee;"><?= htmlspecialchars($startedAt) ?></td>
    </tr>
    <?php if ($expiresAt): ?>
    <tr>
        <td style="padding:10px 14px;font-weight:700;color:#444;border-top:1px solid #eee;">Expires</td>
        <td style="padding:10px 14px;border-top:1px solid #eee;"><?= htmlspecialchars($expiresAt) ?></td>
    </tr>
    <?php endif; ?>
</table>

<?php if ($invoiceUrl): ?>
<p><a href="<?= htmlspecialchars($invoiceUrl) ?>" style="display:inline-block;padding:10px 20px;background:#0077cc;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;font-size:14px;">View Invoice</a></p>
<?php endif; ?>

<p>You can manage your subscription from your <a href="<?= htmlspecialchars($dashboardUrl) ?>" style="color:#0077cc;">plans dashboard</a>.</p>
<p>If you have any questions, please contact our support team.</p>
<?php $body = ob_get_clean(); ?>
<?php include __DIR__ . '/layout.php'; ?>
