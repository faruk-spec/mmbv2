<?php
/**
 * ConvertX Dashboard View
 */
$currentView = 'dashboard';
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="value"><?= (int) ($usage['total_jobs'] ?? 0) ?></div>
        <div class="label">Jobs This Month</div>
    </div>
    <div class="stat-card">
        <div class="value"><?= (int) ($usage['completed'] ?? 0) ?></div>
        <div class="label">Completed</div>
    </div>
    <div class="stat-card">
        <div class="value"><?= (int) ($usage['failed'] ?? 0) ?></div>
        <div class="label">Failed</div>
    </div>
    <div class="stat-card">
        <div class="value"><?= number_format((int) ($usage['tokens_used'] ?? 0)) ?></div>
        <div class="label">AI Tokens Used</div>
    </div>
</div>

<!-- Quick actions -->
<div class="card">
    <div class="card-header"><i class="fa-solid fa-bolt"></i> Quick Actions</div>
    <div style="display:flex;gap:1rem;flex-wrap:wrap;">
        <a href="/projects/convertx/convert" class="btn btn-primary">
            <i class="fa-solid fa-arrow-right-arrow-left"></i> Convert a File
        </a>
        <a href="/projects/convertx/batch" class="btn btn-secondary">
            <i class="fa-solid fa-layer-group"></i> Batch Convert
        </a>
        <a href="/projects/convertx/docs" class="btn btn-secondary">
            <i class="fa-solid fa-book-open"></i> API Docs
        </a>
    </div>
</div>

<!-- Recent jobs -->
<div class="card">
    <div class="card-header"><i class="fa-solid fa-clock-rotate-left"></i> Recent Conversions</div>
    <?php if (empty($recent)): ?>
        <p style="color:var(--text-muted);font-size:.875rem;">No conversions yet. <a href="/projects/convertx/convert" style="color:var(--cx-primary);">Start your first conversion →</a></p>
    <?php else: ?>
        <table class="cx-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>File</th>
                    <th>Conversion</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $job): ?>
                <tr>
                    <td><?= (int) $job['id'] ?></td>
                    <td><?= htmlspecialchars($job['input_filename'] ?? '') ?></td>
                    <td>
                        <span style="color:var(--text-muted);"><?= htmlspecialchars(strtoupper($job['input_format'] ?? '')) ?></span>
                        <i class="fa-solid fa-arrow-right" style="font-size:.7rem;margin:0 .3rem;"></i>
                        <span style="color:var(--cx-accent);"><?= htmlspecialchars(strtoupper($job['output_format'] ?? '')) ?></span>
                    </td>
                    <td><span class="badge badge-<?= htmlspecialchars($job['status']) ?>"><?= htmlspecialchars(ucfirst($job['status'])) ?></span></td>
                    <td style="color:var(--text-muted);font-size:.8rem;"><?= htmlspecialchars(substr($job['created_at'] ?? '', 0, 16)) ?></td>
                    <td>
                        <?php if ($job['status'] === 'completed'): ?>
                            <a href="/projects/convertx/job/<?= (int) $job['id'] ?>/download" class="btn btn-success" style="padding:.3rem .75rem;font-size:.8rem;">
                                <i class="fa-solid fa-download"></i> Download
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="margin-top:1rem;">
            <a href="/projects/convertx/history" style="color:var(--cx-primary);font-size:.875rem;">View all history →</a>
        </div>
    <?php endif; ?>
</div>
