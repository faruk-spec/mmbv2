<?php use Core\View; use Core\Security; ?>
<?php View::extend('linkshortner:app'); ?>
<?php View::section('content'); ?>

<div class="card" style="max-width:700px;">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-edit" style="color:var(--accent);"></i> Edit Link</div>
        <a href="/projects/linkshortner/analytics/<?= View::e($link['code']) ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-chart-bar"></i> Analytics
        </a>
    </div>

    <form method="POST" action="/projects/linkshortner/links/<?= $link['id'] ?>/update">
        <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">

        <div class="form-group">
            <label class="form-label">Short Code</label>
            <input type="text" class="form-input" value="/l/<?= View::e($link['code']) ?>" readonly style="opacity:0.6;">
        </div>

        <div class="form-group">
            <label class="form-label">Destination URL <span style="color:var(--red);">*</span></label>
            <input type="url" name="original_url" class="form-input" value="<?= View::e($link['original_url']) ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-input" value="<?= View::e($link['title'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Click Limit</label>
                <input type="number" name="click_limit" class="form-input" value="<?= View::e($link['click_limit'] ?? '') ?>" min="1">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Expiry Date</label>
            <input type="datetime-local" name="expires_at" class="form-input"
                   value="<?= $link['expires_at'] ? date('Y-m-d\TH:i', strtotime($link['expires_at'])) : '' ?>">
        </div>

        <!-- UTM Parameters -->
        <details style="margin-bottom:18px;">
            <summary style="cursor:pointer;color:var(--text-secondary);font-size:13px;padding:8px 0;">
                <i class="fas fa-tags"></i> UTM Parameters
            </summary>
            <div style="margin-top:12px;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">UTM Source</label>
                        <input type="text" name="utm_source" class="form-input" value="<?= View::e($link['utm_source'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">UTM Medium</label>
                        <input type="text" name="utm_medium" class="form-input" value="<?= View::e($link['utm_medium'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">UTM Campaign</label>
                    <input type="text" name="utm_campaign" class="form-input" value="<?= View::e($link['utm_campaign'] ?? '') ?>">
                </div>
            </div>
        </details>

        <div style="display:flex;gap:12px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            <a href="/projects/linkshortner/links" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php View::end(); ?>
