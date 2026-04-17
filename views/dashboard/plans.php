<?php use Core\View; use Core\Helpers; use Core\Auth; ?>
<?php View::extend('main'); ?>

<?php View::section('styles'); ?>
<style>
/* ── Plans page styles ──────────────────────────────────────────────────── */
.plans-page { max-width: 900px; }
.plans-section-title {
    font-size: .95rem; font-weight: 700; margin: 0 0 14px;
    display: flex; align-items: center; gap: 8px;
}
/* App subscription rows */
.app-sub-row {
    display: grid;
    grid-template-columns: 44px 1fr auto auto;
    align-items: center; gap: 14px;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 14px 18px;
    margin-bottom: 10px;
    transition: border-color .2s;
}
.app-sub-row:hover { border-color: rgba(0,240,255,.25); }
.app-icon {
    width: 44px; height: 44px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.app-info { min-width: 0; }
.app-name  { font-weight: 700; font-size: .9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.app-meta  { font-size: .78rem; color: var(--text-secondary); margin-top: 3px; }
.sub-badge {
    padding: 4px 12px; border-radius: 20px; font-size: .72rem; font-weight: 600;
    white-space: nowrap; flex-shrink: 0;
}
.sub-badge-active  { background: rgba(0,255,136,.15); color: var(--green); }
.sub-badge-free    { background: rgba(255,170,0,.15);  color: #ffaa00; }
.app-actions { display: flex; gap: 8px; flex-shrink: 0; }
.btn-app { padding: 6px 14px; border-radius: 6px; font-size: .78rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: all .2s; }
.btn-app-open     { background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-primary); }
.btn-app-open:hover { border-color: var(--cyan); color: var(--cyan); }
.btn-app-upgrade  { background: linear-gradient(135deg,var(--purple),var(--cyan)); border: none; color: #fff; }
.btn-app-upgrade:hover { opacity: .88; }
/* Plan cards grid */
.plan-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 16px;
}
.plan-card {
    background: var(--bg-card);
    border: 2px solid var(--border-color);
    border-radius: 14px; overflow: hidden;
    transition: border-color .25s, transform .2s;
    display: flex; flex-direction: column;
}
.plan-card:hover { transform: translateY(-2px); }
.plan-card-header { padding: 20px 20px 14px; }
.plan-card-body   { padding: 0 20px 20px; flex: 1; display: flex; flex-direction: column; }
.plan-price { font-size: 1.5rem; font-weight: 800; margin: 8px 0 4px; }
.plan-price small { font-size: .72rem; font-weight: 400; color: var(--text-secondary); }
.plan-apps { display: flex; flex-wrap: wrap; gap: 5px; margin: 12px 0 16px; }
.plan-app-chip {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 9px; border-radius: 20px; font-size: .72rem; font-weight: 600;
    text-decoration: none;
}
.plan-cta {
    display: block; width: 100%; padding: 10px;
    border: none; border-radius: 8px; text-align: center;
    font-size: .85rem; font-weight: 700; cursor: pointer;
    text-decoration: none; transition: opacity .2s; margin-top: auto;
}
.plan-cta:hover { opacity: .88; }
.plan-cta-active { background: rgba(0,255,136,.1); color: var(--green); cursor: default; }
/* Active platform sub cards */
.active-sub-card {
    background: var(--bg-card);
    border-radius: 10px; padding: 16px 20px; margin-bottom: 10px;
    border: 2px solid;
}
/* Section divider */
.plans-divider {
    margin: 28px 0;
    border: none; border-top: 1px solid var(--border-color);
}
/* Responsive */
@media (max-width: 640px) {
    .app-sub-row { grid-template-columns: 36px 1fr; gap: 10px; padding: 12px; }
    .sub-badge, .app-actions { grid-column: 2; }
    .plan-grid { grid-template-columns: 1fr; }
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

<div class="plans-page">

    <!-- Page heading -->
    <div style="margin-bottom:24px;">
        <h1 style="font-size:1.4rem;font-weight:700;margin-bottom:6px;display:flex;align-items:center;gap:10px;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
            My Plans &amp; Subscriptions
        </h1>
        <p style="color:var(--text-secondary);font-size:.875rem;">Manage your active subscriptions and explore upgrade options.</p>
    </div>

    <!-- ── Section 1: Per-app subscriptions ── -->
    <section style="margin-bottom:28px;">
        <p class="plans-section-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
            </svg>
            Application Plans
        </p>

        <?php foreach ($appMeta as $appKey => $meta):
            $sub      = $appSubscriptions[$appKey] ?? null;
            $planName = $sub['plan_name'] ?? null;
            $price    = $sub['price']     ?? null;
            $billing  = $sub['billing_cycle'] ?? null;
            $since    = $sub['started_at']    ?? null;
            $expires  = $sub['expires_at']    ?? null;
        ?>
        <div class="app-sub-row">
            <!-- Icon -->
            <div class="app-icon" style="background:<?= $meta['color'] ?>20;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="<?= $meta['color'] ?>" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                </svg>
            </div>
            <!-- Info -->
            <div class="app-info">
                <div class="app-name"><?= View::e($meta['name']) ?></div>
                <div class="app-meta">
                    <?php if ($planName): ?>
                        Plan: <strong style="color:var(--cyan);"><?= View::e($planName) ?></strong>
                        <?php if ($price !== null): ?>
                            &mdash; <?= $price == 0 ? 'Free' : ('$'.number_format((float)$price,2).' / '.$billing) ?>
                        <?php endif; ?>
                        <?php if ($since): ?>
                            &middot; Active since <?= date('M j, Y', strtotime($since)) ?>
                            <?php if ($expires): ?> &middot; Expires <?= date('M j, Y', strtotime($expires)) ?><?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        No active subscription &mdash; using free tier
                    <?php endif; ?>
                </div>
            </div>
            <!-- Badge -->
            <span class="sub-badge <?= $planName ? 'sub-badge-active' : 'sub-badge-free' ?>">
                <?= $planName ? 'Active' : 'Free' ?>
            </span>
            <!-- Actions -->
            <div class="app-actions">
                <a href="<?= $meta['url'] ?>" class="btn-app btn-app-open">Open</a>
                <?php if (!$planName): ?>
                <a href="#platform-plans" class="btn-app btn-app-upgrade">Upgrade</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </section>

    <?php if (!empty($userPlatformSubs)): ?>
    <hr class="plans-divider">
    <!-- ── Section 2: Active platform subscriptions ── -->
    <section style="margin-bottom:28px;" id="active-platform">
        <p class="plans-section-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            Active Platform Subscriptions
        </p>
        <?php foreach ($userPlatformSubs as $sub):
            $apps = json_decode($sub['included_apps'] ?? '[]', true) ?: [];
            $col  = View::e($sub['color'] ?? '#9945ff');
        ?>
        <div class="active-sub-card" style="border-color:<?= $col ?>;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div>
                    <div style="font-weight:700;font-size:1rem;color:<?= $col ?>;"><?= View::e($sub['plan_name']) ?></div>
                    <div style="font-size:.8rem;color:var(--text-secondary);margin-top:4px;">
                        <?= $sub['price'] == 0 ? 'Free' : ('$'.number_format((float)$sub['price'],2).' / '.$sub['billing_cycle']) ?>
                        &middot; Active since <?= date('M j, Y', strtotime($sub['started_at'])) ?>
                    </div>
                    <div class="plan-apps" style="margin-top:10px;">
                        <?php foreach ($apps as $ak):
                            $m = $appMeta[$ak] ?? null; if (!$m) continue; ?>
                        <a href="<?= $m['url'] ?>" class="plan-app-chip" style="background:<?= $m['color'] ?>20;color:<?= $m['color'] ?>;">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            <?= View::e($m['name']) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <span class="sub-badge sub-badge-active" style="flex-shrink:0;">Active</span>
            </div>
        </div>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <hr class="plans-divider">

    <!-- ── Section 3: Available platform plans ── -->
    <section id="platform-plans" style="margin-bottom:28px;">
        <p class="plans-section-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--magenta)" stroke-width="2">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
            Platform Plans
        </p>
        <p style="color:var(--text-secondary);font-size:.85rem;margin-bottom:18px;">One plan covering multiple applications.</p>

        <?php if (empty($platformPlans)): ?>
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:40px;text-align:center;color:var(--text-secondary);">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:12px;opacity:.4;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            <div>No platform plans available yet.</div>
        </div>
        <?php else: ?>
        <div class="plan-grid">
        <?php foreach ($platformPlans as $plan):
            $isActive  = in_array($plan['id'], $activePlatformPlanIds);
            $planApps  = $plan['included_apps'] ?? [];
            $planColor = $plan['color'] ?? '#9945ff';
        ?>
        <div class="plan-card" style="border-color:<?= $isActive ? $planColor : 'var(--border-color)' ?>;" onmouseover="this.style.borderColor='<?= $planColor ?>'" onmouseout="this.style.borderColor='<?= $isActive ? $planColor : 'var(--border-color)' ?>'">
            <div class="plan-card-header" style="background:linear-gradient(135deg,<?= $planColor ?>22,<?= $planColor ?>08);">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div style="font-weight:700;font-size:1rem;color:<?= $planColor ?>;"><?= View::e($plan['name']) ?></div>
                    <?php if ($isActive): ?>
                    <span class="sub-badge sub-badge-active">Active</span>
                    <?php endif; ?>
                </div>
                <div class="plan-price">
                    <?= $plan['price'] == 0 ? 'Free' : ('$'.number_format((float)$plan['price'],2)) ?>
                    <?php if ($plan['price'] > 0): ?>
                    <small>/ <?= $plan['billing_cycle'] ?></small>
                    <?php endif; ?>
                </div>
                <?php if (!empty($plan['description'])): ?>
                <p style="font-size:.8rem;color:var(--text-secondary);line-height:1.5;"><?= View::e($plan['description']) ?></p>
                <?php endif; ?>
            </div>
            <div class="plan-card-body">
                <?php if (!empty($planApps)): ?>
                <div style="font-size:.72rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Includes</div>
                <div class="plan-apps">
                    <?php foreach ($planApps as $ak):
                        $m = $appMeta[$ak] ?? null; if (!$m) continue; ?>
                    <span class="plan-app-chip" style="background:<?= $m['color'] ?>15;color:<?= $m['color'] ?>;">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <?= View::e($m['name']) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if ($isActive): ?>
                <div class="plan-cta plan-cta-active">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    You're subscribed
                </div>
                <?php else: ?>
                <a href="/plans/subscribe/<?= urlencode($plan['slug']) ?>"
                   class="plan-cta"
                   style="background:linear-gradient(135deg,<?= $planColor ?>,<?= $planColor ?>aa);color:#fff;">
                    Get <?= View::e($plan['name']) ?> →
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>

</div>

<?php View::end(); ?>
