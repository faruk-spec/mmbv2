<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<!-- Stats -->
<div class="grid grid-4 mb-3">
    <div class="stat-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:var(--cyan);"><?= (int)($stats['total'] ?? 0) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Total QR Codes</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:var(--green);"><?= (int)($stats['active'] ?? 0) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Active</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:var(--red);"><?= (int)($stats['blocked'] ?? 0) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Blocked</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:var(--orange);"><?= number_format($stats['total_scans'] ?? 0) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Total Scans</div>
    </div>
</div>

<!-- Flash messages -->
<?php if (Helpers::hasFlash('success')): ?>
    <div style="background:rgba(0,255,136,.1);border:1px solid var(--green);color:var(--green);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
        <i class="fas fa-check-circle"></i> <?= View::e(Helpers::getFlash('success')) ?>
    </div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
    <div style="background:rgba(255,107,107,.1);border:1px solid var(--red);color:var(--red);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
        <i class="fas fa-exclamation-circle"></i> <?= View::e(Helpers::getFlash('error')) ?>
    </div>
<?php endif; ?>

<!-- Filters -->
<div class="card mb-3">
    <form method="GET" action="/admin/qr" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:200px;">
            <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Content, user name or email…" value="<?= View::e($search) ?>">
        </div>
        <div>
            <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">Type</label>
            <select name="type" class="form-control">
                <option value="">All Types</option>
                <?php foreach (['url','text','phone','email','whatsapp','wifi','location','vcard','payment','event','product'] as $t): ?>
                    <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">Status</label>
            <select name="status" class="form-control">
                <option value="">All</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="blocked" <?= $status === 'blocked' ? 'selected' : '' ?>>Blocked</option>
            </select>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="/admin/qr" class="btn btn-secondary">Clear</a>
        </div>
    </form>
</div>

<!-- QR Codes Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-qrcode"></i> All QR Codes</h3>
    </div>

    <?php if (empty($qrCodes)): ?>
        <p style="color:var(--text-secondary);text-align:center;padding:30px;">No QR codes found.</p>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Content</th>
                        <th>Type</th>
                        <th>Dynamic</th>
                        <th>Scans</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($qrCodes as $qr): ?>
                        <tr>
                            <td><?= $qr['id'] ?></td>
                            <td>
                                <div style="font-size:13px;font-weight:500;"><?= View::e($qr['user_name'] ?? '—') ?></div>
                                <div style="font-size:11px;color:var(--text-secondary);"><?= View::e($qr['user_email'] ?? '') ?></div>
                            </td>
                            <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <span title="<?= View::e($qr['content']) ?>"><?= View::e(substr($qr['content'], 0, 60)) ?><?= strlen($qr['content']) > 60 ? '…' : '' ?></span>
                            </td>
                            <td><span class="badge badge-info"><?= View::e($qr['type']) ?></span></td>
                            <td><?= $qr['is_dynamic'] ? '<span class="badge badge-warning">Dynamic</span>' : '<span class="badge" style="background:var(--bg-secondary);color:var(--text-secondary);">Static</span>' ?></td>
                            <td><?= number_format($qr['scan_count']) ?></td>
                            <td>
                                <?php
                                $sBadge = ['active' => 'badge-success', 'inactive' => 'badge-warning', 'blocked' => 'badge-danger'];
                                ?>
                                <span class="badge <?= $sBadge[$qr['status']] ?? 'badge-info' ?>"><?= ucfirst($qr['status']) ?></span>
                            </td>
                            <td style="font-size:12px;"><?= date('M j, Y', strtotime($qr['created_at'])) ?></td>
                            <td>
                                <?php if ($qr['status'] !== 'blocked'): ?>
                                    <form method="POST" action="/admin/qr/<?= $qr['id'] ?>/block" style="display:inline;">
                                        <?= \Core\Security::csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Block this QR code?')"
                                            title="Block QR"><i class="fas fa-ban"></i></button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="/admin/qr/<?= $qr['id'] ?>/unblock" style="display:inline;">
                                        <?= \Core\Security::csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-secondary" title="Unblock QR"><i class="fas fa-check-circle"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['total'] > 1): ?>
            <div style="display:flex;gap:8px;justify-content:center;padding:16px;">
                <?php for ($i = 1; $i <= $pagination['total']; $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($type) ?>&status=<?= urlencode($status) ?>"
                       class="btn btn-sm <?= $i === $pagination['current'] ? 'btn-primary' : 'btn-secondary' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php View::endSection(); ?>
