<?php
/**
 * ConvertX – Resize Images View
 */
$currentView = 'img-resize';
$csrfToken   = \Core\Security::generateCsrfToken();
$hasGd       = $hasGd ?? false;
?>

<div class="page-header">
    <h1><i class="fa-solid fa-expand" style="color:var(--cx-primary);"></i> Resize Images</h1>
    <p>Resize images by exact pixel dimensions or percentage — upload up to 20 images at once</p>
</div>

<?php if (!$hasGd): ?>
<div class="cx-notice">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>
        <strong>PHP GD extension is not loaded</strong>
        Image resizing requires the GD extension. Install with:
        <code style="background:var(--bg-tertiary);padding:.1rem .4rem;border-radius:.3rem;font-size:.82rem;">apt install php-gd</code>
    </div>
</div>
<?php endif; ?>

<div class="cx-batch-grid">

    <!-- Left: file picker -->
    <div class="card">
        <div class="card-header"><i class="fa-solid fa-images"></i> Select Images</div>

        <form id="imgForm" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-file-circle-plus" style="color:var(--cx-primary);"></i>
                    Upload Images
                    <span style="font-size:.73rem;font-weight:400;color:var(--text-muted);margin-left:.4rem;">Max 20 · JPG, PNG, GIF, WebP, BMP</span>
                </label>
                <div class="upload-zone" id="uploadZone">
                    <i class="fa-solid fa-images upload-icon" style="font-size:1.75rem;color:var(--cx-primary);"></i>
                    <p style="font-weight:600;font-size:.875rem;margin:.35rem 0 .2rem;">Drag &amp; drop images or <strong>click to browse</strong></p>
                    <p style="font-size:.73rem;color:var(--text-muted);">JPG, PNG, GIF, WebP, BMP · max 50 MB each</p>
                    <input type="file" name="images[]" id="fileInput" multiple
                           accept=".jpg,.jpeg,.png,.gif,.webp,.bmp" style="display:none;">
                </div>
                <div id="fileList" style="margin-top:.75rem;display:flex;flex-direction:column;gap:.35rem;"></div>
            </div>
        </form>
    </div>

    <!-- Right: settings -->
    <div class="card">
        <div class="card-header"><i class="fa-solid fa-sliders"></i> Resize Settings</div>

        <!-- Resize mode -->
        <div class="form-group">
            <label class="form-label"><i class="fa-solid fa-ruler" style="color:var(--cx-primary);"></i> Resize Mode</label>
            <div style="display:flex;gap:.5rem;">
                <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;flex:1;padding:.5rem .75rem;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.45rem;">
                    <input type="radio" name="resize_mode" id="modePixel" value="pixel" form="imgForm" checked> By Pixel
                </label>
                <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;flex:1;padding:.5rem .75rem;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.45rem;">
                    <input type="radio" name="resize_mode" id="modePercent" value="percent" form="imgForm"> By Percent
                </label>
            </div>
        </div>

        <!-- Pixel fields -->
        <div id="pixelFields">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <div class="form-group">
                    <label class="form-label" for="widthInput">Width (px)</label>
                    <input type="number" name="width" id="widthInput" form="imgForm"
                           class="form-control" min="1" max="20000" value="" placeholder="e.g. 1920">
                </div>
                <div class="form-group">
                    <label class="form-label" for="heightInput">Height (px)</label>
                    <input type="number" name="height" id="heightInput" form="imgForm"
                           class="form-control" min="1" max="20000" value="" placeholder="e.g. 1080">
                </div>
            </div>
            <div class="form-group" style="margin-top:-.25rem;">
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.85rem;">
                    <input type="checkbox" name="maintain_ratio" form="imgForm" value="1" checked>
                    Maintain aspect ratio
                </label>
            </div>
        </div>

        <!-- Percent fields -->
        <div id="percentFields" style="display:none;">
            <div class="form-group">
                <label class="form-label" for="percentSlider">
                    <i class="fa-solid fa-percent" style="color:var(--cx-primary);"></i>
                    Scale: <strong id="percentVal">100</strong>%
                </label>
                <input type="range" name="percent" id="percentSlider" form="imgForm"
                       min="5" max="200" value="100" step="5"
                       style="width:100%;accent-color:var(--cx-primary);"
                       oninput="document.getElementById('percentVal').textContent=this.value">
                <div style="display:flex;justify-content:space-between;font-size:.7rem;color:var(--text-muted);margin-top:.2rem;">
                    <span>5% (tiny)</span><span>100% (original)</span><span>200% (2×)</span>
                </div>
            </div>
        </div>

        <!-- Quality -->
        <div class="form-group">
            <label class="form-label" for="qualitySlider">
                <i class="fa-solid fa-star-half-stroke" style="color:var(--cx-primary);"></i>
                Quality: <strong id="qualityVal">90</strong>%
            </label>
            <input type="range" name="quality" id="qualitySlider" form="imgForm"
                   min="10" max="100" value="90" step="2"
                   style="width:100%;accent-color:var(--cx-primary);"
                   oninput="document.getElementById('qualityVal').textContent=this.value">
        </div>

        <!-- Output format -->
        <div class="form-group">
            <label class="form-label" for="outputFmt">
                <i class="fa-solid fa-arrow-right-arrow-left" style="color:var(--cx-primary);"></i>
                Output Format
            </label>
            <select class="form-control" name="output_format" id="outputFmt" form="imgForm">
                <option value="">Same as input</option>
                <option value="jpg">JPG</option>
                <option value="png">PNG</option>
                <option value="webp">WebP</option>
            </select>
        </div>

        <button type="submit" form="imgForm" class="btn btn-primary" id="submitBtn"
                style="width:100%;justify-content:center;padding:.825rem;"
                <?= !$hasGd ? 'disabled title="GD extension required"' : '' ?>>
            <i class="fa-solid fa-expand"></i> Resize Images
        </button>
    </div>

</div>

<!-- Result card -->
<div id="resultCard" class="card" style="display:none;margin-top:1.25rem;">
    <div class="card-header"><i class="fa-solid fa-check-circle"></i> Resize Complete</div>
    <div id="resultBody" style="padding:.875rem;"></div>
</div>

<style>
.cx-batch-grid { display:grid; grid-template-columns:1.15fr 1fr; gap:1.25rem; align-items:start; }
@media (max-width:768px) { .cx-batch-grid { grid-template-columns:1fr; } }
.cx-file-item {
    display:flex; align-items:center; gap:.5rem; padding:.45rem .625rem;
    background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:.45rem; font-size:.8rem;
}
.cx-file-name { flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.cx-file-size { color:var(--text-muted); flex-shrink:0; font-size:.73rem; }
.cx-file-remove { background:none; border:none; color:var(--cx-danger); cursor:pointer; padding:.2rem .35rem; border-radius:.35rem; opacity:.7; }
.cx-file-remove:hover { opacity:1; background:rgba(239,68,68,.1); }
</style>

<script>
(function () {
    var zone       = document.getElementById('uploadZone');
    var input      = document.getElementById('fileInput');
    var listEl     = document.getElementById('fileList');
    var submitBtn  = document.getElementById('submitBtn');
    var resultCard = document.getElementById('resultCard');
    var resultBody = document.getElementById('resultBody');
    var selectedFiles = [];

    // Mode toggle
    document.querySelectorAll('[name="resize_mode"]').forEach(function (r) {
        r.addEventListener('change', function () {
            var isPercent = this.value === 'percent';
            document.getElementById('pixelFields').style.display   = isPercent ? 'none' : '';
            document.getElementById('percentFields').style.display = isPercent ? ''     : 'none';
        });
    });

    zone.addEventListener('click', function (e) { if (!e.target.closest('.cx-file-item')) input.click(); });
    zone.addEventListener('dragover', function (e) { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', function () { zone.classList.remove('drag-over'); });
    zone.addEventListener('drop', function (e) {
        e.preventDefault(); zone.classList.remove('drag-over');
        addFiles(Array.from(e.dataTransfer.files));
    });
    input.addEventListener('change', function () { addFiles(Array.from(input.files)); input.value = ''; });

    function addFiles(files) {
        var ALLOWED = ['jpg','jpeg','png','gif','webp','bmp'];
        files.forEach(function (f) {
            var ext = f.name.split('.').pop().toLowerCase();
            if (ALLOWED.indexOf(ext) === -1) return;
            var dup = selectedFiles.some(function (sf) { return sf.name===f.name && sf.size===f.size; });
            if (!dup && selectedFiles.length < 20) selectedFiles.push(f);
        });
        renderList();
    }

    function renderList() {
        listEl.innerHTML = '';
        if (!selectedFiles.length) { zone.classList.remove('has-file'); return; }
        zone.classList.add('has-file');
        selectedFiles.forEach(function (f, i) {
            var size = f.size >= 1048576 ? (f.size/1048576).toFixed(1)+' MB' : (f.size/1024).toFixed(1)+' KB';
            var item = document.createElement('div');
            item.className = 'cx-file-item';
            item.innerHTML = '<i class="fa-solid fa-file-image" style="color:var(--cx-primary);flex-shrink:0;font-size:.8rem;"></i>'
                           + '<span class="cx-file-name">'+esc(f.name)+'</span>'
                           + '<span class="cx-file-size">'+size+'</span>'
                           + '<button type="button" class="cx-file-remove" data-idx="'+i+'"><i class="fa-solid fa-xmark"></i></button>';
            item.querySelector('.cx-file-remove').addEventListener('click', function () {
                selectedFiles.splice(parseInt(this.dataset.idx), 1); renderList();
            });
            listEl.appendChild(item);
        });
        var cnt = document.createElement('div');
        cnt.style.cssText = 'font-size:.78rem;color:var(--text-secondary);margin-top:.25rem;';
        cnt.innerHTML = '<i class="fa-solid fa-check-circle" style="color:var(--cx-success);"></i> '+selectedFiles.length+' image(s) selected';
        listEl.appendChild(cnt);
    }

    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    function fmtSize(b) { return b >= 1048576 ? (b/1048576).toFixed(2)+' MB' : (b/1024).toFixed(1)+' KB'; }

    document.getElementById('imgForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!selectedFiles.length) { alert('Please select at least one image.'); return; }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner" style="animation:cx-spin 1s linear infinite;"></i> Resizing…';

        var fd = new FormData();
        fd.append('_token', document.querySelector('[name="_token"]').value);
        fd.append('quality', document.getElementById('qualitySlider').value);
        fd.append('output_format', document.getElementById('outputFmt').value);

        var mode = document.querySelector('[name="resize_mode"]:checked').value;
        if (mode === 'percent') {
            fd.append('percent', document.getElementById('percentSlider').value);
        } else {
            fd.append('width',  document.getElementById('widthInput').value);
            fd.append('height', document.getElementById('heightInput').value);
            if (document.querySelector('[name="maintain_ratio"]').checked) {
                fd.append('maintain_ratio', '1');
            }
        }
        selectedFiles.forEach(function (f) { fd.append('images[]', f); });

        try {
            var res  = await fetch('/projects/convertx/img-resize', { method:'POST', body:fd });
            var data = await res.json();
            resultCard.style.display = '';
            resultCard.scrollIntoView({ behavior:'smooth', block:'nearest' });

            if (data.success) {
                var html = '';
                if (data.count && data.count > 1) {
                    html += '<p style="font-size:.8rem;color:var(--text-secondary);margin-bottom:.75rem;">'
                          + '<i class="fa-solid fa-circle-info"></i> '+data.count+' images resized — downloading as ZIP</p>';
                } else {
                    html += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.875rem;">'
                          + stat(fmtSize(data.original_size), 'Original Size')
                          + stat(fmtSize(data.new_size), 'New Size', 'var(--cx-primary)')
                          + '</div>';
                }
                if (data.errors && data.errors.length) {
                    html += '<p style="color:var(--cx-warning);font-size:.8rem;margin-bottom:.75rem;">'
                          + '<i class="fa-solid fa-triangle-exclamation"></i> Skipped: '+data.errors.map(esc).join(', ')+'</p>';
                }
                html += '<a href="/projects/convertx/pdf-tools/download/'+data.token+'" class="btn btn-success">'
                      + '<i class="fa-solid fa-download"></i> Download '+esc(data.filename)+'</a>';
                resultBody.innerHTML = html;
            } else {
                resultBody.innerHTML = '<p style="color:var(--cx-danger);"><i class="fa-solid fa-circle-xmark"></i> '+esc(data.error||'Resize failed')+'</p>';
            }
        } catch (err) { alert('Network error: '+err.message); }

        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-expand"></i> Resize Images';
    });

    function stat(val, label, color) {
        return '<div style="text-align:center;padding:.75rem;background:var(--bg-secondary);border-radius:.5rem;">'
             + '<div style="font-size:1.2rem;font-weight:700;color:'+(color||'var(--text-primary)')+';">'+val+'</div>'
             + '<div style="font-size:.73rem;color:var(--text-muted);">'+label+'</div></div>';
    }
})();
</script>
