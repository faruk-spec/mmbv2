<?php
/**
 * ConvertX – History View
 */
$currentView = 'history';
$jobs        = $result['jobs']     ?? [];
$total       = $result['total']    ?? 0;
$page        = $result['page']     ?? 1;
$perPage     = $result['per_page'] ?? 20;
$totalPages  = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
?>

<!-- Page header -->
<div class="page-header">
    <h1>Conversion History</h1>
    <p><?= number_format($total) ?> total conversion<?= $total !== 1 ? 's' : '' ?></p>
</div>

<div class="card">
    <div class="card-header" style="justify-content:space-between;">
        <span><i class="fa-solid fa-clock-rotate-left"></i> All Conversions</span>
        <a href="/projects/convertx/convert" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> New Conversion
        </a>
    </div>

    <?php if (empty($jobs)): ?>
        <div style="text-align:center;padding:3rem 1rem;">
            <i class="fa-solid fa-folder-open" style="font-size:3.5rem;background:linear-gradient(135deg,var(--cx-primary),var(--cx-accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:.875rem;display:block;"></i>
            <p style="color:var(--text-secondary);font-size:.95rem;margin-bottom:1.25rem;">No conversion history yet.</p>
            <a href="/projects/convertx/convert" class="btn btn-primary">
                <i class="fa-solid fa-arrow-right-arrow-left"></i> Start your first conversion
            </a>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="cx-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>File</th>
                        <th>Conversion</th>
                        <th>AI Tasks</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td style="color:var(--text-secondary);"><?= (int)$job['id'] ?></td>
                        <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text-primary);">
                            <?= htmlspecialchars($job['input_filename'] ?? '') ?>
                        </td>
                        <td>
                            <span style="color:var(--text-secondary);font-size:.8rem;"><?= htmlspecialchars(strtoupper($job['input_format'] ?? '')) ?></span>
                            <i class="fa-solid fa-arrow-right" style="font-size:.6rem;margin:0 .3rem;color:var(--text-secondary);"></i>
                            <span style="color:var(--cx-accent);font-weight:600;font-size:.8rem;"><?= htmlspecialchars(strtoupper($job['output_format'] ?? '')) ?></span>
                        </td>
                        <td>
                            <?php
                            $tasks = json_decode($job['ai_tasks'] ?? '[]', true);
                            foreach ((array)$tasks as $task) {
                                $lbl = explode(':', $task)[0];
                                echo '<span style="font-size:.7rem;background:rgba(99,102,241,.15);color:var(--cx-primary);padding:.15rem .45rem;border-radius:4px;margin-right:.2rem;display:inline-block;">'
                                   . htmlspecialchars($lbl) . '</span>';
                            }
                            ?>
                        </td>
                        <td><span class="badge badge-<?= htmlspecialchars($job['status']) ?>"><?= htmlspecialchars(ucfirst($job['status'])) ?></span></td>
                        <td style="color:var(--text-secondary);font-size:.78rem;"><?= htmlspecialchars(substr($job['created_at'] ?? '', 0, 16)) ?></td>
                        <td>
                            <?php if ($job['status'] === 'completed'): ?>
                                <a href="/projects/convertx/job/<?= (int)$job['id'] ?>/download"
                                   class="btn btn-success btn-sm">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                            <?php elseif ($job['status'] === 'pending'): ?>
                                <button onclick="cancelJob(<?= (int)$job['id'] ?>)"
                                        class="btn btn-danger btn-sm">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div style="margin-top:1rem;display:flex;gap:.5rem;align-items:center;justify-content:flex-end;flex-wrap:wrap;">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Prev
                </a>
            <?php endif; ?>
            <span style="font-size:.8rem;color:var(--text-secondary);">Page <?= $page ?> / <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="btn btn-secondary btn-sm">
                    Next <i class="fa-solid fa-arrow-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
async function cancelJob(jobId) {
    if (!confirm('Cancel job #' + jobId + '?')) return;
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var res  = await fetch('/projects/convertx/job/' + jobId + '/cancel', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_token=' + encodeURIComponent(csrfToken)
    });
    var data = await res.json();
    if (data.success) location.reload();
    else alert(data.error || 'Could not cancel job');
}
</script>
