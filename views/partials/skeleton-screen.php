<?php
/**
 * Skeleton Screen Partial
 *
 * Renders a full-page shimmer overlay that mirrors the actual layout chrome
 * (sidebar / topbar / content area). It shows immediately on page paint and
 * is removed by JavaScript on DOMContentLoaded so the real content is never
 * "invisible", only covered briefly during the browser's parse/paint cycle.
 *
 * Usage:
 *   $skeletonType = 'admin' | 'main'   (set before including)
 *
 * Context-aware content skeleton is chosen automatically from REQUEST_URI.
 */

$_skType = $skeletonType ?? 'main';
$_skUri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// ── Decide content-area variant ──────────────────────────────────────────
// 'stats'  – 4 stat tiles + table rows  (admin/user dashboard)
// 'table'  – page-title bar + filter + table rows
// 'form'   – page-title bar + card with form groups
// 'grid'   – icon + name cards (apps grid, module list)
// 'detail' – single wide card with label-value rows

function _skMatch(string $uri, array $prefixes): bool {
    foreach ($prefixes as $p) {
        if (str_starts_with($uri, $p)) return true;
    }
    return false;
}

if ($_skType === 'admin') {
    if (_skMatch($_skUri, ['/admin/dashboard', '/admin'])) {
        $_skContent = 'stats';
    } elseif (_skMatch($_skUri, [
        '/admin/users', '/admin/roles', '/admin/audit', '/admin/logs',
        '/admin/sessions', '/admin/security', '/admin/support/tickets',
        '/admin/support/live-chats', '/admin/support/users', '/admin/pages',
        '/admin/notifications', '/admin/email/queue', '/admin/email/templates',
        '/admin/api/logs', '/admin/api/keys', '/admin/api/rate-limits',
        '/admin/projects/billx/bills', '/admin/projects/convertx/jobs',
        '/admin/projects/proshare/files', '/admin/projects/whatsapp/messages',
        '/admin/projects/whatsapp/sessions', '/admin/analytics',
        '/admin/platform-plans', '/admin/qr',
    ])) {
        $_skContent = 'table';
    } elseif (_skMatch($_skUri, [
        '/admin/settings', '/admin/projects/convertx/settings',
        '/admin/projects/proshare/settings', '/admin/projects/whatsapp',
        '/admin/projects/codexpro/settings', '/admin/projects/billx/settings',
        '/admin/projects/idcard/settings', '/admin/projects/resumex/settings',
        '/admin/support/settings', '/admin/mail/', '/admin/oauth',
        '/admin/websocket', '/admin/navbar', '/admin/2fa', '/admin/performance',
    ])) {
        $_skContent = 'form';
    } elseif (_skMatch($_skUri, [
        '/admin/projects',
    ])) {
        $_skContent = 'grid';
    } else {
        $_skContent = 'table'; // sensible default for admin
    }
} else {
    if (_skMatch($_skUri, ['/dashboard'])) {
        $_skContent = 'grid';
    } elseif (_skMatch($_skUri, ['/profile', '/security', '/settings', '/2fa'])) {
        $_skContent = 'form';
    } elseif (_skMatch($_skUri, ['/support', '/mail', '/notifications'])) {
        $_skContent = 'table';
    } else {
        $_skContent = 'grid';
    }
}
?>
<?php /* ──────────────────────────────────────────────────────────────────────
   SKELETON OVERLAY
   z-index 500000 so it sits above real page content until JS removes it.
   We intentionally do NOT use display:none initially — the skeleton is shown
   at parse time and JS hides it (never-FOUC).
─────────────────────────────────────────────────────────────────────────── */ ?>
<style>
/* Light-mode background override for skeleton overlay */
[data-theme="light"] #mmb-skeleton-screen {
    background: #f0f2f5 !important;
}
[data-theme="light"] #mmb-skeleton-screen .skeleton {
    background: linear-gradient(90deg,
        rgba(0,0,0,.06) 25%,
        rgba(0,0,0,.12) 50%,
        rgba(0,0,0,.06) 75%) !important;
    background-size: 200% 100% !important;
}
[data-theme="light"] #mmb-skeleton-screen > div > div,
[data-theme="light"] #mmb-skeleton-screen [style*="var(--bg-secondary"] {
    background: #fff !important;
    border-color: rgba(0,0,0,.08) !important;
}
</style>
<div id="mmb-skeleton-screen" aria-hidden="true" style="
    position:fixed;inset:0;z-index:500000;
    display:flex;overflow:hidden;
    background:var(--bg-primary,#06060a);
    pointer-events:none;">

<?php if ($_skType === 'admin'): ?>
    <!-- ── Admin: left sidebar skeleton ──── -->
    <div style="width:250px;min-width:250px;height:100%;background:var(--bg-secondary,#0c0c12);
                border-right:1px solid rgba(255,255,255,.07);padding:16px 12px;flex-shrink:0;
                display:flex;flex-direction:column;gap:10px;">
        <!-- Logo bar -->
        <div class="skeleton" style="height:36px;border-radius:8px;margin-bottom:14px;"></div>
        <!-- Nav groups -->
        <?php for ($g = 0; $g < 4; $g++): ?>
        <div class="skeleton" style="height:10px;width:60%;border-radius:4px;margin-top:14px;margin-bottom:6px;opacity:.5;"></div>
        <?php for ($i = 0; $i < ($g === 0 ? 1 : 3); $i++): ?>
        <div style="display:flex;align-items:center;gap:10px;padding:6px 8px;">
            <div class="skeleton" style="width:16px;height:16px;border-radius:4px;flex-shrink:0;"></div>
            <div class="skeleton" style="height:12px;flex:1;border-radius:4px;"></div>
        </div>
        <?php endfor; ?>
        <?php endfor; ?>
    </div>

    <!-- ── Admin: topbar + content column ── -->
    <div style="flex:1;display:flex;flex-direction:column;overflow:hidden;">
        <!-- Topbar -->
        <div style="height:60px;background:var(--bg-secondary,#0c0c12);border-bottom:1px solid rgba(255,255,255,.07);
                    display:flex;align-items:center;padding:0 24px;gap:16px;flex-shrink:0;">
            <div class="skeleton" style="height:20px;width:160px;border-radius:6px;"></div>
            <div style="flex:1;"></div>
            <div class="skeleton" style="height:32px;width:200px;border-radius:8px;"></div>
            <div class="skeleton" style="width:32px;height:32px;border-radius:50%;"></div>
            <div class="skeleton" style="width:80px;height:32px;border-radius:8px;"></div>
        </div>

        <!-- Content area -->
        <div style="flex:1;padding:28px 28px;overflow:hidden;">
            <?php include __DIR__ . '/skeleton-content.php'; ?>
        </div>
    </div>

<?php else: /* main layout */ ?>
    <!-- ── Main: navbar skeleton ─────────── -->
    <div style="position:absolute;top:0;left:0;right:0;z-index:1;
                height:70px;background:var(--bg-secondary,#0c0c12);
                border-bottom:1px solid rgba(255,255,255,.07);
                display:flex;align-items:center;padding:0 32px;gap:16px;">
        <div class="skeleton" style="height:28px;width:130px;border-radius:8px;"></div>
        <div style="flex:1;"></div>
        <div class="skeleton" style="height:28px;width:70px;border-radius:6px;"></div>
        <div class="skeleton" style="height:28px;width:70px;border-radius:6px;"></div>
        <div class="skeleton" style="width:34px;height:34px;border-radius:50%;"></div>
    </div>

    <!-- Content area (below navbar) -->
    <div style="position:absolute;top:70px;left:0;right:0;bottom:0;
                padding:28px 32px;overflow:hidden;">
        <?php include __DIR__ . '/skeleton-content.php'; ?>
    </div>
<?php endif; ?>

</div><!-- /#mmb-skeleton-screen -->

<script>
(function(){
    var sk = document.getElementById('mmb-skeleton-screen');
    if (!sk) return;

    function hideSkeleton() {
        sk.style.transition = 'opacity .25s ease';
        sk.style.opacity = '0';
        setTimeout(function(){ if (sk.parentNode) sk.parentNode.removeChild(sk); }, 280);
    }

    // Hide as soon as DOM is ready (content is server-rendered so it's already there)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', hideSkeleton);
    } else {
        // DOMContentLoaded already fired (possible for cached pages / bfcache)
        hideSkeleton();
    }

    // Absolute safety-net: remove after 3 s even if DOMContentLoaded never fires
    setTimeout(hideSkeleton, 3000);
})();
</script>
