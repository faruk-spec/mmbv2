<?php
ob_start();
?>
<div style="margin-bottom:1.2rem;">
    <h2 style="margin:0;font-size:1.25rem;"><i class="fas fa-user-group" style="color:var(--hp-primary);margin-right:.4rem;"></i> Agents &amp; Roles</h2>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Active Tickets</th>
                <th>Total Handled</th>
                <th>Avg Resolution</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($agents)): ?>
            <tr><td colspan="6" style="color:var(--text-secondary);text-align:center;padding:1.5rem;">No agents found.</td></tr>
        <?php else: ?>
            <?php foreach ($agents as $agent): ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        <div style="width:1.8rem;height:1.8rem;border-radius:50%;background:rgba(59,130,246,.15);display:flex;align-items:center;justify-content:center;font-size:.75rem;color:var(--hp-primary);font-weight:700;">
                            <?= mb_strtoupper(mb_substr((string)($agent['name'] ?? '?'), 0, 1)) ?>
                        </div>
                        <strong><?= htmlspecialchars($agent['name']) ?></strong>
                    </div>
                </td>
                <td style="color:var(--text-secondary);font-size:.85rem;"><?= htmlspecialchars($agent['email']) ?></td>
                <td>
                    <?php
                    $roles = array_filter(array_map('trim', explode(',', (string)($agent['role'] ?? ''))));
                    foreach ($roles as $r): ?>
                    <span class="badge badge-open" style="margin-right:.2rem;"><?= htmlspecialchars($r) ?></span>
                    <?php endforeach; ?>
                </td>
                <td>
                    <span class="badge badge-<?= (int)($agent['active_tickets'] ?? 0) > 0 ? 'in_progress' : 'resolved' ?>">
                        <?= (int)($agent['active_tickets'] ?? 0) ?>
                    </span>
                </td>
                <td><?= (int)($agent['tickets_handled'] ?? 0) ?></td>
                <td><?= number_format((float)($agent['avg_resolution_hours'] ?? 0), 1) ?> hrs</td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
require PROJECT_PATH . '/views/layout.php';
