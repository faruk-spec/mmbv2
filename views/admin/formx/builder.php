<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<?php if (Helpers::hasFlash('success')): ?>
<div style="background:rgba(0,255,136,.1);border:1px solid var(--green);color:var(--green);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
    <i class="fas fa-check-circle"></i> <?= View::e(Helpers::getFlash('success')) ?>
</div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
<div style="background:rgba(255,107,107,.1);border:1px solid var(--red);color:var(--red);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
    <i class="fas fa-exclamation-circle"></i> <?= View::e(Helpers::getFlash('error')) ?>
</div>
<?php endif; ?>

<!-- Breadcrumb -->
<div style="margin-bottom:16px;">
    <a href="/admin/formx" style="color:var(--text-secondary);text-decoration:none;font-size:.875rem;" onmouseover="this.style.color='var(--cyan)'" onmouseout="this.style.color='var(--text-secondary)'">
        <i class="fas fa-arrow-left"></i> Back to Forms
    </a>
</div>

<h1 style="font-size:1.3rem;font-weight:700;margin-bottom:6px;"><?= View::e($title) ?></h1>
<p style="color:var(--text-secondary);font-size:.875rem;margin-bottom:24px;">Drag fields from the left panel onto the canvas to build your form.</p>

<!-- Hidden form that submits the serialised JSON -->
<form id="builderForm" method="POST" action="<?= View::e($action) ?>">
    <input type="hidden" name="_csrf_token" value="<?= Security::generateCsrfToken() ?>">
    <input type="hidden" name="id" value="<?= $form ? (int)$form['id'] : '' ?>">
    <input type="hidden" name="fields_json"   id="fieldsJsonInput"   value="<?= View::e(isset($form['fields'])   ? json_encode($form['fields'])   : '[]') ?>">
    <input type="hidden" name="settings_json" id="settingsJsonInput" value="<?= View::e(isset($form['settings']) ? json_encode($form['settings']) : '{}') ?>">

    <div style="display:grid;grid-template-columns:260px 1fr 300px;gap:20px;align-items:start;">

        <!-- ─── Left Panel: Field Palette ─── -->
        <div>
            <div class="card" style="padding:16px;">
                <h3 style="font-size:.85rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px;">Form Settings</h3>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div>
                        <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;">Form Title *</label>
                        <input type="text" name="title" id="formTitle" required
                               value="<?= View::e($form['title'] ?? '') ?>"
                               placeholder="Contact Form"
                               style="width:100%;padding:8px 10px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:6px;color:var(--text-primary);font-size:.85rem;">
                    </div>
                    <div>
                        <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;">Description</label>
                        <textarea name="description" rows="2" placeholder="Optional description shown above the form"
                                  style="width:100%;padding:8px 10px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:6px;color:var(--text-primary);font-size:.85rem;resize:vertical;"><?= View::e($form['description'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;">Status</label>
                        <select name="status" style="width:100%;padding:8px 10px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:6px;color:var(--text-primary);font-size:.85rem;">
                            <option value="draft"    <?= ($form['status'] ?? 'draft') === 'draft'    ? 'selected' : '' ?>>Draft</option>
                            <option value="active"   <?= ($form['status'] ?? '') === 'active'   ? 'selected' : '' ?>>Active (Public)</option>
                            <option value="inactive" <?= ($form['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;">Success Message</label>
                        <textarea name="setting_success_message" rows="2"
                                  placeholder="Thank you! Your response has been submitted."
                                  style="width:100%;padding:8px 10px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:6px;color:var(--text-primary);font-size:.85rem;resize:vertical;"
                                  id="settingSuccessMessage"><?= View::e($form['settings']['success_message'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;">Redirect URL <small style="font-weight:400;">(after submit)</small></label>
                        <input type="text" id="settingRedirectUrl" placeholder="https://example.com/thank-you"
                               value="<?= View::e($form['settings']['redirect_url'] ?? '') ?>"
                               style="width:100%;padding:8px 10px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:6px;color:var(--text-primary);font-size:.85rem;">
                    </div>
                    <div>
                        <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;">Notify Email</label>
                        <input type="email" id="settingNotifyEmail" placeholder="admin@example.com"
                               value="<?= View::e($form['settings']['notify_email'] ?? '') ?>"
                               style="width:100%;padding:8px 10px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:6px;color:var(--text-primary);font-size:.85rem;">
                    </div>
                </div>
            </div>

            <div class="card" style="padding:16px;margin-top:16px;">
                <h3 style="font-size:.85rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px;">Add Fields</h3>
                <p style="font-size:.78rem;color:var(--text-secondary);margin-bottom:12px;">Drag onto the canvas →</p>
                <div id="fieldPalette" style="display:flex;flex-direction:column;gap:8px;">
                    <?php
                    $fieldTypes = [
                        ['type'=>'text',     'icon'=>'fa-font',           'label'=>'Text'],
                        ['type'=>'textarea', 'icon'=>'fa-align-left',     'label'=>'Textarea'],
                        ['type'=>'email',    'icon'=>'fa-envelope',       'label'=>'Email'],
                        ['type'=>'phone',    'icon'=>'fa-phone',          'label'=>'Phone'],
                        ['type'=>'number',   'icon'=>'fa-hashtag',        'label'=>'Number'],
                        ['type'=>'url',      'icon'=>'fa-link',           'label'=>'URL'],
                        ['type'=>'date',     'icon'=>'fa-calendar',       'label'=>'Date'],
                        ['type'=>'time',     'icon'=>'fa-clock',          'label'=>'Time'],
                        ['type'=>'select',   'icon'=>'fa-caret-square-down','label'=>'Dropdown'],
                        ['type'=>'radio',    'icon'=>'fa-dot-circle',     'label'=>'Radio Buttons'],
                        ['type'=>'checkbox', 'icon'=>'fa-check-square',   'label'=>'Checkboxes'],
                        ['type'=>'file',     'icon'=>'fa-file-upload',    'label'=>'File Upload'],
                        ['type'=>'heading',  'icon'=>'fa-heading',        'label'=>'Heading'],
                        ['type'=>'paragraph','icon'=>'fa-paragraph',      'label'=>'Paragraph'],
                        ['type'=>'divider',  'icon'=>'fa-minus',          'label'=>'Divider'],
                        ['type'=>'hidden',   'icon'=>'fa-eye-slash',      'label'=>'Hidden Field'],
                        ['type'=>'rating',   'icon'=>'fa-star',           'label'=>'Rating'],
                    ];
                    foreach ($fieldTypes as $ft): ?>
                    <div class="palette-item" draggable="true" data-field-type="<?= $ft['type'] ?>"
                         style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;cursor:grab;user-select:none;transition:all .2s;"
                         onmouseover="this.style.borderColor='var(--cyan)';this.style.color='var(--cyan)'"
                         onmouseout="this.style.borderColor='var(--border-color)';this.style.color='var(--text-primary)'">
                        <i class="fas <?= $ft['icon'] ?>" style="width:16px;text-align:center;font-size:.85rem;"></i>
                        <span style="font-size:.85rem;font-weight:500;"><?= $ft['label'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ─── Center: Canvas ─── -->
        <div>
            <div class="card" style="padding:20px;min-height:500px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <h3 style="font-size:.9rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.08em;">Form Canvas</h3>
                    <div style="display:flex;gap:8px;">
                        <button type="button" onclick="clearCanvas()" style="padding:6px 12px;background:transparent;border:1px solid var(--red);border-radius:6px;color:var(--red);cursor:pointer;font-size:.8rem;">
                            <i class="fas fa-trash"></i> Clear
                        </button>
                        <button type="submit" style="padding:6px 16px;background:linear-gradient(135deg,var(--cyan),var(--purple));border:none;border-radius:6px;color:#fff;cursor:pointer;font-size:.85rem;font-weight:600;">
                            <i class="fas fa-save"></i> Save Form
                        </button>
                    </div>
                </div>

                <div id="formCanvas"
                     style="min-height:400px;border:2px dashed var(--border-color);border-radius:10px;padding:20px;transition:border-color .2s;">
                    <div id="canvasPlaceholder" style="text-align:center;padding:60px 20px;color:var(--text-secondary);pointer-events:none;">
                        <i class="fas fa-hand-pointer" style="font-size:2.5rem;opacity:.3;margin-bottom:12px;display:block;"></i>
                        <p style="font-size:.875rem;">Drag fields here to build your form</p>
                    </div>
                    <!-- Rendered fields go here -->
                </div>

                <!-- Form preview note -->
                <?php if ($form): ?>
                <div style="margin-top:16px;padding:12px 16px;background:var(--bg-secondary);border-radius:8px;font-size:.8rem;color:var(--text-secondary);">
                    <i class="fas fa-link" style="color:var(--cyan);margin-right:6px;"></i>
                    Public URL: <a href="/forms/<?= View::e($form['slug']) ?>" target="_blank" style="color:var(--cyan);text-decoration:none;">/forms/<?= View::e($form['slug']) ?></a>
                    &nbsp;|&nbsp;
                    <a href="/admin/formx/<?= $form['id'] ?>/submissions" style="color:var(--cyan);text-decoration:none;">
                        <i class="fas fa-inbox"></i> View Submissions (<?= (int)$form['submissions_count'] ?>)
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ─── Right Panel: Field Editor ─── -->
        <div>
            <div class="card" id="fieldEditorPanel" style="padding:16px;display:none;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                    <h3 style="font-size:.85rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.08em;">Field Settings</h3>
                    <button type="button" onclick="closeFieldEditor()" style="background:none;border:none;color:var(--text-secondary);cursor:pointer;font-size:1rem;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="fieldEditorContent" style="display:flex;flex-direction:column;gap:12px;">
                    <!-- dynamically injected by JS -->
                </div>
                <div style="margin-top:16px;display:flex;gap:8px;">
                    <button type="button" onclick="saveFieldEdits()" style="flex:1;padding:8px;background:var(--cyan);color:#000;border:none;border-radius:6px;cursor:pointer;font-size:.85rem;font-weight:600;">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <button type="button" onclick="deleteSelectedField()" style="padding:8px 12px;background:transparent;border:1px solid var(--red);border-radius:6px;color:var(--red);cursor:pointer;font-size:.85rem;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card" id="fieldEditorEmpty" style="padding:20px;text-align:center;color:var(--text-secondary);">
                <i class="fas fa-mouse-pointer" style="font-size:2rem;opacity:.3;display:block;margin-bottom:10px;"></i>
                <p style="font-size:.8rem;">Click a field on the canvas to edit its settings.</p>
            </div>
        </div>

    </div><!-- /grid -->
</form>

<style>
.canvas-field {
    position: relative;
    padding: 14px 16px;
    margin-bottom: 10px;
    background: var(--bg-secondary);
    border: 2px solid var(--border-color);
    border-radius: 10px;
    cursor: pointer;
    transition: border-color .2s, box-shadow .2s;
    user-select: none;
}
.canvas-field:hover { border-color: var(--cyan); }
.canvas-field.selected { border-color: var(--cyan); box-shadow: 0 0 0 3px rgba(0,240,255,.15); }
.canvas-field .field-drag-handle {
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--border-color);
    cursor: grab;
    font-size: .9rem;
}
.canvas-field .field-actions {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    gap: 6px;
    opacity: 0;
    transition: opacity .2s;
}
.canvas-field:hover .field-actions { opacity: 1; }
.canvas-field .field-label {
    font-weight: 600;
    font-size: .875rem;
    color: var(--text-primary);
    padding-left: 24px;
    padding-right: 80px;
}
.canvas-field .field-type-badge {
    font-size: .72rem;
    padding: 2px 8px;
    border-radius: 4px;
    background: var(--bg-primary);
    color: var(--text-secondary);
    margin-left: 8px;
    vertical-align: middle;
}
.canvas-field .field-required-badge {
    font-size: .72rem;
    padding: 2px 6px;
    border-radius: 4px;
    background: rgba(255,107,107,.15);
    color: var(--red);
    margin-left: 4px;
    vertical-align: middle;
}
#formCanvas.drag-over { border-color: var(--cyan); background: rgba(0,240,255,.03); }
</style>

<script>
// ─── State ───────────────────────────────────────────────────────────────────
let fields = <?= json_encode(isset($form['fields']) ? $form['fields'] : []) ?>;
let selectedFieldIdx = null;
let dragSrcFieldIdx  = null;  // index of canvas field being reordered

// ─── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    renderCanvas();
    setupPaletteDrag();
    setupCanvasDrop();
});

// ─── Palette drag ─────────────────────────────────────────────────────────────
function setupPaletteDrag() {
    document.querySelectorAll('.palette-item').forEach(function(el) {
        el.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('field-type', el.dataset.fieldType);
            e.dataTransfer.setData('source', 'palette');
        });
    });
}

// ─── Canvas drop ──────────────────────────────────────────────────────────────
function setupCanvasDrop() {
    var canvas = document.getElementById('formCanvas');
    canvas.addEventListener('dragover', function(e) {
        e.preventDefault();
        canvas.classList.add('drag-over');
    });
    canvas.addEventListener('dragleave', function() {
        canvas.classList.remove('drag-over');
    });
    canvas.addEventListener('drop', function(e) {
        e.preventDefault();
        canvas.classList.remove('drag-over');
        var source = e.dataTransfer.getData('source');
        if (source === 'palette') {
            var type = e.dataTransfer.getData('field-type');
            addField(type);
        }
    });
}

// ─── Add field ────────────────────────────────────────────────────────────────
function addField(type) {
    var defaults = {
        id:          'field_' + Date.now(),
        type:        type,
        name:        'field_' + (fields.length + 1),
        label:       labelFromType(type),
        placeholder: '',
        required:    false,
        options:     ['Option 1', 'Option 2'],
        content:     '',
        rows:        4,
    };
    fields.push(defaults);
    renderCanvas();
    selectField(fields.length - 1);
    syncHidden();
}

function labelFromType(type) {
    var map = {
        text:'Text Field', textarea:'Long Answer', email:'Email Address',
        phone:'Phone Number', number:'Number', url:'Website URL',
        date:'Date', time:'Time', select:'Dropdown', radio:'Radio Buttons',
        checkbox:'Checkboxes', file:'File Upload', heading:'Heading',
        paragraph:'Paragraph Text', divider:'Divider', hidden:'Hidden Field',
        rating:'Rating',
    };
    return map[type] || 'Field';
}

// ─── Render canvas ────────────────────────────────────────────────────────────
function renderCanvas() {
    var canvas = document.getElementById('formCanvas');
    var placeholder = document.getElementById('canvasPlaceholder');

    // Remove existing field elements
    canvas.querySelectorAll('.canvas-field').forEach(function(el) { el.remove(); });

    if (fields.length === 0) {
        if (placeholder) placeholder.style.display = 'block';
        return;
    }
    if (placeholder) placeholder.style.display = 'none';

    fields.forEach(function(field, idx) {
        var el = document.createElement('div');
        el.className = 'canvas-field' + (idx === selectedFieldIdx ? ' selected' : '');
        el.dataset.idx = idx;
        el.draggable = true;

        var typeIcon = iconForType(field.type);
        var isLayout = ['heading','paragraph','divider'].includes(field.type);

        el.innerHTML =
            '<span class="field-drag-handle"><i class="fas fa-grip-vertical"></i></span>' +
            '<span class="field-label">' +
                '<i class="fas ' + typeIcon + '" style="margin-right:8px;opacity:.5;"></i>' +
                escHtml(field.label || field.name || '(untitled)') +
                '<span class="field-type-badge">' + escHtml(field.type) + '</span>' +
                (!isLayout && field.required ? '<span class="field-required-badge">Required</span>' : '') +
            '</span>' +
            '<div class="field-actions">' +
                '<button type="button" onclick="moveField(' + idx + ',-1)" title="Move Up" style="background:none;border:none;color:var(--text-secondary);cursor:pointer;"><i class="fas fa-arrow-up"></i></button>' +
                '<button type="button" onclick="moveField(' + idx + ',1)"  title="Move Down" style="background:none;border:none;color:var(--text-secondary);cursor:pointer;"><i class="fas fa-arrow-down"></i></button>' +
                '<button type="button" onclick="duplicateField(' + idx + ')" title="Duplicate" style="background:none;border:none;color:var(--purple);cursor:pointer;"><i class="fas fa-copy"></i></button>' +
                '<button type="button" onclick="removeField(' + idx + ')"  title="Remove" style="background:none;border:none;color:var(--red);cursor:pointer;"><i class="fas fa-trash"></i></button>' +
            '</div>';

        el.addEventListener('click', function(e) {
            if (e.target.closest('button')) return;
            selectField(idx);
        });

        // Reorder drag
        el.addEventListener('dragstart', function(e) {
            dragSrcFieldIdx = idx;
            e.dataTransfer.setData('source', 'canvas');
            e.dataTransfer.effectAllowed = 'move';
        });
        el.addEventListener('dragover', function(e) {
            e.preventDefault();
            el.style.borderColor = 'var(--magenta)';
        });
        el.addEventListener('dragleave', function() {
            el.style.borderColor = idx === selectedFieldIdx ? 'var(--cyan)' : 'var(--border-color)';
        });
        el.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var source = e.dataTransfer.getData('source');
            if (source === 'canvas' && dragSrcFieldIdx !== null && dragSrcFieldIdx !== idx) {
                var moved = fields.splice(dragSrcFieldIdx, 1)[0];
                var target = dragSrcFieldIdx < idx ? idx - 1 : idx;
                fields.splice(target, 0, moved);
                if (selectedFieldIdx === dragSrcFieldIdx) selectedFieldIdx = target;
                dragSrcFieldIdx = null;
                renderCanvas();
                syncHidden();
            }
        });

        canvas.appendChild(el);
    });
}

function iconForType(type) {
    var map = {
        text:'fa-font', textarea:'fa-align-left', email:'fa-envelope',
        phone:'fa-phone', number:'fa-hashtag', url:'fa-link',
        date:'fa-calendar', time:'fa-clock', select:'fa-caret-square-down',
        radio:'fa-dot-circle', checkbox:'fa-check-square', file:'fa-file-upload',
        heading:'fa-heading', paragraph:'fa-paragraph', divider:'fa-minus',
        hidden:'fa-eye-slash', rating:'fa-star',
    };
    return map[type] || 'fa-square';
}

// ─── Field selection / editor ─────────────────────────────────────────────────
function selectField(idx) {
    selectedFieldIdx = idx;
    renderCanvas();
    openFieldEditor(idx);
}

function openFieldEditor(idx) {
    var field = fields[idx];
    var editorPanel = document.getElementById('fieldEditorPanel');
    var editorEmpty = document.getElementById('fieldEditorEmpty');
    var content     = document.getElementById('fieldEditorContent');
    editorPanel.style.display = 'block';
    editorEmpty.style.display = 'none';

    var hasOptions   = ['select','radio','checkbox'].includes(field.type);
    var isLayoutType = ['heading','paragraph','divider'].includes(field.type);
    var hasRows      = field.type === 'textarea';

    var html = '';

    if (!isLayoutType) {
        html += editorRow('Label', '<input type="text" id="edLabel" value="' + escAttr(field.label || '') + '" style="' + inputStyle() + '">');
        html += editorRow('Field Name (key)', '<input type="text" id="edName" value="' + escAttr(field.name || '') + '" style="' + inputStyle() + '">');
    }
    if (['heading','paragraph'].includes(field.type)) {
        html += editorRow('Content', '<textarea id="edContent" rows="3" style="' + inputStyle() + 'resize:vertical;">' + escHtml(field.content || '') + '</textarea>');
    }
    if (!isLayoutType && field.type !== 'hidden') {
        html += editorRow('Placeholder', '<input type="text" id="edPlaceholder" value="' + escAttr(field.placeholder || '') + '" style="' + inputStyle() + '">');
        html += editorRow('Required',
            '<label style="display:flex;align-items:center;gap:8px;cursor:pointer;">' +
            '<input type="checkbox" id="edRequired" ' + (field.required ? 'checked' : '') + '> ' +
            '<span style="font-size:.85rem;">Mark as required</span></label>');
    }
    if (hasRows) {
        html += editorRow('Rows', '<input type="number" id="edRows" min="2" max="20" value="' + (field.rows || 4) + '" style="' + inputStyle() + '">');
    }
    if (field.type === 'hidden') {
        html += editorRow('Default Value', '<input type="text" id="edPlaceholder" value="' + escAttr(field.placeholder || '') + '" style="' + inputStyle() + '">');
    }
    if (hasOptions) {
        var opts = (field.options || []).join('\n');
        html += editorRow('Options <small style="font-weight:400;">(one per line)</small>',
            '<textarea id="edOptions" rows="5" style="' + inputStyle() + 'resize:vertical;">' + escHtml(opts) + '</textarea>');
    }
    if (field.type === 'rating') {
        html += editorRow('Max Stars', '<input type="number" id="edMax" min="3" max="10" value="' + (field.max || 5) + '" style="' + inputStyle() + '">');
    }
    if (field.type === 'number') {
        html += editorRow('Min', '<input type="number" id="edMin" value="' + (field.min !== undefined ? field.min : '') + '" style="' + inputStyle() + '">');
        html += editorRow('Max', '<input type="number" id="edMax" value="' + (field.max !== undefined ? field.max : '') + '" style="' + inputStyle() + '">');
    }
    if (field.type === 'file') {
        html += editorRow('Accept', '<input type="text" id="edAccept" value="' + escAttr(field.accept || '') + '" placeholder=".pdf,.jpg,.png" style="' + inputStyle() + '">');
    }
    if (['heading'].includes(field.type)) {
        html += editorRow('Level', '<select id="edLevel" style="' + inputStyle() + '">' +
            [1,2,3,4].map(function(n){return '<option value="'+n+'"'+(field.level==n?' selected':'')+'>H'+n+'</option>';}).join('') +
        '</select>');
    }

    content.innerHTML = html;
}

function editorRow(label, control) {
    return '<div><label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;">' + label + '</label>' + control + '</div>';
}
function inputStyle() {
    return 'width:100%;padding:7px 10px;background:var(--bg-primary);border:1px solid var(--border-color);border-radius:6px;color:var(--text-primary);font-size:.85rem;';
}

function saveFieldEdits() {
    if (selectedFieldIdx === null) return;
    var field = fields[selectedFieldIdx];
    var get = function(id) { var el = document.getElementById(id); return el ? el.value : undefined; };
    var getChk = function(id) { var el = document.getElementById(id); return el ? el.checked : false; };

    if (get('edLabel')       !== undefined) field.label       = get('edLabel');
    if (get('edName') !== undefined) {
        var rawName = get('edName').replace(/\s+/g,'_').toLowerCase().replace(/[^a-z0-9_]/g, '');
        field.name = rawName || field.name;
    }
    if (get('edPlaceholder') !== undefined) field.placeholder = get('edPlaceholder');
    if (get('edContent')     !== undefined) field.content     = get('edContent');
    if (get('edRows')        !== undefined) field.rows        = parseInt(get('edRows')) || 4;
    if (getChk('edRequired') !== undefined) field.required   = getChk('edRequired');
    if (get('edOptions')     !== undefined) field.options     = get('edOptions').split('\n').map(function(s){return s.trim();}).filter(Boolean);
    if (get('edMax')         !== undefined) field.max        = get('edMax') !== '' ? parseFloat(get('edMax')) : undefined;
    if (get('edMin')         !== undefined) field.min        = get('edMin') !== '' ? parseFloat(get('edMin')) : undefined;
    if (get('edAccept')      !== undefined) field.accept     = get('edAccept');
    if (get('edLevel')       !== undefined) field.level      = parseInt(get('edLevel')) || 2;

    fields[selectedFieldIdx] = field;
    renderCanvas();
    syncHidden();
}

function closeFieldEditor() {
    selectedFieldIdx = null;
    document.getElementById('fieldEditorPanel').style.display = 'none';
    document.getElementById('fieldEditorEmpty').style.display = 'block';
    renderCanvas();
}

// ─── Field mutations ──────────────────────────────────────────────────────────
function removeField(idx) {
    fields.splice(idx, 1);
    if (selectedFieldIdx === idx) closeFieldEditor();
    else if (selectedFieldIdx > idx) selectedFieldIdx--;
    renderCanvas();
    syncHidden();
}
function duplicateField(idx) {
    var copy = JSON.parse(JSON.stringify(fields[idx]));
    copy.id   = 'field_' + Date.now();
    copy.name = copy.name + '_copy';
    fields.splice(idx + 1, 0, copy);
    renderCanvas();
    syncHidden();
}
function moveField(idx, dir) {
    var target = idx + dir;
    if (target < 0 || target >= fields.length) return;
    var tmp = fields[idx];
    fields[idx] = fields[target];
    fields[target] = tmp;
    if (selectedFieldIdx === idx) selectedFieldIdx = target;
    else if (selectedFieldIdx === target) selectedFieldIdx = idx;
    renderCanvas();
    syncHidden();
}
function clearCanvas() {
    if (!confirm('Remove all fields?')) return;
    fields = [];
    closeFieldEditor();
    renderCanvas();
    syncHidden();
}

// ─── Sync hidden inputs ───────────────────────────────────────────────────────
function syncHidden() {
    document.getElementById('fieldsJsonInput').value = JSON.stringify(fields);
}

// Sync settings before submit
document.getElementById('builderForm').addEventListener('submit', function() {
    syncHidden();
    var settings = {
        success_message: document.getElementById('settingSuccessMessage').value,
        redirect_url:    document.getElementById('settingRedirectUrl').value,
        notify_email:    document.getElementById('settingNotifyEmail').value,
    };
    document.getElementById('settingsJsonInput').value = JSON.stringify(settings);
});

// ─── Helpers ──────────────────────────────────────────────────────────────────
function escHtml(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}
function escAttr(str) {
    var div = document.createElement('div');
    div.setAttribute('data-v', str);
    return div.getAttribute('data-v')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
</script>

<?php View::endSection(); ?>
