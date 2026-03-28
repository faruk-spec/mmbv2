<?php
/**
 * ConvertX – AI File Processing View
 * Upload any file + add remarks → AI processes and returns output
 */
$currentView = 'ai-process';
$csrfToken   = \Core\Security::generateCsrfToken();
?>

<!-- ── Page header ──────────────────────────────────────────────────────── -->
<div class="page-header">
    <h1>
        <i class="fa-solid fa-wand-magic-sparkles" style="
            background:linear-gradient(135deg,var(--cx-primary),var(--cx-accent));
            -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"></i>
        AI File Processing
        <span class="ai-badge" style="font-size:.65rem;vertical-align:middle;margin-left:.4rem;">✨ AI</span>
    </h1>
    <p>Upload any document or image, describe what you need, and let AI do the heavy lifting</p>
</div>

<?php if (!$configured): ?>
<!-- ── Not configured notice ─────────────────────────────────────────── -->
<div class="cx-notice" style="border-color:var(--cx-warning);background:rgba(245,158,11,.08);margin-bottom:1.5rem;">
    <i class="fa-solid fa-triangle-exclamation" style="color:var(--cx-warning);font-size:1.1rem;"></i>
    <div>
        <strong>AI processing is not configured.</strong>
        Ask an admin to enable AI in
        <a href="/projects/convertx/settings" style="color:var(--cx-primary);font-weight:600;">ConvertX → Settings</a>.
    </div>
</div>
<?php endif; ?>

<!-- ── How it works strip ─────────────────────────────────────────────── -->
<div class="cx-aip-how-strip">
    <div class="cx-aip-step">
        <div class="cx-aip-step-icon" style="background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));">
            <i class="fa-solid fa-file-arrow-up"></i>
        </div>
        <div>
            <div class="cx-aip-step-title">1. Upload File</div>
            <div class="cx-aip-step-desc">PDF, DOCX, XLSX, image, CSV…</div>
        </div>
    </div>
    <i class="fa-solid fa-chevron-right cx-aip-arrow"></i>
    <div class="cx-aip-step">
        <div class="cx-aip-step-icon" style="background:linear-gradient(135deg,#7c3aed,#06b6d4);">
            <i class="fa-solid fa-pen-to-square"></i>
        </div>
        <div>
            <div class="cx-aip-step-title">2. Add Instructions</div>
            <div class="cx-aip-step-desc">Summarize, translate, extract…</div>
        </div>
    </div>
    <i class="fa-solid fa-chevron-right cx-aip-arrow"></i>
    <div class="cx-aip-step">
        <div class="cx-aip-step-icon" style="background:linear-gradient(135deg,#0891b2,#10b981);">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
        </div>
        <div>
            <div class="cx-aip-step-title">3. AI Processes</div>
            <div class="cx-aip-step-desc">AI returns the result instantly</div>
        </div>
    </div>
</div>

<!-- ── Main two-column grid ───────────────────────────────────────────── -->
<div class="cx-aip-grid">

    <!-- ══ Left: Input card ══════════════════════════════════════════════ -->
    <div class="card cx-holo" style="margin-bottom:0;">
        <div class="card-header">
            <i class="fa-solid fa-file-arrow-up"></i>
            <span>File &amp; Instructions</span>
        </div>

        <form id="aiProcessForm" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <!-- Upload zone -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-cloud-arrow-up" style="color:var(--cx-primary);"></i>
                    File
                    <span style="font-weight:400;font-size:.72rem;color:var(--text-muted);margin-left:.25rem;">(optional)</span>
                </label>
                <div class="upload-zone" id="uploadZone">
                    <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
                    <p style="font-weight:600;font-size:.875rem;margin:.35rem 0 .15rem;">
                        Drag &amp; drop or <strong>click to browse</strong>
                    </p>
                    <p style="font-size:.73rem;color:var(--text-muted);margin:0;">
                        PDF · DOCX · XLSX · PNG · JPG · TXT · CSV and more
                    </p>
                    <input type="file" name="file" id="fileInput" style="display:none;">
                </div>

                <!-- Selected file chip -->
                <div id="selectedFile" class="cx-aip-file-chip" style="display:none;">
                    <div class="cx-aip-file-info">
                        <i id="fileTypeIcon" class="fa-solid fa-file" style="color:var(--cx-primary);font-size:1.1rem;flex-shrink:0;"></i>
                        <div style="min-width:0;">
                            <div id="fileName" style="font-weight:600;font-size:.82rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text-primary);"></div>
                            <div id="fileSize" style="font-size:.72rem;color:var(--text-muted);"></div>
                        </div>
                    </div>
                    <button type="button" class="cx-aip-file-remove" id="removeFileBtn" title="Remove file">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- Instructions textarea -->
            <div class="form-group">
                <label class="form-label" for="remarks">
                    <i class="fa-solid fa-pen-to-square" style="color:var(--cx-primary);"></i>
                    Instructions <span style="color:var(--cx-danger);font-size:.85em;">*</span>
                </label>
                <textarea name="remarks" id="remarks" rows="5" class="form-control cx-aip-textarea"
                    placeholder="e.g. Summarize this document in bullet points
e.g. Extract all invoice line items as a table
e.g. Translate to French and improve the tone"
                    required><?= htmlspecialchars($_POST['remarks'] ?? '') ?></textarea>
                <div style="margin-top:.3rem;font-size:.72rem;color:var(--text-muted);display:flex;align-items:flex-start;gap:.35rem;">
                    <i class="fa-solid fa-lightbulb" style="color:var(--cx-warning);margin-top:.1rem;flex-shrink:0;"></i>
                    <span><strong>Tip:</strong> Be specific — "List all names and dates", "Summarize in 5 bullet points", "Convert to JSON"</span>
                </div>
            </div>

            <!-- Quick prompt chips -->
            <div class="form-group" style="margin-bottom:1.25rem;">
                <div class="cx-aip-section-label">Quick Prompts</div>
                <div class="cx-aip-chips">
                    <?php
                    $quickPrompts = [
                        ['icon' => 'fa-list-check',    'label' => 'Summarize in bullet points',    'prompt' => 'Summarize this document in bullet points'],
                        ['icon' => 'fa-magnifying-glass','label'=> 'Extract names, dates & amounts','prompt' => 'Extract all names, dates and amounts'],
                        ['icon' => 'fa-clipboard-list', 'label' => 'List key action items',         'prompt' => 'List the key action items'],
                        ['icon' => 'fa-language',       'label' => 'Translate to English',          'prompt' => 'Translate to English'],
                        ['icon' => 'fa-language',       'label' => 'Translate to French',           'prompt' => 'Translate to French'],
                        ['icon' => 'fa-table',          'label' => 'Extract data as table',         'prompt' => 'Extract data as a table'],
                        ['icon' => 'fa-spell-check',    'label' => 'Proofread & fix grammar',       'prompt' => 'Proofread and improve grammar'],
                        ['icon' => 'fa-circle-question','label' => 'What is this about?',           'prompt' => 'What is this document about?'],
                        ['icon' => 'fa-file-lines',     'label' => 'Convert to plain text',         'prompt' => 'Convert to plain text, remove formatting'],
                    ];
                    foreach ($quickPrompts as $p): ?>
                    <button type="button" class="cx-aip-chip"
                            onclick="applyPrompt(<?= json_encode($p['prompt'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)"
                            title="<?= htmlspecialchars($p['prompt']) ?>">
                        <i class="fa-solid <?= $p['icon'] ?>"></i>
                        <?= htmlspecialchars($p['label']) ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn btn-primary cx-aip-submit" id="submitBtn"
                    <?= !$configured ? 'disabled' : '' ?>>
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span>Process with AI</span>
                <span id="processingDots" style="display:none;letter-spacing:.1em;">…</span>
            </button>
        </form>
    </div>

    <!-- ══ Right: Output card ════════════════════════════════════════════ -->
    <div class="card" id="outputCard" style="margin-bottom:0;display:flex;flex-direction:column;">

        <!-- Card header with meta info -->
        <div class="card-header" style="flex-shrink:0;">
            <i class="fa-solid fa-sparkles" style="color:var(--cx-primary);"></i>
            <span>AI Output</span>
            <div id="outputStatusBadge" style="margin-left:.5rem;display:none;">
                <span class="badge badge-completed" id="outputStatusInner"></span>
            </div>
            <div id="outputMeta" class="cx-aip-meta"></div>
        </div>

        <!-- Progress bar (visible while loading) -->
        <div id="outputProgress" class="cx-aip-progress-wrap" style="display:none;">
            <div class="cx-aip-progress-bar"></div>
        </div>

        <!-- Output body -->
        <div id="outputArea" class="cx-aip-output-area">

            <!-- Placeholder state -->
            <div id="outputPlaceholder" class="cx-aip-placeholder">
                <div class="cx-aip-placeholder-icon">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <div class="cx-aip-placeholder-title">Ready to process</div>
                <div class="cx-aip-placeholder-sub">
                    Upload a file (optional), add instructions,<br>then click <strong>Process with AI</strong>
                </div>
                <div class="cx-aip-placeholder-steps">
                    <span><i class="fa-solid fa-check-circle" style="color:var(--cx-success);"></i> PDF, DOCX, XLSX, images &amp; more</span>
                    <span><i class="fa-solid fa-check-circle" style="color:var(--cx-success);"></i> Images via vision AI</span>
                    <span><i class="fa-solid fa-check-circle" style="color:var(--cx-success);"></i> Results in seconds</span>
                </div>
            </div>

            <!-- Loading state -->
            <div id="outputSpinner" class="cx-aip-spinner" style="display:none;">
                <div class="cx-aip-spinner-ring"></div>
                <div class="cx-aip-spinner-text">
                    <span id="spinnerMsg">Sending to AI…</span>
                </div>
            </div>

            <!-- Error state -->
            <div id="outputError" style="display:none;padding:.25rem;"></div>

            <!-- Result state -->
            <div id="outputContent" style="display:none;">
                <div id="outputRendered" class="cx-aip-rendered"></div>
            </div>

        </div>

        <!-- Action bar (copy / download) -->
        <div id="outputActions" class="cx-aip-actions">
            <button type="button" class="btn btn-sm btn-secondary" id="copyBtn" onclick="copyOutput()">
                <i class="fa-solid fa-copy"></i> Copy
            </button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="downloadOutput()">
                <i class="fa-solid fa-download"></i> Download TXT
            </button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="downloadMarkdown()">
                <i class="fa-solid fa-file-code"></i> Download MD
            </button>
            <div id="outputTokenInfo" class="cx-aip-token-info"></div>
        </div>
    </div>

</div>

<!-- ── AI capabilities reminder ───────────────────────────────────────── -->
<div class="cx-aip-caps-strip">
    <div class="cx-aip-cap"><i class="fa-solid fa-eye"></i> OCR images &amp; scans</div>
    <div class="cx-aip-cap"><i class="fa-solid fa-list-check"></i> Summarize documents</div>
    <div class="cx-aip-cap"><i class="fa-solid fa-language"></i> Translate any language</div>
    <div class="cx-aip-cap"><i class="fa-solid fa-table"></i> Extract structured data</div>
    <div class="cx-aip-cap"><i class="fa-solid fa-spell-check"></i> Proofread &amp; rewrite</div>
    <div class="cx-aip-cap"><i class="fa-solid fa-code"></i> Parse &amp; convert formats</div>
</div>

<!-- ── Styles ─────────────────────────────────────────────────────────── -->
<style>
/* ── How-it-works strip ──────────────────────────────────────────────── */
.cx-aip-how-strip {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: .75rem;
    padding: .875rem 1.25rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}
.cx-aip-step {
    display: flex;
    align-items: center;
    gap: .625rem;
    flex: 1;
    min-width: 140px;
}
.cx-aip-step-icon {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: .5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: .875rem;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(99,102,241,.35);
}
.cx-aip-step-title { font-size: .8rem; font-weight: 700; color: var(--text-primary); }
.cx-aip-step-desc  { font-size: .7rem; color: var(--text-muted); }
.cx-aip-arrow { color: var(--text-muted); font-size: .75rem; flex-shrink: 0; }
@media (max-width: 600px) {
    .cx-aip-how-strip { gap: .625rem; padding: .75rem 1rem; }
    .cx-aip-arrow { display: none; }
}

/* ── Main grid ───────────────────────────────────────────────────────── */
.cx-aip-grid {
    display: grid;
    grid-template-columns: 1.05fr 1fr;
    gap: 1.25rem;
    align-items: start;
    margin-bottom: 1.5rem;
}
@media (max-width: 900px) {
    .cx-aip-grid { grid-template-columns: 1fr; }
}

/* ── Textarea matching theme ────────────────────────────────────────── */
.cx-aip-textarea {
    resize: vertical;
    min-height: 6.5rem;
    line-height: 1.6;
    font-size: .875rem;
}

/* ── File chip ──────────────────────────────────────────────────────── */
.cx-aip-file-chip {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .625rem;
    margin-top: .5rem;
    padding: .5rem .75rem;
    background: rgba(16,185,129,.08);
    border: 1px solid rgba(16,185,129,.3);
    border-radius: .5rem;
    animation: cx-slide-down .25s ease;
}
.cx-aip-file-info {
    display: flex;
    align-items: center;
    gap: .5rem;
    min-width: 0;
    flex: 1;
}
.cx-aip-file-remove {
    background: none;
    border: none;
    color: var(--cx-danger);
    cursor: pointer;
    padding: .25rem .375rem;
    border-radius: .35rem;
    font-size: .8rem;
    opacity: .7;
    transition: opacity .15s, background .15s;
    flex-shrink: 0;
}
.cx-aip-file-remove:hover { opacity: 1; background: rgba(239,68,68,.1); }

/* ── Section label ──────────────────────────────────────────────────── */
.cx-aip-section-label {
    font-size: .7rem;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .07em;
    margin-bottom: .4rem;
}

/* ── Quick-prompt chips ─────────────────────────────────────────────── */
.cx-aip-chips {
    display: flex;
    flex-wrap: wrap;
    gap: .35rem;
}
.cx-aip-chip {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    background: var(--bg-tertiary);
    border: 1px solid var(--border-color);
    border-radius: 2rem;
    padding: .275rem .65rem;
    font-size: .7rem;
    font-family: inherit;
    color: var(--text-secondary);
    cursor: pointer;
    transition: border-color .15s, color .15s, background .15s, transform .15s;
    white-space: nowrap;
}
.cx-aip-chip i { font-size: .65rem; }
.cx-aip-chip:hover {
    border-color: var(--cx-primary);
    color: var(--cx-primary);
    background: rgba(99,102,241,.07);
    transform: translateY(-1px);
}
.cx-aip-chip.active {
    background: linear-gradient(135deg, var(--cx-primary), var(--cx-secondary));
    border-color: transparent;
    color: #fff;
}

/* ── Submit button full-width ───────────────────────────────────────── */
.cx-aip-submit {
    width: 100%;
    justify-content: center;
    padding: .825rem 1.25rem;
    font-size: .9rem;
    gap: .5rem;
}

/* ── Output card meta ───────────────────────────────────────────────── */
.cx-aip-meta {
    margin-left: auto;
    font-size: .72rem;
    font-weight: 400;
    color: var(--text-muted);
    text-align: right;
    max-width: 220px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* ── Progress bar ───────────────────────────────────────────────────── */
.cx-aip-progress-wrap {
    height: 3px;
    background: var(--border-color);
    overflow: hidden;
    border-radius: 0;
    flex-shrink: 0;
}
.cx-aip-progress-bar {
    height: 100%;
    width: 40%;
    background: linear-gradient(90deg, var(--cx-primary), var(--cx-accent), var(--cx-secondary));
    background-size: 200% 100%;
    animation: cx-progress-sweep 1.6s ease-in-out infinite;
    border-radius: 3px;
}
@keyframes cx-progress-sweep {
    0%   { transform: translateX(-150%); }
    100% { transform: translateX(400%); }
}

/* ── Output area ────────────────────────────────────────────────────── */
.cx-aip-output-area {
    flex: 1;
    padding: 1.25rem;
    min-height: 260px;
    overflow-y: auto;
}

/* ── Placeholder ────────────────────────────────────────────────────── */
.cx-aip-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .625rem;
    text-align: center;
    padding: 2rem 1rem;
    color: var(--text-muted);
    min-height: 220px;
}
.cx-aip-placeholder-icon {
    width: 4.5rem;
    height: 4.5rem;
    border-radius: 50%;
    background: var(--bg-tertiary);
    border: 1px dashed var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    opacity: .5;
    margin-bottom: .25rem;
}
.cx-aip-placeholder-title { font-size: .95rem; font-weight: 700; color: var(--text-secondary); }
.cx-aip-placeholder-sub   { font-size: .78rem; line-height: 1.6; }
.cx-aip-placeholder-steps {
    display: flex;
    flex-wrap: wrap;
    gap: .625rem;
    justify-content: center;
    margin-top: .5rem;
}
.cx-aip-placeholder-steps span {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    font-size: .72rem;
    color: var(--text-secondary);
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 2rem;
    padding: .2rem .6rem;
}

/* ── Spinner ────────────────────────────────────────────────────────── */
.cx-aip-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    padding: 3.5rem 1rem;
    min-height: 220px;
}
.cx-aip-spinner-ring {
    width: 3rem;
    height: 3rem;
    border: 3px solid var(--border-color);
    border-top-color: var(--cx-primary);
    border-radius: 50%;
    animation: cx-spin 0.8s linear infinite;
}
.cx-aip-spinner-text {
    font-size: .85rem;
    color: var(--text-secondary);
    font-weight: 500;
}

/* ── Rendered markdown output ───────────────────────────────────────── */
.cx-aip-rendered {
    font-size: .875rem;
    line-height: 1.75;
    color: var(--text-primary);
    word-break: break-word;
}
.cx-aip-rendered h1,
.cx-aip-rendered h2,
.cx-aip-rendered h3 {
    font-weight: 700;
    margin: 1.1em 0 .4em;
    line-height: 1.3;
}
.cx-aip-rendered h1 { font-size: 1.3em; }
.cx-aip-rendered h2 { font-size: 1.15em; }
.cx-aip-rendered h3 { font-size: 1em; }
.cx-aip-rendered p  { margin: .6em 0; }
.cx-aip-rendered ul,
.cx-aip-rendered ol { margin: .5em 0 .5em 1.4em; }
.cx-aip-rendered li { margin-bottom: .25em; }
.cx-aip-rendered strong { font-weight: 700; color: var(--text-primary); }
.cx-aip-rendered em     { font-style: italic; }
.cx-aip-rendered code {
    background: var(--cx-code-bg, rgba(0,0,0,.25));
    padding: .1em .35em;
    border-radius: .25rem;
    font-size: .85em;
    font-family: 'JetBrains Mono','Fira Code',monospace;
    color: var(--cx-accent);
}
.cx-aip-rendered pre {
    background: var(--cx-code-bg, rgba(0,0,0,.25));
    border: 1px solid var(--border-color);
    border-radius: .5rem;
    padding: .75rem 1rem;
    overflow-x: auto;
    margin: .75em 0;
}
.cx-aip-rendered pre code {
    background: none;
    padding: 0;
    color: var(--text-primary);
    font-size: .82em;
    display: block;
}
.cx-aip-rendered table {
    width: 100%;
    border-collapse: collapse;
    margin: .75em 0;
    font-size: .82em;
}
.cx-aip-rendered th,
.cx-aip-rendered td {
    border: 1px solid var(--border-color);
    padding: .4rem .65rem;
    text-align: left;
}
.cx-aip-rendered th {
    background: rgba(99,102,241,.08);
    font-weight: 600;
    color: var(--text-secondary);
    font-size: .8em;
    text-transform: uppercase;
    letter-spacing: .03em;
}
.cx-aip-rendered hr {
    border: none;
    border-top: 1px solid var(--border-color);
    margin: 1em 0;
}
.cx-aip-rendered blockquote {
    border-left: 3px solid var(--cx-primary);
    padding-left: .875rem;
    margin: .75em 0;
    color: var(--text-secondary);
    font-style: italic;
}

/* ── Action bar ──────────────────────────────────────────────────────── */
.cx-aip-actions {
    display: none;
    align-items: center;
    gap: .5rem;
    padding: .75rem 1.25rem;
    border-top: 1px solid var(--border-color);
    flex-wrap: wrap;
    flex-shrink: 0;
}
.cx-aip-actions.visible { display: flex; }
.cx-aip-token-info {
    margin-left: auto;
    font-size: .7rem;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: .25rem;
    flex-shrink: 0;
}

/* ── Capabilities strip ──────────────────────────────────────────────── */
.cx-aip-caps-strip {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    margin-top: .25rem;
}
.cx-aip-cap {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    font-size: .72rem;
    color: var(--text-muted);
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 2rem;
    padding: .25rem .65rem;
    transition: border-color .15s, color .15s;
}
.cx-aip-cap i { color: var(--cx-primary); font-size: .65rem; }
.cx-aip-cap:hover { border-color: var(--cx-primary); color: var(--text-secondary); }
</style>

<!-- ── Scripts ────────────────────────────────────────────────────────── -->
<script>
(function () {
'use strict';

// ── DOM refs ──────────────────────────────────────────────────────────
var fileInput   = document.getElementById('fileInput');
var uploadZone  = document.getElementById('uploadZone');
var selectedEl  = document.getElementById('selectedFile');
var fileNameEl  = document.getElementById('fileName');
var fileSizeEl  = document.getElementById('fileSize');
var fileIconEl  = document.getElementById('fileTypeIcon');
var removeBtn   = document.getElementById('removeFileBtn');
var remarksEl   = document.getElementById('remarks');
var submitBtn   = document.getElementById('submitBtn');

// ── File type → FA icon map ───────────────────────────────────────────
var iconMap = {
    pdf:'fa-file-pdf', docx:'fa-file-word', doc:'fa-file-word',
    xlsx:'fa-file-excel', xls:'fa-file-excel', csv:'fa-table',
    pptx:'fa-file-powerpoint', ppt:'fa-file-powerpoint',
    txt:'fa-file-lines', md:'fa-file-code', html:'fa-code',
    jpg:'fa-file-image', jpeg:'fa-file-image', png:'fa-file-image',
    gif:'fa-file-image', webp:'fa-file-image', bmp:'fa-file-image',
    svg:'fa-vector-square', zip:'fa-file-zipper',
};

// ── Upload zone ───────────────────────────────────────────────────────
uploadZone.addEventListener('click', function(e) {
    if (e.target === removeBtn || removeBtn.contains(e.target)) return;
    fileInput.click();
});
uploadZone.addEventListener('dragover', function(e) {
    e.preventDefault();
    uploadZone.classList.add('drag-over');
});
uploadZone.addEventListener('dragleave', function() {
    uploadZone.classList.remove('drag-over');
});
uploadZone.addEventListener('drop', function(e) {
    e.preventDefault();
    uploadZone.classList.remove('drag-over');
    if (e.dataTransfer.files[0]) {
        setFileInputFiles(e.dataTransfer.files);
        showSelected(e.dataTransfer.files[0]);
    }
});
fileInput.addEventListener('change', function() {
    if (fileInput.files[0]) showSelected(fileInput.files[0]);
});
removeBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    fileInput.value = '';
    selectedEl.style.display = 'none';
    uploadZone.classList.remove('has-file');
});

function setFileInputFiles(fileList) {
    try {
        var dt = new DataTransfer();
        dt.items.add(fileList[0]);
        fileInput.files = dt.files;
    } catch(e) {}
}

function showSelected(file) {
    var ext = (file.name.split('.').pop() || '').toLowerCase();
    var icon = iconMap[ext] || 'fa-file';
    fileIconEl.className = 'fa-solid ' + icon;
    fileIconEl.style.color = ext === 'pdf' ? '#ef4444'
        : ['docx','doc'].includes(ext) ? '#2563eb'
        : ['xlsx','xls','csv'].includes(ext) ? '#16a34a'
        : ['jpg','jpeg','png','gif','webp','bmp'].includes(ext) ? '#7c3aed'
        : 'var(--cx-primary)';
    fileNameEl.textContent = file.name;
    fileSizeEl.textContent = file.size >= 1048576
        ? (file.size / 1048576).toFixed(1) + ' MB'
        : (file.size / 1024).toFixed(1) + ' KB';
    selectedEl.style.display = 'flex';
    uploadZone.classList.add('has-file');
}

// ── Quick prompts ─────────────────────────────────────────────────────
window.applyPrompt = function(text) {
    remarksEl.value = text;
    remarksEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    remarksEl.focus();
    // Highlight active chip
    document.querySelectorAll('.cx-aip-chip').forEach(function(c) {
        c.classList.toggle('active', c.getAttribute('title') === text);
    });
};

// ── Lightweight Markdown renderer ────────────────────────────────────
function renderMarkdown(raw) {
    var lines = raw.split('\n');
    var html  = '';
    var inCode = false, codeLang = '', codeBuf = '';
    var inTable = false, tableBuf = [];

    function flushTable() {
        if (!tableBuf.length) return;
        var th = '';
        var rows = tableBuf.filter(function(r) { return !/^[\s|:-]+$/.test(r.replace(/\|/g,'')); });
        rows.forEach(function(row, i) {
            var cells = row.split('|').map(function(c) { return c.trim(); }).filter(function(c) { return c; });
            if (i === 0) {
                th = '<thead><tr>' + cells.map(function(c) { return '<th>' + inlinemd(c) + '</th>'; }).join('') + '</tr></thead>';
            } else {
                th += (i === 1 ? '<tbody>' : '') + '<tr>' + cells.map(function(c) { return '<td>' + inlinemd(c) + '</td>'; }).join('') + '</tr>';
            }
        });
        html += '<table>' + th + '</tbody></table>';
        tableBuf = []; inTable = false;
    }

    lines.forEach(function(line) {
        // Code fences
        var fence = line.match(/^```(\w*)/);
        if (fence) {
            if (!inCode) { inCode = true; codeLang = fence[1]; codeBuf = ''; return; }
            html += '<pre><code class="language-' + escHtml(codeLang) + '">' + escHtml(codeBuf) + '</code></pre>';
            inCode = false; codeLang = ''; codeBuf = ''; return;
        }
        if (inCode) { codeBuf += (codeBuf ? '\n' : '') + line; return; }

        // Tables
        if (line.includes('|')) {
            inTable = true;
            tableBuf.push(line);
            return;
        }
        if (inTable) flushTable();

        // Headings
        var hm = line.match(/^(#{1,3})\s+(.+)/);
        if (hm) { html += '<h' + hm[1].length + '>' + inlinemd(hm[2]) + '</h' + hm[1].length + '>'; return; }

        // HR
        if (/^[-*_]{3,}\s*$/.test(line)) { html += '<hr>'; return; }

        // Blockquote
        var bq = line.match(/^>\s?(.*)/);
        if (bq) { html += '<blockquote>' + inlinemd(bq[1]) + '</blockquote>'; return; }

        // Unordered list
        var ul = line.match(/^[\-\*\+]\s+(.*)/);
        if (ul) { html += '<ul style="list-style:disc"><li>' + inlinemd(ul[1]) + '</li></ul>'; return; }

        // Ordered list
        var ol = line.match(/^\d+\.\s+(.*)/);
        if (ol) { html += '<ol><li>' + inlinemd(ol[1]) + '</li></ol>'; return; }

        // Blank line
        if (line.trim() === '') { html += '<p></p>'; return; }

        // Paragraph
        html += '<p>' + inlinemd(line) + '</p>';
    });

    if (inTable) flushTable();
    if (inCode && codeBuf) html += '<pre><code>' + escHtml(codeBuf) + '</code></pre>';

    // Merge consecutive same-type list items
    html = html
        .replace(/<\/ul>\s*<ul[^>]*>/g, '')
        .replace(/<\/ol>\s*<ol>/g, '');

    return html;
}

function inlinemd(text) {
    return escHtml(text)
        .replace(/`([^`]+)`/g, '<code>$1</code>')
        .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
        .replace(/\*([^*]+)\*/g, '<em>$1</em>')
        .replace(/~~([^~]+)~~/g, '<del>$1</del>')
        .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener" style="color:var(--cx-primary)">$1</a>');
}

function escHtml(s) {
    return String(s)
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;');
}

// ── Form submit ───────────────────────────────────────────────────────
var lastOutput = '';

document.getElementById('aiProcessForm').addEventListener('submit', function(e) {
    e.preventDefault();

    var remarks = remarksEl.value.trim();
    if (!remarks) {
        CXNotify.warning('Please enter instructions or remarks.');
        remarksEl.focus();
        return;
    }

    // UI: loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Processing…</span>';

    document.getElementById('outputPlaceholder').style.display = 'none';
    document.getElementById('outputContent').style.display     = 'none';
    document.getElementById('outputError').style.display       = 'none';
    document.getElementById('outputSpinner').style.display     = 'flex';
    document.getElementById('outputProgress').style.display    = 'block';
    document.getElementById('outputActions').classList.remove('visible');
    document.getElementById('outputMeta').textContent          = '';
    document.getElementById('outputStatusBadge').style.display = 'none';
    document.getElementById('outputTokenInfo').textContent     = '';

    // Cycle spinner messages
    var msgs = ['Sending to AI…', 'Reading your file…', 'Thinking…', 'Generating response…'];
    var mi = 0;
    var msgEl = document.getElementById('spinnerMsg');
    var msgTimer = setInterval(function() {
        mi = (mi + 1) % msgs.length;
        msgEl.textContent = msgs[mi];
    }, 2400);

    fetch('/projects/convertx/ai-process', {
        method: 'POST',
        body:   new FormData(this),
    })
    .then(function(r) {
        if (!r.ok) {
            return r.text().then(function(t) {
                throw new Error('HTTP ' + r.status + ': ' + t.substring(0, 300));
            });
        }
        return r.json();
    })
    .then(function(data) {
        clearInterval(msgTimer);
        document.getElementById('outputSpinner').style.display  = 'none';
        document.getElementById('outputProgress').style.display = 'none';

        if (!data.success) {
            showError(data.error || 'Unknown error');
            return;
        }

        lastOutput = data.output || '';

        // Render markdown
        document.getElementById('outputRendered').innerHTML = renderMarkdown(lastOutput);
        document.getElementById('outputContent').style.display = 'block';
        document.getElementById('outputActions').classList.add('visible');

        // Meta info
        var meta = [];
        if (data.filename) meta.push('<i class="fa-solid fa-file" style="color:var(--cx-primary);"></i> ' + escHtml(data.filename));
        document.getElementById('outputMeta').innerHTML = meta.join(' &nbsp;·&nbsp; ');

        // Status badge
        var badge = document.getElementById('outputStatusBadge');
        document.getElementById('outputStatusInner').textContent = '✓ Completed';
        badge.style.display = 'inline-flex';

        // Token info
        if (data.tokens_used) {
            document.getElementById('outputTokenInfo').innerHTML =
                '<i class="fa-solid fa-circle-dot"></i> ' + data.tokens_used.toLocaleString() + ' tokens';
        }

        CXNotify.success('AI processing complete!');
    })
    .catch(function(err) {
        clearInterval(msgTimer);
        document.getElementById('outputSpinner').style.display  = 'none';
        document.getElementById('outputProgress').style.display = 'none';
        showError(err.message || 'Request failed');
    })
    .finally(function() {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> <span>Process with AI</span>';
    });
});

function showError(msg) {
    var el = document.getElementById('outputError');
    el.style.display = 'block';
    el.innerHTML = '<div class="cx-notice" style="border-color:var(--cx-danger);background:rgba(239,68,68,.07);margin:0;">'
        + '<i class="fa-solid fa-circle-exclamation" style="color:var(--cx-danger);font-size:1rem;flex-shrink:0;"></i>'
        + '<div><strong>Error:</strong> ' + escHtml(msg) + '</div></div>';
    CXNotify.error(msg.length > 100 ? msg.substring(0, 97) + '…' : msg);
}

// ── Copy & Download ───────────────────────────────────────────────────
window.copyOutput = function() {
    if (!lastOutput) return;
    var btn = document.getElementById('copyBtn');
    var orig = btn.innerHTML;

    function onCopied() {
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
        setTimeout(function() { btn.innerHTML = orig; }, 1800);
    }
    function onFailed() {
        btn.innerHTML = orig;
        CXNotify.error('Copy failed — please select and copy the text manually.');
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(lastOutput).then(onCopied).catch(function() {
            // Try legacy fallback
            if (legacyCopy(lastOutput)) { onCopied(); } else { onFailed(); }
        });
    } else {
        if (legacyCopy(lastOutput)) { onCopied(); } else { onFailed(); }
    }
};

function legacyCopy(text) {
    try {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.cssText = 'position:fixed;top:-9999px;left:-9999px;opacity:0;';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        var ok = document.execCommand('copy');
        document.body.removeChild(ta);
        return ok;
    } catch(e) {
        return false;
    }
}

window.downloadOutput = function() {
    if (!lastOutput) return;
    triggerDownload(lastOutput, 'text/plain', 'ai-output-' + Date.now() + '.txt');
};

window.downloadMarkdown = function() {
    if (!lastOutput) return;
    triggerDownload(lastOutput, 'text/markdown', 'ai-output-' + Date.now() + '.md');
};

function triggerDownload(content, mime, filename) {
    var blob = new Blob([content], {type: mime});
    var a    = document.createElement('a');
    a.href   = URL.createObjectURL(blob);
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(a.href);
}

})();
</script>
