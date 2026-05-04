<?php
/**
 * Email Template: subscription-expired
 * Variables: $userName, $planName, $appName, $expiredAt, $resubscribeUrl
 */
$subject      = $subject      ?? 'Your subscription has expired';
$userName     = $userName     ?? 'User';
$planName     = $planName     ?? 'Plan';
$appName      = $appName      ?? 'MMB Platform';
$expiredAt    = $expiredAt    ?? '';
$resubscribeUrl = $resubscribeUrl ?? '/plans';
?>
<?php ob_start(); ?>
<p>Hi <?= htmlspecialchars($userName) ?>,</p>

<p>Your <strong><?= htmlspecialchars($planName) ?></strong> subscription on <?= htmlspecialchars($appName) ?> has <strong style="color:#dc3545;">expired</strong><?= $expiredAt ? ' on '.htmlspecialchars($expiredAt) : '' ?>.</p>

<p>Access to premium features has been paused. You can continue using the free tier or resubscribe to restore full access.</p>

<p><a href="<?= htmlspecialchars($resubscribeUrl) ?>" style="display:inline-block;padding:10px 22px;background:#dc3545;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;font-size:14px;">Resubscribe Now →</a></p>

<p style="color:#666;font-size:13px;">If you have any questions about your account, please contact our support team.</p>
<?php $body = ob_get_clean(); ?>
<?php include __DIR__ . '/layout.php'; ?>
