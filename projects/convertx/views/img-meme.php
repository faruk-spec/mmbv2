<?php
/**
 * ConvertX – Meme Generator View (supports multiple images)
 */
$currentView  = 'img-meme';
$csrfToken    = \Core\Security::generateCsrfToken();
$hasGd        = $hasGd        ?? false;
$maxFiles     = $maxFiles     ?? 20;
$maxSizeMb    = $maxSizeMb    ?? 50;
$allowedExts  = $allowedExts  ?? ['jpg','jpeg','png','gif','webp'];
$allowedLabel = implode(', ', array_map('strtoupper', array_unique($allowedExts)));
$acceptAttr   = implode(',', array_map(fn($e) => '.'.$e, $allowedExts));
?>

<div class="page-header">
    <h1><i class="fa-solid fa-face-laugh-wink" style="color:var(--cx-primary);"></i> Meme Generator</h1>
    <p>Add classic meme-style top and bottom text to up to <?= (int)$maxFiles ?> images at once</p>
</div>

<?php if (!$hasGd): ?>
<div class="cx-notice">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>
        <strong>PHP GD extension is not loaded</strong>
        Meme generation requires the GD extension. Install with:
        <code style="background:var(--bg-tertiary);padding:.1rem .4rem;border-radius:.3rem;font-size:.82rem;">apt install php-gd</code>
    </div>
</div>
<?php endif; ?>

<div class="cx-batch-grid">

    <!-- Left: upload + preview -->
    <div class="card">
        <div class="card-header"><i class="fa-solid fa-images"></i> Upload Images</div>

        <form id="memeForm" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="form-group">
                <div class="upload-zone" id="uploadZone">
                    <i class="fa-solid fa-images upload-icon" style="font-size:1.75rem;color:var(--cx-primary);"></i>
                    <p style="font-weight:600;font-size:.875rem;margin:.35rem 0 .2rem;">Click or drag images here</p>
                    <p style="font-size:.73rem;color:var(--text-muted);"><?= htmlspecialchars($allowedLabel) ?> · max <?= (int)$maxSizeMb ?> MB · up to <?= (int)$maxFiles ?> files</p>
                    <input type="file" name="images[]" id="fileInput"
                           accept="<?= htmlspecialchars($acceptAttr) ?>"
                           multiple style="display:none;">
                </div>
                <div id="fileList" style="margin-top:.6rem;font-size:.78rem;color:var(--text-muted);"></div>
            </div>

            <!-- Live preview of first image -->
            <div id="previewWrap" style="display:none;position:relative;border-radius:.5rem;overflow:hidden;background:#000;">
                <img id="previewImg" style="display:block;max-width:100%;height:auto;" alt="Preview">
                <div id="topTextPreview" class="meme-text-overlay" style="top:5%;"></div>
                <div id="botTextPreview" class="meme-text-overlay" style="bottom:5%;"></div>
            </div>
        </form>
    </div>

    <!-- Right: meme settings -->
    <div class="card">
        <div class="card-header"><i class="fa-solid fa-sliders"></i> Meme Settings</div>

        <div class="form-group">
            <label class="form-label" for="topText">
                <i class="fa-solid fa-arrow-up" style="color:var(--cx-primary);"></i> Top Text
            </label>
            <input type="text" name="top_text" id="topText" form="memeForm"
                   class="form-control" placeholder="WHEN YOU..." maxlength="100">
        </div>

        <div class="form-group">
            <label class="form-label" for="botText">
                <i class="fa-solid fa-arrow-down" style="color:var(--cx-primary);"></i> Bottom Text
            </label>
            <input type="text" name="bottom_text" id="botText" form="memeForm"
                   class="form-control" placeholder="BUT THEN..." maxlength="100">
        </div>

        <div class="form-group">
            <label class="form-label" for="fontSize">Font Size</label>
            <select class="form-control" name="font_size" id="fontSize" form="memeForm">
                <option value="32">Small (32px)</option>
                <option value="48" selected>Medium (48px)</option>
                <option value="64">Large (64px)</option>
                <option value="80">Extra Large (80px)</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Text Color</label>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;padding:.4rem .75rem;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.45rem;">
                    <input type="radio" name="text_color" value="white" form="memeForm" checked>
                    <span style="width:.85rem;height:.85rem;background:#fff;border:1px solid #555;border-radius:50%;display:inline-block;"></span> White
                </label>
                <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;padding:.4rem .75rem;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.45rem;">
                    <input type="radio" name="text_color" value="black" form="memeForm">
                    <span style="width:.85rem;height:.85rem;background:#222;border:1px solid #555;border-radius:50%;display:inline-block;"></span> Black
                </label>
                <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;padding:.4rem .75rem;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.45rem;">
                    <input type="radio" name="text_color" value="yellow" form="memeForm">
                    <span style="width:.85rem;height:.85rem;background:#ff0;border:1px solid #555;border-radius:50%;display:inline-block;"></span> Yellow
                </label>
            </div>
        </div>

        <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:.75rem;">
            <i class="fa-solid fa-circle-info" style="color:var(--cx-primary);"></i>
            The same text settings are applied to all uploaded images.
        </p>

        <button type="submit" form="memeForm" class="btn btn-primary" id="submitBtn"
                style="width:100%;justify-content:center;padding:.825rem;"
                <?= !$hasGd ? 'disabled title="GD extension required"' : '' ?>>
            <i class="fa-solid fa-face-laugh-wink"></i> Generate Memes
        </button>
    </div>

</div>

<!-- Result card -->
<div id="resultCard" class="card" style="display:none;margin-top:1.25rem;">
    <div class="card-header"><i class="fa-solid fa-check-circle"></i> Meme Ready</div>
    <div id="resultBody" style="padding:.875rem;"></div>
</div>

<style>
.cx-batch-grid { display:grid; grid-template-columns:1.2fr 1fr; gap:1.25rem; align-items:start; }
@media (max-width:768px) { .cx-batch-grid { grid-template-columns:1fr; } }
.meme-text-overlay {
    position:absolute; left:50%; transform:translateX(-50%);
    white-space:nowrap; font-weight:900;
    text-shadow:-2px -2px 0 #000, 2px -2px 0 #000, -2px 2px 0 #000, 2px 2px 0 #000;
    font-family:Impact,'Arial Black',sans-serif; color:#fff;
    font-size:2.5rem; text-transform:uppercase; pointer-events:none;
}
</style>

<script>
(function () {
    var MAX_FILES = <?= (int)$maxFiles ?>;
    var MAX_MB    = <?= (int)$maxSizeMb ?>;
    var ALLOWED   = <?= json_encode(array_values($allowedExts)) ?>;

    var zone        = document.getElementById('uploadZone');
    var input       = document.getElementById('fileInput');
    var fileListEl  = document.getElementById('fileList');
    var previewWrap = document.getElementById('previewWrap');
    var previewImg  = document.getElementById('previewImg');
    var topPreview  = document.getElementById('topTextPreview');
    var botPreview  = document.getElementById('botTextPreview');
    var submitBtn   = document.getElementById('submitBtn');
    var resultCard  = document.getElementById('resultCard');
    var resultBody  = document.getElementById('resultBody');
    var selectedFiles = [];

    zone.addEventListener('click', function () { input.click(); });
    zone.addEventListener('dragover', function (e) { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', function () { zone.classList.remove('drag-over'); });
    zone.addEventListener('drop', function (e) {
        e.preventDefault(); zone.classList.remove('drag-over');
        loadFiles(Array.from(e.dataTransfer.files));
    });
    input.addEventListener('change', function () {
        if (input.files.length) loadFiles(Array.from(input.files));
        input.value = '';
    });

    function loadFiles(files) {
        var valid = [];
        files.forEach(function (f) {
            var ext = f.name.split('.').pop().toLowerCase();
            if (ALLOWED.indexOf(ext) === -1) { alert(f.name + ': unsupported format'); return; }
            if (f.size > MAX_MB * 1024 * 1024) { alert(f.name + ': too large (max ' + MAX_MB + ' MB)'); return; }
            valid.push(f);
        });
        selectedFiles = selectedFiles.concat(valid).slice(0, MAX_FILES);
        renderFileList();
        if (selectedFiles.length > 0) loadPreview(selectedFiles[0]);
    }

    function renderFileList() {
        if (!selectedFiles.length) { fileListEl.innerHTML = ''; return; }
        var html = '<strong>' + selectedFiles.length + ' file(s):</strong><ul style="margin:.3rem 0 0 1rem;padding:0;">';
        selectedFiles.forEach(function (f, i) {
            html += '<li>' + esc(f.name) + ' <span style="color:var(--text-muted);">(' + fmtSize(f.size) + ')</span>'
                  + ' <button type="button" onclick="removeMemeFile(' + i + ')" style="background:none;border:none;color:var(--cx-danger);cursor:pointer;padding:0 .25rem;font-size:.85rem;">&times;</button></li>';
        });
        html += '</ul>';
        fileListEl.innerHTML = html;
        zone.classList.toggle('has-file', selectedFiles.length > 0);
    }
    window.removeMemeFile = function(i) {
        selectedFiles.splice(i, 1);
        renderFileList();
        if (selectedFiles.length === 0) previewWrap.style.display = 'none';
        else loadPreview(selectedFiles[0]);
    };

    function loadPreview(f) {
        previewImg.src = URL.createObjectURL(f);
        previewImg.onload = function () { previewWrap.style.display = ''; };
    }

    function updatePreview() {
        var top   = (document.getElementById('topText').value || '').toUpperCase();
        var bot   = (document.getElementById('botText').value || '').toUpperCase();
        var color = (document.querySelector('[name="text_color"]:checked') || {value:'white'}).value;
        var colorMap = { white:'#fff', black:'#111', yellow:'#ff0' };
        var c = colorMap[color] || '#fff';
        topPreview.textContent = top;
        botPreview.textContent = bot;
        topPreview.style.color = c;
        botPreview.style.color = c;
    }

    document.getElementById('topText').addEventListener('input', updatePreview);
    document.getElementById('botText').addEventListener('input', updatePreview);
    document.querySelectorAll('[name="text_color"]').forEach(function (r) { r.addEventListener('change', updatePreview); });

    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    function fmtSize(b) { return b>=1048576?(b/1048576).toFixed(2)+' MB':(b/1024).toFixed(1)+' KB'; }

    document.getElementById('memeForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!selectedFiles.length) { alert('Please select at least one image.'); return; }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner" style="animation:cx-spin 1s linear infinite;"></i> Generating…';

        var fd = new FormData();
        fd.append('_token', document.querySelector('[name="_token"]').value);
        selectedFiles.forEach(function (f) { fd.append('images[]', f); });
        fd.append('top_text',    document.getElementById('topText').value);
        fd.append('bottom_text', document.getElementById('botText').value);
        fd.append('font_size',   document.getElementById('fontSize').value);
        fd.append('text_color',  document.querySelector('[name="text_color"]:checked').value);

        try {
            var res  = await fetch('/projects/convertx/img-meme', { method:'POST', body:fd });
            var data = await res.json();
            resultCard.style.display = '';
            resultCard.scrollIntoView({ behavior:'smooth', block:'nearest' });

            if (data.success) {
                var html = '<p style="margin-bottom:.75rem;font-size:.85rem;">'
                         + '<i class="fa-solid fa-check-circle" style="color:var(--cx-success);"></i> '
                         + data.count + ' meme(s) created!</p>';
                if (data.errors && data.errors.length) {
                    html += '<p style="color:var(--cx-warning);font-size:.8rem;">Warnings: ' + data.errors.map(esc).join('; ') + '</p>';
                }
                html += '<a href="/projects/convertx/pdf-tools/download/'+data.token+'" class="btn btn-success">'
                      + '<i class="fa-solid fa-download"></i> Download ' + esc(data.filename) + '</a>';
                resultBody.innerHTML = html;
            } else {
                resultBody.innerHTML = '<p style="color:var(--cx-danger);"><i class="fa-solid fa-circle-xmark"></i> ' + esc(data.error || 'Meme generation failed') + '</p>';
            }
        } catch (err) { alert('Network error: ' + err.message); }

        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-face-laugh-wink"></i> Generate Memes';
    });
})();
</script>

