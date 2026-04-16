<?php
/**
 * Admin Support Ticket Detail View — Freshservice-inspired redesign
 */
use Core\View;

View::extend('admin');
View::section('content');

$isClosed = ($ticket['status'] === 'closed');

$statusColor = match($ticket['status']) {
    'open'             => '#2563eb',
    'in_progress'      => '#d97706',
    'waiting_customer' => '#7c3aed',
    'resolved'         => '#16a34a',
    'closed'           => '#64748b',
    default            => '#64748b',
};

$priorityColor = match($ticket['priority']) {
    'urgent' => '#dc2626',
    'high'   => '#ea580c',
    'medium' => '#2563eb',
    'low'    => '#64748b',
    default  => '#64748b',
};

$formattedTicketId = '#' . str_pad((string) ((int) $ticket['id']), 5, '0', STR_PAD_LEFT);
$createdAt         = !empty($ticket['created_at']) ? strtotime($ticket['created_at']) : null;
$updatedAt         = !empty($ticket['updated_at']) ? strtotime($ticket['updated_at']) : null;
$firstReplyTs      = !empty($firstAgentReplyAt ?? null) ? strtotime($firstAgentReplyAt) : null;
$responseMinutes   = ($createdAt && $firstReplyTs && $firstReplyTs >= $createdAt)
    ? (int) floor(($firstReplyTs - $createdAt) / 60) : null;

$totalMessages   = count($messages ?? []);
$customerReplies = $agentReplies = $internalNotes = 0;
foreach ($messages ?? [] as $msg) {
    if (!empty($msg['is_internal']))                    $internalNotes++;
    elseif (($msg['sender_type'] ?? '') === 'agent')    $agentReplies++;
    else                                                $customerReplies++;
}

$submittedData = [];
if (!empty($ticket['submitted_data']) && is_string($ticket['submitted_data'])) {
    $parsed = json_decode($ticket['submitted_data'], true);
    if (is_array($parsed)) $submittedData = $parsed;
}

$lifecycleLabel = $ticket['lifecycle_name'] ?? (!empty($ticket['closed_at']) ? 'Completed' : 'Default RLC');

// Initials for avatar fallback
$requesterName     = $ticket['user_name'] ?? 'U';
$requesterEmail    = $ticket['user_email'] ?? '';
$requesterInitials = strtoupper(substr($requesterName, 0, 1));
$csrfToken         = $csrf_token ?? '';
?>

<style>
/* ============================================================
   TICKET VIEW — Freshservice-inspired redesign
   ============================================================ */

/* Page shell */
.tv-page { display: flex; flex-direction: column; min-height: calc(100vh - 56px); background: var(--bg-primary,#0a0a12); }

/* ── Top toolbar ─────────────────────────────────────────── */
.tv-toolbar {
  display: flex; align-items: center; gap: 6px;
  padding: 9px 18px;
  background: var(--bg-card,#0f0f18);
  border-bottom: 1px solid var(--border-color,rgba(255,255,255,.08));
  flex-shrink: 0; flex-wrap: wrap;
}
.tv-toolbar-sep { width: 1px; height: 22px; background: var(--border-color,rgba(255,255,255,.1)); margin: 0 4px; }
.tv-toolbar-right { margin-left: auto; display: flex; align-items: center; gap: 6px; }

.tv-tbtn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 6px 14px; border-radius: 7px;
  border: 1px solid var(--border-color,rgba(255,255,255,.12));
  background: var(--bg-secondary,rgba(255,255,255,.04));
  color: var(--text-primary,#e8eefc);
  font-size: .8rem; font-weight: 500; cursor: pointer; text-decoration: none;
  white-space: nowrap; transition: border-color .15s, background .15s;
}
.tv-tbtn:hover  { border-color: rgba(255,255,255,.25); background: rgba(255,255,255,.07); }
.tv-tbtn.back   { background: transparent; border-color: transparent; }
.tv-tbtn.pickup { border-color: rgba(37,99,235,.4); color: #93c5fd; background: rgba(37,99,235,.1); }
.tv-tbtn.assign { border-color: rgba(124,58,237,.4); color: #c4b5fd; background: rgba(124,58,237,.1); }
.tv-tbtn.resolve{ border-color: rgba(22,163,74,.4);  color: #86efac; background: rgba(22,163,74,.1); }
.tv-tbtn.close  { border-color: rgba(220,38,38,.35); color: #fca5a5; background: rgba(220,38,38,.07); }
.tv-tbtn.actions{ gap: 4px; }
.tv-nav-btn { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border: 1px solid var(--border-color,rgba(255,255,255,.1)); border-radius: 6px; background: var(--bg-secondary,rgba(255,255,255,.04)); color: var(--text-secondary,#8892a6); cursor: pointer; font-size: .75rem; }
.tv-nav-btn:hover { border-color: rgba(255,255,255,.22); color: var(--text-primary,#e8eefc); }

/* ── Body: main + sidebar ────────────────────────────────── */
.tv-body {
  display: grid;
  grid-template-columns: minmax(0,1fr) 300px;
  flex: 1;
  overflow: hidden;
  min-height: 0;
}
.tv-main { display: flex; flex-direction: column; overflow-y: auto; min-height: 0; }
.tv-sidebar { border-left: 1px solid var(--border-color,rgba(255,255,255,.07)); background: var(--bg-card,#0f0f18); overflow-y: auto; min-height: 0; }

/* ── Ticket header ───────────────────────────────────────── */
.tv-head {
  padding: 16px 20px 0;
  background: var(--bg-card,#0f0f18);
  border-bottom: 1px solid var(--border-color,rgba(255,255,255,.06));
}
.tv-head-top { display: flex; align-items: flex-start; gap: 14px; }
.tv-type-icon {
  width: 44px; height: 44px; border-radius: 11px; flex-shrink: 0;
  background: linear-gradient(135deg,#f97316,#dc2626);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 1.1rem; box-shadow: 0 4px 12px rgba(249,115,22,.35);
}
.tv-head-info { flex: 1; min-width: 0; }
.tv-ticket-num { font-size: .75rem; font-weight: 700; color: var(--text-secondary,#8892a6); letter-spacing: .03em; }
.tv-ticket-subject { font-size: 1.2rem; font-weight: 700; color: var(--text-primary,#e8eefc); margin: 3px 0 8px; line-height: 1.3; }
.tv-head-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.tv-type-badge { padding: 3px 10px; border-radius: 5px; font-size: .71rem; font-weight: 700; background: rgba(124,58,237,.18); color: #c4b5fd; border: 1px solid rgba(124,58,237,.3); }
.tv-priority-badge { padding: 3px 10px; border-radius: 5px; font-size: .71rem; font-weight: 700; }
.tv-requested-by { font-size: .78rem; color: var(--text-secondary,#8892a6); }
.tv-requested-by strong { color: var(--cyan,#00f0ff); font-weight: 600; }
.tv-head-actions { display: flex; align-items: center; gap: 8px; margin-left: auto; flex-shrink: 0; }
.tv-reply-btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 7px 16px; background: #2563eb; color: #fff;
  border: none; border-radius: 7px; font-size: .82rem; font-weight: 600; cursor: pointer;
}
.tv-reply-btn:hover { background: #1d4ed8; }
.tv-icon-btn {
  display: inline-flex; align-items: center; justify-content: center;
  width: 32px; height: 32px; border: 1px solid var(--border-color,rgba(255,255,255,.1));
  border-radius: 6px; background: var(--bg-secondary,rgba(255,255,255,.04));
  color: var(--text-secondary,#8892a6); cursor: pointer; font-size: .8rem;
}
.tv-icon-btn:hover { border-color: rgba(255,255,255,.22); color: var(--text-primary,#e8eefc); }

/* ── Status / Transitions block ─────────────────────────── */
.tv-status-block {
  display: flex; align-items: center; gap: 0;
  padding: 10px 20px;
  background: rgba(37,99,235,.06);
  border-bottom: 1px solid rgba(37,99,235,.15);
  flex-wrap: wrap; gap: 0;
}
.tv-sb-item { padding: 0 20px; }
.tv-sb-item:first-child { padding-left: 0; }
.tv-sb-item + .tv-sb-item { border-left: 1px solid rgba(37,99,235,.2); }
.tv-sb-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-secondary,#8892a6); margin-bottom: 4px; }
.tv-sb-value { font-size: .88rem; font-weight: 700; }
.tv-trans-btn {
  padding: 5px 14px; background: var(--bg-secondary,rgba(255,255,255,.06));
  border: 1px solid var(--border-color,rgba(255,255,255,.15));
  border-radius: 6px; font-size: .8rem; font-weight: 500; cursor: pointer;
  color: var(--text-primary,#e8eefc); transition: border-color .15s;
}
.tv-trans-btn:hover { border-color: #2563eb; color: #93c5fd; }

/* ── Tab bar ─────────────────────────────────────────────── */
.tv-tabs {
  display: flex; padding: 0 20px;
  border-bottom: 1px solid var(--border-color,rgba(255,255,255,.07));
  background: var(--bg-card,#0f0f18);
  overflow-x: auto; gap: 0; flex-shrink: 0;
}
.tv-tab-btn {
  padding: 12px 16px; font-size: .81rem; font-weight: 500;
  color: var(--text-secondary,#8892a6); background: transparent;
  border: none; border-bottom: 2px solid transparent;
  cursor: pointer; white-space: nowrap; transition: color .15s, border-color .15s;
}
.tv-tab-btn:hover { color: var(--text-primary,#e8eefc); }
.tv-tab-btn.active { color: #60a5fa; border-bottom-color: #2563eb; font-weight: 600; }

/* ── Tab content ─────────────────────────────────────────── */
.tv-tab-pane { display: none; }
.tv-tab-pane.active { display: block; }

/* ── Conversation filter bar ─────────────────────────────── */
.tv-filter-bar {
  display: flex; align-items: center; gap: 14px;
  padding: 8px 20px; flex-wrap: wrap;
  background: var(--bg-card,#0f0f18);
  border-bottom: 1px solid var(--border-color,rgba(255,255,255,.05));
  font-size: .79rem; color: var(--text-secondary,#8892a6);
}
.tv-filter-label { font-weight: 700; color: var(--text-secondary,#8892a6); }
.tv-filter-check { display: flex; align-items: center; gap: 5px; cursor: pointer; user-select: none; }
.tv-filter-check input { accent-color: #2563eb; }
.tv-sort-btn {
  margin-left: auto; display: flex; align-items: center; gap: 5px;
  padding: 4px 10px; border: 1px solid var(--border-color,rgba(255,255,255,.1));
  border-radius: 5px; cursor: pointer; background: transparent;
  font-size: .76rem; color: var(--text-secondary,#8892a6);
}

/* ── Description tag ─────────────────────────────────────── */
.tv-desc-tag {
  display: inline-block; margin: 12px 20px 8px;
  padding: 3px 10px; border-radius: 5px; font-size: .72rem; font-weight: 600;
  background: rgba(148,163,184,.1); color: var(--text-secondary,#8892a6);
  border: 1px solid rgba(148,163,184,.2);
}

/* ── Message cards ───────────────────────────────────────── */
.tv-message {
  margin: 0 20px 10px;
  border: 1px solid var(--border-color,rgba(255,255,255,.07));
  border-radius: 10px; background: var(--bg-card,#0f0f18); overflow: hidden;
}
.tv-message.agent-msg { border-color: rgba(37,99,235,.25); background: rgba(37,99,235,.04); }
.tv-message.internal-msg { border-color: rgba(217,119,6,.3); background: rgba(217,119,6,.05); }
.tv-msg-head {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 14px; border-bottom: 1px solid var(--border-color,rgba(255,255,255,.05));
}
.tv-avatar {
  width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
  background: rgba(148,163,184,.2); display: flex; align-items: center;
  justify-content: center; font-size: .75rem; font-weight: 700; color: var(--text-secondary,#8892a6);
}
.tv-avatar.agent { background: rgba(37,99,235,.2); color: #93c5fd; }
.tv-avatar.customer { background: rgba(22,163,74,.15); color: #86efac; }
.tv-msg-sender { font-weight: 600; font-size: .84rem; color: var(--text-primary,#e8eefc); }
.tv-msg-time { font-size: .74rem; color: var(--text-secondary,#8892a6); margin-left: auto; }
.tv-internal-tag {
  padding: 2px 8px; border-radius: 4px; font-size: .69rem; font-weight: 700;
  background: rgba(217,119,6,.15); color: #fbbf24; border: 1px solid rgba(217,119,6,.25);
}
.tv-msg-body {
  padding: 12px 14px; font-size: .875rem; color: var(--text-primary,#e8eefc);
  line-height: 1.65; white-space: pre-wrap;
}
.tv-msg-actions {
  display: flex; align-items: center; gap: 6px; padding: 6px 14px 10px;
  border-top: 1px solid var(--border-color,rgba(255,255,255,.04));
}
.tv-msg-act-btn {
  display: inline-flex; align-items: center; gap: 4px;
  background: none; border: none; font-size: .76rem;
  color: var(--text-secondary,#8892a6); cursor: pointer; padding: 3px 6px; border-radius: 5px;
}
.tv-msg-act-btn:hover { color: #60a5fa; background: rgba(37,99,235,.1); }
.tv-msg-dismiss { margin-left: auto; background: none; border: none; color: var(--text-secondary,#8892a6); cursor: pointer; font-size: .8rem; }
.tv-msg-dismiss:hover { color: #f87171; }

/* ── Reply composer ──────────────────────────────────────── */
.tv-composer {
  margin: 12px 20px 20px;
  border: 1px solid var(--border-color,rgba(255,255,255,.1));
  border-radius: 10px; background: var(--bg-card,#0f0f18); overflow: hidden;
}
.tv-comp-tabs { display: flex; border-bottom: 1px solid var(--border-color,rgba(255,255,255,.06)); }
.tv-comp-tab {
  padding: 9px 16px; font-size: .79rem; font-weight: 500;
  color: var(--text-secondary,#8892a6); background: transparent;
  border: none; cursor: pointer; border-bottom: 2px solid transparent;
}
.tv-comp-tab.active { color: #60a5fa; border-bottom-color: #2563eb; }
.tv-comp-tab:hover { color: var(--text-primary,#e8eefc); }
.tv-comp-body { padding: 0; }
.tv-comp-body textarea {
  width: 100%; min-height: 96px; border: none; resize: none;
  padding: 12px 14px; font-size: .875rem; background: transparent;
  color: var(--text-primary,#e8eefc); outline: none; box-sizing: border-box;
  font-family: inherit; line-height: 1.55;
}
.tv-comp-body textarea::placeholder { color: var(--text-secondary,#8892a6); }
.tv-comp-footer {
  display: flex; align-items: center; justify-content: flex-end; gap: 8px;
  padding: 8px 14px; border-top: 1px solid var(--border-color,rgba(255,255,255,.06));
  background: rgba(255,255,255,.01);
}
.tv-send-btn {
  padding: 8px 20px; background: #2563eb; color: #fff;
  border: none; border-radius: 7px; font-size: .83rem; font-weight: 600; cursor: pointer;
}
.tv-send-btn:hover { background: #1d4ed8; }
.tv-cancel-btn {
  padding: 8px 14px; background: transparent; border: 1px solid var(--border-color,rgba(255,255,255,.12));
  border-radius: 7px; font-size: .83rem; color: var(--text-secondary,#8892a6); cursor: pointer;
}

/* ── Content wrapper ─────────────────────────────────────── */
.tv-content { padding: 14px 0 0; }
.tv-closed-notice {
  margin: 10px 20px 0; padding: 10px 14px; border-radius: 8px; font-size: .82rem;
  background: rgba(100,116,139,.08); border: 1px solid rgba(100,116,139,.2);
  color: var(--text-secondary,#8892a6); text-align: center;
}

/* ── Details tab ─────────────────────────────────────────── */
.tv-details-wrap { padding: 14px 20px; }
.tv-detail-section { margin-bottom: 18px; }
.tv-detail-section-title {
  font-size: .72rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .06em; color: var(--text-secondary,#8892a6);
  margin: 0 0 10px; padding-bottom: 6px;
  border-bottom: 1px solid var(--border-color,rgba(255,255,255,.07));
}
.tv-detail-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(200px,1fr)); gap: 10px; }
.tv-detail-field { }
.tv-detail-field .k { font-size: .72rem; color: var(--text-secondary,#8892a6); margin-bottom: 3px; }
.tv-detail-field .v { font-size: .84rem; color: var(--text-primary,#e8eefc); font-weight: 500; word-break: break-word; }
.tv-fdata-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(190px,1fr)); gap: 8px; }
.tv-fdata-item { border: 1px solid var(--border-color,rgba(255,255,255,.07)); border-radius: 8px; padding: 9px 11px; background: rgba(255,255,255,.01); }
.tv-fdata-key { font-size: .71rem; color: var(--text-secondary,#8892a6); text-transform: capitalize; margin-bottom: 3px; }
.tv-fdata-val { font-size: .83rem; color: var(--text-primary,#e8eefc); font-weight: 500; }

/* ── History / Activity timeline ─────────────────────────── */
.tv-timeline-wrap { padding: 14px 20px; }
.tv-tl-item { display: flex; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--border-color,rgba(255,255,255,.05)); }
.tv-tl-item:last-child { border-bottom: none; }
.tv-tl-dot {
  width: 26px; height: 26px; border-radius: 50%; flex-shrink: 0; margin-top: 2px;
  background: rgba(37,99,235,.12); border: 2px solid rgba(37,99,235,.25);
  display: flex; align-items: center; justify-content: center;
  font-size: .65rem; color: #60a5fa;
}
.tv-tl-text { font-size: .83rem; color: var(--text-primary,#e8eefc); line-height: 1.45; }
.tv-tl-meta { font-size: .73rem; color: var(--text-secondary,#8892a6); margin-top: 3px; }

/* ── SIDEBAR ─────────────────────────────────────────────── */
.tv-sb-section { border-bottom: 1px solid var(--border-color,rgba(255,255,255,.07)); }
.tv-sb-sec-head {
  display: flex; align-items: center; justify-content: space-between;
  padding: 12px 16px 10px; cursor: pointer; user-select: none;
}
.tv-sb-sec-head .t { font-size: .8rem; font-weight: 700; color: var(--text-primary,#e8eefc); }
.tv-sb-sec-head .arr { font-size: .7rem; color: var(--text-secondary,#8892a6); transition: transform .2s; }
.tv-sb-sec-head.collapsed .arr { transform: rotate(-90deg); }
.tv-sb-sec-body { padding: 0 16px 14px; }

/* Property rows */
.tv-prop-row {
  display: flex; justify-content: space-between; align-items: baseline;
  padding: 6px 0; border-bottom: 1px solid var(--border-color,rgba(255,255,255,.04));
  font-size: .81rem;
}
.tv-prop-row:last-child { border-bottom: none; }
.tv-prop-key { color: var(--text-secondary,#8892a6); min-width: 90px; flex-shrink: 0; }
.tv-prop-val { color: var(--text-primary,#e8eefc); font-weight: 500; text-align: right; flex: 1; }
.tv-prop-action { color: var(--cyan,#00f0ff); font-size: .76rem; font-weight: 500; cursor: pointer; text-decoration: none; }
.tv-prop-action:hover { text-decoration: underline; }
.tv-notcfg-badge { padding: 2px 7px; border-radius: 4px; font-size: .7rem; background: rgba(100,116,139,.12); color: var(--text-secondary,#8892a6); }

/* Associations */
.tv-assoc-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 7px 0; border-bottom: 1px solid var(--border-color,rgba(255,255,255,.04));
  font-size: .81rem;
}
.tv-assoc-row:last-child { border-bottom: none; }
.tv-assoc-label { color: var(--text-secondary,#8892a6); }
.tv-assoc-btn {
  padding: 3px 10px; border: 1px solid var(--border-color,rgba(255,255,255,.12));
  border-radius: 5px; font-size: .74rem; background: transparent;
  color: var(--text-primary,#e8eefc); cursor: pointer;
}
.tv-assoc-btn:hover { border-color: var(--cyan,#00f0ff); color: var(--cyan,#00f0ff); }

/* Requester card */
.tv-requester-card { display: flex; align-items: center; gap: 10px; padding: 4px 0 10px; }
.tv-req-avatar {
  width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
  background: rgba(0,240,255,.15); display: flex; align-items: center;
  justify-content: center; font-size: .95rem; font-weight: 700; color: var(--cyan,#00f0ff);
}
.tv-req-name { font-size: .88rem; font-weight: 700; color: var(--text-primary,#e8eefc); }
.tv-req-email { font-size: .74rem; color: var(--text-secondary,#8892a6); margin-top: 2px; }
.tv-req-link { font-size: .74rem; color: var(--cyan,#00f0ff); cursor: pointer; display: flex; align-items: center; gap: 3px; margin-top: 3px; text-decoration: none; }

/* Requester action cards */
.tv-req-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 4px; }
.tv-req-act-card {
  border: 1px solid var(--border-color,rgba(255,255,255,.1));
  border-radius: 9px; padding: 10px 8px; text-align: center; cursor: pointer;
  background: var(--bg-secondary,rgba(255,255,255,.03));
  transition: border-color .15s, background .15s;
}
.tv-req-act-card:hover { border-color: rgba(0,240,255,.35); background: rgba(0,240,255,.06); }
.tv-req-act-card i { display: block; font-size: .95rem; color: var(--text-secondary,#8892a6); margin-bottom: 5px; }
.tv-req-act-card span { font-size: .7rem; color: var(--text-secondary,#8892a6); font-weight: 500; }

/* ── Empty states ─────────────────────────────────────────── */
.tv-empty { padding: 32px 20px; text-align: center; color: var(--text-secondary,#8892a6); font-size: .83rem; }

/* ── Responsive ─────────────────────────────────────────────*/
@media (max-width: 1080px) {
  .tv-body { grid-template-columns: 1fr; }
  .tv-sidebar { border-left: none; border-top: 1px solid var(--border-color,rgba(255,255,255,.07)); }
}
</style>

<div class="tv-page">

  <!-- ═══ Top toolbar ═══════════════════════════════════════════ -->
  <div class="tv-toolbar">
    <a href="/admin/support/tickets" class="tv-tbtn back" title="Back to tickets">
      <i class="fas fa-arrow-left"></i>
    </a>
    <div class="tv-toolbar-sep"></div>

    <a href="/admin/support/tickets/<?= (int)$ticket['id'] ?>/edit" class="tv-tbtn">
      <i class="fas fa-pen"></i> Edit
    </a>
    <form method="POST" action="/admin/support/tickets/<?= (int)$ticket['id'] ?>/status" style="display:contents">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" name="status" value="in_progress">
      <button type="submit" class="tv-tbtn pickup">
        <i class="fas fa-user-check"></i> Pick up
      </button>
    </form>
    <form method="POST" action="/admin/support/tickets/<?= (int)$ticket['id'] ?>/assign" style="display:contents">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <button type="submit" class="tv-tbtn assign">
        <i class="fas fa-user-plus"></i> Assign
      </button>
    </form>
    <?php if (!$isClosed): ?>
    <form method="POST" action="/admin/support/tickets/<?= (int)$ticket['id'] ?>/status" style="display:contents">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" name="status" value="resolved">
      <button type="submit" class="tv-tbtn resolve">
        <i class="fas fa-check-circle"></i> Resolve
      </button>
    </form>
    <form method="POST" action="/admin/support/tickets/<?= (int)$ticket['id'] ?>/status" style="display:contents">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" name="status" value="closed">
      <button type="submit" class="tv-tbtn close">
        <i class="fas fa-lock"></i> Close
      </button>
    </form>
    <?php else: ?>
    <form method="POST" action="/admin/support/tickets/<?= (int)$ticket['id'] ?>/status" style="display:contents">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" name="status" value="open">
      <button type="submit" class="tv-tbtn pickup">
        <i class="fas fa-rotate-left"></i> Reopen
      </button>
    </form>
    <?php endif; ?>

    <div class="tv-toolbar-right">
      <button class="tv-tbtn actions">
        <i class="fas fa-ellipsis"></i> Actions <i class="fas fa-chevron-down" style="font-size:.65rem;"></i>
      </button>
      <button class="tv-nav-btn" title="Settings"><i class="fas fa-gear"></i></button>
      <button class="tv-nav-btn" title="Previous"><i class="fas fa-chevron-left"></i></button>
      <button class="tv-nav-btn" title="Next"><i class="fas fa-chevron-right"></i></button>
    </div>
  </div>

  <!-- ═══ Body ══════════════════════════════════════════════════ -->
  <div class="tv-body">

    <!-- ─── Main content ─────────────────────────────────────── -->
    <div class="tv-main">

      <!-- Ticket header -->
      <div class="tv-head">
        <div class="tv-head-top">
          <div class="tv-type-icon">
            <i class="fas fa-ticket"></i>
          </div>
          <div class="tv-head-info">
            <div class="tv-ticket-num"><?= htmlspecialchars($formattedTicketId) ?></div>
            <div class="tv-ticket-subject"><?= htmlspecialchars($ticket['subject']) ?></div>
            <div class="tv-head-meta">
              <span class="tv-type-badge">
                <?= htmlspecialchars(ucwords(str_replace('_',' ', $ticket['category_name'] ?? 'Support Request'))) ?>
              </span>
              <span class="tv-priority-badge" style="background:<?= $priorityColor ?>1f;color:<?= $priorityColor ?>;border:1px solid <?= $priorityColor ?>33;">
                <?= ucfirst($ticket['priority']) ?> Priority
              </span>
              <span class="tv-requested-by">
                Requested By
                <strong><?= htmlspecialchars($requesterName) ?></strong>
                on
                <?= $createdAt ? date('M j, Y h:i A', $createdAt) : '—' ?>
              </span>
            </div>
          </div>
          <div class="tv-head-actions">
            <button class="tv-reply-btn" onclick="document.getElementById('tv-reply-area').scrollIntoView({behavior:'smooth'});document.getElementById('tv-reply-txt').focus()">
              <i class="fas fa-reply"></i> Reply All
              <span style="margin-left:2px; padding-left:6px; border-left:1px solid rgba(255,255,255,.3)"><i class="fas fa-chevron-down" style="font-size:.65rem;"></i></span>
            </button>
            <button class="tv-icon-btn" title="More options"><i class="fas fa-list"></i></button>
          </div>
        </div>
      </div>

      <!-- Status / Transitions block -->
      <div class="tv-status-block">
        <div class="tv-sb-item">
          <div class="tv-sb-label">Status</div>
          <div class="tv-sb-value" style="color:<?= $statusColor ?>;">
            <?= ucwords(str_replace('_', ' ', $ticket['status'])) ?>
          </div>
        </div>
        <div class="tv-sb-item">
          <div class="tv-sb-label">Transitions</div>
          <div style="margin-top:4px; display:flex; gap:6px; flex-wrap:wrap;">
            <?php if ($ticket['status'] === 'open'): ?>
              <form method="POST" action="/admin/support/tickets/<?= (int)$ticket['id'] ?>/status" style="display:contents">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="status" value="in_progress">
                <button type="submit" class="tv-trans-btn">Assign</button>
              </form>
            <?php elseif ($ticket['status'] === 'in_progress'): ?>
              <form method="POST" action="/admin/support/tickets/<?= (int)$ticket['id'] ?>/status" style="display:contents">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="status" value="waiting_customer">
                <button type="submit" class="tv-trans-btn">Pending Customer</button>
              </form>
              <form method="POST" action="/admin/support/tickets/<?= (int)$ticket['id'] ?>/status" style="display:contents">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="status" value="resolved">
                <button type="submit" class="tv-trans-btn">Resolve</button>
              </form>
            <?php elseif ($ticket['status'] === 'resolved'): ?>
              <form method="POST" action="/admin/support/tickets/<?= (int)$ticket['id'] ?>/status" style="display:contents">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="status" value="closed">
                <button type="submit" class="tv-trans-btn">Close</button>
              </form>
              <form method="POST" action="/admin/support/tickets/<?= (int)$ticket['id'] ?>/status" style="display:contents">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="status" value="open">
                <button type="submit" class="tv-trans-btn">Re-open</button>
              </form>
            <?php elseif ($ticket['status'] === 'closed'): ?>
              <form method="POST" action="/admin/support/tickets/<?= (int)$ticket['id'] ?>/status" style="display:contents">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="status" value="open">
                <button type="submit" class="tv-trans-btn">Re-open</button>
              </form>
            <?php else: ?>
              <span style="font-size:.78rem;color:var(--text-secondary,#8892a6);">No transitions available</span>
            <?php endif; ?>
          </div>
        </div>
        <?php if (!empty($ticket['assigned_to'])): ?>
        <div class="tv-sb-item">
          <div class="tv-sb-label">Assigned To</div>
          <div class="tv-sb-value" style="font-size:.83rem;"><?= htmlspecialchars($ticket['agent_name'] ?? 'Agent') ?></div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Tab bar -->
      <div class="tv-tabs">
        <button type="button" class="tv-tab-btn active" data-tab="conversations">Conversations</button>
        <button type="button" class="tv-tab-btn" data-tab="details">Details</button>
        <button type="button" class="tv-tab-btn" data-tab="tasks">Tasks</button>
        <button type="button" class="tv-tab-btn" data-tab="checklists">Checklists</button>
        <button type="button" class="tv-tab-btn" data-tab="resolution">Resolution</button>
        <button type="button" class="tv-tab-btn" data-tab="history">History</button>
      </div>

      <!-- ─ Conversations tab ──────────────────────────────── -->
      <div class="tv-tab-pane active" id="tv-tab-conversations">
        <!-- Filter bar -->
        <div class="tv-filter-bar">
          <span class="tv-filter-label">Filter :</span>
          <label class="tv-filter-check">
            <input type="checkbox" checked id="flt-emails"> Emails
          </label>
          <label class="tv-filter-check">
            <input type="checkbox" id="flt-auto"> Auto Notifications
          </label>
          <label class="tv-filter-check">
            <input type="checkbox" checked id="flt-notes"> Notes
          </label>
          <button class="tv-sort-btn" title="Sort conversations">
            <i class="fas fa-arrow-up-arrow-down"></i>
          </button>
        </div>

        <!-- Description tag -->
        <span class="tv-desc-tag">Description</span>

        <!-- Messages -->
        <?php if (empty($messages)): ?>
          <div class="tv-empty">
            <i class="fas fa-inbox" style="font-size:1.8rem;opacity:.3;display:block;margin-bottom:10px;"></i>
            No messages yet.
          </div>
        <?php else: ?>
          <?php foreach ($messages ?? [] as $msg):
            $isAgent    = (($msg['sender_type'] ?? '') === 'agent');
            $isInternal = !empty($msg['is_internal']);
            $senderName = $msg['sender_name'] ?? ($isAgent ? 'Agent' : $requesterName);
            $initials   = strtoupper(substr($senderName, 0, 1));
            $msgClass   = $isAgent ? ' agent-msg' : '';
            if ($isInternal) $msgClass = ' internal-msg';
          ?>
          <div class="tv-message<?= $msgClass ?>">
            <div class="tv-msg-head">
              <div class="tv-avatar <?= $isAgent ? 'agent' : 'customer' ?>"><?= htmlspecialchars($initials) ?></div>
              <span class="tv-msg-sender"><?= htmlspecialchars($senderName) ?></span>
              <?php if ($isInternal): ?>
                <span class="tv-internal-tag"><i class="fas fa-lock" style="font-size:.6rem;margin-right:3px;"></i>Internal Note</span>
              <?php endif; ?>
              <span class="tv-msg-time">
                <?= !empty($msg['created_at']) ? '· ' . date('M j, Y h:i A', strtotime($msg['created_at'])) : '' ?>
              </span>
            </div>
            <div class="tv-msg-body"><?= htmlspecialchars($msg['message'] ?? '') ?></div>
            <div class="tv-msg-actions">
              <button class="tv-msg-act-btn" title="Reply"><i class="fas fa-reply"></i></button>
              <button class="tv-msg-act-btn" title="Reply All"><i class="fas fa-reply-all"></i></button>
              <button class="tv-msg-act-btn" title="Forward"><i class="fas fa-share"></i></button>
              <button class="tv-msg-dismiss" title="Dismiss"><i class="fas fa-xmark"></i></button>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <!-- Reply composer -->
        <?php if (!$isClosed): ?>
        <div class="tv-composer" id="tv-reply-area">
          <div class="tv-comp-tabs">
            <button type="button" class="tv-comp-tab active" data-comp="reply">Reply</button>
            <button type="button" class="tv-comp-tab" data-comp="note">Internal Note</button>
          </div>
          <form method="POST" action="/admin/support/tickets/<?= (int)$ticket['id'] ?>/reply">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="is_internal" id="tv-is-internal" value="0">
            <div class="tv-comp-body">
              <textarea id="tv-reply-txt" name="message" required
                placeholder="Type your reply here..."></textarea>
            </div>
            <div class="tv-comp-footer">
              <button type="button" class="tv-cancel-btn"
                onclick="document.getElementById('tv-reply-txt').value=''">Discard</button>
              <button type="submit" class="tv-send-btn">
                <i class="fas fa-paper-plane"></i> Send
              </button>
            </div>
          </form>
        </div>
        <?php else: ?>
          <div class="tv-closed-notice">
            <i class="fas fa-lock" style="margin-right:6px;"></i>
            This ticket is closed. Reopen it to reply.
          </div>
        <?php endif; ?>
      </div>

      <!-- ─ Details tab ───────────────────────────────────── -->
      <div class="tv-tab-pane" id="tv-tab-details">
        <div class="tv-details-wrap">
          <div class="tv-detail-section">
            <div class="tv-detail-section-title">Ticket Information</div>
            <div class="tv-detail-grid">
              <div class="tv-detail-field">
                <div class="k">Request ID</div>
                <div class="v"><?= htmlspecialchars($formattedTicketId) ?></div>
              </div>
              <div class="tv-detail-field">
                <div class="k">Status</div>
                <div class="v" style="color:<?= $statusColor ?>;"><?= ucwords(str_replace('_',' ',$ticket['status'])) ?></div>
              </div>
              <div class="tv-detail-field">
                <div class="k">Priority</div>
                <div class="v" style="color:<?= $priorityColor ?>;"><?= ucfirst($ticket['priority']) ?></div>
              </div>
              <div class="tv-detail-field">
                <div class="k">Group</div>
                <div class="v"><?= htmlspecialchars($ticket['group_name'] ?? '—') ?></div>
              </div>
              <div class="tv-detail-field">
                <div class="k">Category</div>
                <div class="v"><?= htmlspecialchars($ticket['category_name'] ?? '—') ?></div>
              </div>
              <div class="tv-detail-field">
                <div class="k">Assigned To</div>
                <div class="v"><?= htmlspecialchars($ticket['agent_name'] ?? 'Not Assigned') ?></div>
              </div>
              <div class="tv-detail-field">
                <div class="k">Created</div>
                <div class="v"><?= $createdAt ? date('M j, Y h:i A', $createdAt) : '—' ?></div>
              </div>
              <div class="tv-detail-field">
                <div class="k">Last Updated</div>
                <div class="v"><?= $updatedAt ? date('M j, Y h:i A', $updatedAt) : '—' ?></div>
              </div>
              <?php if ($responseMinutes !== null): ?>
              <div class="tv-detail-field">
                <div class="k">First Response</div>
                <div class="v"><?= $responseMinutes ?> min</div>
              </div>
              <?php endif; ?>
            </div>
          </div>

          <?php if (!empty($ticket['description'])): ?>
          <div class="tv-detail-section">
            <div class="tv-detail-section-title">Description</div>
            <div style="font-size:.875rem;color:var(--text-primary,#e8eefc);line-height:1.65;white-space:pre-wrap;"><?= htmlspecialchars($ticket['description']) ?></div>
          </div>
          <?php endif; ?>

          <?php if (!empty($submittedData)): ?>
          <div class="tv-detail-section">
            <div class="tv-detail-section-title">Submitted Form Data</div>
            <div class="tv-fdata-grid">
              <?php foreach ($submittedData as $key => $value): ?>
              <div class="tv-fdata-item">
                <div class="tv-fdata-key"><?= htmlspecialchars((string)$key) ?></div>
                <div class="tv-fdata-val"><?= htmlspecialchars(is_scalar($value) ? (string)$value : json_encode($value)) ?></div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- ─ Tasks tab (placeholder) ───────────────────────── -->
      <div class="tv-tab-pane" id="tv-tab-tasks">
        <div class="tv-empty">
          <i class="fas fa-list-check" style="font-size:1.8rem;opacity:.3;display:block;margin-bottom:10px;"></i>
          No tasks linked to this ticket.
        </div>
      </div>

      <!-- ─ Checklists tab (placeholder) ─────────────────── -->
      <div class="tv-tab-pane" id="tv-tab-checklists">
        <div class="tv-empty">
          <i class="fas fa-clipboard-check" style="font-size:1.8rem;opacity:.3;display:block;margin-bottom:10px;"></i>
          No checklists attached.
        </div>
      </div>

      <!-- ─ Resolution tab (placeholder) ─────────────────── -->
      <div class="tv-tab-pane" id="tv-tab-resolution">
        <div class="tv-empty">
          <i class="fas fa-circle-check" style="font-size:1.8rem;opacity:.3;display:block;margin-bottom:10px;"></i>
          No resolution recorded yet.
        </div>
      </div>

      <!-- ─ History tab ───────────────────────────────────── -->
      <div class="tv-tab-pane" id="tv-tab-history">
        <div class="tv-timeline-wrap">
          <?php if (empty($activities ?? [])): ?>
            <div class="tv-empty" style="padding:20px 0;">No activity recorded yet.</div>
          <?php else: ?>
            <?php foreach ($activities as $activity): ?>
            <div class="tv-tl-item">
              <div class="tv-tl-dot"><i class="fas fa-circle-dot"></i></div>
              <div>
                <div class="tv-tl-text"><?= htmlspecialchars($activity['description'] ?? 'Activity updated.') ?></div>
                <div class="tv-tl-meta">
                  <?= !empty($activity['actor_name']) ? htmlspecialchars($activity['actor_name']) . ' · ' : '' ?>
                  <?= !empty($activity['created_at']) ? date('M j, Y h:i A', strtotime($activity['created_at'])) : '' ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

    </div><!-- /.tv-main -->

    <!-- ─── Sidebar ──────────────────────────────────────────── -->
    <aside class="tv-sidebar">

      <!-- Properties section -->
      <div class="tv-sb-section">
        <div class="tv-sb-sec-head" onclick="tvToggle(this)">
          <span class="t">Properties</span>
          <i class="fas fa-chevron-down arr"></i>
        </div>
        <div class="tv-sb-sec-body">
          <div class="tv-prop-row">
            <span class="tv-prop-key">Request ID</span>
            <span class="tv-prop-val"><?= htmlspecialchars($formattedTicketId) ?></span>
          </div>
          <div class="tv-prop-row">
            <span class="tv-prop-key">Status</span>
            <span class="tv-prop-val" style="color:<?= $statusColor ?>;">
              <?= ucwords(str_replace('_', ' ', $ticket['status'])) ?>
            </span>
          </div>
          <div class="tv-prop-row">
            <span class="tv-prop-key">Life cycle</span>
            <span class="tv-prop-val"><?= htmlspecialchars($lifecycleLabel) ?></span>
          </div>
          <div class="tv-prop-row">
            <span class="tv-prop-key">Technician</span>
            <span class="tv-prop-val"><?= htmlspecialchars($ticket['agent_name'] ?? 'Not Assigned') ?></span>
          </div>
          <div class="tv-prop-row">
            <span class="tv-prop-key">Group &amp; Site</span>
            <span class="tv-prop-val" style="font-size:.76rem;">
              <?= htmlspecialchars(($ticket['group_name'] ?? 'Support') . ', ' . ($ticket['category_name'] ?? 'General')) ?>
            </span>
          </div>
          <div class="tv-prop-row">
            <span class="tv-prop-key">Tasks</span>
            <span class="tv-prop-val">0/0</span>
          </div>
          <div class="tv-prop-row">
            <span class="tv-prop-key">Checklists</span>
            <span class="tv-prop-val">0/0</span>
          </div>
          <div class="tv-prop-row">
            <span class="tv-prop-key">Reminders</span>
            <span class="tv-prop-val">0</span>
          </div>
          <div class="tv-prop-row">
            <span class="tv-prop-key">Approval Status</span>
            <span class="tv-prop-val"><span class="tv-notcfg-badge">Not Configured</span></span>
          </div>
          <div class="tv-prop-row">
            <span class="tv-prop-key">Attachments</span>
            <span class="tv-prop-val"><i class="fas fa-paperclip" style="margin-right:4px;font-size:.75rem;"></i>0</span>
          </div>
          <div class="tv-prop-row">
            <span class="tv-prop-key">Due By</span>
            <span class="tv-prop-val" style="color:var(--text-secondary,#8892a6);">No due date set</span>
          </div>
          <div class="tv-prop-row">
            <span class="tv-prop-key">Worklog Timer</span>
            <span class="tv-prop-val"><i class="fas fa-clock" style="font-size:.85rem;color:var(--text-secondary,#8892a6);"></i></span>
          </div>
        </div>
      </div>

      <!-- Associations section -->
      <div class="tv-sb-section">
        <div class="tv-sb-sec-head" onclick="tvToggle(this)">
          <span class="t">Associations</span>
          <i class="fas fa-chevron-down arr"></i>
        </div>
        <div class="tv-sb-sec-body">
          <div class="tv-assoc-row">
            <span class="tv-assoc-label">Linked Requests</span>
            <button class="tv-assoc-btn">Attach</button>
          </div>
          <div class="tv-assoc-row">
            <span class="tv-assoc-label">Tags</span>
            <button class="tv-assoc-btn">Add</button>
          </div>
        </div>
      </div>

      <!-- Requester Details section -->
      <div class="tv-sb-section">
        <div class="tv-sb-sec-head" onclick="tvToggle(this)">
          <span class="t">Requester Details</span>
          <i class="fas fa-chevron-down arr"></i>
        </div>
        <div class="tv-sb-sec-body">
          <div class="tv-requester-card">
            <div class="tv-req-avatar"><?= htmlspecialchars($requesterInitials) ?></div>
            <div>
              <div class="tv-req-name"><?= htmlspecialchars($requesterName) ?></div>
              <?php if ($requesterEmail): ?>
              <div class="tv-req-email"><?= htmlspecialchars($requesterEmail) ?></div>
              <?php endif; ?>
              <a class="tv-req-link">
                View Full Details <i class="fas fa-chevron-down" style="font-size:.6rem;"></i>
              </a>
            </div>
          </div>
          <div class="tv-req-actions">
            <div class="tv-req-act-card">
              <i class="fas fa-box"></i>
              <span>Assets Owned</span>
            </div>
            <div class="tv-req-act-card">
              <i class="fas fa-ticket"></i>
              <span>Previous Requests</span>
            </div>
          </div>
        </div>
      </div>

    </aside><!-- /.tv-sidebar -->
  </div><!-- /.tv-body -->

</div><!-- /.tv-page -->

<script>
// Tab switching
document.querySelectorAll('.tv-tab-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var key = btn.dataset.tab;
        document.querySelectorAll('.tv-tab-btn').forEach(function(el) { el.classList.remove('active'); });
        document.querySelectorAll('.tv-tab-pane').forEach(function(el) { el.classList.remove('active'); });
        btn.classList.add('active');
        var pane = document.getElementById('tv-tab-' + key);
        if (pane) pane.classList.add('active');
    });
});

// Composer tabs (Reply / Internal Note)
document.querySelectorAll('.tv-comp-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tv-comp-tab').forEach(function(el) { el.classList.remove('active'); });
        btn.classList.add('active');
        var isNote = btn.dataset.comp === 'note';
        var internalInput = document.getElementById('tv-is-internal');
        if (internalInput) internalInput.value = isNote ? '1' : '0';
        var txt = document.getElementById('tv-reply-txt');
        if (txt) txt.placeholder = isNote ? 'Add an internal note (not visible to requester)...' : 'Type your reply here...';
    });
});

// Sidebar collapsible sections
function tvToggle(head) {
    head.classList.toggle('collapsed');
    var body = head.nextElementSibling;
    if (body) {
        var collapsed = head.classList.contains('collapsed');
        body.style.display = collapsed ? 'none' : '';
        head.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    }
}
</script>

<?php View::endSection(); ?>
