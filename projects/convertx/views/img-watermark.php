<?php
/**
 * ConvertX – Watermark Image View
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
    <p>Add a text or image watermark to your images — upload up to <?= (int)$maxFiles ?> images at once</p>
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

<div class="cx-batch-grid">

    <!-- Left: file picker -->
    <div class="card">
        <div class="card-header"><i class="fa-solid fa-images"></i> Select Images</div>

        <form id="wmForm" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-file-circle-plus" style="color:var(--cx-primary);"></i>
                    Upload Images
                    <span style="font-size:.73rem;font-weight:400;color:var(--text-muted);margin-left:.4rem;">Max <?= (int)$maxFiles ?> · <?= htmlspecialchars($allowedLabel) ?></span>
                </label>
                <div class="upload-zone" id="uploadZone">
                    <i class="fa-solid fa-images upload-icon" style="font-size:1.75rem;color:var(--cx-primary);"></i>
                    <p style="font-weight:600;font-size:.875rem;margin:.35rem 0 .2rem;">Drag &amp; drop images or <strong>click to browse</strong></p>
                    <p style="font-size:.73rem;color:var(--text-muted);"><?= htmlspecialchars($allowedLabel) ?> · max <?= (int)$maxSizeMb ?> MB each</p>
                    <input type="file" name="images[]" id="fileInput" multiple
                           accept="<?= htmlspecialchars($acceptAttr) ?>" style="display:none;">
                </div>
                <div id="fileList" style="margin-top:.75rem;display:flex;flex-direction:column;gap:.35rem;"></div>
            </div>
        </form>
    </div>

    <!-- Right: watermark settings -->
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
                    <label class="form-label" for="fontSize">Font Size</label>
                    <input type="number" name="font_size" id="fontSize" form="wmForm"
                           class="form-control" min="8" max="72" value="24">
                </div>
                <div class="form-group">
                    <label class="form-label" for="wmColor">Color</label>
                    <input type="color" name="color_hex" id="wmColor" form="wmForm"
                           class="form-control" value="#ffffff" style="height:2.5rem;padding:.25rem;">
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
                   oninput="document.getElementById('opacityVal').textContent=this.value">
        </div>

        <div class="form-group">
            <label class="form-label" for="wmPosition">
                <i class="fa-solid fa-arrows-to-dot" style="color:var(--cx-primary);"></i>
                Position
            </label>
            <select class="form-control" name="position" id="wmPosition" form="wmForm">
                <option value="bottomright" selected>Bottom Right</option>
                <option value="bottomleft">Bottom Left</option>
                <option value="topright">Top Right</option>
                <option value="topleft">Top Left</option>
                <option value="center">Center</option>
            </select>
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
    var MAX_FILES = <?= (int)$maxFiles ?>;
    var MAX_MB    = <?= (int)$maxSizeMb ?>;
    var ALLOWED   = <?= json_encode(array_values($allowedExts)) ?>;

    var zone       = document.getElementById('uploadZone');
    var input      = document.getElementById('fileInput');
    var listEl     = document.getElementById('fileList');
    var submitBtn  = document.getElementById('submitBtn');
    var resultCard = document.getElementById('resultCard');
    var resultBody = document.getElementById('resultBody');
    var selectedFiles = [];

    // Watermark type toggle
    document.querySelectorAll('[name="wm_type"]').forEach(function (r) {
        r.addEventListener('change', function () {
            var isImg = this.value === 'image';
            document.getElementById('textFields').style.display  = isImg ? 'none' : '';
            document.getElementById('imageFields').style.display = isImg ? ''     : 'none';
        });
    });

    // Watermark image picker
    var wmImgZone  = document.getElementById('wmImgZone');
    var wmImgInput = document.getElementById('wmImgInput');
    var wmImgName  = document.getElementById('wmImgName');
    wmImgZone.addEventListener('click', function () { wmImgInput.click(); });
    wmImgInput.addEventListener('change', function () {
        if (wmImgInput.files[0]) {
            wmImgName.textContent = wmImgInput.files[0].name;
            wmImgName.style.display = '';
        }
    });

    // Main image files
    zone.addEventListener('click', function (e) { if (!e.target.closest('.cx-file-item')) input.click(); });
    zone.addEventListener('dragover', function (e) { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', function () { zone.classList.remove('drag-over'); });
    zone.addEventListener('drop', function (e) { e.preventDefault(); zone.classList.remove('drag-over'); addFiles(Array.from(e.dataTransfer.files)); });
    input.addEventListener('change', function () { addFiles(Array.from(input.files)); input.value=''; });

    function addFiles(files) {
        files.forEach(function (f) {
            var ext = f.name.split('.').pop().toLowerCase();
            if (ALLOWED.indexOf(ext) === -1) return;
            if (f.size > MAX_MB * 1024 * 1024) { alert(f.name + ': too large (max ' + MAX_MB + ' MB)'); return; }
            var dup = selectedFiles.some(function (sf) { return sf.name===f.name && sf.size===f.size; });
            if (!dup && selectedFiles.length < MAX_FILES) selectedFiles.push(f);
        });
        renderList();
    }

    function renderList() {
        listEl.innerHTML = '';
        if (!selectedFiles.length) { zone.classList.remove('has-file'); return; }
        zone.classList.add('has-file');
        selectedFiles.forEach(function (f, i) {
            var size = f.size>=1048576?(f.size/1048576).toFixed(1)+' MB':(f.size/1024).toFixed(1)+' KB';
            var item = document.createElement('div');
            item.className = 'cx-file-item';
            item.innerHTML = '<i class="fa-solid fa-file-image" style="color:var(--cx-primary);flex-shrink:0;font-size:.8rem;"></i>'
                           + '<span class="cx-file-name">'+esc(f.name)+'</span>'
                           + '<span class="cx-file-size">'+size+'</span>'
                           + '<button type="button" class="cx-file-remove" data-idx="'+i+'"><i class="fa-solid fa-xmark"></i></button>';
            item.querySelector('.cx-file-remove').addEventListener('click', function () {
                selectedFiles.splice(parseInt(this.dataset.idx),1); renderList();
            });
            listEl.appendChild(item);
        });
        var cnt = document.createElement('div');
        cnt.style.cssText = 'font-size:.78rem;color:var(--text-secondary);margin-top:.25rem;';
        cnt.innerHTML = '<i class="fa-solid fa-check-circle" style="color:var(--cx-success);"></i> '+selectedFiles.length+' image(s) selected';
        listEl.appendChild(cnt);
    }

    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

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
        } else {
            var wf = wmImgInput.files[0];
            if (wf) fd.append('watermark_image', wf);
        }
        fd.append('opacity',  document.getElementById('opacitySlider').value);
        fd.append('position', document.getElementById('wmPosition').value);
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
                          + '<i class="fa-solid fa-circle-info"></i> '+data.count+' images watermarked — downloading as ZIP</p>';
                }
                if (data.errors && data.errors.length) {
                    html += '<p style="color:var(--cx-warning);font-size:.8rem;margin-bottom:.75rem;">'
                          + '<i class="fa-solid fa-triangle-exclamation"></i> Skipped: '+data.errors.map(esc).join(', ')+'</p>';
                }
                html += '<a href="/projects/convertx/pdf-tools/download/'+data.token+'" class="btn btn-success">'
                      + '<i class="fa-solid fa-download"></i> Download '+esc(data.filename)+'</a>';
                resultBody.innerHTML = html;
            } else {
                resultBody.innerHTML = '<p style="color:var(--cx-danger);"><i class="fa-solid fa-circle-xmark"></i> '+esc(data.error||'Watermark failed')+'</p>';
            }
        } catch (err) { alert('Network error: '+err.message); }

        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-stamp"></i> Apply Watermark';
    });
})();
</script>
