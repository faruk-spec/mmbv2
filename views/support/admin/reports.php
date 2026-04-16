<?php
use Core\View;
View::extend('main');
?>

<?php View::section('styles'); ?>
<style>.dashboard-main-content { padding: 0 !important; }</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div class="sp-layout">
    <?php include dirname(__DIR__) . '/_sidebar.php'; ?>
    <div class="sp-main">

        <!-- Mobile menu button -->
        <button class="sp-menu-btn" onclick="spOpenMenu()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            Menu
        </button>

        <h1 style="font-size:1.35rem;font-weight:700;color:var(--text-primary);margin:0 0 18px;">Reports & KPI Dashboard</h1>

        <form method="GET" action="/support/admin/reports" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:14px;display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;margin-bottom:18px;">
            <input type="text" name="q" value="<?= htmlspecialchars($filters['q'] ?? '') ?>" placeholder="Search subject/user..." class="sp-input" style="padding:9px 10px;border:1px solid var(--border-color);border-radius:7px;background:var(--bg-secondary);color:var(--text-primary);">
            <select name="status" style="padding:9px 10px;border:1px solid var(--border-color);border-radius:7px;background:var(--bg-secondary);color:var(--text-primary);">
                <option value="">All Status</option>
                <?php foreach (['open'=>'Open','in_progress'=>'In Progress','waiting_customer'=>'Waiting','resolved'=>'Resolved','closed'=>'Closed'] as $key => $lbl): ?>
                    <option value="<?= $key ?>"<?= ($filters['status'] ?? '') === $key ? ' selected' : '' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
            </select>
            <select name="priority" style="padding:9px 10px;border:1px solid var(--border-color);border-radius:7px;background:var(--bg-secondary);color:var(--text-primary);">
                <option value="">All Priority</option>
                <?php foreach (['low','medium','high','urgent'] as $key): ?>
                    <option value="<?= $key ?>"<?= ($filters['priority'] ?? '') === $key ? ' selected' : '' ?>><?= ucfirst($key) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="assigned_to" style="padding:9px 10px;border:1px solid var(--border-color);border-radius:7px;background:var(--bg-secondary);color:var(--text-primary);">
                <option value="">All Agents</option>
                <?php foreach (($agents ?? []) as $agent): ?>
                    <option value="<?= (int) $agent['id'] ?>"<?= (int) ($filters['assigned_to'] ?? 0) === (int) $agent['id'] ? ' selected' : '' ?>>
                        <?= htmlspecialchars($agent['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="from_date" value="<?= htmlspecialchars($filters['from_date'] ?? '') ?>" style="padding:9px 10px;border:1px solid var(--border-color);border-radius:7px;background:var(--bg-secondary);color:var(--text-primary);">
            <input type="date" name="to_date" value="<?= htmlspecialchars($filters['to_date'] ?? '') ?>" style="padding:9px 10px;border:1px solid var(--border-color);border-radius:7px;background:var(--bg-secondary);color:var(--text-primary);">
            <button type="submit" class="sp-btn sp-btn-primary sp-btn-sm">Apply Filters</button>
            <a href="/support/admin/reports/export?<?= http_build_query($filters ?? []) ?>" class="sp-btn sp-btn-outline sp-btn-sm" style="text-decoration:none;text-align:center;">Export CSV</a>
        </form>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-bottom:18px;">
            <?php foreach ([
                ['open', 'Open', '#00f0ff'],
                ['in_progress', 'In Progress', '#ff9f43'],
                ['waiting_customer', 'Waiting', '#a78bfa'],
                ['resolved', 'Resolved', '#00ff88'],
                ['closed', 'Closed', '#8892a6'],
                ['total', 'Total', '#e8eefc'],
            ] as [$k, $l, $c]): ?>
                <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:12px 14px;">
                    <div style="font-size:1.2rem;font-weight:700;color:<?= $c ?>;"><?= (int) ($stats[$k] ?? 0) ?></div>
                    <div style="font-size:.76rem;color:var(--text-secondary);"><?= $l ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;margin-bottom:18px;">
            <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:14px;">
                <div style="font-size:1.25rem;font-weight:700;color:#00ff88;"><?= (float) ($kpi['resolution_rate'] ?? 0) ?>%</div>
                <div style="font-size:.77rem;color:var(--text-secondary);">Resolution Rate</div>
            </div>
            <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:14px;">
                <div style="font-size:1.25rem;font-weight:700;color:#ff9f43;"><?= (float) ($kpi['first_response_24h'] ?? 0) ?>%</div>
                <div style="font-size:.77rem;color:var(--text-secondary);">Reply within 24h</div>
            </div>
            <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:14px;">
                <div style="font-size:1.25rem;font-weight:700;color:#a78bfa;"><?= (int) ($kpi['active_workload'] ?? 0) ?></div>
                <div style="font-size:.77rem;color:var(--text-secondary);">Active Workload</div>
            </div>
            <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:14px;">
                <div style="font-size:1.25rem;font-weight:700;color:#ff6b6b;"><?= (int) ($kpi['unassigned_count'] ?? 0) ?></div>
                <div style="font-size:.77rem;color:var(--text-secondary);">Unassigned Tickets</div>
            </div>
        </div>

        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;overflow:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <th style="padding:10px 12px;text-align:left;font-size:.72rem;color:var(--text-secondary);">Ticket ID &amp; Subject</th>
                        <th style="padding:10px 12px;text-align:left;font-size:.72rem;color:var(--text-secondary);">Customer</th>
                        <th style="padding:10px 12px;text-align:left;font-size:.72rem;color:var(--text-secondary);">Status</th>
                        <th style="padding:10px 12px;text-align:left;font-size:.72rem;color:var(--text-secondary);">Priority</th>
                        <th style="padding:10px 12px;text-align:left;font-size:.72rem;color:var(--text-secondary);">Assigned</th>
                        <th style="padding:10px 12px;text-align:left;font-size:.72rem;color:var(--text-secondary);">Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tickets)): ?>
                        <tr><td colspan="6" style="padding:28px;text-align:center;color:var(--text-secondary);">No tickets for selected filters.</td></tr>
                    <?php else: foreach ($tickets as $ticket): ?>
                        <tr style="border-bottom:1px solid var(--border-color);">
                            <td style="padding:10px 12px;"><a href="/support/admin/ticket/<?= (int) $ticket['id'] ?>" style="color:var(--text-primary);text-decoration:none;">#<?= sprintf('%07d', (int) $ticket['id']) ?> · <?= htmlspecialchars($ticket['subject']) ?></a></td>
                            <td style="padding:10px 12px;color:var(--text-secondary);"><?= htmlspecialchars($ticket['user_name'] ?? '—') ?></td>
                            <td style="padding:10px 12px;color:var(--text-secondary);"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $ticket['status'] ?? ''))) ?></td>
                            <td style="padding:10px 12px;color:var(--text-secondary);"><?= htmlspecialchars(ucfirst($ticket['priority'] ?? '')) ?></td>
                            <td style="padding:10px 12px;color:var(--text-secondary);"><?= htmlspecialchars($ticket['agent_name'] ?? 'Unassigned') ?></td>
                            <td style="padding:10px 12px;color:var(--text-secondary);font-size:.78rem;"><?= !empty($ticket['updated_at']) ? date('M j, Y H:i', strtotime($ticket['updated_at'])) : '—' ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
