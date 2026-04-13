<?php
/**
 * ConvertX – Photo Editor View (client-side canvas editor)
 */
$currentView = 'img-editor';
$csrfToken   = \Core\Security::generateCsrfToken();
?>

<div class="page-header">
    <h1><i class="fa-solid fa-pen-to-square" style="color:var(--cx-primary);"></i> Photo Editor</h1>
    <p>Client-side canvas editor — add text, apply filters, rotate and flip your images directly in the browser</p>
</div>

<div class="card" id="editorCard">
    <!-- Toolbar -->
    <div class="card-header" style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
        <span style="font-weight:600;"><i class="fa-solid fa-toolbox" style="color:var(--cx-primary);"></i> Toolbar</span>
        <button class="cx-tool-btn" id="btnUpload" title="Open Image"><i class="fa-solid fa-folder-open"></i> Open</button>
        <div class="cx-tool-sep"></div>
        <button class="cx-tool-btn" id="btnAddText" title="Add Text"><i class="fa-solid fa-font"></i> Text</button>
        <div class="cx-tool-sep"></div>
        <button class="cx-tool-btn" id="btnRotateCW"  title="Rotate 90° CW"><i class="fa-solid fa-rotate-right"></i></button>
        <button class="cx-tool-btn" id="btnRotateCCW" title="Rotate 90° CCW"><i class="fa-solid fa-rotate-left"></i></button>
        <button class="cx-tool-btn" id="btnFlipH"  title="Flip Horizontal"><i class="fa-solid fa-left-right"></i></button>
        <button class="cx-tool-btn" id="btnFlipV"  title="Flip Vertical"><i class="fa-solid fa-up-down"></i></button>
        <div class="cx-tool-sep"></div>
        <button class="cx-tool-btn" id="btnReset" title="Reset Filters"><i class="fa-solid fa-arrow-rotate-left"></i> Reset</button>
        <div style="flex:1;"></div>
        <button class="cx-tool-btn cx-tool-btn--primary" id="btnDownload" disabled><i class="fa-solid fa-download"></i> Download</button>
        <input type="file" id="fileInput" accept="image/*" style="display:none;">
    </div>

    <!-- Filters strip -->
    <div style="padding:.625rem 1rem;border-bottom:1px solid var(--border-color);display:flex;flex-wrap:wrap;align-items:center;gap:.75rem;">
        <span style="font-size:.8rem;color:var(--text-muted);font-weight:600;">Filters:</span>

        <label style="display:flex;align-items:center;gap:.35rem;font-size:.8rem;">
            <i class="fa-solid fa-sun" style="color:var(--cx-primary);"></i> Brightness
            <input type="range" id="fBrightness" min="-100" max="100" value="0" step="2"
                   style="accent-color:var(--cx-primary);width:90px;">
            <span id="vBrightness" style="width:2.2rem;font-size:.75rem;color:var(--text-muted);">0</span>
        </label>

        <label style="display:flex;align-items:center;gap:.35rem;font-size:.8rem;">
            <i class="fa-solid fa-circle-half-stroke" style="color:var(--cx-primary);"></i> Contrast
            <input type="range" id="fContrast" min="-100" max="100" value="0" step="2"
                   style="accent-color:var(--cx-primary);width:90px;">
            <span id="vContrast" style="width:2.2rem;font-size:.75rem;color:var(--text-muted);">0</span>
        </label>

        <label style="display:flex;align-items:center;gap:.35rem;font-size:.8rem;">
            <i class="fa-solid fa-droplet" style="color:var(--cx-primary);"></i> Saturation
            <input type="range" id="fSaturation" min="-100" max="100" value="0" step="2"
                   style="accent-color:var(--cx-primary);width:90px;">
            <span id="vSaturation" style="width:2.2rem;font-size:.75rem;color:var(--text-muted);">0</span>
        </label>

        <label style="display:flex;align-items:center;gap:.35rem;font-size:.8rem;">
            <i class="fa-solid fa-temperature-half" style="color:var(--cx-primary);"></i> Blur
            <input type="range" id="fBlur" min="0" max="20" value="0" step="1"
                   style="accent-color:var(--cx-primary);width:90px;">
            <span id="vBlur" style="width:2.2rem;font-size:.75rem;color:var(--text-muted);">0</span>
        </label>

        <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
            <button class="cx-filter-btn" id="btnGrayscale">Grayscale</button>
            <button class="cx-filter-btn" id="btnSepia">Sepia</button>
            <button class="cx-filter-btn" id="btnInvert">Invert</button>
        </div>
    </div>

    <!-- Canvas area -->
    <div id="canvasWrap" style="min-height:400px;display:flex;align-items:center;justify-content:center;padding:1.25rem;background:var(--bg-secondary);position:relative;">
        <div id="emptyState" style="text-align:center;color:var(--text-muted);">
            <i class="fa-solid fa-image" style="font-size:3rem;margin-bottom:.75rem;opacity:.4;display:block;"></i>
            <p style="font-size:.9rem;margin:0;">Open an image to start editing</p>
            <button class="btn btn-primary" style="margin-top:.875rem;" onclick="document.getElementById('fileInput').click()">
                <i class="fa-solid fa-folder-open"></i> Open Image
            </button>
        </div>
        <canvas id="mainCanvas" style="display:none;max-width:100%;max-height:65vh;box-shadow:0 4px 20px rgba(0,0,0,.3);border-radius:.35rem;"></canvas>
    </div>

    <!-- Text overlay panel -->
    <div id="textPanel" style="display:none;padding:.875rem;border-top:1px solid var(--border-color);background:var(--bg-secondary);">
        <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:2;min-width:160px;">
                <label style="font-size:.8rem;color:var(--text-muted);display:block;margin-bottom:.25rem;">Text</label>
                <input type="text" id="textInput" class="form-control" value="Your text here" style="font-size:.9rem;">
            </div>
            <div>
                <label style="font-size:.8rem;color:var(--text-muted);display:block;margin-bottom:.25rem;">Font size</label>
                <input type="number" id="textSize" class="form-control" value="40" min="8" max="200" style="width:80px;">
            </div>
            <div>
                <label style="font-size:.8rem;color:var(--text-muted);display:block;margin-bottom:.25rem;">Color</label>
                <input type="color" id="textColor" class="form-control" value="#ffffff" style="height:2.5rem;width:60px;padding:.25rem;">
            </div>
            <div>
                <label style="font-size:.8rem;color:var(--text-muted);display:block;margin-bottom:.25rem;">X</label>
                <input type="number" id="textX" class="form-control" value="50" style="width:70px;">
            </div>
            <div>
                <label style="font-size:.8rem;color:var(--text-muted);display:block;margin-bottom:.25rem;">Y</label>
                <input type="number" id="textY" class="form-control" value="50" style="width:70px;">
            </div>
            <button class="btn btn-primary" id="btnApplyText"><i class="fa-solid fa-plus"></i> Add</button>
            <button class="btn" style="background:var(--bg-tertiary);color:var(--text-primary);" id="btnCloseText">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>
</div>

<style>
.cx-tool-btn {
    display:inline-flex; align-items:center; gap:.35rem; padding:.4rem .7rem;
    background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:.4rem;
    color:var(--text-primary); font-size:.8rem; cursor:pointer; transition:background .15s;
}
.cx-tool-btn:hover { background:var(--bg-tertiary); }
.cx-tool-btn--primary { background:var(--cx-primary); color:#fff; border-color:var(--cx-primary); }
.cx-tool-btn--primary:hover { opacity:.88; background:var(--cx-primary); }
.cx-tool-btn:disabled { opacity:.4; cursor:not-allowed; }
.cx-tool-sep { width:1px; height:1.5rem; background:var(--border-color); }
.cx-filter-btn {
    padding:.3rem .6rem; font-size:.75rem; border-radius:.35rem;
    background:var(--bg-tertiary); border:1px solid var(--border-color);
    color:var(--text-primary); cursor:pointer; transition:all .15s;
}
.cx-filter-btn:hover, .cx-filter-btn.active { background:var(--cx-primary); color:#fff; border-color:var(--cx-primary); }
</style>

<script>
(function () {
    var canvas   = document.getElementById('mainCanvas');
    var ctx      = canvas.getContext('2d');
    var empty    = document.getElementById('emptyState');
    var textPanel= document.getElementById('textPanel');
    var fileInput= document.getElementById('fileInput');

    var state = {
        baseImage:   null,
        rotation:    0,
        flipH:       false,
        flipV:       false,
        filters:     { brightness:0, contrast:0, saturation:0, blur:0 },
        activeFilters:{ grayscale:false, sepia:false, invert:false },
        textLayers:  [],
    };

    // File open
    document.getElementById('btnUpload').addEventListener('click', function () { fileInput.click(); });
    fileInput.addEventListener('change', function () {
        var f = fileInput.files[0];
        if (!f) return;
        var img = new Image();
        img.onload = function () {
            state.baseImage = img;
            state.rotation  = 0;
            state.flipH = state.flipV = false;
            state.filters   = { brightness:0, contrast:0, saturation:0, blur:0 };
            state.activeFilters = { grayscale:false, sepia:false, invert:false };
            state.textLayers = [];
            resetSliders();
            initCanvas(img.width, img.height);
            render();
            empty.style.display = 'none';
            canvas.style.display = '';
            document.getElementById('btnDownload').disabled = false;
        };
        img.src = URL.createObjectURL(f);
        fileInput.value = '';
    });

    function initCanvas(w, h) {
        // Swap dimensions for 90° and 270° rotations
        var rot = ((state.rotation % 360) + 360) % 360;
        if (rot === 90 || rot === 270) { canvas.width=h; canvas.height=w; }
        else { canvas.width=w; canvas.height=h; }
    }

    function render() {
        if (!state.baseImage) return;
        var img = state.baseImage;
        var rad = state.rotation * Math.PI / 180;
        var cw = canvas.width, ch = canvas.height;

        ctx.clearRect(0, 0, cw, ch);
        ctx.save();
        ctx.translate(cw/2, ch/2);
        ctx.rotate(rad);
        if (state.flipH) ctx.scale(-1, 1);
        if (state.flipV) ctx.scale(1, -1);

        // CSS-filter approach for visual filters
        var f = state.filters;
        var af = state.activeFilters;
        var filterStr = '';
        if (f.brightness) filterStr += 'brightness('+(1 + f.brightness/100)+') ';
        if (f.contrast)   filterStr += 'contrast('+(1 + f.contrast/100)+') ';
        if (f.saturation) filterStr += 'saturate('+(1 + f.saturation/100)+') ';
        if (f.blur)       filterStr += 'blur('+f.blur+'px) ';
        if (af.grayscale) filterStr += 'grayscale(1) ';
        if (af.sepia)     filterStr += 'sepia(1) ';
        if (af.invert)    filterStr += 'invert(1) ';
        ctx.filter = filterStr || 'none';

        var drawW = (state.rotation===90||state.rotation===270) ? img.height : img.width;
        var drawH = (state.rotation===90||state.rotation===270) ? img.width  : img.height;
        ctx.drawImage(img, -drawW/2, -drawH/2, drawW, drawH);
        ctx.restore();
        ctx.filter = 'none';

        // Draw text layers
        state.textLayers.forEach(function (t) {
            ctx.font = 'bold '+t.size+'px Arial, sans-serif';
            ctx.fillStyle = t.color;
            ctx.fillText(t.text, t.x, t.y);
        });
    }

    // Rotation
    document.getElementById('btnRotateCW').addEventListener('click', function () {
        state.rotation = (state.rotation + 90) % 360;
        initCanvas(state.baseImage.width, state.baseImage.height);
        render();
    });
    document.getElementById('btnRotateCCW').addEventListener('click', function () {
        state.rotation = (state.rotation + 270) % 360;
        initCanvas(state.baseImage.width, state.baseImage.height);
        render();
    });

    // Flip
    document.getElementById('btnFlipH').addEventListener('click', function () { state.flipH=!state.flipH; render(); });
    document.getElementById('btnFlipV').addEventListener('click', function () { state.flipV=!state.flipV; render(); });

    // Filter sliders
    ['Brightness','Contrast','Saturation','Blur'].forEach(function (n) {
        var id = 'f'+n, vid='v'+n;
        document.getElementById(id).addEventListener('input', function () {
            state.filters[n.toLowerCase()] = parseFloat(this.value);
            document.getElementById(vid).textContent = this.value;
            render();
        });
    });

    // Toggle filters
    ['Grayscale','Sepia','Invert'].forEach(function (n) {
        document.getElementById('btn'+n).addEventListener('click', function () {
            state.activeFilters[n.toLowerCase()] = !state.activeFilters[n.toLowerCase()];
            this.classList.toggle('active');
            render();
        });
    });

    // Reset
    document.getElementById('btnReset').addEventListener('click', function () {
        state.filters = { brightness:0, contrast:0, saturation:0, blur:0 };
        state.activeFilters = { grayscale:false, sepia:false, invert:false };
        resetSliders();
        render();
    });

    function resetSliders() {
        ['Brightness','Contrast','Saturation','Blur'].forEach(function (n) {
            var el = document.getElementById('f'+n);
            if (el) { el.value = 0; document.getElementById('v'+n).textContent = '0'; }
        });
        ['Grayscale','Sepia','Invert'].forEach(function (n) {
            var b = document.getElementById('btn'+n);
            if (b) b.classList.remove('active');
        });
    }

    // Text overlay
    document.getElementById('btnAddText').addEventListener('click', function () {
        textPanel.style.display = textPanel.style.display === '' ? 'none' : '';
    });
    document.getElementById('btnCloseText').addEventListener('click', function () { textPanel.style.display='none'; });
    document.getElementById('btnApplyText').addEventListener('click', function () {
        state.textLayers.push({
            text:  document.getElementById('textInput').value,
            size:  parseInt(document.getElementById('textSize').value)||40,
            color: document.getElementById('textColor').value,
            x:     parseInt(document.getElementById('textX').value)||50,
            y:     parseInt(document.getElementById('textY').value)||50,
        });
        render();
    });

    // Download
    document.getElementById('btnDownload').addEventListener('click', function () {
        canvas.toBlob(function (blob) {
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'edited-image.png';
            a.click();
        }, 'image/png');
    });
})();
</script>
