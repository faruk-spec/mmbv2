<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1><i class="fas fa-cog" style="color:#6366f1;"></i> CardX — Settings</h1>
        <p style="color:var(--text-secondary);">Configure the ID card generator for all users</p>
    </div>
    <a href="/admin/projects/idcard" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Overview</a>
</div>

<?php if (!empty($_GET['saved'])): ?>
<div class="alert alert-success" style="margin-bottom:20px;"><i class="fas fa-check-circle"></i> Settings saved successfully.</div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
<div class="alert alert-error" style="margin-bottom:20px;"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<form method="POST" action="/admin/projects/idcard/settings">
    <?= \Core\Security::csrfField() ?>

    <div class="card" style="padding:20px;margin-bottom:20px;">
        <h3 style="font-size:1rem;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-sliders-h" style="color:#6366f1;"></i> General Settings
        </h3>

        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:13px;color:var(--text-secondary);margin-bottom:6px;font-weight:600;">
                Max Cards per User
            </label>
            <input type="number" name="max_cards_per_user" min="1" max="10000"
                   value="<?= (int)($settings['max_cards_per_user'] ?? 200) ?>"
                   style="padding:8px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:13px;width:200px;">
            <p style="font-size:12px;color:var(--text-secondary);margin-top:4px;">Maximum number of ID cards a single user can generate (minimum 1).</p>
        </div>

        <div>
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                <input type="checkbox" name="ai_enabled" value="1" <?= !empty($settings['ai_enabled']) ? 'checked' : '' ?>>
                <span style="font-weight:600;">Enable AI Design Assistant</span>
            </label>
            <p style="font-size:12px;color:var(--text-secondary);margin-top:4px;margin-left:22px;">
                When enabled, users can request AI-powered design suggestions. Requires HUGGING_FACE_API_TOKEN for advanced AI (rule-based tips always work).
            </p>
        </div>
    </div>

    <!-- Bulk Generation Settings -->
    <div class="card" style="padding:20px;margin-bottom:20px;">
        <h3 style="font-size:1rem;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-layer-group" style="color:#6366f1;"></i> Bulk Generation Settings
        </h3>

        <div style="margin-bottom:16px;">
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                <input type="checkbox" name="bulk_enabled" value="1" <?= !isset($settings['bulk_enabled']) || !empty($settings['bulk_enabled']) ? 'checked' : '' ?>>
                <span style="font-weight:600;">Enable Bulk Card Generator</span>
            </label>
            <p style="font-size:12px;color:var(--text-secondary);margin-top:4px;margin-left:22px;">
                Allow users to generate multiple ID cards at once by uploading a CSV file.
            </p>
        </div>

        <div>
            <label style="display:block;font-size:13px;color:var(--text-secondary);margin-bottom:6px;font-weight:600;">
                Max Rows per Bulk Upload
            </label>
            <input type="number" name="max_bulk_rows" min="1" max="1000"
                   value="<?= (int)($settings['max_bulk_rows'] ?? 200) ?>"
                   style="padding:8px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:13px;width:200px;">
            <p style="font-size:12px;color:var(--text-secondary);margin-top:4px;">Maximum number of data rows allowed in a single CSV upload (1–1000).</p>
        </div>
    </div>

    <div class="card" style="padding:20px;margin-bottom:20px;">
        <h3 style="font-size:1rem;margin-bottom:4px;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-palette" style="color:#6366f1;"></i> Allowed Templates
        </h3>
        <p style="font-size:12px;color:var(--text-secondary);margin-bottom:16px;">Leave all unchecked to allow all templates.</p>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
            <?php foreach ($templates as $key => $tpl): ?>
            <label style="display:flex;align-items:center;gap:10px;padding:12px;border:1px solid var(--border-color);border-radius:10px;cursor:pointer;transition:all 0.2s;"
                   onmouseover="this.style.borderColor='<?= htmlspecialchars($tpl['color']) ?>'"
                   onmouseout="this.style.borderColor='var(--border-color)'">
                <input type="checkbox" name="allowed_templates[]" value="<?= htmlspecialchars($key) ?>"
                       <?= in_array($key, $settings['allowed_templates'] ?? [], true) ? 'checked' : '' ?>>
                <div>
                    <div style="display:flex;align-items:center;gap:6px;font-weight:600;font-size:13px;">
                        <span style="width:10px;height:10px;border-radius:50%;background:<?= htmlspecialchars($tpl['color']) ?>;display:inline-block;"></span>
                        <?= htmlspecialchars($tpl['name']) ?>
                    </div>
                    <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;"><?= htmlspecialchars($tpl['description']) ?></div>
                </div>
            </label>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="display:flex;gap:12px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
        <a href="/admin/projects/idcard" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php View::endSection(); ?>
