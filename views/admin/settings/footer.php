<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<?php if (Helpers::hasFlash('success')): ?>
<div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
<div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1>Footer Settings</h1>
        <p style="color:var(--text-secondary);">Manage site footer — simple footer, homepage 3-column footer, and footer links.</p>
    </div>
    <a href="/admin/settings" style="font-size:.85rem;color:var(--cyan);text-decoration:none;">← Back to Settings</a>
</div>

<!-- ── Footer Visibility (Projects) ─────────────────────────────── -->
<div class="card" style="margin-bottom:20px;">
    <h4 style="margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-eye" style="color:var(--cyan);"></i> Footer Visibility
    </h4>
    <form method="POST" action="/admin/settings">
        <?= \Core\Security::csrfField() ?>
        <input type="hidden" name="_redirect" value="/admin/settings/footer-page">
        <input type="hidden" name="site_name" value="<?= View::e($settings['site_name'] ?? APP_NAME) ?>">
        <input type="hidden" name="home_page_title" value="<?= View::e($settings['home_page_title'] ?? '') ?>">
        <input type="hidden" name="site_description" value="<?= View::e($settings['site_description'] ?? '') ?>">
        <input type="hidden" name="contact_email" value="<?= View::e($settings['contact_email'] ?? '') ?>">
        <input type="hidden" name="system_timezone" value="<?= View::e($settings['system_timezone'] ?? 'UTC') ?>">
        <input type="hidden" name="date_format" value="<?= View::e($settings['date_format'] ?? 'M d, Y') ?>">
        <input type="hidden" name="time_format" value="<?= View::e($settings['time_format'] ?? 'g:i A') ?>">
        <input type="hidden" name="registration_enabled" value="<?= View::e($settings['registration_enabled'] ?? '1') ?>">
        <input type="hidden" name="auth_tagline" value="<?= View::e($settings['auth_tagline'] ?? '') ?>">
        <input type="hidden" name="auth_logo" value="<?= View::e($settings['auth_logo'] ?? '') ?>">
        <input type="hidden" name="site_favicon" value="<?= View::e($settings['site_favicon'] ?? '') ?>">
        <input type="hidden" name="footer_show_on_projects" value="0">

        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:8px;">
            <input type="checkbox" name="footer_show_on_projects" value="1"
                   <?= ($settings['footer_show_on_projects'] ?? '1') === '1' ? 'checked' : '' ?>>
            <span>Show footer on <code>/projects/*</code> pages</span>
        </label>
        <small class="form-help" style="display:block;margin-bottom:12px;">
            Uncheck to hide footer globally across all project pages (including ResumeX pages).
        </small>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Footer Visibility</button>
    </form>
</div>

<!-- ── Simple Footer ─────────────────────────────────────────────── -->
<div class="card" style="margin-bottom:20px;">
    <h4 style="margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-minus-circle" style="color:var(--purple);"></i> Simple Footer
        <span style="font-size:.78rem;font-weight:400;color:var(--text-secondary);">(dashboard, profile, project pages)</span>
    </h4>
    <form method="POST" action="/admin/settings/footer">
        <?= \Core\Security::csrfField() ?>
        <input type="hidden" name="_redirect" value="/admin/settings/footer-page">
        <div class="grid grid-2" style="gap:16px;margin-bottom:14px;">
            <div>
                <label class="form-label">Footer Tagline</label>
                <input type="text" name="footer_tagline" class="form-input"
                       value="<?= View::e($settings['footer_tagline'] ?? '') ?>"
                       placeholder="Your all-in-one platform.">
            </div>
            <div>
                <label class="form-label">Copyright Text</label>
                <input type="text" name="footer_copyright" class="form-input"
                       value="<?= View::e($settings['footer_copyright'] ?? '') ?>"
                       placeholder="© 2025 YourBrand. All rights reserved.">
            </div>
            <div>
                <label class="form-label">Twitter / X URL</label>
                <input type="url" name="footer_social_twitter" class="form-input"
                       value="<?= View::e($settings['footer_social_twitter'] ?? '') ?>"
                       placeholder="https://x.com/yourhandle">
            </div>
            <div>
                <label class="form-label">GitHub URL</label>
                <input type="url" name="footer_social_github" class="form-input"
                       value="<?= View::e($settings['footer_social_github'] ?? '') ?>"
                       placeholder="https://github.com/yourusername">
            </div>
            <div>
                <label class="form-label">LinkedIn URL</label>
                <input type="url" name="footer_social_linkedin" class="form-input"
                       value="<?= View::e($settings['footer_social_linkedin'] ?? '') ?>"
                       placeholder="https://linkedin.com/in/yourprofile">
            </div>
            <div>
                <label class="form-label">YouTube URL</label>
                <input type="url" name="footer_social_youtube" class="form-input"
                       value="<?= View::e($settings['footer_social_youtube'] ?? '') ?>"
                       placeholder="https://youtube.com/@yourchannel">
            </div>
        </div>
        <div class="form-group" style="margin-bottom:14px;">
            <label class="form-checkbox">
                <input type="checkbox" name="footer_show_social" value="1"
                       <?= ($settings['footer_show_social'] ?? '1') === '1' ? 'checked' : '' ?>>
                <span>Show Social Links in Footer</span>
            </label>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Simple Footer</button>
    </form>
</div>

<!-- ── Homepage 3-Column Footer ─────────────────────────────────── -->
<div class="card" style="margin-bottom:20px;">
    <h4 style="margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-columns" style="color:var(--cyan);"></i> Homepage 3-Column Footer
    </h4>
    <p style="color:var(--text-secondary);font-size:.85rem;margin-bottom:14px;">Manage the rich 3-column footer shown on the home page.</p>
    <form method="POST" action="/admin/settings/homepage-footer">
        <?= \Core\Security::csrfField() ?>
        <input type="hidden" name="_redirect" value="/admin/settings/footer-page">
        <div class="form-group" style="margin-bottom:12px;">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="checkbox" name="hp_footer_enabled" value="1"
                       <?= ($settings['hp_footer_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                <span>Enable homepage 3-column footer</span>
            </label>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:14px;">
            <div>
                <label class="form-label">Column 1 — Heading</label>
                <input type="text" name="hp_footer_col1_heading" class="form-input"
                       value="<?= View::e($settings['hp_footer_col1_heading'] ?? 'About Us') ?>" placeholder="About Us">
                <label class="form-label" style="margin-top:8px;">Column 1 — Body Text</label>
                <textarea name="hp_footer_col1_text" class="form-input" rows="3"
                          placeholder="Short description..."><?= View::e($settings['hp_footer_col1_text'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="form-label">Column 2 — Heading</label>
                <input type="text" name="hp_footer_col2_heading" class="form-input"
                       value="<?= View::e($settings['hp_footer_col2_heading'] ?? 'Quick Links') ?>" placeholder="Quick Links">
                <p style="color:var(--text-secondary);font-size:.8rem;margin-top:6px;">
                    Column 2 links come from published pages with <strong>Show Footer</strong> enabled, or from the <strong>Home Footer Links</strong> section below.
                    Manage pages in <a href="/admin/pages" style="color:var(--cyan);">Admin Pages</a>.
                </p>
            </div>
            <div>
                <label class="form-label">Column 3 — Heading</label>
                <input type="text" name="hp_footer_col3_heading" class="form-input"
                       value="<?= View::e($settings['hp_footer_col3_heading'] ?? 'Contact') ?>" placeholder="Contact">
                <label class="form-label" style="margin-top:8px;">Column 3 — HTML Content</label>
                <textarea name="hp_footer_col3_text" class="form-input" rows="7"
                          placeholder="<p>Address...</p><iframe src='https://www.google.com/maps/embed?...'></iframe>"><?= View::e($settings['hp_footer_col3_text'] ?? '') ?></textarea>
                <small class="form-help">HTML is supported (including map iframe embeds). Unsafe scripts and event handlers are stripped automatically.</small>
            </div>
        </div>
        <div class="form-group" style="margin-bottom:14px;">
            <label class="form-label">Bottom Bar Text (overrides default copyright)</label>
            <input type="text" name="hp_footer_bottom_text" class="form-input"
                   value="<?= View::e($settings['hp_footer_bottom_text'] ?? '') ?>"
                   placeholder="© 2025 YourBrand. All rights reserved.">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Homepage Footer</button>
    </form>
</div>

<!-- ── Footer Links CRUD ─────────────────────────────────────────── -->
<?php
$homeFooterLinks    = $footerLinks['home']    ?? [];
$defaultFooterLinks = $footerLinks['default'] ?? [];
?>
<div class="card" style="margin-bottom:20px;">
    <h4 style="margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-link" style="color:var(--cyan);"></i> Footer Links CRUD
    </h4>
    <p style="color:var(--text-secondary);font-size:.85rem;margin-bottom:14px;">Manage link sets for the homepage footer (Column 2) and the default site footer.</p>

    <!-- Add link form -->
    <form method="POST" action="/admin/settings/footer-links/add"
          style="border:1px solid var(--border-color);border-radius:10px;padding:12px;margin-bottom:16px;background:var(--bg-secondary);">
        <?= \Core\Security::csrfField() ?>
        <input type="hidden" name="_redirect" value="/admin/settings/footer-page">
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
    <div style="margin-bottom:14px;">
        <div style="font-weight:700;font-size:.88rem;margin-bottom:8px;color:var(--text-primary);">
            <?= $areaKey === 'home' ? '<i class="fas fa-home" style="color:var(--cyan);margin-right:4px;"></i> Home Footer Links' : '<i class="fas fa-globe" style="color:var(--purple);margin-right:4px;"></i> Default Footer Links' ?>
        </div>
        <?php if (empty($rows)): ?>
        <p style="font-size:.82rem;color:var(--text-secondary);">No links configured yet.</p>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:8px;">
            <?php foreach ($rows as $lnk): ?>
            <form method="POST" action="/admin/settings/footer-links/update"
                  style="display:grid;grid-template-columns:1fr 1fr 90px auto auto auto;gap:8px;align-items:end;
                         border:1px solid var(--border-color);border-radius:8px;padding:10px;background:var(--bg-secondary);">
                <?= \Core\Security::csrfField() ?>
                <input type="hidden" name="id"       value="<?= (int) ($lnk['id'] ?? 0) ?>">
                <input type="hidden" name="area"     value="<?= View::e($areaKey) ?>">
                <input type="hidden" name="_redirect" value="/admin/settings/footer-page">
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
                    <input type="checkbox" name="is_enabled" value="1"
                           <?= ((int) ($lnk['is_enabled'] ?? 0) === 1) ? 'checked' : '' ?>> Enabled
                </label>
                <button type="submit" class="btn btn-secondary"><i class="fas fa-save"></i> Update</button>
                <button type="submit" formaction="/admin/settings/footer-links/delete"
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

<?php View::endSection(); ?>
