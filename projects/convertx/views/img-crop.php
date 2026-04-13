<?php
/**
 * ConvertX – Crop Image View (supports multiple files, resize handles, quality slider)
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
    <p>Drag &amp; resize the crop area on the preview, then apply to up to <?= (int)$maxFiles ?> images at once.</p>
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
            <div class="card-header"><i class="fa-solid fa-images"></i> Upload &amp; Preview</div>

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

            <!-- Preview with drag+resize crop overlay -->
            <div id="previewWrap" style="display:none;position:relative;margin-top:.5rem;overflow:hidden;border-radius:.5rem;background:#111;user-select:none;touch-action:none;">
                <img id="previewImg" style="display:block;max-width:100%;height:auto;opacity:.85;" alt="Preview">

                <!-- Dark mask outside crop (4 divs) -->
                <div id="maskTop"    style="position:absolute;left:0;right:0;top:0;background:rgba(0,0,0,.5);pointer-events:none;"></div>
                <div id="maskBottom" style="position:absolute;left:0;right:0;bottom:0;background:rgba(0,0,0,.5);pointer-events:none;"></div>
                <div id="maskLeft"   style="position:absolute;top:0;bottom:0;left:0;background:rgba(0,0,0,.5);pointer-events:none;"></div>
                <div id="maskRight"  style="position:absolute;top:0;bottom:0;right:0;background:rgba(0,0,0,.5);pointer-events:none;"></div>

                <!-- Crop box -->
                <div id="cropBox" style="position:absolute;border:2px solid var(--cx-primary);box-sizing:border-box;cursor:move;">
                    <!-- Resize handles (8 directions) -->
                    <div class="crop-handle" data-dir="nw" style="top:-5px;left:-5px;cursor:nw-resize;"></div>
                    <div class="crop-handle" data-dir="n"  style="top:-5px;left:50%;transform:translateX(-50%);cursor:n-resize;"></div>
                    <div class="crop-handle" data-dir="ne" style="top:-5px;right:-5px;cursor:ne-resize;"></div>
                    <div class="crop-handle" data-dir="e"  style="top:50%;right:-5px;transform:translateY(-50%);cursor:e-resize;"></div>
                    <div class="crop-handle" data-dir="se" style="bottom:-5px;right:-5px;cursor:se-resize;"></div>
                    <div class="crop-handle" data-dir="s"  style="bottom:-5px;left:50%;transform:translateX(-50%);cursor:s-resize;"></div>
                    <div class="crop-handle" data-dir="sw" style="bottom:-5px;left:-5px;cursor:sw-resize;"></div>
                    <div class="crop-handle" data-dir="w"  style="top:50%;left:-5px;transform:translateY(-50%);cursor:w-resize;"></div>
                    <!-- Rule-of-thirds grid lines -->
                    <div style="position:absolute;inset:0;pointer-events:none;">
                        <div style="position:absolute;top:33.3%;left:0;right:0;border-top:1px dashed rgba(255,255,255,.35);"></div>
                        <div style="position:absolute;top:66.6%;left:0;right:0;border-top:1px dashed rgba(255,255,255,.35);"></div>
                        <div style="position:absolute;left:33.3%;top:0;bottom:0;border-left:1px dashed rgba(255,255,255,.35);"></div>
                        <div style="position:absolute;left:66.6%;top:0;bottom:0;border-left:1px dashed rgba(255,255,255,.35);"></div>
                    </div>
                    <!-- Size badge -->
                    <div id="cropSizeBadge" style="position:absolute;bottom:2px;right:4px;font-size:.62rem;color:#fff;background:rgba(0,0,0,.5);padding:.05rem .3rem;border-radius:.2rem;pointer-events:none;"></div>
                </div>
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

            <!-- Aspect ratio quick-set -->
            <div class="form-group">
                <label class="form-label"><i class="fa-solid fa-crop" style="color:var(--cx-primary);"></i> Aspect Ratio</label>
                <div style="display:flex;flex-wrap:wrap;gap:.4rem;">
                    <button type="button" class="btn-ar" data-ar="0">Free</button>
                    <button type="button" class="btn-ar" data-ar="1">1:1</button>
                    <button type="button" class="btn-ar" data-ar="1.333">4:3</button>
                    <button type="button" class="btn-ar" data-ar="1.778">16:9</button>
                    <button type="button" class="btn-ar" data-ar="0.5625">9:16</button>
                    <button type="button" class="btn-ar" data-ar="1.5">3:2</button>
                </div>
            </div>

            <!-- Quality slider -->
            <div class="form-group">
                <label class="form-label" for="cropQuality">
                    <i class="fa-solid fa-star-half-stroke" style="color:var(--cx-primary);"></i>
                    Output Quality: <strong id="cropQualityVal">90</strong>%
                </label>
                <input type="range" name="quality" id="cropQuality" form="cropForm"
                       min="10" max="100" value="90" step="2"
                       style="width:100%;accent-color:var(--cx-primary);"
                       oninput="document.getElementById('cropQualityVal').textContent=this.value">
                <div style="display:flex;justify-content:space-between;font-size:.7rem;color:var(--text-muted);margin-top:.2rem;">
                    <span>Smaller file</span><span>Higher quality</span>
                </div>
            </div>

            <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:.75rem;">
                <i class="fa-solid fa-circle-info" style="color:var(--cx-primary);"></i>
                Drag to move or drag a corner/edge handle to resize the crop area. The same crop rectangle is applied to all uploaded images.
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
.crop-handle {
    position:absolute; width:10px; height:10px;
    background:var(--cx-primary); border:2px solid #fff; border-radius:2px;
    box-shadow:0 1px 3px rgba(0,0,0,.4);
}
.btn-ar {
    padding:.3rem .65rem; font-size:.75rem; border-radius:.4rem;
    background:var(--bg-secondary); border:1px solid var(--border-color);
    color:var(--text-primary); cursor:pointer;
}
.btn-ar.active, .btn-ar:hover { background:var(--cx-primary); color:#fff; border-color:var(--cx-primary); }
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
    var cropBox     = document.getElementById('cropBox');
    var dimsEl      = document.getElementById('imgDims');
    var badge       = document.getElementById('cropSizeBadge');
    var submitBtn   = document.getElementById('submitBtn');
    var resultCard  = document.getElementById('resultCard');
    var resultBody  = document.getElementById('resultBody');
    var naturalW = 0, naturalH = 0;
    var selectedFiles = [];
    var lockedAR = 0; // 0 = free

    // ── File handling ────────────────────────────────────────────────────
    zone.addEventListener('click', function () { input.click(); });
    zone.addEventListener('dragover', function (e) { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', function () { zone.classList.remove('drag-over'); });
    zone.addEventListener('drop', function (e) { e.preventDefault(); zone.classList.remove('drag-over'); loadFiles(Array.from(e.dataTransfer.files)); });
    input.addEventListener('change', function () { if (input.files.length) loadFiles(Array.from(input.files)); input.value = ''; });

    function loadFiles(files) {
        files.forEach(function (f) {
            var ext = f.name.split('.').pop().toLowerCase();
            if (ALLOWED.indexOf(ext) === -1) { alert(f.name + ': unsupported format'); return; }
            if (f.size > MAX_MB * 1024 * 1024) { alert(f.name + ': file too large (max ' + MAX_MB + ' MB)'); return; }
            if (selectedFiles.length < MAX_FILES) selectedFiles.push(f);
        });
        renderFileList();
        if (selectedFiles.length > 0) loadPreview(selectedFiles[0]);
    }

    function renderFileList() {
        if (!selectedFiles.length) { fileListEl.innerHTML = ''; return; }
        var html = '<strong>' + selectedFiles.length + ' file(s) selected:</strong><ul style="margin:.3rem 0 0 1rem;padding:0;">';
        selectedFiles.forEach(function (f, i) {
            html += '<li>' + esc(f.name) + ' <span style="color:var(--text-muted);">(' + fmtSize(f.size) + ')</span>'
                  + ' <button type="button" onclick="window._cropRemove(' + i + ')" style="background:none;border:none;color:var(--cx-danger);cursor:pointer;">&times;</button></li>';
        });
        html += '</ul>';
        fileListEl.innerHTML = html;
        zone.classList.toggle('has-file', selectedFiles.length > 0);
    }
    window._cropRemove = function (i) {
        selectedFiles.splice(i, 1); renderFileList();
        if (!selectedFiles.length) { previewWrap.style.display = 'none'; dimsEl.style.display = 'none'; }
        else loadPreview(selectedFiles[0]);
    };

    function loadPreview(f) {
        var url = URL.createObjectURL(f);
        previewImg.onload = function () {
            naturalW = previewImg.naturalWidth;
            naturalH = previewImg.naturalHeight;
            dimsEl.textContent = 'Image: ' + naturalW + ' × ' + naturalH + ' px';
            dimsEl.style.display = '';
            previewWrap.style.display = '';
            // Set default crop to full image
            document.getElementById('cropX').value = 0;
            document.getElementById('cropY').value = 0;
            document.getElementById('cropW').value = naturalW;
            document.getElementById('cropH').value = naturalH;
            renderCropBox();
        };
        previewImg.src = url;
    }

    // ── Crop box rendering ───────────────────────────────────────────────
    function getScale() { return previewImg.offsetWidth / naturalW; }

    function renderCropBox() {
        var scale = getScale();
        var x = clamp(parseNum('cropX'), 0, naturalW - 1);
        var y = clamp(parseNum('cropY'), 0, naturalH - 1);
        var w = clamp(parseNum('cropW'), 1, naturalW - x);
        var h = clamp(parseNum('cropH'), 1, naturalH - y);
        cropBox.style.left   = (x * scale) + 'px';
        cropBox.style.top    = (y * scale) + 'px';
        cropBox.style.width  = (w * scale) + 'px';
        cropBox.style.height = (h * scale) + 'px';
        // masks
        var pw = previewImg.offsetWidth, ph = previewImg.offsetHeight;
        document.getElementById('maskTop').style.height    = (y * scale) + 'px';
        document.getElementById('maskBottom').style.height = (ph - (y + h) * scale) + 'px';
        document.getElementById('maskLeft').style.cssText  += ';width:' + (x * scale) + 'px;top:' + (y * scale) + 'px;height:' + (h * scale) + 'px;';
        document.getElementById('maskRight').style.cssText += ';width:' + (pw - (x + w) * scale) + 'px;top:' + (y * scale) + 'px;height:' + (h * scale) + 'px;';
        badge.textContent = w + '×' + h;
    }

    ['cropX','cropY','cropW','cropH'].forEach(function (id) {
        document.getElementById(id).addEventListener('input', function () {
            if (lockedAR > 0 && (id === 'cropW' || id === 'cropH')) {
                if (id === 'cropW') document.getElementById('cropH').value = Math.round(parseNum('cropW') / lockedAR);
                else document.getElementById('cropW').value = Math.round(parseNum('cropH') * lockedAR);
            }
            renderCropBox();
        });
    });
    window.addEventListener('resize', function () { if (naturalW) renderCropBox(); });

    // ── Drag-to-move ─────────────────────────────────────────────────────
    var drag = null;
    cropBox.addEventListener('mousedown', function (e) {
        if (e.target.classList.contains('crop-handle')) return; // handled by resize
        drag = { type:'move', startX:e.clientX, startY:e.clientY,
                 ox:parseNum('cropX'), oy:parseNum('cropY') };
        e.preventDefault();
    });

    // ── Resize handles ───────────────────────────────────────────────────
    document.querySelectorAll('.crop-handle').forEach(function (h) {
        h.addEventListener('mousedown', function (e) {
            drag = { type:'resize', dir:this.dataset.dir,
                     startX:e.clientX, startY:e.clientY,
                     ox:parseNum('cropX'), oy:parseNum('cropY'),
                     ow:parseNum('cropW'), oh:parseNum('cropH') };
            e.preventDefault(); e.stopPropagation();
        });
    });

    document.addEventListener('mousemove', function (e) {
        if (!drag || !naturalW) return;
        var scale = getScale();
        var dx = Math.round((e.clientX - drag.startX) / scale);
        var dy = Math.round((e.clientY - drag.startY) / scale);

        if (drag.type === 'move') {
            var w = parseNum('cropW'), h = parseNum('cropH');
            document.getElementById('cropX').value = clamp(drag.ox + dx, 0, naturalW - w);
            document.getElementById('cropY').value = clamp(drag.oy + dy, 0, naturalH - h);
        } else {
            var nx = drag.ox, ny = drag.oy, nw = drag.ow, nh = drag.oh;
            var d  = drag.dir;
            if (d.indexOf('e') !== -1) nw = clamp(drag.ow + dx, 1, naturalW - drag.ox);
            if (d.indexOf('s') !== -1) nh = clamp(drag.oh + dy, 1, naturalH - drag.oy);
            if (d.indexOf('w') !== -1) { var x2 = clamp(drag.ox + dx, 0, drag.ox + drag.ow - 1); nw = drag.ow - (x2 - drag.ox); nx = x2; }
            if (d.indexOf('n') !== -1) { var y2 = clamp(drag.oy + dy, 0, drag.oy + drag.oh - 1); nh = drag.oh - (y2 - drag.oy); ny = y2; }
            if (lockedAR > 0) {
                if (d.indexOf('n') !== -1 || d.indexOf('s') !== -1) nw = Math.round(nh * lockedAR);
                else nh = Math.round(nw / lockedAR);
            }
            document.getElementById('cropX').value = nx;
            document.getElementById('cropY').value = ny;
            document.getElementById('cropW').value = Math.max(1, nw);
            document.getElementById('cropH').value = Math.max(1, nh);
        }
        renderCropBox();
    });
    document.addEventListener('mouseup', function () { drag = null; });

    // ── Aspect ratio buttons ─────────────────────────────────────────────
    document.querySelectorAll('.btn-ar').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.btn-ar').forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');
            lockedAR = parseFloat(this.dataset.ar);
            if (lockedAR > 0 && naturalW) {
                var w = parseNum('cropW');
                document.getElementById('cropH').value = Math.round(w / lockedAR);
                renderCropBox();
            }
        });
    });
    // Activate "Free" by default
    document.querySelector('.btn-ar[data-ar="0"]').classList.add('active');

    // ── Helpers ──────────────────────────────────────────────────────────
    function parseNum(id) { return parseInt(document.getElementById(id).value) || 0; }
    function clamp(v, mn, mx) { return Math.min(Math.max(v, mn), mx); }
    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    function fmtSize(b) { return b >= 1048576 ? (b / 1048576).toFixed(2) + ' MB' : (b / 1024).toFixed(1) + ' KB'; }

    // ── Form submit ──────────────────────────────────────────────────────
    document.getElementById('cropForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!selectedFiles.length) { alert('Please select at least one image.'); return; }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner" style="animation:cx-spin 1s linear infinite;"></i> Cropping…';

        var fd = new FormData();
        fd.append('_token',       document.querySelector('[name="_token"]').value);
        fd.append('x',            document.getElementById('cropX').value);
        fd.append('y',            document.getElementById('cropY').value);
        fd.append('crop_width',   document.getElementById('cropW').value);
        fd.append('crop_height',  document.getElementById('cropH').value);
        fd.append('quality',      document.getElementById('cropQuality').value);
        selectedFiles.forEach(function (f) { fd.append('images[]', f); });

        try {
            var res  = await fetch('/projects/convertx/img-crop', { method:'POST', body:fd, headers:{'Accept':'application/json'} });
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
                html += '<a href="/projects/convertx/pdf-tools/download/' + data.token + '" class="btn btn-success">'
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
