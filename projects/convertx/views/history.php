<?php
/**
 * ConvertX – History View
 */
$currentView = 'history';
$jobs        = $result['jobs']     ?? [];
$total       = $result['total']    ?? 0;
$page        = $result['page']     ?? 1;
$perPage     = $result['per_page'] ?? 20;
$totalPages  = $perPage > 0 ? (int) ceil($total / $perPage) : 1;
?>

<div class="card">
    <div class="card-header" style="justify-content:space-between;">
        <span><i class="fa-solid fa-clock-rotate-left"></i> Conversion History</span>
        <span style="font-size:.8rem;color:var(--text-muted);"><?= number_format($total) ?> total</span>
    </div>

    <?php if (empty($jobs)): ?>
        <p style="color:var(--text-muted);font-size:.875rem;">No conversions yet.</p>
    <?php else: ?>
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
                    <td><?= (int) $job['id'] ?></td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        <?= htmlspecialchars($job['input_filename'] ?? '') ?>
                    </td>
                    <td>
                        <span style="color:var(--text-muted);"><?= htmlspecialchars(strtoupper($job['input_format'] ?? '')) ?></span>
                        <i class="fa-solid fa-arrow-right" style="font-size:.65rem;margin:0 .25rem;"></i>
                        <span style="color:var(--cx-accent);"><?= htmlspecialchars(strtoupper($job['output_format'] ?? '')) ?></span>
                    </td>
                    <td>
                        <?php
                        $tasks = json_decode($job['ai_tasks'] ?? '[]', true);
                        foreach ((array) $tasks as $task) {
                            $label = explode(':', $task)[0];
                            echo '<span style="font-size:.7rem;background:rgba(99,102,241,.15);color:var(--cx-primary);padding:.1rem .4rem;border-radius:4px;margin-right:.25rem;">' . htmlspecialchars($label) . '</span>';
                        }
                        ?>
                    </td>
                    <td><span class="badge badge-<?= htmlspecialchars($job['status']) ?>"><?= htmlspecialchars(ucfirst($job['status'])) ?></span></td>
                    <td style="color:var(--text-muted);font-size:.8rem;"><?= htmlspecialchars(substr($job['created_at'] ?? '', 0, 16)) ?></td>
                    <td>
                        <?php if ($job['status'] === 'completed'): ?>
                            <a href="/projects/convertx/job/<?= (int) $job['id'] ?>/download"
                               class="btn btn-success" style="padding:.3rem .65rem;font-size:.78rem;">
                                <i class="fa-solid fa-download"></i>
                            </a>
                        <?php elseif ($job['status'] === 'pending'): ?>
                            <button onclick="cancelJob(<?= (int) $job['id'] ?>)"
                                    class="btn btn-danger" style="padding:.3rem .65rem;font-size:.78rem;">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div style="margin-top:1rem;display:flex;gap:.5rem;align-items:center;justify-content:flex-end;">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="btn btn-secondary" style="padding:.3rem .75rem;font-size:.8rem;">← Prev</a>
            <?php endif; ?>
            <span style="font-size:.8rem;color:var(--text-muted);">Page <?= $page ?> / <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="btn btn-secondary" style="padding:.3rem .75rem;font-size:.8rem;">Next →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
async function cancelJob(jobId) {
    if (!confirm('Cancel job #' + jobId + '?')) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const res  = await fetch('/projects/convertx/job/' + jobId + '/cancel', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_token=' + encodeURIComponent(csrfToken)
    });
    const data = await res.json();
    if (data.success) location.reload();
    else alert(data.error || 'Could not cancel job');
}
</script>
