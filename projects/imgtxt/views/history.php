<?php use Core\View; ?>
<?php View::extend('imgtxt'); ?>

<?php View::section('content'); ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">OCR History</h3>
        <a href="/projects/imgtxt/upload" class="btn btn-primary btn-sm">New OCR</a>
    </div>
    
    <?php if (empty($jobs)): ?>
        <div style="text-align: center; padding: 60px 20px;">
            <p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 20px;">No OCR history yet.</p>
            <a href="/projects/imgtxt/upload" class="btn btn-primary">Start OCR</a>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>File</th>
                    <th>Status</th>
                    <th>Language</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td>#<?= $job['id'] ?></td>
                        <td><?= View::e($job['original_filename'] ?? $job['filename'] ?? 'N/A') ?></td>
                        <td>
                            <span class="badge badge-<?= $job['status'] === 'completed' ? 'success' : ($job['status'] === 'failed' ? 'danger' : 'warning') ?>">
                                <?= View::e(ucfirst($job['status'])) ?>
                            </span>
                        </td>
                        <td><?= View::e($job['language'] ?? 'eng') ?></td>
                        <td><?= date('M j, Y H:i', strtotime($job['created_at'])) ?></td>
                        <td>
                            <?php if ($job['status'] === 'completed'): ?>
                                <a href="/projects/imgtxt/result/<?= $job['id'] ?>" class="btn btn-sm btn-primary">View</a>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-danger" onclick="deleteJob(<?= $job['id'] ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<script>
    async function deleteJob(jobId) {
        if (!confirm('Are you sure you want to delete this OCR job? This action cannot be undone.')) {
            return;
        }
        
        try {
            const response = await fetch('/projects/imgtxt/history/' + jobId, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            if (response.ok) {
                alert('Job deleted successfully');
                window.location.reload();
            } else {
                const text = await response.text();
                alert('Failed to delete job: ' + text);
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    }
</script>
<?php View::endSection(); ?>
