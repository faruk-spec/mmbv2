<?php use Core\View; use Core\Security; ?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>
<div class="container-fluid">
    <div class="page-header" style="margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;">
        <h1 style="font-size:1.5rem;font-weight:700;"><i class="fas fa-link" style="color:#00d4ff;margin-right:10px;"></i> All Links</h1>
        <span style="color:var(--text-secondary);">Total: <?= number_format($total) ?></span>
    </div>

    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;overflow:hidden;">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead><tr>
                    <th style="text-align:left;padding:14px 16px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Code</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">User</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">URL</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Clicks</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Status</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Created</th>
                    <th style="padding:14px 16px;border-bottom:1px solid var(--border-color);"></th>
                </tr></thead>
                <tbody>
                <?php foreach ($links as $link): ?>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                    <td style="padding:12px 16px;"><a href="/l/<?= View::e($link['code']) ?>" target="_blank" style="color:#00d4ff;font-weight:600;">/l/<?= View::e($link['code']) ?></a></td>
                    <td style="padding:12px 16px;color:var(--text-secondary);">UID <?= $link['user_id'] ?></td>
                    <td style="padding:12px 16px;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><a href="<?= View::e($link['original_url']) ?>" target="_blank" style="color:var(--text-secondary);font-size:12px;"><?= View::e($link['original_url']) ?></a></td>
                    <td style="padding:12px 16px;color:#ffaa00;font-weight:600;"><?= number_format($link['total_clicks']) ?></td>
                    <td style="padding:12px 16px;">
                        <?php if ($link['status'] === 'active'): ?><span style="background:rgba(0,255,136,0.15);color:#00ff88;padding:3px 10px;border-radius:20px;font-size:11px;">Active</span>
                        <?php else: ?><span style="background:rgba(255,107,107,0.15);color:#ff6b6b;padding:3px 10px;border-radius:20px;font-size:11px;"><?= ucfirst($link['status']) ?></span><?php endif; ?>
                    </td>
                    <td style="padding:12px 16px;color:var(--text-secondary);font-size:12px;"><?= date('M d, Y', strtotime($link['created_at'])) ?></td>
                    <td style="padding:12px 16px;">
                        <form method="POST" action="/admin/projects/linkshortner/links/delete" onsubmit="return confirm('Delete this link?');" style="display:inline;">
                            <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
                            <input type="hidden" name="id" value="<?= $link['id'] ?>">
                            <button type="submit" style="background:rgba(255,107,107,0.15);color:#ff6b6b;border:1px solid #ff6b6b;padding:5px 10px;border-radius:6px;cursor:pointer;font-family:inherit;font-size:12px;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (($totalPages ?? 1) > 1): ?>
        <div style="display:flex;gap:8px;justify-content:center;padding:16px;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" style="padding:6px 12px;background:<?= $i == $page ? 'var(--cyan)' : 'var(--bg-secondary)' ?>;color:<?= $i == $page ? '#000' : 'inherit' ?>;border-radius:6px;text-decoration:none;font-size:13px;"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php View::end(); ?>
