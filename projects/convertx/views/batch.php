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

<div class="cx-batch-grid">

    <!-- Left: file upload -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-files"></i> Select Files
        </div>

        <form id="batchForm" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

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
        <!-- form continues right column -->

    </div><!-- left card -->

    <!-- Right: output options + submit -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-file-export"></i> Output Options
        </div>

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

<div id="batchResult" class="card" style="display:none;margin-top:1.25rem;">
    <div class="card-header"><i class="fa-solid fa-list-check"></i> Batch Job Status</div>
    <div id="batchDetails"></div>
</div>

<style>
.cx-batch-grid {
    display: grid;
    grid-template-columns: 1.1fr 1fr;
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
</style>

<script>
(function () {
    var zone     = document.getElementById('uploadZone');
    var input    = document.getElementById('fileInput');
    var fileList = document.getElementById('fileList');
    var MAX_FILES = 50;

    // Maintain our own array of selected files
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
        // Reset input so same file can be re-added after removal
        input.value = '';
    });

    function addFiles(files) {
        files.forEach(function (f) {
            // Prevent duplicates by name + size + lastModified
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

    var form      = document.getElementById('batchForm');
    var resultDiv = document.getElementById('batchResult');
    var detailDiv = document.getElementById('batchDetails');
    var submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        // Client-side validation
        if (!selectedFiles.length) {
            zone.style.borderColor = 'var(--cx-danger)';
            zone.style.animation   = 'none';
            fileList.innerHTML = '<div style="font-size:.82rem;color:var(--cx-danger);"><i class="fa-solid fa-circle-xmark"></i>'
                               + ' <strong>Please select at least one file.</strong></div>';
            setTimeout(function () { zone.style.borderColor = ''; zone.style.animation = ''; }, 2500);
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner" style="animation:cx-spin 1s linear infinite;"></i> Uploading…';

        var fd = new FormData();
        fd.append('_token', form.querySelector('[name="_token"]').value);
        fd.append('output_format', form.querySelector('[name="output_format"]').value);
        var webhookInput = form.querySelector('[name="webhook_url"]');
        if (webhookInput) fd.append('webhook_url', webhookInput.value);
        selectedFiles.forEach(function (f) { fd.append('files[]', f); });

        try {
            var res  = await fetch('/projects/convertx/batch', { method: 'POST', body: fd });
            var data = await res.json();

            resultDiv.style.display = 'block';
            resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

            if (data.success) {
                var html = '<p style="color:var(--cx-success);margin-bottom:.75rem;"><i class="fa-solid fa-check-circle"></i> '
                         + data.job_ids.length + ' job(s) queued (Batch: ' + data.batch_id + ')</p>';
                html += '<div style="overflow-x:auto;"><table class="cx-table"><thead><tr><th>Job</th><th>Status</th><th>Action</th></tr></thead><tbody id="batchRows">';
                data.job_ids.forEach(function (jid) {
                    html += '<tr id="row-' + jid + '"><td style="color:var(--text-primary);">#' + jid + '</td><td><span class="badge badge-pending">PENDING</span></td><td></td></tr>';
                });
                html += '</tbody></table></div>';
                if (data.errors && data.errors.length) {
                    html += '<p style="color:var(--cx-warning);margin-top:.5rem;font-size:.8rem;"><i class="fa-solid fa-triangle-exclamation"></i> Skipped: ' + data.errors.join(', ') + '</p>';
                }
                detailDiv.innerHTML = html;
                data.job_ids.forEach(function (jid) { pollJob(jid); });
            } else {
                detailDiv.innerHTML = '<p style="color:var(--cx-danger);"><i class="fa-solid fa-circle-xmark"></i> ' + (data.error || 'Batch failed') + '</p>';
            }
        } catch (err) {
            alert('Network error: ' + err.message);
        }
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-layer-group"></i> Start Batch Conversion';
    });

    async function pollJob(jobId) {
        var attempts = 0;
        var poll = async function () {
            attempts++;
            try {
                var res  = await fetch('/projects/convertx/job/' + jobId);
                var data = await res.json();
                var row  = document.getElementById('row-' + jobId);
                if (!row) return;
                var badgeClass = {
                    pending:'badge-pending', processing:'badge-processing',
                    completed:'badge-completed', failed:'badge-failed', cancelled:'badge-cancelled'
                }[data.status] || 'badge-pending';
                var action = '';
                if (data.status === 'completed') {
                    action = '<a href="/projects/convertx/job/' + jobId + '/download" class="btn btn-success btn-sm"><i class="fa-solid fa-download"></i> Download</a>';
                }
                row.innerHTML = '<td style="color:var(--text-primary);">#' + jobId + '</td><td><span class="badge ' + badgeClass + '">' + data.status.toUpperCase() + '</span></td><td>' + action + '</td>';
                if (!['completed','failed','cancelled'].includes(data.status) && attempts < 120) {
                    setTimeout(poll, 2000);
                }
            } catch (err) {
                if (attempts < 120) setTimeout(poll, 3000);
            }
        };
        poll();
    }
})();
</script>
