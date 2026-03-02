<?php
/**
 * ConvertX – AI File Processing View
 * Upload any file + add remarks → OpenAI processes and returns output
 */
$currentView = 'ai-process';
$csrfToken   = \Core\Security::generateCsrfToken();
?>

<!-- Page header -->
<div class="page-header">
    <h1><i class="fa-solid fa-wand-magic-sparkles" style="color:var(--cx-primary);"></i> AI File Processing</h1>
    <p>Upload any document or image, add your instructions, and let OpenAI do the work</p>
</div>

<?php if (!$configured): ?>
<div class="cx-notice" style="border-color:var(--cx-warning);">
    <i class="fa-solid fa-triangle-exclamation" style="color:var(--cx-warning);"></i>
    <div>
        <strong>OpenAI is not configured.</strong>
        Ask an admin to add an OpenAI API key in
        <a href="/projects/convertx/settings" style="color:var(--cx-primary);">ConvertX → Settings</a>.
    </div>
</div>
<?php endif; ?>

<div class="cx-convert-grid">

    <!-- ── Left column: upload + remarks ── -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-file-arrow-up"></i> File &amp; Instructions
        </div>

        <form id="aiProcessForm" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <!-- Upload zone -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-cloud-arrow-up" style="color:var(--cx-primary);"></i>
                    File <span style="font-weight:400;color:var(--text-muted);">(optional)</span>
                </label>
                <div class="upload-zone" id="uploadZone" style="cursor:pointer;">
                    <i class="fa-solid fa-cloud-arrow-up upload-icon" style="font-size:1.75rem;"></i>
                    <p style="font-weight:600;font-size:.875rem;margin:.35rem 0 .2rem;">
                        Drag &amp; drop or <strong>click to browse</strong>
                    </p>
                    <p style="font-size:.73rem;color:var(--text-muted);">
                        PDF, DOCX, XLSX, TXT, PNG, JPG, CSV and more
                    </p>
                    <input type="file" name="file" id="fileInput" style="display:none;">
                </div>
                <div id="selectedFile" style="margin-top:.4rem;font-size:.82rem;display:none;color:var(--text-secondary);"></div>
            </div>

            <!-- Remarks / Instructions -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-pen-to-square" style="color:var(--cx-primary);"></i>
                    Instructions / Remarks <span style="color:var(--cx-danger);">*</span>
                </label>
                <textarea name="remarks" id="remarks" rows="5"
                    style="width:100%;background:var(--bg-tertiary);border:1.5px solid var(--border-color);
                           border-radius:.5rem;padding:.6rem .75rem;color:var(--text-primary);
                           font-size:.875rem;resize:vertical;font-family:inherit;line-height:1.5;"
                    placeholder="e.g. Summarize this document in bullet points&#10;or: Extract all invoice line items as a table&#10;or: Translate this text to French and improve the tone"
                    required><?= htmlspecialchars($_POST['remarks'] ?? '') ?></textarea>
                <div style="margin-top:.3rem;font-size:.73rem;color:var(--text-muted);">
                    <i class="fa-solid fa-lightbulb"></i>
                    <strong>Tips:</strong> Be specific. Examples: "List all names and dates",
                    "Convert this CSV data into a readable summary", "What are the key findings?"
                </div>
            </div>

            <!-- Quick prompts -->
            <div class="form-group" style="margin-bottom:1rem;">
                <div style="font-size:.75rem;font-weight:600;color:var(--text-muted);margin-bottom:.35rem;text-transform:uppercase;letter-spacing:.05em;">
                    Quick Prompts
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:.35rem;">
                    <?php
                    $quickPrompts = [
                        'Summarize this document in bullet points',
                        'Extract all names, dates and amounts',
                        'List the key action items',
                        'Translate to English',
                        'Translate to French',
                        'Extract data as a table',
                        'Proofread and improve grammar',
                        'What is this document about?',
                        'Convert to plain text, remove formatting',
                    ];
                    foreach ($quickPrompts as $prompt): ?>
                    <button type="button" class="cx-quick-prompt-btn"
                            onclick="applyPrompt(<?= json_encode($prompt, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)">
                        <?= htmlspecialchars($prompt) ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" id="submitBtn" <?= !$configured ? 'disabled' : '' ?>>
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                Process with AI
            </button>
        </form>
    </div>

    <!-- ── Right column: output ── -->
    <div class="card" id="outputCard">
        <div class="card-header" style="display:flex;align-items:center;gap:.5rem;">
            <i class="fa-solid fa-sparkles"></i>
            <span>AI Output</span>
            <span id="outputMeta" style="margin-left:auto;font-size:.75rem;font-weight:400;color:var(--text-muted);"></span>
        </div>

        <div id="outputArea" style="padding:1.25rem;min-height:200px;">
            <div id="outputPlaceholder"
                 style="display:flex;flex-direction:column;align-items:center;justify-content:center;
                        height:200px;color:var(--text-muted);gap:.75rem;text-align:center;">
                <i class="fa-solid fa-robot" style="font-size:2.5rem;opacity:.3;"></i>
                <div>
                    <div style="font-size:.9rem;font-weight:600;">Ready to process</div>
                    <div style="font-size:.78rem;">Upload a file (optional), add instructions, and click <strong>Process with AI</strong></div>
                </div>
            </div>

            <div id="outputContent" style="display:none;">
                <div id="outputText"
                     style="white-space:pre-wrap;word-break:break-word;font-size:.875rem;
                            line-height:1.7;color:var(--text-primary);"></div>
            </div>

            <div id="outputSpinner" style="display:none;text-align:center;padding:3rem 0;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;color:var(--cx-primary);"></i>
                <div style="margin-top:.75rem;color:var(--text-muted);font-size:.875rem;">Processing with AI…</div>
            </div>

            <div id="outputError" style="display:none;"></div>
        </div>

        <!-- Copy / Download output buttons -->
        <div id="outputActions" style="padding:.75rem 1.25rem;border-top:1px solid var(--border-color);gap:.5rem;display:none;">
            <button type="button" class="btn btn-sm btn-secondary" onclick="copyOutput()">
                <i class="fa-solid fa-copy"></i> Copy
            </button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="downloadOutput()">
                <i class="fa-solid fa-download"></i> Download as TXT
            </button>
        </div>
    </div>

</div>

<style>
.cx-quick-prompt-btn {
    background: var(--bg-tertiary);
    border: 1px solid var(--border-color);
    border-radius: 1rem;
    padding: .25rem .65rem;
    font-size: .72rem;
    color: var(--text-secondary);
    cursor: pointer;
    transition: border-color .15s, color .15s;
    white-space: nowrap;
}
.cx-quick-prompt-btn:hover {
    border-color: var(--cx-primary);
    color: var(--cx-primary);
}
#outputActions { display: none; }
#outputActions.visible { display: flex; }
</style>

<script>
// ── File upload handling ──────────────────────────────────────────────
const fileInput  = document.getElementById('fileInput');
const uploadZone = document.getElementById('uploadZone');
const selectedEl = document.getElementById('selectedFile');

uploadZone.addEventListener('click', () => fileInput.click());
uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.style.borderColor = 'var(--cx-primary)'; });
uploadZone.addEventListener('dragleave', () => { uploadZone.style.borderColor = ''; });
uploadZone.addEventListener('drop', e => {
    e.preventDefault();
    uploadZone.style.borderColor = '';
    if (e.dataTransfer.files[0]) {
        fileInput.files = e.dataTransfer.files;
        showSelected(e.dataTransfer.files[0]);
    }
});
fileInput.addEventListener('change', () => {
    if (fileInput.files[0]) showSelected(fileInput.files[0]);
});

function showSelected(file) {
    selectedEl.style.display = 'block';
    const kb = (file.size / 1024).toFixed(1);
    selectedEl.innerHTML = '<i class="fa-solid fa-file" style="color:var(--cx-primary);"></i> '
        + escHtml(file.name) + ' <span style="color:var(--text-muted);">(' + kb + ' KB)</span>';
}

function escHtml(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Quick prompt ─────────────────────────────────────────────────────
function applyPrompt(text) {
    document.getElementById('remarks').value = text;
    document.getElementById('remarks').focus();
}

// ── Form submit ──────────────────────────────────────────────────────
let lastOutput = '';

document.getElementById('aiProcessForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const remarks = document.getElementById('remarks').value.trim();
    if (!remarks) {
        alert('Please enter instructions / remarks.');
        return;
    }

    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing…';

    document.getElementById('outputPlaceholder').style.display = 'none';
    document.getElementById('outputContent').style.display     = 'none';
    document.getElementById('outputError').style.display       = 'none';
    document.getElementById('outputSpinner').style.display     = 'block';
    document.getElementById('outputActions').classList.remove('visible');
    document.getElementById('outputMeta').textContent = '';

    const fd = new FormData(this);

    fetch('/projects/convertx/ai-process', {
        method: 'POST',
        body:   fd,
    })
    .then(function(r) {
        if (!r.ok) {
            return r.text().then(function(t) { throw new Error('HTTP ' + r.status + ': ' + t.substring(0, 300)); });
        }
        return r.json();
    })
    .then(function(data) {
        document.getElementById('outputSpinner').style.display = 'none';

        if (!data.success) {
            showError(data.error || 'Unknown error');
            return;
        }

        lastOutput = data.output || '';
        document.getElementById('outputText').textContent = lastOutput;
        document.getElementById('outputContent').style.display = 'block';
        document.getElementById('outputActions').classList.add('visible');

        const meta = [];
        if (data.model)       meta.push(data.model);
        if (data.tokens_used) meta.push(data.tokens_used + ' tokens');
        if (data.filename)    meta.push('from: ' + escHtml(data.filename));
        document.getElementById('outputMeta').textContent = meta.join(' · ');
    })
    .catch(function(err) {
        document.getElementById('outputSpinner').style.display = 'none';
        showError(err.message || 'Request failed');
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Process with AI';
    });
});

function showError(msg) {
    const el = document.getElementById('outputError');
    el.style.display = 'block';
    el.innerHTML = '<div class="cx-notice" style="border-color:var(--cx-danger);margin:0;">'
        + '<i class="fa-solid fa-circle-exclamation" style="color:var(--cx-danger);"></i>'
        + '<div><strong>Error:</strong> ' + escHtml(msg) + '</div></div>';
}

function copyOutput() {
    if (!lastOutput) return;
    navigator.clipboard.writeText(lastOutput).then(function() {
        const btn = event.target.closest('button');
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
        setTimeout(() => btn.innerHTML = orig, 1800);
    });
}

function downloadOutput() {
    if (!lastOutput) return;
    const blob = new Blob([lastOutput], {type: 'text/plain'});
    const a    = document.createElement('a');
    a.href     = URL.createObjectURL(blob);
    a.download = 'ai-output-' + Date.now() + '.txt';
    a.click();
    URL.revokeObjectURL(a.href);
}
</script>
