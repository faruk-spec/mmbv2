<?php
/**
 * ConvertX – Convert File View
 */
$currentView = 'convert';
$csrfToken   = \Core\Security::generateCsrfToken();

// Preset AI quick-enable from dashboard clicks – validate against allowed values
$allowedPresets = ['ocr', 'summarize', 'translate', 'classify'];
$presetAi = in_array($_GET['ai'] ?? '', $allowedPresets, true) ? ($_GET['ai']) : '';

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

<!-- Page header -->
<div class="page-header" style="margin-bottom:1.5rem;text-align:center;">
    <h1 style="font-size:2rem;font-weight:700;background:linear-gradient(135deg,var(--cx-primary),var(--cx-accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
        Convert a File
    </h1>
    <p style="color:var(--text-secondary);margin-top:.4rem;">
        Upload any document, image or spreadsheet and convert it instantly
    </p>
</div>

<?php if ($presetAi): ?>
<div style="background:linear-gradient(135deg,rgba(99,102,241,.1),rgba(139,92,246,.08));border:2px solid rgba(99,102,241,.35);border-radius:.75rem;padding:.875rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.75rem;animation:cx-slide-down 0.4s ease both;">
    <div style="width:36px;height:36px;background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));border-radius:.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="fa-solid fa-wand-magic-sparkles" style="color:#fff;font-size:.9rem;"></i>
    </div>
    <div>
        <div style="font-weight:600;font-size:.9rem;">
            <i class="fa-solid fa-check-circle" style="color:var(--cx-success);"></i>
            AI preset applied: <em><?= htmlspecialchars(ucfirst($presetAi)) ?></em>
        </div>
        <div style="font-size:.78rem;color:var(--text-secondary);">AI enhancement has been pre-selected for you</div>
    </div>
    <a href="/projects/convertx/convert" style="margin-left:auto;padding:.4rem .875rem;background:rgba(255,255,255,.08);border:1px solid var(--border-color);border-radius:.4rem;text-decoration:none;font-size:.8rem;font-weight:500;color:var(--text-primary);transition:all .2s;"
       onmouseover="this.style.background='rgba(255,255,255,.14)'" onmouseout="this.style.background='rgba(255,255,255,.08)'">
        Clear
    </a>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr;gap:1.5rem;max-width:760px;">

    <!-- Main conversion card -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-arrow-right-arrow-left"></i> Conversion Settings
        </div>

        <form id="convertForm" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <!-- Upload zone -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-file-arrow-up" style="color:var(--cx-primary);"></i> Upload File
                </label>
                <div class="upload-zone" id="uploadZone">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <p style="font-weight:600;margin-bottom:.25rem;">Drag &amp; drop or <strong style="color:var(--cx-primary);">click to browse</strong></p>
                    <p style="margin-top:.3rem;font-size:.78rem;color:var(--text-secondary);">Supports PDF, DOCX, XLSX, PPTX, PNG, JPG and more</p>
                    <input type="file" name="file" id="fileInput" style="display:none;"
                           accept="<?= implode(',', array_map(fn($f) => '.' . $f, $allFormats)) ?>">
                </div>
                <div id="selectedFile" style="margin-top:.5rem;font-size:.875rem;color:var(--cx-success);display:none;"></div>
            </div>

            <!-- Output format -->
            <div class="form-group">
                <label class="form-label" for="outputFormat">
                    <i class="fa-solid fa-file-export" style="color:var(--cx-primary);"></i> Convert To
                </label>
                <select class="form-control" id="outputFormat" name="output_format" required>
                    <option value="">— Select output format —</option>
                    <?php foreach ($allFormats as $fmt): ?>
                        <option value="<?= htmlspecialchars($fmt) ?>"><?= strtoupper(htmlspecialchars($fmt)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- AI enhancement panel -->
            <div style="background:linear-gradient(135deg,rgba(99,102,241,.07),rgba(139,92,246,.05));border:1px solid rgba(99,102,241,.25);border-radius:.625rem;padding:1rem 1.25rem;margin-bottom:1.25rem;">
                <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.875rem;cursor:pointer;" onclick="document.getElementById('aiOptions').style.display=document.getElementById('aiOptions').style.display==='none'?'block':'none'">
                    <i class="fa-solid fa-wand-magic-sparkles" style="color:var(--cx-primary);"></i>
                    <span style="font-size:.875rem;font-weight:600;color:var(--text-primary);">AI Enhancement Options</span>
                    <span class="ai-badge" style="margin-left:.5rem;">✨ AI</span>
                    <i class="fa-solid fa-chevron-down" style="margin-left:auto;font-size:.75rem;color:var(--text-secondary);"></i>
                </div>
                <div id="aiOptions">
                    <div style="display:flex;flex-direction:column;gap:.625rem;">
                        <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;font-size:.875rem;padding:.5rem .625rem;border-radius:.375rem;transition:background .15s;"
                               onmouseover="this.style.background='rgba(99,102,241,.08)'" onmouseout="this.style.background=''">
                            <input type="checkbox" name="ai_ocr" value="1" <?= $presetAi === 'ocr' ? 'checked' : '' ?> style="accent-color:var(--cx-primary);width:1rem;height:1rem;">
                            <i class="fa-solid fa-eye" style="color:var(--cx-primary);width:1rem;"></i>
                            <span>OCR — extract text from scanned content</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;font-size:.875rem;padding:.5rem .625rem;border-radius:.375rem;transition:background .15s;"
                               onmouseover="this.style.background='rgba(99,102,241,.08)'" onmouseout="this.style.background=''">
                            <input type="checkbox" name="ai_summarize" value="1" <?= $presetAi === 'summarize' ? 'checked' : '' ?> style="accent-color:var(--cx-primary);width:1rem;height:1rem;">
                            <i class="fa-solid fa-list-check" style="color:var(--cx-primary);width:1rem;"></i>
                            <span>Summarize document</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;font-size:.875rem;padding:.5rem .625rem;border-radius:.375rem;transition:background .15s;"
                               onmouseover="this.style.background='rgba(99,102,241,.08)'" onmouseout="this.style.background=''">
                            <input type="checkbox" name="ai_translate" value="1" id="translateCheck" <?= $presetAi === 'translate' ? 'checked' : '' ?> style="accent-color:var(--cx-primary);width:1rem;height:1rem;">
                            <i class="fa-solid fa-language" style="color:var(--cx-primary);width:1rem;"></i>
                            <span>Translate document</span>
                        </label>
                        <div id="langSelect" style="<?= $presetAi === 'translate' ? '' : 'display:none;' ?>margin-left:2.25rem;">
                            <select class="form-control" name="target_lang" style="width:200px;font-size:.85rem;">
                                <option value="fr">🇫🇷 French</option>
                                <option value="de">🇩🇪 German</option>
                                <option value="es">🇪🇸 Spanish</option>
                                <option value="ar">🇸🇦 Arabic</option>
                                <option value="zh">🇨🇳 Chinese</option>
                                <option value="ja">🇯🇵 Japanese</option>
                                <option value="pt">🇵🇹 Portuguese</option>
                                <option value="it">🇮🇹 Italian</option>
                            </select>
                        </div>
                        <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;font-size:.875rem;padding:.5rem .625rem;border-radius:.375rem;transition:background .15s;"
                               onmouseover="this.style.background='rgba(99,102,241,.08)'" onmouseout="this.style.background=''">
                            <input type="checkbox" name="ai_classify" value="1" <?= $presetAi === 'classify' ? 'checked' : '' ?> style="accent-color:var(--cx-primary);width:1rem;height:1rem;">
                            <i class="fa-solid fa-tags" style="color:var(--cx-primary);width:1rem;"></i>
                            <span>Classify document type</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Webhook (collapsible) -->
            <details style="margin-bottom:1.25rem;">
                <summary style="cursor:pointer;color:var(--text-secondary);font-size:.8rem;padding:.5rem 0;list-style:none;display:flex;align-items:center;gap:.4rem;">
                    <i class="fa-solid fa-link"></i> Webhook callback URL <span style="font-size:.7rem;opacity:.7;">(optional)</span>
                </summary>
                <div style="padding:.5rem 0 0;">
                    <input type="url" class="form-control" name="webhook_url" placeholder="https://yourapp.com/webhook">
                </div>
            </details>

            <button type="submit" class="btn btn-primary" id="submitBtn" style="width:100%;justify-content:center;padding:.875rem;">
                <i class="fa-solid fa-arrow-right-arrow-left"></i> Start Conversion
            </button>
        </form>
    </div>

    <!-- Job status card (shown after submission) -->
    <div id="jobStatus" class="card" style="display:none;">
        <div class="card-header" id="jobStatusHeader">
            <i class="fa-solid fa-spinner" style="animation:cx-spin 1s linear infinite;"></i> Job Progress
        </div>
        <div id="jobDetails"></div>
    </div>

</div>

<script>
(function () {
    // ── Drag & drop upload zone ──
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
            label.innerHTML = '<i class="fa-solid fa-check-circle" style="color:var(--cx-success);"></i> ' + input.files[0].name + ' (' + (input.files[0].size / 1024).toFixed(1) + ' KB)';
            label.style.display = 'block';
        }
    }

    // ── Translate language toggle ──
    const translateCheck = document.getElementById('translateCheck');
    const langSelect     = document.getElementById('langSelect');
    translateCheck.addEventListener('change', () => {
        langSelect.style.display = translateCheck.checked ? 'block' : 'none';
    });

    // ── Form submission ──
    const form      = document.getElementById('convertForm');
    const statusDiv = document.getElementById('jobStatus');
    const detailDiv = document.getElementById('jobDetails');
    const hdrDiv    = document.getElementById('jobStatusHeader');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', async e => {
        e.preventDefault();
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner" style="animation:cx-spin 1s linear infinite;"></i> Uploading…';

        const fd = new FormData(form);
        try {
            const res  = await fetch('/projects/convertx/convert', { method: 'POST', body: fd });
            const data = await res.json();
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
        let attempts = 0;
        const maxAttempts = 120;
        const poll = async () => {
            attempts++;
            try {
                const res  = await fetch('/projects/convertx/job/' + jobId);
                const data = await res.json();
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
        const badgeClass = { pending:'badge-pending', processing:'badge-processing', completed:'badge-completed', failed:'badge-failed', cancelled:'badge-cancelled' }[data.status] || 'badge-pending';
        let html = '<p style="font-size:.9rem;">Job <strong>#' + jobId + '</strong> &nbsp; <span class="badge ' + badgeClass + '">' + data.status.toUpperCase() + '</span></p>';

        if (data.status === 'processing') {
            html += '<div style="margin-top:.75rem;height:4px;background:var(--border-color);border-radius:4px;overflow:hidden;"><div style="height:100%;width:60%;background:linear-gradient(90deg,var(--cx-primary),var(--cx-accent));border-radius:4px;animation:cx-progress 1.2s ease-in-out infinite;"></div></div>';
        }

        if (data.status === 'completed') {
            html += '<div style="margin-top:1rem;display:flex;gap:.75rem;flex-wrap:wrap;">';
            html += '<a href="/projects/convertx/job/' + jobId + '/download" class="btn btn-success"><i class="fa-solid fa-download"></i> Download ' + (data.output_filename || 'File') + '</a>';
            html += '</div>';
            if (data.ai_result) {
                html += '<div style="margin-top:1rem;border-top:1px solid var(--border-color);padding-top:1rem;">';
                html += '<strong style="font-size:.85rem;color:var(--cx-primary);">✨ AI Results</strong>';
                if (data.ai_result.ocr)        html += '<p style="font-size:.8rem;margin-top:.5rem;"><strong>OCR:</strong> ' + (data.ai_result.ocr.text || '').substring(0, 300) + '…</p>';
                if (data.ai_result.summarize)  html += '<p style="font-size:.8rem;margin-top:.5rem;"><strong>Summary:</strong> ' + (data.ai_result.summarize.summary || '') + '</p>';
                if (data.ai_result.classify)   html += '<p style="font-size:.8rem;margin-top:.5rem;"><strong>Category:</strong> ' + (data.ai_result.classify.category || '') + ' (' + Math.round((data.ai_result.classify.confidence || 0) * 100) + '%)</p>';
                if (data.ai_result.translate)  html += '<p style="font-size:.8rem;margin-top:.5rem;"><strong>Translation:</strong> ' + (data.ai_result.translate.translated || '').substring(0, 300) + '…</p>';
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

<style>
@keyframes cx-progress {
    0%   { transform: translateX(-100%); }
    100% { transform: translateX(200%); }
}
</style>
