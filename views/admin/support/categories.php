<?php
/**
 * Admin: Support Template Categories (under a Group)
 */
use Core\View;

View::extend('admin');
View::section('content');
?>

<style>
.stc-page { padding: 28px 32px; }
.stc-back { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 16px; font-size: .82rem; color: var(--text-secondary); text-decoration: none; }
.stc-back:hover { color: var(--text-primary); }
.stc-hdr  { margin-bottom: 24px; }
.stc-hdr h1 { font-size: 1.4rem; font-weight: 700; color: var(--text-primary); margin: 0 0 4px; display: flex; align-items: center; gap: 10px; }
.stc-hdr p  { color: var(--text-secondary); font-size: .83rem; margin: 4px 0 0; }
.stc-grid { display: grid; grid-template-columns: 1fr 360px; gap: 22px; align-items: start; }
@media(max-width:900px){ .stc-grid { grid-template-columns: 1fr; } }
.stc-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; }
.stc-card-head { padding: 13px 18px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; }
.stc-card-head h3 { margin: 0; font-size: .92rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 7px; }
.stc-card-head h3 i { color: var(--cyan); }
.stc-card-body { padding: 16px 18px; }
.stc-count { padding: 2px 8px; border-radius: 20px; font-size: .72rem; background: var(--bg-secondary); color: var(--text-secondary); border: 1px solid var(--border-color); }
.stc-cat-item { display: flex; align-items: center; gap: 11px; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 9px; margin-bottom: 8px; background: var(--bg-secondary); }
.stc-cat-icon { width: 34px; height: 34px; border-radius: 8px; background: rgba(59,130,246,.1); color: var(--cyan); display: flex; align-items: center; justify-content: center; font-size: .88rem; flex-shrink: 0; }
.stc-cat-info { flex: 1; min-width: 0; }
.stc-cat-name { font-size: .87rem; font-weight: 600; color: var(--text-primary); }
.stc-cat-desc { font-size: .73rem; color: var(--text-secondary); margin-top: 2px; }
.stc-cat-actions { display: flex; gap: 5px; flex-shrink: 0; }
.stc-btn-sm { padding: 5px 11px; border-radius: 6px; font-size: .74rem; font-weight: 600; cursor: pointer; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
.stc-btn-builder { background: rgba(124,58,237,.1); color: #c4b5fd; border: 1px solid rgba(124,58,237,.22); }
.stc-btn-edit    { background: rgba(37,99,235,.1);  color: #60a5fa;  border: 1px solid rgba(37,99,235,.22); }
.stc-btn-danger  { background: rgba(220,38,38,.08); color: #f87171;  border: 1px solid rgba(220,38,38,.18); }
.stc-btn-builder:hover { background: rgba(124,58,237,.2); }
.stc-btn-edit:hover    { background: rgba(37,99,235,.18); }
.stc-btn-danger:hover  { background: rgba(220,38,38,.15); }
.stc-form label { display: block; font-size: .77rem; font-weight: 600; color: var(--text-secondary); margin: 10px 0 4px; }
.stc-form label:first-child { margin-top: 0; }
.stc-input { width: 100%; padding: 8px 10px; border: 1px solid var(--border-color); border-radius: 7px; background: var(--bg-secondary); color: var(--text-primary); font-size: .84rem; outline: none; box-sizing: border-box; }
.stc-input:focus { border-color: var(--cyan); }
.stc-btn-full { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 18px; background: var(--cyan); color: #fff; border: none; border-radius: 7px; font-size: .85rem; font-weight: 700; cursor: pointer; width: 100%; margin-top: 12px; }
.stc-btn-full:hover { opacity: .9; }
.stc-edit-panel { display: none; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 9px; padding: 13px 14px; margin-top: 6px; margin-bottom: 8px; }
.stc-edit-panel.open { display: block; }
.stc-status-dot { display: inline-block; width: 6px; height: 6px; border-radius: 50%; vertical-align: middle; margin-right: 3px; }
</style>

<div class="stc-page">
  <a href="/admin/support/groups" class="stc-back"><i class="fas fa-arrow-left"></i> All Groups</a>

  <div class="stc-hdr">
    <h1>
      <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;background:<?= htmlspecialchars($group['color']) ?>22;color:<?= htmlspecialchars($group['color']) ?>;">
        <i class="fas fa-<?= htmlspecialchars($group['icon']) ?>"></i>
      </span>
      <?= htmlspecialchars($group['name']) ?>
    </h1>
    <?php if (!empty($group['description'])): ?>
    <p><?= htmlspecialchars($group['description']) ?></p>
    <?php endif; ?>
  </div>

  <div class="stc-grid">

    <div>
      <div class="stc-card">
        <div class="stc-card-head">
          <h3><i class="fas fa-tags"></i> Categories</h3>
          <span class="stc-count"><?= count($categories) ?></span>
        </div>
        <div class="stc-card-body">
          <?php if (empty($categories)): ?>
            <p style="color:var(--text-secondary);font-size:.85rem;text-align:center;padding:20px 0;">No categories yet. Add one →</p>
          <?php else: ?>
            <?php foreach ($categories as $cat): ?>
            <div class="stc-cat-item" id="stc-cat-wrap-<?= (int)$cat['id'] ?>">
              <div class="stc-cat-icon"><i class="fas fa-<?= htmlspecialchars($cat['icon']) ?>"></i></div>
              <div class="stc-cat-info">
                <div class="stc-cat-name">
                  <span class="stc-status-dot" style="background:<?= $cat['is_active'] ? '#22c55e' : '#6b7280' ?>"></span>
                  <?= htmlspecialchars($cat['name']) ?>
                </div>
                <?php if (!empty($cat['description'])): ?>
                  <div class="stc-cat-desc"><?= htmlspecialchars($cat['description']) ?></div>
                <?php endif; ?>
              </div>
              <div class="stc-cat-actions">
                <button type="button" class="stc-btn-sm stc-btn-edit" onclick="stcToggleEdit(<?= (int)$cat['id'] ?>)" title="Edit">
                  <i class="fas fa-pencil"></i>
                </button>
                <a href="/admin/support/builder/<?= (int)$cat['id'] ?>" class="stc-btn-sm stc-btn-builder" title="Open Form Builder">
                  <i class="fas fa-wand-magic-sparkles"></i> Builder
                </a>
                <form method="POST" action="/admin/support/categories/<?= (int)$cat['id'] ?>/delete" onsubmit="return confirm('Delete this category and its template?')" style="margin:0;">
                  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                  <button type="submit" class="stc-btn-sm stc-btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                </form>
              </div>
            </div>
            <div class="stc-edit-panel" id="stc-edit-<?= (int)$cat['id'] ?>">
              <form method="POST" action="/admin/support/categories/<?= (int)$cat['id'] ?>/update" class="stc-form">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                  <div><label>Name</label><input type="text" name="name" value="<?= htmlspecialchars($cat['name']) ?>" required class="stc-input"></div>
                  <div><label>Description</label><input type="text" name="description" value="<?= htmlspecialchars($cat['description'] ?? '') ?>" class="stc-input"></div>
                  <div><label>Icon</label><input type="text" name="icon" value="<?= htmlspecialchars($cat['icon']) ?>" class="stc-input"></div>
                  <div><label>Sort Order</label><input type="number" name="sort_order" value="<?= (int)$cat['sort_order'] ?>" min="0" class="stc-input"></div>
                  <div style="display:flex;align-items:flex-end;padding-bottom:4px;">
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;margin:0;">
                      <input type="checkbox" name="is_active" value="1" <?= $cat['is_active'] ? 'checked' : '' ?>> Active
                    </label>
                  </div>
                </div>
                <div style="display:flex;gap:7px;margin-top:11px;">
                  <button type="submit" class="stc-btn-sm stc-btn-builder"><i class="fas fa-check"></i> Save</button>
                  <button type="button" class="stc-btn-sm stc-btn-danger" onclick="stcToggleEdit(<?= (int)$cat['id'] ?>)"><i class="fas fa-xmark"></i> Cancel</button>
                </div>
              </form>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div>
      <div class="stc-card">
        <div class="stc-card-head">
          <h3><i class="fas fa-plus-circle"></i> Add Category</h3>
        </div>
        <div class="stc-card-body">
          <form method="POST" action="/admin/support/groups/<?= (int)$group['id'] ?>/categories/create" class="stc-form">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <label>Category Name <span style="color:#f87171;">*</span></label>
            <input type="text" name="name" required placeholder="e.g. Bug Report" class="stc-input">
            <label>Description</label>
            <input type="text" name="description" placeholder="Short description" class="stc-input">
            <label>Icon <span style="font-weight:400;font-size:.71rem;">(Font Awesome name)</span></label>
            <input type="text" name="icon" value="tag" placeholder="tag" class="stc-input">
            <label>Sort Order</label>
            <input type="number" name="sort_order" value="0" min="0" class="stc-input" style="width:100px;">
            <button type="submit" class="stc-btn-full"><i class="fas fa-plus"></i> Add Category</button>
          </form>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
function stcToggleEdit(id) {
    var panel = document.getElementById('stc-edit-' + id);
    if (panel) panel.classList.toggle('open');
}
</script>

<?php View::endSection(); ?>
