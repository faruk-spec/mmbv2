<?php
/**
 * Email Template: subscription-expiring
 * Variables: $userName, $planName, $appName, $expiresAt, $daysLeft, $renewUrl
 */
$subject  = $subject  ?? 'Your subscription expires soon';
$userName = $userName ?? 'User';
$planName = $planName ?? 'Plan';
$appName  = $appName  ?? 'MMB Platform';
$expiresAt = $expiresAt ?? '';
$daysLeft = isset($daysLeft) ? (int)$daysLeft : 7;
$renewUrl = $renewUrl ?? '/plans';
?>
<?php ob_start(); ?>
<p>Hi <?= htmlspecialchars($userName) ?>,</p>

<p>⏰ <strong>Your <?= htmlspecialchars($planName) ?> subscription on <?= htmlspecialchars($appName) ?> is expiring soon.</strong></p>

<div style="background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:16px 20px;margin:20px 0;">
    <p style="margin:0;font-size:15px;">
        <strong>⚠ <?= $daysLeft ?> day<?= $daysLeft === 1 ? '' : 's' ?> remaining</strong>
        <?php if ($expiresAt): ?> — expires on <strong><?= htmlspecialchars($expiresAt) ?></strong><?php endif; ?>
    </p>
</div>

<p>To continue enjoying uninterrupted access, please renew your subscription before it expires.</p>

<p><a href="<?= htmlspecialchars($renewUrl) ?>" style="display:inline-block;padding:10px 22px;background:#f59e0b;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;font-size:14px;">Renew Subscription →</a></p>

<p style="color:#666;font-size:13px;">If you no longer need this plan, no action is required — your subscription will simply expire.</p>
<?php $body = ob_get_clean(); ?>
<?php include __DIR__ . '/layout.php'; ?>
