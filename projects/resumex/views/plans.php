<?php use Core\View; use Core\Auth; ?>
<?php View::extend('main'); ?>

<?php View::section('styles'); ?>
<style>
.rx-plans-page { max-width: 900px; }
.rx-plan-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; margin-bottom: 32px; }
.rx-plan-card {
    background: var(--bg-card); border: 2px solid var(--border-color); border-radius: 14px;
    overflow: hidden; display: flex; flex-direction: column; transition: border-color .25s, transform .2s;
}
.rx-plan-card:hover { transform: translateY(-2px); }
.rx-plan-header { padding: 20px 20px 14px; }
.rx-plan-body { padding: 0 20px 20px; flex: 1; display: flex; flex-direction: column; }
.rx-plan-price { font-size: 1.5rem; font-weight: 800; margin: 8px 0 4px; }
.rx-plan-price small { font-size: .72rem; font-weight: 400; color: var(--text-secondary); }
.rx-plan-feature { display: flex; align-items: center; gap: 8px; font-size: .82rem; padding: 4px 0; color: var(--text-secondary); }
.rx-plan-feature.on { color: var(--text-primary); }
.rx-plan-feature i.fa-check { color: var(--green); }
.rx-plan-feature i.fa-times { color: var(--text-secondary); opacity: .4; }
.rx-plan-cta {
    display: block; width: 100%; padding: 10px; border: none; border-radius: 8px;
    text-align: center; font-size: .85rem; font-weight: 700; cursor: pointer;
    text-decoration: none; transition: opacity .2s; margin-top: auto;
}
.rx-plan-cta:hover { opacity: .88; }
.rx-plan-cta-active { background: rgba(0,255,136,.1); color: var(--green); cursor: default; }
.rx-history-row {
    display: grid; grid-template-columns: 1fr auto auto auto;
    align-items: center; gap: 14px;
    background: var(--bg-card); border: 1px solid var(--border-color);
    border-radius: 10px; padding: 12px 16px; margin-bottom: 8px; font-size: .85rem;
}
.badge-active  { background: rgba(0,255,136,.15); color: var(--green); padding: 3px 10px; border-radius: 20px; font-size: .72rem; font-weight: 600; }
.badge-paid  { background: rgba(0,255,136,.15); color: var(--green); padding: 3px 10px; border-radius: 20px; font-size: .72rem; font-weight: 600; }
.badge-cancelled { background: rgba(255,107,107,.15); color: var(--red); padding: 3px 10px; border-radius: 20px; font-size: .72rem; font-weight: 600; }
.badge-trial   { background: rgba(245,158,11,.15); color: #f59e0b; padding: 3px 10px; border-radius: 20px; font-size: .72rem; font-weight: 600; }
.badge-pending { background: rgba(0,240,255,.15); color: var(--cyan); padding: 3px 10px; border-radius: 20px; font-size: .72rem; font-weight: 600; }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>

<?php if (isset($_SESSION['_flash']['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($_SESSION['_flash']['success']) ?></div>
<?php unset($_SESSION['_flash']['success']); endif; ?>
<?php if (isset($_SESSION['_flash']['error'])): ?>
<div class="alert alert-error"><?= htmlspecialchars($_SESSION['_flash']['error']) ?></div>
<?php unset($_SESSION['_flash']['error']); endif; ?>

<div class="rx-plans-page">
    <div style="margin-bottom:24px;display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
            <h1 style="font-size:1.4rem;font-weight:700;margin-bottom:6px;">
                <i class="fas fa-crown" style="color:#f59e0b;margin-right:8px;"></i>ResumeX Plans
            </h1>
            <p style="color:var(--text-secondary);font-size:.875rem;">Choose the plan that fits your needs.</p>
        </div>
        <a href="/projects/resumex" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <?php if ($currentSub): ?>
    <div style="background:rgba(0,255,136,.06);border:1px solid rgba(0,255,136,.25);border-radius:12px;padding:20px 24px;margin-bottom:28px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
        <div>
            <div style="font-size:.8rem;color:var(--green);font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Current Plan</div>
            <div style="font-size:1.1rem;font-weight:700;"><?= htmlspecialchars($currentSub['plan_name']) ?></div>
            <div style="font-size:.82rem;color:var(--text-secondary);margin-top:4px;">
                Active since <?= date('M j, Y', strtotime($currentSub['started_at'])) ?>
                <?php if ($currentSub['expires_at']): ?>
                    &middot; Expires <?= date('M j, Y', strtotime($currentSub['expires_at'])) ?>
                    <?php
                    $daysLeft = (int) ceil((strtotime($currentSub['expires_at']) - time()) / 86400);
                    if ($daysLeft <= 7 && $daysLeft > 0): ?>
                        <span style="color:#f59e0b;font-weight:600;"> &#9888; <?= $daysLeft ?> day(s) left</span>
                    <?php elseif ($daysLeft <= 0): ?>
                        <span style="color:var(--red);font-weight:600;"> &#10007; Expired</span>
                    <?php endif; ?>
                <?php else: ?>
                    &middot; <span style="color:var(--green);">Lifetime / No expiry</span>
                <?php endif; ?>
            </div>
        </div>
        <span style="background:rgba(0,255,136,.12);color:var(--green);border:1px solid rgba(0,255,136,.25);padding:6px 16px;border-radius:20px;font-size:.8rem;font-weight:700;">Active</span>
    </div>
    <?php endif; ?>

    <p style="font-size:.95rem;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-star" style="color:#f59e0b;"></i> Available Plans
    </p>
    <?php if (empty($plans)): ?>
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:30px;text-align:center;color:var(--text-secondary);">
            No subscription plans available yet. Check back soon.
        </div>
    <?php else: ?>
    <div class="rx-plan-grid">
    <?php foreach ($plans as $plan):
        $isActive   = $currentSub && $currentSub['plan_slug'] === $plan['slug'];
        $planColor  = '#9945ff';
        $price      = (float)$plan['price'];
        $cur        = htmlspecialchars($plan['currency'] ?? 'USD');
        $features   = json_decode($plan['features'] ?? '{}', true) ?: [];
        $featureLabels = [
            'unlimited_resumes' => 'Unlimited Resumes',
            'pdf_export'        => 'PDF Export',
            'pdf_no_watermark'  => 'No PDF Watermark',
            'premium_templates' => 'Premium Templates',
            'ai_suggestions'    => 'AI Suggestions',
            'linkedin_import'   => 'LinkedIn Import',
            'public_sharing'    => 'Public Sharing',
        ];
    ?>
    <div class="rx-plan-card" style="border-color:<?= $isActive ? '#00ff88' : 'var(--border-color)' ?>;"
         onmouseover="this.style.borderColor='<?= $planColor ?>'" onmouseout="this.style.borderColor='<?= $isActive ? '#00ff88' : 'var(--border-color)' ?>'">
        <div class="rx-plan-header" style="background:linear-gradient(135deg,<?= $planColor ?>22,<?= $planColor ?>08);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div style="font-weight:700;font-size:1rem;color:<?= $planColor ?>;"><?= htmlspecialchars($plan['name']) ?></div>
                <?php if ($isActive): ?>
                <span style="background:rgba(0,255,136,.12);color:var(--green);padding:2px 8px;border-radius:20px;font-size:.7rem;font-weight:700;">Active</span>
                <?php endif; ?>
            </div>
            <div class="rx-plan-price">
                <?= $price == 0 ? 'Free' : ($cur.'&nbsp;'.number_format($price,2)) ?>
                <?php if ($price > 0): ?>
                <small>/ <?= htmlspecialchars($plan['billing_cycle']) ?></small>
                <?php endif; ?>
            </div>
            <div style="font-size:.78rem;color:var(--text-secondary);">Max resumes: <?= $plan['max_resumes'] == 0 ? '&#8734;' : (int)$plan['max_resumes'] ?></div>
        </div>
        <div class="rx-plan-body">
            <?php foreach ($featureLabels as $fk => $fl): ?>
            <div class="rx-plan-feature <?= !empty($features[$fk]) ? 'on' : '' ?>">
                <i class="fas <?= !empty($features[$fk]) ? 'fa-check' : 'fa-times' ?>"></i>
                <?= $fl ?>
            </div>
            <?php endforeach; ?>

            <?php if ($isActive): ?>
            <div class="rx-plan-cta rx-plan-cta-active" style="margin-top:16px;">
                <i class="fas fa-check"></i> Current Plan
            </div>
            <?php else: ?>
            <a href="/projects/resumex/plans/<?= urlencode($plan['slug']) ?>"
               class="rx-plan-cta"
               style="background:linear-gradient(135deg,<?= $planColor ?>,<?= $planColor ?>bb);color:#fff;margin-top:16px;">
                <?= $price == 0 ? 'Activate Free' : 'Subscribe &rarr;' ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($history)): ?>
    <div style="margin-top:32px;">
        <p style="font-size:.95rem;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-history" style="color:var(--cyan);"></i> Subscription History
        </p>
        <?php foreach ($history as $h):
            $hCur = htmlspecialchars($h['currency'] ?? 'USD');
        ?>
        <div class="rx-history-row">
            <div>
                <strong><?= htmlspecialchars($h['plan_name']) ?></strong>
                <div style="font-size:.75rem;color:var(--text-secondary);">
                    Started <?= date('M j, Y', strtotime($h['started_at'])) ?>
                    <?php if ($h['expires_at']): ?> &middot; Expires <?= date('M j, Y', strtotime($h['expires_at'])) ?><?php endif; ?>
                </div>
            </div>
            <div style="font-size:.82rem;color:var(--text-secondary);">
                <?= (float)$h['price'] == 0 ? 'Free' : ($hCur.'&nbsp;'.number_format((float)$h['price'],2).'/'.$h['billing_cycle']) ?>
            </div>
            <span class="badge-<?= htmlspecialchars($h['status']) ?>"><?= ucfirst($h['status']) ?></span>
            <a href="/projects/resumex/plans/invoice/<?= (int)$h['id'] ?>" class="btn btn-secondary btn-sm" style="font-size:.75rem;padding:4px 10px;">
                <i class="fas fa-file-invoice"></i> Invoice
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($paymentHistory)): ?>
    <div style="margin-top:32px;">
        <p style="font-size:.95rem;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-receipt" style="color:var(--purple);"></i> Payment History
        </p>
        <?php foreach ($paymentHistory as $payment): ?>
        <div class="rx-history-row">
            <div>
                <strong><?= htmlspecialchars($payment['plan_name']) ?></strong>
                <div style="font-size:.75rem;color:var(--text-secondary);">
                    <?= strtoupper(htmlspecialchars($payment['gateway'])) ?> &middot; Ref <?= htmlspecialchars($payment['reference']) ?>
                </div>
            </div>
            <div style="font-size:.82rem;color:var(--text-secondary);">
                <?= htmlspecialchars($payment['currency']) ?>&nbsp;<?= number_format((float) $payment['amount'], 2) ?>
            </div>
            <span class="badge-<?= htmlspecialchars(in_array($payment['status'], ['active','paid','cancelled','trial'], true) ? $payment['status'] : 'pending') ?>">
                <?= ucfirst(str_replace('_', ' ', $payment['status'])) ?>
            </span>
            <a href="/projects/resumex/plans/payment/<?= (int) $payment['id'] ?>" class="btn btn-secondary btn-sm" style="font-size:.75rem;padding:4px 10px;">
                <i class="fas fa-eye"></i> View
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php View::endSection(); ?>
