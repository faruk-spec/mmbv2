<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<style>
.sp-table { width: 100%; border-collapse: collapse; font-size: .84rem; }
.sp-table th { padding: 11px 16px; text-align: left; font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--text-secondary); background: rgba(255,255,255,.03); border-bottom: 1px solid var(--border-color); white-space: nowrap; }
.sp-table td { padding: 14px 16px; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
.sp-table tr:last-child td { border-bottom: none; }
.sp-table tr:hover td { background: rgba(255,255,255,.02); }
.sp-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 20px; font-size: .7rem; font-weight: 700; border: 1px solid transparent; white-space: nowrap; }
.sp-badge-paid      { background: rgba(0,255,136,.1);  color: var(--green); border-color: rgba(0,255,136,.2); }
.sp-badge-pending   { background: rgba(255,152,0,.1);  color: #ff9800;      border-color: rgba(255,152,0,.2); }
.sp-badge-failed    { background: rgba(255,60,60,.1);  color: var(--red);   border-color: rgba(255,60,60,.2); }
.sp-badge-cancelled { background: rgba(148,163,184,.1);color: #94a3b8;      border-color: rgba(148,163,184,.2); }
.sp-badge-default   { background: rgba(0,240,255,.1);  color: var(--cyan);  border-color: rgba(0,240,255,.2); }
.sp-badge-refund-req { background: rgba(245,158,11,.12); color: #f59e0b; border-color: rgba(245,158,11,.25); }
.sp-badge-refund-ok  { background: rgba(0,255,136,.1);  color: var(--green); border-color: rgba(0,255,136,.2); }
.sp-badge-refund-rej { background: rgba(255,60,60,.1);  color: var(--red);   border-color: rgba(255,60,60,.2); }
.sp-btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: 7px; font-size: .75rem; font-weight: 600; cursor: pointer; border: 1px solid; transition: all .15s; font-family: inherit; text-decoration: none; white-space: nowrap; }
.sp-btn-approve { background: rgba(0,255,136,.1); border-color: rgba(0,255,136,.3); color: var(--green); }
.sp-btn-approve:hover { background: rgba(0,255,136,.18); }
.sp-btn-reject  { background: rgba(255,60,60,.08);  border-color: rgba(255,60,60,.3);  color: var(--red); }
.sp-btn-reject:hover  { background: rgba(255,60,60,.14); }
.sp-btn-refund  { background: rgba(245,158,11,.1);  border-color: rgba(245,158,11,.3); color: #f59e0b; }
.sp-btn-refund:hover  { background: rgba(245,158,11,.16); }
.sp-act-group { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
@media (max-width: 900px) {
    .sp-table thead th.sp-hide-sm, .sp-table td.sp-hide-sm { display: none; }
}
@media (max-width: 600px) {
    .sp-table thead th.sp-hide-xs, .sp-table td.sp-hide-xs { display: none; }
    .sp-table th, .sp-table td { padding: 10px 10px; }
}
</style>

<?php if (Helpers::hasFlash('success')): ?>
<div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
<div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:1.3rem;font-weight:800;margin-bottom:4px;">Refund Requests</h1>
        <p style="color:var(--text-secondary);font-size:.85rem;">Review and process user refund requests. Cashfree payments trigger automatic gateway refunds when approved.</p>
    </div>
    <a href="/admin/subscription-payments" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;border:1px solid var(--border-color);color:var(--text-secondary);font-size:.82rem;text-decoration:none;">
        <i class="fas fa-arrow-left"></i> All Payments
    </a>
</div>

<?php if (empty($payments)): ?>
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:48px 24px;text-align:center;color:var(--text-secondary);">
    <i class="fas fa-undo-alt" style="font-size:2.5rem;opacity:.3;margin-bottom:14px;display:block;"></i>
    No refund requests found.
</div>
<?php else: ?>
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;overflow:hidden;">
    <div style="overflow-x:auto;">
    <table class="sp-table">
        <thead>
            <tr>
                <th>Reference</th>
                <th>User</th>
                <th>Plan</th>
                <th class="sp-hide-xs">Amount</th>
                <th>Refund Status</th>
                <th class="sp-hide-sm">Requested</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($payments as $payment): ?>
            <?php
            $refundStatus = $payment['refund_status'] ?? 'none';
            $refundClass = match ($refundStatus) {
                'requested' => 'sp-badge-refund-req',
                'refunded', 'approved' => 'sp-badge-refund-ok',
                'rejected' => 'sp-badge-refund-rej',
                default => 'sp-badge-default',
            };
            ?>
            <tr>
                <td>
                    <span style="font-family:monospace;font-size:.78rem;font-weight:600;"><?= View::e($payment['reference']) ?></span><br>
                    <span style="font-size:.7rem;color:var(--text-secondary);"><?= View::e($payment['invoice_no']) ?></span>
                </td>
                <td>
                    <span style="font-weight:600;"><?= View::e($payment['user_name'] ?: 'User #' . $payment['user_id']) ?></span><br>
                    <span style="font-size:.74rem;color:var(--text-secondary);"><?= View::e($payment['user_email']) ?></span>
                </td>
                <td>
                    <span style="font-weight:600;"><?= View::e($payment['plan_name']) ?></span><br>
                    <span style="font-size:.72rem;color:var(--text-secondary);"><?= View::e(strtoupper($payment['app_key'] ?? '')) ?> &middot; <?= View::e($payment['billing_cycle'] ?: 'one-time') ?> &middot; <?= strtoupper(View::e($payment['gateway'])) ?></span>
                </td>
                <td class="sp-hide-xs">
                    <span style="font-weight:700;color:var(--cyan);"><?= View::e($payment['currency']) ?> <?= number_format((float) $payment['amount'], 2) ?></span>
                </td>
                <td>
                    <span class="sp-badge <?= $refundClass ?>">
                        <?= View::e(ucfirst($refundStatus)) ?>
                    </span>
                    <?php if (!empty($payment['admin_notes'])): ?>
                    <div style="font-size:.7rem;color:var(--text-secondary);margin-top:4px;max-width:180px;word-break:break-word;"><?= View::e($payment['admin_notes']) ?></div>
                    <?php endif; ?>
                </td>
                <td class="sp-hide-sm">
                    <?php if (!empty($payment['refund_requested_at'])): ?>
                    <span style="font-size:.78rem;color:var(--text-secondary);"><?= date('M j, Y', strtotime($payment['refund_requested_at'])) ?></span><br>
                    <span style="font-size:.7rem;color:var(--text-secondary);opacity:.7;"><?= date('g:i A', strtotime($payment['refund_requested_at'])) ?></span>
                    <?php else: ?>
                    <span style="color:var(--text-secondary);font-size:.76rem;">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="sp-act-group" style="justify-content:flex-end;">
                    <?php if ($refundStatus === 'requested'): ?>
                        <form method="POST" action="/admin/subscription-payments/<?= (int) $payment['id'] ?>/refund" style="margin:0;">
                            <?= \Core\Security::csrfField() ?>
                            <input type="hidden" name="decision" value="approved">
                            <button type="submit" class="sp-btn sp-btn-refund"
                                    aria-label="Approve refund — will trigger Cashfree gateway automatically if this is a Cashfree payment">
                                <i class="fas fa-undo"></i> Approve
                            </button>
                        </form>
                        <form method="POST" action="/admin/subscription-payments/<?= (int) $payment['id'] ?>/refund" style="margin:0;">
                            <?= \Core\Security::csrfField() ?>
                            <input type="hidden" name="decision" value="rejected">
                            <button type="submit" class="sp-btn sp-btn-reject"><i class="fas fa-ban"></i> Decline</button>
                        </form>
                    <?php else: ?>
                        <span style="color:var(--text-secondary);font-size:.76rem;padding:6px 4px;">
                            <?= View::e(ucfirst($refundStatus)) ?>
                        </span>
                    <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

<?php View::endSection(); ?>
