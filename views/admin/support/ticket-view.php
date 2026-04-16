<?php
/**
 * Admin Support Ticket Detail View
 */
use Core\View;

View::extend('admin');
View::section('content');

$isClosed = ($ticket['status'] === 'closed');
$statusColor = match($ticket['status']) {
    'open'             => '#3b82f6',
    'in_progress'      => '#f59e0b',
    'waiting_customer' => '#8b5cf6',
    'resolved'         => '#10b981',
    'closed'           => '#6b7280',
    default            => '#6b7280',
};
$priorityColor = match($ticket['priority']) {
    'urgent' => '#ef4444',
    'high'   => '#f97316',
    'medium' => '#06b6d4',
    'low'    => '#64748b',
    default  => '#64748b',
};

$formattedTicketId = '#' . str_pad((string) ((int) $ticket['id']), 6, '0', STR_PAD_LEFT);
$createdAt       = !empty($ticket['created_at']) ? strtotime($ticket['created_at']) : null;
$updatedAt       = !empty($ticket['updated_at']) ? strtotime($ticket['updated_at']) : null;
$firstReplyTs    = !empty($firstAgentReplyAt ?? null) ? strtotime($firstAgentReplyAt) : null;
$responseMinutes = ($createdAt && $firstReplyTs && $firstReplyTs >= $createdAt) ? (int) floor(($firstReplyTs - $createdAt) / 60) : null;

$totalMessages   = count($messages ?? []);
$customerReplies = 0;
$agentReplies    = 0;
$internalNotes   = 0;
foreach ($messages ?? [] as $msg) {
    if (!empty($msg['is_internal'])) {
        $internalNotes++;
    } elseif (($msg['sender_type'] ?? '') === 'agent') {
        $agentReplies++;
    } else {
        $customerReplies++;
    }
}

$submittedData = [];
if (!empty($ticket['submitted_data']) && is_string($ticket['submitted_data'])) {
    $parsed = json_decode($ticket['submitted_data'], true);
    if (is_array($parsed)) {
        $submittedData = $parsed;
    }
}
$lifecycleLabel = $ticket['lifecycle_name'] ?? (!empty($ticket['closed_at']) ? 'Completed' : 'Active Workflow');
?>

<style>
.adm-ticket-shell{padding:20px 24px;display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:16px}
.adm-ticket-main,.adm-ticket-side{background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:14px}
.adm-ticket-main{padding:18px}
.adm-toolbar{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
.adm-btn{border:1px solid var(--border-color,rgba(255,255,255,.14));background:var(--bg-secondary,#0b0d16);color:var(--text-primary,#e8eefc);padding:7px 12px;border-radius:8px;font-size:.8rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;cursor:pointer}
.adm-btn:hover{border-color:rgba(255,255,255,.26)}
.adm-btn.primary{background:#1f3a8a;border-color:#2563eb;color:#dbeafe}
.adm-btn.warn{background:#4c1d1d;border-color:#7f1d1d;color:#fecaca}
.adm-header{padding:14px;border:1px solid var(--border-color,rgba(255,255,255,.07));border-radius:12px;background:var(--bg-secondary,#0b0d16)}
.adm-title{margin:0;font-size:1.35rem;color:var(--text-primary,#e8eefc)}
.adm-meta{margin-top:6px;color:var(--text-secondary,#94a3b8);font-size:.78rem;display:flex;gap:10px;flex-wrap:wrap}
.adm-badges{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
.adm-pill{padding:4px 11px;border-radius:999px;font-size:.73rem;font-weight:700}
.adm-summary{margin-top:14px;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
.adm-stat{border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:10px;padding:10px;background:rgba(255,255,255,.01)}
.adm-stat .k{display:block;color:var(--text-secondary,#94a3b8);font-size:.7rem;text-transform:uppercase;letter-spacing:.06em}
.adm-stat .v{display:block;margin-top:4px;color:var(--text-primary,#e8eefc);font-size:.9rem;font-weight:600}
.adm-mini-grid{margin-top:14px;padding:12px;border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:10px;background:rgba(59,130,246,.06);display:flex;gap:16px;flex-wrap:wrap}
.adm-mini-grid .item{min-width:150px}
.adm-tabs{display:flex;gap:8px;margin-top:14px;border-bottom:1px solid var(--border-color,rgba(255,255,255,.08));padding-bottom:8px}
.adm-tab-btn{background:none;border:none;color:var(--text-secondary,#94a3b8);font-size:.83rem;padding:6px 8px;cursor:pointer;border-radius:8px}
.adm-tab-btn.active{color:#a78bfa;background:rgba(167,139,250,.13)}
.adm-tab{display:none;padding-top:14px}
.adm-tab.active{display:block}
.adm-msg{border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:11px;padding:12px 13px;background:rgba(255,255,255,.02);margin-bottom:10px}
.adm-msg.agent{border-color:rgba(249,115,22,.35);background:rgba(249,115,22,.06)}
.adm-msg.internal{border-color:rgba(167,139,250,.35);background:rgba(167,139,250,.08)}
.adm-msg-head{display:flex;align-items:center;gap:8px;color:var(--text-secondary,#94a3b8);font-size:.76rem;margin-bottom:6px}
.adm-msg-name{font-weight:700;color:var(--text-primary,#e8eefc)}
.adm-msg-body{white-space:pre-wrap;color:var(--text-primary,#e8eefc);font-size:.87rem;line-height:1.55}
.adm-panel{border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:11px;padding:14px;margin-top:12px;background:rgba(255,255,255,.01)}
.adm-panel h3{margin:0 0 10px;font-size:.88rem;color:var(--text-primary,#e8eefc)}
.adm-input,.adm-select,.adm-textarea{width:100%;box-sizing:border-box;border:1px solid var(--border-color,rgba(255,255,255,.12));background:var(--bg-secondary,#0b0d16);color:var(--text-primary,#e8eefc);border-radius:8px;padding:8px 10px;font-size:.84rem}
.adm-textarea{min-height:88px;resize:vertical}
.adm-row{margin-bottom:10px}
.adm-side{padding:16px}
.adm-prop{display:grid;grid-template-columns:100px 1fr;gap:8px;margin-bottom:10px;font-size:.82rem}
.adm-prop .k{color:var(--text-secondary,#94a3b8)}
.adm-prop .v{color:var(--text-primary,#e8eefc);font-weight:600}
.adm-activity{border-left:2px solid rgba(148,163,184,.45);padding-left:10px;margin-bottom:12px}
.adm-activity .t{font-size:.8rem;color:var(--text-primary,#e8eefc)}
.adm-activity .d{font-size:.75rem;color:var(--text-secondary,#94a3b8)}
@media (max-width:1100px){.adm-ticket-shell{grid-template-columns:1fr}.adm-summary{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>

<div class="adm-ticket-shell">
    <div class="adm-ticket-main">
        <div class="adm-toolbar">
            <a href="/admin/support/tickets" class="adm-btn"><i class="fas fa-arrow-left"></i>Back</a>
            <form method="POST" action="/admin/support/tickets/<?= (int)$ticket['id'] ?>/status" style="display:inline">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="status" value="in_progress">
                <button type="submit" class="adm-btn"><i class="fas fa-person-running"></i>Pick up</button>
            </form>
            <form method="POST" action="/admin/support/tickets/<?= (int)$ticket['id'] ?>/status" style="display:inline">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="status" value="resolved">
                <button type="submit" class="adm-btn primary"><i class="fas fa-check"></i>Mark Resolved</button>
            </form>
            <?php if (!$isClosed): ?>
            <form method="POST" action="/admin/support/tickets/<?= (int)$ticket['id'] ?>/status" style="display:inline">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="status" value="closed">
                <button type="submit" class="adm-btn warn"><i class="fas fa-lock"></i>Close</button>
            </form>
            <?php endif; ?>
            <?php if ($isClosed): ?>
            <form method="POST" action="/admin/support/tickets/<?= (int)$ticket['id'] ?>/reopen" style="display:inline">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <button type="submit" class="adm-btn"><i class="fas fa-rotate-left"></i>Reopen</button>
            </form>
            <?php endif; ?>
        </div>

        <div class="adm-header">
            <h1 class="adm-title"><?= htmlspecialchars($formattedTicketId) ?> <?= htmlspecialchars($ticket['subject']) ?></h1>
            <div class="adm-meta">
                <span>Requested by <strong><?= htmlspecialchars($ticket['user_name'] ?? 'Unknown') ?></strong></span>
                <span><?= !empty($ticket['created_at']) ? date('M j, Y h:i A', strtotime($ticket['created_at'])) : '—' ?></span>
            </div>
            <div class="adm-badges">
                <span class="adm-pill" style="background:<?= $statusColor ?>22;color:<?= $statusColor ?>;"><?= ucwords(str_replace('_',' ',$ticket['status'])) ?></span>
                <span class="adm-pill" style="background:<?= $priorityColor ?>22;color:<?= $priorityColor ?>;"><?= ucfirst($ticket['priority']) ?> priority</span>
                <?php if (!empty($ticket['assigned_to'])): ?>
                    <span class="adm-pill" style="background:rgba(59,130,246,.2);color:#93c5fd;">Assigned to <?= htmlspecialchars($ticket['agent_name'] ?? 'Agent') ?></span>
                <?php else: ?>
                    <span class="adm-pill" style="background:rgba(148,163,184,.2);color:#cbd5e1;">Not assigned</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="adm-summary">
            <div class="adm-stat"><span class="k">Conversation</span><span class="v"><?= (int)$totalMessages ?> messages</span></div>
            <div class="adm-stat"><span class="k">Customer replies</span><span class="v"><?= (int)$customerReplies ?></span></div>
            <div class="adm-stat"><span class="k">Agent replies</span><span class="v"><?= (int)$agentReplies ?></span></div>
            <div class="adm-stat"><span class="k">First response</span><span class="v"><?= $responseMinutes !== null ? $responseMinutes . ' min' : 'Pending' ?></span></div>
        </div>

        <div class="adm-mini-grid">
            <div class="item"><div class="k" style="font-size:.7rem;color:#94a3b8;">Transitions</div><div class="v" style="margin-top:4px;color:#e8eefc;">Open → In Progress → Resolved → Closed</div></div>
            <div class="item"><div class="k" style="font-size:.7rem;color:#94a3b8;">Internal Notes</div><div class="v" style="margin-top:4px;color:#e8eefc;"><?= (int)$internalNotes ?></div></div>
        </div>

        <div class="adm-tabs">
            <button type="button" class="adm-tab-btn active" data-tab="conversations">Conversations</button>
            <button type="button" class="adm-tab-btn" data-tab="details">Details</button>
            <button type="button" class="adm-tab-btn" data-tab="history">History</button>
        </div>

        <div class="adm-tab active" id="tab-conversations">
            <?php if (empty($messages)): ?>
                <div style="padding:22px;color:#94a3b8;text-align:center;">No messages yet.</div>
            <?php else: ?>
                <?php foreach ($messages as $msg):
                    $isAgent = (($msg['sender_type'] ?? '') === 'agent');
                    $isInternal = !empty($msg['is_internal']);
                ?>
                <div class="adm-msg<?= $isAgent ? ' agent' : '' ?><?= $isInternal ? ' internal' : '' ?>">
                    <div class="adm-msg-head">
                        <span class="adm-msg-name"><?= htmlspecialchars($msg['sender_name'] ?? ($isAgent ? 'Agent' : 'Customer')) ?></span>
                        <span><?= htmlspecialchars(ucfirst($msg['sender_type'] ?? 'user')) ?></span>
                        <?php if ($isInternal): ?><span style="padding:2px 6px;border-radius:999px;background:rgba(167,139,250,.2);color:#c4b5fd;">Internal</span><?php endif; ?>
                        <span style="margin-left:auto;"><?= !empty($msg['created_at']) ? date('M j, Y h:i A', strtotime($msg['created_at'])) : '' ?></span>
                    </div>
                    <div class="adm-msg-body"><?= htmlspecialchars($msg['message'] ?? '') ?></div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!$isClosed): ?>
            <div class="adm-panel">
                <h3><i class="fas fa-reply"></i> Reply to Ticket</h3>
                <form method="POST" action="/admin/support/tickets/<?= (int)$ticket['id'] ?>/reply">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <div class="adm-row">
                        <textarea name="message" class="adm-textarea" required placeholder="Type your response..."></textarea>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
                        <label style="font-size:.8rem;color:#94a3b8;display:flex;align-items:center;gap:8px;">
                            <input type="checkbox" name="is_internal" value="1" style="accent-color:#8b5cf6;">
                            Internal note only
                        </label>
                        <button type="submit" class="adm-btn primary"><i class="fas fa-paper-plane"></i>Send Reply</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <div class="adm-tab" id="tab-details">
            <div class="adm-panel" style="margin-top:0;">
                <h3>Ticket Description</h3>
                <div style="color:#e8eefc;font-size:.86rem;line-height:1.6;white-space:pre-wrap;"><?= htmlspecialchars($ticket['description'] ?? 'No description provided.') ?></div>
            </div>
            <?php if (!empty($submittedData)): ?>
            <div class="adm-panel">
                <h3>Submitted Form Data</h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;">
                    <?php foreach ($submittedData as $key => $value): ?>
                    <div style="border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:8px 10px;background:rgba(255,255,255,.01);">
                        <div style="font-size:.72rem;color:#94a3b8;margin-bottom:4px;"><?= htmlspecialchars((string)$key) ?></div>
                        <div style="font-size:.82rem;color:#e8eefc;word-break:break-word;"><?= htmlspecialchars(is_scalar($value) ? (string)$value : json_encode($value)) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="adm-tab" id="tab-history">
            <?php if (empty($activities ?? [])): ?>
                <div style="padding:18px;color:#94a3b8;">No activity log yet.</div>
            <?php else: ?>
                <?php foreach ($activities as $activity): ?>
                <div class="adm-activity">
                    <div class="t"><?= htmlspecialchars($activity['description'] ?? 'Activity updated.') ?></div>
                    <div class="d">
                        <?= !empty($activity['actor_name']) ? htmlspecialchars($activity['actor_name']) . ' • ' : '' ?>
                        <?= !empty($activity['created_at']) ? date('M j, Y h:i A', strtotime($activity['created_at'])) : '' ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <aside class="adm-ticket-side adm-side">
        <h3 style="margin:0 0 14px;font-size:1rem;color:#e8eefc;">Properties</h3>
        <div class="adm-prop"><div class="k">Request ID</div><div class="v"><?= htmlspecialchars($formattedTicketId) ?></div></div>
        <div class="adm-prop"><div class="k">Status</div><div class="v" style="color:<?= $statusColor ?>;"><?= ucwords(str_replace('_', ' ', $ticket['status'])) ?></div></div>
        <div class="adm-prop"><div class="k">Lifecycle</div><div class="v"><?= htmlspecialchars($lifecycleLabel) ?></div></div>
        <div class="adm-prop"><div class="k">Technician</div><div class="v"><?= htmlspecialchars($ticket['agent_name'] ?? 'Not Assigned') ?></div></div>
        <div class="adm-prop"><div class="k">Group & Site</div><div class="v"><?= htmlspecialchars(($ticket['group_name'] ?? 'Support') . ' / ' . ($ticket['category_name'] ?? 'General')) ?></div></div>
        <div class="adm-prop"><div class="k">Conversations</div><div class="v"><?= (int)$totalMessages ?></div></div>
        <div class="adm-prop"><div class="k">Internal Notes</div><div class="v"><?= (int)$internalNotes ?></div></div>
        <div class="adm-prop"><div class="k">Created</div><div class="v"><?= $createdAt ? date('M j, Y h:i A', $createdAt) : '—' ?></div></div>
        <div class="adm-prop"><div class="k">Updated</div><div class="v"><?= $updatedAt ? date('M j, Y h:i A', $updatedAt) : '—' ?></div></div>
    </aside>
</div>

<script>
document.querySelectorAll('.adm-tab-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        const key = btn.dataset.tab;
        document.querySelectorAll('.adm-tab-btn').forEach((el) => el.classList.remove('active'));
        document.querySelectorAll('.adm-tab').forEach((el) => el.classList.remove('active'));
        btn.classList.add('active');
        const tab = document.getElementById('tab-' + key);
        if (tab) tab.classList.add('active');
    });
});
</script>

<?php View::endSection(); ?>
