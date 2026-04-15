<?php
/**
 * Support Admin — All Tickets (within support portal)
 */
use Core\View;
View::extend('main');

if (!function_exists('satStatusBadge')) {
    function satStatusBadge(string $s): string {
        $m = ['open'=>['#00f0ff','rgba(0,240,255,.12)','Open'],'in_progress'=>['#ff9f43','rgba(255,159,67,.12)','In Progress'],'waiting_customer'=>['#a78bfa','rgba(167,139,250,.12)','Waiting'],'resolved'=>['#00ff88','rgba(0,255,136,.12)','Resolved'],'closed'=>['#8892a6','rgba(136,146,166,.12)','Closed']];
        [$c,$bg,$l]=$m[$s]??['#8892a6','rgba(0,0,0,.2)',ucfirst($s)];
        return "<span style=\"display:inline-block;padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:600;background:{$bg};color:{$c}\">{$l}</span>";
    }
}
if (!function_exists('satPriorityBadge')) {
    function satPriorityBadge(string $p): string {
        $m = ['low'=>['#8892a6','rgba(136,146,166,.12)','Low'],'medium'=>['#00f0ff','rgba(0,240,255,.12)','Medium'],'high'=>['#ff9f43','rgba(255,159,67,.12)','High'],'urgent'=>['#ff6b6b','rgba(255,107,107,.12)','Urgent']];
        [$c,$bg,$l]=$m[$p]??['#8892a6','rgba(0,0,0,.2)',ucfirst($p)];
        return "<span style=\"display:inline-block;padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:600;background:{$bg};color:{$c}\">{$l}</span>";
    }
}
?>

<?php View::section('styles'); ?>
<style>.dashboard-main-content { padding: 0 !important; }</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div style="display:flex;min-height:calc(100vh - 64px);align-items:stretch;">
    <?php include dirname(__DIR__) . '/_sidebar.php'; ?>
    <div style="flex:1;padding:24px 28px;min-width:0;overflow:auto;">

        <!-- Header + action bar -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
            <h1 style="font-size:1.35rem;font-weight:700;color:var(--text-primary,#e8eefc);margin:0;display:flex;align-items:center;gap:10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00f0ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="m9 12 2 2 4-4"/></svg>
                All Requests<?= $statusFilter ? ' — ' . ucfirst(str_replace('_', ' ', $statusFilter)) : '' ?>
            </h1>
            <a href="/support/create" style="display:inline-flex;align-items:center;gap:7px;padding:8px 16px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border-radius:8px;color:white;font-weight:600;text-decoration:none;font-size:.855rem;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Incident
            </a>
        </div>

        <!-- Stats row -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(100px,1fr));gap:10px;margin-bottom:18px;">
            <?php foreach ([
                ['open','#00f0ff','Open'],['in_progress','#ff9f43','In Progress'],
                ['resolved','#00ff88','Resolved'],['closed','#8892a6','Closed'],['total','#a78bfa','Total']
            ] as [$k,$c,$l]): ?>
            <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.07));border-radius:9px;padding:12px 14px;text-align:center;position:relative;overflow:hidden;">
                <div style="position:absolute;top:0;left:0;right:0;height:2px;background:<?= $c ?>40;"></div>
                <div style="font-size:1.4rem;font-weight:700;color:<?= $c ?>;"><?= (int)($stats[$k] ?? 0) ?></div>
                <div style="color:var(--text-secondary,#8892a6);font-size:.74rem;margin-top:2px;"><?= $l ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Status filter tabs -->
        <div style="display:flex;gap:6px;margin-bottom:16px;flex-wrap:wrap;">
            <?php foreach ([
                ['' , 'All'], ['open','Open'], ['in_progress','In Progress'],
                ['waiting_customer','Waiting'], ['resolved','Resolved'], ['closed','Closed']
            ] as [$sv, $sl]): ?>
            <a href="/support/admin/tickets<?= $sv ? '?status='.$sv : '' ?>"
               style="padding:5px 14px;border-radius:6px;font-size:.78rem;font-weight:600;text-decoration:none;<?= ($statusFilter===$sv) ? 'background:rgba(0,240,255,.15);color:#00f0ff;border:1px solid rgba(0,240,255,.3);' : 'background:var(--bg-card,#0f0f18);color:var(--text-secondary,#8892a6);border:1px solid var(--border-color,rgba(255,255,255,.07));' ?>">
                <?= $sl ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Tickets table -->
        <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.07));border-radius:12px;overflow:hidden;">
            <?php if (empty($tickets)): ?>
            <div style="padding:50px 20px;text-align:center;color:var(--text-secondary,#8892a6);">No tickets found.</div>
            <?php else: ?>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--border-color,rgba(255,255,255,.07));">
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.71rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">#</th>
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.71rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Subject</th>
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.71rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">User</th>
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.71rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Status</th>
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.71rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Priority</th>
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.71rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Assigned</th>
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.71rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Date</th>
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.71rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): ?>
                        <tr style="border-bottom:1px solid var(--border-color,rgba(255,255,255,.04));transition:background .15s;" onmouseover="this.style.background='rgba(255,255,255,.02)'" onmouseout="this.style.background=''">
                            <td style="padding:12px 14px;font-family:monospace;font-size:.8rem;color:var(--text-secondary,#8892a6);">#<?= sprintf('%07d', (int)$t['id']) ?></td>
                            <td style="padding:12px 14px;">
                                <a href="/support/view/<?= (int)$t['id'] ?>" style="color:var(--text-primary,#e8eefc);text-decoration:none;font-weight:500;font-size:.88rem;">
                                    <?= htmlspecialchars($t['subject']) ?>
                                </a>
                            </td>
                            <td style="padding:12px 14px;font-size:.83rem;color:var(--text-secondary,#8892a6);"><?= htmlspecialchars($t['user_name'] ?? '—') ?></td>
                            <td style="padding:12px 14px;"><?= satStatusBadge($t['status']) ?></td>
                            <td style="padding:12px 14px;"><?= satPriorityBadge($t['priority']) ?></td>
                            <td style="padding:12px 14px;font-size:.83rem;color:var(--text-secondary,#8892a6);"><?= htmlspecialchars($t['agent_name'] ?? '—') ?></td>
                            <td style="padding:12px 14px;font-size:.78rem;color:var(--text-secondary,#8892a6);"><?= date('M j, Y', strtotime($t['created_at'])) ?></td>
                            <td style="padding:12px 14px;">
                                <div style="display:flex;gap:6px;">
                                    <a href="/support/view/<?= (int)$t['id'] ?>" style="padding:4px 10px;background:rgba(0,240,255,.1);color:#00f0ff;border-radius:5px;text-decoration:none;font-size:.76rem;font-weight:500;">View</a>
                                    <a href="/admin/support/tickets/<?= (int)$t['id'] ?>" style="padding:4px 10px;background:rgba(167,139,250,.1);color:#a78bfa;border-radius:5px;text-decoration:none;font-size:.76rem;font-weight:500;">Manage</a>
                                </div>
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
