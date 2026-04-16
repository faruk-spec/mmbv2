<?php
/**
 * Admin Support Templates — redesigned
 */
use Core\View;

View::extend('admin');
View::section('content');
?>

<style>
.tpl-page { padding: 28px; }
.tpl-header { margin-bottom: 24px; }
.tpl-header h1 { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin: 0 0 4px; display: flex; align-items: center; gap: 10px; }
.tpl-header h1 i { color: var(--cyan); }
.tpl-header p { color: var(--text-secondary); margin: 0; font-size: .85rem; }
.tpl-grid { display: grid; grid-template-columns: 320px 1fr; gap: 22px; align-items: start; }
@media(max-width:900px){ .tpl-grid { grid-template-columns: 1fr; } }

.tpl-panel { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; }
.tpl-panel-head { padding: 15px 18px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; }
.tpl-panel-head h3 { margin: 0; font-size: .95rem; font-weight: 600; color: var(--text-primary); }
.tpl-panel-head .cnt { font-size: .78rem; color: var(--text-secondary); background: var(--bg-secondary); padding: 2px 8px; border-radius: 20px; }
.tpl-panel-body { padding: 16px 18px; }

.cat-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 8px; }
.cat-item-left { display: flex; align-items: center; gap: 10px; }
.cat-item-left i { width: 16px; color: var(--cyan); }
.cat-item-name { font-size: .88rem; font-weight: 500; color: var(--text-primary); }
.cat-item-del { background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: .75rem; padding: 4px 8px; border-radius: 5px; }
.cat-item-del:hover { color: #ef4444; }

.tpl-item { border: 1px solid var(--border-color); border-radius: 10px; margin-bottom: 10px; background: var(--bg-secondary); }
.tpl-item-head { display: flex; align-items: center; justify-content: space-between; padding: 11px 14px; cursor: pointer; gap: 10px; user-select: none; }
.tpl-item-head:hover { background: var(--hover-bg); border-radius: 10px; }
.tpl-item.open .tpl-item-head { border-radius: 10px 10px 0 0; }
.tpl-item-info { flex: 1; min-width: 0; }
.tpl-item-name { font-size: .88rem; font-weight: 600; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tpl-item-meta { font-size: .75rem; color: var(--text-secondary); margin-top: 2px; }
.tpl-item-badges { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
.badge-pri { padding: 2px 8px; border-radius: 10px; font-size: .7rem; font-weight: 600; }
.badge-pri.urgent { background: rgba(239,68,68,.12); color: #ef4444; }
.badge-pri.high    { background: rgba(249,115,22,.12); color: #f97316; }
.badge-pri.medium  { background: rgba(59,130,246,.12); color: #3b82f6; }
.badge-pri.low     { background: rgba(107,114,128,.12); color: #6b7280; }
.tpl-item-arrow { font-size: .7rem; color: var(--text-secondary); transition: transform .2s; flex-shrink: 0; }
.tpl-item.open .tpl-item-arrow { transform: rotate(180deg); }
.tpl-item-body { padding: 12px 14px; border-top: 1px solid var(--border-color); display: none; }
.tpl-item.open .tpl-item-body { display: block; }

.field-chip { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 12px; font-size: .72rem; font-weight: 500; background: rgba(14,165,233,.1); color: #0ea5e9; border: 1px solid rgba(14,165,233,.2); margin: 0 4px 4px 0; }

.tpl-form label { display: block; font-size: .78rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px; margin-top: 10px; }
.tpl-form label:first-child { margin-top: 0; }
.tpl-input { width: 100%; padding: 8px 11px; border: 1px solid var(--border-color); border-radius: 7px; background: var(--bg-card); color: var(--text-primary); font-size: .85rem; outline: none; box-sizing: border-box; }
.tpl-input:focus { border-color: var(--cyan); }
.tpl-select { width: 100%; padding: 8px 11px; border: 1px solid var(--border-color); border-radius: 7px; background: var(--bg-card); color: var(--text-primary); font-size: .85rem; outline: none; box-sizing: border-box; }
.tpl-textarea { width: 100%; padding: 8px 11px; border: 1px solid var(--border-color); border-radius: 7px; background: var(--bg-card); color: var(--text-primary); font-size: .85rem; outline: none; resize: vertical; box-sizing: border-box; }
.tpl-btn-primary { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 18px; background: var(--cyan); color: #fff; border: none; border-radius: 7px; font-size: .85rem; font-weight: 600; cursor: pointer; width: 100%; margin-top: 12px; }
.tpl-btn-primary:hover { opacity: .9; }
.tpl-divider { font-size: .75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: .06em; margin: 18px 0 10px; padding-bottom: 6px; border-bottom: 1px solid var(--border-color); }

.cf-row { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 12px; margin-bottom: 8px; }
.cf-row-top { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; margin-bottom: 6px; }
.cf-si { padding: 5px 8px; border: 1px solid var(--border-color); border-radius: 5px; background: var(--bg-secondary); color: var(--text-primary); font-size: .78rem; outline: none; }
.cf-req { display: flex; align-items: center; gap: 4px; font-size: .75rem; color: var(--text-secondary); cursor: pointer; white-space: nowrap; }
.cf-rm { background: none; border: none; color: var(--text-secondary); cursor: pointer; padding: 2px; font-size: .9rem; margin-left: auto; }
.cf-rm:hover { color: #ef4444; }
.cf-add { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: rgba(14,165,233,.1); border: 1px solid rgba(14,165,233,.25); color: #0ea5e9; border-radius: 6px; font-size: .78rem; font-weight: 600; cursor: pointer; }
</style>

<div class="tpl-page">
    <div class="tpl-header">
        <h1><i class="fas fa-folder-tree"></i> Support Templates</h1>
        <p>Manage issue categories and template items used when customers create tickets.</p>
    </div>

    <div class="tpl-grid">

        <!-- LEFT: Categories -->
        <div>
            <div class="tpl-panel">
                <div class="tpl-panel-head">
                    <h3><i class="fas fa-tags" style="color:var(--cyan);margin-right:7px;"></i>Categories</h3>
                    <span class="cnt"><?= count($categories) ?></span>
                </div>
                <div class="tpl-panel-body">
                    <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $cat): ?>
                    <div class="cat-item">
                        <div class="cat-item-left">
                            <i class="fas fa-<?= htmlspecialchars($cat['icon']) ?>"></i>
                            <span class="cat-item-name"><?= htmlspecialchars($cat['name']) ?></span>
                        </div>
                        <form method="POST" action="/admin/support/templates/category/<?= (int)$cat['id'] ?>/delete" onsubmit="return confirm('Delete this category and all its items?')" style="margin:0;">
                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                            <button type="submit" class="cat-item-del" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <p style="color:var(--text-secondary);font-size:.85rem;text-align:center;padding:14px 0 6px;">No categories yet.</p>
                    <?php endif; ?>

                    <div class="tpl-divider">Add New Category</div>
                    <form method="POST" action="/admin/support/templates/category/create" class="tpl-form">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <label>Category Name</label>
                        <input type="text" name="name" required placeholder="e.g. Billing" class="tpl-input">
                        <label>Icon <span style="font-weight:400;color:var(--text-secondary);">(Font Awesome name, e.g. credit-card)</span></label>
                        <input type="text" name="icon" value="folder" placeholder="folder" class="tpl-input">
                        <label>Description <span style="font-weight:400;">(optional)</span></label>
                        <textarea name="description" rows="2" placeholder="Short description..." class="tpl-textarea"></textarea>
                        <button type="submit" class="tpl-btn-primary"><i class="fas fa-plus"></i>Add Category</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- RIGHT: Items + Add item -->
        <div>
            <div class="tpl-panel" style="margin-bottom:22px;">
                <div class="tpl-panel-head">
                    <h3><i class="fas fa-list-check" style="color:var(--cyan);margin-right:7px;"></i>Template Items</h3>
                    <span class="cnt"><?= count($items) ?></span>
                </div>
                <div class="tpl-panel-body">
                    <?php if (empty($items)): ?>
                    <p style="color:var(--text-secondary);font-size:.85rem;text-align:center;padding:24px 0;">No template items yet. Add one below.</p>
                    <?php else: ?>
                    <?php foreach ($items as $item):
                        $pri = htmlspecialchars($item['default_priority'] ?? 'medium');
                        $fields = [];
                        try { $fields = json_decode($item['fields_schema'] ?? '[]', true) ?: []; } catch(\Throwable $e) { $fields = []; }
                    ?>
                    <div class="tpl-item">
                        <div class="tpl-item-head" onclick="this.closest('.tpl-item').classList.toggle('open')">
                            <div class="tpl-item-info">
                                <div class="tpl-item-name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="tpl-item-meta"><?= htmlspecialchars($item['category_name'] ?? '—') ?> &bull; <?= count($fields) ?> field<?= count($fields) !== 1 ? 's' : '' ?></div>
                            </div>
                            <div class="tpl-item-badges">
                                <span class="badge-pri <?= $pri ?>"><?= ucfirst($pri) ?></span>
                                <i class="fas fa-chevron-down tpl-item-arrow"></i>
                            </div>
                        </div>
                        <div class="tpl-item-body">
                            <?php if (!empty($item['description'])): ?>
                            <p style="color:var(--text-secondary);font-size:.82rem;margin:0 0 10px;"><?= htmlspecialchars($item['description']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($fields)): ?>
                            <div style="margin-bottom:10px;">
                                <span style="font-size:.73rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.05em;">Custom Fields</span>
                                <div style="margin-top:8px;">
                                <?php foreach ($fields as $f):
                                    $ftype = $f['type'] ?? 'text';
                                    $ficon = $ftype === 'checkbox' ? 'check-square' : ($ftype === 'dropdown' ? 'list-ul' : ($ftype === 'textarea' ? 'align-left' : 'font'));
                                ?>
                                <span class="field-chip">
                                    <i class="fas fa-<?= $ficon ?>"></i>
                                    <?= htmlspecialchars($f['label'] ?? $f['name'] ?? '') ?>
                                    <?php if (!empty($f['required'])): ?><span style="color:#ef4444;font-size:.65rem;">*</span><?php endif; ?>
                                </span>
                                <?php endforeach; ?>
                                </div>
                            </div>
                            <?php else: ?>
                            <p style="color:var(--text-secondary);font-size:.78rem;margin:0 0 10px;">No custom fields defined.</p>
                            <?php endif; ?>
                            <form method="POST" action="/admin/support/templates/item/<?= (int)$item['id'] ?>/delete" onsubmit="return confirm('Delete this template item?')">
                                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                <button type="submit" style="display:inline-flex;align-items:center;gap:5px;padding:5px 14px;background:rgba(239,68,68,.08);color:#ef4444;border:1px solid rgba(239,68,68,.2);border-radius:6px;font-size:.75rem;font-weight:500;cursor:pointer;">
                                    <i class="fas fa-trash"></i> Delete Item
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($categories)): ?>
            <div class="tpl-panel">
                <div class="tpl-panel-head">
                    <h3><i class="fas fa-plus-circle" style="color:var(--cyan);margin-right:7px;"></i>Add Template Item</h3>
                </div>
                <div class="tpl-panel-body">
                    <form method="POST" action="/admin/support/templates/item/create" class="tpl-form" id="addItemForm">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

                        <label>Category</label>
                        <select name="category_id" required class="tpl-select">
                            <option value="">— Select —</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label>Item Name</label>
                        <input type="text" name="name" required placeholder="e.g. Payment failed" class="tpl-input">

                        <label>Default Priority</label>
                        <select name="default_priority" class="tpl-select">
                            <?php foreach (['low','medium','high','urgent'] as $p): ?>
                            <option value="<?= $p ?>" <?= $p==='medium'?'selected':'' ?>><?= ucfirst($p) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label>Description <span style="font-weight:400;">(optional)</span></label>
                        <textarea name="description" rows="2" placeholder="What is this template for?" class="tpl-textarea"></textarea>

                        <div style="margin-top:16px;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                                <span style="font-size:.75rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.05em;">Custom Fields</span>
                                <button type="button" onclick="addCF()" class="cf-add"><i class="fas fa-plus"></i>Add Field</button>
                            </div>
                            <div id="cf-list"></div>
                            <div id="cf-empty" style="color:var(--text-secondary);font-size:.78rem;text-align:center;padding:8px 0;">No custom fields added yet.</div>
                            <input type="hidden" name="fields_schema" id="fields_schema_input" value="[]">
                        </div>

                        <button type="submit" class="tpl-btn-primary" onclick="serializeCF()"><i class="fas fa-plus"></i>Add Template Item</button>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="tpl-panel">
                <div class="tpl-panel-body" style="text-align:center;padding:30px;color:var(--text-secondary);font-size:.9rem;">
                    <i class="fas fa-info-circle" style="font-size:1.5rem;display:block;margin-bottom:10px;opacity:.4;"></i>
                    Create at least one category first before adding template items.
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
(function () {
    var cf = [];

    window.addCF = function () {
        cf.push({ type: 'text', label: '', name: '', required: false, placeholder: '', options: [] });
        render();
    };

    function removeCF(i) { cf.splice(i, 1); render(); }
    function updateCF(i, k, v) { cf[i][k] = v; }
    function updateOpts(i, v) { cf[i].options = v.split(',').map(function(s){return s.trim();}).filter(Boolean); }

    function render() {
        var list = document.getElementById('cf-list');
        var empty = document.getElementById('cf-empty');
        if (!list) return;
        list.innerHTML = '';
        if (!cf.length) { empty.style.display = 'block'; return; }
        empty.style.display = 'none';
        var si = 'class="cf-si"';
        cf.forEach(function (f, i) {
            var row = document.createElement('div');
            row.className = 'cf-row';
            var typeOpts = ['text','textarea','dropdown','checkbox'].map(function (t) {
                return '<option value="'+t+'"'+(f.type===t?' selected':'')+'>'+t+'</option>';
            }).join('');
            var top = '<div class="cf-row-top">';
            top += '<select '+si+' onchange="(function(el){cf['+i+'].type=el.value;window.__cfRender();})(this)">'+typeOpts+'</select>';
            top += '<input type="text" '+si+' placeholder="Label" style="flex:1" value="'+ea(f.label)+'" oninput="cf['+i+'].label=this.value">';
            top += '<input type="text" '+si+' placeholder="key_name" style="width:90px" value="'+ea(f.name)+'" oninput="cf['+i+'].name=this.value">';
            top += '<label class="cf-req"><input type="checkbox" '+(f.required?'checked':'')+' onchange="cf['+i+'].required=this.checked"> Required</label>';
            top += '<button type="button" class="cf-rm" onclick="window.__cfRemove('+i+')">&#x2715;</button>';
            top += '</div>';
            var extra = '';
            if (f.type === 'text' || f.type === 'textarea') {
                extra = '<input type="text" '+si+' style="width:100%;box-sizing:border-box" placeholder="Placeholder text" value="'+ea(f.placeholder)+'" oninput="cf['+i+'].placeholder=this.value">';
            } else if (f.type === 'dropdown') {
                extra = '<input type="text" '+si+' style="width:100%;box-sizing:border-box" placeholder="Options (comma-separated)" value="'+ea((f.options||[]).join(','))+'" oninput="window.__cfOpts('+i+',this.value)">';
            }
            row.innerHTML = top + extra;
            list.appendChild(row);
        });
    }

    window.__cfRender = render;
    window.__cfRemove = function (i) { cf.splice(i, 1); render(); };
    window.__cfOpts   = function (i, v) { cf[i].options = v.split(',').map(function(s){return s.trim();}).filter(Boolean); };

    window.serializeCF = function () {
        cf.forEach(function (f) {
            if (!f.name && f.label) f.name = f.label.toLowerCase().replace(/[^a-z0-9]+/g,'_');
        });
        var el = document.getElementById('fields_schema_input');
        if (el) el.value = JSON.stringify(cf);
    };

    function ea(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
}());
</script>

<?php View::endSection(); ?>
