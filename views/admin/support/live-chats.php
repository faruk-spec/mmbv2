<?php
/**
 * Admin Live Chats List
 */
use Core\View;

View::extend('admin');
View::section('content');
?>

<style>
.slc-page  { padding: 28px 32px; }
.slc-hdr   { margin-bottom: 24px; }
.slc-title { font-size: 1.45rem; font-weight: 700; color: var(--text-primary,#e8eefc); margin: 0 0 3px; display: flex; align-items: center; gap: 10px; }
.slc-title i { color: #db2777; }
.slc-sub   { color: var(--text-secondary,#8892a6); margin: 0; font-size: .83rem; }
.slc-table-wrap { background: var(--bg-card,#0f0f18); border: 1px solid var(--border-color,rgba(255,255,255,.08)); border-radius: 12px; overflow: hidden; }
.slc-empty { padding: 64px 40px; text-align: center; color: var(--text-secondary,#8892a6); }
.slc-empty i { font-size: 2rem; opacity: .25; display: block; margin-bottom: 12px; }
.slc-table { width: 100%; border-collapse: collapse; }
.slc-table thead th { padding: 11px 16px; text-align: left; color: var(--text-secondary,#8892a6); font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; border-bottom: 1px solid var(--border-color,rgba(255,255,255,.08)); white-space: nowrap; }
.slc-table tbody tr { border-bottom: 1px solid var(--border-color,rgba(255,255,255,.04)); transition: background .12s; }
.slc-table tbody tr:last-child { border-bottom: none; }
.slc-table tbody tr:hover { background: rgba(255,255,255,.02); }
.slc-table td { padding: 12px 16px; vertical-align: middle; }
.slc-id    { font-size: .8rem; color: var(--text-secondary,#8892a6); }
.slc-name  { font-size: .88rem; color: var(--text-primary,#e8eefc); font-weight: 500; }
.slc-email { font-size: .75rem; color: var(--text-secondary,#8892a6); margin-top: 1px; }
.slc-agent { font-size: .85rem; color: var(--text-primary,#e8eefc); }
.slc-date  { font-size: .79rem; color: var(--text-secondary,#8892a6); white-space: nowrap; }
.slc-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: .72rem; font-weight: 700; }
.slc-actions { display: flex; gap: 6px; }
.slc-btn { display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; border-radius: 6px; text-decoration: none; font-size: .76rem; font-weight: 600; border: none; cursor: pointer; }
</style>

<div class="slc-page">
  <div class="slc-hdr">
    <h1 class="slc-title"><i class="fas fa-comments"></i> Live Chats</h1>
    <p class="slc-sub">
      <?= count(array_filter($chats, fn($c) => $c['status'] === 'active')) ?> active &bull; <?= count($chats) ?> total
    </p>
  </div>

  <div class="slc-table-wrap">
    <?php if (empty($chats)): ?>
    <div class="slc-empty">
      <i class="fas fa-comments"></i>
      <p>No chat sessions found.</p>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="slc-table">
        <thead>
          <tr>
            <th>ID</th><th>User / Guest</th><th>Status</th>
            <th>Agent</th><th>Started</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($chats as $chat):
            $isActive = ($chat['status'] === 'active');
            $sc       = $isActive ? '#16a34a' : '#64748b';
            $userName = $chat['user_name'] ?? ($chat['guest_name'] ? $chat['guest_name'].' (guest)' : 'Guest');
          ?>
          <tr>
            <td><span class="slc-id">#<?= (int)$chat['id'] ?></span></td>
            <td>
              <div class="slc-name"><?= htmlspecialchars($userName) ?></div>
              <?php if (!empty($chat['guest_email'])): ?>
              <div class="slc-email"><?= htmlspecialchars($chat['guest_email']) ?></div>
              <?php endif; ?>
            </td>
            <td>
              <span class="slc-badge" style="background:<?= $sc ?>1a;color:<?= $sc ?>;">
                <i class="fas fa-circle" style="font-size:.45rem;"></i>
                <?= ucfirst($chat['status']) ?>
              </span>
            </td>
            <td><span class="slc-agent"><?= htmlspecialchars($chat['agent_name'] ?? '—') ?></span></td>
            <td><span class="slc-date"><?= date('M j, Y H:i', strtotime($chat['created_at'])) ?></span></td>
            <td>
              <div class="slc-actions">
                <a href="/admin/support/live-chats/<?= (int)$chat['id'] ?>" class="slc-btn" style="background:rgba(219,39,119,.1);color:#f472b6;">
                  <i class="fas fa-eye"></i> View
                </a>
                <?php if (!$isActive): ?>
                <form method="POST" action="/admin/support/live-chats/<?= (int)$chat['id'] ?>/reopen" style="display:contents;">
                  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                  <button type="submit" class="slc-btn" style="background:rgba(22,163,74,.1);color:#4ade80;border:1px solid rgba(22,163,74,.2);">
                    <i class="fas fa-rotate-left"></i> Reopen
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
