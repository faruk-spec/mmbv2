<?php
/**
 * ConvertX – Compress PDF View
 */
$currentView = 'pdf-compress';
$csrfToken   = \Core\Security::generateCsrfToken();
$hasGs       = $hasGs ?? false;
?>

<div class="page-header">
    <h1><i class="fa-solid fa-file-zipper" style="color:var(--cx-primary);"></i> Compress PDF</h1>
    <p>Reduce PDF file size using Ghostscript optimisation</p>
</div>

<?php if (!$hasGs): ?>
<div class="cx-notice">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>
        <strong>Ghostscript is not installed on this server</strong>
        PDF compression requires Ghostscript. Install it with:
        <code style="background:var(--bg-tertiary);padding:.1rem .4rem;border-radius:.3rem;font-size:.82rem;">apt install ghostscript</code>
    </div>
</div>
<?php endif; ?>

<div class="cx-batch-grid">

    <!-- Left: file picker -->
    <div class="card">
        <div class="card-header"><i class="fa-solid fa-file-pdf"></i> Upload PDF</div>

        <form id="compressForm" enctype="multipart/form-data">
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

    <!-- Right: compression level + submit -->
    <div class="card">
        <div class="card-header"><i class="fa-solid fa-sliders"></i> Compression Settings</div>

        <div class="form-group">
            <label class="form-label">
                <i class="fa-solid fa-gauge" style="color:var(--cx-primary);"></i>
                Quality Preset
            </label>
            <div style="display:flex;flex-direction:column;gap:.55rem;font-size:.83rem;" id="qualityOptions">
                <?php
                $presets = [
                    'screen'  => ['Screen (72 DPI)', 'Smallest file — for on-screen viewing only'],
                    'ebook'   => ['eBook (150 DPI)', 'Good balance of size and quality — recommended'],
                    'printer' => ['Printer (300 DPI)', 'High quality — for desktop printing'],
                    'prepress'=> ['Prepress (300 DPI + colour)', 'Maximum quality — for professional print'],
                ];
                foreach ($presets as $val => [$label, $desc]): ?>
                <label style="display:flex;align-items:flex-start;gap:.7rem;cursor:pointer;padding:.5rem .6rem;border:1px solid var(--border-color);border-radius:.45rem;transition:border-color .15s;"
                       id="preset-<?= $val ?>">
                    <input type="radio" name="quality" value="<?= $val ?>" form="compressForm"
                           <?= $val === 'ebook' ? 'checked' : '' ?>
                           style="margin-top:.2rem;flex-shrink:0;accent-color:var(--cx-primary);"
                           onchange="highlightPreset('<?= $val ?>')">
                    <span>
                        <strong><?= $label ?></strong>
                        <span style="display:block;color:var(--text-muted);font-size:.73rem;"><?= $desc ?></span>
                    </span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" form="compressForm" class="btn btn-primary" id="submitBtn"
                style="width:100%;justify-content:center;padding:.825rem;"
                <?= !$hasGs ? 'disabled title="Ghostscript required"' : '' ?>>
            <i class="fa-solid fa-file-zipper"></i> Compress PDF
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
#qualityOptions label:has(input:checked) { border-color:var(--cx-primary); background:rgba(var(--cx-primary-rgb),.04); }
</style>

<script>
function highlightPreset(val) {
    document.querySelectorAll('#qualityOptions label').forEach(function (l) {
        l.style.borderColor = l.id === 'preset-' + val ? 'var(--cx-primary)' : '';
        l.style.background  = l.id === 'preset-' + val ? 'rgba(var(--cx-primary-rgb),.04)' : '';
    });
}
highlightPreset('ebook');

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

    function fmtSize(b) { return b >= 1048576 ? (b / 1048576).toFixed(2) + ' MB' : (b / 1024).toFixed(1) + ' KB'; }
    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    document.getElementById('compressForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!selectedFile) { alert('Please select a PDF file.'); return; }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner" style="animation:cx-spin 1s linear infinite;"></i> Compressing…';

        var fd = new FormData();
        fd.append('_token', document.querySelector('[name="_token"]').value);
        fd.append('pdf', selectedFile);
        var q = document.querySelector('[name="quality"]:checked');
        if (q) fd.append('quality', q.value);

        try {
            var res  = await fetch('/projects/convertx/pdf-compress', { method:'POST', body:fd });
            var data = await res.json();
            resultCard.style.display = '';
            resultCard.scrollIntoView({ behavior:'smooth', block:'nearest' });
            if (data.success) {
                var html = '<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-bottom:.875rem;">';
                html += '<div style="text-align:center;padding:.75rem;background:var(--bg-secondary);border-radius:.5rem;">'
                      + '<div style="font-size:1.25rem;font-weight:700;color:var(--text-primary);">' + fmtSize(data.original_size) + '</div>'
                      + '<div style="font-size:.73rem;color:var(--text-muted);">Original</div></div>';
                html += '<div style="text-align:center;padding:.75rem;background:var(--bg-secondary);border-radius:.5rem;">'
                      + '<div style="font-size:1.25rem;font-weight:700;color:var(--cx-primary);">' + fmtSize(data.new_size) + '</div>'
                      + '<div style="font-size:.73rem;color:var(--text-muted);">Compressed</div></div>';
                var savePct = data.saved_pct;
                var saveColor = savePct > 0 ? 'var(--cx-success)' : 'var(--cx-warning)';
                html += '<div style="text-align:center;padding:.75rem;background:var(--bg-secondary);border-radius:.5rem;">'
                      + '<div style="font-size:1.25rem;font-weight:700;color:' + saveColor + ';">' + (savePct > 0 ? '-' : '') + savePct + '%</div>'
                      + '<div style="font-size:.73rem;color:var(--text-muted);">Reduction</div></div>';
                html += '</div>';
                if (savePct <= 0) {
                    html += '<p style="font-size:.8rem;color:var(--cx-warning);margin-bottom:.75rem;">'
                          + '<i class="fa-solid fa-triangle-exclamation"></i> '
                          + 'The compressed file is not smaller — the PDF is already well-optimised. You can still download it.</p>';
                }
                html += '<a href="/projects/convertx/pdf-tools/download/' + data.token + '" class="btn btn-success">'
                      + '<i class="fa-solid fa-download"></i> Download ' + esc(data.filename) + '</a>';
                resultBody.innerHTML = html;
            } else {
                resultBody.innerHTML = '<p style="color:var(--cx-danger);"><i class="fa-solid fa-circle-xmark"></i> ' + esc(data.error || 'Compression failed') + '</p>';
            }
        } catch (err) { alert('Network error: ' + err.message); }

        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-file-zipper"></i> Compress PDF';
    });
})();
</script>
