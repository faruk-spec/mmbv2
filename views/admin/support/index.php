<?php
/**
 * Admin Support Overview
 */
use Core\View;

View::extend('admin');
View::section('content');
?>

<div style="padding:28px;">
    <div style="margin-bottom:28px;">
        <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-primary,#e8eefc);margin:0 0 4px;">
            <i class="fas fa-headset" style="color:#00f0ff;margin-right:10px;"></i>Support Overview
        </h1>
        <p style="color:var(--text-secondary,#8892a6);margin:0;font-size:.9rem;">Platform-level customer support management.</p>
    </div>

    <!-- Ticket stats -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:16px;margin-bottom:28px;">
        <?php
        $statCards = [
            ['label'=>'Open',        'value'=>$stats['open']??0,        'color'=>'#00f0ff', 'icon'=>'circle-exclamation'],
            ['label'=>'In Progress', 'value'=>$stats['in_progress']??0, 'color'=>'#ff9f43', 'icon'=>'spinner'],
            ['label'=>'Resolved',    'value'=>$stats['resolved']??0,    'color'=>'#00ff88', 'icon'=>'check-circle'],
            ['label'=>'Closed',      'value'=>$stats['closed']??0,      'color'=>'#8892a6', 'icon'=>'lock'],
            ['label'=>'Total',       'value'=>$stats['total']??0,       'color'=>'#a78bfa', 'icon'=>'ticket'],
            ['label'=>'Active Chats','value'=>$activeChats,             'color'=>'#ff2ec4', 'icon'=>'comments'],
        ];
        foreach ($statCards as $card):
        ?>
        <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;padding:20px;text-align:center;">
            <i class="fas fa-<?= $card['icon'] ?>" style="font-size:1.4rem;color:<?= $card['color'] ?>;margin-bottom:10px;display:block;"></i>
            <div style="font-size:1.8rem;font-weight:700;color:<?= $card['color'] ?>;"><?= (int)$card['value'] ?></div>
            <div style="color:var(--text-secondary,#8892a6);font-size:.8rem;margin-top:4px;"><?= $card['label'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Quick links -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
        <?php
        $links = [
            ['href'=>'/admin/support/tickets',   'icon'=>'ticket',      'label'=>'Support Tickets',   'desc'=>'View & manage all tickets'],
            ['href'=>'/admin/support/live-chats', 'icon'=>'comments',    'label'=>'Live Chats',        'desc'=>'Monitor active chat sessions'],
            ['href'=>'/admin/support/templates',  'icon'=>'folder-tree', 'label'=>'Templates',         'desc'=>'Manage issue templates'],
            ['href'=>'/admin/support/users',      'icon'=>'users',       'label'=>'Support Users',     'desc'=>'Users with support activity'],
        ];
        foreach ($links as $link):
        ?>
        <a href="<?= $link['href'] ?>" style="display:block;background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;padding:20px;text-decoration:none;transition:border-color .2s;" onmouseover="this.style.borderColor='rgba(0,240,255,.3)'" onmouseout="this.style.borderColor='var(--border-color,rgba(255,255,255,.08))'">
            <i class="fas fa-<?= $link['icon'] ?>" style="font-size:1.3rem;color:#00f0ff;margin-bottom:10px;display:block;"></i>
            <div style="font-weight:600;color:var(--text-primary,#e8eefc);font-size:.95rem;margin-bottom:4px;"><?= $link['label'] ?></div>
            <div style="color:var(--text-secondary,#8892a6);font-size:.8rem;"><?= $link['desc'] ?></div>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<?php View::endSection(); ?>
