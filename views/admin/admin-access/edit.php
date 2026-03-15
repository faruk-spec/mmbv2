<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div style="margin-bottom:24px;">
    <a href="/admin/admin-access" style="color:var(--text-secondary);font-size:13px;">
        &larr; Back to Admin Users Access
    </a>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<!-- User header card -->
<div class="card" style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:16px;padding:4px 0;">
        <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--cyan),var(--magenta));display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:#000;flex-shrink:0;">
            <?= strtoupper(substr($targetUser['name'], 0, 1)) ?>
        </div>
        <div>
            <h2 style="font-size:1.1rem;font-weight:700;margin:0 0 2px;"><?= View::e($targetUser['name']) ?></h2>
            <p style="color:var(--text-secondary);font-size:12px;margin:0;"><?= View::e($targetUser['email']) ?></p>
        </div>
        <div style="margin-left:auto;font-size:12px;color:var(--text-secondary);">
            Role: <strong style="color:var(--cyan);"><?= View::e($targetUser['role']) ?></strong>
        </div>
        <a href="/admin/users/<?= $targetUser['id'] ?>/edit" class="btn btn-secondary btn-sm">
            <i class="fas fa-user-edit"></i> Edit User
        </a>
    </div>
</div>

<!-- Note for admin/super_admin roles -->
<?php if (in_array($targetUser['role'], ['admin', 'super_admin'])): ?>
<div class="alert alert-success" style="margin-bottom:16px;">
    <i class="fas fa-info-circle"></i>
    This user already has the <strong><?= View::e($targetUser['role']) ?></strong> role and can access the entire admin panel. 
    Permissions below are only enforced for users with the <strong>user</strong> or <strong>project_admin</strong> role.
</div>
<?php endif; ?>

<form method="POST" action="/admin/admin-access/<?= $targetUser['id'] ?>/save">
    <?= \Core\Security::csrfField() ?>

    <?php
    // Group permissions by their 'group' key
    $grouped = [];
    foreach ($permissions as $key => $perm) {
        $grouped[$perm['group']][$key] = $perm;
    }
    ?>

    <?php foreach ($grouped as $groupName => $groupPerms): ?>
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header">
            <h3 class="card-title">
                <?php
                $groupIcons = [
                    'Core'     => 'fas fa-home',
                    'Modules'  => 'fas fa-th-large',
                    'Security' => 'fas fa-shield-alt',
                    'Logs'     => 'fas fa-file-alt',
                    'Settings' => 'fas fa-cog',
                ];
                ?>
                <i class="<?= $groupIcons[$groupName] ?? 'fas fa-folder' ?>"></i>
                <?= htmlspecialchars($groupName) ?>
            </h3>
            <div style="margin-left:auto;display:flex;gap:8px;">
                <button type="button" class="btn btn-secondary btn-sm"
                        onclick="toggleGroup('<?= htmlspecialchars($groupName) ?>', true)">
                    <i class="fas fa-check-square"></i> All
                </button>
                <button type="button" class="btn btn-secondary btn-sm"
                        onclick="toggleGroup('<?= htmlspecialchars($groupName) ?>', false)">
                    <i class="fas fa-square"></i> None
                </button>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:10px;padding:4px 0;">
            <?php foreach ($groupPerms as $key => $perm): ?>
            <?php $isGranted = in_array($key, $grantedKeys, true); ?>
            <label class="perm-toggle <?= $groupName ?>"
                   style="display:flex;align-items:flex-start;gap:12px;padding:12px 14px;border-radius:10px;border:1px solid var(--border-color);cursor:pointer;transition:.15s;background:var(--bg-secondary);<?= $isGranted ? 'border-color:var(--cyan);background:rgba(0,240,255,.05);' : '' ?>">
                <input type="checkbox"
                       name="permissions[]"
                       value="<?= htmlspecialchars($key) ?>"
                       <?= $isGranted ? 'checked' : '' ?>
                       onchange="updateStyle(this)"
                       style="margin-top:2px;accent-color:var(--cyan);">
                <div>
                    <div style="display:flex;align-items:center;gap:7px;margin-bottom:3px;">
                        <i class="<?= htmlspecialchars($perm['icon']) ?>" style="color:var(--cyan);font-size:13px;width:14px;text-align:center;"></i>
                        <span style="font-weight:600;font-size:12px;"><?= htmlspecialchars($perm['label']) ?></span>
                    </div>
                    <p style="font-size:11px;color:var(--text-secondary);margin:0;line-height:1.4;">
                        <?= htmlspecialchars($perm['description']) ?>
                    </p>
                </div>
            </label>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div style="display:flex;gap:12px;margin-top:8px;">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Permissions
        </button>
        <a href="/admin/admin-access" class="btn btn-secondary">Cancel</a>
        <button type="button" class="btn btn-secondary" onclick="clearAll()">
            <i class="fas fa-times"></i> Clear All
        </button>
    </div>
</form>

<style>
.perm-toggle:hover { border-color: var(--cyan) !important; background: rgba(0,240,255,.04) !important; }
</style>

<script>
function updateStyle(checkbox) {
    const label = checkbox.closest('label');
    if (checkbox.checked) {
        label.style.borderColor = 'var(--cyan)';
        label.style.background  = 'rgba(0,240,255,.05)';
    } else {
        label.style.borderColor = 'var(--border-color)';
        label.style.background  = 'var(--bg-secondary)';
    }
}

function toggleGroup(group, state) {
    document.querySelectorAll('label.' + CSS.escape(group) + ' input[type=checkbox]').forEach(cb => {
        cb.checked = state;
        updateStyle(cb);
    });
}

function clearAll() {
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
        cb.checked = false;
        updateStyle(cb);
    });
}
</script>

<?php View::endSection(); ?>
