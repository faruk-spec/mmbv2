<?php
/**
 * Generate with AI — dedicated AI card generation page.
 *
 * @var array  $templates
 * @var string $selectedTpl
 * @var array  $tplConfig
 * @var array  $field_labels
 * @var array  $user
 */
$csrfToken = \Core\Security::generateCsrfToken();
?>

<style>
.ai-gen-wrap { max-width:860px; margin:0 auto; }

/* Header banner */
.ai-hero {
    background:linear-gradient(135deg,rgba(99,102,241,0.12),rgba(0,240,255,0.06));
    border:1px solid rgba(99,102,241,0.25);
    border-radius:16px; padding:28px 28px 22px; margin-bottom:24px;
    text-align:center;
}
.ai-hero-icon { font-size:2.5rem; margin-bottom:10px; }
.ai-hero h1 { font-size:1.5rem; font-weight:700; margin:0 0 6px; }
.ai-hero p  { color:var(--text-secondary); font-size:0.9rem; margin:0; }

/* Step cards */
.step-card {
    background:var(--bg-card); border:1px solid var(--border-color);
    border-radius:14px; padding:22px 24px; margin-bottom:16px;
}
.step-label {
    display:inline-flex; align-items:center; gap:6px;
    font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;
    color:var(--indigo); margin-bottom:14px;
}
.step-label .step-num {
    width:20px; height:20px; border-radius:50%;
    background:var(--indigo); color:#fff;
    font-size:0.65rem; display:flex; align-items:center; justify-content:center;
}

/* Template chips */
.tpl-chips { display:flex; flex-wrap:wrap; gap:8px; }
.tpl-chip {
    padding:6px 14px; border-radius:20px; font-size:0.78rem; font-weight:600;
    border:1.5px solid var(--border-color); cursor:pointer; transition:all 0.2s;
    background:var(--bg-secondary); color:var(--text-secondary);
}
.tpl-chip.active { background:var(--indigo); color:#fff; border-color:var(--indigo); }

/* Prompt area */
.prompt-area {
    width:100%; min-height:90px; padding:14px 16px;
    background:var(--bg-secondary); border:2px solid var(--border-color);
    border-radius:10px; color:var(--text-primary);
    font-family:inherit; font-size:0.9rem; resize:vertical; line-height:1.6;
    transition:border-color 0.2s;
}
.prompt-area:focus { outline:none; border-color:var(--indigo); }

/* Example prompt chips */
.prompt-examples { display:flex; flex-wrap:wrap; gap:6px; margin-top:10px; }
.prompt-example {
    padding:4px 12px; border-radius:14px; font-size:0.72rem; font-weight:600;
    background:rgba(99,102,241,0.1); color:var(--indigo); cursor:pointer;
    border:1px solid rgba(99,102,241,0.2); transition:all 0.2s;
}
.prompt-example:hover { background:rgba(99,102,241,0.2); }

/* Result panels */
.result-wrap { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-top:16px; }
@media(max-width:680px){ .result-wrap{ grid-template-columns:1fr; } }

.result-fields {
    background:rgba(99,102,241,0.06); border:1px solid rgba(99,102,241,0.2);
    border-radius:12px; padding:16px;
}
.result-colors {
    background:rgba(0,240,255,0.04); border:1px solid rgba(0,240,255,0.15);
    border-radius:12px; padding:16px;
}
.result-tips {
    background:var(--bg-secondary); border:1px solid var(--border-color);
    border-radius:12px; padding:16px; margin-top:14px;
}
.result-title { font-size:0.78rem; font-weight:700; color:var(--text-primary); margin-bottom:10px; display:flex; align-items:center; gap:6px; }
.field-row { display:flex; justify-content:space-between; align-items:center; padding:4px 0; border-bottom:1px solid rgba(255,255,255,0.05); }
.field-row:last-child { border-bottom:none; }
.field-key { font-size:0.72rem; color:var(--text-secondary); }
.field-val { font-size:0.76rem; font-weight:600; color:var(--text-primary); max-width:55%; text-align:right; word-break:break-word; }
.color-swatch-row { display:flex; align-items:center; gap:10px; margin-bottom:8px; }
.color-swatch { width:32px; height:32px; border-radius:8px; border:1px solid rgba(255,255,255,0.15); flex-shrink:0; }
.tip-item { font-size:0.79rem; color:var(--text-secondary); line-height:1.6; padding:4px 0; border-bottom:1px solid rgba(255,255,255,0.05); }
.tip-item:last-child { border-bottom:none; }
.tip-item strong { color:var(--text-primary); }

/* Spinner */
.ai-spinner { display:inline-block; width:18px; height:18px; border:2px solid rgba(99,102,241,0.3); border-top-color:var(--indigo); border-radius:50%; animation:spin 0.7s linear infinite; vertical-align:middle; margin-right:6px; }
@keyframes spin { to { transform:rotate(360deg); } }

/* Action bar */
.ai-actions { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-top:16px; }

/* Loading overlay */
.ai-loading { text-align:center; padding:28px 16px; }
.ai-loading p { color:var(--text-secondary); font-size:0.85rem; margin-top:12px; }
</style>

<div class="ai-gen-wrap">

    <!-- Hero -->
    <div class="ai-hero">
        <div class="ai-hero-icon">✨</div>
        <h1>Generate with AI</h1>
        <p>Choose a template, fill in what you know, and let AI complete the rest — colors, design tips, and missing values.</p>
    </div>

    <!-- Step 1: Template -->
    <div class="step-card">
        <div class="step-label">
            <span class="step-num">1</span> Choose a Template
        </div>
        <div class="tpl-chips" id="tplChips">
            <?php foreach ($templates as $key => $tpl): ?>
            <button type="button"
                    class="tpl-chip <?= $key === $selectedTpl ? 'active' : '' ?>"
                    data-key="<?= htmlspecialchars($key) ?>"
                    onclick="selectTpl(this, '<?= htmlspecialchars($key) ?>')">
                <?= htmlspecialchars($tpl['name']) ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Step 2: Template fields -->
    <div class="step-card" id="fieldsCard">
        <div class="step-label">
            <span class="step-num">2</span> Fill in Card Details
            <span style="font-size:0.68rem;color:var(--text-secondary);font-weight:400;margin-left:4px;">(all fields required)</span>
        </div>
        <div id="tplFieldsWrap">
            <!-- injected by JS -->
        </div>
    </div>

    <!-- Step 3: Optional prompt -->
    <div class="step-card">
        <div class="step-label">
            <span class="step-num">3</span> Optional: Describe Your Needs
        </div>
        <textarea id="aiPrompt" class="prompt-area"
                  placeholder="e.g. blue modern theme, formal style, minimalist..."></textarea>
        <div class="prompt-examples" id="promptExamples">
            <!-- injected by JS based on template -->
        </div>
    </div>

    <!-- Step 4: Generate -->
    <div class="step-card">
        <div class="step-label">
            <span class="step-num">4</span> Generate
        </div>
        <button type="button" id="generateBtn" class="btn btn-primary"
                style="width:100%;justify-content:center;padding:12px;"
                onclick="generateWithAI()">
            <i class="fas fa-wand-magic-sparkles"></i>&nbsp; Generate with AI
        </button>
        <div id="aiStatus" style="display:none;margin-top:14px;"></div>
    </div>

    <!-- Results -->
    <div id="aiResults" style="display:none;">
        <div class="step-card" style="border-color:rgba(99,102,241,0.3);">
            <div class="step-label">
                <span class="step-num" style="background:linear-gradient(135deg,#6366f1,#10b981);">✓</span>
                AI-Generated Card Data
                <span id="aiBadge" style="background:linear-gradient(135deg,#6366f1,#10b981);color:#fff;font-size:0.6rem;padding:2px 8px;border-radius:10px;font-weight:700;display:none;"><i class="fas fa-bolt"></i> AI</span>
            </div>

            <div class="result-wrap">
                <!-- Fields -->
                <div class="result-fields">
                    <div class="result-title"><i class="fas fa-list-ul" style="color:var(--indigo);"></i> Card Fields</div>
                    <div id="resultFields"><p style="font-size:0.78rem;color:var(--text-secondary);">No field suggestions from AI.</p></div>
                </div>
                <!-- Colors -->
                <div class="result-colors">
                    <div class="result-title"><i class="fas fa-palette" style="color:#00f0ff;"></i> Suggested Colors</div>
                    <div id="resultColors"><p style="font-size:0.78rem;color:var(--text-secondary);">No color suggestions.</p></div>
                </div>
            </div>

            <!-- Tips -->
            <div class="result-tips" id="resultTipsWrap" style="display:none;">
                <div class="result-title"><i class="fas fa-lightbulb" style="color:#f59e0b;"></i> Design Tips</div>
                <div id="resultTips"></div>
            </div>

            <!-- Actions -->
            <div class="ai-actions">
                <button type="button" id="createCardBtn" class="btn btn-primary" style="display:none;" onclick="createCardFromAI()">
                    <i class="fas fa-id-card"></i> Generate &amp; Save Card
                </button>
                <button type="button" class="btn btn-secondary" onclick="generateWithAI()">
                    <i class="fas fa-redo"></i> Regenerate
                </button>
            </div>

            <!-- Success panel (shown after card is saved) -->
            <div id="cardCreatedPanel" style="display:none;margin-top:16px;padding:16px 18px;background:rgba(0,255,136,0.08);border:1px solid rgba(0,255,136,0.3);border-radius:12px;">
                <div style="font-size:0.85rem;font-weight:700;color:var(--green);margin-bottom:8px;"><i class="fas fa-check-circle"></i> Card saved successfully!</div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <a id="viewCardLink" href="#" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> View Card</a>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="generateWithAI()"><i class="fas fa-redo"></i> Generate Another</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
var TEMPLATES    = <?= json_encode($templates) ?>;
var FIELD_LABELS = <?= json_encode($field_labels) ?>;
var CSRF_TOKEN   = '<?= htmlspecialchars($csrfToken) ?>';
var currentTpl   = '<?= htmlspecialchars($selectedTpl) ?>';

var _aiFieldSuggestions = {};
var _aiColorSuggestions = {};
var _mergedFields       = {};  // user values + AI suggestions combined; used by createCardFromAI()

// Per-template placeholder hints shown as example prompt chips
var TPL_PROMPT_EXAMPLES = {
    'corporate':   ['Professional blue theme', 'Formal minimalist style', 'Dark executive look'],
    'student':     ['Bright academic style', 'Modern university theme', 'Green & white clean look'],
    'event':       ['Bold conference badge', 'Vibrant event colors', 'Dark tech summit style'],
    'visitor':     ['Clean visitor pass', 'Amber security tone', 'Minimalist modern look'],
    'medical':     ['Clinical white theme', 'Emergency-ready red accents', 'Professional hospital look'],
    'tech':        ['Dark neon tech style', 'Modern startup feel', 'Minimalist dark card'],
    'bank':        ['Classic navy & gold', 'Formal banking style', 'Professional finance look'],
    'media':       ['Bold press red theme', 'Journalist credentialing style'],
    'govt':        ['Official green theme', 'Government formal style'],
    'security':    ['Dark tactical look', 'Bold amber security style'],
};

// Fields that should use a textarea (longer text)
var TEXTAREA_FIELDS = ['company_address', 'school_address', 'purpose', 'person_address'];
// Fields for date input
var DATE_FIELDS = ['dob', 'visit_date', 'expiry_date', 'valid_from', 'valid_till', 'joining_date'];

function renderTplFields(tplKey) {
    var tpl  = TEMPLATES[tplKey] || {};
    var fields = (tpl.fields || []).filter(function(f){ return f !== 'photo'; });
    var wrap = document.getElementById('tplFieldsWrap');
    if (!fields.length) {
        wrap.innerHTML = '<p style="font-size:0.8rem;color:var(--text-secondary);">No fields for this template.</p>';
        return;
    }
    var html = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;">';
    fields.forEach(function(f) {
        var label = FIELD_LABELS[f] || f;
        var inputType = DATE_FIELDS.indexOf(f) !== -1 ? 'date' : 'text';
        var isTextarea = TEXTAREA_FIELDS.indexOf(f) !== -1;
        html += '<div style="display:flex;flex-direction:column;gap:4px;">'
              + '<label style="font-size:0.72rem;font-weight:600;color:var(--text-secondary);">' + escAI(label) + ' <span style="color:#ef4444;">*</span></label>';
        if (isTextarea) {
            html += '<textarea id="aifield_' + escAI(f) + '" class="prompt-area" rows="2" required'
                  + ' style="min-height:56px;font-size:0.82rem;padding:8px 10px;"></textarea>';
        } else {
            html += '<input type="' + inputType + '" id="aifield_' + escAI(f) + '" class="prompt-area" required'
                  + ' style="min-height:unset;font-size:0.82rem;padding:8px 10px;">';
        }
        html += '</div>';
    });
    html += '</div>';
    wrap.innerHTML = html;
}

function renderPromptExamples(tplKey) {
    var examples = TPL_PROMPT_EXAMPLES[tplKey] || ['Professional style', 'Modern minimalist theme'];
    var wrap = document.getElementById('promptExamples');
    wrap.innerHTML = '';
    examples.forEach(function(ex) {
        var btn = document.createElement('button');
        btn.className   = 'prompt-example';
        btn.textContent = ex;
        btn.addEventListener('click', function() { fillPrompt(ex); });
        wrap.appendChild(btn);
    });
}

function selectTpl(btn, key) {
    document.querySelectorAll('.tpl-chip').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
    currentTpl = key;
    renderTplFields(key);
    renderPromptExamples(key);
    // Clear previous results when template changes
    document.getElementById('aiResults').style.display = 'none';
    document.getElementById('aiStatus').style.display  = 'none';
}

function fillPrompt(text) {
    document.getElementById('aiPrompt').value = text;
    document.getElementById('aiPrompt').focus();
}

function getFieldValues() {
    var tpl    = TEMPLATES[currentTpl] || {};
    var fields = (tpl.fields || []).filter(function(f){ return f !== 'photo'; });
    var data   = {};
    fields.forEach(function(f) {
        var el = document.getElementById('aifield_' + f);
        if (el) {
            data[f] = el.value.trim();
        }
    });
    return data;
}

function validateRequiredFields() {
    var tpl    = TEMPLATES[currentTpl] || {};
    var fields = (tpl.fields || []).filter(function(f){ return f !== 'photo'; });
    var first  = null;
    fields.forEach(function(f) {
        var el = document.getElementById('aifield_' + f);
        if (el && !el.value.trim()) {
            el.style.borderColor = '#ef4444';
            if (!first) first = el;
        } else if (el) {
            el.style.borderColor = '';
        }
    });
    return first; // null = all valid, element = first empty field
}

function generateWithAI() {
    var btn    = document.getElementById('generateBtn');
    var status = document.getElementById('aiStatus');

    // Validate required fields first
    var invalid = validateRequiredFields();
    if (invalid) {
        status.style.display = 'block';
        status.innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Please fill in all required fields before generating.</div>';
        invalid.focus();
        invalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="ai-spinner"></span> Generating…';
    status.style.display = 'none';
    document.getElementById('aiResults').style.display = 'none';

    var cardData = getFieldValues();
    var prompt   = document.getElementById('aiPrompt').value.trim();
    var fd = new FormData();
    fd.append('_token', CSRF_TOKEN);
    fd.append('template_key', currentTpl);
    fd.append('prompt', prompt);
    Object.keys(cardData).forEach(function(k) { fd.append('card_data[' + k + ']', cardData[k]); });

    fetch('/projects/idcard/ai-suggest', {
        method:  'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body:    fd,
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-wand-magic-sparkles"></i>&nbsp; Generate with AI';

        if (!data.success) {
            status.style.display = 'block';
            status.innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ' + escAI(data.message || 'Generation failed. Try again.') + '</div>';
            return;
        }

        var s = data.suggestions || {};
        _aiFieldSuggestions = s.field_suggestions  || {};
        _aiColorSuggestions = s.color_suggestions  || {};

        // Badge
        var badge = document.getElementById('aiBadge');
        badge.style.display = s.ai_powered ? 'inline-flex' : 'none';

        // Fields — use user-provided values (all required); AI supplements design only
        _mergedFields = cardData;

        var fieldKeys = Object.keys(cardData);
        var fHtml = '';
        if (fieldKeys.length > 0) {
            fieldKeys.forEach(function(fk) {
                var label = FIELD_LABELS[fk] || fk;
                fHtml += '<div class="field-row">'
                       + '<span class="field-key">' + escAI(label) + '</span>'
                       + '<span class="field-val">' + escAI(cardData[fk]) + '</span>'
                       + '</div>';
            });
        } else {
            fHtml = '<p style="font-size:0.78rem;color:var(--text-secondary);">No field data. Please fill in the form above.</p>';
        }
        document.getElementById('resultFields').innerHTML = fHtml;

        // Colors
        var cHtml = '';
        if (_aiColorSuggestions.primary_color || _aiColorSuggestions.accent_color) {
            if (_aiColorSuggestions.primary_color) {
                cHtml += '<div class="color-swatch-row">'
                       + '<div class="color-swatch" style="background:' + escAI(_aiColorSuggestions.primary_color) + ';"></div>'
                       + '<div><div style="font-size:0.72rem;font-weight:700;color:var(--text-primary);">Primary</div>'
                       + '<div style="font-size:0.72rem;color:var(--text-secondary);">' + escAI(_aiColorSuggestions.primary_color) + '</div></div>'
                       + '</div>';
            }
            if (_aiColorSuggestions.accent_color) {
                cHtml += '<div class="color-swatch-row">'
                       + '<div class="color-swatch" style="background:' + escAI(_aiColorSuggestions.accent_color) + ';"></div>'
                       + '<div><div style="font-size:0.72rem;font-weight:700;color:var(--text-primary);">Accent</div>'
                       + '<div style="font-size:0.72rem;color:var(--text-secondary);">' + escAI(_aiColorSuggestions.accent_color) + '</div></div>'
                       + '</div>';
            }
        } else {
            cHtml = '<p style="font-size:0.78rem;color:var(--text-secondary);">No color suggestions.</p>';
        }
        document.getElementById('resultColors').innerHTML = cHtml;

        // Tips
        var tips = (s.design_tips || []);
        if (s.template_tip) tips.unshift(s.template_tip);
        if (s.prompt_hint && s.prompt_hint.length) tips.push(s.prompt_hint);
        if (s.ai_text && s.ai_text.length) tips.push(s.ai_text);

        var tipsWrap = document.getElementById('resultTipsWrap');
        if (tips.length > 0) {
            var tHtml = '';
            tips.forEach(function(t) { tHtml += '<div class="tip-item">' + escAI(t) + '</div>'; });
            document.getElementById('resultTips').innerHTML = tHtml;
            tipsWrap.style.display = 'block';
        } else {
            tipsWrap.style.display = 'none';
        }

        // Show "Generate & Save Card" button when there are field values
        var createBtn = document.getElementById('createCardBtn');
        document.getElementById('cardCreatedPanel').style.display = 'none';
        createBtn.style.display = (fieldKeys.length > 0) ? 'inline-flex' : 'none';

        document.getElementById('aiResults').style.display = 'block';
        document.getElementById('aiResults').scrollIntoView({ behavior: 'smooth', block: 'start' });
    })
    .catch(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-wand-magic-sparkles"></i>&nbsp; Generate with AI';
        status.style.display = 'block';
        status.innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Network error. Please try again.</div>';
    });
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

// Directly save the card with AI-generated data (stays on this page)
function createCardFromAI() {
    var btn = document.getElementById('createCardBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="ai-spinner"></span> Saving…';

    var tpl    = TEMPLATES[currentTpl] || {};
    var fields = (tpl.fields || []).filter(function(f){ return f !== 'photo'; });

    var fd = new FormData();
    fd.append('_token', CSRF_TOKEN);
    fd.append('template_key', currentTpl);

    // Flat field values: merge user-entered values with AI suggestions
    fields.forEach(function(f) {
        var val = _mergedFields[f] || '';
        fd.append(f, val);
    });

    // Design: use AI color suggestions if available, else template defaults
    fd.append('primary_color', (_aiColorSuggestions.primary_color || tpl.color || '#1e40af'));
    fd.append('accent_color',  (_aiColorSuggestions.accent_color  || tpl.accent || '#3b82f6'));
    fd.append('bg_color',      tpl.bg   || '#ffffff');
    fd.append('text_color',    tpl.text || '#1e293b');
    fd.append('font_family',   'Poppins');
    fd.append('design_style',  (tpl.orientation === 'portrait') ? 'v_sharp' : 'classic');
    fd.append('show_qr',       '0');
    fd.append('profile_shape', 'circle');
    fd.append('ai_prompt',     document.getElementById('aiPrompt').value.trim());

    fetch('/projects/idcard/generate', {
        method:  'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body:    fd,
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-id-card"></i> Generate &amp; Save Card';
        if (data.success && data.card_id) {
            btn.style.display = 'none';
            var panel = document.getElementById('cardCreatedPanel');
            document.getElementById('viewCardLink').href = data.redirect || ('/projects/idcard/view/' + data.card_id);
            panel.style.display = 'block';
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            document.getElementById('aiStatus').style.display = 'block';
            document.getElementById('aiStatus').innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Could not save card. Please try again.</div>';
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-id-card"></i> Generate &amp; Save Card';
        document.getElementById('aiStatus').style.display = 'block';
        document.getElementById('aiStatus').innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Network error. Please try again.</div>';
    });
}

// Initialise on load
renderTplFields(currentTpl);
renderPromptExamples(currentTpl);
</script>
