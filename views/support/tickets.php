<?php
/**
 * Support Tickets List (user view)
 */
use Core\View;

View::extend('main');

// Helper badge functions — must be declared before they are called in the loop below
if (!function_exists('stSupportStatusBadge')) {
    function stSupportStatusBadge(string $status): string {
        $map = [
            'open'             => ['#00f0ff', 'rgba(0,240,255,.12)',    'Open'],
            'in_progress'      => ['#ff9f43', 'rgba(255,159,67,.12)',   'In Progress'],
            'waiting_customer' => ['#a78bfa', 'rgba(167,139,250,.12)',  'Waiting'],
            'resolved'         => ['#00ff88', 'rgba(0,255,136,.12)',    'Resolved'],
            'closed'           => ['#8892a6', 'rgba(136,146,166,.12)', 'Closed'],
        ];
        [$c, $bg, $lbl] = $map[$status] ?? ['#8892a6','rgba(0,0,0,.2)',ucfirst($status)];
        return "<span style=\"display:inline-block;padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:600;background:{$bg};color:{$c}\">{$lbl}</span>";
    }
}
if (!function_exists('stSupportPriorityBadge')) {
    function stSupportPriorityBadge(string $priority): string {
        $map = [
            'low'    => ['#8892a6', 'rgba(136,146,166,.12)', 'Low'],
            'medium' => ['#00f0ff', 'rgba(0,240,255,.12)',   'Medium'],
            'high'   => ['#ff9f43', 'rgba(255,159,67,.12)',  'High'],
            'urgent' => ['#ff6b6b', 'rgba(255,107,107,.12)', 'Urgent'],
        ];
        [$c, $bg, $lbl] = $map[$priority] ?? ['#8892a6','rgba(0,0,0,.2)',ucfirst($priority)];
        return "<span style=\"display:inline-block;padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:600;background:{$bg};color:{$c}\">{$lbl}</span>";
    }
}

// Helper: format ticket ID as 7-digit number
if (!function_exists('stFormatTicketId')) {
    function stFormatTicketId(int $id): string {
        return sprintf('%07d', $id);
    }
}

View::extend('main');
?>

<?php View::section('styles'); ?>
<style>
/* Support portal overrides */
.dashboard-main-content { padding: 0 !important; }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div style="display:flex;min-height:calc(100vh - 64px);align-items:stretch;">

    <!-- Sidebar -->
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <!-- Main content -->
    <div style="flex:1;padding:24px 28px;overflow:auto;min-width:0;">

            <!-- Flash messages -->
            <?php if (!empty($_SESSION['_flash']['success'])): ?>
            <div style="background:rgba(0,255,136,.08);border:1px solid rgba(0,255,136,.2);color:#00ff88;padding:12px 16px;border-radius:8px;margin-bottom:18px;font-size:.88rem;display:flex;align-items:center;gap:8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <?= htmlspecialchars($_SESSION['_flash']['success']) ?>
                <?php unset($_SESSION['_flash']['success']); ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['_flash']['error'])): ?>
            <div style="background:rgba(255,107,107,.08);border:1px solid rgba(255,107,107,.2);color:#ff6b6b;padding:12px 16px;border-radius:8px;margin-bottom:18px;font-size:.88rem;display:flex;align-items:center;gap:8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($_SESSION['_flash']['error']) ?>
                <?php unset($_SESSION['_flash']['error']); ?>
            </div>
            <?php endif; ?>

            <!-- Header -->
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;flex-wrap:wrap;gap:12px;">
                <div>
                    <h1 style="font-size:1.45rem;font-weight:700;color:var(--text-primary,#e8eefc);margin:0 0 3px;display:flex;align-items:center;gap:10px;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#00f0ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="m9 12 2 2 4-4"/></svg>
                        My Support Tickets
                    </h1>
                    <p style="color:var(--text-secondary,#8892a6);margin:0;font-size:.84rem;">Track and manage your support requests</p>
                </div>
                <a href="/support/create" style="display:inline-flex;align-items:center;gap:8px;padding:9px 18px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border-radius:8px;color:white;font-weight:600;text-decoration:none;font-size:.875rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    New Ticket
                </a>
            </div>

            <!-- Stats -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:12px;margin-bottom:22px;">
                <?php
                $statItems = [
                    ['open',       '#00f0ff', 'Open',        'folder-open'],
                    ['in_progress','#ff9f43', 'In Progress', 'spinner'],
                    ['resolved',   '#00ff88', 'Resolved',    'check-circle'],
                    ['total',      '#a78bfa', 'Total',       'list'],
                ];
                foreach ($statItems as [$key, $color, $label, $icon]):
                ?>
                <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.07));border-radius:10px;padding:14px 16px;text-align:center;position:relative;overflow:hidden;">
                    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:<?= $color ?>40;"></div>
                    <div style="font-size:1.55rem;font-weight:700;color:<?= $color ?>;"><?= (int)($stats[$key] ?? 0) ?></div>
                    <div style="color:var(--text-secondary,#8892a6);font-size:.76rem;margin-top:3px;"><?= $label ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Tickets table -->
            <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.07));border-radius:12px;overflow:hidden;">
                <?php if (empty($tickets)): ?>
                <div style="padding:60px 20px;text-align:center;color:var(--text-secondary,#8892a6);">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:.25;display:block;margin:0 auto 14px;"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/></svg>
                    <p style="margin:0 0 16px;font-size:.95rem;">You have no support tickets yet.</p>
                    <a href="/support/create" style="display:inline-flex;align-items:center;gap:8px;padding:9px 20px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border-radius:8px;color:white;font-weight:600;text-decoration:none;font-size:.875rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Create Your First Ticket
                    </a>
                </div>
                <?php else: ?>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border-color,rgba(255,255,255,.07));">
                                <th style="padding:12px 16px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">#</th>
                                <th style="padding:12px 16px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Subject</th>
                                <th style="padding:12px 16px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Status</th>
                                <th style="padding:12px 16px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Priority</th>
                                <th style="padding:12px 16px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Created</th>
                                <th style="padding:12px 16px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $ticket): ?>
                            <tr style="border-bottom:1px solid var(--border-color,rgba(255,255,255,.04));transition:background .15s;" onmouseover="this.style.background='rgba(255,255,255,.025)'" onmouseout="this.style.background=''">
                                <td style="padding:13px 16px;color:var(--text-secondary,#8892a6);font-size:.82rem;font-weight:500;font-family:monospace;">#<?= stFormatTicketId((int)$ticket['id']) ?></td>
                                <td style="padding:13px 16px;">
                                    <a href="/support/view/<?= (int)$ticket['id'] ?>" style="color:var(--text-primary,#e8eefc);text-decoration:none;font-weight:500;font-size:.9rem;display:block;">
                                        <?= htmlspecialchars($ticket['subject']) ?>
                                    </a>
                                </td>
                                <td style="padding:13px 16px;"><?= stSupportStatusBadge($ticket['status']) ?></td>
                                <td style="padding:13px 16px;"><?= stSupportPriorityBadge($ticket['priority']) ?></td>
                                <td style="padding:13px 16px;color:var(--text-secondary,#8892a6);font-size:.8rem;">
                                    <?= date('M j, Y', strtotime($ticket['created_at'])) ?>
                                </td>
                                <td style="padding:13px 16px;">
                                    <a href="/support/view/<?= (int)$ticket['id'] ?>" style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:rgba(0,240,255,.1);color:#00f0ff;border-radius:6px;text-decoration:none;font-size:.78rem;font-weight:500;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div><!-- /main content -->
</div><!-- /support flex wrapper -->

<?php View::endSection(); ?>

