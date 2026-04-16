<?php
/**
 * Admin Support Ticket Detail View
 */
use Core\View;

View::extend('admin');
View::section('content');

$isClosed    = ($ticket['status'] === 'closed');
$statusColor = match($ticket['status']) {
    'in_progress'      => '#ff9f43',
    'waiting_customer' => '#a78bfa',
    'resolved'         => '#00ff88',
    'closed'           => '#8892a6',
    default            => '#8892a6',
};
$priorityColor = match($ticket['priority']) {
    'urgent' => '#ff6b6b',
    'high'   => '#ff9f43',
    'medium' => '#00f0ff',
    'low'    => '#8892a6',
    default  => '#8892a6',
};
?>

<div style="padding:28px;max-width:900px;">
    <a href="/admin/support/tickets" style="color:var(--text-secondary,#8892a6);text-decoration:none;font-size:.85rem;display:inline-flex;align-items:center;gap:6px;margin-bottom:18px;">
        <i class="fas fa-arrow-left"></i> Back to Tickets
    </a>

    <!-- Ticket header -->
    <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:14px;padding:22px;margin-bottom:18px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
            <div>
                <div style="color:var(--text-secondary,#8892a6);font-size:.78rem;margin-bottom:6px;">Ticket #<?= (int)$ticket['id'] ?></div>
                <h1 style="font-size:1.2rem;font-weight:700;color:var(--text-primary,#e8eefc);margin:0 0 10px;"><?= htmlspecialchars($ticket['subject']) ?></h1>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <span style="padding:3px 12px;border-radius:20px;font-size:.73rem;font-weight:600;background:<?= $statusColor ?>1a;color:<?= $statusColor ?>">
                        <?= ucwords(str_replace('_',' ',$ticket['status'])) ?>
                    </span>
                    <span style="padding:3px 12px;border-radius:20px;font-size:.73rem;font-weight:600;background:<?= $priorityColor ?>1a;color:<?= $priorityColor ?>">
                        <?= ucfirst($ticket['priority']) ?> Priority
                    </span>
                </div>
            </div>
            <div style="font-size:.8rem;color:var(--text-secondary,#8892a6);text-align:right;">
                <div><strong style="color:var(--text-primary,#e8eefc);"><?= htmlspecialchars($ticket['user_name'] ?? '—') ?></strong></div>
                <div><?= htmlspecialchars($ticket['user_email'] ?? '') ?></div>
                <div style="margin-top:4px;">Created <?= date('M j, Y', strtotime($ticket['created_at'])) ?></div>
                <?php if ($ticket['assigned_to']): ?>
                <div style="margin-top:4px;color:#00f0ff;">Agent: <?= htmlspecialchars($ticket['agent_name'] ?? '') ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 280px;gap:18px;align-items:start;">
        <!-- Messages column -->
        <div>
            <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:18px;">
                <?php foreach ($messages as $msg):
                    $isAgent    = ($msg['sender_type'] === 'agent');
                    $isInternal = (bool)($msg['is_internal']);
                ?>
                <div style="background:<?= $isInternal ? 'rgba(167,139,250,.06)' : 'var(--bg-card,#0f0f18)' ?>;border:1px solid <?= $isInternal ? 'rgba(167,139,250,.2)' : 'var(--border-color,rgba(255,255,255,.08))' ?>;border-radius:12px;padding:14px 16px;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                        <span style="font-weight:600;font-size:.82rem;color:<?= $isAgent ? '#ff9f43' : '#00f0ff' ?>;">
                            <?= htmlspecialchars($msg['sender_name'] ?? ($isAgent ? 'Agent' : 'Customer')) ?>
                            <span style="color:var(--text-secondary,#8892a6);font-weight:400;">(<?= $msg['sender_type'] ?>)</span>
                        </span>
                        <?php if ($isInternal): ?>
                        <span style="padding:2px 8px;border-radius:10px;font-size:.68rem;font-weight:600;background:rgba(167,139,250,.15);color:#a78bfa;">Internal Note</span>
                        <?php endif; ?>
                        <span style="color:var(--text-secondary,#8892a6);font-size:.75rem;margin-left:auto;"><?= date('M j, Y H:i', strtotime($msg['created_at'])) ?></span>
                    </div>
                    <div style="color:var(--text-primary,#e8eefc);font-size:.88rem;line-height:1.6;white-space:pre-wrap;"><?= htmlspecialchars($msg['message']) ?></div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($messages)): ?>
                <div style="text-align:center;padding:40px;color:var(--text-secondary,#8892a6);font-size:.9rem;">No messages yet.</div>
                <?php endif; ?>
            </div>

            <!-- Reply form -->
            <?php if (!$isClosed): ?>
            <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;padding:20px;">
                <h3 style="margin:0 0 14px;font-size:.95rem;font-weight:600;color:var(--text-primary,#e8eefc);">
                    <i class="fas fa-reply" style="color:#ff9f43;margin-right:7px;"></i>Agent Reply
                </h3>
                <form method="POST" action="/admin/support/tickets/<?= (int)$ticket['id'] ?>/reply">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <textarea name="message" required rows="4" placeholder="Type your reply..."
                        style="width:100%;padding:10px 12px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:8px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.88rem;outline:none;resize:vertical;box-sizing:border-box;margin-bottom:12px;"></textarea>
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                        <label style="display:flex;align-items:center;gap:8px;color:var(--text-secondary,#8892a6);font-size:.83rem;cursor:pointer;">
                            <input type="checkbox" name="is_internal" value="1" style="accent-color:#a78bfa;">
                            Mark as internal note (not visible to customer)
                        </label>
                        <button type="submit" style="padding:9px 20px;background:linear-gradient(135deg,#ff9f43,#ff6b6b);border:none;border-radius:7px;color:white;font-weight:600;font-size:.85rem;cursor:pointer;">
                            <i class="fas fa-paper-plane" style="margin-right:6px;"></i>Send Reply
                        </button>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <div style="background:rgba(136,146,166,.08);border:1px solid rgba(136,146,166,.2);border-radius:10px;padding:16px;text-align:center;color:var(--text-secondary,#8892a6);font-size:.88rem;">
                <i class="fas fa-lock" style="margin-right:6px;"></i>This ticket is closed.
                <div style="margin-top:12px;">
                    <form method="POST" action="/admin/support/tickets/<?= (int)$ticket['id'] ?>/reopen" style="display:inline;">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <button type="submit" style="padding:8px 20px;background:rgba(0,255,136,.15);border:1px solid rgba(0,255,136,.3);border-radius:7px;color:#00ff88;font-weight:600;font-size:.85rem;cursor:pointer;">
                            <i class="fas fa-redo" style="margin-right:6px;"></i>Force Reopen Ticket
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Status sidebar -->
        <div>
            <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;padding:18px;">
                <h3 style="margin:0 0 14px;font-size:.9rem;font-weight:600;color:var(--text-primary,#e8eefc);">Update Status</h3>
                <form method="POST" action="/admin/support/tickets/<?= (int)$ticket['id'] ?>/status">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <select name="status" style="width:100%;padding:9px 12px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:7px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.85rem;margin-bottom:10px;">
                        <?php foreach (['open','in_progress','waiting_customer','resolved','closed'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($ticket['status']===$s)?'selected':'' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <textarea name="resolution" rows="2" placeholder="Resolution note (for closed status)"
                        style="width:100%;padding:9px 12px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:7px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.83rem;outline:none;resize:vertical;box-sizing:border-box;margin-bottom:10px;"></textarea>
                    <button type="submit" style="width:100%;padding:9px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border:none;border-radius:7px;color:white;font-weight:600;font-size:.85rem;cursor:pointer;">
                        Save Status
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php View::endSection(); ?>
