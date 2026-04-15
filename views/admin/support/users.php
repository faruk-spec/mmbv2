<?php
/**
 * Admin Support Users
 */
use Core\View;

View::extend('admin');
View::section('content');
?>

<div style="padding:28px;">
    <div style="margin-bottom:24px;">
        <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-primary,#e8eefc);margin:0 0 4px;">
            <i class="fas fa-users" style="color:#00f0ff;margin-right:10px;"></i>Support Users
        </h1>
        <p style="color:var(--text-secondary,#8892a6);margin:0;font-size:.85rem;">Users who have submitted tickets or initiated live chats.</p>
    </div>

    <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;overflow:hidden;">
        <?php if (empty($users)): ?>
        <div style="padding:60px;text-align:center;color:var(--text-secondary,#8892a6);">
            <i class="fas fa-users" style="font-size:2rem;opacity:.3;display:block;margin-bottom:12px;"></i>
            No support users yet.
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border-color,rgba(255,255,255,.08));">
                        <?php foreach (['User','Email','Tickets','Chats','Last Activity','Actions'] as $h): ?>
                        <th style="padding:12px 14px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;"><?= $h ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr style="border-bottom:1px solid var(--border-color,rgba(255,255,255,.04));transition:background .15s;" onmouseover="this.style.background='rgba(255,255,255,.02)'" onmouseout="this.style.background=''">
                        <td style="padding:12px 14px;">
                            <div style="font-weight:600;color:var(--text-primary,#e8eefc);font-size:.88rem;"><?= htmlspecialchars($user['name']) ?></div>
                        </td>
                        <td style="padding:12px 14px;color:var(--text-secondary,#8892a6);font-size:.85rem;"><?= htmlspecialchars($user['email']) ?></td>
                        <td style="padding:12px 14px;text-align:center;">
                            <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:.78rem;font-weight:600;background:rgba(0,240,255,.1);color:#00f0ff;">
                                <?= (int)$user['ticket_count'] ?>
                            </span>
                        </td>
                        <td style="padding:12px 14px;text-align:center;">
                            <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:.78rem;font-weight:600;background:rgba(255,46,196,.1);color:#ff2ec4;">
                                <?= (int)$user['chat_count'] ?>
                            </span>
                        </td>
                        <td style="padding:12px 14px;color:var(--text-secondary,#8892a6);font-size:.8rem;">
                            <?php
                            $la = $user['last_activity'];
                            echo $la && $la !== '1970-01-01' ? date('M j, Y', strtotime($la)) : '—';
                            ?>
                        </td>
                        <td style="padding:12px 14px;">
                            <a href="/admin/support/tickets?user_id=<?= (int)$user['id'] ?>" style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:rgba(0,240,255,.1);color:#00f0ff;border-radius:6px;text-decoration:none;font-size:.78rem;font-weight:500;">
                                <i class="fas fa-ticket"></i> Tickets
                            </a>
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
