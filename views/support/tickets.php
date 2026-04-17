<?php
/**
 * Support Tickets List (user view)
 */
use Core\View;

View::extend('main');

// Helper badge functions — must be declared before they are called in the loop below
if (!function_exists('stSupportStatusBadge')) {
    function stSupportStatusBadge(string $status): string {
        $classes = [
            'open'             => 'sp-badge-open',
            'in_progress'      => 'sp-badge-prog',
            'waiting_customer' => 'sp-badge-wait',
            'resolved'         => 'sp-badge-done',
            'closed'           => 'sp-badge-closed',
        ];
        $labels = [
            'open'             => 'Open',
            'in_progress'      => 'In Progress',
            'waiting_customer' => 'Waiting',
            'resolved'         => 'Resolved',
            'closed'           => 'Closed',
        ];
        $cls = $classes[$status] ?? 'sp-badge-closed';
        $lbl = $labels[$status] ?? ucfirst($status);
        return "<span class=\"sp-badge {$cls}\">{$lbl}</span>";
    }
}
if (!function_exists('stSupportPriorityBadge')) {
    function stSupportPriorityBadge(string $priority): string {
        $classes = ['low' => 'sp-badge-low', 'medium' => 'sp-badge-medium', 'high' => 'sp-badge-high', 'urgent' => 'sp-badge-urgent'];
        $labels  = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'];
        $cls = $classes[$priority] ?? 'sp-badge-closed';
        $lbl = $labels[$priority] ?? ucfirst($priority);
        return "<span class=\"sp-badge {$cls}\">{$lbl}</span>";
    }
}

// Helper: format ticket ID as 7-digit number
if (!function_exists('stFormatTicketId')) {
    function stFormatTicketId(int $id): string {
        return sprintf('%07d', $id);
    }
}
?>

<?php View::section('styles'); ?>
<?php include __DIR__ . '/_styles.php'; ?>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div class="sp-layout">

    <!-- Sidebar -->
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <!-- Main content -->
    <div class="sp-main">

            <!-- Mobile menu button -->
            <button class="sp-menu-btn" onclick="spOpenMenu()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                Menu
            </button>

            <!-- Flash messages -->
            <?php if (!empty($_SESSION['_flash']['success'])): ?>
            <div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);color:#22c55e;padding:12px 16px;border-radius:8px;margin-bottom:18px;font-size:.88rem;display:flex;align-items:center;gap:8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <?= htmlspecialchars($_SESSION['_flash']['success']) ?>
                <?php unset($_SESSION['_flash']['success']); ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['_flash']['error'])): ?>
            <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#ef4444;padding:12px 16px;border-radius:8px;margin-bottom:18px;font-size:.88rem;display:flex;align-items:center;gap:8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($_SESSION['_flash']['error']) ?>
                <?php unset($_SESSION['_flash']['error']); ?>
            </div>
            <?php endif; ?>

            <!-- Header -->
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;flex-wrap:wrap;gap:12px;">
                <div>
                    <h1 style="font-size:1.45rem;font-weight:700;color:var(--text-primary);margin:0 0 3px;display:flex;align-items:center;gap:10px;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color:var(--cyan)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="m9 12 2 2 4-4"/></svg>
                        My Support Tickets
                    </h1>
                    <p style="color:var(--text-secondary,#8892a6);margin:0;font-size:.84rem;">Track and manage your support requests</p>
                </div>
                <a href="/support/new" class="sp-btn sp-btn-primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    New Ticket
                </a>
            </div>

            <!-- Stats -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:12px;margin-bottom:22px;">
                <?php
                $statItems = [
                    ['open',       'var(--cyan)',    'Open'],
                    ['in_progress','var(--orange)',  'In Progress'],
                    ['resolved',   'var(--green)',   'Resolved'],
                    ['total',      'var(--purple)',  'Total'],
                ];
                foreach ($statItems as [$key, $color, $label]):
                ?>
                <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:14px 16px;text-align:center;position:relative;overflow:hidden;">
                    <div class="sp-stat-bar" style="background:<?= $color ?>;opacity:.35;"></div>
                    <div style="font-size:1.55rem;font-weight:700;color:<?= $color ?>;"><?= (int)($stats[$key] ?? 0) ?></div>
                    <div style="color:var(--text-secondary);font-size:.76rem;margin-top:3px;"><?= $label ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;overflow:hidden;">
                <?php if (empty($tickets)): ?>
                <div style="padding:60px 20px;text-align:center;color:var(--text-secondary);">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:.25;display:block;margin:0 auto 14px;"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/></svg>
                    <p style="margin:0 0 16px;font-size:.95rem;">You have no support tickets yet.</p>
                    <a href="/support/new" class="sp-btn sp-btn-primary">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Create Your First Ticket
                    </a>
                </div>
                <?php else: ?>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border-color);">
                                <th style="padding:12px 16px;text-align:left;color:var(--text-secondary);font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">#</th>
                                <th style="padding:12px 16px;text-align:left;color:var(--text-secondary);font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Subject</th>
                                <th style="padding:12px 16px;text-align:left;color:var(--text-secondary);font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Status</th>
                                <th style="padding:12px 16px;text-align:left;color:var(--text-secondary);font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Priority</th>
                                <th style="padding:12px 16px;text-align:left;color:var(--text-secondary);font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Created</th>
                                <th style="padding:12px 16px;text-align:left;color:var(--text-secondary);font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $ticket): ?>
                            <tr class="sp-tr" style="border-bottom:1px solid var(--border-color);">
                                <td style="padding:13px 16px;color:var(--text-secondary);font-size:.82rem;font-weight:500;font-family:monospace;">#<?= stFormatTicketId((int)$ticket['id']) ?></td>
                                <td style="padding:13px 16px;">
                                    <a href="/support/view/<?= (int)$ticket['id'] ?>" style="color:var(--text-primary);text-decoration:none;font-weight:500;font-size:.9rem;display:block;">
                                        <?= htmlspecialchars($ticket['subject']) ?>
                                    </a>
                                </td>
                                <td style="padding:13px 16px;"><?= stSupportStatusBadge($ticket['status']) ?></td>
                                <td style="padding:13px 16px;"><?= stSupportPriorityBadge($ticket['priority']) ?></td>
                                <td style="padding:13px 16px;color:var(--text-secondary);font-size:.8rem;">
                                    <?= date('M j, Y', strtotime($ticket['created_at'])) ?>
                                </td>
                                <td style="padding:13px 16px;">
                                    <a href="/support/view/<?= (int)$ticket['id'] ?>" class="sp-action-view" style="display:inline-flex;align-items:center;gap:5px;">
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
</div><!-- /sp-layout -->

<?php View::endSection(); ?>

