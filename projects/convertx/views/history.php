<?php
/**
 * ConvertX – History View
 * Shows both format conversion jobs and synchronous tool jobs (compress, crop, etc.)
 */
$currentView  = 'history';
$jobs         = $result['jobs']     ?? [];
$total        = $result['total']    ?? 0;
$page         = $result['page']     ?? 1;
$perPage      = $result['per_page'] ?? 20;
$totalPages   = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
$statusFilter = $statusFilter ?? '';

$statusLabels = [
    ''           => 'All Statuses',
    'pending'    => 'Pending',
    'processing' => 'Processing',
    'completed'  => 'Completed',
    'failed'     => 'Failed',
    'cancelled'  => 'Cancelled',
];

// Human-readable labels for tool types
$toolLabels = [
    'pdf_merge'    => ['icon' => 'fa-object-group',    'label' => 'PDF Merge'],
    'pdf_split'    => ['icon' => 'fa-cut',             'label' => 'PDF Split'],
    'pdf_compress' => ['icon' => 'fa-file-zipper',     'label' => 'PDF Compress'],
    'img_compress' => ['icon' => 'fa-image',           'label' => 'Image Compress'],
    'img_resize'   => ['icon' => 'fa-expand-arrows-alt','label' => 'Image Resize'],
    'img_crop'     => ['icon' => 'fa-crop-simple',     'label' => 'Image Crop'],
    'img_watermark'=> ['icon' => 'fa-stamp',           'label' => 'Watermark'],
    'img_meme'     => ['icon' => 'fa-face-laugh',      'label' => 'Meme Generator'],
    'img_rotate'   => ['icon' => 'fa-rotate-right',    'label' => 'Image Rotate'],
];
?>

<!-- Page header -->
<div class="page-header">
    <h1>Conversion History</h1>
    <p><?= number_format($total) ?> total conversion<?= $total !== 1 ? 's' : '' ?><?= $statusFilter ? ' — filtered by <strong>' . htmlspecialchars($statusLabels[$statusFilter] ?? $statusFilter) . '</strong>' : '' ?></p>
</div>

<!-- ── Filter bar ── -->
<div style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;margin-bottom:1.25rem;">
    <?php foreach ($statusLabels as $val => $lbl): ?>
    <a href="?status=<?= htmlspecialchars($val) ?>"
       class="cx-filter-chip <?= $statusFilter === $val ? 'active' : '' ?>">
        <?= htmlspecialchars($lbl) ?>
    </a>
    <?php endforeach; ?>
    <a href="/projects/convertx/convert" class="btn btn-primary btn-sm" style="margin-left:auto;">
        <i class="fa-solid fa-plus"></i> New Conversion
    </a>
</div>

<div class="card">
    <?php if (empty($jobs)): ?>
        <div style="text-align:center;padding:3rem 1rem;">
            <i class="fa-solid fa-folder-open" style="font-size:3.5rem;background:linear-gradient(135deg,var(--cx-primary),var(--cx-accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:.875rem;display:block;"></i>
            <p style="color:var(--text-secondary);font-size:.95rem;margin-bottom:1.25rem;">
                <?= $statusFilter ? 'No ' . htmlspecialchars($statusLabels[$statusFilter] ?? $statusFilter) . ' conversions found.' : 'No conversion history yet.' ?>
            </p>
            <?php if ($statusFilter): ?>
                <a href="?" class="btn btn-secondary" style="margin-right:.5rem;">
                    <i class="fa-solid fa-xmark"></i> Clear filter
                </a>
            <?php endif; ?>
            <a href="/projects/convertx/convert" class="btn btn-primary">
                <i class="fa-solid fa-arrow-right-arrow-left"></i> Start your first conversion
            </a>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="cx-table">
                <thead>
                    <tr>
                        <th class="cx-history-hide-sm">#</th>
                        <th>File(s)</th>
                        <th>Operation</th>
                        <th class="cx-history-hide-sm">Size</th>
                        <th>Status</th>
                        <th class="cx-history-hide-sm">Date</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $job):
                        $isToolJob = ($job['job_source'] ?? '') === 'tool';
                        $toolType  = $job['tool_type'] ?? '';
                        $toolInfo  = $toolLabels[$toolType] ?? ['icon' => 'fa-wrench', 'label' => ucwords(str_replace('_', ' ', $toolType))];
                    ?>
                    <tr>
                        <td class="cx-history-hide-sm" style="color:var(--text-secondary);"><?= (int)$job['id'] ?></td>
                        <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text-primary);">
                            <?php if ($isToolJob): ?>
                                <?= htmlspecialchars($job['input_filenames'] ?? '—') ?>
                            <?php else: ?>
                                <?= htmlspecialchars($job['input_filename'] ?? '') ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($isToolJob): ?>
                                <span style="display:inline-flex;align-items:center;gap:.35rem;font-size:.82rem;font-weight:600;color:var(--cx-primary);">
                                    <i class="fa-solid <?= htmlspecialchars($toolInfo['icon']) ?>" style="font-size:.75rem;"></i>
                                    <?= htmlspecialchars($toolInfo['label']) ?>
                                </span>
                                <?php if (!empty($job['input_count']) && $job['input_count'] > 1): ?>
                                    <span style="font-size:.7rem;color:var(--text-muted);margin-left:.35rem;"><?= (int)$job['input_count'] ?> files</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color:var(--text-secondary);font-size:.8rem;"><?= htmlspecialchars(strtoupper($job['input_format'] ?? '')) ?></span>
                                <i class="fa-solid fa-arrow-right" style="font-size:.6rem;margin:0 .3rem;color:var(--text-secondary);"></i>
                                <span style="color:var(--cx-accent);font-weight:600;font-size:.8rem;"><?= htmlspecialchars(strtoupper($job['output_format'] ?? '')) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="cx-history-hide-sm" style="font-size:.75rem;color:var(--text-muted);">
                            <?php if ($isToolJob):
                                $origSz = (int)($job['original_size'] ?? 0);
                                $outSz  = (int)($job['output_size'] ?? 0);
                                $fmtSz  = fn(int $b): string => $b >= 1048576
                                    ? number_format($b / 1048576, 1) . ' MB'
                                    : ($b > 0 ? number_format($b / 1024, 1) . ' KB' : '—');
                                if ($origSz > 0 && $outSz > 0):
                                    echo htmlspecialchars($fmtSz($origSz) . ' → ' . $fmtSz($outSz));
                                elseif ($outSz > 0):
                                    echo htmlspecialchars($fmtSz($outSz));
                                else:
                                    echo '—';
                                endif;
                            else:
                                $tasks = json_decode($job['ai_tasks'] ?? '[]', true);
                                foreach ((array)$tasks as $task) {
                                    $lbl = explode(':', $task)[0];
                                    echo '<span style="font-size:.7rem;background:rgba(99,102,241,.15);color:var(--cx-primary);padding:.15rem .45rem;border-radius:4px;margin-right:.2rem;display:inline-block;">'
                                       . htmlspecialchars($lbl) . '</span>';
                                }
                                if (empty($tasks)) echo '<span style="color:var(--text-muted);">—</span>';
                            endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-<?= htmlspecialchars($job['status']) ?>"><?= htmlspecialchars(ucfirst($job['status'])) ?></span>
                            <?php if ($job['status'] === 'failed' && !empty($job['error_message'])): ?>
                            <span title="<?= htmlspecialchars($job['error_message']) ?>"
                                  style="cursor:help;margin-left:.3rem;color:var(--cx-danger);font-size:.75rem;">
                                <i class="fa-solid fa-circle-info"></i>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="cx-history-hide-sm" style="color:var(--text-secondary);font-size:.78rem;"><?= htmlspecialchars(substr($job['created_at'] ?? '', 0, 16)) ?></td>
                        <td style="text-align:right;white-space:nowrap;">
                            <?php if ($isToolJob): ?>
                                <?php if ($job['status'] === 'completed'): ?>
                                    <span class="badge badge-completed" style="font-size:.7rem;">Done</span>
                                <?php elseif ($job['status'] === 'failed'): ?>
                                    <?php
                                    // Map tool type to its tool URL for retry
                                    $retryUrls = [
                                        'pdf_merge' => '/projects/convertx/pdf-merge',
                                        'pdf_split' => '/projects/convertx/pdf-split',
                                        'pdf_compress' => '/projects/convertx/pdf-compress',
                                        'img_compress' => '/projects/convertx/img-compress',
                                        'img_resize'   => '/projects/convertx/img-resize',
                                        'img_crop'     => '/projects/convertx/img-crop',
                                        'img_watermark'=> '/projects/convertx/img-watermark',
                                        'img_meme'     => '/projects/convertx/img-meme',
                                        'img_rotate'   => '/projects/convertx/img-rotate',
                                    ];
                                    $retryUrl = $retryUrls[$toolType] ?? '/projects/convertx/dashboard';
                                    ?>
                                    <a href="<?= htmlspecialchars($retryUrl) ?>"
                                       class="btn btn-secondary btn-sm" title="Try again">
                                        <i class="fa-solid fa-rotate"></i>
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($job['status'] === 'completed'): ?>
                                    <a href="/projects/convertx/job/<?= (int)$job['id'] ?>/download"
                                       class="btn btn-success btn-sm" title="Download converted file">
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                <?php elseif ($job['status'] === 'pending'): ?>
                                    <button onclick="cancelJob(<?= (int)$job['id'] ?>)"
                                            class="btn btn-danger btn-sm" title="Cancel job">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                <?php elseif (in_array($job['status'], ['failed', 'cancelled'], true)): ?>
                                    <a href="/projects/convertx/convert"
                                       class="btn btn-secondary btn-sm" title="Try converting again">
                                        <i class="fa-solid fa-rotate"></i>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if ($job['status'] === 'failed' && !empty($job['error_message'])): ?>
                    <tr class="cx-error-row">
                        <td colspan="7">
                            <i class="fa-solid fa-triangle-exclamation" style="color:var(--cx-warning);margin-right:.4rem;"></i>
                            <span style="font-size:.78rem;color:var(--cx-danger);"><?= htmlspecialchars($job['error_message']) ?></span>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination (preserve status filter) -->
        <?php if ($totalPages > 1): ?>
        <div style="margin-top:1rem;display:flex;gap:.5rem;align-items:center;justify-content:flex-end;flex-wrap:wrap;">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&status=<?= htmlspecialchars($statusFilter) ?>" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Prev
                </a>
            <?php endif; ?>
            <span style="font-size:.8rem;color:var(--text-secondary);">Page <?= $page ?> / <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>&status=<?= htmlspecialchars($statusFilter) ?>" class="btn btn-secondary btn-sm">
                    Next <i class="fa-solid fa-arrow-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.cx-filter-chip {
    display: inline-flex;
    align-items: center;
    padding: .3rem .75rem;
    border-radius: 2rem;
    font-size: .78rem;
    font-weight: 500;
    border: 1px solid var(--border-color);
    background: var(--bg-secondary);
    color: var(--text-secondary);
    text-decoration: none;
    transition: background .18s, color .18s, border-color .18s;
    white-space: nowrap;
}
.cx-filter-chip:hover {
    border-color: var(--cx-primary);
    color: var(--text-primary);
}
.cx-filter-chip.active {
    background: linear-gradient(135deg, var(--cx-primary), var(--cx-secondary));
    border-color: transparent;
    color: #fff;
    font-weight: 600;
}
.cx-error-row td {
    padding: .3rem 1rem .6rem;
    background: rgba(239,68,68,.05);
    border-top: none !important;
}
/* On small screens hide less-important columns */
@media (max-width: 37.5rem) {
    .cx-history-hide-sm { display: none; }
    .cx-table th, .cx-table td { padding: .5rem .6rem; font-size: .75rem; }
}
</style>

<script>
async function cancelJob(jobId) {
    if (!confirm('Cancel job #' + jobId + '?')) return;
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var res  = await fetch('/projects/convertx/job/' + jobId + '/cancel', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
        body: '_token=' + encodeURIComponent(csrfToken)
    });
    var data = await res.json();
    if (data.success) location.reload();
    else alert(data.error || 'Could not cancel job');
}
</script>
