<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<?php if (Helpers::hasFlash('success')): ?>
<div style="background:rgba(0,255,136,.1);border:1px solid var(--green);color:var(--green);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
    <i class="fas fa-check-circle"></i> <?= View::e(Helpers::getFlash('success')) ?>
</div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
<div style="background:rgba(255,107,107,.1);border:1px solid var(--red);color:var(--red);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
    <i class="fas fa-exclamation-circle"></i> <?= View::e(Helpers::getFlash('error')) ?>
</div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;margin-bottom:4px;">Subscription Payments</h1>
        <p style="color:var(--text-secondary);font-size:.875rem;">Approve manual UPI payments and review subscription payment records.</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="/admin/subscription-payments" class="btn btn-secondary <?= $activeApp === null ? 'active' : '' ?>">All</a>
        <a href="/admin/subscription-payments?app=platform" class="btn btn-secondary <?= $activeApp === 'platform' ? 'active' : '' ?>">Platform</a>
        <a href="/admin/subscription-payments?app=resumex" class="btn btn-secondary <?= $activeApp === 'resumex' ? 'active' : '' ?>">ResumeX</a>
    </div>
</div>

<?php if (empty($payments)): ?>
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:40px;text-align:center;color:var(--text-secondary);">
    No subscription payments found.
</div>
<?php else: ?>
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
        <thead>
            <tr style="background:rgba(255,255,255,.03);border-bottom:1px solid var(--border-color);">
                <th style="padding:12px 16px;text-align:left;">Reference</th>
                <th style="padding:12px 16px;text-align:left;">User</th>
                <th style="padding:12px 16px;text-align:left;">Plan</th>
                <th style="padding:12px 16px;text-align:left;">Gateway</th>
                <th style="padding:12px 16px;text-align:left;">Amount</th>
                <th style="padding:12px 16px;text-align:left;">Status</th>
                <th style="padding:12px 16px;text-align:left;">Refund</th>
                <th style="padding:12px 16px;text-align:left;">Created</th>
                <th style="padding:12px 16px;text-align:center;">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($payments as $payment): ?>
            <tr style="border-bottom:1px solid var(--border-color);vertical-align:top;">
                <td style="padding:14px 16px;">
                    <strong><?= View::e($payment['reference']) ?></strong><br>
                    <span style="font-size:.75rem;color:var(--text-secondary);"><?= View::e($payment['invoice_no']) ?></span>
                </td>
                <td style="padding:14px 16px;">
                    <?= View::e($payment['user_name'] ?: 'User #' . $payment['user_id']) ?><br>
                    <span style="font-size:.75rem;color:var(--text-secondary);"><?= View::e($payment['user_email']) ?></span>
                </td>
                <td style="padding:14px 16px;">
                    <?= View::e($payment['plan_name']) ?><br>
                    <span style="font-size:.75rem;color:var(--text-secondary);"><?= View::e($payment['app_key']) ?> / <?= View::e($payment['billing_cycle'] ?: 'one-time') ?></span>
                </td>
                <td style="padding:14px 16px;"><?= strtoupper(View::e($payment['gateway'])) ?></td>
                <td style="padding:14px 16px;"><?= View::e($payment['currency']) ?> <?= number_format((float) $payment['amount'], 2) ?></td>
                <td style="padding:14px 16px;">
                    <span style="padding:4px 10px;border-radius:999px;font-size:.75rem;font-weight:600;
                        background:<?= match ($payment['status']) {
                            'paid' => 'rgba(0,255,136,.12)',
                            'verification_pending' => 'rgba(245,158,11,.15)',
                            'failed' => 'rgba(255,107,107,.15)',
                            'cancelled' => 'rgba(148,163,184,.15)',
                            default => 'rgba(0,240,255,.12)',
                        } ?>;
                        color:<?= match ($payment['status']) {
                            'paid' => 'var(--green)',
                            'verification_pending' => '#f59e0b',
                            'failed' => 'var(--red)',
                            'cancelled' => '#94a3b8',
                            default => 'var(--cyan)',
                        } ?>;">
                        <?= View::e(str_replace('_', ' ', ucfirst($payment['status']))) ?>
                    </span>
                </td>
                <td style="padding:14px 16px;">
                    <span style="font-size:.78rem;color:<?= ($payment['refund_status'] ?? 'none') === 'requested' ? '#f59e0b' : 'var(--text-secondary)' ?>;">
                        <?= View::e(ucfirst($payment['refund_status'] ?? 'none')) ?>
                    </span>
                </td>
                <td style="padding:14px 16px;"><?= date('M j, Y H:i', strtotime($payment['created_at'])) ?></td>
                <td style="padding:14px 16px;text-align:center;">
                    <?php if (in_array($payment['status'], ['pending', 'verification_pending'], true)): ?>
                    <form method="POST" action="/admin/subscription-payments/<?= (int) $payment['id'] ?>/approve" style="display:inline-block;margin:0 4px 6px 0;">
                        <?= \Core\Security::csrfField() ?>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-check"></i> Approve</button>
                    </form>
                    <form method="POST" action="/admin/subscription-payments/<?= (int) $payment['id'] ?>/reject" style="display:inline-block;margin:0;">
                        <?= \Core\Security::csrfField() ?>
                        <input type="hidden" name="reason" value="Rejected by admin">
                        <button type="submit" class="btn btn-sm" style="background:rgba(255,107,107,.1);border:1px solid var(--red);color:var(--red);cursor:pointer;">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </form>
                    <?php else: ?>
                        <?php if (($payment['refund_status'] ?? 'none') === 'requested'): ?>
                        <form method="POST" action="/admin/subscription-payments/<?= (int) $payment['id'] ?>/refund" style="display:inline-block;margin:0 4px 6px 0;">
                            <?= \Core\Security::csrfField() ?>
                            <input type="hidden" name="decision" value="approved">
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-undo"></i> Approve Refund</button>
                        </form>
                        <form method="POST" action="/admin/subscription-payments/<?= (int) $payment['id'] ?>/refund" style="display:inline-block;margin:0;">
                            <?= \Core\Security::csrfField() ?>
                            <input type="hidden" name="decision" value="rejected">
                            <button type="submit" class="btn btn-sm" style="background:rgba(255,107,107,.1);border:1px solid var(--red);color:var(--red);cursor:pointer;">
                                <i class="fas fa-times"></i> Reject Refund
                            </button>
                        </form>
                        <?php else: ?>
                        <span style="color:var(--text-secondary);font-size:.8rem;">No action</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php View::endSection(); ?>
