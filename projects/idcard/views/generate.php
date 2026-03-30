<?php
/**
 * @var array  $templates
 * @var string $selectedTpl
 * @var array  $tplConfig
 * @var array  $field_labels
 * @var array  $user
 */
$csrfToken = \Core\Security::generateCsrfToken();
?>

<style>
/* ── Generate page ── */
.gen-wrap { display:grid; grid-template-columns:1fr 440px; gap:20px; align-items:start; }
@media(max-width:960px){ .gen-wrap{ grid-template-columns:1fr; } }

/* Template picker */
.tpl-picker { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
.tpl-btn {
    padding:7px 14px; border-radius:20px; font-size:0.78rem; font-weight:600;
    border:1.5px solid transparent; cursor:pointer; transition:all 0.2s;
    background:var(--bg-secondary); color:var(--text-secondary);
}
.tpl-btn.active { color:#fff; border-color:transparent; }

/* Design style picker */
.style-picker { display:grid; grid-template-columns:repeat(5,1fr); gap:8px; }
@media(max-width:680px){ .style-picker{ grid-template-columns:repeat(3,1fr); } }
.style-card {
    border:2px solid var(--border-color); border-radius:10px; cursor:pointer;
    overflow:hidden; transition:all 0.2s; aspect-ratio:85.6/54; position:relative;
}
.style-card:hover { border-color:var(--indigo); transform:translateY(-2px); }
.style-card.active { border-color:var(--indigo); box-shadow:0 0 0 2px rgba(99,102,241,0.3); }
.style-label { font-size:0.62rem; font-weight:600; text-align:center; margin-top:5px; color:var(--text-secondary); }
.style-label.active { color:var(--indigo); }

/* Live preview */
.preview-area { position:sticky; top:1rem; }
.id-card-preview {
    width:100%; max-width:400px; margin:0 auto;
    border-radius:14px; overflow:hidden;
    box-shadow:0 20px 60px rgba(0,0,0,0.4);
    font-family:'Poppins',sans-serif;
    transition:all 0.3s ease;
    aspect-ratio:85.6/54;
    position:relative;
}

/* AI panel */
.spinner { display:inline-block; width:14px; height:14px; border:2px solid rgba(99,102,241,0.3); border-top-color:var(--indigo); border-radius:50%; animation:spin 0.7s linear infinite; }
@keyframes spin { to{ transform:rotate(360deg); } }
.ai-suggestion { font-size:0.78rem; color:var(--text-secondary); line-height:1.6; padding:6px 10px; background:var(--bg-secondary); border-radius:8px; margin-bottom:6px; }
.ai-suggestion strong { color:var(--text-primary); }
</style>

<a href="/projects/idcard" class="back-link"><i class="fas fa-arrow-left"></i> Dashboard</a>
<h2 class="section-title"><i class="fas fa-id-card" style="color:var(--indigo);"></i> Generate ID Card</h2>

<!-- Template Picker -->
<div class="card" style="margin-bottom:16px;padding:16px;">
    <p style="font-size:0.8rem;color:var(--text-secondary);margin-bottom:10px;font-weight:600;">SELECT TEMPLATE</p>
    <div class="tpl-picker">
        <?php foreach ($templates as $key => $tpl): ?>
        <button type="button" class="tpl-btn <?= $key === $selectedTpl ? 'active' : '' ?>"
                data-key="<?= htmlspecialchars($key) ?>"
                style="<?= $key === $selectedTpl ? "background:{$tpl['color']}" : '' ?>"
                onclick="selectTemplate('<?= htmlspecialchars($key) ?>')">
            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= htmlspecialchars($tpl['color']) ?>;margin-right:4px;"></span>
            <?= htmlspecialchars($tpl['name']) ?>
        </button>
        <?php endforeach; ?>
    </div>
</div>

<div class="gen-wrap">
    <!-- LEFT: Form -->
    <div>
        <form id="cardForm" method="POST" action="/projects/idcard/generate" enctype="multipart/form-data">
            <input type="hidden" name="_token"       value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="template_key" id="template_key" value="<?= htmlspecialchars($selectedTpl) ?>">
            <input type="hidden" name="design_style" id="design_style" value="classic">

            <!-- Dynamic fields -->
            <div class="card" style="margin-bottom:16px;">
                <h3 style="font-size:0.9rem;font-weight:600;margin-bottom:14px;color:var(--indigo);display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-user"></i> Card Information
                </h3>
                <div id="dynamicFields">
                    <?php foreach ($tplConfig['fields'] as $field): ?>
                        <?php if ($field === 'photo'): continue; endif; ?>
                        <div class="form-group" style="margin-bottom:12px;">
                            <label class="form-label" style="font-size:0.78rem;" for="field_<?= $field ?>">
                                <?= htmlspecialchars($field_labels[$field] ?? ucfirst(str_replace('_',' ',$field))) ?>
                            </label>
                            <input type="text" id="field_<?= $field ?>" name="<?= htmlspecialchars($field) ?>"
                                   class="form-input" style="padding:8px 12px;font-size:0.85rem;"
                                   placeholder="Enter <?= strtolower(htmlspecialchars($field_labels[$field] ?? $field)) ?>"
                                   oninput="updatePreview()">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Photo & Logo -->
            <div class="card" style="margin-bottom:16px;">
                <h3 style="font-size:0.9rem;font-weight:600;margin-bottom:14px;color:var(--indigo);display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-camera"></i> Photo &amp; Logo
                </h3>
                <div class="grid grid-2" style="gap:12px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-size:0.78rem;">Profile Photo</label>
                        <input type="file" name="photo" id="photoInput" class="form-input" accept="image/*"
                               style="padding:6px;font-size:0.8rem;" onchange="previewPhoto(this)">
                        <p style="font-size:0.68rem;color:var(--text-secondary);margin-top:4px;">JPG/PNG, max 5 MB</p>
                    </div>
                    <div class="form-group" style="margin-bottom:0;" id="logoWrap">
                        <label class="form-label" style="font-size:0.78rem;">Organisation Logo</label>
                        <input type="file" name="logo" id="logoInput" class="form-input" accept="image/*"
                               style="padding:6px;font-size:0.8rem;">
                        <p style="font-size:0.68rem;color:var(--text-secondary);margin-top:4px;">PNG recommended</p>
                    </div>
                </div>
            </div>

            <!-- Design Style Picker -->
            <div class="card" style="margin-bottom:16px;">
                <h3 style="font-size:0.9rem;font-weight:600;margin-bottom:6px;color:var(--indigo);display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-layer-group"></i> Design Style
                </h3>
                <p style="font-size:0.72rem;color:var(--text-secondary);margin-bottom:12px;">Choose a layout and background graphic</p>
                <div class="style-picker" id="stylePicker"></div>
            </div>

            <!-- Colours & Font -->
            <div class="card" style="margin-bottom:16px;">
                <h3 style="font-size:0.9rem;font-weight:600;margin-bottom:14px;color:var(--indigo);display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-paint-brush"></i> Colours &amp; Font
                </h3>
                <div class="grid grid-2" style="gap:12px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-size:0.78rem;">Primary Colour</label>
                        <input type="color" name="primary_color" id="primaryColor"
                               class="form-input" style="height:38px;padding:2px 6px;cursor:pointer;"
                               value="<?= htmlspecialchars($tplConfig['color']) ?>" oninput="updatePreview()">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-size:0.78rem;">Accent Colour</label>
                        <input type="color" name="accent_color" id="accentColor"
                               class="form-input" style="height:38px;padding:2px 6px;cursor:pointer;"
                               value="<?= htmlspecialchars($tplConfig['accent']) ?>" oninput="updatePreview()">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-size:0.78rem;">Background Colour</label>
                        <input type="color" name="bg_color" id="bgColor"
                               class="form-input" style="height:38px;padding:2px 6px;cursor:pointer;"
                               value="<?= htmlspecialchars($tplConfig['bg']) ?>" oninput="updatePreview()">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-size:0.78rem;">Text Colour</label>
                        <input type="color" name="text_color" id="textColor"
                               class="form-input" style="height:38px;padding:2px 6px;cursor:pointer;"
                               value="<?= htmlspecialchars($tplConfig['text']) ?>" oninput="updatePreview()">
                    </div>
                </div>
                <div class="form-group" style="margin-top:12px;margin-bottom:0;">
                    <label class="form-label" style="font-size:0.78rem;">Font Family</label>
                    <select name="font_family" id="fontFamily" class="form-input" style="padding:8px 12px;font-size:0.85rem;" onchange="updatePreview()">
                        <?php foreach(['Poppins','Inter','Roboto','Lato','Open Sans','Montserrat','Raleway'] as $f): ?>
                        <option value="<?= $f ?>"><?= $f ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:flex;gap:16px;margin-top:12px;flex-wrap:wrap;">
                    <label style="display:flex;align-items:center;gap:6px;font-size:0.8rem;cursor:pointer;">
                        <input type="checkbox" name="show_qr" id="showQr" onchange="updatePreview()"> Show QR Code
                    </label>
                </div>
            </div>

            <!-- AI Assistant -->
            <div class="card" style="margin-bottom:16px;background:linear-gradient(135deg,rgba(99,102,241,0.06),rgba(0,240,255,0.03));border:1px solid rgba(99,102,241,0.2);">
                <h3 style="font-size:0.9rem;font-weight:600;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-robot" style="color:var(--indigo);"></i> AI Design Assistant
                    <span style="background:linear-gradient(135deg,#6366f1,#00f0ff);color:white;font-size:0.6rem;padding:2px 8px;border-radius:10px;">AI</span>
                </h3>
                <div class="form-group" style="margin-bottom:10px;">
                    <label class="form-label" style="font-size:0.78rem;">Describe your needs (optional)</label>
                    <input type="text" name="ai_prompt" id="aiPrompt" class="form-input"
                           style="padding:8px 12px;font-size:0.85rem;"
                           placeholder="e.g. modern tech company, minimalist, blue theme...">
                </div>
                <button type="button" class="btn btn-secondary" style="width:100%;justify-content:center;" onclick="getAISuggestions()">
                    <i class="fas fa-magic"></i> Get AI Suggestions
                </button>
                <div id="aiOutput" style="margin-top:12px;display:none;"></div>
            </div>

            <!-- Submit -->
            <div class="form-actions">
                <button type="submit" id="generateBtn" class="btn btn-primary" style="flex:1;justify-content:center;padding:14px;">
                    <i class="fas fa-id-card"></i> Generate ID Card
                </button>
                <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
        </form>
    </div>

    <!-- RIGHT: Live Preview -->
    <div class="preview-area">
        <div class="card" style="padding:16px;">
            <h3 style="font-size:0.85rem;font-weight:600;margin-bottom:14px;color:var(--text-secondary);display:flex;align-items:center;gap:6px;">
                <i class="fas fa-eye"></i> Live Preview &nbsp;
                <span id="previewTplName" style="color:var(--indigo);font-weight:700;"><?= htmlspecialchars($tplConfig['name']) ?></span>
                <span id="previewStyleName" style="color:var(--text-secondary);font-size:0.72rem;font-weight:400;margin-left:4px;"></span>
            </h3>
            <div id="cardPreview" class="id-card-preview" style="background:<?= htmlspecialchars($tplConfig['bg']) ?>;"></div>
            <p style="text-align:center;font-size:0.72rem;color:var(--text-secondary);margin-top:10px;">
                <i class="fas fa-info-circle"></i> Preview is approximate &mdash; final card will be pixel-perfect
            </p>
        </div>

        <div class="card" style="margin-top:12px;padding:14px;">
            <h4 style="font-size:0.82rem;font-weight:600;margin-bottom:8px;color:var(--text-secondary);">TEMPLATE FIELDS</h4>
            <div id="tplFieldsList" style="display:flex;flex-wrap:wrap;gap:5px;">
                <?php foreach ($tplConfig['fields'] as $field): ?>
                <span style="padding:3px 9px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:12px;font-size:0.7rem;color:var(--text-secondary);">
                    <?= htmlspecialchars($field_labels[$field] ?? $field) ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
// =============================================================================
//  Data from PHP
// =============================================================================
const TEMPLATES    = <?= json_encode($templates) ?>;
const FIELD_LABELS = <?= json_encode($field_labels) ?>;
const CSRF_TOKEN   = '<?= htmlspecialchars($csrfToken) ?>';

let currentTpl   = '<?= htmlspecialchars($selectedTpl) ?>';
let currentStyle = 'classic';
let photoDataUrl = null;

// =============================================================================
//  Design style definitions
// =============================================================================
const DESIGN_STYLES = [
    { key:'classic',     label:'Classic',      desc:'Top stripe bar, photo left, details right' },
    { key:'sidebar',     label:'Sidebar',      desc:'Coloured left column, photo inside sidebar' },
    { key:'wave',        label:'Wave Gradient', desc:'Full gradient with SVG wave decoration' },
    { key:'bold_header', label:'Bold Header',  desc:'Large header block with geometric pattern' },
    { key:'diagonal',    label:'Diagonal',     desc:'SVG diagonal split background graphic' }
];

// =============================================================================
//  Style picker thumbnails (inline SVG, no external images)
// =============================================================================
function buildStyleThumbnail(key, pri, acc, bg, txt) {
    const W = 85.6, H = 54;
    switch(key) {
        case 'classic':
            return '<svg viewBox="0 0 ' + W + ' ' + H + '" xmlns="http://www.w3.org/2000/svg">' +
                '<rect width="' + W + '" height="' + H + '" fill="' + bg + '"/>' +
                '<rect y="0" width="' + W + '" height="7" fill="' + pri + '"/>' +
                '<rect y="' + (H-8) + '" width="' + W + '" height="8" fill="' + pri + '" opacity="0.22"/>' +
                '<circle cx="22" cy="' + (H/2+2) + '" r="10" fill="' + pri + '" opacity="0.15"/>' +
                '<circle cx="22" cy="' + (H/2+2) + '" r="10" fill="none" stroke="' + acc + '" stroke-width="1.5"/>' +
                '<rect x="36" y="18" width="32" height="3.5" rx="1.5" fill="' + txt + '" opacity="0.8"/>' +
                '<rect x="36" y="25" width="22" height="2.5" rx="1" fill="' + acc + '" opacity="0.7"/>' +
                '<rect x="36" y="32" width="28" height="2" rx="1" fill="' + txt + '" opacity="0.3"/>' +
                '<rect x="36" y="37" width="20" height="2" rx="1" fill="' + txt + '" opacity="0.3"/>' +
                '</svg>';
        case 'sidebar':
            return '<svg viewBox="0 0 ' + W + ' ' + H + '" xmlns="http://www.w3.org/2000/svg">' +
                '<rect width="' + W + '" height="' + H + '" fill="' + bg + '"/>' +
                '<rect width="24" height="' + H + '" fill="' + pri + '"/>' +
                '<circle cx="12" cy="19" r="8" fill="rgba(255,255,255,0.25)" stroke="rgba(255,255,255,0.5)" stroke-width="1"/>' +
                '<rect x="5" y="34" width="14" height="1.5" rx="0.75" fill="rgba(255,255,255,0.4)"/>' +
                '<rect x="5" y="39" width="10" height="1.5" rx="0.75" fill="rgba(255,255,255,0.3)"/>' +
                '<rect x="30" y="14" width="40" height="4" rx="1.5" fill="' + txt + '" opacity="0.8"/>' +
                '<rect x="30" y="22" width="26" height="2.8" rx="1" fill="' + acc + '" opacity="0.7"/>' +
                '<rect x="30" y="30" width="36" height="2" rx="1" fill="' + txt + '" opacity="0.3"/>' +
                '<rect x="30" y="36" width="28" height="2" rx="1" fill="' + txt + '" opacity="0.3"/>' +
                '<rect x="30" y="42" width="18" height="2" rx="1" fill="' + txt + '" opacity="0.3"/>' +
                '</svg>';
        case 'wave':
            return '<svg viewBox="0 0 ' + W + ' ' + H + '" xmlns="http://www.w3.org/2000/svg">' +
                '<defs><linearGradient id="wg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="' + pri + '"/><stop offset="100%" stop-color="' + acc + '"/></linearGradient></defs>' +
                '<rect width="' + W + '" height="' + H + '" fill="url(#wg)"/>' +
                '<path d="M0,38 Q21,28 43,36 Q64,44 ' + W + ',34 L' + W + ',' + H + ' L0,' + H + ' Z" fill="rgba(255,255,255,0.12)"/>' +
                '<path d="M0,44 Q26,36 51,42 Q70,48 ' + W + ',40 L' + W + ',' + H + ' L0,' + H + ' Z" fill="rgba(255,255,255,0.07)"/>' +
                '<circle cx="20" cy="25" r="9" fill="rgba(255,255,255,0.2)" stroke="rgba(255,255,255,0.5)" stroke-width="1"/>' +
                '<rect x="34" y="14" width="36" height="3.5" rx="1.5" fill="rgba(255,255,255,0.9)"/>' +
                '<rect x="34" y="21" width="24" height="2.5" rx="1" fill="rgba(255,255,255,0.7)"/>' +
                '<rect x="34" y="28" width="32" height="2" rx="1" fill="rgba(255,255,255,0.4)"/>' +
                '<rect x="34" y="34" width="22" height="2" rx="1" fill="rgba(255,255,255,0.4)"/>' +
                '</svg>';
        case 'bold_header':
            return '<svg viewBox="0 0 ' + W + ' ' + H + '" xmlns="http://www.w3.org/2000/svg">' +
                '<rect width="' + W + '" height="' + H + '" fill="' + bg + '"/>' +
                '<rect width="' + W + '" height="22" fill="' + pri + '"/>' +
                '<circle cx="' + (W-8) + '" cy="8" r="14" fill="rgba(255,255,255,0.07)"/>' +
                '<circle cx="' + (W-22) + '" cy="2" r="10" fill="rgba(255,255,255,0.06)"/>' +
                '<circle cx="' + (W-4) + '" cy="20" r="8" fill="rgba(255,255,255,0.05)"/>' +
                '<rect x="28" y="7" width="38" height="4" rx="1.5" fill="rgba(255,255,255,0.9)"/>' +
                '<rect x="28" y="14" width="26" height="2.8" rx="1" fill="rgba(255,255,255,0.65)"/>' +
                '<circle cx="14" cy="22" r="10" fill="' + bg + '" stroke="' + acc + '" stroke-width="1.5"/>' +
                '<circle cx="14" cy="22" r="7" fill="' + pri + '" opacity="0.15"/>' +
                '<rect x="28" y="26" width="44" height="2.5" rx="1" fill="' + txt + '" opacity="0.7"/>' +
                '<rect x="28" y="32" width="36" height="2" rx="1" fill="' + txt + '" opacity="0.3"/>' +
                '<rect x="28" y="38" width="28" height="2" rx="1" fill="' + txt + '" opacity="0.3"/>' +
                '<rect x="28" y="44" width="20" height="2" rx="1" fill="' + txt + '" opacity="0.3"/>' +
                '</svg>';
        case 'diagonal':
            return '<svg viewBox="0 0 ' + W + ' ' + H + '" xmlns="http://www.w3.org/2000/svg">' +
                '<rect width="' + W + '" height="' + H + '" fill="' + bg + '"/>' +
                '<polygon points="0,0 50,0 33,' + H + ' 0,' + H + '" fill="' + pri + '"/>' +
                '<circle cx="10" cy="12" r="6" fill="rgba(255,255,255,0.1)"/>' +
                '<circle cx="26" cy="8" r="9" fill="rgba(255,255,255,0.07)"/>' +
                '<circle cx="14" cy="42" r="8" fill="rgba(255,255,255,0.07)"/>' +
                '<circle cx="18" cy="27" r="11" fill="rgba(255,255,255,0.18)" stroke="rgba(255,255,255,0.45)" stroke-width="1.2"/>' +
                '<rect x="57" y="15" width="24" height="3.5" rx="1.5" fill="' + txt + '" opacity="0.8"/>' +
                '<rect x="57" y="22" width="17" height="2.5" rx="1" fill="' + acc + '" opacity="0.7"/>' +
                '<rect x="57" y="29" width="22" height="2" rx="1" fill="' + txt + '" opacity="0.3"/>' +
                '<rect x="57" y="35" width="16" height="2" rx="1" fill="' + txt + '" opacity="0.3"/>' +
                '<rect x="57" y="41" width="12" height="2" rx="1" fill="' + txt + '" opacity="0.3"/>' +
                '</svg>';
        default:
            return '<svg viewBox="0 0 ' + W + ' ' + H + '"><rect width="' + W + '" height="' + H + '" fill="' + pri + '"/></svg>';
    }
}

function buildStylePicker() {
    const pri = document.getElementById('primaryColor').value || '#1e40af';
    const acc = document.getElementById('accentColor').value  || '#3b82f6';
    const bg  = document.getElementById('bgColor').value      || '#ffffff';
    const txt = document.getElementById('textColor').value    || '#1e293b';

    const picker = document.getElementById('stylePicker');
    picker.innerHTML = DESIGN_STYLES.map(function(s) {
        const thumb    = buildStyleThumbnail(s.key, pri, acc, bg, txt);
        const isActive = s.key === currentStyle;
        return '<div>' +
            '<div class="style-card' + (isActive ? ' active' : '') + '" id="styleCard_' + s.key + '" ' +
            'title="' + s.desc + '" onclick="selectStyle(\'' + s.key + '\')">' +
            thumb + '</div>' +
            '<div class="style-label' + (isActive ? ' active' : '') + '" id="styleLabel_' + s.key + '">' + s.label + '</div>' +
            '</div>';
    }).join('');
}

function updateStyleThumbnails() {
    const pri = document.getElementById('primaryColor').value || '#1e40af';
    const acc = document.getElementById('accentColor').value  || '#3b82f6';
    const bg  = document.getElementById('bgColor').value      || '#ffffff';
    const txt = document.getElementById('textColor').value    || '#1e293b';
    DESIGN_STYLES.forEach(function(s) {
        var card = document.getElementById('styleCard_' + s.key);
        if (card) card.innerHTML = buildStyleThumbnail(s.key, pri, acc, bg, txt);
    });
}

// =============================================================================
//  Template selector
// =============================================================================
function selectTemplate(key) {
    if (!TEMPLATES[key]) return;
    currentTpl = key;
    const tpl  = TEMPLATES[key];
    document.getElementById('template_key').value = key;

    document.querySelectorAll('.tpl-btn').forEach(function(btn) {
        const isActive = btn.dataset.key === key;
        btn.classList.toggle('active', isActive);
        btn.style.background = isActive ? tpl.color : '';
        btn.style.color      = isActive ? '#fff' : '';
    });

    const container = document.getElementById('dynamicFields');
    container.innerHTML = '';
    (tpl.fields || []).forEach(function(field) {
        if (field === 'photo') return;
        const label = FIELD_LABELS[field] || field.replace(/_/g, ' ');
        container.innerHTML +=
            '<div class="form-group" style="margin-bottom:12px;">' +
            '<label class="form-label" style="font-size:0.78rem;" for="field_' + field + '">' + label + '</label>' +
            '<input type="text" id="field_' + field + '" name="' + field + '" class="form-input" ' +
            'style="padding:8px 12px;font-size:0.85rem;" placeholder="Enter ' + label.toLowerCase() + '" oninput="updatePreview()">' +
            '</div>';
    });

    document.getElementById('primaryColor').value = tpl.color;
    document.getElementById('accentColor').value  = tpl.accent;
    document.getElementById('bgColor').value      = tpl.bg;
    document.getElementById('textColor').value    = tpl.text;
    document.getElementById('logoWrap').style.display = tpl.logo ? '' : 'none';
    document.getElementById('previewTplName').textContent = tpl.name;

    const fieldsList = document.getElementById('tplFieldsList');
    fieldsList.innerHTML = (tpl.fields || []).map(function(f) {
        const lbl = FIELD_LABELS[f] || f;
        return '<span style="padding:3px 9px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:12px;font-size:0.7rem;color:var(--text-secondary);">' + lbl + '</span>';
    }).join('');

    buildStylePicker();
    updatePreview();
}

// =============================================================================
//  Design style selector
// =============================================================================
function selectStyle(key) {
    currentStyle = key;
    document.getElementById('design_style').value = key;

    DESIGN_STYLES.forEach(function(s) {
        var card  = document.getElementById('styleCard_' + s.key);
        var label = document.getElementById('styleLabel_' + s.key);
        var isActive = s.key === key;
        if (card)  { card.classList.toggle('active', isActive); }
        if (label) {
            label.classList.toggle('active', isActive);
        }
    });

    var styleDef = DESIGN_STYLES.find(function(s){ return s.key === key; });
    document.getElementById('previewStyleName').textContent = styleDef ? ('· ' + styleDef.label) : '';
    updatePreview();
}

// =============================================================================
//  Card data helpers
// =============================================================================
function getCardValues() {
    const tpl  = TEMPLATES[currentTpl] || {};
    const pri  = document.getElementById('primaryColor').value || tpl.color  || '#1e40af';
    const acc  = document.getElementById('accentColor').value  || tpl.accent || '#3b82f6';
    const bg   = document.getElementById('bgColor').value      || tpl.bg     || '#ffffff';
    const txt  = document.getElementById('textColor').value    || tpl.text   || '#1e293b';
    const font = document.getElementById('fontFamily').value   || 'Poppins';

    const roleKeys = ['designation','title','course','event_name'];
    const nameEl   = document.getElementById('field_name');
    const nameVal  = (nameEl && nameEl.value) ? nameEl.value : 'Full Name';
    var roleVal = '';
    for (var i = 0; i < roleKeys.length; i++) {
        var el = document.getElementById('field_' + roleKeys[i]);
        if (el && el.value) { roleVal = el.value; break; }
    }
    roleVal = roleVal || 'Designation / Role';

    const icons = {department:'building', employee_id:'hashtag', roll_number:'hashtag',
                   phone:'phone', email:'envelope', blood_group:'tint', badge_id:'hashtag',
                   host_name:'user', purpose:'clipboard', visit_date:'calendar',
                   license_no:'certificate', organization:'building', id_number:'hashtag', year:'graduation-cap'};

    const fieldKeys  = (tpl.fields || []).filter(function(f){ return f !== 'photo' && f !== 'name' && !roleKeys.includes(f); });
    const fieldItems = fieldKeys.slice(0, 3).map(function(f) {
        var el  = document.getElementById('field_' + f);
        var val = el ? (el.value || (FIELD_LABELS[f] || f)) : (FIELD_LABELS[f] || f);
        return { key:f, val:val, icon: icons[f] || 'info-circle' };
    });

    const photoHTML = photoDataUrl
        ? '<img src="' + photoDataUrl + '" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">'
        : '<i class="fas fa-user" style="font-size:1.5rem;opacity:0.7;"></i>';

    return { pri:pri, acc:acc, bg:bg, txt:txt, font:font, nameVal:nameVal, roleVal:roleVal, fieldItems:fieldItems, photoHTML:photoHTML };
}

function fieldListHTML(items, color) {
    return items.map(function(f) {
        return '<div style="display:flex;align-items:center;gap:4%;font-size:clamp(0.44rem,1.05vw,0.64rem);opacity:0.85;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:' + color + ';">' +
               '<i class="fas fa-' + f.icon + '" style="font-size:0.5rem;opacity:0.65;flex-shrink:0;"></i>' +
               '<span>' + f.val + '</span>' +
               '</div>';
    }).join('');
}

// =============================================================================
//  Card renderers — one per design style
// =============================================================================

// Style 1: Classic
function renderClassic(v) {
    return '<div style="width:100%;height:100%;background:' + v.bg + ';font-family:\'' + v.font + '\',sans-serif;position:relative;overflow:hidden;">' +
        '<div style="position:absolute;top:0;left:0;right:0;height:8%;background:' + v.pri + ';"></div>' +
        '<div style="position:absolute;bottom:0;left:0;right:0;height:14%;background:' + v.pri + ';opacity:0.2;"></div>' +
        '<div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;align-items:center;gap:5%;padding:12% 5% 5%;color:' + v.txt + ';">' +
            '<div style="flex-shrink:0;width:20%;aspect-ratio:1;border-radius:50%;border:2.5px solid ' + v.acc + ';background:' + v.pri + '22;overflow:hidden;display:flex;align-items:center;justify-content:center;">' +
                v.photoHTML +
            '</div>' +
            '<div style="flex:1;min-width:0;">' +
                '<div style="font-size:clamp(0.7rem,1.8vw,1rem);font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + v.nameVal + '</div>' +
                '<div style="font-size:clamp(0.5rem,1.2vw,0.72rem);color:' + v.acc + ';margin-top:2%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + v.roleVal + '</div>' +
                '<div style="margin-top:4%;display:flex;flex-direction:column;gap:2%;">' + fieldListHTML(v.fieldItems, v.txt) + '</div>' +
            '</div>' +
        '</div>' +
        '</div>';
}

// Style 2: Sidebar
function renderSidebar(v) {
    return '<div style="width:100%;height:100%;display:flex;font-family:\'' + v.font + '\',sans-serif;overflow:hidden;position:relative;">' +
        '<div style="width:28%;background:' + v.pri + ';display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6%;padding:5% 0;flex-shrink:0;position:relative;">' +
            '<div style="position:absolute;top:-15%;left:-20%;width:100%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.06);"></div>' +
            '<div style="position:absolute;bottom:-20%;right:-30%;width:80%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.04);"></div>' +
            '<div style="width:54%;aspect-ratio:1;border-radius:50%;border:2px solid rgba(255,255,255,0.5);background:rgba(255,255,255,0.15);overflow:hidden;display:flex;align-items:center;justify-content:center;position:relative;z-index:1;">' +
                v.photoHTML +
            '</div>' +
            '<div style="writing-mode:vertical-rl;transform:rotate(180deg);font-size:clamp(0.38rem,0.85vw,0.55rem);color:rgba(255,255,255,0.45);letter-spacing:0.08em;text-transform:uppercase;position:relative;z-index:1;">ID Card</div>' +
        '</div>' +
        '<div style="flex:1;background:' + v.bg + ';padding:5% 6%;display:flex;flex-direction:column;justify-content:center;color:' + v.txt + ';min-width:0;">' +
            '<div style="width:28%;height:2px;background:' + v.acc + ';border-radius:2px;margin-bottom:6%;"></div>' +
            '<div style="font-size:clamp(0.7rem,1.7vw,0.97rem);font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + v.nameVal + '</div>' +
            '<div style="font-size:clamp(0.48rem,1.1vw,0.7rem);color:' + v.acc + ';margin-top:2%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + v.roleVal + '</div>' +
            '<div style="margin-top:4%;display:flex;flex-direction:column;gap:3%;">' + fieldListHTML(v.fieldItems, v.txt) + '</div>' +
        '</div>' +
        '</div>';
}

// Style 3: Wave Gradient
function renderWave(v) {
    return '<div style="width:100%;height:100%;background:linear-gradient(135deg,' + v.pri + ' 0%,' + v.acc + ' 100%);font-family:\'' + v.font + '\',sans-serif;position:relative;overflow:hidden;">' +
        '<svg style="position:absolute;bottom:0;left:0;width:100%;height:45%;" viewBox="0 0 400 100" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">' +
            '<path d="M0,60 Q100,20 200,55 Q300,90 400,40 L400,100 L0,100 Z" fill="rgba(255,255,255,0.12)"/>' +
            '<path d="M0,78 Q130,50 260,72 Q340,88 400,62 L400,100 L0,100 Z" fill="rgba(255,255,255,0.07)"/>' +
        '</svg>' +
        '<div style="position:absolute;top:-8%;right:-8%;width:34%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.08);"></div>' +
        '<div style="position:absolute;top:5%;right:8%;width:14%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.06);"></div>' +
        '<div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;align-items:center;gap:5%;padding:5% 6%;color:rgba(255,255,255,0.95);">' +
            '<div style="flex-shrink:0;width:22%;aspect-ratio:1;border-radius:50%;border:2.5px solid rgba(255,255,255,0.6);background:rgba(255,255,255,0.2);overflow:hidden;display:flex;align-items:center;justify-content:center;">' +
                v.photoHTML +
            '</div>' +
            '<div style="flex:1;min-width:0;">' +
                '<div style="font-size:clamp(0.7rem,1.8vw,1rem);font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + v.nameVal + '</div>' +
                '<div style="font-size:clamp(0.5rem,1.2vw,0.72rem);opacity:0.82;margin-top:2%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + v.roleVal + '</div>' +
                '<div style="margin-top:5%;display:flex;flex-direction:column;gap:3%;">' + fieldListHTML(v.fieldItems, 'rgba(255,255,255,0.92)') + '</div>' +
            '</div>' +
        '</div>' +
        '<div style="position:absolute;bottom:3%;right:4%;font-size:clamp(0.38rem,0.75vw,0.52rem);font-family:monospace;opacity:0.45;color:white;">CardX</div>' +
        '</div>';
}

// Style 4: Bold Header
function renderBoldHeader(v) {
    return '<div style="width:100%;height:100%;background:' + v.bg + ';font-family:\'' + v.font + '\',sans-serif;position:relative;overflow:hidden;color:' + v.txt + ';">' +
        '<div style="position:absolute;top:0;left:0;right:0;height:40%;background:' + v.pri + ';overflow:hidden;">' +
            '<svg style="position:absolute;right:0;top:0;height:130%;opacity:0.14;" viewBox="0 0 120 80" xmlns="http://www.w3.org/2000/svg">' +
                '<circle cx="100" cy="10" r="30" fill="white"/>' +
                '<circle cx="85"  cy="45" r="22" fill="white"/>' +
                '<circle cx="110" cy="62" r="18" fill="white"/>' +
                '<polygon points="60,0 90,0 75,20" fill="white" opacity="0.5"/>' +
            '</svg>' +
            '<div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;flex-direction:column;justify-content:center;padding:0 5% 0 35%;">' +
                '<div style="font-size:clamp(0.64rem,1.6vw,0.93rem);font-weight:700;color:white;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + v.nameVal + '</div>' +
                '<div style="font-size:clamp(0.46rem,1.1vw,0.68rem);color:rgba(255,255,255,0.75);margin-top:4%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + v.roleVal + '</div>' +
            '</div>' +
        '</div>' +
        '<div style="position:absolute;top:22%;left:4%;width:22%;aspect-ratio:1;border-radius:50%;border:3px solid ' + v.bg + ';background:' + v.acc + '22;overflow:hidden;display:flex;align-items:center;justify-content:center;z-index:2;box-shadow:0 2px 10px rgba(0,0,0,0.18);">' +
            v.photoHTML +
        '</div>' +
        '<div style="position:absolute;top:44%;left:0;right:0;bottom:0;padding:2% 5% 4% 5%;display:flex;flex-direction:column;gap:3%;">' +
            '<div style="width:22%;height:2px;background:' + v.acc + ';border-radius:2px;"></div>' +
            '<div style="display:flex;flex-direction:column;gap:3%;margin-top:1%;">' + fieldListHTML(v.fieldItems, v.txt) + '</div>' +
        '</div>' +
        '<div style="position:absolute;bottom:3%;right:4%;font-size:clamp(0.36rem,0.7vw,0.5rem);font-family:monospace;opacity:0.35;color:' + v.txt + ';">CardX</div>' +
        '</div>';
}

// Style 5: Diagonal Split
function renderDiagonal(v) {
    return '<div style="width:100%;height:100%;background:' + v.bg + ';font-family:\'' + v.font + '\',sans-serif;position:relative;overflow:hidden;">' +
        '<svg style="position:absolute;top:0;left:0;width:100%;height:100%;" viewBox="0 0 256 162" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">' +
            '<polygon points="0,0 148,0 100,162 0,162" fill="' + v.pri + '"/>' +
            '<circle cx="22"  cy="28"  r="18" fill="rgba(255,255,255,0.07)"/>' +
            '<circle cx="70"  cy="15"  r="24" fill="rgba(255,255,255,0.05)"/>' +
            '<circle cx="18"  cy="130" r="22" fill="rgba(255,255,255,0.06)"/>' +
            '<circle cx="90"  cy="100" r="14" fill="rgba(255,255,255,0.04)"/>' +
            '<line x1="0" y1="55"  x2="122" y2="55"  stroke="rgba(255,255,255,0.05)" stroke-width="1"/>' +
            '<line x1="0" y1="105" x2="108" y2="105" stroke="rgba(255,255,255,0.05)" stroke-width="1"/>' +
        '</svg>' +
        '<div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;align-items:center;">' +
            '<div style="width:44%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:5%;padding:0 2%;">' +
                '<div style="width:38%;aspect-ratio:1;border-radius:50%;border:2.5px solid rgba(255,255,255,0.5);background:rgba(255,255,255,0.18);overflow:hidden;display:flex;align-items:center;justify-content:center;">' +
                    v.photoHTML +
                '</div>' +
                '<div style="font-size:clamp(0.36rem,0.85vw,0.52rem);color:rgba(255,255,255,0.55);text-align:center;font-family:monospace;text-transform:uppercase;letter-spacing:0.05em;">ID Card</div>' +
            '</div>' +
            '<div style="flex:1;padding:4% 4% 4% 1%;color:' + v.txt + ';min-width:0;">' +
                '<div style="font-size:clamp(0.64rem,1.6vw,0.9rem);font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + v.nameVal + '</div>' +
                '<div style="font-size:clamp(0.47rem,1.1vw,0.67rem);color:' + v.acc + ';margin-top:3%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + v.roleVal + '</div>' +
                '<div style="width:55%;height:1.5px;background:' + v.acc + ';border-radius:2px;margin-top:4%;margin-bottom:4%;opacity:0.5;"></div>' +
                '<div style="display:flex;flex-direction:column;gap:3%;">' + fieldListHTML(v.fieldItems, v.txt) + '</div>' +
            '</div>' +
        '</div>' +
        '</div>';
}

// =============================================================================
//  Main preview updater
// =============================================================================
function updatePreview() {
    const v       = getCardValues();
    updateStyleThumbnails();

    const preview = document.getElementById('cardPreview');
    preview.style.fontFamily = "'" + v.font + "',sans-serif";

    var html = '';
    switch(currentStyle) {
        case 'sidebar':     html = renderSidebar(v);     break;
        case 'wave':        html = renderWave(v);        break;
        case 'bold_header': html = renderBoldHeader(v);  break;
        case 'diagonal':    html = renderDiagonal(v);    break;
        default:            html = renderClassic(v);     break;
    }
    preview.innerHTML = html;
}

// =============================================================================
//  Photo preview
// =============================================================================
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) { photoDataUrl = e.target.result; updatePreview(); };
        reader.readAsDataURL(input.files[0]);
    }
}

// =============================================================================
//  AI Suggestions (AJAX)
// =============================================================================
function getAISuggestions() {
    const prompt = document.getElementById('aiPrompt').value.trim();
    const out    = document.getElementById('aiOutput');
    out.style.display = 'block';
    out.innerHTML = '<div style="text-align:center;padding:12px;"><div class="spinner"></div><p style="font-size:0.75rem;margin-top:6px;color:var(--text-secondary);">Generating suggestions...</p></div>';

    const cardData = {};
    const tpl = TEMPLATES[currentTpl] || {};
    (tpl.fields || []).forEach(function(f) {
        const el = document.getElementById('field_' + f);
        if (el) cardData[f] = el.value;
    });

    const formData = new FormData();
    formData.append('_token', CSRF_TOKEN);
    formData.append('template_key', currentTpl);
    formData.append('prompt', prompt);
    Object.keys(cardData).forEach(function(k) { formData.append('card_data[' + k + ']', cardData[k]); });

    fetch('/projects/idcard/ai-suggest', { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:formData })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        if (!data.success) { out.innerHTML = '<div class="ai-suggestion">Could not get suggestions. Try again.</div>'; return; }
        const s = data.suggestions || {};
        var html = '';
        if (s.template_tip)   html += '<div class="ai-suggestion"><strong>Tip:</strong> ' + s.template_tip + '</div>';
        if (s.missing_fields) html += '<div class="ai-suggestion"><strong>Completeness:</strong> ' + s.missing_fields + '</div>';
        if (s.design_tips && s.design_tips.length)
            s.design_tips.forEach(function(t){ html += '<div class="ai-suggestion"><strong>Design:</strong> ' + t + '</div>'; });
        if (s.prompt_hint)    html += '<div class="ai-suggestion"><strong>Your request:</strong> ' + s.prompt_hint + '</div>';
        if (s.ai_text)        html += '<div class="ai-suggestion"><strong>AI:</strong> ' + s.ai_text + '</div>';
        out.innerHTML = html || '<div class="ai-suggestion">Looking good! Your card is well structured.</div>';
    })
    .catch(function(){ out.innerHTML = '<div class="ai-suggestion">Network error. Please try again.</div>'; });
}

// =============================================================================
//  Reset & submit
// =============================================================================
function resetForm() {
    photoDataUrl = null;
    setTimeout(updatePreview, 50);
}

document.getElementById('cardForm').addEventListener('submit', function() {
    const btn = document.getElementById('generateBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner"></div> Generating...';
});

['primaryColor','accentColor','bgColor','textColor'].forEach(function(id) {
    document.getElementById(id).addEventListener('input', updateStyleThumbnails);
});

// =============================================================================
//  Init
// =============================================================================
buildStylePicker();
updatePreview();
</script>
