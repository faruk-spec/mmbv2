<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
    .stat-card {
        background: linear-gradient(135deg, var(--bg-card), var(--bg-secondary));
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
        position: relative;
        overflow: hidden;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 100px; height: 100px;
        background: radial-gradient(circle, var(--cyan) 0%, transparent 70%);
        opacity: 0.1;
    }
    .stat-value { font-size: 2.5rem; font-weight: 700; margin-bottom: 5px; }
    .stat-label { color: var(--text-secondary); font-size: 14px; }

    .chart-container { height: 200px; display: flex; align-items: flex-end; gap: 8px; padding: 20px 0; }
    .chart-bar {
        flex: 1;
        background: linear-gradient(180deg, var(--cyan), var(--magenta));
        border-radius: 4px 4px 0 0;
        min-height: 10px;
        position: relative;
        transition: var(--transition);
    }
    .chart-bar:hover { opacity: 0.8; }
    .chart-bar::after {
        content: attr(data-value);
        position: absolute; top: -25px; left: 50%; transform: translateX(-50%);
        font-size: 12px; color: var(--text-secondary);
    }
    .chart-bar::before {
        content: attr(data-label);
        position: absolute; bottom: -25px; left: 50%; transform: translateX(-50%);
        font-size: 11px; color: var(--text-secondary); white-space: nowrap;
    }

    .module-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 18px 12px;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        text-decoration: none;
        color: var(--text-primary);
        transition: var(--transition);
        text-align: center;
    }
    .module-card:hover {
        border-color: var(--cyan);
        box-shadow: 0 0 12px rgba(0,240,255,.15);
        transform: translateY(-2px);
        text-decoration: none;
        color: var(--text-primary);
    }
    .module-card .module-icon {
        width: 44px; height: 44px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
    }
    .module-card .module-name { font-size: 13px; font-weight: 600; line-height: 1.3; }
    .module-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px; }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>

<?php if ($canUsers && $stats): ?>
<div class="grid grid-4 mb-3">
    <div class="stat-card">
        <div class="stat-value" style="color: var(--cyan);"><?= $stats['total_users'] ?></div>
        <div class="stat-label">Total Users</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--green);"><?= $stats['active_users'] ?></div>
        <div class="stat-label">Active Users</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--magenta);"><?= $stats['new_users_today'] ?></div>
        <div class="stat-label">New Today</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--orange);"><?= $stats['total_logins_today'] ?></div>
        <div class="stat-label">Logins Today</div>
    </div>
</div>
<?php endif; ?>

<?php if ($canCodexPro || $canProShare): ?>
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title" style="font-size: 1.3rem;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:8px;">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <rect x="7" y="7" width="3" height="9"></rect>
                <rect x="14" y="7" width="3" height="5"></rect>
            </svg>
            Projects Overview
        </h3>
    </div>
    <?php
    $visibleCols = array_values(array_filter([
        $canCodexPro        ? 'codexpro' : null,
        $canProShare        ? 'proshare' : null,
        ($canFormX ?? false) ? 'formx'   : null,
    ]));
    $colCount = count($visibleCols);
    ?>
    <div class="grid grid-<?= $colCount ?>">
        <?php foreach ($visibleCols as $index => $proj): ?>

        <?php if ($proj === 'codexpro'): ?>
        <div style="padding: 20px; <?= $index < $colCount - 1 ? 'border-right: 1px solid var(--border-color);' : '' ?>">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:15px;">
                <div style="width:45px;height:45px;background:linear-gradient(135deg,var(--cyan),var(--purple));border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-code" style="font-size:20px;"></i>
                </div>
                <div>
                    <h4 style="font-size:1.1rem;margin-bottom:3px;">CodeXPro</h4>
                    <p style="font-size:12px;color:var(--text-secondary);">Live Code Editor</p>
                </div>
            </div>
            <div class="grid grid-2" style="gap:15px;">
                <div>
                    <div style="font-size:1.8rem;font-weight:700;color:var(--cyan);"><?= $projectStats['codexpro']['projects'] ?? 0 ?></div>
                    <div style="font-size:12px;color:var(--text-secondary);">Projects</div>
                </div>
                <div>
                    <div style="font-size:1.8rem;font-weight:700;color:var(--green);"><?= $projectStats['codexpro']['snippets'] ?? 0 ?></div>
                    <div style="font-size:12px;color:var(--text-secondary);">Snippets</div>
                </div>
            </div>
            <a href="/admin/projects/codexpro" class="btn btn-secondary mt-2" style="width:100%;justify-content:center;">
                <i class="fas fa-chart-line"></i> View Details
            </a>
        </div>
        <?php endif; ?>

        <?php if ($proj === 'proshare'): ?>
        <div style="padding: 20px;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:15px;">
                <div style="width:45px;height:45px;background:linear-gradient(135deg,var(--magenta),var(--orange));border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-share-alt" style="font-size:20px;"></i>
                </div>
                <div>
                    <h4 style="font-size:1.1rem;margin-bottom:3px;">ProShare</h4>
                    <p style="font-size:12px;color:var(--text-secondary);">Secure Sharing</p>
                </div>
            </div>
            <div class="grid grid-2" style="gap:15px;">
                <div>
                    <div style="font-size:1.8rem;font-weight:700;color:var(--magenta);"><?= $projectStats['proshare']['files'] ?? 0 ?></div>
                    <div style="font-size:12px;color:var(--text-secondary);">Files</div>
                </div>
                <div>
                    <div style="font-size:1.8rem;font-weight:700;color:var(--cyan);"><?= $projectStats['proshare']['texts'] ?? 0 ?></div>
                    <div style="font-size:12px;color:var(--text-secondary);">Texts</div>
                </div>
            </div>
            <a href="/admin/projects/proshare" class="btn btn-secondary mt-2" style="width:100%;justify-content:center;">
                <i class="fas fa-chart-line"></i> View Details
            </a>
        </div>
        <?php endif; ?>

        <?php if ($proj === 'formx'): ?>
        <div style="padding: 20px;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:15px;">
                <div style="width:45px;height:45px;background:linear-gradient(135deg,var(--cyan),var(--purple));border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-wpforms" style="font-size:20px;color:#06060a;"></i>
                </div>
                <div>
                    <h4 style="font-size:1.1rem;margin-bottom:3px;">FormX</h4>
                    <p style="font-size:12px;color:var(--text-secondary);">Form Builder</p>
                </div>
            </div>
            <div class="grid grid-2" style="gap:15px;">
                <div>
                    <div style="font-size:1.8rem;font-weight:700;color:var(--cyan);"><?= $projectStats['formx']['forms'] ?? 0 ?></div>
                    <div style="font-size:12px;color:var(--text-secondary);">Forms</div>
                </div>
                <div>
                    <div style="font-size:1.8rem;font-weight:700;color:var(--purple);"><?= $projectStats['formx']['submissions'] ?? 0 ?></div>
                    <div style="font-size:12px;color:var(--text-secondary);">Submissions</div>
                </div>
            </div>
            <a href="/admin/formx/overview" class="btn btn-secondary mt-2" style="width:100%;justify-content:center;">
                <i class="fas fa-chart-line"></i> View Overview
            </a>
        </div>
        <?php endif; ?>

        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($canUsers || $canProjects): ?>
<div class="grid grid-2 mb-3">
    <?php if ($canUsers && !empty($chartData)): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">User Registrations (Last 7 Days)</h3>
        </div>
        <div class="chart-container">
            <?php
            $maxCount = max(array_column($chartData, 'count')) ?: 1;
            foreach ($chartData as $data):
                $height = ($data['count'] / $maxCount) * 150;
            ?>
                <div class="chart-bar"
                     style="height: <?= max($height, 10) ?>px;"
                     data-value="<?= $data['count'] ?>"
                     data-label="<?= $data['date'] ?>"></div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($canProjects && !empty($projects)): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Projects</h3>
            <a href="/admin/projects" class="btn btn-sm btn-secondary">Manage</a>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <?php foreach ($projects as $key => $project): ?>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:10px;background:var(--bg-secondary);border-radius:8px;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:35px;height:35px;background:<?= $project['color'] ?>20;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="<?= $project['color'] ?>" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                            </svg>
                        </div>
                        <span style="font-weight:500;"><?= View::e($project['name']) ?></span>
                    </div>
                    <span class="badge <?= $project['enabled'] ? 'badge-success' : 'badge-danger' ?>">
                        <?= $project['enabled'] ? 'Active' : 'Disabled' ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($canLogs || $canUsers): ?>
<div class="grid grid-2 mb-3">
    <?php if ($canLogs): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Activity</h3>
            <a href="/admin/logs/activity" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <?php if (empty($recentActivity)): ?>
            <p style="color:var(--text-secondary);text-align:center;padding:20px;">No recent activity</p>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <?php foreach ($recentActivity as $log): ?>
                    <div style="display:flex;align-items:center;gap:12px;padding-bottom:12px;border-bottom:1px solid var(--border-color);">
                        <div style="width:32px;height:32px;background:var(--bg-secondary);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                            </svg>
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:14px;">
                                <strong><?= View::e($log['name'] ?? 'Unknown') ?></strong> — <?= View::e($log['action']) ?>
                            </div>
                            <div style="font-size:12px;color:var(--text-secondary);">
                                <?= Helpers::timeAgo($log['created_at']) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($canUsers): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Users</h3>
            <a href="/admin/users" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <?php if (empty($recentUsers)): ?>
            <p style="color:var(--text-secondary);text-align:center;padding:20px;">No users found</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsers as $u): ?>
                        <tr>
                            <td>
                                <div style="font-weight:500;"><?= View::e($u['name']) ?></div>
                                <div style="font-size:12px;color:var(--text-secondary);"><?= View::e($u['email']) ?></div>
                            </td>
                            <td>
                                    <?php foreach (array_filter(array_map('trim', explode(',', $u['role']))) as $r): ?>
                                        <span class="badge badge-info" style="margin-right:2px;"><?= View::e($r) ?></span>
                                    <?php endforeach; ?>
                                </td>
                            <td>
                                <span class="badge <?= $u['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>">
                                    <?= ucfirst($u['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php
// Quick-access module cards — shown for every module the user has access to
$moduleLinks = [];
if ($canQr)           $moduleLinks[] = ['label' => 'QR Codes',       'icon' => 'fas fa-qrcode',        'href' => '/admin/qr',                    'color' => '#00f0ff'];
if ($canConvertX)     $moduleLinks[] = ['label' => 'ConvertX',        'icon' => 'fas fa-file-export',    'href' => '/admin/projects/convertx',     'color' => '#9945ff'];
if ($canBillX)        $moduleLinks[] = ['label' => 'BillX',           'icon' => 'fas fa-file-invoice',   'href' => '/admin/projects/billx',        'color' => '#ff8800'];
if ($canWhatsApp)     $moduleLinks[] = ['label' => 'WhatsApp',        'icon' => 'fab fa-whatsapp',       'href' => '/admin/whatsapp',              'color' => '#25d366'];
if ($canFormX ?? false) $moduleLinks[] = ['label' => 'FormX',         'icon' => 'fas fa-wpforms',        'href' => '/admin/formx/overview',        'color' => '#00f0ff'];
if ($canSecurity)     $moduleLinks[] = ['label' => 'Security',        'icon' => 'fas fa-shield-alt',     'href' => '/admin/security',              'color' => '#ff4444'];
if ($canPlatformPlans)$moduleLinks[] = ['label' => 'Platform Plans',  'icon' => 'fas fa-layer-group',    'href' => '/admin/platform-plans',        'color' => '#00bbff'];
if ($canLogs && !$canUsers) $moduleLinks[] = ['label' => 'Activity Logs', 'icon' => 'fas fa-history',   'href' => '/admin/logs/activity',         'color' => '#ff8800'];
if ($canProjects && !$canCodexPro && !$canProShare)
                      $moduleLinks[] = ['label' => 'Projects',        'icon' => 'fas fa-folder',         'href' => '/admin/projects',              'color' => '#44cc44'];
?>

<?php if (!empty($moduleLinks)): ?>
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-th" style="margin-right:8px;"></i>Quick Access</h3>
    </div>
    <div class="module-grid">
        <?php foreach ($moduleLinks as $ml): ?>
        <a href="<?= $ml['href'] ?>" class="module-card">
            <div class="module-icon" style="background: <?= $ml['color'] ?>22; color: <?= $ml['color'] ?>;">
                <i class="<?= $ml['icon'] ?>"></i>
            </div>
            <span class="module-name"><?= $ml['label'] ?></span>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!$hasAnyAccess): ?>
<div class="card" style="text-align:center;padding:60px 20px;position:relative;overflow:hidden;">
    <!-- Decorative background rings -->
    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);pointer-events:none;z-index:0;">
        <div style="width:260px;height:260px;border-radius:50%;border:1px solid var(--border-color);opacity:.35;"></div>
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:180px;height:180px;border-radius:50%;border:1px solid var(--border-color);opacity:.25;"></div>
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:100px;height:100px;border-radius:50%;background:radial-gradient(circle,rgba(153,69,255,.08),transparent 70%);"></div>
    </div>
    <div style="position:relative;z-index:1;">
        <div style="width:72px;height:72px;margin:0 auto 20px;background:linear-gradient(135deg,rgba(153,69,255,.15),rgba(0,240,255,.1));border:1px solid rgba(153,69,255,.3);border-radius:50%;display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-shield-alt" style="font-size:28px;color:var(--purple,#9945ff);" aria-hidden="true"></i>
        </div>
        <h3 style="font-size:1.25rem;font-weight:700;margin-bottom:10px;">Access Pending</h3>
        <p style="color:var(--text-secondary);max-width:420px;margin:0 auto 6px;line-height:1.6;">
            You are logged into the admin panel, but no modules have been
            assigned to your account yet.
        </p>
        <p style="color:var(--text-secondary);font-size:13px;max-width:380px;margin:0 auto;">
            Contact your Super Admin to have the appropriate module permissions
            granted to your account.
        </p>
        <div style="margin-top:24px;display:inline-flex;align-items:center;gap:8px;padding:8px 16px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;font-size:13px;color:var(--text-secondary);">
            <i class="fas fa-envelope" style="color:var(--cyan);"></i>
            Waiting for permission assignment
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── Real-time Live Stats Panel ─────────────────────────────────────────── -->
<div class="card mb-3" id="liveStatsCard">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
        <h3 class="card-title" style="font-size:1.1rem;display:flex;align-items:center;gap:8px;">
            <span style="width:8px;height:8px;border-radius:50%;background:#00ff88;display:inline-block;animation:livePulse 2s ease-in-out infinite;flex-shrink:0;"></span>
            Live Activity
        </h3>
        <span id="liveStatsTs" style="font-size:.75rem;color:var(--text-secondary);">updating…</span>
    </div>
    <div style="padding:16px 20px;">
        <div class="grid grid-4" style="gap:12px;margin-bottom:16px;">
            <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:10px;padding:14px 16px;text-align:center;">
                <div id="lsOnline" style="font-size:1.8rem;font-weight:700;color:var(--cyan,#00f0ff);">—</div>
                <div style="font-size:.75rem;color:var(--text-secondary);margin-top:2px;">Online Now</div>
            </div>
            <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:10px;padding:14px 16px;text-align:center;">
                <div id="lsLogins" style="font-size:1.8rem;font-weight:700;color:var(--green,#00ff88);">—</div>
                <div style="font-size:.75rem;color:var(--text-secondary);margin-top:2px;">Logins Today</div>
            </div>
            <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:10px;padding:14px 16px;text-align:center;">
                <div id="lsRegs" style="font-size:1.8rem;font-weight:700;color:var(--magenta,#ff2ec4);">—</div>
                <div style="font-size:.75rem;color:var(--text-secondary);margin-top:2px;">Registrations</div>
            </div>
            <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:10px;padding:14px 16px;text-align:center;">
                <div id="lsNotifs" style="font-size:1.8rem;font-weight:700;color:var(--orange,#f97316);" title="Unread admin notifications">—</div>
                <div style="font-size:.75rem;color:var(--text-secondary);margin-top:2px;">Unread Notifs</div>
            </div>
        </div>
        <div>
            <div style="font-size:.8rem;font-weight:600;color:var(--text-secondary);margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em;">Recent Activity</div>
            <div id="lsActivity" style="font-size:.825rem;display:flex;flex-direction:column;gap:4px;">
                <div style="color:var(--text-secondary);">Loading…</div>
            </div>
        </div>
    </div>
</div>

<style>@keyframes livePulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(1.3)}}</style>
<script>
(function () {
    var online  = document.getElementById('lsOnline');
    var logins  = document.getElementById('lsLogins');
    var regs    = document.getElementById('lsRegs');
    var notifs  = document.getElementById('lsNotifs');
    var activity= document.getElementById('lsActivity');
    var tsEl    = document.getElementById('liveStatsTs');

    function timeAgo(d) {
        var s = Math.floor((Date.now() - new Date(d).getTime()) / 1000);
        if (s < 60) return 'just now';
        if (s < 3600) return Math.floor(s / 60) + 'm ago';
        if (s < 86400) return Math.floor(s / 3600) + 'h ago';
        return Math.floor(s / 86400) + 'd ago';
    }

    function actionLabel(action) {
        var labels = {
            login: 'Logged in', logout: 'Logged out', register: 'Registered',
            '2fa_enabled': 'Enabled 2FA', '2fa_disabled': 'Disabled 2FA',
            password_changed: 'Changed password', profile_updated: 'Updated profile',
        };
        return labels[action] || action.replace(/_/g, ' ');
    }

    function fetchStats() {
        if (document.hidden) return;
        fetch('/admin/api/live-stats', {credentials: 'same-origin'})
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) return;
                if (online)  online.textContent  = d.online_now          ?? '—';
                if (logins)  logins.textContent  = d.logins_today        ?? '—';
                if (regs)    regs.textContent    = d.registrations_today ?? '—';

                // Unread notification count from admin badge
                var badge = document.getElementById('adminNotifBadge');
                if (notifs) notifs.textContent = badge ? (parseInt(badge.textContent, 10) || 0) : '—';

                if (activity && d.recent_activity && d.recent_activity.length) {
                    activity.innerHTML = d.recent_activity.map(function (a) {
                        return '<div style="display:flex;align-items:center;gap:8px;padding:5px 0;border-bottom:1px solid rgba(255,255,255,.05);">' +
                            '<span style="width:6px;height:6px;border-radius:50%;background:var(--cyan,#00f0ff);flex-shrink:0;"></span>' +
                            '<span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' +
                                '<strong style="color:var(--text-primary);">' + (a.user || 'Unknown') + '</strong> — ' + actionLabel(a.action) +
                            '</span>' +
                            '<span style="color:var(--text-secondary);font-size:.75rem;white-space:nowrap;">' + timeAgo(a.time) + '</span>' +
                        '</div>';
                    }).join('');
                } else if (activity) {
                    activity.innerHTML = '<div style="color:var(--text-secondary);">No recent activity.</div>';
                }

                if (tsEl) {
                    var now = new Date();
                    tsEl.textContent = 'updated ' + now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0') + ':' + now.getSeconds().toString().padStart(2,'0');
                }
            })
            .catch(function () {});
    }

    fetchStats();
    setInterval(fetchStats, 15000);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) fetchStats(); });
})();
</script>

<?php View::endSection(); ?>

