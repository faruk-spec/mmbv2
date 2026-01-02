<?php use Core\View; ?>
<?php View::extend('proshare:app'); ?>

<?php View::section('content'); ?>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid rgba(0, 240, 255, 0.1);">
        <h2 style="margin: 0; font-size: 1.3rem; color: var(--cyan);">
            <i class="fas fa-file"></i>
            File Preview
        </h2>
    </div>
    
    <div class="card-body">
        <!-- File Information -->
        <div style="margin-bottom: 30px;">
            <div class="info-grid" style="display: grid; grid-template-columns: 150px 1fr; gap: 15px; margin-bottom: 25px;">
                <div style="color: var(--text-muted); font-weight: 500;">Filename:</div>
                <div style="color: var(--text); word-break: break-all;">
                    <i class="fas fa-file" style="color: var(--cyan); margin-right: 8px;"></i>
                    <?= htmlspecialchars($file['original_name']) ?>
                </div>
                
                <div style="color: var(--text-muted); font-weight: 500;">File Size:</div>
                <div style="color: var(--text);"><?= $fileSize ?></div>
                
                <div style="color: var(--text-muted); font-weight: 500;">Type:</div>
                <div style="color: var(--text);"><?= htmlspecialchars($file['mime_type']) ?></div>
                
                <div style="color: var(--text-muted); font-weight: 500;">Uploaded:</div>
                <div style="color: var(--text);"><?= date('F d, Y \a\t H:i', strtotime($file['created_at'])) ?></div>
                
                <?php if ($file['expires_at']): ?>
                <div style="color: var(--text-muted); font-weight: 500;">Expires:</div>
                <div style="color: var(--warning);">
                    <i class="fas fa-clock"></i>
                    <?= date('F d, Y \a\t H:i', strtotime($file['expires_at'])) ?>
                </div>
                <?php endif; ?>
                
                <div style="color: var(--text-muted); font-weight: 500;">Downloads:</div>
                <div style="color: var(--text);">
                    <?= $file['downloads'] ?>
                    <?php if ($file['max_downloads']): ?>
                        / <?= $file['max_downloads'] ?> (<?= $file['max_downloads'] - $file['downloads'] ?> remaining)
                    <?php else: ?>
                        (unlimited)
                    <?php endif; ?>
                </div>
                
                <?php if ($file['self_destruct']): ?>
                <div style="color: var(--text-muted); font-weight: 500;">Self-Destruct:</div>
                <div style="color: var(--danger);">
                    <i class="fas fa-exclamation-triangle"></i>
                    File will be deleted after download
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- File Preview (for images) -->
        <?php if (strpos($file['mime_type'], 'image/') === 0): ?>
        <div style="margin-bottom: 30px; text-align: center; background: rgba(0, 240, 255, 0.05); padding: 20px; border-radius: 10px;">
            <img src="/projects/proshare/download/<?= $shortcode ?>" 
                 alt="<?= htmlspecialchars($file['original_name']) ?>" 
                 style="max-width: 100%; max-height: 600px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0, 240, 255, 0.2);">
        </div>
        <?php endif; ?>
        
        <!-- Download Button -->
        <div style="text-align: center; margin-top: 30px;">
            <a href="/projects/proshare/download/<?= $shortcode ?>" 
               class="btn btn-primary" 
               style="padding: 15px 40px; font-size: 1.1rem; display: inline-flex; align-items: center; gap: 10px;">
                <i class="fas fa-download"></i>
                Download File
            </a>
        </div>
        
        <!-- Share Link -->
        <div style="margin-top: 30px; padding: 20px; background: rgba(0, 240, 255, 0.05); border-radius: 10px; border: 1px solid rgba(0, 240, 255, 0.1);">
            <label style="display: block; margin-bottom: 10px; color: var(--text-muted); font-weight: 500;">
                <i class="fas fa-link"></i> Share Link:
            </label>
            <div style="display: flex; gap: 10px;">
                <input type="text" 
                       id="shareLink" 
                       value="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/s/<?= $shortcode ?>" 
                       readonly
                       style="flex: 1; padding: 12px; background: var(--dark); border: 1px solid rgba(0, 240, 255, 0.2); border-radius: 5px; color: var(--text); font-family: monospace;">
                <button onclick="copyShareLink()" class="btn btn-secondary" style="padding: 12px 20px;">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function copyShareLink() {
    const input = document.getElementById('shareLink');
    input.select();
    document.execCommand('copy');
    alert('Link copied to clipboard!');
}
</script>

<?php View::endSection(); ?>
