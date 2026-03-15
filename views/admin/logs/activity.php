<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <a href="/admin/logs" style="color:var(--text-secondary);">&larr; Back to Logs</a>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="/admin/logs/activity/export?format=csv&<?= http_build_query(['action'=>$currentAction,'module'=>$currentModule,'user_id'=>$currentUserId,'status'=>$currentStatus,'date_from'=>$dateFrom,'date_to'=>$dateTo,'search'=>$search]) ?>"
           class="btn btn-sm btn-secondary" title="Export filtered logs as CSV">
            <i class="fas fa-file-csv"></i> Export CSV
        </a>
        <a href="/admin/logs/activity/export?format=json&<?= http_build_query(['action'=>$currentAction,'module'=>$currentModule,'user_id'=>$currentUserId,'status'=>$currentStatus,'date_from'=>$dateFrom,'date_to'=>$dateTo,'search'=>$search]) ?>"
           class="btn btn-sm btn-secondary" title="Export filtered logs as JSON">
            <i class="fas fa-file-code"></i> Export JSON
        </a>
        <a href="/admin/logs/activity/api?<?= http_build_query(['action'=>$currentAction,'module'=>$currentModule,'user_id'=>$currentUserId,'status'=>$currentStatus,'date_from'=>$dateFrom,'date_to'=>$dateTo,'search'=>$search]) ?>"
           class="btn btn-sm btn-secondary" target="_blank" title="JSON API endpoint">
            <i class="fas fa-code"></i> API
        </a>
    </div>
</div>

<!-- Stats row -->
<div class="grid grid-4 mb-3" style="--grid-cols:4;">
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:1.8rem;font-weight:700;color:var(--cyan);"><?= number_format($stats['total'] ?? 0) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Total Events</div>
    </div>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:1.8rem;font-weight:700;color:var(--green);"><?= number_format($stats['unique_users'] ?? 0) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Unique Users</div>
    </div>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:1.8rem;font-weight:700;color:var(--orange);"><?= number_format($stats['today'] ?? 0) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Events Today</div>
    </div>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:1.8rem;font-weight:700;color:var(--red, #e74c3c);"><?= number_format($stats['failures'] ?? 0) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Failures</div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3">
    <form method="GET" action="/admin/logs/activity">
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:2;min-width:180px;">
                <label style="display:block;margin-bottom:5px;font-size:12px;color:var(--text-secondary);">Search</label>
                <input type="text" name="search" class="form-input" placeholder="Action, message, user, IP…" value="<?= View::e($search) ?>">
            </div>

            <div style="min-width:160px;">
                <label style="display:block;margin-bottom:5px;font-size:12px;color:var(--text-secondary);">Action</label>
                <select name="action" class="form-input">
                    <option value="">All Actions</option>
                    <?php foreach ($actions as $a): ?>
                        <option value="<?= View::e($a['action']) ?>" <?= $currentAction === $a['action'] ? 'selected' : '' ?>>
                            <?= View::e($a['action']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="min-width:140px;">
                <label style="display:block;margin-bottom:5px;font-size:12px;color:var(--text-secondary);">Module</label>
                <select name="module" class="form-input">
                    <option value="">All Modules</option>
                    <?php foreach ($modules as $m): ?>
                        <option value="<?= View::e($m['module']) ?>" <?= $currentModule === $m['module'] ? 'selected' : '' ?>>
                            <?= View::e(ucfirst($m['module'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="min-width:120px;">
                <label style="display:block;margin-bottom:5px;font-size:12px;color:var(--text-secondary);">Category</label>
                <select name="category" class="form-input">
                    <option value="">All</option>
                    <option value="admin" <?= $category === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="user"  <?= $category === 'user'  ? 'selected' : '' ?>>User</option>
                </select>
            </div>

            <div style="min-width:120px;">
                <label style="display:block;margin-bottom:5px;font-size:12px;color:var(--text-secondary);">Status</label>
                <select name="status" class="form-input">
                    <option value="">All Statuses</option>
                    <option value="success" <?= $currentStatus === 'success' ? 'selected' : '' ?>>Success</option>
                    <option value="failure" <?= $currentStatus === 'failure' ? 'selected' : '' ?>>Failure</option>
                    <option value="pending" <?= $currentStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>

            <div style="min-width:110px;">
                <label style="display:block;margin-bottom:5px;font-size:12px;color:var(--text-secondary);">User ID</label>
                <input type="number" name="user_id" class="form-input" placeholder="User ID" value="<?= View::e($currentUserId) ?>">
            </div>

            <div style="min-width:130px;">
                <label style="display:block;margin-bottom:5px;font-size:12px;color:var(--text-secondary);">From</label>
                <input type="date" name="date_from" class="form-input" value="<?= View::e($dateFrom) ?>">
            </div>

            <div style="min-width:130px;">
                <label style="display:block;margin-bottom:5px;font-size:12px;color:var(--text-secondary);">To</label>
                <input type="date" name="date_to" class="form-input" value="<?= View::e($dateTo) ?>">
            </div>

            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
                <a href="/admin/logs/activity" class="btn btn-secondary btn-sm">Clear</a>
            </div>
        </div>
    </form>
</div>

<!-- Result count -->
<?php if (!empty($logs)): ?>
    <p style="color:var(--text-secondary);font-size:13px;margin-bottom:12px;">
        Showing <?= number_format(count($logs)) ?> of <?= number_format($pagination['count'] ?? 0) ?> events
    </p>
<?php endif; ?>

<!-- Activity Timeline -->
<div class="card">
    <?php if (empty($logs)): ?>
        <p style="color:var(--text-secondary);text-align:center;padding:30px;">No activity logs found.</p>
    <?php else: ?>
        <!-- Desktop table -->
        <div class="table-responsive" style="overflow-x:auto;">
            <table class="table" style="min-width:800px;">
                <thead>
                    <tr>
                        <th style="width:200px;">User</th>
                        <th>Event</th>
                        <th style="width:100px;">Module</th>
                        <th style="width:80px;">Status</th>
                        <th style="width:120px;">IP / Device</th>
                        <th style="width:140px;">Date</th>
                        <th style="width:60px;text-align:center;">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $idx => $log): ?>
                        <?php
                        $isAdmin    = \Controllers\Admin\LogController::isAdminAction($log['action']);
                        $badgeClass = $isAdmin ? 'badge-warning' : 'badge-info';
                        $oldVals    = json_decode($log['old_values'] ?? '', true);
                        $newVals    = json_decode($log['new_values'] ?? '', true);
                        $decodedData = json_decode($log['data'] ?? '', true);
                        $hasDetails  = !empty($oldVals) || !empty($newVals) || !empty($decodedData);
                        $rowId       = 'log-detail-' . $idx;
                        $statusColor = [
                            'success' => 'var(--green)',
                            'failure' => '#e74c3c',
                            'pending' => 'var(--orange)',
                        ][$log['status'] ?? 'success'] ?? 'var(--green)';
                        ?>
                        <tr>
                            <td>
                                <div style="font-weight:500;font-size:13px;"><?= View::e($log['name'] ?? 'Unknown') ?></div>
                                <div style="font-size:11px;color:var(--text-secondary);"><?= View::e($log['email'] ?? '') ?></div>
                                <?php if (!empty($log['user_role'])): ?>
                                    <span style="font-size:10px;background:var(--bg-secondary);padding:1px 6px;border-radius:4px;color:var(--text-secondary);">
                                        <?= View::e($log['user_role']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($log['readable_message'])): ?>
                                    <div style="font-size:13px;margin-bottom:3px;"><?= View::e($log['readable_message']) ?></div>
                                <?php endif; ?>
                                <span class="badge <?= $badgeClass ?>" style="font-size:11px;"><?= View::e($log['action']) ?></span>
                                <?php if (!empty($log['resource_type'])): ?>
                                    <span style="font-size:11px;color:var(--text-secondary);margin-left:4px;">
                                        <?= View::e($log['resource_type']) ?>
                                        <?= !empty($log['resource_id']) ? '#' . View::e($log['resource_id']) : '' ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($log['module'])): ?>
                                    <span style="font-size:11px;background:rgba(0,240,255,0.1);color:var(--cyan);padding:2px 8px;border-radius:10px;">
                                        <?= View::e($log['module']) ?>
                                    </span>
                                <?php else: ?>
                                    <small style="color:var(--text-secondary);">—</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="font-size:11px;font-weight:600;color:<?= $statusColor ?>;">
                                    <?= View::e(ucfirst($log['status'] ?? 'success')) ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-family:monospace;font-size:11px;"><?= View::e($log['ip_address'] ?? '—') ?></div>
                                <?php if (!empty($log['device']) || !empty($log['browser'])): ?>
                                    <div style="font-size:10px;color:var(--text-secondary);">
                                        <?= View::e(implode(' / ', array_filter([$log['device'] ?? null, $log['browser'] ?? null]))) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px;white-space:nowrap;"><?= Helpers::formatDate($log['created_at'], 'M d, Y H:i') ?></td>
                            <td style="text-align:center;">
                                <?php if ($hasDetails): ?>
                                    <button onclick="toggleDetail('<?= $rowId ?>')"
                                            style="background:none;border:none;cursor:pointer;color:var(--cyan);font-size:16px;"
                                            title="Toggle details">⊕</button>
                                <?php else: ?>
                                    <span style="color:var(--text-secondary);font-size:12px;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if ($hasDetails): ?>
                            <tr id="<?= $rowId ?>" style="display:none;background:var(--bg-secondary);">
                                <td colspan="7" style="padding:16px 20px;">
                                    <?php if (!empty($oldVals) || !empty($newVals)): ?>
                                        <div style="display:flex;gap:20px;flex-wrap:wrap;margin-bottom:12px;">
                                            <?php if (!empty($oldVals)): ?>
                                                <div style="flex:1;min-width:200px;">
                                                    <div style="font-size:11px;font-weight:600;color:#e74c3c;margin-bottom:6px;">BEFORE</div>
                                                    <div style="background:rgba(231,76,60,0.08);border:1px solid rgba(231,76,60,0.25);border-radius:8px;padding:10px;">
                                                        <?php foreach ($oldVals as $k => $v): ?>
                                                            <div style="display:flex;gap:8px;margin-bottom:4px;font-size:12px;">
                                                                <span style="color:var(--text-secondary);min-width:100px;flex-shrink:0;"><?= View::e($k) ?>:</span>
                                                                <span style="color:#e74c3c;"><?= View::e(is_array($v) ? json_encode($v) : (string)$v) ?></span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($newVals)): ?>
                                                <div style="flex:1;min-width:200px;">
                                                    <div style="font-size:11px;font-weight:600;color:var(--green);margin-bottom:6px;">AFTER</div>
                                                    <div style="background:rgba(0,200,100,0.08);border:1px solid rgba(0,200,100,0.25);border-radius:8px;padding:10px;">
                                                        <?php foreach ($newVals as $k => $v): ?>
                                                            <div style="display:flex;gap:8px;margin-bottom:4px;font-size:12px;">
                                                                <span style="color:var(--text-secondary);min-width:100px;flex-shrink:0;"><?= View::e($k) ?>:</span>
                                                                <span style="color:var(--green);"><?= View::e(is_array($v) ? json_encode($v) : (string)$v) ?></span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($decodedData)): ?>
                                        <div>
                                            <div style="font-size:11px;font-weight:600;color:var(--text-secondary);margin-bottom:6px;">EXTRA DATA</div>
                                            <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:8px;padding:10px;font-size:12px;">
                                                <?php foreach ($decodedData as $k => $v): ?>
                                                    <div style="display:flex;gap:8px;margin-bottom:4px;">
                                                        <span style="color:var(--text-secondary);min-width:120px;flex-shrink:0;"><?= View::e($k) ?>:</span>
                                                        <span><?= View::e(is_array($v) ? json_encode($v) : (string)$v) ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($log['request_id'])): ?>
                                        <div style="margin-top:8px;font-size:11px;color:var(--text-secondary);">
                                            Request ID: <code><?= View::e($log['request_id']) ?></code>
                                        </div>
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
            <div style="display:flex;justify-content:center;gap:8px;padding:16px;">
                <?php
                $q = http_build_query([
                    'search'        => $search,
                    'action'        => $currentAction,
                    'module'        => $currentModule,
                    'category'      => $category,
                    'status'        => $currentStatus,
                    'user_id'       => $currentUserId,
                    'date_from'     => $dateFrom,
                    'date_to'       => $dateTo,
                ]);
                ?>
                <?php if ($pagination['current'] > 1): ?>
                    <a href="?page=<?= $pagination['current'] - 1 ?>&<?= $q ?>" class="btn btn-sm btn-secondary">← Prev</a>
                <?php endif; ?>

                <span style="padding:8px 14px;color:var(--text-secondary);font-size:13px;">
                    Page <?= $pagination['current'] ?> of <?= $pagination['total'] ?>
                </span>

                <?php if ($pagination['current'] < $pagination['total']): ?>
                    <a href="?page=<?= $pagination['current'] + 1 ?>&<?= $q ?>" class="btn btn-sm btn-secondary">Next →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function toggleDetail(id) {
    const row = document.getElementById(id);
    if (!row) return;
    const btn = row.previousElementSibling.querySelector('button[onclick]');
    if (row.style.display === 'none') {
        row.style.display = '';
        if (btn) btn.textContent = '⊖';
    } else {
        row.style.display = 'none';
        if (btn) btn.textContent = '⊕';
    }
}
</script>
<?php View::endSection(); ?>

