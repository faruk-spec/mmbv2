<?php
/**
 * Support Tickets List (user view)
 */
use Core\View;

View::extend('main');
View::section('content');
?>

<div class="page-container" style="max-width:1100px;margin:0 auto;padding:32px 20px;">
    <!-- Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="font-size:1.7rem;font-weight:700;color:var(--text-primary,#e8eefc);margin:0 0 4px;">
                <i class="fas fa-ticket" style="color:#00f0ff;margin-right:10px;"></i>My Support Tickets
            </h1>
            <p style="color:var(--text-secondary,#8892a6);margin:0;font-size:.9rem;">Track and manage your support requests.</p>
        </div>
        <a href="/support/create" style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border-radius:8px;color:white;font-weight:600;text-decoration:none;font-size:.9rem;">
            <i class="fas fa-plus"></i> Create New Ticket
        </a>
    </div>

    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:16px;margin-bottom:28px;">
        <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;padding:18px 20px;text-align:center;">
            <div style="font-size:1.6rem;font-weight:700;color:#00f0ff;"><?= (int)($stats['open'] ?? 0) ?></div>
            <div style="color:var(--text-secondary,#8892a6);font-size:.82rem;margin-top:4px;">Open</div>
        </div>
        <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;padding:18px 20px;text-align:center;">
            <div style="font-size:1.6rem;font-weight:700;color:#ff9f43;"><?= (int)($stats['in_progress'] ?? 0) ?></div>
            <div style="color:var(--text-secondary,#8892a6);font-size:.82rem;margin-top:4px;">In Progress</div>
        </div>
        <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;padding:18px 20px;text-align:center;">
            <div style="font-size:1.6rem;font-weight:700;color:#00ff88;"><?= (int)($stats['resolved'] ?? 0) ?></div>
            <div style="color:var(--text-secondary,#8892a6);font-size:.82rem;margin-top:4px;">Resolved</div>
        </div>
        <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;padding:18px 20px;text-align:center;">
            <div style="font-size:1.6rem;font-weight:700;color:var(--text-primary,#e8eefc);"><?= (int)($stats['total'] ?? 0) ?></div>
            <div style="color:var(--text-secondary,#8892a6);font-size:.82rem;margin-top:4px;">Total</div>
        </div>
    </div>

    <!-- Tickets table -->
    <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:16px;overflow:hidden;">
        <?php if (empty($tickets)): ?>
            <div style="padding:60px 20px;text-align:center;color:var(--text-secondary,#8892a6);">
                <i class="fas fa-ticket" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:14px;"></i>
                <p style="margin:0 0 16px;font-size:1rem;">You have no support tickets yet.</p>
                <a href="/support/create" style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border-radius:8px;color:white;font-weight:600;text-decoration:none;">
                    <i class="fas fa-plus"></i> Create Your First Ticket
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--border-color,rgba(255,255,255,.08));">
                            <th style="padding:14px 16px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">#</th>
                            <th style="padding:14px 16px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Subject</th>
                            <th style="padding:14px 16px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Status</th>
                            <th style="padding:14px 16px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Priority</th>
                            <th style="padding:14px 16px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Created</th>
                            <th style="padding:14px 16px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                        <tr style="border-bottom:1px solid var(--border-color,rgba(255,255,255,.05));transition:background .15s;" onmouseover="this.style.background='rgba(255,255,255,.03)'" onmouseout="this.style.background=''">
                            <td style="padding:14px 16px;color:var(--text-secondary,#8892a6);font-size:.85rem;">#<?= (int)$ticket['id'] ?></td>
                            <td style="padding:14px 16px;">
                                <a href="/support/view/<?= (int)$ticket['id'] ?>" style="color:var(--text-primary,#e8eefc);text-decoration:none;font-weight:500;font-size:.9rem;">
                                    <?= htmlspecialchars($ticket['subject']) ?>
                                </a>
                            </td>
                            <td style="padding:14px 16px;">
                                <?= renderStatusBadge($ticket['status']) ?>
                            </td>
                            <td style="padding:14px 16px;">
                                <?= renderPriorityBadge($ticket['priority']) ?>
                            </td>
                            <td style="padding:14px 16px;color:var(--text-secondary,#8892a6);font-size:.82rem;">
                                <?= htmlspecialchars(date('M j, Y', strtotime($ticket['created_at']))) ?>
                            </td>
                            <td style="padding:14px 16px;">
                                <a href="/support/view/<?= (int)$ticket['id'] ?>" style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:rgba(0,240,255,.1);color:#00f0ff;border-radius:6px;text-decoration:none;font-size:.82rem;font-weight:500;">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
function renderStatusBadge(string $status): string {
    $map = [
        'open'             => ['color'=>'#00f0ff', 'bg'=>'rgba(0,240,255,.12)', 'label'=>'Open'],
        'in_progress'      => ['color'=>'#ff9f43', 'bg'=>'rgba(255,159,67,.12)', 'label'=>'In Progress'],
        'waiting_customer' => ['color'=>'#a78bfa', 'bg'=>'rgba(167,139,250,.12)', 'label'=>'Waiting'],
        'resolved'         => ['color'=>'#00ff88', 'bg'=>'rgba(0,255,136,.12)', 'label'=>'Resolved'],
        'closed'           => ['color'=>'#8892a6', 'bg'=>'rgba(136,146,166,.12)', 'label'=>'Closed'],
    ];
    $s = $map[$status] ?? ['color'=>'#8892a6','bg'=>'rgba(0,0,0,.2)','label'=>ucfirst($status)];
    return "<span style=\"display:inline-block;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:600;background:{$s['bg']};color:{$s['color']}\">{$s['label']}</span>";
}
function renderPriorityBadge(string $priority): string {
    $map = [
        'low'    => ['color'=>'#8892a6', 'bg'=>'rgba(136,146,166,.12)', 'label'=>'Low'],
        'medium' => ['color'=>'#00f0ff', 'bg'=>'rgba(0,240,255,.12)', 'label'=>'Medium'],
        'high'   => ['color'=>'#ff9f43', 'bg'=>'rgba(255,159,67,.12)', 'label'=>'High'],
        'urgent' => ['color'=>'#ff6b6b', 'bg'=>'rgba(255,107,107,.12)', 'label'=>'Urgent'],
    ];
    $p = $map[$priority] ?? ['color'=>'#8892a6','bg'=>'rgba(0,0,0,.2)','label'=>ucfirst($priority)];
    return "<span style=\"display:inline-block;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:600;background:{$p['bg']};color:{$p['color']}\">{$p['label']}</span>";
}
?>

<?php View::endSection(); ?>
