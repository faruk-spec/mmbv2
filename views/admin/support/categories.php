<?php
/**
 * Admin: Support Template Categories (under a Group)
 */
use Core\View;

View::extend('admin');
View::section('content');
?>

<style>
.stc-page { padding: 28px; }
.stc-hdr  { margin-bottom: 24px; }
.stc-hdr h1 { font-size: 1.4rem; font-weight: 700; color: var(--text-primary); margin: 0 0 4px; display: flex; align-items: center; gap: 10px; }
.stc-grid { display: grid; grid-template-columns: 1fr 360px; gap: 22px; align-items: start; }
@media(max-width:900px){ .stc-grid { grid-template-columns: 1fr; } }
.stc-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; }
.stc-card-head { padding: 15px 18px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; }
.stc-card-head h3 { margin: 0; font-size: .95rem; font-weight: 600; color: var(--text-primary); }
.stc-card-body { padding: 16px 18px; }
.stc-cat-item { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 1px solid var(--border-color); border-radius: 10px; margin-bottom: 10px; background: var(--bg-secondary); }
.stc-cat-icon { width: 36px; height: 36px; border-radius: 9px; background: rgba(0,240,255,.1); color: var(--cyan); display: flex; align-items: center; justify-content: center; font-size: .9rem; flex-shrink: 0; }
.stc-cat-info { flex: 1; min-width: 0; }
.stc-cat-name { font-size: .88rem; font-weight: 600; color: var(--text-primary); }
.stc-cat-desc { font-size: .74rem; color: var(--text-secondary); margin-top: 1px; }
.stc-cat-actions { display: flex; gap: 6px; flex-shrink: 0; }
.stc-btn-sm { padding: 5px 11px; border-radius: 6px; font-size: .75rem; font-weight: 600; cursor: pointer; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
.stc-btn-builder { background: rgba(167,139,250,.1); color: #a78bfa; border: 1px solid rgba(167,139,250,.25); }
.stc-btn-danger  { background: rgba(239,68,68,.08); color: #ef4444; border: 1px solid rgba(239,68,68,.2); }
.stc-form label { display: block; font-size: .78rem; font-weight: 600; color: var(--text-secondary); margin: 10px 0 4px; }
.stc-form label:first-child { margin-top: 0; }
.stc-input { width: 100%; padding: 8px 11px; border: 1px solid var(--border-color); border-radius: 7px; background: var(--bg-card); color: var(--text-primary); font-size: .85rem; outline: none; box-sizing: border-box; }
.stc-input:focus { border-color: var(--cyan); }
.stc-btn-full { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 18px; background: var(--cyan); color: #fff; border: none; border-radius: 7px; font-size: .85rem; font-weight: 600; cursor: pointer; width: 100%; margin-top: 12px; }
.stc-back { display: inline-flex; align-items: center; gap: 6px; color: var(--text-secondary); text-decoration: none; font-size: .83rem; margin-bottom: 16px; }
.stc-back:hover { color: var(--cyan); }
</style>

<div class="stc-page">
    <a href="/admin/support/groups" class="stc-back"><i class="fas fa-arrow-left"></i> All Groups</a>

    <div class="stc-hdr">
        <h1>
            <span style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:9px;background:<?= htmlspecialchars($group['color']) ?>22;color:<?= htmlspecialchars($group['color']) ?>;">
                <i class="fas fa-<?= htmlspecialchars($group['icon']) ?>"></i>
            </span>
            <?= htmlspecialchars($group['name']) ?> — Categories
        </h1>
        <p style="color:var(--text-secondary);font-size:.85rem;margin:4px 0 0;"><?= htmlspecialchars($group['description'] ?? '') ?></p>
    </div>

    <div class="stc-grid">

        <!-- LEFT: Categories list -->
        <div>
            <div class="stc-card">
                <div class="stc-card-head">
                    <h3><i class="fas fa-tags" style="color:var(--cyan);margin-right:7px;"></i>Categories (<?= count($categories) ?>)</h3>
                </div>
                <div class="stc-card-body">
                    <?php if (empty($categories)): ?>
                        <p style="color:var(--text-secondary);font-size:.85rem;text-align:center;padding:20px 0;">No categories yet. Add one →</p>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                        <div class="stc-cat-item">
                            <div class="stc-cat-icon"><i class="fas fa-<?= htmlspecialchars($cat['icon']) ?>"></i></div>
                            <div class="stc-cat-info">
                                <div class="stc-cat-name"><?= htmlspecialchars($cat['name']) ?></div>
                                <?php if (!empty($cat['description'])): ?>
                                    <div class="stc-cat-desc"><?= htmlspecialchars($cat['description']) ?></div>
                                <?php endif; ?>
                                <div style="margin-top:5px;display:flex;gap:5px;flex-wrap:wrap;">
                                    <span style="font-size:.7rem;color:<?= $cat['is_active'] ? '#22c55e' : '#6b7280' ?>;">
                                        <i class="fas fa-circle" style="font-size:.5rem;"></i> <?= $cat['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </div>
                            </div>
                            <div class="stc-cat-actions">
                                <a href="/admin/support/builder/<?= (int)$cat['id'] ?>" class="stc-btn-sm stc-btn-builder" title="Open Form Builder">
                                    <i class="fas fa-wand-magic-sparkles"></i> Builder
                                </a>
                                <form method="POST" action="/admin/support/categories/<?= (int)$cat['id'] ?>/delete" onsubmit="return confirm('Delete this category and its template?')" style="margin:0;">
                                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                    <button type="submit" class="stc-btn-sm stc-btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RIGHT: Add category form -->
        <div>
            <div class="stc-card">
                <div class="stc-card-head">
                    <h3><i class="fas fa-plus-circle" style="color:var(--cyan);margin-right:7px;"></i>Add Category</h3>
                </div>
                <div class="stc-card-body">
                    <form method="POST" action="/admin/support/groups/<?= (int)$group['id'] ?>/categories/create" class="stc-form">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <label>Category Name <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="name" required placeholder="e.g. Bug Report" class="stc-input">
                        <label>Description <span style="font-weight:400;">(optional)</span></label>
                        <input type="text" name="description" placeholder="Short description" class="stc-input">
                        <label>Icon <span style="font-weight:400;font-size:.72rem;">(Font Awesome name)</span></label>
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

<?php View::endSection(); ?>
