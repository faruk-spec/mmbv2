<?php ob_start(); ?>
<div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
    <div>
        <h1 style="margin:0 0 .3rem;font-size:1.45rem;">Helpdesk Pro</h1>
        <p style="margin:0;color:var(--text-secondary);font-size:.9rem;">Enterprise support ticketing + AI/human live assistance.</p>
    </div>
    <div style="display:flex;gap:.6rem;">
        <a class="btn btn-primary" href="/projects/helpdeskpro/tickets/create"><i class="fas fa-plus"></i> New Ticket</a>
        <a class="btn btn-secondary" href="/projects/helpdeskpro/live-support"><i class="fas fa-comment-dots"></i> Open Chat</a>
    </div>
</div>

<div class="grid g3" style="margin-top:1rem;">
    <div class="card"><div style="font-size:1.6rem;font-weight:700;"><?= (int) ($stats['tickets_open'] ?? 0) ?></div><div style="color:var(--text-secondary);font-size:.84rem;">Open Tickets</div></div>
    <div class="card"><div style="font-size:1.6rem;font-weight:700;"><?= (int) ($stats['tickets_mine'] ?? 0) ?></div><div style="color:var(--text-secondary);font-size:.84rem;"><?= !empty($isAgent) ? 'Assigned / Managed' : 'Total My Tickets' ?></div></div>
    <div class="card"><div style="font-size:1.6rem;font-weight:700;"><?= (int) ($stats['live_waiting'] ?? 0) ?></div><div style="color:var(--text-secondary);font-size:.84rem;"><?= !empty($isAgent) ? 'Live Chats Waiting' : 'My Active Live Chats' ?></div></div>
</div>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.8rem;">
        <h2 style="margin:0;font-size:1rem;">Recent Tickets</h2>
        <a href="/projects/helpdeskpro/tickets" style="font-size:.82rem;color:var(--hp-accent);text-decoration:none;">View all</a>
    </div>
    <?php if (!empty($tickets)): ?>
    <table>
        <thead><tr><th>#</th><th>Subject</th><th>Status</th><th>Priority</th><th>Updated</th></tr></thead>
        <tbody>
        <?php foreach ($tickets as $ticket): ?>
            <tr>
                <td><a href="/projects/helpdeskpro/tickets/view/<?= (int) $ticket['id'] ?>">#<?= (int) $ticket['id'] ?></a></td>
                <td><?= htmlspecialchars($ticket['subject']) ?></td>
                <td><span class="badge badge-<?= htmlspecialchars($ticket['status']) ?>"><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $ticket['status']))) ?></span></td>
                <td><?= htmlspecialchars(strtoupper($ticket['priority'])) ?></td>
                <td><?= htmlspecialchars($ticket['updated_at'] ?? $ticket['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p style="color:var(--text-secondary);font-size:.88rem;">No tickets yet. Create one to start tracking support requests.</p>
    <?php endif; ?>
</div>

<?php if (!empty($isAgent)): ?>
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.8rem;">
        <h2 style="margin:0;font-size:1rem;">Live Sessions Queue</h2>
        <a href="/projects/helpdeskpro/agent/live-support" style="font-size:.82rem;color:var(--hp-accent);text-decoration:none;">Open Agent Console</a>
    </div>
    <?php if (!empty($liveSessions)): ?>
        <table>
            <thead><tr><th>Session</th><th>Customer</th><th>Status</th><th>Messages</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($liveSessions as $session): ?>
                <tr>
                    <td>#<?= (int) $session['id'] ?></td>
                    <td><?= htmlspecialchars($session['user_name'] ?? $session['customer_name'] ?? 'Customer') ?></td>
                    <td><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $session['status']))) ?></td>
                    <td><?= (int) ($session['message_count'] ?? 0) ?></td>
                    <td><a href="/projects/helpdeskpro/agent/live-support?sid=<?= (int) $session['id'] ?>">Open</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color:var(--text-secondary);font-size:.88rem;">No active live sessions right now.</p>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php $content = ob_get_clean(); require PROJECT_PATH . '/views/layout.php'; ?>
