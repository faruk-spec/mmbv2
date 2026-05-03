<?php
/**
 * Email Template: subscription-renewal
 * Variables: $userName, $planName, $appName, $currency, $price, $billingCycle, $renewUrl
 */
$subject      = $subject      ?? 'It\'s time to renew your subscription';
$userName     = $userName     ?? 'User';
$planName     = $planName     ?? 'Plan';
$appName      = $appName      ?? 'MMB Platform';
$currency     = $currency     ?? '';
$price        = $price        ?? null;
$billingCycle = $billingCycle ?? '';
$renewUrl     = $renewUrl     ?? '/plans';
?>
<?php ob_start(); ?>
<p>Hi <?= htmlspecialchars($userName) ?>,</p>

<p>🔄 We hope you have been enjoying your <strong><?= htmlspecialchars($planName) ?></strong> subscription on <?= htmlspecialchars($appName) ?>!</p>

<p>It is time to renew your plan to keep all your premium features and ensure uninterrupted service.</p>

<?php if ($price !== null): ?>
<div style="background:#f0f8ff;border:1px solid #0077cc;border-radius:8px;padding:14px 18px;margin:20px 0;font-size:14px;">
    <strong><?= htmlspecialchars($planName) ?></strong>
    — <?= (float)$price === 0.0 ? 'Free' : (htmlspecialchars($currency).' '.number_format((float)$price,2).($billingCycle ? ' / '.htmlspecialchars($billingCycle) : '')) ?>
</div>
<?php endif; ?>

<p><a href="<?= htmlspecialchars($renewUrl) ?>" style="display:inline-block;padding:10px 22px;background:#0077cc;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;font-size:14px;">Renew Subscription →</a></p>

<p style="color:#666;font-size:13px;">If you have decided not to renew, you can simply ignore this email. Your subscription will end at the current billing period.</p>
<?php $body = ob_get_clean(); ?>
<?php include __DIR__ . '/layout.php'; ?>
