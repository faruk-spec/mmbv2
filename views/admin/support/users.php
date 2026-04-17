<?php
/**
 * Admin Support Users & Agent Management
 */
use Core\View;

View::extend('admin');
View::section('content');
?>

<style>
.sua-page  { padding: 28px 32px; }

/* Flash messages */
.sua-flash { padding: 11px 16px; border-radius: 8px; margin-bottom: 20px; font-size: .86rem; }
.sua-flash.ok  { background: rgba(22,163,74,.1);  border: 1px solid rgba(22,163,74,.25);  color: #4ade80; }
.sua-flash.err { background: rgba(220,38,38,.1);  border: 1px solid rgba(220,38,38,.25);  color: #f87171; }

/* Section header */
.sua-sec-hdr { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; }
.sua-sec-title { font-size: 1.25rem; font-weight: 700; color: var(--text-primary,#e8eefc); margin: 0 0 3px; display: flex; align-items: center; gap: 9px; }
.sua-sec-title i { font-size: 1rem; }
.sua-sec-sub   { color: var(--text-secondary,#8892a6); margin: 0; font-size: .8rem; }

/* Add Agent button */
.sua-add-btn {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 8px 18px; background: var(--cyan,#3b82f6); border: none;
  border-radius: 8px; color: #fff; font-weight: 700; font-size: .83rem; cursor: pointer;
}
.sua-add-btn:hover { opacity: .9; }

/* Card / table wrapper */
.sua-card { background: var(--bg-card,#0f0f18); border: 1px solid var(--border-color,rgba(255,255,255,.08)); border-radius: 12px; overflow: hidden; margin-bottom: 36px; }
.sua-empty { padding: 56px 40px; text-align: center; color: var(--text-secondary,#8892a6); }
.sua-empty i { font-size: 2rem; opacity: .25; display: block; margin-bottom: 12px; }
.sua-empty p { margin: 0; font-size: .88rem; }

/* Table */
.sua-table { width: 100%; border-collapse: collapse; }
.sua-table thead th {
  padding: 11px 16px; text-align: left;
  color: var(--text-secondary,#8892a6); font-size: .72rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: .06em;
  border-bottom: 1px solid var(--border-color,rgba(255,255,255,.08)); white-space: nowrap;
}
.sua-table tbody tr { border-bottom: 1px solid var(--border-color,rgba(255,255,255,.04)); transition: background .12s; }
.sua-table tbody tr:last-child { border-bottom: none; }
.sua-table tbody tr:hover { background: rgba(255,255,255,.02); }
.sua-table td { padding: 11px 16px; vertical-align: middle; }

/* Agent avatar */
.sua-avatar {
  width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
  background: linear-gradient(135deg,#d97706,#7c3aed);
  display: flex; align-items: center; justify-content: center;
  font-size: .78rem; font-weight: 800; color: #fff;
}
.sua-agent-cell { display: flex; align-items: center; gap: 10px; }
.sua-agent-name { font-size: .88rem; font-weight: 600; color: var(--text-primary,#e8eefc); }
.sua-cell-text  { font-size: .84rem; color: var(--text-secondary,#8892a6); }
.sua-cell-muted { font-size: .8rem; color: var(--text-secondary,#8892a6); max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sua-badge { display: inline-flex; align-items: center; padding: 2px 9px; border-radius: 20px; font-size: .72rem; font-weight: 700; background: rgba(59,130,246,.1); color: var(--cyan,#3b82f6); }
.sua-badge-pink { background: rgba(219,39,119,.1); color: #f472b6; }
.sua-date  { font-size: .79rem; color: var(--text-secondary,#8892a6); white-space: nowrap; }
.sua-rm-btn {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 5px 12px; background: rgba(220,38,38,.08); border: 1px solid rgba(220,38,38,.18);
  color: #f87171; border-radius: 6px; font-size: .76rem; font-weight: 600; cursor: pointer;
}
.sua-view-btn {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 5px 12px; background: rgba(37,99,235,.1); color: #60a5fa;
  border-radius: 6px; text-decoration: none; font-size: .76rem; font-weight: 600;
}

/* Modal */
.sua-modal-overlay {
  display: none; position: fixed; inset: 0; z-index: 99999;
  align-items: center; justify-content: center;
  background: rgba(0,0,0,.6); backdrop-filter: blur(4px);
}
.sua-modal-overlay.open { display: flex; }
.sua-modal {
  background: var(--bg-card,#0f0f18);
  border: 1px solid var(--border-color,rgba(255,255,255,.1));
  border-radius: 14px; padding: 26px 28px; width: 100%; max-width: 440px; margin: 16px;
}
.sua-modal-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; }
.sua-modal-title { margin: 0; font-size: 1rem; font-weight: 700; color: var(--text-primary,#e8eefc); }
.sua-modal-close { background: none; border: none; color: var(--text-secondary,#8892a6); cursor: pointer; font-size: 1.1rem; padding: 2px; }
.sua-modal-close:hover { color: #f87171; }
.sua-form-label { display: block; font-size: .79rem; font-weight: 600; color: var(--text-secondary,#8892a6); margin-bottom: 5px; }
.sua-form-ctrl {
  width: 100%; padding: 9px 12px; margin-bottom: 16px;
  border: 1px solid var(--border-color,rgba(255,255,255,.1));
  border-radius: 8px; background: var(--bg-secondary,#0c0c12);
  color: var(--text-primary,#e8eefc); font-size: .86rem; outline: none; box-sizing: border-box;
}
.sua-form-ctrl:focus { border-color: var(--cyan,#3b82f6); }
.sua-modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 4px; }
.sua-modal-cancel {
  padding: 8px 18px; background: var(--bg-secondary,#0c0c12);
  border: 1px solid var(--border-color,rgba(255,255,255,.1));
  border-radius: 8px; color: var(--text-secondary,#8892a6); font-size: .86rem; cursor: pointer;
}
.sua-modal-submit {
  padding: 8px 22px; background: var(--cyan,#3b82f6); border: none;
  border-radius: 8px; color: #fff; font-weight: 700; font-size: .86rem; cursor: pointer;
}
</style>

<div class="sua-page">

  <?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="sua-flash ok"><?= htmlspecialchars($_SESSION['flash_success']) ?><?php unset($_SESSION['flash_success']); ?></div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="sua-flash err"><?= htmlspecialchars($_SESSION['flash_error']) ?><?php unset($_SESSION['flash_error']); ?></div>
  <?php endif; ?>

  <!-- Agents -->
  <div class="sua-sec-hdr">
    <div>
      <h2 class="sua-sec-title"><i class="fas fa-user-shield" style="color:#d97706;"></i> Support Agents</h2>
      <p class="sua-sec-sub">Agents can view and reply to live chats and support tickets.</p>
    </div>
    <button class="sua-add-btn" onclick="document.getElementById('suaModal').classList.add('open')">
      <i class="fas fa-plus"></i> Add Agent
    </button>
  </div>

  <div class="sua-card">
    <?php if (empty($agents)): ?>
    <div class="sua-empty">
      <i class="fas fa-user-shield"></i>
      <p>No support agents yet. Add one above.</p>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="sua-table">
        <thead>
          <tr><th>Agent</th><th>Email</th><th>Role</th><th>Notes</th><th>Added By</th><th>Since</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($agents as $agent): ?>
          <tr>
            <td>
              <div class="sua-agent-cell">
                <div class="sua-avatar"><?= strtoupper(substr($agent['name'],0,1)) ?></div>
                <span class="sua-agent-name"><?= htmlspecialchars($agent['name']) ?></span>
              </div>
            </td>
            <td><span class="sua-cell-text"><?= htmlspecialchars($agent['email']) ?></span></td>
            <td><span class="sua-badge"><?= htmlspecialchars($agent['role']) ?></span></td>
            <td><span class="sua-cell-muted" title="<?= htmlspecialchars($agent['notes'] ?: '') ?>"><?= htmlspecialchars($agent['notes'] ?: '—') ?></span></td>
            <td><span class="sua-cell-text"><?= htmlspecialchars($agent['assigned_by_name'] ?? '—') ?></span></td>
            <td><span class="sua-date"><?= date('M j, Y', strtotime($agent['created_at'])) ?></span></td>
            <td>
              <form method="POST" action="/admin/support/agents/<?= (int)$agent['user_id'] ?>/remove"
                    onsubmit="return confirm('Remove this agent?')">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <button type="submit" class="sua-rm-btn"><i class="fas fa-user-minus"></i> Remove</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- Support Users -->
  <div class="sua-sec-hdr">
    <div>
      <h2 class="sua-sec-title"><i class="fas fa-users" style="color:var(--cyan,#3b82f6);"></i> Support Users</h2>
      <p class="sua-sec-sub">Users who have submitted tickets or initiated live chats.</p>
    </div>
  </div>

  <div class="sua-card" style="margin-bottom:0;">
    <?php if (empty($users)): ?>
    <div class="sua-empty">
      <i class="fas fa-users"></i>
      <p>No support users yet.</p>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="sua-table">
        <thead>
          <tr><th>User</th><th>Email</th><th>Tickets</th><th>Chats</th><th>Last Activity</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user): ?>
          <tr>
            <td><span class="sua-agent-name"><?= htmlspecialchars($user['name']) ?></span></td>
            <td><span class="sua-cell-text"><?= htmlspecialchars($user['email']) ?></span></td>
            <td><span class="sua-badge"><?= (int)$user['ticket_count'] ?></span></td>
            <td><span class="sua-badge sua-badge-pink"><?= (int)$user['chat_count'] ?></span></td>
            <td>
              <span class="sua-date">
                <?php $la = $user['last_activity']; echo ($la && $la !== '1970-01-01') ? date('M j, Y', strtotime($la)) : '—'; ?>
              </span>
            </td>
            <td>
              <a href="/admin/support/tickets?user_id=<?= (int)$user['id'] ?>" class="sua-view-btn">
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

<!-- Add Agent Modal -->
<div id="suaModal" class="sua-modal-overlay" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="sua-modal">
    <div class="sua-modal-head">
      <h3 class="sua-modal-title"><i class="fas fa-user-shield" style="color:#d97706;margin-right:8px;"></i>Add Support Agent</h3>
      <button class="sua-modal-close" onclick="document.getElementById('suaModal').classList.remove('open')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" action="/admin/support/agents/add">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
      <label class="sua-form-label">Select User <span style="color:#f87171;">*</span></label>
      <select name="user_id" required class="sua-form-ctrl">
        <option value="">— choose a user —</option>
        <?php foreach ($allUsers as $u): if ($u['is_agent']) continue; ?>
        <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email']) ?>)</option>
        <?php endforeach; ?>
      </select>
      <label class="sua-form-label">Notes <span style="font-weight:400;opacity:.7;">(optional)</span></label>
      <textarea name="notes" rows="2" placeholder="e.g. Handles billing queries" class="sua-form-ctrl" style="resize:vertical;"></textarea>
      <div class="sua-modal-actions">
        <button type="button" class="sua-modal-cancel" onclick="document.getElementById('suaModal').classList.remove('open')">Cancel</button>
        <button type="submit" class="sua-modal-submit"><i class="fas fa-user-plus" style="margin-right:5px;"></i>Add Agent</button>
      </div>
    </form>
  </div>
</div>

<?php View::endSection(); ?>
