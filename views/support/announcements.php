<?php
/**
 * Announcements Page
 */
use Core\View;
View::extend('main');
?>

<?php View::section('styles'); ?>
<style>.dashboard-main-content { padding: 0 !important; }</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div style="display:flex;min-height:calc(100vh - 64px);align-items:stretch;">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <div style="flex:1;padding:24px 28px;min-width:0;">
            <div style="margin-bottom:22px;">
                <h1 style="font-size:1.4rem;font-weight:700;color:var(--text-primary);margin:0 0 4px;display:flex;align-items:center;gap:10px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color:var(--magenta)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
                    Announcements
                </h1>
                <p style="color:var(--text-secondary);margin:0;font-size:.85rem;">Platform updates, maintenance notices, and outage reports.</p>
            </div>

            <?php
            // Placeholder announcements — in future, these will come from the database
            $announcements = [
                [
                    'type'    => 'update',
                    'color'   => '#00f0ff',
                    'icon'    => 'sparkles',
                    'title'   => 'Support Portal Launched',
                    'date'    => 'April 2025',
                    'body'    => 'We have launched the new Support Portal with live chat, ticket management, and an improved help center. Submit your questions directly through the portal for faster responses.',
                ],
                [
                    'type'    => 'maintenance',
                    'color'   => '#ff9f43',
                    'icon'    => 'wrench',
                    'title'   => 'Scheduled Maintenance',
                    'date'    => 'Upcoming',
                    'body'    => 'We periodically perform scheduled maintenance to improve system reliability. Any planned maintenance windows will be posted here in advance.',
                ],
                [
                    'type'    => 'info',
                    'color'   => '#a78bfa',
                    'icon'    => 'circle-info',
                    'title'   => 'No Current Outages',
                    'date'    => 'Status',
                    'body'    => 'All systems are currently operational. If you experience any issues, please create a support ticket or use live chat.',
                ],
            ];
            ?>

            <div style="display:flex;flex-direction:column;gap:12px;">
                <?php foreach ($announcements as $ann): ?>
                <div style="background:var(--bg-card);border:1px solid <?= $ann['color'] ?>33;border-left:3px solid <?= $ann['color'] ?>;border-radius:10px;padding:18px 20px;">
                    <div style="display:flex;align-items:flex-start;gap:12px;">
                        <div style="width:36px;height:36px;border-radius:9px;background:<?= $ann['color'] ?>18;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <i class="fas fa-<?= $ann['icon'] ?>" style="color:<?= $ann['color'] ?>;font-size:.9rem;"></i>
                        </div>
                        <div style="flex:1;">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;flex-wrap:wrap;">
                                <span style="font-weight:700;color:var(--text-primary);font-size:.92rem;"><?= htmlspecialchars($ann['title']) ?></span>
                                <span style="padding:2px 8px;border-radius:10px;font-size:.7rem;font-weight:600;background:<?= $ann['color'] ?>22;color:<?= $ann['color'] ?>"><?= htmlspecialchars($ann['date']) ?></span>
                            </div>
                            <p style="color:var(--text-secondary);font-size:.87rem;margin:0;line-height:1.6;"><?= htmlspecialchars($ann['body']) ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div><!-- /main content -->
</div><!-- /support flex wrapper -->

<?php View::endSection(); ?>
