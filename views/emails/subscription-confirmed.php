<?php
/**
 * Email Template: subscription-confirmed
 */
$subject = $subject ?? 'Subscription Confirmed';
$userName = $userName ?? 'User';
$planName = $planName ?? 'Plan';
$appName  = $appName  ?? 'MMB Platform';
?>
<?php ob_start(); ?>
<p>Hi <?= htmlspecialchars($userName) ?>,</p>
<p>This is a notification regarding your <strong><?= htmlspecialchars($planName) ?></strong> subscription on <?= htmlspecialchars($appName) ?>.</p>
<p>If you have any questions, please contact our support team.</p>
<?php $body = ob_get_clean(); ?>
<?php include __DIR__ . '/layout.php'; ?>
