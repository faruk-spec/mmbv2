<?php
/**
 * View Support Ticket (user view)
 */
use Core\View;

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

<div style="max-width:1200px;margin:0 auto;padding:28px 20px;">
    <div style="display:flex;gap:24px;align-items:flex-start;">

        <!-- Sidebar -->
        <?php include __DIR__ . '/_sidebar.php'; ?>

        <!-- Main content -->
        <div style="flex:1;min-width:0;">

            <!-- Flash messages -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
            <div style="background:rgba(0,255,136,.08);border:1px solid rgba(0,255,136,.2);color:#00ff88;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:.88rem;">
                <?= htmlspecialchars($_SESSION['flash_success']) ?><?php unset($_SESSION['flash_success']); ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['flash_error'])): ?>
            <div style="background:rgba(255,107,107,.08);border:1px solid rgba(255,107,107,.2);color:#ff6b6b;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:.88rem;">
                <?= htmlspecialchars($_SESSION['flash_error']) ?><?php unset($_SESSION['flash_error']); ?>
            </div>
            <?php endif; ?>

            <!-- Back link -->
            <a href="/support" style="color:var(--text-secondary,#8892a6);text-decoration:none;font-size:.83rem;display:inline-flex;align-items:center;gap:6px;margin-bottom:16px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                Back to My Tickets
            </a>

            <!-- Ticket header -->
            <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:14px;padding:22px;margin-bottom:18px;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <div>
                        <div style="color:var(--text-secondary,#8892a6);font-size:.77rem;margin-bottom:5px;">Ticket #<?= (int)$ticket['id'] ?></div>
                        <h1 style="font-size:1.25rem;font-weight:700;color:var(--text-primary,#e8eefc);margin:0 0 10px;">
                            <?= htmlspecialchars($ticket['subject']) ?>
                        </h1>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <span style="padding:3px 12px;border-radius:20px;font-size:.73rem;font-weight:600;background:<?= $statusColor ?>22;color:<?= $statusColor ?>"><?= $statusLabel ?></span>
                            <span style="padding:3px 12px;border-radius:20px;font-size:.73rem;font-weight:600;background:<?= $priorityColor ?>22;color:<?= $priorityColor ?>"><?= ucfirst($ticket['priority']) ?> Priority</span>
                        </div>
                    </div>
                    <div style="text-align:right;font-size:.78rem;color:var(--text-secondary,#8892a6);">
                        <div>Created <?= date('M j, Y', strtotime($ticket['created_at'])) ?></div>
                        <?php if ($ticket['last_reply_at']): ?>
                        <div>Last reply <?= date('M j, Y H:i', strtotime($ticket['last_reply_at'])) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Messages thread -->
            <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:18px;">
                <?php foreach ($messages as $msg):
                    $isUserMsg = ($msg['sender_type'] === 'user');
                ?>
                <div style="display:flex;<?= $isUserMsg ? 'justify-content:flex-end;' : 'justify-content:flex-start;' ?>">
                    <div style="max-width:82%;<?= $isUserMsg
                        ? 'background:linear-gradient(135deg,rgba(0,240,255,.1),rgba(255,46,196,.07));border:1px solid rgba(0,240,255,.15);'
                        : 'background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));' ?>border-radius:12px;padding:14px 16px;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                            <span style="font-weight:600;font-size:.8rem;color:<?= $isUserMsg ? '#00f0ff' : '#ff9f43' ?>;">
                                <?= $isUserMsg ? 'You' : htmlspecialchars($msg['sender_name'] ?? 'Support Agent') ?>
                            </span>
                            <span style="color:var(--text-secondary,#8892a6);font-size:.72rem;">
                                <?= date('M j, Y H:i', strtotime($msg['created_at'])) ?>
                            </span>
                        </div>
                        <div style="color:var(--text-primary,#e8eefc);font-size:.88rem;line-height:1.65;white-space:pre-wrap;"><?= htmlspecialchars($msg['message']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($messages)): ?>
                <div style="text-align:center;padding:40px 20px;color:var(--text-secondary,#8892a6);font-size:.88rem;">No messages yet.</div>
                <?php endif; ?>
            </div>

            <!-- Reply form / closed notice -->
            <?php if ($isClosed): ?>
            <div style="background:rgba(136,146,166,.08);border:1px solid rgba(136,146,166,.2);border-radius:12px;padding:18px 20px;text-align:center;color:var(--text-secondary,#8892a6);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:7px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                This ticket is closed and no longer accepts replies.
                <div style="margin-top:10px;">
                    <a href="/support/create" style="color:#00f0ff;text-decoration:none;font-size:.87rem;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:4px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Open a new ticket
                    </a>
                </div>
            </div>
            <?php else: ?>
            <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:14px;padding:22px;">
                <h3 style="margin:0 0 14px;font-size:.95rem;font-weight:600;color:var(--text-primary,#e8eefc);">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#00f0ff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:7px;"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>
                    Add a Reply
                </h3>
                <form method="POST" action="/support/view/<?= (int)$ticket['id'] ?>/reply">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <textarea name="message" required rows="5" maxlength="5000"
                        placeholder="Write your reply here..."
                        style="width:100%;padding:12px 14px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:8px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.9rem;outline:none;resize:vertical;box-sizing:border-box;margin-bottom:12px;"></textarea>
                    <button type="submit"
                        style="padding:10px 22px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border:none;border-radius:8px;color:white;font-weight:600;font-size:.9rem;cursor:pointer;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:6px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Send Reply
                    </button>
                </form>
            </div>
            <?php endif; ?>

        </div><!-- /main content -->
    </div>
</div>

<?php View::endSection(); ?>

