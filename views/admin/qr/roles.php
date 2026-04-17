<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
.section-title { font-size:1.05rem; font-weight:600; margin:0 0 14px; color:var(--text-primary); border-left:3px solid var(--cyan); padding-left:10px; }
.toggle-switch { position:relative; width:44px; height:22px; display:inline-block; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider { position:absolute; top:0;left:0;right:0;bottom:0; background:#444; border-radius:22px; transition:.2s; cursor:pointer; }
.toggle-slider:before { content:""; position:absolute; width:16px; height:16px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.2s; }
input:checked + .toggle-slider { background:var(--cyan); }
input:checked + .toggle-slider:before { transform:translateX(22px); }
.feat-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:10px; }
.feat-row { display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:var(--bg-secondary,rgba(255,255,255,.03)); border-radius:8px; font-size:13px; }
.feat-row .feat-label { color:var(--text-primary); }
.role-feedback { position:fixed; bottom:20px; right:20px; padding:10px 18px; border-radius:8px; font-size:13px; font-weight:600; z-index:9999; opacity:0; transition:opacity .3s; pointer-events:none; }
.role-feedback.show { opacity:1; }
.role-feedback.ok  { background:rgba(0,255,136,.15); border:1px solid var(--green); color:var(--green); }
.role-feedback.err { background:rgba(255,107,107,.15); border:1px solid var(--red); color:var(--red); }
#userFeaturePanel { display:none; }
.role-table td, .role-table th { text-align:center; vertical-align:middle; }
.role-table td:first-child, .role-table th:first-child { text-align:left; }
details summary { cursor:pointer; user-select:none; padding:8px 0; font-size:13px; color:var(--text-secondary); }
details summary:hover { color:var(--cyan); }
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

<!-- ========== USER FEATURE MANAGER (primary UI) ========== -->
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-cog"></i> Per-User Feature Overrides</h3>
        <span style="font-size:12px;color:var(--text-secondary);">Select a user to view and override their effective feature access. Overrides take priority over plan &amp; role defaults.</span>
    </div>

    <!-- User selector -->
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;margin-bottom:16px;">
        <div style="flex:3;min-width:220px;">
            <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">Select User</label>
            <select id="featureUserId" class="form-input" onchange="loadUserFeatures(this.value)">
                <option value="">— Choose a user to manage —</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>"
                            data-role="<?= View::e($u['role']) ?>"
                            data-plan="<?= View::e($userPlanMap[$u['id']]['plan_name'] ?? 'No plan') ?>">
                        <?= View::e($u['name']) ?> (<?= View::e($u['email']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="userInfoBadges" style="display:none;align-items:center;gap:8px;flex-wrap:wrap;">
            <span style="font-size:12px;color:var(--text-secondary);">Role:</span>
            <span id="uRoleBadge" class="badge badge-info"></span>
            <span style="font-size:12px;color:var(--text-secondary);">Plan:</span>
            <span id="uPlanBadge" class="badge badge-warning"></span>
            <button type="button" class="btn btn-sm btn-danger" id="removeAllOverridesBtn"
                    onclick="removeUserOverrides(document.getElementById('featureUserId').value)"
                    style="margin-left:8px;" title="Remove all overrides for this user">
                <i class="fas fa-trash-alt"></i> Clear All Overrides
            </button>
        </div>
    </div>

    <!-- Per-user feature toggles (shown after user is selected) -->
    <div id="userFeaturePanel">
        <!-- USE PLAN SETTINGS master toggle -->
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:rgba(0,212,255,.06);border:1px solid rgba(0,212,255,.2);border-radius:8px;margin-bottom:14px;">
            <div>
                <strong style="font-size:13px;"><i class="fas fa-crown" style="color:var(--cyan);margin-right:6px;"></i> Use Plan Settings</strong>
                <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">
                    <span id="usePlanHint">ON = plan features apply (overrides disabled) &bull; OFF = custom overrides apply (plan ignored)</span>
                </div>
            </div>
            <label class="toggle-switch" title="Use Plan Settings">
                <input type="checkbox" id="usePlanToggle" checked
                       onchange="setUsePlanMode(document.getElementById('featureUserId').value, this)">
                <span class="toggle-slider"></span>
            </label>
        </div>

        <p class="section-title" style="margin-bottom:12px;">Effective Feature Access <small style="font-size:12px;font-weight:400;color:var(--text-secondary);">(resolved: role &rarr; plan &rarr; per-user)</small></p>
        <div class="feat-grid" id="userFeatureGrid">
            <?php foreach ($allFeatures as $fk => $flabel): ?>
                <div class="feat-row" id="feat_row_<?= $fk ?>">
                    <span class="feat-label"><?= View::e($flabel) ?></span>
                    <label class="toggle-switch" title="<?= View::e($fk) ?>">
                        <input type="checkbox" id="feat_<?= $fk ?>"
                               onchange="applyUserFeature(document.getElementById('featureUserId').value,'<?= $fk ?>',this)">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
        <p style="margin-top:12px;font-size:11px;color:var(--text-secondary);">
            <i class="fas fa-info-circle"></i>
            When <strong>Use Plan Settings</strong> is ON, toggles show effective plan+role features (read-only).
            Switch it OFF to enable per-user custom overrides.
        </p>
    </div>
</div>

<!-- ========== USER PLAN ASSIGNMENT ========== -->
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-crown"></i> Assign QR Plan to User</h3>
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
                <option value="">— Remove plan —</option>
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

<!-- ========== ACTIVE OVERRIDES TABLE ========== -->
<?php if (!empty($userFeatures)): ?>
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list-alt"></i> Active Per-User Overrides</h3>
    </div>
    <table class="table">
        <thead>
            <tr><th>User</th><th>Overridden Features</th><th>Actions</th></tr>
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
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeUserOverrides(<?= $uid ?>)">
                            <i class="fas fa-trash-alt"></i> Remove All
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- ========== ROLE DEFAULTS (advanced, collapsed) ========== -->
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users-cog"></i> Role Baseline Defaults</h3>
        <span style="font-size:12px;color:var(--text-secondary);">Default feature access for each role (lowest priority — overridden by plan &amp; per-user settings).</span>
    </div>
    <details>
        <summary style="padding:0 0 8px 4px;"><i class="fas fa-chevron-right" style="font-size:10px;margin-right:6px;"></i> Show / Edit Role Defaults</summary>
        <div style="overflow-x:auto;margin-top:8px;">
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
                                <td>
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
    </details>
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

function loadUserFeatures(userId) {
    const panel     = document.getElementById('userFeaturePanel');
    const badges    = document.getElementById('userInfoBadges');
    const roleBadge = document.getElementById('uRoleBadge');
    const planBadge = document.getElementById('uPlanBadge');

    if (!userId) {
        panel.style.display = 'none';
        badges.style.display = 'none';
        return;
    }

    const opt = document.querySelector('#featureUserId option[value="' + userId + '"]');
    if (opt) {
        roleBadge.textContent = opt.dataset.role || 'user';
        planBadge.textContent = opt.dataset.plan || 'No plan';
    }
    badges.style.display = 'flex';

    fetch('/admin/qr/roles/user-features/' + userId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const usePlan = data.use_plan !== false; // default true
                document.getElementById('usePlanToggle').checked = usePlan;
                if (usePlan) {
                    // Plan mode: show resolved features, disable toggles
                    applyFeaturesToUI(data.features, true);
                } else {
                    // Override mode: show raw overrides, enable toggles
                    applyFeaturesToUI(data.raw_overrides || {}, false);
                }
                updateFeatureGridState(usePlan);
                panel.style.display = 'block';
            } else {
                showFeedback('Could not load user features.', false);
            }
        })
        .catch(() => showFeedback('Network error loading features.', false));
}

function applyFeaturesToUI(features, readonly) {
    // Set all checkboxes to false first
    document.querySelectorAll('#userFeatureGrid input[type="checkbox"]').forEach(cb => {
        cb.checked = false;
    });
    // Apply provided values
    Object.keys(features).forEach(fk => {
        const cb = document.getElementById('feat_' + fk);
        if (cb) cb.checked = !!features[fk];
    });
}

/**
 * Enable or disable the per-feature toggles based on plan mode.
 * In plan mode (usePlan=true): toggles are disabled (plan controls them).
 * In override mode (usePlan=false): toggles are enabled.
 */
function updateFeatureGridState(usePlan) {
    document.querySelectorAll('#userFeatureGrid input[type="checkbox"]').forEach(cb => {
        cb.disabled = usePlan;
        cb.closest('.feat-row').style.opacity = usePlan ? '0.55' : '1';
    });
}

function setUsePlanMode(userId, checkbox) {
    if (!userId) { showFeedback('Please select a user first.', false); checkbox.checked = !checkbox.checked; return; }
    const enabled = checkbox.checked;
    const fd = new FormData();
    fd.append('user_id', userId);
    fd.append('enabled', enabled ? '1' : '0');
    fd.append('_csrf_token', csrfToken);

    fetch('/admin/qr/roles/set-use-plan', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showFeedback(enabled ? '✓ Plan mode ON — plan features applied' : '✓ Override mode ON — custom overrides apply', true);
                // Reload features for this user to update the grid
                loadUserFeatures(userId);
            } else {
                checkbox.checked = !enabled;
                showFeedback('Error: ' + (data.message || 'Unknown error'), false);
            }
        })
        .catch(() => { checkbox.checked = !enabled; showFeedback('Network error — please retry.', false); });
}

function applyUserFeature(userId, feature, checkbox) {
    if (!userId) { showFeedback('Please select a user first.', false); checkbox.checked = !checkbox.checked; return; }
    const enabled = checkbox.checked;
    const fd = new FormData();
    fd.append('user_id', userId);
    fd.append('feature', feature);
    fd.append('enabled', enabled ? '1' : '0');
    fd.append('_csrf_token', csrfToken);

    fetch('/admin/qr/roles/set-user-feature', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showFeedback('\u2713 Saved: ' + feature + ' = ' + (enabled ? 'ON' : 'OFF'), true);
            } else {
                checkbox.checked = !enabled;
                showFeedback('Error: ' + (data.message || 'Unknown error'), false);
            }
        })
        .catch(() => { checkbox.checked = !enabled; showFeedback('Network error \u2014 please retry.', false); });
}

function removeUserOverrides(userId) {
    if (!userId || !confirm('Remove all feature overrides for this user?')) return;
    const fd = new FormData();
    fd.append('user_id', userId);
    fd.append('_csrf_token', csrfToken);

    fetch('/admin/qr/roles/remove-user-features', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) { location.reload(); }
            else { showFeedback('Error: ' + (data.message || 'Unknown error'), false); }
        })
        .catch(() => showFeedback('Network error \u2014 please retry.', false));
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
                showFeedback('\u2713 Role default saved: ' + role + ' / ' + feature, true);
            } else {
                checkbox.checked = !enabled;
                showFeedback('Error: ' + (data.message || 'Unknown error'), false);
            }
        })
        .catch(() => { checkbox.checked = !enabled; showFeedback('Network error \u2014 please retry.', false); });
}
</script>
<?php View::endSection(); ?>
