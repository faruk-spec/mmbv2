<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
.rx-analytics-wrap { max-width: 100%; }
.rx-anl-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}
.rx-anl-stat {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
}
.rx-anl-stat .val {
    font-size: 2rem;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 4px;
}
.rx-anl-stat .lbl { color: var(--text-secondary); font-size: 0.82rem; }
.rx-anl-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 28px;
}
@media (max-width: 900px) { .rx-anl-grid { grid-template-columns: 1fr; } }
.rx-anl-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px 24px;
}
.rx-anl-card h3 {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.rx-bar-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}
.rx-bar-label {
    width: 130px;
    font-size: 0.82rem;
    color: var(--text-secondary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex-shrink: 0;
}
.rx-bar-track {
    flex: 1;
    height: 8px;
    background: var(--bg-secondary);
    border-radius: 4px;
    overflow: hidden;
}
.rx-bar-fill {
    height: 100%;
    border-radius: 4px;
    background: linear-gradient(90deg, var(--cyan), var(--purple));
}
.rx-bar-count {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text-primary);
    width: 36px;
    text-align: right;
    flex-shrink: 0;
}
.rx-user-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid var(--border-color);
    font-size: 0.85rem;
}
.rx-user-row:last-child { border-bottom: none; }
.rx-user-name { font-weight: 600; color: var(--text-primary); }
.rx-user-email { color: var(--text-secondary); font-size: 0.78rem; }
.rx-user-badge {
    background: rgba(0,240,255,0.1);
    color: var(--cyan);
    border: 1px solid rgba(0,240,255,0.25);
    border-radius: 20px;
    padding: 2px 10px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}
.rx-ai-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}
.rx-ai-stat {
    text-align: center;
    padding: 14px;
    background: rgba(153,69,255,0.06);
    border: 1px solid rgba(153,69,255,0.15);
    border-radius: 10px;
}
.rx-ai-stat .val { font-size: 1.6rem; font-weight: 800; color: var(--purple); }
.rx-ai-stat .lbl { font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px; }
.rx-chart-wrap {
    width: 100%;
    overflow-x: auto;
    padding-bottom: 8px;
}
.rx-chart {
    display: flex;
    align-items: flex-end;
    gap: 4px;
    height: 100px;
    min-width: 100%;
}
.rx-chart-col {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    min-width: 20px;
}
.rx-chart-bar {
    width: 100%;
    background: linear-gradient(to top, var(--cyan), rgba(0,240,255,0.3));
    border-radius: 3px 3px 0 0;
    min-height: 2px;
    transition: opacity 0.2s;
}
.rx-chart-bar:hover { opacity: 0.75; }
.rx-chart-day { font-size: 0.6rem; color: var(--text-secondary); writing-mode: vertical-lr; transform: rotate(180deg); }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div class="rx-analytics-wrap">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <h1><i class="fas fa-chart-bar" style="color:var(--cyan);"></i> ResumeX — Analytics</h1>
            <p style="color:var(--text-secondary);">Usage statistics, template popularity, and AI activity</p>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="/admin/projects/resumex" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Overview</a>
            <a href="/admin/projects/resumex/resumes" class="btn btn-secondary"><i class="fas fa-list"></i> All Resumes</a>
        </div>
    </div>

    <!-- Top Stats -->
    <div class="rx-anl-stats">
        <div class="rx-anl-stat">
            <div class="val" style="color:var(--cyan);"><?= number_format((int)($stats['total'] ?? 0)) ?></div>
            <div class="lbl">Total Resumes</div>
        </div>
        <div class="rx-anl-stat">
            <div class="val" style="color:#34d399;"><?= number_format((int)($stats['today'] ?? 0)) ?></div>
            <div class="lbl">Created Today</div>
        </div>
        <div class="rx-anl-stat">
            <div class="val" style="color:#a78bfa;"><?= number_format((int)($stats['users'] ?? 0)) ?></div>
            <div class="lbl">Active Users</div>
        </div>
        <div class="rx-anl-stat">
            <div class="val" style="color:#f59e0b;"><?= number_format((int)($stats['thisMonth'] ?? 0)) ?></div>
            <div class="lbl">This Month</div>
        </div>
    </div>

    <!-- Daily Creations Chart + AI Usage -->
    <div class="rx-anl-grid" style="margin-bottom:20px;">
        <!-- Daily Chart -->
        <div class="rx-anl-card">
            <h3><i class="fas fa-chart-area" style="color:var(--cyan);"></i> Resumes Created — Last 30 Days</h3>
            <?php if (empty($daily)): ?>
                <p style="color:var(--text-secondary);font-size:0.875rem;">No data yet.</p>
            <?php else: ?>
                <?php
                    $maxVal = max(array_column($daily, 'cnt') ?: [1]);
                ?>
                <div class="rx-chart-wrap">
                    <div class="rx-chart">
                        <?php foreach ($daily as $d): ?>
                            <?php $pct = $maxVal > 0 ? round(($d['cnt'] / $maxVal) * 100) : 0; ?>
                            <div class="rx-chart-col" title="<?= htmlspecialchars($d['day']) ?>: <?= (int)$d['cnt'] ?>">
                                <div class="rx-chart-bar" style="height:<?= max(2, $pct) ?>%;"></div>
                                <div class="rx-chart-day"><?= date('d', strtotime($d['day'])) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- AI Usage -->
        <div class="rx-anl-card">
            <h3><i class="fas fa-robot" style="color:var(--purple);"></i> AI Usage</h3>
            <div class="rx-ai-grid">
                <div class="rx-ai-stat">
                    <div class="val"><?= number_format((int)($aiUsage['today'] ?? 0)) ?></div>
                    <div class="lbl">Today</div>
                </div>
                <div class="rx-ai-stat">
                    <div class="val"><?= number_format((int)($aiUsage['week'] ?? 0)) ?></div>
                    <div class="lbl">This Week</div>
                </div>
                <div class="rx-ai-stat">
                    <div class="val"><?= number_format((int)($aiUsage['month'] ?? 0)) ?></div>
                    <div class="lbl">This Month</div>
                </div>
            </div>
            <div style="margin-top:16px;padding:12px;background:rgba(0,240,255,0.05);border:1px solid rgba(0,240,255,0.15);border-radius:8px;">
                <p style="color:var(--text-secondary);font-size:0.82rem;margin:0;">
                    <i class="fas fa-info-circle" style="color:var(--cyan);margin-right:4px;"></i>
                    AI usage counts are based on activity log entries tagged with the <code>resumex</code> module.
                    Manage AI settings in <a href="/admin/projects/resumex/settings" style="color:var(--cyan);">Settings</a>.
                </p>
            </div>
        </div>
    </div>

    <!-- Template Popularity + Top Users -->
    <div class="rx-anl-grid">
        <!-- Template Popularity -->
        <div class="rx-anl-card">
            <h3><i class="fas fa-layer-group" style="color:#34d399;"></i> Template Popularity</h3>
            <?php if (empty($byTemplate)): ?>
                <p style="color:var(--text-secondary);font-size:0.875rem;">No resume data yet.</p>
            <?php else: ?>
                <?php $maxTpl = max(array_column($byTemplate, 'cnt') ?: [1]); ?>
                <?php foreach ($byTemplate as $tpl): ?>
                    <div class="rx-bar-row">
                        <div class="rx-bar-label" title="<?= htmlspecialchars($tpl['template']) ?>">
                            <?= htmlspecialchars(ucwords(str_replace(['-', '_'], ' ', $tpl['template']))) ?>
                        </div>
                        <div class="rx-bar-track">
                            <div class="rx-bar-fill" style="width:<?= $maxTpl > 0 ? round(($tpl['cnt']/$maxTpl)*100) : 0 ?>%;"></div>
                        </div>
                        <div class="rx-bar-count"><?= (int)$tpl['cnt'] ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Top Users -->
        <div class="rx-anl-card">
            <h3><i class="fas fa-users" style="color:#f59e0b;"></i> Top Users by Resumes</h3>
            <?php if (empty($topUsers)): ?>
                <p style="color:var(--text-secondary);font-size:0.875rem;">No users yet.</p>
            <?php else: ?>
                <?php foreach ($topUsers as $u): ?>
                    <div class="rx-user-row">
                        <div>
                            <div class="rx-user-name"><?= htmlspecialchars($u['user_name'] ?? 'Unknown') ?></div>
                            <div class="rx-user-email"><?= htmlspecialchars($u['user_email'] ?? '') ?></div>
                        </div>
                        <div class="rx-user-badge"><?= (int)$u['resume_count'] ?> resume<?= (int)$u['resume_count'] !== 1 ? 's' : '' ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
