<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<style>
.act-tab-bar{display:flex;gap:6px;margin-bottom:20px;border-bottom:1px solid var(--border-color);padding-bottom:12px;}
.act-tab{padding:7px 18px;border:1px solid var(--border-color);border-radius:8px;cursor:pointer;font-size:13px;background:transparent;color:var(--text-secondary);transition:.15s;}
.act-tab.active{background:var(--cyan);color:#fff;font-weight:600;border-color:var(--cyan);}
.tab-panel{display:none;}
.tab-panel.active{display:block;}
.chart-card{background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;}
.chart-title{font-size:13px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.7px;margin-bottom:14px;}
</style>

<!-- Header -->
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
    <div>
        <h1 style="margin:0;font-size:1.4rem;">Activity Logs</h1>
        <p style="color:var(--text-secondary);margin:4px 0 0;font-size:13px;">
            <a href="/admin/logs" style="color:var(--text-secondary);">← Logs</a> &nbsp;|&nbsp;
            <a href="/admin/audit" style="color:var(--cyan);">🔍 Audit Explorer</a>
        </p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <?php $exportParams = ['action'=>$currentAction,'module'=>$currentModule,'user_id'=>$currentUserId,'email'=>$currentEmail??'','status'=>$currentStatus,'entity_id'=>$currentEntityId??'','entity_name'=>$currentEntityName??'','date_from'=>$dateFrom,'date_to'=>$dateTo,'search'=>$search]; ?>
        <a href="/admin/logs/activity/export?format=csv&<?= http_build_query($exportParams) ?>"
           class="btn btn-sm btn-secondary"><i class="fas fa-file-csv"></i> CSV</a>
        <a href="/admin/logs/activity/export?format=json&<?= http_build_query($exportParams) ?>"
           class="btn btn-sm btn-secondary"><i class="fas fa-file-code"></i> JSON</a>
        <a href="/admin/logs/activity/api?<?= http_build_query($exportParams) ?>"
           class="btn btn-sm btn-secondary" target="_blank"><i class="fas fa-code"></i> API</a>
    </div>
</div>

<!-- 4-stat cards -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
    <?php
    $statCards = [
        ['val'=>$stats['total']??0,        'lbl'=>'Total Events',   'color'=>'var(--cyan)'],
        ['val'=>$stats['unique_users']??0,  'lbl'=>'Unique Users',   'color'=>'var(--green)'],
        ['val'=>$stats['today']??0,         'lbl'=>'Events Today',   'color'=>'var(--orange)'],
        ['val'=>$stats['failures']??0,      'lbl'=>'Failures',       'color'=>'#e74c3c'],
    ];
    foreach ($statCards as $sc): ?>
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:18px 20px;">
            <div style="font-size:1.8rem;font-weight:700;color:<?= $sc['color'] ?>;"><?= number_format($sc['val']) ?></div>
            <div style="color:var(--text-secondary);font-size:13px;"><?= $sc['lbl'] ?></div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Tab bar -->
<div class="act-tab-bar">
    <button class="act-tab active" data-tab="dashboard">📊 Dashboard</button>
    <button class="act-tab"        data-tab="timeline">📋 Timeline</button>
</div>

<!-- ═══════════════════════ DASHBOARD TAB ═══════════════════════ -->
<div class="tab-panel active" id="tab-dashboard">

    <!-- Row 1: 7-day trend (full width) -->
    <div class="chart-card mb-3" style="margin-bottom:16px;">
        <div class="chart-title">7-Day Activity Trend</div>
        <canvas id="trendChart" height="80"></canvas>
    </div>

    <!-- Row 2: 2-column -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
        <div class="chart-card">
            <div class="chart-title">Top Actions</div>
            <canvas id="actionsChart" height="200"></canvas>
        </div>
        <div class="chart-card">
            <div class="chart-title">Module Distribution</div>
            <canvas id="moduleChart" height="200"></canvas>
        </div>
    </div>

    <!-- Row 3: status + heatmap -->
    <div style="display:grid;grid-template-columns:1fr 2fr;gap:16px;">
        <div class="chart-card">
            <div class="chart-title">Status Breakdown</div>
            <canvas id="statusChart" height="160"></canvas>
        </div>
        <div class="chart-card">
            <div class="chart-title">Recent Activity Stream</div>
            <div id="activityStream" style="max-height:210px;overflow-y:auto;display:flex;flex-direction:column;gap:6px;">
                <?php foreach (array_slice($logs, 0, 15) as $log): ?>
                    <?php
                    $sc = ['success'=>'var(--green)','failure'=>'#e74c3c','pending'=>'var(--orange)'][$log['status']??'success'] ?? 'var(--green)';
                    ?>
                    <div style="display:flex;align-items:center;gap:10px;padding:6px 8px;border-radius:8px;background:var(--bg-secondary);">
                        <span style="width:7px;height:7px;border-radius:50%;background:<?= $sc ?>;flex-shrink:0;"></span>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:12px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?= View::e($log['readable_message'] ?? $log['action']) ?>
                            </div>
                            <div style="font-size:10px;color:var(--text-secondary);">
                                <?= View::e($log['name'] ?? 'System') ?> &middot;
                                <?= Helpers::formatDate($log['created_at'], 'M d, H:i') ?>
                            </div>
                        </div>
                        <?php if (!empty($log['module'])): ?>
                            <span style="font-size:10px;background:rgba(59,130,246,0.1);color:var(--cyan);padding:1px 6px;border-radius:8px;white-space:nowrap;"><?= View::e($log['module']) ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════ TIMELINE TAB ═══════════════════════ -->
<div class="tab-panel" id="tab-timeline">

    <!-- Filters -->
    <div class="card mb-3" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:16px;margin-bottom:12px;">
        <form method="GET" action="/admin/logs/activity">
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:2;min-width:160px;">
                    <label style="display:block;margin-bottom:4px;font-size:11px;color:var(--text-secondary);">Search</label>
                    <input type="text" name="search" class="form-input" placeholder="Action, message, user, entity, IP…" value="<?= View::e($search) ?>">
                </div>
                <div style="min-width:150px;">
                    <label style="display:block;margin-bottom:4px;font-size:11px;color:var(--text-secondary);">Action</label>
                    <select name="action" class="form-input">
                        <option value="">All Actions</option>
                        <?php foreach ($actions as $a): ?>
                            <option value="<?= View::e($a['action']) ?>" <?= $currentAction === $a['action'] ? 'selected' : '' ?>><?= View::e($a['action']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width:130px;">
                    <label style="display:block;margin-bottom:4px;font-size:11px;color:var(--text-secondary);">Module</label>
                    <select name="module" class="form-input">
                        <option value="">All Modules</option>
                        <?php foreach ($modules as $m): ?>
                            <option value="<?= View::e($m['module']) ?>" <?= $currentModule === $m['module'] ? 'selected' : '' ?>><?= View::e(ucfirst($m['module'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width:110px;">
                    <label style="display:block;margin-bottom:4px;font-size:11px;color:var(--text-secondary);">Status</label>
                    <select name="status" class="form-input">
                        <option value="">All</option>
                        <option value="success" <?= $currentStatus==='success'?'selected':'' ?>>Success</option>
                        <option value="failure" <?= $currentStatus==='failure'?'selected':'' ?>>Failure</option>
                        <option value="pending" <?= $currentStatus==='pending'?'selected':'' ?>>Pending</option>
                    </select>
                </div>
                <div style="min-width:110px;">
                    <label style="display:block;margin-bottom:4px;font-size:11px;color:var(--text-secondary);">Category</label>
                    <select name="category" class="form-input">
                        <option value="">All</option>
                        <option value="admin" <?= $category==='admin'?'selected':'' ?>>Admin</option>
                        <option value="user"  <?= $category==='user' ?'selected':'' ?>>User</option>
                    </select>
                </div>
                <div style="min-width:100px;">
                    <label style="display:block;margin-bottom:4px;font-size:11px;color:var(--text-secondary);">User ID</label>
                    <input type="number" name="user_id" class="form-input" placeholder="User ID" value="<?= View::e($currentUserId) ?>">
                </div>
                <div style="min-width:160px;">
                    <label style="display:block;margin-bottom:4px;font-size:11px;color:var(--text-secondary);">Email</label>
                    <input type="email" name="email" class="form-input" placeholder="user@example.com" value="<?= View::e($currentEmail ?? '') ?>">
                </div>
                <div style="min-width:110px;">
                    <label style="display:block;margin-bottom:4px;font-size:11px;color:var(--text-secondary);">Entity ID</label>
                    <input type="text" name="entity_id" class="form-input" placeholder="e.g. 42" value="<?= View::e($currentEntityId ?? '') ?>">
                </div>
                <div style="min-width:130px;">
                    <label style="display:block;margin-bottom:4px;font-size:11px;color:var(--text-secondary);">Entity Name</label>
                    <input type="text" name="entity_name" class="form-input" placeholder="e.g. Payment API" value="<?= View::e($currentEntityName ?? '') ?>">
                </div>
                <div style="min-width:120px;">
                    <label style="display:block;margin-bottom:4px;font-size:11px;color:var(--text-secondary);">From</label>
                    <input type="date" name="date_from" class="form-input" value="<?= View::e($dateFrom) ?>">
                </div>
                <div style="min-width:120px;">
                    <label style="display:block;margin-bottom:4px;font-size:11px;color:var(--text-secondary);">To</label>
                    <input type="date" name="date_to" class="form-input" value="<?= View::e($dateTo) ?>">
                </div>
                <div style="display:flex;gap:6px;">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
                    <a href="/admin/logs/activity" class="btn btn-secondary btn-sm">Clear</a>
                </div>
            </div>
        </form>
    </div>

    <?php if (!empty($logs)): ?>
        <p style="color:var(--text-secondary);font-size:12px;margin-bottom:10px;">
            Showing <?= number_format(count($logs)) ?> of <?= number_format($pagination['count'] ?? 0) ?> events
        </p>
    <?php endif; ?>

    <!-- Timeline table -->
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;overflow:hidden;">
        <?php if (empty($logs)): ?>
            <p style="color:var(--text-secondary);text-align:center;padding:30px;">No activity logs found.</p>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="table" style="min-width:800px;">
                    <thead>
                        <tr>
                            <th style="width:190px;">User</th>
                            <th>Event</th>
                            <th style="width:95px;">Module</th>
                            <th style="width:75px;">Status</th>
                            <th style="width:115px;">IP / Device</th>
                            <th style="width:135px;">Date</th>
                            <th style="width:50px;text-align:center;">▾</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $idx => $log): ?>
                            <?php
                            $isAdmin    = \Controllers\Admin\LogController::isAdminAction($log['action']);
                            $badgeCls   = $isAdmin ? 'badge-warning' : 'badge-info';
                            $changesArr = json_decode($log['changes'] ?? '', true);
                            $oldVals    = json_decode($log['old_values'] ?? '', true);
                            $newVals    = json_decode($log['new_values'] ?? '', true);
                            $extraData  = json_decode($log['data'] ?? '', true);
                            $hasDetails = !empty($changesArr) || !empty($oldVals) || !empty($newVals) || !empty($extraData);
                            $rowId      = 'ld-' . $idx;
                            $statusColor = ['success'=>'var(--green)','failure'=>'#e74c3c','pending'=>'var(--orange)'][$log['status']??'success'] ?? 'var(--green)';
                            // Use denormalized user_name when JOIN didn't return a name (e.g. deleted user)
                            $displayName = $log['name'] ?? $log['user_name'] ?? 'Unknown';
                            ?>
                            <tr>
                                <td>
                                    <div style="font-weight:500;font-size:13px;"><?= View::e($displayName) ?></div>
                                    <div style="font-size:11px;color:var(--text-secondary);"><?= View::e($log['email'] ?? '') ?></div>
                                    <?php if (!empty($log['user_id'])): ?>
                                        <span style="font-size:10px;background:var(--bg-secondary);padding:1px 6px;border-radius:4px;color:var(--text-secondary);">ID #<?= (int)$log['user_id'] ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($log['user_role'])): ?>
                                        <span style="font-size:10px;background:var(--bg-secondary);padding:1px 6px;border-radius:4px;color:var(--text-secondary);"><?= View::e($log['user_role']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($log['readable_message'])): ?>
                                        <div style="font-size:13px;margin-bottom:3px;"><?= View::e($log['readable_message']) ?></div>
                                    <?php endif; ?>
                                    <span class="badge <?= $badgeCls ?>" style="font-size:11px;"><?= View::e($log['action']) ?></span>
                                    <?php if (!empty($log['resource_type'])): ?>
                                        <span style="font-size:11px;color:var(--text-secondary);margin-left:4px;">
                                            <?= View::e($log['resource_type']) ?>
                                            <?php if (!empty($log['entity_name'])): ?>
                                                <span style="color:var(--cyan);">'<?= View::e($log['entity_name']) ?>'</span>
                                            <?php elseif (!empty($log['resource_id'])): ?>
                                                #<?= View::e($log['resource_id']) ?>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($changesArr)): ?>
                                        <span style="font-size:10px;background:rgba(255,152,0,0.15);color:#ff9800;padding:1px 6px;border-radius:8px;margin-left:4px;" title="<?= count($changesArr) ?> field(s) changed">
                                            <?= count($changesArr) ?> change<?= count($changesArr) !== 1 ? 's' : '' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($log['module'])): ?>
                                        <span style="font-size:11px;background:rgba(59,130,246,0.1);color:var(--cyan);padding:2px 8px;border-radius:10px;"><?= View::e($log['module']) ?></span>
                                    <?php else: ?><small style="color:var(--text-secondary);">—</small><?php endif; ?>
                                </td>
                                <td><span style="font-size:11px;font-weight:600;color:<?= $statusColor ?>;"><?= View::e(ucfirst($log['status']??'success')) ?></span></td>
                                <td>
                                    <div style="font-family:monospace;font-size:11px;"><?= View::e($log['ip_address'] ?? '—') ?></div>
                                    <?php if (!empty($log['device']) || !empty($log['browser'])): ?>
                                        <div style="font-size:10px;color:var(--text-secondary);"><?= View::e(implode(' / ', array_filter([$log['device']??null,$log['browser']??null]))) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:12px;white-space:nowrap;"><?= Helpers::formatDate($log['created_at'], 'M d, Y H:i') ?></td>
                                <td style="text-align:center;">
                                    <?php if ($hasDetails): ?>
                                        <button onclick="toggleDetail('<?= $rowId ?>')" style="background:none;border:none;cursor:pointer;color:var(--cyan);font-size:16px;" title="Toggle details">⊕</button>
                                    <?php else: ?><span style="color:var(--text-secondary);font-size:12px;">—</span><?php endif; ?>
                                </td>
                            </tr>
                            <?php if ($hasDetails): ?>
                                <tr id="<?= $rowId ?>" style="display:none;background:var(--bg-secondary);">
                                    <td colspan="7" style="padding:14px 18px;">

                                        <?php if (!empty($changesArr)): ?>
                                            <!-- Field-level Changes Diff -->
                                            <div style="margin-bottom:12px;">
                                                <div style="font-size:10px;font-weight:700;color:#ff9800;margin-bottom:7px;text-transform:uppercase;letter-spacing:.6px;">
                                                    ✎ Field Changes
                                                </div>
                                                <div style="display:flex;flex-direction:column;gap:5px;">
                                                    <?php foreach ($changesArr as $field => $diff): ?>
                                                        <?php
                                                        $oldStr = $diff['old'] !== null ? (is_array($diff['old']) ? json_encode($diff['old']) : (string)$diff['old']) : '(empty)';
                                                        $newStr = $diff['new'] !== null ? (is_array($diff['new']) ? json_encode($diff['new']) : (string)$diff['new']) : '(empty)';
                                                        ?>
                                                        <div style="display:flex;align-items:baseline;gap:8px;background:var(--bg-card);border:1px solid var(--border-color);border-radius:7px;padding:7px 10px;font-size:11px;">
                                                            <span style="font-weight:600;color:var(--text-secondary);min-width:100px;flex-shrink:0;"><?= View::e(str_replace('_', ' ', $field)) ?>:</span>
                                                            <span style="color:#e74c3c;text-decoration:line-through;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= View::e($oldStr) ?>"><?= View::e(mb_strlen($oldStr) > 60 ? mb_substr($oldStr, 0, 57) . '…' : $oldStr) ?></span>
                                                            <span style="color:var(--text-secondary);flex-shrink:0;">→</span>
                                                            <span style="color:var(--green);font-weight:500;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= View::e($newStr) ?>"><?= View::e(mb_strlen($newStr) > 60 ? mb_substr($newStr, 0, 57) . '…' : $newStr) ?></span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php elseif (!empty($oldVals) || !empty($newVals)): ?>
                                            <!-- Legacy before/after snapshots (no changes column) -->
                                            <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:10px;">
                                                <?php if (!empty($oldVals)): ?>
                                                    <div style="flex:1;min-width:180px;">
                                                        <div style="font-size:10px;font-weight:700;color:#e74c3c;margin-bottom:5px;">BEFORE</div>
                                                        <div style="background:rgba(231,76,60,0.08);border:1px solid rgba(231,76,60,0.25);border-radius:7px;padding:9px;">
                                                            <?php foreach ($oldVals as $k=>$v): ?>
                                                                <div style="display:flex;gap:6px;margin-bottom:3px;font-size:11px;">
                                                                    <span style="color:var(--text-secondary);min-width:90px;flex-shrink:0;"><?= View::e($k) ?>:</span>
                                                                    <span style="color:#e74c3c;"><?= View::e(is_array($v)?json_encode($v):(string)$v) ?></span>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($newVals)): ?>
                                                    <div style="flex:1;min-width:180px;">
                                                        <div style="font-size:10px;font-weight:700;color:var(--green);margin-bottom:5px;">AFTER</div>
                                                        <div style="background:rgba(0,200,100,0.08);border:1px solid rgba(0,200,100,0.25);border-radius:7px;padding:9px;">
                                                            <?php foreach ($newVals as $k=>$v): ?>
                                                                <div style="display:flex;gap:6px;margin-bottom:3px;font-size:11px;">
                                                                    <span style="color:var(--text-secondary);min-width:90px;flex-shrink:0;"><?= View::e($k) ?>:</span>
                                                                    <span style="color:var(--green);"><?= View::e(is_array($v)?json_encode($v):(string)$v) ?></span>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($extraData)): ?>
                                            <div style="font-size:10px;font-weight:700;color:var(--text-secondary);margin-bottom:5px;">EXTRA DATA</div>
                                            <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:7px;padding:9px;font-size:11px;">
                                                <?php foreach ($extraData as $k=>$v): ?>
                                                    <div style="display:flex;gap:6px;margin-bottom:3px;">
                                                        <span style="color:var(--text-secondary);min-width:110px;flex-shrink:0;"><?= View::e($k) ?>:</span>
                                                        <span><?= View::e(is_array($v)?json_encode($v):(string)$v) ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($log['request_id'])): ?>
                                            <div style="margin-top:6px;font-size:10px;color:var(--text-secondary);">Request ID: <code><?= View::e($log['request_id']) ?></code></div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total'] > 1): ?>
                <div style="display:flex;justify-content:center;gap:8px;padding:14px;">
                    <?php $q = http_build_query(['search'=>$search,'action'=>$currentAction,'module'=>$currentModule,'category'=>$category,'status'=>$currentStatus,'user_id'=>$currentUserId,'email'=>$currentEmail??'','entity_id'=>$currentEntityId??'','entity_name'=>$currentEntityName??'','date_from'=>$dateFrom,'date_to'=>$dateTo]); ?>
                    <?php if ($pagination['current'] > 1): ?>
                        <a href="?page=<?= $pagination['current']-1 ?>&<?= $q ?>" class="btn btn-sm btn-secondary">← Prev</a>
                    <?php endif; ?>
                    <span style="padding:8px 14px;color:var(--text-secondary);font-size:13px;">Page <?= $pagination['current'] ?> of <?= $pagination['total'] ?></span>
                    <?php if ($pagination['current'] < $pagination['total']): ?>
                        <a href="?page=<?= $pagination['current']+1 ?>&<?= $q ?>" class="btn btn-sm btn-secondary">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ═══════════════════════ CHARTS ═══════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// --- Data from PHP ---
const trendData   = <?= json_encode(array_map(fn($r)=>['date'=>$r['date'],'count'=>(int)$r['count']], $trend ?? [])) ?>;
const topActions  = <?= json_encode(array_map(fn($r)=>['action'=>$r['action'],'cnt'=>(int)$r['cnt']], $topActions ?? [])) ?>;
const moduleData  = <?= json_encode(array_map(fn($r)=>['module'=>$r['module'],'cnt'=>(int)$r['cnt']], $moduleDistrib ?? [])) ?>;
const statusData  = <?= json_encode(array_map(fn($r)=>['status'=>$r['status'],'cnt'=>(int)$r['cnt']], $statusDistrib ?? [])) ?>;

const PALETTE = ['#3b82f6','#00c853','#ff9800','#e74c3c','#9c27b0','#2196F3','#ff5722','#4caf50'];
const gridColor = 'rgba(255,255,255,0.06)';
const baseOpts  = { responsive: true, plugins: { legend: { labels: { color: '#aaa', boxWidth: 12 } } } };

// 1. 7-day trend (line)
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: trendData.map(d => d.date),
        datasets: [{
            label: 'Events',
            data: trendData.map(d => d.count),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.08)',
            fill: true,
            tension: 0.35,
            pointBackgroundColor: '#3b82f6',
        }]
    },
    options: {
        ...baseOpts,
        scales: {
            x: { ticks: { color: '#888' }, grid: { color: gridColor } },
            y: { ticks: { color: '#888' }, grid: { color: gridColor }, beginAtZero: true }
        }
    }
});

// 2. Top actions (horizontal bar)
new Chart(document.getElementById('actionsChart'), {
    type: 'bar',
    data: {
        labels: topActions.map(d => d.action),
        datasets: [{
            label: 'Count',
            data: topActions.map(d => d.cnt),
            backgroundColor: PALETTE,
        }]
    },
    options: {
        ...baseOpts,
        indexAxis: 'y',
        scales: {
            x: { ticks: { color: '#888' }, grid: { color: gridColor }, beginAtZero: true },
            y: { ticks: { color: '#888', font: { size: 11 } }, grid: { color: gridColor } }
        }
    }
});

// 3. Module doughnut
new Chart(document.getElementById('moduleChart'), {
    type: 'doughnut',
    data: {
        labels: moduleData.map(d => d.module),
        datasets: [{ data: moduleData.map(d => d.cnt), backgroundColor: PALETTE, borderWidth: 2 }]
    },
    options: { ...baseOpts, cutout: '55%' }
});

// 4. Status doughnut
const STATUS_COLORS = { success: '#00c853', failure: '#e74c3c', pending: '#ff9800' };
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: statusData.map(d => d.status),
        datasets: [{
            data: statusData.map(d => d.cnt),
            backgroundColor: statusData.map(d => STATUS_COLORS[d.status] || '#aaa'),
            borderWidth: 2
        }]
    },
    options: { ...baseOpts, cutout: '50%' }
});

// --- Tabs ---
document.querySelectorAll('.act-tab').forEach(btn => {
    btn.addEventListener('click', () => {
        const target = btn.dataset.tab;
        document.querySelectorAll('.act-tab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + target).classList.add('active');
    });
});

// --- Row details toggle ---
function toggleDetail(id) {
    const row = document.getElementById(id);
    if (!row) return;
    const btn = row.previousElementSibling.querySelector('button[onclick]');
    if (row.style.display === 'none') {
        row.style.display = ''; if (btn) btn.textContent = '⊖';
    } else {
        row.style.display = 'none'; if (btn) btn.textContent = '⊕';
    }
}

// --- If any filter is active, auto-switch to Timeline tab ---
<?php if ($currentAction || $currentModule || $currentStatus || $currentUserId || ($currentEmail??'') || $dateFrom || $dateTo || $search): ?>
document.querySelectorAll('.act-tab').forEach(b => b.classList.remove('active'));
document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
document.querySelector('[data-tab="timeline"]').classList.add('active');
document.getElementById('tab-timeline').classList.add('active');
<?php endif; ?>
</script>

<?php View::endSection(); ?>

