<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-users-cog text-primary"></i> ConvertX — Roles & User Feature Access</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/convertx">ConvertX</a></li>
                    <li class="breadcrumb-item active">Roles</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php if (Helpers::hasFlash('success')): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= View::e(Helpers::getFlash('success')) ?></div>
        <?php endif; ?>
        <?php if (Helpers::hasFlash('error')): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= View::e(Helpers::getFlash('error')) ?></div>
        <?php endif; ?>

        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user"></i> User Sidebar Page Hide / Unhide</h3>
                <small class="text-muted d-block">When a page is hidden, the sidebar link is removed and direct URL access is blocked.</small>
            </div>
            <div class="card-body">
                <div class="form-group mb-3">
                    <label>Select User</label>
                    <select id="featureUserId" class="form-control" onchange="loadUserFeatures(this.value)">
                        <option value="">— Choose user —</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= (int) $u['id'] ?>"><?= View::e($u['name']) ?> (<?= View::e($u['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="featureGrid" class="row" style="display:none;">
                    <?php foreach ($allFeatures as $key => $label): ?>
                        <div class="col-md-4 col-sm-6 mb-2">
                            <div class="d-flex align-items-center justify-content-between p-2 border rounded">
                                <span><?= View::e($label) ?></span>
                                <input type="checkbox" id="feat_<?= View::e($key) ?>"
                                       onchange="setFeature('<?= View::e($key) ?>', this.checked)">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-3" id="clearWrap" style="display:none;">
                    <button type="button" class="btn btn-sm btn-danger" onclick="clearOverrides()">
                        <i class="fas fa-trash"></i> Clear User Overrides
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<input type="hidden" id="csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
<script>
const csrfToken = document.getElementById('csrf_token').value;

function getUserId() {
    return document.getElementById('featureUserId').value;
}

function loadUserFeatures(userId) {
    const grid = document.getElementById('featureGrid');
    const clearWrap = document.getElementById('clearWrap');
    if (!userId) {
        grid.style.display = 'none';
        clearWrap.style.display = 'none';
        return;
    }
    fetch('/admin/projects/convertx/roles/user-features/' + userId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        document.querySelectorAll('#featureGrid input[type="checkbox"]').forEach(el => { el.checked = false; });
        Object.keys(data.features || {}).forEach(k => {
            const el = document.getElementById('feat_' + k);
            if (el) el.checked = !!data.features[k];
        });
        grid.style.display = 'flex';
        clearWrap.style.display = 'block';
    });
}

function setFeature(feature, enabled) {
    const userId = getUserId();
    if (!userId) return;
    const fd = new FormData();
    fd.append('user_id', userId);
    fd.append('feature', feature);
    fd.append('enabled', enabled ? '1' : '0');
    fd.append('_csrf_token', csrfToken);
    fetch('/admin/projects/convertx/roles/set-user-feature', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                const el = document.getElementById('feat_' + feature);
                if (el) el.checked = !enabled;
            }
        })
        .catch(() => {
            const el = document.getElementById('feat_' + feature);
            if (el) el.checked = !enabled;
        });
}

function clearOverrides() {
    const userId = getUserId();
    if (!userId || !confirm('Clear all overrides for this user?')) return;
    const fd = new FormData();
    fd.append('user_id', userId);
    fd.append('_csrf_token', csrfToken);
    fetch('/admin/projects/convertx/roles/remove-user-features', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => { if (data.success) loadUserFeatures(userId); });
}
</script>
<?php View::endSection(); ?>
