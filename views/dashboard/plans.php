<?php use Core\View; use Core\Helpers; use Core\Auth; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<?php if (Helpers::hasFlash('success')): ?>
<div class="alert alert-success" style="margin-bottom:16px;padding:14px 18px;background:rgba(0,255,136,.1);border:1px solid var(--green);border-radius:8px;color:var(--green);">
    <?= View::e(Helpers::getFlash('success')) ?>
</div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
<div class="alert alert-error" style="margin-bottom:16px;padding:14px 18px;background:rgba(255,107,107,.1);border:1px solid #ff6b6b;border-radius:8px;color:#ff6b6b;">
    <?= View::e(Helpers::getFlash('error')) ?>
</div>
<?php endif; ?>

<!-- ── Page heading ──────────────────────────────────────────────────────── -->
<div style="margin-bottom:24px;">
    <h1 style="font-size:1.5rem;font-weight:700;margin-bottom:6px;display:flex;align-items:center;gap:10px;">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
        </svg>
        My Plans &amp; Subscriptions
    </h1>
    <p style="color:var(--text-secondary);font-size:.9rem;">Manage your active subscriptions and explore upgrade options for each application.</p>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════
     SECTION 1 — Per-app active subscriptions
═══════════════════════════════════════════════════════════════════════════ -->
<section style="margin-bottom:32px;">
    <h2 style="font-size:1rem;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2">
            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
        </svg>
        Application-Specific Plans
    </h2>

    <?php
    // Build a display list from all known apps
    $appMeta = $appMeta ?? [];
    foreach ($appMeta as $appKey => $meta):
        $sub = $appSubscriptions[$appKey] ?? null;
        $planName    = $sub['plan_name']    ?? null;
        $price       = $sub['price']        ?? null;
        $billing     = $sub['billing_cycle'] ?? null;
        $startedAt   = $sub['started_at']   ?? null;
        $expiresAt   = $sub['expires_at']   ?? null;
    ?>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:16px 20px;margin-bottom:12px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <!-- App icon -->
        <div style="width:44px;height:44px;background:<?= $meta['color'] ?>20;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="<?= $meta['color'] ?>" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
            </svg>
        </div>
        <!-- App info -->
        <div style="flex:1;min-width:150px;">
            <div style="font-weight:700;font-size:.95rem;"><?= View::e($meta['name']) ?></div>
            <?php if ($planName): ?>
                <div style="font-size:.8rem;color:var(--text-secondary);margin-top:2px;">
                    Plan: <strong style="color:var(--cyan);"><?= View::e($planName) ?></strong>
                    <?php if ($price !== null): ?>
                        &mdash; <?= $price == 0 ? 'Free' : ('$' . number_format((float)$price, 2) . ' / ' . $billing) ?>
                    <?php endif; ?>
                </div>
                <?php if ($startedAt): ?>
                <div style="font-size:.75rem;color:var(--text-secondary);margin-top:2px;">
                    Active since <?= date('M j, Y', strtotime($startedAt)) ?>
                    <?php if ($expiresAt): ?>
                        · Expires <?= date('M j, Y', strtotime($expiresAt)) ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div style="font-size:.8rem;color:var(--text-secondary);margin-top:2px;">No active subscription</div>
            <?php endif; ?>
        </div>
        <!-- Status badge -->
        <div style="flex-shrink:0;">
            <?php if ($planName): ?>
                <span style="display:inline-block;padding:4px 12px;background:rgba(0,255,136,.15);color:var(--green);border-radius:20px;font-size:.75rem;font-weight:600;">Active</span>
            <?php else: ?>
                <span style="display:inline-block;padding:4px 12px;background:rgba(255,170,0,.15);color:#ffaa00;border-radius:20px;font-size:.75rem;font-weight:600;">Free</span>
            <?php endif; ?>
        </div>
        <!-- Actions -->
        <div style="flex-shrink:0;display:flex;gap:8px;">
            <a href="<?= $meta['url'] ?>" style="padding:7px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:6px;font-size:.8rem;font-weight:600;color:var(--text-primary);text-decoration:none;transition:all .2s;" onmouseover="this.style.borderColor='var(--cyan)';this.style.color='var(--cyan)'" onmouseout="this.style.borderColor='var(--border-color)';this.style.color='var(--text-primary)'">
                Open App
            </a>
            <?php if (!$planName): ?>
            <a href="#platform-plans" style="padding:7px 14px;background:linear-gradient(135deg,var(--purple),var(--cyan));border:none;border-radius:6px;font-size:.8rem;font-weight:600;color:#fff;text-decoration:none;transition:all .2s;">
                Upgrade
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</section>

<!-- ═══════════════════════════════════════════════════════════════════════
     SECTION 2 — Active platform subscriptions
═══════════════════════════════════════════════════════════════════════════ -->
<?php if (!empty($userPlatformSubs)): ?>
<section style="margin-bottom:32px;" id="active-platform">
    <h2 style="font-size:1rem;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        Your Active Platform Subscriptions
    </h2>
    <?php foreach ($userPlatformSubs as $sub):
        $apps = json_decode($sub['included_apps'] ?? '[]', true) ?: [];
    ?>
    <div style="background:var(--bg-card);border:2px solid <?= View::e($sub['color'] ?? '#9945ff') ?>;border-radius:10px;padding:16px 20px;margin-bottom:12px;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <div style="font-weight:700;font-size:1rem;color:<?= View::e($sub['color'] ?? '#9945ff') ?>;">
                    <?= View::e($sub['plan_name']) ?>
                </div>
                <div style="font-size:.8rem;color:var(--text-secondary);margin-top:4px;">
                    <?= $sub['price'] == 0 ? 'Free' : ('$' . number_format((float)$sub['price'], 2) . ' / ' . $sub['billing_cycle']) ?>
                    &middot; Active since <?= date('M j, Y', strtotime($sub['started_at'])) ?>
                </div>
                <div style="margin-top:10px;display:flex;flex-wrap:wrap;gap:6px;">
                    <?php foreach ($apps as $appKey):
                        $m = $appMeta[$appKey] ?? null;
                        if (!$m) continue;
                    ?>
                    <a href="<?= $m['url'] ?>" style="padding:3px 10px;background:<?= $m['color'] ?>20;color:<?= $m['color'] ?>;border-radius:20px;font-size:.75rem;font-weight:600;text-decoration:none;">
                        <?= View::e($m['name']) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <span style="padding:5px 14px;background:rgba(0,255,136,.15);color:var(--green);border-radius:20px;font-size:.8rem;font-weight:600;white-space:nowrap;">Active</span>
        </div>
    </div>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════════════════
     SECTION 3 — Available platform plans (upgrade)
═══════════════════════════════════════════════════════════════════════════ -->
<section id="platform-plans">
    <h2 style="font-size:1rem;font-weight:700;margin-bottom:6px;display:flex;align-items:center;gap:8px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--magenta)" stroke-width="2">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
        </svg>
        Platform Plans (Multi-App Bundles)
    </h2>
    <p style="color:var(--text-secondary);font-size:.85rem;margin-bottom:18px;">One plan, multiple applications. Contact admin or your account manager to subscribe.</p>

    <?php if (empty($platformPlans)): ?>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:40px;text-align:center;color:var(--text-secondary);">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:12px;opacity:.4;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        <div>No platform plans available yet.</div>
    </div>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
    <?php foreach ($platformPlans as $plan):
        $isActive  = in_array($plan['id'], $activePlatformPlanIds);
        $planApps  = $plan['included_apps'] ?? [];
        $planColor = $plan['color'] ?? '#9945ff';
    ?>
    <div style="background:var(--bg-card);border:2px solid <?= $isActive ? $planColor : 'var(--border-color)' ?>;border-radius:12px;overflow:hidden;transition:all .3s;" onmouseover="this.style.borderColor='<?= $planColor ?>'" onmouseout="this.style.borderColor='<?= $isActive ? $planColor : 'var(--border-color)' ?>'">
        <!-- Header -->
        <div style="background:linear-gradient(135deg,<?= $planColor ?>22,<?= $planColor ?>08);padding:20px;border-bottom:1px solid var(--border-color);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                    <div style="font-weight:700;font-size:1.05rem;color:<?= $planColor ?>;"><?= View::e($plan['name']) ?></div>
                    <div style="font-size:1.4rem;font-weight:800;margin-top:6px;">
                        <?= $plan['price'] == 0 ? 'Free' : ('$' . number_format((float)$plan['price'], 2)) ?>
                        <?php if ($plan['price'] > 0): ?>
                        <span style="font-size:.75rem;font-weight:400;color:var(--text-secondary);">/ <?= $plan['billing_cycle'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($isActive): ?>
                <span style="padding:4px 12px;background:rgba(0,255,136,.15);color:var(--green);border-radius:20px;font-size:.75rem;font-weight:600;">Active</span>
                <?php endif; ?>
            </div>
            <?php if (!empty($plan['description'])): ?>
            <p style="font-size:.82rem;color:var(--text-secondary);margin-top:10px;line-height:1.5;"><?= View::e($plan['description']) ?></p>
            <?php endif; ?>
        </div>
        <!-- Included apps -->
        <div style="padding:16px 20px;">
            <div style="font-size:.75rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Includes</div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:16px;">
                <?php foreach ($planApps as $appKey):
                    $m = $appMeta[$appKey] ?? null;
                    if (!$m) continue;
                ?>
                <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;background:<?= $m['color'] ?>15;color:<?= $m['color'] ?>;border-radius:20px;font-size:.75rem;font-weight:600;">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <?= View::e($m['name']) ?>
                </span>
                <?php endforeach; ?>
            </div>
            <!-- CTA -->
            <?php if ($isActive): ?>
            <div style="padding:9px;background:rgba(0,255,136,.1);border-radius:6px;text-align:center;font-size:.82rem;color:var(--green);font-weight:600;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                You're subscribed
            </div>
            <?php else: ?>
            <a href="mailto:<?= htmlspecialchars($contactEmail ?? 'support@mmbtech.online') ?>?subject=Upgrade+to+<?= urlencode($plan['name']) ?>&body=Hi,+I'd+like+to+upgrade+to+the+<?= urlencode($plan['name']) ?>+plan.+User:+<?= urlencode(Auth::user()['email'] ?? '') ?>"
               style="display:block;width:100%;padding:10px;background:linear-gradient(135deg,<?= $planColor ?>,<?= $planColor ?>aa);border:none;border-radius:6px;text-align:center;font-size:.85rem;font-weight:700;color:#fff;text-decoration:none;transition:all .2s;" onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
                Upgrade to <?= View::e($plan['name']) ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<?php View::end(); ?>
