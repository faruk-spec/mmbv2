<?php use Core\View; use Core\Helpers;
$permsByGroup = [];
foreach ($permissions as $key => $perm) {
    $permsByGroup[$perm['group']][$key] = $perm;
}
?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<style>
.perm-tree-hdr{display:flex;align-items:center;justify-content:space-between;padding:10px 14px 8px;border-bottom:1px solid var(--border-color);background:var(--bg-secondary);border-radius:10px 10px 0 0;}
.perm-tree-hdr h4{margin:0;font-size:12px;font-weight:700;display:flex;align-items:center;gap:7px;}
.perm-group-card{border:1px solid var(--border-color);border-radius:10px;margin-bottom:14px;overflow:hidden;}
.perm-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:8px;padding:10px 14px;}
.perm-lbl{display:flex;align-items:flex-start;gap:10px;padding:9px 12px;border-radius:8px;border:1px solid var(--border-color);cursor:pointer;transition:.12s;background:var(--bg-secondary);position:relative;}
.perm-lbl.is-child{margin-left:22px;border-left:2px solid var(--border-color);}
.perm-lbl.granted{border-color:var(--cyan);background:rgba(0,240,255,.05);}
.perm-lbl:hover{border-color:var(--cyan);background:rgba(0,240,255,.04);}
.perm-lbl input[type=checkbox]{margin-top:1px;accent-color:var(--cyan);flex-shrink:0;}
.perm-lbl .pi{color:var(--cyan);font-size:12px;width:14px;text-align:center;flex-shrink:0;margin-top:1px;}
.perm-lbl .plabel{font-weight:600;font-size:11.5px;display:block;margin-bottom:2px;}
.perm-lbl .pdesc{font-size:10px;color:var(--text-secondary);display:block;line-height:1.4;}
.perm-parent-badge{font-size:9.5px;color:var(--text-secondary);background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:4px;padding:0 5px;margin-left:4px;white-space:nowrap;}
/* Quick filter */
#permFilter{padding:7px 12px;border-radius:8px;border:1px solid var(--border-color);background:var(--bg-secondary);color:inherit;font-size:12px;width:260px;outline:none;transition:.12s;}
#permFilter:focus{border-color:var(--cyan);}
/* Tree toggle group counts */
.group-count{font-size:10px;color:var(--text-secondary);font-weight:400;margin-left:4px;}
</style>

<div style="margin-bottom:20px;">
    <a href="/admin/admin-access" style="color:var(--text-secondary);font-size:13px;">&larr; Back to Admin Users Access</a>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<!-- User card -->
<div class="card" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:16px;padding:4px 0;">
        <div style="width:46px;height:46px;border-radius:50%;background:linear-gradient(135deg,var(--cyan),var(--magenta));display:flex;align-items:center;justify-content:center;font-size:17px;font-weight:700;color:#000;flex-shrink:0;">
            <?= strtoupper(substr($targetUser['name'], 0, 1)) ?>
        </div>
        <div>
            <h2 style="font-size:1.05rem;font-weight:700;margin:0 0 2px;"><?= View::e($targetUser['name']) ?></h2>
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

<?php if (in_array($targetUser['role'], ['admin', 'super_admin'])): ?>
<div class="alert alert-success" style="margin-bottom:16px;">
    <i class="fas fa-info-circle"></i>
    This user has the <strong><?= View::e($targetUser['role']) ?></strong> role and already has full admin panel access.
    Permissions here only restrict/expand access for <strong>user</strong> or <strong>project_admin</strong> roles.
</div>
<?php endif; ?>

<!-- Controls bar -->
<div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;flex-wrap:wrap;">
    <input type="text" id="permFilter" placeholder="&#128269; Filter permissions by name..." oninput="filterPerms(this.value)">
    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleAll(true)">
        <i class="fas fa-check-square"></i> Select All
    </button>
    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleAll(false)">
        <i class="fas fa-square"></i> Clear All
    </button>
    <span id="selectedCount" style="font-size:12px;color:var(--text-secondary);margin-left:auto;"></span>
</div>

<form method="POST" action="/admin/admin-access/<?= $targetUser['id'] ?>/save" id="permForm">
    <?= \Core\Security::csrfField() ?>

    <?php
    $groupIcons = [
        'Dashboard'          => 'fas fa-tachometer-alt',
        'QR Code Admin'      => 'fas fa-qrcode',
        'Platform Billing'   => 'fas fa-layer-group',
        'Projects'           => 'fas fa-th',
        'ConvertX'           => 'fas fa-file-export',
        'CodeXPro'           => 'fas fa-code',
        'ProShare'           => 'fas fa-share-alt',
        'BillX'              => 'fas fa-file-invoice',
        'WhatsApp'           => 'fab fa-whatsapp',
        'Management'         => 'fas fa-users-cog',
        'Security'           => 'fas fa-shield-alt',
        'Logs'               => 'fas fa-file-alt',
        'Advanced Features'  => 'fas fa-rocket',
        'System'             => 'fas fa-cog',
    ];
    foreach ($permsByGroup as $groupName => $groupPerms): ?>

    <div class="perm-group-card" data-group="<?= htmlspecialchars($groupName) ?>">
        <div class="perm-tree-hdr">
            <h4>
                <i class="<?= $groupIcons[$groupName] ?? 'fas fa-folder' ?>" style="color:var(--cyan);"></i>
                <?= htmlspecialchars($groupName) ?>
                <span class="group-count" id="gc_<?= preg_replace('/\W/','_',$groupName) ?>">
                    (<?= count(array_filter($groupPerms, fn($p) => in_array(array_search($p,$groupPerms), $grantedKeys,true))) ?>/<?= count($groupPerms) ?>)
                </span>
            </h4>
            <div style="display:flex;gap:6px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleGroup(<?= htmlspecialchars(json_encode($groupName)) ?>, true)">
                    All
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleGroup(<?= htmlspecialchars(json_encode($groupName)) ?>, false)">
                    None
                </button>
            </div>
        </div>

        <div class="perm-grid">
            <?php foreach ($groupPerms as $key => $perm):
                $isGranted = in_array($key, $grantedKeys, true);
                $isChild   = isset($perm['parent']);
                $cssClass  = 'perm-lbl' . ($isChild ? ' is-child' : '') . ($isGranted ? ' granted' : '');
            ?>
            <label class="<?= $cssClass ?>"
                   data-group="<?= htmlspecialchars($groupName) ?>"
                   data-search="<?= strtolower(htmlspecialchars($perm['label'] . ' ' . $key . ' ' . $perm['description'])) ?>">
                <input type="checkbox"
                       name="permissions[]"
                       value="<?= htmlspecialchars($key) ?>"
                       <?= $isGranted ? 'checked' : '' ?>
                       onchange="onPermChange(this)">
                <i class="pi <?= htmlspecialchars($perm['icon']) ?>"></i>
                <div>
                    <span class="plabel">
                        <?= htmlspecialchars($perm['label']) ?>
                        <?php if ($isChild): ?>
                            <span class="perm-parent-badge"><?= htmlspecialchars($perm['parent']) ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="pdesc"><?= htmlspecialchars($perm['description']) ?></span>
                </div>
            </label>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div style="display:flex;gap:12px;margin-top:8px;flex-wrap:wrap;">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Permissions
        </button>
        <a href="/admin/admin-access" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<script>
function onPermChange(cb) {
    const lbl = cb.closest('label');
    lbl.classList.toggle('granted', cb.checked);
    updateCounts();
}

function toggleAll(state) {
    document.querySelectorAll('.perm-lbl:not([style*="display: none"]) input[type=checkbox]').forEach(cb => {
        if (cb.offsetParent !== null) { // visible
            cb.checked = state;
            cb.closest('label').classList.toggle('granted', state);
        }
    });
    updateCounts();
}

function toggleGroup(group, state) {
    document.querySelectorAll('.perm-lbl[data-group="'+group+'"] input[type=checkbox]').forEach(cb => {
        cb.checked = state;
        cb.closest('label').classList.toggle('granted', state);
    });
    updateCounts();
}

function filterPerms(q) {
    q = q.toLowerCase().trim();
    document.querySelectorAll('.perm-group-card').forEach(card => {
        let anyVisible = false;
        card.querySelectorAll('.perm-lbl').forEach(lbl => {
            const matches = !q || lbl.dataset.search.includes(q);
            lbl.style.display = matches ? '' : 'none';
            if (matches) anyVisible = true;
        });
        card.style.display = anyVisible ? '' : 'none';
    });
}

function updateCounts() {
    const total   = document.querySelectorAll('.perm-lbl input[type=checkbox]').length;
    const checked = document.querySelectorAll('.perm-lbl input[type=checkbox]:checked').length;
    document.getElementById('selectedCount').textContent = checked + ' / ' + total + ' permissions selected';
}

document.addEventListener('DOMContentLoaded', updateCounts);
</script>
<?php View::endSection(); ?>
