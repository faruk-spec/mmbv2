<?php use Core\View; ?>
<?php View::extend('whatsapp:app'); ?>

<?php View::section('content'); ?>
<style>
.wa-subscription-page { max-width: 1180px; margin: 0 auto; }
.wa-header { margin-bottom: 28px; text-align: center; }
.wa-header h1 { font-size: 2.4rem; margin-bottom: 10px; background: linear-gradient(135deg, var(--whatsapp-green), #128C7E); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.wa-header p { color: var(--text-secondary); }
.wa-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; margin-bottom: 24px; }
.wa-current { border-color: rgba(37, 211, 102, 0.25); box-shadow: 0 10px 30px rgba(37, 211, 102, 0.08); }
.wa-section-title { font-size: 1.2rem; font-weight: 700; margin: 0 0 16px; display: flex; align-items: center; gap: 10px; }
.wa-plan-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; }
.wa-plan-card { background: var(--bg-card); border: 2px solid var(--border-color); border-radius: 14px; padding: 24px; transition: .2s ease; display: flex; flex-direction: column; }
.wa-plan-card:hover { border-color: var(--whatsapp-green); transform: translateY(-3px); }
.wa-plan-card.current { border-color: var(--whatsapp-green); }
.wa-price { font-size: 2rem; font-weight: 800; color: var(--whatsapp-green); margin: 12px 0 4px; }
.wa-muted { color: var(--text-secondary); font-size: .9rem; }
.wa-badge { display: inline-flex; align-items: center; padding: 6px 14px; border-radius: 999px; font-size: .78rem; font-weight: 700; }
.wa-badge.active { background: rgba(40, 199, 111, 0.16); color: var(--success); }
.wa-list { list-style: none; padding: 0; margin: 16px 0 20px; display: flex; flex-direction: column; gap: 8px; }
.wa-list li { display: flex; gap: 8px; align-items: flex-start; font-size: .9rem; }
.wa-list i { color: var(--whatsapp-green); margin-top: 3px; }
.wa-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 18px; border-radius: 10px; text-decoration: none; font-weight: 700; border: 1px solid transparent; }
.wa-btn-primary { background: var(--whatsapp-green); color: #fff; }
.wa-btn-secondary { background: var(--bg-secondary); color: var(--text-primary); border-color: var(--border-color); }
.wa-history { display: flex; flex-direction: column; gap: 12px; }
.wa-history-row { display: grid; grid-template-columns: 1fr auto auto; gap: 12px; align-items: center; padding: 16px 18px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 12px; }
@media (max-width: 768px) {
    .wa-header h1 { font-size: 1.9rem; }
    .wa-history-row { grid-template-columns: 1fr; }
}
</style>

<div class="wa-subscription-page">
    <div class="wa-header">
        <h1><i class="fas fa-crown"></i> My Subscription</h1>
        <p>Manage your WhatsApp API subscription with the shared payment, invoice, cancel, and refund flow.</p>
    </div>

    <?php if ($subscription): ?>
    <div class="wa-card wa-current">
        <div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start;flex-wrap:wrap;">
            <div>
                <div class="wa-muted" style="text-transform:uppercase;letter-spacing:.06em;font-weight:700;margin-bottom:6px;">Current Plan</div>
                <div style="font-size:1.6rem;font-weight:800;"><?= htmlspecialchars($subscription['plan_name'] ?? 'Active Plan') ?></div>
                <div class="wa-muted" style="margin-top:8px;">
                    <?= htmlspecialchars($subscription['currency'] ?? 'USD') ?> <?= number_format((float) ($subscription['price'] ?? 0), 2) ?>
                    / <?= htmlspecialchars($subscription['billing_cycle'] ?? (($subscription['duration_days'] ?? 30) . ' days')) ?>
                    &middot; Started <?= !empty($subscription['started_at']) ? date('M j, Y', strtotime($subscription['started_at'])) : '—' ?>
                    <?php if (!empty($subscription['expires_at'])): ?> &middot; Expires <?= date('M j, Y', strtotime($subscription['expires_at'])) ?><?php endif; ?>
                </div>
            </div>
            <span class="wa-badge active">Active</span>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($plans)): ?>
    <div class="wa-card">
        <h2 class="wa-section-title"><i class="fas fa-layer-group"></i> Available Plans</h2>
        <div class="wa-plan-grid">
            <?php foreach ($plans as $plan): ?>
            <?php $isCurrent = (int) ($subscription['plan_id'] ?? 0) === (int) ($plan['id'] ?? 0); ?>
            <div class="wa-plan-card <?= $isCurrent ? 'current' : '' ?>">
                <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                    <div>
                        <div style="font-size:1.1rem;font-weight:700;"><?= htmlspecialchars($plan['name']) ?></div>
                        <div class="wa-price"><?= htmlspecialchars($plan['currency'] ?? 'USD') ?> <?= number_format((float) ($plan['price'] ?? 0), 2) ?></div>
                        <div class="wa-muted"><?= htmlspecialchars($plan['billing_cycle'] ?? (($plan['duration_days'] ?? 30) . ' days')) ?></div>
                    </div>
                    <?php if ($isCurrent): ?><span class="wa-badge active">Current</span><?php endif; ?>
                </div>

                <?php if (!empty($plan['description'])): ?>
                <p class="wa-muted" style="margin:12px 0 0;line-height:1.6;"><?= htmlspecialchars($plan['description']) ?></p>
                <?php endif; ?>

                <ul class="wa-list">
                    <li><i class="fas fa-check-circle"></i> <?= (int) ($plan['messages_limit'] ?? 0) <= 0 ? 'Unlimited' : number_format((int) $plan['messages_limit']) ?> messages</li>
                    <li><i class="fas fa-check-circle"></i> <?= (int) ($plan['sessions_limit'] ?? 0) <= 0 ? 'Unlimited' : number_format((int) $plan['sessions_limit']) ?> sessions</li>
                    <li><i class="fas fa-check-circle"></i> <?= (int) ($plan['api_calls_limit'] ?? 0) <= 0 ? 'Unlimited' : number_format((int) $plan['api_calls_limit']) ?> API calls</li>
                    <?php if (!empty($plan['cancel_days'])): ?><li><i class="fas fa-check-circle"></i> Cancel within <?= (int) $plan['cancel_days'] ?> day(s)</li><?php endif; ?>
                    <?php if (!empty($plan['refund_days'])): ?><li><i class="fas fa-check-circle"></i> Refund within <?= (int) $plan['refund_days'] ?> day(s)</li><?php endif; ?>
                </ul>

                <?php if ($isCurrent): ?>
                <span class="wa-btn wa-btn-secondary" style="cursor:default;margin-top:auto;">Current Plan</span>
                <?php else: ?>
                <a href="/plans/project/whatsapp/<?= urlencode($plan['slug'] ?? $plan['id']) ?>" class="wa-btn wa-btn-primary" style="margin-top:auto;">Continue</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($history)): ?>
    <div class="wa-card">
        <h2 class="wa-section-title"><i class="fas fa-history"></i> Subscription History</h2>
        <div class="wa-history">
            <?php foreach ($history as $item): ?>
            <div class="wa-history-row">
                <div>
                    <strong><?= htmlspecialchars($item['plan_name'] ?? 'Plan') ?></strong>
                    <div class="wa-muted" style="margin-top:4px;">
                        <?= htmlspecialchars($item['currency'] ?? 'USD') ?> <?= number_format((float) ($item['price'] ?? 0), 2) ?>
                        / <?= htmlspecialchars($item['billing_cycle'] ?? (($item['duration_days'] ?? 30) . ' days')) ?>
                        &middot; Started <?= !empty($item['started_at']) ? date('M j, Y', strtotime($item['started_at'])) : '—' ?>
                        <?php if (!empty($item['expires_at'])): ?> &middot; Expires <?= date('M j, Y', strtotime($item['expires_at'])) ?><?php endif; ?>
                    </div>
                </div>
                <span class="wa-badge active" style="background:rgba(0,0,0,.06);color:var(--text-primary);"><?= htmlspecialchars(ucfirst($item['status'] ?? 'unknown')) ?></span>
                <div>
                    <a href="/plans/project/whatsapp/invoice/<?= (int) ($item['id'] ?? 0) ?>" class="wa-btn wa-btn-secondary">Invoice</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($paymentHistory)): ?>
    <div class="wa-card">
        <h2 class="wa-section-title"><i class="fas fa-credit-card"></i> Payment History</h2>
        <div class="wa-history">
            <?php foreach ($paymentHistory as $payment): ?>
            <div class="wa-history-row">
                <div>
                    <strong><?= htmlspecialchars($payment['plan_name']) ?></strong>
                    <div class="wa-muted" style="margin-top:4px;">
                        <?= htmlspecialchars($payment['currency'] ?? 'USD') ?> <?= number_format((float) ($payment['amount'] ?? 0), 2) ?>
                        &middot; <?= strtoupper(htmlspecialchars($payment['gateway'] ?? 'request')) ?>
                        &middot; Ref <?= htmlspecialchars($payment['reference'] ?? '—') ?>
                    </div>
                </div>
                <span class="wa-badge active" style="background:rgba(0,0,0,.06);color:var(--text-primary);"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $payment['status'] ?? 'pending'))) ?></span>
                <a href="/plans/payment/<?= (int) $payment['id'] ?>" class="wa-btn wa-btn-primary">View</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php View::endSection(); ?>
