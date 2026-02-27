<?php
/**
 * ConvertX – Convert File View
 */
$currentView = 'convert';
$csrfToken   = \Core\Security::generateCsrfToken();

// Preset AI quick-enable from dashboard clicks – validate against allowed values
$allowedPresets = ['ocr', 'summarize', 'translate', 'classify'];
$presetAi = in_array($_GET['ai'] ?? '', $allowedPresets, true) ? ($_GET['ai']) : '';

// Flatten and keep grouped formats for the optgroup selector
$groupedFormats = $formats ?? [];
$imageFormats   = $groupedFormats['image'] ?? ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg'];
$allFormatArrays = array_filter(array_values($groupedFormats), 'is_array');
$allFormats     = $allFormatArrays ? array_unique(array_merge(...$allFormatArrays)) : [];
sort($allFormats);

// Server capability flags
$backends        = $backends ?? ['php' => true, 'gd' => false, 'libreoffice' => false, 'imagemagick' => false, 'pandoc' => false];
$hasLibreOffice  = !empty($backends['libreoffice']);
$hasImageMagick  = !empty($backends['imagemagick']) || !empty($backends['gd']);

// Groups that require LibreOffice
$officeGroups    = ['document', 'spreadsheet', 'presentation'];

// Format category labels (plain text, no emoji — <optgroup> can't render HTML)
$groupLabels = [
    'document'     => 'Documents',
    'spreadsheet'  => 'Spreadsheets',
    'presentation' => 'Presentations',
    'image'        => 'Images',
];

// Which groups are available?
$groupAvailable = [];
foreach ($groupedFormats as $group => $fmts) {
    if (in_array($group, $officeGroups, true)) {
        $groupAvailable[$group] = $hasLibreOffice;
    } else {
        $groupAvailable[$group] = true; // images via GD/ImageMagick
    }
}
?>

<!-- Page header -->
<div class="page-header">
    <h1>Convert a File</h1>
    <p>Upload any document, image or spreadsheet and convert it instantly</p>
</div>

<?php $suggestions = [
    ['from'=>'PDF',  'to'=>'DOCX', 'icon'=>'fa-file-pdf',        'color'=>'#ef4444', 'desc'=>'Edit PDF content'],
    ['from'=>'DOCX', 'to'=>'PDF',  'icon'=>'fa-file-word',        'color'=>'#2563eb', 'desc'=>'Share as PDF'],
    ['from'=>'XLSX', 'to'=>'CSV',  'icon'=>'fa-file-excel',       'color'=>'#16a34a', 'desc'=>'Export data'],
    ['from'=>'PNG',  'to'=>'JPG',  'icon'=>'fa-file-image',       'color'=>'#7c3aed', 'desc'=>'Reduce file size'],
    ['from'=>'JPG',  'to'=>'WEBP', 'icon'=>'fa-image',            'color'=>'#0891b2', 'desc'=>'Web optimised'],
    ['from'=>'PPTX', 'to'=>'PDF',  'icon'=>'fa-file-powerpoint',  'color'=>'#ea580c', 'desc'=>'Present anywhere'],
    ['from'=>'CSV',  'to'=>'XLSX', 'icon'=>'fa-table',            'color'=>'#059669', 'desc'=>'Spreadsheet format'],
    ['from'=>'SVG',  'to'=>'PNG',  'icon'=>'fa-vector-square',    'color'=>'#8b5cf6', 'desc'=>'Rasterise vector'],
    ['from'=>'JPG',  'to'=>'PDF',  'icon'=>'fa-image',            'color'=>'#dc2626', 'desc'=>'Create PDF'],
    ['from'=>'HTML', 'to'=>'PDF',  'icon'=>'fa-code',             'color'=>'#0ea5e9', 'desc'=>'Print to PDF'],
    ['from'=>'EPUB', 'to'=>'PDF',  'icon'=>'fa-book',             'color'=>'#7c3aed', 'desc'=>'Convert ebook'],
    ['from'=>'BMP',  'to'=>'PNG',  'icon'=>'fa-file-image',       'color'=>'#9333ea', 'desc'=>'Modern format'],
]; ?>

<!-- ── Popular Converter Suggestions (desktop: before grid, mobile: after grid via CSS order) ── -->
<div class="cx-suggestions-section cx-suggestions-desktop">
    <div class="cx-suggestions-title">
        <i class="fa-solid fa-bolt-lightning" style="color:var(--cx-primary);"></i>
        Popular Conversions
    </div>
    <div class="cx-suggestions-grid">
        <?php
        foreach ($suggestions as $s): ?>
        <button type="button" class="cx-suggestion-card"
                onclick="applySuggestion('<?= strtolower($s['to']) ?>')"
                title="Convert <?= $s['from'] ?> to <?= $s['to'] ?>">
            <i class="fa-solid <?= $s['icon'] ?>" style="color:<?= $s['color'] ?>;font-size:1.25rem;margin-bottom:.35rem;"></i>
            <span class="cx-sug-route">
                <span style="color:var(--text-secondary);"><?= $s['from'] ?></span>
                <i class="fa-solid fa-arrow-right" style="font-size:.6rem;color:var(--cx-primary);"></i>
                <span style="color:var(--cx-primary);font-weight:600;"><?= $s['to'] ?></span>
            </span>
            <span class="cx-sug-desc"><?= $s['desc'] ?></span>
        </button>
        <?php endforeach; ?>
    </div>
</div>

<?php if (!$hasLibreOffice): ?>
<!-- Server capability notice -->
<div class="cx-notice">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>
        <strong>LibreOffice is not installed on this server</strong>
        Only the following conversions are available without additional server software:
        <div class="cx-notice-formats">
            <span class="cx-notice-tag available"><i class="fa-solid fa-check-circle"></i> TXT, HTML, MD, CSV (text/markup)</span>
            <span class="cx-notice-tag available"><i class="fa-solid fa-check-circle"></i> JPG, PNG, GIF, WebP, BMP (images)</span>
            <span class="cx-notice-tag unavailable"><i class="fa-solid fa-xmark-circle"></i> PDF, DOCX, XLSX, PPTX … (requires LibreOffice)</span>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($presetAi): ?>
<div class="cx-ai-panel" style="margin-bottom:1.5rem;animation:cx-slide-down 0.4s ease both;">
    <div style="display:flex;align-items:center;gap:.75rem;">
        <div style="width:36px;height:36px;background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));border-radius:.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fa-solid fa-wand-magic-sparkles" style="color:#fff;font-size:.9rem;"></i>
        </div>
        <div style="flex:1;">
            <div style="font-weight:600;font-size:.9rem;color:var(--text-primary);">
                <i class="fa-solid fa-check-circle" style="color:var(--cx-success);"></i>
                AI preset applied: <em><?= htmlspecialchars(ucfirst($presetAi)) ?></em>
            </div>
            <div style="font-size:.78rem;color:var(--text-secondary);">AI enhancement has been pre-selected for you</div>
        </div>
        <a href="/projects/convertx/convert" class="cx-clear-btn">
            <i class="fa-solid fa-xmark"></i> Clear
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Two-column form grid: upload+AI on left, options+submit on right -->
<div class="cx-convert-grid">

    <!-- ── Left column: upload + AI ── -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-file-arrow-up"></i> Upload &amp; AI
        </div>

        <form id="convertForm" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <!-- Upload zone -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-cloud-arrow-up" style="color:var(--cx-primary);"></i> Source File
                </label>
                <div class="upload-zone" id="uploadZone">
                    <i class="fa-solid fa-cloud-arrow-up upload-icon" style="font-size:1.75rem;"></i>
                    <p style="font-weight:600;font-size:.875rem;margin:.35rem 0 .2rem;">Drag &amp; drop or <strong>click to browse</strong></p>
                    <p style="font-size:.73rem;color:var(--text-muted);">PDF, DOCX, XLSX, PNG, JPG and more</p>
                    <input type="file" name="file" id="fileInput" style="display:none;"
                           accept="<?= implode(',', array_map(fn($f) => '.' . $f, $allFormats)) ?>">
                </div>
                <div id="selectedFile" style="margin-top:.4rem;font-size:.82rem;display:none;"></div>
            </div>

            <!-- AI enhancement panel -->
            <div class="cx-ai-panel">
                <div class="cx-ai-panel-header" onclick="toggleAiOptions()">
                    <i class="fa-solid fa-wand-magic-sparkles" style="color:var(--cx-primary);"></i>
                    <span>AI Enhancements</span>
                    <span class="ai-badge" style="margin-left:.375rem;">AI</span>
                    <i class="fa-solid fa-chevron-down cx-chevron" id="aiChevron"></i>
                </div>
                <div id="aiOptions">
                    <div style="display:flex;flex-direction:column;gap:.3rem;">
                        <label class="cx-ai-option">
                            <input type="checkbox" name="ai_ocr" value="1" <?= $presetAi === 'ocr' ? 'checked' : '' ?>>
                            <i class="fa-solid fa-eye"></i>
                            <span>OCR (extract text from scanned)</span>
                        </label>
                        <label class="cx-ai-option">
                            <input type="checkbox" name="ai_summarize" value="1" <?= $presetAi === 'summarize' ? 'checked' : '' ?>>
                            <i class="fa-solid fa-list-check"></i>
                            <span>Summarize document</span>
                        </label>
                        <label class="cx-ai-option">
                            <input type="checkbox" name="ai_translate" value="1" id="translateCheck" <?= $presetAi === 'translate' ? 'checked' : '' ?>>
                            <i class="fa-solid fa-language"></i>
                            <span>Translate document</span>
                        </label>
                        <div id="langSelect" style="<?= $presetAi === 'translate' ? '' : 'display:none;' ?>margin-left:2rem;margin-top:.2rem;">
                            <select class="form-control" name="target_lang" style="font-size:.82rem;">
                                <optgroup label="European">
                                    <option value="fr">French</option>
                                    <option value="de">German</option>
                                    <option value="es">Spanish</option>
                                    <option value="it">Italian</option>
                                    <option value="pt">Portuguese</option>
                                    <option value="nl">Dutch</option>
                                    <option value="pl">Polish</option>
                                    <option value="ru">Russian</option>
                                    <option value="uk">Ukrainian</option>
                                    <option value="sv">Swedish</option>
                                    <option value="no">Norwegian</option>
                                    <option value="da">Danish</option>
                                    <option value="fi">Finnish</option>
                                    <option value="cs">Czech</option>
                                    <option value="ro">Romanian</option>
                                    <option value="hu">Hungarian</option>
                                    <option value="el">Greek</option>
                                    <option value="tr">Turkish</option>
                                </optgroup>
                                <optgroup label="Middle East &amp; Africa">
                                    <option value="ar">Arabic</option>
                                    <option value="he">Hebrew</option>
                                    <option value="fa">Persian</option>
                                    <option value="sw">Swahili</option>
                                </optgroup>
                                <optgroup label="Asia">
                                    <option value="zh">Chinese (Simplified)</option>
                                    <option value="zh-TW">Chinese (Traditional)</option>
                                    <option value="ja">Japanese</option>
                                    <option value="ko">Korean</option>
                                    <option value="hi">Hindi</option>
                                    <option value="bn">Bengali</option>
                                    <option value="id">Indonesian</option>
                                    <option value="ms">Malay</option>
                                    <option value="th">Thai</option>
                                    <option value="vi">Vietnamese</option>
                                </optgroup>
                                <optgroup label="Americas">
                                    <option value="en">English</option>
                                </optgroup>
                            </select>
                        </div>
                        <label class="cx-ai-option">
                            <input type="checkbox" name="ai_classify" value="1" <?= $presetAi === 'classify' ? 'checked' : '' ?>>
                            <i class="fa-solid fa-tags"></i>
                            <span>Classify document type</span>
                        </label>
                    </div>
                </div>
            </div>
        <!-- form continues in right column -->

    </div><!-- left card -->

    <!-- ── Right column: format + options + submit ── -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-file-export"></i> Output Options
        </div>

            <!-- Output format -->
            <div class="form-group">
                <label class="form-label" for="outputFormat">
                    <i class="fa-solid fa-arrow-right-arrow-left" style="color:var(--cx-primary);"></i> Convert To
                </label>
                <select class="form-control" id="outputFormat" name="output_format" required
                        form="convertForm" onchange="updateAdvancedOptions(this.value)">
                    <option value="">— Select format —</option>
                    <?php foreach ($groupedFormats as $group => $fmts): ?>
                    <?php $available = $groupAvailable[$group] ?? true; ?>
                    <optgroup label="<?= htmlspecialchars($groupLabels[$group] ?? ucfirst($group)) . ($available ? '' : ' (requires LibreOffice)') ?>"
                              data-group="<?= htmlspecialchars($group) ?>">
                        <?php foreach ($fmts as $fmt): ?>
                        <option value="<?= htmlspecialchars($fmt) ?>"
                                data-fmt="<?= htmlspecialchars($fmt) ?>"
                                <?= $available ? '' : 'disabled data-lo-required="1"' ?>>
                            <?= strtoupper(htmlspecialchars($fmt)) ?>
                        </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endforeach; ?>
                </select>
                <div id="compatHint" style="display:none;margin-top:.35rem;font-size:.78rem;padding:.4rem .6rem;border-radius:.4rem;">
                    <i class="fa-solid fa-circle-info"></i> <span id="compatHintText"></span>
                </div>
            </div>

            <!-- Quality slider (image output only) -->
            <div class="form-group" id="qualityGroup" style="display:none;">
                <label class="form-label">
                    <i class="fa-solid fa-sliders" style="color:var(--cx-primary);"></i>
                    Quality: <strong id="qualityVal">85</strong>%
                </label>
                <input type="range" name="quality" id="qualitySlider" form="convertForm"
                       min="10" max="100" value="85"
                       style="width:100%;accent-color:var(--cx-primary);"
                       oninput="document.getElementById('qualityVal').textContent=this.value">
                <div style="display:flex;justify-content:space-between;font-size:.7rem;color:var(--text-muted);margin-top:.15rem;">
                    <span>Small</span><span>Balanced</span><span>Max</span>
                </div>
            </div>

            <!-- DPI select (image output only) -->
            <div class="form-group" id="dpiGroup" style="display:none;">
                <label class="form-label" for="dpiSelect">
                    <i class="fa-solid fa-expand" style="color:var(--cx-primary);"></i> Resolution (DPI)
                </label>
                <select class="form-control" name="dpi" id="dpiSelect" form="convertForm">
                    <option value="72">72 DPI — Screen</option>
                    <option value="96">96 DPI — Standard</option>
                    <option value="150" selected>150 DPI — Good</option>
                    <option value="300">300 DPI — Print</option>
                    <option value="600">600 DPI — High-res</option>
                </select>
            </div>

            <!-- Webhook -->
            <details style="margin-bottom:1rem;">
                <summary style="cursor:pointer;color:var(--text-secondary);font-size:.8rem;padding:.4rem 0;list-style:none;display:flex;align-items:center;gap:.4rem;">
                    <i class="fa-solid fa-link"></i> Webhook URL <span style="font-size:.7rem;opacity:.6;">(optional)</span>
                </summary>
                <div style="padding:.4rem 0 0;">
                    <input type="url" class="form-control" name="webhook_url"
                           form="convertForm" placeholder="https://yourapp.com/webhook">
                </div>
            </details>

            <button type="submit" form="convertForm" class="btn btn-primary" id="submitBtn"
                    style="width:100%;justify-content:center;padding:.825rem;">
                <i class="fa-solid fa-arrow-right-arrow-left"></i> Start Conversion
            </button>

    </div><!-- right card -->

</div><!-- .cx-convert-grid -->

<!-- ── Popular Conversions (mobile only, shown after the grid) ── -->
<div class="cx-suggestions-section cx-suggestions-mobile">
    <div class="cx-suggestions-title">
        <i class="fa-solid fa-bolt-lightning" style="color:var(--cx-primary);"></i>
        Popular Conversions
    </div>
    <div class="cx-suggestions-grid">
        <?php foreach ($suggestions as $s): ?>
        <button type="button" class="cx-suggestion-card"
                onclick="applySuggestion('<?= strtolower($s['to']) ?>')"
                title="Convert <?= $s['from'] ?> to <?= $s['to'] ?>">
            <i class="fa-solid <?= $s['icon'] ?>" style="color:<?= $s['color'] ?>;font-size:1.25rem;margin-bottom:.35rem;"></i>
            <span class="cx-sug-route">
                <span style="color:var(--text-secondary);"><?= $s['from'] ?></span>
                <i class="fa-solid fa-arrow-right" style="font-size:.6rem;color:var(--cx-primary);"></i>
                <span style="color:var(--cx-primary);font-weight:600;"><?= $s['to'] ?></span>
            </span>
            <span class="cx-sug-desc"><?= $s['desc'] ?></span>
        </button>
        <?php endforeach; ?>
    </div>
</div>

<!-- Job status card (shown after submission) -->
<div id="jobStatus" class="card" style="display:none;margin-top:1.25rem;">
    <div class="card-header" id="jobStatusHeader">
        <i class="fa-solid fa-spinner" style="animation:cx-spin 1s linear infinite;"></i> Job Progress
    </div>
    <div id="jobDetails"></div>
</div>

<style>
/* Convert page 2-column grid */
.cx-convert-grid {
    display: grid;
    grid-template-columns: 1.1fr 1fr;
    gap: 1.25rem;
    align-items: start;
}
@media (max-width: 768px) {
    .cx-convert-grid { grid-template-columns: 1fr; }
}
@keyframes cx-progress {
    0%   { transform: translateX(-100%); }
    100% { transform: translateX(200%); }
}

/* Popular Converter Suggestions */
.cx-suggestions-section {
    margin-bottom: 1.5rem;
}
.cx-suggestions-title {
    font-size: .8rem;
    font-weight: 600;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: .75rem;
    display: flex;
    align-items: center;
    gap: .4rem;
}
.cx-suggestions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
    gap: .5rem;
}
.cx-suggestion-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: .625rem;
    padding: .625rem .5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .1rem;
    cursor: pointer;
    transition: border-color .2s, box-shadow .2s, transform .2s;
    font-family: inherit;
    text-align: center;
}
.cx-suggestion-card:hover {
    border-color: var(--border-hover);
    box-shadow: 0 4px 16px rgba(99,102,241,.16);
    transform: translateY(-2px);
}
.cx-sug-route {
    display: flex;
    align-items: center;
    gap: .25rem;
    font-size: .73rem;
    font-weight: 600;
    margin-top: .1rem;
}
.cx-sug-desc {
    font-size: .67rem;
    color: var(--text-muted);
    line-height: 1.3;
    margin-top: .15rem;
}
@media (max-width: 600px) {
    .cx-suggestions-grid { grid-template-columns: repeat(4, 1fr); }
}

/* Mobile layout: hide desktop suggestions, show mobile copy after grid */
.cx-suggestions-mobile { display: none; }
@media (max-width: 768px) {
    .cx-suggestions-desktop { display: none; }
    .cx-suggestions-mobile  { display: block; }
}
</style>

<script>
var IMAGE_FORMATS = <?= json_encode(array_values($imageFormats)) ?>;

/**
 * Apply a suggestion card: pre-selects the output format dropdown.
 */
function applySuggestion(fmt) {
    var select = document.getElementById('outputFormat');
    var opt = select.querySelector('option[value="' + fmt + '"]');
    if (opt && !opt.disabled) {
        select.value = fmt;
        updateAdvancedOptions(fmt);
        // Scroll to the form
        select.closest('.card').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        // Briefly highlight the select
        select.style.borderColor = 'var(--cx-primary)';
        select.style.boxShadow = '0 0 0 3px rgba(99,102,241,.25)';
        setTimeout(function () { select.style.borderColor = ''; select.style.boxShadow = ''; }, 1500);
    }
}

function updateAdvancedOptions(fmt) {
    var isImage = IMAGE_FORMATS.indexOf(fmt) !== -1;
    document.getElementById('qualityGroup').style.display = isImage ? '' : 'none';
    document.getElementById('dpiGroup').style.display     = isImage ? '' : 'none';
}

/**
 * Enable all compatible output formats for the chosen input file.
 * Cross-family conversions (image → office, office → image) are now
 * supported via a 2-step PDF bridge on the server, so we only disable
 * options that genuinely can't run (LibreOffice not installed).
 */
function filterOutputFormats(inputExt) {
    var ext      = inputExt.toLowerCase();
    var isInImg  = IMAGE_FORMATS.indexOf(ext) !== -1;
    var select   = document.getElementById('outputFormat');
    var hint     = document.getElementById('compatHint');
    var hintText = document.getElementById('compatHintText');

    // Re-enable every option that isn't blocked by a missing server tool
    select.querySelectorAll('option[data-fmt]').forEach(function (opt) {
        if (opt.getAttribute('data-lo-required') !== '1') {
            opt.disabled   = false;
            opt.style.color = '';
        }
    });

    // Reset the selected value if it became disabled
    if (select.value) {
        var sel = select.querySelector('option[value="' + select.value + '"]');
        if (sel && sel.disabled) { select.value = ''; }
    }

    // Informational hint for cross-family conversions (2-step PDF chain)
    if (isInImg) {
        hintText.textContent = ext.toUpperCase()
            + ' → document/spreadsheet formats use a 2-step chain: image \u2192 PDF \u2192 target.';
        hint.style.background   = 'rgba(99,102,241,.1)';
        hint.style.color        = 'var(--cx-primary)';
        hint.style.border       = '1px solid rgba(99,102,241,.25)';
        hint.querySelector('i').className = 'fa-solid fa-circle-info';
        hint.style.display = '';
    } else if (ext && !isInImg) {
        hintText.textContent = ext.toUpperCase()
            + ' \u2192 image formats use a 2-step chain: document \u2192 PDF \u2192 image.';
        hint.style.background   = 'rgba(99,102,241,.1)';
        hint.style.color        = 'var(--cx-primary)';
        hint.style.border       = '1px solid rgba(99,102,241,.25)';
        hint.querySelector('i').className = 'fa-solid fa-circle-info';
        hint.style.display = '';
    } else {
        hint.style.display = 'none';
    }
}

function toggleAiOptions() {
    var box  = document.getElementById('aiOptions');
    var icon = document.getElementById('aiChevron');
    var hidden = box.style.display === 'none';
    box.style.display  = hidden ? '' : 'none';
    icon.style.transform = hidden ? 'rotate(180deg)' : '';
}

(function () {
    // ── Drag & drop upload zone ──
    var zone  = document.getElementById('uploadZone');
    var input = document.getElementById('fileInput');
    var label = document.getElementById('selectedFile');

    zone.addEventListener('click', function () { input.click(); });
    zone.addEventListener('dragover', function (e) { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', function () { zone.classList.remove('drag-over'); });
    zone.addEventListener('drop', function (e) {
        e.preventDefault();
        zone.classList.remove('drag-over');
        if (e.dataTransfer.files.length) {
            input.files = e.dataTransfer.files;
            updateLabel();
        }
    });
    input.addEventListener('change', updateLabel);

    function updateLabel() {
        if (input.files.length) {
            var f    = input.files[0];
            var size = f.size >= 1048576
                ? (f.size / 1048576).toFixed(1) + ' MB'
                : (f.size / 1024).toFixed(1) + ' KB';
            label.innerHTML = '<i class="fa-solid fa-check-circle" style="color:var(--cx-success);"></i> '
                            + htmlEsc(f.name) + ' <span style="color:var(--text-muted);">(' + size + ')</span>';
            label.style.display = 'block';
            zone.classList.add('has-file');
            zone.classList.remove('drag-over');
            // Filter output dropdown to only show compatible formats
            var ext = f.name.includes('.') ? f.name.split('.').pop() : '';
            if (ext) filterOutputFormats(ext);
        }
    }

    function htmlEsc(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Translate language toggle ──
    var translateCheck = document.getElementById('translateCheck');
    var langSelect     = document.getElementById('langSelect');
    translateCheck.addEventListener('change', function () {
        langSelect.style.display = translateCheck.checked ? 'block' : 'none';
    });

    // ── Form submission ──
    var form      = document.getElementById('convertForm');
    var statusDiv = document.getElementById('jobStatus');
    var detailDiv = document.getElementById('jobDetails');
    var hdrDiv    = document.getElementById('jobStatusHeader');
    var submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        // ── Client-side validation ──
        if (!input.files || !input.files.length) {
            zone.style.borderColor = 'var(--cx-danger)';
            zone.style.animation   = 'none';
            label.innerHTML = '<i class="fa-solid fa-circle-xmark" style="color:var(--cx-danger);"></i>'
                            + ' <strong style="color:var(--cx-danger);">Please select a file first.</strong>';
            label.style.display = 'block';
            setTimeout(function () {
                zone.style.borderColor = '';
                zone.style.animation   = '';
            }, 2500);
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner" style="animation:cx-spin 1s linear infinite;"></i> Uploading…';
        showConvertingOverlay();

        var fd = new FormData(form);
        try {
            var res  = await fetch('/projects/convertx/convert', { method: 'POST', body: fd });
            var data = await res.json();
            if (data.success) {
                hideConvertingOverlay();
                statusDiv.style.display = 'block';
                statusDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                pollJobStatus(data.job_id);
            } else {
                hideConvertingOverlay();
                alert('Error: ' + (data.error || 'Unknown error'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-arrow-right-arrow-left"></i> Start Conversion';
            }
        } catch (err) {
            hideConvertingOverlay();
            alert('Network error: ' + err.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-arrow-right-arrow-left"></i> Start Conversion';
        }
    });

    function pollJobStatus(jobId) {
        var attempts = 0;
        var maxAttempts = 120;
        var poll = async function () {
            attempts++;
            try {
                var res  = await fetch('/projects/convertx/job/' + jobId);
                var data = await res.json();
                renderStatus(data, jobId);
                if (!['completed', 'failed', 'cancelled'].includes(data.status) && attempts < maxAttempts) {
                    setTimeout(poll, 1500);
                } else {
                    hdrDiv.innerHTML = '<i class="fa-solid fa-circle-check" style="color:var(--cx-success);"></i> Job Complete';
                    hideConvertingOverlay();
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa-solid fa-arrow-right-arrow-left"></i> Convert Another File';
                }
            } catch (err) {
                if (attempts < maxAttempts) setTimeout(poll, 3000);
            }
        };
        poll();
    }

    function renderStatus(data, jobId) {
        var badgeClass = {
            pending:'badge-pending', processing:'badge-processing',
            completed:'badge-completed', failed:'badge-failed', cancelled:'badge-cancelled'
        }[data.status] || 'badge-pending';

        var html = '<p style="font-size:.9rem;color:var(--text-primary);">Job <strong>#' + jobId + '</strong> &nbsp; <span class="badge ' + badgeClass + '">' + data.status.toUpperCase() + '</span></p>';

        if (data.status === 'processing') {
            showConvertingOverlay('Processing your file…', 'AI analysis and conversion in progress');
            html += '<div style="margin-top:.75rem;height:4px;background:var(--border-color);border-radius:4px;overflow:hidden;">'
                  + '<div style="height:100%;width:60%;background:linear-gradient(90deg,var(--cx-primary),var(--cx-accent));border-radius:4px;animation:cx-progress 1.2s ease-in-out infinite;"></div></div>';
        }

        if (data.status === 'completed') {
            hideConvertingOverlay();
            if (typeof CXNotify !== 'undefined') CXNotify.success('Conversion complete! Your file is ready.');
            html += '<div style="margin-top:1rem;display:flex;gap:.75rem;flex-wrap:wrap;">';
            html += '<a href="/projects/convertx/job/' + jobId + '/download" class="btn btn-success"><i class="fa-solid fa-download"></i> Download ' + (data.output_filename || 'File') + '</a>';
            html += '</div>';
            if (data.ai_result) {
                html += '<div style="margin-top:1rem;border-top:1px solid var(--border-color);padding-top:1rem;">';
                html += '<strong style="font-size:.85rem;color:var(--cx-primary);">✨ AI Results</strong>';
                if (data.ai_result.ocr)       html += '<p style="font-size:.8rem;margin-top:.5rem;color:var(--text-primary);"><strong>OCR:</strong> ' + (data.ai_result.ocr.text || '').substring(0, 300) + '…</p>';
                if (data.ai_result.summarize) html += '<p style="font-size:.8rem;margin-top:.5rem;color:var(--text-primary);"><strong>Summary:</strong> ' + (data.ai_result.summarize.summary || '') + '</p>';
                if (data.ai_result.classify)  html += '<p style="font-size:.8rem;margin-top:.5rem;color:var(--text-primary);"><strong>Category:</strong> ' + (data.ai_result.classify.category || '') + ' (' + Math.round((data.ai_result.classify.confidence || 0) * 100) + '%)</p>';
                if (data.ai_result.translate) html += '<p style="font-size:.8rem;margin-top:.5rem;color:var(--text-primary);"><strong>Translation:</strong> ' + (data.ai_result.translate.translated || '').substring(0, 300) + '…</p>';
                html += '</div>';
            }
        }

        if (data.status === 'failed') {
            hideConvertingOverlay();
            if (typeof CXNotify !== 'undefined') CXNotify.error('Conversion failed: ' + (data.error_message || 'Unknown error'));
            html += '<p style="color:var(--cx-danger);margin-top:.5rem;font-size:.875rem;"><i class="fa-solid fa-circle-xmark"></i> ' + (data.error_message || 'Conversion failed') + '</p>';
        }

        detailDiv.innerHTML = html;
    }
})();
</script>

<!-- Conversion Animation Overlay -->
<div id="convertingOverlay" style="display:none;position:fixed;inset:0;background:rgba(6,6,10,0.85);z-index:9999;align-items:center;justify-content:center;flex-direction:column;gap:1.5rem;backdrop-filter:blur(6px);">
    <div style="text-align:center;">
        <div id="convOrb" style="width:80px;height:80px;margin:0 auto 1.25rem;border-radius:50%;background:linear-gradient(135deg,var(--cx-primary,#6366f1),var(--cx-accent,#06b6d4));display:flex;align-items:center;justify-content:center;animation:cx-orb-pulse 1.6s ease-in-out infinite;box-shadow:0 0 40px rgba(99,102,241,0.6);">
            <i class="fa-solid fa-shuffle" style="font-size:2rem;color:#fff;animation:cx-spin 2s linear infinite;"></i>
        </div>
        <div style="font-size:1.2rem;font-weight:700;color:#fff;margin-bottom:.5rem;" id="convOverlayMsg">Converting your file…</div>
        <div style="font-size:.85rem;color:rgba(255,255,255,.6);" id="convOverlaySub">Please wait while we process your document</div>
        <!-- Animated progress bar -->
        <div style="width:280px;height:4px;background:rgba(255,255,255,.15);border-radius:4px;margin:1.25rem auto 0;overflow:hidden;">
            <div style="height:100%;width:40%;background:linear-gradient(90deg,var(--cx-primary,#6366f1),var(--cx-accent,#06b6d4));border-radius:4px;animation:cx-progress-bar 1.8s ease-in-out infinite;"></div>
        </div>
        <!-- Animated dots -->
        <div style="display:flex;gap:.5rem;justify-content:center;margin-top:1rem;">
            <span style="width:8px;height:8px;background:var(--cx-primary,#6366f1);border-radius:50%;animation:cx-dot-bounce 1.4s ease-in-out infinite;animation-delay:0s;"></span>
            <span style="width:8px;height:8px;background:var(--cx-accent,#06b6d4);border-radius:50%;animation:cx-dot-bounce 1.4s ease-in-out infinite;animation-delay:.2s;"></span>
            <span style="width:8px;height:8px;background:var(--cx-secondary,#8b5cf6);border-radius:50%;animation:cx-dot-bounce 1.4s ease-in-out infinite;animation-delay:.4s;"></span>
        </div>
    </div>
</div>

<style>
@keyframes cx-orb-pulse {
    0%,100% { transform: scale(1);   box-shadow: 0 0 40px rgba(99,102,241,0.5); }
    50%      { transform: scale(1.08); box-shadow: 0 0 70px rgba(99,102,241,0.9); }
}
@keyframes cx-progress-bar {
    0%   { transform: translateX(-100%); }
    50%  { transform: translateX(150%); }
    100% { transform: translateX(-100%); }
}
@keyframes cx-dot-bounce {
    0%,80%,100% { transform: translateY(0); opacity: 0.5; }
    40%          { transform: translateY(-8px); opacity: 1; }
}
</style>

<script>
function showConvertingOverlay(msg, sub) {
    var overlay = document.getElementById('convertingOverlay');
    if (!overlay) return;
    var msgEl = document.getElementById('convOverlayMsg');
    var subEl = document.getElementById('convOverlaySub');
    if (msgEl) msgEl.textContent = msg || 'Converting your file…';
    if (subEl) subEl.textContent = sub || 'Please wait while we process your document';
    overlay.style.display = 'flex';
}
function hideConvertingOverlay() {
    var overlay = document.getElementById('convertingOverlay');
    if (overlay) overlay.style.display = 'none';
}
</script>
