<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<?php if (Helpers::hasFlash('success')): ?>
<div style="background:rgba(0,255,136,.1);border:1px solid var(--green);color:var(--green);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
    <i class="fas fa-check-circle"></i> <?= View::e(Helpers::getFlash('success')) ?>
</div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
<div style="background:rgba(255,107,107,.1);border:1px solid var(--red);color:var(--red);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
    <i class="fas fa-exclamation-circle"></i> <?= View::e(Helpers::getFlash('error')) ?>
</div>
<?php endif; ?>

<!-- Breadcrumb -->
<div style="margin-bottom:16px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
    <a href="/admin/formx" style="color:var(--text-secondary);text-decoration:none;font-size:.875rem;" onmouseover="this.style.color='var(--cyan)'" onmouseout="this.style.color='var(--text-secondary)'">
        <i class="fas fa-arrow-left"></i> Back to Forms
    </a>
    <span style="color:var(--border-color);">›</span>
    <a href="/admin/formx/<?= $form['id'] ?>/edit" style="color:var(--text-secondary);text-decoration:none;font-size:.875rem;"><?= View::e($form['title']) ?></a>
    <span style="color:var(--border-color);">›</span>
    <span style="color:var(--text-primary);font-size:.875rem;">Submissions</span>
</div>

<!-- Page Header -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:1.3rem;font-weight:700;margin-bottom:4px;">
            <i class="fas fa-inbox" style="color:var(--cyan);margin-right:8px;"></i>
            Submissions: <?= View::e($form['title']) ?>
        </h1>
        <p style="color:var(--text-secondary);font-size:.875rem;">
            <?= (int)$form['submissions_count'] ?> total submission<?= $form['submissions_count'] != 1 ? 's' : '' ?>
        </p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="/admin/formx/<?= $form['id'] ?>/export" style="padding:8px 16px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);text-decoration:none;font-size:.875rem;">
            <i class="fas fa-download"></i> Export CSV
        </a>
        <a href="/admin/formx/<?= $form['id'] ?>/edit" style="padding:8px 16px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--cyan);text-decoration:none;font-size:.875rem;">
            <i class="fas fa-edit"></i> Edit Form
        </a>
    </div>
</div>

<!-- Submissions Table -->
<?php
$fieldDefs = $form['fields'];
$labeledFields = array_filter($fieldDefs, function($f) {
    return !in_array($f['type'] ?? '', ['heading','paragraph','divider']);
});
$labeledFields = array_values($labeledFields);
// Show max 5 columns in the table
$tableFields = array_slice($labeledFields, 0, 5);
?>

<div class="card">
    <?php if (empty($submissions)): ?>
    <div style="text-align:center;padding:60px 20px;color:var(--text-secondary);">
        <i class="fas fa-inbox" style="font-size:3rem;opacity:.3;display:block;margin-bottom:12px;"></i>
        <p>No submissions yet.</p>
        <a href="/forms/<?= View::e($form['slug']) ?>" target="_blank" style="margin-top:8px;display:inline-block;color:var(--cyan);text-decoration:none;">
            Open form <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--border-color);">
                    <th style="text-align:left;padding:10px 16px;font-size:.78rem;color:var(--text-secondary);font-weight:600;text-transform:uppercase;">#</th>
                    <th style="text-align:left;padding:10px 16px;font-size:.78rem;color:var(--text-secondary);font-weight:600;text-transform:uppercase;">Submitted At</th>
                    <?php foreach ($tableFields as $tf): ?>
                    <th style="text-align:left;padding:10px 16px;font-size:.78rem;color:var(--text-secondary);font-weight:600;text-transform:uppercase;"><?= View::e($tf['label'] ?? $tf['name'] ?? '') ?></th>
                    <?php endforeach; ?>
                    <?php if (count($labeledFields) > 5): ?>
                    <th style="padding:10px 16px;"></th>
                    <?php endif; ?>
                    <th style="text-align:right;padding:10px 16px;font-size:.78rem;color:var(--text-secondary);font-weight:600;text-transform:uppercase;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $i => $sub): ?>
                <tr style="border-bottom:1px solid var(--border-color);transition:background .2s;" onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background='transparent'">
                    <td style="padding:12px 16px;color:var(--text-secondary);font-size:.8rem;"><?= ($pagination['current'] - 1) * $pagination['perPage'] + $i + 1 ?></td>
                    <td style="padding:12px 16px;color:var(--text-secondary);font-size:.8rem;white-space:nowrap;"><?= date('M d, Y H:i', strtotime($sub['created_at'])) ?></td>
                    <?php foreach ($tableFields as $tf): ?>
                    <?php
                    $key = $tf['name'] ?? '';
                    $val = $sub['data'][$key] ?? '';
                    if (is_array($val)) $val = implode(', ', $val);
                    $display = mb_strlen($val) > 50 ? mb_substr($val, 0, 50) . '…' : $val;
                    ?>
                    <td style="padding:12px 16px;font-size:.85rem;color:var(--text-primary);"><?= View::e($display) ?></td>
                    <?php endforeach; ?>
                    <?php if (count($labeledFields) > 5): ?>
                    <td style="padding:12px 16px;font-size:.78rem;color:var(--text-secondary);">+<?= count($labeledFields) - 5 ?> more</td>
                    <?php endif; ?>
                    <td style="padding:12px 16px;text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;">
                            <a href="/admin/formx/<?= $form['id'] ?>/submissions/<?= $sub['id'] ?>" title="View" style="padding:5px 9px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:6px;color:var(--cyan);text-decoration:none;font-size:.8rem;">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form method="POST" action="/admin/formx/<?= $form['id'] ?>/submissions/<?= $sub['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this submission?')">
                                <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                                <button type="submit" title="Delete" style="padding:5px 9px;background:var(--bg-secondary);border:1px solid var(--red);border-radius:6px;color:var(--red);cursor:pointer;font-size:.8rem;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['total'] > 1): ?>
    <div style="padding:16px;display:flex;justify-content:center;gap:8px;flex-wrap:wrap;">
        <?php for ($p = 1; $p <= $pagination['total']; $p++): ?>
        <a href="/admin/formx/<?= $form['id'] ?>/submissions?page=<?= $p ?>"
           style="padding:6px 12px;border-radius:6px;text-decoration:none;font-size:.8rem;border:1px solid <?= $p === $pagination['current'] ? 'var(--cyan)' : 'var(--border-color)' ?>;background:<?= $p === $pagination['current'] ? 'var(--cyan)' : 'var(--bg-secondary)' ?>;color:<?= $p === $pagination['current'] ? '#000' : 'var(--text-primary)' ?>;">
            <?= $p ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php View::endSection(); ?>
