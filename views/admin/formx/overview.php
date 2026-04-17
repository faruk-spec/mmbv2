<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<?php
// Build daily chart data (full 30 days)
$dailyMap = [];
foreach ($daily as $d) $dailyMap[$d['day']] = (int)$d['cnt'];
$dailyLabels = []; $dailyCounts = [];
for ($i = 29; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $dailyLabels[] = date('M j', strtotime($day));
    $dailyCounts[] = $dailyMap[$day] ?? 0;
}
$dailyMax = max(1, max($dailyCounts));

// Build weekly chart data (8 weeks)
$weeklyMap = [];
foreach ($weekly as $w) $weeklyMap[$w['yw']] = (int)$w['cnt'];
$weeklyLabels = []; $weeklyCounts = [];
for ($i = 7; $i >= 0; $i--) {
    $ts = strtotime("-" . ($i * 7) . " days");
    $yw = date('oW', $ts); // ISO year+week
    $lbl = 'W' . date('W', $ts);
    $weeklyLabels[] = $lbl;
    $weeklyCounts[] = $weeklyMap[$yw] ?? 0;
}
$weeklyMax = max(1, max($weeklyCounts));

// Device donut
$colors = ['#00f0ff', '#9945ff', '#ffaa00', '#ff6b6b', '#00ff88'];
$totalDev = array_sum(array_column($devices, 'cnt'));
$circ = 2 * M_PI * 15.91549;

// Trend arrow
$trendUp = $thisMonth >= $lastMonthCount;
$trendPct = $lastMonthCount > 0 ? round(abs($thisMonth - $lastMonthCount) / $lastMonthCount * 100) : ($thisMonth > 0 ? 100 : 0);
?>
<style>
    .fx-ov-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px;}
    .fx-ov-stat{background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:12px;padding:18px 20px;display:flex;align-items:center;gap:14px;transition:transform .2s;}
    .fx-ov-stat:hover{transform:translateY(-2px);}
    .fx-ov-stat-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.1rem;}
    .fx-ov-stat-val{font-size:1.7rem;font-weight:800;line-height:1;margin-bottom:3px;}
    .fx-ov-stat-lbl{font-size:.74rem;color:var(--text-secondary);font-weight:500;}
    .fx-chart-card{background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:12px;padding:20px;margin-bottom:20px;}
    .fx-chart-head{font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-secondary);margin:0 0 16px;display:flex;align-items:center;gap:8px;}
    .bar-chart{display:flex;align-items:flex-end;gap:3px;height:110px;}
    .bar-wrap{flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;min-width:0;}
    .bar{width:100%;background:rgba(0,240,255,.25);border-radius:3px 3px 0 0;transition:background .15s;}
    .bar:hover{background:rgba(0,240,255,.65);}
    .bar-lbl{font-size:.58rem;color:var(--text-secondary);writing-mode:vertical-rl;transform:rotate(180deg);white-space:nowrap;overflow:hidden;max-height:38px;}
    .donut-wrap{display:flex;align-items:center;gap:22px;flex-wrap:wrap;}
    .donut-legend{display:flex;flex-direction:column;gap:7px;}
    .donut-item{display:flex;align-items:center;gap:8px;font-size:.82rem;}
    .donut-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;}
    .fx-two-col{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;}
    .fx-top-form-row{display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--border-color);gap:10px;}
    .fx-top-form-row:last-child{border-bottom:none;}
    .fx-recent-row{display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border-color);font-size:.82rem;}
    .fx-recent-row:last-child{border-bottom:none;}
    @media(max-width:900px){.fx-ov-stats{grid-template-columns:repeat(2,1fr);}.fx-two-col{grid-template-columns:1fr;}}
    @media(max-width:480px){.fx-ov-stats{grid-template-columns:1fr 1fr;}}
</style>

<!-- Page header -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;margin-bottom:4px;">
            <i class="fas fa-wpforms" style="color:var(--cyan);margin-right:8px;"></i> FormX – Overview
        </h1>
        <p style="color:var(--text-secondary);font-size:.875rem;margin:0;">Platform-wide analytics and form management.</p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="/admin/formx" class="btn btn-secondary" style="padding:8px 16px;border-radius:8px;font-size:.85rem;text-decoration:none;background:var(--bg-secondary);border:1px solid var(--border-color);color:var(--text-primary);">
            <i class="fas fa-list"></i> All Forms
        </a>
        <a href="/admin/formx/create" class="btn btn-primary" style="padding:8px 16px;border-radius:8px;font-size:.85rem;text-decoration:none;background:linear-gradient(135deg,var(--cyan),var(--purple));color:#000;font-weight:600;">
            <i class="fas fa-plus"></i> New Form
        </a>
    </div>
</div>

<!-- Stats -->
<div class="fx-ov-stats">
    <div class="fx-ov-stat" style="border-color:rgba(0,240,255,.2);">
        <div class="fx-ov-stat-icon" style="background:rgba(0,240,255,.1);color:var(--cyan);"><i class="fas fa-wpforms"></i></div>
        <div>
            <div class="fx-ov-stat-val" style="color:var(--cyan);"><?= number_format($totalForms) ?></div>
            <div class="fx-ov-stat-lbl">Total Forms</div>
        </div>
    </div>
    <div class="fx-ov-stat" style="border-color:rgba(0,255,136,.2);">
        <div class="fx-ov-stat-icon" style="background:rgba(0,255,136,.1);color:var(--green);"><i class="fas fa-toggle-on"></i></div>
        <div>
            <div class="fx-ov-stat-val" style="color:var(--green);"><?= number_format($activeForms) ?></div>
            <div class="fx-ov-stat-lbl">Active Forms</div>
        </div>
    </div>
    <div class="fx-ov-stat" style="border-color:rgba(153,69,255,.2);">
        <div class="fx-ov-stat-icon" style="background:rgba(153,69,255,.1);color:var(--purple);"><i class="fas fa-inbox"></i></div>
        <div>
            <div class="fx-ov-stat-val" style="color:var(--purple);"><?= number_format($totalSubmissions) ?></div>
            <div class="fx-ov-stat-lbl">Total Submissions</div>
        </div>
    </div>
    <div class="fx-ov-stat" style="border-color:rgba(255,170,0,.2);">
        <div class="fx-ov-stat-icon" style="background:rgba(255,170,0,.1);color:var(--orange);"><i class="fas fa-calendar-alt"></i></div>
        <div>
            <div class="fx-ov-stat-val" style="color:var(--orange);">
                <?= number_format($thisMonth) ?>
                <?php if ($lastMonthCount > 0 || $thisMonth > 0): ?>
                <span style="font-size:.7rem;font-weight:600;color:<?= $trendUp ? 'var(--green)' : 'var(--red)' ?>;vertical-align:middle;margin-left:4px;">
                    <i class="fas fa-arrow-<?= $trendUp ? 'up' : 'down' ?>"></i><?= $trendPct ?>%
                </span>
                <?php endif; ?>
            </div>
            <div class="fx-ov-stat-lbl">This Month</div>
        </div>
    </div>
</div>

<!-- Daily bar chart -->
<div class="fx-chart-card">
    <div class="fx-chart-head"><i class="fas fa-chart-bar" style="color:var(--cyan);"></i> Submissions – Last 30 Days</div>
    <?php if (array_sum($dailyCounts) === 0): ?>
    <p style="color:var(--text-secondary);font-size:.85rem;text-align:center;padding:20px 0;">No submissions in the last 30 days.</p>
    <?php else: ?>
    <div class="bar-chart">
        <?php foreach ($dailyCounts as $i => $cnt): ?>
        <div class="bar-wrap" title="<?= $dailyLabels[$i] ?>: <?= $cnt ?>">
            <div class="bar" style="height:<?= round($cnt / $dailyMax * 100) ?>%;<?= $cnt > 0 ? 'background:rgba(0,240,255,.5);' : '' ?>"></div>
            <?php if ($i % 5 === 0): ?><div class="bar-lbl"><?= htmlspecialchars($dailyLabels[$i]) ?></div><?php else: ?><div class="bar-lbl" style="opacity:0;"><?= htmlspecialchars($dailyLabels[$i]) ?></div><?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Two columns: weekly chart + device donut -->
<div class="fx-two-col">
    <div class="fx-chart-card" style="margin-bottom:0;">
        <div class="fx-chart-head"><i class="fas fa-chart-line" style="color:var(--purple);"></i> Weekly Submissions – Last 8 Weeks</div>
        <?php if (array_sum($weeklyCounts) === 0): ?>
        <p style="color:var(--text-secondary);font-size:.85rem;text-align:center;padding:20px 0;">No data.</p>
        <?php else: ?>
        <div class="bar-chart">
            <?php foreach ($weeklyCounts as $i => $cnt): ?>
            <div class="bar-wrap" title="<?= $weeklyLabels[$i] ?>: <?= $cnt ?>">
                <div class="bar" style="height:<?= round($cnt / $weeklyMax * 100) ?>%;<?= $cnt > 0 ? 'background:rgba(153,69,255,.55);' : '' ?>"></div>
                <div class="bar-lbl"><?= htmlspecialchars($weeklyLabels[$i]) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="fx-chart-card" style="margin-bottom:0;">
        <div class="fx-chart-head"><i class="fas fa-mobile-alt" style="color:var(--orange);"></i> Device Breakdown</div>
        <?php if (empty($devices)): ?>
        <p style="color:var(--text-secondary);font-size:.85rem;text-align:center;padding:20px 0;">No data.</p>
        <?php else: ?>
        <div class="donut-wrap">
            <svg width="90" height="90" viewBox="0 0 36 36" style="transform:rotate(-90deg);flex-shrink:0;">
                <?php $offset = 0; foreach ($devices as $di => $dev):
                    $pct = $totalDev > 0 ? $dev['cnt'] / $totalDev : 0;
                    $dash = $pct * $circ; $gap = $circ - $dash;
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
                    <span style="color:var(--text-secondary);margin-left:auto;padding-left:14px;"><?= $dev['cnt'] ?> (<?= $totalDev > 0 ? round($dev['cnt']/$totalDev*100) : 0 ?>%)</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Two columns: top forms + recent submissions -->
<div class="fx-two-col" style="margin-top:20px;">
    <div class="fx-chart-card" style="margin-bottom:0;">
        <div class="fx-chart-head"><i class="fas fa-trophy" style="color:var(--cyan);"></i> Top Forms by Submissions</div>
        <?php if (empty($topForms)): ?>
        <p style="color:var(--text-secondary);font-size:.85rem;text-align:center;padding:20px 0;">No forms yet.</p>
        <?php else: ?>
        <?php $maxSubs = max(1, (int)($topForms[0]['submissions_count'] ?? 1)); ?>
        <?php foreach ($topForms as $f): ?>
        <?php
            $statusColors = ['active'=>'var(--green)','inactive'=>'var(--red)','draft'=>'var(--orange)'];
            $sc = $statusColors[$f['status']] ?? 'var(--text-secondary)';
            $barW = $maxSubs > 0 ? round($f['submissions_count'] / $maxSubs * 100) : 0;
        ?>
        <div class="fx-top-form-row">
            <div style="flex:1;min-width:0;">
                <div style="font-size:.82rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= View::e($f['title']) ?>">
                    <a href="/admin/formx/<?= $f['id'] ?>/edit" style="color:var(--text-primary);text-decoration:none;"><?= View::e($f['title']) ?></a>
                </div>
                <div style="margin-top:4px;height:4px;background:var(--border-color);border-radius:2px;overflow:hidden;">
                    <div style="width:<?= $barW ?>%;height:100%;background:var(--cyan);border-radius:2px;transition:width .3s;"></div>
                </div>
            </div>
            <div style="flex-shrink:0;text-align:right;padding-left:10px;">
                <span style="font-size:.82rem;font-weight:700;color:var(--cyan);"><?= number_format((int)$f['submissions_count']) ?></span>
                <span style="font-size:.7rem;color:var(--text-secondary);display:block;margin-top:2px;">
                    <span style="color:<?= $sc ?>;"><?= ucfirst($f['status']) ?></span>
                </span>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="fx-chart-card" style="margin-bottom:0;">
        <div class="fx-chart-head"><i class="fas fa-clock" style="color:var(--green);"></i> Recent Submissions</div>
        <?php if (empty($recentSubmissions)): ?>
        <p style="color:var(--text-secondary);font-size:.85rem;text-align:center;padding:20px 0;">No submissions yet.</p>
        <?php else: ?>
        <?php foreach ($recentSubmissions as $rs): ?>
        <div class="fx-recent-row">
            <div style="flex:1;min-width:0;">
                <div style="font-weight:600;font-size:.82rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    <a href="/admin/formx/<?= (int)$rs['form_id'] ?>/submissions" style="color:var(--cyan);text-decoration:none;"><?= View::e($rs['form_title']) ?></a>
                </div>
                <div style="font-size:.74rem;color:var(--text-secondary);margin-top:2px;">
                    <i class="fas fa-network-wired" style="margin-right:3px;opacity:.5;"></i><?= View::e($rs['ip_address'] ?? '–') ?>
                </div>
            </div>
            <div style="flex-shrink:0;text-align:right;font-size:.74rem;color:var(--text-secondary);">
                <?= date('M j, g:ia', strtotime($rs['created_at'])) ?>
            </div>
        </div>
        <?php endforeach; ?>
        <div style="margin-top:12px;font-size:.8rem;">
            <a href="/admin/formx" style="color:var(--cyan);text-decoration:none;">All forms <i class="fas fa-arrow-right"></i></a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php View::endSection(); ?>
