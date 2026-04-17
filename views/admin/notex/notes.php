<?php use Core\View; use Core\Security; ?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>
<div class="container-fluid">
    <div class="page-header" style="margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;">
        <h1 style="font-size:1.5rem;font-weight:700;"><i class="fas fa-sticky-note" style="color:#ffd700;margin-right:10px;"></i> All Notes</h1>
        <span style="color:var(--text-secondary);">Total: <?= number_format($total) ?></span>
    </div>

    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;overflow:hidden;">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead><tr>
                    <th style="text-align:left;padding:14px 16px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Title</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">User</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Status</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Created</th>
                    <th style="padding:14px 16px;border-bottom:1px solid var(--border-color);"></th>
                </tr></thead>
                <tbody>
                <?php foreach ($notes as $note): ?>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                    <td style="padding:12px 16px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:10px;height:10px;border-radius:50%;background:<?= View::e($note['color'] ?? '#ffd700') ?>;flex-shrink:0;"></div>
                            <?= View::e($note['title']) ?>
                        </div>
                    </td>
                    <td style="padding:12px 16px;color:var(--text-secondary);">UID <?= $note['user_id'] ?></td>
                    <td style="padding:12px 16px;">
                        <?php if ($note['status'] === 'active'): ?><span style="background:rgba(34,197,94,0.15);color:#22c55e;padding:3px 10px;border-radius:20px;font-size:11px;">Active</span>
                        <?php else: ?><span style="background:rgba(239,68,68,0.15);color:#ef4444;padding:3px 10px;border-radius:20px;font-size:11px;"><?= ucfirst($note['status']) ?></span><?php endif; ?>
                    </td>
                    <td style="padding:12px 16px;color:var(--text-secondary);font-size:12px;"><?= date('M d, Y', strtotime($note['created_at'])) ?></td>
                    <td style="padding:12px 16px;">
                        <form method="POST" action="/admin/projects/notex/notes/delete" onsubmit="return confirm('Permanently delete this note?');" style="display:inline;">
                            <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
                            <input type="hidden" name="id" value="<?= $note['id'] ?>">
                            <button type="submit" style="background:rgba(239,68,68,0.15);color:#ef4444;border:1px solid #ef4444;padding:5px 10px;border-radius:6px;cursor:pointer;font-family:inherit;font-size:12px;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (($totalPages ?? 1) > 1): ?>
        <div style="display:flex;gap:8px;justify-content:center;padding:16px;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" style="padding:6px 12px;background:<?= $i == $page ? '#ffd700' : 'var(--bg-secondary)' ?>;color:<?= $i == $page ? '#000' : 'inherit' ?>;border-radius:6px;text-decoration:none;font-size:13px;"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php View::end(); ?>
