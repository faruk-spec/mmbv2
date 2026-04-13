<?php
/**
 * ConvertX – AI OCR View (OpenAI Vision)
 */
$csrfToken = \Core\Security::generateCsrfToken();
$currentView = 'ocr-ai';
?>
<div class="cx-page-header">
    <h1 class="cx-page-title"><i class="fa-solid fa-wand-magic-sparkles"></i> AI OCR – Extract Text</h1>
    <p class="cx-page-subtitle">Use OpenAI Vision for superior text recognition — ideal for handwriting, complex layouts, and low-quality scans.</p>
</div>

<?php if (!$configured): ?>
<div class="alert alert-error" style="margin-bottom:1.25rem;">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <strong>AI OCR is not configured.</strong> An administrator must enable the OpenAI provider in
    <a href="/projects/convertx/settings" style="color:var(--cx-accent);">ConvertX Settings</a>.
</div>
<?php endif; ?>

<div class="ocr-layout">
    <!-- Upload & Options -->
    <div class="ocr-input-card">
        <h3 class="ocr-card-title"><i class="fa-solid fa-upload"></i> Upload Image</h3>

        <div class="ocr-dropzone" id="ocrAiDropzone">
            <input type="file" id="ocrAiFile" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;">
            <i class="fa-solid fa-image ocr-dz-icon"></i>
            <div class="ocr-dz-text">Drag &amp; drop an image here</div>
            <div class="ocr-dz-sub">or <button type="button" class="ocr-browse-btn" onclick="document.getElementById('ocrAiFile').click()">Browse files</button></div>
            <div class="ocr-dz-hint">Supports JPEG · PNG · GIF · WebP · up to 10 MB</div>
        </div>

        <div class="ocr-file-chip" id="ocrAiFileChip" style="display:none;">
            <i class="fa-solid fa-file-image"></i>
            <span id="ocrAiFileName">—</span>
            <button type="button" onclick="clearAiFile()" style="background:none;border:none;cursor:pointer;color:var(--cx-danger);padding:0 4px;font-size:.9rem;">&times;</button>
        </div>

        <div class="ocr-option-row" style="margin-top:1rem;">
            <label class="ocr-label" for="ocrAiPrompt">Instructions (optional)</label>
            <textarea id="ocrAiPrompt" class="ocr-select" rows="3"
                style="resize:vertical;"
                placeholder="e.g. Extract all text. Preserve table layout. Translate to English."><?= htmlspecialchars('Extract all text from this image exactly as written, preserving layout as much as possible.') ?></textarea>
        </div>

        <button type="button" class="cx-btn cx-btn-primary ocr-process-btn" id="ocrAiProcessBtn"
                onclick="runAiOcr()" <?= !$configured ? 'disabled' : '' ?>>
            <i class="fa-solid fa-wand-magic-sparkles"></i> Extract with AI
        </button>
    </div>

    <!-- Result -->
    <div class="ocr-result-card" id="ocrAiResultCard">
        <div class="ocr-result-header">
            <h3 class="ocr-card-title" style="margin:0;"><i class="fa-solid fa-align-left"></i> Extracted Text</h3>
            <div id="ocrAiResultMeta" style="font-size:.78rem;color:var(--text-secondary);"></div>
        </div>

        <div id="ocrAiResultEmpty" style="text-align:center;padding:3rem 1rem;color:var(--text-secondary);">
            <i class="fa-solid fa-sparkles" style="font-size:2.5rem;opacity:.25;display:block;margin-bottom:.75rem;"></i>
            <p>Upload an image and click <strong>Extract with AI</strong> to begin.</p>
        </div>

        <div id="ocrAiResultWrap" style="display:none;">
            <textarea id="ocrAiResultText" class="ocr-result-textarea" readonly></textarea>
            <div style="display:flex;gap:.5rem;margin-top:.75rem;flex-wrap:wrap;">
                <button type="button" class="cx-btn cx-btn-secondary" onclick="copyAiOcrText()"><i class="fa-solid fa-copy"></i> Copy</button>
                <button type="button" class="cx-btn cx-btn-secondary" onclick="downloadAiOcrText()"><i class="fa-solid fa-download"></i> Download .txt</button>
            </div>
        </div>

        <div id="ocrAiProgress" style="display:none;text-align:center;padding:2rem;">
            <i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;color:var(--cx-accent);"></i>
            <p style="margin-top:.75rem;color:var(--text-secondary);">AI is processing your image…</p>
        </div>
    </div>
</div>

<!-- Local OCR promo -->
<div class="ocr-ai-promo">
    <i class="fa-solid fa-microchip" style="font-size:1.5rem;color:var(--cx-primary);"></i>
    <div>
        <strong>Prefer privacy?</strong> Use <a href="/projects/convertx/ocr" style="color:var(--cx-primary);">Local OCR</a> — powered by Tesseract, runs entirely on the server with no data sent to third parties.
    </div>
</div>

<style>
    .ocr-layout{display:grid;grid-template-columns:1fr 1.4fr;gap:1.25rem;margin-bottom:1.25rem;}
    @media(max-width:800px){.ocr-layout{grid-template-columns:1fr;}}
    .ocr-input-card,.ocr-result-card{background:var(--bg-card);border:1px solid var(--border-color);border-radius:.875rem;padding:1.25rem;}
    .ocr-card-title{font-size:.875rem;font-weight:700;margin:0 0 1rem;display:flex;align-items:center;gap:.5rem;}
    .ocr-card-title i{color:var(--cx-accent);}
    .ocr-dropzone{border:2px dashed var(--border-color);border-radius:.75rem;padding:2.5rem 1rem;text-align:center;cursor:pointer;transition:border-color .2s,background .2s;background:var(--bg-secondary);}
    .ocr-dropzone.drag-over,.ocr-dropzone:hover{border-color:var(--cx-accent);background:rgba(6,182,212,.04);}
    .ocr-dz-icon{font-size:2.5rem;color:var(--cx-accent);opacity:.5;margin-bottom:.75rem;}
    .ocr-dz-text{font-size:.9rem;font-weight:600;color:var(--text-primary);margin-bottom:.25rem;}
    .ocr-dz-sub{font-size:.8rem;color:var(--text-secondary);margin-bottom:.25rem;}
    .ocr-dz-hint{font-size:.75rem;color:var(--text-muted);}
    .ocr-browse-btn{background:none;border:none;cursor:pointer;color:var(--cx-accent);font-weight:600;font-size:.8rem;padding:0;text-decoration:underline;}
    .ocr-file-chip{display:flex;align-items:center;gap:.5rem;padding:.5rem .75rem;background:rgba(6,182,212,.08);border:1px solid rgba(6,182,212,.25);border-radius:.5rem;margin-top:.75rem;font-size:.82rem;}
    .ocr-file-chip i{color:var(--cx-accent);}
    .ocr-label{display:block;font-size:.78rem;font-weight:600;color:var(--text-secondary);margin-bottom:.35rem;}
    .ocr-select{width:100%;padding:.5rem .75rem;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.5rem;color:var(--text-primary);font-size:.85rem;font-family:inherit;}
    .ocr-option-row{margin-bottom:.75rem;}
    .ocr-process-btn{width:100%;justify-content:center;margin-top:.5rem;}
    .ocr-process-btn:disabled{opacity:.5;cursor:not-allowed;}
    .ocr-result-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem;}
    .ocr-result-textarea{width:100%;height:320px;resize:vertical;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.5rem;color:var(--text-primary);font-family:monospace;font-size:.82rem;padding:.75rem;line-height:1.5;}
    .ocr-ai-promo{background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.2);border-radius:.875rem;padding:1rem 1.25rem;display:flex;align-items:flex-start;gap:1rem;font-size:.875rem;}
</style>

<script>
(function () {
    var dropzone  = document.getElementById('ocrAiDropzone');
    var fileInput = document.getElementById('ocrAiFile');
    var processBtn = document.getElementById('ocrAiProcessBtn');
    var selectedFile = null;

    dropzone.addEventListener('click', function () { fileInput.click(); });
    dropzone.addEventListener('dragover', function (e) { e.preventDefault(); dropzone.classList.add('drag-over'); });
    dropzone.addEventListener('dragleave', function () { dropzone.classList.remove('drag-over'); });
    dropzone.addEventListener('drop', function (e) {
        e.preventDefault(); dropzone.classList.remove('drag-over');
        if (e.dataTransfer.files.length) setFile(e.dataTransfer.files[0]);
    });
    fileInput.addEventListener('change', function () {
        if (fileInput.files.length) setFile(fileInput.files[0]);
    });

    function setFile(f) {
        selectedFile = f;
        document.getElementById('ocrAiFileName').textContent = f.name;
        document.getElementById('ocrAiFileChip').style.display = 'flex';
        if (<?= $configured ? 'true' : 'false' ?>) processBtn.disabled = false;
    }

    window.clearAiFile = function () {
        selectedFile = null;
        fileInput.value = '';
        document.getElementById('ocrAiFileChip').style.display = 'none';
        processBtn.disabled = true;
    };

    window.runAiOcr = function () {
        if (!selectedFile) return;
        document.getElementById('ocrAiResultEmpty').style.display = 'none';
        document.getElementById('ocrAiResultWrap').style.display = 'none';
        document.getElementById('ocrAiProgress').style.display = 'block';
        processBtn.disabled = true;

        var fd = new FormData();
        fd.append('file', selectedFile);
        fd.append('prompt', document.getElementById('ocrAiPrompt').value);
        fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        fetch('/projects/convertx/ocr-ai/process', { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                document.getElementById('ocrAiProgress').style.display = 'none';
                processBtn.disabled = false;
                if (data.success) {
                    document.getElementById('ocrAiResultText').value = data.text;
                    document.getElementById('ocrAiResultMeta').textContent = (data.chars || 0) + ' characters extracted';
                    document.getElementById('ocrAiResultWrap').style.display = 'block';
                } else {
                    document.getElementById('ocrAiResultEmpty').style.display = 'block';
                    document.getElementById('ocrAiResultEmpty').innerHTML =
                        '<i class="fa-solid fa-circle-xmark" style="font-size:2rem;color:var(--cx-danger);display:block;margin-bottom:.5rem;"></i><p style="color:var(--cx-danger);">' +
                        data.error + '</p>';
                }
            })
            .catch(function () {
                document.getElementById('ocrAiProgress').style.display = 'none';
                processBtn.disabled = false;
                document.getElementById('ocrAiResultEmpty').style.display = 'block';
                document.getElementById('ocrAiResultEmpty').innerHTML = '<p style="color:var(--cx-danger);">An unexpected error occurred.</p>';
            });
    };

    window.copyAiOcrText = function () {
        var ta = document.getElementById('ocrAiResultText');
        navigator.clipboard.writeText(ta.value).then(function () {
            var btn = event.target.closest('button');
            var orig = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
            setTimeout(function () { btn.innerHTML = orig; }, 1500);
        });
    };

    window.downloadAiOcrText = function () {
        var text = document.getElementById('ocrAiResultText').value;
        var blob = new Blob([text], { type: 'text/plain' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'ai-ocr-result.txt';
        a.click();
    };
}());
</script>
