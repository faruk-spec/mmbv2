<?php use Core\View; use Core\Security; ?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>
<div class="container-fluid">
    <div class="page-header" style="margin-bottom:24px;">
        <h1 style="font-size:1.6rem;font-weight:700;display:flex;align-items:center;gap:10px;">
            <i class="fas fa-sticky-note" style="color:#ffd700;"></i> NoteX Overview
        </h1>
        <p style="color:var(--text-secondary);margin-top:4px;">Private Notes & Cloud Notes administration</p>
    </div>

    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;">
        <?php
        $statItems = [
            ['label' => 'Total Notes',   'value' => $stats['total_notes'],   'color' => '#ffd700', 'icon' => 'sticky-note'],
            ['label' => 'Total Users',   'value' => $stats['total_users'],   'color' => '#00d4ff', 'icon' => 'users'],
            ['label' => 'Total Folders', 'value' => $stats['total_folders'], 'color' => '#22c55e', 'icon' => 'folder'],
            ['label' => 'Notes Today',   'value' => $stats['notes_today'],   'color' => '#8b5cf6', 'icon' => 'calendar-day'],
        ];
        foreach ($statItems as $s): ?>
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
            <div style="font-size:1.8rem;font-weight:700;color:<?= $s['color'] ?>;"><?= $s['value'] ?></div>
            <div style="color:var(--text-secondary);font-size:13px;margin-top:4px;"><i class="fas fa-<?= $s['icon'] ?>" style="margin-right:5px;"></i><?= $s['label'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Recent Notes -->
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;overflow:hidden;">
        <div style="padding:18px 20px;border-bottom:1px solid var(--border-color);font-weight:600;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-clock" style="color:#ffd700;"></i> Recent Notes
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead><tr>
                    <th style="text-align:left;padding:12px 16px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Title</th>
                    <th style="text-align:left;padding:12px 16px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">User</th>
                    <th style="text-align:left;padding:12px 16px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Status</th>
                    <th style="text-align:left;padding:12px 16px;color:var(--text-secondary);font-size:12px;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Created</th>
                </tr></thead>
                <tbody>
                <?php foreach ($recentNotes as $note): ?>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                    <td style="padding:12px 16px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:10px;height:10px;border-radius:50%;background:<?= View::e($note['color'] ?? '#ffd700') ?>;flex-shrink:0;"></div>
                            <?= View::e($note['title']) ?>
                            <?php if ($note['is_pinned']): ?><i class="fas fa-thumbtack" style="color:#ffd700;font-size:11px;"></i><?php endif; ?>
                        </div>
                    </td>
                    <td style="padding:12px 16px;color:var(--text-secondary);">UID <?= $note['user_id'] ?></td>
                    <td style="padding:12px 16px;">
                        <?php if ($note['status'] === 'active'): ?><span style="background:rgba(34,197,94,0.15);color:#22c55e;padding:3px 10px;border-radius:20px;font-size:11px;">Active</span>
                        <?php else: ?><span style="background:rgba(239,68,68,0.15);color:#ef4444;padding:3px 10px;border-radius:20px;font-size:11px;"><?= ucfirst($note['status']) ?></span><?php endif; ?>
                    </td>
                    <td style="padding:12px 16px;color:var(--text-secondary);font-size:12px;"><?= date('M d, Y', strtotime($note['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php View::end(); ?>
