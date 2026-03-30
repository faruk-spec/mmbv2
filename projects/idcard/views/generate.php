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
.style-picker.portrait { grid-template-columns:repeat(5,1fr); }
@media(max-width:680px){ .style-picker{ grid-template-columns:repeat(3,1fr); } }
.style-card {
    border:2px solid var(--border-color); border-radius:10px; cursor:pointer;
    overflow:hidden; transition:all 0.2s; aspect-ratio:85.6/54; position:relative;
}
.style-card.portrait { aspect-ratio:54/85.6; }
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
.id-card-preview.portrait {
    aspect-ratio:54/85.6;
    max-width:220px;
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
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;flex-wrap:wrap;gap:8px;">
        <p style="font-size:0.8rem;color:var(--text-secondary);font-weight:600;margin:0;">SELECT TEMPLATE</p>
        <div style="display:flex;gap:8px;">
            <span style="font-size:0.72rem;color:var(--text-secondary);display:flex;align-items:center;gap:4px;padding:3px 8px;background:var(--bg-secondary);border-radius:8px;border:1px solid var(--border-color);">
                <span style="display:inline-block;width:28px;height:16px;background:#1e40af;border-radius:3px;vertical-align:middle;"></span> Landscape
            </span>
            <span style="font-size:0.72rem;color:var(--text-secondary);display:flex;align-items:center;gap:4px;padding:3px 8px;background:var(--bg-secondary);border-radius:8px;border:1px solid var(--border-color);">
                <span style="display:inline-block;width:18px;height:26px;background:#7c3aed;border-radius:3px;vertical-align:middle;"></span> Portrait
            </span>
        </div>
    </div>
    <div class="tpl-picker" style="display:flex;gap:6px;flex-wrap:wrap;">
        <?php foreach ($templates as $key => $tpl): ?>
        <?php $isPortraitTpl = ($tpl['orientation'] ?? 'landscape') === 'portrait'; ?>
        <button type="button" class="tpl-btn <?= $key === $selectedTpl ? 'active' : '' ?>"
                data-key="<?= htmlspecialchars($key) ?>"
                style="<?= $key === $selectedTpl ? "background:{$tpl['color']}" : '' ?>;position:relative;"
                onclick="selectTemplate('<?= htmlspecialchars($key) ?>')">
            <?php if ($isPortraitTpl): ?>
            <span style="display:inline-block;width:6px;height:10px;border-radius:1px;background:<?= htmlspecialchars($tpl['color']) ?>;margin-right:4px;border:1px solid rgba(0,0,0,0.15);"></span>
            <?php else: ?>
            <span style="display:inline-block;width:10px;height:6px;border-radius:1px;background:<?= htmlspecialchars($tpl['color']) ?>;margin-right:4px;border:1px solid rgba(0,0,0,0.15);"></span>
            <?php endif; ?>
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
                <span id="previewOrientation" style="color:var(--text-secondary);font-size:0.72rem;font-weight:400;margin-left:4px;"><?= ($tplConfig['orientation'] ?? 'landscape') === 'portrait' ? '· Portrait' : '· Landscape' ?></span>
                <span id="previewStyleName" style="color:var(--text-secondary);font-size:0.72rem;font-weight:400;margin-left:2px;"></span>
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
var TEMPLATES    = <?= json_encode($templates) ?>;
var FIELD_LABELS = <?= json_encode($field_labels) ?>;
var CSRF_TOKEN   = '<?= htmlspecialchars($csrfToken) ?>';

var currentTpl   = '<?= htmlspecialchars($selectedTpl) ?>';
var currentStyle = '';   // set during init
var photoDataUrl = null;

// =============================================================================
//  Design style definitions — landscape + portrait
// =============================================================================
var LANDSCAPE_STYLES = [
    { key:'classic',     label:'Angled Pro',   portrait:false },
    { key:'sidebar',     label:'Dark Geo',     portrait:false },
    { key:'wave',        label:'Wave Panel',   portrait:false },
    { key:'bold_header', label:'Bold Split',   portrait:false },
    { key:'diagonal',    label:'Triangle Pro', portrait:false }
];
var PORTRAIT_STYLES = [
    { key:'v_sharp',  label:'Sharp V',      portrait:true },
    { key:'v_curve',  label:'Curve Wave',   portrait:true },
    { key:'v_hex',    label:'Hex Badge',    portrait:true },
    { key:'v_circle', label:'Circle Top',   portrait:true },
    { key:'v_split',  label:'Color Split',  portrait:true }
];
function isPortraitTemplate(key) {
    var tpl = TEMPLATES[key];
    return tpl && tpl.orientation === 'portrait';
}
function getStyleSet() {
    return isPortraitTemplate(currentTpl) ? PORTRAIT_STYLES : LANDSCAPE_STYLES;
}

// =============================================================================
//  Barcode SVG
// =============================================================================
var BARCODE_BARS = [2,1,3,1,1,2,1,3,2,1,1,2,1,1,3,1,2,1,1,3,2,1,2,1,1,3,1,1,2,1,3,1,2,1,1,2,3,1,1];
function barcodeStr(color, width) {
    width = width || '62%';
    var svg = '<svg viewBox="0 0 80 14" xmlns="http://www.w3.org/2000/svg" style="display:block;width:'+width+';height:auto;">';
    var x = 0; var isBar = true;
    for (var i = 0; i < BARCODE_BARS.length; i++) {
        var w = BARCODE_BARS[i];
        if (isBar) svg += '<rect x="'+x.toFixed(1)+'" y="0" width="'+w+'" height="14" fill="'+color+'"/>';
        x += w + 1; isBar = !isBar;
    }
    return svg + '</svg>';
}

// =============================================================================
//  Field short-label map (matching reference images)
// =============================================================================
var FIELD_SHORT = {
    department:'DEPT', employee_id:'ID NO', roll_number:'ROLL NO', id_number:'ID NO',
    badge_id:'BADGE', license_no:'LIC NO', blood_group:'B.GROUP',
    phone:'PHONE', email:'E-MAIL', year:'YEAR', organization:'ORG',
    host_name:'HOST', purpose:'PURPOSE', visit_date:'DATE',
    dob:'D.O.B', expiry_date:'EXPIRE', valid_from:'VALID FROM', valid_till:'VALID TILL',
    nationality:'NATION', branch:'BRANCH', shift:'SHIFT', session:'SESSION',
    reg_number:'REG NO', zone:'ZONE', rank:'RANK', gender:'GENDER', joining_date:'JOINED'
};

// =============================================================================
//  Card data extractor
// =============================================================================
function getCardValues() {
    var tpl  = TEMPLATES[currentTpl] || {};
    var pri  = document.getElementById('primaryColor').value || tpl.color  || '#1e40af';
    var acc  = document.getElementById('accentColor').value  || tpl.accent || '#3b82f6';
    var bg   = document.getElementById('bgColor').value      || tpl.bg     || '#ffffff';
    var txt  = document.getElementById('textColor').value    || tpl.text   || '#1e293b';
    var font = document.getElementById('fontFamily').value   || 'Poppins';

    var roleKeys = ['designation','title','course','event_name'];
    var nameEl   = document.getElementById('field_name');
    var nameVal  = (nameEl && nameEl.value) ? nameEl.value : 'YOUR NAME';

    var orgEl = document.getElementById('field_company_name') || document.getElementById('field_school_name');
    var orgVal = (orgEl && orgEl.value) ? orgEl.value : (tpl.name || 'CardX');

    var addrEl = document.getElementById('field_company_address') || document.getElementById('field_school_address');
    var addrVal = (addrEl && addrEl.value) ? addrEl.value : '';

    var roleVal = 'Creative Designer';
    for (var i = 0; i < roleKeys.length; i++) {
        var el = document.getElementById('field_' + roleKeys[i]);
        if (el && el.value) { roleVal = el.value; break; }
    }

    var skipKeys = ['name','company_name','school_name','company_address','school_address'].concat(roleKeys);
    var fieldKeys = (tpl.fields || []).filter(function(f){ return f !== 'photo' && skipKeys.indexOf(f) === -1; });
    var fieldItems = fieldKeys.slice(0,6).map(function(f) {
        var el    = document.getElementById('field_' + f);
        var val   = el ? (el.value || (FIELD_LABELS[f] || f)) : (FIELD_LABELS[f] || f);
        var label = FIELD_SHORT[f] || f.replace(/_/g,' ').toUpperCase();
        return { label:label, val:val };
    });

    var photoHTML = photoDataUrl
        ? '<img src="'+photoDataUrl+'" style="width:100%;height:100%;object-fit:cover;">'
        : '<i class="fas fa-user" style="font-size:1.8rem;opacity:0.55;color:rgba(255,255,255,0.8);"></i>';

    var tplName = tpl.name || 'CardX';
    var portrait = tpl.orientation === 'portrait';

    return { pri:pri, acc:acc, bg:bg, txt:txt, font:font, nameVal:nameVal, roleVal:roleVal,
             orgVal:orgVal, addrVal:addrVal, fieldItems:fieldItems, photoHTML:photoHTML,
             tplName:tplName, portrait:portrait };
}

function fieldRowsHTML(items, lc, vc, fs) {
    fs = fs || 'clamp(0.38rem,0.9vw,0.54rem)';
    return items.map(function(f){
        return '<div style="display:flex;align-items:baseline;font-size:'+fs+';white-space:nowrap;overflow:hidden;margin-bottom:1.8%;">'
            +'<span style="color:'+lc+';font-weight:700;min-width:30%;letter-spacing:0.03em;">'+f.label+'</span>'
            +'<span style="color:'+vc+';margin-left:2%;">: '+f.val+'</span>'
            +'</div>';
    }).join('');
}

// =============================================================================
//  Style thumbnail builder
// =============================================================================
function buildStyleThumbnail(key, pri, acc, portrait) {
    var W = 85.6, H = 54;
    if (portrait) { W = 54; H = 85.6; }
    var vb = '0 0 ' + W + ' ' + H;

    switch(key) {
        // ── Landscape styles ─────────────────────────────────────────────────
        case 'classic':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<defs><linearGradient id="cg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="'+pri+'"/><stop offset="100%" stop-color="'+acc+'"/></linearGradient>'
                +'<clipPath id="cc"><polygon points="0,0 '+W+',0 '+W+','+(H*0.56)+' 0,'+(H*0.72)+'"/></clipPath></defs>'
                +'<rect width="'+W+'" height="'+H+'" fill="#f7f8fc"/>'
                +'<rect width="'+W+'" height="'+H+'" fill="url(#cg)" clip-path="url(#cc)"/>'
                +'<circle cx="'+W*0.1+'" cy="5" r="3" fill="rgba(255,255,255,0.3)"/>'
                +'<circle cx="'+(W/2)+'" cy="'+(H*0.46)+'" r="7" fill="#fff" stroke="'+pri+'" stroke-width="1.5"/>'
                +'<rect x="'+((W-26)/2)+'" y="'+(H*0.58)+'" width="26" height="3.5" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+((W-18)/2)+'" y="'+(H*0.65)+'" width="18" height="2" rx="1" fill="#aaa"/>'
                +'<rect x="8" y="'+(H*0.73)+'" width="16" height="1.8" rx="0.8" fill="#555" opacity="0.5"/>'
                +'<rect x="'+W/2+'" y="'+(H*0.73)+'" width="16" height="1.8" rx="0.8" fill="#555" opacity="0.5"/>'
                +'<rect x="8" y="'+(H*0.80)+'" width="20" height="1.8" rx="0.8" fill="#555" opacity="0.4"/>'
                +'<rect x="'+W/2+'" y="'+(H*0.80)+'" width="20" height="1.8" rx="0.8" fill="#555" opacity="0.4"/>'
                +'<rect x="'+((W-24)/2)+'" y="'+(H*0.89)+'" width="24" height="4" rx="0.5" fill="#ddd"/>'
                +'</svg>';
        case 'sidebar':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#111827"/>'
                +'<rect x="'+(W*0.42)+'" y="'+(-H*0.2)+'" width="'+(W*0.75)+'" height="'+(W*0.75)+'" rx="3" fill="'+pri+'" transform="rotate(45 '+(W*0.82)+' '+(H*0.3)+')" opacity="0.9"/>'
                +'<circle cx="'+(W*0.8)+'" cy="'+(H*0.35)+'" r="8" fill="rgba(255,255,255,0.18)" stroke="rgba(255,255,255,0.6)" stroke-width="1"/>'
                +'<rect x="5" y="'+(H*0.56)+'" width="28" height="3.5" rx="1.5" fill="#fff" opacity="0.9"/>'
                +'<rect x="5" y="'+(H*0.64)+'" width="20" height="2" rx="1" fill="'+acc+'" opacity="0.7"/>'
                +'<rect x="5" y="'+(H*0.74)+'" width="24" height="1.6" rx="0.6" fill="rgba(255,255,255,0.3)"/>'
                +'<rect x="5" y="'+(H*0.80)+'" width="20" height="1.6" rx="0.6" fill="rgba(255,255,255,0.25)"/>'
                +'<rect x="'+(W*0.6)+'" y="'+(H*0.84)+'" width="22" height="5" rx="0.5" fill="rgba(255,255,255,0.12)"/>'
                +'</svg>';
        case 'wave':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#fdf8f3"/>'
                +'<path d="M0,0 L'+(W*0.55)+',0 Q'+(W*0.73)+','+(H*0.28)+' '+(W*0.65)+','+(H*0.5)+' Q'+(W*0.75)+','+(H*0.72)+' '+(W*0.58)+','+H+' L0,'+H+' Z" fill="'+pri+'"/>'
                +'<circle cx="'+(W*0.42)+'" cy="'+(H*0.4)+'" r="8.5" fill="rgba(255,255,255,0.25)" stroke="rgba(255,255,255,0.7)" stroke-width="1.2"/>'
                +'<rect x="4" y="'+(H*0.7)+'" width="22" height="3" rx="1" fill="#fff" opacity="0.9"/>'
                +'<rect x="'+(W*0.68)+'" y="10" width="22" height="1.8" rx="0.7" fill="#888" opacity="0.5"/>'
                +'<rect x="'+(W*0.68)+'" y="15" width="18" height="1.8" rx="0.7" fill="#888" opacity="0.45"/>'
                +'<rect x="4" y="'+(H*0.88)+'" width="20" height="4" rx="0.4" fill="rgba(255,255,255,0.2)"/>'
                +'</svg>';
        case 'bold_header':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<defs><linearGradient id="tbg" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="'+pri+'"/><stop offset="100%" stop-color="'+acc+'"/></linearGradient></defs>'
                +'<rect x="0" y="0" width="'+(W*0.4)+'" height="'+H+'" fill="url(#tbg)"/>'
                +'<rect x="'+(W*0.4)+'" y="0" width="'+(W*0.6)+'" height="'+H+'" fill="#fff"/>'
                +'<rect x="'+(W*0.4)+'" y="0" width="'+(W*0.6)+'" height="3" fill="url(#tbg)"/>'
                +'<circle cx="'+(W*0.2)+'" cy="'+(H*0.52)+'" r="10" fill="rgba(255,255,255,0.2)" stroke="rgba(255,255,255,0.7)" stroke-width="1.2"/>'
                +'<rect x="'+(W*0.44)+'" y="14" width="30" height="3.5" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+(W*0.44)+'" y="20" width="20" height="2" rx="0.8" fill="#aaa"/>'
                +'<rect x="'+(W*0.44)+'" y="26" width="28" height="1.5" rx="0.7" fill="'+acc+'" opacity="0.5"/>'
                +'<rect x="'+(W*0.44)+'" y="31" width="24" height="1.8" rx="0.7" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.44)+'" y="36" width="28" height="1.8" rx="0.7" fill="#555" opacity="0.45"/>'
                +'</svg>';
        case 'diagonal':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#111827"/>'
                +'<rect x="'+(W*0.55)+'" y="0" width="'+(W*0.45)+'" height="'+H+'" fill="'+pri+'15"/>'
                +'<polygon points="'+W+',0 '+W+','+(H*0.4)+' '+(W*0.55)+','+(H*0.2)+'" fill="'+pri+'"/>'
                +'<polygon points="'+W+','+(H*0.34)+' '+W+','+(H*0.73)+' '+(W*0.58)+','+(H*0.535)+'" fill="'+acc+'" opacity="0.85"/>'
                +'<polygon points="'+W+','+(H*0.64)+' '+W+','+H+' '+(W*0.56)+','+(H*0.83)+'" fill="'+pri+'" opacity="0.7"/>'
                +'<circle cx="'+(W*0.14)+'" cy="'+(H*0.5)+'" r="9" fill="rgba(255,255,255,0.1)" stroke="'+acc+'" stroke-width="1.2"/>'
                +'<rect x="'+(W*0.29)+'" y="17" width="22" height="3" rx="1" fill="#fff" opacity="0.9"/>'
                +'<rect x="5" y="'+(H*0.74)+'" width="20" height="1.6" rx="0.6" fill="rgba(255,255,255,0.25)"/>'
                +'<rect x="5" y="'+(H*0.81)+'" width="18" height="1.6" rx="0.6" fill="rgba(255,255,255,0.2)"/>'
                +'</svg>';

        // ── Portrait styles ───────────────────────────────────────────────────
        case 'v_sharp':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#f7f8fc"/>'
                +'<polygon points="0,0 '+W+',0 '+W+','+(H*0.38)+' '+(W*0.5)+','+(H*0.48)+' 0,'+(H*0.38)+'" fill="'+pri+'"/>'
                +'<circle cx="3" cy="4" r="3" fill="rgba(255,255,255,0.25)"/>'
                +'<circle cx="'+(W*0.5)+'" cy="'+(H*0.43)+'" r="9" fill="#fff" stroke="'+pri+'" stroke-width="1.5"/>'
                +'<rect x="'+(W*0.1)+'" y="'+(H*0.54)+'" width="'+(W*0.8)+'" height="4" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+(W*0.15)+'" y="'+(H*0.61)+'" width="'+(W*0.7)+'" height="2.5" rx="1" fill="#aaa"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.67)+'" width="'+(W*0.85)+'" height="1.5" rx="0.6" fill="#555" opacity="0.3"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.72)+'" width="'+(W*0.85)+'" height="2.2" rx="0.8" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.77)+'" width="'+(W*0.75)+'" height="2.2" rx="0.8" fill="#555" opacity="0.45"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.82)+'" width="'+(W*0.80)+'" height="2.2" rx="0.8" fill="#555" opacity="0.4"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.87)+'" width="'+(W*0.60)+'" height="2.2" rx="0.8" fill="#555" opacity="0.35"/>'
                +'<rect x="'+(W*0.1)+'" y="'+(H*0.93)+'" width="'+(W*0.8)+'" height="4" rx="0.5" fill="#ddd"/>'
                +'</svg>';
        case 'v_curve':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#fafafa"/>'
                +'<path d="M0,0 L'+W+',0 L'+W+','+(H*0.55)+' Q'+(W*0.75)+','+(H*0.75)+' '+(W*0.5)+','+(H*0.62)+' Q'+(W*0.25)+','+(H*0.5)+' 0,'+(H*0.67)+' Z" fill="'+pri+'"/>'
                +'<circle cx="3" cy="3" r="3" fill="rgba(255,255,255,0.25)"/>'
                +'<circle cx="'+(W*0.5)+'" cy="'+(H*0.38)+'" r="10" fill="rgba(255,255,255,0.25)" stroke="rgba(255,255,255,0.7)" stroke-width="1.2"/>'
                +'<rect x="'+(W*0.05)+'" y="'+(H*0.7)+'" width="'+(W*0.9)+'" height="4" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+(W*0.1)+'" y="'+(H*0.77)+'" width="'+(W*0.8)+'" height="2.5" rx="1" fill="#aaa"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.83)+'" width="'+(W*0.85)+'" height="2" rx="0.8" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.87)+'" width="'+(W*0.75)+'" height="2" rx="0.8" fill="#555" opacity="0.45"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.91)+'" width="'+(W*0.7)+'" height="1.8" rx="0.7" fill="#555" opacity="0.4"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.95)+'" width="'+(W*0.55)+'" height="3.5" rx="0.5" fill="#ddd"/>'
                +'</svg>';
        case 'v_hex':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#ffffff"/>'
                +'<rect x="0" y="0" width="'+W+'" height="'+(H*0.45)+'" fill="'+pri+'"/>'
                +'<path d="M0,'+(H*0.42)+' Q'+(W*0.5)+','+(H*0.52)+' '+W+','+(H*0.42)+' L'+W+','+(H*0.45)+' L0,'+(H*0.45)+' Z" fill="#fff"/>'
                +'<circle cx="3" cy="3" r="3" fill="rgba(255,255,255,0.25)"/>'
                +'<polygon points="'+(W*0.5)+','+(H*0.23)+' '+(W*0.68)+','+(H*0.33)+' '+(W*0.68)+','+(H*0.53)+' '+(W*0.5)+','+(H*0.63)+' '+(W*0.32)+','+(H*0.53)+' '+(W*0.32)+','+(H*0.33)+'" fill="#fff" stroke="'+pri+'" stroke-width="1.5"/>'
                +'<circle cx="'+(W*0.5)+'" cy="'+(H*0.43)+'" r="7" fill="'+pri+'20"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.65)+'" width="'+(W*0.84)+'" height="4" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.72)+'" width="'+(W*0.75)+'" height="2.5" rx="1" fill="#aaa"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.78)+'" width="'+(W*0.84)+'" height="2" rx="0.8" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.83)+'" width="'+(W*0.78)+'" height="2" rx="0.8" fill="#555" opacity="0.45"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.88)+'" width="'+(W*0.70)+'" height="2" rx="0.8" fill="#555" opacity="0.4"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.93)+'" width="'+(W*0.84)+'" height="4" rx="0.5" fill="#ddd"/>'
                +'</svg>';
        case 'v_circle':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#f7f8fc"/>'
                +'<rect x="0" y="0" width="'+W+'" height="'+(H*0.46)+'" fill="'+pri+'"/>'
                +'<circle cx="3" cy="3" r="3" fill="rgba(255,255,255,0.25)"/>'
                +'<circle cx="'+(W*0.5)+'" cy="'+(H*0.37)+'" r="11" fill="#fff" stroke="'+acc+'" stroke-width="1.5"/>'
                +'<circle cx="'+(W*0.5)+'" cy="'+(H*0.37)+'" r="8" fill="'+pri+'20"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.58)+'" width="'+(W*0.84)+'" height="4" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+(W*0.12)+'" y="'+(H*0.65)+'" width="'+(W*0.76)+'" height="2.5" rx="1" fill="#aaa"/>'
                +'<rect x="'+(W*0.06)+'" y="'+(H*0.71)+'" width="'+(W*0.88)+'" height="1.5" rx="0.5" fill="'+acc+'" opacity="0.4"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.75)+'" width="'+(W*0.84)+'" height="2.2" rx="0.8" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.80)+'" width="'+(W*0.78)+'" height="2.2" rx="0.8" fill="#555" opacity="0.45"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.85)+'" width="'+(W*0.72)+'" height="2.2" rx="0.8" fill="#555" opacity="0.4"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.90)+'" width="'+(W*0.65)+'" height="2.2" rx="0.8" fill="#555" opacity="0.35"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.95)+'" width="'+(W*0.65)+'" height="3.5" rx="0.5" fill="#ddd"/>'
                +'</svg>';
        case 'v_split':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#fff"/>'
                +'<rect x="0" y="0" width="'+(W*0.58)+'" height="'+(H*0.52)+'" fill="'+pri+'"/>'
                +'<polygon points="'+(W*0.48)+',0 '+W+',0 '+W+','+(H*0.4)+'" fill="'+acc+'" opacity="0.9"/>'
                +'<rect x="0" y="'+(H*0.97)+'" width="'+W+'" height="'+(H*0.04)+'" fill="'+pri+'"/>'
                +'<circle cx="3" cy="3" r="3" fill="rgba(255,255,255,0.25)"/>'
                +'<circle cx="'+(W*0.78)+'" cy="'+(H*0.17)+'" r="9" fill="rgba(255,255,255,0.2)" stroke="rgba(255,255,255,0.6)" stroke-width="1"/>'
                +'<rect x="'+(W*0.06)+'" y="'+(H*0.54)+'" width="'+(W*0.88)+'" height="4" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+(W*0.06)+'" y="'+(H*0.61)+'" width="'+(W*0.75)+'" height="2.5" rx="1" fill="#aaa"/>'
                +'<rect x="'+(W*0.06)+'" y="'+(H*0.68)+'" width="'+(W*0.45)+'" height="1.5" rx="0.5" fill="'+acc+'" opacity="0.6"/>'
                +'<rect x="'+(W*0.06)+'" y="'+(H*0.72)+'" width="'+(W*0.88)+'" height="2" rx="0.8" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.06)+'" y="'+(H*0.77)+'" width="'+(W*0.80)+'" height="2" rx="0.8" fill="#555" opacity="0.45"/>'
                +'<rect x="'+(W*0.06)+'" y="'+(H*0.82)+'" width="'+(W*0.88)+'" height="2" rx="0.8" fill="#555" opacity="0.4"/>'
                +'<rect x="'+(W*0.06)+'" y="'+(H*0.87)+'" width="'+(W*0.70)+'" height="2" rx="0.8" fill="#555" opacity="0.35"/>'
                +'<rect x="'+(W*0.06)+'" y="'+(H*0.92)+'" width="'+(W*0.55)+'" height="3.5" rx="0.5" fill="#ddd"/>'
                +'</svg>';
        default:
            return '<svg viewBox="'+vb+'"><rect width="'+W+'" height="'+H+'" fill="'+pri+'"/></svg>';
    }
}

// =============================================================================
//  Style picker builder
// =============================================================================
function buildStylePicker() {
    var pri     = document.getElementById('primaryColor').value || '#1e40af';
    var acc     = document.getElementById('accentColor').value  || '#3b82f6';
    var styles  = getStyleSet();
    var isPrt   = isPortraitTemplate(currentTpl);
    var picker  = document.getElementById('stylePicker');
    picker.className = 'style-picker' + (isPrt ? ' portrait' : '');
    picker.innerHTML = styles.map(function(s) {
        var thumb   = buildStyleThumbnail(s.key, pri, acc, isPrt);
        var isActive = s.key === currentStyle;
        return '<div>'
            +'<div class="style-card'+(isActive?' active':'')+(isPrt?' portrait':'')+'" id="styleCard_'+s.key+'" onclick="selectStyle(\''+s.key+'\')" title="'+s.label+'">'
            +thumb+'</div>'
            +'<div class="style-label'+(isActive?' active':'')+'" id="styleLabel_'+s.key+'">'+s.label+'</div>'
            +'</div>';
    }).join('');
}

function updateStyleThumbnails() {
    var pri    = document.getElementById('primaryColor').value || '#1e40af';
    var acc    = document.getElementById('accentColor').value  || '#3b82f6';
    var isPrt  = isPortraitTemplate(currentTpl);
    getStyleSet().forEach(function(s) {
        var el = document.getElementById('styleCard_'+s.key);
        if (el) el.innerHTML = buildStyleThumbnail(s.key, pri, acc, isPrt);
    });
}

// =============================================================================
//  Template selector
// =============================================================================
function selectTemplate(key) {
    if (!TEMPLATES[key]) return;
    currentTpl = key;
    var tpl    = TEMPLATES[key];
    var isPrt  = tpl.orientation === 'portrait';
    document.getElementById('template_key').value = key;

    document.querySelectorAll('.tpl-btn').forEach(function(btn) {
        var isActive = btn.dataset.key === key;
        btn.classList.toggle('active', isActive);
        btn.style.background = isActive ? tpl.color : '';
        btn.style.color      = isActive ? '#fff' : '';
    });

    // Rebuild dynamic fields
    var container = document.getElementById('dynamicFields');
    container.innerHTML = '';
    (tpl.fields || []).forEach(function(field) {
        if (field === 'photo') return;
        var label = FIELD_LABELS[field] || field.replace(/_/g,' ');
        container.innerHTML +=
            '<div class="form-group" style="margin-bottom:12px;">'
            +'<label class="form-label" style="font-size:0.78rem;" for="field_'+field+'">'+label+'</label>'
            +'<input type="text" id="field_'+field+'" name="'+field+'" class="form-input" '
            +'style="padding:8px 12px;font-size:0.85rem;" placeholder="Enter '+label.toLowerCase()+'" oninput="updatePreview()">'
            +'</div>';
    });

    // Update colours
    document.getElementById('primaryColor').value = tpl.color;
    document.getElementById('accentColor').value  = tpl.accent;
    document.getElementById('bgColor').value      = tpl.bg;
    document.getElementById('textColor').value    = tpl.text;
    document.getElementById('logoWrap').style.display = tpl.logo ? '' : 'none';
    document.getElementById('previewTplName').textContent = tpl.name;

    // Orientation badge
    var oriEl = document.getElementById('previewOrientation');
    if (oriEl) oriEl.textContent = isPrt ? '· Portrait' : '· Landscape';

    // Update template fields tag list
    var fieldsList = document.getElementById('tplFieldsList');
    fieldsList.innerHTML = (tpl.fields||[]).map(function(f){
        var lbl = FIELD_LABELS[f] || f;
        return '<span style="padding:3px 9px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:12px;font-size:0.7rem;color:var(--text-secondary);">'+lbl+'</span>';
    }).join('');

    // Switch default style for orientation
    var newStyles = isPrt ? PORTRAIT_STYLES : LANDSCAPE_STYLES;
    currentStyle = newStyles[0].key;
    document.getElementById('design_style').value = currentStyle;

    // Update preview card aspect ratio
    var previewEl = document.getElementById('cardPreview');
    if (isPrt) { previewEl.classList.add('portrait'); } else { previewEl.classList.remove('portrait'); }

    buildStylePicker();
    updatePreview();
}

// =============================================================================
//  Style selector
// =============================================================================
function selectStyle(key) {
    currentStyle = key;
    document.getElementById('design_style').value = key;
    getStyleSet().forEach(function(s) {
        var card  = document.getElementById('styleCard_'+s.key);
        var label = document.getElementById('styleLabel_'+s.key);
        if (card)  card.classList.toggle('active', s.key===key);
        if (label) label.classList.toggle('active', s.key===key);
    });
    var styleDef = getStyleSet().find(function(s){ return s.key===key; });
    document.getElementById('previewStyleName').textContent = styleDef ? ('· '+styleDef.label) : '';
    updatePreview();
}

// =============================================================================
//  Card renderers — LANDSCAPE
// =============================================================================
function renderClassic(v) {
    var bc = barcodeStr(v.pri,'48%');
    return '<div style="width:100%;height:100%;background:#f7f8fc;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<div style="position:absolute;top:0;left:0;right:0;height:100%;overflow:hidden;pointer-events:none;">'
        +'<div style="position:absolute;inset:0;background:linear-gradient(135deg,'+v.pri+' 0%,'+v.acc+' 100%);clip-path:polygon(0 0,100% 0,100% 56%,0 72%);"></div></div>'
        +'<div style="position:absolute;top:5%;left:5%;display:flex;align-items:center;gap:5%;">'
        +'<div style="width:8%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.4);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.9);font-size:0.4rem;"></i></div>'
        +'<span style="font-size:clamp(0.36rem,0.82vw,0.52rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.06em;text-transform:uppercase;">'+v.orgVal+'</span></div>'
        +'<div style="position:absolute;left:50%;top:32%;transform:translateX(-50%);width:22%;aspect-ratio:1;border-radius:50%;border:3px solid #fff;background:'+v.pri+'22;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 24px rgba(0,0,0,0.22);">'+v.photoHTML+'</div>'
        +'<div style="position:absolute;top:58%;left:0;right:0;text-align:center;padding:0 4%;">'
        +'<div style="font-size:clamp(0.62rem,1.55vw,0.9rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.36rem,0.85vw,0.54rem);color:#888;margin-top:1%;">'+v.roleVal+'</div></div>'
        +'<div style="position:absolute;top:70%;left:5%;right:5%;display:grid;grid-template-columns:1fr 1fr;column-gap:3%;">'+fieldRowsHTML(v.fieldItems,v.pri,'#444')+'</div>'
        +'<div style="position:absolute;bottom:3%;left:50%;transform:translateX(-50%);">'+bc+'</div>'
        +'</div>';
}
function renderSidebar(v) {
    var bc = barcodeStr('rgba(255,255,255,0.35)','100%');
    return '<div style="width:100%;height:100%;background:#111827;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<svg style="position:absolute;top:-18%;right:-12%;width:60%;aspect-ratio:1;" viewBox="0 0 100 100"><rect x="15" y="15" width="70" height="70" rx="3" fill="'+v.pri+'" transform="rotate(45 50 50)"/></svg>'
        +'<svg style="position:absolute;top:-8%;right:-5%;width:42%;aspect-ratio:1;opacity:0.35;" viewBox="0 0 100 100"><rect x="15" y="15" width="70" height="70" rx="3" fill="'+v.acc+'" transform="rotate(45 50 50)"/></svg>'
        +'<div style="position:absolute;top:6%;right:6%;width:22%;aspect-ratio:1;border-radius:50%;border:2.5px solid rgba(255,255,255,0.7);background:rgba(255,255,255,0.1);overflow:hidden;display:flex;align-items:center;justify-content:center;">'+v.photoHTML+'</div>'
        +'<div style="position:absolute;top:5%;left:5%;display:flex;align-items:center;gap:6%;">'
        +'<div style="width:8%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.7);font-size:0.4rem;"></i></div>'
        +'<span style="font-size:clamp(0.34rem,0.78vw,0.5rem);color:rgba(255,255,255,0.8);font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">'+v.orgVal+'</span></div>'
        +'<div style="position:absolute;bottom:36%;left:5%;">'
        +'<div style="font-size:clamp(0.7rem,1.7vw,1rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:55%;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.38rem,0.85vw,0.54rem);color:'+v.acc+';margin-top:1.5%;">'+v.roleVal+'</div></div>'
        +'<div style="position:absolute;bottom:4%;left:5%;right:50%;">'+fieldRowsHTML(v.fieldItems,'rgba(255,255,255,0.55)','rgba(255,255,255,0.88)')+'</div>'
        +'<div style="position:absolute;bottom:4%;right:4%;width:36%;">'+bc+'</div>'
        +'</div>';
}
function renderWave(v) {
    var bc = barcodeStr('rgba(255,255,255,0.45)','100%');
    return '<div style="width:100%;height:100%;background:#fdf8f3;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<svg style="position:absolute;top:0;left:0;width:44%;height:100%;" viewBox="0 0 88 160" preserveAspectRatio="none"><path d="M0,0 L60,0 Q80,25 70,55 Q85,80 72,110 Q88,135 65,160 L0,160 Z" fill="'+v.pri+'"/></svg>'
        +'<div style="position:absolute;left:24%;top:18%;transform:translateX(-50%);width:24%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.8);background:rgba(255,255,255,0.18);overflow:hidden;display:flex;align-items:center;justify-content:center;">'+v.photoHTML+'</div>'
        +'<div style="position:absolute;bottom:12%;left:5%;max-width:40%;">'
        +'<div style="font-size:clamp(0.6rem,1.4vw,0.82rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.35rem,0.78vw,0.5rem);color:rgba(255,255,255,0.75);margin-top:1.5%;">'+v.roleVal+'</div></div>'
        +'<div style="position:absolute;bottom:3%;left:5%;width:36%;">'+bc+'</div>'
        +'<div style="position:absolute;top:5%;right:5%;display:flex;align-items:center;gap:5%;">'
        +'<div style="width:7%;aspect-ratio:1;border-radius:50%;background:'+v.pri+'22;border:1px solid '+v.pri+'44;display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:'+v.pri+';font-size:0.3rem;"></i></div>'
        +'<span style="font-size:clamp(0.32rem,0.72vw,0.46rem);color:'+v.pri+';font-weight:700;letter-spacing:0.06em;text-transform:uppercase;">'+v.tplName+'</span></div>'
        +'<div style="position:absolute;top:14%;right:4%;width:48%;">'+fieldRowsHTML(v.fieldItems,v.pri,'#4a3728')+'</div>'
        +'</div>';
}
function renderBoldHeader(v) {
    var bc = barcodeStr('rgba(255,255,255,0.4)','100%');
    return '<div style="width:100%;height:100%;display:flex;overflow:hidden;font-family:\''+v.font+'\',sans-serif;">'
        +'<div style="width:40%;background:linear-gradient(170deg,'+v.pri+' 0%,'+v.acc+' 100%);display:flex;flex-direction:column;align-items:center;position:relative;overflow:hidden;flex-shrink:0;">'
        +'<div style="padding:10% 0 5%;position:relative;z-index:1;display:flex;flex-direction:column;align-items:center;gap:6%;">'
        +'<div style="width:22%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1.5px solid rgba(255,255,255,0.5);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:white;font-size:0.4rem;"></i></div>'
        +'<span style="font-size:clamp(0.3rem,0.7vw,0.44rem);color:rgba(255,255,255,0.7);font-weight:600;letter-spacing:0.08em;text-transform:uppercase;text-align:center;">'+v.tplName+'</span></div>'
        +'<div style="width:45%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.8);background:rgba(255,255,255,0.15);overflow:hidden;display:flex;align-items:center;justify-content:center;position:relative;z-index:1;margin-top:4%;">'+v.photoHTML+'</div>'
        +'<div style="margin-top:auto;padding-bottom:6%;width:80%;position:relative;z-index:1;">'+bc+'</div></div>'
        +'<div style="flex:1;background:#ffffff;display:flex;flex-direction:column;justify-content:center;padding:6% 7%;min-width:0;position:relative;">'
        +'<div style="position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,'+v.pri+','+v.acc+');"></div>'
        +'<div style="font-size:clamp(0.62rem,1.52vw,0.88rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.36rem,0.82vw,0.52rem);color:#888;margin-top:1.5%;margin-bottom:4%;">'+v.roleVal+'</div>'
        +'<div style="width:60%;height:2px;background:linear-gradient(90deg,'+v.acc+',transparent);border-radius:2px;margin-bottom:5%;"></div>'
        +fieldRowsHTML(v.fieldItems,v.pri,'#555')
        +'</div></div>';
}
function renderDiagonal(v) {
    var bc = barcodeStr('rgba(255,255,255,0.32)','100%');
    return '<div style="width:100%;height:100%;background:#111827;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<svg style="position:absolute;right:0;top:0;width:48%;height:100%;" viewBox="0 0 96 160" preserveAspectRatio="none">'
        +'<rect x="40" y="0" width="56" height="160" fill="'+v.pri+'18"/>'
        +'<polygon points="96,0 96,62 42,31" fill="'+v.pri+'"/>'
        +'<polygon points="96,52 96,112 48,82" fill="'+v.acc+'" opacity="0.85"/>'
        +'<polygon points="96,100 96,160 44,130" fill="'+v.pri+'" opacity="0.7"/></svg>'
        +'<div style="position:absolute;left:5%;top:50%;transform:translateY(-50%);width:22%;aspect-ratio:1;border-radius:50%;border:2.5px solid '+v.acc+';background:rgba(255,255,255,0.08);overflow:hidden;display:flex;align-items:center;justify-content:center;">'+v.photoHTML+'</div>'
        +'<div style="position:absolute;top:5%;left:5%;display:flex;align-items:center;gap:6%;">'
        +'<div style="width:8%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.25);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.6);font-size:0.4rem;"></i></div>'
        +'<span style="font-size:clamp(0.34rem,0.76vw,0.48rem);color:rgba(255,255,255,0.65);font-weight:700;letter-spacing:0.08em;text-transform:uppercase;">'+v.orgVal+'</span></div>'
        +'<div style="position:absolute;left:32%;top:20%;max-width:30%;">'
        +'<div style="font-size:clamp(0.58rem,1.38vw,0.82rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.76vw,0.5rem);color:'+v.acc+';margin-top:2%;">'+v.roleVal+'</div></div>'
        +'<div style="position:absolute;bottom:6%;left:5%;max-width:50%;">'+fieldRowsHTML(v.fieldItems,'rgba(255,255,255,0.55)','rgba(255,255,255,0.9)')+'</div>'
        +'<div style="position:absolute;bottom:4%;right:4%;width:36%;">'+bc+'</div>'
        +'</div>';
}

// =============================================================================
//  Card renderers — PORTRAIT (vertical)
// =============================================================================
function renderVSharp(v) {
    var bc = barcodeStr(v.pri,'62%');
    return '<div style="width:100%;height:100%;background:#f7f8fc;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<div style="position:absolute;inset:0;background:linear-gradient(160deg,'+v.pri+' 0%,'+v.acc+' 100%);clip-path:polygon(0 0,100% 0,100% 38%,50% 48%,0 38%);"></div>'
        +'<div style="position:absolute;inset:0;background:rgba(255,255,255,0.1);clip-path:polygon(0 0,40% 0,0 20%);pointer-events:none;"></div>'
        // Logo + org top row
        +'<div style="position:absolute;top:3%;left:4%;right:4%;display:flex;align-items:center;justify-content:space-between;z-index:2;">'
        +'<div style="display:flex;align-items:center;gap:6%;">'
        +'<div style="width:12%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.35);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.85);font-size:0.35rem;"></i></div>'
        +'<div style="font-size:clamp(0.42rem,1vw,0.6rem);color:rgba(255,255,255,0.95);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:70%;">'+v.orgVal+'</div></div>'
        +'<div style="width:10%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.18);border:1px solid rgba(255,255,255,0.35);display:flex;align-items:center;justify-content:center;flex-shrink:0;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.8);font-size:0.32rem;"></i></div></div>'
        // Circle photo at V boundary
        +'<div style="position:absolute;left:50%;top:34%;transform:translateX(-50%);width:26%;aspect-ratio:1;border-radius:50%;border:3px solid #fff;background:'+v.pri+'22;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 28px rgba(0,0,0,0.25);z-index:3;">'+v.photoHTML+'</div>'
        // Name + role
        +'<div style="position:absolute;top:62%;left:4%;right:4%;text-align:center;">'
        +'<div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#777;margin-top:1%;">'+v.roleVal+'</div></div>'
        // Accent divider
        +'<div style="position:absolute;top:71%;left:10%;right:10%;height:1.5px;background:linear-gradient(90deg,transparent,'+v.acc+',transparent);opacity:0.5;"></div>'
        // Fields
        +'<div style="position:absolute;top:73%;left:6%;right:6%;">'+fieldRowsHTML(v.fieldItems,v.pri,'#444','clamp(0.38rem,0.9vw,0.54rem)')+'</div>'
        // Barcode
        +'<div style="position:absolute;bottom:2%;left:50%;transform:translateX(-50%);">'+bc+'</div>'
        +'</div>';
}

function renderVCurve(v) {
    var bc = barcodeStr(v.pri,'62%');
    return '<div style="width:100%;height:100%;background:#fafafa;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<svg style="position:absolute;top:0;left:0;width:100%;height:50%;" viewBox="0 0 100 100" preserveAspectRatio="none">'
        +'<path d="M0,0 L100,0 L100,70 Q75,95 50,80 Q25,65 0,85 Z" fill="'+v.pri+'"/>'
        +'<path d="M0,0 L100,0 L100,55 Q70,80 50,65 Q25,50 0,70 Z" fill="rgba(255,255,255,0.1)"/></svg>'
        +'<div style="position:absolute;bottom:-8%;right:-8%;width:35%;aspect-ratio:1;border-radius:50%;background:'+v.acc+';opacity:0.12;"></div>'
        +'<div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:8%;z-index:2;">'
        +'<div style="width:12%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.35);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.85);font-size:0.35rem;"></i></div>'
        +'<span style="font-size:clamp(0.4rem,1vw,0.56rem);color:rgba(255,255,255,0.9);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:70%;">'+v.orgVal+'</span></div>'
        +'<div style="position:absolute;left:50%;top:30%;transform:translateX(-50%);width:28%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.9);background:rgba(255,255,255,0.2);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 28px rgba(0,0,0,0.3);z-index:3;">'+v.photoHTML+'</div>'
        +'<div style="position:absolute;top:62%;left:4%;right:4%;text-align:center;">'
        +'<div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#888;margin-top:1%;">'+v.roleVal+'</div></div>'
        +'<div style="position:absolute;top:70%;left:10%;right:10%;height:1px;background:'+v.pri+';opacity:0.2;"></div>'
        +'<div style="position:absolute;top:72%;left:6%;right:6%;">'+fieldRowsHTML(v.fieldItems,v.pri,'#444','clamp(0.38rem,0.9vw,0.54rem)')+'</div>'
        +'<div style="position:absolute;bottom:2%;left:50%;transform:translateX(-50%);">'+bc+'</div>'
        +'</div>';
}

function renderVHex(v) {
    var bc = barcodeStr(v.pri,'62%');
    return '<div style="width:100%;height:100%;background:#ffffff;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<div style="position:absolute;top:0;left:0;right:0;height:45%;background:linear-gradient(135deg,'+v.pri+' 0%,'+v.acc+' 100%);overflow:hidden;">'
        +'<svg style="position:absolute;top:-15%;right:-12%;width:45%;aspect-ratio:1;opacity:0.18;" viewBox="0 0 100 100"><rect x="10" y="10" width="80" height="80" rx="4" fill="#fff" transform="rotate(45 50 50)"/></svg></div>'
        +'<div style="position:absolute;top:38%;left:0;right:0;height:8%;overflow:hidden;">'
        +'<svg viewBox="0 0 100 20" preserveAspectRatio="none" style="width:100%;height:100%;"><path d="M0,20 Q50,-5 100,20 L100,0 L0,0 Z" fill="'+v.pri+'"/><path d="M0,20 Q50,5 100,20" fill="none" stroke="#fff" stroke-width="1.5"/></svg></div>'
        +'<div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:8%;z-index:2;">'
        +'<div style="width:12%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.35);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.85);font-size:0.35rem;"></i></div>'
        +'<div style="font-size:clamp(0.4rem,1vw,0.56rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;">'+v.orgVal+'</div></div>'
        // Hexagonal photo
        +'<div style="position:absolute;left:50%;top:25%;transform:translateX(-50%);width:28%;aspect-ratio:1;z-index:4;">'
        +'<svg style="position:absolute;inset:-15%;width:130%;height:130%;" viewBox="0 0 100 100"><polygon points="50,2 95,26 95,74 50,98 5,74 5,26" fill="#fff" stroke="'+v.pri+'" stroke-width="2"/></svg>'
        +'<div style="position:absolute;inset:0;overflow:hidden;clip-path:polygon(50% 4%,93% 26%,93% 74%,50% 96%,7% 74%,7% 26%);display:flex;align-items:center;justify-content:center;background:'+v.pri+'20;">'+v.photoHTML+'</div></div>'
        // Name + role
        +'<div style="position:absolute;top:58%;left:4%;right:4%;text-align:center;">'
        +'<div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#888;margin-top:1%;">'+v.roleVal+'</div></div>'
        +'<div style="position:absolute;top:66%;left:8%;right:8%;height:1.5px;background:'+v.pri+';opacity:0.25;"></div>'
        +'<div style="position:absolute;top:68%;left:6%;right:6%;">'+fieldRowsHTML(v.fieldItems,v.pri,'#444','clamp(0.38rem,0.9vw,0.54rem)')+'</div>'
        +'<div style="position:absolute;bottom:2%;left:50%;transform:translateX(-50%);">'+bc+'</div>'
        +'</div>';
}

function renderVCircle(v) {
    var bc = barcodeStr(v.pri,'62%');
    return '<div style="width:100%;height:100%;background:#f7f8fc;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<div style="position:absolute;top:0;left:0;right:0;height:46%;background:linear-gradient(150deg,'+v.pri+' 0%,'+v.acc+' 100%);overflow:hidden;">'
        +'<svg style="position:absolute;right:-10%;bottom:-20%;width:60%;aspect-ratio:1;opacity:0.1;" viewBox="0 0 100 100">'
        +'<circle cx="50" cy="50" r="48" fill="none" stroke="#fff" stroke-width="6"/>'
        +'<circle cx="50" cy="50" r="35" fill="none" stroke="#fff" stroke-width="4"/>'
        +'<circle cx="50" cy="50" r="20" fill="none" stroke="#fff" stroke-width="3"/></svg></div>'
        +'<div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:6%;z-index:2;">'
        +'<div style="width:12%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.35);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.85);font-size:0.35rem;"></i></div>'
        +'<div style="font-size:clamp(0.38rem,0.9vw,0.54rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;">'+v.orgVal+'</div></div>'
        // Large circle photo
        +'<div style="position:absolute;left:50%;top:25%;transform:translateX(-50%);width:30%;aspect-ratio:1;border-radius:50%;border:4px solid #fff;background:'+v.pri+'22;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 32px rgba(0,0,0,0.28);z-index:3;">'+v.photoHTML+'</div>'
        // Name + role
        +'<div style="position:absolute;top:60%;left:4%;right:4%;text-align:center;">'
        +'<div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#777;margin-top:1%;">'+v.roleVal+'</div></div>'
        +'<div style="position:absolute;top:68%;left:10%;right:10%;height:2px;background:linear-gradient(90deg,transparent,'+v.acc+',transparent);opacity:0.6;"></div>'
        +'<div style="position:absolute;top:70%;left:6%;right:6%;">'+fieldRowsHTML(v.fieldItems,v.pri,'#444','clamp(0.38rem,0.9vw,0.54rem)')+'</div>'
        +'<div style="position:absolute;bottom:2%;left:50%;transform:translateX(-50%);">'+bc+'</div>'
        +'</div>';
}

function renderVSplit(v) {
    var bc = barcodeStr(v.pri,'55%');
    return '<div style="width:100%;height:100%;background:#ffffff;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<div style="position:absolute;top:0;left:0;width:55%;height:52%;background:'+v.pri+';overflow:hidden;">'
        +'<div style="position:absolute;bottom:-4%;right:-4%;width:50%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.08);"></div></div>'
        +'<div style="position:absolute;top:0;right:0;width:48%;height:40%;background:'+v.acc+';clip-path:polygon(10% 0,100% 0,100% 100%,0 100%);"></div>'
        +'<div style="position:absolute;bottom:0;left:0;right:0;height:5%;background:'+v.pri+';"></div>'
        +'<div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:7%;z-index:2;">'
        +'<div style="width:12%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.35);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.85);font-size:0.35rem;"></i></div>'
        +'<span style="font-size:clamp(0.38rem,0.9vw,0.54rem);color:rgba(255,255,255,0.9);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:65%;">'+v.orgVal+'</span></div>'
        // Photo upper-right
        +'<div style="position:absolute;top:5%;right:4%;width:28%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.85);background:rgba(255,255,255,0.15);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 22px rgba(0,0,0,0.25);z-index:3;">'+v.photoHTML+'</div>'
        // Name + role
        +'<div style="position:absolute;top:46%;left:4%;right:4%;">'
        +'<div style="font-size:clamp(0.82rem,2vw,1.08rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#888;margin-top:1%;">'+v.roleVal+'</div></div>'
        +'<div style="position:absolute;top:56%;left:4%;right:4%;height:2px;background:linear-gradient(90deg,'+v.acc+',transparent);"></div>'
        +'<div style="position:absolute;top:58%;left:4%;right:4%;">'+fieldRowsHTML(v.fieldItems,v.pri,'#444','clamp(0.38rem,0.9vw,0.54rem)')+'</div>'
        +'<div style="position:absolute;bottom:7%;left:4%;width:50%;">'+bc+'</div>'
        +'</div>';
}

// =============================================================================
//  Main preview updater
// =============================================================================
function updatePreview() {
    var v       = getCardValues();
    updateStyleThumbnails();
    var preview = document.getElementById('cardPreview');
    preview.style.fontFamily = "'"+v.font+"',sans-serif";
    var html = '';
    switch (currentStyle) {
        case 'sidebar':     html = renderSidebar(v);    break;
        case 'wave':        html = renderWave(v);       break;
        case 'bold_header': html = renderBoldHeader(v); break;
        case 'diagonal':    html = renderDiagonal(v);   break;
        case 'v_sharp':     html = renderVSharp(v);     break;
        case 'v_curve':     html = renderVCurve(v);     break;
        case 'v_hex':       html = renderVHex(v);       break;
        case 'v_circle':    html = renderVCircle(v);    break;
        case 'v_split':     html = renderVSplit(v);     break;
        default:            html = renderClassic(v);    break;
    }
    preview.innerHTML = html;
}

// =============================================================================
//  Photo preview
// =============================================================================
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) { photoDataUrl = e.target.result; updatePreview(); };
        reader.readAsDataURL(input.files[0]);
    }
}

// =============================================================================
//  AI Suggestions (AJAX)
// =============================================================================
function getAISuggestions() {
    var prompt = document.getElementById('aiPrompt').value.trim();
    var out    = document.getElementById('aiOutput');
    out.style.display = 'block';
    out.innerHTML = '<div style="text-align:center;padding:12px;"><div class="spinner"></div><p style="font-size:0.75rem;margin-top:6px;color:var(--text-secondary);">Generating suggestions...</p></div>';
    var cardData = {};
    var tpl = TEMPLATES[currentTpl] || {};
    (tpl.fields||[]).forEach(function(f){ var el=document.getElementById('field_'+f); if(el) cardData[f]=el.value; });
    var fd = new FormData();
    fd.append('_token', CSRF_TOKEN);
    fd.append('template_key', currentTpl);
    fd.append('prompt', prompt);
    Object.keys(cardData).forEach(function(k){ fd.append('card_data['+k+']', cardData[k]); });
    fetch('/projects/idcard/ai-suggest',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:fd})
    .then(function(r){ return r.json(); })
    .then(function(data){
        if(!data.success){out.innerHTML='<div class="ai-suggestion">Could not get suggestions. Try again.</div>';return;}
        var s=data.suggestions||{};var html='';
        if(s.template_tip)   html+='<div class="ai-suggestion"><strong>Tip:</strong> '+s.template_tip+'</div>';
        if(s.missing_fields) html+='<div class="ai-suggestion"><strong>Completeness:</strong> '+s.missing_fields+'</div>';
        if(s.design_tips&&s.design_tips.length) s.design_tips.forEach(function(t){html+='<div class="ai-suggestion"><strong>Design:</strong> '+t+'</div>';});
        if(s.ai_text)        html+='<div class="ai-suggestion"><strong>AI:</strong> '+s.ai_text+'</div>';
        out.innerHTML=html||'<div class="ai-suggestion">Looking good! Your card is well structured.</div>';
    }).catch(function(){out.innerHTML='<div class="ai-suggestion">Network error. Please try again.</div>';});
}

// =============================================================================
//  Reset & submit
// =============================================================================
function resetForm() { photoDataUrl = null; setTimeout(updatePreview, 50); }
document.getElementById('cardForm').addEventListener('submit',function(){
    var btn=document.getElementById('generateBtn');
    btn.disabled=true;
    btn.innerHTML='<div class="spinner"></div> Generating...';
});
['primaryColor','accentColor','bgColor','textColor'].forEach(function(id){
    document.getElementById(id).addEventListener('input', updateStyleThumbnails);
});

// =============================================================================
//  Init
// =============================================================================
(function init() {
    var isPrt = isPortraitTemplate(currentTpl);
    currentStyle = isPrt ? PORTRAIT_STYLES[0].key : LANDSCAPE_STYLES[0].key;
    document.getElementById('design_style').value = currentStyle;
    if (isPrt) { document.getElementById('cardPreview').classList.add('portrait'); }
    var oriEl = document.getElementById('previewOrientation');
    if (oriEl) oriEl.textContent = isPrt ? '· Portrait' : '· Landscape';
    buildStylePicker();
    updatePreview();
})();
</script>
