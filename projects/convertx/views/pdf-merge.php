<?php
/**
 * ConvertX – Merge PDFs View
 */
$currentView = 'pdf-merge';
$csrfToken   = \Core\Security::generateCsrfToken();
$hasGs       = $hasGs ?? false;
?>

<div class="page-header">
    <h1><i class="fa-solid fa-object-group" style="color:var(--cx-primary);"></i> Merge PDFs</h1>
    <p>Combine multiple PDF files into one — drag to reorder before merging</p>
</div>

<?php if (!$hasGs): ?>
<div class="cx-notice">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>
        <strong>Ghostscript is not installed on this server</strong>
        PDF merging requires Ghostscript. Install it with:
        <code style="background:var(--bg-tertiary);padding:.1rem .4rem;border-radius:.3rem;font-size:.82rem;">apt install ghostscript</code>
    </div>
</div>
<?php endif; ?>

<div class="cx-batch-grid">

    <!-- Left: file picker -->
    <div class="card">
        <div class="card-header"><i class="fa-solid fa-file-pdf"></i> Select PDF Files</div>

        <form id="mergeForm" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-file-circle-plus" style="color:var(--cx-primary);"></i>
                    Upload PDFs
                    <span style="font-size:.73rem;font-weight:400;color:var(--text-muted);margin-left:.4rem;">2–20 files · drag to reorder</span>
                </label>
                <div class="upload-zone" id="uploadZone">
                    <i class="fa-solid fa-file-pdf upload-icon" style="font-size:1.75rem;color:var(--cx-danger);"></i>
                    <p style="font-weight:600;font-size:.875rem;margin:.35rem 0 .2rem;">Drag &amp; drop PDFs or <strong>click to browse</strong></p>
                    <p style="font-size:.73rem;color:var(--text-muted);">Only PDF files · max 200 MB each</p>
                    <input type="file" name="pdfs[]" id="fileInput" multiple accept=".pdf" style="display:none;">
                </div>
                <div id="fileList" style="margin-top:.75rem;display:flex;flex-direction:column;gap:.35rem;"></div>
            </div>
        </form>
    </div><!-- left -->

    <!-- Right: order info + submit -->
    <div class="card">
        <div class="card-header"><i class="fa-solid fa-layer-group"></i> Merge Options</div>

        <div class="form-group" style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.6rem;padding:.875rem;">
            <div style="display:flex;align-items:center;gap:.5rem;font-size:.82rem;font-weight:600;color:var(--text-primary);margin-bottom:.5rem;">
                <i class="fa-solid fa-circle-info" style="color:var(--cx-primary);"></i>
                How merging works
            </div>
            <ul style="font-size:.8rem;color:var(--text-secondary);margin:0;padding-left:1.2rem;line-height:1.7;">
                <li>Files will be merged in the order shown on the left</li>
                <li>Drag the items to reorder before merging</li>
                <li>Original files are not modified</li>
                <li>The result downloads as a single <strong>merged.pdf</strong></li>
            </ul>
        </div>

        <button type="submit" form="mergeForm" class="btn btn-primary" id="submitBtn"
                style="width:100%;justify-content:center;padding:.825rem;"
                <?= !$hasGs ? 'disabled title="Ghostscript required"' : '' ?>>
            <i class="fa-solid fa-object-group"></i> Merge PDFs
        </button>
    </div><!-- right -->

</div>

<!-- Result card -->
<div id="resultCard" class="card" style="display:none;margin-top:1.25rem;">
    <div class="card-header"><i class="fa-solid fa-check-circle"></i> Merge Complete</div>
    <div id="resultBody" style="padding:.875rem;"></div>
</div>

<style>
.cx-batch-grid { display:grid; grid-template-columns:1.15fr 1fr; gap:1.25rem; align-items:start; }
@media (max-width:768px) { .cx-batch-grid { grid-template-columns:1fr; } }
.cx-file-item {
    display:flex; align-items:center; gap:.5rem; padding:.45rem .625rem;
    background:var(--bg-secondary); border:1px solid var(--border-color);
    border-radius:.45rem; font-size:.8rem; cursor:grab;
}
.cx-file-item:active { cursor:grabbing; }
.cx-file-item.drag-over { border-color:var(--cx-primary); background:rgba(var(--cx-primary-rgb),.06); }
.cx-file-name { flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.cx-file-size { color:var(--text-muted); flex-shrink:0; font-size:.73rem; }
.cx-file-remove {
    background:none; border:none; color:var(--cx-danger); cursor:pointer;
    padding:.2rem .35rem; border-radius:.35rem; opacity:.7;
}
.cx-file-remove:hover { opacity:1; background:rgba(239,68,68,.1); }
.cx-file-count { font-size:.78rem; color:var(--text-secondary); margin-top:.25rem; }
</style>

<script>
(function () {
    var zone     = document.getElementById('uploadZone');
    var input    = document.getElementById('fileInput');
    var listEl   = document.getElementById('fileList');
    var submitBtn= document.getElementById('submitBtn');
    var resultCard = document.getElementById('resultCard');
    var resultBody = document.getElementById('resultBody');
    var selectedFiles = [];
    var dragSrcIdx = null;

    zone.addEventListener('click', function (e) {
        if (e.target.closest('.cx-file-item')) return;
        input.click();
    });
    zone.addEventListener('dragover', function (e) { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', function () { zone.classList.remove('drag-over'); });
    zone.addEventListener('drop', function (e) {
        e.preventDefault(); zone.classList.remove('drag-over');
        addFiles(Array.from(e.dataTransfer.files));
    });
    input.addEventListener('change', function () { addFiles(Array.from(input.files)); input.value = ''; });

    function addFiles(files) {
        files.forEach(function (f) {
            if (f.name.toLowerCase().endsWith('.pdf') && selectedFiles.length < 20) {
                var dup = selectedFiles.some(function (sf) {
                    return sf.name === f.name && sf.size === f.size && sf.lastModified === f.lastModified;
                });
                if (!dup) selectedFiles.push(f);
            }
        });
        renderList();
    }

    function renderList() {
        listEl.innerHTML = '';
        if (!selectedFiles.length) { zone.classList.remove('has-file'); return; }
        zone.classList.add('has-file');
        selectedFiles.forEach(function (f, i) {
            var size = f.size >= 1048576 ? (f.size / 1048576).toFixed(1) + ' MB' : (f.size / 1024).toFixed(1) + ' KB';
            var item = document.createElement('div');
            item.className = 'cx-file-item';
            item.draggable = true;
            item.dataset.idx = i;
            item.innerHTML = '<i class="fa-solid fa-grip-vertical" style="color:var(--text-muted);font-size:.75rem;flex-shrink:0;"></i>'
                           + '<i class="fa-solid fa-file-pdf" style="color:var(--cx-danger);flex-shrink:0;font-size:.8rem;"></i>'
                           + '<span class="cx-file-name" title="' + esc(f.name) + '">' + (i+1) + '. ' + esc(f.name) + '</span>'
                           + '<span class="cx-file-size">' + size + '</span>'
                           + '<button type="button" class="cx-file-remove" data-idx="' + i + '" title="Remove"><i class="fa-solid fa-xmark"></i></button>';
            item.querySelector('.cx-file-remove').addEventListener('click', function () {
                selectedFiles.splice(parseInt(this.dataset.idx), 1); renderList();
            });
            // Drag-to-reorder
            item.addEventListener('dragstart', function () { dragSrcIdx = parseInt(this.dataset.idx); this.style.opacity = '.4'; });
            item.addEventListener('dragend',   function () { this.style.opacity = ''; });
            item.addEventListener('dragover',  function (e) { e.preventDefault(); this.classList.add('drag-over'); });
            item.addEventListener('dragleave', function () { this.classList.remove('drag-over'); });
            item.addEventListener('drop', function (e) {
                e.preventDefault(); this.classList.remove('drag-over');
                var dropIdx = parseInt(this.dataset.idx);
                if (dragSrcIdx === null || dragSrcIdx === dropIdx) return;
                var moved = selectedFiles.splice(dragSrcIdx, 1)[0];
                selectedFiles.splice(dropIdx, 0, moved);
                dragSrcIdx = null;
                renderList();
            });
            listEl.appendChild(item);
        });
        var count = document.createElement('div');
        count.className = 'cx-file-count';
        count.innerHTML = '<i class="fa-solid fa-check-circle" style="color:var(--cx-success);"></i> '
                        + selectedFiles.length + ' file(s) selected';
        listEl.appendChild(count);
    }

    function esc(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    document.getElementById('mergeForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        if (selectedFiles.length < 2) {
            alert('Please select at least 2 PDF files.');
            return;
        }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner" style="animation:cx-spin 1s linear infinite;"></i> Merging…';

        var fd = new FormData();
        fd.append('_token', document.querySelector('[name="_token"]').value);
        selectedFiles.forEach(function (f) { fd.append('pdfs[]', f); });

        try {
            var res  = await fetch('/projects/convertx/pdf-merge', { method:'POST', body:fd, headers:{'Accept':'application/json'} });
            var data = await res.json();
            resultCard.style.display = '';
            resultCard.scrollIntoView({ behavior:'smooth', block:'nearest' });
            if (data.success) {
                var sz = data.size >= 1048576 ? (data.size / 1048576).toFixed(2) + ' MB' : (data.size / 1024).toFixed(1) + ' KB';
                var html = '<p style="color:var(--cx-success);margin-bottom:.75rem;">'
                         + '<i class="fa-solid fa-check-circle"></i> '
                         + selectedFiles.length + ' files merged successfully · ' + sz + '</p>';
                if (data.errors && data.errors.length) {
                    html += '<p style="color:var(--cx-warning);font-size:.8rem;"><i class="fa-solid fa-triangle-exclamation"></i> Skipped: ' + data.errors.map(function(e){return esc(e);}).join(', ') + '</p>';
                }
                html += '<a href="/projects/convertx/pdf-tools/download/' + data.token + '" class="btn btn-success">'
                      + '<i class="fa-solid fa-download"></i> Download ' + esc(data.filename) + '</a>';
                resultBody.innerHTML = html;
            } else {
                resultBody.innerHTML = '<p style="color:var(--cx-danger);"><i class="fa-solid fa-circle-xmark"></i> ' + esc(data.error || 'Merge failed') + '</p>';
            }
        } catch (err) { alert('Network error: ' + err.message); }

        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-object-group"></i> Merge PDFs';
    });
})();
</script>
