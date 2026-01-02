<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1>⚙️ Navbar Settings</h1>
        <p style="color: var(--text-secondary);">Customize your navigation bar logo, links, and appearance</p>
    </div>
    <a href="/" target="_blank" class="btn btn-secondary">
        <i class="fas fa-external-link-alt"></i> Preview Changes
    </a>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<form method="POST" action="/admin/navbar" enctype="multipart/form-data">
    <?= \Core\Security::csrfField() ?>
    
    <!-- Logo Settings -->
    <div class="card" style="margin-bottom: 30px;">
        <h2 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.5rem;">🎨</span> Logo Settings
        </h2>
        
        <div class="form-group">
            <label class="form-label">Logo Type</label>
            <div style="display: flex; gap: 20px;">
                <label class="form-checkbox">
                    <input type="radio" name="logo_type" value="text" 
                           <?= ($settings['logo_type'] ?? 'text') === 'text' ? 'checked' : '' ?>>
                    <span>Text Logo</span>
                </label>
                <label class="form-checkbox">
                    <input type="radio" name="logo_type" value="image" 
                           <?= ($settings['logo_type'] ?? 'text') === 'image' ? 'checked' : '' ?>>
                    <span>Image Logo</span>
                </label>
            </div>
        </div>
        
        <div class="grid grid-2">
            <div class="form-group">
                <label class="form-label">Logo Text</label>
                <input type="text" name="logo_text" class="form-input" 
                       value="<?= View::e($settings['logo_text'] ?? APP_NAME) ?>" 
                       placeholder="Your Site Name">
                <small style="color: var(--text-secondary);">Used when "Text Logo" is selected</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Logo Image</label>
                <?php if (!empty($settings['logo_image_url'])): ?>
                    <div style="margin-bottom: 10px; padding: 20px; background: var(--bg-secondary); border-radius: 8px; text-align: center; position: relative;">
                        <img src="<?= View::e($settings['logo_image_url']) ?>" alt="Logo" 
                             style="max-height: 50px; max-width: 200px;">
                        <button type="button" class="btn btn-danger" id="removeLogoBtn" 
                                style="position: absolute; top: 10px; right: 10px; padding: 5px 10px; font-size: 12px;">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </div>
                    <input type="hidden" name="remove_logo_image" id="removeLogoInput" value="0">
                <?php endif; ?>
                <input type="file" name="logo_image" class="form-input" accept="image/*">
                <input type="hidden" name="current_logo_image_url" value="<?= View::e($settings['logo_image_url'] ?? '') ?>">
                <small style="color: var(--text-secondary);">Upload logo image (max 2MB, SVG/PNG/JPG)</small>
            </div>
        </div>
    </div>
    
    <!-- Navigation Links Settings -->
    <div class="card" style="margin-bottom: 30px;">
        <h2 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.5rem;">🔗</span> Navigation Links
        </h2>
        
        <div class="grid grid-2">
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="show_home_link" value="1" 
                           <?= ($settings['show_home_link'] ?? 1) ? 'checked' : '' ?>>
                    <span>Show Home Link</span>
                </label>
            </div>
            
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="show_dashboard_link" value="1" 
                           <?= ($settings['show_dashboard_link'] ?? 1) ? 'checked' : '' ?>>
                    <span>Show Dashboard Link (logged in users)</span>
                </label>
            </div>
            
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="show_profile_link" value="1" 
                           <?= ($settings['show_profile_link'] ?? 1) ? 'checked' : '' ?>>
                    <span>Show Profile Link (logged in users)</span>
                </label>
            </div>
            
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="show_admin_link" value="1" 
                           <?= ($settings['show_admin_link'] ?? 1) ? 'checked' : '' ?>>
                    <span>Show Admin Link (admin users)</span>
                </label>
            </div>
        </div>
    </div>
    
    <!-- Custom Links -->
    <div class="card" style="margin-bottom: 30px;">
        <h2 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.5rem;">➕</span> Custom Links
        </h2>
        
        <div id="customLinksContainer">
            <?php 
            $customLinks = $settings['custom_links'] ?? [];
            if (empty($customLinks)): 
            ?>
                <p style="color: var(--text-secondary);">No custom links added. Click "Add Link" to create one.</p>
            <?php else: ?>
                <?php foreach ($customLinks as $index => $link): ?>
                    <div class="custom-link-row" style="display: grid; grid-template-columns: 1fr 1fr 150px auto; gap: 15px; margin-bottom: 15px;">
                        <input type="text" name="link_text[]" class="form-input" 
                               value="<?= View::e($link['text']) ?>" placeholder="Link Text">
                        <input type="text" name="link_url[]" class="form-input" 
                               value="<?= View::e($link['url']) ?>" placeholder="Link URL (e.g., /about)">
                        <select name="link_visibility[]" class="form-input">
                            <option value="all" <?= ($link['visibility'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Users</option>
                            <option value="logged_in" <?= ($link['visibility'] ?? 'all') === 'logged_in' ? 'selected' : '' ?>>Logged In Only</option>
                        </select>
                        <button type="button" class="btn btn-danger remove-link">Remove</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <button type="button" class="btn btn-secondary" id="addLink" style="margin-top: 15px;">
            <i class="fas fa-plus"></i> Add Link
        </button>
    </div>
    
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> Save Navbar Settings
    </button>
</form>

<script>
// Handle logo image removal
const removeLogoBtn = document.getElementById('removeLogoBtn');
if (removeLogoBtn) {
    removeLogoBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to remove the logo image?')) {
            document.getElementById('removeLogoInput').value = '1';
            this.closest('div').remove();
        }
    });
}

// Add custom link
document.getElementById('addLink').addEventListener('click', function() {
    const container = document.getElementById('customLinksContainer');
    const noLinksMsg = container.querySelector('p');
    if (noLinksMsg) {
        noLinksMsg.remove();
    }
    
    const row = document.createElement('div');
    row.className = 'custom-link-row';
    row.style.display = 'grid';
    row.style.gridTemplateColumns = '1fr 1fr 150px auto';
    row.style.gap = '15px';
    row.style.marginBottom = '15px';
    
    row.innerHTML = `
        <input type="text" name="link_text[]" class="form-input" placeholder="Link Text">
        <input type="text" name="link_url[]" class="form-input" placeholder="Link URL (e.g., /about)">
        <select name="link_visibility[]" class="form-input">
            <option value="all">All Users</option>
            <option value="logged_in">Logged In Only</option>
        </select>
        <button type="button" class="btn btn-danger remove-link">Remove</button>
    `;
    
    container.appendChild(row);
});

// Remove custom link
document.getElementById('customLinksContainer').addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-link') || e.target.parentElement.classList.contains('remove-link')) {
        const row = e.target.closest('.custom-link-row');
        if (row) {
            row.remove();
            
            // Check if there are no more links
            const remainingLinks = document.querySelectorAll('.custom-link-row');
            if (remainingLinks.length === 0) {
                const container = document.getElementById('customLinksContainer');
                container.innerHTML = '<p style="color: var(--text-secondary);">No custom links added. Click "Add Link" to create one.</p>';
            }
        }
    }
});
</script>
<?php View::endSection(); ?>
