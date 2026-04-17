<?php use Core\View; use Core\Helpers; ?>
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
#permFilter{padding:7px 12px;border-radius:8px;border:1px solid var(--border-color);background:var(--bg-secondary);color:inherit;font-size:12px;width:260px;outline:none;transition:.12s;}
#permFilter:focus{border-color:var(--cyan);}
.group-count{font-size:10px;color:var(--text-secondary);font-weight:400;margin-left:4px;}
</style>

<div style="margin-bottom: 30px;">
    <a href="/admin/roles" style="color: var(--text-secondary);">&larr; Back to Roles</a>
    <h1 style="margin-top: 10px;"><?= View::e($title) ?></h1>
</div>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<form method="POST" action="<?= View::e($action) ?>" id="roleForm">
    <?= \Core\Security::csrfField() ?>

    <!-- Basic settings card -->
    <div class="card" style="max-width: 700px; margin-bottom: 24px;">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-tag"></i> Role Details</h3></div>

        <div class="form-group">
            <label class="form-label" for="name">Role Name <span style="color: var(--danger);">*</span></label>
            <input type="text" id="name" name="name" class="form-input"
                   value="<?= View::e($role['name'] ?? '') ?>" required maxlength="100">
        </div>

        <div class="form-group">
            <label class="form-label" for="slug">Slug <span style="color: var(--danger);">*</span></label>
            <input type="text" id="slug" name="slug" class="form-input"
                   value="<?= View::e($role['slug'] ?? '') ?>"
                   <?= (isset($role) && $role && $role['is_system']) ? 'readonly' : 'required' ?>
                   placeholder="e.g. moderator" maxlength="100">
            <div style="margin-top: 6px; font-size: 13px; color: var(--text-secondary);">
                <?php if (isset($role) && $role && $role['is_system']): ?>
                    Slugs for system roles cannot be changed.
                <?php else: ?>
                    Lowercase letters, numbers and underscores only. Used as the internal identifier.
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="description">Description</label>
            <textarea id="description" name="description" class="form-input" rows="3" maxlength="500"><?= View::e($role['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Color</label>
            <div style="display: flex; align-items: center; gap: 12px;">
                <input type="color" id="color" name="color"
                       style="width: 60px; height: 40px; padding: 2px 4px; cursor: pointer; border: 1px solid var(--border); border-radius: 6px; background: transparent;"
                       value="<?= View::e($role['color'] ?? '#9945ff') ?>">
                <input type="text" id="colorHex" class="form-input" style="max-width: 120px;"
                       value="<?= View::e($role['color'] ?? '#9945ff') ?>" maxlength="7"
                       placeholder="#rrggbb"
                       oninput="syncColorPicker(this.value)">
            </div>
        </div>

        <div style="display: flex; gap: 20px;">
            <div class="form-group" style="flex: 1;">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-input">
                    <option value="active"   <?= (($role['status'] ?? 'active') === 'active')   ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= (($role['status'] ?? '')        === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="form-group" style="max-width: 130px;">
                <label class="form-label" for="sort_order">Sort Order</label>
                <input type="number" id="sort_order" name="sort_order" class="form-input"
                       value="<?= (int) ($role['sort_order'] ?? 0) ?>" min="0">
            </div>
        </div>
    </div>

    <!-- Permissions section -->
    <?php if (!empty($permissions)):
        $permsByGroup = [];
        foreach ($permissions as $key => $perm) {
            $permsByGroup[$perm['group']][$key] = $perm;
        }
        $groupIcons = [
            'Dashboard'         => 'fas fa-tachometer-alt',
            'QR Code Admin'     => 'fas fa-qrcode',
            'Platform Billing'  => 'fas fa-layer-group',
            'Projects'          => 'fas fa-th',
            'ConvertX'          => 'fas fa-file-export',
            'CodeXPro'          => 'fas fa-code',
            'ProShare'          => 'fas fa-share-alt',
            'BillX'             => 'fas fa-file-invoice',
            'WhatsApp'          => 'fab fa-whatsapp',
            'Management'        => 'fas fa-users-cog',
            'Security'          => 'fas fa-shield-alt',
            'Logs'              => 'fas fa-file-alt',
            'Advanced Features' => 'fas fa-rocket',
            'System'            => 'fas fa-cog',
        ];
    ?>
    <div style="margin-bottom: 14px;">
        <h2 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 6px;">
            <i class="fas fa-shield-alt" style="color: var(--cyan);"></i> Admin Panel Permissions
        </h2>
        <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 14px;">
            Users assigned this role will inherit these permissions (individual user overrides take precedence).
        </p>

        <!-- Controls -->
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;flex-wrap:wrap;">
            <input type="text" id="permFilter" placeholder="&#128269; Filter permissions..." oninput="filterPerms(this.value)">
            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleAll(true)">
                <i class="fas fa-check-square"></i> Select All
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleAll(false)">
                <i class="fas fa-square"></i> Clear All
            </button>
            <span id="selectedCount" style="font-size:12px;color:var(--text-secondary);margin-left:auto;"></span>
        </div>

        <?php foreach ($permsByGroup as $groupName => $groupPerms): ?>
        <div class="perm-group-card" data-group="<?= htmlspecialchars($groupName) ?>">
            <div class="perm-tree-hdr">
                <h4>
                    <i class="<?= $groupIcons[$groupName] ?? 'fas fa-folder' ?>" style="color:var(--cyan);"></i>
                    <?= htmlspecialchars($groupName) ?>
                    <span class="group-count" id="gc_<?= preg_replace('/\W/', '_', $groupName) ?>">
                        (<?= count(array_filter(array_keys($groupPerms), fn($k) => in_array($k, $grantedKeys, true))) ?>/<?= count($groupPerms) ?>)
                    </span>
                </h4>
                <div style="display:flex;gap:6px;">
                    <button type="button" class="btn btn-secondary btn-sm"
                            onclick="toggleGroup(<?= htmlspecialchars(json_encode($groupName)) ?>, true)">All</button>
                    <button type="button" class="btn btn-secondary btn-sm"
                            onclick="toggleGroup(<?= htmlspecialchars(json_encode($groupName)) ?>, false)">None</button>
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
    <?php endif; ?>

    <div style="display: flex; gap: 15px; margin-top: 20px;">
        <button type="submit" class="btn btn-primary"><?= ($role ? 'Update Role' : 'Create Role') ?></button>
        <a href="/admin/roles" class="btn btn-secondary">Cancel</a>
    </div>

</form>

<script>
    function syncColorPicker(hex) {
        if (/^#[0-9a-fA-F]{6}$/.test(hex)) {
            document.getElementById('color').value = hex;
        }
    }
    document.getElementById('color').addEventListener('input', function () {
        document.getElementById('colorHex').value = this.value;
    });

    <?php if (!isset($role) || !$role || !$role['is_system']): ?>
    var slugEdited = <?= ($role && !empty($role['slug'])) ? 'true' : 'false' ?>;
    document.getElementById('slug').addEventListener('input', function () { slugEdited = true; });
    document.getElementById('name').addEventListener('input', function () {
        if (!slugEdited) {
            document.getElementById('slug').value = this.value
                .toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
        }
    });
    <?php endif; ?>

    // ── Permission helpers ──────────────────────────────────────────────────
    function updateCount() {
        var all  = document.querySelectorAll('.perm-lbl input[type=checkbox]');
        var checked = document.querySelectorAll('.perm-lbl input[type=checkbox]:checked');
        document.getElementById('selectedCount').textContent = checked.length + ' / ' + all.length + ' selected';

        // Per-group counts
        document.querySelectorAll('.perm-group-card').forEach(function(card) {
            var g = card.dataset.group;
            var total   = card.querySelectorAll('input[type=checkbox]').length;
            var granted = card.querySelectorAll('input[type=checkbox]:checked').length;
            var id = 'gc_' + g.replace(/\W/g, '_');
            var el = document.getElementById(id);
            if (el) el.textContent = '(' + granted + '/' + total + ')';
        });
    }

    function onPermChange(cb) {
        var lbl = cb.closest('.perm-lbl');
        if (cb.checked) lbl.classList.add('granted');
        else lbl.classList.remove('granted');
        updateCount();
    }

    function toggleAll(state) {
        document.querySelectorAll('.perm-lbl').forEach(function(lbl) {
            if (lbl.style.display !== 'none') {
                var cb = lbl.querySelector('input[type=checkbox]');
                cb.checked = state;
                state ? lbl.classList.add('granted') : lbl.classList.remove('granted');
            }
        });
        updateCount();
    }

    function toggleGroup(groupName, state) {
        document.querySelectorAll('.perm-lbl[data-group="' + CSS.escape(groupName) + '"]').forEach(function(lbl) {
            var cb = lbl.querySelector('input[type=checkbox]');
            cb.checked = state;
            state ? lbl.classList.add('granted') : lbl.classList.remove('granted');
        });
        updateCount();
    }

    function filterPerms(q) {
        q = q.toLowerCase();
        document.querySelectorAll('.perm-lbl').forEach(function(lbl) {
            lbl.style.display = (!q || (lbl.dataset.search || '').includes(q)) ? '' : 'none';
        });
    }

    updateCount();
</script>
<?php View::endSection(); ?>

