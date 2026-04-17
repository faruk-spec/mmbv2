<?php
/**
 * Admin Support Tickets List
 */
use Core\View;

View::extend('admin');
View::section('content');
?>

<style>
.stl-page  { padding: 28px 32px; }
.stl-hdr   { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
.stl-title { font-size: 1.45rem; font-weight: 700; color: var(--text-primary,#e8eefc); margin: 0 0 3px; display: flex; align-items: center; gap: 10px; }
.stl-title i { color: var(--cyan,#3b82f6); }
.stl-sub   { color: var(--text-secondary,#8892a6); margin: 0; font-size: .83rem; }

.stl-filter {
  background: var(--bg-card,#0f0f18);
  border: 1px solid var(--border-color,rgba(255,255,255,.08));
  border-radius: 12px; padding: 14px 18px;
  margin-bottom: 18px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center;
}
.stl-filter-sel {
  padding: 7px 11px; border: 1px solid var(--border-color,rgba(255,255,255,.1));
  border-radius: 7px; background: var(--bg-secondary,#0c0c12);
  color: var(--text-primary,#e8eefc); font-size: .83rem; outline: none;
}
.stl-filter-sel:focus { border-color: var(--cyan,#3b82f6); }
.stl-filter-btn {
  padding: 7px 18px; background: var(--cyan,#3b82f6); border: none;
  border-radius: 7px; color: #fff; font-weight: 700; font-size: .83rem; cursor: pointer;
  display: inline-flex; align-items: center; gap: 6px;
}
.stl-filter-clear {
  padding: 7px 12px; border: 1px solid var(--border-color,rgba(255,255,255,.1));
  border-radius: 7px; color: var(--text-secondary,#8892a6);
  text-decoration: none; font-size: .83rem; display: inline-flex; align-items: center; gap: 5px;
}
.stl-filter-clear:hover { border-color: rgba(255,255,255,.22); color: var(--text-primary,#e8eefc); }

.stl-table-wrap {
  background: var(--bg-card,#0f0f18);
  border: 1px solid var(--border-color,rgba(255,255,255,.08));
  border-radius: 12px; overflow: hidden;
}
.stl-empty { padding: 64px 40px; text-align: center; color: var(--text-secondary,#8892a6); }
.stl-empty i { font-size: 2rem; opacity: .25; display: block; margin-bottom: 12px; }
.stl-empty p { margin: 0; font-size: .88rem; }
.stl-table { width: 100%; border-collapse: collapse; }
.stl-table thead th {
  padding: 11px 16px; text-align: left;
  color: var(--text-secondary,#8892a6); font-size: .72rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: .06em;
  border-bottom: 1px solid var(--border-color,rgba(255,255,255,.08));
  white-space: nowrap;
}
.stl-table tbody tr {
  border-bottom: 1px solid var(--border-color,rgba(255,255,255,.04));
  transition: background .12s;
}
.stl-table tbody tr:last-child { border-bottom: none; }
.stl-table tbody tr:hover { background: rgba(255,255,255,.02); }
.stl-table td { padding: 12px 16px; vertical-align: middle; }
.stl-id  { font-size: .8rem; color: var(--text-secondary,#8892a6); font-weight: 500; }
.stl-user-name { font-size: .86rem; color: var(--text-primary,#e8eefc); font-weight: 500; }
.stl-subject {
  color: var(--text-primary,#e8eefc); text-decoration: none;
  font-size: .88rem; font-weight: 500;
}
.stl-subject:hover { color: var(--cyan,#3b82f6); }
.stl-badge {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 3px 10px; border-radius: 20px; font-size: .72rem; font-weight: 700; white-space: nowrap;
}
.stl-date { font-size: .79rem; color: var(--text-secondary,#8892a6); white-space: nowrap; }
.stl-actions { display: flex; gap: 6px; }
.stl-act-btn {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 5px 12px; border-radius: 6px; text-decoration: none;
  font-size: .76rem; font-weight: 600; border: none; cursor: pointer;
}
</style>

<div class="stl-page">
  <div class="stl-hdr">
    <div>
      <h1 class="stl-title"><i class="fas fa-ticket"></i> Tickets</h1>
      <p class="stl-sub"><?= (int)($stats['open']??0) ?> open &bull; <?= (int)($stats['total']??0) ?> total</p>
    </div>
  </div>

  <form method="GET" action="/admin/support/tickets" class="stl-filter">
    <select name="status" class="stl-filter-sel">
      <option value="">All Statuses</option>
      <?php foreach (['open','in_progress','waiting_customer','resolved','closed'] as $s): ?>
      <option value="<?= $s ?>" <?= ($filters['status']==$s)?'selected':'' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="priority" class="stl-filter-sel">
      <option value="">All Priorities</option>
      <?php foreach (['low','medium','high','urgent'] as $p): ?>
      <option value="<?= $p ?>" <?= ($filters['priority']==$p)?'selected':'' ?>><?= ucfirst($p) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="stl-filter-btn"><i class="fas fa-filter"></i> Filter</button>
    <?php if (!empty(array_filter($filters))): ?>
    <a href="/admin/support/tickets" class="stl-filter-clear"><i class="fas fa-xmark"></i> Clear</a>
    <?php endif; ?>
  </form>

  <div class="stl-table-wrap">
    <?php if (empty($tickets)): ?>
    <div class="stl-empty">
      <i class="fas fa-ticket"></i>
      <p>No tickets found.</p>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="stl-table">
        <thead>
          <tr>
            <th>ID</th><th>Requester</th><th>Subject</th>
            <th>Status</th><th>Priority</th><th>Created</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tickets as $ticket):
            $sc = ['open'=>'#2563eb','in_progress'=>'#d97706','waiting_customer'=>'#7c3aed','resolved'=>'#16a34a','closed'=>'#64748b'];
            $pc = ['urgent'=>'#dc2626','high'=>'#ea580c','medium'=>'#2563eb','low'=>'#64748b'];
            $sColor = $sc[$ticket['status']] ?? '#64748b';
            $pColor = $pc[$ticket['priority']] ?? '#64748b';
          ?>
          <tr>
            <td><span class="stl-id">#<?= sprintf('%07d', (int)$ticket['id']) ?></span></td>
            <td><span class="stl-user-name"><?= htmlspecialchars($ticket['user_name'] ?? '—') ?></span></td>
            <td>
              <a href="/admin/support/tickets/<?= (int)$ticket['id'] ?>" class="stl-subject">
                <?= htmlspecialchars($ticket['subject']) ?>
              </a>
            </td>
            <td>
              <span class="stl-badge" style="background:<?= $sColor ?>1a;color:<?= $sColor ?>;">
                <?= ucwords(str_replace('_',' ',$ticket['status'])) ?>
              </span>
            </td>
            <td>
              <span class="stl-badge" style="background:<?= $pColor ?>1a;color:<?= $pColor ?>;">
                <?= ucfirst($ticket['priority']) ?>
              </span>
            </td>
            <td><span class="stl-date"><?= date('M j, Y', strtotime($ticket['created_at'])) ?></span></td>
            <td>
              <div class="stl-actions">
                <a href="/admin/support/tickets/<?= (int)$ticket['id'] ?>" class="stl-act-btn" style="background:rgba(37,99,235,.1);color:#60a5fa;">
                  <i class="fas fa-eye"></i> View
                </a>
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
