<?php
/**
 * ConvertX – Settings / API Keys View
 */
$currentView = 'settings';
$csrfToken   = \Core\Security::generateCsrfToken();
?>

<!-- Page header -->
<div class="page-header" style="margin-bottom:1.5rem;text-align:center;">
    <h1 style="font-size:2rem;font-weight:700;background:linear-gradient(135deg,var(--cx-primary),var(--cx-accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
        API Keys
    </h1>
    <p style="color:var(--text-secondary);margin-top:.4rem;">
        Authenticate your API requests with your personal key
    </p>
</div>

<div style="max-width:660px;">

    <!-- API key card -->
    <div class="card" style="border-color:rgba(99,102,241,.3);">
        <div class="card-header">
            <i class="fa-solid fa-key"></i> Your API Key
            <?php if ($apiKey): ?>
                <span class="ai-badge" style="margin-left:auto;">✓ Active</span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="fa-solid fa-shield-halved" style="color:var(--cx-primary);"></i>
                Include as <code style="background:rgba(99,102,241,.12);padding:.1rem .4rem;border-radius:.25rem;font-size:.85em;">X-Api-Key</code> header in all requests
            </label>
            <div style="display:flex;gap:.5rem;">
                <input type="<?= $apiKey ? 'password' : 'text' ?>" class="form-control" id="apiKeyDisplay"
                       value="<?= $apiKey ? htmlspecialchars($apiKey) : '' ?>"
                       placeholder="<?= $apiKey ? '' : '(no API key yet — generate one below)' ?>"
                       readonly
                       style="font-family:monospace;font-size:.85rem;<?= $apiKey ? 'letter-spacing:.04em;' : '' ?>">
                <?php if ($apiKey): ?>
                <button onclick="toggleKey()" class="btn btn-secondary" id="toggleBtn" title="Show/hide key">
                    <i class="fa-solid fa-eye" id="toggleIcon"></i>
                </button>
                <button onclick="copyKey(event)" class="btn btn-secondary" title="Copy to clipboard">
                    <i class="fa-solid fa-copy"></i>
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions">
            <form method="POST" action="/projects/convertx/settings" style="display:inline;">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="action" value="generate_api_key">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-rotate"></i> <?= $apiKey ? 'Regenerate Key' : 'Generate API Key' ?>
                </button>
            </form>

            <?php if ($apiKey): ?>
            <form method="POST" action="/projects/convertx/settings"
                  onsubmit="return confirm('Revoke API key? All integrations using this key will stop working immediately.')">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="action" value="revoke_api_key">
                <button type="submit" class="btn btn-danger">
                    <i class="fa-solid fa-ban"></i> Revoke Key
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick start card -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-terminal"></i> Quick Start
        </div>
        <pre style="background:var(--cx-code-bg);border:1px solid var(--border-color);border-radius:.5rem;padding:1rem;font-size:.78rem;overflow-x:auto;line-height:1.6;"><span style="color:var(--cx-primary);">curl</span> -X POST https://yourdomain.com/projects/convertx/api/convert \
  -H <span style="color:var(--cx-accent);">"X-Api-Key: <?= $apiKey ? htmlspecialchars($apiKey) : 'cx_your_api_key' ?>"</span> \
  -F <span style="color:var(--cx-success);">"file=@document.pdf"</span> \
  -F <span style="color:var(--cx-success);">"output_format=docx"</span></pre>
        <div style="margin-top:1rem;">
            <a href="/projects/convertx/docs" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-book-open"></i> Full API Documentation
            </a>
        </div>
    </div>

</div>

<script>
function toggleKey() {
    const input = document.getElementById('apiKeyDisplay');
    const icon  = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa-solid fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa-solid fa-eye';
    }
}

function copyKey(event) {
    const input = document.getElementById('apiKeyDisplay');
    const val   = input.value;
    if (!val) return;
    navigator.clipboard.writeText(val).then(() => {
        const btn = event.currentTarget;
        btn.innerHTML = '<i class="fa-solid fa-check"></i>';
        setTimeout(() => { btn.innerHTML = '<i class="fa-solid fa-copy"></i>'; }, 1500);
    });
}
</script>
