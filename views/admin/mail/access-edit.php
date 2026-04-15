<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <a href="/admin/mail/access" style="font-size:13px;color:#667eea;display:inline-flex;align-items:center;gap:6px;margin-bottom:6px;"><i class="fas fa-arrow-left"></i> Back to Mail Access</a>
        <h1 style="margin:0;"><?= View::e($title ?? 'Edit Mail Access') ?></h1>
        <p style="color:var(--text-secondary);margin:4px 0 0;"><?= View::e($targetUser['email']) ?></p>
    </div>
</div>

<?php if (Helpers::hasFlash('error')): ?>
<div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<form method="POST" action="/admin/mail/access/<?= (int)$targetUser['id'] ?>/save">
    <?= Security::csrfField() ?>

    <!-- Mail Permission Toggle -->
    <div class="card" style="margin-bottom:20px;">
        <h3 style="margin:0 0 16px;font-size:15px;">Mail Access Permission</h3>

        <?php if ($targetUser['role'] === 'super_admin'): ?>
        <div style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);border-radius:8px;padding:12px 16px;font-size:13px;color:#6ee7b7;">
            <i class="fas fa-check-circle"></i> This user is a <strong>Super Admin</strong> — they always have full mail access.
        </div>
        <?php else: ?>
        <label style="display:flex;align-items:center;gap:12px;cursor:pointer;padding:12px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:8px;">
            <input type="checkbox" name="grant_mail" value="1" <?= !empty($hasMailPerm) ? 'checked' : '' ?>
                   style="width:18px;height:18px;cursor:pointer;">
            <div>
                <div style="font-weight:500;">Grant <code>/mail</code> access</div>
                <div style="font-size:12px;color:#64748b;margin-top:2px;">Allows this user to open the mail module at /mail</div>
            </div>
        </label>
        <?php endif; ?>
    </div>

    <!-- Provider Assignments -->
    <div class="card" style="margin-bottom:20px;">
        <h3 style="margin:0 0 6px;font-size:15px;">Assigned Mail Accounts</h3>
        <p style="font-size:13px;color:#64748b;margin:0 0 16px;">Select which email accounts (providers) this user can send from and sync inbox with. If none selected, the global active provider is used.</p>

        <?php if (empty($providers)): ?>
        <div style="color:#94a3b8;font-size:13px;"><i class="fas fa-info-circle"></i> No mail providers configured yet. <a href="/admin/mail/config/create" style="color:#667eea;">Add a provider</a>.</div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <?php foreach ($providers as $p): ?>
            <label style="display:flex;align-items:center;gap:12px;cursor:pointer;padding:12px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:8px;<?= in_array($p['id'], $assignedProviderIds ?? [], false) ? 'border-color:rgba(102,126,234,.4);background:rgba(102,126,234,.08);' : '' ?>">
                <input type="checkbox" name="provider_ids[]" value="<?= (int)$p['id'] ?>"
                       <?= in_array((int)$p['id'], array_map('intval', $assignedProviderIds ?? []), true) ? 'checked' : '' ?>
                       style="width:16px;height:16px;cursor:pointer;">
                <div style="flex:1;">
                    <div style="font-weight:500;font-size:13px;">
                        <?= View::e($p['from_name'] ? $p['from_name'] . ' <' . $p['from_email'] . '>' : $p['from_email']) ?>
                        <span style="margin-left:6px;font-size:11px;padding:2px 6px;border-radius:4px;background:rgba(255,255,255,.07);color:#94a3b8;text-transform:uppercase;"><?= View::e($p['provider_type']) ?></span>
                        <?php if ($p['is_active']): ?>
                        <span style="margin-left:4px;font-size:11px;color:#6ee7b7;"><i class="fas fa-circle"></i> active</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($p['name']): ?>
                    <div style="font-size:12px;color:#64748b;"><?= View::e($p['name']) ?></div>
                    <?php endif; ?>
                </div>
                <div style="font-size:12px;color:#475569;">ID #<?= (int)$p['id'] ?></div>
            </label>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div style="display:flex;gap:10px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
        <a href="/admin/mail/access" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php View::endSection(); ?>
