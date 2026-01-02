<a href="/projects/proshare/files" class="back-link">← Back to My Files</a>

<h1 style="margin-bottom: 30px;">Share File</h1>

<div class="grid grid-2">
    <div class="card">
        <h3 style="margin-bottom: 20px;">File Details</h3>
        
        <div class="file-item" style="margin-bottom: 20px;">
            <div class="file-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14,2 14,8 20,8"/>
                </svg>
            </div>
            <div class="file-info">
                <div class="file-name"><?= htmlspecialchars($file['original_name']) ?></div>
                <div class="file-meta">
                    <?= round($file['size'] / 1024, 2) ?> KB • 
                    <?= $file['downloads'] ?> downloads
                </div>
            </div>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label class="form-label">Share Link</label>
            <div style="display: flex; gap: 10px;">
                <input type="text" class="form-input" value="<?= htmlspecialchars($shareUrl) ?>" 
                       id="shareLink" readonly style="flex: 1;">
                <button type="button" class="btn btn-primary" onclick="copyLink()">Copy</button>
            </div>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <a href="/projects/proshare/download/<?= htmlspecialchars($file['short_code']) ?>" 
               class="btn btn-secondary">Download</a>
        </div>
    </div>
    
    <div class="card">
        <h3 style="margin-bottom: 20px;">Share Statistics</h3>
        
        <div style="text-align: center; padding: 30px 0;">
            <div style="font-size: 3rem; font-weight: 700; color: var(--orange);"><?= $file['downloads'] ?></div>
            <div style="color: var(--text-secondary);">Total Downloads</div>
        </div>
        
        <div style="border-top: 1px solid var(--border-color); padding-top: 20px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span style="color: var(--text-secondary);">Created</span>
                <span><?= date('M d, Y H:i', strtotime($file['created_at'])) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-secondary);">File Type</span>
                <span><?= htmlspecialchars($file['mime_type']) ?></span>
            </div>
        </div>
    </div>
</div>

<script>
function copyLink() {
    const input = document.getElementById('shareLink');
    input.select();
    document.execCommand('copy');
    alert('Link copied to clipboard!');
}
</script>
