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
var TEMPLATES    = <?= json_encode($templates) ?>;
var FIELD_LABELS = <?= json_encode($field_labels) ?>;
var CSRF_TOKEN   = '<?= htmlspecialchars($csrfToken) ?>';

var currentTpl   = '<?= htmlspecialchars($selectedTpl) ?>';
var currentStyle = 'classic';
var photoDataUrl = null;

// =============================================================================
//  Design style definitions (matching reference images exactly)
// =============================================================================
var DESIGN_STYLES = [
    { key:'classic',     label:'Angled Pro',   desc:'Coloured angled header, centred circle photo' },
    { key:'sidebar',     label:'Dark Geo',     desc:'Dark card with geometric diamond accent shape' },
    { key:'wave',        label:'Wave Panel',   desc:'Cream card with organic wave left, fields right' },
    { key:'bold_header', label:'Bold Split',   desc:'Coloured left panel, white right panel with fields' },
    { key:'diagonal',    label:'Triangle Pro', desc:'Dark card with arrow triangle shapes and photo' }
];

// =============================================================================
//  Barcode SVG (pre-computed realistic barcode pattern)
// =============================================================================
var BARCODE_BARS = [2,1,3,1,1,2,1,3,2,1,1,2,1,1,3,1,2,1,1,3,2,1,2,1,1,3,1,1,2,1,3,1,2,1,1,2,3,1,1];

function barcodeStr(color, width) {
    width = width || '52%';
    var svg = '<svg viewBox="0 0 80 13" xmlns="http://www.w3.org/2000/svg" style="display:block;width:'+width+';height:auto;">';
    var x = 0; var isBar = true;
    for (var i = 0; i < BARCODE_BARS.length; i++) {
        var w = BARCODE_BARS[i];
        if (isBar) svg += '<rect x="'+x.toFixed(1)+'" y="0" width="'+w+'" height="13" fill="'+color+'"/>';
        x += w + 1;
        isBar = !isBar;
    }
    return svg + '</svg>';
}

// =============================================================================
//  Style picker thumbnails  (accurate miniature previews)
// =============================================================================
function buildStyleThumbnail(key, pri, acc, bg, txt) {
    var W = 85.6, H = 54;
    switch(key) {

        case 'classic':
            // White card — coloured angled header — centred circle photo
            return '<svg viewBox="0 0 ' + W + ' ' + H + '" xmlns="http://www.w3.org/2000/svg">' +
                '<defs>' +
                '<linearGradient id="tcg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="'+pri+'"/><stop offset="100%" stop-color="'+acc+'"/></linearGradient>' +
                '<clipPath id="tcc"><polygon points="0,0 '+W+',0 '+W+','+(H*0.56)+' 0,'+(H*0.72)+'"/></clipPath>' +
                '</defs>' +
                '<rect width="'+W+'" height="'+H+'" fill="#f7f8fc"/>' +
                '<rect width="'+W+'" height="'+H+'" fill="url(#tcg)" clip-path="url(#tcc)"/>' +
                // Logo area top-left
                '<circle cx="6" cy="5" r="3" fill="rgba(255,255,255,0.3)"/>' +
                '<rect x="11" y="3.5" width="18" height="2" rx="1" fill="rgba(255,255,255,0.7)"/>' +
                // Centred circle photo at boundary
                '<circle cx="'+W/2+'" cy="'+H*0.46+'" r="7" fill="#fff" stroke="'+pri+'" stroke-width="1.5"/>' +
                '<circle cx="'+W/2+'" cy="'+H*0.46+'" r="5" fill="'+pri+'22"/>' +
                // Name below photo
                '<rect x="'+((W-26)/2)+'" y="'+H*0.58+'" width="26" height="3.5" rx="1.5" fill="'+pri+'" opacity="0.85"/>' +
                '<rect x="'+((W-18)/2)+'" y="'+H*0.65+'" width="18" height="2" rx="1" fill="#aaa"/>' +
                // Fields 2-col
                '<rect x="8" y="'+H*0.72+'" width="16" height="1.8" rx="0.8" fill="#555" opacity="0.5"/>' +
                '<rect x="'+W/2+'" y="'+H*0.72+'" width="16" height="1.8" rx="0.8" fill="#555" opacity="0.5"/>' +
                '<rect x="8" y="'+H*0.78+'" width="20" height="1.8" rx="0.8" fill="#555" opacity="0.4"/>' +
                '<rect x="'+W/2+'" y="'+H*0.78+'" width="20" height="1.8" rx="0.8" fill="#555" opacity="0.4"/>' +
                // Barcode
                '<rect x="'+((W-24)/2)+'" y="'+H*0.88+'" width="24" height="5" rx="0.5" fill="#ddd"/>' +
                '</svg>';

        case 'sidebar':
            // Dark card — rotated diamond upper-right — photo circle inside
            return '<svg viewBox="0 0 ' + W + ' ' + H + '" xmlns="http://www.w3.org/2000/svg">' +
                '<rect width="'+W+'" height="'+H+'" fill="#111827"/>' +
                // Diamond shape upper-right
                '<rect x="'+W*0.45+'" y="'+(-H*0.2)+'" width="'+W*0.75+'" height="'+W*0.75+'" rx="3"' +
                '  fill="'+pri+'" transform="rotate(45 '+(W*0.82)+' '+(H*0.3)+')" opacity="0.9"/>' +
                // Photo in diamond area
                '<circle cx="'+W*0.8+'" cy="'+H*0.35+'" r="8" fill="rgba(255,255,255,0.18)" stroke="rgba(255,255,255,0.6)" stroke-width="1"/>' +
                // Logo + company top-left
                '<circle cx="6" cy="5.5" r="3" fill="rgba(255,255,255,0.2)"/>' +
                '<rect x="11" y="4" width="20" height="2" rx="1" fill="rgba(255,255,255,0.5)"/>' +
                // Name
                '<rect x="5" y="'+H*0.56+'" width="28" height="3.5" rx="1.5" fill="#fff" opacity="0.9"/>' +
                '<rect x="5" y="'+H*0.64+'" width="20" height="2" rx="1" fill="'+acc+'" opacity="0.7"/>' +
                // Fields stacked
                '<rect x="5" y="'+H*0.74+'" width="24" height="1.6" rx="0.6" fill="rgba(255,255,255,0.3)"/>' +
                '<rect x="5" y="'+H*0.80+'" width="20" height="1.6" rx="0.6" fill="rgba(255,255,255,0.25)"/>' +
                '<rect x="5" y="'+H*0.86+'" width="16" height="1.6" rx="0.6" fill="rgba(255,255,255,0.2)"/>' +
                // Barcode right
                '<rect x="'+W*0.6+'" y="'+H*0.84+'" width="22" height="5" rx="0.5" fill="rgba(255,255,255,0.12)"/>' +
                '</svg>';

        case 'wave':
            // Cream — dark organic wave left — photo boundary — fields right
            return '<svg viewBox="0 0 ' + W + ' ' + H + '" xmlns="http://www.w3.org/2000/svg">' +
                '<rect width="'+W+'" height="'+H+'" fill="#fdf8f3"/>' +
                // Wave/blob shape left
                '<path d="M0,0 L'+W*0.55+',0 Q'+W*0.73+','+H*0.28+' '+W*0.65+','+H*0.5+' Q'+W*0.75+','+H*0.72+' '+W*0.58+','+H+' L0,'+H+' Z" fill="'+pri+'"/>' +
                '<path d="M0,0 L'+W*0.4+',0 Q'+W*0.56+','+H*0.25+' '+W*0.5+','+H*0.5+' Q'+W*0.58+','+H*0.75+' '+W*0.43+','+H+' L0,'+H+' Z" fill="rgba(255,255,255,0.07)"/>' +
                // Photo at boundary
                '<circle cx="'+W*0.42+'" cy="'+H*0.4+'" r="8.5" fill="rgba(255,255,255,0.25)" stroke="rgba(255,255,255,0.7)" stroke-width="1.2"/>' +
                // Name bottom-left
                '<rect x="4" y="'+H*0.7+'" width="22" height="3" rx="1" fill="#fff" opacity="0.9"/>' +
                '<rect x="4" y="'+H*0.78+'" width="16" height="1.8" rx="0.7" fill="rgba(255,255,255,0.6)"/>' +
                // Barcode bottom-left
                '<rect x="4" y="'+H*0.88+'" width="20" height="4" rx="0.4" fill="rgba(255,255,255,0.2)"/>' +
                // Logo top-right
                '<circle cx="'+W*0.9+'" cy="5" r="3" fill="'+pri+'33"/>' +
                // Fields right
                '<rect x="'+W*0.68+'" y="10" width="22" height="1.8" rx="0.7" fill="#888" opacity="0.5"/>' +
                '<rect x="'+W*0.68+'" y="15" width="20" height="1.8" rx="0.7" fill="#888" opacity="0.45"/>' +
                '<rect x="'+W*0.68+'" y="20" width="24" height="1.8" rx="0.7" fill="#888" opacity="0.4"/>' +
                '<rect x="'+W*0.68+'" y="25" width="18" height="1.8" rx="0.7" fill="#888" opacity="0.35"/>' +
                '</svg>';

        case 'bold_header':
            // Coloured left panel — white right panel
            return '<svg viewBox="0 0 ' + W + ' ' + H + '" xmlns="http://www.w3.org/2000/svg">' +
                '<defs><linearGradient id="tbg" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="'+pri+'"/><stop offset="100%" stop-color="'+acc+'"/></linearGradient></defs>' +
                // Left panel
                '<rect x="0" y="0" width="'+W*0.4+'" height="'+H+'" fill="url(#tbg)"/>' +
                // Decorative circles in left panel
                '<circle cx="'+W*0.2+'" cy="-4" r="14" fill="rgba(255,255,255,0.06)"/>' +
                '<circle cx="0" cy="'+H*0.8+'" r="10" fill="rgba(255,255,255,0.05)"/>' +
                // Logo top of left panel
                '<circle cx="'+W*0.2+'" cy="6" r="3" fill="rgba(255,255,255,0.25)"/>' +
                // Photo circle centred in left panel
                '<circle cx="'+W*0.2+'" cy="'+H*0.52+'" r="10" fill="rgba(255,255,255,0.2)" stroke="rgba(255,255,255,0.7)" stroke-width="1.2"/>' +
                // Barcode bottom of left panel
                '<rect x="4" y="'+H*0.88+'" width="'+W*0.32+'" height="4" rx="0.4" fill="rgba(255,255,255,0.18)"/>' +
                // Right panel
                '<rect x="'+W*0.4+'" y="0" width="'+W*0.6+'" height="'+H+'" fill="#fff"/>' +
                // Accent line top of right panel
                '<rect x="'+W*0.4+'" y="0" width="'+W*0.6+'" height="3" fill="url(#tbg)"/>' +
                // Name
                '<rect x="'+W*0.44+'" y="14" width="30" height="3.5" rx="1.5" fill="'+pri+'" opacity="0.85"/>' +
                '<rect x="'+W*0.44+'" y="20" width="20" height="2" rx="0.8" fill="#aaa"/>' +
                // Divider
                '<rect x="'+W*0.44+'" y="26" width="28" height="1.5" rx="0.7" fill="'+acc+'" opacity="0.5"/>' +
                // Fields right
                '<rect x="'+W*0.44+'" y="31" width="24" height="1.8" rx="0.7" fill="#555" opacity="0.5"/>' +
                '<rect x="'+W*0.44+'" y="36" width="28" height="1.8" rx="0.7" fill="#555" opacity="0.45"/>' +
                '<rect x="'+W*0.44+'" y="41" width="20" height="1.8" rx="0.7" fill="#555" opacity="0.4"/>' +
                '<rect x="'+W*0.44+'" y="46" width="24" height="1.8" rx="0.7" fill="#555" opacity="0.35"/>' +
                '</svg>';

        case 'diagonal':
            // Dark — arrow triangle shapes right — photo+text left
            return '<svg viewBox="0 0 ' + W + ' ' + H + '" xmlns="http://www.w3.org/2000/svg">' +
                '<rect width="'+W+'" height="'+H+'" fill="#111827"/>' +
                // Right background tint
                '<rect x="'+W*0.55+'" y="0" width="'+W*0.45+'" height="'+H+'" fill="'+pri+'15"/>' +
                // Arrow triangle 1 (top)
                '<polygon points="'+W+',0 '+W+','+H*0.4+' '+W*0.55+','+H*0.2+'" fill="'+pri+'"/>' +
                // Arrow triangle 2 (middle)
                '<polygon points="'+W+','+H*0.34+' '+W+','+H*0.73+' '+W*0.58+','+H*0.535+'" fill="'+acc+'" opacity="0.85"/>' +
                // Arrow triangle 3 (bottom)
                '<polygon points="'+W+','+H*0.64+' '+W+','+H+' '+W*0.56+','+H*0.83+'" fill="'+pri+'" opacity="0.7"/>' +
                // Photo left
                '<circle cx="'+W*0.14+'" cy="'+H*0.5+'" r="9" fill="rgba(255,255,255,0.1)" stroke="'+acc+'" stroke-width="1.2"/>' +
                // Logo top-left
                '<circle cx="6" cy="5" r="3" fill="rgba(255,255,255,0.15)"/>' +
                '<rect x="11" y="3.5" width="18" height="2" rx="1" fill="rgba(255,255,255,0.4)"/>' +
                // Name + role
                '<rect x="'+W*0.29+'" y="17" width="22" height="3" rx="1" fill="#fff" opacity="0.9"/>' +
                '<rect x="'+W*0.29+'" y="23" width="16" height="2" rx="0.8" fill="'+acc+'" opacity="0.7"/>' +
                // Fields
                '<rect x="5" y="'+H*0.74+'" width="20" height="1.6" rx="0.6" fill="rgba(255,255,255,0.25)"/>' +
                '<rect x="5" y="'+H*0.81+'" width="18" height="1.6" rx="0.6" fill="rgba(255,255,255,0.2)"/>' +
                '<rect x="5" y="'+H*0.88+'" width="22" height="1.6" rx="0.6" fill="rgba(255,255,255,0.18)"/>' +
                // Barcode right
                '<rect x="'+W*0.6+'" y="'+H*0.86+'" width="24" height="5" rx="0.4" fill="rgba(255,255,255,0.1)"/>' +
                '</svg>';

        default:
            return '<svg viewBox="0 0 '+W+' '+H+'"><rect width="'+W+'" height="'+H+'" fill="'+pri+'"/></svg>';
    }
}

function buildStylePicker() {
    var pri = document.getElementById('primaryColor').value || '#1e40af';
    var acc = document.getElementById('accentColor').value  || '#3b82f6';
    var bg  = document.getElementById('bgColor').value      || '#ffffff';
    var txt = document.getElementById('textColor').value    || '#1e293b';
    var picker = document.getElementById('stylePicker');
    picker.innerHTML = DESIGN_STYLES.map(function(s) {
        var thumb    = buildStyleThumbnail(s.key, pri, acc, bg, txt);
        var isActive = s.key === currentStyle;
        return '<div>' +
            '<div class="style-card' + (isActive ? ' active' : '') + '" id="styleCard_' + s.key + '" ' +
            'title="' + s.desc + '" onclick="selectStyle(\'' + s.key + '\')">' +
            thumb + '</div>' +
            '<div class="style-label' + (isActive ? ' active' : '') + '" id="styleLabel_' + s.key + '">' + s.label + '</div>' +
            '</div>';
    }).join('');
}

function updateStyleThumbnails() {
    var pri = document.getElementById('primaryColor').value || '#1e40af';
    var acc = document.getElementById('accentColor').value  || '#3b82f6';
    var bg  = document.getElementById('bgColor').value      || '#ffffff';
    var txt = document.getElementById('textColor').value    || '#1e293b';
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
    var tpl    = TEMPLATES[key];
    document.getElementById('template_key').value = key;

    document.querySelectorAll('.tpl-btn').forEach(function(btn) {
        var isActive = btn.dataset.key === key;
        btn.classList.toggle('active', isActive);
        btn.style.background = isActive ? tpl.color : '';
        btn.style.color      = isActive ? '#fff' : '';
    });

    var container = document.getElementById('dynamicFields');
    container.innerHTML = '';
    (tpl.fields || []).forEach(function(field) {
        if (field === 'photo') return;
        var label = FIELD_LABELS[field] || field.replace(/_/g, ' ');
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

    var fieldsList = document.getElementById('tplFieldsList');
    fieldsList.innerHTML = (tpl.fields || []).map(function(f) {
        var lbl = FIELD_LABELS[f] || f;
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
        if (label) { label.classList.toggle('active', isActive); }
    });
    var styleDef = DESIGN_STYLES.find(function(s){ return s.key === key; });
    document.getElementById('previewStyleName').textContent = styleDef ? ('· ' + styleDef.label) : '';
    updatePreview();
}

// =============================================================================
//  Card data helpers
// =============================================================================
var FIELD_SHORT_LABELS = {
    department:'DEPT', employee_id:'ID NO', roll_number:'ROLL NO', id_number:'ID NO',
    badge_id:'BADGE', license_no:'LIC NO', blood_group:'B.GROUP',
    phone:'PHONE', email:'E-MAIL', year:'YEAR', organization:'ORG',
    host_name:'HOST', purpose:'PURPOSE', visit_date:'DATE'
};

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
    var roleVal  = '';
    for (var i = 0; i < roleKeys.length; i++) {
        var el = document.getElementById('field_' + roleKeys[i]);
        if (el && el.value) { roleVal = el.value; break; }
    }
    roleVal = roleVal || 'Creative Designer';

    var fieldKeys  = (tpl.fields || []).filter(function(f){ return f !== 'photo' && f !== 'name' && roleKeys.indexOf(f) === -1; });
    var fieldItems = fieldKeys.slice(0, 5).map(function(f) {
        var el    = document.getElementById('field_' + f);
        var val   = el ? (el.value || (FIELD_LABELS[f] || f)) : (FIELD_LABELS[f] || f);
        var label = FIELD_SHORT_LABELS[f] || f.replace(/_/g,' ').toUpperCase();
        return { label: label, val: val };
    });

    var photoHTML = photoDataUrl
        ? '<img src="' + photoDataUrl + '" style="width:100%;height:100%;object-fit:cover;">'
        : '<i class="fas fa-user" style="font-size:1.8rem;opacity:0.55;"></i>';

    var tplName = tpl.name || 'CardX';

    return { pri:pri, acc:acc, bg:bg, txt:txt, font:font, nameVal:nameVal, roleVal:roleVal, fieldItems:fieldItems, photoHTML:photoHTML, tplName:tplName };
}

function fieldRowsHTML(items, labelColor, valueColor) {
    return items.map(function(f) {
        return '<div style="display:flex;align-items:baseline;gap:2%;font-size:clamp(0.36rem,0.8vw,0.52rem);white-space:nowrap;overflow:hidden;margin-bottom:1.5%;">'
            + '<span style="color:'+labelColor+';font-weight:700;min-width:28%;letter-spacing:0.04em;">'+f.label+'</span>'
            + '<span style="color:'+valueColor+';opacity:0.8;">: '+f.val+'</span>'
            + '</div>';
    }).join('');
}

// =============================================================================
//  Card renderers — matching reference images exactly
// =============================================================================

// ── STYLE 1: Angled Pro ── coloured angled header, centred circle photo ──
function renderClassic(v) {
    var barcode = barcodeStr(v.pri, '48%');
    return '<div style="width:100%;height:100%;background:#f7f8fc;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        // Angled coloured header
        + '<div style="position:absolute;top:0;left:0;right:0;height:100%;overflow:hidden;pointer-events:none;">'
        +   '<div style="position:absolute;top:0;left:0;right:0;bottom:0;background:linear-gradient(135deg,'+v.pri+' 0%,'+v.acc+' 100%);clip-path:polygon(0 0,100% 0,100% 56%,0 72%);"></div>'
        +   '<div style="position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,0.08);clip-path:polygon(0 70%,100% 54%,100% 59%,0 75%);"></div>'
        + '</div>'
        // Logo + company name top-left
        + '<div style="position:absolute;top:5%;left:5%;display:flex;align-items:center;gap:6%;">'
        +   '<div style="width:8%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.4);display:flex;align-items:center;justify-content:center;">'
        +     '<i class="fas fa-infinity" style="color:rgba(255,255,255,0.9);font-size:clamp(0.28rem,0.65vw,0.42rem);"></i>'
        +   '</div>'
        +   '<span style="font-size:clamp(0.36rem,0.82vw,0.52rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.06em;text-transform:uppercase;">'+v.tplName+'</span>'
        + '</div>'
        // Infinity badge top-right
        + '<div style="position:absolute;top:4%;right:4%;width:7%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.18);border:1px solid rgba(255,255,255,0.4);display:flex;align-items:center;justify-content:center;">'
        +   '<i class="fas fa-infinity" style="color:white;font-size:clamp(0.28rem,0.62vw,0.4rem);"></i>'
        + '</div>'
        // Centred circular photo overlapping header/body boundary
        + '<div style="position:absolute;left:50%;top:32%;transform:translateX(-50%);width:22%;aspect-ratio:1;border-radius:50%;border:3px solid #fff;background:'+v.pri+'22;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 24px rgba(0,0,0,0.22);">'
        +   v.photoHTML
        + '</div>'
        // Name + role centred below photo
        + '<div style="position:absolute;top:58%;left:0;right:0;text-align:center;padding:0 4%;">'
        +   '<div style="font-size:clamp(0.62rem,1.55vw,0.9rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;letter-spacing:0.02em;">'+v.nameVal+'</div>'
        +   '<div style="font-size:clamp(0.38rem,0.88vw,0.56rem);color:#888;margin-top:1%;letter-spacing:0.03em;">'+v.roleVal+'</div>'
        + '</div>'
        // Fields — two-column grid
        + '<div style="position:absolute;top:70%;left:5%;right:5%;display:grid;grid-template-columns:1fr 1fr;column-gap:4%;">'
        +   fieldRowsHTML(v.fieldItems, v.pri, '#444')
        + '</div>'
        // Barcode
        + '<div style="position:absolute;bottom:2.5%;left:50%;transform:translateX(-50%);">'+barcode+'</div>'
        + '</div>';
}

// ── STYLE 2: Dark Geo ── dark bg, rotated diamond accent, photo inside ──
function renderSidebar(v) {
    var barcode = barcodeStr('rgba(255,255,255,0.35)', '100%');
    return '<div style="width:100%;height:100%;background:#111827;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        // Large rotated diamond SVG upper-right
        + '<svg style="position:absolute;top:-18%;right:-12%;width:60%;aspect-ratio:1;" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">'
        +   '<rect x="15" y="15" width="70" height="70" rx="3" fill="'+v.pri+'" transform="rotate(45 50 50)"/>'
        + '</svg>'
        // Smaller accent diamond
        + '<svg style="position:absolute;top:-8%;right:-5%;width:42%;aspect-ratio:1;opacity:0.35;" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">'
        +   '<rect x="15" y="15" width="70" height="70" rx="3" fill="'+v.acc+'" transform="rotate(45 50 50)"/>'
        + '</svg>'
        // Photo circle in diamond area
        + '<div style="position:absolute;top:6%;right:6%;width:22%;aspect-ratio:1;border-radius:50%;border:2.5px solid rgba(255,255,255,0.7);background:rgba(255,255,255,0.1);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 18px rgba(0,0,0,0.4);">'
        +   v.photoHTML
        + '</div>'
        // Logo + company name top-left white
        + '<div style="position:absolute;top:5%;left:5%;display:flex;align-items:center;gap:6%;">'
        +   '<div style="width:8%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);display:flex;align-items:center;justify-content:center;">'
        +     '<i class="fas fa-infinity" style="color:rgba(255,255,255,0.7);font-size:clamp(0.28rem,0.62vw,0.4rem);"></i>'
        +   '</div>'
        +   '<span style="font-size:clamp(0.34rem,0.78vw,0.5rem);color:rgba(255,255,255,0.8);font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">'+v.tplName+'</span>'
        + '</div>'
        // NAME large white
        + '<div style="position:absolute;bottom:36%;left:5%;">'
        +   '<div style="font-size:clamp(0.7rem,1.7vw,1rem);font-weight:800;color:#fff;letter-spacing:0.03em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:55%;">'+v.nameVal+'</div>'
        +   '<div style="font-size:clamp(0.38rem,0.85vw,0.54rem);color:'+v.acc+';margin-top:1.5%;letter-spacing:0.04em;">'+v.roleVal+'</div>'
        + '</div>'
        // Fields bottom-left
        + '<div style="position:absolute;bottom:4%;left:5%;right:50%;">'
        +   fieldRowsHTML(v.fieldItems, 'rgba(255,255,255,0.55)', 'rgba(255,255,255,0.88)')
        + '</div>'
        // Barcode bottom-right
        + '<div style="position:absolute;bottom:4%;right:4%;width:36%;">'+barcode+'</div>'
        + '</div>';
}

// ── STYLE 3: Wave Panel ── cream, organic wave left, photo boundary, fields right ──
function renderWave(v) {
    var barcode = barcodeStr('rgba(255,255,255,0.45)', '100%');
    return '<div style="width:100%;height:100%;background:#fdf8f3;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        // Organic wave blob
        + '<svg style="position:absolute;top:0;left:0;width:44%;height:100%;" viewBox="0 0 88 160" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">'
        +   '<path d="M0,0 L60,0 Q80,25 70,55 Q85,80 72,110 Q88,135 65,160 L0,160 Z" fill="'+v.pri+'"/>'
        +   '<path d="M0,0 L45,0 Q62,22 55,52 Q68,78 56,108 Q70,132 50,160 L0,160 Z" fill="rgba(255,255,255,0.07)"/>'
        +   '<path d="M0,30 Q30,45 28,80 Q30,115 0,130" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="2"/>'
        + '</svg>'
        // Photo at wave boundary
        + '<div style="position:absolute;left:24%;top:18%;transform:translateX(-50%);width:24%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.8);background:rgba(255,255,255,0.18);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 22px rgba(0,0,0,0.25);">'
        +   v.photoHTML
        + '</div>'
        // NAME + role bottom-left
        + '<div style="position:absolute;bottom:12%;left:5%;max-width:40%;">'
        +   '<div style="font-size:clamp(0.6rem,1.4vw,0.82rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;letter-spacing:0.03em;">'+v.nameVal+'</div>'
        +   '<div style="font-size:clamp(0.35rem,0.78vw,0.5rem);color:rgba(255,255,255,0.75);margin-top:1.5%;letter-spacing:0.04em;">'+v.roleVal+'</div>'
        + '</div>'
        // Barcode bottom-left
        + '<div style="position:absolute;bottom:3%;left:5%;width:36%;">'+barcode+'</div>'
        // Logo top-right
        + '<div style="position:absolute;top:5%;right:5%;display:flex;align-items:center;gap:5%;">'
        +   '<div style="width:7%;aspect-ratio:1;border-radius:50%;background:'+v.pri+'22;border:1px solid '+v.pri+'44;display:flex;align-items:center;justify-content:center;">'
        +     '<i class="fas fa-infinity" style="color:'+v.pri+';font-size:0.3rem;"></i>'
        +   '</div>'
        +   '<span style="font-size:clamp(0.32rem,0.72vw,0.46rem);color:'+v.pri+';font-weight:700;letter-spacing:0.06em;text-transform:uppercase;">'+v.tplName+'</span>'
        + '</div>'
        // Fields right side
        + '<div style="position:absolute;top:14%;right:4%;width:48%;">'
        +   fieldRowsHTML(v.fieldItems, v.pri, '#4a3728')
        + '</div>'
        + '</div>';
}

// ── STYLE 4: Bold Split ── coloured left panel, white right panel ──
function renderBoldHeader(v) {
    var barcode = barcodeStr('rgba(255,255,255,0.4)', '100%');
    return '<div style="width:100%;height:100%;display:flex;overflow:hidden;font-family:\''+v.font+'\',sans-serif;">'
        // Coloured left panel
        + '<div style="width:40%;background:linear-gradient(170deg,'+v.pri+' 0%,'+v.acc+' 100%);display:flex;flex-direction:column;align-items:center;position:relative;overflow:hidden;flex-shrink:0;">'
        +   '<div style="position:absolute;top:-20%;left:-30%;width:90%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.07);"></div>'
        +   '<div style="position:absolute;bottom:-15%;right:-25%;width:70%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.05);"></div>'
        // Logo top of panel
        +   '<div style="padding:10% 0 5%;position:relative;z-index:1;display:flex;flex-direction:column;align-items:center;gap:8%;">'
        +     '<div style="width:22%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1.5px solid rgba(255,255,255,0.5);display:flex;align-items:center;justify-content:center;">'
        +       '<i class="fas fa-infinity" style="color:white;font-size:clamp(0.28rem,0.65vw,0.42rem);"></i>'
        +     '</div>'
        +     '<span style="font-size:clamp(0.3rem,0.7vw,0.44rem);color:rgba(255,255,255,0.7);font-weight:600;letter-spacing:0.08em;text-transform:uppercase;text-align:center;">'+v.tplName+'</span>'
        +   '</div>'
        // Photo circle centred
        +   '<div style="width:45%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.8);background:rgba(255,255,255,0.15);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 20px rgba(0,0,0,0.25);position:relative;z-index:1;margin-top:4%;">'
        +     v.photoHTML
        +   '</div>'
        // Barcode at bottom of panel
        +   '<div style="margin-top:auto;padding-bottom:6%;width:80%;position:relative;z-index:1;">'+barcode+'</div>'
        + '</div>'
        // White right panel
        + '<div style="flex:1;background:#ffffff;display:flex;flex-direction:column;justify-content:center;padding:6% 7%;min-width:0;position:relative;">'
        +   '<div style="position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,'+v.pri+','+v.acc+');"></div>'
        +   '<div style="font-size:clamp(0.62rem,1.52vw,0.88rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;letter-spacing:0.02em;">'+v.nameVal+'</div>'
        +   '<div style="font-size:clamp(0.36rem,0.82vw,0.52rem);color:#888;margin-top:1.5%;letter-spacing:0.04em;margin-bottom:4%;">'+v.roleVal+'</div>'
        +   '<div style="width:60%;height:2px;background:linear-gradient(90deg,'+v.acc+',transparent);border-radius:2px;margin-bottom:5%;"></div>'
        +   fieldRowsHTML(v.fieldItems, v.pri, '#555')
        + '</div>'
        + '</div>';
}

// ── STYLE 5: Triangle Pro ── dark bg, arrow triangles right, photo+text left ──
function renderDiagonal(v) {
    var barcode = barcodeStr('rgba(255,255,255,0.32)', '100%');
    return '<div style="width:100%;height:100%;background:#111827;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        // Arrow/chevron triangles right side
        + '<svg style="position:absolute;right:0;top:0;width:48%;height:100%;" viewBox="0 0 96 160" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">'
        +   '<rect x="40" y="0" width="56" height="160" fill="'+v.pri+'18"/>'
        +   '<polygon points="96,0 96,62 42,31" fill="'+v.pri+'"/>'
        +   '<polygon points="96,52 96,112 48,82" fill="'+v.acc+'" opacity="0.85"/>'
        +   '<polygon points="96,100 96,160 44,130" fill="'+v.pri+'" opacity="0.7"/>'
        +   '<line x1="96" y1="62" x2="96" y2="52" stroke="rgba(255,255,255,0.2)" stroke-width="1"/>'
        +   '<line x1="96" y1="112" x2="96" y2="100" stroke="rgba(255,255,255,0.2)" stroke-width="1"/>'
        + '</svg>'
        // Photo circle — left area, vertically centred
        + '<div style="position:absolute;left:5%;top:50%;transform:translateY(-50%);width:22%;aspect-ratio:1;border-radius:50%;border:2.5px solid '+v.acc+';background:rgba(255,255,255,0.08);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 18px rgba(0,0,0,0.4);">'
        +   v.photoHTML
        + '</div>'
        // Logo top-left
        + '<div style="position:absolute;top:5%;left:5%;display:flex;align-items:center;gap:6%;">'
        +   '<div style="width:8%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.25);display:flex;align-items:center;justify-content:center;">'
        +     '<i class="fas fa-infinity" style="color:rgba(255,255,255,0.6);font-size:clamp(0.28rem,0.62vw,0.4rem);"></i>'
        +   '</div>'
        +   '<span style="font-size:clamp(0.34rem,0.76vw,0.48rem);color:rgba(255,255,255,0.65);font-weight:700;letter-spacing:0.08em;text-transform:uppercase;">'+v.tplName+'</span>'
        + '</div>'
        // Name + role — right of photo
        + '<div style="position:absolute;left:32%;top:20%;max-width:30%;">'
        +   '<div style="font-size:clamp(0.58rem,1.38vw,0.82rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +   '<div style="font-size:clamp(0.34rem,0.76vw,0.5rem);color:'+v.acc+';margin-top:2%;letter-spacing:0.04em;">'+v.roleVal+'</div>'
        + '</div>'
        // Fields — lower-left
        + '<div style="position:absolute;bottom:6%;left:5%;max-width:50%;">'
        +   fieldRowsHTML(v.fieldItems, 'rgba(255,255,255,0.55)', 'rgba(255,255,255,0.9)')
        + '</div>'
        // Barcode bottom-right
        + '<div style="position:absolute;bottom:4%;right:4%;width:36%;">'+barcode+'</div>'
        + '</div>';
}

// =============================================================================
//  Main preview updater
// =============================================================================
function updatePreview() {
    var v       = getCardValues();
    updateStyleThumbnails();
    var preview = document.getElementById('cardPreview');
    preview.style.fontFamily = "'" + v.font + "',sans-serif";
    var html = '';
    switch (currentStyle) {
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
    (tpl.fields || []).forEach(function(f) {
        var el = document.getElementById('field_' + f);
        if (el) cardData[f] = el.value;
    });

    var formData = new FormData();
    formData.append('_token', CSRF_TOKEN);
    formData.append('template_key', currentTpl);
    formData.append('prompt', prompt);
    Object.keys(cardData).forEach(function(k) { formData.append('card_data[' + k + ']', cardData[k]); });

    fetch('/projects/idcard/ai-suggest', { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:formData })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        if (!data.success) { out.innerHTML = '<div class="ai-suggestion">Could not get suggestions. Try again.</div>'; return; }
        var s = data.suggestions || {};
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
    var btn = document.getElementById('generateBtn');
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
