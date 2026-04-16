<?php
/**
 * Create Support Ticket (user view)
 */
use Core\View;

View::extend('main');

// Build a map: item_id => fields_schema JSON for JS
$itemsSchemaMap = [];
foreach ($items as $item) {
    if (!empty($item['fields_schema'])) {
        $decoded = json_decode($item['fields_schema'], true);
        if (is_array($decoded) && count($decoded) > 0) {
            $itemsSchemaMap[(int)$item['id']] = $decoded;
        }
    }
}
$itemsSchemaJson = json_encode($itemsSchemaMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
?>

<?php View::section('styles'); ?>
<style>.dashboard-main-content { padding: 0 !important; }</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div style="display:flex;min-height:calc(100vh - 64px);align-items:stretch;">

    <!-- Sidebar -->
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <!-- Main content -->
    <div style="flex:1;padding:24px 28px;min-width:0;max-width:760px;">

            <!-- Flash messages -->
            <?php if (!empty($_SESSION['_flash']['error'])): ?>
            <div style="background:rgba(255,107,107,.08);border:1px solid rgba(255,107,107,.2);color:var(--red);padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:.88rem;">
                <?= htmlspecialchars($_SESSION['_flash']['error']) ?><?php unset($_SESSION['_flash']['error']); ?>
            </div>
            <?php endif; ?>

            <!-- Header -->
            <div style="margin-bottom:22px;">
                <a href="/support" style="color:var(--text-secondary);text-decoration:none;font-size:.83rem;display:inline-flex;align-items:center;gap:6px;margin-bottom:12px;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    Back to My Tickets
                </a>
                <h1 style="font-size:1.4rem;font-weight:700;color:var(--text-primary);margin:0 0 4px;display:flex;align-items:center;gap:10px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color:var(--cyan)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                    Create Support Ticket
                </h1>
                <p style="color:var(--text-secondary);margin:0;font-size:.85rem;">Describe your issue and our team will get back to you.</p>
            </div>

            <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;padding:26px;">
                <form method="POST" action="/support/create" autocomplete="off" id="createTicketForm" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

                    <!-- Template selector -->
                    <?php if (!empty($items)): ?>
                    <div style="margin-bottom:20px;">
                        <label style="display:block;font-weight:600;color:var(--text-primary);font-size:.87rem;margin-bottom:7px;">
                            Issue Template <span style="color:var(--text-secondary);font-weight:400;">(optional)</span>
                        </label>
                        <div style="position:relative;">
                            <select name="template_item_id" id="template_item_id" onchange="applyTemplate(this)"
                                style="width:100%;padding:10px 14px;border:1px solid var(--border-color);border-radius:8px;background:var(--bg-secondary);color:var(--text-primary);font-size:.9rem;outline:none;appearance:none;padding-right:36px;">
                                <option value="">— Select a template (optional) —</option>
                                <?php
                                $grouped = [];
                                foreach ($items as $item) {
                                    $catLabel = $item['category_name'] ?? 'General';
                                    if (!empty($item['department'])) {
                                        $catLabel = $item['department'] . ' / ' . $catLabel;
                                    }
                                    $grouped[$catLabel][] = $item;
                                }
                                foreach ($grouped as $catName => $catItems):
                                ?>
                                <optgroup label="<?= htmlspecialchars($catName) ?>">
                                    <?php foreach ($catItems as $item): ?>
                                    <option value="<?= (int)$item['id'] ?>"
                                        data-priority="<?= htmlspecialchars($item['default_priority']) ?>"
                                        data-name="<?= htmlspecialchars($item['name']) ?>"
                                        data-description="<?= htmlspecialchars($item['description'] ?? '') ?>">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <?php endforeach; ?>
                            </select>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);pointer-events:none;"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Subject -->
                    <div style="margin-bottom:20px;">
                        <label for="subject" style="display:block;font-weight:600;color:var(--text-primary);font-size:.87rem;margin-bottom:7px;">
                            Subject <span style="color:var(--red);">*</span>
                        </label>
                        <input type="text" id="subject" name="subject" required maxlength="255"
                            value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>"
                            placeholder="Brief description of your issue"
                            style="width:100%;padding:10px 14px;border:1px solid var(--border-color);border-radius:8px;background:var(--bg-secondary);color:var(--text-primary);font-size:.9rem;outline:none;box-sizing:border-box;">
                    </div>

                    <!-- Description -->
                    <div style="margin-bottom:20px;">
                        <label for="description" style="display:block;font-weight:600;color:var(--text-primary);font-size:.87rem;margin-bottom:7px;">
                            Description <span style="color:var(--red);">*</span>
                        </label>
                        <textarea id="description" name="description" required rows="6" maxlength="5000"
                            placeholder="Describe your issue in detail. Include error messages, steps to reproduce, etc."
                            style="width:100%;padding:10px 14px;border:1px solid var(--border-color);border-radius:8px;background:var(--bg-secondary);color:var(--text-primary);font-size:.9rem;outline:none;resize:vertical;box-sizing:border-box;"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        <div style="text-align:right;font-size:.72rem;color:var(--text-secondary);margin-top:4px;">Max 5000 characters</div>
                    </div>

                    <!-- Priority -->
                    <div style="margin-bottom:20px;">
                        <label for="priority" style="display:block;font-weight:600;color:var(--text-primary);font-size:.87rem;margin-bottom:7px;">Priority</label>
                        <div style="position:relative;">
                            <select id="priority" name="priority"
                                style="width:100%;padding:10px 14px;border:1px solid var(--border-color);border-radius:8px;background:var(--bg-secondary);color:var(--text-primary);font-size:.9rem;outline:none;appearance:none;padding-right:36px;">
                                <?php foreach ($priorities as $p): ?>
                                <option value="<?= $p ?>" <?= (($_POST['priority'] ?? 'medium') === $p) ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);pointer-events:none;"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                    </div>

                    <!-- Custom fields (rendered dynamically by JS) -->
                    <div id="custom-fields-container" style="display:none;border-top:1px solid var(--border-color);padding-top:18px;margin-bottom:20px;">
                        <div style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--text-secondary);margin-bottom:14px;">Additional Details</div>
                        <div id="custom-fields-inner"></div>
                    </div>

                    <button type="submit"
                        style="width:100%;padding:12px;background:linear-gradient(135deg,var(--cyan),var(--magenta));border:none;border-radius:8px;color:white;font-weight:700;font-size:.95rem;cursor:pointer;letter-spacing:.02em;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:7px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Submit Ticket
                    </button>
                </form>
            </div>
        </div><!-- /main content -->
</div><!-- /support flex wrapper -->

<script>
var ITEMS_SCHEMA = <?= $itemsSchemaJson ?>;

function applyTemplate(sel) {
    var opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) {
        document.getElementById('custom-fields-container').style.display = 'none';
        document.getElementById('custom-fields-inner').innerHTML = '';
        return;
    }
    var name  = opt.getAttribute('data-name') || '';
    var desc  = opt.getAttribute('data-description') || '';
    var prio  = opt.getAttribute('data-priority') || 'medium';
    if (name) document.getElementById('subject').value = name;
    if (desc) document.getElementById('description').value = desc;
    var prioSel = document.getElementById('priority');
    for (var i = 0; i < prioSel.options.length; i++) {
        if (prioSel.options[i].value === prio) { prioSel.selectedIndex = i; break; }
    }
    // Render custom fields
    var itemId = parseInt(opt.value);
    var schema = ITEMS_SCHEMA[itemId] || [];
    renderCustomFields(schema);
}

function renderCustomFields(schema) {
    var container = document.getElementById('custom-fields-container');
    var inner     = document.getElementById('custom-fields-inner');
    inner.innerHTML = '';
    if (!schema || schema.length === 0) {
        container.style.display = 'none';
        return;
    }
    container.style.display = 'block';
    schema.forEach(function(field) {
        var name  = 'custom_' + (field.name || '');
        var label = field.label || field.name || '';
        var req   = field.required ? ' *' : '';
        var reqAttr = field.required ? ' required' : '';
        var wrapper = document.createElement('div');
        wrapper.style.marginBottom = '16px';
        var labelEl = document.createElement('label');
        labelEl.setAttribute('for', name);
        labelEl.style.cssText = 'display:block;font-weight:600;color:var(--text-primary);font-size:.87rem;margin-bottom:7px;';
        labelEl.innerHTML = escHtml(label) + (field.required ? ' <span style="color:var(--red)">*</span>' : '');
        wrapper.appendChild(labelEl);
        var fieldEl;
        var inputStyle = 'width:100%;padding:10px 14px;border:1px solid var(--border-color);border-radius:8px;background:var(--bg-secondary);color:var(--text-primary);font-size:.9rem;outline:none;box-sizing:border-box;';
        if (field.type === 'textarea') {
            fieldEl = document.createElement('textarea');
            fieldEl.rows = 3;
            fieldEl.style.cssText = inputStyle + 'resize:vertical;';
        } else if (field.type === 'dropdown') {
            fieldEl = document.createElement('select');
            fieldEl.style.cssText = inputStyle;
            var blankOpt = document.createElement('option');
            blankOpt.value = '';
            blankOpt.textContent = '— Select —';
            fieldEl.appendChild(blankOpt);
            (field.options || []).forEach(function(opt) {
                var o = document.createElement('option');
                o.value = opt;
                o.textContent = opt;
                fieldEl.appendChild(o);
            });
        } else if (field.type === 'attachment') {
            fieldEl = document.createElement('input');
            fieldEl.type = 'file';
            fieldEl.accept = '.pdf,.png,.jpg,.jpeg,.gif,.webp,.txt,.zip,.doc,.docx,.xlsx,.csv';
            fieldEl.style.cssText = inputStyle;
        } else if (field.type === 'email') {
            fieldEl = document.createElement('input');
            fieldEl.type = 'email';
            fieldEl.placeholder = field.placeholder || '';
            fieldEl.style.cssText = inputStyle;
        } else if (field.type === 'number') {
            fieldEl = document.createElement('input');
            fieldEl.type = 'number';
            fieldEl.placeholder = field.placeholder || '';
            fieldEl.style.cssText = inputStyle;
        } else if (field.type === 'date') {
            fieldEl = document.createElement('input');
            fieldEl.type = 'date';
            fieldEl.style.cssText = inputStyle;
        } else if (field.type === 'checkbox') {
            var checkWrap = document.createElement('label');
            checkWrap.style.cssText = 'display:flex;align-items:center;gap:9px;cursor:pointer;color:var(--text-primary);font-size:.9rem;';
            fieldEl = document.createElement('input');
            fieldEl.type = 'checkbox';
            fieldEl.style.accentColor = 'var(--cyan)';
            fieldEl.style.width = '16px';
            fieldEl.style.height = '16px';
            checkWrap.appendChild(fieldEl);
            checkWrap.appendChild(document.createTextNode(label));
            fieldEl.name  = name;
            fieldEl.id    = name;
            wrapper.appendChild(checkWrap);
            inner.appendChild(wrapper);
            return;
        } else {
            fieldEl = document.createElement('input');
            fieldEl.type = 'text';
            fieldEl.placeholder = field.placeholder || '';
            fieldEl.style.cssText = inputStyle;
        }
        fieldEl.name = name;
        fieldEl.id   = name;
        if (field.required) fieldEl.required = true;
        wrapper.appendChild(fieldEl);
        inner.appendChild(wrapper);
    });
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<?php View::endSection(); ?>
