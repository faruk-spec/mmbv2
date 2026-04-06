<?php
/**
 * ConvertX – Local OCR View (Tesseract)
 */
$csrfToken = \Core\Security::generateCsrfToken();
$currentView = 'ocr';
?>
<div class="cx-page-header">
    <h1 class="cx-page-title"><i class="fa-solid fa-file-image"></i> OCR – Extract Text</h1>
    <p class="cx-page-subtitle">Extract text from images and PDFs using local Tesseract OCR engine.</p>
</div>

<div class="ocr-layout">
    <!-- Upload & Options -->
    <div class="ocr-input-card">
        <h3 class="ocr-card-title"><i class="fa-solid fa-upload"></i> Upload File</h3>

        <div class="ocr-dropzone" id="ocrDropzone">
            <input type="file" id="ocrFile" accept="image/jpeg,image/png,image/gif,image/webp,application/pdf" style="display:none;">
            <i class="fa-solid fa-image ocr-dz-icon"></i>
            <div class="ocr-dz-text">Drag &amp; drop an image or PDF here</div>
            <div class="ocr-dz-sub">or <button type="button" class="ocr-browse-btn" onclick="document.getElementById('ocrFile').click()">Browse files</button></div>
            <div class="ocr-dz-hint">Supports JPEG · PNG · GIF · WebP · PDF · up to 10 MB</div>
        </div>

        <div class="ocr-file-chip" id="ocrFileChip" style="display:none;">
            <i class="fa-solid fa-file-image"></i>
            <span id="ocrFileName">—</span>
            <button type="button" onclick="clearOcrFile()" style="background:none;border:none;cursor:pointer;color:var(--cx-danger);padding:0 4px;font-size:.9rem;">&times;</button>
        </div>

        <div class="ocr-option-row" style="margin-top:1rem;">
            <label class="ocr-label" for="ocrLang">Language</label>
            <select id="ocrLang" class="ocr-select">
                <option value="eng">English</option>
                <option value="fra">French</option>
                <option value="deu">German</option>
                <option value="spa">Spanish</option>
                <option value="ita">Italian</option>
                <option value="por">Portuguese</option>
                <option value="rus">Russian</option>
                <option value="ara">Arabic</option>
                <option value="chi_sim">Chinese (Simplified)</option>
                <option value="jpn">Japanese</option>
                <option value="kor">Korean</option>
            </select>
        </div>

        <button type="button" class="cx-btn cx-btn-primary ocr-process-btn" id="ocrProcessBtn" onclick="runOcr()" disabled>
            <i class="fa-solid fa-magic-wand-sparkles"></i> Extract Text
        </button>
    </div>

    <!-- Result -->
    <div class="ocr-result-card" id="ocrResultCard">
        <div class="ocr-result-header">
            <h3 class="ocr-card-title" style="margin:0;"><i class="fa-solid fa-align-left"></i> Extracted Text</h3>
            <div id="ocrResultMeta" style="font-size:.78rem;color:var(--text-secondary);"></div>
        </div>

        <div id="ocrResultEmpty" style="text-align:center;padding:3rem 1rem;color:var(--text-secondary);">
            <i class="fa-solid fa-file-circle-question" style="font-size:2.5rem;opacity:.25;display:block;margin-bottom:.75rem;"></i>
            <p>Upload a file and click <strong>Extract Text</strong> to begin.</p>
        </div>

        <div id="ocrResultWrap" style="display:none;">
            <textarea id="ocrResultText" class="ocr-result-textarea" readonly></textarea>
            <div style="display:flex;gap:.5rem;margin-top:.75rem;flex-wrap:wrap;">
                <button type="button" class="cx-btn cx-btn-secondary" onclick="copyOcrText()"><i class="fa-solid fa-copy"></i> Copy</button>
                <button type="button" class="cx-btn cx-btn-secondary" onclick="downloadOcrText()"><i class="fa-solid fa-download"></i> Download .txt</button>
            </div>
        </div>

        <div id="ocrProgress" style="display:none;text-align:center;padding:2rem;">
            <i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;color:var(--cx-accent);"></i>
            <p style="margin-top:.75rem;color:var(--text-secondary);">Processing OCR…</p>
        </div>
    </div>
</div>

<!-- AI OCR Promo -->
<div class="ocr-ai-promo">
    <i class="fa-solid fa-wand-magic-sparkles" style="font-size:1.5rem;color:var(--cx-accent);"></i>
    <div>
        <strong>Need better accuracy?</strong> Try <a href="/projects/convertx/ocr-ai" style="color:var(--cx-accent);">AI OCR</a> — uses OpenAI Vision for superior text recognition on complex layouts and handwriting.
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
    .ocr-ai-promo{background:rgba(6,182,212,.06);border:1px solid rgba(6,182,212,.2);border-radius:.875rem;padding:1rem 1.25rem;display:flex;align-items:flex-start;gap:1rem;font-size:.875rem;}
</style>

<script>
(function () {
    var dropzone = document.getElementById('ocrDropzone');
    var fileInput = document.getElementById('ocrFile');
    var processBtn = document.getElementById('ocrProcessBtn');
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
        document.getElementById('ocrFileName').textContent = f.name;
        document.getElementById('ocrFileChip').style.display = 'flex';
        processBtn.disabled = false;
    }

    window.clearOcrFile = function () {
        selectedFile = null;
        fileInput.value = '';
        document.getElementById('ocrFileChip').style.display = 'none';
        processBtn.disabled = true;
    };

    window.runOcr = function () {
        if (!selectedFile) return;
        document.getElementById('ocrResultEmpty').style.display = 'none';
        document.getElementById('ocrResultWrap').style.display = 'none';
        document.getElementById('ocrProgress').style.display = 'block';
        processBtn.disabled = true;

        var fd = new FormData();
        fd.append('file', selectedFile);
        fd.append('lang', document.getElementById('ocrLang').value);
        fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        fetch('/projects/convertx/ocr/process', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                document.getElementById('ocrProgress').style.display = 'none';
                processBtn.disabled = false;
                if (data.success) {
                    document.getElementById('ocrResultText').value = data.text;
                    document.getElementById('ocrResultMeta').textContent = (data.chars || 0) + ' characters extracted';
                    document.getElementById('ocrResultWrap').style.display = 'block';
                } else {
                    document.getElementById('ocrResultEmpty').style.display = 'block';
                    document.getElementById('ocrResultEmpty').innerHTML =
                        '<i class="fa-solid fa-circle-xmark" style="font-size:2rem;color:var(--cx-danger);display:block;margin-bottom:.5rem;"></i><p style="color:var(--cx-danger);">' +
                        data.error + '</p>';
                }
            })
            .catch(function () {
                document.getElementById('ocrProgress').style.display = 'none';
                processBtn.disabled = false;
                document.getElementById('ocrResultEmpty').style.display = 'block';
                document.getElementById('ocrResultEmpty').innerHTML = '<p style="color:var(--cx-danger);">An unexpected error occurred.</p>';
            });
    };

    window.copyOcrText = function () {
        var ta = document.getElementById('ocrResultText');
        navigator.clipboard.writeText(ta.value).then(function () {
            // brief flash
            var btn = event.target.closest('button');
            var orig = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
            setTimeout(function () { btn.innerHTML = orig; }, 1500);
        });
    };

    window.downloadOcrText = function () {
        var text = document.getElementById('ocrResultText').value;
        var blob = new Blob([text], { type: 'text/plain' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'ocr-result.txt';
        a.click();
    };
}());
</script>
