<?php
/**
 * ConvertX – Watermark Image View
 * Features: live canvas preview, drag-to-position, rotation slider, quality slider
 */
$currentView  = 'img-watermark';
$csrfToken    = \Core\Security::generateCsrfToken();
$hasGd        = $hasGd        ?? false;
$maxFiles     = $maxFiles     ?? 20;
$maxSizeMb    = $maxSizeMb    ?? 50;
$allowedExts  = $allowedExts  ?? ['jpg','jpeg','png','gif','webp','bmp'];
$allowedLabel = implode(', ', array_map('strtoupper', array_unique($allowedExts)));
$acceptAttr   = implode(',', array_map(fn($e) => '.'.$e, $allowedExts));
?>

<div class="page-header">
    <h1><i class="fa-solid fa-stamp" style="color:var(--cx-primary);"></i> Watermark Images</h1>
    <p>Add a text or image watermark — live preview, drag to position — upload up to <?= (int)$maxFiles ?> images at once</p>
</div>

<?php if (!$hasGd): ?>
<div class="cx-notice">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>
        <strong>PHP GD extension is not loaded</strong>
        Watermarking requires the GD extension. Install with:
        <code style="background:var(--bg-tertiary);padding:.1rem .4rem;border-radius:.3rem;font-size:.82rem;">apt install php-gd</code>
    </div>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;align-items:start;" id="wmGrid">

    <!-- Left col: upload + live canvas preview -->
    <div style="display:flex;flex-direction:column;gap:1.25rem;">

        <!-- Upload card -->
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-images"></i> Select Images</div>
            <form id="wmForm" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <div class="form-group">
                    <div class="upload-zone" id="uploadZone">
                        <i class="fa-solid fa-images upload-icon" style="font-size:1.75rem;color:var(--cx-primary);"></i>
                        <p style="font-weight:600;font-size:.875rem;margin:.35rem 0 .2rem;">Drag &amp; drop images or <strong>click to browse</strong></p>
                        <p style="font-size:.73rem;color:var(--text-muted);"><?= htmlspecialchars($allowedLabel) ?> · max <?= (int)$maxSizeMb ?> MB · up to <?= (int)$maxFiles ?> files</p>
                        <input type="file" name="images[]" id="fileInput" multiple
                               accept="<?= htmlspecialchars($acceptAttr) ?>" style="display:none;">
                    </div>
                    <div id="fileList" style="margin-top:.75rem;display:flex;flex-direction:column;gap:.35rem;"></div>
                </div>
            </form>
        </div>

        <!-- Live preview card -->
        <div class="card" id="previewCard" style="display:none;">
            <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
                <span><i class="fa-solid fa-eye" style="color:var(--cx-primary);"></i> Live Preview</span>
                <span style="font-size:.73rem;color:var(--text-muted);">Drag watermark to reposition</span>
            </div>
            <div style="position:relative;background:#111;border-radius:.4rem;overflow:hidden;">
                <canvas id="wmCanvas" style="display:block;max-width:100%;cursor:crosshair;"></canvas>
            </div>
            <p style="font-size:.72rem;color:var(--text-muted);padding:.4rem .5rem 0;">
                <i class="fa-solid fa-circle-info" style="color:var(--cx-primary);"></i>
                Preview shows first image only. Exact rendering may differ slightly from server output.
            </p>
        </div>
    </div>

    <!-- Right col: watermark settings -->
    <div class="card">
        <div class="card-header"><i class="fa-solid fa-sliders"></i> Watermark Settings</div>

        <!-- Type toggle -->
        <div class="form-group">
            <label class="form-label"><i class="fa-solid fa-toggle-on" style="color:var(--cx-primary);"></i> Watermark Type</label>
            <div style="display:flex;gap:.5rem;">
                <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;flex:1;padding:.5rem .75rem;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.45rem;">
                    <input type="radio" name="wm_type" id="typeText" value="text" form="wmForm" checked> Text
                </label>
                <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;flex:1;padding:.5rem .75rem;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.45rem;">
                    <input type="radio" name="wm_type" id="typeImage" value="image" form="wmForm"> Image
                </label>
            </div>
        </div>

        <!-- Text watermark fields -->
        <div id="textFields">
            <div class="form-group">
                <label class="form-label" for="wmText">Watermark Text</label>
                <input type="text" name="text" id="wmText" form="wmForm"
                       class="form-control" value="© My Watermark" maxlength="120">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <div class="form-group">
                    <label class="form-label" for="fontSize">Font Size (px)</label>
                    <input type="number" name="font_size" id="fontSize" form="wmForm"
                           class="form-control" min="8" max="72" value="24">
                </div>
                <div class="form-group">
                    <label class="form-label" for="wmColor">Color</label>
                    <input type="color" name="color_hex" id="wmColor" form="wmForm"
                           class="form-control" value="#ffffff" style="height:2.5rem;padding:.25rem;">
                </div>
            </div>

            <!-- Rotation slider (text only) -->
            <div class="form-group">
                <label class="form-label" for="wmRotation">
                    <i class="fa-solid fa-rotate-right" style="color:var(--cx-primary);"></i>
                    Text Rotation: <strong id="wmRotationVal">0</strong>°
                </label>
                <input type="range" name="rotation" id="wmRotation" form="wmForm"
                       min="-45" max="45" value="0" step="1"
                       style="width:100%;accent-color:var(--cx-primary);"
                       oninput="document.getElementById('wmRotationVal').textContent=this.value;updatePreview();">
                <div style="display:flex;justify-content:space-between;font-size:.7rem;color:var(--text-muted);margin-top:.2rem;">
                    <span>-45°</span><span>0°</span><span>+45°</span>
                </div>
            </div>
        </div>

        <!-- Image watermark fields -->
        <div id="imageFields" style="display:none;">
            <div class="form-group">
                <label class="form-label">Watermark Image (PNG with transparency recommended)</label>
                <div class="upload-zone" id="wmImgZone" style="padding:.75rem;">
                    <i class="fa-solid fa-image" style="font-size:1.25rem;color:var(--cx-primary);"></i>
                    <p style="font-size:.82rem;margin:.25rem 0 0;">Click to upload watermark image</p>
                    <input type="file" name="watermark_image" id="wmImgInput" form="wmForm"
                           accept=".jpg,.jpeg,.png,.gif,.webp" style="display:none;">
                </div>
                <p id="wmImgName" style="font-size:.78rem;color:var(--text-secondary);margin-top:.35rem;display:none;"></p>
            </div>
        </div>

        <!-- Shared settings -->
        <div class="form-group">
            <label class="form-label" for="opacitySlider">
                <i class="fa-solid fa-droplet-slash" style="color:var(--cx-primary);"></i>
                Opacity: <strong id="opacityVal">50</strong>%
            </label>
            <input type="range" name="opacity" id="opacitySlider" form="wmForm"
                   min="5" max="100" value="50" step="5"
                   style="width:100%;accent-color:var(--cx-primary);"
                   oninput="document.getElementById('opacityVal').textContent=this.value;updatePreview();">
        </div>

        <!-- Position (hidden when dragged, shown for snap presets) -->
        <div class="form-group">
            <label class="form-label" for="wmPosition">
                <i class="fa-solid fa-arrows-to-dot" style="color:var(--cx-primary);"></i>
                Position Preset
            </label>
            <select class="form-control" name="position" id="wmPosition" form="wmForm">
                <option value="bottomright" selected>Bottom Right</option>
                <option value="bottomleft">Bottom Left</option>
                <option value="topright">Top Right</option>
                <option value="topleft">Top Left</option>
                <option value="center">Center</option>
                <option value="custom">Custom (drag on preview)</option>
            </select>
        </div>

        <!-- Hidden custom position fields (filled by drag) -->
        <input type="hidden" name="custom_x_pct" id="customXPct" form="wmForm" value="90">
        <input type="hidden" name="custom_y_pct" id="customYPct" form="wmForm" value="90">

        <!-- Output quality slider -->
        <div class="form-group">
            <label class="form-label" for="wmQuality">
                <i class="fa-solid fa-star-half-stroke" style="color:var(--cx-primary);"></i>
                Output Quality: <strong id="wmQualityVal">90</strong>%
            </label>
            <input type="range" name="quality" id="wmQuality" form="wmForm"
                   min="10" max="100" value="90" step="2"
                   style="width:100%;accent-color:var(--cx-primary);"
                   oninput="document.getElementById('wmQualityVal').textContent=this.value">
            <div style="display:flex;justify-content:space-between;font-size:.7rem;color:var(--text-muted);margin-top:.2rem;">
                <span>Smaller file</span><span>Higher quality</span>
            </div>
        </div>

        <button type="submit" form="wmForm" class="btn btn-primary" id="submitBtn"
                style="width:100%;justify-content:center;padding:.825rem;"
                <?= !$hasGd ? 'disabled title="GD extension required"' : '' ?>>
            <i class="fa-solid fa-stamp"></i> Apply Watermark
        </button>
    </div>

</div>

<!-- Result card -->
<div id="resultCard" class="card" style="display:none;margin-top:1.25rem;">
    <div class="card-header"><i class="fa-solid fa-check-circle"></i> Watermark Applied</div>
    <div id="resultBody" style="padding:.875rem;"></div>
</div>

<style>
@media (max-width:768px) { #wmGrid { grid-template-columns:1fr !important; } }
.cx-file-item { display:flex;align-items:center;gap:.5rem;padding:.45rem .625rem;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.45rem;font-size:.8rem; }
.cx-file-name { flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.cx-file-size { color:var(--text-muted);flex-shrink:0;font-size:.73rem; }
.cx-file-remove { background:none;border:none;color:var(--cx-danger);cursor:pointer;padding:.2rem .35rem;border-radius:.35rem;opacity:.7; }
.cx-file-remove:hover { opacity:1;background:rgba(239,68,68,.1); }
</style>

<script>
(function () {
    var MAX_FILES = <?= (int)$maxFiles ?>;
    var MAX_MB    = <?= (int)$maxSizeMb ?>;
    var ALLOWED   = <?= json_encode(array_values($allowedExts)) ?>;

    var zone       = document.getElementById('uploadZone');
    var input      = document.getElementById('fileInput');
    var listEl     = document.getElementById('fileList');
    var submitBtn  = document.getElementById('submitBtn');
    var resultCard = document.getElementById('resultCard');
    var resultBody = document.getElementById('resultBody');
    var canvas     = document.getElementById('wmCanvas');
    var ctx        = canvas.getContext('2d');
    var previewCard= document.getElementById('previewCard');
    var selectedFiles = [];

    // ── Canvas state ─────────────────────────────────────────────────────
    var bgImg       = null;
    var wmImgObj    = null; // loaded watermark image element
    var dragState   = null;
    // Watermark position in canvas coords (% of canvas size)
    var wmXPct = 90, wmYPct = 90;

    // ── File handling ─────────────────────────────────────────────────────
    zone.addEventListener('click', function (e) { if (!e.target.closest('.cx-file-item')) input.click(); });
    zone.addEventListener('dragover', function (e) { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', function () { zone.classList.remove('drag-over'); });
    zone.addEventListener('drop', function (e) { e.preventDefault(); zone.classList.remove('drag-over'); addFiles(Array.from(e.dataTransfer.files)); });
    input.addEventListener('change', function () { addFiles(Array.from(input.files)); input.value = ''; });

    function addFiles(files) {
        files.forEach(function (f) {
            var ext = f.name.split('.').pop().toLowerCase();
            if (ALLOWED.indexOf(ext) === -1) return;
            if (f.size > MAX_MB * 1024 * 1024) { alert(f.name + ': too large (max ' + MAX_MB + ' MB)'); return; }
            var dup = selectedFiles.some(function (sf) { return sf.name === f.name && sf.size === f.size; });
            if (!dup && selectedFiles.length < MAX_FILES) selectedFiles.push(f);
        });
        renderList();
        if (selectedFiles.length > 0 && !bgImg) loadBgPreview(selectedFiles[0]);
    }

    function renderList() {
        listEl.innerHTML = '';
        if (!selectedFiles.length) { zone.classList.remove('has-file'); return; }
        zone.classList.add('has-file');
        selectedFiles.forEach(function (f, i) {
            var size = f.size >= 1048576 ? (f.size / 1048576).toFixed(1) + ' MB' : (f.size / 1024).toFixed(1) + ' KB';
            var item = document.createElement('div');
            item.className = 'cx-file-item';
            item.innerHTML = '<i class="fa-solid fa-file-image" style="color:var(--cx-primary);flex-shrink:0;font-size:.8rem;"></i>'
                           + '<span class="cx-file-name">' + esc(f.name) + '</span>'
                           + '<span class="cx-file-size">' + size + '</span>'
                           + '<button type="button" class="cx-file-remove" data-idx="' + i + '"><i class="fa-solid fa-xmark"></i></button>';
            item.querySelector('.cx-file-remove').addEventListener('click', function () {
                selectedFiles.splice(parseInt(this.dataset.idx), 1); renderList();
                if (!selectedFiles.length) { bgImg = null; previewCard.style.display = 'none'; }
            });
            listEl.appendChild(item);
        });
        var cnt = document.createElement('div');
        cnt.style.cssText = 'font-size:.78rem;color:var(--text-secondary);margin-top:.25rem;';
        cnt.innerHTML = '<i class="fa-solid fa-check-circle" style="color:var(--cx-success);"></i> ' + selectedFiles.length + ' image(s) selected';
        listEl.appendChild(cnt);
    }

    function loadBgPreview(f) {
        var img = new Image();
        img.onload = function () { bgImg = img; previewCard.style.display = ''; positionFromPreset(); updatePreview(); };
        img.src = URL.createObjectURL(f);
    }

    // ── Watermark image input ─────────────────────────────────────────────
    var wmImgZone  = document.getElementById('wmImgZone');
    var wmImgInput = document.getElementById('wmImgInput');
    var wmImgName  = document.getElementById('wmImgName');
    wmImgZone.addEventListener('click', function () { wmImgInput.click(); });
    wmImgInput.addEventListener('change', function () {
        if (!wmImgInput.files[0]) return;
        wmImgName.textContent = wmImgInput.files[0].name; wmImgName.style.display = '';
        var img = new Image();
        img.onload = function () { wmImgObj = img; updatePreview(); };
        img.src = URL.createObjectURL(wmImgInput.files[0]);
    });

    // ── Type toggle ───────────────────────────────────────────────────────
    document.querySelectorAll('[name="wm_type"]').forEach(function (r) {
        r.addEventListener('change', function () {
            var isImg = this.value === 'image';
            document.getElementById('textFields').style.display  = isImg ? 'none' : '';
            document.getElementById('imageFields').style.display = isImg ? '' : 'none';
            updatePreview();
        });
    });

    // ── Position preset ───────────────────────────────────────────────────
    document.getElementById('wmPosition').addEventListener('change', function () {
        if (this.value !== 'custom') positionFromPreset();
        updatePreview();
    });

    function positionFromPreset() {
        var p = document.getElementById('wmPosition').value;
        var map = { topleft:[5,5], topright:[95,5], bottomleft:[5,95], bottomright:[90,90], center:[50,50], custom:[wmXPct,wmYPct] };
        var coords = map[p] || [90,90];
        wmXPct = coords[0]; wmYPct = coords[1];
        document.getElementById('customXPct').value = wmXPct;
        document.getElementById('customYPct').value = wmYPct;
    }

    // ── Live preview drawing ──────────────────────────────────────────────
    function updatePreview() {
        if (!bgImg) return;
        var maxW = previewCard.offsetWidth - 40;
        var scale = Math.min(1, maxW / bgImg.naturalWidth);
        canvas.width  = Math.round(bgImg.naturalWidth  * scale);
        canvas.height = Math.round(bgImg.naturalHeight * scale);
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(bgImg, 0, 0, canvas.width, canvas.height);

        var opacity = parseInt(document.getElementById('opacitySlider').value) / 100;
        ctx.globalAlpha = opacity;

        var wmType = document.querySelector('[name="wm_type"]:checked').value;
        if (wmType === 'image' && wmImgObj) {
            // Image watermark
            var wmScale  = Math.min(0.3, 200 / Math.max(wmImgObj.naturalWidth, wmImgObj.naturalHeight));
            var wmW = wmImgObj.naturalWidth * wmScale;
            var wmH = wmImgObj.naturalHeight * wmScale;
            var px  = (wmXPct / 100) * (canvas.width  - wmW);
            var py  = (wmYPct / 100) * (canvas.height - wmH);
            ctx.drawImage(wmImgObj, px, py, wmW, wmH);
        } else {
            // Text watermark
            var text     = document.getElementById('wmText').value || 'Watermark';
            var fontSize = parseInt(document.getElementById('fontSize').value) || 24;
            var scaledFs = fontSize * scale;
            var color    = document.getElementById('wmColor').value;
            var rotation = parseInt(document.getElementById('wmRotation').value) * Math.PI / 180;
            ctx.font      = 'bold ' + scaledFs + 'px sans-serif';
            ctx.fillStyle = color;
            var tm = ctx.measureText(text);
            var tw = tm.width, th = scaledFs;
            var px = (wmXPct / 100) * (canvas.width  - tw);
            var py = (wmYPct / 100) * (canvas.height - th) + th;
            ctx.save();
            ctx.translate(px + tw / 2, py - th / 2);
            ctx.rotate(-rotation); // canvas rotation is clockwise when positive in our convention
            ctx.fillText(text, -tw / 2, th / 2);
            ctx.restore();
        }
        ctx.globalAlpha = 1;
    }

    // Input listeners for live preview
    ['wmText','fontSize','wmColor'].forEach(function (id) {
        document.getElementById(id).addEventListener('input', updatePreview);
    });

    // ── Canvas drag to reposition ─────────────────────────────────────────
    canvas.addEventListener('mousedown', function (e) {
        var r = canvas.getBoundingClientRect();
        dragState = { startX: e.clientX - r.left, startY: e.clientY - r.top };
        document.getElementById('wmPosition').value = 'custom';
        e.preventDefault();
    });
    document.addEventListener('mousemove', function (e) {
        if (!dragState) return;
        var r = canvas.getBoundingClientRect();
        var cx = e.clientX - r.left, cy = e.clientY - r.top;
        wmXPct = Math.max(0, Math.min(100, cx / canvas.width  * 100));
        wmYPct = Math.max(0, Math.min(100, cy / canvas.height * 100));
        document.getElementById('customXPct').value = wmXPct.toFixed(1);
        document.getElementById('customYPct').value = wmYPct.toFixed(1);
        updatePreview();
    });
    document.addEventListener('mouseup', function () { dragState = null; });

    // ── Helpers ───────────────────────────────────────────────────────────
    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    // ── Form submit ───────────────────────────────────────────────────────
    document.getElementById('wmForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!selectedFiles.length) { alert('Please select at least one image.'); return; }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner" style="animation:cx-spin 1s linear infinite;"></i> Applying watermark…';

        var fd = new FormData();
        fd.append('_token', document.querySelector('[name="_token"]').value);
        var wmType = document.querySelector('[name="wm_type"]:checked').value;
        if (wmType === 'text') {
            fd.append('text',      document.getElementById('wmText').value);
            fd.append('font_size', document.getElementById('fontSize').value);
            fd.append('color_hex', document.getElementById('wmColor').value);
            fd.append('rotation',  document.getElementById('wmRotation').value);
        } else {
            var wf = wmImgInput.files[0];
            if (wf) fd.append('watermark_image', wf);
        }
        fd.append('opacity',      document.getElementById('opacitySlider').value);
        fd.append('position',     document.getElementById('wmPosition').value);
        fd.append('custom_x_pct', document.getElementById('customXPct').value);
        fd.append('custom_y_pct', document.getElementById('customYPct').value);
        fd.append('quality',      document.getElementById('wmQuality').value);
        selectedFiles.forEach(function (f) { fd.append('images[]', f); });

        try {
            var res  = await fetch('/projects/convertx/img-watermark', { method:'POST', body:fd, headers:{'Accept':'application/json'} });
            var data = await res.json();
            resultCard.style.display = '';
            resultCard.scrollIntoView({ behavior:'smooth', block:'nearest' });

            if (data.success) {
                var html = '';
                if (data.count && data.count > 1) {
                    html += '<p style="font-size:.8rem;color:var(--text-secondary);margin-bottom:.75rem;">'
                          + '<i class="fa-solid fa-circle-info"></i> ' + data.count + ' images watermarked — downloading as ZIP</p>';
                }
                if (data.errors && data.errors.length) {
                    html += '<p style="color:var(--cx-warning);font-size:.8rem;margin-bottom:.75rem;">'
                          + '<i class="fa-solid fa-triangle-exclamation"></i> Skipped: ' + data.errors.map(esc).join(', ') + '</p>';
                }
                html += '<a href="/projects/convertx/pdf-tools/download/' + data.token + '" class="btn btn-success">'
                      + '<i class="fa-solid fa-download"></i> Download ' + esc(data.filename) + '</a>';
                resultBody.innerHTML = html;
            } else {
                resultBody.innerHTML = '<p style="color:var(--cx-danger);"><i class="fa-solid fa-circle-xmark"></i> ' + esc(data.error || 'Watermark failed') + '</p>';
            }
        } catch (err) { alert('Network error: ' + err.message); }

        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-stamp"></i> Apply Watermark';
    });
})();
</script>
