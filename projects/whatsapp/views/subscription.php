<?php
use Core\View;
use Core\Security;
View::extend('whatsapp:app');
View::section('content');

$currentSub  = $subscription ?? null;
$currentSlug = $currentSub['plan_slug'] ?? null;
$hasPaidSub  = $currentSub && (float)($currentSub['price'] ?? 0) > 0;
?>
<div style="padding:4px 0 20px;">
    <h2 style="font-size:1.5rem;font-weight:700;margin-bottom:4px;color:var(--whatsapp-green);">
        <i class="fas fa-crown" style="margin-right:8px;"></i>Subscription
    </h2>
    <p style="color:var(--text-secondary);font-size:.9rem;">Manage your WhatsApp API plan.</p>
</div>

<!-- Active Subscription -->
<?php if ($currentSub): ?>
<div style="background:linear-gradient(135deg,rgba(37,211,102,.12),rgba(7,94,84,.08));border:1px solid rgba(37,211,102,.3);border-radius:12px;padding:20px;margin-bottom:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
        <div>
            <div style="font-size:1rem;font-weight:700;color:var(--whatsapp-green);"><?= View::e($currentSub['plan_name']) ?></div>
            <div style="font-size:.82rem;color:var(--text-secondary);margin-top:3px;">
                <?= View::e($currentSub['currency'] ?? 'USD') ?> <?= number_format((float)($currentSub['price'] ?? 0), 2) ?> / <?= View::e($currentSub['billing_cycle'] ?? 'monthly') ?>
                &middot; Since <?= date('M j, Y', strtotime($currentSub['started_at'] ?? 'now')) ?>
                <?php if (!empty($currentSub['expires_at'])): ?>&middot; Expires <?= date('M j, Y', strtotime($currentSub['expires_at'])) ?><?php endif; ?>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="/plans" style="padding:7px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);color:var(--text-primary);border-radius:7px;text-decoration:none;font-size:.82rem;font-weight:600;">
                <i class="fas fa-eye" style="margin-right:4px;"></i>View All Plans
            </a>
            <span style="padding:7px 14px;background:rgba(37,211,102,.12);color:var(--whatsapp-green);border-radius:7px;font-size:.82rem;font-weight:700;">
                <i class="fas fa-check-circle" style="margin-right:4px;"></i>Active
            </span>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Available Plans -->
<?php if (!empty($plans)): ?>
<div style="margin-bottom:28px;">
    <h3 style="font-size:1rem;font-weight:700;margin-bottom:14px;color:var(--text-primary);display:flex;align-items:center;gap:8px;">
        <i class="fas fa-layer-group" style="color:var(--whatsapp-green);"></i>Available Plans
    </h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;">
        <?php foreach ($plans as $plan):
            $isActive  = $currentSlug === ($plan['slug'] ?? '');
            $isFree    = ((float)($plan['price'] ?? 0) == 0);
            $isLocked  = $isFree && !$isActive && $hasPaidSub;
            $planColor = '#25D366';
            $features  = json_decode($plan['features'] ?? '{}', true) ?: [];
        ?>
        <div style="background:var(--bg-card);border:1px solid <?= $isActive ? 'rgba(37,211,102,.5)' : 'var(--border-color)' ?>;border-radius:12px;overflow:hidden;<?= $isLocked ? 'opacity:.5;pointer-events:none;' : '' ?>">
            <div style="padding:16px 18px;background:linear-gradient(135deg,rgba(37,211,102,.12),rgba(37,211,102,.04));">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                    <div style="font-weight:700;font-size:.95rem;color:var(--whatsapp-green);"><?= View::e($plan['name']) ?></div>
                    <?php if ($isActive): ?>
                    <span style="font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:20px;background:rgba(37,211,102,.15);color:var(--whatsapp-green);">Active</span>
                    <?php elseif ($isLocked): ?>
                    <span style="font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:20px;background:rgba(120,120,120,.15);color:#888;">Locked</span>
                    <?php endif; ?>
                </div>
                <div style="font-size:1.5rem;font-weight:800;color:var(--text-primary);">
                    <?= $isFree ? 'Free' : (View::e($plan['currency'] ?? 'USD').'&nbsp;'.number_format((float)$plan['price'],2)) ?>
                    <?php if (!$isFree): ?><small style="font-size:.75rem;font-weight:400;color:var(--text-secondary);">/ <?= View::e($plan['billing_cycle']) ?></small><?php endif; ?>
                </div>
            </div>
            <div style="padding:14px 18px 16px;">
                <?php foreach ($features as $fk => $fv): if ($fv): ?>
                <div style="font-size:.82rem;color:var(--text-secondary);margin-bottom:4px;">
                    <i class="fas fa-check" style="color:var(--whatsapp-green);margin-right:6px;"></i>
                    <?= View::e(ucwords(str_replace('_', ' ', $fk))) ?>
                </div>
                <?php endif; endforeach; ?>
                <div style="margin-top:14px;">
                    <?php if ($isActive): ?>
                    <div style="padding:9px;background:rgba(37,211,102,.1);border-radius:7px;text-align:center;color:var(--whatsapp-green);font-weight:700;font-size:.85rem;">
                        <i class="fas fa-check-circle" style="margin-right:4px;"></i>Current Plan
                    </div>
                    <?php elseif ($isLocked): ?>
                    <div style="padding:9px;background:rgba(120,120,120,.1);border-radius:7px;text-align:center;color:#888;font-size:.85rem;">
                        Not available with paid plan
                    </div>
                    <?php else: ?>
                    <a href="/projects/whatsapp/subscription/<?= urlencode($plan['slug']) ?>"
                       style="display:block;padding:9px;background:var(--whatsapp-green);color:#fff;border-radius:7px;text-align:center;font-weight:600;font-size:.85rem;text-decoration:none;">
                        <?= $hasPaidSub ? 'Upgrade →' : ($isFree ? 'Activate Free' : 'Subscribe →') ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Payment History -->
<?php if (!empty($paymentHistory)): ?>
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
    <h3 style="font-size:1rem;font-weight:700;margin-bottom:14px;color:var(--text-primary);display:flex;align-items:center;gap:8px;">
        <i class="fas fa-receipt" style="color:var(--whatsapp-green);"></i>Payment History
    </h3>
    <div style="display:flex;flex-direction:column;gap:8px;">
        <?php foreach ($paymentHistory as $pay): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;padding:10px 14px;background:var(--bg-secondary);border-radius:8px;border:1px solid var(--border-color);">
            <div>
                <div style="font-size:.88rem;font-weight:600;color:var(--text-primary);"><?= View::e($pay['plan_name'] ?? '—') ?></div>
                <div style="font-size:.76rem;color:var(--text-secondary);"><?= date('M j, Y', strtotime($pay['created_at'] ?? 'now')) ?></div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:.85rem;font-weight:700;color:var(--text-primary);"><?= View::e($pay['currency'] ?? 'USD') ?> <?= number_format((float)($pay['amount'] ?? 0), 2) ?></span>
                <span style="font-size:.72rem;padding:3px 10px;border-radius:20px;background:<?= ($pay['status']??'')=='paid'?'rgba(0,255,136,.15)':'rgba(255,170,0,.15)' ?>;color:<?= ($pay['status']??'')=='paid'?'var(--whatsapp-green)':'#ffaa00' ?>;">
                    <?= View::e(ucfirst($pay['status'] ?? 'pending')) ?>
                </span>
                <?php if (($pay['status']??'')=='paid' && !empty($pay['subscription_id'])): ?>
                <a href="/plans/invoice/<?= (int)$pay['subscription_id'] ?>" style="font-size:.75rem;color:var(--whatsapp-green);text-decoration:none;" title="View Invoice">
                    <i class="fas fa-file-invoice"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
<?php View::end(); ?>
