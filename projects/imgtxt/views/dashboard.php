<?php use Core\View; ?>
<?php View::extend('imgtxt'); ?>

<?php View::section('content'); ?>

<div class="grid grid-4 mb-3">
    <div class="card">
        <div style="text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--green); margin-bottom: 10px;">
                <?= $stats['total_jobs'] ?? 0 ?>
            </div>
            <div style="color: var(--text-secondary); font-size: 14px;">Total Jobs</div>
        </div>
    </div>
    <div class="card">
        <div style="text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--cyan); margin-bottom: 10px;">
                <?= $stats['completed_jobs'] ?? 0 ?>
            </div>
            <div style="color: var(--text-secondary); font-size: 14px;">Completed</div>
        </div>
    </div>
    <div class="card">
        <div style="text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--orange); margin-bottom: 10px;">
                <?= $stats['processing_jobs'] ?? 0 ?>
            </div>
            <div style="color: var(--text-secondary); font-size: 14px;">Processing</div>
        </div>
    </div>
    <div class="card">
        <div style="text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--magenta); margin-bottom: 10px;">
                <?= $todayStats['total_jobs'] ?? 0 ?>
            </div>
            <div style="color: var(--text-secondary); font-size: 14px;">Today's Jobs</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent OCR Jobs</h3>
        <a href="/projects/imgtxt/history" class="btn btn-sm btn-secondary">View All</a>
    </div>
    
    <?php if (!empty($recentJobs)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>File</th>
                    <th>Status</th>
                    <th>Language</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentJobs as $job): ?>
                    <tr>
                        <td><?= htmlspecialchars($job['original_filename']) ?></td>
                        <td>
                            <span class="badge badge-<?= $job['status'] === 'completed' ? 'success' : ($job['status'] === 'failed' ? 'danger' : 'warning') ?>">
                                <?= ucfirst($job['status']) ?>
                            </span>
                        </td>
                        <td><?= strtoupper($job['language']) ?></td>
                        <td><?= date('M d, H:i', strtotime($job['created_at'])) ?></td>
                        <td>
                            <?php if ($job['status'] === 'completed'): ?>
                                <a href="/projects/imgtxt/result/<?= $job['id'] ?>" class="btn btn-sm">View</a>
                            <?php elseif ($job['status'] === 'failed'): ?>
                                <span style="color: var(--red);">Failed</span>
                            <?php else: ?>
                                <span style="color: var(--orange);">Processing...</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
            <p>No OCR jobs yet. <a href="/projects/imgtxt/upload" style="color: var(--green);">Upload your first image</a></p>
        </div>
    <?php endif; ?>
</div>

<?php View::endSection(); ?>
