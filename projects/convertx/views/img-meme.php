<?php
/**
 * ConvertX – Meme Generator View
 */
$currentView = 'img-meme';
$csrfToken   = \Core\Security::generateCsrfToken();
$hasGd       = $hasGd ?? false;
?>

<div class="page-header">
    <h1><i class="fa-solid fa-face-laugh-wink" style="color:var(--cx-primary);"></i> Meme Generator</h1>
    <p>Add classic meme-style top and bottom text to any image</p>
</div>

<?php if (!$hasGd): ?>
<div class="cx-notice">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>
        <strong>PHP GD extension is not loaded</strong>
        Meme generation requires the GD extension. Install with:
        <code style="background:var(--bg-tertiary);padding:.1rem .4rem;border-radius:.3rem;font-size:.82rem;">apt install php-gd</code>
    </div>
</div>
<?php endif; ?>

<div class="cx-batch-grid">

    <!-- Left: upload + preview -->
    <div class="card">
        <div class="card-header"><i class="fa-solid fa-image"></i> Upload Image</div>

        <form id="memeForm" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="form-group">
                <div class="upload-zone" id="uploadZone">
                    <i class="fa-solid fa-image upload-icon" style="font-size:1.75rem;color:var(--cx-primary);"></i>
                    <p style="font-weight:600;font-size:.875rem;margin:.35rem 0 .2rem;">Click or drag an image here</p>
                    <p style="font-size:.73rem;color:var(--text-muted);">JPG, PNG, GIF, WebP · max 50 MB</p>
                    <input type="file" name="image" id="fileInput"
                           accept=".jpg,.jpeg,.png,.gif,.webp" style="display:none;">
                </div>
            </div>

            <!-- Live preview -->
            <div id="previewWrap" style="display:none;position:relative;border-radius:.5rem;overflow:hidden;background:#000;">
                <img id="previewImg" style="display:block;max-width:100%;height:auto;" alt="Preview">
                <div id="topTextPreview" style="position:absolute;top:5%;left:50%;transform:translateX(-50%);white-space:nowrap;font-weight:900;text-shadow:-2px -2px 0 #000,2px -2px 0 #000,-2px 2px 0 #000,2px 2px 0 #000;font-family:Impact,Arial Black,sans-serif;color:#fff;font-size:2.5rem;text-transform:uppercase;pointer-events:none;"></div>
                <div id="botTextPreview" style="position:absolute;bottom:5%;left:50%;transform:translateX(-50%);white-space:nowrap;font-weight:900;text-shadow:-2px -2px 0 #000,2px -2px 0 #000,-2px 2px 0 #000,2px 2px 0 #000;font-family:Impact,Arial Black,sans-serif;color:#fff;font-size:2.5rem;text-transform:uppercase;pointer-events:none;"></div>
            </div>
        </form>
    </div>

    <!-- Right: meme settings -->
    <div class="card">
        <div class="card-header"><i class="fa-solid fa-sliders"></i> Meme Settings</div>

        <div class="form-group">
            <label class="form-label" for="topText">
                <i class="fa-solid fa-arrow-up" style="color:var(--cx-primary);"></i> Top Text
            </label>
            <input type="text" name="top_text" id="topText" form="memeForm"
                   class="form-control" placeholder="WHEN YOU..." maxlength="100">
        </div>

        <div class="form-group">
            <label class="form-label" for="botText">
                <i class="fa-solid fa-arrow-down" style="color:var(--cx-primary);"></i> Bottom Text
            </label>
            <input type="text" name="bottom_text" id="botText" form="memeForm"
                   class="form-control" placeholder="BUT THEN..." maxlength="100">
        </div>

        <div class="form-group">
            <label class="form-label" for="fontSize">Font Size</label>
            <select class="form-control" name="font_size" id="fontSize" form="memeForm">
                <option value="32">Small (32px)</option>
                <option value="48" selected>Medium (48px)</option>
                <option value="64">Large (64px)</option>
                <option value="80">Extra Large (80px)</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Text Color</label>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;padding:.4rem .75rem;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.45rem;">
                    <input type="radio" name="text_color" value="white" form="memeForm" checked>
                    <span style="width:.85rem;height:.85rem;background:#fff;border:1px solid #555;border-radius:50%;display:inline-block;"></span> White
                </label>
                <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;padding:.4rem .75rem;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.45rem;">
                    <input type="radio" name="text_color" value="black" form="memeForm">
                    <span style="width:.85rem;height:.85rem;background:#222;border:1px solid #555;border-radius:50%;display:inline-block;"></span> Black
                </label>
                <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;padding:.4rem .75rem;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.45rem;">
                    <input type="radio" name="text_color" value="yellow" form="memeForm">
                    <span style="width:.85rem;height:.85rem;background:#ff0;border:1px solid #555;border-radius:50%;display:inline-block;"></span> Yellow
                </label>
            </div>
        </div>

        <button type="submit" form="memeForm" class="btn btn-primary" id="submitBtn"
                style="width:100%;justify-content:center;padding:.825rem;"
                <?= !$hasGd ? 'disabled title="GD extension required"' : '' ?>>
            <i class="fa-solid fa-face-laugh-wink"></i> Generate Meme
        </button>
    </div>

</div>

<!-- Result card -->
<div id="resultCard" class="card" style="display:none;margin-top:1.25rem;">
    <div class="card-header"><i class="fa-solid fa-check-circle"></i> Meme Ready</div>
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
    var topPreview = document.getElementById('topTextPreview');
    var botPreview = document.getElementById('botTextPreview');
    var submitBtn  = document.getElementById('submitBtn');
    var resultCard = document.getElementById('resultCard');
    var resultBody = document.getElementById('resultBody');
    var selectedFile = null;

    zone.addEventListener('click', function () { input.click(); });
    zone.addEventListener('dragover', function (e) { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', function () { zone.classList.remove('drag-over'); });
    zone.addEventListener('drop', function (e) { e.preventDefault(); zone.classList.remove('drag-over'); loadFile(e.dataTransfer.files[0]); });
    input.addEventListener('change', function () { if (input.files[0]) loadFile(input.files[0]); input.value=''; });

    function loadFile(f) {
        if (!f) return;
        selectedFile = f;
        var url = URL.createObjectURL(f);
        previewImg.src = url;
        previewImg.onload = function () { previewWrap.style.display = ''; zone.classList.add('has-file'); };
    }

    function updatePreview() {
        var top   = (document.getElementById('topText').value || '').toUpperCase();
        var bot   = (document.getElementById('botText').value || '').toUpperCase();
        var color = (document.querySelector('[name="text_color"]:checked') || {value:'white'}).value;
        var colorMap = { white:'#fff', black:'#111', yellow:'#ff0' };
        var c = colorMap[color] || '#fff';
        topPreview.textContent = top;
        botPreview.textContent = bot;
        topPreview.style.color = c;
        botPreview.style.color = c;
    }

    document.getElementById('topText').addEventListener('input', updatePreview);
    document.getElementById('botText').addEventListener('input', updatePreview);
    document.querySelectorAll('[name="text_color"]').forEach(function (r) { r.addEventListener('change', updatePreview); });

    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    document.getElementById('memeForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!selectedFile) { alert('Please select an image first.'); return; }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner" style="animation:cx-spin 1s linear infinite;"></i> Generating…';

        var fd = new FormData();
        fd.append('_token',      document.querySelector('[name="_token"]').value);
        fd.append('image',       selectedFile);
        fd.append('top_text',    document.getElementById('topText').value);
        fd.append('bottom_text', document.getElementById('botText').value);
        fd.append('font_size',   document.getElementById('fontSize').value);
        fd.append('text_color',  document.querySelector('[name="text_color"]:checked').value);

        try {
            var res  = await fetch('/projects/convertx/img-meme', { method:'POST', body:fd });
            var data = await res.json();
            resultCard.style.display = '';
            resultCard.scrollIntoView({ behavior:'smooth', block:'nearest' });

            if (data.success) {
                var html = '<p style="margin-bottom:.75rem;font-size:.85rem;"><i class="fa-solid fa-check-circle" style="color:var(--cx-success);"></i> Meme created successfully!</p>'
                         + '<a href="/projects/convertx/pdf-tools/download/'+data.token+'" class="btn btn-success">'
                         + '<i class="fa-solid fa-download"></i> Download '+esc(data.filename)+'</a>';
                resultBody.innerHTML = html;
            } else {
                resultBody.innerHTML = '<p style="color:var(--cx-danger);"><i class="fa-solid fa-circle-xmark"></i> '+esc(data.error||'Meme generation failed')+'</p>';
            }
        } catch (err) { alert('Network error: '+err.message); }

        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-face-laugh-wink"></i> Generate Meme';
    });
})();
</script>
