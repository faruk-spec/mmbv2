<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
.notif-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: .72rem;
    font-weight: 600;
    white-space: nowrap;
}
.notif-unread { background: rgba(59,130,246,.12); color: var(--cyan); border: 1px solid rgba(59,130,246,.25); }
.notif-read   { background: rgba(255,255,255,.05); color: var(--text-secondary); border: 1px solid rgba(255,255,255,.1); }
.type-badge {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 8px;
    font-size: .7rem;
    font-weight: 600;
    background: rgba(168,85,247,.12);
    color: #a855f7;
    border: 1px solid rgba(168,85,247,.25);
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align: middle;
}
.notif-table th { color: var(--text-secondary); font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; padding: 10px 14px; border-bottom: 1px solid var(--border-color); }
.notif-table td { padding: 10px 14px; border-bottom: 1px solid rgba(255,255,255,.04); font-size: .85rem; vertical-align: middle; }
.notif-table tr:last-child td { border-bottom: none; }
.notif-table tr:hover td { background: rgba(255,255,255,.02); }
.notif-msg { max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text-primary); }
.notif-user { font-weight: 600; color: var(--text-primary); font-size: .83rem; }
.notif-email { font-size: .75rem; color: var(--text-secondary); margin-top: 1px; }
.filter-bar { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.pagination { display: flex; gap: 6px; justify-content: center; padding: 16px 0; }
.pagination a, .pagination span {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; border-radius: 6px;
    font-size: .8rem; text-decoration: none;
    border: 1px solid var(--border-color);
    color: var(--text-secondary);
}
.pagination a:hover { background: rgba(255,255,255,.06); color: var(--text-primary); }
.pagination .active { background: var(--cyan); color: #000; border-color: var(--cyan); font-weight: 700; }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div class="page-header mb-3">
    <div>
        <h1 class="page-title">All Notifications</h1>
        <p class="page-subtitle" style="color:var(--text-secondary);font-size:.9rem;">System-wide notification log with user &amp; email details</p>
    </div>
    <div style="display:flex;gap:10px;">
        <button onclick="deleteOldModal()" class="btn btn-secondary" style="font-size:.85rem;">
            <i class="fas fa-trash-alt"></i> Purge Old
        </button>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-4 mb-3">
    <div class="card" style="padding:16px 20px;text-align:center;">
        <div style="font-size:1.7rem;font-weight:700;color:var(--cyan);"><?= number_format($stats['total']) ?></div>
        <div style="color:var(--text-secondary);font-size:.8rem;margin-top:2px;">Total</div>
    </div>
    <div class="card" style="padding:16px 20px;text-align:center;">
        <div style="font-size:1.7rem;font-weight:700;color:var(--magenta);"><?= number_format($stats['unread']) ?></div>
        <div style="color:var(--text-secondary);font-size:.8rem;margin-top:2px;">Unread</div>
    </div>
    <div class="card" style="padding:16px 20px;text-align:center;">
        <div style="font-size:1.7rem;font-weight:700;color:var(--green);"><?= number_format($stats['today']) ?></div>
        <div style="color:var(--text-secondary);font-size:.8rem;margin-top:2px;">Today</div>
    </div>
    <div class="card" style="padding:16px 20px;text-align:center;">
        <div style="font-size:1.7rem;font-weight:700;color:var(--orange);"><?= number_format($totalPages) ?></div>
        <div style="color:var(--text-secondary);font-size:.8rem;margin-top:2px;">Pages (<?= $perPage ?>/page)</div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3" style="padding:14px 18px;">
    <form method="get" class="filter-bar">
        <select name="type" style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;padding:7px 12px;color:var(--text-primary);font-size:.85rem;min-width:180px;">
            <option value="">All Types</option>
            <?php foreach ($types as $t): ?>
            <option value="<?= htmlspecialchars($t['type']) ?>" <?= ($filters['type'] ?? '') === $t['type'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($t['type']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <select name="is_read" style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;padding:7px 12px;color:var(--text-primary);font-size:.85rem;">
            <option value="">All Status</option>
            <option value="0" <?= ($filters['is_read'] ?? '') === '0' ? 'selected' : '' ?>>Unread</option>
            <option value="1" <?= ($filters['is_read'] ?? '') === '1' ? 'selected' : '' ?>>Read</option>
        </select>
        <button type="submit" class="btn btn-primary" style="font-size:.85rem; padding: 7px 14px;">
            <i class="fas fa-filter"></i> Filter
        </button>
        <?php if (!empty($filters['type']) || $filters['is_read'] !== null): ?>
        <a href="/admin/notifications/all" class="btn btn-secondary" style="font-size:.85rem; padding: 7px 14px;">
            <i class="fas fa-times"></i> Clear
        </a>
        <?php endif; ?>
        <span style="margin-left:auto;font-size:.8rem;color:var(--text-secondary);">
            <?= number_format($total) ?> result<?= $total !== 1 ? 's' : '' ?>
        </span>
    </form>
</div>

<!-- Table -->
<div class="card">
    <?php if (empty($notifications)): ?>
    <div style="padding:48px;text-align:center;color:var(--text-secondary);">
        <i class="fas fa-bell-slash" style="font-size:2rem;margin-bottom:12px;display:block;opacity:.4;"></i>
        No notifications found.
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table class="notif-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Type</th>
                    <th>User / Email</th>
                    <th>Message</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notifications as $n): ?>
                <tr>
                    <td style="white-space:nowrap;color:var(--text-secondary);font-size:.78rem;">
                        <div><?= date('M j, Y', strtotime($n['created_at'])) ?></div>
                        <div><?= date('H:i:s', strtotime($n['created_at'])) ?></div>
                    </td>
                    <td>
                        <span class="type-badge" title="<?= htmlspecialchars($n['type']) ?>">
                            <?= htmlspecialchars($n['type']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="notif-user"><?= htmlspecialchars($n['user_name'] ?? '—') ?></div>
                        <?php if (!empty($n['email'])): ?>
                        <div class="notif-email">
                            <i class="fas fa-envelope" style="font-size:.65rem;margin-right:3px;"></i><?= htmlspecialchars($n['email']) ?>
                        </div>
                        <?php else: ?>
                        <div class="notif-email" style="opacity:.4;">No email</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="notif-msg" title="<?= htmlspecialchars($n['message']) ?>">
                            <?= htmlspecialchars($n['message']) ?>
                        </div>
                    </td>
                    <td>
                        <?php if ($n['is_read']): ?>
                        <span class="notif-badge notif-read">Read</span>
                        <?php else: ?>
                        <span class="notif-badge notif-unread">Unread</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>&type=<?= urlencode($filters['type'] ?? '') ?>&is_read=<?= urlencode($filters['is_read'] ?? '') ?>">
            <i class="fas fa-chevron-left"></i>
        </a>
        <?php endif; ?>
        <?php
        $start = max(1, $page - 2);
        $end   = min($totalPages, $page + 2);
        for ($i = $start; $i <= $end; $i++):
        ?>
        <a href="?page=<?= $i ?>&type=<?= urlencode($filters['type'] ?? '') ?>&is_read=<?= urlencode($filters['is_read'] ?? '') ?>"
           class="<?= $i === (int)$page ? 'active' : '' ?>">
            <?= $i ?>
        </a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>&type=<?= urlencode($filters['type'] ?? '') ?>&is_read=<?= urlencode($filters['is_read'] ?? '') ?>">
            <i class="fas fa-chevron-right"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Purge modal -->
<div id="purgeModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;display:none;align-items:center;justify-content:center;">
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:28px;max-width:400px;width:90%;">
        <h3 style="margin:0 0 12px;font-size:1.1rem;">Purge Old Read Notifications</h3>
        <p style="color:var(--text-secondary);font-size:.875rem;margin:0 0 16px;">Delete all read notifications older than X days.</p>
        <div style="display:flex;gap:10px;align-items:center;margin-bottom:20px;">
            <input type="number" id="purgeDays" value="30" min="1" max="365"
                   style="width:80px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;padding:8px 10px;color:var(--text-primary);font-size:.875rem;">
            <span style="color:var(--text-secondary);font-size:.875rem;">days old</span>
        </div>
        <div style="display:flex;gap:10px;">
            <button onclick="confirmPurge()" class="btn btn-danger" style="font-size:.875rem;" id="purgeBtn">
                <i class="fas fa-trash-alt"></i> Purge
            </button>
            <button onclick="document.getElementById('purgeModal').style.display='none'" class="btn btn-secondary" style="font-size:.875rem;">
                Cancel
            </button>
        </div>
        <div id="purgeResult" style="margin-top:12px;font-size:.85rem;display:none;"></div>
    </div>
</div>

<script>
function deleteOldModal() {
    document.getElementById('purgeModal').style.display = 'flex';
    document.getElementById('purgeResult').style.display = 'none';
}
function confirmPurge() {
    var days = document.getElementById('purgeDays').value;
    var btn = document.getElementById('purgeBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Purging…';
    fetch('/admin/notifications/delete-old', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_token=' + encodeURIComponent(document.querySelector('meta[name="csrf-token"]').content) + '&days=' + encodeURIComponent(days)
    })
    .then(r => r.json())
    .then(d => {
        var res = document.getElementById('purgeResult');
        res.style.display = 'block';
        if (d.success) {
            res.style.color = 'var(--green)';
            res.innerHTML = '<i class="fas fa-check-circle"></i> ' + (d.message || 'Done');
            setTimeout(() => location.reload(), 1200);
        } else {
            res.style.color = 'var(--red, #ef4444)';
            res.innerHTML = '<i class="fas fa-times-circle"></i> ' + (d.message || 'Error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash-alt"></i> Purge';
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash-alt"></i> Purge';
    });
}
</script>
<?php View::endSection(); ?>
