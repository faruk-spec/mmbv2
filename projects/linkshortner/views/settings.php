<?php use Core\View; use Core\Security; ?>
<?php View::extend('linkshortner:app'); ?>
<?php View::section('content'); ?>

<div class="card" style="max-width:600px;">
    <div class="card-title" style="margin-bottom:20px;"><i class="fas fa-cog" style="color:var(--accent);"></i> Settings</div>

    <form method="POST" action="/projects/linkshortner/settings">
        <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">

        <div class="form-group">
            <label class="form-label">Default Link Expiry (days)</label>
            <input type="number" name="default_expiry_days" class="form-input" min="1"
                   value="<?= View::e($settings['default_expiry_days'] ?? '') ?>"
                   placeholder="Leave empty for no default expiry">
        </div>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                <input type="checkbox" name="notifications_enabled" value="1"
                       <?= ($settings['notifications_enabled'] ?? 1) ? 'checked' : '' ?>
                       style="width:16px;height:16px;accent-color:var(--accent);">
                <span>Enable notifications</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
    </form>
</div>
<?php View::end(); ?>
