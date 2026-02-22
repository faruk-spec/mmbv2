<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
.plan-card { background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; margin-bottom:24px; overflow:hidden; }
.plan-header { padding:20px 24px; border-bottom:1px solid var(--border-color); display:flex; align-items:center; justify-content:space-between; }
.plan-body { padding:20px 24px; }
.feature-toggle { display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--border-color); }
.feature-toggle:last-child { border-bottom:none; }
.toggle-switch { position:relative; width:44px; height:22px; cursor:pointer; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider { position:absolute; top:0; left:0; right:0; bottom:0; background:#444; border-radius:22px; transition:.2s; }
.toggle-slider:before { content:""; position:absolute; width:16px; height:16px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.2s; }
input:checked + .toggle-slider { background:var(--cyan); }
input:checked + .toggle-slider:before { transform:translateX(22px); }
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

<?php foreach ($plans as $plan): ?>
    <?php $features = json_decode($plan['features'] ?? '{}', true) ?: []; ?>
    <div class="plan-card">
        <div class="plan-header">
            <div>
                <h3 style="margin:0;font-size:1.2rem;"><?= View::e($plan['name']) ?></h3>
                <p style="margin:4px 0 0;font-size:13px;color:var(--text-secondary);">
                    Slug: <code><?= View::e($plan['slug']) ?></code> &bull;
                    <?= $plan['subscriber_count'] ?> active subscriber(s) &bull;
                    <span class="badge <?= $plan['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>"><?= ucfirst($plan['status']) ?></span>
                </p>
            </div>
            <button class="btn btn-secondary btn-sm" onclick="togglePlanForm(<?= $plan['id'] ?>)">
                <i class="fas fa-edit"></i> Edit Limits
            </button>
        </div>

        <!-- Limits edit form (collapsed by default) -->
        <div id="plan-form-<?= $plan['id'] ?>" style="display:none;padding:16px 24px;background:var(--bg-secondary);border-bottom:1px solid var(--border-color);">
            <form method="POST" action="/admin/qr/plans/<?= $plan['id'] ?>/update">
                <?= \Core\Security::csrfField() ?>
                <div class="grid grid-3" style="gap:12px;margin-bottom:12px;">
                    <div>
                        <label style="font-size:12px;color:var(--text-secondary);">Plan Name</label>
                        <input type="text" name="name" class="form-control" value="<?= View::e($plan['name']) ?>">
                    </div>
                    <div>
                        <label style="font-size:12px;color:var(--text-secondary);">Max Static QR (-1 = unlimited)</label>
                        <input type="number" name="max_static_qr" class="form-control" value="<?= $plan['max_static_qr'] ?>">
                    </div>
                    <div>
                        <label style="font-size:12px;color:var(--text-secondary);">Max Dynamic QR (-1 = unlimited)</label>
                        <input type="number" name="max_dynamic_qr" class="form-control" value="<?= $plan['max_dynamic_qr'] ?>">
                    </div>
                    <div>
                        <label style="font-size:12px;color:var(--text-secondary);">Max Scans/Month (-1 = unlimited)</label>
                        <input type="number" name="max_scans_per_month" class="form-control" value="<?= $plan['max_scans_per_month'] ?>">
                    </div>
                    <div>
                        <label style="font-size:12px;color:var(--text-secondary);">Max Bulk Generation</label>
                        <input type="number" name="max_bulk_generation" class="form-control" value="<?= $plan['max_bulk_generation'] ?>">
                    </div>
                    <div>
                        <label style="font-size:12px;color:var(--text-secondary);">Status</label>
                        <select name="status" class="form-control">
                            <option value="active" <?= $plan['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $plan['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px;color:var(--text-secondary);">Download Formats (comma-separated: png,svg,pdf)</label>
                    <input type="text" name="feature_downloads" class="form-control" value="<?= View::e(implode(',', $features['downloads'] ?? [])) ?>" placeholder="png,svg,pdf">
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Save Limits</button>
            </form>
        </div>

        <!-- Per-feature toggles -->
        <div class="plan-body">
            <h4 style="margin:0 0 12px;font-size:0.95rem;color:var(--text-secondary);">Feature Flags — click to toggle, changes save instantly</h4>
            <div class="grid grid-2" style="gap:0;">
                <?php
                $featureLabels = [
                    'analytics'           => ['icon'=>'fas fa-chart-line', 'label'=>'Scan Analytics'],
                    'bulk'                => ['icon'=>'fas fa-layer-group', 'label'=>'Bulk Generation'],
                    'ai'                  => ['icon'=>'fas fa-robot',       'label'=>'AI Design'],
                    'password_protection' => ['icon'=>'fas fa-lock',        'label'=>'Password Protection'],
                    'campaigns'           => ['icon'=>'fas fa-bullhorn',    'label'=>'Campaigns'],
                    'api'                 => ['icon'=>'fas fa-plug',        'label'=>'API Access'],
                    'whitelabel'          => ['icon'=>'fas fa-tag',         'label'=>'White-Label'],
                    'priority_support'    => ['icon'=>'fas fa-headset',     'label'=>'Priority Support'],
                    'team_roles'          => ['icon'=>'fas fa-users-cog',   'label'=>'Team Roles'],
                    'export_data'         => ['icon'=>'fas fa-download',    'label'=>'Export Scan Data'],
                ];
                ?>
                <?php foreach ($featureLabels as $key => $meta): ?>
                    <div class="feature-toggle">
                        <span style="font-size:14px;"><i class="<?= $meta['icon'] ?>" style="width:18px;color:var(--cyan);"></i> <?= $meta['label'] ?></span>
                        <label class="toggle-switch">
                            <input type="checkbox" <?= !empty($features[$key]) ? 'checked' : '' ?>
                                onchange="togglePlanFeature(<?= $plan['id'] ?>,'<?= $key ?>',this)"
                                data-plan="<?= $plan['id'] ?>" data-feature="<?= $key ?>">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<script>
function togglePlanForm(id) {
    const el = document.getElementById('plan-form-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function togglePlanFeature(planId, feature, checkbox) {
    const enabled = checkbox.checked;
    const formData = new FormData();
    formData.append('feature', feature);
    formData.append('enabled', enabled ? '1' : '0');
    formData.append('_csrf_token', document.querySelector('input[name="_csrf_token"]') ?
        document.querySelector('input[name="_csrf_token"]').value : '');

    fetch('/admin/qr/plans/' + planId + '/toggle-feature', {
        method: 'POST',
        headers: { 'X-CSRF-Token': document.querySelector('meta[name=csrf-token]')?.content || '' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            checkbox.checked = !enabled; // revert
            alert('Failed to update feature: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(() => {
        checkbox.checked = !enabled;
    });
}
</script>
<?php View::endSection(); ?>
