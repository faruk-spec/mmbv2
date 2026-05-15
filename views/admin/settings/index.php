<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1>Site Settings</h1>
        <p style="color: var(--text-secondary);">Configure platform settings</p>
    </div>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div class="grid grid-3">
    <div style="grid-column: span 2;">
        <div class="card">
            <form method="POST" action="/admin/settings">
                <?= \Core\Security::csrfField() ?>
                
                <div class="form-group">
                    <label class="form-label">Auth Page Tagline</label>
                    <input type="text" name="auth_tagline" class="form-input"
                           value="<?= View::e($settings['auth_tagline'] ?? '') ?>"
                           placeholder="Your tools, all in one place.">
                    <small class="form-help">Short subtitle shown below the logo on login &amp; register pages.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Site Name</label>
                    <input type="text" name="site_name" class="form-input" 
                           value="<?= View::e($settings['site_name'] ?? APP_NAME) ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Site Description</label>
                    <textarea name="site_description" class="form-input" rows="3"><?= View::e($settings['site_description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Contact Email</label>
                    <input type="email" name="contact_email" class="form-input" 
                           value="<?= View::e($settings['contact_email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">System Timezone</label>
                    <select name="system_timezone" class="form-input">
                        <?php
                        $currentTimezone = $settings['system_timezone'] ?? 'UTC';
                        $timezones = [
                            'UTC' => 'UTC (Coordinated Universal Time)',
                            'America/New_York' => 'America/New York (EST/EDT)',
                            'America/Chicago' => 'America/Chicago (CST/CDT)',
                            'America/Denver' => 'America/Denver (MST/MDT)',
                            'America/Los_Angeles' => 'America/Los Angeles (PST/PDT)',
                            'America/Toronto' => 'America/Toronto',
                            'America/Mexico_City' => 'America/Mexico City',
                            'America/Sao_Paulo' => 'America/São Paulo',
                            'Europe/London' => 'Europe/London (GMT/BST)',
                            'Europe/Paris' => 'Europe/Paris (CET/CEST)',
                            'Europe/Berlin' => 'Europe/Berlin (CET/CEST)',
                            'Europe/Rome' => 'Europe/Rome (CET/CEST)',
                            'Europe/Madrid' => 'Europe/Madrid (CET/CEST)',
                            'Europe/Amsterdam' => 'Europe/Amsterdam (CET/CEST)',
                            'Europe/Brussels' => 'Europe/Brussels (CET/CEST)',
                            'Europe/Vienna' => 'Europe/Vienna (CET/CEST)',
                            'Europe/Stockholm' => 'Europe/Stockholm (CET/CEST)',
                            'Europe/Warsaw' => 'Europe/Warsaw (CET/CEST)',
                            'Europe/Athens' => 'Europe/Athens (EET/EEST)',
                            'Europe/Istanbul' => 'Europe/Istanbul (TRT)',
                            'Europe/Moscow' => 'Europe/Moscow (MSK)',
                            'Africa/Cairo' => 'Africa/Cairo (EET)',
                            'Africa/Johannesburg' => 'Africa/Johannesburg (SAST)',
                            'Asia/Dubai' => 'Asia/Dubai (GST)',
                            'Asia/Kolkata' => 'Asia/Kolkata (IST)',
                            'Asia/Bangkok' => 'Asia/Bangkok (ICT)',
                            'Asia/Singapore' => 'Asia/Singapore (SGT)',
                            'Asia/Hong_Kong' => 'Asia/Hong Kong (HKT)',
                            'Asia/Shanghai' => 'Asia/Shanghai (CST)',
                            'Asia/Tokyo' => 'Asia/Tokyo (JST)',
                            'Asia/Seoul' => 'Asia/Seoul (KST)',
                            'Australia/Sydney' => 'Australia/Sydney (AEDT/AEST)',
                            'Australia/Melbourne' => 'Australia/Melbourne (AEDT/AEST)',
                            'Australia/Perth' => 'Australia/Perth (AWST)',
                            'Pacific/Auckland' => 'Pacific/Auckland (NZDT/NZST)',
                        ];
                        foreach ($timezones as $tz => $label):
                        ?>
                            <option value="<?= $tz ?>" <?= $currentTimezone === $tz ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-help">Current server time: <?= date('Y-m-d H:i:s T') ?></small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Date Format</label>
                    <select name="date_format" class="form-input">
                        <?php
                        $currentFormat = $settings['date_format'] ?? 'M d, Y';
                        $formats = [
                            'M d, Y' => date('M d, Y') . ' (M d, Y)',
                            'Y-m-d' => date('Y-m-d') . ' (Y-m-d)',
                            'd/m/Y' => date('d/m/Y') . ' (d/m/Y)',
                            'm/d/Y' => date('m/d/Y') . ' (m/d/Y)',
                            'F j, Y' => date('F j, Y') . ' (F j, Y)',
                            'd-M-Y' => date('d-M-Y') . ' (d-M-Y)',
                        ];
                        foreach ($formats as $format => $label):
                        ?>
                            <option value="<?= $format ?>" <?= $currentFormat === $format ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Time Format</label>
                    <select name="time_format" class="form-input">
                        <?php
                        $currentTimeFormat = $settings['time_format'] ?? 'g:i A';
                        $timeFormats = [
                            'g:i A' => date('g:i A') . ' (12-hour with AM/PM)',
                            'H:i' => date('H:i') . ' (24-hour)',
                            'h:i:s A' => date('h:i:s A') . ' (12-hour with seconds)',
                            'H:i:s' => date('H:i:s') . ' (24-hour with seconds)',
                        ];
                        foreach ($timeFormats as $format => $label):
                        ?>
                            <option value="<?= $format ?>" <?= $currentTimeFormat === $format ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" name="registration_enabled" value="1" 
                               <?= ($settings['registration_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span>Enable User Registration</span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Settings</button>
                <input type="hidden" name="site_favicon" value="<?= View::e($settings['site_favicon'] ?? '') ?>">
                <input type="hidden" name="footer_show_on_projects" value="<?= View::e($settings['footer_show_on_projects'] ?? '1') ?>">
            </form>
        </div>
    </div>
    
    <div>
        <div class="card" style="margin-bottom: 16px;">
            <h4 style="margin-bottom: 15px;">Auth Page Logo</h4>

            <?php $currentLogo = $settings['auth_logo'] ?? ''; ?>
            <?php if (!empty($currentLogo)): ?>
            <div style="text-align:center;margin-bottom:14px;">
                <img src="<?= View::e($currentLogo) ?>" alt="Current auth logo"
                     style="max-height:80px;max-width:160px;object-fit:contain;border-radius:12px;border:1px solid var(--border-color);background:var(--bg-secondary);padding:8px;">
            </div>
            <form method="POST" action="/admin/settings/delete-logo" style="margin-bottom:12px;">
                <?= \Core\Security::csrfField() ?>
                <button type="submit" class="btn btn-danger" style="width:100%;font-size:13px;"
                        onclick="return confirm('Remove the current logo?')">
                    <i class="fas fa-trash-alt"></i> Remove Logo
                </button>
            </form>
            <?php endif; ?>

            <!-- Upload logo file -->
            <form method="POST" action="/admin/settings/upload-logo" enctype="multipart/form-data" style="margin-bottom:14px;">
                <?= \Core\Security::csrfField() ?>
                <div class="form-group" style="margin-bottom:10px;">
                    <label class="form-label" style="margin-bottom:6px;"><?= empty($currentLogo) ? 'Upload Logo' : 'Replace Logo' ?></label>
                    <input type="file" name="auth_logo_file" class="form-input"
                           accept=".jpg,.jpeg,.png,.gif,.webp" required
                           style="padding:8px;font-size:13px;">
                    <small class="form-help">JPG, PNG, GIF or WebP · Max 2 MB</small>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;font-size:13px;">
                    <i class="fas fa-upload"></i> Upload
                </button>
            </form>

            <!-- Or use a URL / path -->
            <form method="POST" action="/admin/settings">
                <?= \Core\Security::csrfField() ?>
                <input type="hidden" name="site_name" value="<?= View::e($settings['site_name'] ?? APP_NAME) ?>">
                <input type="hidden" name="site_description" value="<?= View::e($settings['site_description'] ?? '') ?>">
                <input type="hidden" name="contact_email" value="<?= View::e($settings['contact_email'] ?? '') ?>">
                <input type="hidden" name="system_timezone" value="<?= View::e($settings['system_timezone'] ?? 'UTC') ?>">
                <input type="hidden" name="date_format" value="<?= View::e($settings['date_format'] ?? 'M d, Y') ?>">
                <input type="hidden" name="time_format" value="<?= View::e($settings['time_format'] ?? 'g:i A') ?>">
                <input type="hidden" name="registration_enabled" value="<?= View::e($settings['registration_enabled'] ?? '1') ?>">
                <input type="hidden" name="auth_tagline" value="<?= View::e($settings['auth_tagline'] ?? '') ?>">
                <input type="hidden" name="site_favicon" value="<?= View::e($settings['site_favicon'] ?? '') ?>">
                <input type="hidden" name="footer_show_on_projects" value="<?= View::e($settings['footer_show_on_projects'] ?? '1') ?>">
                <div class="form-group" style="margin-bottom:10px;">
                    <label class="form-label" style="margin-bottom:6px;">Or enter Logo URL / Path</label>
                    <input type="text" name="auth_logo" class="form-input"
                           value="<?= View::e($currentLogo) ?>"
                           placeholder="https://… or /uploads/oauth/logo.png"
                           style="font-size:13px;">
                    <small class="form-help">Absolute URL or a relative path served by the app.</small>
                </div>
                <button type="submit" class="btn btn-secondary" style="width:100%;font-size:13px;">
                    <i class="fas fa-save"></i> Save Path
                </button>
            </form>
        </div>

        <!-- Favicon Settings -->
        <div class="card" style="margin-top:16px;">
            <h4 style="margin-bottom:15px;"><i class="fas fa-star" style="margin-right:8px;color:var(--orange);"></i> Site Favicon</h4>
            <?php $currentFavicon = $settings['site_favicon'] ?? ''; ?>
            <?php if (!empty($currentFavicon)): ?>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                <img src="<?= View::e($currentFavicon) ?>" alt="Current favicon"
                     style="width:32px;height:32px;object-fit:contain;border-radius:4px;border:1px solid var(--border-color);background:var(--bg-secondary);">
                <span style="font-size:.8rem;color:var(--text-secondary);"><?= View::e($currentFavicon) ?></span>
            </div>
            <?php endif; ?>
            <!-- Upload favicon file -->
            <form method="POST" action="/admin/settings/upload-favicon" enctype="multipart/form-data" style="margin-bottom:14px;">
                <?= \Core\Security::csrfField() ?>
                <div class="form-group" style="margin-bottom:10px;">
                    <label class="form-label" style="margin-bottom:6px;">Upload Favicon</label>
                    <input type="file" name="site_favicon_file" class="form-input"
                           accept=".ico,.png,.jpg,.jpeg,.webp,.svg" required
                           style="padding:8px;font-size:13px;">
                    <small class="form-help">ICO, PNG, SVG or WebP · Max 1 MB · Recommended: 32×32 or 64×64 px</small>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;font-size:13px;">
                    <i class="fas fa-upload"></i> Upload Favicon
                </button>
            </form>
            <!-- Or use a URL / path -->
            <form method="POST" action="/admin/settings">
                <?= \Core\Security::csrfField() ?>
                <input type="hidden" name="site_name"             value="<?= View::e($settings['site_name'] ?? APP_NAME) ?>">
                <input type="hidden" name="site_description"      value="<?= View::e($settings['site_description'] ?? '') ?>">
                <input type="hidden" name="contact_email"         value="<?= View::e($settings['contact_email'] ?? '') ?>">
                <input type="hidden" name="system_timezone"       value="<?= View::e($settings['system_timezone'] ?? 'UTC') ?>">
                <input type="hidden" name="date_format"           value="<?= View::e($settings['date_format'] ?? 'M d, Y') ?>">
                <input type="hidden" name="time_format"           value="<?= View::e($settings['time_format'] ?? 'g:i A') ?>">
                <input type="hidden" name="registration_enabled"  value="<?= View::e($settings['registration_enabled'] ?? '1') ?>">
                <input type="hidden" name="auth_tagline"          value="<?= View::e($settings['auth_tagline'] ?? '') ?>">
                <input type="hidden" name="auth_logo"             value="<?= View::e($settings['auth_logo'] ?? '') ?>">
                <input type="hidden" name="footer_show_on_projects" value="<?= View::e($settings['footer_show_on_projects'] ?? '1') ?>">
                <div class="form-group" style="margin-bottom:10px;">
                    <label class="form-label" style="margin-bottom:6px;">Or enter Favicon URL / Path</label>
                    <input type="text" name="site_favicon" class="form-input"
                           value="<?= View::e($currentFavicon) ?>"
                           placeholder="https://… or /uploads/oauth/favicon.ico"
                           style="font-size:13px;">
                    <small class="form-help">Absolute URL or a relative path served by the app.</small>
                </div>
                <button type="submit" class="btn btn-secondary" style="width:100%;font-size:13px;">
                    <i class="fas fa-save"></i> Save Favicon URL
                </button>
            </form>
        </div>

        <div class="card" style="margin-top:16px;">
            <h4 style="margin-bottom:15px;"><i class="fas fa-shoe-prints" style="margin-right:8px;color:var(--cyan);"></i> Footer Settings</h4>
            <p style="color:var(--text-secondary);font-size:.83rem;margin-bottom:12px;">
                For full footer management, visit the <a href="/admin/settings/footer-page" style="color:var(--cyan);">Footer Settings page</a>.
            </p>
            <form method="POST" action="/admin/settings">
                <?= \Core\Security::csrfField() ?>
                <input type="hidden" name="site_name"             value="<?= View::e($settings['site_name'] ?? APP_NAME) ?>">
                <input type="hidden" name="site_description"      value="<?= View::e($settings['site_description'] ?? '') ?>">
                <input type="hidden" name="contact_email"         value="<?= View::e($settings['contact_email'] ?? '') ?>">
                <input type="hidden" name="system_timezone"       value="<?= View::e($settings['system_timezone'] ?? 'UTC') ?>">
                <input type="hidden" name="date_format"           value="<?= View::e($settings['date_format'] ?? 'M d, Y') ?>">
                <input type="hidden" name="time_format"           value="<?= View::e($settings['time_format'] ?? 'g:i A') ?>">
                <input type="hidden" name="registration_enabled"  value="<?= View::e($settings['registration_enabled'] ?? '1') ?>">
                <input type="hidden" name="auth_tagline"          value="<?= View::e($settings['auth_tagline'] ?? '') ?>">
                <input type="hidden" name="auth_logo"             value="<?= View::e($settings['auth_logo'] ?? '') ?>">
                <input type="hidden" name="site_favicon"          value="<?= View::e($settings['site_favicon'] ?? '') ?>">
                <div class="form-group" style="margin-bottom:10px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="footer_show_on_projects" value="1"
                               <?= ($settings['footer_show_on_projects'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span>Show footer on <code>/projects/*</code> pages</span>
                    </label>
                    <small class="form-help" style="margin-top:4px;">Uncheck to hide the footer on all project pages (useful when projects have their own footer).</small>
                </div>
                <button type="submit" class="btn btn-primary" style="font-size:13px;">
                    <i class="fas fa-save"></i> Save Footer Visibility
                </button>
            </form>
        </div>

        <div class="card" style="margin-top:16px;">
            <h4 style="margin-bottom:15px;"><i class="fas fa-shoe-prints" style="margin-right:8px;color:var(--cyan);"></i> Footer Content</h4>
            <form method="POST" action="/admin/settings/footer">
                <?= \Core\Security::csrfField() ?>

                <div class="form-group">
                    <label class="form-label">Footer Tagline</label>
                    <input type="text" name="footer_tagline" class="form-input"
                           value="<?= View::e($settings['footer_tagline'] ?? '') ?>"
                           placeholder="Your tools, all in one place.">
                </div>
                <div class="form-group">
                    <label class="form-label">Footer Copyright Text</label>
                    <input type="text" name="footer_copyright" class="form-input"
                           value="<?= View::e($settings['footer_copyright'] ?? '') ?>"
                           placeholder="Leave blank to use default: © YEAR APP_NAME">
                </div>
                <div class="form-group">
                    <label class="form-label">Social Links</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <div>
                            <label style="font-size:.8rem;">Twitter/X URL</label>
                            <input type="url" name="footer_social_twitter" class="form-input" value="<?= View::e($settings['footer_social_twitter'] ?? '') ?>" placeholder="https://twitter.com/...">
                        </div>
                        <div>
                            <label style="font-size:.8rem;">GitHub URL</label>
                            <input type="url" name="footer_social_github" class="form-input" value="<?= View::e($settings['footer_social_github'] ?? '') ?>" placeholder="https://github.com/...">
                        </div>
                        <div>
                            <label style="font-size:.8rem;">LinkedIn URL</label>
                            <input type="url" name="footer_social_linkedin" class="form-input" value="<?= View::e($settings['footer_social_linkedin'] ?? '') ?>" placeholder="https://linkedin.com/...">
                        </div>
                        <div>
                            <label style="font-size:.8rem;">YouTube URL</label>
                            <input type="url" name="footer_social_youtube" class="form-input" value="<?= View::e($settings['footer_social_youtube'] ?? '') ?>" placeholder="https://youtube.com/...">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" name="footer_show_social" value="1"
                               <?= ($settings['footer_show_social'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span>Show Social Links in Footer</span>
                    </label>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Footer Settings</button>
            </form>
        </div>

        <!-- Homepage 3-Column Footer -->
        <div class="card">
            <h4 style="margin-bottom:15px;"><i class="fas fa-columns" style="margin-right:8px;color:var(--cyan);"></i> Homepage 3-Column Footer</h4>
            <p style="color:var(--text-secondary);font-size:.85rem;margin-bottom:14px;">Manage the rich 3-column footer shown on the home page. Dashboard, profile, and project pages use the simple single-line footer above.</p>
            <form method="POST" action="/admin/settings/homepage-footer">
                <?= \Core\Security::csrfField() ?>
                <div class="form-group" style="margin-bottom:10px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="hp_footer_enabled" value="1" <?= ($settings['hp_footer_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span>Enable homepage 3-column footer</span>
                    </label>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-top:12px;">
                    <div>
                        <label class="form-label">Column 1 — Heading</label>
                        <input type="text" name="hp_footer_col1_heading" class="form-input" value="<?= \Core\View::e($settings['hp_footer_col1_heading'] ?? 'About Us') ?>" placeholder="About Us">
                        <label class="form-label" style="margin-top:8px;">Column 1 — Body Text</label>
                        <textarea name="hp_footer_col1_text" class="form-input" rows="3" placeholder="Short description..."><?= \Core\View::e($settings['hp_footer_col1_text'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="form-label">Column 2 — Heading</label>
                        <input type="text" name="hp_footer_col2_heading" class="form-input" value="<?= \Core\View::e($settings['hp_footer_col2_heading'] ?? 'Quick Links') ?>" placeholder="Quick Links">
                        <p style="color:var(--text-secondary);font-size:.8rem;margin-top:6px;">Column 2 links come from published pages with <strong>Show Footer</strong> enabled. Manage them in <a href="/admin/pages" style="color:var(--cyan);">Admin Pages</a>.</p>
                    </div>
                    <div>
                        <label class="form-label">Column 3 — Heading</label>
                        <input type="text" name="hp_footer_col3_heading" class="form-input" value="<?= \Core\View::e($settings['hp_footer_col3_heading'] ?? 'Contact') ?>" placeholder="Contact">
                        <label class="form-label" style="margin-top:8px;">Column 3 — Body Text</label>
                        <textarea name="hp_footer_col3_text" class="form-input" rows="3" placeholder="Address, email, etc."><?= \Core\View::e($settings['hp_footer_col3_text'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="form-group" style="margin-top:12px;">
                    <label class="form-label">Bottom Bar Text (overrides default copyright)</label>
                    <input type="text" name="hp_footer_bottom_text" class="form-input" value="<?= \Core\View::e($settings['hp_footer_bottom_text'] ?? '') ?>" placeholder="© 2025 YourBrand. All rights reserved.">
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:6px;"><i class="fas fa-save"></i> Save Homepage Footer</button>
            </form>
        </div>

        <?php
        $homeFooterLinks = $footerLinks['home'] ?? [];
        $defaultFooterLinks = $footerLinks['default'] ?? [];
        ?>
        <div class="card">
            <h4 style="margin-bottom:15px;"><i class="fas fa-link" style="margin-right:8px;color:var(--cyan);"></i> Footer Links CRUD</h4>
            <p style="color:var(--text-secondary);font-size:.85rem;margin-bottom:14px;">Manage separate link sets for homepage footer and default dashboard/project footer.</p>

            <form method="POST" action="/admin/settings/footer-links/add" style="border:1px solid var(--border-color);border-radius:10px;padding:12px;margin-bottom:14px;background:var(--bg-secondary);">
                <?= \Core\Security::csrfField() ?>
                <div style="display:grid;grid-template-columns:120px 1fr 1fr 90px auto auto;gap:8px;align-items:end;">
                    <div>
                        <label class="form-label">Area</label>
                        <select name="area" class="form-input">
                            <option value="home">Home</option>
                            <option value="default">Default</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Label</label>
                        <input type="text" name="label" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">URL</label>
                        <input type="text" name="url" class="form-input" placeholder="/pages/privacy-policy" required>
                    </div>
                    <div>
                        <label class="form-label">Sort</label>
                        <input type="number" name="sort_order" class="form-input" value="0">
                    </div>
                    <label style="display:flex;align-items:center;gap:6px;font-size:.8rem;">
                        <input type="checkbox" name="is_enabled" value="1" checked> Enabled
                    </label>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add</button>
                </div>
            </form>

            <?php foreach (['home' => $homeFooterLinks, 'default' => $defaultFooterLinks] as $areaKey => $rows): ?>
                <div style="margin-bottom:12px;">
                    <div style="font-weight:700;font-size:.88rem;margin-bottom:8px;color:var(--text-primary);">
                        <?= $areaKey === 'home' ? 'Home Footer Links' : 'Default Footer Links' ?>
                    </div>
                    <?php if (empty($rows)): ?>
                        <p style="font-size:.82rem;color:var(--text-secondary);margin-bottom:8px;">No links configured.</p>
                    <?php else: ?>
                        <div style="display:flex;flex-direction:column;gap:8px;">
                            <?php foreach ($rows as $lnk): ?>
                                <form method="POST" action="/admin/settings/footer-links/update" style="display:grid;grid-template-columns:1fr 1fr 90px auto auto auto;gap:8px;align-items:end;border:1px solid var(--border-color);border-radius:8px;padding:10px;background:var(--bg-secondary);">
                                    <?= \Core\Security::csrfField() ?>
                                    <input type="hidden" name="id" value="<?= (int) ($lnk['id'] ?? 0) ?>">
                                    <input type="hidden" name="area" value="<?= View::e($areaKey) ?>">
                                    <div>
                                        <label class="form-label">Label</label>
                                        <input type="text" name="label" class="form-input" value="<?= View::e($lnk['label'] ?? '') ?>" required>
                                    </div>
                                    <div>
                                        <label class="form-label">URL</label>
                                        <input type="text" name="url" class="form-input" value="<?= View::e($lnk['url'] ?? '') ?>" required>
                                    </div>
                                    <div>
                                        <label class="form-label">Sort</label>
                                        <input type="number" name="sort_order" class="form-input" value="<?= (int) ($lnk['sort_order'] ?? 0) ?>">
                                    </div>
                                    <label style="display:flex;align-items:center;gap:6px;font-size:.8rem;">
                                        <input type="checkbox" name="is_enabled" value="1" <?= ((int) ($lnk['is_enabled'] ?? 0) === 1) ? 'checked' : '' ?>> Enabled
                                    </label>
                                    <button type="submit" class="btn btn-secondary"><i class="fas fa-save"></i> Update</button>
                                    <button type="submit"
                                            formaction="/admin/settings/footer-links/delete"
                                            class="btn btn-danger"
                                            onclick="return confirm('Delete this footer link?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h4 style="margin-bottom: 15px;">Quick Actions</h4>
            
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <a href="/admin/settings/maintenance" class="btn btn-secondary" style="width: 100%;">
                    <i class="fas fa-tools"></i> Maintenance Mode
                </a>
            </div>
        </div>
        
        <div class="card mt-2">
            <h4 style="margin-bottom: 15px;">System Info</h4>
            
            <div style="font-size: 14px;">
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="color: var(--text-secondary);">PHP Version</span>
                    <span><?= PHP_VERSION ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="color: var(--text-secondary);">Platform Version</span>
                    <span><?= APP_VERSION ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                    <span style="color: var(--text-secondary);">Server</span>
                    <span><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
