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

<div class="card" style="max-width:720px;">
    <div class="card-header"><i class="fa-solid fa-layer-group"></i> Batch Convert Files</div>
    <p style="font-size:.875rem;color:var(--text-muted);margin-bottom:1.25rem;">
        Upload multiple files at once — all will be converted to the same output format.
    </p>

    <form id="batchForm" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <div class="form-group">
            <label>Files (select multiple)</label>
            <div class="upload-zone" id="uploadZone">
                <i class="fa-solid fa-files"></i>
                <p>Drag &amp; drop files or <strong>click to browse</strong></p>
                <p style="margin-top:.4rem;font-size:.8rem;color:var(--text-muted);">Maximum 50 files per batch</p>
                <input type="file" name="files[]" id="fileInput" multiple style="display:none;"
                       accept="<?= implode(',', array_map(fn($f) => '.' . $f, $allFormats)) ?>">
            </div>
            <div id="fileList" style="margin-top:.5rem;font-size:.8rem;color:var(--cx-success);"></div>
        </div>

        <div class="form-group">
            <label for="outputFormat">Convert All To</label>
            <select class="form-control" id="outputFormat" name="output_format" required>
                <option value="">— Select output format —</option>
                <?php foreach ($allFormats as $fmt): ?>
                    <option value="<?= htmlspecialchars($fmt) ?>"><?= strtoupper(htmlspecialchars($fmt)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <details style="margin-bottom:1.25rem;">
            <summary style="cursor:pointer;color:var(--text-muted);font-size:.8rem;padding:.5rem 0;">
                Webhook callback URL (optional)
            </summary>
            <div style="padding:.5rem 0 0;">
                <input type="url" class="form-control" name="webhook_url" placeholder="https://yourapp.com/webhook">
            </div>
        </details>

        <button type="submit" class="btn btn-primary" id="submitBtn">
            <i class="fa-solid fa-layer-group"></i> Start Batch Conversion
        </button>
    </form>
</div>

<div id="batchResult" class="card" style="max-width:720px;display:none;">
    <div class="card-header"><i class="fa-solid fa-list-check"></i> Batch Job Status</div>
    <div id="batchDetails"></div>
</div>

<script>
(function () {
    const zone    = document.getElementById('uploadZone');
    const input   = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');

    zone.addEventListener('click', () => input.click());
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
    zone.addEventListener('drop', e => {
        e.preventDefault(); zone.classList.remove('drag-over');
        // We cannot programmatically set input.files for security reasons;
        // just show a notice
        fileList.textContent = e.dataTransfer.files.length + ' file(s) ready (drag & drop preview only — please use Browse to select)';
    });
    input.addEventListener('change', () => {
        if (input.files.length) {
            fileList.textContent = '✓ ' + input.files.length + ' file(s) selected';
        }
    });

    const form      = document.getElementById('batchForm');
    const resultDiv = document.getElementById('batchResult');
    const detailDiv = document.getElementById('batchDetails');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', async e => {
        e.preventDefault();
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uploading…';

        const fd = new FormData(form);
        try {
            const res  = await fetch('/projects/convertx/batch', { method: 'POST', body: fd });
            const data = await res.json();

            resultDiv.style.display = 'block';

            if (data.success) {
                let html = '<p style="color:var(--cx-success);margin-bottom:.75rem;">✓ ' + data.job_ids.length + ' job(s) queued (Batch: ' + data.batch_id + ')</p>';
                html += '<table class="cx-table"><thead><tr><th>Job ID</th><th>Status</th><th></th></tr></thead><tbody id="batchRows">';
                data.job_ids.forEach(jid => {
                    html += '<tr id="row-' + jid + '"><td>#' + jid + '</td><td><span class="badge badge-pending">PENDING</span></td><td></td></tr>';
                });
                html += '</tbody></table>';

                if (data.errors && data.errors.length) {
                    html += '<p style="color:var(--cx-warning);margin-top:.5rem;font-size:.8rem;">Skipped: ' + data.errors.join(', ') + '</p>';
                }

                detailDiv.innerHTML = html;
                data.job_ids.forEach(jid => pollJob(jid));
            } else {
                detailDiv.innerHTML = '<p style="color:var(--cx-danger);">' + (data.error || 'Batch failed') + '</p>';
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
                    pending: 'badge-pending', processing: 'badge-processing',
                    completed: 'badge-completed', failed: 'badge-failed', cancelled: 'badge-cancelled'
                }[data.status] || 'badge-pending';

                let actionCell = '';
                if (data.status === 'completed') {
                    actionCell = '<a href="/projects/convertx/job/' + jobId + '/download" class="btn btn-success" style="padding:.3rem .6rem;font-size:.75rem;"><i class="fa-solid fa-download"></i> Download</a>';
                }
                row.innerHTML = '<td>#' + jobId + '</td><td><span class="badge ' + badgeClass + '">' + data.status.toUpperCase() + '</span></td><td>' + actionCell + '</td>';

                if (!['completed', 'failed', 'cancelled'].includes(data.status) && attempts < 120) {
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
