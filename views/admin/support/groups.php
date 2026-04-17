<?php
/**
 * Admin: Support Template Groups
 */
use Core\View;

View::extend('admin');
View::section('content');
?>

<style>
.stg-page  { padding: 28px 32px; }
.stg-hdr   { margin-bottom: 24px; }
.stg-hdr h1 { font-size: 1.45rem; font-weight: 700; color: var(--text-primary); margin: 0 0 4px; display: flex; align-items: center; gap: 10px; }
.stg-hdr h1 i { color: var(--cyan); font-size: 1.1rem; }
.stg-hdr p   { color: var(--text-secondary); margin: 0; font-size: .83rem; }
.stg-grid { display: grid; grid-template-columns: 420px 1fr; gap: 22px; align-items: start; }
@media(max-width:900px){ .stg-grid { grid-template-columns: 1fr; } }
.stg-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; }
.stg-card-head { padding: 13px 18px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; }
.stg-card-head h3 { margin: 0; font-size: .92rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 7px; }
.stg-card-head h3 i { color: var(--cyan); }
.stg-card-body { padding: 16px 18px; }
.stg-group-item { display: flex; align-items: center; gap: 12px; padding: 11px 13px; border: 1px solid var(--border-color); border-radius: 10px; margin-bottom: 8px; background: var(--bg-secondary); }
.stg-group-icon { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: .9rem; flex-shrink: 0; }
.stg-group-info { flex: 1; min-width: 0; }
.stg-group-name { font-size: .88rem; font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: 6px; }
.stg-group-desc { font-size: .74rem; color: var(--text-secondary); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.stg-group-actions { display: flex; gap: 5px; flex-shrink: 0; }
.stg-btn-sm { padding: 5px 11px; border-radius: 6px; font-size: .74rem; font-weight: 600; cursor: pointer; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
.stg-btn-primary { background: rgba(59,130,246,.1); color: var(--cyan); border: 1px solid rgba(59,130,246,.22); }
.stg-btn-danger  { background: rgba(220,38,38,.08); color: #f87171; border: 1px solid rgba(220,38,38,.18); }
.stg-btn-edit    { background: rgba(124,58,237,.1); color: #c4b5fd; border: 1px solid rgba(124,58,237,.22); }
.stg-btn-primary:hover { background: rgba(59,130,246,.18); }
.stg-btn-danger:hover  { background: rgba(220,38,38,.15); }
.stg-btn-edit:hover    { background: rgba(124,58,237,.18); }
.stg-form label { display: block; font-size: .77rem; font-weight: 600; color: var(--text-secondary); margin: 10px 0 4px; }
.stg-form label:first-child { margin-top: 0; }
.stg-input { width: 100%; padding: 8px 10px; border: 1px solid var(--border-color); border-radius: 7px; background: var(--bg-secondary); color: var(--text-primary); font-size: .84rem; outline: none; box-sizing: border-box; }
.stg-input:focus { border-color: var(--cyan); }
.stg-btn-full { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 18px; background: var(--cyan); color: #fff; border: none; border-radius: 7px; font-size: .85rem; font-weight: 700; cursor: pointer; width: 100%; margin-top: 12px; }
.stg-btn-full:hover { opacity: .9; }
.stg-edit-panel { display: none; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 9px; padding: 14px 15px; margin-top: 6px; margin-bottom: 8px; }
.stg-edit-panel.open { display: block; }
.stg-cat-chip { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 5px; font-size: .71rem; background: rgba(255,255,255,.05); color: var(--text-secondary); border: 1px solid var(--border-color); margin: 2px 2px 0 0; }
.stg-cat-chip a { color: inherit; text-decoration: none; }
.stg-cat-chip a:hover { color: var(--cyan); }
.stg-dot { display: inline-block; width: 6px; height: 6px; border-radius: 50%; vertical-align: middle; }
.stg-dot.on  { background: #22c55e; }
.stg-dot.off { background: #6b7280; }
.stg-count { padding: 2px 8px; border-radius: 20px; font-size: .72rem; background: var(--bg-secondary); color: var(--text-secondary); border: 1px solid var(--border-color); }
</style>

<div class="stg-page">
  <div class="stg-hdr">
    <h1><i class="fas fa-layer-group"></i> Template Groups</h1>
    <p>Departments or teams. Each group contains issue categories with a dynamic form template.</p>
  </div>

  <div class="stg-grid">

    <div>
      <div class="stg-card">
        <div class="stg-card-head">
          <h3><i class="fas fa-layer-group"></i> Groups</h3>
          <span class="stg-count"><?= count($groups) ?></span>
        </div>
        <div class="stg-card-body">
          <?php if (empty($groups)): ?>
            <p style="color:var(--text-secondary);font-size:.85rem;text-align:center;padding:20px 0;">No groups yet. Create the first one →</p>
          <?php else: ?>
            <?php foreach ($groups as $g): ?>
            <div class="stg-group-item" id="stg-group-wrap-<?= (int)$g['id'] ?>">
              <div class="stg-group-icon" style="background:<?= htmlspecialchars($g['color']) ?>22;color:<?= htmlspecialchars($g['color']) ?>;">
                <i class="fas fa-<?= htmlspecialchars($g['icon']) ?>"></i>
              </div>
              <div class="stg-group-info">
                <div class="stg-group-name">
                  <span class="stg-dot <?= $g['is_active'] ? 'on' : 'off' ?>" title="<?= $g['is_active'] ? 'Active' : 'Inactive' ?>"></span>
                  <?= htmlspecialchars($g['name']) ?>
                </div>
                <?php if (!empty($g['description'])): ?>
                  <div class="stg-group-desc"><?= htmlspecialchars($g['description']) ?></div>
                <?php endif; ?>
                <div style="margin-top:6px;">
                  <?php
                  $hasCats = false;
                  foreach ($categories as $c) {
                    if ((int)$c['group_id'] !== (int)$g['id']) continue;
                    $hasCats = true; ?>
                    <span class="stg-cat-chip">
                      <i class="fas fa-<?= htmlspecialchars($c['icon']) ?>"></i>
                      <a href="/admin/support/builder/<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></a>
                    </span>
                  <?php } if (!$hasCats): ?>
                    <span style="font-size:.72rem;color:var(--text-secondary);">No categories</span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="stg-group-actions">
                <button type="button" class="stg-btn-sm stg-btn-edit" onclick="stgToggleEdit(<?= (int)$g['id'] ?>)" title="Edit group">
                  <i class="fas fa-pencil"></i>
                </button>
                <a href="/admin/support/groups/<?= (int)$g['id'] ?>/categories" class="stg-btn-sm stg-btn-primary" title="Manage categories">
                  <i class="fas fa-tags"></i>
                </a>
                <form method="POST" action="/admin/support/groups/<?= (int)$g['id'] ?>/delete" onsubmit="return confirm('Delete this group and ALL its categories?')" style="margin:0;">
                  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                  <button type="submit" class="stg-btn-sm stg-btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                </form>
              </div>
            </div>
            <div class="stg-edit-panel" id="stg-edit-<?= (int)$g['id'] ?>">
              <form method="POST" action="/admin/support/groups/<?= (int)$g['id'] ?>/update" class="stg-form">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                  <div><label>Name</label><input type="text" name="name" value="<?= htmlspecialchars($g['name']) ?>" required class="stg-input"></div>
                  <div><label>Description</label><input type="text" name="description" value="<?= htmlspecialchars($g['description'] ?? '') ?>" class="stg-input"></div>
                  <div><label>Icon</label><input type="text" name="icon" value="<?= htmlspecialchars($g['icon']) ?>" class="stg-input"></div>
                  <div><label>Color</label><input type="color" name="color" value="<?= htmlspecialchars($g['color']) ?>" class="stg-input" style="height:37px;padding:3px 5px;"></div>
                  <div><label>Sort Order</label><input type="number" name="sort_order" value="<?= (int)$g['sort_order'] ?>" min="0" class="stg-input"></div>
                  <div style="display:flex;align-items:flex-end;padding-bottom:4px;">
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;margin:0;">
                      <input type="checkbox" name="is_active" value="1" <?= $g['is_active'] ? 'checked' : '' ?>> Active
                    </label>
                  </div>
                </div>
                <div style="display:flex;gap:7px;margin-top:11px;">
                  <button type="submit" class="stg-btn-sm stg-btn-primary"><i class="fas fa-check"></i> Save</button>
                  <button type="button" class="stg-btn-sm stg-btn-danger" onclick="stgToggleEdit(<?= (int)$g['id'] ?>)"><i class="fas fa-xmark"></i> Cancel</button>
                </div>
              </form>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div>
      <div class="stg-card">
        <div class="stg-card-head">
          <h3><i class="fas fa-plus-circle"></i> Create Group</h3>
        </div>
        <div class="stg-card-body">
          <form method="POST" action="/admin/support/groups/create" class="stg-form">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <label>Group Name <span style="color:#f87171;">*</span></label>
            <input type="text" name="name" required placeholder="e.g. Engineering Support" class="stg-input">
            <label>Description</label>
            <input type="text" name="description" placeholder="Short description" class="stg-input">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
              <div>
                <label>Icon <span style="font-weight:400;font-size:.71rem;">(Font Awesome name)</span></label>
                <input type="text" name="icon" value="users" placeholder="users" class="stg-input">
              </div>
              <div>
                <label>Color</label>
                <input type="color" name="color" value="#3b82f6" class="stg-input" style="height:37px;padding:3px 5px;">
              </div>
            </div>
            <label>Sort Order</label>
            <input type="number" name="sort_order" value="0" min="0" class="stg-input" style="width:100px;">
            <button type="submit" class="stg-btn-full"><i class="fas fa-plus"></i> Create Group</button>
          </form>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
function stgToggleEdit(id) {
    var panel = document.getElementById('stg-edit-' + id);
    if (panel) panel.classList.toggle('open');
}
</script>

<?php View::endSection(); ?>
