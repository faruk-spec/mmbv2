<?php
$resolvedToday = 0;
$slaBreaches   = 0;
$totalCustomers = 0;
$aiRate = 0;
if ($isAgent) {
    try {
        $db = \Core\Database::getInstance();
        $resolvedToday  = (int) ($db->fetchColumn("SELECT COUNT(*) FROM helpdesk_tickets WHERE status IN ('resolved','closed') AND DATE(updated_at)=CURDATE()") ?? 0);
        $slaBreaches    = 0; // placeholder — requires SLA rule join
        $totalCustomers = (int) ($db->fetchColumn("SELECT COUNT(DISTINCT user_id) FROM helpdesk_tickets") ?? 0);
        $liveTotal = (int) ($db->fetchColumn("SELECT COUNT(*) FROM helpdesk_live_sessions") ?? 0);
        $aiMsgs    = (int) ($db->fetchColumn("SELECT COUNT(*) FROM helpdesk_live_messages WHERE is_ai=1") ?? 0);
        $aiRate = $liveTotal > 0 ? round(($aiMsgs / max(1, $liveTotal)) * 100) : 0;
    } catch (\Throwable $e) { }
}
ob_start();
?>
<div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1.2rem;">
    <div>
        <h1 style="margin:0 0 .3rem;font-size:1.45rem;">Helpdesk Pro</h1>
        <p style="margin:0;color:var(--text-secondary);font-size:.9rem;">Enterprise support ticketing + AI/human live assistance.</p>
    </div>
    <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
        <a class="btn btn-primary" href="/projects/helpdeskpro/tickets/create"><i class="fas fa-plus"></i> New Ticket</a>
        <a class="btn btn-secondary" href="/projects/helpdeskpro/live-support"><i class="fas fa-comment-dots"></i> Open Chat</a>
        <?php if ($isAgent): ?>
        <a class="btn btn-secondary" href="/projects/helpdeskpro/analytics"><i class="fas fa-chart-bar"></i> Analytics</a>
        <?php endif; ?>
    </div>
</div>

<div class="grid g3" style="margin-bottom:1.2rem;">
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:#fbbf24;"><?= (int) ($stats['tickets_open'] ?? 0) ?></div>
        <div style="color:var(--text-secondary);font-size:.84rem;margin-top:.2rem;">Open Tickets</div>
    </div>
    <?php if ($isAgent): ?>
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:#34d399;"><?= $resolvedToday ?></div>
        <div style="color:var(--text-secondary);font-size:.84rem;margin-top:.2rem;">Resolved Today</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:var(--hp-accent);"><?= (int) ($stats['live_waiting'] ?? 0) ?></div>
        <div style="color:var(--text-secondary);font-size:.84rem;margin-top:.2rem;">Active Live Chats</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:#f87171;"><?= $slaBreaches ?></div>
        <div style="color:var(--text-secondary);font-size:.84rem;margin-top:.2rem;">SLA Breaches</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:var(--hp-primary);"><?= $totalCustomers ?></div>
        <div style="color:var(--text-secondary);font-size:.84rem;margin-top:.2rem;">Total Customers</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:#a78bfa;"><?= $aiRate ?>%</div>
        <div style="color:var(--text-secondary);font-size:.84rem;margin-top:.2rem;">AI Message Rate</div>
    </div>
    <?php else: ?>
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:var(--hp-primary);"><?= (int) ($stats['tickets_mine'] ?? 0) ?></div>
        <div style="color:var(--text-secondary);font-size:.84rem;margin-top:.2rem;">My Total Tickets</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:var(--hp-accent);"><?= (int) ($stats['live_waiting'] ?? 0) ?></div>
        <div style="color:var(--text-secondary);font-size:.84rem;margin-top:.2rem;">Active Live Chats</div>
    </div>
    <?php endif; ?>
</div>

<div class="grid <?= $isAgent ? 'g2' : '' ?>" style="margin-bottom:1.2rem;">
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.8rem;">
            <h2 style="margin:0;font-size:1rem;">Recent Tickets</h2>
            <a href="/projects/helpdeskpro/tickets" style="font-size:.82rem;color:var(--hp-accent);text-decoration:none;">View all</a>
        </div>
        <?php if (!empty($tickets)): ?>
        <table>
            <thead><tr><th>#</th><th>Subject</th><th>Status</th><th>Priority</th></tr></thead>
            <tbody>
            <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td><a href="/projects/helpdeskpro/tickets/view/<?= (int) $ticket['id'] ?>" style="color:var(--hp-accent);">#<?= (int) $ticket['id'] ?></a></td>
                    <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars(mb_substr((string)$ticket['subject'], 0, 50)) ?></td>
                    <td><span class="badge badge-<?= htmlspecialchars($ticket['status']) ?>" style="font-size:.72rem;"><?= htmlspecialchars(str_replace('_', ' ', ucfirst($ticket['status']))) ?></span></td>
                    <td style="font-size:.82rem;color:var(--text-secondary);"><?= htmlspecialchars(ucfirst($ticket['priority'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p style="color:var(--text-secondary);font-size:.88rem;">No tickets yet. <a href="/projects/helpdeskpro/tickets/create" style="color:var(--hp-accent);">Create one</a>.</p>
        <?php endif; ?>
    </div>

    <?php if ($isAgent): ?>
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.8rem;">
            <h2 style="margin:0;font-size:1rem;">Live Sessions Queue</h2>
            <a href="/projects/helpdeskpro/agent/live-support" style="font-size:.82rem;color:var(--hp-accent);text-decoration:none;">Agent Console</a>
        </div>
        <?php if (!empty($liveSessions)): ?>
            <table>
                <thead><tr><th>Session</th><th>Customer</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($liveSessions as $session): ?>
                    <tr>
                        <td>#<?= (int) $session['id'] ?></td>
                        <td><?= htmlspecialchars(mb_substr((string)($session['user_name'] ?? $session['customer_name'] ?? 'Customer'), 0, 30)) ?></td>
                        <td><span class="badge badge-open" style="font-size:.72rem;"><?= htmlspecialchars(str_replace('_', ' ', ucfirst($session['status']))) ?></span></td>
                        <td><a href="/projects/helpdeskpro/agent/live-support?sid=<?= (int) $session['id'] ?>" style="color:var(--hp-accent);font-size:.82rem;">Open</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color:var(--text-secondary);font-size:.88rem;">No active live sessions.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php if ($isAgent && !empty($agentPerformance ?? [])): ?>
<div class="card">
    <h2 style="margin:0 0 .8rem;font-size:1rem;">Agent Performance (Quick View)</h2>
    <table>
        <thead><tr><th>Agent</th><th>Active</th><th>Handled</th><th>Avg Resolution</th></tr></thead>
        <tbody>
        <?php foreach (array_slice($agentPerformance ?? [], 0, 5) as $ap): ?>
            <tr>
                <td><?= htmlspecialchars($ap['name']) ?></td>
                <td><span class="badge badge-open"><?= (int)($ap['active_tickets'] ?? 0) ?></span></td>
                <td><?= (int)($ap['tickets_handled'] ?? 0) ?></td>
                <td><?= number_format((float)($ap['avg_resolution_hours'] ?? 0), 1) ?> hrs</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php $content = ob_get_clean(); require PROJECT_PATH . '/views/layout.php'; ?>

