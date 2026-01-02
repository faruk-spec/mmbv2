<?php use Core\View; ?>
<?php View::extend('imgtxt'); ?>

<?php View::section('styles'); ?>
<style>
    .result-info {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .info-row {
        display: flex;
        padding: 10px 0;
        border-bottom: 1px solid var(--border-color);
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-row .label {
        font-weight: 600;
        color: var(--green);
        min-width: 120px;
    }
    
    .result-image {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .result-image h2 {
        color: var(--text-primary);
        font-size: 1.2rem;
        margin-bottom: 15px;
    }
    
    .result-image img {
        max-width: 100%;
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }
    
    .result-text {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
    }
    
    .result-text h2 {
        color: var(--text-primary);
        font-size: 1.2rem;
        margin-bottom: 15px;
    }
    
    .text-actions {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .result-text pre {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 15px;
        max-height: 500px;
        overflow-y: auto;
        white-space: pre-wrap;
        word-wrap: break-word;
        color: var(--text-primary);
        font-family: 'Courier New', monospace;
        font-size: 14px;
        line-height: 1.6;
    }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>

<div class="result-info">
    <div class="info-row">
        <span class="label">File:</span>
        <span><?= View::e($job['original_filename'] ?? $job['filename'] ?? 'N/A') ?></span>
    </div>
    <div class="info-row">
        <span class="label">Language:</span>
        <span><?= View::e(strtoupper($job['language'] ?? 'ENG')) ?></span>
    </div>
    <div class="info-row">
        <span class="label">Status:</span>
        <span class="badge badge-<?= $job['status'] === 'completed' ? 'success' : 'warning' ?>">
            <?= View::e(ucfirst($job['status'])) ?>
        </span>
    </div>
    <div class="info-row">
        <span class="label">Created:</span>
        <span><?= date('M j, Y H:i:s', strtotime($job['created_at'])) ?></span>
    </div>
</div>

<?php if (!empty($job['image_path'])): ?>
<div class="result-image">
    <h2>Source Image</h2>
    <img src="<?= View::e($job['image_path']) ?>" alt="Source Image">
</div>
<?php endif; ?>

<?php if (!empty($job['extracted_text'])): ?>
<div class="result-text">
    <h2>Extracted Text</h2>
    <div class="text-actions">
        <button class="btn btn-sm" onclick="copyText()"><i class="fas fa-copy"></i> Copy Text</button>
        <button class="btn btn-sm btn-primary" onclick="downloadText()"><i class="fas fa-download"></i> Download TXT</button>
    </div>
    <pre id="extracted-text"><?= View::e($job['extracted_text']) ?></pre>
</div>
<?php endif; ?>

<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<script>
    function copyText() {
        const text = document.getElementById('extracted-text').textContent;
        navigator.clipboard.writeText(text).then(() => {
            alert('Text copied to clipboard!');
        });
    }

    function downloadText() {
        const text = document.getElementById('extracted-text').textContent;
        const blob = new Blob([text], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'ocr-result-<?= $job['id'] ?>.txt';
        a.click();
        URL.revokeObjectURL(url);
    }
</script>
<?php View::endSection(); ?>
