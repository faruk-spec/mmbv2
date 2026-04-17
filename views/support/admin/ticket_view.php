<?php
/**
 * Support Admin — Individual Ticket (portal agent view, Freshservice-inspired)
 */
use Core\View;
use Core\Auth;

View::extend('main');

$isClosed    = ($ticket['status'] === 'closed');
$ticketId    = (int) $ticket['id'];
$fmtId       = '#' . sprintf('%07d', $ticketId);
$createdAt   = !empty($ticket['created_at']) ? strtotime($ticket['created_at']) : null;
$updatedAt   = !empty($ticket['updated_at']) ? strtotime($ticket['updated_at']) : null;

$statusColor = match($ticket['status']) {
    'open'             => '#2563eb',
    'in_progress'      => '#d97706',
    'waiting_customer' => '#7c3aed',
    'resolved'         => '#16a34a',
    'closed'           => '#64748b',
    default            => '#64748b',
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
    'urgent' => '#dc2626',
    'high'   => '#ea580c',
    'medium' => '#2563eb',
    'low'    => '#64748b',
    default  => '#64748b',
};

$requesterName     = $ticket['user_name'] ?? 'User';
$requesterEmail    = $ticket['user_email'] ?? '';
$requesterInitials = strtoupper(substr($requesterName, 0, 1));
$csrfToken         = $csrf_token ?? '';

$submittedData = [];
if (!empty($ticket['submitted_data']) && is_string($ticket['submitted_data'])) {
    $parsed = json_decode($ticket['submitted_data'], true);
    if (is_array($parsed)) $submittedData = $parsed;
}
?>

<?php View::section('styles'); ?>
<?php include dirname(__DIR__) . '/_styles.php'; ?>
<style>
/* ============================================================
   TICKET VIEW — Portal Agent View (Freshservice-inspired)
   ============================================================ */
.ptv-page { display:flex; flex-direction:column; min-height:0; }

/* Toolbar */
.ptv-toolbar {
    display:flex; align-items:center; gap:6px; flex-wrap:wrap;
    padding:9px 14px;
    background:var(--bg-card,#0f0f18);
    border-bottom:1px solid var(--border-color,rgba(255,255,255,.08));
    margin-bottom:0;
}
.ptv-toolbar-sep { width:1px; height:20px; background:var(--border-color,rgba(255,255,255,.1)); margin:0 2px; }
.ptv-tbtn {
    display:inline-flex; align-items:center; gap:5px;
    padding:6px 12px; border-radius:7px;
    border:1px solid var(--border-color,rgba(255,255,255,.12));
    background:var(--bg-secondary,rgba(255,255,255,.04));
    color:var(--text-primary,#e8eefc);
    font-size:.78rem; font-weight:500; cursor:pointer; text-decoration:none;
    white-space:nowrap; transition:border-color .15s,background .15s;
}
.ptv-tbtn:hover  { border-color:rgba(255,255,255,.25); background:rgba(255,255,255,.07); }
.ptv-tbtn.back   { background:transparent; border-color:transparent; color:var(--text-secondary); }
.ptv-tbtn.back:hover { color:var(--text-primary); }
.ptv-tbtn.pickup { border-color:rgba(37,99,235,.4); color:#93c5fd; background:rgba(37,99,235,.1); }
.ptv-tbtn.assign { border-color:rgba(124,58,237,.4); color:#c4b5fd; background:rgba(124,58,237,.1); }
.ptv-tbtn.resolve{ border-color:rgba(22,163,74,.4);  color:#86efac; background:rgba(22,163,74,.1); }
.ptv-tbtn.close  { border-color:rgba(220,38,38,.35); color:#fca5a5; background:rgba(220,38,38,.07); }
.ptv-tbtn.edit   { border-color:rgba(234,179,8,.35); color:#fde68a; background:rgba(234,179,8,.07); }
.ptv-tbtn.print  { border-color:rgba(148,163,184,.22); color:var(--text-secondary); }
.ptv-toolbar-r   { margin-left:auto; display:flex; align-items:center; gap:6px; }

/* Body grid */
.ptv-body {
    display:grid; grid-template-columns:minmax(0,1fr) 272px;
    overflow:hidden; min-height:0; flex:1;
}
.ptv-main    { display:flex; flex-direction:column; overflow-y:auto; }
.ptv-sidebar { border-left:1px solid var(--border-color,rgba(255,255,255,.07)); background:var(--bg-card,#0f0f18); overflow-y:auto; }

/* Ticket header */
.ptv-head {
    padding:14px 16px 0;
    background:var(--bg-card,#0f0f18);
    border-bottom:1px solid var(--border-color,rgba(255,255,255,.06));
}
.ptv-head-top  { display:flex; align-items:flex-start; gap:12px; }
.ptv-type-icon {
    width:40px; height:40px; border-radius:10px; flex-shrink:0;
    background:linear-gradient(135deg,#f97316,#dc2626);
    display:flex; align-items:center; justify-content:center; color:#fff; font-size:.95rem;
}
.ptv-head-info { flex:1; min-width:0; }
.ptv-ticket-num { font-size:.73rem; font-weight:700; color:var(--text-secondary,#8892a6); letter-spacing:.04em; }
.ptv-ticket-subject { font-size:1.05rem; font-weight:700; color:var(--text-primary,#e8eefc); margin:3px 0 8px; line-height:1.3; }
.ptv-head-meta { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.ptv-priority-badge { padding:3px 9px; border-radius:5px; font-size:.69rem; font-weight:700; }
.ptv-requested-by   { font-size:.77rem; color:var(--text-secondary,#8892a6); }
.ptv-requested-by strong { color:var(--cyan,#3b82f6); }
.ptv-requested-by a { color:var(--cyan,#3b82f6); text-decoration:none; }
.ptv-requested-by a:hover { text-decoration:underline; }

/* Status / Transitions block */
.ptv-status-block {
    display:flex; align-items:flex-start; gap:0; flex-wrap:wrap;
    padding:9px 16px;
    background:rgba(37,99,235,.06);
    border-bottom:1px solid rgba(37,99,235,.15);
}
.ptv-sb-item { padding:0 16px; }
.ptv-sb-item:first-child { padding-left:0; }
.ptv-sb-item + .ptv-sb-item { border-left:1px solid rgba(37,99,235,.18); }
.ptv-sb-label { font-size:.66rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--text-secondary); margin-bottom:4px; }
.ptv-sb-value { font-size:.85rem; font-weight:700; }
.ptv-trans-btn {
    padding:4px 11px; background:var(--bg-secondary,rgba(255,255,255,.06));
    border:1px solid var(--border-color,rgba(255,255,255,.15));
    border-radius:5px; font-size:.77rem; font-weight:500; cursor:pointer;
    color:var(--text-primary,#e8eefc); transition:border-color .15s;
}
.ptv-trans-btn:hover { border-color:#2563eb; color:#93c5fd; }

/* Tabs */
.ptv-tabs {
    display:flex; padding:0 16px;
    border-bottom:1px solid var(--border-color,rgba(255,255,255,.07));
    background:var(--bg-card,#0f0f18);
    overflow-x:auto; flex-shrink:0;
}
.ptv-tab-btn {
    padding:11px 14px; font-size:.79rem; font-weight:500;
    color:var(--text-secondary); background:transparent;
    border:none; border-bottom:2px solid transparent;
    cursor:pointer; white-space:nowrap; transition:color .15s,border-color .15s;
}
.ptv-tab-btn:hover  { color:var(--text-primary); }
.ptv-tab-btn.active { color:#60a5fa; border-bottom-color:#2563eb; font-weight:600; }
.ptv-tab-pane       { display:none; }
.ptv-tab-pane.active{ display:block; }

/* Description tag */
.ptv-desc-tag {
    display:inline-block; margin:12px 16px 6px;
    padding:3px 9px; border-radius:5px; font-size:.7rem; font-weight:600;
    background:rgba(148,163,184,.1); color:var(--text-secondary);
    border:1px solid rgba(148,163,184,.2);
}

/* Message cards */
.ptv-message {
    margin:0 16px 10px;
    border:1px solid var(--border-color,rgba(255,255,255,.07));
    border-radius:10px; background:var(--bg-card,#0f0f18); overflow:hidden;
}
.ptv-message.agent-msg    { border-color:rgba(37,99,235,.25); background:rgba(37,99,235,.04); }
.ptv-message.internal-msg { border-color:rgba(217,119,6,.3);  background:rgba(217,119,6,.05); }
.ptv-msg-head {
    display:flex; align-items:center; gap:10px;
    padding:9px 13px; border-bottom:1px solid var(--border-color,rgba(255,255,255,.05));
}
.ptv-avatar {
    width:30px; height:30px; border-radius:50%; flex-shrink:0;
    background:rgba(148,163,184,.2); display:flex; align-items:center;
    justify-content:center; font-size:.73rem; font-weight:700; color:var(--text-secondary);
}
.ptv-avatar.agent    { background:rgba(37,99,235,.2);  color:#93c5fd; }
.ptv-avatar.customer { background:rgba(22,163,74,.15); color:#86efac; }
.ptv-msg-sender-wrap { flex:1; min-width:0; }
.ptv-msg-sender { font-weight:600; font-size:.82rem; color:var(--text-primary); }
.ptv-msg-email  { font-size:.71rem; color:var(--text-secondary); }
.ptv-msg-time   { font-size:.72rem; color:var(--text-secondary); white-space:nowrap; }
.ptv-internal-tag {
    padding:2px 7px; border-radius:4px; font-size:.68rem; font-weight:700;
    background:rgba(217,119,6,.15); color:#fbbf24; border:1px solid rgba(217,119,6,.25);
}
.ptv-msg-body { padding:12px 13px; font-size:.875rem; color:var(--text-primary); line-height:1.65; white-space:pre-wrap; }

/* Reply composer */
.ptv-composer {
    margin:10px 16px 16px;
    border:1px solid var(--border-color,rgba(255,255,255,.1));
    border-radius:10px; background:var(--bg-card,#0f0f18); overflow:hidden;
}
.ptv-comp-tabs { display:flex; border-bottom:1px solid var(--border-color,rgba(255,255,255,.06)); }
.ptv-comp-tab  {
    padding:8px 13px; font-size:.77rem; font-weight:500;
    color:var(--text-secondary); background:transparent;
    border:none; cursor:pointer; border-bottom:2px solid transparent;
}
.ptv-comp-tab.active { color:#60a5fa; border-bottom-color:#2563eb; }
.ptv-comp-body textarea {
    width:100%; min-height:88px; border:none; resize:none;
    padding:11px 13px; font-size:.875rem; background:transparent;
    color:var(--text-primary); outline:none; box-sizing:border-box;
    font-family:inherit; line-height:1.55;
}
.ptv-comp-body textarea::placeholder { color:var(--text-secondary); }
.ptv-comp-footer {
    display:flex; align-items:center; justify-content:flex-end; gap:8px;
    padding:7px 12px; border-top:1px solid var(--border-color,rgba(255,255,255,.06));
}
.ptv-send-btn   { padding:7px 17px; background:#2563eb; color:#fff; border:none; border-radius:7px; font-size:.81rem; font-weight:600; cursor:pointer; }
.ptv-send-btn:hover { background:#1d4ed8; }
.ptv-cancel-btn { padding:7px 11px; background:transparent; border:1px solid var(--border-color,rgba(255,255,255,.12)); border-radius:7px; font-size:.81rem; color:var(--text-secondary); cursor:pointer; }
.ptv-closed-notice { margin:10px 16px 0; padding:10px 13px; border-radius:8px; font-size:.81rem; background:rgba(100,116,139,.08); border:1px solid rgba(100,116,139,.2); color:var(--text-secondary); text-align:center; }

/* Details tab */
.ptv-details-wrap { padding:14px 16px; }
.ptv-det-section  { margin-bottom:16px; }
.ptv-det-section-title { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--text-secondary); margin:0 0 9px; padding-bottom:5px; border-bottom:1px solid var(--border-color,rgba(255,255,255,.07)); }
.ptv-det-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(185px,1fr)); gap:9px; }
.ptv-det-field .k { font-size:.7rem; color:var(--text-secondary); margin-bottom:3px; }
.ptv-det-field .v { font-size:.82rem; color:var(--text-primary); font-weight:500; word-break:break-word; }
.ptv-fdata-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(175px,1fr)); gap:7px; }
.ptv-fdata-item { border:1px solid var(--border-color,rgba(255,255,255,.07)); border-radius:7px; padding:8px 10px; background:rgba(255,255,255,.01); }
.ptv-fdata-key  { font-size:.69rem; color:var(--text-secondary); text-transform:capitalize; margin-bottom:3px; }
.ptv-fdata-val  { font-size:.81rem; color:var(--text-primary); font-weight:500; }

/* History timeline */
.ptv-timeline-wrap { padding:14px 16px; }
.ptv-tl-item { display:flex; gap:11px; padding:9px 0; border-bottom:1px solid var(--border-color,rgba(255,255,255,.05)); }
.ptv-tl-item:last-child { border-bottom:none; }
.ptv-tl-dot  { width:22px; height:22px; border-radius:50%; flex-shrink:0; margin-top:2px; background:rgba(37,99,235,.12); border:2px solid rgba(37,99,235,.22); display:flex; align-items:center; justify-content:center; font-size:.58rem; color:#60a5fa; }
.ptv-tl-text { font-size:.81rem; color:var(--text-primary); line-height:1.45; }
.ptv-tl-meta { font-size:.71rem; color:var(--text-secondary); margin-top:3px; }

/* Sidebar sections */
.ptv-sb-section { border-bottom:1px solid var(--border-color,rgba(255,255,255,.07)); }
.ptv-sb-sec-head { display:flex; align-items:center; justify-content:space-between; padding:10px 13px 9px; cursor:pointer; user-select:none; }
.ptv-sb-sec-head .t { font-size:.78rem; font-weight:700; color:var(--text-primary); }
.ptv-sb-sec-head .arr { font-size:.68rem; color:var(--text-secondary); transition:transform .2s; }
.ptv-sb-sec-head.collapsed .arr { transform:rotate(-90deg); }
.ptv-sb-sec-body { padding:0 13px 11px; }
.ptv-prop-row { display:flex; justify-content:space-between; align-items:baseline; padding:5px 0; border-bottom:1px solid var(--border-color,rgba(255,255,255,.04)); font-size:.79rem; }
.ptv-prop-row:last-child { border-bottom:none; }
.ptv-prop-key { color:var(--text-secondary); min-width:78px; flex-shrink:0; }
.ptv-prop-val { color:var(--text-primary); font-weight:500; text-align:right; flex:1; }
.ptv-req-card { display:flex; align-items:center; gap:9px; padding:4px 0 8px; }
.ptv-req-avatar { width:34px; height:34px; border-radius:50%; flex-shrink:0; background:rgba(59,130,246,.15); display:flex; align-items:center; justify-content:center; font-size:.88rem; font-weight:700; color:var(--cyan,#3b82f6); }
.ptv-req-name  { font-size:.84rem; font-weight:700; color:var(--text-primary); }
.ptv-req-email { font-size:.72rem; color:var(--text-secondary); margin-top:2px; }

/* Assign dropdown */
.ptv-assign-wrap { position:relative; display:inline-flex; }
.ptv-assign-dd {
    display:none; position:absolute; top:calc(100% + 4px); left:0; z-index:200;
    background:var(--bg-card,#0f0f18); border:1px solid var(--border-color,rgba(255,255,255,.15));
    border-radius:8px; padding:8px; min-width:200px;
    box-shadow:0 8px 28px rgba(0,0,0,.4);
}
.ptv-assign-dd.open { display:block; }
.ptv-assign-dd select { width:100%; padding:7px 9px; border-radius:6px; border:1px solid var(--border-color,rgba(255,255,255,.15)); background:var(--bg-secondary,rgba(255,255,255,.06)); color:var(--text-primary); font-size:.81rem; margin-bottom:6px; outline:none; }
.ptv-assign-dd button[type=submit] { width:100%; padding:7px; background:#7c3aed; color:#fff; border:none; border-radius:6px; font-size:.8rem; font-weight:600; cursor:pointer; }

/* Edit modal */
.ptv-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:300; align-items:center; justify-content:center; }
.ptv-modal-overlay.open { display:flex; }
.ptv-modal { background:var(--bg-card,#0f0f18); border:1px solid var(--border-color,rgba(255,255,255,.12)); border-radius:12px; padding:22px; width:520px; max-width:95vw; max-height:88vh; overflow-y:auto; }
.ptv-modal h3 { font-size:.95rem; font-weight:700; color:var(--text-primary); margin:0 0 16px; }
.ptv-modal label { display:block; font-size:.77rem; color:var(--text-secondary); margin-bottom:4px; }
.ptv-modal input[type=text], .ptv-modal textarea, .ptv-modal select {
    width:100%; padding:8px 11px; border:1px solid var(--border-color,rgba(255,255,255,.15));
    border-radius:7px; background:var(--bg-secondary,rgba(255,255,255,.06)); color:var(--text-primary);
    font-size:.875rem; font-family:inherit; outline:none; box-sizing:border-box; margin-bottom:12px;
}
.ptv-modal textarea { min-height:96px; resize:vertical; line-height:1.55; }
.ptv-modal-actions { display:flex; justify-content:flex-end; gap:8px; margin-top:4px; }
.ptv-modal-save   { padding:8px 18px; background:#2563eb; color:#fff; border:none; border-radius:7px; font-size:.82rem; font-weight:600; cursor:pointer; }
.ptv-modal-cancel { padding:8px 13px; background:transparent; border:1px solid var(--border-color,rgba(255,255,255,.12)); border-radius:7px; font-size:.82rem; color:var(--text-secondary); cursor:pointer; }

/* Flash */
.ptv-flash-ok  { background:color-mix(in srgb,var(--green) 8%,transparent); border:1px solid color-mix(in srgb,var(--green) 25%,transparent); color:var(--green); padding:11px 14px; border-radius:8px; font-size:.87rem; display:flex; align-items:center; gap:8px; margin-bottom:10px; }
.ptv-flash-err { background:color-mix(in srgb,var(--red)   8%,transparent); border:1px solid color-mix(in srgb,var(--red)   25%,transparent); color:var(--red);   padding:11px 14px; border-radius:8px; font-size:.87rem; display:flex; align-items:center; gap:8px; margin-bottom:10px; }

/* Print */
@media print {
    .sp-sidebar, .ptv-toolbar, .ptv-tabs, .ptv-composer, .ptv-sidebar,
    .ptv-status-block, .ptv-modal-overlay, .sp-menu-btn, nav { display:none !important; }
    .ptv-body { grid-template-columns:1fr; }
    .ptv-tab-pane { display:block !important; }
}

/* Responsive */
@media (max-width:880px) {
    .ptv-body { grid-template-columns:1fr; }
    .ptv-sidebar { border-left:none; border-top:1px solid var(--border-color,rgba(255,255,255,.07)); }
}
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div class="sp-layout">
    <?php include dirname(__DIR__) . '/_sidebar.php'; ?>

    <div class="sp-main">

        <!-- Flash messages -->
        <?php if (!empty($_SESSION['_flash']['success'])): ?>
        <div class="ptv-flash-ok">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <?= htmlspecialchars($_SESSION['_flash']['success']) ?><?php unset($_SESSION['_flash']['success']); ?>
        </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['_flash']['error'])): ?>
        <div class="ptv-flash-err">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?= htmlspecialchars($_SESSION['_flash']['error']) ?><?php unset($_SESSION['_flash']['error']); ?>
        </div>
        <?php endif; ?>

        <div class="ptv-page">

        <!-- ═══════ Toolbar ══════════════════════════════════ -->
        <div class="ptv-toolbar">
            <a href="/support/admin/tickets" class="ptv-tbtn back">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                Back
            </a>
            <div class="ptv-toolbar-sep"></div>

            <!-- Edit -->
            <button type="button" class="ptv-tbtn edit" onclick="ptvOpenEdit()">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
            </button>

            <!-- Pick Up -->
            <form method="POST" action="/support/admin/ticket/<?= $ticketId ?>/pickup" style="display:contents">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" class="ptv-tbtn pickup">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
                    Pick Up
                </button>
            </form>

            <!-- Assign dropdown -->
            <div class="ptv-assign-wrap" id="ptvAssignWrap">
                <button type="button" class="ptv-tbtn assign" onclick="ptvToggleAssign(event)">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                    Assign
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="ptv-assign-dd" id="ptvAssignDd">
                    <form method="POST" action="/support/admin/ticket/<?= $ticketId ?>/assign">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <select name="assigned_to" required>
                            <option value="">Select agent…</option>
                            <?php foreach (($agents ?? []) as $agent): ?>
                            <option value="<?= (int)$agent['id'] ?>"<?= (int)($ticket['assigned_to'] ?? 0) === (int)$agent['id'] ? ' selected' : '' ?>>
                                <?= htmlspecialchars($agent['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Assign Agent</button>
                    </form>
                </div>
            </div>

            <?php if (!$isClosed): ?>
            <!-- Resolve -->
            <form method="POST" action="/support/admin/ticket/<?= $ticketId ?>/status" style="display:contents">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="status" value="resolved">
                <input type="hidden" name="status_reason" value="Ticket resolved by agent.">
                <button type="submit" class="ptv-tbtn resolve">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Resolve
                </button>
            </form>
            <!-- Close -->
            <form method="POST" action="/support/admin/ticket/<?= $ticketId ?>/status" style="display:contents">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="status" value="closed">
                <input type="hidden" name="status_reason" value="Ticket closed by agent.">
                <button type="submit" class="ptv-tbtn close">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Close
                </button>
            </form>
            <?php else: ?>
            <!-- Reopen -->
            <form method="POST" action="/support/admin/ticket/<?= $ticketId ?>/status" style="display:contents">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="status" value="open">
                <input type="hidden" name="status_reason" value="Ticket reopened by agent.">
                <button type="submit" class="ptv-tbtn pickup">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.5"/></svg>
                    Reopen
                </button>
            </form>
            <?php endif; ?>

            <div class="ptv-toolbar-r">
                <button type="button" class="ptv-tbtn print" onclick="window.print()" title="Print ticket">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Print
                </button>
            </div>
        </div><!-- /toolbar -->

        <!-- ═══════ Body ═════════════════════════════════════ -->
        <div class="ptv-body">

            <!-- ─── Main content ─────────────────────────── -->
            <div class="ptv-main">

                <!-- Ticket header -->
                <div class="ptv-head">
                    <div class="ptv-head-top">
                        <div class="ptv-type-icon">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v2z"/></svg>
                        </div>
                        <div class="ptv-head-info">
                            <div class="ptv-ticket-num"><?= htmlspecialchars($fmtId) ?></div>
                            <div class="ptv-ticket-subject"><?= htmlspecialchars($ticket['subject']) ?></div>
                            <div class="ptv-head-meta">
                                <span class="ptv-priority-badge" style="background:<?= $priorityColor ?>1f;color:<?= $priorityColor ?>;border:1px solid <?= $priorityColor ?>33;">
                                    <?= ucfirst($ticket['priority']) ?> Priority
                                </span>
                                <span class="ptv-requested-by">
                                    Requested by
                                    <strong><?= htmlspecialchars($requesterName) ?></strong>
                                    <?php if ($requesterEmail): ?>
                                    (<a href="mailto:<?= htmlspecialchars($requesterEmail) ?>"><?= htmlspecialchars($requesterEmail) ?></a>)
                                    <?php endif; ?>
                                    on <?= $createdAt ? date('M j, Y h:i A', $createdAt) : '—' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div style="height:10px;"></div>
                </div>

                <!-- Status / Transitions block -->
                <div class="ptv-status-block">
                    <div class="ptv-sb-item">
                        <div class="ptv-sb-label">Status</div>
                        <div class="ptv-sb-value" style="color:<?= $statusColor ?>;"><?= $statusLabel ?></div>
                    </div>
                    <div class="ptv-sb-item">
                        <div class="ptv-sb-label">Transition</div>
                        <div style="margin-top:4px;display:flex;gap:5px;flex-wrap:wrap;">
                            <?php if ($ticket['status'] === 'open'): ?>
                            <form method="POST" action="/support/admin/ticket/<?= $ticketId ?>/pickup" style="display:contents">
                                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <button type="submit" class="ptv-trans-btn">Assign (Pick Up)</button>
                            </form>
                            <?php elseif ($ticket['status'] === 'in_progress'): ?>
                            <form method="POST" action="/support/admin/ticket/<?= $ticketId ?>/status" style="display:contents">
                                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="status" value="waiting_customer">
                                <input type="hidden" name="status_reason" value="Pending customer response.">
                                <button type="submit" class="ptv-trans-btn">Pending Customer</button>
                            </form>
                            <form method="POST" action="/support/admin/ticket/<?= $ticketId ?>/status" style="display:contents">
                                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="status" value="resolved">
                                <input type="hidden" name="status_reason" value="Issue resolved.">
                                <button type="submit" class="ptv-trans-btn">Resolve</button>
                            </form>
                            <?php elseif ($ticket['status'] === 'waiting_customer'): ?>
                            <form method="POST" action="/support/admin/ticket/<?= $ticketId ?>/pickup" style="display:contents">
                                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <button type="submit" class="ptv-trans-btn">Resume Work</button>
                            </form>
                            <?php elseif ($ticket['status'] === 'resolved'): ?>
                            <form method="POST" action="/support/admin/ticket/<?= $ticketId ?>/status" style="display:contents">
                                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="status" value="closed">
                                <input type="hidden" name="status_reason" value="Ticket closed after resolution.">
                                <button type="submit" class="ptv-trans-btn">Close</button>
                            </form>
                            <form method="POST" action="/support/admin/ticket/<?= $ticketId ?>/status" style="display:contents">
                                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="status" value="open">
                                <input type="hidden" name="status_reason" value="Ticket reopened.">
                                <button type="submit" class="ptv-trans-btn">Re-open</button>
                            </form>
                            <?php elseif ($ticket['status'] === 'closed'): ?>
                            <form method="POST" action="/support/admin/ticket/<?= $ticketId ?>/status" style="display:contents">
                                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="status" value="open">
                                <input type="hidden" name="status_reason" value="Ticket reopened.">
                                <button type="submit" class="ptv-trans-btn">Re-open</button>
                            </form>
                            <?php else: ?>
                            <span style="font-size:.77rem;color:var(--text-secondary);">—</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!empty($ticket['assigned_to'])): ?>
                    <div class="ptv-sb-item">
                        <div class="ptv-sb-label">Assigned To</div>
                        <div class="ptv-sb-value" style="font-size:.81rem;"><?= htmlspecialchars($ticket['agent_name'] ?? 'Agent') ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Tab bar -->
                <div class="ptv-tabs">
                    <button type="button" class="ptv-tab-btn active" data-ptv-tab="conversations">Conversations</button>
                    <button type="button" class="ptv-tab-btn" data-ptv-tab="details">Details</button>
                    <button type="button" class="ptv-tab-btn" data-ptv-tab="history">History</button>
                </div>

                <!-- ── Conversations ────────────────────── -->
                <div class="ptv-tab-pane active" id="ptv-tab-conversations">

                    <!-- Description card -->
                    <span class="ptv-desc-tag">Description</span>
                    <div class="ptv-message" style="margin-top:4px;">
                        <div class="ptv-msg-head">
                            <div class="ptv-avatar customer"><?= htmlspecialchars($requesterInitials) ?></div>
                            <div class="ptv-msg-sender-wrap">
                                <div class="ptv-msg-sender"><?= htmlspecialchars($requesterName) ?></div>
                                <?php if ($requesterEmail): ?>
                                <div class="ptv-msg-email"><?= htmlspecialchars($requesterEmail) ?></div>
                                <?php endif; ?>
                            </div>
                            <span class="ptv-msg-time"><?= $createdAt ? date('M j, Y h:i A', $createdAt) : '' ?></span>
                        </div>
                        <div class="ptv-msg-body"><?= htmlspecialchars($ticket['description']) ?></div>
                    </div>

                    <!-- Messages -->
                    <?php foreach (($messages ?? []) as $msg):
                        $isSys   = ($msg['sender_type'] === 'system');
                        $isAgent = ($msg['sender_type'] === 'agent');
                        $isIntl  = !empty($msg['is_internal']);
                        $sName   = $msg['sender_name'] ?? ($isAgent ? 'Agent' : $requesterName);
                        $sEmail  = $msg['sender_email'] ?? null;
                        $sInit   = strtoupper(substr($sName, 0, 1));
                        $mClass  = $isAgent ? ' agent-msg' : '';
                        if ($isIntl) $mClass = ' internal-msg';
                    ?>
                    <?php if ($isSys): ?>
                    <div class="ptv-message" style="background:color-mix(in srgb,#00c6ff 6%,transparent);border:1px dashed color-mix(in srgb,#00c6ff 25%,transparent);">
                        <div class="ptv-msg-head">
                            <div class="ptv-avatar" style="background:linear-gradient(135deg,#00c6ff,#3b82f6);font-size:.66rem;color:#fff;">ℹ</div>
                            <div class="ptv-msg-sender-wrap">
                                <div class="ptv-msg-sender" style="color:#00c6ff;">System Update</div>
                            </div>
                            <span class="ptv-msg-time"><?= !empty($msg['created_at']) ? date('M j, Y h:i A', strtotime($msg['created_at'])) : '' ?></span>
                        </div>
                        <div class="ptv-msg-body"><?= htmlspecialchars($msg['message'] ?? '') ?></div>
                    </div>
                    <?php else: ?>
                    <div class="ptv-message<?= $mClass ?>">
                        <div class="ptv-msg-head">
                            <div class="ptv-avatar <?= $isAgent ? 'agent' : 'customer' ?>"><?= htmlspecialchars($sInit) ?></div>
                            <div class="ptv-msg-sender-wrap">
                                <div class="ptv-msg-sender"><?= htmlspecialchars($sName) ?></div>
                                <?php if ($sEmail): ?><div class="ptv-msg-email"><?= htmlspecialchars($sEmail) ?></div><?php endif; ?>
                            </div>
                            <?php if ($isIntl): ?>
                            <span class="ptv-internal-tag">
                                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:2px;"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                Internal Note
                            </span>
                            <?php endif; ?>
                            <span class="ptv-msg-time"><?= !empty($msg['created_at']) ? date('M j, Y h:i A', strtotime($msg['created_at'])) : '' ?></span>
                        </div>
                        <div class="ptv-msg-body"><?= htmlspecialchars($msg['message'] ?? '') ?></div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>

                    <!-- Reply composer -->
                    <?php if (!$isClosed): ?>
                    <div class="ptv-composer" id="ptv-reply-area">
                        <div class="ptv-comp-tabs">
                            <button type="button" class="ptv-comp-tab active" data-ptv-comp="reply">Reply</button>
                            <button type="button" class="ptv-comp-tab" data-ptv-comp="note">Internal Note</button>
                        </div>
                        <form method="POST" action="/support/admin/ticket/<?= $ticketId ?>/reply">
                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <input type="hidden" name="is_internal" id="ptv-is-internal" value="0">
                            <div class="ptv-comp-body">
                                <textarea id="ptv-reply-txt" name="message" required placeholder="Type your reply here…"></textarea>
                            </div>
                            <div class="ptv-comp-footer">
                                <button type="button" class="ptv-cancel-btn" onclick="document.getElementById('ptv-reply-txt').value=''">Clear</button>
                                <button type="submit" class="ptv-send-btn">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:4px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                    Send Reply
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php else: ?>
                    <div class="ptv-closed-notice">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:5px;"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        This ticket is closed. Reopen it to send replies.
                    </div>
                    <?php endif; ?>

                </div><!-- /conversations tab -->

                <!-- ── Details ──────────────────────────── -->
                <div class="ptv-tab-pane" id="ptv-tab-details">
                    <div class="ptv-details-wrap">
                        <div class="ptv-det-section">
                            <div class="ptv-det-section-title">Ticket Information</div>
                            <div class="ptv-det-grid">
                                <div class="ptv-det-field"><div class="k">Request ID</div><div class="v"><?= htmlspecialchars($fmtId) ?></div></div>
                                <div class="ptv-det-field"><div class="k">Status</div><div class="v" style="color:<?= $statusColor ?>;"><?= $statusLabel ?></div></div>
                                <div class="ptv-det-field"><div class="k">Priority</div><div class="v" style="color:<?= $priorityColor ?>;"><?= ucfirst($ticket['priority']) ?></div></div>
                                <div class="ptv-det-field"><div class="k">Assigned To</div><div class="v"><?= htmlspecialchars($ticket['agent_name'] ?? 'Not Assigned') ?></div></div>
                                <div class="ptv-det-field"><div class="k">Created</div><div class="v"><?= $createdAt ? date('M j, Y h:i A', $createdAt) : '—' ?></div></div>
                                <div class="ptv-det-field"><div class="k">Last Updated</div><div class="v"><?= $updatedAt ? date('M j, Y h:i A', $updatedAt) : '—' ?></div></div>
                                <?php if (!empty($ticket['closed_at'])): ?>
                                <div class="ptv-det-field"><div class="k">Closed</div><div class="v"><?= date('M j, Y h:i A', strtotime($ticket['closed_at'])) ?></div></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="ptv-det-section">
                            <div class="ptv-det-section-title">Requester</div>
                            <div class="ptv-det-grid">
                                <div class="ptv-det-field"><div class="k">Name</div><div class="v"><?= htmlspecialchars($requesterName) ?></div></div>
                                <div class="ptv-det-field"><div class="k">Email</div><div class="v"><?= htmlspecialchars($requesterEmail ?: '—') ?></div></div>
                            </div>
                        </div>

                        <div class="ptv-det-section">
                            <div class="ptv-det-section-title">Description</div>
                            <div style="font-size:.875rem;color:var(--text-primary);line-height:1.65;white-space:pre-wrap;"><?= htmlspecialchars($ticket['description']) ?></div>
                        </div>

                        <?php if (!empty($submittedData)): ?>
                        <div class="ptv-det-section">
                            <div class="ptv-det-section-title">Submitted Form Data</div>
                            <div class="ptv-fdata-grid">
                                <?php foreach ($submittedData as $key => $value): ?>
                                <div class="ptv-fdata-item">
                                    <div class="ptv-fdata-key"><?= htmlspecialchars((string)$key) ?></div>
                                    <div class="ptv-fdata-val"><?= htmlspecialchars(is_scalar($value) ? (string)$value : json_encode($value)) ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div><!-- /details tab -->

                <!-- ── History ──────────────────────────── -->
                <div class="ptv-tab-pane" id="ptv-tab-history">
                    <div class="ptv-timeline-wrap">
                        <?php if (empty($activities ?? [])): ?>
                        <div class="ptv-empty">No activity recorded yet.</div>
                        <?php else: ?>
                        <?php foreach ($activities as $activity): ?>
                        <div class="ptv-tl-item">
                            <div class="ptv-tl-dot">
                                <svg width="7" height="7" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8"/></svg>
                            </div>
                            <div>
                                <div class="ptv-tl-text"><?= htmlspecialchars($activity['description'] ?? 'Activity updated.') ?></div>
                                <div class="ptv-tl-meta">
                                    <?= !empty($activity['actor_name']) ? htmlspecialchars($activity['actor_name']) . ' · ' : '' ?>
                                    <?= !empty($activity['created_at']) ? date('M j, Y h:i A', strtotime($activity['created_at'])) : '' ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div><!-- /history tab -->

            </div><!-- /.ptv-main -->

            <!-- ─── Sidebar ──────────────────────────────── -->
            <aside class="ptv-sidebar">

                <!-- Properties -->
                <div class="ptv-sb-section">
                    <div class="ptv-sb-sec-head" onclick="ptvToggle(this)">
                        <span class="t">Properties</span>
                        <svg class="arr" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="ptv-sb-sec-body">
                        <div class="ptv-prop-row"><span class="ptv-prop-key">Request ID</span><span class="ptv-prop-val"><?= htmlspecialchars($fmtId) ?></span></div>
                        <div class="ptv-prop-row"><span class="ptv-prop-key">Status</span><span class="ptv-prop-val" style="color:<?= $statusColor ?>;"><?= $statusLabel ?></span></div>
                        <div class="ptv-prop-row"><span class="ptv-prop-key">Priority</span><span class="ptv-prop-val" style="color:<?= $priorityColor ?>;"><?= ucfirst($ticket['priority']) ?></span></div>
                        <div class="ptv-prop-row"><span class="ptv-prop-key">Agent</span><span class="ptv-prop-val"><?= htmlspecialchars($ticket['agent_name'] ?? 'Unassigned') ?></span></div>
                        <div class="ptv-prop-row"><span class="ptv-prop-key">Created</span><span class="ptv-prop-val" style="font-size:.73rem;"><?= $createdAt ? date('M j, Y', $createdAt) : '—' ?></span></div>
                        <?php if ($ticket['last_reply_at']): ?>
                        <div class="ptv-prop-row"><span class="ptv-prop-key">Last Reply</span><span class="ptv-prop-val" style="font-size:.73rem;"><?= date('M j, Y', strtotime($ticket['last_reply_at'])) ?></span></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Change Status (manual with reason) -->
                <div class="ptv-sb-section">
                    <div class="ptv-sb-sec-head" onclick="ptvToggle(this)">
                        <span class="t">Change Status</span>
                        <svg class="arr" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="ptv-sb-sec-body">
                        <form method="POST" action="/support/admin/ticket/<?= $ticketId ?>/status">
                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <select name="status" style="width:100%;padding:6px 9px;border-radius:6px;border:1px solid var(--border-color,rgba(255,255,255,.15));background:var(--bg-secondary,rgba(255,255,255,.06));color:var(--text-primary);font-size:.8rem;margin-bottom:8px;outline:none;">
                                <?php foreach (['open'=>'Open','in_progress'=>'In Progress','waiting_customer'=>'Waiting on Customer','resolved'=>'Resolved','closed'=>'Closed'] as $sv => $sl): ?>
                                <option value="<?= $sv ?>"<?= $ticket['status']===$sv?' selected':'' ?>><?= $sl ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label style="display:block;font-size:.74rem;color:var(--text-secondary);margin-bottom:4px;">Reason / Description <span style="color:var(--red);">*</span></label>
                            <textarea name="status_reason" rows="2" maxlength="1000" required
                                placeholder="Reason for this status change…"
                                style="width:100%;padding:7px 9px;border-radius:6px;border:1px solid var(--border-color,rgba(255,255,255,.15));background:var(--bg-secondary,rgba(255,255,255,.06));color:var(--text-primary);font-size:.8rem;resize:none;outline:none;font-family:inherit;line-height:1.4;margin-bottom:7px;box-sizing:border-box;"></textarea>
                            <button type="submit" style="width:100%;padding:7px;background:#2563eb;color:#fff;border:none;border-radius:6px;font-size:.8rem;font-weight:600;cursor:pointer;">Update Status</button>
                        </form>
                    </div>
                </div>

                <!-- Change Priority -->
                <div class="ptv-sb-section">
                    <div class="ptv-sb-sec-head" onclick="ptvToggle(this)">
                        <span class="t">Change Priority</span>
                        <svg class="arr" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="ptv-sb-sec-body">
                        <form method="POST" action="/support/admin/ticket/<?= $ticketId ?>/priority">
                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <select name="priority" style="width:100%;padding:6px 9px;border-radius:6px;border:1px solid var(--border-color,rgba(255,255,255,.15));background:var(--bg-secondary,rgba(255,255,255,.06));color:var(--text-primary);font-size:.8rem;margin-bottom:7px;outline:none;">
                                <?php foreach (['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent'] as $pv => $pl): ?>
                                <option value="<?= $pv ?>"<?= $ticket['priority']===$pv?' selected':'' ?>><?= $pl ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" style="width:100%;padding:7px;background:#7c3aed;color:#fff;border:none;border-radius:6px;font-size:.8rem;font-weight:600;cursor:pointer;">Update Priority</button>
                        </form>
                    </div>
                </div>

                <!-- Requester Details -->
                <div class="ptv-sb-section">
                    <div class="ptv-sb-sec-head" onclick="ptvToggle(this)">
                        <span class="t">Requester Details</span>
                        <svg class="arr" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="ptv-sb-sec-body">
                        <div class="ptv-req-card">
                            <div class="ptv-req-avatar"><?= htmlspecialchars($requesterInitials) ?></div>
                            <div>
                                <div class="ptv-req-name"><?= htmlspecialchars($requesterName) ?></div>
                                <?php if ($requesterEmail): ?>
                                <div class="ptv-req-email"><?= htmlspecialchars($requesterEmail) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </aside><!-- /.ptv-sidebar -->

        </div><!-- /.ptv-body -->
        </div><!-- /.ptv-page -->

    </div><!-- /.sp-main -->
</div><!-- /.sp-layout -->

<!-- ── Edit Modal ────────────────────────────────────────── -->
<div class="ptv-modal-overlay" id="ptvEditModal">
    <div class="ptv-modal">
        <h3>Edit Ticket</h3>
        <form method="POST" action="/support/admin/ticket/<?= $ticketId ?>/edit">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <label>Subject</label>
            <input type="text" name="subject" value="<?= htmlspecialchars($ticket['subject']) ?>" maxlength="255" required>
            <label>Priority</label>
            <select name="priority">
                <?php foreach (['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent'] as $pv => $pl): ?>
                <option value="<?= $pv ?>"<?= $ticket['priority']===$pv?' selected':'' ?>><?= $pl ?></option>
                <?php endforeach; ?>
            </select>
            <label>Description</label>
            <textarea name="description" rows="5" maxlength="10000"><?= htmlspecialchars($ticket['description']) ?></textarea>
            <div class="ptv-modal-actions">
                <button type="button" class="ptv-modal-cancel" onclick="ptvCloseEdit()">Cancel</button>
                <button type="submit" class="ptv-modal-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
// Tab switching
document.querySelectorAll('[data-ptv-tab]').forEach(function(btn){
    btn.addEventListener('click', function(){
        var key = btn.dataset.ptvTab;
        document.querySelectorAll('[data-ptv-tab]').forEach(function(b){ b.classList.remove('active'); });
        document.querySelectorAll('.ptv-tab-pane').forEach(function(p){ p.classList.remove('active'); });
        btn.classList.add('active');
        var pane = document.getElementById('ptv-tab-' + key);
        if (pane) pane.classList.add('active');
    });
});

// Composer tabs
document.querySelectorAll('[data-ptv-comp]').forEach(function(btn){
    btn.addEventListener('click', function(){
        document.querySelectorAll('[data-ptv-comp]').forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');
        var isNote = btn.dataset.ptvComp === 'note';
        var inp = document.getElementById('ptv-is-internal');
        if (inp) inp.value = isNote ? '1' : '0';
        var txt = document.getElementById('ptv-reply-txt');
        if (txt) txt.placeholder = isNote ? 'Add an internal note (not visible to requester)…' : 'Type your reply here…';
    });
});

// Sidebar collapse
function ptvToggle(head){
    head.classList.toggle('collapsed');
    var body = head.nextElementSibling;
    if (body) body.style.display = head.classList.contains('collapsed') ? 'none' : '';
}

// Assign dropdown
function ptvToggleAssign(e){
    e.stopPropagation();
    document.getElementById('ptvAssignDd').classList.toggle('open');
}
document.addEventListener('click', function(e){
    var wrap = document.getElementById('ptvAssignWrap');
    if (wrap && !wrap.contains(e.target)){
        var dd = document.getElementById('ptvAssignDd');
        if (dd) dd.classList.remove('open');
    }
});

// Edit modal
function ptvOpenEdit(){  document.getElementById('ptvEditModal').classList.add('open'); }
function ptvCloseEdit(){ document.getElementById('ptvEditModal').classList.remove('open'); }
document.getElementById('ptvEditModal').addEventListener('click', function(e){
    if (e.target === this) ptvCloseEdit();
});
</script>

<?php View::endSection(); ?>
