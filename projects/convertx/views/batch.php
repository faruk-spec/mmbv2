<?php
/**
 * ConvertX – Batch Conversion View
 */
$currentView = 'batch';
$csrfToken   = \Core\Security::generateCsrfToken();

// Server capability flags
$backends       = $backends ?? ['php' => true, 'gd' => false, 'libreoffice' => false, 'imagemagick' => false, 'pandoc' => false];
$hasLibreOffice = !empty($backends['libreoffice']);

// Build flat format list for accept attribute
$allFormats = [];
foreach (($formats ?? []) as $formats_list) {
    foreach ($formats_list as $fmt) {
        $allFormats[] = $fmt;
    }
}
$allFormats = array_unique($allFormats);
sort($allFormats);

// Grouped formats for optgroup
$groupedFormats = $formats ?? [];
$officeGroups   = ['document', 'spreadsheet', 'presentation'];
$groupLabels    = [
    'document'     => 'Documents',
    'spreadsheet'  => 'Spreadsheets',
    'presentation' => 'Presentations',
    'image'        => 'Images',
];
$groupAvailable = [];
foreach ($groupedFormats as $group => $fmts) {
    $groupAvailable[$group] = $hasLibreOffice || !in_array($group, $officeGroups, true);
}
?>

<!-- Page header -->
<div class="page-header">
    <h1>Batch Convert</h1>
    <p>Upload up to 50 files — convert them all to the same format in one go</p>
</div>

<?php if (!$hasLibreOffice): ?>
<div class="cx-notice">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>
        <strong>LibreOffice is not installed on this server</strong>
        Batch conversions are limited to text/markup (TXT, HTML, MD, CSV) and image-to-image formats.
        Document formats (PDF, DOCX, XLSX…) require LibreOffice to be installed.
    </div>
</div>
<?php endif; ?>

<!-- ── Main form grid: left = files + AI, right = options + submit ── -->
<div class="cx-batch-grid">

    <!-- Left: file upload + AI enhancements -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-files"></i> Select Files &amp; AI Enhancements
        </div>

        <form id="batchForm" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <!-- File upload zone -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-file-circle-plus" style="color:var(--cx-primary);"></i>
                    Upload Files
                    <span style="font-size:.73rem;font-weight:400;color:var(--text-muted);margin-left:.4rem;">Max 50 per batch</span>
                </label>
                <div class="upload-zone" id="uploadZone">
                    <i class="fa-solid fa-files upload-icon" style="font-size:1.75rem;"></i>
                    <p style="font-weight:600;font-size:.875rem;margin:.35rem 0 .2rem;">Drag &amp; drop files or <strong>click to browse</strong></p>
                    <p style="font-size:.73rem;color:var(--text-muted);">Select multiple files at once</p>
                    <input type="file" name="files[]" id="fileInput" multiple style="display:none;"
                           accept="<?= implode(',', array_map(fn($f) => '.' . $f, $allFormats)) ?>">
                </div>
                <div id="fileList" style="margin-top:.75rem;display:flex;flex-direction:column;gap:.35rem;"></div>
            </div>

            <!-- AI enhancement panel -->
            <div class="form-group" style="border:1px solid var(--border-color);border-radius:.6rem;padding:.875rem;background:var(--bg-secondary);">
                <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;font-size:.82rem;font-weight:600;color:var(--text-primary);">
                    <i class="fa-solid fa-microchip" style="color:var(--cx-primary);"></i>
                    <span>AI Enhancements</span>
                    <span class="ai-badge" style="margin-left:.375rem;">AI</span>
                </div>

                <div style="display:flex;flex-direction:column;gap:.6rem;font-size:.8rem;">
                    <!-- OCR -->
                    <label style="display:flex;align-items:flex-start;gap:.6rem;cursor:pointer;">
                        <input type="checkbox" name="ai_ocr" value="1" style="margin-top:.15rem;flex-shrink:0;">
                        <span>
                            <strong>OCR – Extract text from images</strong>
                            <span style="display:block;color:var(--text-muted);font-size:.73rem;margin-top:.1rem;">Use AI vision to read text from scanned images or photos</span>
                        </span>
                    </label>

                    <!-- Summarize -->
                    <label style="display:flex;align-items:flex-start;gap:.6rem;cursor:pointer;">
                        <input type="checkbox" name="ai_summarize" value="1" style="margin-top:.15rem;flex-shrink:0;">
                        <span>
                            <strong>Summarize document</strong>
                            <span style="display:block;color:var(--text-muted);font-size:.73rem;margin-top:.1rem;">Generate a concise summary of each file's content</span>
                        </span>
                    </label>

                    <!-- Translate -->
                    <label style="display:flex;align-items:flex-start;gap:.6rem;cursor:pointer;" id="translateLabel">
                        <input type="checkbox" name="ai_translate" value="1" id="translateCheck" style="margin-top:.15rem;flex-shrink:0;">
                        <span style="flex:1;">
                            <strong>Translate</strong>
                            <span style="display:block;color:var(--text-muted);font-size:.73rem;margin-top:.1rem;">Translate each document to the selected language</span>
                            <div id="translateLangBox" style="display:none;margin-top:.45rem;">
                                <select name="target_lang" class="form-control" style="font-size:.8rem;padding:.35rem .55rem;height:auto;">
                                    <option value="es">Spanish</option>
                                    <option value="fr">French</option>
                                    <option value="de">German</option>
                                    <option value="it">Italian</option>
                                    <option value="pt">Portuguese</option>
                                    <option value="ru">Russian</option>
                                    <option value="zh">Chinese (Simplified)</option>
                                    <option value="ar">Arabic</option>
                                    <option value="ja">Japanese</option>
                                    <option value="ko">Korean</option>
                                    <option value="tr">Turkish</option>
                                    <option value="nl">Dutch</option>
                                    <option value="pl">Polish</option>
                                    <option value="sv">Swedish</option>
                                </select>
                            </div>
                        </span>
                    </label>

                    <!-- Classify -->
                    <label style="display:flex;align-items:flex-start;gap:.6rem;cursor:pointer;">
                        <input type="checkbox" name="ai_classify" value="1" style="margin-top:.15rem;flex-shrink:0;">
                        <span>
                            <strong>Classify document</strong>
                            <span style="display:block;color:var(--text-muted);font-size:.73rem;margin-top:.1rem;">Automatically categorise each document by type and topic</span>
                        </span>
                    </label>
                </div>
            </div><!-- /AI panel -->

        <!-- form continues in right column -->
    </div><!-- left card -->

    <!-- Right: output format + quality + submit -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-file-export"></i> Output Options
        </div>

            <!-- Output format -->
            <div class="form-group">
                <label class="form-label" for="outputFormat">
                    <i class="fa-solid fa-arrow-right-arrow-left" style="color:var(--cx-primary);"></i> Convert All To
                </label>
                <select class="form-control" id="outputFormat" name="output_format" required form="batchForm">
                    <option value="">— Select output format —</option>
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

            <!-- Quality slider (images only) -->
            <div class="form-group" id="qualityGroup" style="display:none;">
                <label class="form-label" for="qualitySlider">
                    <i class="fa-solid fa-sliders" style="color:var(--cx-primary);"></i>
                    Quality: <strong id="qualityVal">85</strong>%
                </label>
                <input type="range" name="quality" id="qualitySlider" form="batchForm"
                       min="10" max="100" value="85" step="5"
                       style="width:100%;accent-color:var(--cx-primary);"
                       oninput="document.getElementById('qualityVal').textContent=this.value">
            </div>

            <!-- DPI selector (images only) -->
            <div class="form-group" id="dpiGroup" style="display:none;">
                <label class="form-label" for="dpiSelect">
                    <i class="fa-solid fa-expand" style="color:var(--cx-primary);"></i> Resolution (DPI)
                </label>
                <select class="form-control" name="dpi" id="dpiSelect" form="batchForm">
                    <option value="72">72 DPI — Screen</option>
                    <option value="96">96 DPI — Web</option>
                    <option value="150" selected>150 DPI — Standard</option>
                    <option value="300">300 DPI — Print quality</option>
                    <option value="600">600 DPI — High resolution</option>
                </select>
            </div>

            <!-- Webhook -->
            <details style="margin-bottom:1rem;">
                <summary style="cursor:pointer;color:var(--text-secondary);font-size:.8rem;padding:.4rem 0;display:flex;align-items:center;gap:.4rem;list-style:none;">
                    <i class="fa-solid fa-link"></i> Webhook URL <span style="font-size:.7rem;opacity:.6;">(optional)</span>
                </summary>
                <div style="padding:.4rem 0 0;">
                    <input type="url" class="form-control" name="webhook_url" form="batchForm" placeholder="https://yourapp.com/webhook">
                </div>
            </details>

            <button type="submit" form="batchForm" class="btn btn-primary" id="submitBtn"
                    style="width:100%;justify-content:center;padding:.825rem;">
                <i class="fa-solid fa-layer-group"></i> Start Batch Conversion
            </button>

    </div><!-- right card -->

</div><!-- .cx-batch-grid -->

<!-- ── Batch results ── -->
<div id="batchResult" class="card" style="display:none;margin-top:1.25rem;">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
        <span><i class="fa-solid fa-list-check"></i> Batch Job Status</span>
        <button id="downloadAllBtn" class="btn btn-success btn-sm" style="display:none;" onclick="downloadAllZip()">
            <i class="fa-solid fa-file-zipper"></i> Download All as ZIP
        </button>
    </div>
    <div id="batchDetails"></div>
</div>

<style>
.cx-batch-grid {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    gap: 1.25rem;
    align-items: start;
}
@media (max-width: 768px) {
    .cx-batch-grid { grid-template-columns: 1fr; }
}
.cx-file-item {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .45rem .625rem;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: .45rem;
    font-size: .8rem;
    color: var(--text-primary);
    transition: border-color .2s;
}
.cx-file-item:hover { border-color: var(--border-hover); }
.cx-file-item .cx-file-name {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.cx-file-item .cx-file-size {
    color: var(--text-muted);
    flex-shrink: 0;
    font-size: .73rem;
}
.cx-file-remove {
    background: none;
    border: none;
    color: var(--cx-danger);
    cursor: pointer;
    padding: .2rem .35rem;
    border-radius: .35rem;
    flex-shrink: 0;
    line-height: 1;
    font-size: .8rem;
    opacity: .7;
    transition: opacity .15s, background .15s;
}
.cx-file-remove:hover { opacity: 1; background: rgba(239,68,68,.1); }
.cx-file-count {
    font-size: .78rem;
    color: var(--text-secondary);
    margin-top: .25rem;
}
/* Batch results table */
#batchResult .cx-table td { vertical-align: middle; }
.cx-batch-filename {
    max-width: 220px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block;
    font-size: .82rem;
    color: var(--text-primary);
}
</style>

<script>
(function () {
    /* ── File picker ─────────────────────────────────────────── */
    var zone     = document.getElementById('uploadZone');
    var input    = document.getElementById('fileInput');
    var fileList = document.getElementById('fileList');
    var MAX_FILES = 50;

    var selectedFiles = [];

    zone.addEventListener('click', function (e) {
        if (e.target.classList.contains('cx-file-remove')) return;
        input.click();
    });
    zone.addEventListener('dragover', function (e) { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', function () { zone.classList.remove('drag-over'); });
    zone.addEventListener('drop', function (e) {
        e.preventDefault();
        zone.classList.remove('drag-over');
        addFiles(Array.from(e.dataTransfer.files));
    });
    input.addEventListener('change', function () {
        addFiles(Array.from(input.files));
        input.value = '';
    });

    function addFiles(files) {
        files.forEach(function (f) {
            var exists = selectedFiles.some(function (sf) {
                return sf.name === f.name && sf.size === f.size && sf.lastModified === f.lastModified;
            });
            if (!exists && selectedFiles.length < MAX_FILES) {
                selectedFiles.push(f);
            }
        });
        renderList();
    }

    function removeFile(idx) {
        selectedFiles.splice(idx, 1);
        renderList();
    }

    function renderList() {
        fileList.innerHTML = '';
        if (!selectedFiles.length) {
            zone.classList.remove('has-file');
            return;
        }
        zone.classList.add('has-file');
        selectedFiles.forEach(function (f, i) {
            var size = f.size >= 1048576
                ? (f.size / 1048576).toFixed(1) + ' MB'
                : (f.size / 1024).toFixed(1) + ' KB';
            var item = document.createElement('div');
            item.className = 'cx-file-item';
            item.innerHTML = '<i class="fa-solid fa-file" style="color:var(--cx-primary);flex-shrink:0;font-size:.8rem;"></i>'
                           + '<span class="cx-file-name" title="' + htmlEsc(f.name) + '">' + htmlEsc(f.name) + '</span>'
                           + '<span class="cx-file-size">' + size + '</span>'
                           + '<button type="button" class="cx-file-remove" data-idx="' + i + '" title="Remove"><i class="fa-solid fa-xmark"></i></button>';
            item.querySelector('.cx-file-remove').addEventListener('click', function () {
                removeFile(parseInt(this.getAttribute('data-idx')));
            });
            fileList.appendChild(item);
        });
        var count = document.createElement('div');
        count.className = 'cx-file-count';
        count.innerHTML = '<i class="fa-solid fa-check-circle" style="color:var(--cx-success);"></i> '
                        + selectedFiles.length + ' file(s) selected';
        fileList.appendChild(count);
    }

    function htmlEsc(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ── Translate language toggle ─────────────────────────── */
    var translateCheck = document.getElementById('translateCheck');
    var translateBox   = document.getElementById('translateLangBox');
    if (translateCheck) {
        translateCheck.addEventListener('change', function () {
            translateBox.style.display = this.checked ? '' : 'none';
        });
    }

    /* ── Quality / DPI visibility based on format ───────────── */
    var outputFormat = document.getElementById('outputFormat');
    var qualityGroup = document.getElementById('qualityGroup');
    var dpiGroup     = document.getElementById('dpiGroup');
    var imageFormats = ['jpg','jpeg','png','gif','webp','bmp','tiff'];

    function updateOptionsVisibility() {
        var fmt     = outputFormat ? outputFormat.value.toLowerCase() : '';
        var isImage = imageFormats.indexOf(fmt) !== -1;
        if (qualityGroup) qualityGroup.style.display = isImage ? '' : 'none';
        if (dpiGroup)     dpiGroup.style.display     = isImage ? '' : 'none';
    }
    if (outputFormat) {
        outputFormat.addEventListener('change', updateOptionsVisibility);
        updateOptionsVisibility();
    }

    /* ── Form submit ─────────────────────────────────────────── */
    var form       = document.getElementById('batchForm');
    var resultDiv  = document.getElementById('batchResult');
    var detailDiv  = document.getElementById('batchDetails');
    var submitBtn  = document.getElementById('submitBtn');
    var dlAllBtn   = document.getElementById('downloadAllBtn');

    // Batch metadata kept for Download All
    var currentBatchId = null;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (!selectedFiles.length) {
            zone.style.borderColor = 'var(--cx-danger)';
            fileList.innerHTML = '<div style="font-size:.82rem;color:var(--cx-danger);"><i class="fa-solid fa-circle-xmark"></i>'
                               + ' <strong>Please select at least one file.</strong></div>';
            setTimeout(function () { zone.style.borderColor = ''; }, 2500);
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner" style="animation:cx-spin 1s linear infinite;"></i> Uploading…';
        if (dlAllBtn) dlAllBtn.style.display = 'none';

        var fd = new FormData();
        fd.append('_token', form.querySelector('[name="_token"]').value);
        fd.append('output_format', form.querySelector('[name="output_format"]').value);
        var webhookInput = form.querySelector('[name="webhook_url"]');
        if (webhookInput) fd.append('webhook_url', webhookInput.value);

        // AI options
        ['ai_ocr','ai_summarize','ai_translate','ai_classify'].forEach(function(n) {
            var el = form.querySelector('[name="' + n + '"]');
            if (el && el.checked) fd.append(n, '1');
        });
        var langEl = form.querySelector('[name="target_lang"]');
        if (langEl) fd.append('target_lang', langEl.value);

        // Quality / DPI
        var qEl = form.querySelector('[name="quality"]');
        if (qEl) fd.append('quality', qEl.value);
        var dEl = form.querySelector('[name="dpi"]');
        if (dEl) fd.append('dpi', dEl.value);

        selectedFiles.forEach(function (f) { fd.append('files[]', f); });

        try {
            var res  = await fetch('/projects/convertx/batch', { method: 'POST', body: fd, headers: {'Accept': 'application/json'} });
            var data = await res.json();

            resultDiv.style.display = 'block';
            resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

            if (data.success) {
                currentBatchId = data.batch_id;

                var html = '<p style="color:var(--cx-success);margin:.75rem .875rem .5rem;font-size:.88rem;">'
                         + '<i class="fa-solid fa-check-circle"></i> '
                         + data.job_ids.length + ' file(s) queued'
                         + ' <span style="color:var(--text-muted);font-size:.75rem;font-weight:400;">'
                         + '(Batch: ' + data.batch_id + ')</span></p>';

                html += '<div style="overflow-x:auto;padding:0 .25rem .875rem;">'
                      + '<table class="cx-table">'
                      + '<thead><tr><th style="min-width:200px;">File</th><th>Status</th><th>Download</th></tr></thead>'
                      + '<tbody id="batchRows"></tbody></table></div>';

                if (data.errors && data.errors.length) {
                    html += '<p style="color:var(--cx-warning);margin:.25rem .875rem .75rem;font-size:.8rem;">'
                          + '<i class="fa-solid fa-triangle-exclamation"></i> Skipped: '
                          + data.errors.map(function(e){return htmlEsc(e);}).join(', ') + '</p>';
                }

                detailDiv.innerHTML = html;
                var tbody = document.getElementById('batchRows');
                data.job_ids.forEach(function (jid) {
                    var tr = document.createElement('tr');
                    tr.id  = 'row-' + jid;
                    tr.innerHTML = '<td><span class="cx-batch-filename" id="fname-' + jid + '">Job #' + jid + '</span></td>'
                                 + '<td><span class="badge badge-pending">PENDING</span></td><td></td>';
                    tbody.appendChild(tr);
                });

                // Start polling batch status
                pollBatch(data.batch_id, data.job_ids);

            } else {
                detailDiv.innerHTML = '<p style="color:var(--cx-danger);margin:.875rem;"><i class="fa-solid fa-circle-xmark"></i> '
                                    + htmlEsc(data.error || 'Batch failed') + '</p>';
            }
        } catch (err) {
            alert('Network error: ' + err.message);
        }

        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-layer-group"></i> Start Batch Conversion';
    });

    /* ── Batch status polling ────────────────────────────────── */
    function pollBatch(batchId, jobIds) {
        var terminal   = new Set();
        var maxAttempts = 120;
        var attempts   = 0;

        var poll = async function () {
            attempts++;
            try {
                var res  = await fetch('/projects/convertx/batch/status/' + encodeURIComponent(batchId));
                var data = await res.json();
                if (!data.success) return;

                var allDone = true;

                data.jobs.forEach(function (job) {
                    var jid = job.job_id;
                    var row = document.getElementById('row-' + jid);
                    if (!row) return;

                    // Update filename cell
                    var fnameEl = document.getElementById('fname-' + jid);
                    if (fnameEl && job.input_filename) {
                        fnameEl.textContent = job.input_filename;
                        fnameEl.title       = job.input_filename;
                    }

                    var badgeClass = {
                        pending:'badge-pending', processing:'badge-processing',
                        completed:'badge-completed', failed:'badge-failed', cancelled:'badge-cancelled'
                    }[job.status] || 'badge-pending';

                    var cells = row.querySelectorAll('td');
                    if (cells[1]) cells[1].innerHTML = '<span class="badge ' + badgeClass + '">' + job.status.toUpperCase() + '</span>';

                    if (job.status === 'completed') {
                        terminal.add(jid);
                        if (cells[2]) {
                            cells[2].innerHTML = '<a href="/projects/convertx/job/' + jid + '/download"'
                                              + ' class="btn btn-success btn-sm"'
                                              + '><i class="fa-solid fa-download"></i> Download</a>';
                        }
                    } else if (job.status === 'failed') {
                        terminal.add(jid);
                        if (cells[2] && job.error_message) {
                            cells[2].innerHTML = '<span style="font-size:.73rem;color:var(--cx-danger);" title="' + htmlEsc(job.error_message) + '">'
                                              + '<i class="fa-solid fa-triangle-exclamation"></i> Failed</span>';
                        }
                    } else if (job.status === 'cancelled') {
                        terminal.add(jid);
                    }

                    if (!['completed','failed','cancelled'].includes(job.status)) {
                        allDone = false;
                    }
                });

                // Show Download All button if at least one job completed
                if (dlAllBtn && terminal.size > 0) {
                    dlAllBtn.style.display = '';
                }

                if (!allDone && attempts < maxAttempts) {
                    setTimeout(poll, 2000);
                }
            } catch (err) {
                if (attempts < maxAttempts) setTimeout(poll, 3000);
            }
        };

        poll();
    }

    /* ── Download All as ZIP ─────────────────────────────────── */
    window.downloadAllZip = function () {
        if (!currentBatchId) return;
        window.location.href = '/projects/convertx/batch/download/' + encodeURIComponent(currentBatchId);
    };

})();
</script>

