<?php
/**
 * ConvertX – Convert File View
 */
$currentView = 'convert';
$csrfToken   = \Core\Security::generateCsrfToken();

// Flatten all supported formats
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
    <div class="card-header"><i class="fa-solid fa-arrow-right-arrow-left"></i> Convert a File</div>

    <form id="convertForm" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <!-- Upload zone -->
        <div class="form-group">
            <label>Upload File</label>
            <div class="upload-zone" id="uploadZone">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <p>Drag &amp; drop or <strong>click to browse</strong></p>
                <p style="margin-top:.4rem;font-size:.8rem;color:var(--text-muted);">Supports PDF, DOCX, XLSX, PPTX, images and more</p>
                <input type="file" name="file" id="fileInput" style="display:none;" accept="<?= implode(',', array_map(fn($f) => '.' . $f, $allFormats)) ?>">
            </div>
            <div id="selectedFile" style="margin-top:.5rem;font-size:.875rem;color:var(--cx-success);display:none;"></div>
        </div>

        <!-- Output format -->
        <div class="form-group">
            <label for="outputFormat">Convert To</label>
            <select class="form-control" id="outputFormat" name="output_format" required>
                <option value="">— Select output format —</option>
                <?php foreach ($allFormats as $fmt): ?>
                    <option value="<?= htmlspecialchars($fmt) ?>"><?= strtoupper(htmlspecialchars($fmt)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- AI features (collapsible) -->
        <details style="margin-bottom:1.25rem;">
            <summary style="cursor:pointer;color:var(--cx-primary);font-size:.875rem;font-weight:500;padding:.5rem 0;">
                <i class="fa-solid fa-wand-magic-sparkles"></i> AI Enhancement Options
            </summary>
            <div style="padding:.75rem 0 0 1rem;display:flex;flex-direction:column;gap:.75rem;">
                <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;font-size:.875rem;">
                    <input type="checkbox" name="ai_ocr" value="1">
                    Enable OCR (extract text from scanned content)
                </label>
                <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;font-size:.875rem;">
                    <input type="checkbox" name="ai_summarize" value="1">
                    Summarize document
                </label>
                <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;font-size:.875rem;" id="translateToggle">
                    <input type="checkbox" name="ai_translate" value="1" id="translateCheck">
                    Translate document
                </label>
                <div id="langSelect" style="display:none;margin-left:1.5rem;">
                    <select class="form-control" name="target_lang" style="width:200px;">
                        <option value="fr">French</option>
                        <option value="de">German</option>
                        <option value="es">Spanish</option>
                        <option value="ar">Arabic</option>
                        <option value="zh">Chinese</option>
                        <option value="ja">Japanese</option>
                        <option value="pt">Portuguese</option>
                        <option value="it">Italian</option>
                    </select>
                </div>
                <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;font-size:.875rem;">
                    <input type="checkbox" name="ai_classify" value="1">
                    Classify document type
                </label>
            </div>
        </details>

        <!-- Webhook -->
        <details style="margin-bottom:1.25rem;">
            <summary style="cursor:pointer;color:var(--text-muted);font-size:.8rem;padding:.5rem 0;">
                Webhook callback URL (optional)
            </summary>
            <div style="padding:.5rem 0 0;">
                <input type="url" class="form-control" name="webhook_url" placeholder="https://yourapp.com/webhook">
            </div>
        </details>

        <button type="submit" class="btn btn-primary" id="submitBtn">
            <i class="fa-solid fa-arrow-right-arrow-left"></i> Start Conversion
        </button>
    </form>
</div>

<!-- Job status card (shown after submission) -->
<div id="jobStatus" class="card" style="max-width:720px;display:none;">
    <div class="card-header"><i class="fa-solid fa-spinner fa-spin"></i> Job Progress</div>
    <div id="jobDetails"></div>
</div>

<script>
(function () {
    // Drag & drop upload zone
    const zone  = document.getElementById('uploadZone');
    const input = document.getElementById('fileInput');
    const label = document.getElementById('selectedFile');

    zone.addEventListener('click', () => input.click());
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
    zone.addEventListener('drop', e => {
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
            label.textContent = '✓ ' + input.files[0].name;
            label.style.display = 'block';
        }
    }

    // Translate language picker
    const translateCheck = document.getElementById('translateCheck');
    const langSelect     = document.getElementById('langSelect');
    translateCheck.addEventListener('change', () => {
        langSelect.style.display = translateCheck.checked ? 'block' : 'none';
    });

    // Form submission
    const form      = document.getElementById('convertForm');
    const statusDiv = document.getElementById('jobStatus');
    const detailDiv = document.getElementById('jobDetails');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', async e => {
        e.preventDefault();
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uploading…';

        const fd = new FormData(form);
        try {
            const res  = await fetch('/projects/convertx/convert', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                statusDiv.style.display = 'block';
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
        let attempts = 0;
        const maxAttempts = 120; // 2 minutes at 1s interval

        const poll = async () => {
            attempts++;
            try {
                const res  = await fetch('/projects/convertx/job/' + jobId);
                const data = await res.json();

                renderStatus(data, jobId);

                if (data.status === 'completed' || data.status === 'failed' || data.status === 'cancelled') {
                    return; // stop polling
                }
                if (attempts < maxAttempts) {
                    setTimeout(poll, 1500);
                }
            } catch (err) {
                setTimeout(poll, 3000);
            }
        };
        poll();
    }

    function renderStatus(data, jobId) {
        const badgeClass = {
            pending:    'badge-pending',
            processing: 'badge-processing',
            completed:  'badge-completed',
            failed:     'badge-failed',
            cancelled:  'badge-cancelled',
        }[data.status] || 'badge-pending';

        let html = '<p>Job #' + jobId + ' &nbsp; <span class="badge ' + badgeClass + '">' + data.status.toUpperCase() + '</span></p>';

        if (data.status === 'completed') {
            html += '<div style="margin-top:1rem;">';
            html += '<a href="/projects/convertx/job/' + jobId + '/download" class="btn btn-success"><i class="fa-solid fa-download"></i> Download ' + (data.output_filename || 'File') + '</a>';
            html += '</div>';

            if (data.ai_result) {
                html += '<div style="margin-top:1rem;">';
                html += '<strong style="font-size:.875rem;">AI Results:</strong>';
                if (data.ai_result.ocr)        html += '<p style="font-size:.8rem;margin-top:.5rem;"><strong>OCR:</strong> ' + (data.ai_result.ocr.text || '').substring(0, 300) + '…</p>';
                if (data.ai_result.summarize)  html += '<p style="font-size:.8rem;margin-top:.5rem;"><strong>Summary:</strong> ' + (data.ai_result.summarize.summary || '') + '</p>';
                if (data.ai_result.classify)   html += '<p style="font-size:.8rem;margin-top:.5rem;"><strong>Category:</strong> ' + (data.ai_result.classify.category || '') + ' (' + Math.round((data.ai_result.classify.confidence || 0) * 100) + '%)</p>';
                if (data.ai_result.translate)  html += '<p style="font-size:.8rem;margin-top:.5rem;"><strong>Translation (excerpt):</strong> ' + (data.ai_result.translate.translated || '').substring(0, 300) + '…</p>';
                html += '</div>';
            }
        }

        if (data.status === 'failed') {
            html += '<p style="color:var(--cx-danger);margin-top:.5rem;font-size:.875rem;">' + (data.error_message || 'Conversion failed') + '</p>';
        }

        detailDiv.innerHTML = html;
    }
})();
</script>
