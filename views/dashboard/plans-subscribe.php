<?php use Core\View; use Core\Helpers; use Core\Auth; use Core\Security; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<?php if (Helpers::hasFlash('error')): ?>
<div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<?php
$planColor = View::e($plan['color'] ?? '#9945ff');
$planApps  = $plan['included_apps'] ?? [];
$isFree    = (float)($plan['price'] ?? 0) === 0.0;
?>

<!-- Breadcrumb -->
<div style="margin-bottom:20px;">
    <a href="/plans" style="color:var(--text-secondary);font-size:.875rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;" onmouseover="this.style.color='var(--cyan)'" onmouseout="this.style.color='var(--text-secondary)'">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        Back to Plans
    </a>
</div>

<div style="max-width:600px;">

    <!-- Plan summary card -->
    <div style="background:var(--bg-card);border:2px solid <?= $planColor ?>;border-radius:14px;overflow:hidden;margin-bottom:24px;">
        <div style="background:linear-gradient(135deg,<?= $planColor ?>22,<?= $planColor ?>08);padding:24px;">
            <div style="font-weight:700;font-size:1.1rem;color:<?= $planColor ?>;"><?= View::e($plan['name']) ?></div>
            <div style="font-size:1.6rem;font-weight:800;margin:10px 0 4px;">
                <?= $isFree ? 'Free' : ('$' . number_format((float)$plan['price'], 2)) ?>
                <?php if (!$isFree): ?>
                <span style="font-size:.75rem;font-weight:400;color:var(--text-secondary);">/ <?= View::e($plan['billing_cycle']) ?></span>
                <?php endif; ?>
            </div>
            <?php if (!empty($plan['description'])): ?>
            <p style="font-size:.85rem;color:var(--text-secondary);margin-top:8px;line-height:1.5;"><?= View::e($plan['description']) ?></p>
            <?php endif; ?>
        </div>
        <?php if (!empty($planApps)): ?>
        <div style="padding:16px 24px;border-top:1px solid var(--border-color);">
            <div style="font-size:.75rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:10px;">Included Applications</div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                <?php foreach ($planApps as $ak):
                    $m = $appMeta[$ak] ?? null; if (!$m) continue; ?>
                <span style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:<?= $m['color'] ?>15;color:<?= $m['color'] ?>;border-radius:20px;font-size:.78rem;font-weight:600;">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <?= View::e($m['name']) ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($existing): ?>
    <!-- Already subscribed -->
    <div style="background:rgba(0,255,136,.08);border:1px solid var(--green);border-radius:10px;padding:20px;text-align:center;">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2" style="margin-bottom:10px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <div style="font-weight:700;font-size:1rem;color:var(--green);margin-bottom:6px;">You're already subscribed!</div>
        <div style="font-size:.85rem;color:var(--text-secondary);">Active since <?= date('M j, Y', strtotime($existing['started_at'])) ?></div>
        <a href="/plans" style="display:inline-block;margin-top:16px;padding:8px 20px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:6px;color:var(--text-primary);text-decoration:none;font-size:.875rem;">← Back to Plans</a>
    </div>

    <?php else: ?>
    <!-- Subscription request form -->
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:24px;">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:6px;">
            <?= $isFree ? 'Activate Plan' : 'Request Subscription' ?>
        </h2>
        <p style="color:var(--text-secondary);font-size:.85rem;margin-bottom:20px;">
            <?php if ($isFree): ?>
                This plan is free. Click below to activate it instantly.
            <?php else: ?>
                Your request will be reviewed by an admin who will activate the subscription for you.
            <?php endif; ?>
        </p>

        <form method="POST" action="/plans/subscribe/<?= urlencode($plan['slug']) ?>">
            <input type="hidden" name="_csrf_token" value="<?= Security::generateCsrfToken() ?>">

            <!-- User info (read-only) -->
            <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;padding:14px 16px;margin-bottom:16px;font-size:.85rem;">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="color:var(--text-secondary);">Account</span>
                    <span style="color:var(--text-primary);font-weight:500;"><?= View::e(Auth::user()['email'] ?? '') ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:var(--text-secondary);">Plan</span>
                    <span style="color:<?= $planColor ?>;font-weight:600;"><?= View::e($plan['name']) ?></span>
                </div>
            </div>

            <?php if (!$isFree): ?>
            <!-- Optional message for paid plans -->
            <div class="form-group">
                <label class="form-label">Message to Admin <span style="color:var(--text-secondary);font-weight:400;">(optional)</span></label>
                <textarea name="message" rows="3"
                    placeholder="Any additional information about your request..."
                    class="form-input" style="resize:vertical;"><?= View::e($_POST['message'] ?? '') ?></textarea>
            </div>

            <!-- Pricing reminder -->
            <div style="background:rgba(153,69,255,.08);border:1px solid rgba(153,69,255,.3);border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:.82rem;color:var(--text-secondary);">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2" style="vertical-align:middle;margin-right:6px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                Price: <strong style="color:var(--text-primary);">$<?= number_format((float)$plan['price'],2) ?> / <?= View::e($plan['billing_cycle']) ?></strong>.
                Payment and activation will be handled by an admin after reviewing your request.
            </div>
            <?php endif; ?>

            <div style="display:flex;gap:12px;">
                <button type="submit" style="flex:1;padding:12px;background:linear-gradient(135deg,<?= $planColor ?>,<?= $planColor ?>bb);border:none;border-radius:8px;color:#fff;font-size:.9rem;font-weight:700;cursor:pointer;transition:opacity .2s;" onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                    <?= $isFree ? '✓ Activate Now' : '→ Submit Request' ?>
                </button>
                <a href="/plans" style="padding:12px 20px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);text-decoration:none;font-size:.85rem;display:inline-flex;align-items:center;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>

</div>

<?php View::end(); ?>
