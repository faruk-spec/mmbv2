<?php
/**
 * Admin Support Overview
 */
use Core\View;

View::extend('admin');
View::section('content');
?>

<style>
.so-page { padding: 28px 32px; }
.so-page-header { margin-bottom: 28px; }
.so-page-title { font-size: 1.45rem; font-weight: 700; color: var(--text-primary,#e8eefc); margin: 0 0 4px; display: flex; align-items: center; gap: 10px; }
.so-page-title i { color: var(--cyan,#3b82f6); font-size: 1.2rem; }
.so-page-sub { color: var(--text-secondary,#8892a6); margin: 0; font-size: .85rem; }

.so-stats { display: grid; grid-template-columns: repeat(6, minmax(0,1fr)); gap: 14px; margin-bottom: 28px; }
@media(max-width:1200px){ .so-stats { grid-template-columns: repeat(3, 1fr); } }
@media(max-width:640px){  .so-stats { grid-template-columns: repeat(2, 1fr); } }

.so-stat-card {
  background: var(--bg-card,#0f0f18);
  border: 1px solid var(--border-color,rgba(255,255,255,.08));
  border-radius: 12px; padding: 18px 16px;
  display: flex; flex-direction: column; align-items: flex-start; gap: 8px;
  transition: border-color .2s, transform .2s;
}
.so-stat-card:hover { border-color: rgba(255,255,255,.15); transform: translateY(-1px); }
.so-stat-icon { width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: .9rem; }
.so-stat-val  { font-size: 1.85rem; font-weight: 800; line-height: 1; }
.so-stat-lbl  { font-size: .75rem; color: var(--text-secondary,#8892a6); font-weight: 500; }

.so-nav { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; }
.so-nav-card {
  display: flex; align-items: center; gap: 14px;
  background: var(--bg-card,#0f0f18);
  border: 1px solid var(--border-color,rgba(255,255,255,.08));
  border-radius: 12px; padding: 18px;
  text-decoration: none; transition: border-color .2s, background .2s;
}
.so-nav-card:hover { border-color: rgba(59,130,246,.3); background: rgba(59,130,246,.03); }
.so-nav-icon { width: 40px; height: 40px; border-radius: 10px; background: rgba(59,130,246,.1); color: var(--cyan,#3b82f6); display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.so-nav-title { font-weight: 600; color: var(--text-primary,#e8eefc); font-size: .92rem; margin-bottom: 2px; }
.so-nav-desc  { color: var(--text-secondary,#8892a6); font-size: .76rem; }
</style>

<div class="so-page">
  <div class="so-page-header">
    <h1 class="so-page-title"><i class="fas fa-headset"></i> Support</h1>
    <p class="so-page-sub">Platform-wide customer support management</p>
  </div>

  <div class="so-stats">
    <?php
    $statCards = [
      ['label'=>'Open',        'value'=>$stats['open']??0,        'color'=>'#2563eb', 'icon'=>'circle-exclamation'],
      ['label'=>'In Progress', 'value'=>$stats['in_progress']??0, 'color'=>'#d97706', 'icon'=>'arrow-rotate-right'],
      ['label'=>'Resolved',    'value'=>$stats['resolved']??0,    'color'=>'#16a34a', 'icon'=>'circle-check'],
      ['label'=>'Closed',      'value'=>$stats['closed']??0,      'color'=>'#64748b', 'icon'=>'lock'],
      ['label'=>'Total',       'value'=>$stats['total']??0,       'color'=>'#7c3aed', 'icon'=>'ticket'],
      ['label'=>'Active Chats','value'=>$activeChats,             'color'=>'#db2777', 'icon'=>'comments'],
    ];
    foreach ($statCards as $c): ?>
    <div class="so-stat-card">
      <div class="so-stat-icon" style="background:<?= $c['color'] ?>1a;color:<?= $c['color'] ?>;"><i class="fas fa-<?= $c['icon'] ?>"></i></div>
      <div class="so-stat-val" style="color:<?= $c['color'] ?>;"><?= (int)$c['value'] ?></div>
      <div class="so-stat-lbl"><?= $c['label'] ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="so-nav">
    <?php
    $links = [
      ['href'=>'/admin/support/tickets',   'icon'=>'ticket',      'label'=>'Tickets',        'desc'=>'View & manage all support tickets'],
      ['href'=>'/admin/support/live-chats','icon'=>'comments',    'label'=>'Live Chats',      'desc'=>'Monitor active chat sessions'],
      ['href'=>'/admin/support/templates', 'icon'=>'folder-tree', 'label'=>'Templates',       'desc'=>'Manage issue form templates'],
      ['href'=>'/admin/support/users',     'icon'=>'users',       'label'=>'Users & Agents',  'desc'=>'Manage agents and support users'],
    ];
    foreach ($links as $l): ?>
    <a href="<?= $l['href'] ?>" class="so-nav-card">
      <div class="so-nav-icon"><i class="fas fa-<?= $l['icon'] ?>"></i></div>
      <div>
        <div class="so-nav-title"><?= $l['label'] ?></div>
        <div class="so-nav-desc"><?= $l['desc'] ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<?php View::endSection(); ?>
