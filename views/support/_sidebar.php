<?php
/**
 * Support Portal Left Sidebar
 * Variables available: $currentPage (string), $isSupportAdmin (bool)
 */
use Core\Auth;

$page = $currentPage ?? '';
$isAdmin = $isSupportAdmin ?? false;

// Helper: nav item HTML
function supportNavItem(string $href, string $icon, string $label, bool $active, string $badge = ''): string {
    $bg     = $active ? 'background:linear-gradient(135deg,rgba(0,240,255,.12),rgba(255,46,196,.08));border-color:rgba(0,240,255,.25);' : '';
    $color  = $active ? 'color:#00f0ff;' : 'color:var(--text-secondary,#8892a6);';
    $weight = $active ? 'font-weight:600;' : 'font-weight:400;';
    $badgeHtml = $badge ? "<span style=\"margin-left:auto;padding:1px 7px;border-radius:10px;font-size:.68rem;font-weight:700;background:rgba(255,46,196,.2);color:#ff2ec4;\">{$badge}</span>" : '';
    return "<a href=\"{$href}\" style=\"display:flex;align-items:center;gap:10px;padding:9px 14px;border-radius:8px;text-decoration:none;font-size:.855rem;transition:all .15s;border:1px solid transparent;{$bg}{$color}{$weight}\"><i class=\"fas fa-{$icon}\" style=\"width:16px;text-align:center;\"></i><span>{$label}</span>{$badgeHtml}</a>";
}
function supportNavSection(string $label): string {
    return "<div style=\"padding:10px 14px 4px;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-secondary,#5c6478);\">{$label}</div>";
}
?>
<aside style="width:220px;flex-shrink:0;">
    <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:14px;overflow:hidden;position:sticky;top:20px;">

        <!-- Brand header -->
        <div style="padding:18px 16px 14px;border-bottom:1px solid var(--border-color,rgba(255,255,255,.06));">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <div>
                    <div style="font-weight:700;font-size:.9rem;color:var(--text-primary,#e8eefc);">Support</div>
                    <div style="font-size:.7rem;color:var(--text-secondary,#8892a6);"><?= $isAdmin ? 'Agent Portal' : 'Help Center' ?></div>
                </div>
            </div>
        </div>

        <div style="padding:10px 8px;display:flex;flex-direction:column;gap:2px;">

        <?php if ($isAdmin): ?>
            <?= supportNavSection('Overview') ?>
            <?= supportNavItem('/admin/support', 'gauge', 'Dashboard', $page === 'admin_dashboard') ?>
            <?= supportNavItem('/admin/support/tickets', 'ticket', 'All Requests', $page === 'admin_tickets') ?>
            <?= supportNavItem('/admin/support/tickets?status=open', 'folder-open', 'Open', $page === 'admin_open') ?>
            <?= supportNavItem('/admin/support/tickets?status=in_progress', 'spinner', 'In Progress', $page === 'admin_inprogress') ?>
            <?= supportNavItem('/admin/support/tickets?status=resolved', 'check-circle', 'Resolved', $page === 'admin_resolved') ?>
            <?= supportNavItem('/admin/support/tickets?status=closed', 'archive', 'Closed', $page === 'admin_closed') ?>

            <?= supportNavSection('Communication') ?>
            <?= supportNavItem('/admin/support/live-chats', 'comments', 'Live Chats', $page === 'admin_live') ?>
            <?= supportNavItem('/support/faq', 'circle-question', 'FAQ', $page === 'faq') ?>
            <?= supportNavItem('/support/help', 'book-open', 'Knowledge Base', $page === 'help') ?>

            <?= supportNavSection('Management') ?>
            <?= supportNavItem('/admin/support/templates', 'folder-tree', 'Templates', $page === 'admin_templates') ?>
            <?= supportNavItem('/admin/support/users', 'user-shield', 'Agents & Users', $page === 'admin_users') ?>

        <?php else: ?>
            <?= supportNavSection('My Support') ?>
            <?= supportNavItem('/support', 'gauge', 'Dashboard', $page === 'dashboard') ?>
            <?= supportNavItem('/support', 'ticket', 'My Tickets', $page === 'tickets') ?>
            <?= supportNavItem('/support/create', 'plus-circle', 'Create Ticket', $page === 'create') ?>

            <?= supportNavSection('Resources') ?>
            <?= supportNavItem('/support/faq', 'circle-question', 'FAQ', $page === 'faq') ?>
            <?= supportNavItem('/support/live', 'headset', 'Live Support', $page === 'live') ?>
            <?= supportNavItem('/support/help', 'book-open', 'Help & Resources', $page === 'help') ?>
            <?= supportNavItem('/support/announcements', 'bullhorn', 'Announcements', $page === 'announcements') ?>
        <?php endif; ?>

        </div>

        <!-- Status indicator -->
        <div style="padding:10px 14px 14px;border-top:1px solid var(--border-color,rgba(255,255,255,.06));margin-top:4px;">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:7px;height:7px;border-radius:50%;background:#00ff88;box-shadow:0 0 6px #00ff88;"></div>
                <span style="font-size:.72rem;color:var(--text-secondary,#8892a6);">Support is online</span>
            </div>
        </div>
    </div>
</aside>
