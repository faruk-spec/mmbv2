<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<!-- Flash messages -->
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

<!-- Header -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;margin-bottom:4px;">Platform Plans</h1>
        <p style="color:var(--text-secondary);font-size:.875rem;">Universal plans that bundle multiple applications into a single subscription.</p>
    </div>
    <a href="/admin/platform-plans/create" style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;background:linear-gradient(135deg,var(--purple),var(--cyan));border:none;border-radius:8px;color:#fff;font-size:.875rem;font-weight:600;text-decoration:none;">
        <i class="fas fa-plus"></i> New Plan
    </a>
</div>

<!-- Stats row -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:16px;margin-bottom:24px;">
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:18px;text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:var(--cyan);"><?= count($plans) ?></div>
        <div style="color:var(--text-secondary);font-size:.8rem;">Total Plans</div>
    </div>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:18px;text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:var(--green);"><?= count(array_filter($plans, fn($p) => $p['status'] === 'active')) ?></div>
        <div style="color:var(--text-secondary);font-size:.8rem;">Active</div>
    </div>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:18px;text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:var(--purple);"><?= array_sum(array_column($plans, 'subscriber_count')) ?></div>
        <div style="color:var(--text-secondary);font-size:.8rem;">Total Subscribers</div>
    </div>
</div>

<!-- Plans table -->
<?php if (empty($plans)): ?>
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:48px;text-align:center;color:var(--text-secondary);">
    <i class="fas fa-layer-group" style="font-size:2.5rem;margin-bottom:16px;opacity:.3;display:block;"></i>
    <p>No platform plans yet. <a href="/admin/platform-plans/create" style="color:var(--cyan);text-decoration:none;">Create the first plan</a>.</p>
</div>
<?php else: ?>
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
        <thead>
            <tr style="background:rgba(255,255,255,.03);border-bottom:1px solid var(--border-color);">
                <th style="padding:12px 16px;text-align:left;color:var(--text-secondary);font-weight:600;">Plan</th>
                <th style="padding:12px 16px;text-align:left;color:var(--text-secondary);font-weight:600;">Price</th>
                <th style="padding:12px 16px;text-align:left;color:var(--text-secondary);font-weight:600;">Included Apps</th>
                <th style="padding:12px 16px;text-align:left;color:var(--text-secondary);font-weight:600;">Subscribers</th>
                <th style="padding:12px 16px;text-align:left;color:var(--text-secondary);font-weight:600;">Status</th>
                <th style="padding:12px 16px;text-align:center;color:var(--text-secondary);font-weight:600;">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($plans as $plan):
            $apps = $plan['included_apps'];
        ?>
        <tr style="border-bottom:1px solid var(--border-color);transition:background .2s;" onmouseover="this.style.background='rgba(255,255,255,.02)'" onmouseout="this.style.background='transparent'">
            <td style="padding:14px 16px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:10px;height:10px;border-radius:50%;background:<?= View::e($plan['color'] ?? '#9945ff') ?>;flex-shrink:0;"></div>
                    <div>
                        <div style="font-weight:600;"><?= View::e($plan['name']) ?></div>
                        <div style="font-size:.75rem;color:var(--text-secondary);"><?= View::e($plan['slug']) ?></div>
                    </div>
                </div>
            </td>
            <td style="padding:14px 16px;">
                <?php if ((float)$plan['price'] === 0.0): ?>
                    <span style="color:var(--green);font-weight:600;">Free</span>
                <?php else: ?>
                    <span style="font-weight:600;">$<?= number_format((float)$plan['price'], 2) ?></span>
                    <span style="color:var(--text-secondary);font-size:.8rem;">/ <?= $plan['billing_cycle'] ?></span>
                <?php endif; ?>
            </td>
            <td style="padding:14px 16px;">
                <div style="display:flex;flex-wrap:wrap;gap:5px;">
                <?php foreach ($apps as $appKey):
                    $appName = $appNames[$appKey] ?? ucfirst($appKey);
                ?>
                <span style="padding:2px 8px;background:rgba(0,240,255,.1);color:var(--cyan);border-radius:12px;font-size:.72rem;font-weight:600;"><?= View::e($appName) ?></span>
                <?php endforeach; ?>
                <?php if (empty($apps)): ?><span style="color:var(--text-secondary);font-size:.8rem;">None</span><?php endif; ?>
                </div>
            </td>
            <td style="padding:14px 16px;">
                <span style="font-weight:600;color:var(--cyan);"><?= (int)$plan['subscriber_count'] ?></span>
            </td>
            <td style="padding:14px 16px;">
                <?php if ($plan['status'] === 'active'): ?>
                <span style="padding:3px 10px;background:rgba(0,255,136,.1);color:var(--green);border-radius:12px;font-size:.75rem;font-weight:600;">Active</span>
                <?php else: ?>
                <span style="padding:3px 10px;background:rgba(255,107,107,.1);color:var(--red);border-radius:12px;font-size:.75rem;font-weight:600;">Inactive</span>
                <?php endif; ?>
            </td>
            <td style="padding:14px 16px;text-align:center;">
                <div style="display:flex;gap:8px;justify-content:center;">
                    <a href="/admin/platform-plans/<?= (int)$plan['id'] ?>/edit" style="padding:6px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:6px;font-size:.8rem;color:var(--text-primary);text-decoration:none;" onmouseover="this.style.borderColor='var(--cyan)';this.style.color='var(--cyan)'" onmouseout="this.style.borderColor='var(--border-color)';this.style.color='var(--text-primary)'">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form method="POST" action="/admin/platform-plans/<?= (int)$plan['id'] ?>/delete" style="margin:0;" onsubmit="return confirm('Delete plan <?= addslashes(View::e($plan['name'])) ?>?')">
                        <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                        <button type="submit" style="padding:6px 12px;background:rgba(255,107,107,.1);border:1px solid var(--red);border-radius:6px;font-size:.8rem;color:var(--red);cursor:pointer;" onmouseover="this.style.background='rgba(255,107,107,.25)'" onmouseout="this.style.background='rgba(255,107,107,.1)'">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php View::end(); ?>
