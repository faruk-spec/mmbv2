<?php
/**
 * Support Admin — Reports (within support portal)
 */
use Core\View;
View::extend('main');
?>

<?php View::section('styles'); ?>
<style>.dashboard-main-content { padding: 0 !important; }</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div style="display:flex;min-height:calc(100vh - 64px);align-items:stretch;">
    <?php include dirname(__DIR__) . '/_sidebar.php'; ?>
    <div style="flex:1;padding:24px 28px;min-width:0;overflow:auto;">

        <h1 style="font-size:1.35rem;font-weight:700;color:var(--text-primary,#e8eefc);margin:0 0 22px;display:flex;align-items:center;gap:10px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00f0ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            Reports & Analytics
        </h1>

        <!-- Stats grid -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:26px;">
            <?php foreach ([
                ['open','#00f0ff','Open Tickets'],
                ['in_progress','#ff9f43','In Progress'],
                ['waiting_customer','#a78bfa','Awaiting Reply'],
                ['resolved','#00ff88','Resolved'],
                ['closed','#8892a6','Closed'],
                ['total','#e8eefc','Total Tickets'],
            ] as [$k,$c,$l]): ?>
            <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.07));border-radius:10px;padding:18px 20px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:0;left:0;right:0;height:3px;background:<?= $c ?>;opacity:.4;"></div>
                <div style="font-size:2rem;font-weight:700;color:<?= $c ?>;"><?= (int)($stats[$k] ?? 0) ?></div>
                <div style="color:var(--text-secondary,#8892a6);font-size:.8rem;margin-top:4px;"><?= $l ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Resolution rate -->
        <?php
        $total    = (int)($stats['total'] ?? 0);
        $resolved = (int)($stats['resolved'] ?? 0) + (int)($stats['closed'] ?? 0);
        $rate     = $total > 0 ? round(($resolved / $total) * 100) : 0;
        ?>
        <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.07));border-radius:12px;padding:22px 24px;margin-bottom:20px;">
            <div style="font-weight:600;color:var(--text-primary,#e8eefc);font-size:.95rem;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#00ff88" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Resolution Rate
            </div>
            <div style="display:flex;align-items:center;gap:16px;">
                <div style="flex:1;background:rgba(255,255,255,.06);border-radius:8px;height:10px;overflow:hidden;">
                    <div style="height:100%;width:<?= $rate ?>%;background:linear-gradient(90deg,#00f0ff,#00ff88);border-radius:8px;transition:width .5s;"></div>
                </div>
                <div style="font-size:1.5rem;font-weight:700;color:#00ff88;min-width:60px;text-align:right;"><?= $rate ?>%</div>
            </div>
            <div style="color:var(--text-secondary,#8892a6);font-size:.8rem;margin-top:8px;"><?= $resolved ?> of <?= $total ?> tickets resolved or closed</div>
        </div>

        <div style="padding:20px 24px;background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.07));border-radius:12px;color:var(--text-secondary,#8892a6);font-size:.85rem;text-align:center;">
            Advanced analytics (response times, SLA tracking, agent performance) — coming soon.
        </div>
    </div><!-- /main content -->
</div><!-- /support flex wrapper -->

<?php View::endSection(); ?>
