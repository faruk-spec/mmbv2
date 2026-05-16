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
.sp-btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: 7px; font-size: .75rem; font-weight: 600; cursor: pointer; border: 1px solid; transition: all .15s; font-family: inherit; text-decoration: none; white-space: nowrap; }
.sp-btn-approve { background: rgba(0,255,136,.1); border-color: rgba(0,255,136,.3); color: var(--green); }
.sp-btn-approve:hover { background: rgba(0,255,136,.18); }
.sp-btn-reject  { background: rgba(255,60,60,.08);  border-color: rgba(255,60,60,.3);  color: var(--red); }
.sp-btn-reject:hover  { background: rgba(255,60,60,.14); }
.sp-btn-refund  { background: rgba(245,158,11,.1);  border-color: rgba(245,158,11,.3); color: #f59e0b; }
.sp-btn-refund:hover  { background: rgba(245,158,11,.16); }
.sp-btn-cancel  { background: rgba(148,163,184,.08); border-color: rgba(148,163,184,.3); color: #94a3b8; }
.sp-btn-cancel:hover  { background: rgba(148,163,184,.14); }
.sp-act-group { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
.sp-filter-bar { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
.sp-filter-btn { padding: 7px 16px; border-radius: 8px; font-size: .8rem; font-weight: 600; cursor: pointer; border: 1px solid var(--border-color); background: var(--bg-secondary); color: var(--text-secondary); text-decoration: none; transition: all .15s; }
.sp-filter-btn:hover { border-color: var(--cyan); color: var(--cyan); }
.sp-filter-btn.active { background: var(--cyan); color: #06060a; border-color: var(--cyan); }
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
        <h1 style="font-size:1.3rem;font-weight:800;margin-bottom:4px;">Subscription Payments</h1>
        <p style="color:var(--text-secondary);font-size:.85rem;">Approve manual payments, review refund requests, and manage subscriptions.</p>
    </div>
    <div class="sp-filter-bar">
        <a href="/admin/subscription-payments" class="sp-filter-btn <?= $activeApp === null ? 'active' : '' ?>">All</a>
        <a href="/admin/subscription-payments?app=resumex"  class="sp-filter-btn <?= $activeApp === 'resumex'  ? 'active' : '' ?>">ResumeX</a>
        <a href="/admin/subscription-payments?app=qr"       class="sp-filter-btn <?= $activeApp === 'qr'       ? 'active' : '' ?>">QR Generator</a>
        <a href="/admin/subscription-payments?app=convertx" class="sp-filter-btn <?= $activeApp === 'convertx' ? 'active' : '' ?>">ConvertX</a>
        <a href="/admin/subscription-payments?app=whatsapp" class="sp-filter-btn <?= $activeApp === 'whatsapp' ? 'active' : '' ?>">WhatsApp</a>
    </div>
</div>

<?php if (empty($payments)): ?>
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:48px 24px;text-align:center;color:var(--text-secondary);">
    <i class="fas fa-receipt" style="font-size:2.5rem;opacity:.3;margin-bottom:14px;display:block;"></i>
    No subscription payments found<?= $activeApp ? ' for the selected filter' : '' ?>.
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
                <th>Status</th>
                <th class="sp-hide-sm">Refund</th>
                <th class="sp-hide-sm">Date</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($payments as $payment): ?>
            <?php
            $statusClass = match ($payment['status']) {
                'paid'   => 'sp-badge-paid',
                'verification_pending', 'pending' => 'sp-badge-pending',
                'failed' => 'sp-badge-failed',
                'cancelled' => 'sp-badge-cancelled',
                default => 'sp-badge-default',
            };
            $refundStatus = $payment['refund_status'] ?? 'none';
            $refundClass = match ($refundStatus) {
                'requested' => 'sp-badge-refund-req',
                'refunded', 'approved' => 'sp-badge-refund-ok',
                default => '',
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
                    <span class="sp-badge <?= $statusClass ?>">
                        <?= View::e(str_replace('_', ' ', ucfirst($payment['status']))) ?>
                    </span>
                </td>
                <td class="sp-hide-sm">
                    <?php if ($refundStatus !== 'none'): ?>
                    <span class="sp-badge <?= $refundClass ?>">
                        <?= View::e(ucfirst($refundStatus)) ?>
                    </span>
                    <?php else: ?>
                    <span style="color:var(--text-secondary);font-size:.76rem;">—</span>
                    <?php endif; ?>
                </td>
                <td class="sp-hide-sm">
                    <span style="font-size:.78rem;color:var(--text-secondary);"><?= date('M j, Y', strtotime($payment['created_at'])) ?></span><br>
                    <span style="font-size:.7rem;color:var(--text-secondary);opacity:.7;"><?= date('g:i A', strtotime($payment['created_at'])) ?></span>
                </td>
                <td>
                    <div class="sp-act-group" style="justify-content:flex-end;">
                    <?php if (in_array($payment['status'], ['pending', 'verification_pending'], true)): ?>
                        <form method="POST" action="/admin/subscription-payments/<?= (int) $payment['id'] ?>/approve" style="margin:0;">
                            <?= \Core\Security::csrfField() ?>
                            <button type="submit" class="sp-btn sp-btn-approve"><i class="fas fa-check"></i> Approve</button>
                        </form>
                        <form method="POST" action="/admin/subscription-payments/<?= (int) $payment['id'] ?>/reject" style="margin:0;">
                            <?= \Core\Security::csrfField() ?>
                            <input type="hidden" name="reason" value="Rejected by admin">
                            <button type="submit" class="sp-btn sp-btn-reject"><i class="fas fa-times"></i> Reject</button>
                        </form>
                    <?php else: ?>
                        <?php if ($refundStatus === 'requested'): ?>
                        <form method="POST" action="/admin/subscription-payments/<?= (int) $payment['id'] ?>/refund" style="margin:0;">
                            <?= \Core\Security::csrfField() ?>
                            <input type="hidden" name="decision" value="approved">
                            <button type="submit" class="sp-btn sp-btn-refund"
                                    aria-label="Approve refund — will trigger Cashfree gateway automatically if this is a Cashfree payment">
                                <i class="fas fa-undo"></i> Approve Refund
                            </button>
                        </form>
                        <form method="POST" action="/admin/subscription-payments/<?= (int) $payment['id'] ?>/refund" style="margin:0;">
                            <?= \Core\Security::csrfField() ?>
                            <input type="hidden" name="decision" value="rejected">
                            <button type="submit" class="sp-btn sp-btn-reject"><i class="fas fa-ban"></i> Decline</button>
                        </form>
                        <?php elseif ($refundStatus === 'none'): ?>
                        <span style="color:var(--text-secondary);font-size:.76rem;padding:6px 4px;">—</span>
                        <?php endif; ?>
                        <?php if ($payment['status'] === 'paid' && !in_array($refundStatus, ['approved', 'refunded'], true)): ?>
                        <button type="button" class="sp-btn sp-btn-refund"
                            data-payment-id="<?= (int) $payment['id'] ?>"
                            data-csrf="<?= View::e(\Core\Security::csrfToken()) ?>"
                            onclick="openManualRefundModal(this)">
                            <i class="fas fa-hand-holding-usd"></i> Refund
                        </button>
                        <?php endif; ?>
                        <?php if ($payment['status'] === 'paid'): ?>
                        <form method="POST" action="/admin/subscription-payments/<?= (int) $payment['id'] ?>/cancel-plan"
                              style="margin:0;"
                              onsubmit="return confirm('Cancel this user\'s subscription? This cannot be undone.');">
                            <?= \Core\Security::csrfField() ?>
                            <button type="submit" class="sp-btn sp-btn-cancel"><i class="fas fa-ban"></i> Cancel</button>
                        </form>
                        <?php endif; ?>
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

<!-- Manual Refund Modal -->
<div style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9000;align-items:center;justify-content:center;" id="manualRefundModal">
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;padding:28px;max-width:420px;width:calc(100% - 32px);box-shadow:0 20px 60px rgba(0,0,0,.5);">
        <p style="font-size:1rem;font-weight:700;margin:0 0 6px;color:var(--text-primary);"><i class="fas fa-hand-holding-usd" style="color:#f59e0b;margin-right:7px;"></i>Manual Refund</p>
        <p style="font-size:.83rem;color:var(--text-secondary);margin:0 0 20px;">This will mark the payment as refunded and cancel the subscription immediately. If it is a Cashfree payment the gateway refund API will be called automatically.</p>
        <form method="POST" id="manualRefundForm">
            <input type="hidden" name="_csrf_token" id="mrCsrf">
            <div style="margin-bottom:16px;">
                <label style="font-size:.82rem;font-weight:600;display:block;margin-bottom:6px;">Admin note <span style="color:var(--text-secondary);font-weight:400;">(optional)</span></label>
                <textarea id="mrReason" name="reason" rows="2" placeholder="Reason for manual refund…"
                    style="width:100%;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;padding:10px 12px;color:var(--text-primary);font-size:.84rem;resize:vertical;font-family:inherit;box-sizing:border-box;"></textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;">
                <button type="button" class="sp-btn" style="border-color:var(--border-color);color:var(--text-secondary);" onclick="closeManualRefundModal()">Cancel</button>
                <button type="submit" class="sp-btn sp-btn-refund"><i class="fas fa-undo"></i> Process Refund</button>
            </div>
        </form>
    </div>
</div>

<?php View::section('scripts'); ?>
<script>
function openManualRefundModal(btn) {
    var id = btn.getAttribute('data-payment-id');
    var csrf = btn.getAttribute('data-csrf');
    document.getElementById('manualRefundForm').action = '/admin/subscription-payments/' + id + '/manual-refund';
    document.getElementById('mrCsrf').value = csrf;
    document.getElementById('mrReason').value = '';
    var modal = document.getElementById('manualRefundModal');
    modal.style.display = 'flex';
}
function closeManualRefundModal() {
    document.getElementById('manualRefundModal').style.display = 'none';
}
document.getElementById('manualRefundModal').addEventListener('click', function(e) {
    if (e.target === this) closeManualRefundModal();
});
</script>
<?php View::end(); ?>

<?php View::endSection(); ?>
