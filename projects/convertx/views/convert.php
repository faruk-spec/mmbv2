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
                    <optgroup label="<?= htmlspecialchars($groupLabels[$group] ?? ucfirst($group)) . ($available ? '' : ' (requires LibreOffice)') ?>">
                        <?php foreach ($fmts as $fmt): ?>
                        <option value="<?= htmlspecialchars($fmt) ?>"
                                <?= $available ? '' : 'disabled' ?>>
                            <?= strtoupper(htmlspecialchars($fmt)) ?>
                        </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endforeach; ?>
                </select>
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
</style>

<script>
var IMAGE_FORMATS = <?= json_encode(array_values($imageFormats)) ?>;

function updateAdvancedOptions(fmt) {
    var isImage = IMAGE_FORMATS.indexOf(fmt) !== -1;
    document.getElementById('qualityGroup').style.display = isImage ? '' : 'none';
    document.getElementById('dpiGroup').style.display     = isImage ? '' : 'none';
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

        var fd = new FormData(form);
        try {
            var res  = await fetch('/projects/convertx/convert', { method: 'POST', body: fd });
            var data = await res.json();
            if (data.success) {
                statusDiv.style.display = 'block';
                statusDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                pollJobStatus(data.job_id);
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-arrow-right-arrow-left"></i> Start Conversion';
            }
        } catch (err) {
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
            html += '<div style="margin-top:.75rem;height:4px;background:var(--border-color);border-radius:4px;overflow:hidden;">'
                  + '<div style="height:100%;width:60%;background:linear-gradient(90deg,var(--cx-primary),var(--cx-accent));border-radius:4px;animation:cx-progress 1.2s ease-in-out infinite;"></div></div>';
        }

        if (data.status === 'completed') {
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
            html += '<p style="color:var(--cx-danger);margin-top:.5rem;font-size:.875rem;"><i class="fa-solid fa-circle-xmark"></i> ' + (data.error_message || 'Conversion failed') + '</p>';
        }

        detailDiv.innerHTML = html;
    }
})();
</script>
