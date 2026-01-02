<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
    .settings-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
    }
    
    .settings-card h3 {
        margin: 0 0 20px 0;
        font-size: 1.25rem;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .settings-card h3 i {
        color: var(--cyan);
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--text-primary);
    }
    
    .form-group small {
        display: block;
        margin-top: 5px;
        color: var(--text-secondary);
        font-size: 13px;
    }
    
    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--bg-secondary);
        color: var(--text-primary);
        font-size: 14px;
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--cyan);
    }
    
    .switch-container {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
    }
    
    .switch {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 24px;
    }
    
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #555;
        transition: .3s;
        border-radius: 24px;
    }
    
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }
    
    input:checked + .slider {
        background-color: var(--cyan);
    }
    
    input:checked + .slider:before {
        transform: translateX(24px);
    }
    
    .color-input-group {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .color-input-group input[type="color"] {
        width: 60px;
        height: 40px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        cursor: pointer;
    }
    
    .color-input-group input[type="text"] {
        flex: 1;
    }
    
    .custom-link-item {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
    }
    
    .custom-link-item .form-row {
        display: grid;
        grid-template-columns: 2fr 2fr 1fr 80px;
        gap: 12px;
        margin-bottom: 10px;
    }
    
    .btn-remove-link {
        padding: 8px 12px;
        background: var(--danger);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 13px;
    }
    
    .btn-add-link {
        padding: 10px 20px;
        background: var(--cyan);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }
    
    .preview-section {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        margin-top: 24px;
    }
    
    .preview-section h4 {
        margin: 0 0 16px 0;
        font-size: 1.1rem;
    }
    
    .preview-navbar {
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .btn-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }
    
    .btn-primary, .btn-secondary {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-primary {
        background: var(--cyan);
        color: white;
    }
    
    .btn-primary:hover {
        background: var(--cyan-hover);
    }
    
    .btn-secondary {
        background: var(--bg-secondary);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
    }
    
    .btn-secondary:hover {
        background: var(--bg-card);
    }
    
    .radio-group {
        display: flex;
        gap: 20px;
    }
    
    .radio-group label {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>

<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 24px;">
    <div class="page-header" style="margin-bottom: 32px;">
        <h1 style="margin: 0 0 8px 0; font-size: 2rem;">⚙️ Navbar Settings</h1>
        <p style="color: var(--text-secondary); margin: 0;">Customize your platform's navigation bar appearance and behavior</p>
    </div>

    <?php if (Helpers::hasFlash('success')): ?>
        <div class="alert alert-success" style="background: rgba(0, 255, 136, 0.1); border: 1px solid var(--green); color: var(--green); padding: 14px 18px; border-radius: 8px; margin-bottom: 24px;">
            <i class="fas fa-check-circle"></i> <?= View::e(Helpers::getFlash('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (Helpers::hasFlash('error')): ?>
        <div class="alert alert-error" style="background: rgba(255, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); padding: 14px 18px; border-radius: 8px; margin-bottom: 24px;">
            <i class="fas fa-exclamation-circle"></i> <?= View::e(Helpers::getFlash('error')) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/admin/navbar/update" enctype="multipart/form-data">
        <?= \Core\Security::csrfField() ?>
        
        <!-- Logo Settings -->
        <div class="settings-card">
            <h3><i class="fas fa-image"></i> Logo Settings</h3>
            
            <div class="form-group">
                <label>Logo Type</label>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="logo_type" value="text" <?= $settings['logo_type'] === 'text' ? 'checked' : '' ?>> Text
                    </label>
                    <label>
                        <input type="radio" name="logo_type" value="image" <?= $settings['logo_type'] === 'image' ? 'checked' : '' ?>> Image
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label>Logo Text</label>
                <input type="text" name="logo_text" class="form-control" value="<?= View::e($settings['logo_text']) ?>" placeholder="MyMultiBranch">
                <small>Text displayed when logo type is set to "Text"</small>
            </div>
            
            <div class="form-group">
                <label>Logo Image</label>
                <input type="file" name="logo_image" class="form-control" accept="image/*">
                <small>Upload a logo image (PNG, JPG, SVG recommended). Current: <?= $settings['logo_image_url'] ? View::e($settings['logo_image_url']) : 'None' ?></small>
                <?php if ($settings['logo_image_url']): ?>
                    <div style="margin-top: 10px;">
                        <img src="<?= View::e($settings['logo_image_url']) ?>" alt="Current Logo" style="max-height: 50px; border-radius: 6px;">
                    </div>
                <?php endif; ?>
            </div>
            
            <input type="hidden" name="logo_image_url" value="<?= View::e($settings['logo_image_url'] ?? '') ?>">
        </div>

        <!-- Link Visibility -->
        <div class="settings-card">
            <h3><i class="fas fa-eye"></i> Link Visibility</h3>
            
            <div class="switch-container">
                <label class="switch">
                    <input type="checkbox" name="show_home_link" <?= $settings['show_home_link'] ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>
                <span>Show Home Link</span>
            </div>
            
            <div class="switch-container">
                <label class="switch">
                    <input type="checkbox" name="show_dashboard_link" <?= $settings['show_dashboard_link'] ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>
                <span>Show Dashboard Link (for logged-in users)</span>
            </div>
            
            <div class="switch-container">
                <label class="switch">
                    <input type="checkbox" name="show_profile_link" <?= $settings['show_profile_link'] ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>
                <span>Show Profile Link (for logged-in users)</span>
            </div>
            
            <div class="switch-container">
                <label class="switch">
                    <input type="checkbox" name="show_admin_link" <?= $settings['show_admin_link'] ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>
                <span>Show Admin Link (for admin users)</span>
            </div>
            
            <div class="switch-container">
                <label class="switch">
                    <input type="checkbox" name="show_projects_dropdown" <?= $settings['show_projects_dropdown'] ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>
                <span>Show Projects Dropdown</span>
            </div>
            
            <div class="switch-container">
                <label class="switch">
                    <input type="checkbox" name="show_theme_toggle" <?= $settings['show_theme_toggle'] ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>
                <span>Show Theme Toggle (Dark/Light)</span>
            </div>
        </div>

        <!-- Theme Settings -->
        <div class="settings-card">
            <h3><i class="fas fa-palette"></i> Theme & Colors</h3>
            
            <div class="form-group">
                <label>Default Theme</label>
                <select name="default_theme" class="form-control">
                    <option value="dark" <?= $settings['default_theme'] === 'dark' ? 'selected' : '' ?>>Dark</option>
                    <option value="light" <?= $settings['default_theme'] === 'light' ? 'selected' : '' ?>>Light</option>
                </select>
                <small>Default theme when users first visit (can be changed by theme toggle)</small>
            </div>
            
            <div class="form-group">
                <label>Navbar Background Color</label>
                <div class="color-input-group">
                    <input type="color" name="navbar_bg_color" value="<?= View::e($settings['navbar_bg_color']) ?>">
                    <input type="text" class="form-control" value="<?= View::e($settings['navbar_bg_color']) ?>" readonly>
                </div>
            </div>
            
            <div class="form-group">
                <label>Navbar Text Color</label>
                <div class="color-input-group">
                    <input type="color" name="navbar_text_color" value="<?= View::e($settings['navbar_text_color']) ?>">
                    <input type="text" class="form-control" value="<?= View::e($settings['navbar_text_color']) ?>" readonly>
                </div>
            </div>
            
            <div class="form-group">
                <label>Navbar Border Color</label>
                <div class="color-input-group">
                    <input type="color" name="navbar_border_color" value="<?= View::e($settings['navbar_border_color']) ?>">
                    <input type="text" class="form-control" value="<?= View::e($settings['navbar_border_color']) ?>" readonly>
                </div>
            </div>
        </div>

        <!-- Custom Links -->
        <div class="settings-card">
            <h3><i class="fas fa-link"></i> Custom Links</h3>
            <p style="color: var(--text-secondary); margin-bottom: 16px;">Add custom menu links to your navbar</p>
            
            <div id="custom-links-container">
                <?php if (!empty($settings['custom_links'])): ?>
                    <?php foreach ($settings['custom_links'] as $index => $link): ?>
                        <div class="custom-link-item">
                            <div class="form-row">
                                <input type="text" name="custom_link_title[]" class="form-control" placeholder="Link Title" value="<?= View::e($link['title']) ?>">
                                <input type="text" name="custom_link_url[]" class="form-control" placeholder="/path or https://..." value="<?= View::e($link['url']) ?>">
                                <input type="text" name="custom_link_icon[]" class="form-control" placeholder="fas fa-icon" value="<?= View::e($link['icon'] ?? '') ?>">
                                <input type="number" name="custom_link_position[]" class="form-control" placeholder="Order" value="<?= $link['position'] ?? 0 ?>" min="0">
                            </div>
                            <button type="button" class="btn-remove-link" onclick="this.parentElement.remove()">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <button type="button" class="btn-add-link" onclick="addCustomLink()">
                <i class="fas fa-plus"></i> Add Custom Link
            </button>
        </div>

        <!-- Custom CSS -->
        <div class="settings-card">
            <h3><i class="fas fa-code"></i> Custom CSS</h3>
            <div class="form-group">
                <label>Additional CSS</label>
                <textarea name="custom_css" class="form-control" rows="8" placeholder=".navbar { /* your custom styles */ }"><?= View::e($settings['custom_css'] ?? '') ?></textarea>
                <small>Add custom CSS to further customize your navbar appearance</small>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="btn-actions">
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Save Changes
            </button>
            <button type="button" class="btn-secondary" onclick="resetNavbarSettings(event)">
                <i class="fas fa-undo"></i> Reset to Default
            </button>
        </div>
    </form>
    
    <!-- Hidden form for reset -->
    <form id="resetForm" method="POST" action="/admin/navbar/reset" style="display: none;">
        <?= \Core\Security::csrfField() ?>
    </form>
</div>

<script>
function resetNavbarSettings(e) {
    e.preventDefault();
    if (confirm('Reset all navbar settings to default? This will overwrite all current settings.')) {
        document.getElementById('resetForm').submit();
    }
}
</script>

<script>
function addCustomLink() {
    const container = document.getElementById('custom-links-container');
    const linkItem = document.createElement('div');
    linkItem.className = 'custom-link-item';
    linkItem.innerHTML = `
        <div class="form-row">
            <input type="text" name="custom_link_title[]" class="form-control" placeholder="Link Title">
            <input type="text" name="custom_link_url[]" class="form-control" placeholder="/path or https://...">
            <input type="text" name="custom_link_icon[]" class="form-control" placeholder="fas fa-icon">
            <input type="number" name="custom_link_position[]" class="form-control" placeholder="Order" value="0" min="0">
        </div>
        <button type="button" class="btn-remove-link" onclick="this.parentElement.remove()">
            <i class="fas fa-trash"></i> Remove
        </button>
    `;
    container.appendChild(linkItem);
}

// Sync color picker with text input
document.querySelectorAll('input[type="color"]').forEach(colorInput => {
    const textInput = colorInput.nextElementSibling;
    colorInput.addEventListener('input', function() {
        textInput.value = this.value;
    });
});
</script>

<?php View::endSection(); ?>
