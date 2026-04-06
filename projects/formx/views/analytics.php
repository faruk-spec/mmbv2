<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('main'); ?>
<?php View::section('styles'); ?><style>.main{padding:0!important;}</style><?php View::endSection(); ?>

<?php View::section('content'); ?>
<style>
    .fx-layout{display:flex;min-height:calc(100vh - 70px);}
    .fx-sidebar{width:220px;flex-shrink:0;background:var(--bg-card);border-right:1px solid var(--border-color);display:flex;flex-direction:column;padding:24px 0 20px;position:sticky;top:0;height:calc(100vh - 70px);overflow-y:auto;}
    .fx-sidebar-logo{display:flex;align-items:center;gap:10px;padding:0 16px 18px;border-bottom:1px solid var(--border-color);margin-bottom:10px;}
    .fx-sidebar-logo-icon{width:32px;height:32px;border-radius:7px;background:linear-gradient(135deg,var(--cyan),var(--purple));display:flex;align-items:center;justify-content:center;font-weight:800;color:#06060a;font-size:.85rem;flex-shrink:0;}
    .fx-sidebar-logo-text{font-size:1rem;font-weight:800;background:linear-gradient(135deg,var(--cyan),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .fx-nav-section{padding:2px 8px;margin-bottom:2px;}
    .fx-nav-title{font-size:.65rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-secondary);padding:7px 8px 3px;opacity:.6;}
    .fx-nav-link{display:flex;align-items:center;gap:9px;padding:7px 9px;border-radius:7px;color:var(--text-secondary);text-decoration:none;font-size:.845rem;font-weight:500;transition:background .15s,color .15s;position:relative;}
    .fx-nav-link:hover{background:rgba(0,240,255,.07);color:var(--text-primary);text-decoration:none;}
    .fx-nav-link.active{background:rgba(0,240,255,.1);color:var(--cyan);}
    .fx-nav-link.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:60%;background:var(--cyan);border-radius:0 3px 3px 0;}
    .fx-nav-link i{width:16px;flex-shrink:0;text-align:center;opacity:.75;}
    .fx-main{flex:1;min-width:0;padding:28px;overflow-y:auto;}
    .fx-stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:24px;}
    .fx-stat{background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:18px 20px;}
    .fx-stat-val{font-size:1.9rem;font-weight:800;line-height:1;margin-bottom:5px;}
    .fx-stat-lbl{font-size:.75rem;color:var(--text-secondary);}
    .fx-card{background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:22px;margin-bottom:20px;}
    .fx-card h3{font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-secondary);margin:0 0 18px;}
    .bar-chart{display:flex;align-items:flex-end;gap:4px;height:120px;}
    .bar-wrap{flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;min-width:0;}
    .bar{width:100%;background:rgba(0,240,255,.3);border-radius:3px 3px 0 0;transition:background .15s;cursor:default;}
    .bar:hover{background:rgba(0,240,255,.7);}
    .bar-lbl{font-size:.6rem;color:var(--text-secondary);writing-mode:vertical-rl;transform:rotate(180deg);white-space:nowrap;overflow:hidden;max-height:40px;}
    .donut-wrap{display:flex;align-items:center;gap:24px;flex-wrap:wrap;}
    .donut-legend{display:flex;flex-direction:column;gap:8px;}
    .donut-item{display:flex;align-items:center;gap:8px;font-size:.82rem;}
    .donut-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;}
    .fx-sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99;}
    .fx-sidebar-toggle{display:none;position:fixed;bottom:24px;right:20px;z-index:100;width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--cyan),var(--purple));border:none;cursor:pointer;align-items:center;justify-content:center;box-shadow:0 4px 18px rgba(0,240,255,.4);color:#06060a;font-size:1.1rem;}
    @media(max-width:900px){
        .fx-sidebar{position:fixed;left:-240px;top:0;height:100vh;z-index:100;width:220px;transition:left .25s;padding-top:70px;}
        .fx-sidebar.open{left:0;}
        .fx-sidebar-overlay{display:block;opacity:0;pointer-events:none;transition:opacity .25s;}
        .fx-sidebar-overlay.active{opacity:1;pointer-events:all;}
        .fx-sidebar-toggle{display:flex;}
        .fx-main{padding:18px 14px;}
    }
</style>

<div class="fx-layout">
    <aside class="fx-sidebar" id="fxSidebar">
        <div class="fx-sidebar-logo">
            <div class="fx-sidebar-logo-icon"><i class="fas fa-wpforms" style="-webkit-text-fill-color:#06060a;"></i></div>
            <span class="fx-sidebar-logo-text">FormX</span>
        </div>
        <div class="fx-nav-section">
            <div class="fx-nav-title">Workspace</div>
            <a href="/projects/formx" class="fx-nav-link"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
            <a href="/projects/formx/create" class="fx-nav-link"><i class="fas fa-plus-circle"></i><span>New Form</span></a>
            <a href="/projects/formx/forms" class="fx-nav-link active"><i class="fas fa-list"></i><span>My Forms</span></a>
        </div>
        <div class="fx-nav-section">
            <div class="fx-nav-title">This Form</div>
            <a href="/projects/formx/<?= (int)$form['id'] ?>/edit" class="fx-nav-link"><i class="fas fa-edit"></i><span>Edit Builder</span></a>
            <a href="/projects/formx/<?= (int)$form['id'] ?>/submissions" class="fx-nav-link"><i class="fas fa-inbox"></i><span>Submissions</span></a>
            <a href="/projects/formx/<?= (int)$form['id'] ?>/analytics" class="fx-nav-link active"><i class="fas fa-chart-bar"></i><span>Analytics</span></a>
            <a href="/projects/formx/<?= (int)$form['id'] ?>/versions" class="fx-nav-link"><i class="fas fa-history"></i><span>Versions</span></a>
        </div>
        <?php if (!empty($sidebarForms)): ?>
        <div class="fx-nav-section">
            <div class="fx-nav-title">Recent Forms</div>
            <?php foreach ($sidebarForms as $sf): ?>
            <a href="/projects/formx/<?= (int)$sf['id'] ?>/edit"
               class="fx-nav-link <?= (int)$sf['id'] === (int)$form['id'] ? 'active' : '' ?>"
               title="<?= htmlspecialchars($sf['title']) ?>">
                <i class="fas fa-file-alt" style="font-size:.75rem;"></i>
                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($sf['title']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </aside>

    <main class="fx-main">
        <!-- Back -->
        <div style="margin-bottom:14px;font-size:.82rem;">
            <a href="/projects/formx/<?= (int)$form['id'] ?>/edit" style="color:var(--text-secondary);text-decoration:none;">
                <i class="fas fa-arrow-left"></i> Back to form
            </a>
        </div>

        <div style="margin-bottom:22px;">
            <h1 style="font-size:1.4rem;font-weight:800;background:linear-gradient(135deg,var(--cyan),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin:0 0 3px;">Analytics</h1>
            <p style="color:var(--text-secondary);font-size:.85rem;margin:0;"><?= View::e($form['title']) ?></p>
        </div>

        <!-- Stats -->
        <div class="fx-stat-grid">
            <div class="fx-stat">
                <div class="fx-stat-val" style="color:var(--cyan);"><?= number_format($total) ?></div>
                <div class="fx-stat-lbl"><i class="fas fa-inbox"></i> Total Submissions</div>
            </div>
            <div class="fx-stat">
                <div class="fx-stat-val" style="color:var(--green);"><?= number_format($thisMonth) ?></div>
                <div class="fx-stat-lbl"><i class="fas fa-calendar-alt"></i> This Month</div>
            </div>
            <div class="fx-stat">
                <div class="fx-stat-val" style="color:var(--purple);"><?= number_format($lastMonth) ?></div>
                <div class="fx-stat-lbl"><i class="fas fa-clock"></i> Last 30 Days</div>
            </div>
            <div class="fx-stat">
                <div class="fx-stat-val" style="color:var(--orange);"><?= number_format($avgPerDay ?? 0, 1) ?></div>
                <div class="fx-stat-lbl"><i class="fas fa-chart-line"></i> Avg / Day</div>
            </div>
        </div>

        <?php
        // Build a full 30-day labels array
        $dailyMap = [];
        foreach ($daily as $d) $dailyMap[$d['day']] = (int)$d['cnt'];
        $labels = []; $counts = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('M j', strtotime($day));
            $counts[] = $dailyMap[$day] ?? 0;
        }
        $maxCount = max(1, max($counts));

        // Build weekly chart data (8 weeks)
        $weeklyMap = [];
        foreach ($weekly ?? [] as $w) $weeklyMap[$w['yw']] = (int)$w['cnt'];
        $wLabels = []; $wCounts = [];
        for ($i = 7; $i >= 0; $i--) {
            $ts = strtotime("-" . ($i * 7) . " days");
            $yw = date('oW', $ts);
            $wLabels[] = 'W' . date('W', $ts);
            $wCounts[] = $weeklyMap[$yw] ?? 0;
        }
        $wMax = max(1, max($wCounts));
        ?>

        <!-- Daily submissions chart -->
        <div class="fx-card">
            <h3><i class="fas fa-chart-bar"></i> Submissions – Last 30 Days</h3>
            <?php if (array_sum($counts) === 0): ?>
            <p style="color:var(--text-secondary);font-size:.85rem;text-align:center;padding:20px;">No submissions in the last 30 days.</p>
            <?php else: ?>
            <div class="bar-chart" id="dailyChart">
                <?php foreach ($counts as $i => $cnt): ?>
                <div class="bar-wrap" title="<?= htmlspecialchars($labels[$i]) ?>: <?= $cnt ?>">
                    <div class="bar" style="height:<?= round($cnt / $maxCount * 100) ?>%;<?= $cnt > 0 ? 'background:rgba(0,240,255,.55);' : '' ?>"
                         title="<?= $cnt ?> submissions on <?= htmlspecialchars($labels[$i]) ?>"></div>
                    <?php if ($i % 5 === 0): ?>
                    <div class="bar-lbl"><?= htmlspecialchars($labels[$i]) ?></div>
                    <?php else: ?>
                    <div class="bar-lbl" style="opacity:0;"><?= htmlspecialchars($labels[$i]) ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Weekly chart -->
        <?php if (array_sum($wCounts) > 0): ?>
        <div class="fx-card">
            <h3><i class="fas fa-chart-line"></i> Weekly Trend – Last 8 Weeks</h3>
            <div class="bar-chart">
                <?php foreach ($wCounts as $i => $cnt): ?>
                <div class="bar-wrap" title="<?= htmlspecialchars($wLabels[$i]) ?>: <?= $cnt ?>">
                    <div class="bar" style="height:<?= round($cnt / $wMax * 100) ?>%;<?= $cnt > 0 ? 'background:rgba(153,69,255,.55);' : '' ?>"></div>
                    <div class="bar-lbl"><?= htmlspecialchars($wLabels[$i]) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Device + Browser breakdown side-by-side -->
        <?php if (!empty($devices) || !empty($browsers)): ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;" class="fx-two-charts">
        <?php if (!empty($devices)): ?>
        <div class="fx-card" style="margin-bottom:0;">
            <h3><i class="fas fa-mobile-alt"></i> Device Breakdown</h3>
            <?php
            $colors = ['#00f0ff', '#9945ff', '#ffaa00', '#ff6b6b', '#00ff88'];
            $totalDev = array_sum(array_column($devices, 'cnt'));
            ?>
            <div class="donut-wrap">
                <svg width="100" height="100" viewBox="0 0 36 36" style="transform:rotate(-90deg);flex-shrink:0;">
                    <?php
                    $offset = 0;
                    $circ = 2 * M_PI * 15.91549;
                    foreach ($devices as $di => $dev):
                        $pct = $totalDev > 0 ? $dev['cnt'] / $totalDev : 0;
                        $dash = $pct * $circ;
                        $gap  = $circ - $dash;
                    ?>
                    <circle cx="18" cy="18" r="15.91549" fill="none"
                            stroke="<?= $colors[$di % count($colors)] ?>" stroke-width="3.5"
                            stroke-dasharray="<?= round($dash, 2) ?> <?= round($gap, 2) ?>"
                            stroke-dashoffset="<?= round(-$offset, 2) ?>"/>
                    <?php $offset += $dash; endforeach; ?>
                </svg>
                <div class="donut-legend">
                    <?php foreach ($devices as $di => $dev): ?>
                    <div class="donut-item">
                        <div class="donut-dot" style="background:<?= $colors[$di % count($colors)] ?>;"></div>
                        <span><?= View::e($dev['device_type']) ?></span>
                        <span style="color:var(--text-secondary);margin-left:auto;padding-left:12px;"><?= $dev['cnt'] ?> (<?= $totalDev > 0 ? round($dev['cnt']/$totalDev*100) : 0 ?>%)</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($browsers)): ?>
        <div class="fx-card" style="margin-bottom:0;">
            <h3><i class="fas fa-globe"></i> Browser Breakdown</h3>
            <?php
            $bcolors = ['#00f0ff', '#9945ff', '#ffaa00', '#ff6b6b', '#00ff88', '#00aaff'];
            $totalBr = array_sum(array_column($browsers, 'cnt'));
            ?>
            <div class="donut-wrap">
                <svg width="100" height="100" viewBox="0 0 36 36" style="transform:rotate(-90deg);flex-shrink:0;">
                    <?php
                    $offset2 = 0;
                    $circ2 = 2 * M_PI * 15.91549;
                    foreach ($browsers as $bi => $br):
                        $pct2 = $totalBr > 0 ? $br['cnt'] / $totalBr : 0;
                        $dash2 = $pct2 * $circ2;
                        $gap2  = $circ2 - $dash2;
                    ?>
                    <circle cx="18" cy="18" r="15.91549" fill="none"
                            stroke="<?= $bcolors[$bi % count($bcolors)] ?>" stroke-width="3.5"
                            stroke-dasharray="<?= round($dash2, 2) ?> <?= round($gap2, 2) ?>"
                            stroke-dashoffset="<?= round(-$offset2, 2) ?>"/>
                    <?php $offset2 += $dash2; endforeach; ?>
                </svg>
                <div class="donut-legend">
                    <?php foreach ($browsers as $bi => $br): ?>
                    <div class="donut-item">
                        <div class="donut-dot" style="background:<?= $bcolors[$bi % count($bcolors)] ?>;"></div>
                        <span><?= View::e($br['browser_name']) ?></span>
                        <span style="color:var(--text-secondary);margin-left:auto;padding-left:12px;"><?= $br['cnt'] ?> (<?= $totalBr > 0 ? round($br['cnt']/$totalBr*100) : 0 ?>%)</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Recent submissions -->
        <?php if (!empty($recentSubmissions)): ?>
        <div class="fx-card">
            <h3><i class="fas fa-clock"></i> Recent Submissions</h3>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <th style="text-align:left;padding:7px 0;font-size:.73rem;color:var(--text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">#</th>
                        <th style="text-align:left;padding:7px 0;font-size:.73rem;color:var(--text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">IP Address</th>
                        <th style="text-align:right;padding:7px 0;font-size:.73rem;color:var(--text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recentSubmissions as $i => $rs): ?>
                <tr style="border-bottom:1px solid var(--border-color);">
                    <td style="padding:8px 0;font-size:.82rem;color:var(--text-secondary);"><?= $i + 1 ?></td>
                    <td style="padding:8px 0;font-size:.82rem;">
                        <a href="/projects/formx/<?= (int)$form['id'] ?>/submissions/<?= (int)$rs['id'] ?>" style="color:var(--cyan);text-decoration:none;"><?= View::e($rs['ip_address'] ?? '–') ?></a>
                    </td>
                    <td style="padding:8px 0;font-size:.78rem;color:var(--text-secondary);text-align:right;"><?= date('M j, g:ia', strtotime($rs['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div style="margin-top:12px;">
                <a href="/projects/formx/<?= (int)$form['id'] ?>/submissions" style="font-size:.82rem;color:var(--cyan);text-decoration:none;">All submissions <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Links to related pages -->
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="/projects/formx/<?= (int)$form['id'] ?>/submissions" style="padding:9px 16px;background:var(--bg-card);border:1px solid var(--border-color);border-radius:8px;color:var(--text-secondary);text-decoration:none;font-size:.82rem;display:inline-flex;align-items:center;gap:6px;">
                <i class="fas fa-inbox"></i> View Submissions
            </a>
            <a href="/projects/formx/<?= (int)$form['id'] ?>/versions" style="padding:9px 16px;background:var(--bg-card);border:1px solid var(--border-color);border-radius:8px;color:var(--text-secondary);text-decoration:none;font-size:.82rem;display:inline-flex;align-items:center;gap:6px;">
                <i class="fas fa-history"></i> Version History
            </a>
        </div>
    </main>
</div>
<style>
@media(max-width:680px){.fx-two-charts{grid-template-columns:1fr!important;}}
</style>

<div class="fx-sidebar-overlay" id="fxOverlay"></div>
<button class="fx-sidebar-toggle" id="fxToggle"><i class="fas fa-bars"></i></button>
<script>
(function(){
    const s=document.getElementById('fxSidebar'),o=document.getElementById('fxOverlay'),t=document.getElementById('fxToggle');
    t.addEventListener('click',()=>{const open=s.classList.toggle('open');o.classList.toggle('active',open);t.innerHTML=open?'<i class="fas fa-times"></i>':'<i class="fas fa-bars"></i>';});
    o.addEventListener('click',()=>{s.classList.remove('open');o.classList.remove('active');t.innerHTML='<i class="fas fa-bars"></i>';});
})();
</script>

<?php View::endSection(); ?>
