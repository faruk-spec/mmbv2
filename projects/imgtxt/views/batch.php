<?php use Core\View; ?>
<?php View::extend('imgtxt'); ?>

<?php View::section('styles'); ?>
<style>
    .batches-list {
        display: grid;
        gap: 20px;
    }
    
    .batch-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        transition: var(--transition);
    }
    
    .batch-card:hover {
        border-color: var(--green);
        box-shadow: 0 4px 20px rgba(0, 255, 136, 0.1);
    }
    
    .batch-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .batch-header h3 {
        color: var(--text-primary);
        font-size: 1.2rem;
        margin: 0;
    }
    
    .batch-meta {
        display: flex;
        gap: 20px;
        margin-bottom: 15px;
        color: var(--text-secondary);
        font-size: 14px;
    }
    
    .batch-actions {
        display: flex;
        gap: 10px;
    }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>

<div class="batches-list">
    <?php if (empty($batches)): ?>
        <div class="card" style="text-align: center; padding: 60px 20px;">
            <p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 20px;">No batch jobs yet.</p>
            <a href="/projects/imgtxt/upload" class="btn btn-primary">Start OCR</a>
        </div>
    <?php else: ?>
        <?php foreach ($batches as $batch): ?>
            <div class="batch-card">
                <div class="batch-header">
                    <h3>Batch #<?= $batch['id'] ?></h3>
                    <span class="badge badge-<?= $batch['status'] === 'completed' ? 'success' : 'warning' ?>">
                        <?= View::e(ucfirst($batch['status'])) ?>
                    </span>
                </div>
                <div class="batch-meta">
                    <span><i class="fas fa-file"></i> Files: <?= $batch['total_files'] ?? 0 ?></span>
                    <span><i class="fas fa-calendar"></i> Created: <?= date('M j, Y H:i', strtotime($batch['created_at'])) ?></span>
                </div>
                <div class="batch-actions">
                    <a href="/projects/imgtxt/batch/<?= $batch['id'] ?>" class="btn btn-sm">View Details</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php View::endSection(); ?>
