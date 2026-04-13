<?php
/**
 * ConvertX – Crop Image View (supports multiple files)
 */
$currentView  = 'img-crop';
$csrfToken    = \Core\Security::generateCsrfToken();
$hasGd        = $hasGd        ?? false;
$maxFiles     = $maxFiles     ?? 20;
$maxSizeMb    = $maxSizeMb    ?? 50;
$allowedExts  = $allowedExts  ?? ['jpg','jpeg','png','gif','webp'];
$allowedLabel = implode(', ', array_map('strtoupper', array_unique($allowedExts)));
$acceptAttr   = implode(',', array_map(fn($e) => '.'.$e, $allowedExts));
?>

<div class="page-header">
    <h1><i class="fa-solid fa-crop-simple" style="color:var(--cx-primary);"></i> Crop Images</h1>
    <p>Crop up to <?= (int)$maxFiles ?> images at once using the same crop rectangle. Define X/Y offset and dimensions, or drag the overlay on the preview.</p>
</div>

<?php if (!$hasGd): ?>
<div class="cx-notice">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>
        <strong>PHP GD extension is not loaded</strong>
        Image cropping requires the GD extension. Install with:
        <code style="background:var(--bg-tertiary);padding:.1rem .4rem;border-radius:.3rem;font-size:.82rem;">apt install php-gd</code>
    </div>
</div>
<?php endif; ?>

<form id="cropForm" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <div class="cx-batch-grid">

        <!-- Left: upload + preview -->
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-images"></i> Upload Images</div>

            <div class="form-group">
                <div class="upload-zone" id="uploadZone">
                    <i class="fa-solid fa-images upload-icon" style="font-size:1.75rem;color:var(--cx-primary);"></i>
                    <p style="font-weight:600;font-size:.875rem;margin:.35rem 0 .2rem;">Click or drag images here</p>
                    <p style="font-size:.73rem;color:var(--text-muted);"><?= htmlspecialchars($allowedLabel) ?> · max <?= (int)$maxSizeMb ?> MB · up to <?= (int)$maxFiles ?> files</p>
                    <input type="file" name="images[]" id="fileInput"
                           accept="<?= htmlspecialchars($acceptAttr) ?>"
                           multiple style="display:none;">
                </div>
                <!-- file list -->
                <div id="fileList" style="margin-top:.6rem;font-size:.78rem;color:var(--text-muted);"></div>
            </div>

            <!-- Preview of first selected image with crop overlay -->
            <div id="previewWrap" style="display:none;position:relative;margin-top:.5rem;overflow:hidden;border-radius:.5rem;background:var(--bg-secondary);user-select:none;">
                <img id="previewImg" style="display:block;max-width:100%;height:auto;" alt="Preview">
                <div id="cropOverlay" style="
                    position:absolute;
                    border:2px solid var(--cx-primary);
                    box-shadow:0 0 0 9999px rgba(0,0,0,.45);
                    cursor:move;
                    box-sizing:border-box;
                "></div>
            </div>
            <p id="imgDims" style="font-size:.75rem;color:var(--text-muted);margin-top:.35rem;display:none;"></p>
        </div>

        <!-- Right: crop settings -->
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-sliders"></i> Crop Settings</div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <div class="form-group">
                    <label class="form-label" for="cropX">X Offset (px)</label>
                    <input type="number" name="x" id="cropX" class="form-control" min="0" value="0">
                </div>
                <div class="form-group">
                    <label class="form-label" for="cropY">Y Offset (px)</label>
                    <input type="number" name="y" id="cropY" class="form-control" min="0" value="0">
                </div>
                <div class="form-group">
                    <label class="form-label" for="cropW">Crop Width (px)</label>
                    <input type="number" name="crop_width" id="cropW" class="form-control" min="1" value="100">
                </div>
                <div class="form-group">
                    <label class="form-label" for="cropH">Crop Height (px)</label>
                    <input type="number" name="crop_height" id="cropH" class="form-control" min="1" value="100">
                </div>
            </div>

            <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:.75rem;">
                <i class="fa-solid fa-circle-info" style="color:var(--cx-primary);"></i>
                Drag the blue rectangle on the preview to reposition. The same crop area is applied to all uploaded images.
            </p>

            <button type="submit" form="cropForm" class="btn btn-primary" id="submitBtn"
                    style="width:100%;justify-content:center;padding:.825rem;"
                    <?= !$hasGd ? 'disabled title="GD extension required"' : '' ?>>
                <i class="fa-solid fa-crop-simple"></i> Crop Images
            </button>
        </div>

    </div>
</form>

<!-- Result card -->
<div id="resultCard" class="card" style="display:none;margin-top:1.25rem;">
    <div class="card-header"><i class="fa-solid fa-check-circle"></i> Crop Complete</div>
    <div id="resultBody" style="padding:.875rem;"></div>
</div>

<style>
.cx-batch-grid { display:grid; grid-template-columns:1.2fr 1fr; gap:1.25rem; align-items:start; }
@media (max-width:768px) { .cx-batch-grid { grid-template-columns:1fr; } }
</style>

<script>
(function () {
    var MAX_FILES  = <?= (int)$maxFiles ?>;
    var MAX_MB     = <?= (int)$maxSizeMb ?>;
    var ALLOWED    = <?= json_encode(array_values($allowedExts)) ?>;

    var zone        = document.getElementById('uploadZone');
    var input       = document.getElementById('fileInput');
    var fileListEl  = document.getElementById('fileList');
    var previewWrap = document.getElementById('previewWrap');
    var previewImg  = document.getElementById('previewImg');
    var cropOvl     = document.getElementById('cropOverlay');
    var dimsEl      = document.getElementById('imgDims');
    var submitBtn   = document.getElementById('submitBtn');
    var resultCard  = document.getElementById('resultCard');
    var resultBody  = document.getElementById('resultBody');
    var naturalW    = 0;
    var naturalH    = 0;
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
            if (f.size > MAX_MB * 1024 * 1024) { alert(f.name + ': file too large (max ' + MAX_MB + ' MB)'); return; }
            valid.push(f);
        });
        selectedFiles = selectedFiles.concat(valid).slice(0, MAX_FILES);
        renderFileList();
        if (selectedFiles.length > 0) loadPreview(selectedFiles[0]);
    }

    function renderFileList() {
        if (!selectedFiles.length) { fileListEl.innerHTML = ''; return; }
        var html = '<strong>' + selectedFiles.length + ' file(s) selected:</strong><ul style="margin:.3rem 0 0 1rem;padding:0;">';
        selectedFiles.forEach(function (f, i) {
            html += '<li>' + esc(f.name) + ' <span style="color:var(--text-muted);">(' + fmtSize(f.size) + ')</span>'
                  + ' <button type="button" onclick="removeFile(' + i + ')" style="background:none;border:none;color:var(--cx-danger);cursor:pointer;padding:0 .25rem;font-size:.85rem;">&times;</button></li>';
        });
        html += '</ul>';
        fileListEl.innerHTML = html;
        zone.classList.toggle('has-file', selectedFiles.length > 0);
    }
    window.removeFile = function(i) {
        selectedFiles.splice(i, 1);
        renderFileList();
        if (selectedFiles.length === 0) { previewWrap.style.display = 'none'; dimsEl.style.display = 'none'; }
        else loadPreview(selectedFiles[0]);
    };

    function loadPreview(f) {
        var url = URL.createObjectURL(f);
        previewImg.onload = function () {
            naturalW = previewImg.naturalWidth;
            naturalH = previewImg.naturalHeight;
            dimsEl.textContent = 'Preview: ' + naturalW + ' × ' + naturalH + ' px';
            dimsEl.style.display = '';
            previewWrap.style.display = '';
            syncOverlay();
        };
        previewImg.src = url;
    }

    function syncOverlay() {
        var scale = previewImg.offsetWidth / naturalW;
        var x = clamp(parseInt(document.getElementById('cropX').value)||0, 0, naturalW-1);
        var y = clamp(parseInt(document.getElementById('cropY').value)||0, 0, naturalH-1);
        var w = clamp(parseInt(document.getElementById('cropW').value)||100, 1, naturalW-x);
        var h = clamp(parseInt(document.getElementById('cropH').value)||100, 1, naturalH-y);
        cropOvl.style.left   = (x*scale)+'px';
        cropOvl.style.top    = (y*scale)+'px';
        cropOvl.style.width  = (w*scale)+'px';
        cropOvl.style.height = (h*scale)+'px';
    }

    ['cropX','cropY','cropW','cropH'].forEach(function (id) {
        document.getElementById(id).addEventListener('input', syncOverlay);
    });
    window.addEventListener('resize', syncOverlay);

    // Drag overlay
    var dragging = false, startX, startY, startOX, startOY;
    cropOvl.addEventListener('mousedown', function (e) {
        dragging = true;
        startX = e.clientX; startY = e.clientY;
        startOX = parseInt(document.getElementById('cropX').value)||0;
        startOY = parseInt(document.getElementById('cropY').value)||0;
        e.preventDefault();
    });
    document.addEventListener('mousemove', function (e) {
        if (!dragging) return;
        var scale = previewImg.offsetWidth / naturalW;
        var dx = Math.round((e.clientX - startX) / scale);
        var dy = Math.round((e.clientY - startY) / scale);
        var w  = parseInt(document.getElementById('cropW').value)||100;
        var h  = parseInt(document.getElementById('cropH').value)||100;
        document.getElementById('cropX').value = clamp(startOX+dx, 0, naturalW-w);
        document.getElementById('cropY').value = clamp(startOY+dy, 0, naturalH-h);
        syncOverlay();
    });
    document.addEventListener('mouseup', function () { dragging = false; });

    function clamp(v,mn,mx){ return Math.min(Math.max(v,mn),mx); }
    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    function fmtSize(b) { return b>=1048576?(b/1048576).toFixed(2)+' MB':(b/1024).toFixed(1)+' KB'; }

    document.getElementById('cropForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!selectedFiles.length) { alert('Please select at least one image.'); return; }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner" style="animation:cx-spin 1s linear infinite;"></i> Cropping…';

        var fd = new FormData();
        fd.append('_token', document.querySelector('[name="_token"]').value);
        selectedFiles.forEach(function (f) { fd.append('images[]', f); });
        fd.append('x',           document.getElementById('cropX').value);
        fd.append('y',           document.getElementById('cropY').value);
        fd.append('crop_width',  document.getElementById('cropW').value);
        fd.append('crop_height', document.getElementById('cropH').value);

        try {
            var res  = await fetch('/projects/convertx/img-crop', { method:'POST', body:fd });
            var data = await res.json();
            resultCard.style.display = '';
            resultCard.scrollIntoView({ behavior:'smooth', block:'nearest' });

            if (data.success) {
                var html = '<p style="font-size:.85rem;margin-bottom:.75rem;">'
                         + '<i class="fa-solid fa-check-circle" style="color:var(--cx-success);"></i> '
                         + data.count + ' image(s) cropped.'
                         + (data.new_size ? ' Output: <strong>' + fmtSize(data.new_size) + '</strong>' : '') + '</p>';
                if (data.errors && data.errors.length) {
                    html += '<p style="color:var(--cx-warning);font-size:.8rem;">Warnings: ' + data.errors.map(esc).join('; ') + '</p>';
                }
                html += '<a href="/projects/convertx/pdf-tools/download/'+data.token+'" class="btn btn-success">'
                      + '<i class="fa-solid fa-download"></i> Download ' + esc(data.filename) + '</a>';
                resultBody.innerHTML = html;
            } else {
                resultBody.innerHTML = '<p style="color:var(--cx-danger);"><i class="fa-solid fa-circle-xmark"></i> ' + esc(data.error || 'Crop failed') + '</p>';
            }
        } catch (err) { alert('Network error: ' + err.message); }

        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-crop-simple"></i> Crop Images';
    });
})();
</script>
