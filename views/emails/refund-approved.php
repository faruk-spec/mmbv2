<?php
$amount    = $data['amount'] ?? '0';
$currency  = $data['currency'] ?? 'USD';
$planName  = $data['plan_name'] ?? 'your plan';
$userName  = $data['user_name'] ?? 'User';
$invoiceNo = $data['invoice_no'] ?? '';
?>
<p>Hi <?= htmlspecialchars($userName) ?>,</p>
<p>Great news! Your refund of <strong><?= htmlspecialchars($currency) ?> <?= htmlspecialchars((string)$amount) ?></strong> for <strong><?= htmlspecialchars($planName) ?></strong> has been approved and processed.</p>
<?php if ($invoiceNo): ?><p>Invoice: <strong><?= htmlspecialchars($invoiceNo) ?></strong></p><?php endif; ?>
<p>Refunds typically appear in your account within 5-10 business days depending on your bank.</p>
<p>Thanks for your patience.</p>
<p>The <?= htmlspecialchars(defined('APP_NAME') ? APP_NAME : 'Platform') ?> Team</p>
