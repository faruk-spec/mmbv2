<?php
/**
 * QR Generator — My Plan view
 * Rendered via DashboardController::plan() which wraps this in layout.php.
 */
use Core\Auth;
?>

<!-- ── Page heading ───────────────────────────────────────────────────── -->
<div style="margin-bottom:24px;">
    <h1 style="font-size:1.5rem;font-weight:700;margin-bottom:6px;display:flex;align-items:center;gap:10px;">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
        </svg>
        My QR Plan
    </h1>
    <p style="color:var(--text-secondary);font-size:.9rem;">
        View your current QR Generator subscription and explore upgrade options.
    </p>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════
     SECTION 1 — Current QR subscription
═══════════════════════════════════════════════════════════════════════════ -->
<section style="margin-bottom:32px;">
    <h2 style="font-size:1rem;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        QR Generator Subscription
    </h2>

    <?php if ($qrSub): ?>
    <div style="background:var(--bg-card);border:2px solid var(--purple);border-radius:12px;padding:20px;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
            <div>
                <div style="font-weight:700;font-size:1.1rem;color:var(--purple);"><?= htmlspecialchars($qrSub['plan_name']) ?></div>
                <div style="font-size:.82rem;color:var(--text-secondary);margin-top:4px;">
                    <?= $qrSub['price'] == 0 ? 'Free' : (htmlspecialchars($qrSub['currency'] ?? 'USD') . ' ' . number_format((float)$qrSub['price'], 2) . ' / ' . htmlspecialchars($qrSub['billing_cycle'])) ?>
                    &middot; Active since <?= date('M j, Y', strtotime($qrSub['started_at'])) ?>
                    <?php if (!empty($qrSub['expires_at'])): ?>
                        &middot; Expires <?= date('M j, Y', strtotime($qrSub['expires_at'])) ?>
                    <?php endif; ?>
                </div>
            </div>
            <span style="padding:5px 14px;background:rgba(0,255,136,.15);color:var(--green);border-radius:20px;font-size:.8rem;font-weight:600;">Active</span>
        </div>

        <!-- Usage bars -->
        <?php
        $maxStatic  = (int)($qrSub['max_static_qr']  ?? 0);
        $maxDynamic = (int)($qrSub['max_dynamic_qr'] ?? 0);
        ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
            <?php if ($maxStatic > 0): ?>
            <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;padding:12px;">
                <div style="font-size:.75rem;font-weight:600;color:var(--text-secondary);margin-bottom:6px;">Static QR Codes</div>
                <div style="font-size:1.2rem;font-weight:700;"><?= $staticCount ?> / <?= $maxStatic ?></div>
                <div style="margin-top:6px;height:4px;background:var(--border-color);border-radius:2px;overflow:hidden;">
                    <div style="height:100%;background:var(--purple);border-radius:2px;width:<?= min(100, round($staticCount / $maxStatic * 100)) ?>%;"></div>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($maxDynamic > 0): ?>
            <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;padding:12px;">
                <div style="font-size:.75rem;font-weight:600;color:var(--text-secondary);margin-bottom:6px;">Dynamic QR Codes</div>
                <div style="font-size:1.2rem;font-weight:700;"><?= $dynamicCount ?> / <?= $maxDynamic ?></div>
                <div style="margin-top:6px;height:4px;background:var(--border-color);border-radius:2px;overflow:hidden;">
                    <div style="height:100%;background:var(--cyan);border-radius:2px;width:<?= min(100, round($dynamicCount / $maxDynamic * 100)) ?>%;"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($qrSub['features'])): ?>
        <div style="margin-top:14px;">
            <div style="font-size:.75rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Included Features</div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;">
            <?php foreach ($qrSub['features'] as $feat => $enabled):
                if (!$enabled) continue;
                $label = ucwords(str_replace('_', ' ', $feat));
            ?>
                <span style="padding:3px 10px;background:rgba(153,69,255,.15);color:var(--purple);border-radius:20px;font-size:.75rem;font-weight:600;"><?= htmlspecialchars($label) ?></span>
            <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php else: ?>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
        <div>
            <div style="font-weight:600;font-size:.95rem;margin-bottom:4px;">Free Plan</div>
            <div style="font-size:.82rem;color:var(--text-secondary);">
                Currently using: <?= $staticCount ?> static QR code<?= $staticCount !== 1 ? 's' : '' ?>
                <?php if ($dynamicCount > 0): ?>, <?= $dynamicCount ?> dynamic<?php endif; ?>
            </div>
        </div>
        <a href="#qr-plans" style="padding:9px 18px;background:linear-gradient(135deg,var(--purple),var(--cyan));border-radius:8px;font-size:.85rem;font-weight:700;color:#fff;text-decoration:none;">
            Upgrade Plan
        </a>
    </div>
    <?php endif; ?>
</section>

<!-- ═══════════════════════════════════════════════════════════════════════
     SECTION 2 — QR Generator specific plans
═══════════════════════════════════════════════════════════════════════════ -->
<?php if (!empty($qrUpgradePlans)): ?>
<section id="qr-plans" style="margin-bottom:32px;">
    <h2 style="font-size:1rem;font-weight:700;margin-bottom:6px;display:flex;align-items:center;gap:8px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
        </svg>
        QR Generator Plans
    </h2>
    <p style="color:var(--text-secondary);font-size:.85rem;margin-bottom:18px;">
        Upgrade your QR Generator to unlock more features and higher limits.
    </p>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;">
    <?php foreach ($qrUpgradePlans as $qrPlan):
        $isCurrent = $qrSub && ((int)($qrSub['plan_id'] ?? 0) === (int)$qrPlan['id']);
        $isFree = ($qrPlan['price'] == 0);
        $feats = $qrPlan['features_arr'] ?? [];
        $planSlug = $qrPlan['slug'] ?? $qrPlan['id'];
    ?>
    <div style="background:var(--bg-card);border:2px solid <?= $isCurrent ? 'var(--purple)' : 'var(--border-color)' ?>;border-radius:12px;overflow:hidden;transition:border-color .2s;"
         onmouseover="this.style.borderColor='var(--purple)'" onmouseout="this.style.borderColor='<?= $isCurrent ? 'var(--purple)' : 'var(--border-color)' ?>'">
        <div style="background:linear-gradient(135deg,rgba(153,69,255,.12),rgba(0,240,255,.06));padding:18px;border-bottom:1px solid var(--border-color);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                    <div style="font-weight:700;font-size:1rem;color:var(--purple);"><?= htmlspecialchars($qrPlan['name']) ?></div>
                    <div style="font-size:1.3rem;font-weight:800;margin-top:4px;">
                        <?= $isFree ? 'Free' : (htmlspecialchars($qrPlan['currency'] ?? 'USD') . ' ' . number_format((float)$qrPlan['price'], 2)) ?>
                        <?php if (!$isFree): ?>
                        <span style="font-size:.75rem;font-weight:400;color:var(--text-secondary);">/ <?= htmlspecialchars($qrPlan['billing_cycle'] ?? 'month') ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($isCurrent): ?>
                <span style="padding:3px 10px;background:rgba(0,255,136,.15);color:var(--green);border-radius:20px;font-size:.72rem;font-weight:600;">Current</span>
                <?php endif; ?>
            </div>
            <?php if (!empty($qrPlan['description'])): ?>
            <p style="font-size:.8rem;color:var(--text-secondary);margin-top:8px;line-height:1.5;"><?= htmlspecialchars($qrPlan['description']) ?></p>
            <?php endif; ?>
        </div>
        <div style="padding:14px 18px;">
            <!-- Limits -->
            <div style="font-size:.72rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Limits</div>
            <ul style="list-style:none;padding:0;margin:0 0 12px;font-size:.8rem;color:var(--text-secondary);">
                <?php if ((int)$qrPlan['max_static_qr'] > 0): ?>
                <li>▸ <?= number_format((int)$qrPlan['max_static_qr']) ?> static QR codes</li>
                <?php elseif ((int)$qrPlan['max_static_qr'] === -1): ?>
                <li style="color:var(--green);">▸ Unlimited static QR codes</li>
                <?php endif; ?>
                <?php if ((int)$qrPlan['max_dynamic_qr'] > 0): ?>
                <li>▸ <?= number_format((int)$qrPlan['max_dynamic_qr']) ?> dynamic QR codes</li>
                <?php elseif ((int)$qrPlan['max_dynamic_qr'] === -1): ?>
                <li style="color:var(--green);">▸ Unlimited dynamic QR codes</li>
                <?php endif; ?>
            </ul>
            <!-- Included features -->
            <?php $enabledFeats = array_keys(array_filter($feats)); if (!empty($enabledFeats)): ?>
            <div style="font-size:.72rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Includes</div>
            <div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:14px;">
                <?php foreach (array_slice($enabledFeats, 0, 8) as $fk): ?>
                <span style="padding:2px 8px;background:rgba(153,69,255,.12);color:var(--purple);border-radius:20px;font-size:.7rem;font-weight:600;"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $fk))) ?></span>
                <?php endforeach; ?>
                <?php if (count($enabledFeats) > 8): ?>
                <span style="padding:2px 8px;background:rgba(0,240,255,.08);color:var(--cyan);border-radius:20px;font-size:.7rem;">+<?= count($enabledFeats) - 8 ?> more</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <!-- CTA -->
            <?php if ($isCurrent): ?>
            <div style="padding:8px;background:rgba(0,255,136,.1);border-radius:6px;text-align:center;font-size:.8rem;color:var(--green);font-weight:600;">
                ✓ Your current plan
            </div>
            <?php elseif ($isFree): ?>
            <div style="padding:8px;background:rgba(255,255,255,.05);border-radius:6px;text-align:center;font-size:.8rem;color:var(--text-secondary);">
                Default free tier
            </div>
            <?php else: ?>
            <a href="/plans/project/qr/<?= urlencode($planSlug) ?>"
               style="display:block;width:100%;padding:9px;background:linear-gradient(135deg,var(--purple),var(--cyan));border-radius:6px;text-align:center;font-size:.82rem;font-weight:700;color:#000;text-decoration:none;">
                Upgrade to <?= htmlspecialchars($qrPlan['name']) ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($qrHistory)): ?>
<section style="margin-bottom:32px;">
    <h2 style="font-size:1rem;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
        Subscription History
    </h2>
    <div style="display:flex;flex-direction:column;gap:10px;">
        <?php foreach ($qrHistory as $history): ?>
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:14px 18px;display:grid;grid-template-columns:1fr auto auto;gap:12px;align-items:center;">
            <div>
                <strong><?= htmlspecialchars($history['plan_name']) ?></strong>
                <div style="font-size:.78rem;color:var(--text-secondary);margin-top:4px;">
                    <?= htmlspecialchars($history['currency'] ?? 'USD') ?> <?= number_format((float) ($history['price'] ?? 0), 2) ?> / <?= htmlspecialchars($history['billing_cycle'] ?? 'monthly') ?>
                    &middot; Started <?= date('M j, Y', strtotime($history['started_at'])) ?>
                    <?php if (!empty($history['expires_at'])): ?> &middot; Expires <?= date('M j, Y', strtotime($history['expires_at'])) ?><?php endif; ?>
                </div>
            </div>
            <span style="padding:4px 12px;border-radius:999px;font-size:.75rem;font-weight:600;background:<?= ($history['status'] ?? '') === 'active' ? 'rgba(0,255,136,.12)' : 'rgba(255,170,0,.12)' ?>;color:<?= ($history['status'] ?? '') === 'active' ? 'var(--green)' : '#ffaa00' ?>;">
                <?= htmlspecialchars(ucfirst($history['status'] ?? 'unknown')) ?>
            </span>
            <div style="display:flex;gap:8px;">
                <?php foreach (($paymentHistory ?? []) as $payment): if ((int) ($payment['subscription_id'] ?? 0) !== (int) ($history['id'] ?? 0)) continue; ?>
                <a href="/plans/payment/<?= (int) $payment['id'] ?>/invoice" class="btn btn-secondary btn-sm">Invoice</a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($paymentHistory)): ?>
<section>
    <h2 style="font-size:1rem;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--magenta)" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
        Payment History
    </h2>
    <div style="display:flex;flex-direction:column;gap:10px;">
        <?php foreach ($paymentHistory as $payment): ?>
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:14px 18px;display:grid;grid-template-columns:1fr auto auto;gap:12px;align-items:center;">
            <div>
                <strong><?= htmlspecialchars($payment['plan_name']) ?></strong>
                <div style="font-size:.78rem;color:var(--text-secondary);margin-top:4px;"><?= htmlspecialchars($payment['currency']) ?> <?= number_format((float) $payment['amount'], 2) ?> &middot; <?= strtoupper(htmlspecialchars($payment['gateway'])) ?> &middot; Ref <?= htmlspecialchars($payment['reference']) ?></div>
            </div>
            <span style="padding:4px 12px;border-radius:999px;font-size:.75rem;font-weight:600;background:<?= ($payment['status'] ?? '') === 'paid' ? 'rgba(0,255,136,.12)' : 'rgba(0,240,255,.12)' ?>;color:<?= ($payment['status'] ?? '') === 'paid' ? 'var(--green)' : 'var(--cyan)' ?>;">
                <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $payment['status'] ?? 'pending'))) ?>
            </span>
            <a href="/plans/payment/<?= (int) $payment['id'] ?>" class="btn btn-secondary btn-sm">View</a>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

