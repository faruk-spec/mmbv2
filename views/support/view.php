<?php
/**
 * View Support Ticket (user view)
 */
use Core\View;
use Core\Auth;

View::extend('main');
View::section('content');

$isClosed    = ($ticket['status'] === 'closed');
$statusColor = match($ticket['status']) {
    'open'             => '#00f0ff',
    'in_progress'      => '#ff9f43',
    'waiting_customer' => '#a78bfa',
    'resolved'         => '#00ff88',
    'closed'           => '#8892a6',
    default            => '#8892a6',
};
$statusLabel = match($ticket['status']) {
    'open'             => 'Open',
    'in_progress'      => 'In Progress',
    'waiting_customer' => 'Waiting on Customer',
    'resolved'         => 'Resolved',
    'closed'           => 'Closed',
    default            => ucfirst($ticket['status']),
};
$priorityColor = match($ticket['priority']) {
    'urgent' => '#ff6b6b',
    'high'   => '#ff9f43',
    'medium' => '#00f0ff',
    'low'    => '#8892a6',
    default  => '#8892a6',
};
?>

<div class="page-container" style="max-width:820px;margin:0 auto;padding:32px 20px;">
    <!-- Back link -->
    <a href="/support" style="color:var(--text-secondary,#8892a6);text-decoration:none;font-size:.85rem;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">
        <i class="fas fa-arrow-left"></i> Back to My Tickets
    </a>

    <!-- Ticket header -->
    <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:16px;padding:24px;margin-bottom:20px;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <div>
                <div style="color:var(--text-secondary,#8892a6);font-size:.8rem;margin-bottom:6px;">Ticket #<?= (int)$ticket['id'] ?></div>
                <h1 style="font-size:1.3rem;font-weight:700;color:var(--text-primary,#e8eefc);margin:0 0 10px;">
                    <?= htmlspecialchars($ticket['subject']) ?>
                </h1>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <span style="padding:3px 12px;border-radius:20px;font-size:.75rem;font-weight:600;background:<?= $statusColor ?>22;color:<?= $statusColor ?>">
                        <?= $statusLabel ?>
                    </span>
                    <span style="padding:3px 12px;border-radius:20px;font-size:.75rem;font-weight:600;background:<?= $priorityColor ?>22;color:<?= $priorityColor ?>">
                        <?= ucfirst($ticket['priority']) ?> Priority
                    </span>
                </div>
            </div>
            <div style="text-align:right;font-size:.8rem;color:var(--text-secondary,#8892a6);">
                <div>Created <?= htmlspecialchars(date('M j, Y', strtotime($ticket['created_at']))) ?></div>
                <?php if ($ticket['last_reply_at']): ?>
                <div>Last reply <?= htmlspecialchars(date('M j, Y H:i', strtotime($ticket['last_reply_at']))) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Messages thread -->
    <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:20px;">
        <?php foreach ($messages as $msg):
            $isUserMsg = ($msg['sender_type'] === 'user');
        ?>
        <div style="display:flex;<?= $isUserMsg ? 'justify-content:flex-end;' : 'justify-content:flex-start;' ?>">
            <div style="max-width:80%;<?= $isUserMsg
                ? 'background:linear-gradient(135deg,rgba(0,240,255,.12),rgba(255,46,196,.08));border:1px solid rgba(0,240,255,.15);'
                : 'background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));' ?>border-radius:14px;padding:14px 16px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                    <span style="font-weight:600;font-size:.82rem;color:<?= $isUserMsg ? '#00f0ff' : '#ff9f43' ?>;">
                        <?= $isUserMsg ? 'You' : htmlspecialchars($msg['sender_name'] ?? 'Support Agent') ?>
                    </span>
                    <span style="color:var(--text-secondary,#8892a6);font-size:.75rem;">
                        <?= htmlspecialchars(date('M j, Y H:i', strtotime($msg['created_at']))) ?>
                    </span>
                </div>
                <div style="color:var(--text-primary,#e8eefc);font-size:.9rem;line-height:1.6;white-space:pre-wrap;"><?= htmlspecialchars($msg['message']) ?></div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($messages)): ?>
        <div style="text-align:center;padding:40px 20px;color:var(--text-secondary,#8892a6);font-size:.9rem;">
            No messages yet.
        </div>
        <?php endif; ?>
    </div>

    <!-- Reply form / closed notice -->
    <?php if ($isClosed): ?>
    <div style="background:rgba(136,146,166,.08);border:1px solid rgba(136,146,166,.2);border-radius:12px;padding:18px 20px;text-align:center;color:var(--text-secondary,#8892a6);">
        <i class="fas fa-lock" style="margin-right:8px;"></i>
        This ticket is closed and no longer accepts replies.
        <div style="margin-top:10px;">
            <a href="/support/create" style="color:#00f0ff;text-decoration:none;font-size:.88rem;">
                <i class="fas fa-plus" style="margin-right:4px;"></i>Open a new ticket
            </a>
        </div>
    </div>
    <?php else: ?>
    <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:16px;padding:24px;">
        <h3 style="margin:0 0 16px;font-size:1rem;font-weight:600;color:var(--text-primary,#e8eefc);">
            <i class="fas fa-reply" style="color:#00f0ff;margin-right:8px;"></i>Add a Reply
        </h3>
        <form method="POST" action="/support/view/<?= (int)$ticket['id'] ?>/reply">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <textarea name="message" required rows="5" maxlength="5000"
                placeholder="Write your reply here..."
                style="width:100%;padding:12px 14px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:8px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.9rem;outline:none;resize:vertical;box-sizing:border-box;margin-bottom:14px;"></textarea>
            <button type="submit"
                style="padding:10px 24px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border:none;border-radius:8px;color:white;font-weight:600;font-size:.9rem;cursor:pointer;">
                <i class="fas fa-paper-plane" style="margin-right:6px;"></i>Send Reply
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php View::endSection(); ?>
