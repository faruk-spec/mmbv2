<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1 style="margin:0;">Mail User Access</h1>
        <p style="color:var(--text-secondary);margin:4px 0 0;">Grant users access to <strong>/mail</strong> and assign them to specific email accounts</p>
    </div>
    <a href="/admin/mail/config" class="btn btn-secondary"><i class="fas fa-cog"></i> Mail Config</a>
</div>

<?php if (Helpers::hasFlash('success')): ?>
<div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
<div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Email</th>
                <th>Role</th>
                <th>Mail Access</th>
                <th>Assigned Providers</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td>
                    <div style="font-weight:500;"><?= View::e($u['name']) ?></div>
                </td>
                <td style="font-size:13px;color:var(--text-secondary);"><?= View::e($u['email']) ?></td>
                <td><span class="badge badge-<?= $u['role'] === 'admin' || $u['role'] === 'super_admin' ? 'success' : 'default' ?>"><?= View::e($u['role']) ?></span></td>
                <td>
                    <?php if ($u['role'] === 'admin' || $u['role'] === 'super_admin'): ?>
                    <span class="badge badge-success"><i class="fas fa-check"></i> Admin (always)</span>
                    <?php elseif ($u['has_mail_perm']): ?>
                    <span class="badge badge-success"><i class="fas fa-check"></i> Granted</span>
                    <?php else: ?>
                    <span class="badge badge-default"><i class="fas fa-times"></i> No access</span>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px;color:var(--text-secondary);">
                    <?= $u['assigned_providers'] ? View::e($u['assigned_providers']) : '<span style="color:#64748b;">none (uses global active)</span>' ?>
                </td>
                <td>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        <a href="/admin/mail/access/<?= (int)$u['id'] ?>/edit" class="btn btn-sm btn-secondary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <?php if ($u['has_mail_perm'] && $u['role'] !== 'admin' && $u['role'] !== 'super_admin'): ?>
                        <form method="POST" action="/admin/mail/access/<?= (int)$u['id'] ?>/revoke" onsubmit="return confirm('Revoke all mail access for this user?')">
                            <?= Security::csrfField() ?>
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-ban"></i> Revoke</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card" style="margin-top:20px;background:rgba(102,126,234,.06);border-color:rgba(102,126,234,.2);">
    <h3 style="margin:0 0 10px;font-size:14px;color:#a5b4fc;"><i class="fas fa-info-circle"></i> How Mail Access Works</h3>
    <ul style="font-size:13px;color:#94a3b8;margin:0;padding-left:20px;line-height:1.8;">
        <li><strong>Admin / Super Admin</strong> users always have access to <code>/mail</code>.</li>
        <li>Regular users need the <strong>mail</strong> permission granted here.</li>
        <li><strong>Assigned Providers</strong>: when a user has specific providers assigned, their inbox syncs from those IMAP accounts. Otherwise the global active provider is used.</li>
        <li>You can assign multiple providers to one user (e.g. sales@ and support@).</li>
    </ul>
</div>

<?php View::endSection(); ?>
