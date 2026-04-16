<?php
/**
 * Admin Live Chats List
 */
use Core\View;

View::extend('admin');
View::section('content');
?>

<div style="padding:28px;">
    <div style="margin-bottom:24px;">
        <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-primary,#e8eefc);margin:0 0 4px;">
            <i class="fas fa-comments" style="color:#ff2ec4;margin-right:10px;"></i>Live Chats
        </h1>
        <p style="color:var(--text-secondary,#8892a6);margin:0;font-size:.85rem;">
            <?= count(array_filter($chats, fn($c) => $c['status'] === 'active')) ?> active &bull; <?= count($chats) ?> total
        </p>
    </div>

    <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;overflow:hidden;">
        <?php if (empty($chats)): ?>
        <div style="padding:60px;text-align:center;color:var(--text-secondary,#8892a6);">
            <i class="fas fa-comments" style="font-size:2rem;opacity:.3;display:block;margin-bottom:12px;"></i>
            No chat sessions found.
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border-color,rgba(255,255,255,.08));">
                        <?php foreach (['#','User / Guest','Status','Assigned Agent','Started','Actions'] as $h): ?>
                        <th style="padding:12px 14px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;"><?= $h ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chats as $chat):
                        $isActive   = ($chat['status'] === 'active');
                        $sc         = $isActive ? '#00ff88' : '#8892a6';
                        $userName   = $chat['user_name'] ?? ($chat['guest_name'] ? $chat['guest_name'].' (guest)' : 'Guest');
                    ?>
                    <tr style="border-bottom:1px solid var(--border-color,rgba(255,255,255,.04));transition:background .15s;" onmouseover="this.style.background='rgba(255,255,255,.02)'" onmouseout="this.style.background=''">
                        <td style="padding:12px 14px;color:var(--text-secondary,#8892a6);font-size:.82rem;">#<?= (int)$chat['id'] ?></td>
                        <td style="padding:12px 14px;">
                            <div style="color:var(--text-primary,#e8eefc);font-size:.88rem;font-weight:500;"><?= htmlspecialchars($userName) ?></div>
                            <?php if (!empty($chat['guest_email'])): ?>
                            <div style="color:var(--text-secondary,#8892a6);font-size:.75rem;"><?= htmlspecialchars($chat['guest_email']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="padding:12px 14px;">
                            <span style="padding:3px 10px;border-radius:20px;font-size:.73rem;font-weight:600;background:<?= $sc ?>1a;color:<?= $sc ?>">
                                <?= ucfirst($chat['status']) ?>
                            </span>
                        </td>
                        <td style="padding:12px 14px;color:var(--text-secondary,#8892a6);font-size:.85rem;">
                            <?= htmlspecialchars($chat['agent_name'] ?? '—') ?>
                        </td>
                        <td style="padding:12px 14px;color:var(--text-secondary,#8892a6);font-size:.8rem;">
                            <?= date('M j, Y H:i', strtotime($chat['created_at'])) ?>
                        </td>
                        <td style="padding:12px 14px;">
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                <a href="/admin/support/live-chats/<?= (int)$chat['id'] ?>" style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:rgba(255,46,196,.1);color:#ff2ec4;border-radius:6px;text-decoration:none;font-size:.78rem;font-weight:500;">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <?php if (!$isActive): ?>
                                <form method="POST" action="/admin/support/live-chats/<?= (int)$chat['id'] ?>/reopen" style="display:inline;">
                                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                    <button type="submit" style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:rgba(0,255,136,.1);color:#00ff88;border:1px solid rgba(0,255,136,.2);border-radius:6px;font-size:.78rem;font-weight:500;cursor:pointer;">
                                        <i class="fas fa-redo"></i> Reopen
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php View::endSection(); ?>
