<?php
/**
 * ConvertX – Plans & Pricing View
 */
$currentView = 'plan';
$currentPlanSlug = $currentPlanSlug ?? 'free';
$plans = $plans ?? [];
$currentSub = $currentSub ?? null;
$history = $history ?? [];
$paymentHistory = $paymentHistory ?? [];
?>

<div class="page-header">
    <h1>Plans &amp; Pricing</h1>
    <p>Choose the plan that fits your needs. Upgrade or downgrade at any time.</p>
</div>

<?php if ($currentSub): ?>
<div class="card" style="margin-bottom:1.5rem;border:1px solid rgba(99,102,241,.25);">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <div>
            <div style="font-size:.8rem;font-weight:700;color:var(--cx-primary);text-transform:uppercase;letter-spacing:.05em;">Current Plan</div>
            <div style="font-size:1.15rem;font-weight:700;"><?= htmlspecialchars($currentSub['plan_name']) ?></div>
            <div style="font-size:.85rem;color:var(--text-secondary);margin-top:4px;">
                <?= htmlspecialchars($currentSub['currency'] ?? 'USD') ?> <?= number_format((float) ($currentSub['price'] ?? 0), 2) ?> / <?= htmlspecialchars($currentSub['billing_cycle'] ?? 'monthly') ?>
                &middot; Started <?= date('M j, Y', strtotime($currentSub['started_at'])) ?>
                <?php if (!empty($currentSub['expires_at'])): ?> &middot; Expires <?= date('M j, Y', strtotime($currentSub['expires_at'])) ?><?php endif; ?>
            </div>
        </div>
        <span class="btn btn-secondary" style="cursor:default;">Active</span>
    </div>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1.5rem;">
    <?php foreach ($plans as $plan): ?>
    <?php
    $isCurrent = ($currentSub['plan_slug'] ?? $currentPlanSlug) === ($plan['slug'] ?? '');
    $featureMap = json_decode($plan['features'] ?? '{}', true) ?: [];
    $highlights = array_keys(array_filter($featureMap));
    ?>
    <div class="cx-price-card <?= $isCurrent ? 'cx-price-featured' : '' ?>">
        <div class="plan-name"><?= htmlspecialchars($plan['name']) ?></div>
        <div class="plan-price" style="background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
            <?= htmlspecialchars($plan['currency'] ?? 'USD') ?> <?= number_format((float) ($plan['price'] ?? 0), 2) ?>
        </div>
        <div class="plan-period">/ <?= htmlspecialchars($plan['billing_cycle'] ?? 'monthly') ?></div>
        <div class="plan-tagline"><?= htmlspecialchars($plan['description'] ?? 'Professional conversion features.') ?></div>
        <ul>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> <?= (int) ($plan['max_jobs_per_month'] ?? 0) < 0 ? 'Unlimited' : number_format((int) ($plan['max_jobs_per_month'] ?? 0)) ?> conversions/month</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> <?= number_format((int) ($plan['max_file_size_mb'] ?? 0)) ?> MB max file size</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> Batch size <?= number_format((int) ($plan['max_batch_size'] ?? 0)) ?></li>
            <?php foreach (array_slice($highlights, 0, 4) as $feature): ?>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> <?= htmlspecialchars(ucwords(str_replace('_', ' ', $feature))) ?></li>
            <?php endforeach; ?>
            <?php if (!empty($plan['cancel_days'])): ?><li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> Cancel within <?= (int) $plan['cancel_days'] ?> day(s)</li><?php endif; ?>
            <?php if (!empty($plan['refund_days'])): ?><li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> Refund within <?= (int) $plan['refund_days'] ?> day(s)</li><?php endif; ?>
        </ul>
        <?php if ($isCurrent): ?>
        <span class="btn btn-secondary" style="width:100%;justify-content:center;cursor:default;">Current Plan</span>
        <?php else: ?>
        <a href="/plans/project/convertx/<?= urlencode($plan['slug'] ?? '') ?>" class="btn btn-primary" style="width:100%;justify-content:center;">Continue</a>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<?php if (!empty($history)): ?>
<div style="margin-top:2rem;">
    <h3 style="margin-bottom:.75rem;">Subscription History</h3>
    <div style="display:flex;flex-direction:column;gap:.75rem;">
        <?php foreach ($history as $item): ?>
        <div class="card" style="display:grid;grid-template-columns:1fr auto auto;gap:1rem;align-items:center;">
            <div>
                <strong><?= htmlspecialchars($item['plan_name']) ?></strong>
                <div style="font-size:.82rem;color:var(--text-secondary);margin-top:4px;">
                    <?= htmlspecialchars($item['currency'] ?? 'USD') ?> <?= number_format((float) ($item['price'] ?? 0), 2) ?> / <?= htmlspecialchars($item['billing_cycle'] ?? 'monthly') ?>
                    &middot; Started <?= date('M j, Y', strtotime($item['started_at'])) ?>
                    <?php if (!empty($item['expires_at'])): ?> &middot; Expires <?= date('M j, Y', strtotime($item['expires_at'])) ?><?php endif; ?>
                </div>
            </div>
            <span class="btn btn-secondary" style="cursor:default;"><?= htmlspecialchars(ucfirst($item['status'] ?? 'unknown')) ?></span>
            <div>
                <?php foreach ($paymentHistory as $payment): if ((int) ($payment['subscription_id'] ?? 0) !== (int) ($item['id'] ?? 0)) continue; ?>
                <a href="/plans/payment/<?= (int) $payment['id'] ?>/invoice" class="btn btn-secondary">Invoice</a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($paymentHistory)): ?>
<div style="margin-top:2rem;">
    <h3 style="margin-bottom:.75rem;">Payment History</h3>
    <div style="display:flex;flex-direction:column;gap:.75rem;">
        <?php foreach ($paymentHistory as $payment): ?>
        <div class="card" style="display:grid;grid-template-columns:1fr auto auto;gap:1rem;align-items:center;">
            <div>
                <strong><?= htmlspecialchars($payment['plan_name']) ?></strong>
                <div style="font-size:.82rem;color:var(--text-secondary);margin-top:4px;"><?= htmlspecialchars($payment['currency']) ?> <?= number_format((float) $payment['amount'], 2) ?> &middot; <?= strtoupper(htmlspecialchars($payment['gateway'])) ?> &middot; Ref <?= htmlspecialchars($payment['reference']) ?></div>
            </div>
            <span class="btn btn-secondary" style="cursor:default;"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $payment['status'] ?? 'pending'))) ?></span>
            <a href="/plans/payment/<?= (int) $payment['id'] ?>" class="btn btn-primary">View</a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
