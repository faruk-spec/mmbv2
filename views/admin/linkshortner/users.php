<?php use Core\View; ?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>
<div class="container-fluid">
    <div class="page-header" style="margin-bottom:24px;">
        <h1 style="font-size:1.5rem;font-weight:700;"><i class="fas fa-users" style="color:#00d4ff;margin-right:10px;"></i> Users</h1>
    </div>

    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;overflow:hidden;">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead><tr>
                    <th style="text-align:left;padding:14px 16px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">User ID</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Links</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Total Clicks</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Last Link</th>
                </tr></thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                    <td style="padding:12px 16px;"><a href="/admin/users/<?= $u['user_id'] ?>" style="color:#00d4ff;">#<?= $u['user_id'] ?></a></td>
                    <td style="padding:12px 16px;font-weight:600;"><?= number_format($u['link_count']) ?></td>
                    <td style="padding:12px 16px;color:#f59e0b;"><?= number_format($u['total_clicks'] ?? 0) ?></td>
                    <td style="padding:12px 16px;color:var(--text-secondary);font-size:12px;"><?= date('M d, Y', strtotime($u['last_link_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php View::end(); ?>
