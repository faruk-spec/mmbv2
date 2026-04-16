<?php
/**
 * Admin: Support Template Groups
 */
use Core\View;

View::extend('admin');
View::section('content');
?>

<style>
.stg-page  { padding: 28px; }
.stg-hdr   { margin-bottom: 24px; }
.stg-hdr h1 { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin: 0 0 4px; display: flex; align-items: center; gap: 10px; }
.stg-hdr h1 i { color: var(--cyan); }
.stg-hdr p   { color: var(--text-secondary); margin: 0; font-size: .85rem; }
.stg-grid { display: grid; grid-template-columns: 400px 1fr; gap: 22px; align-items: start; }
@media(max-width:900px){ .stg-grid { grid-template-columns: 1fr; } }
.stg-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; }
.stg-card-head { padding: 15px 18px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; }
.stg-card-head h3 { margin: 0; font-size: .95rem; font-weight: 600; color: var(--text-primary); }
.stg-card-body { padding: 16px 18px; }
.stg-group-item { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 1px solid var(--border-color); border-radius: 10px; margin-bottom: 10px; background: var(--bg-secondary); }
.stg-group-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.stg-group-info { flex: 1; min-width: 0; }
.stg-group-name { font-size: .9rem; font-weight: 600; color: var(--text-primary); }
.stg-group-desc { font-size: .75rem; color: var(--text-secondary); margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.stg-group-actions { display: flex; gap: 6px; flex-shrink: 0; }
.stg-btn-sm { padding: 5px 12px; border-radius: 6px; font-size: .75rem; font-weight: 600; cursor: pointer; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
.stg-btn-primary { background: rgba(0,240,255,.1); color: var(--cyan); border: 1px solid rgba(0,240,255,.25); }
.stg-btn-danger  { background: rgba(239,68,68,.08); color: #ef4444; border: 1px solid rgba(239,68,68,.2); }
.stg-btn-primary:hover { background: rgba(0,240,255,.18); }
.stg-btn-danger:hover  { background: rgba(239,68,68,.16); }
.stg-form label { display: block; font-size: .78rem; font-weight: 600; color: var(--text-secondary); margin: 10px 0 4px; }
.stg-form label:first-child { margin-top: 0; }
.stg-input { width: 100%; padding: 8px 11px; border: 1px solid var(--border-color); border-radius: 7px; background: var(--bg-card); color: var(--text-primary); font-size: .85rem; outline: none; box-sizing: border-box; }
.stg-input:focus { border-color: var(--cyan); }
.stg-divider { font-size: .75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: .06em; margin: 18px 0 12px; padding-bottom: 6px; border-bottom: 1px solid var(--border-color); }
.stg-btn-full { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 18px; background: var(--cyan); color: #fff; border: none; border-radius: 7px; font-size: .85rem; font-weight: 600; cursor: pointer; width: 100%; margin-top: 12px; }
.stg-cat-chip { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 20px; font-size: .72rem; background: rgba(14,165,233,.1); color: #0ea5e9; border: 1px solid rgba(14,165,233,.2); margin: 2px; }
.stg-cat-chip a { color: inherit; text-decoration: none; }
.stg-cat-chip a:hover { text-decoration: underline; }
.badge-active { display: inline-block; width: 7px; height: 7px; border-radius: 50%; }
.badge-active.on { background: #22c55e; }
.badge-active.off { background: #6b7280; }
</style>

<div class="stg-page">
    <div class="stg-hdr">
        <h1><i class="fas fa-layer-group"></i> Template Groups</h1>
        <p>Departments or teams. Each group contains issue categories, each category has one dynamic form template.</p>
    </div>

    <div class="stg-grid">

        <!-- LEFT: Existing groups -->
        <div>
            <div class="stg-card">
                <div class="stg-card-head">
                    <h3><i class="fas fa-layer-group" style="color:var(--cyan);margin-right:7px;"></i>Groups</h3>
                    <span style="font-size:.75rem;color:var(--text-secondary);background:var(--bg-secondary);padding:2px 8px;border-radius:20px;"><?= count($groups) ?></span>
                </div>
                <div class="stg-card-body">
                    <?php if (empty($groups)): ?>
                        <p style="color:var(--text-secondary);font-size:.85rem;text-align:center;padding:20px 0;">No groups yet. Add the first one →</p>
                    <?php else: ?>
                        <?php foreach ($groups as $g):
                            $catCount = 0;
                            foreach ($categories as $c) { if ((int)$c['group_id'] === (int)$g['id']) $catCount++; }
                        ?>
                        <div class="stg-group-item">
                            <div class="stg-group-icon" style="background:<?= htmlspecialchars($g['color']) ?>22;color:<?= htmlspecialchars($g['color']) ?>;">
                                <i class="fas fa-<?= htmlspecialchars($g['icon']) ?>"></i>
                            </div>
                            <div class="stg-group-info">
                                <div class="stg-group-name">
                                    <span class="badge-active <?= $g['is_active'] ? 'on' : 'off' ?>" title="<?= $g['is_active'] ? 'Active' : 'Inactive' ?>"></span>&nbsp;
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
                                        <span style="font-size:.73rem;color:var(--text-secondary);">No categories yet.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="stg-group-actions">
                                <a href="/admin/support/groups/<?= (int)$g['id'] ?>/categories" class="stg-btn-sm stg-btn-primary" title="Manage categories">
                                    <i class="fas fa-tags"></i>
                                </a>
                                <form method="POST" action="/admin/support/groups/<?= (int)$g['id'] ?>/delete" onsubmit="return confirm('Delete this group and ALL its categories?')" style="margin:0;">
                                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                    <button type="submit" class="stg-btn-sm stg-btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RIGHT: Add group form -->
        <div>
            <div class="stg-card">
                <div class="stg-card-head">
                    <h3><i class="fas fa-plus-circle" style="color:var(--cyan);margin-right:7px;"></i>Create New Group</h3>
                </div>
                <div class="stg-card-body">
                    <form method="POST" action="/admin/support/groups/create" class="stg-form">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <label>Group Name <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="name" required placeholder="e.g. Engineering Support" class="stg-input">
                        <label>Description <span style="font-weight:400;color:var(--text-secondary);">(optional)</span></label>
                        <input type="text" name="description" placeholder="Short description" class="stg-input">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                            <div>
                                <label>Icon <span style="font-weight:400;font-size:.72rem;">(Font Awesome name)</span></label>
                                <input type="text" name="icon" value="users" placeholder="users" class="stg-input">
                            </div>
                            <div>
                                <label>Color</label>
                                <input type="color" name="color" value="#00f0ff" class="stg-input" style="height:38px;padding:4px 6px;">
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

<?php View::endSection(); ?>
