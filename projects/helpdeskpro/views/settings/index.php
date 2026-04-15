<?php
$csrfToken = \Core\Security::generateCsrfToken();
$priorities = ['low', 'medium', 'high', 'urgent'];
$defaults = ['low' => [48, 120], 'medium' => [24, 72], 'high' => [8, 24], 'urgent' => [2, 8]];

$slaMap = [];
foreach ($slaRules as $rule) {
    $slaMap[$rule['priority']] = $rule;
}
ob_start();
?>
<div style="margin-bottom:1.2rem;">
    <h2 style="margin:0;font-size:1.25rem;"><i class="fas fa-gear" style="color:var(--hp-primary);margin-right:.4rem;"></i> Settings</h2>
</div>

<div class="card">
    <h3 style="margin:0 0 .75rem;font-size:1rem;"><i class="fas fa-clock" style="margin-right:.4rem;color:var(--hp-accent);"></i> SLA Rules</h3>
    <p style="margin:0 0 1rem;color:var(--text-secondary);font-size:.85rem;">Define response and resolution time targets (in hours) for each ticket priority.</p>
    <form method="POST" action="/projects/helpdeskpro/settings/sla">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <table>
            <thead>
                <tr>
                    <th>Priority</th>
                    <th>First Response (hours)</th>
                    <th>Resolution (hours)</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($priorities as $prio): ?>
            <?php
            $fr  = (int)($slaMap[$prio]['first_response_hours'] ?? $defaults[$prio][0]);
            $res = (int)($slaMap[$prio]['resolution_hours'] ?? $defaults[$prio][1]);
            ?>
            <tr>
                <td>
                    <span class="badge badge-<?= $prio === 'urgent' || $prio === 'high' ? 'in_progress' : 'open' ?>" style="font-size:.82rem;">
                        <?= ucfirst($prio) ?>
                    </span>
                </td>
                <td>
                    <input type="number" name="first_response[<?= $prio ?>]" value="<?= $fr ?>" min="1" max="9999" style="width:100px;">
                </td>
                <td>
                    <input type="number" name="resolution[<?= $prio ?>]" value="<?= $res ?>" min="1" max="9999" style="width:100px;">
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div style="margin-top:1rem;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save SLA Rules</button>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
require PROJECT_PATH . '/views/layout.php';
