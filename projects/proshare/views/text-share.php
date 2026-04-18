<?php use Core\View; use Core\Security; ?>
<?php View::extend('proshare:app'); ?>

<?php View::section('content'); ?>

<div class="card mb-3" id="textShareForm">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-file-alt"></i> Share Text
        </h3>
    </div>
    
    <form id="shareForm">
        <input type="hidden" name="_csrf_token" value="<?= Security::generateToken() ?>">
        
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-heading"></i> Title <span class="text-muted">(optional)</span>
            </label>
            <input type="text" name="title" class="form-control" placeholder="e.g., Meeting Notes, Code Snippet">
        </div>
        
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-align-left"></i> Content <span style="color: var(--ps-danger);">*</span>
            </label>
            <textarea name="content" id="contentArea" class="form-control" rows="12" placeholder="Paste your text, code, or notes here…" required style="font-family: 'Courier New', monospace; resize: vertical;"></textarea>
            <div class="text-muted" style="font-size: 0.8rem; margin-top: 4px; text-align: right;">
                <span id="charCount">0</span> characters
            </div>
        </div>
        
        <div class="ps-grid ps-grid-2" style="margin-bottom: 1rem;">
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
                <i class="fas fa-lock"></i> Password Protection <span class="text-muted">(optional)</span>
            </label>
            <input type="password" name="password" class="form-control" placeholder="Leave empty for no password">
        </div>
        
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 12px 16px; background: var(--bg-secondary); border-radius: 8px; border: 1px solid var(--border-color);">
                <input type="checkbox" name="self_destruct" value="1" style="width: 16px; height: 16px; accent-color: var(--cyan);">
                <span><i class="fas fa-fire" style="color: var(--ps-danger);"></i> Self-destruct after first view</span>
            </label>
        </div>
        
        <div id="shareError" style="display: none;" class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <span id="shareErrorMsg"></span>
        </div>
        
        <button type="submit" id="shareBtn" class="btn btn-primary">
            <i class="fas fa-share-alt"></i> Create Share Link
        </button>
    </form>
</div>

<!-- Result Panel (shown after successful share) -->
<div class="card" id="resultPanel" style="display: none;">
    <div style="padding: 2rem; text-align: center;">
        <i class="fas fa-check-circle" style="font-size: 3.5rem; color: var(--green); margin-bottom: 1rem; display: block;"></i>
        <h3 style="color: var(--green); margin-bottom: 1.25rem;">Text Share Created!</h3>
        
        <div style="background: rgba(0, 240, 255, 0.04); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.25rem; margin: 1.25rem 0;">
            <div style="font-weight: 600; margin-bottom: 12px; color: var(--text-secondary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.06em;">
                <i class="fas fa-link"></i> Your Share Link
            </div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <input type="text" id="shareLinkInput" value="" readonly class="form-control" style="flex: 1; min-width: 0; font-size: 0.875rem;">
                <button onclick="copyShareLink(this)" class="btn btn-secondary" style="white-space: nowrap; padding: 8px 16px;">
                    <i class="fas fa-copy"></i> Copy
                </button>
                <a id="shareLinkOpen" href="#" target="_blank" class="btn btn-secondary" style="white-space: nowrap; padding: 8px 16px;">
                    <i class="fas fa-external-link-alt"></i> Open
                </a>
            </div>
        </div>
        
        <div style="margin-top: 1.5rem; display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <a href="/projects/proshare/dashboard" class="btn btn-primary">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <button onclick="resetForm()" class="btn btn-secondary">
                <i class="fas fa-plus"></i> Share More Text
            </button>
        </div>
    </div>
</div>

<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<script>
    const contentArea  = document.getElementById('contentArea');
    const charCount    = document.getElementById('charCount');
    const shareForm    = document.getElementById('shareForm');
    const shareBtn     = document.getElementById('shareBtn');
    const shareError   = document.getElementById('shareError');
    const shareErrorMsg = document.getElementById('shareErrorMsg');
    const resultPanel  = document.getElementById('resultPanel');
    const formCard     = document.getElementById('textShareForm');
    
    contentArea.addEventListener('input', () => {
        charCount.textContent = contentArea.value.length.toLocaleString();
    });
    
    shareForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        shareError.style.display = 'none';
        shareBtn.disabled = true;
        shareBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating…';
        
        const fd = new FormData(shareForm);
        
        try {
            const response = await fetch('/projects/proshare/text/create', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: fd
            });
            
            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (_) {
                throw new Error('Server returned an unexpected response. Please try again.');
            }
            
            if (data.success) {
                const link = data.share_url || data.share_link || '';
                document.getElementById('shareLinkInput').value = link;
                document.getElementById('shareLinkOpen').href = link;
                formCard.style.display = 'none';
                resultPanel.style.display = 'block';
            } else {
                showError(data.error || 'Failed to create text share. Please try again.');
            }
        } catch (err) {
            showError(err.message || 'An error occurred. Please try again.');
        } finally {
            shareBtn.disabled = false;
            shareBtn.innerHTML = '<i class="fas fa-share-alt"></i> Create Share Link';
        }
    });
    
    function showError(msg) {
        shareErrorMsg.textContent = msg;
        shareError.style.display = 'flex';
    }
    
    function copyShareLink(btn) {
        const link = document.getElementById('shareLinkInput').value;
        navigator.clipboard.writeText(link).then(() => {
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            setTimeout(() => { btn.innerHTML = orig; }, 2000);
        });
    }
    
    function resetForm() {
        shareForm.reset();
        charCount.textContent = '0';
        resultPanel.style.display = 'none';
        formCard.style.display = 'block';
        shareError.style.display = 'none';
    }
</script>
<?php View::endSection(); ?>
