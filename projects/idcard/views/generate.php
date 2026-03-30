<?php
/**
 * @var array  $templates
 * @var string $selectedTpl
 * @var array  $tplConfig
 * @var array  $field_labels
 * @var array  $user
 */
$csrfToken = \Core\Security::generateCsrfToken();
?>

<style>
/* ── Generate page ── */
.gen-wrap { display:grid; grid-template-columns:1fr 420px; gap:20px; align-items:start; }
@media(max-width:900px){ .gen-wrap{ grid-template-columns:1fr; } }

/* Template picker */
.tpl-picker { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
.tpl-btn {
    padding:7px 14px; border-radius:20px; font-size:0.78rem; font-weight:600;
    border:1.5px solid transparent; cursor:pointer; transition:all 0.2s;
    background:var(--bg-secondary); color:var(--text-secondary);
}
.tpl-btn.active { color:#fff; border-color:transparent; }

/* Live preview card */
.preview-area { position:sticky; top:1rem; }
.id-card-preview {
    width:100%; max-width:380px; margin:0 auto;
    border-radius:16px; overflow:hidden;
    box-shadow:0 20px 60px rgba(0,0,0,0.4);
    font-family:'Poppins',sans-serif;
    transition:all 0.3s ease;
    aspect-ratio: 85.6 / 54; /* CR80 standard */
    position:relative;
}
.card-inner { width:100%; height:100%; padding:5% 6%; display:flex; gap:6%; align-items:center; position:relative; }
.card-photo-wrap { flex-shrink:0; }
.card-photo {
    width:22%; aspect-ratio:1; border-radius:50%; object-fit:cover;
    border:2px solid rgba(255,255,255,0.4);
    background:rgba(255,255,255,0.2);
    display:flex; align-items:center; justify-content:center; font-size:1.6rem; color:rgba(255,255,255,0.6);
}
.card-details { flex:1; min-width:0; }
.card-name { font-size:clamp(0.7rem,1.8vw,1rem); font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.card-role { font-size:clamp(0.55rem,1.3vw,0.78rem); margin-top:1%; opacity:0.85; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.card-fields { margin-top:3%; display:flex; flex-direction:column; gap:1%; }
.card-field  { font-size:clamp(0.5rem,1.2vw,0.7rem); opacity:0.8; display:flex; align-items:center; gap:3%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.card-stripe {
    position:absolute; bottom:0; left:0; right:0; height:18%;
    opacity:0.25;
}
.card-logo { position:absolute; top:4%; right:4%; width:12%; aspect-ratio:1; object-fit:contain; }
.card-org  { position:absolute; bottom:4%; right:5%; font-size:clamp(0.45rem,1vw,0.6rem); opacity:0.7; text-align:right; }

/* AI panel */
.ai-panel { background:linear-gradient(135deg,rgba(99,102,241,0.08),rgba(0,240,255,0.04)); border:1px solid rgba(99,102,241,0.2); border-radius:12px; padding:16px; margin-top:16px; }
.ai-panel h4 { font-size:0.85rem; font-weight:600; display:flex; align-items:center; gap:6px; margin-bottom:10px; }
.ai-suggestion { font-size:0.78rem; color:var(--text-secondary); line-height:1.6; padding:6px 10px; background:var(--bg-secondary); border-radius:8px; margin-bottom:6px; }
.ai-suggestion strong { color:var(--text-primary); }
.spinner { display:inline-block; width:14px; height:14px; border:2px solid rgba(99,102,241,0.3); border-top-color:var(--indigo); border-radius:50%; animation:spin 0.7s linear infinite; }
@keyframes spin { to{ transform:rotate(360deg); } }
</style>

<a href="/projects/idcard" class="back-link"><i class="fas fa-arrow-left"></i> Dashboard</a>

<h2 class="section-title"><i class="fas fa-id-card" style="color:var(--indigo);"></i> Generate ID Card</h2>

<!-- Template Picker -->
<div class="card" style="margin-bottom:16px;padding:16px;">
    <p style="font-size:0.8rem;color:var(--text-secondary);margin-bottom:10px;font-weight:600;">SELECT TEMPLATE</p>
    <div class="tpl-picker">
        <?php foreach ($templates as $key => $tpl): ?>
        <button type="button" class="tpl-btn <?= $key === $selectedTpl ? 'active' : '' ?>"
                data-key="<?= htmlspecialchars($key) ?>"
                style="<?= $key === $selectedTpl ? "background:{$tpl['color']}" : '' ?>"
                onclick="selectTemplate('<?= htmlspecialchars($key) ?>')">
            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= htmlspecialchars($tpl['color']) ?>;margin-right:4px;"></span>
            <?= htmlspecialchars($tpl['name']) ?>
        </button>
        <?php endforeach; ?>
    </div>
</div>

<div class="gen-wrap">
    <!-- LEFT: Form -->
    <div>
        <form id="cardForm" method="POST" action="/projects/idcard/generate" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="template_key" id="template_key" value="<?= htmlspecialchars($selectedTpl) ?>">

            <!-- Dynamic fields -->
            <div class="card" style="margin-bottom:16px;">
                <h3 style="font-size:0.9rem;font-weight:600;margin-bottom:14px;color:var(--indigo);display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-user"></i> Card Information
                </h3>
                <div id="dynamicFields">
                    <?php foreach ($tplConfig['fields'] as $field): ?>
                        <?php if ($field === 'photo'): continue; endif; ?>
                        <div class="form-group" style="margin-bottom:12px;">
                            <label class="form-label" style="font-size:0.78rem;" for="field_<?= $field ?>">
                                <?= htmlspecialchars($field_labels[$field] ?? ucfirst(str_replace('_',' ',$field))) ?>
                            </label>
                            <input type="text" id="field_<?= $field ?>" name="<?= htmlspecialchars($field) ?>"
                                   class="form-input" style="padding:8px 12px;font-size:0.85rem;"
                                   placeholder="Enter <?= strtolower(htmlspecialchars($field_labels[$field] ?? $field)) ?>"
                                   oninput="updatePreview()">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Photo & Logo -->
            <div class="card" style="margin-bottom:16px;">
                <h3 style="font-size:0.9rem;font-weight:600;margin-bottom:14px;color:var(--indigo);display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-camera"></i> Photo & Logo
                </h3>
                <div class="grid grid-2" style="gap:12px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-size:0.78rem;">Profile Photo</label>
                        <input type="file" name="photo" id="photoInput" class="form-input" accept="image/*"
                               style="padding:6px;font-size:0.8rem;" onchange="previewPhoto(this)">
                        <p style="font-size:0.68rem;color:var(--text-secondary);margin-top:4px;">JPG/PNG, max 5 MB</p>
                    </div>
                    <div class="form-group" style="margin-bottom:0;" id="logoWrap" style="<?= $tplConfig['logo'] ? '' : 'display:none' ?>">
                        <label class="form-label" style="font-size:0.78rem;">Organisation Logo</label>
                        <input type="file" name="logo" id="logoInput" class="form-input" accept="image/*"
                               style="padding:6px;font-size:0.8rem;" onchange="previewLogo(this)">
                        <p style="font-size:0.68rem;color:var(--text-secondary);margin-top:4px;">PNG recommended</p>
                    </div>
                </div>
            </div>

            <!-- Design Options -->
            <div class="card" style="margin-bottom:16px;">
                <h3 style="font-size:0.9rem;font-weight:600;margin-bottom:14px;color:var(--indigo);display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-paint-brush"></i> Design Customisation
                </h3>
                <div class="grid grid-2" style="gap:12px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-size:0.78rem;">Primary Colour</label>
                        <input type="color" name="primary_color" id="primaryColor"
                               class="form-input" style="height:38px;padding:2px 6px;cursor:pointer;"
                               value="<?= htmlspecialchars($tplConfig['color']) ?>" oninput="updatePreview()">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-size:0.78rem;">Accent Colour</label>
                        <input type="color" name="accent_color" id="accentColor"
                               class="form-input" style="height:38px;padding:2px 6px;cursor:pointer;"
                               value="<?= htmlspecialchars($tplConfig['accent']) ?>" oninput="updatePreview()">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-size:0.78rem;">Background Colour</label>
                        <input type="color" name="bg_color" id="bgColor"
                               class="form-input" style="height:38px;padding:2px 6px;cursor:pointer;"
                               value="<?= htmlspecialchars($tplConfig['bg']) ?>" oninput="updatePreview()">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-size:0.78rem;">Text Colour</label>
                        <input type="color" name="text_color" id="textColor"
                               class="form-input" style="height:38px;padding:2px 6px;cursor:pointer;"
                               value="<?= htmlspecialchars($tplConfig['text']) ?>" oninput="updatePreview()">
                    </div>
                </div>
                <div class="form-group" style="margin-top:12px;margin-bottom:0;">
                    <label class="form-label" style="font-size:0.78rem;">Font Family</label>
                    <select name="font_family" id="fontFamily" class="form-input" style="padding:8px 12px;font-size:0.85rem;" onchange="updatePreview()">
                        <?php foreach(['Poppins','Inter','Roboto','Lato','Open Sans','Montserrat','Raleway'] as $f): ?>
                        <option value="<?= $f ?>"><?= $f ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:flex;gap:16px;margin-top:12px;flex-wrap:wrap;">
                    <label style="display:flex;align-items:center;gap:6px;font-size:0.8rem;cursor:pointer;">
                        <input type="checkbox" name="show_qr" id="showQr" onchange="updatePreview()"> Show QR Code
                    </label>
                </div>
            </div>

            <!-- AI Assistant -->
            <div class="card" style="margin-bottom:16px;background:linear-gradient(135deg,rgba(99,102,241,0.06),rgba(0,240,255,0.03));border:1px solid rgba(99,102,241,0.2);">
                <h3 style="font-size:0.9rem;font-weight:600;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-robot" style="color:var(--indigo);"></i> AI Design Assistant
                    <span style="background:linear-gradient(135deg,#6366f1,#00f0ff);color:white;font-size:0.6rem;padding:2px 8px;border-radius:10px;">AI</span>
                </h3>
                <div class="form-group" style="margin-bottom:10px;">
                    <label class="form-label" style="font-size:0.78rem;">Describe your needs (optional)</label>
                    <input type="text" name="ai_prompt" id="aiPrompt" class="form-input"
                           style="padding:8px 12px;font-size:0.85rem;"
                           placeholder="e.g. modern tech company, minimalist, blue theme…">
                </div>
                <button type="button" class="btn btn-secondary" style="width:100%;justify-content:center;" onclick="getAISuggestions()">
                    <i class="fas fa-magic"></i> Get AI Suggestions
                </button>
                <div id="aiOutput" style="margin-top:12px;display:none;"></div>
            </div>

            <!-- Submit -->
            <div class="form-actions">
                <button type="submit" id="generateBtn" class="btn btn-primary" style="flex:1;justify-content:center;padding:14px;">
                    <i class="fas fa-id-card"></i> Generate ID Card
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
                <i class="fas fa-eye"></i> Live Preview
                <span id="previewTplName" style="color:var(--indigo);font-weight:700;"><?= htmlspecialchars($tplConfig['name']) ?></span>
            </h3>

            <!-- ID Card Preview -->
            <div id="cardPreview" class="id-card-preview" style="background:<?= htmlspecialchars($tplConfig['bg']) ?>;">
                <div class="card-inner" style="color:<?= htmlspecialchars($tplConfig['text']) ?>;">
                    <!-- Decorative stripe -->
                    <div class="card-stripe" style="background:<?= htmlspecialchars($tplConfig['color']) ?>;"></div>

                    <!-- Photo -->
                    <div class="card-photo-wrap">
                        <div class="card-photo" id="previewPhoto" style="background:<?= htmlspecialchars($tplConfig['color']) ?>20;border-color:<?= htmlspecialchars($tplConfig['accent']) ?>;">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="card-details">
                        <!-- Header stripe bar -->
                        <div style="position:absolute;top:0;left:0;right:0;height:8px;background:<?= htmlspecialchars($tplConfig['color']) ?>;"></div>

                        <div class="card-name" id="previewName">Full Name</div>
                        <div class="card-role" id="previewRole" style="color:<?= htmlspecialchars($tplConfig['accent']) ?>;">Designation / Role</div>
                        <div class="card-fields" id="previewFields">
                            <div class="card-field"><i class="fas fa-building" style="font-size:0.55rem;opacity:0.7;"></i> <span>Department / Organisation</span></div>
                            <div class="card-field"><i class="fas fa-hashtag" style="font-size:0.55rem;opacity:0.7;"></i> <span>ID Number</span></div>
                        </div>
                    </div>

                    <!-- Org name bottom right -->
                    <div class="card-org" id="previewOrg" style="color:<?= htmlspecialchars($tplConfig['text']) ?>;">
                        <?= htmlspecialchars($tplConfig['name']) ?> Card
                    </div>
                </div>
            </div>

            <!-- Preview note -->
            <p style="text-align:center;font-size:0.72rem;color:var(--text-secondary);margin-top:10px;">
                <i class="fas fa-info-circle"></i> Preview is approximate — final card will be pixel-perfect
            </p>
        </div>

        <!-- Template details -->
        <div class="card" style="margin-top:12px;padding:14px;" id="tplInfoCard">
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

<script>
// Template configurations passed from PHP
const TEMPLATES = <?= json_encode($templates) ?>;
const FIELD_LABELS = <?= json_encode($field_labels) ?>;
const CSRF_TOKEN = '<?= htmlspecialchars($csrfToken) ?>';

let currentTpl = '<?= htmlspecialchars($selectedTpl) ?>';
let photoDataUrl  = null;

// ── Template selector ───────────────────────────────────────────────────────
function selectTemplate(key) {
    if (!TEMPLATES[key]) return;
    currentTpl = key;
    const tpl = TEMPLATES[key];
    document.getElementById('template_key').value = key;

    // Update picker UI
    document.querySelectorAll('.tpl-btn').forEach(btn => {
        const isActive = btn.dataset.key === key;
        btn.classList.toggle('active', isActive);
        btn.style.background = isActive ? tpl.color : '';
        btn.style.color = isActive ? '#fff' : '';
    });

    // Rebuild dynamic fields
    const container = document.getElementById('dynamicFields');
    container.innerHTML = '';
    (tpl.fields || []).forEach(field => {
        if (field === 'photo') return;
        const label = FIELD_LABELS[field] || field.replace(/_/g, ' ');
        container.innerHTML += `
            <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label" style="font-size:0.78rem;" for="field_${field}">${label}</label>
                <input type="text" id="field_${field}" name="${field}"
                       class="form-input" style="padding:8px 12px;font-size:0.85rem;"
                       placeholder="Enter ${label.toLowerCase()}" oninput="updatePreview()">
            </div>`;
    });

    // Update colours
    document.getElementById('primaryColor').value = tpl.color;
    document.getElementById('accentColor').value  = tpl.accent;
    document.getElementById('bgColor').value      = tpl.bg;
    document.getElementById('textColor').value    = tpl.text;

    // Show/hide logo field
    document.getElementById('logoWrap').style.display = tpl.logo ? '' : 'none';

    // Update template name in preview
    document.getElementById('previewTplName').textContent = tpl.name;

    // Update fields list
    const fieldsList = document.getElementById('tplFieldsList');
    fieldsList.innerHTML = (tpl.fields || []).map(f => {
        const lbl = FIELD_LABELS[f] || f;
        return `<span style="padding:3px 9px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:12px;font-size:0.7rem;color:var(--text-secondary);">${lbl}</span>`;
    }).join('');

    updatePreview();
}

// ── Live preview updater ─────────────────────────────────────────────────────
function updatePreview() {
    const tpl   = TEMPLATES[currentTpl] || {};
    const bg    = document.getElementById('bgColor').value    || tpl.bg    || '#fff';
    const pri   = document.getElementById('primaryColor').value || tpl.color || '#000';
    const acc   = document.getElementById('accentColor').value  || tpl.accent || '#333';
    const txt   = document.getElementById('textColor').value    || tpl.text  || '#000';
    const font  = document.getElementById('fontFamily').value   || 'Poppins';

    const preview = document.getElementById('cardPreview');
    preview.style.background   = bg;
    preview.style.fontFamily   = `'${font}',sans-serif`;
    preview.style.color        = txt;

    // Header stripe
    const stripes = preview.querySelectorAll('.card-stripe, [style*="height:8px"]');
    preview.querySelectorAll('[style*="height:8px"]').forEach(el => el.style.background = pri);
    preview.querySelector('.card-stripe').style.background = pri;

    // Photo circle
    const photoEl = document.getElementById('previewPhoto');
    photoEl.style.background   = pri + '30';
    photoEl.style.borderColor  = acc;

    // Text
    const nameVal  = (document.getElementById('field_name')        || {value:''}).value || 'Full Name';
    const roleKeys = ['designation','title','course','event_name'];
    let   roleVal  = '';
    for (const k of roleKeys) {
        const el = document.getElementById('field_' + k);
        if (el && el.value) { roleVal = el.value; break; }
    }
    roleVal = roleVal || 'Designation / Role';

    document.getElementById('previewName').textContent  = nameVal;
    document.getElementById('previewName').style.color  = txt;
    document.getElementById('previewRole').textContent  = roleVal;
    document.getElementById('previewRole').style.color  = acc;

    // Extra fields
    const fieldKeys = (tpl.fields || []).filter(f => f !== 'photo' && f !== 'name' && !roleKeys.includes(f));
    const fieldsEl  = document.getElementById('previewFields');
    fieldsEl.innerHTML = '';
    const icons = {department:'building', employee_id:'hashtag', roll_number:'hashtag',
                   phone:'phone', email:'envelope', blood_group:'tint', badge_id:'hashtag',
                   host_name:'user', purpose:'clipboard', visit_date:'calendar',
                   license_no:'certificate', organization:'building', id_number:'hashtag', year:'graduation-cap'};
    fieldKeys.slice(0,3).forEach(field => {
        const el  = document.getElementById('field_' + field);
        const val = el ? (el.value || (FIELD_LABELS[field] || field)) : (FIELD_LABELS[field] || field);
        const ic  = icons[field] || 'info-circle';
        fieldsEl.innerHTML += `<div class="card-field" style="color:${txt};"><i class="fas fa-${ic}" style="font-size:0.55rem;opacity:0.7;"></i> <span>${val}</span></div>`;
    });

    // Photo preview
    if (photoDataUrl) {
        photoEl.innerHTML = `<img src="${photoDataUrl}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
    } else {
        photoEl.innerHTML = '<i class="fas fa-user"></i>';
    }
}

// ── Photo preview ────────────────────────────────────────────────────────────
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) { photoDataUrl = e.target.result; updatePreview(); };
        reader.readAsDataURL(input.files[0]);
    }
}
function previewLogo(input) { /* logo preview not shown on small preview */ }

// ── AI Suggestions ───────────────────────────────────────────────────────────
function getAISuggestions() {
    const prompt = document.getElementById('aiPrompt').value.trim();
    const out    = document.getElementById('aiOutput');
    out.style.display = 'block';
    out.innerHTML = '<div style="text-align:center;padding:12px;"><div class="spinner"></div><p style="font-size:0.75rem;margin-top:6px;color:var(--text-secondary);">Generating suggestions…</p></div>';

    // Collect current card data
    const cardData = {};
    const tpl = TEMPLATES[currentTpl] || {};
    (tpl.fields || []).forEach(f => {
        const el = document.getElementById('field_' + f);
        if (el) cardData[f] = el.value;
    });

    const formData = new FormData();
    formData.append('_token', CSRF_TOKEN);
    formData.append('template_key', currentTpl);
    formData.append('prompt', prompt);
    Object.keys(cardData).forEach(k => formData.append('card_data[' + k + ']', cardData[k]));

    fetch('/projects/idcard/ai-suggest', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) { out.innerHTML = '<div class="ai-suggestion">Could not get suggestions. Try again.</div>'; return; }
        const s = data.suggestions || {};
        let html = '';

        if (s.template_tip) {
            html += `<div class="ai-suggestion"><strong>💡 Tip:</strong> ${s.template_tip}</div>`;
        }
        if (s.missing_fields) {
            html += `<div class="ai-suggestion"><strong>📋 Completeness:</strong> ${s.missing_fields}</div>`;
        }
        if (s.design_tips && s.design_tips.length) {
            s.design_tips.forEach(t => { html += `<div class="ai-suggestion"><strong>🎨 Design:</strong> ${t}</div>`; });
        }
        if (s.prompt_hint) {
            html += `<div class="ai-suggestion"><strong>✨ Your request:</strong> ${s.prompt_hint}</div>`;
        }
        if (s.ai_text) {
            html += `<div class="ai-suggestion"><strong>🤖 AI:</strong> ${s.ai_text}</div>`;
        }

        out.innerHTML = html || '<div class="ai-suggestion">Looking good! Your card is well structured.</div>';
    })
    .catch(() => {
        out.innerHTML = '<div class="ai-suggestion">Network error. Please try again.</div>';
    });
}

// ── Reset ─────────────────────────────────────────────────────────────────────
function resetForm() {
    photoDataUrl = null;
    setTimeout(updatePreview, 50);
}

// ── Form submit ───────────────────────────────────────────────────────────────
document.getElementById('cardForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('generateBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner"></div> Generating…';
});

// Init
updatePreview();
</script>
