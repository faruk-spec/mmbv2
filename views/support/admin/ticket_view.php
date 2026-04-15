<?php
/**
 * Support Admin — Manage Individual Ticket (within support portal)
 */
use Core\View;
use Core\Auth;

View::extend('main');

$isClosed    = in_array($ticket['status'], ['closed', 'resolved']);
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
<?php include dirname(__DIR__) . '/_styles.php'; ?>
<style>
.sp-internal-label {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: .75rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 12px;
    background: color-mix(in srgb, var(--orange) 12%, transparent);
    color: var(--orange);
    margin-left: 6px;
    vertical-align: middle;
}
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div style="display:flex;min-height:calc(100vh - 64px);align-items:stretch;">
    <?php include dirname(__DIR__) . '/_sidebar.php'; ?>

    <div style="flex:1;padding:24px 28px;min-width:0;overflow:auto;">

        <!-- Flash messages -->
        <?php if (!empty($_SESSION['_flash']['success'])): ?>
        <div style="background:color-mix(in srgb,var(--green) 8%,transparent);border:1px solid color-mix(in srgb,var(--green) 25%,transparent);color:var(--green);padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:.88rem;display:flex;align-items:center;gap:8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <?= htmlspecialchars($_SESSION['_flash']['success']) ?><?php unset($_SESSION['_flash']['success']); ?>
        </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['_flash']['error'])): ?>
        <div style="background:color-mix(in srgb,var(--red) 8%,transparent);border:1px solid color-mix(in srgb,var(--red) 25%,transparent);color:var(--red);padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:.88rem;display:flex;align-items:center;gap:8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?= htmlspecialchars($_SESSION['_flash']['error']) ?><?php unset($_SESSION['_flash']['error']); ?>
        </div>
        <?php endif; ?>

        <!-- Breadcrumb -->
        <a href="/support/admin/tickets" style="color:var(--text-secondary);text-decoration:none;font-size:.83rem;display:inline-flex;align-items:center;gap:6px;margin-bottom:16px;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Back to All Requests
        </a>

        <div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;" class="sp-manage-grid">

            <!-- ── Left: Ticket thread ───────────────────────────────────── -->
            <div>
                <!-- Ticket header card -->
                <div class="sp-card" style="padding:22px;margin-bottom:16px;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                        <div style="min-width:0;flex:1;">
                            <div style="color:var(--text-secondary);font-size:.75rem;margin-bottom:6px;font-family:monospace;">
                                #<?= sprintf('%07d', (int)$ticket['id']) ?>
                            </div>
                            <h2 style="font-size:1.15rem;font-weight:700;color:var(--text-primary);margin:0 0 10px;line-height:1.3;">
                                <?= htmlspecialchars($ticket['subject']) ?>
                            </h2>
                            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                                <span class="sp-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                                <span class="sp-badge <?= $priorityClass ?>"><?= ucfirst($ticket['priority']) ?> Priority</span>
                                <?php if ($ticket['agent_name']): ?>
                                <span style="font-size:.75rem;color:var(--text-secondary);">Assigned to: <strong style="color:var(--text-primary);"><?= htmlspecialchars($ticket['agent_name']) ?></strong></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="font-size:.77rem;color:var(--text-secondary);text-align:right;white-space:nowrap;">
                            <div>By: <strong style="color:var(--text-primary);"><?= htmlspecialchars($ticket['user_name'] ?? '—') ?></strong></div>
                            <div style="margin-top:3px;"><?= date('M j, Y H:i', strtotime($ticket['created_at'])) ?></div>
                            <?php if ($ticket['last_reply_at']): ?>
                            <div style="margin-top:2px;color:var(--text-secondary);">Last reply <?= date('M j H:i', strtotime($ticket['last_reply_at'])) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Conversation thread -->
                <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:16px;">
                    <?php foreach ($messages as $msg):
                        $isUser     = ($msg['sender_type'] === 'user');
                        $isInternal = (bool)($msg['is_internal'] ?? false);
                    ?>
                    <div class="<?= $isInternal ? 'sp-msg-internal' : ($isUser ? 'sp-msg-user' : 'sp-msg-agent') ?>" style="border-radius:10px;padding:14px 16px;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;flex-wrap:wrap;">
                            <span style="font-weight:600;font-size:.8rem;color:<?= $isUser ? 'var(--cyan)' : ($isInternal ? 'var(--orange)' : 'var(--green)') ?>;">
                                <?= $isUser ? htmlspecialchars($ticket['user_name'] ?? 'User') : htmlspecialchars($msg['sender_name'] ?? 'Agent') ?>
                            </span>
                            <?php if ($isInternal): ?>
                            <span class="sp-internal-label">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                Internal Note
                            </span>
                            <?php endif; ?>
                            <span style="color:var(--text-secondary);font-size:.72rem;"><?= date('M j, Y H:i', strtotime($msg['created_at'])) ?></span>
                        </div>
                        <div style="color:var(--text-primary);font-size:.88rem;line-height:1.65;white-space:pre-wrap;"><?= htmlspecialchars($msg['message']) ?></div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($messages)): ?>
                    <div style="text-align:center;padding:30px;color:var(--text-secondary);font-size:.88rem;">No messages yet.</div>
                    <?php endif; ?>
                </div>

                <!-- Reply form -->
                <?php if (!$isClosed): ?>
                <div class="sp-card" style="padding:20px;">
                    <h3 class="sp-section-heading" style="margin:0 0 14px;display:flex;align-items:center;gap:8px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color:var(--cyan);" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>
                        Post Reply
                    </h3>
                    <form method="POST" action="/support/admin/ticket/<?= (int)$ticket['id'] ?>/reply">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <textarea name="message" required rows="5" maxlength="5000"
                            placeholder="Write your reply here (visible to customer)..."
                            class="sp-textarea" style="margin-bottom:10px;"></textarea>
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                            <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:.85rem;color:var(--text-secondary);">
                                <input type="checkbox" name="is_internal" value="1"
                                    style="width:15px;height:15px;accent-color:var(--orange);">
                                <span>Internal note only (hidden from customer)</span>
                            </label>
                            <button type="submit" class="sp-btn sp-btn-primary sp-btn-sm">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                Send Reply
                            </button>
                        </div>
                    </form>
                </div>
                <?php else: ?>
                <div style="background:color-mix(in srgb,var(--text-secondary) 6%,transparent);border:1px solid var(--border-color);border-radius:10px;padding:16px;text-align:center;color:var(--text-secondary);font-size:.88rem;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:6px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    This ticket is <?= $ticket['status'] ?>. Use the Reopen button to accept new replies.
                </div>
                <?php endif; ?>
            </div>

            <!-- ── Right: Management panel ───────────────────────────────── -->
            <div style="display:flex;flex-direction:column;gap:14px;">

                <!-- Change Status -->
                <div class="sp-card" style="padding:18px;">
                    <div class="sp-section-heading" style="margin-bottom:12px;display:flex;align-items:center;gap:7px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color:var(--cyan);" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        Change Status
                    </div>
                    <form method="POST" action="/support/admin/ticket/<?= (int)$ticket['id'] ?>/status">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <select name="status" class="sp-select" style="width:100%;margin-bottom:8px;">
                            <?php foreach (['open'=>'Open','in_progress'=>'In Progress','waiting_customer'=>'Waiting on Customer','resolved'=>'Resolved','closed'=>'Closed'] as $sv => $sl): ?>
                            <option value="<?= $sv ?>"<?= $ticket['status']===$sv?' selected':'' ?>><?= $sl ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div id="sp-resolution-wrap" style="display:<?= in_array($ticket['status'],['closed','resolved'])?'block':'none' ?>;margin-bottom:8px;">
                            <textarea name="resolution" rows="3" maxlength="1000"
                                placeholder="Brief resolution summary for the customer..."
                                class="sp-textarea"></textarea>
                        </div>
                        <button type="submit" class="sp-btn sp-btn-primary sp-btn-sm" style="width:100%;justify-content:center;">
                            Update Status
                        </button>
                    </form>
                </div>

                <!-- Change Priority -->
                <div class="sp-card" style="padding:18px;">
                    <div class="sp-section-heading" style="margin-bottom:12px;display:flex;align-items:center;gap:7px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color:var(--orange);" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        Change Priority
                    </div>
                    <form method="POST" action="/support/admin/ticket/<?= (int)$ticket['id'] ?>/priority">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <select name="priority" class="sp-select" style="width:100%;margin-bottom:8px;">
                            <?php foreach (['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent'] as $pv => $pl): ?>
                            <option value="<?= $pv ?>"<?= $ticket['priority']===$pv?' selected':'' ?>><?= $pl ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="sp-btn sp-btn-primary sp-btn-sm" style="width:100%;justify-content:center;">
                            Update Priority
                        </button>
                    </form>
                </div>

                <!-- Ticket Info -->
                <div class="sp-card" style="padding:18px;">
                    <div class="sp-section-heading" style="margin-bottom:12px;display:flex;align-items:center;gap:7px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color:var(--text-secondary);" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Ticket Info
                    </div>
                    <?php foreach ([
                        ['Customer',    htmlspecialchars($ticket['user_name'] ?? '—')],
                        ['Email',       htmlspecialchars($ticket['user_email'] ?? '—')],
                        ['Assigned To', htmlspecialchars($ticket['agent_name'] ?? 'Unassigned')],
                        ['Created',     date('M j, Y H:i', strtotime($ticket['created_at']))],
                        ['Updated',     $ticket['updated_at'] ? date('M j, Y H:i', strtotime($ticket['updated_at'])) : '—'],
                        ['Closed',      $ticket['closed_at'] ? date('M j, Y H:i', strtotime($ticket['closed_at'])) : '—'],
                    ] as [$label, $value]): ?>
                    <div style="display:flex;justify-content:space-between;gap:8px;padding:6px 0;border-bottom:1px solid var(--border-color);font-size:.82rem;">
                        <span style="color:var(--text-secondary);white-space:nowrap;"><?= $label ?></span>
                        <span style="color:var(--text-primary);text-align:right;word-break:break-word;"><?= $value ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Quick actions -->
                <?php if ($isClosed): ?>
                <form method="POST" action="/support/admin/ticket/<?= (int)$ticket['id'] ?>/status">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <input type="hidden" name="status" value="open">
                    <button type="submit" class="sp-btn sp-btn-outline" style="width:100%;justify-content:center;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.5"/></svg>
                        Reopen Ticket
                    </button>
                </form>
                <?php endif; ?>

            </div><!-- /management panel -->
        </div><!-- /grid -->
    </div><!-- /main content -->
</div><!-- /support flex wrapper -->

<style>
@media (max-width: 860px) {
    .sp-manage-grid { grid-template-columns: 1fr !important; }
}
</style>

<script>
(function () {
    var sel = document.querySelector('select[name="status"]');
    var wrap = document.getElementById('sp-resolution-wrap');
    if (!sel || !wrap) return;
    sel.addEventListener('change', function () {
        wrap.style.display = (this.value === 'closed' || this.value === 'resolved') ? 'block' : 'none';
    });
})();
</script>

<?php View::endSection(); ?>
