<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1><i class="fas fa-history" style="color:#f59e0b;"></i> BillX — Activity Logs</h1>
        <p style="color:var(--text-secondary);">All user actions for the BillX bill generator</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="/admin/projects/billx" class="btn btn-secondary"><i class="fas fa-tachometer-alt"></i> Overview</a>
        <a href="/admin/projects/billx/bills" class="btn btn-secondary"><i class="fas fa-list"></i> All Bills</a>
        <a href="/admin/projects/billx/settings" class="btn btn-secondary"><i class="fas fa-cog"></i> Settings</a>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:20px;">
    <form method="GET" action="/admin/projects/billx/activity-logs" style="padding:16px;">
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
            <div style="flex:1;min-width:160px;">
                <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">Action</label>
                <select name="action" class="form-control">
                    <option value="">All Actions</option>
                    <?php foreach ($actions as $act): ?>
                    <option value="<?= View::e($act) ?>" <?= ($filters['action'] ?? '') === $act ? 'selected' : '' ?>>
                        <?= View::e($act) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex:1;min-width:160px;">
                <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">Search (user/data)</label>
                <input type="text" name="search" class="form-control" placeholder="Name, email, bill ID…"
                       value="<?= View::e($filters['search'] ?? '') ?>">
            </div>
            <div style="flex:1;min-width:130px;">
                <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">Date From</label>
                <input type="date" name="date_from" class="form-control" value="<?= View::e($filters['dateFrom'] ?? '') ?>">
            </div>
            <div style="flex:1;min-width:130px;">
                <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">Date To</label>
                <input type="date" name="date_to" class="form-control" value="<?= View::e($filters['dateTo'] ?? '') ?>">
            </div>
            <div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                <a href="/admin/projects/billx/activity-logs" class="btn btn-secondary" style="margin-left:8px;">Reset</a>
            </div>
        </div>
    </form>
</div>

<!-- Stats bar -->
<div style="color:var(--text-secondary);font-size:13px;margin-bottom:12px;">
    Showing <?= number_format(count($logs)) ?> of <?= number_format($total) ?> log entries
    <?php if ($total > $perPage): ?>
    &nbsp;| Page <?= $page ?> of <?= ceil($total / $perPage) ?>
    <?php endif; ?>
</div>

<!-- Logs Table -->
<div class="card">
    <div style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:60px;">#</th>
                    <th>Action</th>
                    <th>User</th>
                    <th>Data</th>
                    <th>IP Address</th>
                    <th>Date / Time</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-secondary);">
                    <i class="fas fa-inbox"></i> No activity logs found.
                </td></tr>
            <?php else: foreach ($logs as $log):
                $logData = [];
                if (!empty($log['data'])) {
                    $logData = json_decode($log['data'], true) ?: [];
                }
                $actionClass = match(true) {
                    str_contains($log['action'], 'created')  => 'badge-success',
                    str_contains($log['action'], 'deleted')  => 'badge-danger',
                    str_contains($log['action'], 'pdf')      => 'badge-info',
                    str_contains($log['action'], 'download') => 'badge-info',
                    str_contains($log['action'], 'settings') => 'badge-warning',
                    default                                  => 'badge-secondary',
                };
            ?>
                <tr>
                    <td style="color:var(--text-secondary);font-size:12px;"><?= (int)$log['id'] ?></td>
                    <td><span class="badge <?= $actionClass ?>" style="font-size:11px;"><?= View::e($log['action']) ?></span></td>
                    <td style="font-size:13px;">
                        <?php if (!empty($log['user_name'])): ?>
                        <div style="font-weight:600;"><?= View::e($log['user_name']) ?></div>
                        <div style="color:var(--text-secondary);font-size:12px;"><?= View::e($log['user_email'] ?? '') ?></div>
                        <?php else: ?>
                        <span style="color:var(--text-secondary);">User #<?= (int)$log['user_id'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;max-width:260px;">
                        <?php if (!empty($logData)): ?>
                        <?php foreach ($logData as $k => $v): ?>
                        <span style="color:var(--text-secondary);"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?>:</span>
                        <strong><?= htmlspecialchars(is_array($v) ? json_encode($v) : (string)$v) ?></strong>
                        <?php if (array_key_last($logData) !== $k): ?>&nbsp; <?php endif; ?>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <span style="color:var(--text-secondary);">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:var(--text-secondary);"><?= View::e($log['ip_address'] ?? '—') ?></td>
                    <td style="font-size:12px;white-space:nowrap;color:var(--text-secondary);">
                        <?= $log['created_at'] ? date('d M Y H:i', strtotime($log['created_at'])) : '—' ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($total > $perPage):
    $totalPages = (int)ceil($total / $perPage);
    $qp = array_filter($filters, fn($v) => $v !== '');
?>
<div style="display:flex;justify-content:center;gap:6px;margin-top:20px;flex-wrap:wrap;">
    <?php if ($page > 1): ?>
    <a href="?<?= http_build_query(array_merge($qp, ['page' => $page - 1])) ?>" class="btn btn-secondary btn-sm">
        <i class="fas fa-chevron-left"></i> Prev
    </a>
    <?php endif; ?>
    <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
    <a href="?<?= http_build_query(array_merge($qp, ['page' => $p])) ?>"
       class="btn <?= $p === $page ? 'btn-primary' : 'btn-secondary' ?> btn-sm"><?= $p ?></a>
    <?php endfor; ?>
    <?php if ($page < $totalPages): ?>
    <a href="?<?= http_build_query(array_merge($qp, ['page' => $page + 1])) ?>" class="btn btn-secondary btn-sm">
        Next <i class="fas fa-chevron-right"></i>
    </a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php View::endSection(); ?>
