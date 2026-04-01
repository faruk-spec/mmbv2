<?php
/**
 * @var string $title
 * @var array  $user
 * @var array  $templates
 * @var array  $jobs
 * @var string $csrfToken
 */
?>
<style>
.bulk-section { margin-bottom:28px; }
.bulk-card {
    background:var(--bg-card);
    border:1px solid var(--border-color);
    border-radius:14px;
    padding:24px;
}
.tpl-select-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(140px,1fr));
    gap:10px;
    margin-top:12px;
}
.tpl-btn {
    display:flex;flex-direction:column;align-items:center;gap:6px;
    padding:14px 10px;border-radius:10px;border:2px solid var(--border-color);
    background:var(--bg-secondary);cursor:pointer;transition:all 0.2s;text-align:center;
}
.tpl-btn:hover { transform:translateY(-2px); }
.tpl-btn.active { box-shadow:0 0 0 2px var(--indigo); }
.tpl-btn .tpl-icon {
    width:36px;height:36px;border-radius:8px;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.upload-zone {
    border:2px dashed var(--border-color);border-radius:12px;
    padding:36px 20px;text-align:center;cursor:pointer;transition:all 0.2s;
    background:var(--bg-secondary);
}
.upload-zone:hover,.upload-zone.dragover { border-color:var(--indigo);background:rgba(99,102,241,0.06); }
.upload-zone input[type=file] { display:none; }
#uploadFilename { font-size:0.85rem;color:var(--indigo);margin-top:8px;font-weight:600; }
.progress-bar-wrap { background:var(--border-color);border-radius:99px;height:10px;overflow:hidden;margin-top:12px; }
.progress-bar-fill { height:100%;border-radius:99px;background:linear-gradient(90deg,#6366f1,#00f0ff);transition:width 0.4s; }
#bulkResultsWrap { margin-top:20px; }
.result-badge {
    display:inline-flex;align-items:center;gap:6px;padding:6px 14px;
    border-radius:20px;font-size:0.82rem;font-weight:600;
}
.badge-success { background:rgba(0,255,136,0.12);color:#00ff88; }
.badge-fail    { background:rgba(239,68,68,0.12);color:#ef4444; }
/* Jobs history table */
.jobs-table { width:100%;border-collapse:collapse;font-size:13px; }
.jobs-table th { text-align:left;padding:10px 12px;color:var(--text-secondary);font-weight:600;border-bottom:1px solid var(--border-color); }
.jobs-table td { padding:10px 12px;border-bottom:1px solid var(--border-color); }
.status-chip {
    display:inline-block;padding:2px 10px;border-radius:10px;
    font-size:11px;font-weight:700;letter-spacing:0.02em;
}
.status-done       { background:rgba(0,255,136,0.12);color:#00ff88; }
.status-error      { background:rgba(239,68,68,0.12);color:#ef4444; }
.status-processing { background:rgba(245,158,11,0.12);color:#f59e0b; }
.status-pending    { background:rgba(99,102,241,0.12);color:#6366f1; }
</style>

<!-- Hero -->
<div style="background:linear-gradient(135deg,rgba(99,102,241,0.12),rgba(0,240,255,0.05));
     border:1px solid rgba(99,102,241,0.2);border-radius:14px;padding:20px 24px;
     margin-bottom:24px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
    <div style="width:52px;height:52px;background:linear-gradient(135deg,#6366f1,#00f0ff);border-radius:13px;
         display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="fas fa-layer-group" style="color:#fff;font-size:1.3rem;"></i>
    </div>
    <div style="flex:1;min-width:160px;">
        <div style="font-size:1.15rem;font-weight:800;background:linear-gradient(135deg,#6366f1,#00f0ff);
             -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:3px;">
            Bulk ID Card Generator
        </div>
        <div style="font-size:0.82rem;color:var(--text-secondary);">
            Upload a CSV to generate multiple cards at once &mdash; select a category, download the sample, fill it in, and upload.
        </div>
    </div>
    <a href="/projects/idcard" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Dashboard</a>
</div>

<div style="display:grid;grid-template-columns:1fr;gap:24px;">

    <!-- Step 1: Pick template -->
    <div class="bulk-card bulk-section">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:4px;display:flex;align-items:center;gap:8px;">
            <span style="width:24px;height:24px;background:var(--indigo);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:800;flex-shrink:0;">1</span>
            Select Card Category
        </h3>
        <p style="font-size:0.8rem;color:var(--text-secondary);margin-bottom:0;">
            Choose the template for all cards in your CSV. Then download the matching sample CSV.
        </p>

        <div class="tpl-select-grid" id="tplGrid">
            <?php foreach ($templates as $key => $tpl): ?>
            <div class="tpl-btn" data-tpl="<?= htmlspecialchars($key) ?>"
                 data-color="<?= htmlspecialchars($tpl['color']) ?>"
                 onclick="selectTemplate(this)">
                <div class="tpl-icon" style="background:<?= htmlspecialchars($tpl['color']) ?>;">
                    <i class="fas fa-id-card" style="color:#fff;font-size:0.9rem;"></i>
                </div>
                <span style="font-size:0.72rem;font-weight:600;color:var(--text-primary);line-height:1.3;">
                    <?= htmlspecialchars($tpl['name']) ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top:16px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <div style="font-size:0.85rem;color:var(--text-secondary);">
                Selected: <strong id="selectedTplName" style="color:var(--indigo);">—</strong>
            </div>
            <a id="sampleCsvBtn" href="#" onclick="return false;"
               style="pointer-events:none;opacity:0.4;"
               class="btn btn-secondary btn-sm">
                <i class="fas fa-download"></i> Download Sample CSV
            </a>
        </div>
    </div>

    <!-- Step 2: Upload CSV -->
    <div class="bulk-card bulk-section">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:4px;display:flex;align-items:center;gap:8px;">
            <span style="width:24px;height:24px;background:var(--indigo);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:800;flex-shrink:0;">2</span>
            Upload Your CSV
        </h3>
        <p style="font-size:0.8rem;color:var(--text-secondary);margin-bottom:16px;">
            Fill the sample CSV with your data and upload it here. Maximum <?= (int)(($adminCfg ?? [])['max_bulk_rows'] ?? 200) ?> rows.
        </p>

        <form id="bulkForm" onsubmit="submitBulk(event)">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="template_key" id="bulkTemplateKey" value="">

            <div class="upload-zone" id="uploadZone"
                 onclick="document.getElementById('csvFileInput').click()"
                 ondragover="handleDragOver(event)"
                 ondragleave="handleDragLeave(event)"
                 ondrop="handleDrop(event)">
                <i class="fas fa-file-csv" style="font-size:2.5rem;color:var(--indigo);opacity:0.6;margin-bottom:10px;display:block;"></i>
                <div style="font-size:0.9rem;font-weight:600;color:var(--text-primary);">
                    Click to choose CSV or drag &amp; drop here
                </div>
                <div style="font-size:0.75rem;color:var(--text-secondary);margin-top:4px;">
                    Accepts .csv files only
                </div>
                <input type="file" id="csvFileInput" name="csv_file" accept=".csv,text/csv"
                       onchange="handleFileSelect(this)">
                <div id="uploadFilename"></div>
            </div>

            <div id="progressWrap" style="display:none;margin-top:16px;">
                <div style="font-size:0.8rem;color:var(--text-secondary);" id="progressLabel">Processing…</div>
                <div class="progress-bar-wrap"><div class="progress-bar-fill" id="progressBar" style="width:0%;"></div></div>
            </div>

            <div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;">
                <button type="submit" id="submitBtn" class="btn btn-primary" disabled>
                    <i class="fas fa-bolt"></i> Generate Cards
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>
        </form>

        <div id="bulkResultsWrap" style="display:none;margin-top:20px;">
            <div id="bulkResultInner"></div>
        </div>
    </div>

    <!-- Step 3: Recent Bulk Jobs -->
    <div class="bulk-card">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-history" style="color:var(--indigo);"></i> Recent Bulk Jobs
        </h3>

        <?php if (empty($jobs)): ?>
        <div style="text-align:center;padding:32px 0;color:var(--text-secondary);">
            <i class="fas fa-layer-group" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:8px;"></i>
            No bulk jobs yet.
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="jobs-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Template</th>
                        <th>Total</th>
                        <th>Completed</th>
                        <th>Failed</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td style="font-family:monospace;font-size:11px;color:var(--text-secondary);"><?= (int)$job['id'] ?></td>
                        <td>
                            <span style="background:rgba(99,102,241,0.12);color:var(--indigo);
                                         padding:2px 10px;border-radius:10px;font-size:11px;font-weight:600;">
                                <?= htmlspecialchars($job['template_key']) ?>
                            </span>
                        </td>
                        <td><?= (int)$job['total_rows'] ?></td>
                        <td style="color:#00ff88;font-weight:600;"><?= (int)$job['completed'] ?></td>
                        <td style="color:<?= $job['failed'] > 0 ? '#ef4444' : 'var(--text-secondary)' ?>;font-weight:<?= $job['failed'] > 0 ? '600' : '400' ?>;">
                            <?= (int)$job['failed'] ?>
                        </td>
                        <td>
                            <span class="status-chip status-<?= htmlspecialchars($job['status']) ?>">
                                <?= htmlspecialchars(ucfirst($job['status'])) ?>
                            </span>
                        </td>
                        <td style="font-size:12px;color:var(--text-secondary);">
                            <?= date('d M Y, H:i', strtotime($job['created_at'])) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <div style="margin-top:14px;text-align:right;">
            <a href="/projects/idcard/history" class="btn btn-secondary btn-sm">
                <i class="fas fa-id-card"></i> View All My Cards
            </a>
        </div>
    </div>

</div>

<script>
var selectedTemplate = '';

function selectTemplate(el) {
    // Deactivate others
    document.querySelectorAll('.tpl-btn').forEach(function(b) {
        b.classList.remove('active');
        b.style.borderColor = 'var(--border-color)';
    });
    el.classList.add('active');
    el.style.borderColor = el.dataset.color;

    selectedTemplate = el.dataset.tpl;
    document.getElementById('bulkTemplateKey').value = selectedTemplate;
    document.getElementById('selectedTplName').textContent = el.querySelector('span').textContent.trim();

    var sampleBtn = document.getElementById('sampleCsvBtn');
    sampleBtn.href = '/projects/idcard/bulk/sample-csv?template=' + encodeURIComponent(selectedTemplate);
    sampleBtn.style.pointerEvents = 'auto';
    sampleBtn.style.opacity = '1';

    updateSubmitState();
}

var fileSelected = false;

function handleFileSelect(input) {
    if (input.files && input.files[0]) {
        document.getElementById('uploadFilename').textContent = '📎 ' + input.files[0].name;
        fileSelected = true;
        updateSubmitState();
    }
}

function handleDragOver(e) {
    e.preventDefault();
    document.getElementById('uploadZone').classList.add('dragover');
}

function handleDragLeave(e) {
    document.getElementById('uploadZone').classList.remove('dragover');
}

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('uploadZone').classList.remove('dragover');
    var input = document.getElementById('csvFileInput');
    if (e.dataTransfer.files.length) {
        input.files = e.dataTransfer.files;
        handleFileSelect(input);
    }
}

function updateSubmitState() {
    document.getElementById('submitBtn').disabled = !(selectedTemplate && fileSelected);
}

function resetForm() {
    document.getElementById('bulkForm').reset();
    document.getElementById('uploadFilename').textContent = '';
    fileSelected = false;
    selectedTemplate = '';
    document.getElementById('bulkTemplateKey').value = '';
    document.getElementById('selectedTplName').textContent = '—';
    document.getElementById('progressWrap').style.display = 'none';
    document.getElementById('bulkResultsWrap').style.display = 'none';
    var sampleBtn = document.getElementById('sampleCsvBtn');
    sampleBtn.href = '#';
    sampleBtn.style.pointerEvents = 'none';
    sampleBtn.style.opacity = '0.4';
    document.querySelectorAll('.tpl-btn').forEach(function(b) {
        b.classList.remove('active');
        b.style.borderColor = 'var(--border-color)';
    });
    updateSubmitState();
}

function submitBulk(e) {
    e.preventDefault();

    if (!selectedTemplate) {
        alert('Please select a card category first.');
        return;
    }
    var fileInput = document.getElementById('csvFileInput');
    if (!fileInput.files || !fileInput.files[0]) {
        alert('Please select a CSV file.');
        return;
    }

    var submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating…';

    var progressWrap = document.getElementById('progressWrap');
    progressWrap.style.display = 'block';
    document.getElementById('progressBar').style.width = '30%';
    document.getElementById('progressLabel').textContent = 'Uploading and processing CSV…';

    var fd = new FormData(document.getElementById('bulkForm'));

    fetch('/projects/idcard/bulk/upload', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        document.getElementById('progressBar').style.width = '100%';
        document.getElementById('progressLabel').textContent = data.message || (data.success ? 'Done!' : 'Error');

        var wrap = document.getElementById('bulkResultsWrap');
        wrap.style.display = 'block';

        if (data.success) {
            document.getElementById('bulkResultInner').innerHTML =
                '<div style="padding:16px;background:rgba(0,255,136,0.06);border:1px solid rgba(0,255,136,0.2);border-radius:10px;">' +
                '<div style="font-weight:700;color:#00ff88;margin-bottom:6px;font-size:1rem;"><i class="fas fa-check-circle"></i> Generation Complete!</div>' +
                '<div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">' +
                '<span class="result-badge badge-success"><i class="fas fa-id-card"></i> ' + data.completed + ' cards created</span>' +
                (data.failed > 0 ? '<span class="result-badge badge-fail"><i class="fas fa-exclamation-triangle"></i> ' + data.failed + ' rows skipped</span>' : '') +
                '</div>' +
                '<div style="margin-top:12px;">' +
                '<a href="/projects/idcard/history" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> View Generated Cards</a>' +
                '</div>' +
                '</div>';
            // Reload jobs table after short delay
            setTimeout(function() { window.location.reload(); }, 3000);
        } else {
            document.getElementById('bulkResultInner').innerHTML =
                '<div style="padding:14px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.2);border-radius:10px;color:#ef4444;">' +
                '<i class="fas fa-times-circle"></i> ' + (data.message || 'An error occurred.') +
                '</div>';
        }

        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-bolt"></i> Generate Cards';
    })
    .catch(function(err) {
        document.getElementById('progressLabel').textContent = 'Request failed.';
        document.getElementById('bulkResultsWrap').style.display = 'block';
        document.getElementById('bulkResultInner').innerHTML =
            '<div style="padding:14px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.2);border-radius:10px;color:#ef4444;">' +
            '<i class="fas fa-times-circle"></i> Network error. Please try again.' +
            '</div>';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-bolt"></i> Generate Cards';
    });
}
</script>
