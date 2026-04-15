<?php ob_start(); ?>
<div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
    <div>
        <h1 style="margin:0 0 .25rem;font-size:1.3rem;">Tickets</h1>
        <p style="margin:0;color:var(--text-secondary);font-size:.88rem;">Track support issues, updates, and resolution timelines.</p>
    </div>
    <a class="btn btn-primary" href="/projects/helpdeskpro/tickets/create"><i class="fas fa-plus"></i> Create Ticket</a>
</div>

<div class="card" style="margin-top:1rem;">
    <?php if (!empty($tickets)): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <?php if (!empty($isAgent)): ?><th>Requester</th><?php endif; ?>
                <th>Subject</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Updated</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($tickets as $ticket): ?>
            <tr>
                <td>#<?= (int) $ticket['id'] ?></td>
                <?php if (!empty($isAgent)): ?><td><?= htmlspecialchars($ticket['requester_name'] ?? '-') ?></td><?php endif; ?>
                <td><?= htmlspecialchars($ticket['subject']) ?></td>
                <td><span class="badge badge-<?= htmlspecialchars($ticket['status']) ?>"><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $ticket['status']))) ?></span></td>
                <td><?= htmlspecialchars(strtoupper($ticket['priority'])) ?></td>
                <td><?= htmlspecialchars($ticket['updated_at'] ?? $ticket['created_at']) ?></td>
                <td><a href="/projects/helpdeskpro/tickets/view/<?= (int) $ticket['id'] ?>">Open</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p style="color:var(--text-secondary);font-size:.88rem;">No tickets available.</p>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); require PROJECT_PATH . '/views/layout.php'; ?>
