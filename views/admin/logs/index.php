<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<style>
/* ── Logs Dashboard ──────────────────────────────────────────── */
.ld-grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px;}
.ld-stat{background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;padding:20px 22px;display:flex;align-items:center;gap:16px;}
.ld-stat-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.ld-stat-val{font-size:1.75rem;font-weight:700;line-height:1;}
.ld-stat-lbl{font-size:12px;color:var(--text-secondary);margin-top:3px;}

.ld-section-title{font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--text-secondary);margin-bottom:12px;}

.ld-nav-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px;}
.ld-nav-card{background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;padding:20px 22px;text-decoration:none;color:inherit;display:flex;align-items:center;gap:16px;transition:border-color .15s,transform .15s;}
.ld-nav-card:hover{border-color:var(--cyan);transform:translateY(-2px);}
.ld-nav-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.ld-nav-title{font-size:15px;font-weight:600;margin-bottom:3px;}
.ld-nav-desc{font-size:12px;color:var(--text-secondary);}
.ld-nav-arrow{margin-left:auto;color:var(--text-secondary);font-size:18px;}

.ld-two-col{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;}
.ld-card{background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;padding:20px;}

.stream-item{display:flex;align-items:flex-start;gap:10px;padding:9px 0;border-bottom:1px solid var(--border-color);}
.stream-item:last-child{border-bottom:none;padding-bottom:0;}
.stream-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;margin-top:4px;}
.stream-msg{font-size:12px;flex:1;min-width:0;}
.stream-msg .main{font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.stream-meta{font-size:10px;color:var(--text-secondary);margin-top:2px;}
.stream-time{font-size:11px;color:var(--text-secondary);white-space:nowrap;flex-shrink:0;}

.mod-bar{display:flex;align-items:center;gap:10px;margin-bottom:8px;}
.mod-bar:last-child{margin-bottom:0;}
.mod-label{font-size:12px;width:80px;flex-shrink:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.mod-track{flex:1;height:6px;background:var(--border-color);border-radius:3px;overflow:hidden;}
.mod-fill{height:100%;border-radius:3px;background:var(--cyan);}
.mod-count{font-size:11px;color:var(--text-secondary);width:40px;text-align:right;flex-shrink:0;}

.status-pill{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;}

@media(max-width:900px){.ld-grid-4{grid-template-columns:repeat(2,1fr);}.ld-nav-grid{grid-template-columns:1fr;}.ld-two-col{grid-template-columns:1fr;}}
</style>

<!-- Header -->
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:22px;">
    <div>
        <h1 style="margin:0;font-size:1.5rem;">📋 Logs &amp; Audit</h1>
        <p style="color:var(--text-secondary);margin:4px 0 0;font-size:13px;">Monitor all platform activity, errors, and user actions</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="/admin/logs/activity/export?format=csv" class="btn btn-sm btn-secondary"><i class="fas fa-file-csv"></i> Export CSV</a>
        <a href="/admin/logs/activity/export?format=json" class="btn btn-sm btn-secondary"><i class="fas fa-file-code"></i> Export JSON</a>
    </div>
</div>

<!-- Stats row -->
<div class="ld-grid-4">
    <div class="ld-stat">
        <div class="ld-stat-icon" style="background:rgba(0,240,255,0.12);">
            <i class="fas fa-list-alt" style="color:var(--cyan);font-size:18px;"></i>
        </div>
        <div>
            <div class="ld-stat-val" style="color:var(--cyan);"><?= number_format($stats['total'] ?? 0) ?></div>
            <div class="ld-stat-lbl">Total Events</div>
        </div>
    </div>
    <div class="ld-stat">
        <div class="ld-stat-icon" style="background:rgba(0,200,100,0.12);">
            <i class="fas fa-calendar-day" style="color:var(--green);font-size:18px;"></i>
        </div>
        <div>
            <div class="ld-stat-val" style="color:var(--green);"><?= number_format($stats['today'] ?? 0) ?></div>
            <div class="ld-stat-lbl">Events Today</div>
        </div>
    </div>
    <div class="ld-stat">
        <div class="ld-stat-icon" style="background:rgba(231,76,60,0.12);">
            <i class="fas fa-exclamation-triangle" style="color:#e74c3c;font-size:18px;"></i>
        </div>
        <div>
            <div class="ld-stat-val" style="color:#e74c3c;"><?= number_format($stats['failures'] ?? 0) ?></div>
            <div class="ld-stat-lbl">Failures</div>
        </div>
    </div>
    <div class="ld-stat">
        <div class="ld-stat-icon" style="background:rgba(255,152,0,0.12);">
            <i class="fas fa-users" style="color:var(--orange);font-size:18px;"></i>
        </div>
        <div>
            <div class="ld-stat-val" style="color:var(--orange);"><?= number_format($stats['unique_users'] ?? 0) ?></div>
            <div class="ld-stat-lbl">Unique Users</div>
        </div>
    </div>
</div>

<!-- Navigation cards -->
<div class="ld-section-title">Browse Logs</div>
<div class="ld-nav-grid">
    <a href="/admin/logs/activity" class="ld-nav-card">
        <div class="ld-nav-icon" style="background:rgba(0,240,255,0.12);">
            <i class="fas fa-history" style="color:var(--cyan);font-size:20px;"></i>
        </div>
        <div>
            <div class="ld-nav-title">Activity Logs</div>
            <div class="ld-nav-desc">User actions, audit trail &amp; all events with charts</div>
        </div>
        <span class="ld-nav-arrow">›</span>
    </a>
    <a href="/admin/audit" class="ld-nav-card">
        <div class="ld-nav-icon" style="background:rgba(0,200,100,0.12);">
            <i class="fas fa-search" style="color:var(--green);font-size:20px;"></i>
        </div>
        <div>
            <div class="ld-nav-title">Audit Explorer</div>
            <div class="ld-nav-desc">Visual query builder — filter, group, export any audit data</div>
        </div>
        <span class="ld-nav-arrow">›</span>
    </a>
    <a href="/admin/logs/system" class="ld-nav-card">
        <div class="ld-nav-icon" style="background:rgba(255,46,196,0.12);">
            <i class="fas fa-file-alt" style="color:var(--magenta, #ff2ec4);font-size:20px;"></i>
        </div>
        <div>
            <div class="ld-nav-title">System Logs</div>
            <div class="ld-nav-desc">Application errors, warnings and debug output</div>
        </div>
        <span class="ld-nav-arrow">›</span>
    </a>
</div>

<!-- Two-column: activity stream + module breakdown -->
<div class="ld-two-col">

    <!-- Activity Stream -->
    <div class="ld-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div class="ld-section-title" style="margin:0;">Live Activity Stream</div>
            <a href="/admin/logs/activity" style="font-size:11px;color:var(--cyan);">View all →</a>
        </div>
        <?php if (empty($recentActivity)): ?>
            <p style="color:var(--text-secondary);font-size:13px;text-align:center;padding:20px 0;">No activity recorded yet.</p>
        <?php else: ?>
            <?php foreach ($recentActivity as $event):
                $sc = match($event['status'] ?? 'success') {
                    'failure' => '#e74c3c',
                    'pending' => 'var(--orange)',
                    default   => 'var(--green)',
                };
            ?>
            <div class="stream-item">
                <div class="stream-dot" style="background:<?= $sc ?>;"></div>
                <div class="stream-msg">
                    <div class="main" title="<?= View::e($event['readable_message'] ?? $event['action']) ?>">
                        <?= View::e(mb_strimwidth($event['readable_message'] ?? $event['action'], 0, 70, '…')) ?>
                    </div>
                    <div class="stream-meta">
                        <?= View::e($event['user_name'] ?? 'System') ?>
                        <?php if ($event['module']): ?>
                            &middot; <span style="color:var(--cyan);"><?= View::e($event['module']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="stream-time"><?= Helpers::formatDate($event['created_at'], 'H:i') ?></div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Right column: module breakdown + status -->
    <div style="display:flex;flex-direction:column;gap:16px;">

        <!-- Module breakdown -->
        <div class="ld-card">
            <div class="ld-section-title">Activity by Module</div>
            <?php if (empty($moduleBreakdown)): ?>
                <p style="color:var(--text-secondary);font-size:12px;">No data yet.</p>
            <?php else:
                $maxCnt = max(array_column($moduleBreakdown, 'cnt') ?: [1]);
                $modColors = ['whatsapp'=>'#25d366','qr'=>'var(--cyan)','proshare'=>'var(--magenta, #ff2ec4)','billx'=>'#f39c12','codexpro'=>'#9b59b6','convertx'=>'#3498db','auth'=>'#2ecc71','devzone'=>'#ff2ec4','core'=>'var(--text-secondary)'];
                foreach ($moduleBreakdown as $m):
                    $color = $modColors[strtolower($m['module'])] ?? 'var(--cyan)';
                    $pct = round(($m['cnt'] / $maxCnt) * 100);
            ?>
            <div class="mod-bar">
                <div class="mod-label"><?= View::e(ucfirst($m['module'])) ?></div>
                <div class="mod-track"><div class="mod-fill" style="width:<?= $pct ?>%;background:<?= $color ?>;"></div></div>
                <div class="mod-count"><?= number_format($m['cnt']) ?></div>
            </div>
            <?php endforeach; endif; ?>
        </div>

        <!-- Status pills -->
        <div class="ld-card">
            <div class="ld-section-title">Status Breakdown</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <?php
                $statusColors = ['success'=>['bg'=>'rgba(0,200,100,0.12)','text'=>'var(--green)'],'failure'=>['bg'=>'rgba(231,76,60,0.12)','text'=>'#e74c3c'],'pending'=>['bg'=>'rgba(255,152,0,0.12)','text'=>'var(--orange)']];
                foreach ($statusBreakdown as $s):
                    $sc = $statusColors[$s['status']] ?? ['bg'=>'rgba(200,200,200,0.1)','text'=>'var(--text-secondary)'];
                ?>
                <span class="status-pill" style="background:<?= $sc['bg'] ?>;color:<?= $sc['text'] ?>;">
                    <?= View::e(ucfirst($s['status'])) ?> — <?= number_format($s['cnt']) ?>
                </span>
                <?php endforeach; ?>
                <?php if (empty($statusBreakdown)): ?>
                    <span style="color:var(--text-secondary);font-size:12px;">No data yet.</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick export actions -->
        <div class="ld-card">
            <div class="ld-section-title">Quick Exports</div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <a href="/admin/logs/activity/export?format=csv" class="btn btn-sm btn-secondary" style="text-align:left;">
                    <i class="fas fa-file-csv"></i> All Activity Logs (CSV)
                </a>
                <a href="/admin/logs/activity/export?format=json" class="btn btn-sm btn-secondary" style="text-align:left;">
                    <i class="fas fa-file-code"></i> All Activity Logs (JSON)
                </a>
                <a href="/admin/logs/activity/export?format=csv&status=failure" class="btn btn-sm btn-secondary" style="text-align:left;">
                    <i class="fas fa-exclamation-triangle"></i> Failures Only (CSV)
                </a>
                <a href="/admin/logs/activity/api" class="btn btn-sm btn-secondary" target="_blank" style="text-align:left;">
                    <i class="fas fa-code"></i> JSON API Endpoint
                </a>
            </div>
        </div>
    </div>
</div>

<?php View::endSection(); ?>


