<?php
/**
 * ConvertX – Compress Images View
 */
$currentView = 'img-compress';
$csrfToken   = \Core\Security::generateCsrfToken();
$hasGd       = $hasGd ?? false;
?>

<div class="page-header">
    <h1><i class="fa-solid fa-image" style="color:var(--cx-primary);"></i> Compress Images</h1>
    <p>Reduce image file sizes while controlling quality — upload up to 20 images at once</p>
</div>

<?php if (!$hasGd): ?>
<div class="cx-notice">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>
        <strong>PHP GD extension is not loaded</strong>
        Image compression requires the GD extension. Enable it in <code>php.ini</code> or install with:
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
        <div class="card-header"><i class="fa-solid fa-sliders"></i> Compression Settings</div>

        <!-- Quality slider -->
        <div class="form-group">
            <label class="form-label" for="qualitySlider">
                <i class="fa-solid fa-star-half-stroke" style="color:var(--cx-primary);"></i>
                Quality: <strong id="qualityVal">82</strong>%
            </label>
            <input type="range" name="quality" id="qualitySlider" form="imgForm"
                   min="10" max="100" value="82" step="2"
                   style="width:100%;accent-color:var(--cx-primary);"
                   oninput="document.getElementById('qualityVal').textContent=this.value">
            <div style="display:flex;justify-content:space-between;font-size:.7rem;color:var(--text-muted);margin-top:.2rem;">
                <span>Smaller file</span><span>Higher quality</span>
            </div>
        </div>

        <!-- Max width -->
        <div class="form-group">
            <label class="form-label" for="maxWidth">
                <i class="fa-solid fa-arrows-left-right" style="color:var(--cx-primary);"></i>
                Max Width (px)
            </label>
            <input type="number" name="max_width" id="maxWidth" form="imgForm"
                   class="form-control" min="0" max="10000" value="0" placeholder="0 = no resize">
            <p style="font-size:.73rem;color:var(--text-muted);margin-top:.35rem;">
                Leave 0 to keep original dimensions. Images wider than this value will be scaled down proportionally.
            </p>
        </div>

        <!-- Output format -->
        <div class="form-group">
            <label class="form-label" for="outputFmt">
                <i class="fa-solid fa-arrow-right-arrow-left" style="color:var(--cx-primary);"></i>
                Output Format
            </label>
            <select class="form-control" name="output_format" id="outputFmt" form="imgForm">
                <option value="">Same as input</option>
                <option value="jpg">JPG — best for photos</option>
                <option value="png">PNG — lossless with transparency</option>
                <option value="webp">WebP — modern web format</option>
            </select>
        </div>

        <button type="submit" form="imgForm" class="btn btn-primary" id="submitBtn"
                style="width:100%;justify-content:center;padding:.825rem;"
                <?= !$hasGd ? 'disabled title="GD extension required"' : '' ?>>
            <i class="fa-solid fa-compress"></i> Compress Images
        </button>
    </div>

</div>

<!-- Result card -->
<div id="resultCard" class="card" style="display:none;margin-top:1.25rem;">
    <div class="card-header"><i class="fa-solid fa-check-circle"></i> Compression Complete</div>
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
.cx-file-count { font-size:.78rem; color:var(--text-secondary); margin-top:.25rem; }
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
    var ALLOWED_EXTS  = ['jpg','jpeg','png','gif','webp','bmp'];

    zone.addEventListener('click', function (e) { if (!e.target.closest('.cx-file-item')) input.click(); });
    zone.addEventListener('dragover', function (e) { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', function () { zone.classList.remove('drag-over'); });
    zone.addEventListener('drop', function (e) {
        e.preventDefault(); zone.classList.remove('drag-over');
        addFiles(Array.from(e.dataTransfer.files));
    });
    input.addEventListener('change', function () { addFiles(Array.from(input.files)); input.value = ''; });

    function addFiles(files) {
        files.forEach(function (f) {
            var ext = f.name.split('.').pop().toLowerCase();
            if (ALLOWED_EXTS.indexOf(ext) === -1) return;
            var dup = selectedFiles.some(function (sf) {
                return sf.name === f.name && sf.size === f.size && sf.lastModified === f.lastModified;
            });
            if (!dup && selectedFiles.length < 20) selectedFiles.push(f);
        });
        renderList();
    }

    function renderList() {
        listEl.innerHTML = '';
        if (!selectedFiles.length) { zone.classList.remove('has-file'); return; }
        zone.classList.add('has-file');
        selectedFiles.forEach(function (f, i) {
            var size = f.size >= 1048576 ? (f.size / 1048576).toFixed(1) + ' MB' : (f.size / 1024).toFixed(1) + ' KB';
            var ext  = f.name.split('.').pop().toLowerCase();
            var icon = ['jpg','jpeg','png','webp','gif','bmp'].indexOf(ext) !== -1 ? 'fa-file-image' : 'fa-file';
            var item = document.createElement('div');
            item.className = 'cx-file-item';
            item.innerHTML = '<i class="fa-solid ' + icon + '" style="color:var(--cx-primary);flex-shrink:0;font-size:.8rem;"></i>'
                           + '<span class="cx-file-name" title="' + esc(f.name) + '">' + esc(f.name) + '</span>'
                           + '<span class="cx-file-size">' + size + '</span>'
                           + '<button type="button" class="cx-file-remove" data-idx="' + i + '" title="Remove"><i class="fa-solid fa-xmark"></i></button>';
            item.querySelector('.cx-file-remove').addEventListener('click', function () {
                selectedFiles.splice(parseInt(this.dataset.idx), 1); renderList();
            });
            listEl.appendChild(item);
        });
        var count = document.createElement('div');
        count.className = 'cx-file-count';
        count.innerHTML = '<i class="fa-solid fa-check-circle" style="color:var(--cx-success);"></i> '
                        + selectedFiles.length + ' image(s) selected';
        listEl.appendChild(count);
    }

    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    function fmtSize(b) { return b >= 1048576 ? (b / 1048576).toFixed(2) + ' MB' : (b / 1024).toFixed(1) + ' KB'; }

    document.getElementById('imgForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!selectedFiles.length) { alert('Please select at least one image.'); return; }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner" style="animation:cx-spin 1s linear infinite;"></i> Compressing…';

        var fd = new FormData();
        fd.append('_token', document.querySelector('[name="_token"]').value);
        fd.append('quality', document.getElementById('qualitySlider').value);
        fd.append('max_width', document.getElementById('maxWidth').value);
        fd.append('output_format', document.getElementById('outputFmt').value);
        selectedFiles.forEach(function (f) { fd.append('images[]', f); });

        try {
            var res  = await fetch('/projects/convertx/img-compress', { method:'POST', body:fd });
            var data = await res.json();
            resultCard.style.display = '';
            resultCard.scrollIntoView({ behavior:'smooth', block:'nearest' });

            if (data.success) {
                var html = '<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-bottom:.875rem;">';
                html += stat(fmtSize(data.original_size), 'Original Total');
                html += stat(fmtSize(data.new_size), 'Compressed Total', 'var(--cx-primary)');
                var savePct = data.saved_pct;
                html += stat((savePct > 0 ? '-' : '') + savePct + '%', 'Reduction', savePct > 0 ? 'var(--cx-success)' : 'var(--cx-warning)');
                html += '</div>';

                if (data.count && data.count > 1) {
                    html += '<p style="font-size:.8rem;color:var(--text-secondary);margin-bottom:.75rem;">'
                          + '<i class="fa-solid fa-circle-info"></i> ' + data.count + ' images compressed — downloading as ZIP</p>';
                }
                if (data.errors && data.errors.length) {
                    html += '<p style="color:var(--cx-warning);font-size:.8rem;margin-bottom:.75rem;">'
                          + '<i class="fa-solid fa-triangle-exclamation"></i> Skipped: ' + data.errors.map(function(e){return esc(e);}).join(', ') + '</p>';
                }
                html += '<a href="/projects/convertx/pdf-tools/download/' + data.token + '" class="btn btn-success">'
                      + '<i class="fa-solid fa-download"></i> Download ' + esc(data.filename) + '</a>';
                resultBody.innerHTML = html;
            } else {
                resultBody.innerHTML = '<p style="color:var(--cx-danger);"><i class="fa-solid fa-circle-xmark"></i> ' + esc(data.error || 'Compression failed') + '</p>';
            }
        } catch (err) { alert('Network error: ' + err.message); }

        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-compress"></i> Compress Images';
    });

    function stat(val, label, color) {
        return '<div style="text-align:center;padding:.75rem;background:var(--bg-secondary);border-radius:.5rem;">'
             + '<div style="font-size:1.2rem;font-weight:700;color:' + (color || 'var(--text-primary)') + ';">' + val + '</div>'
             + '<div style="font-size:.73rem;color:var(--text-muted);">' + label + '</div></div>';
    }
})();
</script>
