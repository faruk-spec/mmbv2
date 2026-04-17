<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
/* Theme-aware styles */
.admin-content {
    max-width: none;
    width: 100%;
}

.content-header h1 {
    color: var(--text-primary);
    margin-bottom: 10px;
}

.text-muted {
    color: var(--text-secondary) !important;
}

.settings-form, .content-section {
    background: var(--bg-card);
    padding: 30px;
    border-radius: 12px;
    border: 1px solid var(--border-color);
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    color: var(--text-primary);
    font-weight: 500;
    margin-bottom: 8px;
    display: block;
}

.form-group input, .form-group select, .form-group textarea {
    width: 100%;
    padding: 10px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    color: var(--text-primary);
}

.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    outline: none;
    border-color: var(--cyan);
}

.btn-save, .btn-primary {
    background: linear-gradient(135deg, var(--cyan), var(--magenta));
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
}

.btn-save:hover, .btn-primary:hover {
    opacity: 0.9;
}

.alert {
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.alert-success {
    background: rgba(0, 255, 136, 0.1);
    color: var(--green);
    border: 1px solid var(--green);
}

.alert-danger {
    background: rgba(255, 107, 107, 0.1);
    color: var(--red);
    border: 1px solid var(--red);
}
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div class="admin-content">
        <h1>CodeXPro Settings</h1>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/admin/projects/codexpro/settings" class="settings-form">
            <input type="hidden" name="_csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <h2 style="color: #00ff88; margin-top: 0;">Project Limits</h2>

            <div class="form-group">
                <label for="max_project_size">Max Project Size (MB)</label>
                <input type="number" id="max_project_size" name="max_project_size" 
                       value="<?= $settings['max_project_size'] ?? 50 ?>" min="1" max="500">
                <small>Maximum size allowed for a single project</small>
            </div>

            <div class="form-group">
                <label for="max_projects_per_user">Max Projects Per User</label>
                <input type="number" id="max_projects_per_user" name="max_projects_per_user" 
                       value="<?= $settings['max_projects_per_user'] ?? 100 ?>" min="1" max="1000">
                <small>Maximum number of projects a user can create</small>
            </div>

            <h2 style="color: #00ff88; margin-top: 30px;">Editor Settings</h2>

            <div class="form-group">
                <label for="auto_save_interval">Auto-save Interval (seconds)</label>
                <input type="number" id="auto_save_interval" name="auto_save_interval" 
                       value="<?= $settings['auto_save_interval'] ?? 30 ?>" min="10" max="300">
                <small>How often to auto-save projects (0 to disable)</small>
            </div>

            <div class="form-group">
                <label for="default_theme">Default Editor Theme</label>
                <select id="default_theme" name="default_theme">
                    <option value="dark" <?= ($settings['default_theme'] ?? 'dark') === 'dark' ? 'selected' : '' ?>>Dark</option>
                    <option value="light" <?= ($settings['default_theme'] ?? 'dark') === 'light' ? 'selected' : '' ?>>Light</option>
                    <option value="monokai" <?= ($settings['default_theme'] ?? 'dark') === 'monokai' ? 'selected' : '' ?>>Monokai</option>
                </select>
            </div>

            <h2 style="color: #00ff88; margin-top: 30px;">Feature Toggles</h2>

            <div class="form-group toggle-group">
                <label class="toggle-switch">
                    <input type="checkbox" name="enable_auto_save" 
                           <?= ($settings['enable_auto_save'] ?? true) ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>
                <label for="enable_auto_save">Enable Auto-save</label>
            </div>

            <div class="form-group toggle-group">
                <label class="toggle-switch">
                    <input type="checkbox" name="enable_auto_preview" 
                           <?= ($settings['enable_auto_preview'] ?? true) ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>
                <label for="enable_auto_preview">Enable Auto-preview</label>
            </div>

            <div class="form-group toggle-group">
                <label class="toggle-switch">
                    <input type="checkbox" name="enable_exports" 
                           <?= ($settings['enable_exports'] ?? true) ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>
                <label for="enable_exports">Enable Project Exports</label>
            </div>

            <button type="submit" class="btn-primary">Save Settings</button>
        </form>
    </div>
<?php View::endSection(); ?>
