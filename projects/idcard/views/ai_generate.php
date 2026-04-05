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
        <p>Describe your card in plain language — AI fills in the fields, picks colors, and suggests a design style.</p>
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

    <!-- Step 2: Describe -->
    <div class="step-card">
        <div class="step-label">
            <span class="step-num">2</span> Describe Your Card
        </div>
        <textarea id="aiPrompt" class="prompt-area"
                  placeholder="e.g. John Smith, Senior Software Engineer at TechCorp, blue modern theme..."></textarea>
        <div class="prompt-examples">
            <button class="prompt-example" onclick="fillPrompt('John Smith, Marketing Manager at ABC Corp, professional blue theme')">Corporate example</button>
            <button class="prompt-example" onclick="fillPrompt('Emily Davis, BSc Computer Science, 3rd year student at State University')">Student example</button>
            <button class="prompt-example" onclick="fillPrompt('Dr. Raj Kumar, Cardiologist, City Hospital, emergency contact available')">Medical example</button>
            <button class="prompt-example" onclick="fillPrompt('Security Guard, Night Shift, Zone A, professional dark theme')">Security example</button>
        </div>
    </div>

    <!-- Step 3: Generate -->
    <div class="step-card">
        <div class="step-label">
            <span class="step-num">3</span> Generate
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
                <span id="aiBadge" style="background:linear-gradient(135deg,#6366f1,#10b981);color:#fff;font-size:0.6rem;padding:2px 8px;border-radius:10px;font-weight:700;display:none;"><i class="fas fa-bolt"></i> OpenAI</span>
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
                <a id="applyAndGoBtn" href="#" class="btn btn-primary" style="display:none;">
                    <i class="fas fa-arrow-right"></i> Apply &amp; Open Card Generator
                </a>
                <button type="button" class="btn btn-secondary" onclick="generateWithAI()">
                    <i class="fas fa-redo"></i> Regenerate
                </button>
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

function selectTpl(btn, key) {
    document.querySelectorAll('.tpl-chip').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
    currentTpl = key;
}

function fillPrompt(text) {
    document.getElementById('aiPrompt').value = text;
    document.getElementById('aiPrompt').focus();
}

function generateWithAI() {
    var prompt = document.getElementById('aiPrompt').value.trim();
    if (!prompt) {
        document.getElementById('aiPrompt').focus();
        return;
    }

    var btn    = document.getElementById('generateBtn');
    var status = document.getElementById('aiStatus');

    btn.disabled = true;
    btn.innerHTML = '<span class="ai-spinner"></span> Generating…';
    status.style.display = 'none';
    document.getElementById('aiResults').style.display = 'none';

    var tpl = TEMPLATES[currentTpl] || {};
    var fd  = new FormData();
    fd.append('_token', CSRF_TOKEN);
    fd.append('template_key', currentTpl);
    fd.append('prompt', prompt);
    // Don't pre-fill card_data — let AI generate from scratch
    fd.append('card_data[_empty]', '1');

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

        // Fields
        var fieldKeys = Object.keys(_aiFieldSuggestions);
        var fHtml = '';
        if (fieldKeys.length > 0) {
            fieldKeys.forEach(function(fk) {
                var label = FIELD_LABELS[fk] || fk;
                fHtml += '<div class="field-row"><span class="field-key">' + escAI(label) + '</span><span class="field-val">' + escAI(_aiFieldSuggestions[fk]) + '</span></div>';
            });
        } else {
            fHtml = '<p style="font-size:0.78rem;color:var(--text-secondary);">No field suggestions. Try a more descriptive prompt.</p>';
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

        // "Apply & Open" button: build query string with AI values
        var applyBtn = document.getElementById('applyAndGoBtn');
        if (fieldKeys.length > 0 || _aiColorSuggestions.primary_color) {
            var params = new URLSearchParams();
            params.set('template', currentTpl);
            fieldKeys.forEach(function(fk) {
                params.set('ai_' + fk, _aiFieldSuggestions[fk]);
            });
            if (_aiColorSuggestions.primary_color) params.set('ai_primary_color', _aiColorSuggestions.primary_color);
            if (_aiColorSuggestions.accent_color)  params.set('ai_accent_color',  _aiColorSuggestions.accent_color);
            applyBtn.href = '/projects/idcard/generate?' + params.toString();
            applyBtn.style.display = 'inline-flex';
        } else {
            applyBtn.style.display = 'none';
        }

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
</script>
