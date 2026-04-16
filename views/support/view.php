<?php
/**
 * View Support Ticket (user view)
 */
use Core\View;

View::extend('main');

$isClosed    = ($ticket['status'] === 'closed');
$statusClass = match($ticket['status']) {
    'open'             => 'sp-badge-open',
    'in_progress'      => 'sp-badge-prog',
    'waiting_customer' => 'sp-badge-wait',
    'resolved'         => 'sp-badge-done',
    'closed'           => 'sp-badge-closed',
    default            => 'sp-badge-closed',
};
$statusLabel = match($ticket['status']) {
    'open'             => 'Open',
    'in_progress'      => 'In Progress',
    'waiting_customer' => 'Waiting on Customer',
    'resolved'         => 'Resolved',
    'closed'           => 'Closed',
    default            => ucfirst($ticket['status']),
};
$priorityClass = match($ticket['priority']) {
    'urgent' => 'sp-badge-urgent',
    'high'   => 'sp-badge-high',
    'medium' => 'sp-badge-medium',
    'low'    => 'sp-badge-low',
    default  => 'sp-badge-closed',
};
?>

<?php View::section('styles'); ?>
<?php include __DIR__ . '/_styles.php'; ?>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div class="sp-layout">

    <!-- Sidebar -->
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <!-- Main content -->
    <div class="sp-main">

        <!-- Mobile menu button -->
        <button class="sp-menu-btn" onclick="spOpenMenu()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            Menu
        </button>

        <!-- Flash messages -->
        <?php if (!empty($_SESSION['_flash']['success'])): ?>
        <div style="background:color-mix(in srgb,var(--green) 8%,transparent);border:1px solid color-mix(in srgb,var(--green) 25%,transparent);color:var(--green);padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:.88rem;display:flex;align-items:center;gap:8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <?= htmlspecialchars($_SESSION['_flash']['success']) ?>
            <?php unset($_SESSION['_flash']['success']); ?>
        </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['_flash']['error'])): ?>
        <div style="background:color-mix(in srgb,var(--red) 8%,transparent);border:1px solid color-mix(in srgb,var(--red) 25%,transparent);color:var(--red);padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:.88rem;display:flex;align-items:center;gap:8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?= htmlspecialchars($_SESSION['_flash']['error']) ?>
            <?php unset($_SESSION['_flash']['error']); ?>
        </div>
        <?php endif; ?>

        <!-- Back link -->
        <a href="/support" style="color:var(--text-secondary);text-decoration:none;font-size:.83rem;display:inline-flex;align-items:center;gap:6px;margin-bottom:16px;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Back to My Tickets
        </a>

        <!-- Ticket header -->
        <div class="sp-card" style="padding:22px;margin-bottom:18px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                <div>
                    <div style="color:var(--text-secondary);font-size:.77rem;margin-bottom:5px;">
                        Ticket #<?= sprintf('%07d', (int)$ticket['id']) ?>
                    </div>
                    <h1 style="font-size:1.2rem;font-weight:700;color:var(--text-primary);margin:0 0 10px;">
                        <?= htmlspecialchars($ticket['subject']) ?>
                    </h1>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <span class="sp-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                        <span class="sp-badge <?= $priorityClass ?>"><?= ucfirst($ticket['priority']) ?> Priority</span>
                    </div>
                </div>
                <div style="text-align:right;font-size:.78rem;color:var(--text-secondary);">
                    <div>Created <?= date('M j, Y', strtotime($ticket['created_at'])) ?></div>
                    <?php if ($ticket['last_reply_at']): ?>
                    <div style="margin-top:3px;">Last reply <?= date('M j, Y H:i', strtotime($ticket['last_reply_at'])) ?></div>
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
                <div class="<?= $isUserMsg ? 'sp-msg-user' : 'sp-msg-agent' ?>" style="max-width:82%;border-radius:12px;padding:14px 16px;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                        <span style="font-weight:600;font-size:.8rem;color:<?= $isUserMsg ? 'var(--cyan)' : 'var(--orange)' ?>;">
                            <?= $isUserMsg ? 'You' : htmlspecialchars($msg['sender_name'] ?? 'Support Agent') ?>
                        </span>
                        <span style="color:var(--text-secondary);font-size:.72rem;">
                            <?= date('M j, Y H:i', strtotime($msg['created_at'])) ?>
                        </span>
                    </div>
                    <div style="color:var(--text-primary);font-size:.88rem;line-height:1.65;white-space:pre-wrap;"><?= htmlspecialchars($msg['message']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($messages)): ?>
            <div style="text-align:center;padding:40px 20px;color:var(--text-secondary);font-size:.88rem;">No messages yet.</div>
            <?php endif; ?>
        </div>

        <!-- Reply form / closed notice -->
        <?php if ($isClosed): ?>
        <div style="background:color-mix(in srgb,var(--text-secondary) 6%,transparent);border:1px solid var(--border-color);border-radius:12px;padding:20px;text-align:center;color:var(--text-secondary);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:7px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            This ticket is closed and no longer accepts replies.
            <div style="margin-top:12px;">
                <a href="/support/new" style="display:inline-flex;align-items:center;gap:5px;color:var(--cyan);text-decoration:none;font-size:.87rem;font-weight:500;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Open a new ticket
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="sp-card" style="padding:22px;">
            <h3 class="sp-section-heading" style="margin:0 0 14px;display:flex;align-items:center;gap:8px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color:var(--cyan);" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>
                Add a Reply
            </h3>
            <form method="POST" action="/support/view/<?= (int)$ticket['id'] ?>/reply">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <textarea name="message" required rows="5" maxlength="5000"
                    placeholder="Write your reply here..."
                    class="sp-textarea" style="margin-bottom:12px;"></textarea>
                <button type="submit" class="sp-btn sp-btn-primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Send Reply
                </button>
            </form>
        </div>
        <?php endif; ?>

    </div><!-- /main content -->
</div><!-- /sp-layout -->

<?php View::endSection(); ?>
