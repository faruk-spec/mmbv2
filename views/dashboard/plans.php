<?php use Core\View; use Core\Helpers; use Core\Auth; ?>
<?php View::extend('main'); ?>

<?php View::section('styles'); ?>
<style>
/* ── Plans page ──────────────────────────────────────────────────────────── */
.plans-page {
    max-width: 68rem;
    width: 100%;
    margin: 0 auto;
    padding-bottom: 1rem;
}
.plans-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, .92fr);
    gap: 1rem;
    align-items: stretch;
}
.plans-col {
    min-width: 0;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: .75rem;
    padding: .875rem;
    display: flex;
    flex-direction: column;
}
/* Scrollable container for Application Plans list */
.plans-app-list {
    flex: 1;
    overflow-y: auto;
    padding-right: 2px;
    min-height: 8rem;
}
.plans-history-list {
    flex: 1;
    overflow: auto;
    border-radius: .625rem;
    min-height: 8rem;
}

/* Section titles */
.plans-section-title {
    font-size: .9rem; font-weight: 700; margin: 0 0 16px;
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
}

/* App subscription rows */
.app-sub-row {
    display: grid;
    grid-template-columns: 44px 1fr auto;
    align-items: center; gap: 16px;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 10px;
    transition: border-color .2s, box-shadow .2s;
}
.app-sub-row:hover { border-color: rgba(0,240,255,.25); box-shadow: 0 2px 12px rgba(0,0,0,.12); }
.app-icon {
    width: 44px; height: 44px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 1.15rem;
}
.app-info { min-width: 0; }
.app-name  { font-weight: 700; font-size: .9rem; }
.app-meta  { font-size: .78rem; color: var(--text-secondary); margin-top: 3px; line-height: 1.5; }
.sub-badge {
    padding: 4px 12px; border-radius: 20px; font-size: .7rem; font-weight: 700;
    white-space: nowrap; flex-shrink: 0; border: 1px solid transparent;
}
.sub-badge-active  { background: rgba(0,255,136,.12); color: var(--green); border-color: rgba(0,255,136,.2); }
.sub-badge-free    { background: rgba(255,170,0,.12);  color: #ffaa00; border-color: rgba(255,170,0,.2); }
.app-actions { display: flex; gap: 8px; flex-shrink: 0; flex-wrap: wrap; align-items: center; }

/* Buttons */
.btn-app { padding: 7px 14px; border-radius: 8px; font-size: .78rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: all .2s; border: 1px solid transparent; display: inline-flex; align-items: center; gap: 5px; }
.btn-app-open     { background: var(--bg-secondary); border-color: var(--border-color); color: var(--text-primary); }
.btn-app-open:hover { border-color: var(--cyan); color: var(--cyan); }
.btn-app-manage   { background: rgba(153,69,255,.1); border-color: rgba(153,69,255,.25); color: var(--purple); }
.btn-app-manage:hover { background: rgba(153,69,255,.18); }
.btn-app-invoice  { background: rgba(0,240,255,.08); border-color: rgba(0,240,255,.2); color: var(--cyan); }
.btn-app-invoice:hover { background: rgba(0,240,255,.14); }
.btn-app-upgrade  { background: linear-gradient(135deg,var(--purple),var(--cyan)); border: none; color: #06060a; font-weight: 700; }
.btn-app-upgrade:hover { opacity: .88; transform: translateY(-1px); }

/* History rows */
.history-row { display: grid; grid-template-columns: 1fr auto auto; align-items: center; gap: .75rem; padding: .75rem 1rem; border-bottom: 1px solid var(--border-color); }
.history-row:last-child { border-bottom: none; }

/* Section divider */
.plans-divider { margin: 28px 0; border: none; border-top: 1px solid var(--border-color); }

/* Pagination */
.pay-pagination { display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 18px; flex-wrap: wrap; }
.pay-pg-btn { padding: 7px 14px; border-radius: 8px; font-size: .78rem; font-weight: 600; text-decoration: none; border: 1px solid var(--border-color); background: var(--bg-secondary); color: var(--text-secondary); transition: all .2s; }
.pay-pg-btn:hover { border-color: var(--cyan); color: var(--cyan); }
.pay-pg-btn.active { background: var(--cyan); color: #06060a; border-color: var(--cyan); cursor: default; }
.pay-pg-btn.disabled { opacity: .4; pointer-events: none; }

/* Responsive */
@media (max-width: 1100px) {
    .plans-layout { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .plans-layout { grid-template-columns: 1fr; }
    .app-sub-row { grid-template-columns: 40px 1fr; gap: 12px; padding: 14px; }
    .app-actions { grid-column: 1 / -1; gap: 6px; }
    .btn-app { padding: 7px 12px; font-size: .76rem; }
    .history-row { grid-template-columns: 1fr auto; gap: 8px; }
    .history-row .app-actions { grid-column: 1 / -1; }
}
@media (max-width: 400px) {
    .btn-app { padding: 6px 10px; font-size: .72rem; }
}
</style>
<?php View::end(); ?>

<?php View::section('content'); ?>

<?php if (Helpers::hasFlash('success')): ?>
<div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
<div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('info')): ?>
<div class="alert alert-info"><?= View::e(Helpers::getFlash('info')) ?></div>
<?php endif; ?>

<div class="plans-page">

    <!-- Page heading -->
    <div style="margin-bottom:28px;">
        <h1 style="font-size:1.35rem;font-weight:800;margin-bottom:6px;display:flex;align-items:center;gap:10px;">
            <span style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--purple),var(--cyan));display:inline-flex;align-items:center;justify-content:center;font-size:1rem;color:#06060a;">
                <i class="fas fa-layer-group"></i>
            </span>
            My Plans &amp; Subscriptions
        </h1>
        <p style="color:var(--text-secondary);font-size:.875rem;">Manage your active subscriptions and explore upgrade options for each application.</p>
    </div>

    <div class="plans-layout">
    <!-- ── Section 1: Application Plans ── -->
    <section class="plans-col">
        <p class="plans-section-title">
            <i class="fas fa-th-large" style="color:var(--purple);"></i>
            Application Plans
        </p>

        <div class="plans-app-list">
        <?php foreach ($appMeta as $appKey => $meta):
            $sub        = $appSubscriptions[$appKey] ?? null;
            $planName   = $sub['plan_name'] ?? null;
            $price      = $sub['price']     ?? null;
            $billing    = $sub['billing_cycle'] ?? null;
            $since      = $sub['started_at']    ?? null;
            $expires    = $sub['expires_at']    ?? null;
            $manageId   = $appManagePaymentIds[$appKey] ?? null;
            $upgradeUrl = match ($appKey) {
                'resumex'  => '/projects/resumex/plans',
                'qr'       => '/projects/qr/plans',
                'convertx' => '/projects/convertx/plans',
                'whatsapp' => '/projects/whatsapp/plans',
                default    => '/plans',
            };
        ?>
        <div class="app-sub-row">
            <!-- Icon -->
            <div class="app-icon" style="background:<?= $meta['color'] ?>20;color:<?= $meta['color'] ?>;">
                <i class="fas fa-cube"></i>
            </div>
            <!-- Info -->
            <div class="app-info">
                <div class="app-name"><?= View::e($meta['name']) ?></div>
                <div class="app-meta">
                    <?php if ($planName): ?>
                        <span style="color:var(--cyan);font-weight:600;"><?= View::e($planName) ?></span>
                        <?php if ($price !== null): ?>
                            &mdash; <?php $cur = $sub['currency'] ?? 'USD'; echo $price == 0 ? '<span style="color:var(--green);">Free</span>' : (htmlspecialchars($cur).'&nbsp;'.number_format((float)$price,2).' / '.htmlspecialchars($billing ?? '')); ?>
                        <?php endif; ?>
                        <?php if ($since): ?>
                            <br><span style="opacity:.7;">Active since <?= date('M j, Y', strtotime($since)) ?><?php if ($expires): ?> &middot; Expires <?= date('M j, Y', strtotime($expires)) ?><?php endif; ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        No active subscription &mdash; using free tier
                    <?php endif; ?>
                </div>
            </div>
            <!-- Actions -->
            <div class="app-actions">
                <span class="sub-badge <?= $planName ? 'sub-badge-active' : 'sub-badge-free' ?>">
                    <?= $planName ? '<i class="fas fa-check-circle"></i> Active' : 'Free' ?>
                </span>
                <a href="<?= $meta['url'] ?>" class="btn-app btn-app-open"><i class="fas fa-external-link-alt"></i> Open</a>
                <?php if ($planName && $manageId): ?>
                <a href="/plans/payment/<?= $manageId ?>" class="btn-app btn-app-manage"><i class="fas fa-cog"></i> Manage</a>
                <?php elseif (!$planName): ?>
                <a href="<?= $upgradeUrl ?>" class="btn-app btn-app-upgrade"><i class="fas fa-arrow-up"></i> Upgrade</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        </div><!-- /.plans-app-list -->
    </section>

    <?php if (!empty($paymentHistory) || ($payTotal ?? 0) > 0): ?>
    <section id="payment-history" class="plans-col">
        <p class="plans-section-title">
            <i class="fas fa-history" style="color:var(--magenta);"></i>
            Payment History
            <?php if (($payTotal ?? 0) > 0): ?>
            <span style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:20px;padding:2px 10px;font-size:.7rem;font-weight:600;color:var(--text-secondary);"><?= (int) $payTotal ?> total</span>
            <?php endif; ?>
        </p>
        <div class="plans-history-list" style="border:1px solid var(--border-color);">
            <?php foreach ($paymentHistory as $payment): ?>
            <div class="history-row">
                <div>
                    <div style="font-weight:600;font-size:.875rem;"><?= View::e($payment['plan_name']) ?></div>
                    <div style="font-size:.76rem;color:var(--text-secondary);margin-top:2px;">
                        <?= View::e($payment['currency']) ?>&nbsp;<?= number_format((float) $payment['amount'], 2) ?>
                        &middot; <?= View::e(strtoupper($payment['app_key'])) ?>
                        &middot; <?= strtoupper(View::e($payment['gateway'])) ?>
                        &middot; <?= date('M j, Y g:i A', strtotime($payment['created_at'])) ?>
                    </div>
                </div>
                <span class="sub-badge <?= ($payment['status'] ?? '') === 'paid' ? 'sub-badge-active' : 'sub-badge-free' ?>">
                    <?= View::e(str_replace('_', ' ', ucfirst($payment['status'] ?? 'pending'))) ?>
                </span>
                <div class="app-actions">
                    <a href="/plans/payment/<?= (int) $payment['id'] ?>" class="btn-app btn-app-open">View</a>
                    <?php if (($payment['status'] ?? '') === 'paid'): ?>
                    <a href="/plans/payment/<?= (int) $payment['id'] ?>/invoice" class="btn-app btn-app-invoice"><i class="fas fa-file-invoice"></i> Invoice</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (($payTotalPages ?? 1) > 1): ?>
        <nav class="pay-pagination" aria-label="Payment history pages">
            <?php $isPrevDisabled = ($payPage ?? 1) <= 1; ?>
            <?php if ($isPrevDisabled): ?>
            <span class="pay-pg-btn disabled">&laquo;</span>
            <?php else: ?>
            <a href="?pay_page=<?= max(1, ($payPage ?? 1) - 1) ?>#payment-history" class="pay-pg-btn">&laquo;</a>
            <?php endif; ?>
            <?php
            $start = max(1, ($payPage ?? 1) - 2);
            $end   = min($payTotalPages ?? 1, ($payPage ?? 1) + 2);
            if ($start > 1): ?><a href="?pay_page=1#payment-history" class="pay-pg-btn">1</a><?php if ($start > 2): ?><span class="pay-pg-btn disabled">&hellip;</span><?php endif; ?>
            <?php endif;
            for ($p = $start; $p <= $end; $p++): ?>
            <?php if ($p === ($payPage ?? 1)): ?>
            <span class="pay-pg-btn active" aria-current="page"><?= $p ?></span>
            <?php else: ?>
            <a href="?pay_page=<?= $p ?>#payment-history" class="pay-pg-btn"><?= $p ?></a>
            <?php endif; ?>
            <?php endfor;
            if ($end < ($payTotalPages ?? 1)): ?><?php if ($end < ($payTotalPages ?? 1) - 1): ?><span class="pay-pg-btn disabled">&hellip;</span><?php endif; ?><a href="?pay_page=<?= $payTotalPages ?>#payment-history" class="pay-pg-btn"><?= $payTotalPages ?></a>
            <?php endif; ?>
            <?php $isNextDisabled = ($payPage ?? 1) >= ($payTotalPages ?? 1); ?>
            <?php if ($isNextDisabled): ?>
            <span class="pay-pg-btn disabled">&raquo;</span>
            <?php else: ?>
            <a href="?pay_page=<?= min($payTotalPages ?? 1, ($payPage ?? 1) + 1) ?>#payment-history" class="pay-pg-btn">&raquo;</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </section>
    <?php endif; ?>
    </div>

</div>

<?php View::end(); ?>
