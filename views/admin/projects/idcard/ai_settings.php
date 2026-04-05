<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<style>
.ai-settings-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
}
.ai-settings-card h3 {
    font-size: 1rem;
    margin: 0 0 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 6px;
}
.form-group input[type="text"],
.form-group input[type="password"],
.form-group input[type="number"] {
    width: 100%;
    max-width: 480px;
    padding: 9px 12px;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 13px;
    transition: border-color 0.2s;
}
.form-group input:focus {
    outline: none;
    border-color: #6366f1;
}
.form-group small {
    display: block;
    font-size: 12px;
    color: var(--text-secondary);
    margin-top: 5px;
}
.token-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    max-width: 480px;
}
.token-wrap input { flex: 1; max-width: none; }
.token-toggle {
    padding: 9px 12px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-secondary);
    cursor: pointer;
    font-size: 13px;
    transition: all 0.2s;
}
.token-toggle:hover { border-color: #6366f1; color: #6366f1; }
.toggle-row {
    display: flex;
    align-items: center;
    gap: 12px;
}
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    flex-shrink: 0;
}
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.slider {
    position: absolute;
    inset: 0;
    background: var(--border-color);
    border-radius: 24px;
    transition: 0.3s;
    cursor: pointer;
}
.slider:before {
    content: '';
    position: absolute;
    width: 18px; height: 18px;
    left: 3px; bottom: 3px;
    background: #fff;
    border-radius: 50%;
    transition: 0.3s;
}
.toggle-switch input:checked + .slider { background: #6366f1; }
.toggle-switch input:checked + .slider:before { transform: translateX(20px); }
.badge-ai-on  { display:inline-flex;align-items:center;gap:5px;padding:3px 9px;background:rgba(34,197,94,.15);color:#22c55e;border-radius:20px;font-size:12px;font-weight:600; }
.badge-ai-off { display:inline-flex;align-items:center;gap:5px;padding:3px 9px;background:rgba(239,68,68,.1);color:#ef4444;border-radius:20px;font-size:12px;font-weight:600; }
.pro-badge { display:inline-flex;align-items:center;gap:5px;padding:2px 8px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border-radius:20px;font-size:11px;font-weight:700; }
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1><i class="fas fa-robot" style="color:#6366f1;"></i> CardX — AI Integration</h1>
        <p style="color:var(--text-secondary);">Configure OpenAI-powered ID card generation and pro features</p>
    </div>
    <a href="/admin/projects/idcard/settings" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Settings</a>
</div>

<?php if (!empty($_GET['saved'])): ?>
<div class="alert alert-success" style="margin-bottom:20px;"><i class="fas fa-check-circle"></i> AI settings saved successfully.</div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
<div class="alert alert-error" style="margin-bottom:20px;"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<form method="POST" action="/admin/projects/idcard/ai-settings">
    <?= \Core\Security::csrfField() ?>

    <!-- AI Integration (OpenAI) -->
    <div class="ai-settings-card">
        <h3><i class="fas fa-robot" style="color:#6366f1;"></i> AI Integration (OpenAI)</h3>

        <!-- Enable / Disable AI -->
        <div class="form-group">
            <label>AI-Powered Card Generation</label>
            <div class="toggle-row">
                <label class="toggle-switch">
                    <input type="checkbox" name="idcard_ai_enabled" value="1" id="ai_enabled_toggle"
                           <?= ($settings['idcard_ai_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>
                <span id="ai_status_badge">
                    <?php if (($settings['idcard_ai_enabled'] ?? '1') === '1'): ?>
                    <span class="badge-ai-on"><i class="fas fa-bolt"></i> AI Enabled</span>
                    <?php else: ?>
                    <span class="badge-ai-off"><i class="fas fa-times"></i> AI Disabled — using rule-based engine</span>
                    <?php endif; ?>
                </span>
            </div>
            <small>When enabled and a valid API key is provided, users get OpenAI-powered design suggestions. Falls back to rule-based tips automatically.</small>
        </div>

        <!-- OpenAI API Key -->
        <div class="form-group" id="ai_key_group">
            <label for="openai_key">OpenAI API Key</label>
            <div class="token-wrap">
                <input type="password" id="openai_key" name="idcard_openai_api_key"
                       value="<?= htmlspecialchars($settings['idcard_openai_api_key'] ?? '') ?>"
                       placeholder="sk-..."
                       autocomplete="off">
                <button type="button" class="token-toggle" onclick="toggleToken()" title="Show/hide key">
                    <i class="fas fa-eye" id="tokenEyeIcon"></i>
                </button>
            </div>
            <small>
                Get your API key at
                <a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener noreferrer"
                   style="color:#6366f1;">platform.openai.com/api-keys</a>.
                Leave blank to use the server environment constant (<code>OPENAI_API_KEY</code>).
            </small>
        </div>

        <!-- OpenAI Model -->
        <div class="form-group" id="ai_model_group">
            <label for="openai_model">OpenAI Model</label>
            <input type="text" id="openai_model" name="idcard_openai_model"
                   value="<?= htmlspecialchars($settings['idcard_openai_model'] ?? 'gpt-4o-mini') ?>"
                   placeholder="gpt-4o-mini"
                   style="max-width:280px;">
            <small>
                Recommended: <code>gpt-4o-mini</code> (fast &amp; cost-efficient) or <code>gpt-4o</code> (highest quality).
                Any OpenAI chat completions model is supported.
            </small>
        </div>

        <!-- Daily AI limit -->
        <div class="form-group" id="ai_limit_group">
            <label for="ai_daily_limit">Daily AI Suggestions Per User</label>
            <input type="number" id="ai_daily_limit" name="idcard_ai_daily_limit"
                   value="<?= (int)($settings['idcard_ai_daily_limit'] ?? 0) ?>"
                   min="0" max="9999" step="1" style="max-width:140px;">
            <small>
                Maximum AI suggestion calls allowed per user per day.
                Set to <code>0</code> for unlimited. Users who exceed the limit receive rule-based suggestions instead.
            </small>
        </div>
    </div>

    <!-- Pro Features -->
    <div class="ai-settings-card">
        <h3>
            <i class="fas fa-star" style="color:#f59e0b;"></i> Pro Features
            <span class="pro-badge"><i class="fas fa-crown"></i> PRO</span>
        </h3>
        <p style="font-size:13px;color:var(--text-secondary);margin-bottom:16px;">
            When pro features are enabled, advanced templates and design styles become available.
            These can be gated to specific subscription plans via Platform Plans.
        </p>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                <input type="checkbox" name="idcard_pro_templates" value="1"
                       <?= !empty($settings['idcard_pro_templates']) && $settings['idcard_pro_templates'] === '1' ? 'checked' : '' ?>>
                <span style="font-weight:600;">Enable Pro Templates</span>
            </label>
            <small style="margin-left:22px;">
                Marks advanced templates (Executive, Neon, Gradient Pro, Bank, Government, etc.) as pro-only.
                Users on free plans see a lock icon and upgrade prompt.
            </small>
        </div>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                <input type="checkbox" name="idcard_pro_styles" value="1"
                       <?= !empty($settings['idcard_pro_styles']) && $settings['idcard_pro_styles'] === '1' ? 'checked' : '' ?>>
                <span style="font-weight:600;">Enable Pro Design Styles</span>
            </label>
            <small style="margin-left:22px;">
                Marks premium design styles (Glass, Executive, Neon, Gradient Pro, Zigzag, Ribbon, etc.) as pro-only in the card generator.
            </small>
        </div>
    </div>

    <!-- How it works -->
    <div class="ai-settings-card" style="background:rgba(99,102,241,.04);border-color:rgba(99,102,241,.2);">
        <h3><i class="fas fa-info-circle" style="color:#6366f1;"></i> How AI Card Generation Works</h3>
        <ul style="color:var(--text-secondary);font-size:0.875rem;line-height:1.8;margin:0;padding-left:20px;">
            <li>When AI is enabled and a valid API key is configured, the card generator sends the template type, user-provided card data, and a design prompt to OpenAI.</li>
            <li>OpenAI returns tailored design suggestions — colour palettes, layout tips, style recommendations, and content improvements — specific to the card type.</li>
            <li>If the API call fails (network error, rate limit, invalid key, etc.), the system silently falls back to the built-in rule-based suggestion engine.</li>
            <li>The daily limit protects against unexpected API costs. Users over the limit receive rule-based suggestions without any error message.</li>
            <li>Pro templates and styles can be combined with Platform Plans to monetise premium card designs.</li>
        </ul>
    </div>

    <div style="display:flex;gap:12px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save AI Settings</button>
        <a href="/admin/projects/idcard" class="btn btn-secondary">Cancel</a>
    </div>
</form>

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

document.getElementById('ai_enabled_toggle')?.addEventListener('change', function () {
    const badge = document.getElementById('ai_status_badge');
    if (this.checked) {
        badge.innerHTML = '<span class="badge-ai-on"><i class="fas fa-bolt"></i> AI Enabled</span>';
    } else {
        badge.innerHTML = '<span class="badge-ai-off"><i class="fas fa-times"></i> AI Disabled — using rule-based engine</span>';
    }
});
</script>

<?php View::endSection(); ?>
