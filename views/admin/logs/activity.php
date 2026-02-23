<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="margin-bottom:24px;">
    <a href="/admin/logs" style="color:var(--text-secondary);">&larr; Back to Logs</a>
</div>

<!-- Stats row -->
<div class="grid grid-3 mb-3">
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
</div>

<!-- Filters -->
<div class="card mb-3">
    <form method="GET" action="/admin/logs/activity" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:2;min-width:180px;">
            <label style="display:block;margin-bottom:5px;font-size:12px;color:var(--text-secondary);">Search</label>
            <input type="text" name="search" class="form-input" placeholder="Action, user name, email, IP…" value="<?= View::e($search) ?>">
        </div>

        <div style="min-width:180px;">
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

        <div style="min-width:130px;">
            <label style="display:block;margin-bottom:5px;font-size:12px;color:var(--text-secondary);">Category</label>
            <select name="category" class="form-input">
                <option value="">All</option>
                <option value="admin"  <?= $category === 'admin'  ? 'selected' : '' ?>>Admin Actions</option>
                <option value="user"   <?= $category === 'user'   ? 'selected' : '' ?>>User Actions</option>
            </select>
        </div>

        <div style="min-width:130px;">
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
    </form>
</div>

<!-- Logs table -->
<div class="card">
    <?php if (empty($logs)): ?>
        <p style="color:var(--text-secondary);text-align:center;padding:30px;">No activity logs found.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>IP Address</th>
                    <th>Date</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <?php
                    $isAdmin = \Controllers\Admin\LogController::isAdminAction($log['action']);
                    $badgeClass = $isAdmin ? 'badge-warning' : 'badge-info';
                    $decodedData = json_decode($log['data'] ?? '', true);
                    ?>
                    <tr>
                        <td>
                            <div style="font-weight:500;font-size:13px;"><?= View::e($log['name'] ?? 'Unknown') ?></div>
                            <div style="font-size:11px;color:var(--text-secondary);"><?= View::e($log['email'] ?? '') ?></div>
                            <?php if (!empty($log['user_id'])): ?>
                                <div style="font-size:10px;color:var(--text-secondary);">ID: <?= $log['user_id'] ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $badgeClass ?>" style="font-size:11px;"><?= View::e($log['action']) ?></span>
                        </td>
                        <td style="font-family:monospace;font-size:12px;"><?= View::e($log['ip_address'] ?? '—') ?></td>
                        <td style="font-size:12px;white-space:nowrap;"><?= Helpers::formatDate($log['created_at'], 'M d, Y H:i') ?></td>
                        <td style="max-width:260px;">
                            <?php if (!empty($decodedData)): ?>
                                <div style="font-size:11px;color:var(--text-secondary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= View::e(json_encode($decodedData)) ?>">
                                    <?php foreach (array_slice($decodedData, 0, 3) as $k => $v): ?>
                                        <span style="background:var(--bg-secondary);padding:1px 5px;border-radius:3px;margin:1px;display:inline-block;">
                                            <?= View::e($k) ?>: <?= View::e(is_array($v) ? json_encode($v) : (string)$v) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <small style="color:var(--text-secondary);">—</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($pagination['total'] > 1): ?>
            <div style="display:flex;justify-content:center;gap:8px;padding:16px;">
                <?php
                $q = http_build_query([
                    'search'    => $search,
                    'action'    => $currentAction,
                    'category'  => $category,
                    'user_id'   => $currentUserId,
                    'date_from' => $dateFrom,
                    'date_to'   => $dateTo,
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
<?php View::endSection(); ?>

