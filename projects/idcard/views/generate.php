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
    { key:'classic',     label:'Classic',       desc:'Corporate with hexagon mesh pattern' },
    { key:'sidebar',     label:'Tech',          desc:'Dark sidebar with circuit-board traces' },
    { key:'wave',        label:'Aurora',        desc:'Gradient aurora with layered waves' },
    { key:'bold_header', label:'Geometric',     desc:'Low-poly triangle header pattern' },
    { key:'diagonal',    label:'Mosaic',        desc:'Diagonal split with halftone dots' }
];

// =============================================================================
//  SVG thumbnail builder – richer previews matching real card graphics
// =============================================================================
function buildStyleThumbnail(key, pri, acc, bg, txt) {
    var W = 85.6, H = 54;
    switch(key) {
        case 'classic':
            // Hex mesh + gradient header + diagonal stripes
            return '<svg viewBox="0 0 ' + W + ' ' + H + '" xmlns="http://www.w3.org/2000/svg">' +
                '<defs>' +
                '<linearGradient id="thg1" x1="0" y1="0" x2="1" y2="0"><stop offset="0%" stop-color="' + pri + '"/><stop offset="100%" stop-color="' + acc + '"/></linearGradient>' +
                '<pattern id="thx1" patternUnits="userSpaceOnUse" width="8" height="13.9">' +
                '<polygon points="4,0.3 7.5,2.3 7.5,6.3 4,8.3 0.5,6.3 0.5,2.3" fill="none" stroke="' + pri + '" stroke-width="0.35" opacity="0.22"/>' +
                '</pattern>' +
                '</defs>' +
                '<rect width="' + W + '" height="' + H + '" fill="' + bg + '"/>' +
                '<rect width="' + W + '" height="' + H + '" fill="url(#thx1)"/>' +
                '<rect y="0" width="' + W + '" height="6" fill="url(#thg1)"/>' +
                '<path d="M0,0 L8,0 L0,6 Z" fill="rgba(255,255,255,0.18)"/>' +
                '<path d="M16,0 L24,0 L16,6 Z" fill="rgba(255,255,255,0.18)"/>' +
                '<path d="M32,0 L40,0 L32,6 Z" fill="rgba(255,255,255,0.18)"/>' +
                '<path d="M48,0 L56,0 L48,6 Z" fill="rgba(255,255,255,0.18)"/>' +
                '<path d="M64,0 L72,0 L64,6 Z" fill="rgba(255,255,255,0.18)"/>' +
                '<rect y="' + (H-4) + '" width="' + W + '" height="4" fill="url(#thg1)" opacity="0.3"/>' +
                '<circle cx="20" cy="' + (H/2+2) + '" r="8" fill="' + pri + '" opacity="0.08"/>' +
                '<circle cx="20" cy="' + (H/2+2) + '" r="8" fill="none" stroke="url(#thg1)" stroke-width="1.5"/>' +
                '<rect x="33" y="20" width="30" height="3" rx="1.2" fill="' + txt + '" opacity="0.8"/>' +
                '<rect x="33" y="25" width="3" height="3" rx="1" fill="url(#thg1)" opacity="0.9"/>' +
                '<rect x="33" y="26.5" width="22" height="1.5" rx="0.6" fill="' + acc + '" opacity="0.7"/>' +
                '<rect x="33" y="31" width="24" height="1.5" rx="0.5" fill="' + txt + '" opacity="0.3"/>' +
                '<rect x="33" y="35" width="18" height="1.5" rx="0.5" fill="' + txt + '" opacity="0.3"/>' +
                '<rect x="33" y="39" width="14" height="1.5" rx="0.5" fill="' + txt + '" opacity="0.3"/>' +
                '</svg>';
        case 'sidebar':
            // Circuit-board sidebar
            return '<svg viewBox="0 0 ' + W + ' ' + H + '" xmlns="http://www.w3.org/2000/svg">' +
                '<rect width="' + W + '" height="' + H + '" fill="' + bg + '"/>' +
                '<rect width="22" height="' + H + '" fill="' + pri + '"/>' +
                // Circuit traces
                '<line x1="3" y1="8" x2="18" y2="8" stroke="rgba(255,255,255,0.35)" stroke-width="0.7"/>' +
                '<line x1="10" y1="8" x2="10" y2="20" stroke="rgba(255,255,255,0.35)" stroke-width="0.7"/>' +
                '<line x1="3" y1="28" x2="19" y2="28" stroke="rgba(255,255,255,0.35)" stroke-width="0.7"/>' +
                '<line x1="16" y1="28" x2="16" y2="38" stroke="rgba(255,255,255,0.35)" stroke-width="0.7"/>' +
                '<line x1="3" y1="44" x2="19" y2="44" stroke="rgba(255,255,255,0.35)" stroke-width="0.7"/>' +
                '<circle cx="10" cy="8" r="1.3" fill="rgba(255,255,255,0.7)"/>' +
                '<circle cx="16" cy="28" r="1.3" fill="rgba(255,255,255,0.7)"/>' +
                '<circle cx="6" cy="44" r="1.3" fill="rgba(255,255,255,0.7)"/>' +
                // Photo square
                '<rect x="5" y="14" width="12" height="12" rx="2" fill="rgba(255,255,255,0.22)" stroke="rgba(255,255,255,0.45)" stroke-width="0.7"/>' +
                // Right content
                '<rect x="28" y="13" width="3" height="28" rx="1.5" fill="' + acc + '" opacity="0.8"/>' +
                '<rect x="35" y="13" width="36" height="3.5" rx="1.2" fill="' + txt + '" opacity="0.8"/>' +
                '<rect x="35" y="20" width="24" height="2.5" rx="1" fill="' + acc + '" opacity="0.7"/>' +
                '<rect x="35" y="27" width="32" height="1.8" rx="0.6" fill="' + txt + '" opacity="0.3"/>' +
                '<rect x="35" y="32" width="26" height="1.8" rx="0.6" fill="' + txt + '" opacity="0.3"/>' +
                '<rect x="35" y="37" width="18" height="1.8" rx="0.6" fill="' + txt + '" opacity="0.3"/>' +
                '</svg>';
        case 'wave':
            // Aurora gradient + multi-waves
            return '<svg viewBox="0 0 ' + W + ' ' + H + '" xmlns="http://www.w3.org/2000/svg">' +
                '<defs>' +
                '<linearGradient id="tag" x1="0" y1="0" x2="0.7" y2="1"><stop offset="0%" stop-color="' + pri + '"/><stop offset="60%" stop-color="' + acc + '"/><stop offset="100%" stop-color="' + pri + '" stop-opacity="0.7"/></linearGradient>' +
                '</defs>' +
                '<rect width="' + W + '" height="' + H + '" fill="url(#tag)"/>' +
                // Circles top-right
                '<circle cx="' + (W-4) + '" cy="6" r="14" fill="rgba(255,255,255,0.07)"/>' +
                '<circle cx="' + (W-10) + '" cy="2" r="8" fill="rgba(255,255,255,0.05)"/>' +
                // 5 wave paths
                '<path d="M0,' + (H*0.6) + ' Q' + (W*0.25) + ',' + (H*0.4) + ' ' + (W*0.5) + ',' + (H*0.58) + ' Q' + (W*0.75) + ',' + (H*0.76) + ' ' + W + ',' + (H*0.55) + ' L' + W + ',' + H + ' L0,' + H + ' Z" fill="rgba(255,255,255,0.05)"/>' +
                '<path d="M0,' + (H*0.68) + ' Q' + (W*0.3) + ',' + (H*0.52) + ' ' + (W*0.6) + ',' + (H*0.65) + ' Q' + (W*0.8) + ',' + (H*0.75) + ' ' + W + ',' + (H*0.62) + ' L' + W + ',' + H + ' L0,' + H + ' Z" fill="rgba(255,255,255,0.07)"/>' +
                '<path d="M0,' + (H*0.76) + ' Q' + (W*0.35) + ',' + (H*0.63) + ' ' + (W*0.65) + ',' + (H*0.74) + ' Q' + (W*0.85) + ',' + (H*0.82) + ' ' + W + ',' + (H*0.7) + ' L' + W + ',' + H + ' L0,' + H + ' Z" fill="rgba(255,255,255,0.1)"/>' +
                '<path d="M0,' + (H*0.84) + ' Q' + (W*0.4) + ',' + (H*0.76) + ' ' + (W*0.7) + ',' + (H*0.82) + ' Q' + (W*0.88) + ',' + (H*0.87) + ' ' + W + ',' + (H*0.78) + ' L' + W + ',' + H + ' L0,' + H + ' Z" fill="rgba(255,255,255,0.12)"/>' +
                '<path d="M0,' + (H*0.91) + ' Q' + (W*0.45) + ',' + (H*0.86) + ' ' + (W*0.75) + ',' + (H*0.9) + ' Q' + (W*0.9) + ',' + (H*0.93) + ' ' + W + ',' + (H*0.87) + ' L' + W + ',' + H + ' L0,' + H + ' Z" fill="rgba(255,255,255,0.15)"/>' +
                // Photo circle with double ring
                '<circle cx="19" cy="22" r="10" fill="rgba(255,255,255,0.25)"/>' +
                '<circle cx="19" cy="22" r="10" fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="1"/>' +
                '<circle cx="19" cy="22" r="12" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="0.8"/>' +
                '<rect x="33" y="14" width="34" height="3.5" rx="1.5" fill="rgba(255,255,255,0.92)"/>' +
                '<rect x="33" y="21" width="22" height="2.5" rx="1" fill="rgba(255,255,255,0.7)"/>' +
                '<rect x="33" y="28" width="28" height="1.8" rx="0.6" fill="rgba(255,255,255,0.4)"/>' +
                '<rect x="33" y="33" width="20" height="1.8" rx="0.6" fill="rgba(255,255,255,0.4)"/>' +
                '</svg>';
        case 'bold_header':
            // Triangle tessellation header
            return '<svg viewBox="0 0 ' + W + ' ' + H + '" xmlns="http://www.w3.org/2000/svg">' +
                '<rect width="' + W + '" height="' + H + '" fill="' + bg + '"/>' +
                '<rect width="' + W + '" height="22" fill="' + pri + '"/>' +
                // Triangle tessellation in header
                '<polygon points="50,0 62,0 56,11" fill="rgba(255,255,255,0.08)"/>' +
                '<polygon points="62,0 74,0 68,11" fill="rgba(255,255,255,0.12)"/>' +
                '<polygon points="74,0 86,0 80,11" fill="rgba(255,255,255,0.07)"/>' +
                '<polygon points="56,11 68,11 62,22" fill="rgba(255,255,255,0.1)"/>' +
                '<polygon points="68,11 80,11 74,22" fill="rgba(255,255,255,0.06)"/>' +
                '<polygon points="62,11 74,11 68,0" fill="rgba(255,255,255,0.14)"/>' +
                '<polygon points="70,22 84,22 77,11" fill="rgba(255,255,255,0.09)"/>' +
                // Name in header
                '<rect x="24" y="7" width="38" height="3.5" rx="1.5" fill="rgba(255,255,255,0.92)"/>' +
                '<rect x="24" y="14" width="26" height="2.5" rx="1" fill="rgba(255,255,255,0.65)"/>' +
                // Photo overlapping
                '<circle cx="12" cy="22" r="10" fill="' + bg + '" stroke="' + acc + '" stroke-width="1.5"/>' +
                '<circle cx="12" cy="22" r="7" fill="' + pri + '" opacity="0.2"/>' +
                // Fields
                '<rect x="24" y="27" width="42" height="2.5" rx="1" fill="' + txt + '" opacity="0.7"/>' +
                '<rect x="24" y="33" width="34" height="2" rx="1" fill="' + txt + '" opacity="0.3"/>' +
                '<rect x="24" y="38" width="26" height="2" rx="1" fill="' + txt + '" opacity="0.3"/>' +
                '<rect x="24" y="43" width="18" height="2" rx="1" fill="' + txt + '" opacity="0.3"/>' +
                '</svg>';
        case 'diagonal':
            // Halftone dots on diagonal zone
            return '<svg viewBox="0 0 ' + W + ' ' + H + '" xmlns="http://www.w3.org/2000/svg">' +
                '<defs><linearGradient id="tdg" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="' + pri + '"/><stop offset="100%" stop-color="' + acc + '"/></linearGradient></defs>' +
                '<rect width="' + W + '" height="' + H + '" fill="' + bg + '"/>' +
                '<polygon points="0,0 48,0 32,' + H + ' 0,' + H + '" fill="url(#tdg)"/>' +
                // Halftone dots in colored zone
                '<circle cx="5" cy="5" r="1.5" fill="rgba(255,255,255,0.25)"/>' +
                '<circle cx="14" cy="5" r="1.5" fill="rgba(255,255,255,0.25)"/>' +
                '<circle cx="23" cy="5" r="1.5" fill="rgba(255,255,255,0.25)"/>' +
                '<circle cx="9" cy="14" r="1.5" fill="rgba(255,255,255,0.25)"/>' +
                '<circle cx="18" cy="14" r="1.5" fill="rgba(255,255,255,0.25)"/>' +
                '<circle cx="5" cy="23" r="1.5" fill="rgba(255,255,255,0.25)"/>' +
                '<circle cx="14" cy="23" r="1.5" fill="rgba(255,255,255,0.25)"/>' +
                '<circle cx="9" cy="32" r="1.5" fill="rgba(255,255,255,0.25)"/>' +
                '<circle cx="5" cy="41" r="1.5" fill="rgba(255,255,255,0.25)"/>' +
                '<circle cx="14" cy="41" r="1.5" fill="rgba(255,255,255,0.25)"/>' +
                // Photo (square-ish)
                '<rect x="5" y="16" width="22" height="22" rx="3" fill="rgba(255,255,255,0.25)" stroke="rgba(255,255,255,0.5)" stroke-width="0.8"/>' +
                // Right content
                '<rect x="56" y="14" width="26" height="3.5" rx="1.5" fill="' + txt + '" opacity="0.8"/>' +
                '<rect x="56" y="21" width="18" height="2.5" rx="1" fill="' + acc + '" opacity="0.7"/>' +
                '<rect x="56" y="28" width="1.5" height="14" rx="0.75" fill="' + acc + '" opacity="0.6"/>' +
                '<rect x="60" y="29" width="20" height="1.8" rx="0.6" fill="' + txt + '" opacity="0.3"/>' +
                '<rect x="60" y="34" width="16" height="1.8" rx="0.6" fill="' + txt + '" opacity="0.3"/>' +
                '<rect x="60" y="39" width="12" height="1.8" rx="0.6" fill="' + txt + '" opacity="0.3"/>' +
                '</svg>';
        default:
            return '<svg viewBox="0 0 ' + W + ' ' + H + '"><rect width="' + W + '" height="' + H + '" fill="' + pri + '"/></svg>';
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
    var tpl  = TEMPLATES[key];
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
function getCardValues() {
    var tpl  = TEMPLATES[currentTpl] || {};
    var pri  = document.getElementById('primaryColor').value || tpl.color  || '#1e40af';
    var acc  = document.getElementById('accentColor').value  || tpl.accent || '#3b82f6';
    var bg   = document.getElementById('bgColor').value      || tpl.bg     || '#ffffff';
    var txt  = document.getElementById('textColor').value    || tpl.text   || '#1e293b';
    var font = document.getElementById('fontFamily').value   || 'Poppins';

    var roleKeys = ['designation','title','course','event_name'];
    var nameEl   = document.getElementById('field_name');
    var nameVal  = (nameEl && nameEl.value) ? nameEl.value : 'Full Name';
    var roleVal  = '';
    for (var i = 0; i < roleKeys.length; i++) {
        var el = document.getElementById('field_' + roleKeys[i]);
        if (el && el.value) { roleVal = el.value; break; }
    }
    roleVal = roleVal || 'Designation / Role';

    var icons = {department:'building', employee_id:'hashtag', roll_number:'hashtag',
                 phone:'phone', email:'envelope', blood_group:'tint', badge_id:'hashtag',
                 host_name:'user', purpose:'clipboard', visit_date:'calendar',
                 license_no:'certificate', organization:'building', id_number:'hashtag', year:'graduation-cap'};

    var fieldKeys  = (tpl.fields || []).filter(function(f){ return f !== 'photo' && f !== 'name' && !roleKeys.includes(f); });
    var fieldItems = fieldKeys.slice(0, 3).map(function(f) {
        var el  = document.getElementById('field_' + f);
        var val = el ? (el.value || (FIELD_LABELS[f] || f)) : (FIELD_LABELS[f] || f);
        return { key:f, val:val, icon: icons[f] || 'info-circle' };
    });

    var photoHTML = photoDataUrl
        ? '<img src="' + photoDataUrl + '" style="width:100%;height:100%;object-fit:cover;">'
        : '<i class="fas fa-user" style="font-size:1.6rem;opacity:0.65;"></i>';

    return { pri:pri, acc:acc, bg:bg, txt:txt, font:font, nameVal:nameVal, roleVal:roleVal, fieldItems:fieldItems, photoHTML:photoHTML };
}

function fieldListHTML(items, color) {
    return items.map(function(f) {
        return '<div style="display:flex;align-items:center;gap:4%;font-size:clamp(0.44rem,1.05vw,0.64rem);opacity:0.88;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:' + color + ';margin-bottom:1%;">' +
               '<i class="fas fa-' + f.icon + '" style="font-size:0.48rem;opacity:0.6;flex-shrink:0;"></i>' +
               '<span>' + f.val + '</span>' +
               '</div>';
    }).join('');
}

// =============================================================================
//  Card renderers — dramatically upgraded with rich SVG graphic backgrounds
// =============================================================================

// ── Style 1: Classic — hexagon mesh texture + gradient header ───────────────
function renderClassic(v) {
    // Inline SVG hex mesh pattern as card texture
    var hexMesh =
        '<svg style="position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;" xmlns="http://www.w3.org/2000/svg">' +
        '<defs><pattern id="chx" patternUnits="userSpaceOnUse" width="11" height="19.1">' +
        '<polygon points="5.5,0.4 10,3 10,8.6 5.5,11.2 1,8.6 1,3" fill="none" stroke="' + v.pri + '" stroke-width="0.5" opacity="0.18"/>' +
        '</pattern></defs>' +
        '<rect width="100%" height="100%" fill="url(#chx)"/>' +
        '</svg>';
    // Gradient header with diagonal stripe decoration
    var header =
        '<div style="position:absolute;top:0;left:0;right:0;height:10%;background:linear-gradient(90deg,' + v.pri + ' 0%,' + v.acc + ' 100%);">' +
        '<svg style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0.18;" viewBox="0 0 200 20" preserveAspectRatio="none">' +
        '<path d="M0,0 L10,0 L0,20 Z" fill="white"/><path d="M20,0 L30,0 L20,20 Z" fill="white"/>' +
        '<path d="M40,0 L50,0 L40,20 Z" fill="white"/><path d="M60,0 L70,0 L60,20 Z" fill="white"/>' +
        '<path d="M80,0 L90,0 L80,20 Z" fill="white"/><path d="M100,0 L110,0 L100,20 Z" fill="white"/>' +
        '<path d="M120,0 L130,0 L120,20 Z" fill="white"/><path d="M140,0 L150,0 L140,20 Z" fill="white"/>' +
        '<path d="M160,0 L170,0 L160,20 Z" fill="white"/><path d="M180,0 L190,0 L180,20 Z" fill="white"/>' +
        '</svg>' +
        '</div>';
    // Bottom gradient footer bar
    var footer = '<div style="position:absolute;bottom:0;left:0;right:0;height:5%;background:linear-gradient(90deg,' + v.acc + ',' + v.pri + ');opacity:0.35;"></div>';
    // Photo with gradient ring
    var photo =
        '<div style="flex-shrink:0;width:20%;aspect-ratio:1;border-radius:50%;background:linear-gradient(135deg,' + v.pri + ',' + v.acc + ');padding:2.5px;box-shadow:0 3px 12px rgba(0,0,0,0.2);">' +
        '<div style="width:100%;height:100%;border-radius:50%;background:' + v.bg + ';padding:2px;">' +
        '<div style="width:100%;height:100%;border-radius:50%;background:' + v.pri + '18;overflow:hidden;display:flex;align-items:center;justify-content:center;">' +
        v.photoHTML +
        '</div></div></div>';
    // Info block
    var info =
        '<div style="flex:1;min-width:0;">' +
        '<div style="font-size:clamp(0.7rem,1.8vw,1rem);font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:' + v.txt + ';">' + v.nameVal + '</div>' +
        '<div style="width:35%;height:2px;background:linear-gradient(90deg,' + v.acc + ',transparent);border-radius:2px;margin:3% 0;"></div>' +
        '<div style="font-size:clamp(0.5rem,1.2vw,0.7rem);color:' + v.acc + ';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600;margin-bottom:4%;">' + v.roleVal + '</div>' +
        '<div style="display:flex;flex-direction:column;gap:0;">' + fieldListHTML(v.fieldItems, v.txt) + '</div>' +
        '</div>';
    return '<div style="width:100%;height:100%;background:' + v.bg + ';font-family:\'' + v.font + '\',sans-serif;position:relative;overflow:hidden;">' +
        hexMesh + header + footer +
        '<div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;align-items:center;gap:5%;padding:13% 5% 7%;">' +
        photo + info +
        '</div>' +
        '</div>';
}

// ── Style 2: Tech/Sidebar — circuit-board pattern sidebar ───────────────────
function renderSidebar(v) {
    // Circuit board SVG traces on sidebar
    var circuitSVG =
        '<svg style="position:absolute;top:0;left:0;width:100%;height:100%;" viewBox="0 0 80 120" xmlns="http://www.w3.org/2000/svg">' +
        // Horizontal traces
        '<line x1="0" y1="12" x2="35" y2="12" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>' +
        '<line x1="45" y1="12" x2="80" y2="12" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>' +
        '<line x1="0" y1="28" x2="22" y2="28" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>' +
        '<line x1="58" y1="28" x2="80" y2="28" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>' +
        '<line x1="8" y1="55" x2="72" y2="55" stroke="rgba(255,255,255,0.18)" stroke-width="0.8"/>' +
        '<line x1="0" y1="75" x2="48" y2="75" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>' +
        '<line x1="32" y1="90" x2="80" y2="90" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>' +
        '<line x1="15" y1="108" x2="65" y2="108" stroke="rgba(255,255,255,0.18)" stroke-width="0.8"/>' +
        // Vertical traces
        '<line x1="18" y1="0" x2="18" y2="12" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>' +
        '<line x1="18" y1="28" x2="18" y2="55" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>' +
        '<line x1="35" y1="12" x2="35" y2="28" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>' +
        '<line x1="55" y1="0" x2="55" y2="12" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>' +
        '<line x1="55" y1="28" x2="55" y2="55" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>' +
        '<line x1="62" y1="55" x2="62" y2="75" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>' +
        '<line x1="22" y1="75" x2="22" y2="90" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>' +
        '<line x1="48" y1="90" x2="48" y2="108" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>' +
        '<line x1="32" y1="108" x2="32" y2="120" stroke="rgba(255,255,255,0.18)" stroke-width="0.8"/>' +
        // Junction dots
        '<circle cx="18" cy="12" r="2" fill="rgba(255,255,255,0.65)"/>' +
        '<circle cx="35" cy="28" r="2" fill="rgba(255,255,255,0.65)"/>' +
        '<circle cx="55" cy="12" r="2" fill="rgba(255,255,255,0.65)"/>' +
        '<circle cx="55" cy="55" r="2" fill="rgba(255,255,255,0.65)"/>' +
        '<circle cx="62" cy="75" r="2" fill="rgba(255,255,255,0.65)"/>' +
        '<circle cx="22" cy="90" r="2" fill="rgba(255,255,255,0.65)"/>' +
        '<circle cx="48" cy="108" r="2" fill="rgba(255,255,255,0.65)"/>' +
        '<circle cx="18" cy="55" r="1.4" fill="rgba(255,255,255,0.4)"/>' +
        '<circle cx="22" cy="75" r="1.4" fill="rgba(255,255,255,0.4)"/>' +
        '</svg>';
    // Right side dot watermark
    var dotWatermark =
        '<svg style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0.055;" viewBox="0 0 160 100" xmlns="http://www.w3.org/2000/svg">' +
        (function(){var d='';for(var r=0;r<10;r++)for(var c=0;c<16;c++)d+='<circle cx="'+(c*10+5)+'" cy="'+(r*10+5)+'" r="1.2" fill="'+v.txt+'"/>';return d;})() +
        '</svg>';
    return '<div style="width:100%;height:100%;display:flex;font-family:\'' + v.font + '\',sans-serif;overflow:hidden;position:relative;">' +
        // Sidebar
        '<div style="width:29%;background:' + v.pri + ';display:flex;flex-direction:column;align-items:center;justify-content:center;gap:5%;padding:5% 0;flex-shrink:0;position:relative;overflow:hidden;">' +
        circuitSVG +
        // Square rounded-corner photo
        '<div style="width:55%;aspect-ratio:1;border-radius:10%;border:2px solid rgba(255,255,255,0.6);background:rgba(255,255,255,0.15);overflow:hidden;display:flex;align-items:center;justify-content:center;position:relative;z-index:1;box-shadow:0 4px 16px rgba(0,0,0,0.2);">' +
        v.photoHTML + '</div>' +
        '<div style="font-size:clamp(0.36rem,0.8vw,0.52rem);color:rgba(255,255,255,0.5);letter-spacing:0.1em;text-transform:uppercase;position:relative;z-index:1;text-align:center;padding:0 8%;">ID Card</div>' +
        '</div>' +
        // Right content
        '<div style="flex:1;background:' + v.bg + ';position:relative;overflow:hidden;">' +
        dotWatermark +
        '<div style="position:absolute;top:0;bottom:0;left:0;width:3px;background:linear-gradient(180deg,' + v.pri + ',' + v.acc + ');"></div>' +
        '<div style="position:absolute;top:0;left:0;right:0;bottom:0;padding:5% 6% 5% 9%;display:flex;flex-direction:column;justify-content:center;color:' + v.txt + ';min-width:0;">' +
        '<div style="font-size:clamp(0.7rem,1.7vw,0.97rem);font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + v.nameVal + '</div>' +
        '<div style="font-size:clamp(0.48rem,1.1vw,0.7rem);color:' + v.acc + ';margin-top:2%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600;">' + v.roleVal + '</div>' +
        '<div style="width:40%;height:1.5px;background:linear-gradient(90deg,' + v.acc + ',transparent);border-radius:2px;margin:4% 0;"></div>' +
        '<div style="display:flex;flex-direction:column;">' + fieldListHTML(v.fieldItems, v.txt) + '</div>' +
        '</div></div>' +
        '</div>';
}

// ── Style 3: Aurora Wave — gradient + 5 layered waves + double-ring photo ──
function renderWave(v) {
    var waves =
        '<svg style="position:absolute;bottom:0;left:0;width:100%;height:68%;" viewBox="0 0 400 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">' +
        '<path d="M0,80 Q50,50 100,72 Q150,94 200,62 Q250,30 300,58 Q350,86 400,55 L400,120 L0,120 Z" fill="rgba(255,255,255,0.04)"/>' +
        '<path d="M0,90 Q60,65 120,85 Q180,105 240,78 Q300,51 360,74 Q380,82 400,65 L400,120 L0,120 Z" fill="rgba(255,255,255,0.06)"/>' +
        '<path d="M0,100 Q70,82 140,96 Q210,110 280,88 Q340,70 400,84 L400,120 L0,120 Z" fill="rgba(255,255,255,0.08)"/>' +
        '<path d="M0,108 Q80,98 160,105 Q240,112 320,100 Q360,94 400,102 L400,120 L0,120 Z" fill="rgba(255,255,255,0.1)"/>' +
        '<path d="M0,114 Q90,108 180,112 Q270,116 360,108 L400,112 L400,120 L0,120 Z" fill="rgba(255,255,255,0.13)"/>' +
        '</svg>';
    // Large background circles (top-right decoration)
    var bgCircles =
        '<div style="position:absolute;top:-22%;right:-12%;width:50%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.07);"></div>' +
        '<div style="position:absolute;top:2%;right:8%;width:20%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.05);"></div>' +
        '<div style="position:absolute;top:20%;right:-5%;width:14%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.04);"></div>';
    // Photo with double ring
    var photo =
        '<div style="flex-shrink:0;position:relative;">' +
        '<div style="width:21%;aspect-ratio:1;border-radius:50%;border:2.5px solid rgba(255,255,255,0.7);background:rgba(255,255,255,0.2);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 0 0 4px rgba(255,255,255,0.15);">' +
        v.photoHTML + '</div></div>';
    return '<div style="width:100%;height:100%;background:linear-gradient(140deg,' + v.pri + ' 0%,' + v.acc + ' 60%,' + v.pri + 'cc 100%);font-family:\'' + v.font + '\',sans-serif;position:relative;overflow:hidden;">' +
        waves + bgCircles +
        '<div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;align-items:center;gap:5%;padding:5% 6%;color:rgba(255,255,255,0.96);">' +
        photo +
        '<div style="flex:1;min-width:0;">' +
        '<div style="font-size:clamp(0.7rem,1.8vw,1rem);font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + v.nameVal + '</div>' +
        '<div style="font-size:clamp(0.5rem,1.2vw,0.72rem);opacity:0.85;margin-top:2%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:500;">' + v.roleVal + '</div>' +
        '<div style="width:40%;height:1.5px;background:rgba(255,255,255,0.4);border-radius:2px;margin:4% 0;"></div>' +
        '<div style="display:flex;flex-direction:column;">' + fieldListHTML(v.fieldItems, 'rgba(255,255,255,0.92)') + '</div>' +
        '</div>' +
        '</div>' +
        '<div style="position:absolute;bottom:3%;right:4%;font-size:clamp(0.36rem,0.75vw,0.5rem);font-family:monospace;opacity:0.4;color:white;letter-spacing:0.05em;">CardX</div>' +
        '</div>';
}

// ── Style 4: Geometric — low-poly triangle tessellation in header ────────────
function renderBoldHeader(v) {
    // Triangle tessellation (low-poly art) filling the header
    var trianglePoly =
        '<svg style="position:absolute;right:0;top:0;width:70%;height:135%;opacity:0.13;" viewBox="0 0 200 120" preserveAspectRatio="xMaxYMid slice">' +
        // Row 1 up-triangles
        '<polygon points="0,0 40,0 20,25" fill="rgba(255,255,255,0.9)"/>' +
        '<polygon points="40,0 80,0 60,25" fill="rgba(255,255,255,0.5)"/>' +
        '<polygon points="80,0 120,0 100,25" fill="rgba(255,255,255,0.75)"/>' +
        '<polygon points="120,0 160,0 140,25" fill="rgba(255,255,255,0.4)"/>' +
        '<polygon points="160,0 200,0 180,25" fill="rgba(255,255,255,0.65)"/>' +
        // Row 1 down-triangles
        '<polygon points="20,25 60,25 40,0" fill="rgba(255,255,255,0.35)"/>' +
        '<polygon points="60,25 100,25 80,0" fill="rgba(255,255,255,0.7)"/>' +
        '<polygon points="100,25 140,25 120,0" fill="rgba(255,255,255,0.45)"/>' +
        '<polygon points="140,25 180,25 160,0" fill="rgba(255,255,255,0.8)"/>' +
        // Row 2 up-triangles
        '<polygon points="0,25 40,25 20,50" fill="rgba(255,255,255,0.6)"/>' +
        '<polygon points="40,25 80,25 60,50" fill="rgba(255,255,255,0.85)"/>' +
        '<polygon points="80,25 120,25 100,50" fill="rgba(255,255,255,0.4)"/>' +
        '<polygon points="120,25 160,25 140,50" fill="rgba(255,255,255,0.7)"/>' +
        '<polygon points="160,25 200,25 180,50" fill="rgba(255,255,255,0.5)"/>' +
        // Row 2 down-triangles
        '<polygon points="20,50 60,50 40,25" fill="rgba(255,255,255,0.75)"/>' +
        '<polygon points="60,50 100,50 80,25" fill="rgba(255,255,255,0.4)"/>' +
        '<polygon points="100,50 140,50 120,25" fill="rgba(255,255,255,0.85)"/>' +
        '<polygon points="140,50 180,50 160,25" fill="rgba(255,255,255,0.55)"/>' +
        // Row 3
        '<polygon points="0,50 40,50 20,75" fill="rgba(255,255,255,0.5)"/>' +
        '<polygon points="40,50 80,50 60,75" fill="rgba(255,255,255,0.7)"/>' +
        '<polygon points="80,50 120,50 100,75" fill="rgba(255,255,255,0.6)"/>' +
        '<polygon points="120,50 160,50 140,75" fill="rgba(255,255,255,0.4)"/>' +
        '<polygon points="160,50 200,50 180,75" fill="rgba(255,255,255,0.8)"/>' +
        '<polygon points="20,75 60,75 40,50" fill="rgba(255,255,255,0.45)"/>' +
        '<polygon points="60,75 100,75 80,50" fill="rgba(255,255,255,0.65)"/>' +
        '<polygon points="100,75 140,75 120,50" fill="rgba(255,255,255,0.75)"/>' +
        '<polygon points="140,75 180,75 160,50" fill="rgba(255,255,255,0.5)"/>' +
        '</svg>';
    return '<div style="width:100%;height:100%;background:' + v.bg + ';font-family:\'' + v.font + '\',sans-serif;position:relative;overflow:hidden;color:' + v.txt + ';">' +
        // Header block
        '<div style="position:absolute;top:0;left:0;right:0;height:42%;background:linear-gradient(135deg,' + v.pri + ' 0%,' + v.acc + 'cc 100%);overflow:hidden;">' +
        trianglePoly +
        '<div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;flex-direction:column;justify-content:center;padding:0 5% 0 34%;">' +
        '<div style="font-size:clamp(0.64rem,1.6vw,0.92rem);font-weight:700;color:white;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + v.nameVal + '</div>' +
        '<div style="font-size:clamp(0.46rem,1.1vw,0.67rem);color:rgba(255,255,255,0.8);margin-top:3%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + v.roleVal + '</div>' +
        '</div>' +
        '</div>' +
        // Photo overlapping header/body
        '<div style="position:absolute;top:22%;left:4%;width:23%;aspect-ratio:1;border-radius:50%;border:3px solid ' + v.bg + ';background:' + v.acc + '22;overflow:hidden;display:flex;align-items:center;justify-content:center;z-index:2;box-shadow:0 4px 16px rgba(0,0,0,0.22);">' +
        v.photoHTML +
        '</div>' +
        // Body
        '<div style="position:absolute;top:44%;left:0;right:0;bottom:0;padding:2% 5% 4% 5%;display:flex;flex-direction:column;">' +
        '<div style="display:flex;align-items:center;gap:4%;margin-bottom:3%;">' +
        '<div style="flex:1;height:1.5px;background:linear-gradient(90deg,' + v.acc + ',transparent);border-radius:2px;"></div>' +
        '</div>' +
        '<div style="display:flex;flex-direction:column;">' + fieldListHTML(v.fieldItems, v.txt) + '</div>' +
        '</div>' +
        '<div style="position:absolute;bottom:3%;right:4%;font-size:clamp(0.35rem,0.7vw,0.5rem);font-family:monospace;opacity:0.3;color:' + v.txt + ';">CardX</div>' +
        '</div>';
}

// ── Style 5: Mosaic/Diagonal — halftone dots + gradient diagonal split ───────
function renderDiagonal(v) {
    // Build halftone dot grid for colored zone
    function dotGrid(cols, rows) {
        var dots = '';
        for (var r = 0; r < rows; r++) {
            for (var c = 0; c < cols; c++) {
                dots += '<circle cx="' + (c * 9 + 5) + '" cy="' + (r * 9 + 5) + '" r="1.8" fill="rgba(255,255,255,0.3)"/>';
            }
        }
        return dots;
    }
    var bgSVG =
        '<svg style="position:absolute;top:0;left:0;width:100%;height:100%;" viewBox="0 0 256 162" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">' +
        '<defs><linearGradient id="dg5" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="' + v.pri + '"/><stop offset="100%" stop-color="' + v.acc + '"/></linearGradient></defs>' +
        '<polygon points="0,0 155,0 105,162 0,162" fill="url(#dg5)"/>' +
        '</svg>';
    var halftone =
        '<svg style="position:absolute;top:0;left:0;width:62%;height:100%;opacity:0.9;" viewBox="0 0 155 162" xmlns="http://www.w3.org/2000/svg">' +
        '<clipPath id="dcl"><polygon points="0,0 155,0 105,162 0,162"/></clipPath>' +
        '<g clip-path="url(#dcl)">' +
        dotGrid(17, 18) +
        '</g>' +
        '</svg>';
    return '<div style="width:100%;height:100%;background:' + v.bg + ';font-family:\'' + v.font + '\',sans-serif;position:relative;overflow:hidden;">' +
        bgSVG + halftone +
        '<div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;align-items:center;">' +
        // Left colored zone — photo + card type label
        '<div style="width:45%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:5%;padding:0 3%;">' +
        '<div style="width:40%;aspect-ratio:1;border-radius:12%;border:2.5px solid rgba(255,255,255,0.65);background:rgba(255,255,255,0.18);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(0,0,0,0.15);">' +
        v.photoHTML + '</div>' +
        '<div style="font-size:clamp(0.34rem,0.8vw,0.5rem);color:rgba(255,255,255,0.6);text-align:center;font-family:monospace;text-transform:uppercase;letter-spacing:0.07em;">ID Card</div>' +
        '</div>' +
        // Right light zone
        '<div style="flex:1;padding:4% 4% 4% 2%;color:' + v.txt + ';min-width:0;">' +
        '<div style="font-size:clamp(0.65rem,1.6vw,0.92rem);font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + v.nameVal + '</div>' +
        '<div style="font-size:clamp(0.47rem,1.1vw,0.67rem);color:' + v.acc + ';margin-top:3%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600;">' + v.roleVal + '</div>' +
        '<div style="display:flex;align-items:center;gap:5%;margin:4% 0;">' +
        '<div style="flex:1;height:1.5px;background:linear-gradient(90deg,' + v.acc + ',transparent);border-radius:2px;"></div>' +
        '</div>' +
        '<div style="display:flex;flex-direction:column;">' + fieldListHTML(v.fieldItems, v.txt) + '</div>' +
        '</div>' +
        '</div>' +
        '</div>';
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
