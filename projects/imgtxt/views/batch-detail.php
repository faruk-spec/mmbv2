<?php use Core\View; ?>
<?php View::extend('imgtxt'); ?>

<?php View::section('styles'); ?>
<style>
    .batch-info {
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
    
    .batch-results {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
    }
    
    .batch-results h2 {
        color: var(--text-primary);
        font-size: 1.2rem;
        margin-bottom: 20px;
    }
    
    .results-grid {
        display: grid;
        gap: 20px;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    }
    
    .result-card {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        overflow: hidden;
        transition: var(--transition);
    }
    
    .result-card:hover {
        border-color: var(--green);
        box-shadow: 0 4px 20px rgba(0, 255, 136, 0.1);
    }
    
    .result-image {
        width: 100%;
        height: 200px;
        overflow: hidden;
        background: var(--bg-primary);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .result-image img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    
    .result-text {
        padding: 15px;
    }
    
    .result-text pre {
        max-height: 150px;
        overflow-y: auto;
        white-space: pre-wrap;
        word-wrap: break-word;
        color: var(--text-primary);
        font-family: 'Courier New', monospace;
        font-size: 12px;
        line-height: 1.4;
    }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>

<div class="batch-info">
    <div class="info-row">
        <span class="label">Status:</span>
        <span class="badge badge-<?= $batch['status'] === 'completed' ? 'success' : 'warning' ?>">
            <?= View::e(ucfirst($batch['status'])) ?>
        </span>
    </div>
    <div class="info-row">
        <span class="label">Total Files:</span>
        <span><?= $batch['total_files'] ?? 0 ?></span>
    </div>
    <div class="info-row">
        <span class="label">Created:</span>
        <span><?= date('M j, Y H:i:s', strtotime($batch['created_at'])) ?></span>
    </div>
    <?php if (!empty($batch['completed_at'])): ?>
    <div class="info-row">
        <span class="label">Completed:</span>
        <span><?= date('M j, Y H:i:s', strtotime($batch['completed_at'])) ?></span>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($files)): ?>
<div class="batch-results">
    <h2>Results</h2>
    <div class="results-grid">
        <?php foreach ($files as $result): ?>
            <div class="result-card">
                <div class="result-image">
                    <?php if (!empty($result['image_path'])): ?>
                        <img src="<?= View::e($result['image_path']) ?>" alt="Source Image">
                    <?php else: ?>
                        <i class="fas fa-image" style="font-size: 3rem; color: var(--text-secondary);"></i>
                    <?php endif; ?>
                </div>
                <div class="result-text">
                    <pre><?= View::e($result['text']) ?></pre>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php View::endSection(); ?>

