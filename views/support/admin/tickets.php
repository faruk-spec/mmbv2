<?php
/**
 * Support Admin — All Tickets (within support portal)
 */
use Core\View;
View::extend('main');

if (!function_exists('satStatusBadge')) {
    function satStatusBadge(string $s): string {
        $classes = ['open'=>'sp-badge-open','in_progress'=>'sp-badge-prog','waiting_customer'=>'sp-badge-wait','resolved'=>'sp-badge-done','closed'=>'sp-badge-closed'];
        $labels  = ['open'=>'Open','in_progress'=>'In Progress','waiting_customer'=>'Waiting','resolved'=>'Resolved','closed'=>'Closed'];
        $cls = $classes[$s] ?? 'sp-badge-closed';
        $lbl = $labels[$s]  ?? ucfirst($s);
        return "<span class=\"sp-badge {$cls}\">{$lbl}</span>";
    }
}
if (!function_exists('satPriorityBadge')) {
    function satPriorityBadge(string $p): string {
        $classes = ['low'=>'sp-badge-low','medium'=>'sp-badge-medium','high'=>'sp-badge-high','urgent'=>'sp-badge-urgent'];
        $labels  = ['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent'];
        $cls = $classes[$p] ?? 'sp-badge-closed';
        $lbl = $labels[$p]  ?? ucfirst($p);
        return "<span class=\"sp-badge {$cls}\">{$lbl}</span>";
    }
}
?>

<?php View::section('styles'); ?>
<?php include dirname(__DIR__) . '/_styles.php'; ?>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div style="display:flex;min-height:calc(100vh - 64px);align-items:stretch;">
    <?php include dirname(__DIR__) . '/_sidebar.php'; ?>
    <div style="flex:1;padding:24px 28px;min-width:0;overflow:auto;">

        <!-- Header -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
            <h1 style="font-size:1.35rem;font-weight:700;color:var(--text-primary);margin:0;display:flex;align-items:center;gap:10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color:var(--cyan)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="m9 12 2 2 4-4"/></svg>
                All Requests<?= $statusFilter ? ' — ' . ucfirst(str_replace('_', ' ', $statusFilter)) : '' ?>
            </h1>
            <a href="/support/new" class="sp-btn sp-btn-primary sp-btn-sm">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Incident
            </a>
        </div>

        <!-- Stats row -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(100px,1fr));gap:10px;margin-bottom:18px;">
            <?php foreach ([
                ['open',       'var(--cyan)',   'Open'],
                ['in_progress','var(--orange)', 'In Progress'],
                ['resolved',   'var(--green)',  'Resolved'],
                ['closed',     'var(--text-secondary)', 'Closed'],
                ['total',      'var(--purple)', 'Total'],
            ] as [$k,$c,$l]): ?>
            <div class="sp-card" style="padding:12px 14px;text-align:center;position:relative;overflow:hidden;">
                <div class="sp-stat-bar" style="background:<?= $c ?>;opacity:.35;"></div>
                <div style="font-size:1.4rem;font-weight:700;color:<?= $c ?>;"><?= (int)($stats[$k] ?? 0) ?></div>
                <div style="color:var(--text-secondary);font-size:.74rem;margin-top:2px;"><?= $l ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <form method="GET" action="/support/admin/tickets" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:8px;margin-bottom:14px;">
            <input type="text" name="q" value="<?= htmlspecialchars($searchQuery ?? '') ?>" placeholder="Search subject/user..." style="padding:8px 10px;border:1px solid var(--border-color);border-radius:7px;background:var(--bg-secondary);color:var(--text-primary);">
            <select name="status" style="padding:8px 10px;border:1px solid var(--border-color);border-radius:7px;background:var(--bg-secondary);color:var(--text-primary);">
                <option value="">All Status</option>
                <?php foreach (['open'=>'Open','in_progress'=>'In Progress','waiting_customer'=>'Waiting','resolved'=>'Resolved','closed'=>'Closed'] as $v => $l): ?>
                <option value="<?= $v ?>"<?= ($statusFilter ?? '') === $v ? ' selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
            <select name="priority" style="padding:8px 10px;border:1px solid var(--border-color);border-radius:7px;background:var(--bg-secondary);color:var(--text-primary);">
                <option value="">All Priority</option>
                <?php foreach (['low','medium','high','urgent'] as $v): ?>
                <option value="<?= $v ?>"<?= ($priorityFilter ?? '') === $v ? ' selected' : '' ?>><?= ucfirst($v) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="assigned_to" style="padding:8px 10px;border:1px solid var(--border-color);border-radius:7px;background:var(--bg-secondary);color:var(--text-primary);">
                <option value="">All Agents</option>
                <?php foreach (($agents ?? []) as $agent): ?>
                <option value="<?= (int) $agent['id'] ?>"<?= (string) ($assignedFilter ?? '') === (string) $agent['id'] ? ' selected' : '' ?>>
                    <?= htmlspecialchars($agent['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="sp-btn sp-btn-primary sp-btn-sm">Apply</button>
        </form>

        <!-- Status filter tabs -->
        <div style="display:flex;gap:6px;margin-bottom:16px;flex-wrap:wrap;">
            <?php foreach ([
                ['' , 'All'], ['open','Open'], ['in_progress','In Progress'],
                ['waiting_customer','Waiting'], ['resolved','Resolved'], ['closed','Closed']
            ] as [$sv, $sl]): ?>
            <a href="/support/admin/tickets<?= $sv ? '?status='.$sv : '' ?>"
               class="sp-filter-tab<?= ($statusFilter===$sv) ? ' active' : '' ?>">
                <?= $sl ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Tickets table -->
        <div class="sp-card" style="overflow:hidden;">
            <?php if (empty($tickets)): ?>
            <div style="padding:50px 20px;text-align:center;color:var(--text-secondary);">No tickets found.</div>
            <?php else: ?>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--border-color);">
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary);font-size:.71rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">#</th>
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary);font-size:.71rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Subject</th>
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary);font-size:.71rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">User</th>
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary);font-size:.71rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Status</th>
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary);font-size:.71rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Priority</th>
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary);font-size:.71rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Assigned</th>
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary);font-size:.71rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Date</th>
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary);font-size:.71rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): ?>
                        <tr class="sp-tr" style="border-bottom:1px solid var(--border-color);">
                            <td style="padding:12px 14px;font-family:monospace;font-size:.8rem;color:var(--text-secondary);">#<?= sprintf('%07d', (int)$t['id']) ?></td>
                            <td style="padding:12px 14px;">
                                <a href="/support/admin/ticket/<?= (int)$t['id'] ?>" style="color:var(--text-primary);text-decoration:none;font-weight:500;font-size:.88rem;">
                                    <?= htmlspecialchars($t['subject']) ?>
                                </a>
                            </td>
                            <td style="padding:12px 14px;font-size:.83rem;color:var(--text-secondary);"><?= htmlspecialchars($t['user_name'] ?? '—') ?></td>
                            <td style="padding:12px 14px;"><?= satStatusBadge($t['status']) ?></td>
                            <td style="padding:12px 14px;"><?= satPriorityBadge($t['priority']) ?></td>
                            <td style="padding:12px 14px;font-size:.83rem;color:var(--text-secondary);">
                                <form method="POST" action="/support/admin/ticket/<?= (int)$t['id'] ?>/assign" style="display:flex;gap:6px;align-items:center;">
                                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                    <select name="assigned_to" style="min-width:130px;padding:5px 7px;border:1px solid var(--border-color);border-radius:6px;background:var(--bg-secondary);color:var(--text-primary);font-size:.78rem;">
                                        <option value="">Assign...</option>
                                        <?php foreach (($agents ?? []) as $agent): ?>
                                        <option value="<?= (int) $agent['id'] ?>"<?= (int)($t['assigned_to'] ?? 0) === (int)$agent['id'] ? ' selected' : '' ?>>
                                            <?= htmlspecialchars($agent['name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="sp-btn sp-btn-sm sp-btn-outline" style="padding:5px 8px;">Save</button>
                                </form>
                            </td>
                            <td style="padding:12px 14px;font-size:.78rem;color:var(--text-secondary);"><?= date('M j, Y', strtotime($t['created_at'])) ?></td>
                            <td style="padding:12px 14px;">
                                <div style="display:flex;gap:6px;">
                                    <a href="/support/admin/ticket/<?= (int)$t['id'] ?>" class="sp-action-manage">Manage</a>
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
