<?php
ob_start();
?>
<div style="margin-bottom:1.2rem;">
    <h2 style="margin:0;font-size:1.25rem;"><i class="fas fa-users" style="color:var(--hp-primary);margin-right:.4rem;"></i> Customers</h2>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Total Tickets</th>
                <th>Last Activity</th>
                <th>Profile</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($customers)): ?>
            <tr><td colspan="5" style="color:var(--text-secondary);text-align:center;padding:1.5rem;">No customers found.</td></tr>
        <?php else: ?>
            <?php foreach ($customers as $cust): ?>
            <?php
                $lastActivity = max(
                    (string)($cust['last_ticket_at'] ?? ''),
                    (string)($cust['last_chat_at'] ?? '')
                );
            ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        <div style="width:1.8rem;height:1.8rem;border-radius:50%;background:rgba(6,182,212,.12);display:flex;align-items:center;justify-content:center;font-size:.75rem;color:var(--hp-accent);font-weight:700;">
                            <?= mb_strtoupper(mb_substr((string)($cust['name'] ?? '?'), 0, 1)) ?>
                        </div>
                        <strong><?= htmlspecialchars($cust['name']) ?></strong>
                    </div>
                </td>
                <td style="color:var(--text-secondary);font-size:.85rem;"><?= htmlspecialchars($cust['email']) ?></td>
                <td><span class="badge badge-open"><?= (int)($cust['ticket_count'] ?? 0) ?></span></td>
                <td style="color:var(--text-secondary);font-size:.85rem;"><?= $lastActivity ? htmlspecialchars(date('M d, Y', strtotime($lastActivity))) : '—' ?></td>
                <td>
                    <a href="/projects/helpdeskpro/customers/view/<?= (int)$cust['id'] ?>" class="btn btn-secondary" style="padding:.3rem .6rem;font-size:.8rem;"><i class="fas fa-eye"></i> View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
require PROJECT_PATH . '/views/layout.php';
