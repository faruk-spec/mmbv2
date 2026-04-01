<?php
/**
 * @var string $title
 * @var array  $user
 * @var array  $templates
 * @var array  $jobs
 * @var string $csrfToken
 * @var array  $adminCfg
 */
$maxBulkRows = (int)(($adminCfg ?? [])['max_bulk_rows'] ?? 200);

// Build template options for dropdown
$tplOptions = [];
foreach ($templates as $key => $tpl) {
    $tplOptions[] = ['key' => $key, 'name' => $tpl['name'], 'color' => $tpl['color'], 'orientation' => $tpl['orientation'] ?? 'landscape'];
}
?>
<style>
/* ── Bulk page ───────────────────────────────────────────────────── */
.bulk-card {
    background:var(--bg-card);
    border:1px solid var(--border-color);
    border-radius:14px;
    padding:22px 24px;
    margin-bottom:22px;
}
.step-num {
    width:26px;height:26px;background:var(--indigo);color:#fff;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-size:0.72rem;font-weight:800;flex-shrink:0;
}

/* ── Template dropdown (mobile-first) ───────────────────────────── */
.tpl-dropdown-wrap { position:relative;display:none; }
.tpl-dropdown-wrap select {
    width:100%;padding:10px 14px;border-radius:10px;
    background:var(--bg-secondary);border:2px solid var(--border-color);
    color:var(--text-primary);font-size:0.9rem;cursor:pointer;
    appearance:none;-webkit-appearance:none;
    padding-right:36px;
}
.tpl-dropdown-wrap::after {
    content:'▾';position:absolute;right:12px;top:50%;transform:translateY(-50%);
    color:var(--text-secondary);pointer-events:none;font-size:1rem;
}

/* ── Template grid (desktop) ────────────────────────────────────── */
.tpl-select-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(130px,1fr));
    gap:9px;
    margin-top:12px;
}
.tpl-btn {
    display:flex;flex-direction:column;align-items:center;gap:6px;
    padding:13px 8px;border-radius:10px;border:2px solid var(--border-color);
    background:var(--bg-secondary);cursor:pointer;transition:all 0.2s;text-align:center;
    user-select:none;
}
.tpl-btn:hover { transform:translateY(-2px); }
.tpl-btn.active { box-shadow:0 0 0 2px var(--indigo); }
.tpl-btn .tpl-icon {
    width:34px;height:34px;border-radius:8px;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}

/* Switch grid→dropdown at ≤640px */
@media(max-width:640px) {
    .tpl-select-grid { display:none; }
    .tpl-dropdown-wrap { display:block; }
    .bulk-card { padding:18px 16px; }
}

/* ── Upload zone ─────────────────────────────────────────────────── */
.upload-zone {
    border:2px dashed var(--border-color);border-radius:12px;
    padding:34px 18px;text-align:center;cursor:pointer;transition:all 0.2s;
    background:var(--bg-secondary);
}
.upload-zone:hover,.upload-zone.dragover { border-color:var(--indigo);background:rgba(99,102,241,0.06); }
.upload-zone input[type=file] { display:none; }
#uploadFilename { font-size:0.83rem;color:var(--indigo);margin-top:8px;font-weight:600; }

/* ── 2-column layout ─────────────────────────────────────────────── */
.bulk-gen-wrap { display:grid; grid-template-columns:1fr 360px; gap:20px; align-items:start; }
@media(max-width:960px){ .bulk-gen-wrap { grid-template-columns:1fr; } }

/* ── Live preview ────────────────────────────────────────────────── */
.bulk-preview-area { position:sticky; top:20px; }
.id-card-preview {
    width:100%; max-width:340px; margin:0 auto;
    border-radius:14px; overflow:hidden;
    box-shadow:0 20px 60px rgba(0,0,0,0.4);
    font-family:'Poppins',sans-serif;
    transition:all 0.3s ease;
    aspect-ratio:85.6/54; position:relative;
}
.id-card-preview.portrait { aspect-ratio:54/85.6; max-width:200px; }

/* ── Design controls ─────────────────────────────────────────────── */
.design-row { display:flex;flex-wrap:wrap;gap:12px;margin-top:14px; }
.design-ctrl { display:flex;flex-direction:column;gap:5px; }
.design-ctrl label { font-size:0.72rem;font-weight:600;color:var(--text-secondary); }
.design-ctrl input[type=color] {
    width:44px;height:34px;border-radius:8px;border:1.5px solid var(--border-color);
    background:var(--bg-secondary);cursor:pointer;padding:2px 3px;
}
.design-ctrl select {
    padding:7px 10px;background:var(--bg-secondary);border:1.5px solid var(--border-color);
    border-radius:8px;color:var(--text-primary);font-size:0.82rem;cursor:pointer;
}

/* ── Style picker (matches generate page) ───────────────────────── */
.style-picker { display:grid; grid-template-columns:repeat(5,1fr); gap:8px; }
@media(max-width:680px){ .style-picker { grid-template-columns:repeat(3,1fr); } }
.style-card {
    border-radius:8px; border:2px solid var(--border-color);
    aspect-ratio:85.6/54; overflow:hidden; cursor:pointer; transition:all 0.2s;
    background:#111;
}
.style-card.portrait { aspect-ratio:54/85.6; }
.style-card:hover { border-color:var(--indigo); transform:translateY(-2px); }
.style-card.active { border-color:var(--indigo); box-shadow:0 0 0 2px rgba(99,102,241,0.3); }
.style-label { font-size:0.62rem; font-weight:600; text-align:center; margin-top:5px; color:var(--text-secondary); }
.style-label.active { color:var(--indigo); }
.filter-btn {
    padding:4px 12px; border-radius:16px; font-size:0.72rem; font-weight:600;
    border:1.5px solid var(--border-color); cursor:pointer;
    background:var(--bg-secondary); color:var(--text-secondary); transition:all 0.2s;
}
.filter-btn.active { background:var(--indigo); color:#fff; border-color:var(--indigo); }

/* ── Mobile preview button ───────────────────────────────────────── */
.bulk-preview-btn {
    display:none; position:fixed; bottom:20px; right:20px; z-index:1000;
    background:var(--indigo); color:#fff; border:none; border-radius:50px;
    padding:12px 20px; font-size:0.85rem; font-weight:600; cursor:pointer;
    box-shadow:0 4px 20px rgba(99,102,241,0.5);
}
@media(max-width:960px){ .bulk-preview-btn { display:flex; align-items:center; gap:8px; } }
/* hide sticky preview panel on mobile */
@media(max-width:960px){ .bulk-preview-area { display:none; } }

/* ── Preview modal (mobile) ─────────────────────────────────────── */
.preview-modal-overlay {
    display:none; position:fixed; inset:0; z-index:2000;
    background:rgba(0,0,0,0.75); align-items:center; justify-content:center;
}
.preview-modal-overlay.open { display:flex; }
.preview-modal-box {
    background:var(--bg-card); border-radius:18px; padding:24px 20px;
    width:92%; max-width:380px; position:relative;
}
.preview-modal-close {
    position:absolute; top:12px; right:14px; background:none; border:none;
    font-size:1.4rem; cursor:pointer; color:var(--text-secondary);
}

/* ── Progress / results ──────────────────────────────────────────── */
.progress-bar-wrap { background:var(--border-color);border-radius:99px;height:10px;overflow:hidden;margin-top:10px; }
.progress-bar-fill { height:100%;border-radius:99px;background:linear-gradient(90deg,#6366f1,#00f0ff);transition:width 0.4s; }
.result-badge { display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:20px;font-size:0.8rem;font-weight:600; }
.badge-success { background:rgba(0,255,136,0.12);color:#00ff88; }
.badge-fail    { background:rgba(239,68,68,0.12);color:#ef4444; }

/* ── Jobs table ──────────────────────────────────────────────────── */
.jobs-table { width:100%;border-collapse:collapse;font-size:13px; }
.jobs-table th { text-align:left;padding:9px 12px;color:var(--text-secondary);font-weight:600;border-bottom:1px solid var(--border-color); }
.jobs-table td { padding:9px 12px;border-bottom:1px solid var(--border-color); }
.status-chip { display:inline-block;padding:2px 10px;border-radius:10px;font-size:11px;font-weight:700;letter-spacing:0.02em; }
.status-done       { background:rgba(0,255,136,0.12);color:#00ff88; }
.status-error      { background:rgba(239,68,68,0.12);color:#ef4444; }
.status-processing { background:rgba(245,158,11,0.12);color:#f59e0b; }
.status-pending    { background:rgba(99,102,241,0.12);color:#6366f1; }
</style>

<!-- Hero -->
<div style="background:linear-gradient(135deg,rgba(99,102,241,0.12),rgba(0,240,255,0.05));
     border:1px solid rgba(99,102,241,0.2);border-radius:14px;padding:18px 22px;
     margin-bottom:22px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
    <div style="width:50px;height:50px;background:linear-gradient(135deg,#6366f1,#00f0ff);border-radius:13px;
         display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="fas fa-layer-group" style="color:#fff;font-size:1.25rem;"></i>
    </div>
    <div style="flex:1;min-width:150px;">
        <div style="font-size:1.1rem;font-weight:800;background:linear-gradient(135deg,#6366f1,#00f0ff);
             -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:2px;">
            Bulk ID Card Generator
        </div>
        <div style="font-size:0.8rem;color:var(--text-secondary);">
            Select category &rarr; upload CSV &rarr; pick design &rarr; generate all cards at once
        </div>
    </div>
    <a href="/projects/idcard" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Dashboard</a>
</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- STEP 1 — Category                                             -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="bulk-card">
    <h3 style="font-size:0.95rem;font-weight:700;margin-bottom:4px;display:flex;align-items:center;gap:8px;">
        <span class="step-num">1</span> Select Card Category
    </h3>
    <p style="font-size:0.78rem;color:var(--text-secondary);margin-bottom:12px;">
        Choose the template for all cards in your CSV, then download the matching sample.
    </p>

    <!-- Mobile dropdown -->
    <div class="tpl-dropdown-wrap" id="tplDropdownWrap">
        <select id="tplDropdown" onchange="selectTemplateByKey(this.value)">
            <option value="">— choose a category —</option>
            <?php foreach ($templates as $key => $tpl): ?>
            <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($tpl['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Desktop grid -->
    <div class="tpl-select-grid" id="tplGrid">
        <?php foreach ($templates as $key => $tpl): ?>
        <div class="tpl-btn" id="tplBtn_<?= htmlspecialchars($key) ?>"
             data-tpl="<?= htmlspecialchars($key) ?>"
             data-color="<?= htmlspecialchars($tpl['color']) ?>"
             data-orientation="<?= htmlspecialchars($tpl['orientation'] ?? 'landscape') ?>"
             onclick="selectTemplate(this)">
            <div class="tpl-icon" style="background:<?= htmlspecialchars($tpl['color']) ?>;">
                <i class="fas fa-id-card" style="color:#fff;font-size:0.85rem;"></i>
            </div>
            <span style="font-size:0.7rem;font-weight:600;color:var(--text-primary);line-height:1.3;">
                <?= htmlspecialchars($tpl['name']) ?>
            </span>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="margin-top:14px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <div style="font-size:0.85rem;color:var(--text-secondary);">
            Selected: <strong id="selectedTplName" style="color:var(--indigo);">—</strong>
        </div>
        <a id="sampleCsvBtn" href="#"
           style="pointer-events:none;opacity:0.4;" class="btn btn-secondary btn-sm">
            <i class="fas fa-download"></i> Download Sample CSV
        </a>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- STEP 2 — Upload CSV                                           -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="bulk-card">
    <h3 style="font-size:0.95rem;font-weight:700;margin-bottom:4px;display:flex;align-items:center;gap:8px;">
        <span class="step-num">2</span> Upload Your CSV
    </h3>
    <p style="font-size:0.78rem;color:var(--text-secondary);margin-bottom:14px;">
        Fill the sample CSV with your data and upload it here. Maximum <?= $maxBulkRows ?> rows.
    </p>

    <div class="upload-zone" id="uploadZone"
         onclick="document.getElementById('csvFileInput').click()"
         ondragover="handleDragOver(event)"
         ondragleave="handleDragLeave(event)"
         ondrop="handleDrop(event)">
        <i class="fas fa-file-csv" style="font-size:2.2rem;color:var(--indigo);opacity:0.6;margin-bottom:8px;display:block;"></i>
        <div style="font-size:0.88rem;font-weight:600;color:var(--text-primary);">
            Click to choose CSV or drag &amp; drop here
        </div>
        <div style="font-size:0.74rem;color:var(--text-secondary);margin-top:3px;">Accepts .csv files only</div>
        <input type="file" id="csvFileInput" accept=".csv,text/csv" onchange="handleFileSelect(this)">
        <div id="uploadFilename"></div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- STEP 3 — Design                                               -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="bulk-card">
    <h3 style="font-size:0.95rem;font-weight:700;margin-bottom:4px;display:flex;align-items:center;gap:8px;">
        <span class="step-num">3</span> Choose Design <span style="font-size:0.72rem;color:var(--text-secondary);font-weight:400;margin-left:4px;">(applied to all cards)</span>
    </h3>
    <p style="font-size:0.78rem;color:var(--text-secondary);margin-bottom:10px;">
        Pick a card style and customize colours &amp; font. This design will be used for every card in your CSV.
    </p>

    <!-- Colour + font row -->
    <div class="design-row">
        <div class="design-ctrl">
            <label>Primary Color</label>
            <input type="color" id="d_primary" value="#1e40af">
        </div>
        <div class="design-ctrl">
            <label>Accent Color</label>
            <input type="color" id="d_accent" value="#3b82f6">
        </div>
        <div class="design-ctrl">
            <label>Background</label>
            <input type="color" id="d_bg" value="#ffffff">
        </div>
        <div class="design-ctrl">
            <label>Text Color</label>
            <input type="color" id="d_text" value="#1e293b">
        </div>
        <div class="design-ctrl" style="min-width:110px;">
            <label>Font</label>
            <select id="d_font">
                <?php foreach(['Poppins','Inter','Roboto','Lato','Open Sans','Montserrat','Raleway'] as $f): ?>
                <option value="<?= $f ?>"><?= $f ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="design-ctrl" style="min-width:100px;">
            <label>Photo Shape</label>
            <select id="d_shape">
                <option value="circle">Circle</option>
                <option value="oval">Oval</option>
                <option value="square">Square</option>
            </select>
        </div>
    </div>

    <!-- Design style picker -->
    <div style="margin-top:16px;">
        <div style="font-size:0.78rem;font-weight:600;color:var(--text-secondary);margin-bottom:6px;">
            Card Style — <span id="selectedStyleName" style="color:var(--indigo);">Angled Pro</span>
        </div>
        <div class="style-mini-grid" id="styleMiniGrid">
            <!-- populated by JS -->
        </div>
    </div>
    <input type="hidden" id="d_style" value="classic">
</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- STEP 4 — Generate                                             -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="bulk-card">
    <h3 style="font-size:0.95rem;font-weight:700;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
        <span class="step-num">4</span> Generate All Cards
    </h3>

    <form id="bulkForm" onsubmit="submitBulk(event)">
        <input type="hidden" name="_token"       value="<?= htmlspecialchars($csrfToken) ?>">
        <input type="hidden" name="template_key" id="bulkTemplateKey" value="">
        <input type="hidden" name="primary_color" id="f_primary" value="#1e40af">
        <input type="hidden" name="accent_color"  id="f_accent"  value="#3b82f6">
        <input type="hidden" name="bg_color"      id="f_bg"      value="#ffffff">
        <input type="hidden" name="text_color"    id="f_text"    value="#1e293b">
        <input type="hidden" name="font_family"   id="f_font"    value="Poppins">
        <input type="hidden" name="profile_shape" id="f_shape"   value="circle">
        <input type="hidden" name="design_style"  id="f_style"   value="classic">
        <!-- csv_file will be appended in JS via FormData -->
    </form>

    <div id="progressWrap" style="display:none;margin-bottom:14px;">
        <div style="font-size:0.8rem;color:var(--text-secondary);" id="progressLabel">Processing…</div>
        <div class="progress-bar-wrap"><div class="progress-bar-fill" id="progressBar" style="width:0%;"></div></div>
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <button id="submitBtn" class="btn btn-primary" disabled onclick="submitBulk(event)">
            <i class="fas fa-bolt"></i> Generate All Cards
        </button>
        <button type="button" class="btn btn-secondary" onclick="resetAll()">
            <i class="fas fa-redo"></i> Reset
        </button>
        <span id="readinessHint" style="font-size:0.78rem;color:var(--text-secondary);">
            Select a category &amp; upload a CSV to continue.
        </span>
    </div>

    <div id="bulkResultsWrap" style="display:none;margin-top:18px;">
        <div id="bulkResultInner"></div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- Recent Bulk Jobs                                              -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="bulk-card" style="margin-bottom:0;">
    <h3 style="font-size:0.95rem;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-history" style="color:var(--indigo);"></i> Recent Bulk Jobs
    </h3>

    <?php if (empty($jobs)): ?>
    <div style="text-align:center;padding:28px 0;color:var(--text-secondary);">
        <i class="fas fa-layer-group" style="font-size:1.8rem;opacity:0.3;display:block;margin-bottom:8px;"></i>
        No bulk jobs yet.
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table class="jobs-table">
            <thead>
                <tr>
                    <th>#</th><th>Template</th><th>Total</th><th>Done</th><th>Failed</th><th>Status</th><th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): ?>
                <tr>
                    <td style="font-family:monospace;font-size:11px;color:var(--text-secondary);"><?= (int)$job['id'] ?></td>
                    <td>
                        <span style="background:rgba(99,102,241,0.12);color:var(--indigo);
                                     padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">
                            <?= htmlspecialchars($job['template_key']) ?>
                        </span>
                    </td>
                    <td><?= (int)$job['total_rows'] ?></td>
                    <td style="color:#00ff88;font-weight:600;"><?= (int)$job['completed'] ?></td>
                    <td style="color:<?= $job['failed'] > 0 ? '#ef4444' : 'var(--text-secondary)' ?>;font-weight:<?= $job['failed'] > 0 ? '600' : '400' ?>;"><?= (int)$job['failed'] ?></td>
                    <td><span class="status-chip status-<?= htmlspecialchars($job['status']) ?>"><?= htmlspecialchars(ucfirst($job['status'])) ?></span></td>
                    <td style="font-size:12px;color:var(--text-secondary);"><?= date('d M Y, H:i', strtotime($job['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div style="margin-top:12px;text-align:right;">
        <a href="/projects/idcard/bulk/cards" class="btn btn-secondary btn-sm">
            <i class="fas fa-id-card"></i> View All Bulk Cards
        </a>
    </div>
</div>

<script>
/* ================================================================
   Template colour presets (matches config.php)
================================================================= */
var TPL_COLORS = <?php
$presets = [];
foreach ($templates as $k => $t) {
    $presets[$k] = ['color'=>$t['color'],'accent'=>$t['accent'],'bg'=>$t['bg'],'text'=>$t['text'],'orientation'=>$t['orientation'] ?? 'landscape'];
}
echo json_encode($presets);
?>;

/* ================================================================
   Design style list
================================================================= */
var STYLES_LANDSCAPE = [
    {key:'classic',       label:'Angled Pro'},
    {key:'gradient_pro',  label:'Gradient'},
    {key:'neon',          label:'Neon'},
    {key:'executive',     label:'Executive'},
    {key:'stripe',        label:'Stripe'},
    {key:'metro',         label:'Metro'},
    {key:'glass',         label:'Glass'},
    {key:'zigzag',        label:'Zigzag'},
    {key:'ribbon',        label:'Ribbon'},
];
var STYLES_PORTRAIT = [
    {key:'v_sharp',   label:'Sharp V'},
    {key:'v_curve',   label:'Curve'},
    {key:'v_hex',     label:'Hex'},
    {key:'v_circle',  label:'Circle'},
    {key:'v_split',   label:'Split'},
    {key:'v_ribbon',  label:'Ribbon'},
    {key:'v_arch',    label:'Arch'},
    {key:'v_diamond', label:'Diamond'},
    {key:'v_corner',  label:'Corner'},
    {key:'v_dual',    label:'Dual'},
    {key:'v_stripe',  label:'Stripe'},
    {key:'v_badge',   label:'Badge'},
];

/* ================================================================
   State
================================================================= */
var selectedTemplate  = '';
var fileSelected      = false;
var currentPortrait   = false;
var currentStyleKey   = 'classic';

/* ================================================================
   Template selection
================================================================= */
function selectTemplate(el) {
    document.querySelectorAll('.tpl-btn').forEach(function(b) {
        b.classList.remove('active');
        b.style.borderColor = 'var(--border-color)';
    });
    el.classList.add('active');
    el.style.borderColor = el.dataset.color;
    applyTemplate(el.dataset.tpl, el.querySelector('span').textContent.trim());
}

function selectTemplateByKey(key) {
    if (!key) return;
    var el = document.getElementById('tplBtn_' + key);
    var name = el ? el.querySelector('span').textContent.trim() : key;
    applyTemplate(key, name);
}

function applyTemplate(key, name) {
    selectedTemplate = key;
    document.getElementById('bulkTemplateKey').value = key;
    document.getElementById('selectedTplName').textContent = name;

    // Enable sample CSV download
    var btn = document.getElementById('sampleCsvBtn');
    btn.href = '/projects/idcard/bulk/sample-csv?template=' + encodeURIComponent(key);
    btn.style.pointerEvents = 'auto';
    btn.style.opacity = '1';

    // Set colour presets from template config
    var p = TPL_COLORS[key];
    if (p) {
        document.getElementById('d_primary').value = p.color;
        document.getElementById('d_accent').value  = p.accent;
        document.getElementById('d_bg').value      = p.bg;
        document.getElementById('d_text').value    = p.text;
        currentPortrait = (p.orientation === 'portrait');
    }

    // Rebuild style mini-grid for orientation
    buildStyleGrid(currentPortrait);
    updateSubmitState();
}

/* ================================================================
   Build design style mini-grid
================================================================= */
function buildStyleGrid(portrait) {
    var styles = portrait ? STYLES_PORTRAIT : STYLES_LANDSCAPE;
    var defaultKey = portrait ? 'v_sharp' : 'classic';

    // Keep current selection if still valid, else default
    var validKeys = styles.map(function(s){return s.key;});
    if (validKeys.indexOf(currentStyleKey) === -1) {
        currentStyleKey = defaultKey;
    }
    document.getElementById('d_style').value = currentStyleKey;
    document.getElementById('f_style').value = currentStyleKey;

    var grid = document.getElementById('styleMiniGrid');
    grid.innerHTML = '';
    styles.forEach(function(s) {
        var isActive = (s.key === currentStyleKey);
        var wrap = document.createElement('div');
        wrap.style.textAlign = 'center';
        wrap.innerHTML =
            '<div class="style-mini-card'+(portrait?' portrait':'')+(isActive?' active':'')+'" id="smc_'+s.key+'"'+
            ' onclick="pickStyle(\''+s.key+'\')" title="'+s.label+'">'+
            renderMiniPreview(s.key, portrait)+
            '</div>'+
            '<div class="style-mini-label'+(isActive?' active':'')+'" id="sml_'+s.key+'">'+s.label+'</div>';
        grid.appendChild(wrap);
    });

    document.getElementById('selectedStyleName').textContent =
        (styles.find(function(s){return s.key===currentStyleKey;}) || {label:currentStyleKey}).label;
}

function pickStyle(key) {
    // Deactivate old
    var oldCard  = document.getElementById('smc_' + currentStyleKey);
    var oldLabel = document.getElementById('sml_' + currentStyleKey);
    if (oldCard)  { oldCard.classList.remove('active'); }
    if (oldLabel) { oldLabel.classList.remove('active'); }

    currentStyleKey = key;
    document.getElementById('d_style').value = key;
    document.getElementById('f_style').value = key;

    var newCard  = document.getElementById('smc_' + key);
    var newLabel = document.getElementById('sml_' + key);
    if (newCard)  { newCard.classList.add('active'); }
    if (newLabel) { newLabel.classList.add('active'); }

    var allStyles = currentPortrait ? STYLES_PORTRAIT : STYLES_LANDSCAPE;
    document.getElementById('selectedStyleName').textContent =
        (allStyles.find(function(s){return s.key===key;}) || {label:key}).label;
}

/* tiny SVG thumbnail per style */
function renderMiniPreview(key, portrait) {
    var w = portrait ? 54 : 86, h = portrait ? 86 : 54;
    var pri = document.getElementById('d_primary').value || '#1e40af';
    var bg  = document.getElementById('d_bg').value      || '#ffffff';

    if (key.indexOf('v_') === 0 || key === 'gradient_pro') {
        return '<svg viewBox="0 0 '+w+' '+h+'" style="width:100%;height:100%;">'+
               '<rect width="'+w+'" height="'+h+'" fill="'+bg+'"/>'+
               '<polygon points="0,0 '+w+',0 '+w+','+Math.round(h*0.45)+' 0,'+Math.round(h*0.55)+'" fill="'+pri+'" opacity="0.9"/>'+
               '</svg>';
    }
    if (key === 'neon') {
        return '<svg viewBox="0 0 '+w+' '+h+'" style="width:100%;height:100%;">'+
               '<rect width="'+w+'" height="'+h+'" fill="#0f172a"/>'+
               '<rect x="0" y="0" width="'+Math.round(w*0.3)+'" height="'+h+'" fill="'+pri+'" opacity="0.7"/>'+
               '</svg>';
    }
    if (key === 'stripe' || key === 'v_stripe') {
        return '<svg viewBox="0 0 '+w+' '+h+'" style="width:100%;height:100%;">'+
               '<rect width="'+w+'" height="'+h+'" fill="'+bg+'"/>'+
               '<rect x="0" y="0" width="'+w+'" height="'+Math.round(h*0.28)+'" fill="'+pri+'"/>'+
               '<rect x="0" y="'+Math.round(h*0.72)+'" width="'+w+'" height="'+Math.round(h*0.06)+'" fill="'+pri+'" opacity="0.4"/>'+
               '</svg>';
    }
    // default: angled top bar
    return '<svg viewBox="0 0 '+w+' '+h+'" style="width:100%;height:100%;">'+
           '<rect width="'+w+'" height="'+h+'" fill="'+bg+'"/>'+
           '<polygon points="0,0 '+w+',0 '+w+','+Math.round(h*0.38)+' 0,'+Math.round(h*0.52)+'" fill="'+pri+'" opacity="0.85"/>'+
           '</svg>';
}

// Refresh thumbnails when colours change
['d_primary','d_bg'].forEach(function(id) {
    document.getElementById(id).addEventListener('input', function() {
        if (selectedTemplate) buildStyleGrid(currentPortrait);
    });
});

/* ================================================================
   File handling
================================================================= */
function handleFileSelect(input) {
    if (input.files && input.files[0]) {
        document.getElementById('uploadFilename').textContent = '📎 ' + input.files[0].name;
        fileSelected = true;
        updateSubmitState();
    }
}
function handleDragOver(e)  { e.preventDefault(); document.getElementById('uploadZone').classList.add('dragover'); }
function handleDragLeave(e) { document.getElementById('uploadZone').classList.remove('dragover'); }
function handleDrop(e) {
    e.preventDefault();
    document.getElementById('uploadZone').classList.remove('dragover');
    var input = document.getElementById('csvFileInput');
    if (e.dataTransfer.files.length) {
        // DataTransfer → input.files is read-only; use a workaround via the file list
        // Just call handleFileSelect with the first file displayed
        var file = e.dataTransfer.files[0];
        document.getElementById('uploadFilename').textContent = '📎 ' + file.name;
        fileSelected = true;
        // Store file reference for FormData
        window._droppedFile = file;
        updateSubmitState();
    }
}

/* ================================================================
   Submit state
================================================================= */
function updateSubmitState() {
    var ready = selectedTemplate && fileSelected;
    document.getElementById('submitBtn').disabled = !ready;
    document.getElementById('readinessHint').textContent = ready
        ? 'Ready to generate — click the button!'
        : (!selectedTemplate ? 'Select a category first.' : 'Upload a CSV file.');
}

/* ================================================================
   Reset
================================================================= */
function resetAll() {
    selectedTemplate = '';
    fileSelected = false;
    window._droppedFile = null;

    document.getElementById('csvFileInput').value = '';
    document.getElementById('uploadFilename').textContent = '';
    document.getElementById('bulkTemplateKey').value = '';
    document.getElementById('selectedTplName').textContent = '—';
    document.getElementById('progressWrap').style.display = 'none';
    document.getElementById('bulkResultsWrap').style.display = 'none';

    var btn = document.getElementById('sampleCsvBtn');
    btn.href = '#'; btn.style.pointerEvents = 'none'; btn.style.opacity = '0.4';

    document.querySelectorAll('.tpl-btn').forEach(function(b) {
        b.classList.remove('active'); b.style.borderColor = 'var(--border-color)';
    });
    var dd = document.getElementById('tplDropdown');
    if (dd) dd.value = '';

    document.getElementById('styleMiniGrid').innerHTML = '';
    document.getElementById('selectedStyleName').textContent = 'Angled Pro';
    document.getElementById('d_style').value = 'classic';
    document.getElementById('f_style').value = 'classic';

    updateSubmitState();
}

/* ================================================================
   Generate (submit)
================================================================= */
function submitBulk(e) {
    e.preventDefault();
    if (!selectedTemplate || !fileSelected) return;

    // Sync design fields from pickers → hidden form inputs
    document.getElementById('f_primary').value = document.getElementById('d_primary').value;
    document.getElementById('f_accent').value  = document.getElementById('d_accent').value;
    document.getElementById('f_bg').value      = document.getElementById('d_bg').value;
    document.getElementById('f_text').value    = document.getElementById('d_text').value;
    document.getElementById('f_font').value    = document.getElementById('d_font').value;
    document.getElementById('f_shape').value   = document.getElementById('d_shape').value;
    document.getElementById('f_style').value   = document.getElementById('d_style').value;

    var btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating…';

    var pw = document.getElementById('progressWrap');
    pw.style.display = 'block';
    document.getElementById('progressBar').style.width = '25%';
    document.getElementById('progressLabel').textContent = 'Uploading CSV and creating cards…';

    var fd = new FormData(document.getElementById('bulkForm'));

    // Attach the CSV file (either from input or dropped)
    var csvInput = document.getElementById('csvFileInput');
    if (window._droppedFile) {
        fd.append('csv_file', window._droppedFile, window._droppedFile.name);
    } else if (csvInput.files && csvInput.files[0]) {
        fd.append('csv_file', csvInput.files[0]);
    }

    fetch('/projects/idcard/bulk/upload', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        document.getElementById('progressBar').style.width = '100%';
        document.getElementById('progressLabel').textContent = data.message || (data.success ? 'Done!' : 'Error');

        document.getElementById('bulkResultsWrap').style.display = 'block';

        if (data.success) {
            document.getElementById('bulkResultInner').innerHTML =
                '<div style="padding:14px;background:rgba(0,255,136,0.06);border:1px solid rgba(0,255,136,0.2);border-radius:10px;">'+
                '<div style="font-weight:700;color:#00ff88;margin-bottom:6px;font-size:0.95rem;"><i class="fas fa-check-circle"></i> Generation Complete!</div>'+
                '<div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:6px;">'+
                '<span class="result-badge badge-success"><i class="fas fa-id-card"></i> '+data.completed+' cards created</span>'+
                (data.failed > 0 ? '<span class="result-badge badge-fail"><i class="fas fa-exclamation-triangle"></i> '+data.failed+' rows skipped</span>' : '')+
                '</div>'+
                '<div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">'+
                '<a href="/projects/idcard/bulk/cards" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> View Bulk Cards</a>'+
                '<a href="/projects/idcard/history" class="btn btn-secondary btn-sm"><i class="fas fa-list"></i> All My Cards</a>'+
                '</div></div>';
            setTimeout(function(){ window.location.reload(); }, 3500);
        } else {
            document.getElementById('bulkResultInner').innerHTML =
                '<div style="padding:12px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.2);border-radius:10px;color:#ef4444;">'+
                '<i class="fas fa-times-circle"></i> '+(data.message||'An error occurred.')+'</div>';
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-bolt"></i> Generate All Cards';
    })
    .catch(function() {
        document.getElementById('progressLabel').textContent = 'Request failed.';
        document.getElementById('bulkResultsWrap').style.display = 'block';
        document.getElementById('bulkResultInner').innerHTML =
            '<div style="padding:12px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.2);border-radius:10px;color:#ef4444;">'+
            '<i class="fas fa-times-circle"></i> Network error. Please try again.</div>';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-bolt"></i> Generate All Cards';
    });
}

// Init: build a default style grid (landscape)
buildStyleGrid(false);
</script>
