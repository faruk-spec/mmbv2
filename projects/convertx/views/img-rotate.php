<?php
/**
 * ConvertX – Rotate Images View
 */
$currentView = 'img-rotate';
$csrfToken   = \Core\Security::generateCsrfToken();
$hasGd       = $hasGd ?? false;
?>

<div class="page-header">
    <h1><i class="fa-solid fa-rotate-right" style="color:var(--cx-primary);"></i> Rotate Images</h1>
    <p>Rotate images by 90°, 180°, 270° or any custom angle — upload up to 20 images at once</p>
</div>

<?php if (!$hasGd): ?>
<div class="cx-notice">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>
        <strong>PHP GD extension is not loaded</strong>
        Image rotation requires the GD extension. Install with:
        <code style="background:var(--bg-tertiary);padding:.1rem .4rem;border-radius:.3rem;font-size:.82rem;">apt install php-gd</code>
    </div>
</div>
<?php endif; ?>

<div class="cx-batch-grid">

    <!-- Left: file picker -->
    <div class="card">
        <div class="card-header"><i class="fa-solid fa-images"></i> Select Images</div>

        <form id="rotateForm" enctype="multipart/form-data">
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
        <div class="card-header"><i class="fa-solid fa-sliders"></i> Rotation Settings</div>

        <div class="form-group">
            <label class="form-label"><i class="fa-solid fa-rotate-right" style="color:var(--cx-primary);"></i> Rotation Angle</label>
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.5rem;margin-bottom:.75rem;">
                <button type="button" class="btn-angle btn-primary" data-deg="90" style="padding:.55rem;font-size:.8rem;border-radius:.45rem;background:var(--cx-primary);color:#fff;border:none;cursor:pointer;">
                    <i class="fa-solid fa-rotate-right"></i> 90° CW
                </button>
                <button type="button" class="btn-angle" data-deg="180" style="padding:.55rem;font-size:.8rem;border-radius:.45rem;background:var(--bg-secondary);color:var(--text-primary);border:1px solid var(--border-color);cursor:pointer;">
                    <i class="fa-solid fa-rotate"></i> 180°
                </button>
                <button type="button" class="btn-angle" data-deg="270" style="padding:.55rem;font-size:.8rem;border-radius:.45rem;background:var(--bg-secondary);color:var(--text-primary);border:1px solid var(--border-color);cursor:pointer;">
                    <i class="fa-solid fa-rotate-left"></i> 90° CCW
                </button>
                <button type="button" class="btn-angle" data-deg="0" style="padding:.55rem;font-size:.8rem;border-radius:.45rem;background:var(--bg-secondary);color:var(--text-primary);border:1px solid var(--border-color);cursor:pointer;">
                    <i class="fa-solid fa-sliders"></i> Custom
                </button>
            </div>
        </div>

        <!-- Custom angle input (hidden by default) -->
        <div id="customAngleWrap" style="display:none;" class="form-group">
            <label class="form-label" for="customDeg">Custom Degrees (clockwise)</label>
            <input type="number" id="customDeg" class="form-control" min="0" max="359" value="45" placeholder="0–359">
        </div>

        <input type="hidden" name="degrees" id="degreesHidden" form="rotateForm" value="90">

        <p style="font-size:.8rem;color:var(--text-secondary);margin-bottom:.75rem;">
            <i class="fa-solid fa-circle-info" style="color:var(--cx-primary);"></i>
            Selected: <strong id="angleDisplay">90° clockwise</strong>
        </p>

        <button type="submit" form="rotateForm" class="btn btn-primary" id="submitBtn"
                style="width:100%;justify-content:center;padding:.825rem;"
                <?= !$hasGd ? 'disabled title="GD extension required"' : '' ?>>
            <i class="fa-solid fa-rotate-right"></i> Rotate Images
        </button>
    </div>

</div>

<!-- Result card -->
<div id="resultCard" class="card" style="display:none;margin-top:1.25rem;">
    <div class="card-header"><i class="fa-solid fa-check-circle"></i> Rotation Complete</div>
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
    var currentDeg = 90;
    var isCustom   = false;

    // Angle buttons
    document.querySelectorAll('.btn-angle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.btn-angle').forEach(function (b) {
                b.style.background = 'var(--bg-secondary)';
                b.style.color      = 'var(--text-primary)';
                b.style.border     = '1px solid var(--border-color)';
            });
            this.style.background = 'var(--cx-primary)';
            this.style.color      = '#fff';
            this.style.border     = 'none';

            var deg = parseInt(this.dataset.deg);
            isCustom = (deg === 0);
            document.getElementById('customAngleWrap').style.display = isCustom ? '' : 'none';

            if (!isCustom) {
                currentDeg = deg;
                document.getElementById('degreesHidden').value = deg;
                var labels = {90:'90° clockwise', 180:'180°', 270:'90° counter-clockwise'};
                document.getElementById('angleDisplay').textContent = labels[deg] || deg+'°';
            } else {
                updateCustom();
            }
        });
    });

    document.getElementById('customDeg').addEventListener('input', updateCustom);
    function updateCustom() {
        currentDeg = Math.abs(parseInt(document.getElementById('customDeg').value)||0) % 360;
        document.getElementById('degreesHidden').value = currentDeg;
        document.getElementById('angleDisplay').textContent = currentDeg + '° clockwise';
    }

    zone.addEventListener('click', function (e) { if (!e.target.closest('.cx-file-item')) input.click(); });
    zone.addEventListener('dragover', function (e) { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', function () { zone.classList.remove('drag-over'); });
    zone.addEventListener('drop', function (e) { e.preventDefault(); zone.classList.remove('drag-over'); addFiles(Array.from(e.dataTransfer.files)); });
    input.addEventListener('change', function () { addFiles(Array.from(input.files)); input.value=''; });

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

    document.getElementById('rotateForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!selectedFiles.length) { alert('Please select at least one image.'); return; }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner" style="animation:cx-spin 1s linear infinite;"></i> Rotating…';

        var fd = new FormData();
        fd.append('_token', document.querySelector('[name="_token"]').value);
        fd.append('degrees', document.getElementById('degreesHidden').value);
        selectedFiles.forEach(function (f) { fd.append('images[]', f); });

        try {
            var res  = await fetch('/projects/convertx/img-rotate', { method:'POST', body:fd });
            var data = await res.json();
            resultCard.style.display = '';
            resultCard.scrollIntoView({ behavior:'smooth', block:'nearest' });

            if (data.success) {
                var html = '';
                if (data.count && data.count > 1) {
                    html += '<p style="font-size:.8rem;color:var(--text-secondary);margin-bottom:.75rem;">'
                          + '<i class="fa-solid fa-circle-info"></i> '+data.count+' images rotated — downloading as ZIP</p>';
                }
                if (data.errors && data.errors.length) {
                    html += '<p style="color:var(--cx-warning);font-size:.8rem;margin-bottom:.75rem;">'
                          + '<i class="fa-solid fa-triangle-exclamation"></i> Skipped: '+data.errors.map(esc).join(', ')+'</p>';
                }
                html += '<a href="/projects/convertx/pdf-tools/download/'+data.token+'" class="btn btn-success">'
                      + '<i class="fa-solid fa-download"></i> Download '+esc(data.filename)+'</a>';
                resultBody.innerHTML = html;
            } else {
                resultBody.innerHTML = '<p style="color:var(--cx-danger);"><i class="fa-solid fa-circle-xmark"></i> '+esc(data.error||'Rotation failed')+'</p>';
            }
        } catch (err) { alert('Network error: '+err.message); }

        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> Rotate Images';
    });
})();
</script>
