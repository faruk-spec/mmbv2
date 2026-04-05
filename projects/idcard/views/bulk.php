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

/* ── Step 1: 3-column top row ───────────────────────────────────── */
.step1-row {
    display:grid;
    grid-template-columns:240px auto auto auto 240px;
    gap:16px;
    align-items:start;
}
@media(max-width:760px){ .step1-row { grid-template-columns:1fr; gap:14px; } }

/* Category dropdown */
.tpl-select-wrap { position:relative; }
.tpl-select-wrap select {
    width:100%;padding:11px 38px 11px 14px;border-radius:10px;
    background:var(--bg-secondary);border:2px solid var(--border-color);
    color:var(--text-primary);font-size:0.88rem;cursor:pointer;
    appearance:none;-webkit-appearance:none;
}
.tpl-select-wrap select:focus { border-color:var(--indigo);outline:none; }
.tpl-select-wrap::after {
    content:'▾';position:absolute;right:12px;top:50%;transform:translateY(-50%);
    color:var(--text-secondary);pointer-events:none;font-size:1rem;
}

/* Theme colour dots */
.theme-dots-col { text-align:center; min-width:140px; }
.tpl-theme-dot {
    display:inline-block;width:22px;height:22px;border-radius:50%;
    cursor:pointer;border:2.5px solid transparent;transition:all 0.18s;
    margin:3px;vertical-align:middle;
}
.tpl-theme-dot.active { border-color:#fff;box-shadow:0 0 0 2px var(--indigo); }
.tpl-theme-dot:hover { transform:scale(1.2); }

/* Upload zone (compact) */
.upload-zone-compact {
    border:2px dashed var(--border-color);border-radius:12px;
    padding:18px 14px;text-align:center;cursor:pointer;transition:all 0.2s;
    background:var(--bg-secondary);
}
.upload-zone-compact:hover,.upload-zone-compact.dragover {
    border-color:var(--indigo);background:rgba(99,102,241,0.06);
}
.upload-zone-compact input[type=file] { display:none; }
#uploadFilename { font-size:0.78rem;color:var(--indigo);margin-top:6px;font-weight:600; }

/* divider between cols */
.step1-divider {
    width:1px;background:var(--border-color);align-self:stretch;
    margin-top:4px;
}
@media(max-width:760px){ .step1-divider { width:100%;height:1px; margin:0; } }

/* ── Upload zone (old, kept for fallback) ────────────────────────── */
.upload-zone {
    border:2px dashed var(--border-color);border-radius:12px;
    padding:34px 18px;text-align:center;cursor:pointer;transition:all 0.2s;
    background:var(--bg-secondary);
}
.upload-zone:hover,.upload-zone.dragover { border-color:var(--indigo);background:rgba(99,102,241,0.06); }
.upload-zone input[type=file] { display:none; }

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
.design-row { display:flex;flex-wrap:nowrap;gap:10px;margin-top:14px;overflow-x:auto;padding-bottom:4px;align-items:flex-end; }
.design-row::-webkit-scrollbar { height:3px; }
.design-row::-webkit-scrollbar-thumb { background:var(--border-color);border-radius:3px; }
.design-ctrl { display:flex;flex-direction:column;gap:5px;flex-shrink:0; }
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

/* ── Design filmstrip ────────────────────────────────────────────── */
.filmstrip-wrap {
    display:flex;
    align-items:center;
    gap:8px;
    margin-top:14px;
}
.filmstrip-thumb-wrap {
    flex-shrink:0;
    width:100px;
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:4px;
    cursor:pointer;
    opacity:0.5;
    transition:opacity 0.2s;
}
.filmstrip-thumb-wrap:hover { opacity:0.9; }
.filmstrip-thumb-card {
    width:100%;
    border-radius:8px;
    border:2px solid var(--border-color);
    overflow:hidden;
    aspect-ratio:85.6/54;
    background:#111;
    transition:border-color 0.18s;
}
.filmstrip-thumb-card.portrait { aspect-ratio:54/85.6; }
.filmstrip-thumb-wrap:hover .filmstrip-thumb-card { border-color:var(--indigo); }
.filmstrip-center-wrap {
    flex:1;
    display:flex;
    flex-direction:column;
    align-items:center;
    min-width:0;
}
.filmstrip-center-card {
    width:100%;
    max-width:440px;
    border-radius:14px;
    overflow:hidden;
    box-shadow:0 20px 60px rgba(0,0,0,0.45);
    aspect-ratio:85.6/54;
    position:relative;
    transition:all 0.3s ease;
    background:#f7f8fc;
}
.filmstrip-center-card.portrait { aspect-ratio:54/85.6; max-width:250px; }
.filmstrip-nav-btn {
    flex-shrink:0;width:34px;height:34px;border-radius:50%;
    background:var(--bg-secondary);border:1.5px solid var(--border-color);
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;font-size:1.4rem;line-height:1;color:var(--text-secondary);
    transition:all 0.18s;user-select:none;padding:0;
}
.filmstrip-nav-btn:hover:not(:disabled) { border-color:var(--indigo);color:var(--indigo); }
.filmstrip-nav-btn:disabled { opacity:0.22;cursor:not-allowed; }
@media(max-width:680px){ .filmstrip-thumb-wrap { display:none; } }

/* ── See All modal ───────────────────────────────────────────────── */
.see-all-modal-overlay {
    display:none;position:fixed;inset:0;z-index:3000;
    background:rgba(0,0,0,0.75);align-items:flex-start;justify-content:center;
    overflow-y:auto;padding:30px 16px;
}
.see-all-modal-overlay.open { display:flex; }
.see-all-modal-box {
    background:var(--bg-card);border-radius:18px;padding:24px;
    width:100%;max-width:720px;position:relative;
}
.see-all-modal-close {
    position:absolute;top:12px;right:14px;background:none;border:none;
    font-size:1.4rem;cursor:pointer;color:var(--text-secondary);
}
.see-all-grid {
    display:grid;
    grid-template-columns:repeat(5,1fr);
    gap:10px;
    margin-top:14px;
}
@media(max-width:560px){ .see-all-grid { grid-template-columns:repeat(3,1fr); } }
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
<!-- STEP 1 — Category + Theme + Upload (one row)                  -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="bulk-card">
    <h3 style="font-size:0.95rem;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
        <span class="step-num">1</span> Setup &nbsp;<span style="font-size:0.72rem;color:var(--text-secondary);font-weight:400;">Category → Theme → Upload CSV</span>
    </h3>

    <div class="step1-row">
        <!-- Col A: Category dropdown -->
        <div>
            <div style="font-size:0.72rem;font-weight:600;color:var(--text-secondary);margin-bottom:7px;">
                <i class="fas fa-tags" style="color:var(--indigo);margin-right:4px;"></i> SELECT CARD CATEGORY
            </div>
            <div class="tpl-select-wrap">
                <select id="tplDropdown" onchange="selectTemplateByKey(this.value)">
                    <option value="">— choose a category —</option>
                    <?php foreach ($templates as $key => $tpl): ?>
                    <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($tpl['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-top:10px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span style="font-size:0.78rem;color:var(--text-secondary);">
                    Template: <strong id="selectedTplName" style="color:var(--indigo);">—</strong>
                </span>
                <a id="sampleCsvBtn" href="#"
                   style="pointer-events:none;opacity:0.4;" class="btn btn-secondary btn-sm">
                    <i class="fas fa-download"></i> Sample CSV
                </a>
            </div>
        </div>

        <!-- Divider -->
        <div class="step1-divider"></div>

        <!-- Col B: Theme colour dots -->
        <div class="theme-dots-col">
            <div style="font-size:0.72rem;font-weight:600;color:var(--text-secondary);margin-bottom:9px;">
                <i class="fas fa-palette" style="color:var(--indigo);margin-right:4px;"></i> THEME COLOUR
            </div>
            <div id="themeDots" style="display:flex;flex-wrap:wrap;justify-content:center;gap:4px;max-width:220px;margin:0 auto;">
                <?php foreach ($templates as $key => $tpl): ?>
                <span class="tpl-theme-dot"
                      style="background:<?= htmlspecialchars($tpl['color']) ?>;"
                      title="<?= htmlspecialchars($tpl['name']) ?>"
                      data-tpl="<?= htmlspecialchars($key) ?>"
                      onclick="selectTemplateByKey('<?= htmlspecialchars($key) ?>')"></span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Divider -->
        <div class="step1-divider"></div>

        <!-- Col C: Upload CSV -->
        <div>
            <div style="font-size:0.72rem;font-weight:600;color:var(--text-secondary);margin-bottom:7px;">
                <i class="fas fa-upload" style="color:var(--indigo);margin-right:4px;"></i> UPLOAD YOUR CSV
                <span style="font-size:0.65rem;font-weight:400;">(max <?= $maxBulkRows ?> rows)</span>
            </div>
            <div class="upload-zone-compact" id="uploadZone"
                 onclick="document.getElementById('csvFileInput').click()"
                 ondragover="handleDragOver(event)"
                 ondragleave="handleDragLeave(event)"
                 ondrop="handleDrop(event)">
                <i class="fas fa-file-csv" style="font-size:1.6rem;color:var(--indigo);opacity:0.6;margin-bottom:6px;display:block;"></i>
                <div style="font-size:0.82rem;font-weight:600;color:var(--text-primary);">Click or drag &amp; drop</div>
                <div style="font-size:0.7rem;color:var(--text-secondary);margin-top:2px;">.csv files only</div>
                <input type="file" id="csvFileInput" accept=".csv,text/csv" onchange="handleFileSelect(this)">
                <div id="uploadFilename"></div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- STEP 2 — Design                                               -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="bulk-card">
    <h3 style="font-size:0.95rem;font-weight:700;margin-bottom:4px;display:flex;align-items:center;gap:8px;">
        <span class="step-num">2</span> Choose Design
        <span style="font-size:0.72rem;color:var(--text-secondary);font-weight:400;margin-left:4px;">(applied to all cards)</span>
    </h3>

    <!-- Colour + font + filter row (single horizontal scrollable line) -->
    <div class="design-row">
        <div class="design-ctrl">
            <label>Primary</label>
            <input type="color" id="d_primary" value="#1e40af" oninput="buildFilmstrip()">
        </div>
        <div class="design-ctrl">
            <label>Accent</label>
            <input type="color" id="d_accent" value="#3b82f6" oninput="buildFilmstrip()">
        </div>
        <div class="design-ctrl">
            <label>Background</label>
            <input type="color" id="d_bg" value="#ffffff" oninput="buildFilmstrip()">
        </div>
        <div class="design-ctrl">
            <label>Text</label>
            <input type="color" id="d_text" value="#1e293b">
        </div>
        <div class="design-ctrl" style="min-width:100px;">
            <label>Font</label>
            <select id="d_font">
                <?php foreach(['Poppins','Inter','Roboto','Lato','Open Sans','Montserrat','Raleway'] as $f): ?>
                <option value="<?= $f ?>"><?= $f ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="design-ctrl" style="min-width:90px;">
            <label>Photo Shape</label>
            <select id="d_shape">
                <option value="circle">Circle</option>
                <option value="oval">Oval</option>
                <option value="square">Square</option>
            </select>
        </div>
        <!-- Filter buttons inline -->
        <div class="design-ctrl" style="border-left:1.5px solid var(--border-color);padding-left:10px;margin-left:2px;">
            <label>Filter</label>
            <div style="display:flex;gap:5px;flex-shrink:0;">
                <button type="button" id="filterAll" class="filter-btn active" onclick="setStyleFilter('all')">All</button>
                <button type="button" id="filterLandscape" class="filter-btn" onclick="setStyleFilter('landscape')">🖥</button>
                <button type="button" id="filterPortrait" class="filter-btn" onclick="setStyleFilter('portrait')">📱</button>
            </div>
        </div>
    </div>

    <!-- Style label + See All -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:14px;gap:8px;flex-wrap:wrap;">
        <div style="font-size:0.78rem;font-weight:600;color:var(--text-secondary);">
            Card Style &mdash; <span id="selectedStyleName" style="color:var(--indigo);">Angled Pro</span>
        </div>
        <button type="button" class="btn btn-secondary btn-sm" onclick="openSeeAll()">
            <i class="fas fa-th"></i> See All
        </button>
    </div>

    <!-- Filmstrip: [←] [prev-thumb] [LIVE PREVIEW] [next-thumb] [→] -->
    <div class="filmstrip-wrap">
        <button type="button" class="filmstrip-nav-btn" id="filmPrev" onclick="filmstripNavigate(-1)" disabled>&#8249;</button>
        <div class="filmstrip-thumb-wrap" id="filmThumbPrev" onclick="filmstripNavigate(-1)" style="display:none;">
            <div class="filmstrip-thumb-card" id="filmThumbPrevCard"></div>
            <div style="font-size:0.58rem;color:var(--text-secondary);text-align:center;line-height:1.2;" id="filmThumbPrevLabel"></div>
        </div>
        <div class="filmstrip-center-wrap">
            <div class="filmstrip-center-card" id="bulkStylePreview"></div>
        </div>
        <div class="filmstrip-thumb-wrap" id="filmThumbNext" onclick="filmstripNavigate(1)" style="display:none;">
            <div class="filmstrip-thumb-card" id="filmThumbNextCard"></div>
            <div style="font-size:0.58rem;color:var(--text-secondary);text-align:center;line-height:1.2;" id="filmThumbNextLabel"></div>
        </div>
        <button type="button" class="filmstrip-nav-btn" id="filmNext" onclick="filmstripNavigate(1)">&#8250;</button>
    </div>

    <input type="hidden" id="d_style" value="classic">
</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- See All Modal                                                  -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="see-all-modal-overlay" id="seeAllModal">
    <div class="see-all-modal-box">
        <button class="see-all-modal-close" onclick="closeSeeAll()">&times;</button>
        <h3 style="font-size:0.95rem;font-weight:700;margin-bottom:4px;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-th" style="color:var(--indigo);"></i> All Card Styles
        </h3>
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px;margin-top:8px;">
            <button type="button" id="maAll" class="filter-btn active" onclick="setModalFilter('all')">All (25)</button>
            <button type="button" id="maLandscape" class="filter-btn" onclick="setModalFilter('landscape')">🖥 Landscape</button>
            <button type="button" id="maPortrait" class="filter-btn" onclick="setModalFilter('portrait')">📱 Portrait</button>
        </div>
        <div class="see-all-grid" id="seeAllGrid"><!-- populated by JS --></div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- STEP 3 — Generate                                             -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="bulk-card">
    <h3 style="font-size:0.95rem;font-weight:700;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
        <span class="step-num">3</span> Generate All Cards
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

var ALL_STYLES = [
    { key:'classic',       label:'Angled Pro',    portrait:false },
    { key:'sidebar',       label:'Dark Geo',      portrait:false },
    { key:'wave',          label:'Wave Panel',    portrait:false },
    { key:'bold_header',   label:'Bold Split',    portrait:false },
    { key:'diagonal',      label:'Triangle Pro',  portrait:false },
    { key:'gradient_pro',  label:'Gradient Pro',  portrait:false },
    { key:'neon',          label:'Neon Glow',     portrait:false },
    { key:'executive',     label:'Executive',     portrait:false },
    { key:'stripe',        label:'Stripe Band',   portrait:false },
    { key:'metro',         label:'Metro Flat',    portrait:false },
    { key:'glass',         label:'Glassmorphism', portrait:false },
    { key:'zigzag',        label:'Zig-Zag',       portrait:false },
    { key:'ribbon',        label:'Ribbon',        portrait:false },
    { key:'v_sharp',       label:'Sharp V',       portrait:true },
    { key:'v_curve',       label:'Curve Wave',    portrait:true },
    { key:'v_hex',         label:'Hex Badge',     portrait:true },
    { key:'v_circle',      label:'Circle Top',    portrait:true },
    { key:'v_split',       label:'Color Split',   portrait:true },
    { key:'v_ribbon',      label:'Ribbon (V)',    portrait:true },
    { key:'v_arch',        label:'Arch (V)',      portrait:true },
    { key:'v_diamond',     label:'Diamond (V)',   portrait:true },
    { key:'v_corner',      label:'Corner (V)',    portrait:true },
    { key:'v_dual',        label:'Dual Band (V)', portrait:true },
    { key:'v_stripe',      label:'Stripe (V)',    portrait:true },
    { key:'v_badge',       label:'Badge (V)',     portrait:true }
];

var FIELD_SHORT = {
    department:'DEPT', employee_id:'ID NO', roll_number:'ROLL NO', id_number:'ID NO',
    badge_id:'BADGE', license_no:'LIC NO', blood_group:'B.GRP',
    phone:'PHONE', email:'E-MAIL', year:'YEAR', organization:'ORG'
};

var selectedTemplate   = '';
var fileSelected       = false;
var currentStyleKey    = 'classic';
var currentStyleFilter = 'all';

function getFilteredStyles() {
    if (currentStyleFilter === 'landscape') return ALL_STYLES.filter(function(s){ return !s.portrait; });
    if (currentStyleFilter === 'portrait')  return ALL_STYLES.filter(function(s){ return s.portrait; });
    return ALL_STYLES;
}

function setStyleFilter(f) {
    currentStyleFilter = f;
    ['all','landscape','portrait'].forEach(function(id) {
        var btn = document.getElementById('filter' + id.charAt(0).toUpperCase() + id.slice(1));
        if (btn) btn.classList.toggle('active', id === f);
    });
    var styles = getFilteredStyles();
    if (!styles.find(function(s){ return s.key === currentStyleKey; })) {
        currentStyleKey = styles[0] ? styles[0].key : 'classic';
        document.getElementById('d_style').value = currentStyleKey;
    }
    buildFilmstrip();
}

function qrSlotHTML(ps) { return ''; }

function fieldRowsHTML(items, lc, vc, fs) {
    fs = fs || 'clamp(0.38rem,0.9vw,0.54rem)';
    return items.map(function(f) {
        return '<div style="display:flex;align-items:baseline;font-size:'+fs+';white-space:nowrap;overflow:hidden;margin-bottom:1.8%;">'
            +'<span style="color:'+lc+';font-weight:700;min-width:30%;letter-spacing:0.03em;flex-shrink:0;">'+f.label+'</span>'
            +'<span style="color:'+vc+';margin-left:2%;overflow:hidden;text-overflow:ellipsis;">: '+f.val+'</span>'
            +'</div>';
    }).join('');
}

function getBulkCardValues() {
    var pri  = document.getElementById('d_primary').value || '#1e40af';
    var acc  = document.getElementById('d_accent').value  || '#3b82f6';
    var bg   = document.getElementById('d_bg').value      || '#ffffff';
    var txt  = document.getElementById('d_text').value    || '#1e293b';
    var font = document.getElementById('d_font').value    || 'Poppins';
    var shapeEl = document.getElementById('d_shape');
    var profileShape = shapeEl ? shapeEl.value : 'circle';
    var photoShapeCSS = profileShape === 'square' ? 'border-radius:4px;'
                      : (profileShape === 'oval'  ? 'border-radius:50% / 40%;' : 'border-radius:50%;');
    var photoHTML = '<i class="fas fa-user" style="font-size:1.8rem;opacity:0.55;color:rgba(255,255,255,0.8);"></i>';
    var s = ALL_STYLES.find(function(x){ return x.key === currentStyleKey; });
    var isPortrait = s ? s.portrait : false;
    var fieldItems = [
        { label:'DEPT',  val:'Marketing' },
        { label:'ID NO', val:'MBV-2024' },
        { label:'PHONE', val:'+1 234 567' },
        { label:'E-MAIL', val:'john@org.com' }
    ].slice(0, isPortrait ? 4 : 3);
    return { pri:pri, acc:acc, bg:bg, txt:txt, font:font,
             nameVal:'John Smith', roleVal:'Marketing Manager',
             orgVal:'Your Organization', addrVal:'',
             fieldItems:fieldItems, photoHTML:photoHTML,
             photoShapeCSS:photoShapeCSS, tplName:'CardX', portrait:isPortrait };
}
function renderClassic(v) {
    return '<div style="width:100%;height:100%;background:#f7f8fc;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<div style="position:absolute;top:0;left:0;right:0;height:100%;overflow:hidden;pointer-events:none;">'
        +'<div style="position:absolute;inset:0;background:linear-gradient(135deg,'+v.pri+' 0%,'+v.acc+' 100%);clip-path:polygon(0 0,100% 0,100% 40%,0 52%);"></div></div>'
        // School name centered in header
        +'<div style="position:absolute;top:4%;left:0;right:0;text-align:center;z-index:2;padding:0 4%;">'
        +'<div style="font-size:clamp(0.44rem,1.1vw,0.68rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.orgVal+'</div>'
        +(v.addrVal ? '<div style="font-size:clamp(0.3rem,0.7vw,0.44rem);color:rgba(255,255,255,0.8);margin-top:0.5%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.addrVal+'</div>' : '')
        +'</div>'
        // Three-column row: Photo | Name+Course | QR
        +'<div style="position:absolute;top:48%;left:4%;right:4%;display:flex;align-items:flex-start;gap:3%;z-index:2;">'
        // Left: photo
        +'<div style="width:22%;aspect-ratio:1;'+v.photoShapeCSS+'border:2.5px solid '+v.pri+';background:'+v.pri+'22;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(0,0,0,0.2);flex-shrink:0;">'+v.photoHTML+'</div>'
        // Middle: name + role + fields
        +'<div style="flex:1;min-width:0;overflow:hidden;">'
        +'<div style="font-size:clamp(0.56rem,1.3vw,0.8rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.32rem,0.76vw,0.48rem);color:#666;margin-top:0.5%;margin-bottom:2%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.roleVal+'</div>'
        +fieldRowsHTML(v.fieldItems,v.pri,'#444')
        +'</div>'
        // Right: QR slot
        +'<div style="flex-shrink:0;display:flex;align-items:flex-start;">'
        +qrSlotHTML('position:relative;')
        +'</div>'
        +'</div>'
        +'</div>';
}
function renderSidebar(v) {
    return '<div style="width:100%;height:100%;background:#111827;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<svg style="position:absolute;top:-18%;right:-12%;width:60%;aspect-ratio:1;" viewBox="0 0 100 100"><rect x="15" y="15" width="70" height="70" rx="3" fill="'+v.pri+'" transform="rotate(45 50 50)"/></svg>'
        +'<svg style="position:absolute;top:-8%;right:-5%;width:42%;aspect-ratio:1;opacity:0.35;" viewBox="0 0 100 100"><rect x="15" y="15" width="70" height="70" rx="3" fill="'+v.acc+'" transform="rotate(45 50 50)"/></svg>'
        // School name centered at top
        +'<div style="position:absolute;top:5%;left:5%;right:32%;text-align:left;z-index:2;">'
        +'<div style="font-size:clamp(0.36rem,0.85vw,0.54rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.orgVal+'</div>'
        +(v.addrVal ? '<div style="font-size:clamp(0.28rem,0.65vw,0.42rem);color:rgba(255,255,255,0.6);margin-top:0.5%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.addrVal+'</div>' : '')
        +'</div>'
        // Photo top-right
        +'<div style="position:absolute;top:6%;right:6%;width:22%;aspect-ratio:1;'+v.photoShapeCSS+'border:2.5px solid rgba(255,255,255,0.7);background:rgba(255,255,255,0.1);overflow:hidden;display:flex;align-items:center;justify-content:center;">'+v.photoHTML+'</div>'
        // Name
        +'<div style="position:absolute;top:32%;left:5%;right:32%;">'
        +'<div style="font-size:clamp(0.62rem,1.5vw,0.9rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.78vw,0.5rem);color:'+v.acc+';margin-top:1%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.roleVal+'</div>'
        +'</div>'
        // Fields left side
        +'<div style="position:absolute;top:52%;left:5%;right:50%;">'+fieldRowsHTML(v.fieldItems,'rgba(255,255,255,0.55)','rgba(255,255,255,0.88)')+'</div>'
        +qrSlotHTML('bottom:4%;right:4%;')
        +'</div>';
}
function renderWave(v) {
    return '<div style="width:100%;height:100%;background:#fdf8f3;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<svg style="position:absolute;top:0;left:0;width:44%;height:100%;" viewBox="0 0 88 160" preserveAspectRatio="none"><path d="M0,0 L60,0 Q80,25 70,55 Q85,80 72,110 Q88,135 65,160 L0,160 Z" fill="'+v.pri+'"/></svg>'
        +'<div style="position:absolute;left:24%;top:18%;transform:translateX(-50%);width:24%;aspect-ratio:1;'+v.photoShapeCSS+'border:3px solid rgba(255,255,255,0.8);background:rgba(255,255,255,0.18);overflow:hidden;display:flex;align-items:center;justify-content:center;">'+v.photoHTML+'</div>'
        +'<div style="position:absolute;bottom:14%;left:5%;max-width:40%;">'
        +'<div style="font-size:clamp(0.6rem,1.4vw,0.82rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.35rem,0.78vw,0.5rem);color:rgba(255,255,255,0.75);margin-top:1.5%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.roleVal+'</div></div>'
        +'<div style="position:absolute;top:5%;right:5%;display:flex;align-items:center;gap:5%;">'
        +'<div style="width:7%;aspect-ratio:1;border-radius:50%;background:'+v.pri+'22;border:1px solid '+v.pri+'44;display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:'+v.pri+';font-size:0.3rem;"></i></div>'
        +'<span style="font-size:clamp(0.32rem,0.72vw,0.46rem);color:'+v.pri+';font-weight:700;letter-spacing:0.06em;text-transform:uppercase;">'+v.tplName+'</span></div>'
        +'<div style="position:absolute;top:14%;right:4%;width:48%;">'+fieldRowsHTML(v.fieldItems,v.pri,'#4a3728')+'</div>'
        +qrSlotHTML('bottom:3%;right:4%;')
        +'</div>';
}
function renderBoldHeader(v) {
    return '<div style="width:100%;height:100%;display:flex;overflow:hidden;font-family:\''+v.font+'\',sans-serif;">'
        +'<div style="width:40%;background:linear-gradient(170deg,'+v.pri+' 0%,'+v.acc+' 100%);display:flex;flex-direction:column;align-items:center;position:relative;overflow:hidden;flex-shrink:0;">'
        +'<div style="position:absolute;top:-20%;left:-30%;width:90%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.07);"></div>'
        +'<div style="padding:8% 0 4%;position:relative;z-index:1;display:flex;flex-direction:column;align-items:center;gap:4%;width:100%;">'
        +'<div style="width:22%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1.5px solid rgba(255,255,255,0.5);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:white;font-size:0.4rem;"></i></div>'
        +'</div>'
        +'<div style="width:45%;aspect-ratio:1;'+v.photoShapeCSS+'border:3px solid rgba(255,255,255,0.8);background:rgba(255,255,255,0.15);overflow:hidden;display:flex;align-items:center;justify-content:center;position:relative;z-index:1;margin-top:2%;">'+v.photoHTML+'</div>'
        // Address below photo
        +(v.addrVal ? '<div style="position:relative;z-index:1;margin-top:4%;padding:0 6%;width:100%;text-align:center;"><div style="font-size:clamp(0.26rem,0.62vw,0.4rem);color:rgba(255,255,255,0.65);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.addrVal+'</div></div>' : '')
        // QR centered at bottom
        +'<div style="margin-top:auto;margin-bottom:5%;position:relative;z-index:1;">'
        +qrSlotHTML('position:relative;')
        +'</div>'
        +'</div>'
        +'<div style="flex:1;background:#ffffff;display:flex;flex-direction:column;justify-content:center;padding:6% 7%;min-width:0;position:relative;">'
        +'<div style="position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,'+v.pri+','+v.acc+');"></div>'
        // School name at top of right panel
        +'<div style="font-size:clamp(0.3rem,0.72vw,0.46rem);color:'+v.pri+';font-weight:700;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:3%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.orgVal+'</div>'
        +'<div style="font-size:clamp(0.62rem,1.52vw,0.88rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.8vw,0.5rem);color:#888;margin-top:1.5%;margin-bottom:4%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.roleVal+'</div>'
        +'<div style="width:60%;height:2px;background:linear-gradient(90deg,'+v.acc+',transparent);border-radius:2px;margin-bottom:5%;"></div>'
        +fieldRowsHTML(v.fieldItems,v.pri,'#555')
        +'</div></div>';
}
function renderDiagonal(v) {
    return '<div style="width:100%;height:100%;background:#111827;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<svg style="position:absolute;right:0;top:0;width:48%;height:100%;" viewBox="0 0 96 160" preserveAspectRatio="none">'
        +'<rect x="40" y="0" width="56" height="160" fill="'+v.pri+'18"/>'
        +'<polygon points="96,0 96,62 42,31" fill="'+v.pri+'"/>'
        +'<polygon points="96,52 96,112 48,82" fill="'+v.acc+'" opacity="0.85"/>'
        +'<polygon points="96,100 96,160 44,130" fill="'+v.pri+'" opacity="0.7"/></svg>'
        // School name at top left
        +'<div style="position:absolute;top:4%;left:5%;right:52%;z-index:2;">'
        +'<div style="font-size:clamp(0.34rem,0.8vw,0.5rem);color:rgba(255,255,255,0.85);font-weight:700;letter-spacing:0.06em;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.orgVal+'</div>'
        +(v.addrVal ? '<div style="font-size:clamp(0.28rem,0.65vw,0.42rem);color:rgba(255,255,255,0.55);margin-top:0.5%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.addrVal+'</div>' : '')
        +'</div>'
        // Photo on left, vertically centered
        +'<div style="position:absolute;left:5%;top:50%;transform:translateY(-50%);width:22%;aspect-ratio:1;'+v.photoShapeCSS+'border:2.5px solid '+v.acc+';background:rgba(255,255,255,0.08);overflow:hidden;display:flex;align-items:center;justify-content:center;">'+v.photoHTML+'</div>'
        // Name + role in middle
        +'<div style="position:absolute;left:32%;top:20%;right:52%;">'
        +'<div style="font-size:clamp(0.56rem,1.3vw,0.8rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.32rem,0.76vw,0.48rem);color:'+v.acc+';margin-top:2%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.roleVal+'</div>'
        +'</div>'
        // Fields below name/photo
        +'<div style="position:absolute;bottom:6%;left:5%;right:52%;overflow:hidden;">'+fieldRowsHTML(v.fieldItems,'rgba(255,255,255,0.55)','rgba(255,255,255,0.9)')+'</div>'
        +qrSlotHTML('bottom:4%;right:4%;z-index:2;')
        +'</div>';
}

// =============================================================================
//  Card renderers — PORTRAIT (vertical)
// =============================================================================
function renderVSharp(v) {
    return '<div style="width:100%;height:100%;background:#f7f8fc;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<div style="position:absolute;inset:0;background:linear-gradient(160deg,'+v.pri+' 0%,'+v.acc+' 100%);clip-path:polygon(0 0,100% 0,100% 38%,50% 48%,0 38%);"></div>'
        +'<div style="position:absolute;inset:0;background:rgba(255,255,255,0.1);clip-path:polygon(0 0,40% 0,0 20%);pointer-events:none;"></div>'
        // Logo + org top row
        +'<div style="position:absolute;top:3%;left:4%;right:4%;display:flex;align-items:center;justify-content:space-between;z-index:2;">'
        +'<div style="display:flex;align-items:center;gap:6%;">'
        +'<div style="width:12%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.35);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.85);font-size:0.35rem;"></i></div>'
        +'<div style="font-size:clamp(0.42rem,1vw,0.6rem);color:rgba(255,255,255,0.95);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:70%;">'+v.orgVal+'</div></div>'
        +'<div style="width:10%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.18);border:1px solid rgba(255,255,255,0.35);display:flex;align-items:center;justify-content:center;flex-shrink:0;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.8);font-size:0.32rem;"></i></div></div>'
        // Circle photo at V boundary
        +'<div style="position:absolute;left:50%;top:34%;transform:translateX(-50%);width:26%;aspect-ratio:1;border-radius:50%;border:3px solid #fff;background:'+v.pri+'22;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 28px rgba(0,0,0,0.25);z-index:3;">'+v.photoHTML+'</div>'
        // Name + role
        +'<div style="position:absolute;top:62%;left:4%;right:4%;text-align:center;">'
        +'<div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#777;margin-top:1%;;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.roleVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.82vw,0.5rem);color:'+v.pri+';font-weight:700;margin-top:2%;letter-spacing:0.04em;text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.orgVal+'</div></div>'
        // Accent divider
        +'<div style="position:absolute;top:71%;left:10%;right:10%;height:1.5px;background:linear-gradient(90deg,transparent,'+v.acc+',transparent);opacity:0.5;"></div>'
        // Fields
        +'<div style="position:absolute;top:73%;left:6%;right:6%;">'+fieldRowsHTML(v.fieldItems,v.pri,'#444','clamp(0.38rem,0.9vw,0.54rem)')+'</div>'
        // QR slot
        +qrSlotHTML('bottom:2%;right:3%;')
        +'</div>';
}

function renderVCurve(v) {
    return '<div style="width:100%;height:100%;background:#fafafa;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<svg style="position:absolute;top:0;left:0;width:100%;height:50%;" viewBox="0 0 100 100" preserveAspectRatio="none">'
        +'<path d="M0,0 L100,0 L100,70 Q75,95 50,80 Q25,65 0,85 Z" fill="'+v.pri+'"/>'
        +'<path d="M0,0 L100,0 L100,55 Q70,80 50,65 Q25,50 0,70 Z" fill="rgba(255,255,255,0.1)"/></svg>'
        +'<div style="position:absolute;bottom:-8%;right:-8%;width:35%;aspect-ratio:1;border-radius:50%;background:'+v.acc+';opacity:0.12;"></div>'
        +'<div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:8%;z-index:2;">'
        +'<div style="width:12%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.35);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.85);font-size:0.35rem;"></i></div>'
        +'<span style="font-size:clamp(0.4rem,1vw,0.56rem);color:rgba(255,255,255,0.9);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:70%;">'+v.orgVal+'</span></div>'
        +'<div style="position:absolute;left:50%;top:30%;transform:translateX(-50%);width:28%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.9);background:rgba(255,255,255,0.2);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 28px rgba(0,0,0,0.3);z-index:3;">'+v.photoHTML+'</div>'
        +'<div style="position:absolute;top:62%;left:4%;right:4%;text-align:center;">'
        +'<div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#888;margin-top:1%;;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.roleVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.82vw,0.5rem);color:'+v.pri+';font-weight:700;margin-top:2%;letter-spacing:0.04em;text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.orgVal+'</div></div>'
        +'<div style="position:absolute;top:70%;left:10%;right:10%;height:1px;background:'+v.pri+';opacity:0.2;"></div>'
        +'<div style="position:absolute;top:72%;left:6%;right:6%;">'+fieldRowsHTML(v.fieldItems,v.pri,'#444','clamp(0.38rem,0.9vw,0.54rem)')+'</div>'
        +qrSlotHTML('bottom:2%;right:3%;')
        +'</div>';
}

function renderVHex(v) {
    return '<div style="width:100%;height:100%;background:#ffffff;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<div style="position:absolute;top:0;left:0;right:0;height:45%;background:linear-gradient(135deg,'+v.pri+' 0%,'+v.acc+' 100%);overflow:hidden;">'
        +'<svg style="position:absolute;top:-15%;right:-12%;width:45%;aspect-ratio:1;opacity:0.18;" viewBox="0 0 100 100"><rect x="10" y="10" width="80" height="80" rx="4" fill="#fff" transform="rotate(45 50 50)"/></svg></div>'
        +'<div style="position:absolute;top:38%;left:0;right:0;height:8%;overflow:hidden;">'
        +'<svg viewBox="0 0 100 20" preserveAspectRatio="none" style="width:100%;height:100%;"><path d="M0,20 Q50,-5 100,20 L100,0 L0,0 Z" fill="'+v.pri+'"/><path d="M0,20 Q50,5 100,20" fill="none" stroke="#fff" stroke-width="1.5"/></svg></div>'
        +'<div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:8%;z-index:2;">'
        +'<div style="width:12%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.35);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.85);font-size:0.35rem;"></i></div>'
        +'<div style="font-size:clamp(0.4rem,1vw,0.56rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;">'+v.orgVal+'</div></div>'
        // Hexagonal photo
        +'<div style="position:absolute;left:50%;top:25%;transform:translateX(-50%);width:28%;aspect-ratio:1;z-index:4;">'
        +'<svg style="position:absolute;inset:-15%;width:130%;height:130%;" viewBox="0 0 100 100"><polygon points="50,2 95,26 95,74 50,98 5,74 5,26" fill="#fff" stroke="'+v.pri+'" stroke-width="2"/></svg>'
        +'<div style="position:absolute;inset:0;overflow:hidden;clip-path:polygon(50% 4%,93% 26%,93% 74%,50% 96%,7% 74%,7% 26%);display:flex;align-items:center;justify-content:center;background:'+v.pri+'20;">'+v.photoHTML+'</div></div>'
        // Name + role
        +'<div style="position:absolute;top:58%;left:4%;right:4%;text-align:center;">'
        +'<div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#888;margin-top:1%;;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.roleVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.82vw,0.5rem);color:'+v.pri+';font-weight:700;margin-top:2%;letter-spacing:0.04em;text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.orgVal+'</div></div>'
        +'<div style="position:absolute;top:66%;left:8%;right:8%;height:1.5px;background:'+v.pri+';opacity:0.25;"></div>'
        +'<div style="position:absolute;top:68%;left:6%;right:6%;">'+fieldRowsHTML(v.fieldItems,v.pri,'#444','clamp(0.38rem,0.9vw,0.54rem)')+'</div>'
        +qrSlotHTML('bottom:2%;right:3%;')
        +'</div>';
}

function renderVCircle(v) {
    return '<div style="width:100%;height:100%;background:#f7f8fc;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<div style="position:absolute;top:0;left:0;right:0;height:46%;background:linear-gradient(150deg,'+v.pri+' 0%,'+v.acc+' 100%);overflow:hidden;">'
        +'<svg style="position:absolute;right:-10%;bottom:-20%;width:60%;aspect-ratio:1;opacity:0.1;" viewBox="0 0 100 100">'
        +'<circle cx="50" cy="50" r="48" fill="none" stroke="#fff" stroke-width="6"/>'
        +'<circle cx="50" cy="50" r="35" fill="none" stroke="#fff" stroke-width="4"/>'
        +'<circle cx="50" cy="50" r="20" fill="none" stroke="#fff" stroke-width="3"/></svg></div>'
        +'<div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:6%;z-index:2;">'
        +'<div style="width:12%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.35);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.85);font-size:0.35rem;"></i></div>'
        +'<div style="font-size:clamp(0.38rem,0.9vw,0.54rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;">'+v.orgVal+'</div></div>'
        // Large circle photo
        +'<div style="position:absolute;left:50%;top:25%;transform:translateX(-50%);width:30%;aspect-ratio:1;border-radius:50%;border:4px solid #fff;background:'+v.pri+'22;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 32px rgba(0,0,0,0.28);z-index:3;">'+v.photoHTML+'</div>'
        // Name + role
        +'<div style="position:absolute;top:60%;left:4%;right:4%;text-align:center;">'
        +'<div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#777;margin-top:1%;;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.roleVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.82vw,0.5rem);color:'+v.pri+';font-weight:700;margin-top:2%;letter-spacing:0.04em;text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.orgVal+'</div></div>'
        +'<div style="position:absolute;top:68%;left:10%;right:10%;height:2px;background:linear-gradient(90deg,transparent,'+v.acc+',transparent);opacity:0.6;"></div>'
        +'<div style="position:absolute;top:70%;left:6%;right:6%;">'+fieldRowsHTML(v.fieldItems,v.pri,'#444','clamp(0.38rem,0.9vw,0.54rem)')+'</div>'
        +qrSlotHTML('bottom:2%;right:3%;')
        +'</div>';
}

function renderVSplit(v) {
    return '<div style="width:100%;height:100%;background:#ffffff;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<div style="position:absolute;top:0;left:0;width:55%;height:52%;background:'+v.pri+';overflow:hidden;">'
        +'<div style="position:absolute;bottom:-4%;right:-4%;width:50%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.08);"></div></div>'
        +'<div style="position:absolute;top:0;right:0;width:48%;height:40%;background:'+v.acc+';clip-path:polygon(10% 0,100% 0,100% 100%,0 100%);"></div>'
        +'<div style="position:absolute;bottom:0;left:0;right:0;height:5%;background:'+v.pri+';"></div>'
        +'<div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:7%;z-index:2;">'
        +'<div style="width:12%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.35);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.85);font-size:0.35rem;"></i></div>'
        +'<span style="font-size:clamp(0.38rem,0.9vw,0.54rem);color:rgba(255,255,255,0.9);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:65%;">'+v.orgVal+'</span></div>'
        // Photo upper-right
        +'<div style="position:absolute;top:5%;right:4%;width:28%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.85);background:rgba(255,255,255,0.15);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 22px rgba(0,0,0,0.25);z-index:3;">'+v.photoHTML+'</div>'
        // Name + role
        +'<div style="position:absolute;top:46%;left:4%;right:4%;">'
        +'<div style="font-size:clamp(0.82rem,2vw,1.08rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#888;margin-top:1%;;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.roleVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.82vw,0.5rem);color:'+v.pri+';font-weight:700;margin-top:2%;letter-spacing:0.04em;text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.orgVal+'</div></div>'
        +'<div style="position:absolute;top:56%;left:4%;right:4%;height:2px;background:linear-gradient(90deg,'+v.acc+',transparent);"></div>'
        +'<div style="position:absolute;top:58%;left:4%;right:4%;">'+fieldRowsHTML(v.fieldItems,v.pri,'#444','clamp(0.38rem,0.9vw,0.54rem)')+'</div>'
        +qrSlotHTML('bottom:7%;left:4%;')
        +'</div>';
}


// =============================================================================
//  New Landscape renderers (8 additional styles)
// =============================================================================
function renderGradientPro(v) {
    return '<div style="width:100%;height:100%;background:linear-gradient(135deg,'+v.pri+' 0%,'+v.acc+' 100%);font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<div style="position:absolute;inset:0;background:rgba(0,0,0,0.18);"></div>'
        // School name top center
        +'<div style="position:absolute;top:4%;left:0;right:0;text-align:center;z-index:2;padding:0 4%;">'
        +'<div style="font-size:clamp(0.38rem,0.9vw,0.56rem);font-weight:700;color:rgba(255,255,255,0.92);letter-spacing:0.05em;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.orgVal+'</div>'
        +'</div>'
        // Three-column: photo | name+fields | QR
        +'<div style="position:absolute;top:20%;left:4%;right:4%;bottom:8%;display:flex;align-items:center;gap:3%;z-index:2;">'
        +'<div style="width:26%;aspect-ratio:1;'+v.photoShapeCSS+'border:3px solid rgba(255,255,255,0.8);background:rgba(255,255,255,0.15);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 24px rgba(0,0,0,0.3);flex-shrink:0;">'+v.photoHTML+'</div>'
        +'<div style="flex:1;min-width:0;overflow:hidden;">'
        +'<div style="font-size:clamp(0.62rem,1.55vw,0.9rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.8vw,0.5rem);color:rgba(255,255,255,0.75);margin-top:1.5%;margin-bottom:3%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.roleVal+'</div>'
        +'<div style="width:80%;height:1.5px;background:rgba(255,255,255,0.35);border-radius:2px;margin-bottom:4%;"></div>'
        +fieldRowsHTML(v.fieldItems,'rgba(255,255,255,0.65)','rgba(255,255,255,0.9)')
        +'</div>'
        // QR extreme right
        +'<div style="flex-shrink:0;display:flex;align-items:center;">'
        +qrSlotHTML('position:relative;')
        +'</div>'
        +'</div>'
        +'</div>';
}

function renderNeon(v) {
    return '<div style="width:100%;height:100%;background:#050a10;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<div style="position:absolute;top:0;left:0;right:0;height:3px;background:'+v.pri+';box-shadow:0 0 10px '+v.pri+';"></div>'
        +'<div style="position:absolute;bottom:0;left:0;right:0;height:3px;background:'+v.acc+';box-shadow:0 0 10px '+v.acc+';"></div>'
        // School name + address at top
        +'<div style="position:absolute;top:7%;left:5%;right:5%;text-align:center;z-index:2;">'
        +'<div style="font-size:clamp(0.38rem,0.9vw,0.56rem);color:'+v.acc+';font-weight:700;letter-spacing:0.08em;text-transform:uppercase;text-shadow:0 0 8px '+v.acc+'80;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.orgVal+'</div>'
        +(v.addrVal ? '<div style="font-size:clamp(0.28rem,0.65vw,0.42rem);color:rgba(255,255,255,0.45);margin-top:0.5%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.addrVal+'</div>' : '')
        +'</div>'
        // Photo left
        +'<div style="position:absolute;left:5%;top:50%;transform:translateY(-50%);width:22%;aspect-ratio:1;'+v.photoShapeCSS+'border:2.5px solid '+v.acc+';background:rgba(255,255,255,0.04);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 0 16px '+v.acc+'60;">'+v.photoHTML+'</div>'
        // Name + role + fields in middle
        +'<div style="position:absolute;left:34%;top:22%;right:18%;overflow:hidden;">'
        +'<div style="font-size:clamp(0.62rem,1.55vw,0.9rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-shadow:0 0 12px rgba(255,255,255,0.3);">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.8vw,0.5rem);color:'+v.acc+';margin-top:2%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-shadow:0 0 8px '+v.acc+'60;">'+v.roleVal+'</div>'
        +'</div>'
        +'<div style="position:absolute;top:52%;left:34%;right:18%;overflow:hidden;">'+fieldRowsHTML(v.fieldItems,'rgba(255,255,255,0.45)','rgba(255,255,255,0.85)')+'</div>'
        // QR extreme right
        +qrSlotHTML('top:50%;right:2%;transform:translateY(-50%);')
        +'</div>';
}

function renderExecutive(v) {
    var gold = '#c9a84c';
    return '<div style="width:100%;height:100%;background:#1a1f2e;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<div style="position:absolute;top:0;left:0;right:0;height:4px;background:'+gold+';"></div>'
        +'<div style="position:absolute;bottom:0;left:0;right:0;height:4px;background:'+gold+';"></div>'
        +'<div style="position:absolute;top:4px;left:4px;right:4px;bottom:4px;border:0.5px solid rgba(201,168,76,0.25);pointer-events:none;"></div>'
        // School name at top center
        +'<div style="position:absolute;top:7%;left:5%;right:5%;text-align:center;z-index:2;">'
        +'<div style="font-size:clamp(0.38rem,0.9vw,0.56rem);color:'+gold+';font-weight:700;letter-spacing:0.08em;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.orgVal+'</div>'
        +'</div>'
        // Photo left
        +'<div style="position:absolute;left:5%;top:50%;transform:translateY(-50%);width:22%;aspect-ratio:1;'+v.photoShapeCSS+'border:2.5px solid '+gold+';background:rgba(201,168,76,0.1);overflow:hidden;display:flex;align-items:center;justify-content:center;">'+v.photoHTML+'</div>'
        // Name + role + fields in middle
        +'<div style="position:absolute;left:34%;top:22%;right:18%;overflow:hidden;">'
        +'<div style="font-size:clamp(0.62rem,1.55vw,0.9rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.8vw,0.5rem);color:'+gold+';margin-top:2%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.roleVal+'</div>'
        +'<div style="width:60%;height:1.5px;background:linear-gradient(90deg,'+gold+',transparent);margin-top:4%;margin-bottom:4%;"></div>'
        +fieldRowsHTML(v.fieldItems,'rgba(255,255,255,0.45)','rgba(255,255,255,0.88)')
        +'</div>'
        // QR extreme right
        +qrSlotHTML('top:50%;right:2%;transform:translateY(-50%);')
        +'</div>';
}

function renderStripe(v) {
    return '<div style="width:100%;height:100%;background:#f5f7fa;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<div style="position:absolute;top:0;left:0;right:0;height:18%;background:'+v.pri+';display:flex;align-items:center;justify-content:center;padding:0 4%;">'
        +'<div style="font-size:clamp(0.4rem,0.95vw,0.6rem);font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-align:center;letter-spacing:0.05em;text-transform:uppercase;">'+v.orgVal+'</div>'
        +'</div>'
        +'<div style="position:absolute;bottom:0;left:0;right:0;height:16%;background:'+v.acc+';"></div>'
        // Center: photo
        +'<div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:22%;aspect-ratio:1;'+v.photoShapeCSS+'border:2.5px solid '+v.pri+';background:#fff;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(0,0,0,0.15);">'+v.photoHTML+'</div>'
        // Right: name + fields
        +'<div style="position:absolute;right:5%;top:20%;max-width:44%;">'
        +'<div style="font-size:clamp(0.56rem,1.3vw,0.8rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.32rem,0.76vw,0.48rem);color:#888;margin-top:2%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.roleVal+'</div>'
        +'<div style="width:60%;height:2px;background:linear-gradient(90deg,'+v.acc+',transparent);border-radius:2px;margin:4% 0;"></div>'
        +fieldRowsHTML(v.fieldItems,v.pri,'#555')
        +'</div>'
        +qrSlotHTML('bottom:18%;left:5%;')
        +'</div>';
}

function renderMetro(v) {
    return '<div style="width:100%;height:100%;display:flex;overflow:hidden;font-family:\''+v.font+'\',sans-serif;border-top:4px solid '+v.pri+';border-bottom:4px solid '+v.acc+';">'
        +'<div style="width:35%;background:'+v.pri+';display:flex;flex-direction:column;align-items:center;flex-shrink:0;position:relative;">'
        +'<div style="position:absolute;top:0;right:0;width:4px;height:100%;background:'+v.acc+'88;"></div>'
        +'<div style="padding:8% 0 5%;display:flex;flex-direction:column;align-items:center;gap:5%;width:100%;">'
        +'<div style="width:22%;aspect-ratio:1;border-radius:0;background:rgba(255,255,255,0.22);border:1.5px solid rgba(255,255,255,0.5);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:white;font-size:0.4rem;"></i></div>'
        +'</div>'
        +'<div style="width:55%;aspect-ratio:1;'+v.photoShapeCSS+'border:3px solid rgba(255,255,255,0.8);background:rgba(255,255,255,0.15);overflow:hidden;display:flex;align-items:center;justify-content:center;margin-top:4%;">'+v.photoHTML+'</div>'
        +'<div style="margin-top:auto;padding-bottom:6%;display:flex;align-items:center;justify-content:center;width:100%;">'
        +qrSlotHTML('position:relative;')
        +'</div>'
        +'</div>'
        +'<div style="flex:1;background:#ffffff;display:flex;flex-direction:column;justify-content:center;padding:6% 7%;min-width:0;">'
        +'<div style="font-size:clamp(0.38rem,0.9vw,0.56rem);font-weight:700;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:1%;">'+v.orgVal+'</div>'
        +(v.addrVal ? '<div style="font-size:clamp(0.28rem,0.65vw,0.42rem);color:#999;margin-bottom:3%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.addrVal+'</div>' : '')
        +'<div style="font-size:clamp(0.62rem,1.52vw,0.88rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.8vw,0.5rem);color:#888;margin-top:1.5%;margin-bottom:4%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.roleVal+'</div>'
        +'<div style="width:50%;height:3px;background:'+v.acc+';border-radius:0;margin-bottom:5%;"></div>'
        +fieldRowsHTML(v.fieldItems,v.pri,'#555')
        +'</div></div>';
}

function renderGlass(v) {
    return '<div style="width:100%;height:100%;background:linear-gradient(135deg,'+v.pri+' 0%,'+v.acc+' 100%);font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<div style="position:absolute;inset:8% 6%;background:rgba(255,255,255,0.15);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);border:1px solid rgba(255,255,255,0.35);border-radius:10px;overflow:hidden;">'
        +'<div style="position:absolute;top:-30%;left:-20%;width:60%;aspect-ratio:1;background:rgba(255,255,255,0.08);border-radius:50%;"></div>'
        +'</div>'
        // School name at top
        +'<div style="position:absolute;top:10%;left:10%;right:10%;text-align:center;z-index:2;">'
        +'<div style="font-size:clamp(0.38rem,0.9vw,0.56rem);font-weight:700;color:rgba(255,255,255,0.92);letter-spacing:0.06em;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.orgVal+'</div>'
        +'</div>'
        // Photo left + QR below photo
        +'<div style="position:absolute;left:10%;top:24%;display:flex;flex-direction:column;align-items:center;gap:4%;z-index:3;">'
        +'<div style="width:22%;aspect-ratio:1;'+v.photoShapeCSS+'border:2.5px solid rgba(255,255,255,0.8);background:rgba(255,255,255,0.2);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 28px rgba(0,0,0,0.2);">'+v.photoHTML+'</div>'
        +qrSlotHTML('position:relative;margin-top:4%;')
        +'</div>'
        // Name + fields right
        +'<div style="position:absolute;left:40%;top:22%;right:10%;z-index:2;overflow:hidden;">'
        +'<div style="font-size:clamp(0.62rem,1.55vw,0.9rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-shadow:0 2px 8px rgba(0,0,0,0.2);">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.8vw,0.5rem);color:rgba(255,255,255,0.8);margin-top:2%;margin-bottom:4%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.roleVal+'</div>'
        +'<div style="width:60%;height:1px;background:rgba(255,255,255,0.4);margin-bottom:4%;"></div>'
        +fieldRowsHTML(v.fieldItems,'rgba(255,255,255,0.7)','rgba(255,255,255,0.92)')
        +'</div>'
        +'</div>';
}

function renderZigzag(v) {
    var W = 85.6, H = 54;
    var zzPath = 'M0,0 L'+W+',0 L'+W+','+(H*0.4);
    for (var i = 10; i >= 0; i--) {
        var xr = (i/10)*W;
        var ym = (i%2===0) ? (H*0.4) : (H*0.32);
        zzPath += ' L'+xr+','+ym;
    }
    zzPath += ' Z';
    return '<div style="width:100%;height:100%;background:#f7f8fc;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<svg style="position:absolute;top:0;left:0;width:100%;height:100%;" viewBox="0 0 '+W+' '+H+'" xmlns="http://www.w3.org/2000/svg">'
        +'<path d="'+zzPath+'" fill="'+v.pri+'"/>'
        +'</svg>'
        // School name centered in header
        +'<div style="position:absolute;top:5%;left:0;right:0;text-align:center;z-index:2;padding:0 4%;">'
        +'<div style="font-size:clamp(0.4rem,0.95vw,0.6rem);font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.orgVal+'</div>'
        +'</div>'
        // Three-column: photo left | name+fields center | QR right
        +'<div style="position:absolute;top:38%;left:3%;right:3%;display:flex;align-items:flex-start;gap:2%;z-index:2;">'
        // Photo left
        +'<div style="width:22%;aspect-ratio:1;'+v.photoShapeCSS+'border:2.5px solid '+v.pri+';background:'+v.pri+'22;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(0,0,0,0.2);flex-shrink:0;">'+v.photoHTML+'</div>'
        // Name + role + fields center
        +'<div style="flex:1;min-width:0;overflow:hidden;">'
        +'<div style="font-size:clamp(0.56rem,1.3vw,0.8rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.32rem,0.76vw,0.48rem);color:#666;margin-top:1%;margin-bottom:2%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.roleVal+'</div>'
        +fieldRowsHTML(v.fieldItems,v.pri,'#444')
        +'</div>'
        // QR right
        +'<div style="flex-shrink:0;display:flex;align-items:flex-start;">'
        +qrSlotHTML('position:relative;')
        +'</div>'
        +'</div>'
        +'</div>';
}

function renderRibbon(v) {
    return '<div style="width:100%;height:100%;background:#111827;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<svg style="position:absolute;inset:0;width:100%;height:100%;" viewBox="0 0 85.6 54" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">'
        +'<polygon points="0,15 85.6,8 85.6,27 0,34" fill="'+v.pri+'" opacity="0.92"/>'
        +'<polygon points="0,18 85.6,11 85.6,31 0,38" fill="'+v.acc+'" opacity="0.55"/>'
        +'</svg>'
        // School name at top left
        +'<div style="position:absolute;top:4%;left:5%;right:32%;z-index:2;">'
        +'<div style="font-size:clamp(0.38rem,0.9vw,0.56rem);font-weight:700;color:rgba(255,255,255,0.88);letter-spacing:0.05em;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.orgVal+'</div>'
        +'</div>'
        // Photo on RIGHT side
        +'<div style="position:absolute;right:5%;top:50%;transform:translateY(-50%);width:22%;aspect-ratio:1;'+v.photoShapeCSS+'border:2.5px solid rgba(255,255,255,0.8);background:rgba(255,255,255,0.1);overflow:hidden;display:flex;align-items:center;justify-content:center;z-index:3;">'+v.photoHTML+'</div>'
        // Name + role on left
        +'<div style="position:absolute;left:5%;top:14%;right:30%;z-index:2;overflow:hidden;">'
        +'<div style="font-size:clamp(0.62rem,1.55vw,0.9rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.8vw,0.5rem);color:rgba(255,255,255,0.75);margin-top:2%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.roleVal+'</div>'
        +'</div>'
        // Fields bottom left
        +'<div style="position:absolute;bottom:8%;left:5%;right:30%;z-index:2;overflow:hidden;">'+fieldRowsHTML(v.fieldItems,'rgba(255,255,255,0.5)','rgba(255,255,255,0.88)')+'</div>'
        +qrSlotHTML('bottom:4%;right:30%;z-index:2;')
        +'</div>';
}

// =============================================================================
//  New Portrait renderers (7 additional styles)
// =============================================================================
function renderVRibbon(v) {
    return '<div style="width:100%;height:100%;background:#f7f8fc;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        // Top narrow band
        +'<div style="position:absolute;top:0;left:0;right:0;height:20%;background:'+v.pri+';"></div>'
        // Accent ribbon band
        +'<div style="position:absolute;top:22%;left:0;right:0;height:13%;background:'+v.acc+';opacity:0.9;"></div>'
        +'<div style="position:absolute;top:35%;left:0;right:0;height:3%;background:'+v.pri+'22;"></div>'
        // Logo top-left
        +'<div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:6%;z-index:2;">'
        +'<div style="width:12%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.4);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.9);font-size:0.35rem;"></i></div>'
        +'<span style="font-size:clamp(0.42rem,1vw,0.6rem);color:rgba(255,255,255,0.95);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:70%;">'+v.orgVal+'</span></div>'
        // Photo circle centred on ribbon boundary
        +'<div style="position:absolute;left:50%;top:18%;transform:translateX(-50%);width:26%;aspect-ratio:1;border-radius:50%;border:3px solid #fff;background:'+v.pri+'22;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 28px rgba(0,0,0,0.25);z-index:3;">'+v.photoHTML+'</div>'
        // Name + role
        +'<div style="position:absolute;top:42%;left:4%;right:4%;text-align:center;">'
        +'<div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#777;margin-top:1%;;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.roleVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.82vw,0.5rem);color:'+v.pri+';font-weight:700;margin-top:2%;letter-spacing:0.04em;text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.orgVal+'</div></div>'
        +'<div style="position:absolute;top:51%;left:10%;right:10%;height:1.5px;background:linear-gradient(90deg,transparent,'+v.acc+',transparent);opacity:0.6;"></div>'
        +'<div style="position:absolute;top:53%;left:6%;right:6%;">'+fieldRowsHTML(v.fieldItems,v.pri,'#444','clamp(0.38rem,0.9vw,0.54rem)')+'</div>'
        +qrSlotHTML('bottom:2%;right:3%;')
        +'</div>';
}

function renderVArch(v) {
    return '<div style="width:100%;height:100%;background:#ffffff;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        // Arch SVG header — fills top ~60% with curved arch bottom
        +'<svg style="position:absolute;top:0;left:0;width:100%;height:62%;" viewBox="0 0 54 54" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">'
        +'<path d="M0,0 L54,0 L54,40 Q54,54 27,54 Q0,54 0,40 Z" fill="'+v.pri+'"/>'
        +'<path d="M0,0 L54,0 L54,28 Q54,40 27,40 Q0,40 0,28 Z" fill="rgba(255,255,255,0.08)"/>'
        +'</svg>'
        // Logo top-left
        +'<div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:6%;z-index:2;">'
        +'<div style="width:12%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.4);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.85);font-size:0.35rem;"></i></div>'
        +'<span style="font-size:clamp(0.42rem,1vw,0.6rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;">'+v.orgVal+'</span></div>'
        // Photo inside arch
        +'<div style="position:absolute;left:50%;top:18%;transform:translateX(-50%);width:28%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.85);background:rgba(255,255,255,0.18);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 28px rgba(0,0,0,0.28);z-index:3;">'+v.photoHTML+'</div>'
        // Name + role below arch
        +'<div style="position:absolute;top:64%;left:4%;right:4%;text-align:center;">'
        +'<div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#888;margin-top:1%;;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.roleVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.82vw,0.5rem);color:'+v.pri+';font-weight:700;margin-top:2%;letter-spacing:0.04em;text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.orgVal+'</div></div>'
        +'<div style="position:absolute;top:72%;left:10%;right:10%;height:1px;background:'+v.pri+';opacity:0.2;"></div>'
        +'<div style="position:absolute;top:74%;left:6%;right:6%;">'+fieldRowsHTML(v.fieldItems,v.pri,'#444','clamp(0.38rem,0.9vw,0.54rem)')+'</div>'
        +qrSlotHTML('bottom:2%;right:3%;')
        +'</div>';
}

function renderVDiamond(v) {
    return '<div style="width:100%;height:100%;background:#f7f8fc;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        +'<div style="position:absolute;top:0;left:0;right:0;height:50%;background:'+v.pri+';overflow:hidden;">'
        +'<svg style="position:absolute;bottom:-2%;left:0;width:100%;height:22%;" viewBox="0 0 54 12" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">'
        +'<polygon points="27,12 54,0 0,0" fill="#f7f8fc"/>'
        +'</svg>'
        +'<svg style="position:absolute;top:5%;right:5%;width:30%;opacity:0.15;" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">'
        +'<rect x="10" y="10" width="80" height="80" rx="4" fill="#fff" transform="rotate(45 50 50)"/>'
        +'</svg></div>'
        +'<div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:6%;z-index:2;">'
        +'<div style="width:12%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.4);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.85);font-size:0.35rem;"></i></div>'
        +'<span style="font-size:clamp(0.42rem,1vw,0.6rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;">'+v.orgVal+'</span></div>'
        // Diamond/rhombus photo frame
        +'<div style="position:absolute;left:50%;top:28%;transform:translateX(-50%);width:28%;aspect-ratio:1;z-index:4;">'
        +'<svg style="position:absolute;inset:-18%;width:136%;height:136%;" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">'
        +'<polygon points="50,2 98,50 50,98 2,50" fill="#fff" stroke="'+v.pri+'" stroke-width="2.5"/>'
        +'</svg>'
        +'<div style="position:absolute;inset:0;overflow:hidden;clip-path:polygon(50% 2%,98% 50%,50% 98%,2% 50%);display:flex;align-items:center;justify-content:center;background:'+v.pri+'20;">'+v.photoHTML+'</div></div>'
        // Name + role
        +'<div style="position:absolute;top:60%;left:4%;right:4%;text-align:center;">'
        +'<div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#888;margin-top:1%;;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.roleVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.82vw,0.5rem);color:'+v.pri+';font-weight:700;margin-top:2%;letter-spacing:0.04em;text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.orgVal+'</div></div>'
        +'<div style="position:absolute;top:68%;left:8%;right:8%;height:1.5px;background:'+v.pri+';opacity:0.25;"></div>'
        +'<div style="position:absolute;top:70%;left:6%;right:6%;">'+fieldRowsHTML(v.fieldItems,v.pri,'#444','clamp(0.38rem,0.9vw,0.54rem)')+'</div>'
        +qrSlotHTML('bottom:2%;right:3%;')
        +'</div>';
}

function renderVCorner(v) {
    return '<div style="width:100%;height:100%;background:#ffffff;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        // Large triangle top-left
        +'<svg style="position:absolute;top:0;left:0;width:100%;height:100%;" viewBox="0 0 54 85.6" xmlns="http://www.w3.org/2000/svg">'
        +'<polygon points="0,0 35,0 0,47" fill="'+v.pri+'"/>'
        +'<polygon points="0,0 22,0 0,30" fill="rgba(255,255,255,0.1)"/>'
        +'<polygon points="54,85.6 19,85.6 54,39" fill="'+v.acc+'" opacity="0.85"/>'
        +'</svg>'
        // Logo top-left
        +'<div style="position:absolute;top:3%;left:4%;z-index:2;">'
        +'<div style="width:14%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.4);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.85);font-size:0.35rem;"></i></div></div>'
        // Photo circle centre-top
        +'<div style="position:absolute;left:50%;top:22%;transform:translateX(-50%);width:28%;aspect-ratio:1;border-radius:50%;border:3px solid #fff;background:'+v.pri+'15;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 28px rgba(0,0,0,0.2);z-index:3;">'+v.photoHTML+'</div>'
        // Org name right of logo
        +'<div style="position:absolute;top:3%;right:4%;max-width:45%;text-align:right;z-index:2;">'
        +'<div style="font-size:clamp(0.4rem,1vw,0.58rem);color:'+v.pri+';font-weight:700;letter-spacing:0.05em;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.orgVal+'</div></div>'
        // Name + role
        +'<div style="position:absolute;top:53%;left:4%;right:4%;text-align:center;">'
        +'<div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#888;margin-top:1%;;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.roleVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.82vw,0.5rem);color:'+v.pri+';font-weight:700;margin-top:2%;letter-spacing:0.04em;text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.orgVal+'</div></div>'
        +'<div style="position:absolute;top:61%;left:10%;right:10%;height:1.5px;background:linear-gradient(90deg,transparent,'+v.acc+',transparent);opacity:0.5;"></div>'
        +'<div style="position:absolute;top:63%;left:6%;right:6%;">'+fieldRowsHTML(v.fieldItems,v.pri,'#444','clamp(0.38rem,0.9vw,0.54rem)')+'</div>'
        +qrSlotHTML('bottom:2%;right:3%;')
        +'</div>';
}

function renderVDual(v) {
    return '<div style="width:100%;height:100%;background:#f7f8fc;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        // Top band
        +'<div style="position:absolute;top:0;left:0;right:0;height:20%;background:'+v.pri+';"></div>'
        // Bottom band
        +'<div style="position:absolute;bottom:0;left:0;right:0;height:18%;background:'+v.acc+';"></div>'
        // Logo top
        +'<div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:6%;z-index:2;">'
        +'<div style="width:12%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.4);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.85);font-size:0.35rem;"></i></div>'
        +'<span style="font-size:clamp(0.42rem,1vw,0.6rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:70%;">'+v.orgVal+'</span></div>'
        // Photo circle centred in white area
        +'<div style="position:absolute;left:50%;top:26%;transform:translateX(-50%);width:28%;aspect-ratio:1;border-radius:50%;border:3px solid '+v.pri+';background:#fff;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 28px rgba(0,0,0,0.2);z-index:3;">'+v.photoHTML+'</div>'
        // Name + role
        +'<div style="position:absolute;top:57%;left:4%;right:4%;text-align:center;">'
        +'<div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#777;margin-top:1%;;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.roleVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.82vw,0.5rem);color:'+v.pri+';font-weight:700;margin-top:2%;letter-spacing:0.04em;text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.orgVal+'</div></div>'
        +'<div style="position:absolute;top:65%;left:10%;right:10%;height:1px;background:'+v.pri+';opacity:0.2;"></div>'
        +'<div style="position:absolute;top:67%;left:6%;right:6%;">'+fieldRowsHTML(v.fieldItems,v.pri,'#444','clamp(0.38rem,0.9vw,0.54rem)')+'</div>'
        // QR sits above bottom band
        +qrSlotHTML('bottom:20%;left:50%;transform:translateX(-50%);')
        +'</div>';
}

function renderVStripe(v) {
    return '<div style="width:100%;height:100%;background:linear-gradient(135deg,'+v.pri+' 0%,'+v.acc+' 100%);font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        // Frosted inner panel
        +'<div style="position:absolute;top:5%;left:5%;right:5%;bottom:5%;background:rgba(255,255,255,0.18);border:1px solid rgba(255,255,255,0.35);border-radius:8px;backdrop-filter:blur(3px);"></div>'
        // Logo top
        +'<div style="position:absolute;top:8%;left:9%;display:flex;align-items:center;gap:6%;z-index:2;">'
        +'<div style="width:12%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.4);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.9);font-size:0.35rem;"></i></div>'
        +'<span style="font-size:clamp(0.42rem,1vw,0.6rem);color:rgba(255,255,255,0.9);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;">'+v.orgVal+'</span></div>'
        // Photo
        +'<div style="position:absolute;left:50%;top:22%;transform:translateX(-50%);width:28%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.85);background:rgba(255,255,255,0.2);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 30px rgba(0,0,0,0.3);z-index:3;">'+v.photoHTML+'</div>'
        // Name + role
        +'<div style="position:absolute;top:56%;left:4%;right:4%;text-align:center;">'
        +'<div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-shadow:0 2px 8px rgba(0,0,0,0.2);">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.42rem,1vw,0.58rem);color:rgba(255,255,255,0.8);margin-top:1%;;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.roleVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.82vw,0.5rem);color:'+v.pri+';font-weight:700;margin-top:2%;letter-spacing:0.04em;text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.orgVal+'</div></div>'
        +'<div style="position:absolute;top:64%;left:10%;right:10%;height:1px;background:rgba(255,255,255,0.4);"></div>'
        +'<div style="position:absolute;top:66%;left:9%;right:9%;">'+fieldRowsHTML(v.fieldItems,'rgba(255,255,255,0.7)','rgba(255,255,255,0.92)','clamp(0.38rem,0.9vw,0.54rem)')+'</div>'
        +qrSlotHTML('bottom:7%;left:50%;transform:translateX(-50%);')
        +'</div>';
}

function renderVBadge(v) {
    return '<div style="width:100%;height:100%;background:#f7f8fc;font-family:\''+v.font+'\',sans-serif;position:relative;overflow:hidden;">'
        // Shield/badge shape fills top portion using SVG clip
        +'<svg style="position:absolute;top:0;left:0;width:100%;height:100%;" viewBox="0 0 54 85.6" xmlns="http://www.w3.org/2000/svg">'
        +'<path d="M4,0 L50,0 L54,5 L54,36 Q54,48 27,52 Q0,48 0,36 L0,5 Z" fill="'+v.pri+'"/>'
        +'<path d="M4,0 L50,0 L54,5 L54,25 Q54,36 27,38 Q0,36 0,25 L0,5 Z" fill="rgba(255,255,255,0.08)"/>'
        +'<circle cx="3" cy="3" r="2.5" fill="rgba(255,255,255,0.25)"/>'
        +'</svg>'
        // Logo top
        +'<div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:6%;z-index:2;">'
        +'<div style="width:12%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.4);display:flex;align-items:center;justify-content:center;">'
        +'<i class="fas fa-infinity" style="color:rgba(255,255,255,0.85);font-size:0.35rem;"></i></div>'
        +'<span style="font-size:clamp(0.42rem,1vw,0.6rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;">'+v.orgVal+'</span></div>'
        // Photo inside shield
        +'<div style="position:absolute;left:50%;top:18%;transform:translateX(-50%);width:28%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.85);background:rgba(255,255,255,0.2);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 28px rgba(0,0,0,0.28);z-index:3;">'+v.photoHTML+'</div>'
        // Name + role
        +'<div style="position:absolute;top:60%;left:4%;right:4%;text-align:center;">'
        +'<div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:'+v.pri+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+v.nameVal+'</div>'
        +'<div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#888;margin-top:1%;;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.roleVal+'</div>'
        +'<div style="font-size:clamp(0.34rem,0.82vw,0.5rem);color:'+v.pri+';font-weight:700;margin-top:2%;letter-spacing:0.04em;text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+v.orgVal+'</div></div>'
        +'<div style="position:absolute;top:68%;left:8%;right:8%;height:1.5px;background:'+v.pri+';opacity:0.25;"></div>'
        +'<div style="position:absolute;top:70%;left:6%;right:6%;">'+fieldRowsHTML(v.fieldItems,v.pri,'#444','clamp(0.38rem,0.9vw,0.54rem)')+'</div>'
        +qrSlotHTML('bottom:2%;right:3%;')
        +'</div>';
}

/* ================================================================
   updateBulkPreview — render the current style into the center card
================================================================= */
function updateBulkPreview() {
    var v = getBulkCardValues();
    var el = document.getElementById('bulkStylePreview');
    if (!el) return;
    var s = ALL_STYLES.find(function(x){ return x.key === currentStyleKey; });
    var isPrt = s ? s.portrait : false;
    if (isPrt) {
        el.classList.add('portrait');
        el.style.aspectRatio = '54/85.6';
        el.style.maxWidth = '250px';
    } else {
        el.classList.remove('portrait');
        el.style.aspectRatio = '85.6/54';
        el.style.maxWidth = '440px';
    }
    var html = '';
    switch (currentStyleKey) {
        case 'sidebar':      html = renderSidebar(v);     break;
        case 'wave':         html = renderWave(v);        break;
        case 'bold_header':  html = renderBoldHeader(v);  break;
        case 'diagonal':     html = renderDiagonal(v);    break;
        case 'gradient_pro': html = renderGradientPro(v); break;
        case 'neon':         html = renderNeon(v);        break;
        case 'executive':    html = renderExecutive(v);   break;
        case 'stripe':       html = renderStripe(v);      break;
        case 'metro':        html = renderMetro(v);       break;
        case 'glass':        html = renderGlass(v);       break;
        case 'zigzag':       html = renderZigzag(v);      break;
        case 'ribbon':       html = renderRibbon(v);      break;
        case 'v_sharp':      html = renderVSharp(v);      break;
        case 'v_curve':      html = renderVCurve(v);      break;
        case 'v_hex':        html = renderVHex(v);        break;
        case 'v_circle':     html = renderVCircle(v);     break;
        case 'v_split':      html = renderVSplit(v);      break;
        case 'v_ribbon':     html = renderVRibbon(v);     break;
        case 'v_arch':       html = renderVArch(v);       break;
        case 'v_diamond':    html = renderVDiamond(v);    break;
        case 'v_corner':     html = renderVCorner(v);     break;
        case 'v_dual':       html = renderVDual(v);       break;
        case 'v_stripe':     html = renderVStripe(v);     break;
        case 'v_badge':      html = renderVBadge(v);      break;
        default:             html = renderClassic(v);     break;
    }
    el.innerHTML = html;
    el.style.fontFamily = "'"+v.font+"',sans-serif";
}

/* ================================================================
   buildStyleThumbnail — SVG thumbnail for filmstrip thumbs
================================================================= */
function buildStyleThumbnail(key, pri, acc, portrait) {
    var W = 85.6, H = 54;
    if (portrait) { W = 54; H = 85.6; }
    var vb = '0 0 ' + W + ' ' + H;
    var uid = key + (portrait?'p':'l');
    switch(key) {
        case 'classic':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;display:block;">'
                +'<rect width="'+W+'" height="'+H+'" fill="#f7f8fc"/>'
                +'<rect width="'+W+'" height="'+H+'" fill="'+pri+'" clip-path="url(#cc'+uid+')"/>'
                +'<defs><clipPath id="cc'+uid+'"><polygon points="0,0 '+W+',0 '+W+','+(H*0.56)+' 0,'+(H*0.72)+'"/></clipPath></defs>'
                +'<circle cx="'+(W/2)+'" cy="'+(H*0.46)+'" r="7" fill="#fff" stroke="'+pri+'" stroke-width="1.5"/>'
                +'<rect x="'+((W-26)/2)+'" y="'+(H*0.58)+'" width="26" height="3.5" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'</svg>';
        case 'neon':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;display:block;">'
                +'<rect width="'+W+'" height="'+H+'" fill="#050a10"/>'
                +'<rect x="0" y="0" width="'+W+'" height="3" fill="'+pri+'"/>'
                +'<rect x="0" y="'+(H-3)+'" width="'+W+'" height="3" fill="'+acc+'"/>'
                +'<circle cx="'+(W*0.22)+'" cy="'+(H*0.5)+'" r="9" fill="none" stroke="'+acc+'" stroke-width="1.5"/>'
                +'<rect x="'+(W*0.38)+'" y="'+(H*0.35)+'" width="'+(W*0.38)+'" height="3" rx="1.5" fill="#fff" opacity="0.7"/>'
                +'</svg>';
        case 'executive':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;display:block;">'
                +'<rect width="'+W+'" height="'+H+'" fill="#1a1f2e"/>'
                +'<rect x="0" y="0" width="'+W+'" height="4" fill="#c9a84c"/>'
                +'<rect x="0" y="'+(H-4)+'" width="'+W+'" height="4" fill="#c9a84c"/>'
                +'<circle cx="'+(W*0.22)+'" cy="'+(H*0.5)+'" r="9" fill="none" stroke="#c9a84c" stroke-width="1.2"/>'
                +'</svg>';
        case 'glass': case 'gradient_pro':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;display:block;">'
                +'<defs><linearGradient id="g'+uid+'" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="'+pri+'"/><stop offset="100%" stop-color="'+acc+'"/></linearGradient></defs>'
                +'<rect width="'+W+'" height="'+H+'" fill="url(#g'+uid+')"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.1)+'" width="'+(W*0.84)+'" height="'+(H*0.8)+'" rx="4" fill="rgba(255,255,255,0.18)" stroke="rgba(255,255,255,0.3)" stroke-width="0.7"/>'
                +'</svg>';
        case 'sidebar': case 'ribbon': case 'diagonal':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;display:block;">'
                +'<rect width="'+W+'" height="'+H+'" fill="#111827"/>'
                +'<rect x="'+(W*0.42)+'" y="'+(-H*0.2)+'" width="'+(W*0.75)+'" height="'+(W*0.75)+'" rx="3" fill="'+pri+'" transform="rotate(45 '+(W*0.82)+' '+(H*0.3)+')" opacity="0.9"/>'
                +'<rect x="5" y="'+(H*0.56)+'" width="28" height="3.5" rx="1.5" fill="#fff" opacity="0.9"/>'
                +'</svg>';
        case 'stripe':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;display:block;">'
                +'<rect width="'+W+'" height="'+H+'" fill="#f5f7fa"/>'
                +'<rect x="0" y="0" width="'+W+'" height="'+(H*0.18)+'" fill="'+pri+'"/>'
                +'<rect x="0" y="'+(H*0.82)+'" width="'+W+'" height="'+(H*0.18)+'" fill="'+acc+'"/>'
                +'</svg>';
        case 'metro': case 'bold_header':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;display:block;">'
                +'<rect width="'+W+'" height="'+H+'" fill="#fff"/>'
                +'<rect x="0" y="0" width="'+(W*0.36)+'" height="'+H+'" fill="'+pri+'"/>'
                +'<rect x="0" y="0" width="'+W+'" height="4" fill="'+pri+'"/>'
                +'</svg>';
        case 'wave': case 'zigzag':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;display:block;">'
                +'<rect width="'+W+'" height="'+H+'" fill="#fdf8f3"/>'
                +'<path d="M0,0 L'+(W*0.55)+',0 Q'+(W*0.73)+','+(H*0.28)+' '+(W*0.65)+','+(H*0.5)+' Q'+(W*0.75)+','+(H*0.72)+' '+(W*0.58)+','+H+' L0,'+H+' Z" fill="'+pri+'"/>'
                +'</svg>';
        default:
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;display:block;">'
                +'<rect width="'+W+'" height="'+H+'" fill="#f7f8fc"/>'
                +'<rect width="'+W+'" height="'+(H*0.48)+'" fill="'+pri+'"/>'
                +'<circle cx="'+(W*0.5)+'" cy="'+(H*0.44)+'" r="'+(Math.min(W,H)*0.12)+'" fill="#fff" stroke="'+pri+'" stroke-width="1.5"/>'
                +'<rect x="'+(W*0.2)+'" y="'+(H*0.6)+'" width="'+(W*0.6)+'" height="3" rx="1.5" fill="'+pri+'" opacity="0.75"/>'
                +'</svg>';
    }
}

/* ================================================================
   buildFilmstrip — render prev/center/next
================================================================= */
function buildFilmstrip() {
    var styles = getFilteredStyles();
    var idx = styles.findIndex(function(s){ return s.key === currentStyleKey; });
    if (idx === -1) { idx = 0; currentStyleKey = styles[0] ? styles[0].key : 'classic'; }

    var pri = document.getElementById('d_primary').value || '#1e40af';
    var acc = document.getElementById('d_accent').value  || '#3b82f6';

    var prevStyle = idx > 0 ? styles[idx - 1] : null;
    var nextStyle = idx < styles.length - 1 ? styles[idx + 1] : null;

    var prevWrap = document.getElementById('filmThumbPrev');
    var prevCard = document.getElementById('filmThumbPrevCard');
    var prevLbl  = document.getElementById('filmThumbPrevLabel');
    if (prevStyle) {
        prevCard.innerHTML = buildStyleThumbnail(prevStyle.key, pri, acc, prevStyle.portrait);
        prevCard.className = 'filmstrip-thumb-card' + (prevStyle.portrait ? ' portrait' : '');
        if (prevLbl) prevLbl.textContent = prevStyle.label;
        prevWrap.style.display = '';
    } else {
        prevWrap.style.display = 'none';
    }

    var nextWrap = document.getElementById('filmThumbNext');
    var nextCard = document.getElementById('filmThumbNextCard');
    var nextLbl  = document.getElementById('filmThumbNextLabel');
    if (nextStyle) {
        nextCard.innerHTML = buildStyleThumbnail(nextStyle.key, pri, acc, nextStyle.portrait);
        nextCard.className = 'filmstrip-thumb-card' + (nextStyle.portrait ? ' portrait' : '');
        if (nextLbl) nextLbl.textContent = nextStyle.label;
        nextWrap.style.display = '';
    } else {
        nextWrap.style.display = 'none';
    }

    var prevBtn = document.getElementById('filmPrev');
    var nextBtn = document.getElementById('filmNext');
    if (prevBtn) prevBtn.disabled = !prevStyle;
    if (nextBtn) nextBtn.disabled = !nextStyle;

    updateBulkPreview();

    var s = ALL_STYLES.find(function(x){ return x.key === currentStyleKey; });
    var lbl = document.getElementById('selectedStyleName');
    if (lbl) lbl.textContent = s ? s.label : currentStyleKey;
    document.getElementById('d_style').value = currentStyleKey;
    var fStyle = document.getElementById('f_style');
    if (fStyle) fStyle.value = currentStyleKey;
}

function filmstripNavigate(dir) {
    var styles = getFilteredStyles();
    var idx = styles.findIndex(function(s){ return s.key === currentStyleKey; });
    var newIdx = idx + dir;
    if (newIdx >= 0 && newIdx < styles.length) {
        currentStyleKey = styles[newIdx].key;
        buildFilmstrip();
    }
}

/* ================================================================
   See All modal
================================================================= */
function openSeeAll()  { buildSeeAllGrid(); document.getElementById('seeAllModal').classList.add('open'); }
function closeSeeAll() { document.getElementById('seeAllModal').classList.remove('open'); }

var modalFilter = 'all';
function setModalFilter(f) {
    modalFilter = f;
    ['all','landscape','portrait'].forEach(function(id) {
        var btn = document.getElementById('ma' + id.charAt(0).toUpperCase() + id.slice(1));
        if (btn) btn.classList.toggle('active', id === f);
    });
    buildSeeAllGrid();
}

function buildSeeAllGrid() {
    var styles;
    if (modalFilter === 'landscape')     styles = ALL_STYLES.filter(function(s){ return !s.portrait; });
    else if (modalFilter === 'portrait') styles = ALL_STYLES.filter(function(s){ return s.portrait; });
    else                                 styles = ALL_STYLES;
    var pri  = document.getElementById('d_primary').value || '#1e40af';
    var acc  = document.getElementById('d_accent').value  || '#3b82f6';
    var grid = document.getElementById('seeAllGrid');
    grid.innerHTML = styles.map(function(s) {
        var isActive = s.key === currentStyleKey;
        var cardCls  = 'style-card' + (s.portrait ? ' portrait' : '') + (isActive ? ' active' : '');
        return '<div style="text-align:center;">'
            + '<div class="'+cardCls+'" onclick="pickStyle(\''+s.key+'\');closeSeeAll();">'
            + buildStyleThumbnail(s.key, pri, acc, s.portrait) + '</div>'
            + '<div class="style-label'+(isActive?' active':'')+'">'+s.label+'</div>'
            + '</div>';
    }).join('');
}

document.addEventListener('click', function(e) {
    var m = document.getElementById('seeAllModal');
    if (m && e.target === m) closeSeeAll();
});

/* ================================================================
   pickStyle
================================================================= */
function pickStyle(key) {
    currentStyleKey = key;
    document.getElementById('d_style').value = key;
    var fStyle = document.getElementById('f_style');
    if (fStyle) fStyle.value = key;
    buildFilmstrip();
}

/* ================================================================
   Template selection
================================================================= */
function selectTemplateByKey(key) {
    if (!key) return;
    selectedTemplate = key;
    document.getElementById('bulkTemplateKey').value = key;
    var dd = document.getElementById('tplDropdown');
    if (dd) dd.value = key;
    document.querySelectorAll('.tpl-theme-dot').forEach(function(d) {
        d.classList.toggle('active', d.dataset.tpl === key);
    });
    var opt = document.querySelector('#tplDropdown option[value="'+key+'"]');
    document.getElementById('selectedTplName').textContent = opt ? opt.textContent : key;
    var btn = document.getElementById('sampleCsvBtn');
    btn.href = '/projects/idcard/bulk/sample-csv?template=' + encodeURIComponent(key);
    btn.style.pointerEvents = 'auto'; btn.style.opacity = '1';
    var p = TPL_COLORS[key];
    if (p) {
        document.getElementById('d_primary').value = p.color;
        document.getElementById('d_accent').value  = p.accent;
        document.getElementById('d_bg').value      = p.bg;
        document.getElementById('d_text').value    = p.text;
    }
    buildFilmstrip();
    updateSubmitState();
}

/* ================================================================
   File handling
================================================================= */
function handleFileSelect(input) {
    if (input.files && input.files[0]) {
        document.getElementById('uploadFilename').textContent = '📎 ' + input.files[0].name;
        fileSelected = true; updateSubmitState();
    }
}
function handleDragOver(e)  { e.preventDefault(); document.getElementById('uploadZone').classList.add('dragover'); }
function handleDragLeave(e) { document.getElementById('uploadZone').classList.remove('dragover'); }
function handleDrop(e) {
    e.preventDefault(); document.getElementById('uploadZone').classList.remove('dragover');
    if (e.dataTransfer.files.length) {
        var file = e.dataTransfer.files[0];
        document.getElementById('uploadFilename').textContent = '📎 ' + file.name;
        fileSelected = true; window._droppedFile = file; updateSubmitState();
    }
}

/* ================================================================
   Submit state
================================================================= */
function updateSubmitState() {
    var ready = selectedTemplate && fileSelected;
    document.getElementById('submitBtn').disabled = !ready;
    document.getElementById('readinessHint').textContent = ready
        ? 'Ready — click Generate!'
        : (!selectedTemplate ? 'Select a category first.' : 'Upload a CSV file.');
}

/* ================================================================
   Reset
================================================================= */
function resetAll() {
    selectedTemplate = ''; fileSelected = false; window._droppedFile = null;
    document.getElementById('csvFileInput').value = '';
    document.getElementById('uploadFilename').textContent = '';
    document.getElementById('bulkTemplateKey').value = '';
    document.getElementById('selectedTplName').textContent = '—';
    document.getElementById('progressWrap').style.display = 'none';
    document.getElementById('bulkResultsWrap').style.display = 'none';
    var btn = document.getElementById('sampleCsvBtn');
    btn.href = '#'; btn.style.pointerEvents = 'none'; btn.style.opacity = '0.4';
    var dd = document.getElementById('tplDropdown'); if (dd) dd.value = '';
    document.querySelectorAll('.tpl-theme-dot').forEach(function(d){ d.classList.remove('active'); });
    currentStyleKey = 'classic';
    buildFilmstrip(); updateSubmitState();
}

/* ================================================================
   Submit
================================================================= */
function submitBulk(e) {
    e.preventDefault();
    if (!selectedTemplate || !fileSelected) return;
    document.getElementById('f_primary').value = document.getElementById('d_primary').value;
    document.getElementById('f_accent').value  = document.getElementById('d_accent').value;
    document.getElementById('f_bg').value      = document.getElementById('d_bg').value;
    document.getElementById('f_text').value    = document.getElementById('d_text').value;
    document.getElementById('f_font').value    = document.getElementById('d_font').value;
    document.getElementById('f_shape').value   = document.getElementById('d_shape').value;
    document.getElementById('f_style').value   = document.getElementById('d_style').value;
    var submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
    var pw = document.getElementById('progressWrap');
    pw.style.display = 'block';
    document.getElementById('progressBar').style.width = '25%';
    document.getElementById('progressLabel').textContent = 'Uploading CSV and creating cards...';
    var fd = new FormData(document.getElementById('bulkForm'));
    var csvInput = document.getElementById('csvFileInput');
    if (window._droppedFile) {
        fd.append('csv_file', window._droppedFile, window._droppedFile.name);
    } else if (csvInput.files && csvInput.files[0]) {
        fd.append('csv_file', csvInput.files[0]);
    }
    fetch('/projects/idcard/bulk/upload', {
        method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}
    })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        document.getElementById('progressBar').style.width = '100%';
        document.getElementById('progressLabel').textContent = data.message || (data.success ? 'Done!' : 'Error');
        document.getElementById('bulkResultsWrap').style.display = 'block';
        if (data.success) {
            document.getElementById('bulkResultInner').innerHTML =
                '<div style="padding:14px;background:rgba(0,255,136,0.06);border:1px solid rgba(0,255,136,0.2);border-radius:10px;">'
                +'<div style="font-weight:700;color:#00ff88;margin-bottom:6px;font-size:0.95rem;"><i class="fas fa-check-circle"></i> Generation Complete!</div>'
                +'<div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:6px;">'
                +'<span class="result-badge badge-success"><i class="fas fa-id-card"></i> '+data.completed+' cards created</span>'
                +(data.failed>0?'<span class="result-badge badge-fail"><i class="fas fa-exclamation-triangle"></i> '+data.failed+' rows skipped</span>':'')
                +'</div>'
                +'<div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">'
                +'<a href="/projects/idcard/bulk/cards" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> View Bulk Cards</a>'
                +'<a href="/projects/idcard/history" class="btn btn-secondary btn-sm"><i class="fas fa-list"></i> All My Cards</a>'
                +'</div></div>';
            setTimeout(function(){ window.location.reload(); }, 3500);
        } else {
            document.getElementById('bulkResultInner').innerHTML =
                '<div style="padding:12px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.2);border-radius:10px;color:#ef4444;">'
                +'<i class="fas fa-times-circle"></i> '+(data.message||'An error occurred.')+'</div>';
        }
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-bolt"></i> Generate All Cards';
    })
    .catch(function() {
        document.getElementById('progressLabel').textContent = 'Request failed.';
        document.getElementById('bulkResultsWrap').style.display = 'block';
        document.getElementById('bulkResultInner').innerHTML =
            '<div style="padding:12px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.2);border-radius:10px;color:#ef4444;">'
            +'<i class="fas fa-times-circle"></i> Network error. Please try again.</div>';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-bolt"></i> Generate All Cards';
    });
}

// Init
buildFilmstrip();

</script>
