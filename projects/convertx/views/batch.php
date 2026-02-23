<?php
/**
 * ConvertX – Batch Conversion View
 */
$currentView = 'batch';
$csrfToken   = \Core\Security::generateCsrfToken();

$allFormats = [];
foreach (($formats ?? []) as $formats_list) {
    foreach ($formats_list as $fmt) {
        $allFormats[] = $fmt;
    }
}
$allFormats = array_unique($allFormats);
sort($allFormats);
?>

<!-- Page header -->
<div class="page-header" style="margin-bottom:1.5rem;text-align:center;">
    <h1 style="font-size:2rem;font-weight:700;background:linear-gradient(135deg,var(--cx-primary),var(--cx-accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
        Batch Convert
    </h1>
    <p style="color:var(--text-secondary);margin-top:.4rem;">
        Upload up to 50 files — convert them all to the same format in one go
    </p>
</div>

<div style="max-width:760px;">
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-layer-group"></i> Batch Conversion Settings
        </div>

        <form id="batchForm" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-files" style="color:var(--cx-primary);"></i> Select Files
                    <span style="font-size:.75rem;font-weight:400;color:var(--text-secondary);margin-left:.5rem;">Max 50 files per batch</span>
                </label>
                <div class="upload-zone" id="uploadZone">
                    <i class="fa-solid fa-files"></i>
                    <p style="font-weight:600;margin-bottom:.25rem;">Drag &amp; drop files or <strong style="color:var(--cx-primary);">click to browse</strong></p>
                    <p style="margin-top:.3rem;font-size:.78rem;color:var(--text-secondary);">Select multiple files at once</p>
                    <input type="file" name="files[]" id="fileInput" multiple style="display:none;"
                           accept="<?= implode(',', array_map(fn($f) => '.' . $f, $allFormats)) ?>">
                </div>
                <div id="fileList" style="margin-top:.5rem;font-size:.8rem;color:var(--cx-success);"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="outputFormat">
                    <i class="fa-solid fa-file-export" style="color:var(--cx-primary);"></i> Convert All To
                </label>
                <select class="form-control" id="outputFormat" name="output_format" required>
                    <option value="">— Select output format —</option>
                    <?php foreach ($allFormats as $fmt): ?>
                        <option value="<?= htmlspecialchars($fmt) ?>"><?= strtoupper(htmlspecialchars($fmt)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <details style="margin-bottom:1.25rem;">
                <summary style="cursor:pointer;color:var(--text-secondary);font-size:.8rem;padding:.5rem 0;display:flex;align-items:center;gap:.4rem;list-style:none;">
                    <i class="fa-solid fa-link"></i> Webhook callback URL <span style="font-size:.7rem;opacity:.7;">(optional)</span>
                </summary>
                <div style="padding:.5rem 0 0;">
                    <input type="url" class="form-control" name="webhook_url" placeholder="https://yourapp.com/webhook">
                </div>
            </details>

            <button type="submit" class="btn btn-primary" id="submitBtn" style="width:100%;justify-content:center;padding:.875rem;">
                <i class="fa-solid fa-layer-group"></i> Start Batch Conversion
            </button>
        </form>
    </div>

    <div id="batchResult" class="card" style="display:none;">
        <div class="card-header"><i class="fa-solid fa-list-check"></i> Batch Job Status</div>
        <div id="batchDetails"></div>
    </div>
</div>

<script>
(function () {
    const zone     = document.getElementById('uploadZone');
    const input    = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');

    zone.addEventListener('click', () => input.click());
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
    zone.addEventListener('drop', e => {
        e.preventDefault(); zone.classList.remove('drag-over');
        fileList.innerHTML = '<i class="fa-solid fa-circle-info"></i> ' + e.dataTransfer.files.length + ' file(s) ready — use Browse to confirm selection';
    });
    input.addEventListener('change', () => {
        if (input.files.length) {
            fileList.innerHTML = '<i class="fa-solid fa-check-circle" style="color:var(--cx-success);"></i> ' + input.files.length + ' file(s) selected';
        }
    });

    const form      = document.getElementById('batchForm');
    const resultDiv = document.getElementById('batchResult');
    const detailDiv = document.getElementById('batchDetails');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', async e => {
        e.preventDefault();
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner" style="animation:cx-spin 1s linear infinite;"></i> Uploading…';

        const fd = new FormData(form);
        try {
            const res  = await fetch('/projects/convertx/batch', { method: 'POST', body: fd });
            const data = await res.json();

            resultDiv.style.display = 'block';
            resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

            if (data.success) {
                let html = '<p style="color:var(--cx-success);margin-bottom:.75rem;"><i class="fa-solid fa-check-circle"></i> ' + data.job_ids.length + ' job(s) queued (Batch: ' + data.batch_id + ')</p>';
                html += '<table class="cx-table"><thead><tr><th>Job</th><th>Status</th><th>Action</th></tr></thead><tbody id="batchRows">';
                data.job_ids.forEach(jid => {
                    html += '<tr id="row-' + jid + '"><td>#' + jid + '</td><td><span class="badge badge-pending">PENDING</span></td><td></td></tr>';
                });
                html += '</tbody></table>';
                if (data.errors && data.errors.length) {
                    html += '<p style="color:var(--cx-warning);margin-top:.5rem;font-size:.8rem;"><i class="fa-solid fa-triangle-exclamation"></i> Skipped: ' + data.errors.join(', ') + '</p>';
                }
                detailDiv.innerHTML = html;
                data.job_ids.forEach(jid => pollJob(jid));
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
        let attempts = 0;
        const poll = async () => {
            attempts++;
            try {
                const res  = await fetch('/projects/convertx/job/' + jobId);
                const data = await res.json();
                const row  = document.getElementById('row-' + jobId);
                if (!row) return;
                const badgeClass = {
                    pending:'badge-pending', processing:'badge-processing',
                    completed:'badge-completed', failed:'badge-failed', cancelled:'badge-cancelled'
                }[data.status] || 'badge-pending';
                let action = '';
                if (data.status === 'completed') {
                    action = '<a href="/projects/convertx/job/' + jobId + '/download" class="btn btn-success btn-sm"><i class="fa-solid fa-download"></i> Download</a>';
                }
                row.innerHTML = '<td>#' + jobId + '</td><td><span class="badge ' + badgeClass + '">' + data.status.toUpperCase() + '</span></td><td>' + action + '</td>';
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
