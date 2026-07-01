<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php
$activeTab = $activeTab ?? 'overview';
$phpVersion    = PHP_VERSION;
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
$diskTotal  = disk_total_space('/');
$diskFree   = disk_free_space('/');
$diskUsed   = $diskTotal - $diskFree;
$diskPct    = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 1) : 0;
$memUsage   = memory_get_usage(true);
$memPeak    = memory_get_peak_usage(true);
function bsz(int $bytes): string {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576)    return round($bytes / 1048576,    2) . ' MB';
    if ($bytes >= 1024)       return round($bytes / 1024,       2) . ' KB';
    return $bytes . ' B';
}
$gitBranch  = trim(shell_exec('git rev-parse --abbrev-ref HEAD 2>/dev/null') ?? 'unknown');
$gitCommit  = trim(shell_exec('git log -1 --format="%h %s" 2>/dev/null') ?? '—');
$gitStatus  = trim(shell_exec('git status --short 2>/dev/null') ?? '');
$gitLog     = array_filter(explode("\n", trim(shell_exec('git log --oneline -10 2>/dev/null') ?? '')));
$gitRemotes = array_filter(explode("\n", trim(shell_exec('git remote -v 2>/dev/null') ?? '')));
$gitBranches= array_filter(explode("\n", trim(shell_exec('git branch -a 2>/dev/null') ?? '')));
$gitTags    = array_filter(explode("\n", trim(shell_exec('git tag --sort=-creatordate 2>/dev/null') ?? '')));
?>

<?php View::section('styles'); ?>
<style>
/* ── Deployment Backstage Dashboard ───────────────────────────────────────── */
.dep-wrap { padding: 24px; }

/* Page header */
.dep-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 12px;
}
.dep-header-left { display: flex; align-items: center; gap: 14px; }
.dep-header-icon {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, var(--cyan), var(--magenta));
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; color: #fff;
    box-shadow: 0 4px 20px rgba(0,240,255,.25);
    flex-shrink: 0;
}
.dep-header-text h1 { margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-primary); }
.dep-header-text p  { margin: 2px 0 0; font-size: 0.85rem; color: var(--text-secondary); }
.dep-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;
}
.dep-badge.live { background: rgba(0,220,130,.15); color: #00dc82; border: 1px solid rgba(0,220,130,.3); }
.dep-badge.branch { background: rgba(0,240,255,.1); color: var(--cyan); border: 1px solid rgba(0,240,255,.25); }

/* Tab nav */
.dep-tabs {
    display: flex;
    gap: 4px;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 28px;
    overflow-x: auto;
    padding-bottom: 1px;
}
.dep-tabs::-webkit-scrollbar { height: 4px; }
.dep-tabs::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 2px; }
.dep-tab {
    display: flex; align-items: center; gap: 7px;
    padding: 9px 16px;
    border-bottom: 2px solid transparent;
    font-size: 13px; font-weight: 500; color: var(--text-secondary);
    cursor: pointer; white-space: nowrap;
    text-decoration: none;
    transition: color .2s, border-color .2s;
    border-radius: 4px 4px 0 0;
}
.dep-tab:hover { color: var(--text-primary); text-decoration: none; }
.dep-tab.active { color: var(--cyan); border-bottom-color: var(--cyan); }

/* Panel */
.dep-panel { display: none; }
.dep-panel.active { display: block; }

/* Stat cards */
.dep-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
.dep-stat {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    position: relative; overflow: hidden;
}
.dep-stat::after {
    content: '';
    position: absolute; top: -20px; right: -20px;
    width: 80px; height: 80px;
    border-radius: 50%;
    opacity: .08;
}
.dep-stat.cyan::after  { background: var(--cyan); }
.dep-stat.green::after { background: var(--green, #00dc82); }
.dep-stat.mag::after   { background: var(--magenta); }
.dep-stat.orange::after{ background: var(--orange, #f59e0b); }
.dep-stat-icon { font-size: 22px; margin-bottom: 12px; }
.dep-stat.cyan  .dep-stat-icon { color: var(--cyan); }
.dep-stat.green .dep-stat-icon { color: #00dc82; }
.dep-stat.mag   .dep-stat-icon { color: var(--magenta); }
.dep-stat.orange .dep-stat-icon{ color: #f59e0b; }
.dep-stat-val  { font-size: 1.4rem; font-weight: 700; color: var(--text-primary); word-break: break-all; }
.dep-stat-label{ font-size: 12px; color: var(--text-secondary); margin-top: 4px; }

/* Card */
.dep-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    margin-bottom: 20px;
    overflow: hidden;
}
.dep-card-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color);
    font-size: 14px; font-weight: 600; color: var(--text-primary);
}
.dep-card-head i { color: var(--cyan); margin-right: 8px; }
.dep-card-body { padding: 20px; }

/* Code / monospace block */
.dep-code {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 14px 16px;
    font-family: 'Courier New', monospace;
    font-size: 12.5px;
    color: var(--text-secondary);
    overflow-x: auto;
    white-space: pre;
    line-height: 1.6;
    max-height: 300px;
    overflow-y: auto;
}
.dep-code .c-green  { color: #4ade80; }
.dep-code .c-cyan   { color: var(--cyan); }
.dep-code .c-orange { color: #f59e0b; }
.dep-code .c-red    { color: #f87171; }
.dep-code .c-dim    { color: #555; }

/* Info table */
.dep-info-table { width: 100%; border-collapse: collapse; }
.dep-info-table tr { border-bottom: 1px solid var(--border-color); }
.dep-info-table tr:last-child { border-bottom: none; }
.dep-info-table td { padding: 10px 4px; font-size: 13px; vertical-align: middle; }
.dep-info-table td:first-child { color: var(--text-secondary); width: 40%; white-space: nowrap; }
.dep-info-table td:last-child  { color: var(--text-primary); font-weight: 500; }

/* Progress bar */
.dep-progress { background: var(--bg-secondary); border-radius: 20px; height: 8px; overflow: hidden; }
.dep-progress-fill { height: 100%; border-radius: 20px; transition: width .5s; }

/* Log line */
.dep-log-line { padding: 5px 0; border-bottom: 1px solid rgba(255,255,255,.04); font-size: 12.5px; }
.dep-log-line:last-child { border-bottom: none; }
.dep-log-hash { color: var(--cyan); font-family: monospace; }
.dep-log-msg  { color: var(--text-primary); }
.dep-log-dim  { color: var(--text-secondary); }

/* Branch pill */
.dep-branch-pill {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 5px 12px;
    font-size: 12px; color: var(--text-secondary);
    margin: 4px;
}
.dep-branch-pill.current { border-color: var(--cyan); color: var(--cyan); background: rgba(0,240,255,.06); }
.dep-branch-pill i { font-size: 10px; }

/* Tag pill */
.dep-tag-pill {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(245,158,11,.08);
    border: 1px solid rgba(245,158,11,.2);
    border-radius: 20px;
    padding: 5px 12px;
    font-size: 12px; color: #f59e0b;
    margin: 4px;
}
.dep-tag-pill i { font-size: 10px; }

/* Grid 2-col */
.dep-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
@media (max-width: 768px) { .dep-grid-2 { grid-template-columns: 1fr; } }

/* Deploy button */
.dep-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600;
    border: none; cursor: pointer; transition: all .2s; text-decoration: none;
}
.dep-btn-primary {
    background: linear-gradient(135deg, var(--cyan), var(--magenta));
    color: #fff;
    box-shadow: 0 4px 16px rgba(0,240,255,.2);
}
.dep-btn-primary:hover { opacity: .9; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(0,240,255,.3); }
.dep-btn-secondary {
    background: var(--bg-secondary); color: var(--text-primary);
    border: 1px solid var(--border-color);
}
.dep-btn-secondary:hover { border-color: var(--cyan); color: var(--cyan); }
.dep-btn-danger {
    background: rgba(248,113,113,.12); color: #f87171;
    border: 1px solid rgba(248,113,113,.2);
}
.dep-btn-danger:hover { background: rgba(248,113,113,.2); }

/* Deploy step */
.dep-step {
    display: flex; align-items: flex-start; gap: 14px;
    padding: 14px 0; border-bottom: 1px solid var(--border-color);
}
.dep-step:last-child { border-bottom: none; }
.dep-step-num {
    width: 28px; height: 28px; border-radius: 50%;
    background: var(--bg-secondary); border: 1px solid var(--border-color);
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; color: var(--text-secondary);
    flex-shrink: 0; margin-top: 2px;
}
.dep-step-body { flex: 1; }
.dep-step-title { font-size: 13px; font-weight: 600; color: var(--text-primary); margin-bottom: 2px; }
.dep-step-desc  { font-size: 12px; color: var(--text-secondary); }

/* Timeline */
.dep-timeline { list-style: none; padding: 0; margin: 0; }
.dep-timeline li {
    display: flex; gap: 14px; padding: 12px 0;
    border-bottom: 1px solid rgba(255,255,255,.04);
    font-size: 13px;
}
.dep-timeline li:last-child { border-bottom: none; }
.dep-tl-dot {
    width: 10px; height: 10px; border-radius: 50%;
    margin-top: 4px; flex-shrink: 0;
}
.dep-tl-dot.ok    { background: #00dc82; box-shadow: 0 0 8px rgba(0,220,130,.4); }
.dep-tl-dot.warn  { background: #f59e0b; }
.dep-tl-dot.info  { background: var(--cyan); }

/* Empty state */
.dep-empty { text-align: center; padding: 40px 20px; color: var(--text-secondary); font-size: 13px; }
.dep-empty i { font-size: 2.5rem; margin-bottom: 12px; opacity: .3; display: block; }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div class="dep-wrap">

    <!-- Header -->
    <div class="dep-header">
        <div class="dep-header-left">
            <div class="dep-header-icon"><i class="fas fa-rocket"></i></div>
            <div class="dep-header-text">
                <h1><?= htmlspecialchars($title) ?></h1>
                <p>Manage deployments, source control, and server environment</p>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <?php if ($gitBranch && $gitBranch !== 'unknown'): ?>
            <span class="dep-badge branch"><i class="fas fa-code-branch"></i><?= htmlspecialchars($gitBranch) ?></span>
            <?php endif; ?>
            <span class="dep-badge live"><i class="fas fa-circle" style="font-size:8px;"></i>Live</span>
        </div>
    </div>

    <!-- Tab navigation -->
    <nav class="dep-tabs">
        <a href="/admin/deployment"           class="dep-tab <?= $activeTab === 'overview'  ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Overview</a>
        <a href="/admin/deployment/github"    class="dep-tab <?= $activeTab === 'github'    ? 'active' : '' ?>"><i class="fab fa-github"></i> GitHub</a>
        <a href="/admin/deployment/branches"  class="dep-tab <?= $activeTab === 'branches'  ? 'active' : '' ?>"><i class="fas fa-code-branch"></i> Branches</a>
        <a href="/admin/deployment/deploy"    class="dep-tab <?= $activeTab === 'deploy'    ? 'active' : '' ?>"><i class="fas fa-rocket"></i> Deploy</a>
        <a href="/admin/deployment/history"   class="dep-tab <?= $activeTab === 'history'   ? 'active' : '' ?>"><i class="fas fa-history"></i> History</a>
        <a href="/admin/deployment/versions"  class="dep-tab <?= $activeTab === 'versions'  ? 'active' : '' ?>"><i class="fas fa-tag"></i> Versions</a>
        <a href="/admin/deployment/logs"      class="dep-tab <?= $activeTab === 'logs'      ? 'active' : '' ?>"><i class="fas fa-terminal"></i> Logs</a>
        <a href="/admin/deployment/server"    class="dep-tab <?= $activeTab === 'server'    ? 'active' : '' ?>"><i class="fas fa-server"></i> Server</a>
        <a href="/admin/deployment/settings"  class="dep-tab <?= $activeTab === 'settings'  ? 'active' : '' ?>"><i class="fas fa-cog"></i> Settings</a>
    </nav>

    <!-- ═══════════════════════════════ OVERVIEW ═══════════════════════════════ -->
    <div class="dep-panel <?= $activeTab === 'overview' ? 'active' : '' ?>">
        <div class="dep-stats">
            <div class="dep-stat cyan">
                <div class="dep-stat-icon"><i class="fas fa-code-branch"></i></div>
                <div class="dep-stat-val"><?= htmlspecialchars($gitBranch ?: '—') ?></div>
                <div class="dep-stat-label">Active Branch</div>
            </div>
            <div class="dep-stat green">
                <div class="dep-stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="dep-stat-val"><?= count($gitLog) ?: 0 ?></div>
                <div class="dep-stat-label">Recent Commits</div>
            </div>
            <div class="dep-stat mag">
                <div class="dep-stat-icon"><i class="fas fa-hdd"></i></div>
                <div class="dep-stat-val"><?= $diskPct ?>%</div>
                <div class="dep-stat-label">Disk Used</div>
            </div>
            <div class="dep-stat orange">
                <div class="dep-stat-icon"><i class="fas fa-microchip"></i></div>
                <div class="dep-stat-val"><?= bsz($memUsage) ?></div>
                <div class="dep-stat-label">Memory Usage</div>
            </div>
        </div>

        <div class="dep-grid-2">
            <!-- Latest commit -->
            <div class="dep-card">
                <div class="dep-card-head"><span><i class="fas fa-code-commit"></i>Latest Commit</span></div>
                <div class="dep-card-body">
                    <?php if ($gitCommit && $gitCommit !== '—'): ?>
                    <div class="dep-code"><?= htmlspecialchars($gitCommit) ?></div>
                    <?php else: ?>
                    <div class="dep-empty"><i class="fas fa-code-commit"></i>No git data available</div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Working tree -->
            <div class="dep-card">
                <div class="dep-card-head"><span><i class="fas fa-folder-open"></i>Working Tree</span></div>
                <div class="dep-card-body">
                    <?php if ($gitStatus): ?>
                    <div class="dep-code"><?= htmlspecialchars($gitStatus) ?></div>
                    <?php else: ?>
                    <div style="display:flex;align-items:center;gap:8px;color:#00dc82;font-size:13px;">
                        <i class="fas fa-check-circle"></i> Working tree is clean
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Disk usage -->
        <div class="dep-card">
            <div class="dep-card-head"><span><i class="fas fa-hdd"></i>Disk Usage</span></div>
            <div class="dep-card-body">
                <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--text-secondary);margin-bottom:8px;">
                    <span>Used: <?= bsz($diskUsed) ?></span>
                    <span>Free: <?= bsz($diskFree) ?></span>
                    <span>Total: <?= bsz($diskTotal) ?></span>
                </div>
                <div class="dep-progress">
                    <div class="dep-progress-fill" style="width:<?= $diskPct ?>%;background:linear-gradient(90deg,var(--cyan),var(--magenta));"></div>
                </div>
                <div style="text-align:right;font-size:12px;color:var(--text-secondary);margin-top:6px;"><?= $diskPct ?>% used</div>
            </div>
        </div>

        <!-- Recent commits -->
        <div class="dep-card">
            <div class="dep-card-head"><span><i class="fas fa-history"></i>Recent Commits</span></div>
            <div class="dep-card-body" style="padding:12px 20px;">
                <?php if ($gitLog): foreach ($gitLog as $line):
                    $parts = explode(' ', trim($line), 2);
                    $hash = $parts[0] ?? '';
                    $msg  = $parts[1] ?? '';
                ?>
                <div class="dep-log-line">
                    <span class="dep-log-hash"><?= htmlspecialchars($hash) ?></span>
                    <span class="dep-log-dim"> &mdash; </span>
                    <span class="dep-log-msg"><?= htmlspecialchars($msg) ?></span>
                </div>
                <?php endforeach; else: ?>
                <div class="dep-empty"><i class="fas fa-history"></i>No commits found</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════ GITHUB ═════════════════════════════════ -->
    <div class="dep-panel <?= $activeTab === 'github' ? 'active' : '' ?>">
        <div class="dep-card">
            <div class="dep-card-head"><span><i class="fab fa-github"></i>Remote Repositories</span></div>
            <div class="dep-card-body">
                <?php if ($gitRemotes): ?>
                <div class="dep-code"><?php foreach ($gitRemotes as $r): ?><?= htmlspecialchars(trim($r)) . "\n" ?><?php endforeach; ?></div>
                <?php else: ?>
                <div class="dep-empty"><i class="fab fa-github"></i>No remotes configured</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="dep-card">
            <div class="dep-card-head"><span><i class="fas fa-info-circle"></i>Repository Info</span></div>
            <div class="dep-card-body">
                <table class="dep-info-table">
                    <tr><td>Current Branch</td><td><?= htmlspecialchars($gitBranch ?: '—') ?></td></tr>
                    <tr><td>Last Commit</td><td><?= htmlspecialchars($gitCommit ?: '—') ?></td></tr>
                    <tr><td>Working Tree</td><td><?= $gitStatus ? '<span style="color:#f87171;">Dirty ('.count(explode("\n",$gitStatus)).' change(s))</span>' : '<span style="color:#00dc82;">Clean</span>' ?></td></tr>
                    <tr><td>Total Tags</td><td><?= count(array_filter($gitTags)) ?></td></tr>
                    <tr><td>Total Branches</td><td><?= count(array_filter($gitBranches)) ?></td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════ BRANCHES ══════════════════════════════ -->
    <div class="dep-panel <?= $activeTab === 'branches' ? 'active' : '' ?>">
        <div class="dep-card">
            <div class="dep-card-head"><span><i class="fas fa-code-branch"></i>All Branches</span></div>
            <div class="dep-card-body">
                <?php if ($gitBranches): ?>
                <div style="flex-wrap:wrap;display:flex;">
                    <?php foreach ($gitBranches as $b):
                        $b = trim($b);
                        if ($b === '') continue;
                        $current = str_starts_with($b, '*');
                        $b = ltrim($b, '* ');
                    ?>
                    <span class="dep-branch-pill <?= $current ? 'current' : '' ?>">
                        <i class="fas fa-code-branch"></i><?= htmlspecialchars($b) ?>
                        <?php if ($current): ?><i class="fas fa-check" style="color:#00dc82;margin-left:2px;"></i><?php endif; ?>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="dep-empty"><i class="fas fa-code-branch"></i>No branches found</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="dep-card">
            <div class="dep-card-head"><span><i class="fas fa-terminal"></i>Branch Details</span></div>
            <div class="dep-card-body">
                <div class="dep-code"><?= htmlspecialchars(shell_exec('git branch -avv 2>/dev/null') ?: 'No branch data available') ?></div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════ DEPLOY ════════════════════════════════ -->
    <div class="dep-panel <?= $activeTab === 'deploy' ? 'active' : '' ?>">
        <div class="dep-card">
            <div class="dep-card-head"><span><i class="fas fa-rocket"></i>Deployment Checklist</span></div>
            <div class="dep-card-body">
                <?php
                $checks = [
                    ['Working tree clean',      !$gitStatus,           $gitStatus ? 'Uncommitted changes detected' : 'No pending changes'],
                    ['PHP version compatible',  version_compare(PHP_VERSION, '8.0', '>='), 'PHP ' . PHP_VERSION],
                    ['Disk space available',    $diskPct < 90,         bsz($diskFree) . ' free (' . $diskPct . '% used)'],
                    ['Memory within limits',    $memUsage < 100*1024*1024, bsz($memUsage) . ' / ' . bsz($memPeak) . ' peak'],
                    ['Composer autoload',       file_exists(BASE_PATH . '/vendor/autoload.php'), 'vendor/autoload.php'],
                    ['Environment config',      file_exists(BASE_PATH . '/config/config.php'),  'config/config.php'],
                ];
                ?>
                <?php foreach ($checks as [$label, $ok, $detail]): ?>
                <div class="dep-step">
                    <div class="dep-step-num" style="<?= $ok ? 'border-color:#00dc82;color:#00dc82;background:rgba(0,220,130,.1);' : 'border-color:#f87171;color:#f87171;background:rgba(248,113,113,.1);' ?>">
                        <i class="fas <?= $ok ? 'fa-check' : 'fa-times' ?>"></i>
                    </div>
                    <div class="dep-step-body">
                        <div class="dep-step-title"><?= htmlspecialchars($label) ?></div>
                        <div class="dep-step-desc"><?= htmlspecialchars($detail) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="dep-card">
            <div class="dep-card-head"><span><i class="fas fa-play-circle"></i>Quick Actions</span></div>
            <div class="dep-card-body" style="display:flex;flex-wrap:wrap;gap:10px;">
                <button class="dep-btn dep-btn-primary" onclick="depRunCmd('git pull')"><i class="fas fa-cloud-download-alt"></i>Pull Latest</button>
                <button class="dep-btn dep-btn-secondary" onclick="depRunCmd('composer install --no-dev --optimize-autoloader')"><i class="fas fa-box"></i>Install Dependencies</button>
                <button class="dep-btn dep-btn-secondary" onclick="depRunCmd('git status')"><i class="fas fa-info-circle"></i>Git Status</button>
                <button class="dep-btn dep-btn-danger"    onclick="if(confirm('Clear cache?')) depRunCmd('php -r \"echo shell_exec(\\\"find storage/cache -type f -name *.php -delete\\\");\"')"><i class="fas fa-trash-alt"></i>Clear Cache</button>
            </div>
        </div>

        <div class="dep-card" id="depCmdOutput" style="display:none;">
            <div class="dep-card-head"><span><i class="fas fa-terminal"></i>Command Output</span></div>
            <div class="dep-card-body"><div class="dep-code" id="depCmdContent"></div></div>
        </div>
    </div>

    <!-- ═══════════════════════════════ HISTORY ═══════════════════════════════ -->
    <div class="dep-panel <?= $activeTab === 'history' ? 'active' : '' ?>">
        <div class="dep-card">
            <div class="dep-card-head"><span><i class="fas fa-history"></i>Commit History (last 25)</span></div>
            <div class="dep-card-body" style="padding:12px 20px;">
                <?php
                $fullLog = array_filter(explode("\n", trim(shell_exec('git log --oneline -25 2>/dev/null') ?? '')));
                ?>
                <?php if ($fullLog): foreach ($fullLog as $line):
                    $parts = explode(' ', trim($line), 2);
                    $hash = $parts[0] ?? '';
                    $msg  = $parts[1] ?? '';
                ?>
                <div class="dep-log-line" style="display:flex;gap:8px;align-items:baseline;">
                    <span class="dep-log-hash" style="min-width:58px;flex-shrink:0;"><?= htmlspecialchars($hash) ?></span>
                    <span class="dep-log-msg"><?= htmlspecialchars($msg) ?></span>
                </div>
                <?php endforeach; else: ?>
                <div class="dep-empty"><i class="fas fa-history"></i>No commit history available</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════ VERSIONS ══════════════════════════════ -->
    <div class="dep-panel <?= $activeTab === 'versions' ? 'active' : '' ?>">
        <div class="dep-card">
            <div class="dep-card-head"><span><i class="fas fa-tag"></i>Git Tags / Releases</span></div>
            <div class="dep-card-body">
                <?php if ($gitTags): ?>
                <div style="flex-wrap:wrap;display:flex;">
                    <?php foreach (array_slice(array_filter($gitTags), 0, 40) as $tag): ?>
                    <span class="dep-tag-pill"><i class="fas fa-tag"></i><?= htmlspecialchars(trim($tag)) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="dep-empty"><i class="fas fa-tag"></i>No tags found</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="dep-card">
            <div class="dep-card-head"><span><i class="fas fa-layer-group"></i>Version Info</span></div>
            <div class="dep-card-body">
                <table class="dep-info-table">
                    <tr><td>Latest Tag</td><td><?= htmlspecialchars(trim(reset($gitTags) ?: '—')) ?></td></tr>
                    <tr><td>Total Tags</td><td><?= count(array_filter($gitTags)) ?></td></tr>
                    <tr><td>App Version</td><td><?= defined('APP_VERSION') ? htmlspecialchars(APP_VERSION) : '—' ?></td></tr>
                    <tr><td>PHP Version</td><td><?= htmlspecialchars($phpVersion) ?></td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════ LOGS ══════════════════════════════════ -->
    <div class="dep-panel <?= $activeTab === 'logs' ? 'active' : '' ?>">
        <div class="dep-card">
            <div class="dep-card-head"><span><i class="fas fa-terminal"></i>Git Reflog (last 20)</span></div>
            <div class="dep-card-body">
                <div class="dep-code"><?= htmlspecialchars(shell_exec('git reflog --oneline -20 2>/dev/null') ?: 'No reflog data') ?></div>
            </div>
        </div>

        <?php
        $logDir = BASE_PATH . '/storage/logs';
        $logFiles = glob($logDir . '/*.log') ?: [];
        usort($logFiles, fn($a, $b) => filemtime($b) - filemtime($a));
        ?>
        <?php if ($logFiles): ?>
        <div class="dep-card">
            <div class="dep-card-head"><span><i class="fas fa-file-alt"></i>Application Logs</span></div>
            <div class="dep-card-body" style="padding:0;">
                <?php foreach (array_slice($logFiles, 0, 3) as $lf): ?>
                <div style="padding:12px 20px;border-bottom:1px solid var(--border-color);">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <span style="font-size:13px;font-weight:600;color:var(--text-primary);">
                            <i class="fas fa-file-code" style="color:var(--cyan);margin-right:6px;"></i><?= htmlspecialchars(basename($lf)) ?>
                        </span>
                        <span style="font-size:11px;color:var(--text-secondary);"><?= bsz(filesize($lf)) ?> &bull; <?= date('Y-m-d H:i', filemtime($lf)) ?></span>
                    </div>
                    <div class="dep-code" style="max-height:160px;"><?= htmlspecialchars(implode("\n", array_slice(array_reverse(file($lf, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)), 0, 20))) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="dep-card">
            <div class="dep-card-head"><span><i class="fas fa-file-alt"></i>Application Logs</span></div>
            <div class="dep-card-body"><div class="dep-empty"><i class="fas fa-file-alt"></i>No log files found in storage/logs</div></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ═══════════════════════════════ SERVER ════════════════════════════════ -->
    <div class="dep-panel <?= $activeTab === 'server' ? 'active' : '' ?>">
        <div class="dep-stats">
            <div class="dep-stat cyan">
                <div class="dep-stat-icon"><i class="fas fa-microchip"></i></div>
                <div class="dep-stat-val"><?= htmlspecialchars($phpVersion) ?></div>
                <div class="dep-stat-label">PHP Version</div>
            </div>
            <div class="dep-stat green">
                <div class="dep-stat-icon"><i class="fas fa-memory"></i></div>
                <div class="dep-stat-val"><?= bsz($memUsage) ?></div>
                <div class="dep-stat-label">Memory Usage</div>
            </div>
            <div class="dep-stat mag">
                <div class="dep-stat-icon"><i class="fas fa-hdd"></i></div>
                <div class="dep-stat-val"><?= $diskPct ?>%</div>
                <div class="dep-stat-label">Disk Used</div>
            </div>
            <div class="dep-stat orange">
                <div class="dep-stat-icon"><i class="fas fa-server"></i></div>
                <div class="dep-stat-val" style="font-size:1rem;"><?= htmlspecialchars(substr($serverSoftware, 0, 20)) ?></div>
                <div class="dep-stat-label">Web Server</div>
            </div>
        </div>

        <div class="dep-grid-2">
            <div class="dep-card">
                <div class="dep-card-head"><span><i class="fas fa-server"></i>Server Environment</span></div>
                <div class="dep-card-body">
                    <table class="dep-info-table">
                        <tr><td>PHP Version</td><td><?= htmlspecialchars($phpVersion) ?></td></tr>
                        <tr><td>Server Software</td><td><?= htmlspecialchars($serverSoftware) ?></td></tr>
                        <tr><td>OS</td><td><?= htmlspecialchars(PHP_OS_FAMILY) ?></td></tr>
                        <tr><td>Memory Limit</td><td><?= ini_get('memory_limit') ?></td></tr>
                        <tr><td>Max Execution</td><td><?= ini_get('max_execution_time') ?>s</td></tr>
                        <tr><td>Upload Max</td><td><?= ini_get('upload_max_filesize') ?></td></tr>
                        <tr><td>Post Max</td><td><?= ini_get('post_max_size') ?></td></tr>
                        <tr><td>Timezone</td><td><?= htmlspecialchars(date_default_timezone_get()) ?></td></tr>
                    </table>
                </div>
            </div>
            <div class="dep-card">
                <div class="dep-card-head"><span><i class="fas fa-puzzle-piece"></i>PHP Extensions</span></div>
                <div class="dep-card-body">
                    <?php
                    $wantedExt = ['pdo','pdo_mysql','mbstring','json','openssl','curl','gd','zip','intl','fileinfo','opcache'];
                    foreach ($wantedExt as $ext): $loaded = extension_loaded($ext); ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px;">
                        <span style="color:var(--text-primary);"><?= htmlspecialchars($ext) ?></span>
                        <span style="color:<?= $loaded ? '#00dc82' : '#f87171' ?>;">
                            <i class="fas <?= $loaded ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                            <?= $loaded ? 'Loaded' : 'Missing' ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="dep-card">
            <div class="dep-card-head"><span><i class="fas fa-hdd"></i>Disk Storage</span></div>
            <div class="dep-card-body">
                <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--text-secondary);margin-bottom:10px;">
                    <span><i class="fas fa-circle" style="color:#f87171;font-size:10px;margin-right:4px;"></i>Used: <?= bsz($diskUsed) ?></span>
                    <span><i class="fas fa-circle" style="color:#00dc82;font-size:10px;margin-right:4px;"></i>Free: <?= bsz($diskFree) ?></span>
                    <span>Total: <?= bsz($diskTotal) ?></span>
                </div>
                <div class="dep-progress" style="height:14px;">
                    <?php $col = $diskPct > 85 ? '#f87171' : ($diskPct > 70 ? '#f59e0b' : 'var(--cyan)'); ?>
                    <div class="dep-progress-fill" style="width:<?= $diskPct ?>%;background:<?= $col ?>;"></div>
                </div>
                <div style="text-align:center;font-size:12px;color:var(--text-secondary);margin-top:6px;"><?= $diskPct ?>% used</div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════ SETTINGS ══════════════════════════════ -->
    <div class="dep-panel <?= $activeTab === 'settings' ? 'active' : '' ?>">
        <div class="dep-card">
            <div class="dep-card-head"><span><i class="fas fa-cog"></i>Deployment Configuration</span></div>
            <div class="dep-card-body">
                <table class="dep-info-table">
                    <tr><td>Base Path</td><td><?= htmlspecialchars(defined('BASE_PATH') ? BASE_PATH : '—') ?></td></tr>
                    <tr><td>App Name</td><td><?= htmlspecialchars(defined('APP_NAME') ? APP_NAME : '—') ?></td></tr>
                    <tr><td>App URL</td><td><?= htmlspecialchars(defined('APP_URL') ? APP_URL : '—') ?></td></tr>
                    <tr><td>Environment</td><td><?= htmlspecialchars(defined('APP_ENV') ? APP_ENV : 'production') ?></td></tr>
                    <tr><td>Debug Mode</td><td><?= defined('APP_DEBUG') && APP_DEBUG ? '<span style="color:#f59e0b;">Enabled</span>' : '<span style="color:#00dc82;">Disabled</span>' ?></td></tr>
                    <tr><td>PHP CLI</td><td><?= htmlspecialchars(PHP_BINARY) ?></td></tr>
                </table>
            </div>
        </div>

        <div class="dep-card">
            <div class="dep-card-head"><span><i class="fas fa-folder"></i>Key Directories</span></div>
            <div class="dep-card-body">
                <?php
                $dirs = [
                    'storage/logs'    => BASE_PATH . '/storage/logs',
                    'storage/cache'   => BASE_PATH . '/storage/cache',
                    'public/uploads'  => BASE_PATH . '/public/uploads',
                    'vendor'          => BASE_PATH . '/vendor',
                ];
                foreach ($dirs as $label => $path):
                    $exists   = is_dir($path);
                    $writable = $exists && is_writable($path);
                ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px;">
                    <span style="color:var(--text-primary);font-family:monospace;"><?= htmlspecialchars($label) ?></span>
                    <div style="display:flex;gap:8px;">
                        <span style="color:<?= $exists ? '#00dc82' : '#f87171' ?>;">
                            <i class="fas <?= $exists ? 'fa-check' : 'fa-times' ?>"></i> <?= $exists ? 'Exists' : 'Missing' ?>
                        </span>
                        <?php if ($exists): ?>
                        <span style="color:<?= $writable ? '#00dc82' : '#f59e0b' ?>;">
                            <i class="fas <?= $writable ? 'fa-lock-open' : 'fa-lock' ?>"></i> <?= $writable ? 'Writable' : 'Read-only' ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>
<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<script>
function depRunCmd(cmd) {
    var out = document.getElementById('depCmdOutput');
    var content = document.getElementById('depCmdContent');
    out.style.display = 'block';
    content.textContent = '⏳ Running…';
    out.scrollIntoView({ behavior: 'smooth', block: 'center' });
    fetch('/admin/deployment/run', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cmd: cmd })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) { content.textContent = d.output || d.error || '(no output)'; })
    .catch(function(e) { content.textContent = 'Error: ' + e.message; });
}
</script>
<?php View::endSection(); ?>
