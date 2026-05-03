<?php use Core\View; use Core\Security; ?>
<?php View::extend('linkshortner:app'); ?>
<?php View::section('content'); ?>

<div class="card" style="max-width:700px;">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-plus-circle" style="color:var(--accent);"></i> Create Short Link</div>
    </div>

    <form method="POST" action="/projects/linkshortner/create">
        <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">

        <div class="form-group">
            <label class="form-label">Destination URL <span style="color:var(--red);">*</span></label>
            <input type="url" name="original_url" class="form-input" placeholder="https://example.com/your-long-url" required value="<?= View::e($_GET['url'] ?? '') ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Custom Code <span style="color:var(--text-secondary);">(optional)</span></label>
                <input type="text" name="custom_code" class="form-input" placeholder="e.g. my-offer (3–20 chars)" pattern="[a-zA-Z0-9_-]{3,20}">
            </div>
            <div class="form-group">
                <label class="form-label">Title <span style="color:var(--text-secondary);">(optional)</span></label>
                <input type="text" name="title" class="form-input" placeholder="Friendly name for this link">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Expiry Date <span style="color:var(--text-secondary);">(optional)</span></label>
                <input type="datetime-local" name="expires_at" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Click Limit <span style="color:var(--text-secondary);">(optional)</span></label>
                <input type="number" name="click_limit" class="form-input" placeholder="e.g. 1000" min="1">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Password Protection <span style="color:var(--text-secondary);">(optional)</span></label>
            <input type="password" name="password" class="form-input" placeholder="Leave empty for no password">
        </div>

        <!-- UTM Parameters -->
        <details style="margin-bottom:18px;">
            <summary style="cursor:pointer;color:var(--text-secondary);font-size:13px;padding:8px 0;">
                <i class="fas fa-tags"></i> UTM Parameters (optional)
            </summary>
            <div style="margin-top:12px;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">UTM Source</label>
                        <input type="text" name="utm_source" class="form-input" placeholder="e.g. google">
                    </div>
                    <div class="form-group">
                        <label class="form-label">UTM Medium</label>
                        <input type="text" name="utm_medium" class="form-input" placeholder="e.g. email">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">UTM Campaign</label>
                    <input type="text" name="utm_campaign" class="form-input" placeholder="e.g. spring_sale">
                </div>
            </div>
        </details>

        <div style="display:flex;gap:12px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-compress-alt"></i> Shorten URL</button>
            <a href="/projects/linkshortner/links" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php View::end(); ?>
