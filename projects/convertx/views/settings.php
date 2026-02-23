<?php
/**
 * ConvertX – Settings View
 */
$currentView = 'settings';
$csrfToken   = \Core\Security::generateCsrfToken();
?>

<div class="card" style="max-width:620px;">
    <div class="card-header"><i class="fa-solid fa-key"></i> API Key Management</div>

    <div class="form-group">
        <label>Your API Key</label>
        <div style="display:flex;gap:.5rem;">
            <input type="text" class="form-control" id="apiKeyDisplay"
                   value="<?= $apiKey ? htmlspecialchars($apiKey) : '(none)' ?>"
                   readonly style="font-family:monospace;font-size:.85rem;">
            <?php if ($apiKey): ?>
            <button onclick="copyKey()" class="btn btn-secondary" style="white-space:nowrap;">
                <i class="fa-solid fa-copy"></i> Copy
            </button>
            <?php endif; ?>
        </div>
        <p style="font-size:.8rem;color:var(--text-muted);margin-top:.4rem;">
            Include this key as <code>X-Api-Key</code> header in all API requests.
        </p>
    </div>

    <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
        <form method="POST" action="/projects/convertx/settings" style="display:inline;">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="action" value="generate_api_key">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-rotate"></i> <?= $apiKey ? 'Regenerate Key' : 'Generate API Key' ?>
            </button>
        </form>

        <?php if ($apiKey): ?>
        <form method="POST" action="/projects/convertx/settings"
              onsubmit="return confirm('Revoke API key? All integrations using this key will stop working.')">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="action" value="revoke_api_key">
            <button type="submit" class="btn btn-danger">
                <i class="fa-solid fa-ban"></i> Revoke Key
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="max-width:620px;">
    <div class="card-header"><i class="fa-solid fa-book-open"></i> Quick Start</div>
    <pre style="background:rgba(0,0,0,.4);border-radius:8px;padding:1rem;font-size:.8rem;overflow-x:auto;">curl -X POST https://yourdomain.com/projects/convertx/api/convert \
  -H "X-Api-Key: <?= $apiKey ? htmlspecialchars($apiKey) : 'cx_your_api_key' ?>" \
  -F "file=@document.pdf" \
  -F "output_format=docx"</pre>
    <p style="margin-top:.75rem;">
        <a href="/projects/convertx/docs" style="color:var(--cx-primary);font-size:.875rem;">
            <i class="fa-solid fa-book-open"></i> Full API documentation →
        </a>
    </p>
</div>

<script>
function copyKey() {
    const input = document.getElementById('apiKeyDisplay');
    navigator.clipboard.writeText(input.value).then(() => {
        alert('API key copied to clipboard!');
    });
}
</script>
