<?php
/**
 * Admin Support Templates
 */
use Core\View;

View::extend('admin');
View::section('content');
?>

<div style="padding:28px;">
    <div style="margin-bottom:24px;">
        <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-primary,#e8eefc);margin:0 0 4px;">
            <i class="fas fa-folder-tree" style="color:#00f0ff;margin-right:10px;"></i>Support Templates
        </h1>
        <p style="color:var(--text-secondary,#8892a6);margin:0;font-size:.85rem;">Manage issue categories and ticket templates.</p>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

        <!-- Categories -->
        <div>
            <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;overflow:hidden;">
                <div style="padding:16px 18px;border-bottom:1px solid var(--border-color,rgba(255,255,255,.06));display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="margin:0;font-size:.95rem;font-weight:600;color:var(--text-primary,#e8eefc);">Categories</h3>
                    <span style="color:var(--text-secondary,#8892a6);font-size:.8rem;"><?= count($categories) ?> total</span>
                </div>
                <div style="padding:16px 18px;">
                    <?php if (empty($categories)): ?>
                    <p style="color:var(--text-secondary,#8892a6);font-size:.85rem;text-align:center;padding:20px 0;">No categories yet.</p>
                    <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px;">
                        <?php foreach ($categories as $cat): ?>
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:rgba(255,255,255,.03);border:1px solid var(--border-color,rgba(255,255,255,.06));border-radius:8px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <i class="fas fa-<?= htmlspecialchars($cat['icon']) ?>" style="color:#00f0ff;width:16px;"></i>
                                <span style="color:var(--text-primary,#e8eefc);font-size:.88rem;font-weight:500;"><?= htmlspecialchars($cat['name']) ?></span>
                            </div>
                            <form method="POST" action="/admin/support/templates/category/<?= (int)$cat['id'] ?>/delete" onsubmit="return confirm('Delete this category?')">
                                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                <button type="submit" style="background:none;border:none;color:#ff6b6b;cursor:pointer;font-size:.75rem;padding:4px 8px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Add category form -->
                    <div style="border-top:1px solid var(--border-color,rgba(255,255,255,.06));padding-top:14px;">
                        <h4 style="margin:0 0 12px;font-size:.85rem;font-weight:600;color:var(--text-secondary,#8892a6);">Add Category</h4>
                        <form method="POST" action="/admin/support/templates/category/create">
                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                            <input type="text" name="name" required placeholder="Category name"
                                style="width:100%;padding:8px 10px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:6px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.85rem;margin-bottom:8px;box-sizing:border-box;">
                            <input type="text" name="icon" value="folder" placeholder="Font Awesome icon name"
                                style="width:100%;padding:8px 10px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:6px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.85rem;margin-bottom:8px;box-sizing:border-box;">
                            <textarea name="description" placeholder="Description (optional)" rows="2"
                                style="width:100%;padding:8px 10px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:6px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.85rem;margin-bottom:8px;box-sizing:border-box;resize:vertical;"></textarea>
                            <button type="submit" style="width:100%;padding:8px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border:none;border-radius:6px;color:white;font-weight:600;font-size:.85rem;cursor:pointer;">
                                <i class="fas fa-plus" style="margin-right:5px;"></i>Add Category
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items -->
        <div>
            <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;overflow:hidden;">
                <div style="padding:16px 18px;border-bottom:1px solid var(--border-color,rgba(255,255,255,.06));display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="margin:0;font-size:.95rem;font-weight:600;color:var(--text-primary,#e8eefc);">Template Items</h3>
                    <span style="color:var(--text-secondary,#8892a6);font-size:.8rem;"><?= count($items) ?> total</span>
                </div>
                <div style="padding:16px 18px;">
                    <?php if (empty($items)): ?>
                    <p style="color:var(--text-secondary,#8892a6);font-size:.85rem;text-align:center;padding:20px 0;">No template items yet.</p>
                    <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px;">
                        <?php foreach ($items as $item):
                            $pc = ['urgent'=>'#ff6b6b','high'=>'#ff9f43','medium'=>'#00f0ff','low'=>'#8892a6'];
                            $pColor = $pc[$item['default_priority']] ?? '#8892a6';
                        ?>
                        <div style="padding:10px 12px;background:rgba(255,255,255,.03);border:1px solid var(--border-color,rgba(255,255,255,.06));border-radius:8px;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
                                <span style="color:var(--text-primary,#e8eefc);font-size:.88rem;font-weight:500;"><?= htmlspecialchars($item['name']) ?></span>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span style="padding:2px 8px;border-radius:10px;font-size:.7rem;font-weight:600;background:<?= $pColor ?>1a;color:<?= $pColor ?>"><?= ucfirst($item['default_priority']) ?></span>
                                    <form method="POST" action="/admin/support/templates/item/<?= (int)$item['id'] ?>/delete" onsubmit="return confirm('Delete this item?')">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                        <button type="submit" style="background:none;border:none;color:#ff6b6b;cursor:pointer;font-size:.75rem;padding:2px 6px;"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                            <div style="color:var(--text-secondary,#8892a6);font-size:.75rem;"><?= htmlspecialchars($item['category_name'] ?? '') ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Add item form -->
                    <?php if (!empty($categories)): ?>
                    <div style="border-top:1px solid var(--border-color,rgba(255,255,255,.06));padding-top:14px;">
                        <h4 style="margin:0 0 12px;font-size:.85rem;font-weight:600;color:var(--text-secondary,#8892a6);">Add Item</h4>
                        <form method="POST" action="/admin/support/templates/item/create">
                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                            <select name="category_id" required style="width:100%;padding:8px 10px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:6px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.85rem;margin-bottom:8px;">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="name" required placeholder="Item name"
                                style="width:100%;padding:8px 10px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:6px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.85rem;margin-bottom:8px;box-sizing:border-box;">
                            <select name="default_priority" style="width:100%;padding:8px 10px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:6px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.85rem;margin-bottom:8px;">
                                <?php foreach (['low','medium','high','urgent'] as $p): ?>
                                <option value="<?= $p ?>" <?= $p==='medium'?'selected':'' ?>><?= ucfirst($p) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <textarea name="description" placeholder="Description (optional)" rows="2"
                                style="width:100%;padding:8px 10px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:6px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.85rem;margin-bottom:10px;box-sizing:border-box;resize:vertical;"></textarea>

                            <!-- Custom fields builder -->
                            <div style="border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:8px;padding:12px;margin-bottom:10px;background:rgba(255,255,255,.02);">
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                                    <span style="font-size:.8rem;font-weight:600;color:var(--text-secondary,#8892a6);">Custom Fields</span>
                                    <button type="button" onclick="addTemplateField()" style="padding:4px 10px;background:rgba(0,240,255,.12);border:1px solid rgba(0,240,255,.2);border-radius:5px;color:#00f0ff;font-size:.75rem;cursor:pointer;font-weight:600;">
                                        <i class="fas fa-plus" style="margin-right:4px;"></i>Add Field
                                    </button>
                                </div>
                                <div id="template-fields-list" style="display:flex;flex-direction:column;gap:8px;"></div>
                                <div id="template-fields-empty" style="color:var(--text-secondary,#5c6478);font-size:.78rem;text-align:center;padding:6px 0;">No custom fields added yet.</div>
                                <input type="hidden" name="fields_schema" id="fields_schema_input" value="[]">
                            </div>

                            <button type="submit" onclick="serializeFields()" style="width:100%;padding:8px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border:none;border-radius:6px;color:white;font-weight:600;font-size:.85rem;cursor:pointer;">
                                <i class="fas fa-plus" style="margin-right:5px;"></i>Add Item
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <p style="color:var(--text-secondary,#8892a6);font-size:.82rem;text-align:center;margin-top:10px;">Create a category first.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
var templateFields = [];

function addTemplateField() {
    var idx = templateFields.length;
    templateFields.push({type:'text', label:'', name:'', required:false, placeholder:'', options:[]});
    renderFieldsList();
}

function removeTemplateField(idx) {
    templateFields.splice(idx, 1);
    renderFieldsList();
}

function renderFieldsList() {
    var list  = document.getElementById('template-fields-list');
    var empty = document.getElementById('template-fields-empty');
    list.innerHTML = '';
    if (templateFields.length === 0) {
        empty.style.display = 'block';
        return;
    }
    empty.style.display = 'none';
    var inputStyle = 'padding:5px 8px;border:1px solid rgba(255,255,255,.1);border-radius:5px;background:#0c0c12;color:#e8eefc;font-size:.78rem;outline:none;';
    templateFields.forEach(function(f, i) {
        var row = document.createElement('div');
        row.style.cssText = 'background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:7px;padding:10px;';
        var topRow = '<div style="display:flex;gap:6px;margin-bottom:6px;align-items:center;flex-wrap:wrap;">';
        // Type select
        topRow += '<select onchange="updateField('+i+',\'type\',this.value)" style="'+inputStyle+'">'
            + ['text','textarea','dropdown','checkbox'].map(function(t){return'<option value="'+t+'"'+(f.type===t?' selected':'')+'>'+t+'</option>';}).join('')
            + '</select>';
        // Label input
        topRow += '<input type="text" placeholder="Label" value="'+escAttr(f.label)+'" oninput="updateField('+i+',\'label\',this.value)" style="'+inputStyle+'flex:1;">';
        // Name (key) input
        topRow += '<input type="text" placeholder="name_key" value="'+escAttr(f.name)+'" oninput="updateField('+i+',\'name\',this.value)" style="'+inputStyle+'width:90px;">';
        // Required checkbox
        topRow += '<label style="display:flex;align-items:center;gap:4px;font-size:.75rem;color:#8892a6;cursor:pointer;"><input type="checkbox" '+(f.required?'checked':'')+' onchange="updateField('+i+',\'required\',this.checked)" style="accent-color:#00f0ff;"> Req</label>';
        // Remove
        topRow += '<button type="button" onclick="removeTemplateField('+i+')" style="background:none;border:none;color:#ff6b6b;cursor:pointer;padding:2px;font-size:.9rem;">✕</button>';
        topRow += '</div>';

        var extraRow = '';
        if (f.type === 'text' || f.type === 'textarea') {
            extraRow = '<input type="text" placeholder="Placeholder text" value="'+escAttr(f.placeholder)+'" oninput="updateField('+i+',\'placeholder\',this.value)" style="'+inputStyle+'width:100%;box-sizing:border-box;">';
        } else if (f.type === 'dropdown') {
            extraRow = '<input type="text" placeholder="Options (comma-separated)" value="'+escAttr((f.options||[]).join(','))+'" oninput="updateFieldOptions('+i+',this.value)" style="'+inputStyle+'width:100%;box-sizing:border-box;">';
        }

        row.innerHTML = topRow + extraRow;
        list.appendChild(row);
    });
}

function updateField(idx, key, val) {
    templateFields[idx][key] = val;
}
function updateFieldOptions(idx, val) {
    templateFields[idx].options = val.split(',').map(function(s){return s.trim();}).filter(Boolean);
}

function serializeFields() {
    // Auto-generate name from label if empty
    templateFields.forEach(function(f) {
        if (!f.name && f.label) {
            f.name = f.label.toLowerCase().replace(/[^a-z0-9]+/g,'_');
        }
    });
    document.getElementById('fields_schema_input').value = JSON.stringify(templateFields);
}

function escAttr(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>

<?php View::endSection(); ?>
