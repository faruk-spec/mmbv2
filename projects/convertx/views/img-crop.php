<?php
/**
 * ConvertX – Crop Image View
 */
$currentView = 'img-crop';
$csrfToken   = \Core\Security::generateCsrfToken();
$hasGd       = $hasGd ?? false;
?>

<div class="page-header">
    <h1><i class="fa-solid fa-crop-simple" style="color:var(--cx-primary);"></i> Crop Image</h1>
    <p>Crop a single image to your desired dimensions using pixel offsets</p>
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
            <div class="card-header"><i class="fa-solid fa-image"></i> Upload Image</div>

            <div class="form-group">
                <div class="upload-zone" id="uploadZone">
                    <i class="fa-solid fa-image upload-icon" style="font-size:1.75rem;color:var(--cx-primary);"></i>
                    <p style="font-weight:600;font-size:.875rem;margin:.35rem 0 .2rem;">Click or drag an image here</p>
                    <p style="font-size:.73rem;color:var(--text-muted);">JPG, PNG, GIF, WebP · max 50 MB</p>
                    <input type="file" name="image" id="fileInput"
                           accept=".jpg,.jpeg,.png,.gif,.webp" style="display:none;">
                </div>
            </div>

            <!-- Preview with crop overlay -->
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
                    <input type="number" name="x" id="cropX" form="cropForm"
                           class="form-control" min="0" value="0">
                </div>
                <div class="form-group">
                    <label class="form-label" for="cropY">Y Offset (px)</label>
                    <input type="number" name="y" id="cropY" form="cropForm"
                           class="form-control" min="0" value="0">
                </div>
                <div class="form-group">
                    <label class="form-label" for="cropW">Crop Width (px)</label>
                    <input type="number" name="crop_width" id="cropW" form="cropForm"
                           class="form-control" min="1" value="100">
                </div>
                <div class="form-group">
                    <label class="form-label" for="cropH">Crop Height (px)</label>
                    <input type="number" name="crop_height" id="cropH" form="cropForm"
                           class="form-control" min="1" value="100">
                </div>
            </div>

            <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:.75rem;">
                <i class="fa-solid fa-circle-info" style="color:var(--cx-primary);"></i>
                You can also drag the blue rectangle on the preview to position the crop area.
            </p>

            <button type="submit" form="cropForm" class="btn btn-primary" id="submitBtn"
                    style="width:100%;justify-content:center;padding:.825rem;"
                    <?= !$hasGd ? 'disabled title="GD extension required"' : '' ?>>
                <i class="fa-solid fa-crop-simple"></i> Crop Image
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
    var zone       = document.getElementById('uploadZone');
    var input      = document.getElementById('fileInput');
    var previewWrap= document.getElementById('previewWrap');
    var previewImg = document.getElementById('previewImg');
    var cropOvl    = document.getElementById('cropOverlay');
    var dimsEl     = document.getElementById('imgDims');
    var submitBtn  = document.getElementById('submitBtn');
    var resultCard = document.getElementById('resultCard');
    var resultBody = document.getElementById('resultBody');
    var naturalW   = 0;
    var naturalH   = 0;
    var selectedFile = null;

    zone.addEventListener('click', function () { input.click(); });
    zone.addEventListener('dragover', function (e) { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', function () { zone.classList.remove('drag-over'); });
    zone.addEventListener('drop', function (e) { e.preventDefault(); zone.classList.remove('drag-over'); loadFile(e.dataTransfer.files[0]); });
    input.addEventListener('change', function () { if (input.files[0]) loadFile(input.files[0]); input.value=''; });

    function loadFile(f) {
        if (!f) return;
        var ALLOWED = ['jpg','jpeg','png','gif','webp'];
        if (ALLOWED.indexOf(f.name.split('.').pop().toLowerCase()) === -1) { alert('Unsupported format.'); return; }
        selectedFile = f;
        var url = URL.createObjectURL(f);
        previewImg.onload = function () {
            naturalW = previewImg.naturalWidth;
            naturalH = previewImg.naturalHeight;
            dimsEl.textContent = 'Original: ' + naturalW + ' × ' + naturalH + ' px';
            dimsEl.style.display = '';
            previewWrap.style.display = '';
            syncOverlay();
        };
        previewImg.src = url;
        zone.classList.add('has-file');
    }

    // Update overlay position from fields
    function syncOverlay() {
        var scale = previewImg.offsetWidth / naturalW;
        var x = clamp(parseInt(document.getElementById('cropX').value)||0, 0, naturalW);
        var y = clamp(parseInt(document.getElementById('cropY').value)||0, 0, naturalH);
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

    // Drag the overlay
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
        if (!selectedFile) { alert('Please select an image first.'); return; }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner" style="animation:cx-spin 1s linear infinite;"></i> Cropping…';

        var fd = new FormData();
        fd.append('_token', document.querySelector('[name="_token"]').value);
        fd.append('image', selectedFile);
        fd.append('x',          document.getElementById('cropX').value);
        fd.append('y',          document.getElementById('cropY').value);
        fd.append('crop_width', document.getElementById('cropW').value);
        fd.append('crop_height',document.getElementById('cropH').value);

        try {
            var res  = await fetch('/projects/convertx/img-crop', { method:'POST', body:fd });
            var data = await res.json();
            resultCard.style.display = '';
            resultCard.scrollIntoView({ behavior:'smooth', block:'nearest' });

            if (data.success) {
                var html = '<p style="margin-bottom:.75rem;font-size:.85rem;"><i class="fa-solid fa-check-circle" style="color:var(--cx-success);"></i> '
                         + 'New size: <strong>' + fmtSize(data.new_size) + '</strong></p>';
                html += '<a href="/projects/convertx/pdf-tools/download/'+data.token+'" class="btn btn-success">'
                      + '<i class="fa-solid fa-download"></i> Download '+esc(data.filename)+'</a>';
                resultBody.innerHTML = html;
            } else {
                resultBody.innerHTML = '<p style="color:var(--cx-danger);"><i class="fa-solid fa-circle-xmark"></i> '+esc(data.error||'Crop failed')+'</p>';
            }
        } catch (err) { alert('Network error: '+err.message); }

        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-crop-simple"></i> Crop Image';
    });
})();
</script>
