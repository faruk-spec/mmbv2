<?php
ob_start();
$totalTickets  = (int)($ticketAnalytics['total'] ?? 0);
$openTickets   = (int)($ticketAnalytics['open'] ?? 0);
$resolvedTickets = (int)(($ticketAnalytics['resolved'] ?? 0) + ($ticketAnalytics['closed'] ?? 0));
$totalLive     = (int)($liveAnalytics['total_sessions'] ?? 0);
$aiHandled     = (int)($liveAnalytics['ai_handled'] ?? 0);
$aiRate        = $totalLive > 0 ? round(($aiHandled / max(1, $totalLive)) * 100) : 0;
?>
<div style="margin-bottom:1.2rem;">
    <h2 style="margin:0;font-size:1.25rem;"><i class="fas fa-chart-bar" style="color:var(--hp-primary);margin-right:.4rem;"></i> Analytics</h2>
</div>

<div class="grid g3" style="margin-bottom:1.2rem;">
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:var(--hp-primary);"><?= $totalTickets ?></div>
        <div style="color:var(--text-secondary);font-size:.85rem;margin-top:.2rem;">Total Tickets</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:#fbbf24;"><?= $openTickets ?></div>
        <div style="color:var(--text-secondary);font-size:.85rem;margin-top:.2rem;">Open Tickets</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:#34d399;"><?= $resolvedTickets ?></div>
        <div style="color:var(--text-secondary);font-size:.85rem;margin-top:.2rem;">Resolved / Closed</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:var(--hp-accent);"><?= $totalLive ?></div>
        <div style="color:var(--text-secondary);font-size:.85rem;margin-top:.2rem;">Live Sessions Total</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:#a78bfa;"><?= (int)($liveAnalytics['active'] ?? 0) ?></div>
        <div style="color:var(--text-secondary);font-size:.85rem;margin-top:.2rem;">Active Live Chats</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:#34d399;"><?= $aiRate ?>%</div>
        <div style="color:var(--text-secondary);font-size:.85rem;margin-top:.2rem;">AI Message Rate</div>
    </div>
</div>

<div class="grid g2" style="margin-bottom:1.2rem;">
    <div class="card">
        <h3 style="margin:0 0 .75rem;font-size:1rem;">Ticket Status Breakdown</h3>
        <table>
            <thead><tr><th>Status</th><th>Count</th></tr></thead>
            <tbody>
                <tr><td><span class="badge badge-open">Open</span></td><td><?= (int)($ticketAnalytics['open'] ?? 0) ?></td></tr>
                <tr><td><span class="badge badge-in_progress">In Progress</span></td><td><?= (int)($ticketAnalytics['in_progress'] ?? 0) ?></td></tr>
                <tr><td><span class="badge badge-resolved">Resolved</span></td><td><?= (int)($ticketAnalytics['resolved'] ?? 0) ?></td></tr>
                <tr><td><span class="badge badge-closed">Closed</span></td><td><?= (int)($ticketAnalytics['closed'] ?? 0) ?></td></tr>
            </tbody>
        </table>
    </div>
    <div class="card">
        <h3 style="margin:0 0 .75rem;font-size:1rem;">Tickets by Priority</h3>
        <table>
            <thead><tr><th>Priority</th><th>Count</th></tr></thead>
            <tbody>
            <?php
            $priorityCounts = [];
            foreach (($ticketAnalytics['by_priority'] ?? []) as $row) {
                $priorityCounts[$row['priority']] = (int)$row['cnt'];
            }
            foreach (['urgent', 'high', 'medium', 'low'] as $prio): ?>
            <tr>
                <td><span class="badge badge-<?= $prio === 'urgent' || $prio === 'high' ? 'in_progress' : 'open' ?>"><?= ucfirst($prio) ?></span></td>
                <td><?= $priorityCounts[$prio] ?? 0 ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3 style="margin:0 0 .75rem;font-size:1rem;">Agent Performance</h3>
    <table>
        <thead>
            <tr>
                <th>Agent</th>
                <th>Email</th>
                <th>Tickets Handled</th>
                <th>Active Tickets</th>
                <th>Avg Resolution (hrs)</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($agentPerformance)): ?>
            <tr><td colspan="5" style="color:var(--text-secondary);text-align:center;padding:1.5rem;">No agent data.</td></tr>
        <?php else: ?>
            <?php foreach ($agentPerformance as $agent): ?>
            <tr>
                <td><strong><?= htmlspecialchars($agent['name']) ?></strong></td>
                <td style="color:var(--text-secondary);font-size:.85rem;"><?= htmlspecialchars($agent['email']) ?></td>
                <td><span class="badge badge-resolved"><?= (int)$agent['tickets_handled'] ?></span></td>
                <td><span class="badge badge-open"><?= (int)$agent['active_tickets'] ?></span></td>
                <td><?= number_format((float)$agent['avg_resolution_hours'], 1) ?> hrs</td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
require PROJECT_PATH . '/views/layout.php';
