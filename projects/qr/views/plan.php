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
                    <?= $qrSub['price'] == 0 ? 'Free' : ('$' . number_format((float)$qrSub['price'], 2) . ' / ' . htmlspecialchars($qrSub['billing_cycle'])) ?>
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
        <a href="#platform-plans" style="padding:9px 18px;background:linear-gradient(135deg,var(--purple),var(--cyan));border-radius:8px;font-size:.85rem;font-weight:700;color:#fff;text-decoration:none;">
            Upgrade Plan
        </a>
    </div>
    <?php endif; ?>
</section>

<!-- ═══════════════════════════════════════════════════════════════════════
     SECTION 2 — Platform / Bundle plans
═══════════════════════════════════════════════════════════════════════════ -->
<section id="platform-plans">
    <h2 style="font-size:1rem;font-weight:700;margin-bottom:6px;display:flex;align-items:center;gap:8px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--magenta)" stroke-width="2">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
        </svg>
        Platform Plans (Multi-App Bundles)
    </h2>
    <p style="color:var(--text-secondary);font-size:.85rem;margin-bottom:18px;">
        One plan covering multiple applications.
        <a href="/plans" style="color:var(--cyan);text-decoration:none;">View all plans &rarr;</a>
    </p>

    <?php if (empty($platformPlans)): ?>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:40px;text-align:center;color:var(--text-secondary);">
        <div style="opacity:.4;margin-bottom:10px;">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
        </div>
        No platform plans available yet.
    </div>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px;">
    <?php foreach ($platformPlans as $plan):
        $isActive  = in_array($plan['id'], $activePlatformPlanIds);
        $planColor = $plan['color'] ?? '#9945ff';
        $apps      = is_array($plan['included_apps']) ? $plan['included_apps'] : [];
        $includesQR = in_array('qr', $apps);
    ?>
    <div style="background:var(--bg-card);border:2px solid <?= $isActive ? $planColor : 'var(--border-color)' ?>;border-radius:12px;overflow:hidden;transition:border-color .2s;" onmouseover="this.style.borderColor='<?= $planColor ?>'" onmouseout="this.style.borderColor='<?= $isActive ? $planColor : 'var(--border-color)' ?>'">
        <div style="background:linear-gradient(135deg,<?= $planColor ?>22,<?= $planColor ?>08);padding:18px;border-bottom:1px solid var(--border-color);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                    <div style="font-weight:700;font-size:1rem;color:<?= $planColor ?>;"><?= htmlspecialchars($plan['name']) ?></div>
                    <div style="font-size:1.3rem;font-weight:800;margin-top:4px;">
                        <?= $plan['price'] == 0 ? 'Free' : ('$' . number_format((float)$plan['price'], 2)) ?>
                        <?php if ($plan['price'] > 0): ?>
                        <span style="font-size:.75rem;font-weight:400;color:var(--text-secondary);">/ <?= htmlspecialchars($plan['billing_cycle']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($isActive): ?>
                <span style="padding:3px 10px;background:rgba(0,255,136,.15);color:var(--green);border-radius:20px;font-size:.72rem;font-weight:600;">Active</span>
                <?php elseif ($includesQR): ?>
                <span style="padding:3px 10px;background:rgba(153,69,255,.15);color:var(--purple);border-radius:20px;font-size:.72rem;font-weight:600;">Includes QR</span>
                <?php endif; ?>
            </div>
            <?php if (!empty($plan['description'])): ?>
            <p style="font-size:.8rem;color:var(--text-secondary);margin-top:8px;line-height:1.5;"><?= htmlspecialchars($plan['description']) ?></p>
            <?php endif; ?>
        </div>
        <div style="padding:14px 18px;">
            <?php if (!empty($apps)): ?>
            <div style="font-size:.72rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Includes</div>
            <div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:14px;">
                <?php
                $appNames = ['qr'=>'QR Generator','whatsapp'=>'WhatsApp API','proshare'=>'ProShare','codexpro'=>'CodeXPro','imgtxt'=>'ImgTxt','resumex'=>'ResumeX'];
                $appColors= ['qr'=>'#9945ff','whatsapp'=>'#25D366','proshare'=>'#ffaa00','codexpro'=>'#00f0ff','imgtxt'=>'#00ff88','resumex'=>'#ff6b6b'];
                foreach ($apps as $appKey):
                    $appName  = $appNames[$appKey]  ?? ucfirst($appKey);
                    $appColor = $appColors[$appKey] ?? '#aaa';
                ?>
                <span style="padding:3px 8px;background:<?= $appColor ?>18;color:<?= $appColor ?>;border-radius:20px;font-size:.72rem;font-weight:600;"><?= htmlspecialchars($appName) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($isActive): ?>
            <div style="padding:8px;background:rgba(0,255,136,.1);border-radius:6px;text-align:center;font-size:.8rem;color:var(--green);font-weight:600;">
                ✓ You're subscribed
            </div>
            <?php else: ?>
            <a href="/plans/subscribe/<?= urlencode($plan['slug'] ?? $plan['id']) ?>"
               style="display:block;width:100%;padding:9px;background:linear-gradient(135deg,<?= $planColor ?>,<?= $planColor ?>aa);border-radius:6px;text-align:center;font-size:.82rem;font-weight:700;color:#fff;text-decoration:none;">
                Upgrade to <?= htmlspecialchars($plan['name']) ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
