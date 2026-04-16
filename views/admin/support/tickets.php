<?php
/**
 * Admin Support Tickets List
 */
use Core\View;

View::extend('admin');
View::section('content');
?>

<div style="padding:28px;">
    <!-- Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-primary,#e8eefc);margin:0 0 4px;">
                <i class="fas fa-ticket" style="color:#00f0ff;margin-right:10px;"></i>Support Tickets
            </h1>
            <p style="color:var(--text-secondary,#8892a6);margin:0;font-size:.85rem;">
                <?= (int)($stats['open']??0) ?> open &bull; <?= (int)($stats['total']??0) ?> total
            </p>
        </div>
    </div>

    <!-- Filter bar -->
    <form method="GET" action="/admin/support/tickets" style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;padding:16px 20px;margin-bottom:20px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
        <select name="status" style="padding:8px 12px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:6px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.85rem;">
            <option value="">All Statuses</option>
            <?php foreach (['open','in_progress','waiting_customer','resolved','closed'] as $s): ?>
            <option value="<?= $s ?>" <?= ($filters['status']==$s)?'selected':'' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="priority" style="padding:8px 12px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:6px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.85rem;">
            <option value="">All Priorities</option>
            <?php foreach (['low','medium','high','urgent'] as $p): ?>
            <option value="<?= $p ?>" <?= ($filters['priority']==$p)?'selected':'' ?>><?= ucfirst($p) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" style="padding:8px 18px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border:none;border-radius:6px;color:white;font-weight:600;font-size:.85rem;cursor:pointer;">
            <i class="fas fa-filter" style="margin-right:6px;"></i>Filter
        </button>
        <?php if (!empty(array_filter($filters))): ?>
        <a href="/admin/support/tickets" style="padding:8px 14px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:6px;color:var(--text-secondary,#8892a6);text-decoration:none;font-size:.85rem;">
            <i class="fas fa-times" style="margin-right:4px;"></i>Clear
        </a>
        <?php endif; ?>
    </form>

    <!-- Tickets table -->
    <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;overflow:hidden;">
        <?php if (empty($tickets)): ?>
        <div style="padding:60px;text-align:center;color:var(--text-secondary,#8892a6);">
            <i class="fas fa-ticket" style="font-size:2rem;opacity:.3;display:block;margin-bottom:12px;"></i>
            No tickets found.
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border-color,rgba(255,255,255,.08));">
                        <?php foreach (['#','User','Subject','Status','Priority','Created','Actions'] as $h): ?>
                        <th style="padding:12px 14px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;"><?= $h ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket):
                        $sc = ['open'=>'#00f0ff','in_progress'=>'#ff9f43','waiting_customer'=>'#a78bfa','resolved'=>'#00ff88','closed'=>'#8892a6'];
                        $pc = ['urgent'=>'#ff6b6b','high'=>'#ff9f43','medium'=>'#00f0ff','low'=>'#8892a6'];
                        $sColor = $sc[$ticket['status']] ?? '#8892a6';
                        $pColor = $pc[$ticket['priority']] ?? '#8892a6';
                    ?>
                    <tr style="border-bottom:1px solid var(--border-color,rgba(255,255,255,.04));transition:background .15s;" onmouseover="this.style.background='rgba(255,255,255,.02)'" onmouseout="this.style.background=''">
                        <td style="padding:12px 14px;color:var(--text-secondary,#8892a6);font-size:.82rem;">#<?= (int)$ticket['id'] ?></td>
                        <td style="padding:12px 14px;color:var(--text-primary,#e8eefc);font-size:.85rem;"><?= htmlspecialchars($ticket['user_name'] ?? '—') ?></td>
                        <td style="padding:12px 14px;">
                            <a href="/admin/support/tickets/<?= (int)$ticket['id'] ?>" style="color:var(--text-primary,#e8eefc);text-decoration:none;font-size:.88rem;font-weight:500;">
                                <?= htmlspecialchars($ticket['subject']) ?>
                            </a>
                        </td>
                        <td style="padding:12px 14px;">
                            <span style="padding:3px 10px;border-radius:20px;font-size:.73rem;font-weight:600;background:<?= $sColor ?>1a;color:<?= $sColor ?>">
                                <?= ucwords(str_replace('_',' ',$ticket['status'])) ?>
                            </span>
                        </td>
                        <td style="padding:12px 14px;">
                            <span style="padding:3px 10px;border-radius:20px;font-size:.73rem;font-weight:600;background:<?= $pColor ?>1a;color:<?= $pColor ?>">
                                <?= ucfirst($ticket['priority']) ?>
                            </span>
                        </td>
                        <td style="padding:12px 14px;color:var(--text-secondary,#8892a6);font-size:.8rem;"><?= date('M j, Y', strtotime($ticket['created_at'])) ?></td>
                        <td style="padding:12px 14px;">
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <a href="/admin/support/tickets/<?= (int)$ticket['id'] ?>" style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:rgba(0,240,255,.1);color:#00f0ff;border-radius:6px;text-decoration:none;font-size:.78rem;font-weight:500;">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="/admin/support/tickets/<?= (int)$ticket['id'] ?>" style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:rgba(167,139,250,.1);color:#a78bfa;border-radius:6px;text-decoration:none;font-size:.78rem;font-weight:500;">
                                    <i class="fas fa-pen-to-square"></i> Manage
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php View::endSection(); ?>
