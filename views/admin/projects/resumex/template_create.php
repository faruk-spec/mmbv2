<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <div>
        <h1><i class="fas fa-paint-brush" style="color:var(--cyan);"></i>
            <?= !empty($isOverride) ? 'Override Built-in Template' : 'Template Designer' ?>
        </h1>
        <p style="color:var(--text-secondary);">Design every aspect of a resume template — no PHP code required.</p>
    </div>
    <a href="/admin/projects/resumex/templates" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Templates</a>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger" style="margin-bottom:18px;">
    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<?php if (!empty($isOverride) && !empty($prefill)): ?>
<div class="alert" style="margin-bottom:18px;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.35);border-radius:10px;padding:14px 18px;display:flex;gap:12px;align-items:flex-start;">
    <i class="fas fa-exclamation-triangle" style="color:#f59e0b;margin-top:2px;flex-shrink:0;"></i>
    <div>
        <strong style="color:#f59e0b;">Warning: You are overriding a built-in template</strong><br>
        <span style="font-size:0.85rem;color:var(--text-secondary);">
            Saving with the same key <code><?= htmlspecialchars($prefill['key']) ?></code> will permanently replace the built-in
            <strong><?= htmlspecialchars($prefill['name']) ?></strong> for all users.
            The override is stored as a custom template and can be deleted to restore the original.
        </span>
    </div>
</div>
<?php endif; ?>

<form method="POST" action="/admin/projects/resumex/templates/create" id="designerForm">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <?php if (!empty($isOverride)): ?>
    <input type="hidden" name="is_override" value="1">
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;">

        <!-- ── Left: all fields ── -->
        <div style="display:flex;flex-direction:column;gap:20px;">

            <!-- Identity -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-id-card"></i> Identity</h3></div>
                <div style="padding:18px;display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label class="form-label">Key <span style="color:#f87171;">*</span>
                            <span style="font-size:0.75rem;color:var(--text-secondary);font-weight:400;"> (lowercase letters, digits, hyphens)</span>
                        </label>
                        <input class="form-input" type="text" name="key" required
                               pattern="[a-z0-9\-]+" maxlength="100"
                               placeholder="e.g. my-corporate-blue"
                               value="<?= htmlspecialchars($prefill['key'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="form-label">Display Name <span style="color:#f87171;">*</span></label>
                        <input class="form-input" type="text" name="name" required maxlength="255"
                               placeholder="e.g. Corporate Blue"
                               value="<?= htmlspecialchars($prefill['name'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="form-label">Category <span style="color:#f87171;">*</span></label>
                        <?php $cat = $prefill['category'] ?? 'custom'; ?>
                        <select class="form-input" name="category">
                            <?php foreach (['professional','academic','dark','light','creative','custom','warm'] as $c): ?>
                            <option value="<?= $c ?>" <?= $cat === $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Layout Mode <span style="color:#f87171;">*</span></label>
                        <?php $lm = $prefill['layoutMode'] ?? 'two-column'; ?>
                        <select class="form-input" name="layoutMode">
                            <option value="two-column" <?= $lm==='two-column'?'selected':''?>>Two-Column</option>
                            <option value="single" <?= $lm==='single'?'selected':''?>>Single Column</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Layout Style <span style="color:#f87171;">*</span></label>
                        <?php $ls = $prefill['layoutStyle'] ?? 'minimal'; ?>
                        <select class="form-input" name="layoutStyle">
                            <?php foreach (['sidebar-dark','minimal','academic','timeline','banner','developer','full-header','classic','bold'] as $v): ?>
                            <option value="<?= $v ?>" <?= $ls===$v?'selected':''?>><?= ucwords(str_replace('-',' ',$v)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Colors -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-palette"></i> Colors</h3></div>
                <div style="padding:18px;display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
                    <?php
                    $colorFields = [
                        'primaryColor'    => ['Primary Color',    $prefill['primaryColor']    ?? '#0ea5e9'],
                        'secondaryColor'  => ['Secondary Color',  $prefill['secondaryColor']  ?? '#06b6d4'],
                        'backgroundColor' => ['Background',       $prefill['backgroundColor'] ?? '#0f172a'],
                        'surfaceColor'    => ['Surface / Cards',  $prefill['surfaceColor']    ?? '#1e293b'],
                        'textColor'       => ['Text Color',       $prefill['textColor']       ?? '#e2e8f0'],
                        'textMuted'       => ['Muted Text',       $prefill['textMuted']       ?? '#94a3b8'],
                    ];
                    foreach ($colorFields as $fieldName => [$label, $default]):
                    ?>
                    <div>
                        <label class="form-label"><?= $label ?> <span style="color:#f87171;">*</span></label>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <input type="color" name="<?= $fieldName ?>_pick" value="<?= htmlspecialchars($default) ?>"
                                   oninput="document.getElementById('<?= $fieldName ?>').value=this.value;updatePreview();"
                                   style="width:36px;height:36px;border:none;background:none;cursor:pointer;padding:0;border-radius:6px;overflow:hidden;">
                            <input class="form-input" type="text" name="<?= $fieldName ?>" id="<?= $fieldName ?>"
                                   value="<?= htmlspecialchars($default) ?>" required
                                   pattern="^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$"
                                   placeholder="#rrggbb"
                                   oninput="syncPicker('<?= $fieldName ?>');updatePreview();"
                                   style="flex:1;min-width:0;">
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div style="grid-column:1/-1;">
                        <label class="form-label">Border Color <span style="color:#f87171;">*</span>
                            <span style="font-size:0.75rem;color:var(--text-secondary);font-weight:400;"> (hex or rgba)</span>
                        </label>
                        <input class="form-input" type="text" name="borderColor"
                               value="<?= htmlspecialchars($prefill['borderColor'] ?? 'rgba(14,165,233,0.2)') ?>"
                               placeholder="e.g. #e2e8f0 or rgba(14,165,233,0.2)" required>
                    </div>
                </div>
            </div>

            <!-- Typography -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-font"></i> Typography</h3></div>
                <div style="padding:18px;display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
                    <div>
                        <label class="form-label">Font Family <span style="color:#f87171;">*</span></label>
                        <?php $ff = $prefill['fontFamily'] ?? 'Inter'; ?>
                        <select class="form-input" name="fontFamily">
                            <?php foreach (['Inter','Merriweather','Fira Code','Georgia','Arial','Roboto','Poppins'] as $f): ?>
                            <option value="<?= $f ?>" <?= $ff===$f?'selected':''?>><?= $f ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Font Size (px) <span style="color:#f87171;">*</span></label>
                        <?php $fs = $prefill['fontSize'] ?? '14'; ?>
                        <select class="form-input" name="fontSize">
                            <option value="12" <?= $fs==='12'?'selected':''?>>12</option>
                            <option value="13" <?= $fs==='13'?'selected':''?>>13</option>
                            <option value="14" <?= $fs==='14'?'selected':''?>>14</option>
                            <option value="15" <?= $fs==='15'?'selected':''?>>15</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Font Weight <span style="color:#f87171;">*</span></label>
                        <?php $fw = $prefill['fontWeight'] ?? '400'; ?>
                        <select class="form-input" name="fontWeight">
                            <option value="300" <?= $fw==='300'?'selected':''?>>300 (Light)</option>
                            <option value="400" <?= $fw==='400'?'selected':''?>>400 (Regular)</option>
                            <option value="500" <?= $fw==='500'?'selected':''?>>500 (Medium)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Styles -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-sliders-h"></i> Style Options</h3></div>
                <div style="padding:18px;display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
                    <?php
                    $styleFields = [
                        'headerStyle' => ['Header Style', ['gradient','underline','minimal','solid','banner','neon','classic','bold'], $prefill['headerStyle'] ?? 'gradient'],
                        'buttonStyle' => ['Button Style', ['pill','square','rounded'], $prefill['buttonStyle'] ?? 'pill'],
                        'cardStyle'   => ['Card Style',   ['bordered','flat','shadow','glass'], $prefill['cardStyle'] ?? 'bordered'],
                        'spacing'     => ['Spacing',      ['compact','normal','comfortable','spacious'], $prefill['spacing'] ?? 'compact'],
                        'iconStyle'   => ['Icon Style',   ['filled','outline'], $prefill['iconStyle'] ?? 'filled'],
                    ];
                    foreach ($styleFields as $fn => [$label, $opts, $selected]):
                    ?>
                    <div>
                        <label class="form-label"><?= $label ?> <span style="color:#f87171;">*</span></label>
                        <select class="form-input" name="<?= $fn ?>">
                            <?php foreach ($opts as $o): ?>
                            <option value="<?= $o ?>" <?= $selected===$o?'selected':''?>><?= ucwords(str_replace('-',' ',$o)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endforeach; ?>
                    <div style="display:flex;flex-direction:column;gap:10px;padding-top:20px;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.875rem;">
                            <input type="checkbox" name="accentHighlights" value="1"
                                   <?= !empty($prefill['accentHighlights']) ? 'checked' : '' ?>
                                   style="width:16px;height:16px;accent-color:var(--cyan);">
                            <span>Accent Highlights</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.875rem;">
                            <input type="checkbox" name="animations" value="1"
                                   <?= !empty($prefill['animations']) ? 'checked' : '' ?>
                                   style="width:16px;height:16px;accent-color:var(--cyan);">
                            <span>Enable Animations</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Color Variants -->
            <div class="card">
                <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
                    <h3 class="card-title"><i class="fas fa-swatchbook"></i> Color Variants (1–4)</h3>
                    <button type="button" onclick="addVariant()" class="btn btn-secondary btn-sm">
                        <i class="fas fa-plus"></i> Add Variant
                    </button>
                </div>
                <div style="padding:18px;" id="variantList">
                    <?php
                    $variants = $prefill['colorVariants'] ?? [
                        ['label' => 'Default', 'primary' => '#0ea5e9', 'secondary' => '#06b6d4'],
                    ];
                    foreach ($variants as $vi => $variant):
                    ?>
                    <div class="variant-row" id="variant-<?= $vi ?>" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;align-items:end;margin-bottom:10px;">
                        <div>
                            <?php if ($vi === 0): ?>
                            <label class="form-label">Label</label>
                            <?php endif; ?>
                            <input class="form-input" type="text" name="variant_label[]"
                                   value="<?= htmlspecialchars($variant['label']) ?>"
                                   placeholder="e.g. Blue" required>
                        </div>
                        <div>
                            <?php if ($vi === 0): ?>
                            <label class="form-label">Primary</label>
                            <?php endif; ?>
                            <div style="display:flex;gap:6px;align-items:center;">
                                <input type="color" value="<?= htmlspecialchars($variant['primary']) ?>"
                                       oninput="this.nextElementSibling.value=this.value"
                                       style="width:32px;height:32px;border:none;background:none;cursor:pointer;padding:0;">
                                <input class="form-input" type="text" name="variant_primary[]"
                                       value="<?= htmlspecialchars($variant['primary']) ?>"
                                       placeholder="#rrggbb" required
                                       pattern="^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$"
                                       oninput="this.previousElementSibling.value=this.value"
                                       style="flex:1;min-width:0;">
                            </div>
                        </div>
                        <div>
                            <?php if ($vi === 0): ?>
                            <label class="form-label">Secondary</label>
                            <?php endif; ?>
                            <div style="display:flex;gap:6px;align-items:center;">
                                <input type="color" value="<?= htmlspecialchars($variant['secondary']) ?>"
                                       oninput="this.nextElementSibling.value=this.value"
                                       style="width:32px;height:32px;border:none;background:none;cursor:pointer;padding:0;">
                                <input class="form-input" type="text" name="variant_secondary[]"
                                       value="<?= htmlspecialchars($variant['secondary']) ?>"
                                       placeholder="#rrggbb" required
                                       pattern="^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$"
                                       oninput="this.previousElementSibling.value=this.value"
                                       style="flex:1;min-width:0;">
                            </div>
                        </div>
                        <div>
                            <?php if ($vi === 0): ?>
                            <div style="height:22px;"></div>
                            <?php endif; ?>
                            <?php if ($vi > 0): ?>
                            <button type="button" onclick="removeVariant(<?= $vi ?>)" class="btn btn-danger btn-sm"
                                    style="width:36px;padding:0;text-align:center;">
                                <i class="fas fa-times"></i>
                            </button>
                            <?php else: ?>
                            <div style="width:36px;"></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Submit -->
            <div style="display:flex;gap:10px;margin-bottom:8px;">
                <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;">
                    <i class="fas fa-save"></i>
                    <?= !empty($isOverride) ? 'Save Override Template' : 'Create Template' ?>
                </button>
                <a href="/admin/projects/resumex/templates" class="btn btn-secondary">Cancel</a>
            </div>

        </div>

        <!-- ── Right: Live Preview ── -->
        <div style="position:sticky;top:20px;">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-eye"></i> Live Preview</h3></div>
                <div style="padding:16px;" id="previewPane">
                    <!-- Header -->
                    <div id="pvHeader" style="padding:16px;border-radius:8px;margin-bottom:12px;transition:all 0.2s;">
                        <div style="font-size:1.1rem;font-weight:700;" id="pvName">Your Name</div>
                        <div style="font-size:0.8rem;opacity:0.75;" id="pvTitle">Job Title</div>
                    </div>
                    <!-- Section -->
                    <div id="pvSection" style="margin-bottom:10px;padding-bottom:8px;">
                        <div id="pvSectionTitle" style="font-size:0.7rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding-bottom:4px;border-bottom:2px solid;margin-bottom:6px;opacity:0.9;">Experience</div>
                        <div id="pvCard" style="padding:10px;border-radius:6px;font-size:0.78rem;">
                            <div style="font-weight:600;margin-bottom:3px;">Senior Developer</div>
                            <div style="opacity:0.65;font-size:0.72rem;">2020 – Present · Company Name</div>
                        </div>
                    </div>
                    <!-- Variants preview -->
                    <div style="margin-top:12px;">
                        <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:1px;opacity:0.5;margin-bottom:6px;">Color Variants</div>
                        <div id="pvVariants" style="display:flex;gap:6px;flex-wrap:wrap;"></div>
                    </div>
                    <!-- Pill/Badge -->
                    <div style="margin-top:12px;display:flex;gap:6px;flex-wrap:wrap;">
                        <span id="pvBadge" style="font-size:0.7rem;padding:3px 10px;font-weight:600;">JavaScript</span>
                        <span id="pvBadge2" style="font-size:0.7rem;padding:3px 10px;font-weight:600;">PHP</span>
                        <span id="pvBadge3" style="font-size:0.7rem;padding:3px 10px;font-weight:600;">MySQL</span>
                    </div>
                </div>
            </div>

            <!-- Quick Help -->
            <div class="card" style="margin-top:16px;">
                <div class="card-header"><h3 class="card-title" style="font-size:0.85rem;"><i class="fas fa-info-circle"></i> Quick Reference</h3></div>
                <div style="padding:12px;font-size:0.78rem;color:var(--text-secondary);line-height:1.6;">
                    <b style="color:var(--text-primary);">Key</b> — unique slug used internally and in PHP<br>
                    <b style="color:var(--text-primary);">Layout Mode</b> — two-column adds a dark sidebar<br>
                    <b style="color:var(--text-primary);">Layout Style</b> — controls the overall HTML structure<br>
                    <b style="color:var(--text-primary);">Color Variants</b> — shown as swatches in the editor; first variant is the default<br>
                    <b style="color:var(--text-primary);">Accent Highlights</b> — colored accent lines on sections<br>
                    <b style="color:var(--text-primary);">Animations</b> — hover/reveal effects on cards
                </div>
            </div>
        </div>

    </div>
</form>

<script>
// ── Color picker sync ─────────────────────────────────────────────────────────
function syncPicker(fieldId) {
    var txt = document.getElementById(fieldId);
    if (!txt) return;
    var pick = document.querySelector('[name="' + fieldId + '_pick"]');
    if (pick && /^#[0-9a-fA-F]{6}$/.test(txt.value)) pick.value = txt.value;
}

// ── Live preview ──────────────────────────────────────────────────────────────
function g(name) {
    var el = document.querySelector('[name="' + name + '"]');
    return el ? el.value : '';
}

function updatePreview() {
    var bg  = g('backgroundColor');
    var sur = g('surfaceColor');
    var pri = g('primaryColor');
    var txt = g('textColor');
    var mut = g('textMuted');
    var bdr = g('borderColor') || '#e2e8f0';
    var bs  = g('buttonStyle');
    var cs  = g('cardStyle');
    var hs  = g('headerStyle');

    // Background
    document.getElementById('previewPane').style.background    = bg;
    document.getElementById('previewPane').style.color         = txt;
    document.getElementById('previewPane').style.borderRadius  = '6px';
    document.getElementById('previewPane').style.padding       = '16px';

    // Header
    var hdr = document.getElementById('pvHeader');
    if (hs === 'gradient') {
        hdr.style.background = 'linear-gradient(135deg,' + pri + ', ' + g('secondaryColor') + ')';
        hdr.style.color = '#fff';
    } else if (hs === 'banner') {
        hdr.style.background = pri;
        hdr.style.color = '#fff';
    } else {
        hdr.style.background = sur;
        hdr.style.color = txt;
        hdr.style.borderBottom = '3px solid ' + pri;
    }

    // Section title
    document.getElementById('pvSectionTitle').style.color        = pri;
    document.getElementById('pvSectionTitle').style.borderColor  = pri;

    // Card
    var card = document.getElementById('pvCard');
    card.style.color      = txt;
    if (cs === 'bordered') {
        card.style.background = sur;
        card.style.border     = '1px solid ' + bdr;
    } else if (cs === 'shadow') {
        card.style.background = sur;
        card.style.border     = 'none';
        card.style.boxShadow  = '0 2px 8px rgba(0,0,0,0.25)';
    } else if (cs === 'glass') {
        card.style.background = 'rgba(255,255,255,0.06)';
        card.style.border     = '1px solid rgba(255,255,255,0.12)';
        card.style.backdropFilter = 'blur(8px)';
    } else {
        card.style.background = 'transparent';
        card.style.border     = 'none';
    }

    // Badges
    var radius = bs === 'pill' ? '999px' : (bs === 'rounded' ? '6px' : '3px');
    ['pvBadge','pvBadge2','pvBadge3'].forEach(function(id) {
        var b = document.getElementById(id);
        b.style.background    = pri + '22';
        b.style.color         = pri;
        b.style.border        = '1px solid ' + pri + '44';
        b.style.borderRadius  = radius;
    });

    // Variants
    updateVariantPreview();
}

function updateVariantPreview() {
    var cont = document.getElementById('pvVariants');
    cont.innerHTML = '';
    document.querySelectorAll('.variant-row').forEach(function(row) {
        var lbl  = row.querySelector('[name="variant_label[]"]').value   || '?';
        var prim = row.querySelector('[name="variant_primary[]"]').value || '#888';
        var sec  = row.querySelector('[name="variant_secondary[]"]').value || '#666';
        var div  = document.createElement('div');
        div.title = lbl;
        div.style.cssText = 'width:20px;height:20px;border-radius:50%;background:linear-gradient(135deg,' + prim + ',' + sec + ');border:2px solid rgba(255,255,255,0.2);cursor:pointer;';
        cont.appendChild(div);
    });
}

// ── Color variants ────────────────────────────────────────────────────────────
var variantCount = <?= count($variants ?? [['label'=>'Default','primary'=>'#0ea5e9','secondary'=>'#06b6d4']]) ?>;

function addVariant() {
    var rows = document.querySelectorAll('.variant-row');
    if (rows.length >= 4) {
        alert('Maximum 4 color variants allowed.');
        return;
    }
    var idx = variantCount++;
    var div = document.createElement('div');
    div.className = 'variant-row';
    div.id = 'variant-' + idx;
    div.style.cssText = 'display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;align-items:end;margin-bottom:10px;';
    div.innerHTML =
        '<div><input class="form-input" type="text" name="variant_label[]" placeholder="Label" required></div>' +
        '<div><div style="display:flex;gap:6px;align-items:center;">' +
            '<input type="color" value="#0ea5e9" oninput="this.nextElementSibling.value=this.value" style="width:32px;height:32px;border:none;background:none;cursor:pointer;padding:0;">' +
            '<input class="form-input" type="text" name="variant_primary[]" value="#0ea5e9" pattern="^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$" required oninput="this.previousElementSibling.value=this.value" style="flex:1;min-width:0;"></div></div>' +
        '<div><div style="display:flex;gap:6px;align-items:center;">' +
            '<input type="color" value="#06b6d4" oninput="this.nextElementSibling.value=this.value" style="width:32px;height:32px;border:none;background:none;cursor:pointer;padding:0;">' +
            '<input class="form-input" type="text" name="variant_secondary[]" value="#06b6d4" pattern="^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$" required oninput="this.previousElementSibling.value=this.value" style="flex:1;min-width:0;"></div></div>' +
        '<div><button type="button" onclick="removeVariant(' + idx + ')" class="btn btn-danger btn-sm" style="width:36px;padding:0;text-align:center;"><i class="fas fa-times"></i></button></div>';
    document.getElementById('variantList').appendChild(div);
    updatePreview();
}

function removeVariant(idx) {
    var el = document.getElementById('variant-' + idx);
    if (el) {
        el.remove();
        updatePreview();
    }
}

// Hook all inputs in the form to update preview
document.getElementById('designerForm').addEventListener('input', updatePreview);
// Initial render
updatePreview();
</script>

<?php View::endSection(); ?>
