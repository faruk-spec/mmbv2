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

    <!-- ── Per-App Feature Configuration ── -->
    <div style="margin-top:24px;">
        <label style="font-size:.875rem;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:12px;">Per-App Feature Configuration</label>
        <?php
        $appFeatureDefs = [
            'qr' => ['title'=>'QR Generator','color'=>'#9945ff',
                'numbers'=>['max_static_qr'=>['label'=>'Max Static QR','placeholder'=>'-1 = unlimited'],'max_dynamic_qr'=>['label'=>'Max Dynamic QR','placeholder'=>'-1 = unlimited'],'max_scans_per_month'=>['label'=>'Max Scans/Month','placeholder'=>'-1 = unlimited']],
                'booleans'=>['dynamic_qr'=>'Dynamic QR Codes','analytics'=>'Analytics','password_protection'=>'Password Protection','expiry_date'=>'Expiry Date','bulk_generation'=>'Bulk Generation','ai_design'=>'AI Design','api_access'=>'API Access','white_label'=>'White-label','custom_colors'=>'Custom Colors & Logos','campaigns'=>'Campaigns','team_roles'=>'Team Roles','export_pdf'=>'Export PDF/SVG']],
            'whatsapp' => ['title'=>'WhatsApp API','color'=>'#25D366',
                'numbers'=>['max_sessions'=>['label'=>'Max Sessions','placeholder'=>'-1 = unlimited'],'max_messages_per_day'=>['label'=>'Max Messages/Day','placeholder'=>'-1 = unlimited'],'max_contacts'=>['label'=>'Max Contacts','placeholder'=>'-1 = unlimited']],
                'booleans'=>['bulk_messaging'=>'Bulk Messaging','auto_reply'=>'Auto Reply','media_messages'=>'Media Messages','api_access'=>'API Access','webhooks'=>'Webhooks','analytics'=>'Analytics','multi_device'=>'Multi-Device']],
            'proshare' => ['title'=>'ProShare','color'=>'#ffaa00',
                'numbers'=>['max_files'=>['label'=>'Max Files','placeholder'=>'-1 = unlimited'],'max_file_size_mb'=>['label'=>'Max File Size (MB)','placeholder'=>'e.g. 50'],'max_storage_mb'=>['label'=>'Max Storage (MB)','placeholder'=>'-1 = unlimited']],
                'booleans'=>['password_protected'=>'Password Protected','expiry_links'=>'Expiry Links','analytics'=>'Analytics','custom_domain'=>'Custom Domain','api_access'=>'API Access']],
            'codexpro' => ['title'=>'CodeXPro','color'=>'#00f0ff',
                'numbers'=>['max_projects'=>['label'=>'Max Projects','placeholder'=>'-1 = unlimited'],'max_executions_day'=>['label'=>'Max Executions/Day','placeholder'=>'-1 = unlimited']],
                'booleans'=>['ai_completion'=>'AI Completion','private_snippets'=>'Private Snippets','team_sharing'=>'Team Sharing','api_access'=>'API Access']],
            'imgtxt' => ['title'=>'ImgTxt','color'=>'#00ff88',
                'numbers'=>['max_jobs_day'=>['label'=>'Max Jobs/Day','placeholder'=>'-1 = unlimited'],'max_image_size_mb'=>['label'=>'Max Image Size (MB)','placeholder'=>'e.g. 10']],
                'booleans'=>['batch_processing'=>'Batch Processing','high_accuracy'=>'High Accuracy Mode','api_access'=>'API Access']],
        ];
        $savedFeatures = $plan['app_features_arr'] ?? [];
        ?>
        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;" id="tabButtons">
            <?php foreach ($appFeatureDefs as $appKey => $appDef): ?>
            <button type="button" data-tab="app-tab-<?= $appKey ?>" class="app-feat-tab"
                    style="padding:6px 14px;border-radius:6px;font-size:.8rem;font-weight:600;cursor:pointer;border:1px solid var(--border-color);background:var(--bg-secondary);color:var(--text-secondary);transition:all .2s;"
                    onclick="showAppTab('app-tab-<?= $appKey ?>',this)"><?= View::e($appDef['title']) ?></button>
            <?php endforeach; ?>
        </div>
        <?php foreach ($appFeatureDefs as $appKey => $appDef):
            $saved = $savedFeatures[$appKey] ?? [];
        ?>
        <div id="app-tab-<?= $appKey ?>" class="app-feat-panel" style="display:none;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:10px;padding:16px;">
            <div style="font-size:.88rem;font-weight:700;color:<?= View::e($appDef['color']) ?>;margin-bottom:14px;"><?= View::e($appDef['title']) ?> Features</div>
            <?php if (!empty($appDef['numbers'])): ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:10px;margin-bottom:14px;">
                <?php foreach ($appDef['numbers'] as $key => $meta): ?>
                <div>
                    <label style="font-size:.74rem;color:var(--text-secondary);display:block;margin-bottom:4px;"><?= View::e($meta['label']) ?></label>
                    <input type="number" min="-1" name="app_feat[<?= $appKey ?>][<?= $key ?>]" value="<?= isset($saved[$key]) ? (int)$saved[$key] : '' ?>" placeholder="<?= View::e($meta['placeholder']) ?>"
                           style="width:100%;padding:7px 10px;background:var(--bg-card);border:1px solid var(--border-color);border-radius:6px;color:var(--text-primary);font-size:.8rem;">
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($appDef['booleans'])): ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:7px;">
                <?php foreach ($appDef['booleans'] as $key => $label):
                    $checked = isset($saved[$key]) ? (bool)$saved[$key] : false;
                ?>
                <label style="display:flex;align-items:center;gap:8px;padding:7px 10px;background:var(--bg-card);border:1px solid var(--border-color);border-radius:6px;cursor:pointer;font-size:.8rem;transition:border-color .2s;" onmouseover="this.style.borderColor='<?= View::e($appDef['color']) ?>'" onmouseout="this.style.borderColor='var(--border-color)'">
                    <input type="checkbox" name="app_feat[<?= $appKey ?>][<?= $key ?>]" value="1" <?= $checked ? 'checked' : '' ?> style="width:14px;height:14px;accent-color:<?= View::e($appDef['color']) ?>;">
                    <span><?= View::e($label) ?></span>
                </label>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <details style="margin-top:12px;">
            <summary style="font-size:.78rem;color:var(--text-secondary);cursor:pointer;user-select:none;">Advanced: Raw JSON override (for other apps)</summary>
            <textarea name="app_features_raw" rows="4" id="appFeaturesJson" placeholder='{"resumex":{"max_resumes":10}}'
                      style="width:100%;margin-top:8px;padding:10px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-family:monospace;font-size:.8rem;resize:vertical;"></textarea>
            <div id="jsonError" style="display:none;color:var(--red);font-size:.78rem;margin-top:3px;"></div>
        </details>
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
(function(){
    const picker = document.querySelector('input[type=color][name=color]');
    const hex    = document.getElementById('colorHex');
    if (picker && hex) picker.addEventListener('input', () => { hex.value = picker.value; });
    const ta  = document.getElementById('appFeaturesJson');
    const err = document.getElementById('jsonError');
    if (ta && err) {
        ta.addEventListener('blur', () => {
            try { if (ta.value.trim()) JSON.parse(ta.value); err.style.display='none'; ta.style.borderColor='var(--border-color)'; }
            catch(e) { err.textContent='Invalid JSON: '+e.message; err.style.display='block'; ta.style.borderColor='var(--red)'; }
        });
    }
    const nameInput = document.querySelector('input[name=name]');
    const slugInput = document.querySelector('input[name=slug]');
    if (nameInput && slugInput && !slugInput.value) {
        nameInput.addEventListener('input', () => { slugInput.value = nameInput.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,''); });
    }
    // Show first tab
    const firstBtn = document.querySelector('.app-feat-tab');
    if (firstBtn) firstBtn.click();
})();
function showAppTab(panelId, btn) {
    document.querySelectorAll('.app-feat-panel').forEach(p => p.style.display='none');
    document.querySelectorAll('.app-feat-tab').forEach(b => { b.style.background='var(--bg-secondary)'; b.style.color='var(--text-secondary)'; b.style.borderColor='var(--border-color)'; });
    const panel = document.getElementById(panelId);
    if (panel) panel.style.display='block';
    if (btn) { btn.style.background='rgba(0,240,255,.12)'; btn.style.color='var(--cyan)'; btn.style.borderColor='var(--cyan)'; }
}
</script>

<?php View::end(); ?>
