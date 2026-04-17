<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <div>
        <h2 style="font-size:1.3rem;font-weight:700;margin:0 0 4px;">
            <i class="fas fa-user-shield" style="color:var(--cyan);margin-right:8px;"></i>Admin Users Access
        </h2>
        <p style="color:var(--text-secondary);font-size:13px;margin:0;">
            Grant users granular access to specific admin panel features and modules.
        </p>
    </div>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users"></i> Users</h3>
        <div style="margin-left:auto;">
            <input type="text" id="userSearch" placeholder="Search users…"
                   style="padding:6px 12px;border-radius:8px;border:1px solid var(--border-color);background:var(--bg-secondary);color:inherit;font-size:12px;width:200px;"
                   oninput="filterUsers(this.value)">
        </div>
    </div>

    <table style="width:100%;border-collapse:collapse;font-size:13px;" id="userTable">
        <thead>
            <tr style="border-bottom:2px solid var(--border-color);">
                <th style="padding:10px 14px;text-align:left;font-weight:600;color:var(--text-secondary);">Name</th>
                <th style="padding:10px 14px;text-align:left;font-weight:600;color:var(--text-secondary);">Email</th>
                <th style="padding:10px 14px;text-align:left;font-weight:600;color:var(--text-secondary);">Role</th>
                <th style="padding:10px 14px;text-align:left;font-weight:600;color:var(--text-secondary);">Status</th>
                <th style="padding:10px 14px;text-align:left;font-weight:600;color:var(--text-secondary);">Granted Permissions</th>
                <th style="padding:10px 14px;text-align:left;font-weight:600;color:var(--text-secondary);">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr class="user-row" data-search="<?= strtolower(View::e($u['name']) . ' ' . View::e($u['email'])) ?>"
                style="border-bottom:1px solid var(--border-color);transition:.1s;">
                <td style="padding:10px 14px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:32px;height:32px;border-radius:50%;background:var(--cyan);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0;">
                            <?= strtoupper(substr($u['name'], 0, 1)) ?>
                        </div>
                        <span style="font-weight:500;"><?= View::e($u['name']) ?></span>
                    </div>
                </td>
                <td style="padding:10px 14px;color:var(--text-secondary);"><?= View::e($u['email']) ?></td>
                <td style="padding:10px 14px;">
                    <?php
                    $roleColors = ['super_admin'=>'#8b5cf6','admin'=>'#3b82f6','project_admin'=>'#f59e0b','user'=>'#8892a6'];
                    $roleLabels = ['super_admin'=>'Owner','admin'=>'Admin','project_admin'=>'Manager','user'=>'User'];
                    $rc = $roleColors[$u['role']] ?? '#8892a6';
                    $rl = $roleLabels[$u['role']] ?? $u['role'];
                    ?>
                    <span style="font-size:11px;padding:2px 8px;border-radius:20px;background:<?= $rc ?>22;color:<?= $rc ?>;font-weight:600;">
                        <?= View::e($rl) ?>
                    </span>
                </td>
                <td style="padding:10px 14px;">
                    <?php $sc = $u['status'] === 'active' ? 'var(--green)' : ($u['status'] === 'banned' ? 'var(--red)' : 'var(--text-secondary)'); ?>
                    <span style="font-size:11px;color:<?= $sc ?>;font-weight:600;"><?= View::e(ucfirst($u['status'])) ?></span>
                </td>
                <td style="padding:10px 14px;">
                    <?php if ($u['perm_count'] > 0): ?>
                        <span style="font-size:11px;padding:2px 8px;border-radius:20px;background:rgba(59,130,246,.1);color:var(--cyan);font-weight:600;">
                            <?= (int)$u['perm_count'] ?> permission<?= $u['perm_count'] != 1 ? 's' : '' ?>
                        </span>
                    <?php else: ?>
                        <span style="font-size:11px;color:var(--text-secondary);">No custom permissions</span>
                    <?php endif; ?>
                </td>
                <td style="padding:10px 14px;">
                    <a href="/admin/admin-access/<?= $u['id'] ?>/edit" class="btn btn-secondary btn-sm">
                        <i class="fas fa-shield-alt"></i> Manage Access
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function filterUsers(q) {
    q = q.toLowerCase().trim();
    document.querySelectorAll('.user-row').forEach(row => {
        row.style.display = (!q || row.dataset.search.includes(q)) ? '' : 'none';
    });
}
</script>

<?php View::endSection(); ?>
