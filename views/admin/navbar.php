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
    
    .dropdown-item-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 14px;
        margin-bottom: 10px;
    }
    
    .di-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 8px;
    }
    
    .di-row {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-bottom: 8px;
    }
    
    .di-row .form-control { flex: 1; }
    
    .logo-preview {
        width: 40px;
        height: 40px;
        object-fit: contain;
        border-radius: 6px;
        border: 1px solid var(--border-color);
    }
    
    .upload-row {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    
    .upload-row input[type="file"] { flex: 1; }
    .upload-row input[type="text"] { flex: 2; }
    
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
        <h1 style="margin: 0 0 8px 0; font-size: 2rem;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M12 1v6m0 6v6M5.64 5.64l4.24 4.24m4.24 4.24l4.24 4.24M1 12h6m6 0h6M5.64 18.36l4.24-4.24m4.24-4.24l4.24-4.24"></path>
            </svg>
            Navbar Settings
        </h1>
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
                        <input type="radio" name="logo_type" value="text" <?= $settings['logo_type'] === 'text' ? 'checked' : '' ?> onchange="toggleLogoType(this.value)"> Text
                    </label>
                    <label>
                        <input type="radio" name="logo_type" value="image" <?= $settings['logo_type'] === 'image' ? 'checked' : '' ?> onchange="toggleLogoType(this.value)"> Image / SVG
                    </label>
                </div>
            </div>
            
            <div id="logo-text-section" class="form-group" <?= $settings['logo_type'] === 'image' ? 'style="display:none"' : '' ?>>
                <label>Logo Text</label>
                <input type="text" name="logo_text" class="form-control" value="<?= View::e($settings['logo_text']) ?>" placeholder="MyMultiBranch">
                <small>Text displayed when logo type is set to "Text"</small>
            </div>
            
            <div id="logo-image-section" class="form-group" <?= $settings['logo_type'] !== 'image' ? 'style="display:none"' : '' ?>>
                <label>Logo Image / SVG — Upload</label>
                <input type="file" name="logo_image" class="form-control" accept="image/*,.svg" onchange="previewNavbarLogo(this)">
                <small>Upload a logo image (PNG, JPG, GIF, WebP, SVG). Uploaded file takes priority over the URL below.</small>
                
                <label style="margin-top: 14px; display: block;">Or paste an image URL</label>
                <input type="text" name="logo_image_url" class="form-control" value="<?= View::e($settings['logo_image_url'] ?? '') ?>" placeholder="https://example.com/logo.png  or  /uploads/navbar/logo.svg">
                <small>You can type or paste any accessible image URL here. Leave blank to keep the currently uploaded file.</small>
                
                <div id="logo-image-preview" style="margin-top: 10px; <?= $settings['logo_image_url'] ? '' : 'display:none;' ?>">
                    <img id="logo-img-tag" src="<?= View::e($settings['logo_image_url'] ?? '') ?>" alt="Logo Preview" style="max-height: 50px; border-radius: 6px;">
                </div>
            </div>
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
                    <input type="checkbox" name="show_projects_dropdown_to_user" <?= !empty($settings['show_projects_dropdown_to_user']) ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>
                <span>Show Projects Dropdown to User (non-admin users)</span>
            </div>
            
            <div class="switch-container">
                <label class="switch">
                    <input type="checkbox" name="show_theme_toggle" <?= $settings['show_theme_toggle'] ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>
                <span>Show Theme Toggle (Dark/Light)</span>
            </div>
            
            <div class="switch-container">
                <label class="switch">
                    <input type="checkbox" name="navbar_sticky" <?= isset($settings['navbar_sticky']) ? ($settings['navbar_sticky'] ? 'checked' : '') : 'checked' ?>>
                    <span class="slider"></span>
                </label>
                <span>Enable Sticky Navbar (stays at top when scrolling)</span>
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
            <p style="color: var(--text-secondary); margin-bottom: 16px;">Add custom menu links to your navbar. You can make any link a dropdown with rich sub-items (logo, title, description, styling).</p>
            
            <div id="custom-links-container">
                <?php if (!empty($settings['custom_links'])): ?>
                    <?php foreach ($settings['custom_links'] as $index => $link): ?>
                        <div class="custom-link-item" data-index="<?= $index ?>">
                            <!-- Hidden field so the controller can match this link's data-index regardless of delete order -->
                            <input type="hidden" name="custom_link_idx[]" value="<?= $index ?>">
                            
                            <div class="form-row">
                                <input type="text" name="custom_link_title[]" class="form-control" placeholder="Link Title" value="<?= View::e($link['title']) ?>">
                                <input type="text" name="custom_link_url[]" class="form-control" placeholder="/path or https://..." value="<?= View::e($link['url']) ?>">
                                <input type="text" name="custom_link_icon[]" class="form-control" placeholder="fas fa-icon (optional)" value="<?= View::e($link['icon'] ?? '') ?>">
                                <input type="number" name="custom_link_position[]" class="form-control" placeholder="Order" value="<?= $link['position'] ?? 0 ?>" min="0">
                            </div>
                            
                            <!-- Per-link logo/SVG upload -->
                            <div class="form-group" style="margin-top:10px;">
                                <label style="font-size:13px; color:var(--text-secondary);">Link Logo / SVG (optional)</label>
                                <div class="upload-row">
                                    <input type="file" name="custom_link_logo[]" class="form-control" accept="image/*,.svg" onchange="previewLinkLogo(this)">
                                    <input type="text" name="custom_link_logo_url[]" class="form-control" placeholder="Or paste image URL" value="<?= View::e($link['logo_url'] ?? '') ?>">
                                    <?php if (!empty($link['logo_url'])): ?>
                                        <img src="<?= View::e($link['logo_url']) ?>" class="logo-preview" alt="">
                                    <?php else: ?>
                                        <img class="logo-preview" alt="" style="display:none;">
                                    <?php endif; ?>
                                </div>
                                <small>Upload or paste a URL for this link's icon (PNG, JPG, SVG). Overrides the icon class above.</small>
                            </div>
                            
                            <div class="form-row" style="margin-top: 10px;">
                                <label class="switch" style="margin-right: 10px;">
                                    <input type="checkbox" name="custom_link_is_dropdown[]" value="<?= $index ?>" <?= !empty($link['is_dropdown']) ? 'checked' : '' ?> onchange="toggleDropdownItems(this)">
                                    <span class="slider"></span>
                                </label>
                                <span style="color: var(--text-secondary); font-size: 14px; flex: 1;">Make this a dropdown menu</span>
                                <button type="button" class="btn-remove-link" onclick="this.closest('.custom-link-item').remove()">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                            
                            <!-- Dropdown Items Container -->
                            <div class="dropdown-items-container" style="<?= empty($link['is_dropdown']) ? 'display: none;' : '' ?> margin-top: 15px; padding-left: 20px; border-left: 3px solid var(--cyan);">
                                <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 10px; font-weight:600;">Dropdown Menu Items <small style="font-weight:400;">(Logo + Title + Description visible in navbar)</small></p>
                                <div class="dropdown-items-list">
                                    <?php if (!empty($link['dropdown_items'])): ?>
                                        <?php foreach ($link['dropdown_items'] as $subIndex => $subLink): ?>
                                            <div class="dropdown-item-card" data-sub-index="<?= $subIndex ?>">
                                                <!-- Hidden sub-index (mirrors custom_link_idx pattern) -->
                                                <input type="hidden" name="dropdown_item_sub_idx_<?= $index ?>[]" value="<?= $subIndex ?>">
                                                <!-- Row 1: Title + URL -->
                                                <div class="di-grid">
                                                    <div>
                                                        <label style="font-size:12px;color:var(--text-secondary);">Title *</label>
                                                        <input type="text" name="dropdown_item_title_<?= $index ?>[]" class="form-control" placeholder="Item Title" value="<?= View::e($subLink['title']) ?>">
                                                    </div>
                                                    <div>
                                                        <label style="font-size:12px;color:var(--text-secondary);">URL *</label>
                                                        <input type="text" name="dropdown_item_url_<?= $index ?>[]" class="form-control" placeholder="/path" value="<?= View::e($subLink['url']) ?>">
                                                    </div>
                                                </div>
                                                <!-- Row 2: Description + Logo URL -->
                                                <div class="di-grid">
                                                    <div>
                                                        <label style="font-size:12px;color:var(--text-secondary);">Description</label>
                                                        <input type="text" name="dropdown_item_description_<?= $index ?>[]" class="form-control" placeholder="Short description" value="<?= View::e($subLink['description'] ?? '') ?>">
                                                    </div>
                                                    <div>
                                                        <label style="font-size:12px;color:var(--text-secondary);">Logo URL (image/SVG)</label>
                                                        <input type="text" name="dropdown_item_logo_url_<?= $index ?>[]" class="form-control" placeholder="/uploads/navbar/icon.svg" value="<?= View::e($subLink['logo_url'] ?? '') ?>">
                                                    </div>
                                                </div>
                                                <!-- Row 3: Text colour + Bold + Font size + FA icon + Remove -->
                                                <div class="di-row">
                                                    <div style="display:flex;flex-direction:column;gap:2px;">
                                                        <label style="font-size:11px;color:var(--text-secondary);">Text Colour</label>
                                                        <input type="color" name="dropdown_item_text_color_<?= $index ?>[]" value="<?= View::e($subLink['text_color'] ?: '#e8eefc') ?>" style="width:42px;height:32px;border:1px solid var(--border-color);border-radius:6px;cursor:pointer;">
                                                    </div>
                                                    <div style="display:flex;flex-direction:column;gap:2px;">
                                                        <label style="font-size:11px;color:var(--text-secondary);">Bold</label>
                                                        <select name="dropdown_item_font_bold_<?= $index ?>[]" class="form-control" style="width:90px;">
                                                            <option value="normal" <?= empty($subLink['font_bold']) ? 'selected' : '' ?>>Normal</option>
                                                            <option value="bold"   <?= !empty($subLink['font_bold'])  ? 'selected' : '' ?>>Bold</option>
                                                        </select>
                                                    </div>
                                                    <div style="display:flex;flex-direction:column;gap:2px;">
                                                        <label style="font-size:11px;color:var(--text-secondary);">Size (px)</label>
                                                        <input type="number" name="dropdown_item_font_size_<?= $index ?>[]" class="form-control" value="<?= (int)($subLink['font_size'] ?? 14) ?>" min="10" max="32" style="width:70px;">
                                                    </div>
                                                    <div style="display:flex;flex-direction:column;gap:2px;flex:1;">
                                                        <label style="font-size:11px;color:var(--text-secondary);">FA Icon (optional)</label>
                                                        <input type="text" name="dropdown_item_icon_<?= $index ?>[]" class="form-control" placeholder="fas fa-star" value="<?= View::e($subLink['icon'] ?? '') ?>">
                                                    </div>
                                                    <button type="button" onclick="this.closest('.dropdown-item-card').remove()" style="padding: 8px 12px; background: var(--danger); color: white; border: none; border-radius: 6px; cursor: pointer; margin-top:14px;">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                <!-- Sub-sub dropdown toggle -->
                                                <div style="margin-top:10px;display:flex;align-items:center;gap:8px;">
                                                    <input type="checkbox"
                                                           name="dropdown_item_is_sub_dropdown_<?= $index ?>[]"
                                                           value="<?= $subIndex ?>"
                                                           <?= !empty($subLink['is_sub_dropdown']) ? 'checked' : '' ?>
                                                           onchange="toggleSubDropdownItems(this)">
                                                    <span style="font-size:13px;color:var(--text-secondary);">Make this a flyout sub-menu</span>
                                                </div>
                                                <!-- Sub-sub items list -->
                                                <div class="sub-items-container" style="<?= empty($subLink['is_sub_dropdown']) ? 'display:none;' : '' ?> margin-top:10px; padding-left:18px; border-left:3px solid var(--warning,#f59e0b);">
                                                    <p style="color:var(--text-secondary);font-size:12px;margin-bottom:8px;font-weight:600;">Sub-menu Items <small style="font-weight:400;">(Title + URL required)</small></p>
                                                    <div class="sub-items-list">
                                                        <?php if (!empty($subLink['sub_items'])): ?>
                                                            <?php foreach ($subLink['sub_items'] as $ti => $subSubLink): ?>
                                                                <div class="sub-item-row" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:8px;margin-bottom:8px;align-items:end;">
                                                                    <div>
                                                                        <label style="font-size:11px;color:var(--text-secondary);">Title *</label>
                                                                        <input type="text" name="sub_item_title_<?= $index ?>_<?= $subIndex ?>[]" class="form-control" placeholder="Sub-item title" value="<?= View::e($subSubLink['title']) ?>">
                                                                    </div>
                                                                    <div>
                                                                        <label style="font-size:11px;color:var(--text-secondary);">URL *</label>
                                                                        <input type="text" name="sub_item_url_<?= $index ?>_<?= $subIndex ?>[]" class="form-control" placeholder="/path" value="<?= View::e($subSubLink['url']) ?>">
                                                                    </div>
                                                                    <div>
                                                                        <label style="font-size:11px;color:var(--text-secondary);">FA Icon</label>
                                                                        <input type="text" name="sub_item_icon_<?= $index ?>_<?= $subIndex ?>[]" class="form-control" placeholder="fas fa-star" value="<?= View::e($subSubLink['icon'] ?? '') ?>">
                                                                    </div>
                                                                    <div>
                                                                        <label style="font-size:11px;color:var(--text-secondary);">Logo URL</label>
                                                                        <input type="text" name="sub_item_logo_url_<?= $index ?>_<?= $subIndex ?>[]" class="form-control" placeholder="URL" value="<?= View::e($subSubLink['logo_url'] ?? '') ?>">
                                                                    </div>
                                                                    <button type="button" onclick="this.closest('.sub-item-row').remove()" style="padding:6px 10px;background:var(--danger);color:white;border:none;border-radius:6px;cursor:pointer;">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <button type="button" onclick="addSubDropdownItem(this)" style="padding:5px 10px;background:var(--warning,#f59e0b);color:#000;border:none;border-radius:6px;cursor:pointer;font-size:12px;margin-top:4px;">
                                                        <i class="fas fa-plus"></i> Add Sub-item
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="btn-add-dropdown-item" onclick="addDropdownItem(this)" style="padding: 6px 12px; background: var(--cyan); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; margin-top: 8px;">
                                    <i class="fas fa-plus"></i> Add Dropdown Item
                                </button>
                            </div>
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
function toggleLogoType(value) {
    document.getElementById('logo-text-section').style.display  = value === 'text'  ? '' : 'none';
    document.getElementById('logo-image-section').style.display = value === 'image' ? '' : 'none';
}

function previewNavbarLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('logo-image-preview');
            const img     = document.getElementById('logo-img-tag');
            img.src = e.target.result;
            preview.style.display = '';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Live preview when URL is pasted/typed
document.addEventListener('DOMContentLoaded', function() {
    const urlInput = document.querySelector('input[name="logo_image_url"]');
    if (urlInput) {
        urlInput.addEventListener('input', function() {
            const preview = document.getElementById('logo-image-preview');
            const img     = document.getElementById('logo-img-tag');
            if (this.value.trim()) {
                img.src = this.value.trim();
                preview.style.display = '';
            } else {
                preview.style.display = 'none';
            }
        });
    }
});
</script>

<script>
let linkCounter = <?= !empty($settings['custom_links']) ? count($settings['custom_links']) : 0 ?>;

function previewLinkLogo(input) {
    const row = input.closest('.upload-row');
    const img = row.querySelector('img.logo-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            img.src = e.target.result;
            img.style.display = '';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function addCustomLink() {
    const container = document.getElementById('custom-links-container');
    const idx       = linkCounter;
    const linkItem  = document.createElement('div');
    linkItem.className = 'custom-link-item';
    linkItem.setAttribute('data-index', idx);
    linkItem.innerHTML = `
        <input type="hidden" name="custom_link_idx[]" value="${idx}">
        <div class="form-row">
            <input type="text" name="custom_link_title[]" class="form-control" placeholder="Link Title">
            <input type="text" name="custom_link_url[]" class="form-control" placeholder="/path or https://...">
            <input type="text" name="custom_link_icon[]" class="form-control" placeholder="fas fa-icon (optional)">
            <input type="number" name="custom_link_position[]" class="form-control" placeholder="Order" value="0" min="0">
        </div>
        <div class="form-group" style="margin-top:10px;">
            <label style="font-size:13px; color:var(--text-secondary);">Link Logo / SVG (optional)</label>
            <div class="upload-row">
                <input type="file" name="custom_link_logo[]" class="form-control" accept="image/*,.svg" onchange="previewLinkLogo(this)">
                <input type="text" name="custom_link_logo_url[]" class="form-control" placeholder="Or paste image URL">
                <img class="logo-preview" alt="" style="display:none;">
            </div>
            <small>Upload or paste a URL for this link's icon (PNG, JPG, SVG).</small>
        </div>
        <div class="form-row" style="margin-top: 10px;">
            <label class="switch" style="margin-right: 10px;">
                <input type="checkbox" name="custom_link_is_dropdown[]" value="${idx}" onchange="toggleDropdownItems(this)">
                <span class="slider"></span>
            </label>
            <span style="color: var(--text-secondary); font-size: 14px; flex: 1;">Make this a dropdown menu</span>
            <button type="button" class="btn-remove-link" onclick="this.closest('.custom-link-item').remove()">
                <i class="fas fa-trash"></i> Remove
            </button>
        </div>
        <div class="dropdown-items-container" style="display: none; margin-top: 15px; padding-left: 20px; border-left: 3px solid var(--cyan);">
            <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 10px; font-weight:600;">Dropdown Menu Items <small style="font-weight:400;">(Logo + Title + Description visible in navbar)</small></p>
            <div class="dropdown-items-list"></div>
            <button type="button" class="btn-add-dropdown-item" onclick="addDropdownItem(this)" style="padding: 6px 12px; background: var(--cyan); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; margin-top: 8px;">
                <i class="fas fa-plus"></i> Add Dropdown Item
            </button>
        </div>
    `;
    container.appendChild(linkItem);
    linkCounter++;
}

function toggleDropdownItems(checkbox) {
    const linkItem         = checkbox.closest('.custom-link-item');
    const dropdownContainer = linkItem.querySelector('.dropdown-items-container');
    dropdownContainer.style.display = checkbox.checked ? 'block' : 'none';
}

function addDropdownItem(button) {
    const linkItem  = button.closest('.custom-link-item');
    const linkIdx   = linkItem.getAttribute('data-index');
    const diList    = linkItem.querySelector('.dropdown-items-list');

    // Assign a unique sub-index by counting existing cards
    const subIdx = diList.querySelectorAll('.dropdown-item-card').length;

    const card      = document.createElement('div');
    card.className  = 'dropdown-item-card';
    card.setAttribute('data-sub-index', subIdx);
    card.innerHTML  = `
        <input type="hidden" name="dropdown_item_sub_idx_${linkIdx}[]" value="${subIdx}">
        <div class="di-grid">
            <div>
                <label style="font-size:12px;color:var(--text-secondary);">Title *</label>
                <input type="text" name="dropdown_item_title_${linkIdx}[]" class="form-control" placeholder="Item Title">
            </div>
            <div>
                <label style="font-size:12px;color:var(--text-secondary);">URL *</label>
                <input type="text" name="dropdown_item_url_${linkIdx}[]" class="form-control" placeholder="/path">
            </div>
        </div>
        <div class="di-grid">
            <div>
                <label style="font-size:12px;color:var(--text-secondary);">Description</label>
                <input type="text" name="dropdown_item_description_${linkIdx}[]" class="form-control" placeholder="Short description">
            </div>
            <div>
                <label style="font-size:12px;color:var(--text-secondary);">Logo URL (image/SVG)</label>
                <input type="text" name="dropdown_item_logo_url_${linkIdx}[]" class="form-control" placeholder="/uploads/navbar/icon.svg">
            </div>
        </div>
        <div class="di-row">
            <div style="display:flex;flex-direction:column;gap:2px;">
                <label style="font-size:11px;color:var(--text-secondary);">Text Colour</label>
                <input type="color" name="dropdown_item_text_color_${linkIdx}[]" value="#e8eefc" style="width:42px;height:32px;border:1px solid var(--border-color);border-radius:6px;cursor:pointer;">
            </div>
            <div style="display:flex;flex-direction:column;gap:2px;">
                <label style="font-size:11px;color:var(--text-secondary);">Bold</label>
                <select name="dropdown_item_font_bold_${linkIdx}[]" class="form-control" style="width:90px;">
                    <option value="normal">Normal</option>
                    <option value="bold">Bold</option>
                </select>
            </div>
            <div style="display:flex;flex-direction:column;gap:2px;">
                <label style="font-size:11px;color:var(--text-secondary);">Size (px)</label>
                <input type="number" name="dropdown_item_font_size_${linkIdx}[]" class="form-control" value="14" min="10" max="32" style="width:70px;">
            </div>
            <div style="display:flex;flex-direction:column;gap:2px;flex:1;">
                <label style="font-size:11px;color:var(--text-secondary);">FA Icon (optional)</label>
                <input type="text" name="dropdown_item_icon_${linkIdx}[]" class="form-control" placeholder="fas fa-star">
            </div>
            <button type="button" onclick="this.closest('.dropdown-item-card').remove()" style="padding: 8px 12px; background: var(--danger); color: white; border: none; border-radius: 6px; cursor: pointer; margin-top:14px;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <!-- Sub-sub dropdown toggle -->
        <div style="margin-top:10px;display:flex;align-items:center;gap:8px;">
            <input type="checkbox"
                   name="dropdown_item_is_sub_dropdown_${linkIdx}[]"
                   value="${subIdx}"
                   onchange="toggleSubDropdownItems(this)">
            <span style="font-size:13px;color:var(--text-secondary);">Make this a flyout sub-menu</span>
        </div>
        <!-- Sub-sub items list -->
        <div class="sub-items-container" style="display:none; margin-top:10px; padding-left:18px; border-left:3px solid var(--warning,#f59e0b);">
            <p style="color:var(--text-secondary);font-size:12px;margin-bottom:8px;font-weight:600;">Sub-menu Items <small style="font-weight:400;">(Title + URL required)</small></p>
            <div class="sub-items-list"></div>
            <button type="button" onclick="addSubDropdownItem(this)" style="padding:5px 10px;background:var(--warning,#f59e0b);color:#000;border:none;border-radius:6px;cursor:pointer;font-size:12px;margin-top:4px;">
                <i class="fas fa-plus"></i> Add Sub-item
            </button>
        </div>
    `;
    diList.appendChild(card);
}

function toggleSubDropdownItems(checkbox) {
    const card = checkbox.closest('.dropdown-item-card');
    const container = card.querySelector('.sub-items-container');
    container.style.display = checkbox.checked ? 'block' : 'none';
}

function addSubDropdownItem(button) {
    const card    = button.closest('.dropdown-item-card');
    const siList  = card.querySelector('.sub-items-list');
    // Determine the link index and sub-index from the hidden inputs in the card
    const hiddenSubIdx = card.querySelector('input[type="hidden"][name^="dropdown_item_sub_idx_"]');
    const subIdx  = hiddenSubIdx ? hiddenSubIdx.value : '0';
    // Link index is the numeric suffix of the hidden input's name
    const linkIdx = hiddenSubIdx ? hiddenSubIdx.name.replace('dropdown_item_sub_idx_', '').replace('[]', '') : '0';

    const row = document.createElement('div');
    row.className = 'sub-item-row';
    row.style.cssText = 'display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:8px;margin-bottom:8px;align-items:end;';
    row.innerHTML = `
        <div>
            <label style="font-size:11px;color:var(--text-secondary);">Title *</label>
            <input type="text" name="sub_item_title_${linkIdx}_${subIdx}[]" class="form-control" placeholder="Sub-item title">
        </div>
        <div>
            <label style="font-size:11px;color:var(--text-secondary);">URL *</label>
            <input type="text" name="sub_item_url_${linkIdx}_${subIdx}[]" class="form-control" placeholder="/path">
        </div>
        <div>
            <label style="font-size:11px;color:var(--text-secondary);">FA Icon</label>
            <input type="text" name="sub_item_icon_${linkIdx}_${subIdx}[]" class="form-control" placeholder="fas fa-star">
        </div>
        <div>
            <label style="font-size:11px;color:var(--text-secondary);">Logo URL</label>
            <input type="text" name="sub_item_logo_url_${linkIdx}_${subIdx}[]" class="form-control" placeholder="URL">
        </div>
        <button type="button" onclick="this.closest('.sub-item-row').remove()" style="padding:6px 10px;background:var(--danger);color:white;border:none;border-radius:6px;cursor:pointer;">
            <i class="fas fa-times"></i>
        </button>
    `;
    siList.appendChild(row);
}

// Sync color picker with text input
document.querySelectorAll('input[type="color"]').forEach(colorInput => {
    const textInput = colorInput.nextElementSibling;
    if (textInput && textInput.type === 'text') {
        colorInput.addEventListener('input', function() {
            textInput.value = this.value;
        });
    }
});
</script>

<?php View::endSection(); ?>
