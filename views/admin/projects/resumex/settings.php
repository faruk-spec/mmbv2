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
.form-group input[type="password"],
.form-group input[type="url"] {
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
.form-group input:focus {
    outline: none;
    border-color: var(--cyan);
}
.toggle-row {
    display: flex;
    align-items: center;
    gap: 14px;
}
.toggle-switch {
    position: relative;
    width: 48px;
    height: 26px;
    flex-shrink: 0;
}
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-switch .slider {
    position: absolute;
    inset: 0;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 26px;
    cursor: pointer;
    transition: background .25s, border-color .25s;
}
.toggle-switch .slider::before {
    content: '';
    position: absolute;
    width: 18px; height: 18px;
    left: 3px; top: 3px;
    background: var(--text-secondary);
    border-radius: 50%;
    transition: transform .25s, background .25s;
}
.toggle-switch input:checked + .slider { background: var(--cyan); border-color: var(--cyan); }
.toggle-switch input:checked + .slider::before { transform: translateX(22px); background: #fff; }
.toggle-label { font-size: .9rem; color: var(--text-primary); }
.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: .9rem;
}
.alert-success { background: rgba(0,255,136,.1); color: var(--green, #00ff88); border: 1px solid var(--green, #00ff88); }
.alert-danger  { background: rgba(255,107,107,.1); color: var(--red, #ff6b6b); border: 1px solid var(--red, #ff6b6b); }
.btn-save {
    background: linear-gradient(135deg, var(--cyan), var(--magenta));
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 28px;
    font-size: .9rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity .2s;
}
.btn-save:hover { opacity: .88; }
.token-wrap { position: relative; }
.token-wrap input { padding-right: 42px; }
.token-toggle {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    font-size: 1rem;
    padding: 0;
}
.token-toggle:hover { color: var(--cyan); }
.badge-ai-on  { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.75rem; font-weight:600; background:rgba(0,240,255,.15); color:var(--cyan); border:1px solid var(--cyan); }
.badge-ai-off { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.75rem; font-weight:600; background:rgba(255,107,107,.12); color:var(--red,#ff6b6b); border:1px solid var(--red,#ff6b6b); }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1><i class="fas fa-cog" style="color:var(--cyan);"></i> ResumeX — Settings</h1>
        <p style="color:var(--text-secondary);">Configure AI API integration and feature options</p>
    </div>
    <a href="/admin/projects/resumex" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Overview</a>
</div>

<?php if (!empty($success)): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" action="/admin/projects/resumex/settings">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <!-- AI Integration Card -->
    <div class="settings-card">
        <h3><i class="fas fa-robot" style="margin-right:8px;"></i>AI Integration (OpenAI)</h3>

        <div class="form-row">
            <!-- Enable / disable AI -->
            <div class="form-group">
                <label>AI-Powered Suggestions</label>
                <div class="toggle-row">
                    <label class="toggle-switch">
                        <input type="checkbox" name="resumex_ai_enabled" value="1"
                               <?= ($settings['resumex_ai_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                    <span class="toggle-label">
                        <?php if (($settings['resumex_ai_enabled'] ?? '1') === '1'): ?>
                        <span class="badge-ai-on"><i class="fas fa-bolt"></i> AI Enabled</span>
                        <?php else: ?>
                        <span class="badge-ai-off"><i class="fas fa-times"></i> AI Disabled — using rule-based engine</span>
                        <?php endif; ?>
                    </span>
                </div>
                <small>When disabled, the rule-based engine is used for all suggestions without calling external APIs.</small>
            </div>

            <!-- OpenAI API Key -->
            <div class="form-group">
                <label for="openai_key">OpenAI API Key</label>
                <div class="token-wrap">
                    <input type="password" id="openai_key" name="resumex_openai_api_key"
                           value="<?= htmlspecialchars($settings['resumex_openai_api_key'] ?? '') ?>"
                           placeholder="sk-..."
                           autocomplete="off">
                    <button type="button" class="token-toggle" onclick="toggleToken()" title="Show/hide key">
                        <i class="fas fa-eye" id="tokenEyeIcon"></i>
                    </button>
                </div>
                <small>
                    Get your API key at
                    <a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener noreferrer"
                       style="color:var(--cyan);">platform.openai.com/api-keys</a>.
                    Leave blank to use the server environment constant (<code>OPENAI_API_KEY</code>).
                </small>
            </div>

            <!-- OpenAI Model -->
            <div class="form-group">
                <label for="openai_model">OpenAI Model</label>
                <input type="text" id="openai_model" name="resumex_openai_model"
                       value="<?= htmlspecialchars($settings['resumex_openai_model'] ?? 'gpt-4o-mini') ?>"
                       placeholder="gpt-4o-mini">
                <small>
                    Recommended: <code>gpt-4o-mini</code> (fast &amp; cheap) or <code>gpt-4o</code> (highest quality).
                    Any OpenAI chat model can be used.
                </small>
            </div>

            <!-- Daily AI generation limit -->
            <div class="form-group">
                <label for="ai_daily_limit">Daily AI Generations Per User</label>
                <input type="number" id="ai_daily_limit" name="resumex_ai_daily_limit"
                       value="<?= (int)($settings['resumex_ai_daily_limit'] ?? 0) ?>"
                       min="0" max="9999" step="1" style="max-width:140px;">
                <small>
                    Maximum AI suggestion calls allowed per user per day.
                    Set to <code>0</code> for unlimited.
                    Users who exceed the limit automatically receive rule-based suggestions instead.
                </small>
            </div>
        </div>
    </div>

    <!-- Info box -->
    <div class="settings-card" style="background:rgba(0,240,255,.04);border-color:rgba(0,240,255,.2);">
        <h3><i class="fas fa-info-circle" style="margin-right:8px;"></i>How it works</h3>
        <ul style="color:var(--text-secondary);font-size:.875rem;line-height:1.8;margin:0;padding-left:20px;">
            <li>When AI is enabled and a valid API key is provided, the resume builder sends the job title and experience level to OpenAI to generate a tailored summary, skills list, and bullet points.</li>
            <li>If the API call fails (network error, rate-limit, invalid key, etc.), the error is <strong style="color:var(--text-primary);">logged</strong> and an <strong style="color:var(--text-primary);">admin notification</strong> is sent to the notification bell.</li>
            <li>The user always receives helpful suggestions — either AI-generated or from the built-in rule-based engine as a fallback.</li>
            <li>Disabling AI or leaving the key blank skips the API call entirely and uses the rule-based engine.</li>
            <li>The <strong style="color:var(--text-primary);">Daily AI Generations Per User</strong> limit resets every day at midnight. Users who reach the limit transparently receive rule-based suggestions for the rest of the day.</li>
        </ul>
    </div>

    <!-- Pro Features Card -->
    <div class="settings-card">
        <h3><i class="fas fa-crown" style="margin-right:8px;color:#f59e0b;"></i>Pro Feature Controls</h3>
        <div class="form-row">

            <!-- Max resumes for free users -->
            <div class="form-group">
                <label for="max_resumes_free">Max Resumes — Free Users</label>
                <input type="number" id="max_resumes_free" name="resumex_max_resumes_free"
                       value="<?= (int)($settings['resumex_max_resumes_free'] ?? 3) ?>"
                       min="0" max="9999" step="1" style="max-width:140px;">
                <small>Maximum number of resumes a free user can create. Set to <code>0</code> for unlimited.</small>
            </div>

            <!-- Max resumes for pro users -->
            <div class="form-group">
                <label for="max_resumes_pro">Max Resumes — Pro Users</label>
                <input type="number" id="max_resumes_pro" name="resumex_max_resumes_pro"
                       value="<?= (int)($settings['resumex_max_resumes_pro'] ?? 0) ?>"
                       min="0" max="9999" step="1" style="max-width:140px;">
                <small>Maximum number of resumes a Pro user can create. Set to <code>0</code> for unlimited.</small>
            </div>

            <!-- PDF Watermark for free users -->
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

            <!-- PDF Watermark Text -->
            <div class="form-group" style="margin-top:-8px;padding-left:4px;">
                <label>Watermark Text</label>
                <input type="text" name="resumex_pdf_watermark_text" class="form-input"
                       value="<?= htmlspecialchars($settings['resumex_pdf_watermark_text'] ?? 'ResumeX Free') ?>"
                       maxlength="80" placeholder="e.g. ResumeX Free"
                       style="max-width:360px;">
                <small>Text displayed diagonally across the PDF. Keep it short (e.g. "ResumeX Free" or your brand name).</small>
            </div>

            <!-- Pro-only templates -->
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

            <!-- LinkedIn Import -->
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

            <!-- Public Resume Sharing -->
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

            <!-- Custom Domain -->
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

    <!-- Sample Template Downloads -->
    <div class="settings-card" style="background:rgba(153,69,255,.04);border-color:rgba(153,69,255,.2);">
        <h3><i class="fas fa-download" style="margin-right:8px;color:#a78bfa;"></i>Sample Templates</h3>
        <p style="color:var(--text-secondary);font-size:.875rem;margin:0 0 14px;">
            Download sample PHP template files to use as a starting point for custom templates.
        </p>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <a href="/admin/projects/resumex/templates/sample-download" class="btn btn-secondary">
                <i class="fas fa-file-code"></i> Download sample-template.php
            </a>
            <a href="/admin/projects/resumex/templates/sample-full-download" class="btn btn-secondary">
                <i class="fas fa-file-archive"></i> Download sample-full-template.php
            </a>
        </div>
    </div>

    <div style="display:flex;justify-content:flex-end;">
        <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Settings</button>
    </div>
</form>
<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<script>
function toggleToken() {
    const input = document.getElementById('openai_key');
    const icon  = document.getElementById('tokenEyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}
// Live toggle badge update
document.querySelector('input[name="resumex_ai_enabled"]')?.addEventListener('change', function () {
    const badge = this.closest('.toggle-row').querySelector('.toggle-label span');
    if (badge) {
        if (this.checked) {
            badge.className = 'badge-ai-on';
            badge.innerHTML = '<i class="fas fa-bolt"></i> AI Enabled';
        } else {
            badge.className = 'badge-ai-off';
            badge.innerHTML = '<i class="fas fa-times"></i> AI Disabled — using rule-based engine';
        }
    }
});
</script>
<?php View::endSection(); ?>
