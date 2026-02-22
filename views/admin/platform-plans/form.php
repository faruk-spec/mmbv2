<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<?php if (Helpers::hasFlash('error')): ?>
<div style="background:rgba(255,107,107,.1);border:1px solid var(--red);color:var(--red);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
    <i class="fas fa-exclamation-circle"></i> <?= View::e(Helpers::getFlash('error')) ?>
</div>
<?php endif; ?>

<!-- Breadcrumb -->
<div style="margin-bottom:20px;">
    <a href="/admin/platform-plans" style="color:var(--text-secondary);text-decoration:none;font-size:.875rem;" onmouseover="this.style.color='var(--cyan)'" onmouseout="this.style.color='var(--text-secondary)'">
        <i class="fas fa-arrow-left"></i> Back to Platform Plans
    </a>
</div>

<h1 style="font-size:1.3rem;font-weight:700;margin-bottom:24px;"><?= View::e($title) ?></h1>

<form method="POST" action="<?= View::e($action) ?>" style="max-width:780px;">
    <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        <!-- Plan Name -->
        <div style="display:flex;flex-direction:column;gap:6px;">
            <label style="font-size:.875rem;font-weight:600;color:var(--text-secondary);">Plan Name *</label>
            <input type="text" name="name" required value="<?= View::e($plan['name'] ?? '') ?>"
                   placeholder="e.g. Pro Bundle"
                   style="padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.875rem;">
        </div>

        <!-- Slug -->
        <div style="display:flex;flex-direction:column;gap:6px;">
            <label style="font-size:.875rem;font-weight:600;color:var(--text-secondary);">Slug * <small style="font-weight:400;">(URL-safe, e.g. pro-bundle)</small></label>
            <input type="text" name="slug" required value="<?= View::e($plan['slug'] ?? '') ?>"
                   placeholder="pro-bundle"
                   style="padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.875rem;">
        </div>

        <!-- Price -->
        <div style="display:flex;flex-direction:column;gap:6px;">
            <label style="font-size:.875rem;font-weight:600;color:var(--text-secondary);">Price (USD)</label>
            <input type="number" name="price" min="0" step="0.01" value="<?= htmlspecialchars(number_format((float)($plan['price'] ?? 0), 2, '.', '')) ?>"
                   style="padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.875rem;">
        </div>

        <!-- Billing cycle -->
        <div style="display:flex;flex-direction:column;gap:6px;">
            <label style="font-size:.875rem;font-weight:600;color:var(--text-secondary);">Billing Cycle</label>
            <select name="billing_cycle" style="padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.875rem;">
                <?php foreach (['monthly','yearly','lifetime'] as $cycle): ?>
                <option value="<?= $cycle ?>" <?= ($plan['billing_cycle'] ?? 'monthly') === $cycle ? 'selected' : '' ?>><?= ucfirst($cycle) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Accent colour -->
        <div style="display:flex;flex-direction:column;gap:6px;">
            <label style="font-size:.875rem;font-weight:600;color:var(--text-secondary);">Accent Colour</label>
            <div style="display:flex;align-items:center;gap:10px;">
                <input type="color" name="color" value="<?= View::e($plan['color'] ?? '#9945ff') ?>"
                       style="width:44px;height:38px;padding:2px;border:1px solid var(--border-color);border-radius:8px;background:var(--bg-secondary);cursor:pointer;">
                <input type="text" id="colorHex" value="<?= View::e($plan['color'] ?? '#9945ff') ?>"
                       style="flex:1;padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.875rem;" readonly>
            </div>
        </div>

        <!-- Status + sort order -->
        <div style="display:flex;gap:16px;">
            <div style="flex:1;display:flex;flex-direction:column;gap:6px;">
                <label style="font-size:.875rem;font-weight:600;color:var(--text-secondary);">Status</label>
                <select name="status" style="padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.875rem;">
                    <option value="active" <?= ($plan['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($plan['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div style="flex:1;display:flex;flex-direction:column;gap:6px;">
                <label style="font-size:.875rem;font-weight:600;color:var(--text-secondary);">Sort Order</label>
                <input type="number" name="sort_order" min="0" value="<?= (int)($plan['sort_order'] ?? 0) ?>"
                       style="padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.875rem;">
            </div>
        </div>

    </div><!-- /grid -->

    <!-- Description -->
    <div style="margin-top:20px;display:flex;flex-direction:column;gap:6px;">
        <label style="font-size:.875rem;font-weight:600;color:var(--text-secondary);">Description</label>
        <textarea name="description" rows="3" placeholder="Short description shown to users..."
                  style="padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.875rem;resize:vertical;"><?= View::e($plan['description'] ?? '') ?></textarea>
    </div>

    <!-- Included apps -->
    <div style="margin-top:20px;">
        <label style="font-size:.875rem;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:10px;">Included Applications</label>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;">
        <?php
        $selectedApps = $plan['included_apps_arr'] ?? [];
        foreach ($apps as $appKey => $appLabel):
        ?>
        <label style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;cursor:pointer;transition:border-color .2s;" onmouseover="this.style.borderColor='var(--cyan)'" onmouseout="this.style.borderColor='var(--border-color)'">
            <input type="checkbox" name="included_apps[]" value="<?= View::e($appKey) ?>"
                   <?= in_array($appKey, $selectedApps) ? 'checked' : '' ?>
                   style="width:15px;height:15px;accent-color:var(--cyan);">
            <span style="font-size:.85rem;font-weight:500;"><?= View::e($appLabel) ?></span>
        </label>
        <?php endforeach; ?>
        </div>
    </div>

    <!-- App features JSON -->
    <div style="margin-top:20px;">
        <label style="font-size:.875rem;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:6px;">
            Per-App Feature Configuration <small style="font-weight:400;">(JSON)</small>
        </label>
        <p style="font-size:.78rem;color:var(--text-secondary);margin-bottom:8px;">
            Override per-app limits/features. Example: <code style="background:rgba(0,0,0,.3);padding:2px 6px;border-radius:4px;font-size:.75rem;">{"qr":{"max_dynamic_qr":-1,"analytics":true},"whatsapp":{"max_sessions":3}}</code>
        </p>
        <textarea name="app_features" rows="8" id="appFeaturesJson"
                  placeholder='{"qr":{"max_static_qr":-1,"analytics":true}}'
                  style="width:100%;padding:12px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-family:monospace;font-size:.82rem;resize:vertical;"><?= htmlspecialchars(
    isset($plan['app_features_arr']) && !empty($plan['app_features_arr'])
        ? json_encode($plan['app_features_arr'], JSON_PRETTY_PRINT)
        : ($plan['app_features'] ?? '')
) ?></textarea>
        <div id="jsonError" style="display:none;color:var(--red);font-size:.8rem;margin-top:4px;"></div>
    </div>

    <!-- Actions -->
    <div style="margin-top:28px;display:flex;gap:12px;">
        <button type="submit" style="padding:10px 24px;background:linear-gradient(135deg,var(--purple),var(--cyan));border:none;border-radius:8px;color:#fff;font-size:.9rem;font-weight:700;cursor:pointer;">
            <i class="fas fa-save"></i> <?= $plan ? 'Update Plan' : 'Create Plan' ?>
        </button>
        <a href="/admin/platform-plans" style="padding:10px 20px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);text-decoration:none;font-size:.9rem;">Cancel</a>
    </div>
</form>

<script>
// Sync colour picker with hex text
(function(){
    const picker = document.querySelector('input[type=color][name=color]');
    const hex    = document.getElementById('colorHex');
    if (picker && hex) {
        picker.addEventListener('input', () => { hex.value = picker.value; });
    }
    // JSON validation
    const ta  = document.getElementById('appFeaturesJson');
    const err = document.getElementById('jsonError');
    if (ta && err) {
        ta.addEventListener('blur', () => {
            try {
                if (ta.value.trim()) JSON.parse(ta.value);
                err.style.display = 'none';
                ta.style.borderColor = 'var(--border-color)';
            } catch(e) {
                err.textContent = 'Invalid JSON: ' + e.message;
                err.style.display = 'block';
                ta.style.borderColor = 'var(--red)';
            }
        });
    }
    // Auto-generate slug from name
    const nameInput = document.querySelector('input[name=name]');
    const slugInput = document.querySelector('input[name=slug]');
    if (nameInput && slugInput && !slugInput.value) {
        nameInput.addEventListener('input', () => {
            slugInput.value = nameInput.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        });
    }
})();
</script>

<?php View::end(); ?>
