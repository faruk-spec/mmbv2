<?php use Core\View; use Core\Security; ?>
<?php View::extend('proshare:app'); ?>

<?php View::section('content'); ?>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-file-alt"></i> Share Text
        </h3>
    </div>
    
    <form id="textShareForm" method="POST" action="/projects/proshare/text/create">
        <input type="hidden" name="csrf_token" value="<?= Security::generateToken() ?>">
        
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-heading"></i> Title (Optional)
            </label>
            <input type="text" name="title" class="form-control" placeholder="e.g., Meeting Notes, Code Snippet">
        </div>
        
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-align-left"></i> Content
            </label>
            <textarea name="content" class="form-control" rows="12" placeholder="Paste your text, code, or notes here..." required></textarea>
        </div>
        
        <div class="grid grid-2">
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-clock"></i> Link Expiry
                </label>
                <select name="expiry" class="form-control">
                    <option value="1">1 Hour</option>
                    <option value="6">6 Hours</option>
                    <option value="24" selected>24 Hours</option>
                    <option value="168">7 Days</option>
                    <option value="720">30 Days</option>
                    <option value="0">Never</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-eye"></i> Max Views
                </label>
                <select name="max_views" class="form-control">
                    <option value="">Unlimited</option>
                    <option value="1">1 View</option>
                    <option value="5">5 Views</option>
                    <option value="10">10 Views</option>
                    <option value="50">50 Views</option>
                    <option value="100">100 Views</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-lock"></i> Password Protection (Optional)
            </label>
            <input type="password" name="password" class="form-control" placeholder="Leave empty for no password">
        </div>
        
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="self_destruct" value="1">
                <span><i class="fas fa-fire"></i> Self-destruct after first view</span>
            </label>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-share-alt"></i> Create Share Link
        </button>
    </form>
</div>

<!-- Result Panel (shown after successful share) -->
<?php if (isset($shareLink)): ?>
<div class="card">
    <div style="padding: 40px; text-align: center;">
        <i class="fas fa-check-circle" style="font-size: 4rem; color: var(--green); margin-bottom: 20px; display: block;"></i>
        <h3 style="color: var(--green); margin-bottom: 20px;">Text Share Created!</h3>
        
        <div style="background: rgba(0, 240, 255, 0.05); border: 1px solid var(--border-color); border-radius: 8px; padding: 20px; margin: 30px 0;">
            <div style="font-weight: 600; margin-bottom: 15px; color: var(--text-primary);">
                <i class="fas fa-link"></i> Your Share Link
            </div>
            <div style="display: flex; gap: 10px;">
                <input type="text" id="shareLink" value="<?= View::e($shareLink) ?>" readonly class="form-control" style="flex: 1;">
                <button onclick="copyToClipboard()" class="btn btn-secondary" style="white-space: nowrap;">
                    <i class="fas fa-copy"></i> Copy
                </button>
                <a href="<?= View::e($shareLink) ?>" target="_blank" class="btn btn-secondary" style="white-space: nowrap;">
                    <i class="fas fa-external-link-alt"></i> Open
                </a>
            </div>
        </div>
        
        <div style="margin-top: 30px;">
            <a href="/projects/proshare/dashboard" class="btn btn-primary">
                <i class="fas fa-home"></i> Back to Dashboard
            </a>
            <a href="/projects/proshare/text" class="btn btn-secondary">
                <i class="fas fa-plus"></i> Share More Text
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<script>
    function copyToClipboard() {
        const linkInput = document.getElementById('shareLink');
        if (linkInput) {
            linkInput.select();
            document.execCommand('copy');
            alert('Link copied to clipboard!');
        }
    }
</script>
<?php View::endSection(); ?>
