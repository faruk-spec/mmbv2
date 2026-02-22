<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
.role-table td, .role-table th { text-align:center; vertical-align:middle; }
.role-table td:first-child, .role-table th:first-child { text-align:left; }
.toggle-cell label { display:inline-block; cursor:pointer; }
.toggle-switch { position:relative; width:44px; height:22px; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider { position:absolute; top:0;left:0;right:0;bottom:0; background:#444; border-radius:22px; transition:.2s; }
.toggle-slider:before { content:""; position:absolute; width:16px; height:16px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.2s; }
input:checked + .toggle-slider { background:var(--cyan); }
input:checked + .toggle-slider:before { transform:translateX(22px); }
.section-title { font-size:1.1rem; font-weight:600; margin:0 0 16px; color:var(--text-primary); border-left:3px solid var(--cyan); padding-left:10px; }
.role-feedback { position:fixed; bottom:20px; right:20px; padding:10px 18px; border-radius:8px; font-size:13px; font-weight:600; z-index:9999; opacity:0; transition:opacity .3s; pointer-events:none; }
.role-feedback.show { opacity:1; }
.role-feedback.ok  { background:rgba(0,255,136,.15); border:1px solid var(--green); color:var(--green); }
.role-feedback.err { background:rgba(255,107,107,.15); border:1px solid var(--red); color:var(--red); }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>

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

<!-- ========== ROLE FEATURE MATRIX ========== -->
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users-cog"></i> Role Feature Permissions</h3>
        <span style="font-size:12px;color:var(--text-secondary);">Toggle features per role — changes save instantly.</span>
    </div>
    <div style="overflow-x:auto;">
        <table class="table role-table">
            <thead>
                <tr>
                    <th style="min-width:200px;">Feature</th>
                    <?php foreach ($roles as $role): ?>
                        <th style="min-width:120px;">
                            <span class="badge badge-info"><?= View::e($roleLabels[$role] ?? ucfirst($role)) ?></span>
                            <div style="font-size:10px;color:var(--text-secondary);margin-top:2px;"><?= View::e($role) ?></div>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allFeatures as $featureKey => $featureLabel): ?>
                    <tr>
                        <td><?= View::e($featureLabel) ?></td>
                        <?php foreach ($roles as $role): ?>
                            <?php $enabled = $roleFeatures[$role][$featureKey] ?? false; ?>
                            <td class="toggle-cell">
                                <label class="toggle-switch">
                                    <input type="checkbox" <?= $enabled ? 'checked' : '' ?>
                                        onchange="setRoleFeature('<?= $role ?>','<?= $featureKey ?>',this)">
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ========== USER PLAN ASSIGNMENT ========== -->
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-crown"></i> Assign Plan to User</h3>
    </div>
    <form method="POST" action="/admin/qr/roles/assign-plan" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;padding:8px 0;">
        <?= \Core\Security::csrfField() ?>
        <div style="flex:2;min-width:200px;">
            <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">Select User</label>
            <select name="user_id" class="form-input" required>
                <option value="">— Choose user —</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>">
                        <?= View::e($u['name']) ?> (<?= View::e($u['email']) ?>) —
                        <?= isset($userPlanMap[$u['id']]) ? View::e($userPlanMap[$u['id']]['plan_name']) : 'No plan' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="flex:1;min-width:160px;">
            <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">Plan</label>
            <select name="plan_id" class="form-input">
                <option value="">— Choose plan —</option>
                <?php foreach ($plans as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= View::e($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Assign Plan</button>
        </div>
    </form>
</div>

<!-- ========== USER FEATURE OVERRIDES ========== -->
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-cog"></i> Per-User Feature Overrides</h3>
        <span style="font-size:12px;color:var(--text-secondary);">Override specific features for individual users regardless of their plan/role.</span>
    </div>

    <!-- Add override form -->
    <div style="padding:12px 0;border-bottom:1px solid var(--border-color);">
        <p class="section-title">Add / Update Override</p>
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;" id="addOverrideForm">
            <div style="flex:2;min-width:200px;">
                <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">User</label>
                <select id="ov_user" class="form-input">
                    <option value="">— Select user —</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= View::e($u['name']) ?> (<?= View::e($u['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex:2;min-width:180px;">
                <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">Feature</label>
                <select id="ov_feature" class="form-input">
                    <?php foreach ($allFeatures as $k => $label): ?>
                        <option value="<?= $k ?>"><?= View::e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex:1;min-width:120px;">
                <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">Access</label>
                <select id="ov_enabled" class="form-input">
                    <option value="1">Enable</option>
                    <option value="0">Disable</option>
                </select>
            </div>
            <div>
                <button type="button" class="btn btn-primary" onclick="applyUserOverride()"><i class="fas fa-save"></i> Apply</button>
            </div>
        </div>
    </div>

    <!-- Existing overrides -->
    <?php if (empty($userFeatures)): ?>
        <p style="color:var(--text-secondary);text-align:center;padding:20px;">No per-user overrides configured yet.</p>
    <?php else: ?>
        <table class="table" style="margin-top:16px;">
            <thead>
                <tr><th>User</th><th>Features</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($userFeatures as $uid => $uf): ?>
                    <tr>
                        <td>
                            <div style="font-weight:500;"><?= View::e($uf['user_name']) ?></div>
                            <div style="font-size:11px;color:var(--text-secondary);"><?= View::e($uf['user_email']) ?></div>
                        </td>
                        <td>
                            <?php foreach ($uf['features'] as $fk => $fv): ?>
                                <span class="badge <?= $fv ? 'badge-success' : 'badge-danger' ?>" style="margin:2px;">
                                    <?= View::e($allFeatures[$fk] ?? $fk) ?>: <?= $fv ? 'ON' : 'OFF' ?>
                                </span>
                            <?php endforeach; ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger"
                                onclick="removeUserOverrides(<?= $uid ?>)"
                                title="Remove all overrides for this user">
                                <i class="fas fa-trash-alt"></i> Remove All
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Toast feedback -->
<div class="role-feedback" id="roleFeedback"></div>

<!-- Hidden CSRF token for JS requests -->
<input type="hidden" id="csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">

<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<script>
const csrfToken = document.getElementById('csrf_token').value;

function showFeedback(msg, ok) {
    const el = document.getElementById('roleFeedback');
    el.textContent = msg;
    el.className = 'role-feedback show ' + (ok ? 'ok' : 'err');
    clearTimeout(el._t);
    el._t = setTimeout(() => { el.className = 'role-feedback'; }, 2500);
}

function setRoleFeature(role, feature, checkbox) {
    const enabled = checkbox.checked;
    const fd = new FormData();
    fd.append('role', role);
    fd.append('feature', feature);
    fd.append('enabled', enabled ? '1' : '0');
    fd.append('_csrf_token', csrfToken);

    fetch('/admin/qr/roles/set-role-feature', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showFeedback('✓ Saved: ' + role + ' / ' + feature, true);
            } else {
                checkbox.checked = !enabled;
                showFeedback('Error: ' + (data.message || 'Unknown error'), false);
            }
        })
        .catch(() => {
            checkbox.checked = !enabled;
            showFeedback('Network error — please retry.', false);
        });
}

function applyUserOverride() {
    const userId  = document.getElementById('ov_user').value;
    const feature = document.getElementById('ov_feature').value;
    const enabled = document.getElementById('ov_enabled').value;

    if (!userId) { showFeedback('Please select a user.', false); return; }

    const fd = new FormData();
    fd.append('user_id', userId);
    fd.append('feature', feature);
    fd.append('enabled', enabled);
    fd.append('_csrf_token', csrfToken);

    fetch('/admin/qr/roles/set-user-feature', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showFeedback('Error: ' + (data.message || 'Unknown error'), false);
            }
        })
        .catch(() => showFeedback('Network error — please retry.', false));
}

function removeUserOverrides(userId) {
    if (!confirm('Remove all feature overrides for this user?')) return;

    const fd = new FormData();
    fd.append('user_id', userId);
    fd.append('_csrf_token', csrfToken);

    fetch('/admin/qr/roles/remove-user-features', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showFeedback('Error: ' + (data.message || 'Unknown error'), false);
            }
        })
        .catch(() => showFeedback('Network error — please retry.', false));
}
</script>
<?php View::endSection(); ?>

