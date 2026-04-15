<?php
/**
 * Support Portal Left Sidebar
 * Variables available: $currentPage (string), $isSupportAdmin (bool)
 */

$page = $currentPage ?? '';
$isAdmin = $isSupportAdmin ?? false;

// Helper: nav item HTML
if (!function_exists('supportNavItem')) {
    function supportNavItem(string $href, string $icon, string $label, bool $active, string $badge = ''): string {
        $bg     = $active ? 'background:linear-gradient(135deg,rgba(0,240,255,.12),rgba(255,46,196,.08));border-color:rgba(0,240,255,.25);' : '';
        $color  = $active ? 'color:var(--cyan);' : 'color:var(--text-secondary);';
        $weight = $active ? 'font-weight:600;' : 'font-weight:400;';
        $badgeHtml = $badge ? "<span style=\"margin-left:auto;padding:1px 7px;border-radius:10px;font-size:.68rem;font-weight:700;background:rgba(255,46,196,.2);color:var(--magenta);\">{$badge}</span>" : '';
        return "<a href=\"{$href}\" style=\"display:flex;align-items:center;gap:10px;padding:9px 14px;border-radius:8px;text-decoration:none;font-size:.855rem;transition:all .15s;border:1px solid transparent;{$bg}{$color}{$weight}\"><i class=\"fas fa-{$icon}\" style=\"width:16px;text-align:center;\"></i><span>{$label}</span>{$badgeHtml}</a>";
    }
}
if (!function_exists('supportNavSection')) {
    function supportNavSection(string $label): string {
        return "<div style=\"padding:10px 14px 4px;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-secondary,#5c6478);\">{$label}</div>";
    }
}
?>
<aside style="width:240px;min-width:240px;background:var(--bg-primary,#08080f);border-right:1px solid var(--border-color);display:flex;flex-direction:column;min-height:calc(100vh - 64px);position:sticky;top:64px;height:calc(100vh - 64px);overflow-y:auto;z-index:10;">

    <!-- Brand header -->
    <div style="padding:18px 16px 14px;border-bottom:1px solid var(--border-color,rgba(255,255,255,.06));">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,var(--cyan),var(--magenta));display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div>
                <div style="font-weight:700;font-size:.9rem;color:var(--text-primary);">Support</div>
                <div style="font-size:.7rem;color:var(--text-secondary);"><?= $isAdmin ? 'Agent Portal' : 'Help Center' ?></div>
            </div>
        </div>
    </div>

    <div style="padding:10px 8px;display:flex;flex-direction:column;gap:2px;flex:1;">

    <?php if ($isAdmin): ?>
        <?= supportNavSection('Overview') ?>
        <?= supportNavItem('/support', 'gauge', 'Dashboard', $page === 'tickets' || $page === 'admin_dashboard') ?>
        <?= supportNavItem('/support/admin/tickets', 'ticket', 'All Requests', $page === 'admin_tickets') ?>

        <?= supportNavSection('By Status') ?>
        <?= supportNavItem('/support/admin/tickets?status=open', 'folder-open', 'Open', $page === 'admin_open') ?>
        <?= supportNavItem('/support/admin/tickets?status=in_progress', 'spinner', 'In Progress', $page === 'admin_inprogress') ?>
        <?= supportNavItem('/support/admin/tickets?status=resolved', 'check-circle', 'Resolved', $page === 'admin_resolved') ?>
        <?= supportNavItem('/support/admin/tickets?status=closed', 'archive', 'Closed', $page === 'admin_closed') ?>

        <?= supportNavSection('Communication') ?>
        <?= supportNavItem('/support/admin/live', 'comments', 'Live Chats', $page === 'admin_live') ?>
        <?= supportNavItem('/support/faq', 'circle-question', 'FAQ', $page === 'faq') ?>

        <?= supportNavSection('Management') ?>
        <?= supportNavItem('/admin/support/templates', 'folder-tree', 'Templates', $page === 'admin_templates') ?>
        <?= supportNavItem('/admin/support/users', 'user-shield', 'Agents & Users', $page === 'admin_users') ?>

        <?= supportNavSection('Reports') ?>
        <?= supportNavItem('/support/admin/reports', 'chart-bar', 'Reports', $page === 'admin_reports') ?>

    <?php else: ?>
        <?= supportNavSection('My Support') ?>
        <?= supportNavItem('/support', 'gauge', 'Dashboard', $page === 'tickets' || $page === 'dashboard') ?>
        <?= supportNavItem('/support/create', 'plus-circle', 'Create Ticket', $page === 'create') ?>

        <?= supportNavSection('Resources') ?>
        <?= supportNavItem('/support/faq', 'circle-question', 'FAQ', $page === 'faq') ?>
        <?= supportNavItem('/support/live', 'headset', 'Live Support', $page === 'live') ?>
        <?= supportNavItem('/support/help', 'book-open', 'Help & Resources', $page === 'help') ?>
        <?= supportNavItem('/support/announcements', 'bullhorn', 'Announcements', $page === 'announcements') ?>
    <?php endif; ?>

    </div>

    <!-- Status indicator -->
    <div style="padding:10px 14px 14px;border-top:1px solid var(--border-color,rgba(255,255,255,.06));">
        <div style="display:flex;align-items:center;gap:8px;">
            <div style="width:7px;height:7px;border-radius:50%;background:var(--green);box-shadow:0 0 6px var(--green);animation:sbPulse 2s infinite;"></div>
            <span style="font-size:.72rem;color:var(--text-secondary);">Support is online</span>
        </div>
    </div>
</aside>
<style>@keyframes sbPulse{0%,100%{opacity:1}50%{opacity:.4}}</style>
