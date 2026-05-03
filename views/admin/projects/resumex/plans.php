<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
.settings-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 28px 32px;
    margin-bottom: 24px;
}
.settings-card h3 {
    color: var(--cyan);
    font-size: 1rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin: 0 0 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border-color);
}
.form-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 18px;
}
.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.form-group label {
    font-size: .875rem;
    font-weight: 600;
    color: var(--text-primary);
}
.form-group small {
    color: var(--text-secondary);
    font-size: .78rem;
    margin-top: 2px;
}
.form-group input[type="text"],
.form-group input[type="number"] {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-primary);
    padding: 9px 12px;
    font-size: .9rem;
    transition: border-color .2s;
    width: 100%;
    box-sizing: border-box;
}
.form-group input:focus { outline: none; border-color: var(--cyan); }
.toggle-row { display: flex; align-items: center; gap: 14px; }
.toggle-switch { position: relative; width: 48px; height: 26px; flex-shrink: 0; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-switch .slider {
    position: absolute; inset: 0;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 26px; cursor: pointer;
    transition: background .25s, border-color .25s;
}
.toggle-switch .slider::before {
    content: ''; position: absolute;
    width: 18px; height: 18px;
    left: 3px; top: 3px;
    background: var(--text-secondary);
    border-radius: 50%;
    transition: transform .25s, background .25s;
}
.toggle-switch input:checked + .slider { background: var(--cyan); border-color: var(--cyan); }
.toggle-switch input:checked + .slider::before { transform: translateX(22px); background: #fff; }
.toggle-label { font-size: .9rem; color: var(--text-primary); }
.alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: .9rem; }
.alert-success { background: rgba(0,255,136,.1); color: var(--green, #00ff88); border: 1px solid var(--green, #00ff88); }
.alert-danger  { background: rgba(255,107,107,.1); color: var(--red, #ff6b6b); border: 1px solid var(--red, #ff6b6b); }
.btn-save {
    background: linear-gradient(135deg, var(--cyan), var(--magenta));
    color: #fff; border: none; border-radius: 8px;
    padding: 10px 28px; font-size: .9rem; font-weight: 600;
    cursor: pointer; transition: opacity .2s;
}
.btn-save:hover { opacity: .88; }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1><i class="fas fa-crown" style="color:#f59e0b;"></i> ResumeX — Plans &amp; Pro Features</h1>
        <p style="color:var(--text-secondary);">Control what free and pro users can access in ResumeX</p>
    </div>
    <a href="/admin/projects/resumex" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Overview</a>
</div>

<?php if (!empty($success)): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" action="/admin/projects/resumex/plans">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <!-- Resume Limits -->
    <div class="settings-card">
        <h3><i class="fas fa-file-alt" style="margin-right:8px;"></i>Resume Limits</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="max_resumes_free">Max Resumes — Free Users</label>
                <input type="number" id="max_resumes_free" name="resumex_max_resumes_free"
                       value="<?= (int)($settings['resumex_max_resumes_free'] ?? 3) ?>"
                       min="0" max="9999" step="1" style="max-width:140px;">
                <small>Maximum number of resumes a free user can create. Set to <code>0</code> for unlimited.</small>
            </div>
            <div class="form-group">
                <label for="max_resumes_pro">Max Resumes — Pro Users</label>
                <input type="number" id="max_resumes_pro" name="resumex_max_resumes_pro"
                       value="<?= (int)($settings['resumex_max_resumes_pro'] ?? 0) ?>"
                       min="0" max="9999" step="1" style="max-width:140px;">
                <small>Maximum number of resumes a Pro user can create. Set to <code>0</code> for unlimited.</small>
            </div>
        </div>
    </div>

    <!-- PDF Export -->
    <div class="settings-card">
        <h3><i class="fas fa-file-pdf" style="margin-right:8px;"></i>PDF Export</h3>
        <div class="form-row">
            <div class="form-group">
                <label>PDF Watermark on Free Exports</label>
                <div class="toggle-row">
                    <label class="toggle-switch">
                        <input type="checkbox" name="resumex_pdf_watermark_free" value="1"
                               <?= ($settings['resumex_pdf_watermark_free'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                    <span class="toggle-label">Add a watermark to PDFs exported by free users</span>
                </div>
                <small>When enabled, PDF exports for non-pro users will include the watermark text below.</small>
            </div>
            <div class="form-group" style="margin-top:-8px;padding-left:4px;">
                <label>Watermark Text</label>
                <input type="text" name="resumex_pdf_watermark_text"
                       value="<?= htmlspecialchars($settings['resumex_pdf_watermark_text'] ?? 'ResumeX Free') ?>"
                       maxlength="80" placeholder="e.g. ResumeX Free"
                       style="max-width:360px;">
                <small>Text displayed diagonally across the PDF. Keep it short (e.g. "ResumeX Free" or your brand name).</small>
            </div>
        </div>
    </div>

    <!-- Feature Access -->
    <div class="settings-card">
        <h3><i class="fas fa-sliders-h" style="margin-right:8px;"></i>Feature Access</h3>
        <div class="form-row">
            <div class="form-group">
                <label>Pro-Only Premium Templates</label>
                <div class="toggle-row">
                    <label class="toggle-switch">
                        <input type="checkbox" name="resumex_pro_templates_only" value="1"
                               <?= ($settings['resumex_pro_templates_only'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                    <span class="toggle-label">Restrict designer/custom templates to Pro users only</span>
                </div>
                <small>When enabled, uploaded and designer-created templates are locked to pro plan users.</small>
            </div>
            <div class="form-group">
                <label>LinkedIn Import</label>
                <div class="toggle-row">
                    <label class="toggle-switch">
                        <input type="checkbox" name="resumex_linkedin_import" value="1"
                               <?= ($settings['resumex_linkedin_import'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                    <span class="toggle-label">Allow users to import their LinkedIn profile data</span>
                </div>
            </div>
            <div class="form-group">
                <label>Public Resume Sharing</label>
                <div class="toggle-row">
                    <label class="toggle-switch">
                        <input type="checkbox" name="resumex_public_resumes" value="1"
                               <?= ($settings['resumex_public_resumes'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                    <span class="toggle-label">Allow users to create shareable public resume links</span>
                </div>
            </div>
            <div class="form-group">
                <label>Custom Domain (Pro Only)</label>
                <div class="toggle-row">
                    <label class="toggle-switch">
                        <input type="checkbox" name="resumex_custom_domain" value="1"
                               <?= ($settings['resumex_custom_domain'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                    <span class="toggle-label">Allow Pro users to use a custom domain for their resume</span>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;justify-content:flex-end;">
        <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Plan Settings</button>
    </div>
</form>
<?php View::endSection(); ?>
