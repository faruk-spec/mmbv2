<?php
/**
 * Support Admin — Live Chats (within support portal)
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

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;">
            <h1 style="font-size:1.35rem;font-weight:700;color:var(--text-primary);margin:0;display:flex;align-items:center;gap:10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color:var(--cyan)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Live Chats
            </h1>
        </div>

        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;overflow:hidden;">
            <?php if (empty($chats)): ?>
            <div style="padding:50px 20px;text-align:center;color:var(--text-secondary);">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:.25;display:block;margin:0 auto 12px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                No active live chats.
            </div>
            <?php else: ?>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--border-color);">
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary);font-size:.71rem;font-weight:600;text-transform:uppercase;">#</th>
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary);font-size:.71rem;font-weight:600;text-transform:uppercase;">User / Guest</th>
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary);font-size:.71rem;font-weight:600;text-transform:uppercase;">Status</th>
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary);font-size:.71rem;font-weight:600;text-transform:uppercase;">Agent</th>
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary);font-size:.71rem;font-weight:600;text-transform:uppercase;">Started</th>
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary);font-size:.71rem;font-weight:600;text-transform:uppercase;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($chats as $c): ?>
                        <tr style="border-bottom:1px solid var(--border-color,rgba(255,255,255,.04));transition:background .15s;" onmouseover="this.style.background='rgba(255,255,255,.02)'" onmouseout="this.style.background=''">
                            <td style="padding:12px 14px;font-family:monospace;font-size:.8rem;color:var(--text-secondary);">#<?= (int)$c['id'] ?></td>
                            <td style="padding:12px 14px;font-size:.88rem;color:var(--text-primary);"><?= htmlspecialchars($c['user_name'] ?? ($c['guest_name'] ?: 'Guest')) ?></td>
                            <td style="padding:12px 14px;">
                                <?php if ($c['status'] === 'active'): ?>
                                <span style="display:inline-flex;align-items:center;gap:5px;font-size:.78rem;font-weight:600;color:var(--green);"><span style="width:6px;height:6px;border-radius:50%;background:var(--green);"></span>Active</span>
                                <?php else: ?>
                                <span style="font-size:.78rem;font-weight:600;color:var(--text-secondary);">Closed</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:12px 14px;font-size:.83rem;color:var(--text-secondary);"><?= htmlspecialchars($c['agent_name'] ?? '—') ?></td>
                            <td style="padding:12px 14px;font-size:.78rem;color:var(--text-secondary);"><?= date('M j, H:i', strtotime($c['created_at'])) ?></td>
                            <td style="padding:12px 14px;">
                                <a href="/admin/support/live-chats/<?= (int)$c['id'] ?>" style="padding:4px 10px;background:color-mix(in srgb,var(--cyan) 10%,transparent);color:var(--cyan);border-radius:5px;text-decoration:none;font-size:.76rem;font-weight:500;">Manage</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div><!-- /main content -->
</div><!-- /support flex wrapper -->

<?php View::endSection(); ?>
