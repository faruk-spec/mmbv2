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
        
        <!-- Live Preview / Thumbnail -->
        <?php
        $mime      = $file['mime_type'];
        $serveUrl  = '/projects/proshare/serve/' . rawurlencode($shortcode);
        $isImage   = str_starts_with($mime, 'image/');
        $isVideo   = str_starts_with($mime, 'video/');
        $isAudio   = str_starts_with($mime, 'audio/');
        $isPdf     = $mime === 'application/pdf';
        $isArchive = str_contains($mime, 'zip') || str_contains($mime, 'rar') || str_contains($mime, 'tar') || str_contains($mime, '7z');
        $isWord    = str_contains($mime, 'word') || str_contains($mime, 'msword');
        $isExcel   = str_contains($mime, 'excel') || str_contains($mime, 'spreadsheet');
        $isText    = str_starts_with($mime, 'text/');
        ?>

        <div style="margin-bottom: 30px;">
            <?php if ($isImage): ?>
                <div style="text-align:center; background:rgba(0,240,255,0.05); padding:20px; border-radius:10px; border:1px solid rgba(0,240,255,0.15);">
                    <img src="<?= htmlspecialchars($serveUrl) ?>"
                         alt="<?= htmlspecialchars($file['original_name']) ?>"
                         style="max-width:100%; max-height:480px; border-radius:8px; object-fit:contain;"
                         loading="lazy">
                </div>
            <?php elseif ($isVideo): ?>
                <div style="background:#000; border-radius:10px; overflow:hidden; border:1px solid rgba(0,240,255,0.15);">
                    <video controls preload="metadata" style="width:100%; max-height:480px; display:block;">
                        <source src="<?= htmlspecialchars($serveUrl) ?>" type="<?= htmlspecialchars($mime) ?>">
                        Your browser does not support video playback.
                    </video>
                </div>
            <?php elseif ($isAudio): ?>
                <div style="background:rgba(0,240,255,0.05); padding:24px; border-radius:10px; border:1px solid rgba(0,240,255,0.15); text-align:center;">
                    <i class="fas fa-music" style="font-size:3rem; color:#1abc9c; margin-bottom:16px; display:block;"></i>
                    <audio controls preload="metadata" style="width:100%; max-width:500px;">
                        <source src="<?= htmlspecialchars($serveUrl) ?>" type="<?= htmlspecialchars($mime) ?>">
                        Your browser does not support audio playback.
                    </audio>
                </div>
            <?php elseif ($isPdf): ?>
                <div style="border-radius:10px; overflow:hidden; border:1px solid rgba(0,240,255,0.15);">
                    <iframe src="<?= htmlspecialchars($serveUrl) ?>#toolbar=1&navpanes=0"
                            style="width:100%; height:520px; border:none; display:block;"
                            title="PDF Preview"></iframe>
                </div>
            <?php else:
                // Thumbnail icon for ZIP, Word, Excel, text and any other type
                $iconClass = 'fa-file'; $iconColor = 'var(--cyan)';
                if ($isArchive)     { $iconClass = 'fa-file-archive';  $iconColor = '#f39c12'; }
                elseif ($isWord)    { $iconClass = 'fa-file-word';     $iconColor = '#2980b9'; }
                elseif ($isExcel)   { $iconClass = 'fa-file-excel';    $iconColor = '#27ae60'; }
                elseif ($isText)    { $iconClass = 'fa-file-alt';      $iconColor = '#95a5a6'; }
            ?>
                <div style="text-align:center; background:rgba(0,240,255,0.05); padding:40px 20px; border-radius:10px; border:1px solid rgba(0,240,255,0.1);">
                    <i class="fas <?= $iconClass ?>" style="font-size:6rem; color:<?= $iconColor ?>; margin-bottom:12px; display:block;"></i>
                    <div style="color:var(--text-muted); font-size:0.9rem;">No live preview available for this file type</div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Download Button -->
        <div style="text-align: center; margin-top: 30px;">
            <a href="/projects/proshare/download/<?= rawurlencode($shortcode) ?>" 
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
                       value="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/s/<?= rawurlencode($shortcode) ?>" 
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
    navigator.clipboard.writeText(input.value).catch(() => document.execCommand('copy'));
    const btn = input.nextElementSibling;
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    setTimeout(() => { btn.innerHTML = orig; }, 2000);
}
</script>

<?php View::endSection(); ?>

