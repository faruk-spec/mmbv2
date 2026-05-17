<?php
$amount    = $data['amount'] ?? '0';
$currency  = $data['currency'] ?? 'USD';
$planName  = $data['plan_name'] ?? 'your plan';
$userName  = $data['user_name'] ?? 'User';
$invoiceNo = $data['invoice_no'] ?? '';
?>
<p>Hi <?= htmlspecialchars($userName) ?>,</p>
<p>We've received your refund request for <strong><?= htmlspecialchars($planName) ?></strong> — <?= htmlspecialchars($currency) ?> <?= htmlspecialchars((string)$amount) ?>.</p>
<?php if ($invoiceNo): ?><p>Invoice: <strong><?= htmlspecialchars($invoiceNo) ?></strong></p><?php endif; ?>
<p>Our team will review it and process it within 5-7 business days. You'll receive another email once it's approved.</p>
<p>If you have questions, reply to this email.</p>
<p>Thanks,<br>The <?= htmlspecialchars(defined('APP_NAME') ? APP_NAME : 'Platform') ?> Team</p>
