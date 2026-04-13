<?php
/**
 * ConvertX – Split PDF View
 */
$currentView = 'pdf-split';
$csrfToken   = \Core\Security::generateCsrfToken();
$hasGs       = $hasGs ?? false;
?>

<div class="page-header">
    <h1><i class="fa-solid fa-scissors" style="color:var(--cx-primary);"></i> Split PDF</h1>
    <p>Extract individual pages or a range of pages from a PDF</p>
</div>

<?php if (!$hasGs): ?>
<div class="cx-notice">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>
        <strong>Ghostscript is not installed on this server</strong>
        PDF splitting requires Ghostscript. Install it with:
        <code style="background:var(--bg-tertiary);padding:.1rem .4rem;border-radius:.3rem;font-size:.82rem;">apt install ghostscript</code>
    </div>
</div>
<?php endif; ?>

<div class="cx-batch-grid">

    <!-- Left: file picker -->
    <div class="card">
        <div class="card-header"><i class="fa-solid fa-file-pdf"></i> Upload PDF</div>

        <form id="splitForm" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-file-circle-plus" style="color:var(--cx-primary);"></i>
                    Select a PDF
                </label>
                <div class="upload-zone" id="uploadZone">
                    <i class="fa-solid fa-file-pdf upload-icon" style="font-size:1.75rem;color:var(--cx-danger);"></i>
                    <p style="font-weight:600;font-size:.875rem;margin:.35rem 0 .2rem;">Drag &amp; drop or <strong>click to browse</strong></p>
                    <p style="font-size:.73rem;color:var(--text-muted);">PDF only · max 200 MB</p>
                    <input type="file" name="pdf" id="fileInput" accept=".pdf" style="display:none;">
                </div>
                <div id="fileInfo" style="margin-top:.75rem;display:none;" class="cx-file-item">
                    <i class="fa-solid fa-file-pdf" style="color:var(--cx-danger);flex-shrink:0;font-size:.85rem;"></i>
                    <span class="cx-file-name" id="fileName"></span>
                    <span class="cx-file-size" id="fileSize"></span>
                    <button type="button" class="cx-file-remove" id="removeFile" title="Remove"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
        </form>
    </div>

    <!-- Right: split options -->
    <div class="card">
        <div class="card-header"><i class="fa-solid fa-scissors"></i> Split Options</div>

        <div class="form-group">
            <label class="form-label">
                <i class="fa-solid fa-list-ol" style="color:var(--cx-primary);"></i>
                Page Range
            </label>
            <input type="text" name="page_range" id="pageRange" form="splitForm"
                   class="form-control" placeholder="e.g. 1,3-5,7 · leave blank for all pages"
                   style="font-size:.88rem;">
            <p style="font-size:.73rem;color:var(--text-muted);margin-top:.4rem;">
                Examples: <code>1-3</code> (pages 1–3) · <code>1,5,8</code> (specific pages) · blank = all pages
            </p>
        </div>

        <div class="form-group" style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.6rem;padding:.875rem;">
            <div style="font-size:.82rem;font-weight:600;color:var(--text-primary);margin-bottom:.4rem;">
                <i class="fa-solid fa-circle-info" style="color:var(--cx-primary);"></i>
                Output
            </div>
            <p style="font-size:.8rem;color:var(--text-secondary);margin:0;line-height:1.6;">
                A single extracted page downloads as a PDF.<br>
                Multiple pages are packaged into a ZIP file.
            </p>
        </div>

        <button type="submit" form="splitForm" class="btn btn-primary" id="submitBtn"
                style="width:100%;justify-content:center;padding:.825rem;"
                <?= !$hasGs ? 'disabled title="Ghostscript required"' : '' ?>>
            <i class="fa-solid fa-scissors"></i> Split PDF
        </button>
    </div>

</div>

<!-- Result card -->
<div id="resultCard" class="card" style="display:none;margin-top:1.25rem;">
    <div class="card-header"><i class="fa-solid fa-check-circle"></i> Split Complete</div>
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
    var fileInfo   = document.getElementById('fileInfo');
    var fileName   = document.getElementById('fileName');
    var fileSize   = document.getElementById('fileSize');
    var removeBtn  = document.getElementById('removeFile');
    var submitBtn  = document.getElementById('submitBtn');
    var resultCard = document.getElementById('resultCard');
    var resultBody = document.getElementById('resultBody');
    var selectedFile = null;

    zone.addEventListener('click', function (e) { if (!e.target.closest('.cx-file-remove')) input.click(); });
    zone.addEventListener('dragover', function (e) { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', function () { zone.classList.remove('drag-over'); });
    zone.addEventListener('drop', function (e) {
        e.preventDefault(); zone.classList.remove('drag-over');
        var f = e.dataTransfer.files[0];
        if (f && f.name.toLowerCase().endsWith('.pdf')) setFile(f);
    });
    input.addEventListener('change', function () { if (input.files[0]) setFile(input.files[0]); input.value = ''; });
    removeBtn.addEventListener('click', function () { selectedFile = null; fileInfo.style.display = 'none'; zone.classList.remove('has-file'); });

    function setFile(f) {
        selectedFile = f;
        zone.classList.add('has-file');
        fileName.textContent = f.name;
        fileSize.textContent = f.size >= 1048576 ? (f.size / 1048576).toFixed(1) + ' MB' : (f.size / 1024).toFixed(1) + ' KB';
        fileInfo.style.display = '';
    }

    function esc(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    document.getElementById('splitForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!selectedFile) { alert('Please select a PDF file.'); return; }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner" style="animation:cx-spin 1s linear infinite;"></i> Splitting…';

        var fd = new FormData();
        fd.append('_token', document.querySelector('[name="_token"]').value);
        fd.append('pdf', selectedFile);
        var pr = document.getElementById('pageRange').value.trim();
        if (pr) fd.append('page_range', pr);

        try {
            var res  = await fetch('/projects/convertx/pdf-split', { method:'POST', body:fd, headers:{'Accept':'application/json'} });
            var data = await res.json();
            resultCard.style.display = '';
            resultCard.scrollIntoView({ behavior:'smooth', block:'nearest' });
            if (data.success) {
                var sz = data.size >= 1048576 ? (data.size / 1048576).toFixed(2) + ' MB' : (data.size / 1024).toFixed(1) + ' KB';
                resultBody.innerHTML = '<p style="color:var(--cx-success);margin-bottom:.75rem;">'
                    + '<i class="fa-solid fa-check-circle"></i> '
                    + (data.page_count > 1 ? data.page_count + ' pages extracted' : '1 page extracted') + ' · ' + sz + '</p>'
                    + '<a href="/projects/convertx/pdf-tools/download/' + data.token + '" class="btn btn-success">'
                    + '<i class="fa-solid fa-download"></i> Download ' + esc(data.filename) + '</a>';
            } else {
                resultBody.innerHTML = '<p style="color:var(--cx-danger);"><i class="fa-solid fa-circle-xmark"></i> ' + esc(data.error || 'Split failed') + '</p>';
            }
        } catch (err) { alert('Network error: ' + err.message); }

        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-scissors"></i> Split PDF';
    });
})();
</script>
