<?php
$amount    = $data['amount'] ?? '0';
$currency  = $data['currency'] ?? 'USD';
$planName  = $data['plan_name'] ?? 'N/A';
$userEmail = $data['user_email'] ?? '';
$userName  = $data['user_name'] ?? '';
$invoiceNo = $data['invoice_no'] ?? '';
?>
<p>A refund has been requested.</p>
<ul>
  <li><strong>User:</strong> <?= htmlspecialchars($userName) ?> (<?= htmlspecialchars($userEmail) ?>)</li>
  <li><strong>Plan:</strong> <?= htmlspecialchars($planName) ?></li>
  <li><strong>Amount:</strong> <?= htmlspecialchars($currency) ?> <?= htmlspecialchars((string)$amount) ?></li>
  <?php if ($invoiceNo): ?><li><strong>Invoice:</strong> <?= htmlspecialchars($invoiceNo) ?></li><?php endif; ?>
</ul>
<p><a href="/admin/refunds">Review on admin refunds page →</a></p>
