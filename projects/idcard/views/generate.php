<?php
/**
 * @var array  $templates
 * @var string $selectedTpl
 * @var array  $tplConfig
 * @var array  $field_labels
 * @var array  $user
 * @var int|null    $editCardId   (optional, present when editing)
 * @var array|null  $editCardData (optional, pre-fill field values)
 * @var array|null  $editDesign   (optional, pre-fill design settings)
 */
$csrfToken    = \Core\Security::generateCsrfToken();
$isEditMode   = isset($editCardId) && $editCardId > 0;
$editCardData = $editCardData ?? [];
$editDesign   = $editDesign   ?? [];
?>

<style>
/* ── Generate page ── */
.gen-wrap { display:grid; grid-template-columns:1fr 400px; gap:20px; align-items:start; }
@media(max-width:960px){ .gen-wrap{ grid-template-columns:1fr; } }

/* Template picker */
.tpl-picker { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
.tpl-btn {
    padding:7px 14px; border-radius:20px; font-size:0.78rem; font-weight:600;
    border:1.5px solid transparent; cursor:pointer; transition:all 0.2s;
    background:var(--bg-secondary); color:var(--text-secondary);
}
.tpl-btn.active { color:#fff; border-color:transparent; }

/* Design style picker */
.style-picker { display:grid; grid-template-columns:repeat(5,1fr); gap:8px; }
@media(max-width:680px){ .style-picker{ grid-template-columns:repeat(3,1fr); } }
.style-card {
    border:2px solid var(--border-color); border-radius:10px; cursor:pointer;
    overflow:hidden; transition:all 0.2s; aspect-ratio:85.6/54; position:relative;
}
.style-card.portrait { aspect-ratio:54/85.6; }
.style-card:hover { border-color:var(--indigo); transform:translateY(-2px); }
.style-card.active { border-color:var(--indigo); box-shadow:0 0 0 2px rgba(99,102,241,0.3); }
.style-label { font-size:0.62rem; font-weight:600; text-align:center; margin-top:5px; color:var(--text-secondary); }
.style-label.active { color:var(--indigo); }

/* Live preview — sticky sidebar */
.preview-area {
    position:sticky;
    top:72px;
    height:calc(100vh - 88px);
    overflow-y:auto;
    scrollbar-width:thin;
}
@media(max-width:960px){ .preview-area{ position:static; height:auto; } }

.id-card-preview {
    width:100%; max-width:360px; margin:0 auto;
    border-radius:14px; overflow:hidden;
    box-shadow:0 20px 60px rgba(0,0,0,0.4);
    font-family:'Poppins',sans-serif;
    transition:all 0.3s ease;
    aspect-ratio:85.6/54;
    position:relative;
}
.id-card-preview.portrait {
    aspect-ratio:54/85.6;
    max-width:200px;
}

/* Compact controls row (desktop) */
.compact-controls {
    display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;
}
.compact-controls .ctrl-group {
    flex:1; min-width:60px;
}
.compact-controls .ctrl-color {
    flex:0 0 52px;
}
.compact-controls label { font-size:0.68rem; display:block; margin-bottom:3px; color:var(--text-secondary); font-weight:600; }
.compact-controls input[type=color] { width:100%; height:34px; padding:2px 4px; cursor:pointer; border-radius:6px; border:1px solid var(--border-color); }
.compact-controls select.form-input { padding:6px 8px; font-size:0.78rem; }
.compact-controls .qr-toggle { display:flex; align-items:center; gap:5px; font-size:0.75rem; cursor:pointer; white-space:nowrap; padding-bottom:6px; }

/* Photo & Logo compact */
.photo-logo-row { display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap; }
.photo-logo-row .photo-ctrl { flex:1; min-width:120px; }
.photo-logo-row label { font-size:0.68rem; display:block; margin-bottom:3px; color:var(--text-secondary); font-weight:600; }
.photo-logo-row input[type=file] { font-size:0.75rem; padding:4px 6px; }

/* Category dropdown smaller */
#categorySelect { max-width:260px; }

/* AI panel */
.spinner { display:inline-block; width:14px; height:14px; border:2px solid rgba(99,102,241,0.3); border-top-color:var(--indigo); border-radius:50%; animation:spin 0.7s linear infinite; }
@keyframes spin { to{ transform:rotate(360deg); } }
.ai-suggestion { font-size:0.78rem; color:var(--text-secondary); line-height:1.6; padding:6px 10px; background:var(--bg-secondary); border-radius:8px; margin-bottom:6px; }
.ai-suggestion strong { color:var(--text-primary); }

/* Filter buttons */
.filter-btn {
    padding:4px 12px; border-radius:16px; font-size:0.72rem; font-weight:600;
    border:1.5px solid var(--border-color); cursor:pointer;
    background:var(--bg-secondary); color:var(--text-secondary); transition:all 0.2s;
}
.filter-btn.active { background:var(--indigo); color:#fff; border-color:var(--indigo); }

/* Collapsible */
.collapsible-hidden { display:none !important; }

/* ── Mobile floating preview button ── */
.mobile-preview-btn {
    display:none;
    position:fixed; bottom:20px; right:20px; z-index:1000;
    background:var(--indigo); color:#fff;
    border:none; border-radius:50px; padding:12px 20px;
    font-size:0.85rem; font-weight:700; cursor:pointer;
    box-shadow:0 4px 20px rgba(99,102,241,0.5);
    align-items:center; gap:8px;
}
@media(max-width:960px){
    .mobile-preview-btn { display:flex; }
    #categorySelect { max-width:100%; }
    .compact-controls .ctrl-color { flex:0 0 44px; }
}

/* ── Mobile preview modal ── */
.preview-modal-overlay {
    display:none; position:fixed; inset:0; z-index:2000;
    background:rgba(0,0,0,0.75); align-items:center; justify-content:center;
    padding:16px;
}
.preview-modal-overlay.open { display:flex; }
.preview-modal-box {
    background:var(--bg-card,#fff); border-radius:16px;
    padding:18px; max-width:420px; width:100%;
    max-height:90vh; overflow-y:auto; position:relative;
}
.preview-modal-close {
    position:absolute; top:10px; right:12px;
    background:none; border:none; font-size:1.3rem; cursor:pointer; color:var(--text-secondary);
}

/* ── Template theme colour dots ── */
.tpl-theme-dots { display:flex; flex-wrap:wrap; gap:7px; }
.tpl-theme-dot {
    width:22px; height:22px; border-radius:50%; cursor:pointer;
    border:2.5px solid transparent; transition:transform 0.15s, border-color 0.15s;
    flex-shrink:0; position:relative;
}
.tpl-theme-dot:hover { transform:scale(1.18); }
.tpl-theme-dot.active { border-color:#fff; box-shadow:0 0 0 2px var(--indigo); }
.tpl-theme-dot[title]:hover::after {
    content:attr(title); position:absolute; bottom:120%; left:50%; transform:translateX(-50%);
    background:#111; color:#fff; font-size:0.6rem; padding:2px 6px; border-radius:4px;
    white-space:nowrap; pointer-events:none; z-index:100;
}

/* ── Mobile nav bar height (used to offset sidebar toggle) ── */
:root { --mobile-nav-height: 68px; }

/* ── Sidebar toggle — lift above mobile nav bar ── */
@media(max-width:600px) {
    .sidebar-toggle { bottom: calc(var(--mobile-nav-height) + 8px) !important; z-index: 210 !important; }
}

/* ── Mobile bottom nav bar ── */
.cx-mobile-nav-bar {
    display:none; position:fixed; bottom:0; left:0; right:0; z-index:200;
    background:var(--bg-card); border-top:1px solid var(--border-color);
    overflow-x:auto; -webkit-overflow-scrolling:touch;
}
.cx-mobile-nav-bar::-webkit-scrollbar { display:none; }
.cx-mobile-nav-inner {
    display:flex; min-width:max-content; padding:4px 8px; gap:2px;
    min-height:var(--mobile-nav-height);
}
.cx-mobile-nav-btn {
    display:flex; flex-direction:column; align-items:center; gap:2px;
    padding:6px 12px; border-radius:8px; border:none;
    background:transparent; color:var(--text-secondary);
    font-size:0.62rem; font-weight:600; font-family:'Poppins',sans-serif;
    cursor:pointer; white-space:nowrap; min-width:52px; transition:all 0.15s;
}
.cx-mobile-nav-btn.active { color:var(--indigo); background:rgba(99,102,241,0.1); }
.cx-mobile-nav-btn:hover { color:var(--indigo); }
@media(max-width:600px) {
    .cx-mobile-nav-bar { display:flex !important; }
    .cx-main { padding-bottom:72px !important; }
    .mobile-preview-btn { display:none !important; }
    /* Hide the right-column preview area elements on mobile since they are
       duplicated in the mobile-only form sections */
    .preview-area .card:not(:first-child) { display:none !important; }
    /* Hide the step-progress banner on mobile — the bottom nav already
       provides category/section navigation */
    #stepProgressBanner { display:none !important; }
}

/* ── Inline bulk panel ── */
.inline-bulk-panel { display:none; }
.inline-bulk-panel.visible { display:block; }
.inline-bulk-toggle { display:flex; align-items:center; gap:8px; cursor:pointer; user-select:none; width:100%; }
.inline-bulk-upload-zone {
    border:2px dashed var(--border-color); border-radius:10px;
    padding:14px 12px; text-align:center; cursor:pointer; transition:all 0.2s;
    background:var(--bg-secondary); margin-top:10px;
}
.inline-bulk-upload-zone:hover, .inline-bulk-upload-zone.dragover {
    border-color:var(--indigo); background:rgba(99,102,241,0.06);
}
.inline-bulk-upload-zone input[type=file] { display:none; }
.inline-bulk-progress-wrap { background:var(--border-color); border-radius:99px; height:7px; overflow:hidden; margin-top:6px; }
.inline-bulk-progress-fill { height:100%; border-radius:99px; background:linear-gradient(90deg,#6366f1,#00f0ff); transition:width 0.4s; }
</style>

<div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;flex-wrap:wrap;">
    <a href="/projects/idcard" class="back-link" style="margin-bottom:0;"><i class="fas fa-arrow-left"></i> Dashboard</a>
</div>

<?php if (!$isEditMode): ?>
<!-- Step progress indicator (create mode only, hidden on mobile) -->
<div id="stepProgressBanner" style="background:linear-gradient(135deg,rgba(99,102,241,0.12),rgba(0,240,255,0.05));border:1px solid rgba(99,102,241,0.2);border-radius:14px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
    <div style="width:44px;height:44px;background:linear-gradient(135deg,#6366f1,#00f0ff);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="fas fa-id-card" style="color:#fff;font-size:1.2rem;"></i>
    </div>
    <div style="flex:1;min-width:160px;">
        <div style="font-size:1.1rem;font-weight:800;background:linear-gradient(135deg,#6366f1,#00f0ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:2px;">
            Create ID Card
        </div>
        <div style="font-size:0.78rem;color:var(--text-secondary);">
            Select a category &rarr; fill details &rarr; pick a style &rarr; generate
        </div>
    </div>
    <div style="display:flex;gap:6px;align-items:center;flex-shrink:0;">
        <span style="width:24px;height:24px;border-radius:50%;background:var(--indigo);color:#fff;font-size:0.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;">1</span>
        <span style="font-size:0.68rem;color:var(--indigo);font-weight:600;">Category</span>
        <i class="fas fa-chevron-right" style="color:var(--text-secondary);font-size:0.6rem;margin:0 2px;"></i>
        <span style="width:24px;height:24px;border-radius:50%;background:var(--bg-secondary);border:1.5px solid var(--border-color);color:var(--text-secondary);font-size:0.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;">2</span>
        <span style="font-size:0.68rem;color:var(--text-secondary);font-weight:600;">Details</span>
        <i class="fas fa-chevron-right" style="color:var(--text-secondary);font-size:0.6rem;margin:0 2px;"></i>
        <span style="width:24px;height:24px;border-radius:50%;background:var(--bg-secondary);border:1.5px solid var(--border-color);color:var(--text-secondary);font-size:0.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;">3</span>
        <span style="font-size:0.68rem;color:var(--text-secondary);font-weight:600;">Style</span>
        <i class="fas fa-chevron-right" style="color:var(--text-secondary);font-size:0.6rem;margin:0 2px;"></i>
        <span style="width:24px;height:24px;border-radius:50%;background:var(--bg-secondary);border:1.5px solid var(--border-color);color:var(--text-secondary);font-size:0.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;">4</span>
        <span style="font-size:0.68rem;color:var(--text-secondary);font-weight:600;">Generate</span>
    </div>
</div>
<?php else: ?>
<h2 class="section-title" style="margin-bottom:18px;">
    <i class="fas fa-edit" style="color:var(--indigo);"></i> Edit ID Card
    <span style="font-size:0.72rem;font-weight:400;color:var(--text-secondary);margin-left:8px;font-style:italic;">Editing saved card</span>
</h2>
<?php endif; ?>

<div class="gen-wrap">
    <!-- LEFT: Form -->
    <div>
        <form id="cardForm" method="POST" action="/projects/idcard/generate" enctype="multipart/form-data">
            <input type="hidden" name="_token"       value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="template_key" id="template_key" value="<?= htmlspecialchars($selectedTpl) ?>">
            <input type="hidden" name="design_style" id="design_style" value="<?= htmlspecialchars($editDesign['design_style'] ?? 'classic') ?>">
            <?php if ($isEditMode): ?>
            <input type="hidden" name="edit_card_id" value="<?= (int)$editCardId ?>">
            <?php endif; ?>

            <!-- Mobile-only: Category selector (desktop shows it in right sidebar) -->
            <div class="card mobile-only-section" id="mobileCategorySection" style="margin-bottom:16px;display:none;">
                <h3 style="font-size:0.9rem;font-weight:600;color:var(--indigo);margin:0 0 12px;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-tags"></i> Select Card Category
                </h3>
                <select class="form-input" style="padding:8px 10px;font-size:0.85rem;cursor:pointer;"
                        onchange="selectTemplate(this.value);syncCategorySelects(this.value);" id="mobileCategorySelect">
                    <option value="corporate"   <?= $selectedTpl === 'corporate'   ? 'selected' : '' ?>>Corporate</option>
                    <option value="student"     <?= $selectedTpl === 'student'     ? 'selected' : '' ?>>Student / School</option>
                    <option value="event"       <?= $selectedTpl === 'event'       ? 'selected' : '' ?>>Event</option>
                    <option value="visitor"     <?= $selectedTpl === 'visitor'     ? 'selected' : '' ?>>Visitor</option>
                    <option value="medical"     <?= $selectedTpl === 'medical'     ? 'selected' : '' ?>>Medical Staff</option>
                    <option value="tech"        <?= $selectedTpl === 'tech'        ? 'selected' : '' ?>>Tech Company</option>
                    <option value="bank"        <?= $selectedTpl === 'bank'        ? 'selected' : '' ?>>Banking / Finance</option>
                    <option value="media"       <?= $selectedTpl === 'media'       ? 'selected' : '' ?>>Press / Media</option>
                    <option value="govt"        <?= $selectedTpl === 'govt'        ? 'selected' : '' ?>>Government</option>
                    <option value="security"    <?= $selectedTpl === 'security'    ? 'selected' : '' ?>>Security</option>
                    <option value="hospital_v"  <?= $selectedTpl === 'hospital_v'  ? 'selected' : '' ?>>Hospital</option>
                    <option value="ngo_v"       <?= $selectedTpl === 'ngo_v'       ? 'selected' : '' ?>>NGO / Non-Profit</option>
                    <option value="library_v"   <?= $selectedTpl === 'library_v'   ? 'selected' : '' ?>>Library Card</option>
                    <option value="gym_v"       <?= $selectedTpl === 'gym_v'       ? 'selected' : '' ?>>Gym / Fitness</option>
                    <option value="transport_v" <?= $selectedTpl === 'transport_v' ? 'selected' : '' ?>>Transport</option>
                    <option value="university_v" <?= $selectedTpl === 'university_v' ? 'selected' : '' ?>>University Faculty</option>
                    <option value="security_v"  <?= $selectedTpl === 'security_v'  ? 'selected' : '' ?>>Security Guard</option>
                    <option value="retail_v"    <?= $selectedTpl === 'retail_v'    ? 'selected' : '' ?>>Retail / Shop</option>
                </select>
            </div>

            <!-- Dynamic fields -->
            <div class="card" style="margin-bottom:16px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                    <h3 style="font-size:0.9rem;font-weight:600;color:var(--indigo);display:flex;align-items:center;gap:6px;margin:0;">
                        <i class="fas fa-user"></i> Card Information
                    </h3>
                    <button type="button" onclick="toggleSection('dynamicFields')" class="btn btn-secondary btn-sm" style="padding:3px 10px;font-size:0.72rem;">
                        <i class="fas fa-chevron-up" id="infoChevron"></i>
                    </button>
                </div>
                <div id="dynamicFields">
                    <div class="grid grid-2" style="gap:8px 12px;">
                    <?php foreach ($tplConfig['fields'] as $field): ?>
                        <?php if ($field === 'photo'): continue; endif; ?>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:0.72rem;" for="field_<?= $field ?>">
                                <?= htmlspecialchars($field_labels[$field] ?? ucfirst(str_replace('_',' ',$field))) ?>
                            </label>
                            <input type="text" id="field_<?= $field ?>" name="<?= htmlspecialchars($field) ?>"
                                   class="form-input" style="padding:6px 10px;font-size:0.82rem;"
                                   placeholder="<?= strtolower(htmlspecialchars($field_labels[$field] ?? $field)) ?>"
                                   value="<?= htmlspecialchars($editCardData[$field] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                   oninput="updatePreview()">
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Photo & Logo + Colours & Font + QR — compact single row -->
            <div class="card" style="margin-bottom:16px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                    <h3 style="font-size:0.9rem;font-weight:600;color:var(--indigo);display:flex;align-items:center;gap:6px;margin:0;">
                        <i class="fas fa-sliders-h"></i> Design Controls
                    </h3>
                    <button type="button" onclick="toggleSection('designControls')" class="btn btn-secondary btn-sm" style="padding:3px 10px;font-size:0.72rem;">
                        <i class="fas fa-chevron-up" id="controlsChevron"></i>
                    </button>
                </div>
                <div id="designControls">
                    <!-- Photo & Logo row -->
                    <div class="photo-logo-row" style="margin-bottom:10px;">
                        <div class="photo-ctrl form-group" style="margin-bottom:0;">
                            <label>📷 Profile Photo</label>
                            <input type="file" name="photo" id="photoInput" class="form-input" accept="image/*"
                                   style="padding:4px 6px;font-size:0.75rem;" onchange="previewPhoto(this)">
                        </div>
                        <div class="photo-ctrl form-group" style="margin-bottom:0;" id="logoWrap">
                            <label>🏢 Organisation Logo</label>
                            <input type="file" name="logo" id="logoInput" class="form-input" accept="image/*"
                                   style="padding:4px 6px;font-size:0.75rem;">
                        </div>
                        <div class="photo-ctrl form-group" style="margin-bottom:0;">
                            <label>🔵 Photo Shape</label>
                            <select name="profile_shape" id="profileShape" class="form-input" style="padding:6px 8px;font-size:0.78rem;" onchange="updatePreview()">
                                <?php $editShape = $editDesign['profile_shape'] ?? 'circle'; ?>
                                <option value="circle"  <?= $editShape === 'circle'  ? 'selected' : '' ?>>Circle</option>
                                <option value="oval"    <?= $editShape === 'oval'    ? 'selected' : '' ?>>Oval</option>
                                <option value="square"  <?= $editShape === 'square'  ? 'selected' : '' ?>>Square</option>
                            </select>
                        </div>
                    </div>
                    <!-- Colours + Font + QR single row -->
                    <div class="compact-controls">
                        <div class="ctrl-color">
                            <label>Primary</label>
                            <input type="color" name="primary_color" id="primaryColor"
                                   value="<?= htmlspecialchars($editDesign['primary_color'] ?? $tplConfig['color']) ?>" oninput="updatePreview()">
                        </div>
                        <div class="ctrl-color">
                            <label>Accent</label>
                            <input type="color" name="accent_color" id="accentColor"
                                   value="<?= htmlspecialchars($editDesign['accent_color'] ?? $tplConfig['accent']) ?>" oninput="updatePreview()">
                        </div>
                        <div class="ctrl-color">
                            <label>Background</label>
                            <input type="color" name="bg_color" id="bgColor"
                                   value="<?= htmlspecialchars($editDesign['bg_color'] ?? $tplConfig['bg']) ?>" oninput="updatePreview()">
                        </div>
                        <div class="ctrl-color">
                            <label>Text</label>
                            <input type="color" name="text_color" id="textColor"
                                   value="<?= htmlspecialchars($editDesign['text_color'] ?? $tplConfig['text']) ?>" oninput="updatePreview()">
                        </div>
                        <div class="ctrl-group" style="min-width:100px;">
                            <label>Font</label>
                            <select name="font_family" id="fontFamily" class="form-input" onchange="updatePreview()">
                                <?php foreach(['Poppins','Inter','Roboto','Lato','Open Sans','Montserrat','Raleway'] as $f): ?>
                                <option value="<?= $f ?>" <?= ($editDesign['font_family'] ?? 'Poppins') === $f ? 'selected' : '' ?>><?= $f ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="ctrl-group" style="min-width:90px;">
                            <label>QR Size: <span id="qrSizeVal">54</span>px</label>
                            <input type="range" id="qrSize" name="qr_size" min="36" max="90" value="<?= (int)($editDesign['qr_size'] ?? 54) ?>" style="width:100%;"
                                   oninput="document.getElementById('qrSizeVal').textContent=this.value;updatePreview()">
                        </div>
                        <div class="ctrl-group" style="flex:0;min-width:auto;">
                            <label>&nbsp;</label>
                            <label class="qr-toggle">
                                <input type="checkbox" name="show_qr" id="showQr" onchange="updatePreview()" <?= (!$isEditMode || !empty($editDesign['show_qr'])) ? 'checked' : '' ?>>
                                <span>QR</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile-only: Theme colour picker -->
            <div class="card mobile-only-section" id="mobileThemeSection" style="margin-bottom:16px;display:none;">
                <h3 style="font-size:0.9rem;font-weight:600;color:var(--indigo);margin:0 0 12px;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-palette"></i> Theme Colour
                </h3>
                <div class="tpl-theme-dots" id="mobileThemeDots">
                    <?php foreach ($templates as $tKey => $tDef): ?>
                    <span class="tpl-theme-dot<?= $tKey === $selectedTpl ? ' active' : '' ?>"
                          style="background:<?= htmlspecialchars($tDef['color']) ?>;"
                          title="<?= htmlspecialchars($tDef['name']) ?>"
                          data-tpl="<?= htmlspecialchars($tKey) ?>"
                          onclick="applyThemeColor('<?= htmlspecialchars($tKey) ?>');syncThemeDots('<?= htmlspecialchars($tKey) ?>')"></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Design Style Picker -->
            <div class="card" style="margin-bottom:16px;" id="styleSection">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                    <h3 style="font-size:0.9rem;font-weight:600;color:var(--indigo);display:flex;align-items:center;gap:6px;margin:0;">
                        <i class="fas fa-layer-group"></i> Design Style
                    </h3>
                    <button type="button" onclick="toggleSection('styleBody')" class="btn btn-secondary btn-sm" style="padding:3px 10px;font-size:0.72rem;">
                        <i class="fas fa-chevron-up" id="styleChevron"></i>
                    </button>
                </div>
                <div id="styleBody">
                    <p style="font-size:0.72rem;color:var(--text-secondary);margin-bottom:8px;">Choose a layout &amp; orientation</p>
                    <div style="display:flex;gap:6px;margin-bottom:10px;flex-wrap:wrap;">
                        <button type="button" id="filterAll" class="filter-btn active" onclick="setStyleFilter('all')">All (25)</button>
                        <button type="button" id="filterLandscape" class="filter-btn" onclick="setStyleFilter('landscape')">🖥 Landscape (13)</button>
                        <button type="button" id="filterPortrait" class="filter-btn" onclick="setStyleFilter('portrait')">📱 Portrait (12)</button>
                    </div>
                    <div class="style-picker" id="stylePicker" style="grid-template-columns:repeat(5,1fr);"></div>
                </div>
            </div>

            <!-- AI Assistant -->
            <div id="aiSection" class="card" style="margin-bottom:16px;background:linear-gradient(135deg,rgba(99,102,241,0.06),rgba(0,240,255,0.03));border:1px solid rgba(99,102,241,0.2);">
                <h3 style="font-size:0.9rem;font-weight:600;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-robot" style="color:var(--indigo);"></i> AI Design Assistant
                    <span style="background:linear-gradient(135deg,#6366f1,#00f0ff);color:white;font-size:0.6rem;padding:2px 8px;border-radius:10px;">AI</span>
                </h3>
                <div class="form-group" style="margin-bottom:10px;">
                    <label class="form-label" style="font-size:0.78rem;">Describe your needs (optional)</label>
                    <input type="text" name="ai_prompt" id="aiPrompt" class="form-input"
                           style="padding:8px 12px;font-size:0.85rem;"
                           placeholder="e.g. modern tech company, John Smith, software engineer, blue theme...">
                </div>
                <button type="button" class="btn btn-primary" style="width:100%;justify-content:center;" onclick="getAISuggestions()">
                    <i class="fas fa-magic"></i> Generate with AI
                </button>
                <div id="aiOutput" style="margin-top:12px;display:none;"></div>
                <!-- Apply actions (shown after AI returns results) -->
                <div id="aiActions" style="display:none;margin-top:10px;gap:8px;flex-wrap:wrap;">
                    <button type="button" class="btn btn-primary btn-sm" onclick="applyAIFields()" id="applyFieldsBtn" style="display:none;">
                        <i class="fas fa-fill-drip"></i> Apply to Fields
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="applyAIColors()" id="applyColorsBtn" style="display:none;">
                        <i class="fas fa-palette"></i> Apply Colors
                    </button>
                </div>
            </div>

            <!-- Inline Bulk Mode panel -->
            <div class="card" id="bulkModeCard" style="margin-bottom:16px;border-style:dashed;">
                <label class="inline-bulk-toggle" for="bulkModeToggle">
                    <input type="checkbox" id="bulkModeToggle" style="width:16px;height:16px;cursor:pointer;" onchange="toggleBulkMode()">
                    <span style="font-size:0.9rem;font-weight:700;color:var(--indigo);display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-layer-group"></i> Bulk Mode
                    </span>
                    <span style="font-size:0.72rem;color:var(--text-secondary);">Generate multiple cards from a CSV</span>
                </label>
                <div class="inline-bulk-panel" id="inlineBulkPanel">
                    <div style="display:flex;gap:12px;margin-top:14px;flex-wrap:wrap;align-items:flex-start;">
                        <!-- Sample CSV -->
                        <div style="flex:1;min-width:150px;">
                            <div style="font-size:0.7rem;font-weight:600;color:var(--text-secondary);margin-bottom:6px;"><i class="fas fa-download" style="color:var(--indigo);margin-right:3px;"></i> Step 1 — Download template</div>
                            <a id="inlineSampleCsvBtn" href="#" style="pointer-events:none;opacity:0.4;" class="btn btn-secondary btn-sm">
                                <i class="fas fa-download"></i> Sample CSV
                            </a>
                            <div style="font-size:0.65rem;color:var(--text-secondary);margin-top:4px;">Contains all fields for the selected category</div>
                        </div>
                        <!-- Upload -->
                        <div style="flex:1;min-width:150px;">
                            <div style="font-size:0.7rem;font-weight:600;color:var(--text-secondary);margin-bottom:6px;"><i class="fas fa-upload" style="color:var(--indigo);margin-right:3px;"></i> Step 2 — Upload filled CSV</div>
                            <div class="inline-bulk-upload-zone" id="inlineUploadZone"
                                 onclick="document.getElementById('inlineCsvFile').click()"
                                 ondragover="inlineDragOver(event)"
                                 ondragleave="inlineDragLeave(event)"
                                 ondrop="inlineDrop(event)">
                                <i class="fas fa-file-csv" style="font-size:1.4rem;color:var(--indigo);opacity:0.7;display:block;margin-bottom:4px;"></i>
                                <div style="font-size:0.78rem;font-weight:600;">Click or drag CSV here</div>
                                <div style="font-size:0.68rem;color:var(--text-secondary);margin-top:2px;">.csv files only</div>
                                <input type="file" id="inlineCsvFile" accept=".csv,text/csv" onchange="inlineFileSelected(this)">
                                <div id="inlineCsvFilename" style="font-size:0.74rem;color:var(--indigo);margin-top:5px;font-weight:600;word-break:break-all;"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Progress -->
                    <div id="inlineBulkProgress" style="display:none;margin-top:12px;">
                        <div style="font-size:0.78rem;color:var(--text-secondary);" id="inlineBulkProgressLabel">Processing…</div>
                        <div class="inline-bulk-progress-wrap"><div class="inline-bulk-progress-fill" id="inlineBulkProgressBar" style="width:0%;"></div></div>
                    </div>
                    <!-- Result -->
                    <div id="inlineBulkResult" style="display:none;margin-top:12px;"></div>
                    <!-- Generate button -->
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:14px;">
                        <button type="button" id="inlineBulkBtn" class="btn btn-primary" onclick="submitInlineBulk()">
                            <i class="fas fa-bolt"></i> Generate All Cards
                        </button>
                        <span id="inlineBulkHint" style="font-size:0.74rem;color:var(--text-secondary);">Upload a CSV to generate all cards at once.</span>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="form-actions">
                <button type="submit" id="generateBtn" class="btn btn-primary" style="flex:1;justify-content:center;padding:14px;">
                    <i class="fas fa-<?= $isEditMode ? 'save' : 'id-card' ?>"></i> <?= $isEditMode ? 'Update Card' : 'Generate ID Card' ?>
                </button>
                <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
        </form>
    </div>

    <!-- RIGHT: Live Preview -->
    <div class="preview-area">
        <div class="card" style="padding:16px;">
            <h3 style="font-size:0.85rem;font-weight:600;margin-bottom:14px;color:var(--text-secondary);display:flex;align-items:center;gap:6px;">
                <i class="fas fa-eye"></i> Live Preview &nbsp;
                <span id="previewTplName" style="color:var(--indigo);font-weight:700;"><?= htmlspecialchars($tplConfig['name']) ?></span>
                <span id="previewOrientation" style="color:var(--text-secondary);font-size:0.72rem;font-weight:400;margin-left:4px;"><?= ($tplConfig['orientation'] ?? 'landscape') === 'portrait' ? '· Portrait' : '· Landscape' ?></span>
                <span id="previewStyleName" style="color:var(--text-secondary);font-size:0.72rem;font-weight:400;margin-left:2px;"></span>
            </h3>
            <div id="cardPreview" class="id-card-preview" style="background:<?= htmlspecialchars($tplConfig['bg']) ?>;"></div>
            <p style="text-align:center;font-size:0.72rem;color:var(--text-secondary);margin-top:10px;">
                <i class="fas fa-info-circle"></i> Preview is approximate &mdash; final card will be pixel-perfect
            </p>
        </div>

        <!-- Category selector (moved from top) -->
        <div class="card" style="margin-top:12px;padding:14px;">
            <p style="font-size:0.75rem;color:var(--text-secondary);font-weight:600;margin:0 0 8px;"><i class="fas fa-tags" style="color:var(--indigo);margin-right:5px;"></i> SELECT CARD CATEGORY</p>
            <select id="categorySelect" class="form-input" style="padding:8px 10px;font-size:0.82rem;cursor:pointer;" onchange="selectTemplate(this.value);syncCategorySelects(this.value);">
                <option value="corporate" <?= $selectedTpl === 'corporate' ? 'selected' : '' ?>>Corporate</option>
                <option value="student" <?= $selectedTpl === 'student' ? 'selected' : '' ?>>Student / School</option>
                <option value="event" <?= $selectedTpl === 'event' ? 'selected' : '' ?>>Event</option>
                <option value="visitor" <?= $selectedTpl === 'visitor' ? 'selected' : '' ?>>Visitor</option>
                <option value="medical" <?= $selectedTpl === 'medical' ? 'selected' : '' ?>>Medical Staff</option>
                <option value="tech" <?= $selectedTpl === 'tech' ? 'selected' : '' ?>>Tech Company</option>
                <option value="bank" <?= $selectedTpl === 'bank' ? 'selected' : '' ?>>Banking / Finance</option>
                <option value="media" <?= $selectedTpl === 'media' ? 'selected' : '' ?>>Press / Media</option>
                <option value="govt" <?= $selectedTpl === 'govt' ? 'selected' : '' ?>>Government</option>
                <option value="security" <?= $selectedTpl === 'security' ? 'selected' : '' ?>>Security</option>
                <option value="hospital_v" <?= $selectedTpl === 'hospital_v' ? 'selected' : '' ?>>Hospital</option>
                <option value="ngo_v" <?= $selectedTpl === 'ngo_v' ? 'selected' : '' ?>>NGO / Non-Profit</option>
                <option value="library_v" <?= $selectedTpl === 'library_v' ? 'selected' : '' ?>>Library Card</option>
                <option value="gym_v" <?= $selectedTpl === 'gym_v' ? 'selected' : '' ?>>Gym / Fitness</option>
                <option value="transport_v" <?= $selectedTpl === 'transport_v' ? 'selected' : '' ?>>Transport</option>
                <option value="university_v" <?= $selectedTpl === 'university_v' ? 'selected' : '' ?>>University Faculty</option>
                <option value="security_v" <?= $selectedTpl === 'security_v' ? 'selected' : '' ?>>Security Guard</option>
                <option value="retail_v" <?= $selectedTpl === 'retail_v' ? 'selected' : '' ?>>Retail / Shop</option>
            </select>
        </div>

        <!-- Template theme colour picker (no text, colour dots only) -->
        <div class="card" style="margin-top:12px;padding:14px;">
            <p style="font-size:0.75rem;color:var(--text-secondary);font-weight:600;margin:0 0 10px;"><i class="fas fa-palette" style="color:var(--indigo);margin-right:5px;"></i> THEME COLOUR</p>
            <div class="tpl-theme-dots" id="tplThemeDots">
                <?php foreach ($templates as $tKey => $tDef): ?>
                <span class="tpl-theme-dot<?= $tKey === $selectedTpl ? ' active' : '' ?>"
                      style="background:<?= htmlspecialchars($tDef['color']) ?>;"
                      title="<?= htmlspecialchars($tDef['name']) ?>"
                      data-tpl="<?= htmlspecialchars($tKey) ?>"
                      onclick="applyThemeColor('<?= htmlspecialchars($tKey) ?>');syncThemeDots('<?= htmlspecialchars($tKey) ?>')"></span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card" style="margin-top:12px;padding:14px;">
            <h4 style="font-size:0.82rem;font-weight:600;margin-bottom:8px;color:var(--text-secondary);">TEMPLATE FIELDS</h4>
            <div id="tplFieldsList" style="display:flex;flex-wrap:wrap;gap:5px;">
                <?php foreach ($tplConfig['fields'] as $field): ?>
                <span style="padding:3px 9px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:12px;font-size:0.7rem;color:var(--text-secondary);">
                    <?= htmlspecialchars($field_labels[$field] ?? $field) ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Mobile floating preview button -->
<button type="button" class="mobile-preview-btn" onclick="openMobilePreview()">
    <i class="fas fa-eye"></i> Preview Card
</button>

<!-- Mobile preview modal -->
<div class="preview-modal-overlay" id="mobilePreviewModal">
    <div class="preview-modal-box">
        <button class="preview-modal-close" onclick="closeMobilePreview()" aria-label="Close">&times;</button>
        <h3 style="font-size:0.88rem;font-weight:700;margin-bottom:14px;color:var(--indigo);display:flex;align-items:center;gap:6px;">
            <i class="fas fa-eye"></i> Live Preview
            <span id="mobilePreviewTplName" style="color:var(--text-secondary);font-weight:500;font-size:0.78rem;"></span>
        </h3>
        <div id="mobileCardPreview" style="width:100%;max-width:340px;margin:0 auto;border-radius:14px;overflow:hidden;box-shadow:0 12px 40px rgba(0,0,0,0.35);aspect-ratio:85.6/54;position:relative;" class="id-card-preview"></div>
        <p style="text-align:center;font-size:0.7rem;color:var(--text-secondary);margin-top:10px;">
            <i class="fas fa-info-circle"></i> Preview is approximate
        </p>
    </div>
</div>

<!-- Mobile bottom navigation bar (shown on ≤600px) -->
<!-- Order: Category → Info → Style → Design → Theme → AI → Preview -->
<div class="cx-mobile-nav-bar" id="cxMobileNavBar">
    <div class="cx-mobile-nav-inner">
        <button type="button" class="cx-mobile-nav-btn" data-section="mobileCategorySection" onclick="cxMobileNav('mobileCategorySection',this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
            Category
        </button>
        <button type="button" class="cx-mobile-nav-btn active" data-section="dynamicFields" onclick="cxMobileNav('dynamicFields',this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            Info
        </button>
        <button type="button" class="cx-mobile-nav-btn" data-section="styleBody" onclick="cxMobileNav('styleBody',this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
            Style
        </button>
        <button type="button" class="cx-mobile-nav-btn" data-section="designControls" onclick="cxMobileNav('designControls',this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41M12 2v2M12 20v2M2 12h2M20 12h2"></path></svg>
            Design
        </button>
        <button type="button" class="cx-mobile-nav-btn" data-section="mobileThemeSection" onclick="cxMobileNav('mobileThemeSection',this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"></path></svg>
            Theme
        </button>
        <button type="button" class="cx-mobile-nav-btn" data-section="aiSection" onclick="cxMobileNav('aiSection',this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8z"></path><path d="M12 6v6l4 2"></path></svg>
            AI
        </button>
        <button type="button" class="cx-mobile-nav-btn" onclick="cxMobileNavBulk(this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
            Bulk
        </button>
        <button type="button" class="cx-mobile-nav-btn" onclick="openMobilePreview()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            Preview
        </button>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
// =============================================================================
//  Data from PHP
// =============================================================================
var TEMPLATES    = <?= json_encode($templates) ?>;
var FIELD_LABELS = <?= json_encode($field_labels) ?>;
var CSRF_TOKEN   = '<?= htmlspecialchars($csrfToken) ?>';

var currentTpl   = '<?= htmlspecialchars($selectedTpl) ?>';
var currentStyle = '';   // set during init
var photoDataUrl = null;

// =============================================================================
//  Design style definitions — all 25 styles
// =============================================================================
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
var currentStyleFilter = 'all';

function setStyleFilter(filter) {
    currentStyleFilter = filter;
    ['all','landscape','portrait'].forEach(function(f) {
        var btn = document.getElementById('filter'+f.charAt(0).toUpperCase()+f.slice(1));
        if (btn) btn.classList.toggle('active', f === filter);
    });
    buildStylePicker();
}

function getFilteredStyles() {
    if (currentStyleFilter === 'landscape') return ALL_STYLES.filter(function(s){ return !s.portrait; });
    if (currentStyleFilter === 'portrait')  return ALL_STYLES.filter(function(s){ return s.portrait; });
    return ALL_STYLES;
}

function isPortraitStyle(key) {
    var s = ALL_STYLES.find(function(x){ return x.key === key; });
    return s ? s.portrait : false;
}

var STYLE_COLOR_PRESETS = {
    'classic':       { pri:'#1e40af', acc:'#3b82f6', bg:'#ffffff', txt:'#1e293b' },
    'sidebar':       { pri:'#6366f1', acc:'#818cf8', bg:'#111827', txt:'#f1f5f9' },
    'wave':          { pri:'#065f46', acc:'#10b981', bg:'#fdf8f3', txt:'#064e3b' },
    'bold_header':   { pri:'#0369a1', acc:'#0ea5e9', bg:'#ffffff', txt:'#1e293b' },
    'diagonal':      { pri:'#7c3aed', acc:'#a78bfa', bg:'#111827', txt:'#f5f3ff' },
    'gradient_pro':  { pri:'#1e40af', acc:'#3b82f6', bg:'#1e3a8a', txt:'#ffffff' },
    'neon':          { pri:'#00f0ff', acc:'#ff2ec4', bg:'#050a10', txt:'#e0f2fe' },
    'executive':     { pri:'#1a1f2e', acc:'#c9a84c', bg:'#1a1f2e', txt:'#ffffff' },
    'stripe':        { pri:'#dc2626', acc:'#f87171', bg:'#f5f7fa', txt:'#1e293b' },
    'metro':         { pri:'#14532d', acc:'#22c55e', bg:'#ffffff', txt:'#1e293b' },
    'glass':         { pri:'#7c3aed', acc:'#a78bfa', bg:'#7c3aed', txt:'#ffffff' },
    'zigzag':        { pri:'#b45309', acc:'#f59e0b', bg:'#f7f8fc', txt:'#92400e' },
    'ribbon':        { pri:'#6366f1', acc:'#818cf8', bg:'#111827', txt:'#f1f5f9' },
    'v_sharp':       { pri:'#1e40af', acc:'#3b82f6', bg:'#f7f8fc', txt:'#1e293b' },
    'v_curve':       { pri:'#065f46', acc:'#10b981', bg:'#fafafa', txt:'#064e3b' },
    'v_hex':         { pri:'#0369a1', acc:'#0ea5e9', bg:'#ffffff', txt:'#1e293b' },
    'v_circle':      { pri:'#7c3aed', acc:'#a78bfa', bg:'#f7f8fc', txt:'#1e293b' },
    'v_split':       { pri:'#dc2626', acc:'#f87171', bg:'#ffffff', txt:'#1e293b' },
    'v_ribbon':      { pri:'#14532d', acc:'#22c55e', bg:'#f7f8fc', txt:'#1e293b' },
    'v_arch':        { pri:'#1e40af', acc:'#3b82f6', bg:'#ffffff', txt:'#1e293b' },
    'v_diamond':     { pri:'#0891b2', acc:'#22d3ee', bg:'#f7f8fc', txt:'#1e293b' },
    'v_corner':      { pri:'#7c3aed', acc:'#a78bfa', bg:'#ffffff', txt:'#1e293b' },
    'v_dual':        { pri:'#b45309', acc:'#f59e0b', bg:'#f7f8fc', txt:'#92400e' },
    'v_stripe':      { pri:'#6366f1', acc:'#818cf8', bg:'#6366f1', txt:'#ffffff' },
    'v_badge':       { pri:'#14532d', acc:'#22c55e', bg:'#f7f8fc', txt:'#1e293b' }
};

function applyStyleColors(styleKey) {
    var preset = STYLE_COLOR_PRESETS[styleKey];
    if (!preset) return;
    document.getElementById('primaryColor').value = preset.pri;
    document.getElementById('accentColor').value  = preset.acc;
    document.getElementById('bgColor').value      = preset.bg;
    document.getElementById('textColor').value    = preset.txt;
}

// =============================================================================
//  QR Code helpers
// =============================================================================
function buildQRData() {
    var parts = [];
    // Scan ALL visible input fields in the dynamic fields container
    var container = document.getElementById('dynamicFields');
    if (container) {
        var inputs = container.querySelectorAll('input[type=text]');
        inputs.forEach(function(inp) {
            if (!inp.value || !inp.id) return;
            var fieldKey = inp.id.replace(/^field_/, '');
            var label = (FIELD_LABELS[fieldKey] || fieldKey.replace(/_/g,' ')).toUpperCase();
            parts.push(label+': '+inp.value);
        });
    }
    // Fallback: try named fields directly
    if (parts.length === 0) {
        var nameEl = document.getElementById('field_name');
        if (nameEl && nameEl.value) parts.push('Name: '+nameEl.value);
    }
    return parts.join('\n') || 'CardX ID Card';
}

function qrSlotHTML(posStyle) {
    return '<div class="qr-slot" style="position:absolute;'+posStyle+';display:flex;align-items:center;justify-content:center;background:#fff;border-radius:3px;z-index:10;"></div>';
}

function renderQRCode() {
    var showQrEl = document.getElementById('showQr');
    var show = showQrEl && showQrEl.checked;
    var slot = document.querySelector('#cardPreview .qr-slot');
    if (!slot) return;
    if (!show) { slot.style.display='none'; return; }
    slot.style.display = '';
    var sizeEl = document.getElementById('qrSize');
    var size = sizeEl ? parseInt(sizeEl.value) : 54;
    var data = buildQRData();
    slot.innerHTML = '';
    try {
        new QRCode(slot, {
            text: data,
            width: size,
            height: size,
            correctLevel: QRCode.CorrectLevel.L,
            colorDark: '#000000',
            colorLight: '#ffffff'
        });
    } catch(e) {
        slot.innerHTML = '<div style="width:'+size+'px;height:'+size+'px;background:#fff;border:1px solid #ccc;display:flex;align-items:center;justify-content:center;font-size:0.5rem;color:#999;">QR</div>';
    }
}

// =============================================================================
//  Field short-label map (matching reference images)
// =============================================================================
var FIELD_SHORT = {
    department:'DEPT', employee_id:'ID NO', roll_number:'ROLL NO', id_number:'ID NO',
    badge_id:'BADGE', license_no:'LIC NO', blood_group:'B.GROUP',
    phone:'PHONE', email:'E-MAIL', year:'YEAR', organization:'ORG',
    host_name:'HOST', purpose:'PURPOSE', visit_date:'DATE',
    dob:'D.O.B', expiry_date:'EXPIRE', valid_from:'VALID FROM', valid_till:'VALID TILL',
    nationality:'NATION', branch:'BRANCH', shift:'SHIFT', session:'SESSION',
    reg_number:'REG NO', zone:'ZONE', rank:'RANK', gender:'GENDER', joining_date:'JOINED',
    person_address:'ADDRESS', company_address:'ADDRESS', school_address:'ADDRESS'
};

// =============================================================================
//  Card data extractor
// =============================================================================
function getCardValues() {
    var tpl  = TEMPLATES[currentTpl] || {};
    var pri  = document.getElementById('primaryColor').value || tpl.color  || '#1e40af';
    var acc  = document.getElementById('accentColor').value  || tpl.accent || '#3b82f6';
    var bg   = document.getElementById('bgColor').value      || tpl.bg     || '#ffffff';
    var txt  = document.getElementById('textColor').value    || tpl.text   || '#1e293b';
    var font = document.getElementById('fontFamily').value   || 'Poppins';

    var roleKeys = ['designation','title','course','event_name'];
    var nameEl   = document.getElementById('field_name');
    var nameVal  = (nameEl && nameEl.value) ? nameEl.value : 'YOUR NAME';

    var orgEl = document.getElementById('field_company_name') || document.getElementById('field_school_name');
    var orgVal = (orgEl && orgEl.value) ? orgEl.value : (tpl.name || 'CardX');

    var addrEl = document.getElementById('field_company_address') || document.getElementById('field_school_address');
    var addrVal = (addrEl && addrEl.value) ? addrEl.value : '';

    var roleVal = 'Creative Designer';
    for (var i = 0; i < roleKeys.length; i++) {
        var el = document.getElementById('field_' + roleKeys[i]);
        if (el && el.value) { roleVal = el.value; break; }
    }

    var skipKeys = ['name','company_name','school_name','company_address','school_address'].concat(roleKeys);
    var fieldKeys = (tpl.fields || []).filter(function(f){ return f !== 'photo' && skipKeys.indexOf(f) === -1; });
    var isPortrait = isPortraitStyle(currentStyle);
    var fieldItems = fieldKeys.slice(0, isPortrait ? 5 : 6).map(function(f) {
        var el    = document.getElementById('field_' + f);
        var val   = el ? (el.value || (FIELD_LABELS[f] || f)) : (FIELD_LABELS[f] || f);
        var label = FIELD_SHORT[f] || f.replace(/_/g,' ').toUpperCase();
        return { label:label, val:val };
    });

    var shapeEl = document.getElementById('profileShape');
    var profileShape = shapeEl ? shapeEl.value : 'circle';
    var photoShapeCSS = profileShape === 'square' ? 'border-radius:4px;' : (profileShape === 'oval' ? 'border-radius:50% / 40%;' : 'border-radius:50%;');
    var photoHTML = photoDataUrl
        ? '<img src="'+photoDataUrl+'" style="width:100%;height:100%;object-fit:cover;">'
        : '<i class="fas fa-user" style="font-size:1.8rem;opacity:0.55;color:rgba(255,255,255,0.8);"></i>';

    var tplName = tpl.name || 'CardX';
    var portrait = tpl.orientation === 'portrait';

    return { pri:pri, acc:acc, bg:bg, txt:txt, font:font, nameVal:nameVal, roleVal:roleVal,
             orgVal:orgVal, addrVal:addrVal, fieldItems:fieldItems, photoHTML:photoHTML,
             photoShapeCSS:photoShapeCSS, tplName:tplName, portrait:portrait };
}

function fieldRowsHTML(items, lc, vc, fs) {
    fs = fs || 'clamp(0.38rem,0.9vw,0.54rem)';
    return items.map(function(f){
        return '<div style="display:flex;align-items:baseline;font-size:'+fs+';white-space:nowrap;overflow:hidden;margin-bottom:1.8%;">'
            +'<span style="color:'+lc+';font-weight:700;min-width:30%;letter-spacing:0.03em;flex-shrink:0;">'+f.label+'</span>'
            +'<span style="color:'+vc+';margin-left:2%;overflow:hidden;text-overflow:ellipsis;">: '+f.val+'</span>'
            +'</div>';
    }).join('');
}

// =============================================================================
//  Style thumbnail builder
// =============================================================================
function buildStyleThumbnail(key, pri, acc, portrait) {
    var W = 85.6, H = 54;
    if (portrait) { W = 54; H = 85.6; }
    var vb = '0 0 ' + W + ' ' + H;

    switch(key) {
        // ── Landscape styles ─────────────────────────────────────────────────
        case 'classic':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<defs><linearGradient id="cg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="'+pri+'"/><stop offset="100%" stop-color="'+acc+'"/></linearGradient>'
                +'<clipPath id="cc"><polygon points="0,0 '+W+',0 '+W+','+(H*0.56)+' 0,'+(H*0.72)+'"/></clipPath></defs>'
                +'<rect width="'+W+'" height="'+H+'" fill="#f7f8fc"/>'
                +'<rect width="'+W+'" height="'+H+'" fill="url(#cg)" clip-path="url(#cc)"/>'
                +'<circle cx="'+W*0.1+'" cy="5" r="3" fill="rgba(255,255,255,0.3)"/>'
                +'<circle cx="'+(W/2)+'" cy="'+(H*0.46)+'" r="7" fill="#fff" stroke="'+pri+'" stroke-width="1.5"/>'
                +'<rect x="'+((W-26)/2)+'" y="'+(H*0.58)+'" width="26" height="3.5" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+((W-18)/2)+'" y="'+(H*0.65)+'" width="18" height="2" rx="1" fill="#aaa"/>'
                +'<rect x="8" y="'+(H*0.73)+'" width="16" height="1.8" rx="0.8" fill="#555" opacity="0.5"/>'
                +'<rect x="'+W/2+'" y="'+(H*0.73)+'" width="16" height="1.8" rx="0.8" fill="#555" opacity="0.5"/>'
                +'<rect x="8" y="'+(H*0.80)+'" width="20" height="1.8" rx="0.8" fill="#555" opacity="0.4"/>'
                +'<rect x="'+W/2+'" y="'+(H*0.80)+'" width="20" height="1.8" rx="0.8" fill="#555" opacity="0.4"/>'
                +'<rect x="'+((W-24)/2)+'" y="'+(H*0.89)+'" width="24" height="4" rx="0.5" fill="#ddd"/>'
                +'</svg>';
        case 'sidebar':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#111827"/>'
                +'<rect x="'+(W*0.42)+'" y="'+(-H*0.2)+'" width="'+(W*0.75)+'" height="'+(W*0.75)+'" rx="3" fill="'+pri+'" transform="rotate(45 '+(W*0.82)+' '+(H*0.3)+')" opacity="0.9"/>'
                +'<circle cx="'+(W*0.8)+'" cy="'+(H*0.35)+'" r="8" fill="rgba(255,255,255,0.18)" stroke="rgba(255,255,255,0.6)" stroke-width="1"/>'
                +'<rect x="5" y="'+(H*0.56)+'" width="28" height="3.5" rx="1.5" fill="#fff" opacity="0.9"/>'
                +'<rect x="5" y="'+(H*0.64)+'" width="20" height="2" rx="1" fill="'+acc+'" opacity="0.7"/>'
                +'<rect x="5" y="'+(H*0.74)+'" width="24" height="1.6" rx="0.6" fill="rgba(255,255,255,0.3)"/>'
                +'<rect x="5" y="'+(H*0.80)+'" width="20" height="1.6" rx="0.6" fill="rgba(255,255,255,0.25)"/>'
                +'<rect x="'+(W*0.6)+'" y="'+(H*0.84)+'" width="22" height="5" rx="0.5" fill="rgba(255,255,255,0.12)"/>'
                +'</svg>';
        case 'wave':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#fdf8f3"/>'
                +'<path d="M0,0 L'+(W*0.55)+',0 Q'+(W*0.73)+','+(H*0.28)+' '+(W*0.65)+','+(H*0.5)+' Q'+(W*0.75)+','+(H*0.72)+' '+(W*0.58)+','+H+' L0,'+H+' Z" fill="'+pri+'"/>'
                +'<circle cx="'+(W*0.42)+'" cy="'+(H*0.4)+'" r="8.5" fill="rgba(255,255,255,0.25)" stroke="rgba(255,255,255,0.7)" stroke-width="1.2"/>'
                +'<rect x="4" y="'+(H*0.7)+'" width="22" height="3" rx="1" fill="#fff" opacity="0.9"/>'
                +'<rect x="'+(W*0.68)+'" y="10" width="22" height="1.8" rx="0.7" fill="#888" opacity="0.5"/>'
                +'<rect x="'+(W*0.68)+'" y="15" width="18" height="1.8" rx="0.7" fill="#888" opacity="0.45"/>'
                +'<rect x="4" y="'+(H*0.88)+'" width="20" height="4" rx="0.4" fill="rgba(255,255,255,0.2)"/>'
                +'</svg>';
        case 'bold_header':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<defs><linearGradient id="tbg" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="'+pri+'"/><stop offset="100%" stop-color="'+acc+'"/></linearGradient></defs>'
                +'<rect x="0" y="0" width="'+(W*0.4)+'" height="'+H+'" fill="url(#tbg)"/>'
                +'<rect x="'+(W*0.4)+'" y="0" width="'+(W*0.6)+'" height="'+H+'" fill="#fff"/>'
                +'<rect x="'+(W*0.4)+'" y="0" width="'+(W*0.6)+'" height="3" fill="url(#tbg)"/>'
                +'<circle cx="'+(W*0.2)+'" cy="'+(H*0.52)+'" r="10" fill="rgba(255,255,255,0.2)" stroke="rgba(255,255,255,0.7)" stroke-width="1.2"/>'
                +'<rect x="'+(W*0.44)+'" y="14" width="30" height="3.5" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+(W*0.44)+'" y="20" width="20" height="2" rx="0.8" fill="#aaa"/>'
                +'<rect x="'+(W*0.44)+'" y="26" width="28" height="1.5" rx="0.7" fill="'+acc+'" opacity="0.5"/>'
                +'<rect x="'+(W*0.44)+'" y="31" width="24" height="1.8" rx="0.7" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.44)+'" y="36" width="28" height="1.8" rx="0.7" fill="#555" opacity="0.45"/>'
                +'</svg>';
        case 'diagonal':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#111827"/>'
                +'<rect x="'+(W*0.55)+'" y="0" width="'+(W*0.45)+'" height="'+H+'" fill="'+pri+'15"/>'
                +'<polygon points="'+W+',0 '+W+','+(H*0.4)+' '+(W*0.55)+','+(H*0.2)+'" fill="'+pri+'"/>'
                +'<polygon points="'+W+','+(H*0.34)+' '+W+','+(H*0.73)+' '+(W*0.58)+','+(H*0.535)+'" fill="'+acc+'" opacity="0.85"/>'
                +'<polygon points="'+W+','+(H*0.64)+' '+W+','+H+' '+(W*0.56)+','+(H*0.83)+'" fill="'+pri+'" opacity="0.7"/>'
                +'<circle cx="'+(W*0.14)+'" cy="'+(H*0.5)+'" r="9" fill="rgba(255,255,255,0.1)" stroke="'+acc+'" stroke-width="1.2"/>'
                +'<rect x="'+(W*0.29)+'" y="17" width="22" height="3" rx="1" fill="#fff" opacity="0.9"/>'
                +'<rect x="5" y="'+(H*0.74)+'" width="20" height="1.6" rx="0.6" fill="rgba(255,255,255,0.25)"/>'
                +'<rect x="5" y="'+(H*0.81)+'" width="18" height="1.6" rx="0.6" fill="rgba(255,255,255,0.2)"/>'
                +'</svg>';

        // ── Portrait styles ───────────────────────────────────────────────────
        case 'v_sharp':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#f7f8fc"/>'
                +'<polygon points="0,0 '+W+',0 '+W+','+(H*0.38)+' '+(W*0.5)+','+(H*0.48)+' 0,'+(H*0.38)+'" fill="'+pri+'"/>'
                +'<circle cx="3" cy="4" r="3" fill="rgba(255,255,255,0.25)"/>'
                +'<circle cx="'+(W*0.5)+'" cy="'+(H*0.43)+'" r="9" fill="#fff" stroke="'+pri+'" stroke-width="1.5"/>'
                +'<rect x="'+(W*0.1)+'" y="'+(H*0.54)+'" width="'+(W*0.8)+'" height="4" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+(W*0.15)+'" y="'+(H*0.61)+'" width="'+(W*0.7)+'" height="2.5" rx="1" fill="#aaa"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.67)+'" width="'+(W*0.85)+'" height="1.5" rx="0.6" fill="#555" opacity="0.3"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.72)+'" width="'+(W*0.85)+'" height="2.2" rx="0.8" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.77)+'" width="'+(W*0.75)+'" height="2.2" rx="0.8" fill="#555" opacity="0.45"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.82)+'" width="'+(W*0.80)+'" height="2.2" rx="0.8" fill="#555" opacity="0.4"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.87)+'" width="'+(W*0.60)+'" height="2.2" rx="0.8" fill="#555" opacity="0.35"/>'
                +'<rect x="'+(W*0.1)+'" y="'+(H*0.93)+'" width="'+(W*0.8)+'" height="4" rx="0.5" fill="#ddd"/>'
                +'</svg>';
        case 'v_curve':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#fafafa"/>'
                +'<path d="M0,0 L'+W+',0 L'+W+','+(H*0.55)+' Q'+(W*0.75)+','+(H*0.75)+' '+(W*0.5)+','+(H*0.62)+' Q'+(W*0.25)+','+(H*0.5)+' 0,'+(H*0.67)+' Z" fill="'+pri+'"/>'
                +'<circle cx="3" cy="3" r="3" fill="rgba(255,255,255,0.25)"/>'
                +'<circle cx="'+(W*0.5)+'" cy="'+(H*0.38)+'" r="10" fill="rgba(255,255,255,0.25)" stroke="rgba(255,255,255,0.7)" stroke-width="1.2"/>'
                +'<rect x="'+(W*0.05)+'" y="'+(H*0.7)+'" width="'+(W*0.9)+'" height="4" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+(W*0.1)+'" y="'+(H*0.77)+'" width="'+(W*0.8)+'" height="2.5" rx="1" fill="#aaa"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.83)+'" width="'+(W*0.85)+'" height="2" rx="0.8" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.87)+'" width="'+(W*0.75)+'" height="2" rx="0.8" fill="#555" opacity="0.45"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.91)+'" width="'+(W*0.7)+'" height="1.8" rx="0.7" fill="#555" opacity="0.4"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.95)+'" width="'+(W*0.55)+'" height="3.5" rx="0.5" fill="#ddd"/>'
                +'</svg>';
        case 'v_hex':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#ffffff"/>'
                +'<rect x="0" y="0" width="'+W+'" height="'+(H*0.45)+'" fill="'+pri+'"/>'
                +'<path d="M0,'+(H*0.42)+' Q'+(W*0.5)+','+(H*0.52)+' '+W+','+(H*0.42)+' L'+W+','+(H*0.45)+' L0,'+(H*0.45)+' Z" fill="#fff"/>'
                +'<circle cx="3" cy="3" r="3" fill="rgba(255,255,255,0.25)"/>'
                +'<polygon points="'+(W*0.5)+','+(H*0.23)+' '+(W*0.68)+','+(H*0.33)+' '+(W*0.68)+','+(H*0.53)+' '+(W*0.5)+','+(H*0.63)+' '+(W*0.32)+','+(H*0.53)+' '+(W*0.32)+','+(H*0.33)+'" fill="#fff" stroke="'+pri+'" stroke-width="1.5"/>'
                +'<circle cx="'+(W*0.5)+'" cy="'+(H*0.43)+'" r="7" fill="'+pri+'20"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.65)+'" width="'+(W*0.84)+'" height="4" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.72)+'" width="'+(W*0.75)+'" height="2.5" rx="1" fill="#aaa"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.78)+'" width="'+(W*0.84)+'" height="2" rx="0.8" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.83)+'" width="'+(W*0.78)+'" height="2" rx="0.8" fill="#555" opacity="0.45"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.88)+'" width="'+(W*0.70)+'" height="2" rx="0.8" fill="#555" opacity="0.4"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.93)+'" width="'+(W*0.84)+'" height="4" rx="0.5" fill="#ddd"/>'
                +'</svg>';
        case 'v_circle':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#f7f8fc"/>'
                +'<rect x="0" y="0" width="'+W+'" height="'+(H*0.46)+'" fill="'+pri+'"/>'
                +'<circle cx="3" cy="3" r="3" fill="rgba(255,255,255,0.25)"/>'
                +'<circle cx="'+(W*0.5)+'" cy="'+(H*0.37)+'" r="11" fill="#fff" stroke="'+acc+'" stroke-width="1.5"/>'
                +'<circle cx="'+(W*0.5)+'" cy="'+(H*0.37)+'" r="8" fill="'+pri+'20"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.58)+'" width="'+(W*0.84)+'" height="4" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+(W*0.12)+'" y="'+(H*0.65)+'" width="'+(W*0.76)+'" height="2.5" rx="1" fill="#aaa"/>'
                +'<rect x="'+(W*0.06)+'" y="'+(H*0.71)+'" width="'+(W*0.88)+'" height="1.5" rx="0.5" fill="'+acc+'" opacity="0.4"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.75)+'" width="'+(W*0.84)+'" height="2.2" rx="0.8" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.80)+'" width="'+(W*0.78)+'" height="2.2" rx="0.8" fill="#555" opacity="0.45"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.85)+'" width="'+(W*0.72)+'" height="2.2" rx="0.8" fill="#555" opacity="0.4"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.90)+'" width="'+(W*0.65)+'" height="2.2" rx="0.8" fill="#555" opacity="0.35"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.95)+'" width="'+(W*0.65)+'" height="3.5" rx="0.5" fill="#ddd"/>'
                +'</svg>';
        case 'v_split':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#fff"/>'
                +'<rect x="0" y="0" width="'+(W*0.58)+'" height="'+(H*0.52)+'" fill="'+pri+'"/>'
                +'<polygon points="'+(W*0.48)+',0 '+W+',0 '+W+','+(H*0.4)+'" fill="'+acc+'" opacity="0.9"/>'
                +'<rect x="0" y="'+(H*0.97)+'" width="'+W+'" height="'+(H*0.04)+'" fill="'+pri+'"/>'
                +'<circle cx="3" cy="3" r="3" fill="rgba(255,255,255,0.25)"/>'
                +'<circle cx="'+(W*0.78)+'" cy="'+(H*0.17)+'" r="9" fill="rgba(255,255,255,0.2)" stroke="rgba(255,255,255,0.6)" stroke-width="1"/>'
                +'<rect x="'+(W*0.06)+'" y="'+(H*0.54)+'" width="'+(W*0.88)+'" height="4" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+(W*0.06)+'" y="'+(H*0.61)+'" width="'+(W*0.75)+'" height="2.5" rx="1" fill="#aaa"/>'
                +'<rect x="'+(W*0.06)+'" y="'+(H*0.68)+'" width="'+(W*0.45)+'" height="1.5" rx="0.5" fill="'+acc+'" opacity="0.6"/>'
                +'<rect x="'+(W*0.06)+'" y="'+(H*0.72)+'" width="'+(W*0.88)+'" height="2" rx="0.8" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.06)+'" y="'+(H*0.77)+'" width="'+(W*0.80)+'" height="2" rx="0.8" fill="#555" opacity="0.45"/>'
                +'<rect x="'+(W*0.06)+'" y="'+(H*0.82)+'" width="'+(W*0.88)+'" height="2" rx="0.8" fill="#555" opacity="0.4"/>'
                +'<rect x="'+(W*0.06)+'" y="'+(H*0.87)+'" width="'+(W*0.70)+'" height="2" rx="0.8" fill="#555" opacity="0.35"/>'
                +'<rect x="'+(W*0.06)+'" y="'+(H*0.92)+'" width="'+(W*0.55)+'" height="3.5" rx="0.5" fill="#ddd"/>'
                +'</svg>';
        case 'gradient_pro':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<defs><linearGradient id="gpg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="'+pri+'"/><stop offset="100%" stop-color="'+acc+'"/></linearGradient></defs>'
                +'<rect width="'+W+'" height="'+H+'" fill="url(#gpg)"/>'
                +'<rect x="0" y="0" width="'+W+'" height="'+H+'" fill="rgba(0,0,0,0.18)"/>'
                +'<circle cx="'+(W*0.28)+'" cy="'+(H*0.5)+'" r="11" fill="rgba(255,255,255,0.2)" stroke="rgba(255,255,255,0.7)" stroke-width="1.2"/>'
                +'<rect x="'+(W*0.45)+'" y="12" width="30" height="3.5" rx="1.5" fill="#fff" opacity="0.95"/>'
                +'<rect x="'+(W*0.45)+'" y="18" width="22" height="2" rx="1" fill="rgba(255,255,255,0.6)"/>'
                +'<rect x="'+(W*0.45)+'" y="25" width="28" height="1.8" rx="0.7" fill="rgba(255,255,255,0.4)"/>'
                +'<rect x="'+(W*0.45)+'" y="30" width="24" height="1.8" rx="0.7" fill="rgba(255,255,255,0.35)"/>'
                +'<rect x="'+(W*0.45)+'" y="35" width="26" height="1.8" rx="0.7" fill="rgba(255,255,255,0.3)"/>'
                +'<rect x="'+(W*0.45)+'" y="40" width="20" height="1.8" rx="0.7" fill="rgba(255,255,255,0.25)"/>'
                +'<rect x="'+(W*0.1)+'" y="'+(H*0.88)+'" width="'+(W*0.45)+'" height="4" rx="0.5" fill="rgba(255,255,255,0.15)"/>'
                +'</svg>';
        case 'neon':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#050a10"/>'
                +'<rect x="0" y="0" width="'+W+'" height="3" fill="'+pri+'"/>'
                +'<rect x="0" y="'+(H-3)+'" width="'+W+'" height="3" fill="'+acc+'"/>'
                +'<circle cx="'+(W*0.22)+'" cy="'+(H*0.5)+'" r="11" fill="rgba(255,255,255,0.04)" stroke="'+acc+'" stroke-width="1.5"/>'
                +'<circle cx="'+(W*0.22)+'" cy="'+(H*0.5)+'" r="7" fill="rgba(255,255,255,0.07)"/>'
                +'<rect x="'+(W*0.4)+'" y="10" width="32" height="3.5" rx="1.5" fill="'+pri+'" opacity="0.95"/>'
                +'<rect x="'+(W*0.4)+'" y="16" width="22" height="2" rx="1" fill="'+acc+'" opacity="0.7"/>'
                +'<rect x="'+(W*0.4)+'" y="23" width="28" height="1.5" rx="0.6" fill="rgba(255,255,255,0.2)"/>'
                +'<rect x="'+(W*0.4)+'" y="28" width="24" height="1.5" rx="0.6" fill="rgba(255,255,255,0.18)"/>'
                +'<rect x="'+(W*0.4)+'" y="33" width="26" height="1.5" rx="0.6" fill="rgba(255,255,255,0.15)"/>'
                +'<rect x="'+(W*0.4)+'" y="38" width="20" height="1.5" rx="0.6" fill="rgba(255,255,255,0.12)"/>'
                +'<rect x="'+(W*0.1)+'" y="'+(H*0.87)+'" width="30" height="4.5" rx="0.5" fill="rgba(255,255,255,0.07)"/>'
                +'</svg>';
        case 'executive':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#1a1f2e"/>'
                +'<rect x="0" y="0" width="'+W+'" height="4" fill="#c9a84c"/>'
                +'<rect x="0" y="'+(H-4)+'" width="'+W+'" height="4" fill="#c9a84c"/>'
                +'<rect x="4" y="4" width="'+(W-8)+'" height="'+(H-8)+'" fill="none" stroke="rgba(201,168,76,0.3)" stroke-width="0.5"/>'
                +'<circle cx="'+(W*0.22)+'" cy="'+(H*0.5)+'" r="11" fill="rgba(201,168,76,0.15)" stroke="#c9a84c" stroke-width="1.2"/>'
                +'<rect x="'+(W*0.38)+'" y="10" width="32" height="3.5" rx="1.5" fill="#fff" opacity="0.92"/>'
                +'<rect x="'+(W*0.38)+'" y="16" width="22" height="2" rx="1" fill="#c9a84c" opacity="0.8"/>'
                +'<rect x="'+(W*0.38)+'" y="23" width="28" height="1.5" rx="0.6" fill="rgba(255,255,255,0.3)"/>'
                +'<rect x="'+(W*0.38)+'" y="28" width="24" height="1.5" rx="0.6" fill="rgba(255,255,255,0.25)"/>'
                +'<rect x="'+(W*0.38)+'" y="33" width="26" height="1.5" rx="0.6" fill="rgba(255,255,255,0.2)"/>'
                +'<rect x="'+(W*0.38)+'" y="38" width="20" height="1.5" rx="0.6" fill="rgba(255,255,255,0.15)"/>'
                +'<rect x="'+(W*0.1)+'" y="'+(H*0.87)+'" width="30" height="4" rx="0.5" fill="rgba(201,168,76,0.15)"/>'
                +'</svg>';
        case 'stripe':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#f5f7fa"/>'
                +'<rect x="0" y="0" width="'+W+'" height="'+(H*0.18)+'" fill="'+pri+'"/>'
                +'<rect x="0" y="'+(H*0.18)+'" width="'+W+'" height="'+(H*0.64)+'" fill="#fff"/>'
                +'<rect x="0" y="'+(H*0.82)+'" width="'+W+'" height="'+(H*0.18)+'" fill="'+acc+'"/>'
                +'<circle cx="'+(W*0.22)+'" cy="'+(H*0.5)+'" r="12" fill="'+pri+'20" stroke="'+pri+'" stroke-width="1.5"/>'
                +'<rect x="'+(W*0.38)+'" y="'+(H*0.22)+'" width="32" height="3.5" rx="1.5" fill="'+pri+'" opacity="0.88"/>'
                +'<rect x="'+(W*0.38)+'" y="'+(H*0.32)+'" width="22" height="2" rx="1" fill="#888"/>'
                +'<rect x="'+(W*0.38)+'" y="'+(H*0.42)+'" width="28" height="1.8" rx="0.7" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.38)+'" y="'+(H*0.50)+'" width="24" height="1.8" rx="0.7" fill="#555" opacity="0.45"/>'
                +'<rect x="'+(W*0.38)+'" y="'+(H*0.58)+'" width="26" height="1.8" rx="0.7" fill="#555" opacity="0.4"/>'
                +'<rect x="'+(W*0.38)+'" y="'+(H*0.66)+'" width="20" height="1.8" rx="0.7" fill="#555" opacity="0.35"/>'
                +'</svg>';
        case 'metro':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#fff"/>'
                +'<rect x="0" y="0" width="'+(W*0.35)+'" height="'+H+'" fill="'+pri+'"/>'
                +'<rect x="'+(W*0.35)+'" y="0" width="'+(W*0.04)+'" height="'+H+'" fill="'+acc+'"/>'
                +'<circle cx="'+(W*0.175)+'" cy="'+(H*0.42)+'" r="10" fill="rgba(255,255,255,0.2)" stroke="rgba(255,255,255,0.6)" stroke-width="1"/>'
                +'<rect x="5" y="'+(H*0.72)+'" width="'+(W*0.26)+'" height="3" rx="1" fill="rgba(255,255,255,0.8)"/>'
                +'<rect x="5" y="'+(H*0.80)+'" width="'+(W*0.22)+'" height="2" rx="0.8" fill="rgba(255,255,255,0.5)"/>'
                +'<rect x="'+(W*0.45)+'" y="10" width="30" height="3.5" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+(W*0.45)+'" y="16" width="20" height="2" rx="1" fill="#aaa"/>'
                +'<rect x="'+(W*0.45)+'" y="24" width="28" height="1.8" rx="0.7" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.45)+'" y="29" width="24" height="1.8" rx="0.7" fill="#555" opacity="0.45"/>'
                +'<rect x="'+(W*0.45)+'" y="34" width="26" height="1.8" rx="0.7" fill="#555" opacity="0.4"/>'
                +'<rect x="'+(W*0.45)+'" y="39" width="20" height="1.8" rx="0.7" fill="#555" opacity="0.35"/>'
                +'</svg>';
        case 'glass':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<defs><linearGradient id="glbg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="'+pri+'"/><stop offset="100%" stop-color="'+acc+'"/></linearGradient></defs>'
                +'<rect width="'+W+'" height="'+H+'" fill="url(#glbg)"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.1)+'" width="'+(W*0.84)+'" height="'+(H*0.8)+'" rx="5" fill="rgba(255,255,255,0.18)" stroke="rgba(255,255,255,0.35)" stroke-width="0.7"/>'
                +'<circle cx="'+(W*0.28)+'" cy="'+(H*0.46)+'" r="11" fill="rgba(255,255,255,0.2)" stroke="rgba(255,255,255,0.6)" stroke-width="1"/>'
                +'<rect x="'+(W*0.42)+'" y="'+(H*0.17)+'" width="30" height="3.5" rx="1.5" fill="#fff" opacity="0.92"/>'
                +'<rect x="'+(W*0.42)+'" y="'+(H*0.28)+'" width="22" height="2" rx="1" fill="rgba(255,255,255,0.65)"/>'
                +'<rect x="'+(W*0.42)+'" y="'+(H*0.38)+'" width="28" height="1.5" rx="0.6" fill="rgba(255,255,255,0.4)"/>'
                +'<rect x="'+(W*0.42)+'" y="'+(H*0.45)+'" width="24" height="1.5" rx="0.6" fill="rgba(255,255,255,0.35)"/>'
                +'<rect x="'+(W*0.42)+'" y="'+(H*0.52)+'" width="26" height="1.5" rx="0.6" fill="rgba(255,255,255,0.3)"/>'
                +'<rect x="'+(W*0.42)+'" y="'+(H*0.59)+'" width="20" height="1.5" rx="0.6" fill="rgba(255,255,255,0.25)"/>'
                +'<rect x="'+(W*0.14)+'" y="'+(H*0.82)+'" width="28" height="4" rx="0.5" fill="rgba(255,255,255,0.2)"/>'
                +'</svg>';
        case 'zigzag':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#f7f8fc"/>'
                +'<path d="M0,0 L'+W+',0 L'+W+','+(H*0.42)+' '
                +' L'+(W*0.9)+','+(H*0.35)+' L'+(W*0.8)+','+(H*0.42)+' L'+(W*0.7)+','+(H*0.35)+' L'+(W*0.6)+','+(H*0.42)+' L'+(W*0.5)+','+(H*0.35)+' L'+(W*0.4)+','+(H*0.42)+' L'+(W*0.3)+','+(H*0.35)+' L'+(W*0.2)+','+(H*0.42)+' L'+(W*0.1)+','+(H*0.35)+' L0,'+(H*0.42)+' Z" fill="'+pri+'"/>'
                +'<circle cx="'+(W*0.5)+'" cy="'+(H*0.46)+'" r="8" fill="#fff" stroke="'+pri+'" stroke-width="1.5"/>'
                +'<rect x="'+(W*0.05)+'" y="'+(H*0.6)+'" width="'+(W*0.55)+'" height="3.5" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+(W*0.05)+'" y="'+(H*0.68)+'" width="'+(W*0.4)+'" height="2" rx="1" fill="#aaa"/>'
                +'<rect x="'+(W*0.05)+'" y="'+(H*0.76)+'" width="'+(W*0.5)+'" height="1.8" rx="0.7" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.05)+'" y="'+(H*0.83)+'" width="'+(W*0.45)+'" height="1.8" rx="0.7" fill="#555" opacity="0.45"/>'
                +'<rect x="'+(W*0.62)+'" y="'+(H*0.62)+'" width="28" height="5" rx="0.5" fill="#eee"/>'
                +'</svg>';
        case 'ribbon':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#111827"/>'
                +'<polygon points="0,'+(H*0.28)+' '+W+','+(H*0.15)+' '+W+','+(H*0.5)+' 0,'+(H*0.63)+'" fill="'+pri+'" opacity="0.92"/>'
                +'<polygon points="0,'+(H*0.32)+' '+W+','+(H*0.19)+' '+W+','+(H*0.54)+' 0,'+(H*0.67)+'" fill="'+acc+'" opacity="0.6"/>'
                +'<circle cx="'+(W*0.2)+'" cy="'+(H*0.4)+'" r="10" fill="rgba(255,255,255,0.18)" stroke="rgba(255,255,255,0.6)" stroke-width="1"/>'
                +'<rect x="'+(W*0.36)+'" y="'+(H*0.22)+'" width="28" height="3" rx="1" fill="#fff" opacity="0.92"/>'
                +'<rect x="'+(W*0.36)+'" y="'+(H*0.3)+'" width="20" height="2" rx="0.8" fill="rgba(255,255,255,0.7)"/>'
                +'<rect x="'+(W*0.05)+'" y="'+(H*0.74)+'" width="24" height="1.8" rx="0.7" fill="rgba(255,255,255,0.25)"/>'
                +'<rect x="'+(W*0.05)+'" y="'+(H*0.81)+'" width="20" height="1.8" rx="0.7" fill="rgba(255,255,255,0.2)"/>'
                +'<rect x="'+(W*0.05)+'" y="'+(H*0.88)+'" width="28" height="4" rx="0.5" fill="rgba(255,255,255,0.1)"/>'
                +'</svg>';
        // ── New portrait thumbnails ────────────────────────────────────────────
        case 'v_ribbon':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#f7f8fc"/>'
                +'<rect x="0" y="0" width="'+W+'" height="'+(H*0.2)+'" fill="'+pri+'"/>'
                +'<rect x="0" y="'+(H*0.28)+'" width="'+W+'" height="'+(H*0.14)+'" fill="'+acc+'"/>'
                +'<rect x="0" y="'+(H*0.42)+'" width="'+W+'" height="'+(H*0.04)+'" fill="'+pri+'22"/>'
                +'<circle cx="'+(W*0.5)+'" cy="'+(H*0.28)+'" r="'+(W*0.17)+'" fill="#fff" stroke="'+pri+'" stroke-width="1.5"/>'
                +'<rect x="'+(W*0.1)+'" y="'+(H*0.5)+'" width="'+(W*0.8)+'" height="4" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+(W*0.15)+'" y="'+(H*0.57)+'" width="'+(W*0.7)+'" height="2.5" rx="1" fill="#aaa"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.63)+'" width="'+(W*0.84)+'" height="2" rx="0.8" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.68)+'" width="'+(W*0.78)+'" height="2" rx="0.8" fill="#555" opacity="0.45"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.73)+'" width="'+(W*0.70)+'" height="2" rx="0.8" fill="#555" opacity="0.4"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.78)+'" width="'+(W*0.65)+'" height="2" rx="0.8" fill="#555" opacity="0.35"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.83)+'" width="'+(W*0.60)+'" height="2" rx="0.8" fill="#555" opacity="0.3"/>'
                +'<rect x="'+(W*0.1)+'" y="'+(H*0.93)+'" width="'+(W*0.8)+'" height="4" rx="0.5" fill="#ddd"/>'
                +'</svg>';
        case 'v_arch':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#ffffff"/>'
                +'<path d="M0,0 L'+W+',0 L'+W+','+(H*0.5)+' Q'+W+','+(H*0.65)+' '+(W*0.5)+','+(H*0.65)+' Q0,'+(H*0.65)+' 0,'+(H*0.5)+' Z" fill="'+pri+'"/>'
                +'<path d="M0,0 L'+W+',0 L'+W+','+(H*0.38)+' Q'+W+','+(H*0.52)+' '+(W*0.5)+','+(H*0.52)+' Q0,'+(H*0.52)+' 0,'+(H*0.38)+' Z" fill="rgba(255,255,255,0.1)"/>'
                +'<circle cx="'+(W*0.5)+'" cy="'+(H*0.28)+'" r="'+(W*0.18)+'" fill="rgba(255,255,255,0.25)" stroke="rgba(255,255,255,0.7)" stroke-width="1.2"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.68)+'" width="'+(W*0.84)+'" height="4" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+(W*0.13)+'" y="'+(H*0.75)+'" width="'+(W*0.74)+'" height="2.5" rx="1" fill="#aaa"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.81)+'" width="'+(W*0.84)+'" height="2" rx="0.8" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.86)+'" width="'+(W*0.78)+'" height="2" rx="0.8" fill="#555" opacity="0.45"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.91)+'" width="'+(W*0.70)+'" height="2" rx="0.8" fill="#555" opacity="0.4"/>'
                +'<rect x="'+(W*0.1)+'" y="'+(H*0.95)+'" width="'+(W*0.8)+'" height="4" rx="0.5" fill="#ddd"/>'
                +'</svg>';
        case 'v_diamond':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#f7f8fc"/>'
                +'<rect x="0" y="0" width="'+W+'" height="'+(H*0.5)+'" fill="'+pri+'"/>'
                +'<polygon points="'+(W*0.5)+','+(H*0.52)+' '+W+','+(H*0.38)+' '+(W*0.5)+','+(H*0.24)+' 0,'+(H*0.38)+'" fill="'+acc+'" opacity="0.8"/>'
                +'<circle cx="'+(W*0.5)+'" cy="'+(H*0.38)+'" r="'+(W*0.14)+'" fill="rgba(255,255,255,0.25)" stroke="#fff" stroke-width="1.2"/>'
                +'<circle cx="3" cy="3" r="2.5" fill="rgba(255,255,255,0.25)"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.57)+'" width="'+(W*0.84)+'" height="4" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+(W*0.13)+'" y="'+(H*0.64)+'" width="'+(W*0.74)+'" height="2.5" rx="1" fill="#aaa"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.70)+'" width="'+(W*0.84)+'" height="2" rx="0.8" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.75)+'" width="'+(W*0.78)+'" height="2" rx="0.8" fill="#555" opacity="0.45"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.80)+'" width="'+(W*0.70)+'" height="2" rx="0.8" fill="#555" opacity="0.4"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.85)+'" width="'+(W*0.65)+'" height="2" rx="0.8" fill="#555" opacity="0.35"/>'
                +'<rect x="'+(W*0.1)+'" y="'+(H*0.93)+'" width="'+(W*0.8)+'" height="4" rx="0.5" fill="#ddd"/>'
                +'</svg>';
        case 'v_corner':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#ffffff"/>'
                +'<polygon points="0,0 '+(W*0.65)+',0 0,'+(H*0.55)+'" fill="'+pri+'"/>'
                +'<polygon points="'+W+','+H+' '+(W*0.35)+','+H+' '+W+','+(H*0.45)+'" fill="'+acc+'" opacity="0.85"/>'
                +'<circle cx="'+(W*0.5)+'" cy="'+(H*0.32)+'" r="'+(W*0.17)+'" fill="rgba(255,255,255,0.2)" stroke="rgba(255,255,255,0.7)" stroke-width="1.2"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.54)+'" width="'+(W*0.84)+'" height="4" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+(W*0.13)+'" y="'+(H*0.61)+'" width="'+(W*0.74)+'" height="2.5" rx="1" fill="#aaa"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.67)+'" width="'+(W*0.84)+'" height="2" rx="0.8" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.72)+'" width="'+(W*0.78)+'" height="2" rx="0.8" fill="#555" opacity="0.45"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.77)+'" width="'+(W*0.70)+'" height="2" rx="0.8" fill="#555" opacity="0.4"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.82)+'" width="'+(W*0.65)+'" height="2" rx="0.8" fill="#555" opacity="0.35"/>'
                +'<rect x="'+(W*0.1)+'" y="'+(H*0.93)+'" width="'+(W*0.8)+'" height="4" rx="0.5" fill="#ddd"/>'
                +'</svg>';
        case 'v_dual':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#f7f8fc"/>'
                +'<rect x="0" y="0" width="'+W+'" height="'+(H*0.22)+'" fill="'+pri+'"/>'
                +'<rect x="0" y="'+(H*0.78)+'" width="'+W+'" height="'+(H*0.22)+'" fill="'+acc+'"/>'
                +'<circle cx="3" cy="3" r="2.5" fill="rgba(255,255,255,0.25)"/>'
                +'<circle cx="'+(W*0.5)+'" cy="'+(H*0.4)+'" r="'+(W*0.18)+'" fill="#fff" stroke="'+pri+'" stroke-width="1.5"/>'
                +'<rect x="'+(W*0.1)+'" y="'+(H*0.6)+'" width="'+(W*0.8)+'" height="3.5" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+(W*0.15)+'" y="'+(H*0.66)+'" width="'+(W*0.7)+'" height="2.5" rx="1" fill="#aaa"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.71)+'" width="'+(W*0.84)+'" height="2" rx="0.8" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.75)+'" width="'+(W*0.78)+'" height="2" rx="0.8" fill="#555" opacity="0.45"/>'
                +'</svg>';
        case 'v_stripe':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<defs><linearGradient id="vsg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="'+pri+'"/><stop offset="100%" stop-color="'+acc+'"/></linearGradient></defs>'
                +'<rect width="'+W+'" height="'+H+'" fill="url(#vsg)"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.05)+'" width="'+(W*0.84)+'" height="'+(H*0.88)+'" rx="4" fill="rgba(255,255,255,0.22)" stroke="rgba(255,255,255,0.4)" stroke-width="0.6"/>'
                +'<circle cx="'+(W*0.5)+'" cy="'+(H*0.3)+'" r="'+(W*0.18)+'" fill="rgba(255,255,255,0.2)" stroke="rgba(255,255,255,0.7)" stroke-width="1.2"/>'
                +'<rect x="'+(W*0.12)+'" y="'+(H*0.52)+'" width="'+(W*0.76)+'" height="4" rx="1.5" fill="#fff" opacity="0.92"/>'
                +'<rect x="'+(W*0.17)+'" y="'+(H*0.59)+'" width="'+(W*0.66)+'" height="2.5" rx="1" fill="rgba(255,255,255,0.65)"/>'
                +'<rect x="'+(W*0.12)+'" y="'+(H*0.65)+'" width="'+(W*0.76)+'" height="2" rx="0.8" fill="rgba(255,255,255,0.4)"/>'
                +'<rect x="'+(W*0.12)+'" y="'+(H*0.70)+'" width="'+(W*0.68)+'" height="2" rx="0.8" fill="rgba(255,255,255,0.35)"/>'
                +'<rect x="'+(W*0.12)+'" y="'+(H*0.75)+'" width="'+(W*0.60)+'" height="2" rx="0.8" fill="rgba(255,255,255,0.3)"/>'
                +'<rect x="'+(W*0.12)+'" y="'+(H*0.80)+'" width="'+(W*0.56)+'" height="2" rx="0.8" fill="rgba(255,255,255,0.25)"/>'
                +'<rect x="'+(W*0.12)+'" y="'+(H*0.87)+'" width="'+(W*0.76)+'" height="4" rx="0.5" fill="rgba(255,255,255,0.15)"/>'
                +'</svg>';
        case 'v_badge':
            return '<svg viewBox="'+vb+'" xmlns="http://www.w3.org/2000/svg">'
                +'<rect width="'+W+'" height="'+H+'" fill="#f7f8fc"/>'
                +'<path d="M'+(W*0.08)+',0 L'+(W*0.92)+',0 L'+W+','+(H*0.06)+' L'+W+','+(H*0.42)+' Q'+W+','+(H*0.56)+' '+(W*0.5)+','+(H*0.58)+' Q0,'+(H*0.56)+' 0,'+(H*0.42)+' L0,'+(H*0.06)+' Z" fill="'+pri+'"/>'
                +'<path d="M'+(W*0.08)+',0 L'+(W*0.92)+',0 L'+W+','+(H*0.06)+' L'+W+','+(H*0.3)+' Q'+W+','+(H*0.44)+' '+(W*0.5)+','+(H*0.44)+' Q0,'+(H*0.44)+' 0,'+(H*0.3)+' L0,'+(H*0.06)+' Z" fill="rgba(255,255,255,0.1)"/>'
                +'<circle cx="3" cy="3" r="2.5" fill="rgba(255,255,255,0.25)"/>'
                +'<circle cx="'+(W*0.5)+'" cy="'+(H*0.3)+'" r="'+(W*0.16)+'" fill="rgba(255,255,255,0.25)" stroke="rgba(255,255,255,0.75)" stroke-width="1.2"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.62)+'" width="'+(W*0.84)+'" height="4" rx="1.5" fill="'+pri+'" opacity="0.85"/>'
                +'<rect x="'+(W*0.13)+'" y="'+(H*0.69)+'" width="'+(W*0.74)+'" height="2.5" rx="1" fill="#aaa"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.75)+'" width="'+(W*0.84)+'" height="2" rx="0.8" fill="#555" opacity="0.5"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.80)+'" width="'+(W*0.78)+'" height="2" rx="0.8" fill="#555" opacity="0.45"/>'
                +'<rect x="'+(W*0.08)+'" y="'+(H*0.85)+'" width="'+(W*0.70)+'" height="2" rx="0.8" fill="#555" opacity="0.4"/>'
                +'<rect x="'+(W*0.1)+'" y="'+(H*0.93)+'" width="'+(W*0.8)+'" height="4" rx="0.5" fill="#ddd"/>'
                +'</svg>';
        default:
            return '<svg viewBox="'+vb+'"><rect width="'+W+'" height="'+H+'" fill="'+pri+'"/></svg>';
    }
}

// =============================================================================
//  Style picker builder
// =============================================================================
function buildStylePicker() {
    var pri    = document.getElementById('primaryColor').value || '#1e40af';
    var acc    = document.getElementById('accentColor').value  || '#3b82f6';
    var styles = getFilteredStyles();
    var picker = document.getElementById('stylePicker');
    picker.innerHTML = styles.map(function(s) {
        var isPrt   = s.portrait;
        var thumb   = buildStyleThumbnail(s.key, pri, acc, isPrt);
        var isActive = s.key === currentStyle;
        return '<div>'
            +'<div class="style-card'+(isActive?' active':'')+(isPrt?' portrait':'')+'" id="styleCard_'+s.key+'" onclick="selectStyle(\''+s.key+'\')" title="'+s.label+'">'
            +thumb+'</div>'
            +'<div class="style-label'+(isActive?' active':'')+'" id="styleLabel_'+s.key+'">'+s.label+'</div>'
            +'</div>';
    }).join('');
}

function updateStyleThumbnails() {
    var pri   = document.getElementById('primaryColor').value || '#1e40af';
    var acc   = document.getElementById('accentColor').value  || '#3b82f6';
    getFilteredStyles().forEach(function(s) {
        var el = document.getElementById('styleCard_'+s.key);
        if (el) el.innerHTML = buildStyleThumbnail(s.key, pri, acc, s.portrait);
    });
}

// =============================================================================
//  Template selector
// =============================================================================
function selectTemplate(key) {
    if (!TEMPLATES[key]) return;
    currentTpl = key;
    var tpl    = TEMPLATES[key];
    document.getElementById('template_key').value = key;

    var sel = document.getElementById('categorySelect');
    if (sel) sel.value = key;

    // Save existing field values BEFORE clearing (Fix 5)
    var savedValues = {};
    var container = document.getElementById('dynamicFields');
    container.querySelectorAll('input[type=text]').forEach(function(inp) {
        if (inp.id && inp.value) savedValues[inp.id] = inp.value;
    });

    // Rebuild dynamic fields as 2-column grid (Fix 3)
    container.innerHTML = '<div class="grid grid-2" style="gap:8px 12px;">';
    (tpl.fields || []).forEach(function(field) {
        if (field === 'photo') return;
        var label = FIELD_LABELS[field] || field.replace(/_/g,' ');
        var savedVal = savedValues['field_'+field] || '';
        container.querySelector('.grid').innerHTML +=
            '<div class="form-group" style="margin-bottom:0;">'
            +'<label class="form-label" style="font-size:0.72rem;" for="field_'+field+'">'+label+'</label>'
            +'<input type="text" id="field_'+field+'" name="'+field+'" class="form-input" '
            +'style="padding:6px 10px;font-size:0.82rem;" placeholder="'+label.toLowerCase()+'" value="'+savedVal+'" oninput="updatePreview()">'
            +'</div>';
    });
    container.innerHTML += '</div>';

    // Do NOT reset colours when switching category (Fix 1)
    document.getElementById('logoWrap').style.display = tpl.logo ? '' : 'none';
    document.getElementById('previewTplName').textContent = tpl.name;

    // Sync theme colour dots
    document.querySelectorAll('.tpl-theme-dot').forEach(function(d) {
        d.classList.toggle('active', d.dataset.tpl === key);
    });

    // Orientation badge
    var oriEl = document.getElementById('previewOrientation');
    if (oriEl) oriEl.textContent = '';

    // Update template fields tag list
    var fieldsList = document.getElementById('tplFieldsList');
    fieldsList.innerHTML = (tpl.fields||[]).map(function(f){
        var lbl = FIELD_LABELS[f] || f;
        return '<span style="padding:3px 9px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:12px;font-size:0.7rem;color:var(--text-secondary);">'+lbl+'</span>';
    }).join('');

    // Keep current style but update preview orientation
    var previewEl = document.getElementById('cardPreview');
    if (isPortraitStyle(currentStyle)) { previewEl.classList.add('portrait'); } else { previewEl.classList.remove('portrait'); }

    buildStylePicker();
    updatePreview();
    // Refresh inline bulk sample CSV link if bulk mode is open
    if (document.getElementById('bulkModeToggle') && document.getElementById('bulkModeToggle').checked) {
        updateInlineSampleCsv();
    }
}

// =============================================================================
//  Style selector
// =============================================================================
function selectStyle(key) {
    currentStyle = key;
    document.getElementById('design_style').value = key;
    getFilteredStyles().forEach(function(s) {
        var card  = document.getElementById('styleCard_'+s.key);
        var label = document.getElementById('styleLabel_'+s.key);
        if (card)  card.classList.toggle('active', s.key===key);
        if (label) label.classList.toggle('active', s.key===key);
    });
    var styleDef = ALL_STYLES.find(function(s){ return s.key===key; });
    document.getElementById('previewStyleName').textContent = styleDef ? ('· '+styleDef.label) : '';
    // Update preview aspect ratio to match style orientation
    var previewEl = document.getElementById('cardPreview');
    if (styleDef && styleDef.portrait) {
        previewEl.classList.add('portrait');
    } else {
        previewEl.classList.remove('portrait');
    }
    updatePreview();
}

// =============================================================================
//  Card renderers — LANDSCAPE
// =============================================================================
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

// =============================================================================
//  Main preview updater
// =============================================================================
function updatePreview() {
    var v       = getCardValues();
    updateStyleThumbnails();
    var preview = document.getElementById('cardPreview');
    preview.style.fontFamily = "'"+v.font+"',sans-serif";
    var html = '';
    switch (currentStyle) {
        case 'sidebar':       html = renderSidebar(v);      break;
        case 'wave':          html = renderWave(v);         break;
        case 'bold_header':   html = renderBoldHeader(v);   break;
        case 'diagonal':      html = renderDiagonal(v);     break;
        case 'gradient_pro':  html = renderGradientPro(v);  break;
        case 'neon':          html = renderNeon(v);         break;
        case 'executive':     html = renderExecutive(v);    break;
        case 'stripe':        html = renderStripe(v);       break;
        case 'metro':         html = renderMetro(v);        break;
        case 'glass':         html = renderGlass(v);        break;
        case 'zigzag':        html = renderZigzag(v);       break;
        case 'ribbon':        html = renderRibbon(v);       break;
        case 'v_sharp':       html = renderVSharp(v);       break;
        case 'v_curve':       html = renderVCurve(v);       break;
        case 'v_hex':         html = renderVHex(v);         break;
        case 'v_circle':      html = renderVCircle(v);      break;
        case 'v_split':       html = renderVSplit(v);       break;
        case 'v_ribbon':      html = renderVRibbon(v);      break;
        case 'v_arch':        html = renderVArch(v);        break;
        case 'v_diamond':     html = renderVDiamond(v);     break;
        case 'v_corner':      html = renderVCorner(v);      break;
        case 'v_dual':        html = renderVDual(v);        break;
        case 'v_stripe':      html = renderVStripe(v);      break;
        case 'v_badge':       html = renderVBadge(v);       break;
        default:              html = renderClassic(v);      break;
    }
    preview.innerHTML = html;
    // Sync mobile preview modal if open
    var mob = document.getElementById('mobileCardPreview');
    if (mob) {
        mob.innerHTML = html;
        mob.style.fontFamily = "'"+v.font+"',sans-serif";
        if (isPortraitStyle(currentStyle)) {
            mob.classList.add('portrait');
            mob.style.aspectRatio = '54/85.6'; mob.style.maxWidth = '200px';
        } else {
            mob.classList.remove('portrait');
            mob.style.aspectRatio = '85.6/54'; mob.style.maxWidth = '340px';
        }
    }
    setTimeout(renderQRCode, 10);
}

// =============================================================================
//  Photo preview
// =============================================================================
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) { photoDataUrl = e.target.result; updatePreview(); };
        reader.readAsDataURL(input.files[0]);
    }
}

// =============================================================================
//  AI Suggestions (AJAX)
// =============================================================================
var _aiFieldSuggestions = {};
var _aiColorSuggestions = {};

function getAISuggestions() {
    var prompt = document.getElementById('aiPrompt').value.trim();
    var out    = document.getElementById('aiOutput');
    out.style.display = 'block';
    out.innerHTML = '<div style="text-align:center;padding:12px;"><div class="spinner"></div><p style="font-size:0.75rem;margin-top:6px;color:var(--text-secondary);">Generating suggestions...</p></div>';

    // Hide action buttons while loading
    document.getElementById('applyFieldsBtn').style.display = 'none';
    document.getElementById('applyColorsBtn').style.display = 'none';
    _aiFieldSuggestions = {};
    _aiColorSuggestions = {};

    var cardData = {};
    var tpl = TEMPLATES[currentTpl] || {};
    (tpl.fields||[]).forEach(function(f){ var el=document.getElementById('field_'+f); if(el) cardData[f]=el.value; });
    var fd = new FormData();
    fd.append('_token', CSRF_TOKEN);
    fd.append('template_key', currentTpl);
    fd.append('prompt', prompt);
    Object.keys(cardData).forEach(function(k){ fd.append('card_data['+k+']', cardData[k]); });
    fetch('/projects/idcard/ai-suggest',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:fd})
    .then(function(r){ return r.json(); })
    .then(function(data){
        if(!data.success){out.innerHTML='<div class="ai-suggestion">Could not get suggestions. Try again.</div>';return;}
        var s = data.suggestions || {};
        var html = '';

        // Badge: AI-powered vs rule-based
        if (s.ai_powered) {
            html += '<div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">'
                  + '<span style="background:linear-gradient(135deg,#6366f1,#10b981);color:#fff;font-size:0.65rem;padding:2px 8px;border-radius:10px;font-weight:700;"><i class="fas fa-bolt"></i> OpenAI</span>'
                  + '<span style="font-size:0.72rem;color:var(--text-secondary);">AI-powered suggestions</span>'
                  + '</div>';
        }

        if(s.template_tip)   html+='<div class="ai-suggestion"><strong>💡 Tip:</strong> '+escAI(s.template_tip)+'</div>';
        if(s.missing_fields) html+='<div class="ai-suggestion"><strong>✅ Completeness:</strong> '+escAI(s.missing_fields)+'</div>';
        if(s.design_tips&&s.design_tips.length) s.design_tips.forEach(function(t){html+='<div class="ai-suggestion"><strong>🎨 Design:</strong> '+escAI(t)+'</div>';});
        if(s.prompt_hint && s.prompt_hint.length) html+='<div class="ai-suggestion"><strong>🤖 AI:</strong> '+escAI(s.prompt_hint)+'</div>';
        if(s.ai_text && s.ai_text.length)         html+='<div class="ai-suggestion">'+escAI(s.ai_text)+'</div>';

        // Field suggestions preview table
        var fieldKeys = Object.keys(s.field_suggestions || {});
        if (fieldKeys.length > 0) {
            _aiFieldSuggestions = s.field_suggestions;
            html += '<div style="margin-top:10px;padding:10px;background:rgba(99,102,241,0.08);border-radius:8px;border:1px solid rgba(99,102,241,0.2);">'
                  + '<div style="font-size:0.75rem;font-weight:700;color:var(--indigo);margin-bottom:8px;"><i class="fas fa-fill-drip"></i> AI-Generated Field Values</div>';
            fieldKeys.forEach(function(fk) {
                var label = (FIELD_LABELS[fk] || fk);
                html += '<div style="display:flex;justify-content:space-between;align-items:center;padding:3px 0;border-bottom:1px solid rgba(99,102,241,0.1);">'
                      + '<span style="font-size:0.72rem;color:var(--text-secondary);">' + escAI(label) + '</span>'
                      + '<span style="font-size:0.76rem;font-weight:600;color:var(--text-primary);max-width:60%;text-align:right;word-break:break-word;">' + escAI(s.field_suggestions[fk]) + '</span>'
                      + '</div>';
            });
            html += '</div>';
            document.getElementById('applyFieldsBtn').style.display = 'inline-flex';
        }

        // Color suggestions
        var cs = s.color_suggestions || {};
        if (cs.primary_color || cs.accent_color) {
            _aiColorSuggestions = cs;
            html += '<div style="margin-top:8px;padding:8px 10px;background:rgba(99,102,241,0.06);border-radius:8px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">'
                  + '<span style="font-size:0.72rem;font-weight:700;color:var(--indigo);"><i class="fas fa-palette"></i> Suggested Colors</span>';
            if (cs.primary_color) {
                html += '<span style="display:inline-flex;align-items:center;gap:4px;font-size:0.72rem;color:var(--text-secondary);">'
                      + '<span style="width:14px;height:14px;border-radius:50%;background:'+escAI(cs.primary_color)+';display:inline-block;border:1px solid rgba(0,0,0,0.2);"></span>'
                      + 'Primary: '+escAI(cs.primary_color)+'</span>';
            }
            if (cs.accent_color) {
                html += '<span style="display:inline-flex;align-items:center;gap:4px;font-size:0.72rem;color:var(--text-secondary);">'
                      + '<span style="width:14px;height:14px;border-radius:50%;background:'+escAI(cs.accent_color)+';display:inline-block;border:1px solid rgba(0,0,0,0.2);"></span>'
                      + 'Accent: '+escAI(cs.accent_color)+'</span>';
            }
            html += '</div>';
            document.getElementById('applyColorsBtn').style.display = 'inline-flex';
        }

        out.innerHTML = html || '<div class="ai-suggestion">Looking good! Your card is well structured.</div>';

        // Show action row if any buttons are visible
        var anyBtn = document.getElementById('applyFieldsBtn').style.display !== 'none'
                  || document.getElementById('applyColorsBtn').style.display !== 'none';
        if (anyBtn) {
            document.getElementById('aiActions').style.display = 'flex';
        }

    }).catch(function(){out.innerHTML='<div class="ai-suggestion">Network error. Please try again.</div>';});
}

function applyAIFields() {
    var keys = Object.keys(_aiFieldSuggestions);
    if (!keys.length) return;
    keys.forEach(function(fk) {
        var el = document.getElementById('field_' + fk);
        if (el && _aiFieldSuggestions[fk]) {
            el.value = _aiFieldSuggestions[fk];
            // Briefly highlight the field
            el.style.transition = 'border-color 0.3s, background 0.3s';
            el.style.borderColor = '#6366f1';
            el.style.background  = 'rgba(99,102,241,0.06)';
            setTimeout(function(){ el.style.borderColor = ''; el.style.background = ''; }, 1500);
        }
    });
    updatePreview();
}

function applyAIColors() {
    if (_aiColorSuggestions.primary_color) {
        var pc = document.getElementById('primaryColor');
        if (pc) { pc.value = _aiColorSuggestions.primary_color; }
    }
    if (_aiColorSuggestions.accent_color) {
        var ac = document.getElementById('accentColor');
        if (ac) { ac.value = _aiColorSuggestions.accent_color; }
    }
    updatePreview();
}

function escAI(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}


// =============================================================================
//  Reset & submit
// =============================================================================
function resetForm() { photoDataUrl = null; setTimeout(updatePreview, 50); }

function toggleSection(id) {
    var el = document.getElementById(id);
    if (!el) return;
    var hidden = el.classList.toggle('collapsible-hidden');
    var chevronMap = {
        'dynamicFields': 'infoChevron',
        'styleBody':     'styleChevron',
        'designControls':'controlsChevron'
    };
    var chevronId = chevronMap[id] || null;
    if (chevronId) {
        var ch = document.getElementById(chevronId);
        if (ch) { ch.classList.toggle('fa-chevron-up', !hidden); ch.classList.toggle('fa-chevron-down', hidden); }
    }
}

function openMobilePreview() {
    var modal = document.getElementById('mobilePreviewModal');
    if (!modal) return;
    modal.classList.add('open');
    // Sync name label
    var tplNameEl = document.getElementById('previewTplName');
    var mobLabel  = document.getElementById('mobilePreviewTplName');
    if (tplNameEl && mobLabel) mobLabel.textContent = tplNameEl.textContent;
    updatePreview();
    setTimeout(function() {
        var mob = document.getElementById('mobileCardPreview');
        var slot = mob ? mob.querySelector('.qr-slot') : null;
        if (slot) {
            var showQrEl = document.getElementById('showQr');
            var show = showQrEl && showQrEl.checked;
            if (show) {
                var sizeEl = document.getElementById('qrSize');
                var size = sizeEl ? parseInt(sizeEl.value) : 54;
                var data = buildQRData();
                slot.innerHTML = '';
                try { new QRCode(slot, { text:data, width:size, height:size, correctLevel:QRCode.CorrectLevel.L }); } catch(e) {}
            }
        }
    }, 30);
}

function closeMobilePreview() {
    var modal = document.getElementById('mobilePreviewModal');
    if (modal) modal.classList.remove('open');
}

// Close modal on backdrop click
document.addEventListener('click', function(e) {
    var modal = document.getElementById('mobilePreviewModal');
    if (modal && e.target === modal) closeMobilePreview();
});
document.getElementById('cardForm').addEventListener('submit',function(){
    var btn=document.getElementById('generateBtn');
    btn.disabled=true;
    btn.innerHTML='<div class="spinner"></div> Generating...';
});
['primaryColor','accentColor','bgColor','textColor'].forEach(function(id){
    document.getElementById(id).addEventListener('input', updateStyleThumbnails);
});

// =============================================================================
//  Theme colour apply (dots — colour only, fields untouched)
//  Applies only the primary/accent/bg/text colours from the specified template
//  without modifying form input fields. Distinct from selectTemplate() which
//  also rebuilds the field list.
// =============================================================================
function applyThemeColor(key) {
    var tpl = TEMPLATES[key];
    if (!tpl) return;
    document.getElementById('primaryColor').value = tpl.color  || '#1e40af';
    document.getElementById('accentColor').value  = tpl.accent || '#3b82f6';
    document.getElementById('bgColor').value      = tpl.bg     || '#ffffff';
    document.getElementById('textColor').value    = tpl.text   || '#1e293b';
    document.querySelectorAll('.tpl-theme-dot').forEach(function(d) {
        d.classList.toggle('active', d.dataset.tpl === key);
    });
    updatePreview();
}

// =============================================================================
//  Mobile nav (bottom bar)
//  Scrolls to the target section, expands it if collapsed, and marks the
//  clicked nav button as active.
// =============================================================================
function cxMobileNav(sectionId, btn) {
    // Mobile-only sections: show the selected, hide other mobile-only sections
    var mobileOnlySections = ['mobileCategorySection', 'mobileThemeSection'];
    mobileOnlySections.forEach(function(id) {
        var mobileSectionEl = document.getElementById(id);
        if (mobileSectionEl && window.innerWidth <= 600) {
            mobileSectionEl.style.display = (id === sectionId) ? 'block' : 'none';
        }
    });

    var el = document.getElementById(sectionId);
    if (el) {
        // Expand section if it was collapsed
        if (el.classList.contains('collapsible-hidden')) {
            el.classList.remove('collapsible-hidden');
        }
        // Scroll to the parent card if available, otherwise scroll to the element
        var scrollTarget = (typeof el.closest === 'function' && el.closest('.card')) ? el.closest('.card') : el;
        scrollTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    document.querySelectorAll('.cx-mobile-nav-btn').forEach(function(b){ b.classList.remove('active'); });
    if (btn) btn.classList.add('active');
}

/**
 * Keep both desktop + mobile category selects in sync.
 */
function syncCategorySelects(val) {
    var d = document.getElementById('categorySelect');
    var m = document.getElementById('mobileCategorySelect');
    if (d && d.value !== val) d.value = val;
    if (m && m.value !== val) m.value = val;
}

/**
 * Sync all theme dot sets (desktop + mobile).
 */
function syncThemeDots(tpl) {
    document.querySelectorAll('.tpl-theme-dot').forEach(function(dot) {
        dot.classList.toggle('active', dot.getAttribute('data-tpl') === tpl);
    });
}

// =============================================================================
//  Inline Bulk Mode
// =============================================================================
var inlineBulkCsvSelected = false;
var inlineDroppedFile     = null;

function toggleBulkMode() {
    var toggle = document.getElementById('bulkModeToggle');
    var panel  = document.getElementById('inlineBulkPanel');
    if (toggle.checked) {
        panel.classList.add('visible');
        updateInlineSampleCsv();
    } else {
        panel.classList.remove('visible');
        inlineBulkCsvSelected = false;
        inlineDroppedFile     = null;
        document.getElementById('inlineCsvFilename').textContent = '';
        document.getElementById('inlineBulkResult').style.display   = 'none';
        document.getElementById('inlineBulkProgress').style.display = 'none';
    }
}

function updateInlineSampleCsv() {
    var btn = document.getElementById('inlineSampleCsvBtn');
    if (!btn) return;
    btn.href = '/projects/idcard/bulk/sample-csv?template=' + encodeURIComponent(currentTpl || 'corporate');
    btn.style.pointerEvents = 'auto';
    btn.style.opacity = '1';
}

function inlineDragOver(e)  { e.preventDefault(); document.getElementById('inlineUploadZone').classList.add('dragover'); }
function inlineDragLeave(e) { document.getElementById('inlineUploadZone').classList.remove('dragover'); }
function inlineDrop(e) {
    e.preventDefault();
    document.getElementById('inlineUploadZone').classList.remove('dragover');
    if (e.dataTransfer.files.length) {
        var file = e.dataTransfer.files[0];
        document.getElementById('inlineCsvFilename').textContent = '📎 ' + file.name;
        inlineBulkCsvSelected = true;
        inlineDroppedFile     = file;
    }
}
function inlineFileSelected(input) {
    if (input.files && input.files[0]) {
        document.getElementById('inlineCsvFilename').textContent = '📎 ' + input.files[0].name;
        inlineBulkCsvSelected = true;
        inlineDroppedFile     = null;
    }
}

function submitInlineBulk() {
    if (!inlineBulkCsvSelected) {
        document.getElementById('inlineBulkHint').textContent = '⚠ Please upload a CSV file first.';
        document.getElementById('inlineBulkHint').style.color = '#ef4444';
        return;
    }
    var btn = document.getElementById('inlineBulkBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner"></div> Generating…';
    var prog = document.getElementById('inlineBulkProgress');
    prog.style.display = 'block';
    document.getElementById('inlineBulkProgressBar').style.width = '25%';
    document.getElementById('inlineBulkProgressLabel').textContent = 'Uploading CSV and creating cards…';
    document.getElementById('inlineBulkResult').style.display = 'none';

    var fd = new FormData();
    fd.append('_token',        CSRF_TOKEN);
    fd.append('template_key',  currentTpl || 'corporate');
    fd.append('primary_color', document.getElementById('primaryColor').value);
    fd.append('accent_color',  document.getElementById('accentColor').value);
    fd.append('bg_color',      document.getElementById('bgColor').value);
    fd.append('text_color',    document.getElementById('textColor').value);
    fd.append('font_family',   document.getElementById('fontFamily').value);
    fd.append('profile_shape', document.getElementById('profileShape').value);
    fd.append('design_style',  document.getElementById('design_style').value);
    var csvInput = document.getElementById('inlineCsvFile');
    if (inlineDroppedFile) {
        fd.append('csv_file', inlineDroppedFile, inlineDroppedFile.name);
    } else if (csvInput.files && csvInput.files[0]) {
        fd.append('csv_file', csvInput.files[0]);
    }

    fetch('/projects/idcard/bulk/upload', {
        method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}
    })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        document.getElementById('inlineBulkProgressBar').style.width = '100%';
        document.getElementById('inlineBulkProgressLabel').textContent = data.message || (data.success ? 'Done!' : 'Error');
        var res = document.getElementById('inlineBulkResult');
        res.style.display = 'block';
        if (data.success) {
            res.innerHTML = '<div style="padding:12px;background:rgba(0,255,136,0.06);border:1px solid rgba(0,255,136,0.2);border-radius:10px;">'
                +'<div style="font-weight:700;color:#00ff88;margin-bottom:6px;font-size:0.88rem;"><i class="fas fa-check-circle"></i> Generation Complete!</div>'
                +'<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px;">'
                +'<span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:16px;font-size:0.78rem;font-weight:600;background:rgba(0,255,136,0.12);color:#00ff88;"><i class="fas fa-id-card"></i> '+data.completed+' cards created</span>'
                +(data.failed>0?'<span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:16px;font-size:0.78rem;font-weight:600;background:rgba(239,68,68,0.12);color:#ef4444;"><i class="fas fa-exclamation-triangle"></i> '+data.failed+' rows skipped</span>':'')
                +'</div>'
                +'<div style="display:flex;gap:8px;flex-wrap:wrap;">'
                +'<a href="/projects/idcard/bulk/cards" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> View Bulk Cards</a>'
                +'<a href="/projects/idcard/history" class="btn btn-secondary btn-sm"><i class="fas fa-list"></i> All My Cards</a>'
                +'</div></div>';
        } else {
            res.innerHTML = '<div style="padding:10px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.2);border-radius:8px;color:#ef4444;">'
                +'<i class="fas fa-times-circle"></i> '+(data.message||'An error occurred.')+'</div>';
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-bolt"></i> Generate All Cards';
    })
    .catch(function() {
        document.getElementById('inlineBulkProgressLabel').textContent = 'Request failed.';
        var res = document.getElementById('inlineBulkResult');
        res.style.display = 'block';
        res.innerHTML = '<div style="padding:10px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.2);border-radius:8px;color:#ef4444;">'
            +'<i class="fas fa-times-circle"></i> Network error. Please try again.</div>';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-bolt"></i> Generate All Cards';
    });
}

function cxMobileNavBulk(btn) {
    var toggle = document.getElementById('bulkModeToggle');
    if (toggle && !toggle.checked) { toggle.checked = true; toggleBulkMode(); }
    var card = document.getElementById('bulkModeCard');
    if (card) card.scrollIntoView({ behavior:'smooth', block:'start' });
    document.querySelectorAll('.cx-mobile-nav-btn').forEach(function(b){ b.classList.remove('active'); });
    if (btn) btn.classList.add('active');
}

// =============================================================================
//  Init
// =============================================================================
(function init() {
    // When editing, use saved design style; otherwise default to 'classic'
    var savedStyle = document.getElementById('design_style').value || 'classic';
    currentStyle = savedStyle;
    buildStylePicker();
    updatePreview();
    // Update qrSizeVal display
    var qrEl = document.getElementById('qrSize');
    if (qrEl) document.getElementById('qrSizeVal').textContent = qrEl.value;
})();
</script>
