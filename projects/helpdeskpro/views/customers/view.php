<?php
ob_start();
?>
<div style="margin-bottom:1rem;">
    <a href="/projects/helpdeskpro/customers" style="color:var(--text-secondary);text-decoration:none;font-size:.85rem;"><i class="fas fa-arrow-left"></i> Back to Customers</a>
</div>

<div class="card" style="margin-bottom:1.2rem;">
    <div style="display:flex;align-items:center;gap:1rem;">
        <div style="width:3rem;height:3rem;border-radius:50%;background:rgba(6,182,212,.12);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--hp-accent);font-weight:700;">
            <?= mb_strtoupper(mb_substr((string)($customer['name'] ?? '?'), 0, 1)) ?>
        </div>
        <div>
            <h2 style="margin:0;font-size:1.2rem;"><?= htmlspecialchars($customer['name']) ?></h2>
            <p style="margin:.2rem 0 0;color:var(--text-secondary);font-size:.88rem;"><?= htmlspecialchars($customer['email']) ?></p>
            <?php if (!empty($customer['created_at'])): ?>
            <p style="margin:.1rem 0 0;color:var(--text-secondary);font-size:.8rem;">Member since <?= htmlspecialchars(date('M Y', strtotime($customer['created_at']))) ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:1.2rem;">
    <h3 style="margin:0 0 .75rem;font-size:1rem;">Recent Tickets</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Subject</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($tickets)): ?>
            <tr><td colspan="6" style="color:var(--text-secondary);text-align:center;padding:1.5rem;">No tickets.</td></tr>
        <?php else: ?>
            <?php foreach ($tickets as $ticket): ?>
            <tr>
                <td style="color:var(--text-secondary);">#<?= (int)$ticket['id'] ?></td>
                <td><?= htmlspecialchars(mb_substr((string)$ticket['subject'], 0, 60)) ?></td>
                <td><span class="badge badge-<?= $ticket['priority'] === 'urgent' || $ticket['priority'] === 'high' ? 'in_progress' : 'open' ?>"><?= htmlspecialchars(ucfirst($ticket['priority'])) ?></span></td>
                <td><span class="badge badge-<?= htmlspecialchars($ticket['status']) ?>"><?= htmlspecialchars(str_replace('_', ' ', ucfirst($ticket['status']))) ?></span></td>
                <td style="color:var(--text-secondary);font-size:.82rem;"><?= htmlspecialchars(date('M d, Y', strtotime($ticket['created_at']))) ?></td>
                <td><a href="/projects/helpdeskpro/tickets/view/<?= (int)$ticket['id'] ?>" class="btn btn-secondary" style="padding:.25rem .5rem;font-size:.78rem;">View</a></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h3 style="margin:0 0 .75rem;font-size:1rem;">Live Chat Sessions</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Status</th>
                <th>Started</th>
                <th>Closed</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($liveSessions)): ?>
            <tr><td colspan="4" style="color:var(--text-secondary);text-align:center;padding:1.5rem;">No live sessions.</td></tr>
        <?php else: ?>
            <?php foreach ($liveSessions as $sess): ?>
            <tr>
                <td style="color:var(--text-secondary);">#<?= (int)$sess['id'] ?></td>
                <td><span class="badge badge-<?= htmlspecialchars($sess['status']) ?>"><?= htmlspecialchars(str_replace('_', ' ', ucfirst($sess['status']))) ?></span></td>
                <td style="color:var(--text-secondary);font-size:.82rem;"><?= htmlspecialchars(date('M d, Y H:i', strtotime($sess['created_at']))) ?></td>
                <td style="color:var(--text-secondary);font-size:.82rem;"><?= !empty($sess['closed_at']) ? htmlspecialchars(date('M d, Y H:i', strtotime($sess['closed_at']))) : '—' ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
require PROJECT_PATH . '/views/layout.php';
