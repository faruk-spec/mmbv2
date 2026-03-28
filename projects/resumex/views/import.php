<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<style>
.rxi-wrap {
    max-width: 1100px;
    margin: 0 auto;
    padding: 36px 24px 60px;
}
.rxi-back {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: var(--text-secondary);
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    margin-bottom: 32px;
    transition: color 0.2s;
}
.rxi-back:hover { color: var(--cyan); text-decoration: none; }
.rxi-header {
    text-align: center;
    margin-bottom: 36px;
}
.rxi-header h1 {
    font-size: clamp(1.8rem, 4vw, 2.4rem);
    font-weight: 800;
    letter-spacing: -0.5px;
    background: linear-gradient(135deg, var(--cyan) 0%, var(--purple) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0 0 10px;
}
.rxi-header p { color: var(--text-secondary); font-size: 1rem; margin: 0; }
.rxi-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 28px;
    margin-bottom: 24px;
}
.rxi-card h2 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 18px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.rxi-card h2 .step {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--cyan), var(--purple));
    color: #06060a;
    font-size: 0.75rem;
    font-weight: 800;
    flex-shrink: 0;
}
.rxi-label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 7px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.rxi-input {
    width: 100%;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 9px;
    color: var(--text-primary);
    font-size: 0.95rem;
    padding: 11px 14px;
    transition: border-color 0.2s;
    box-sizing: border-box;
}
.rxi-input:focus {
    outline: none;
    border-color: rgba(0,240,255,0.45);
    box-shadow: 0 0 0 3px rgba(0,240,255,0.08);
}
.rxi-textarea {
    min-height: 160px;
    resize: vertical;
    font-family: monospace;
    font-size: 0.85rem;
}
.rxi-or {
    text-align: center;
    color: var(--text-secondary);
    font-size: 0.8rem;
    font-weight: 600;
    margin: 14px 0;
    position: relative;
}
.rxi-or::before, .rxi-or::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 42%;
    height: 1px;
    background: var(--border-color);
}
.rxi-or::before { left: 0; }
.rxi-or::after { right: 0; }
.rxi-dropzone {
    border: 2px dashed var(--border-color);
    border-radius: 10px;
    padding: 28px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
}
.rxi-dropzone:hover, .rxi-dropzone.drag-over {
    border-color: rgba(0,240,255,0.5);
    background: rgba(0,240,255,0.04);
}
.rxi-dropzone-icon { font-size: 2rem; color: var(--text-secondary); margin-bottom: 8px; }
.rxi-dropzone p { color: var(--text-secondary); font-size: 0.85rem; margin: 0; }
.rxi-dropzone .rxi-file-name { font-size: 0.82rem; color: var(--cyan); margin-top: 6px; font-weight: 600; }
.rxi-submit {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--cyan), var(--purple));
    color: #06060a;
    font-size: 1rem;
    font-weight: 800;
    cursor: pointer;
    letter-spacing: 0.3px;
    transition: opacity 0.2s;
    margin-top: 8px;
}
.rxi-submit:hover { opacity: 0.9; }
.rxi-alert {
    background: rgba(239,68,68,0.1);
    border: 1px solid rgba(239,68,68,0.3);
    border-radius: 8px;
    padding: 12px 16px;
    color: #f87171;
    font-size: 0.88rem;
    margin-bottom: 20px;
}
</style>

<div class="rxi-wrap">
    <a href="/projects/resumex/create" class="rxi-back">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        Back to Create
    </a>

    <?php $isLinkedIn = !empty($_GET['source']) && $_GET['source'] === 'linkedin'; ?>
    <div class="rxi-header">
        <?php if ($isLinkedIn): ?>
        <h1>LinkedIn Import</h1>
        <p>Import your LinkedIn profile data to build a resume. Export your profile as JSON from LinkedIn, then upload or paste it below.</p>
        <div style="margin-top:16px;padding:14px 18px;background:rgba(10,102,194,0.1);border:1px solid rgba(10,102,194,0.3);border-radius:10px;font-size:0.82rem;color:var(--text-secondary);line-height:1.6;">
            <strong style="color:#3b82f6;display:block;margin-bottom:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px;"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
                How to export from LinkedIn:
            </strong>
            Go to <strong>LinkedIn Settings</strong> → <strong>Data Privacy</strong> → <strong>Get a copy of your data</strong> → select <em>Profile</em> and request download. Once received, upload the JSON file below.
        </div>
        <?php else: ?>
        <h1>Import Resume</h1>
        <p>Paste JSON or upload a previously exported resume file to get started.</p>
        <?php endif; ?>
    </div>

    <?php if (($_GET['error'] ?? '') === 'token'): ?>
    <div class="rxi-alert"><i class="fas fa-exclamation-circle"></i> Security token expired. Please try again.</div>
    <?php elseif (!empty($_GET['error'])): ?>
    <div class="rxi-alert"><i class="fas fa-exclamation-circle"></i> Could not import resume. Please check your file and try again.</div>
    <?php endif; ?>

    <form method="POST" action="/projects/resumex/import" enctype="multipart/form-data" id="importForm">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <!-- Step 1: Resume name -->
        <div class="rxi-card">
            <h2><span class="step">1</span> Resume Name</h2>
            <label class="rxi-label" for="importTitle">Name your resume</label>
            <input class="rxi-input" type="text" id="importTitle" name="title"
                   placeholder="e.g. My Software Engineer Resume"
                   value="<?= $isLinkedIn ? 'My LinkedIn Resume' : 'My Imported Resume' ?>" maxlength="200" required>
        </div>

        <!-- Step 2: Upload source -->
        <div class="rxi-card">
            <h2><span class="step">2</span> Resume Data</h2>

            <label class="rxi-label" for="resumeJson">Paste JSON</label>
            <textarea class="rxi-input rxi-textarea" id="resumeJson" name="resume_json"
                      placeholder='{"basics":{"name":"Jane Doe",...}}'></textarea>

            <div class="rxi-or">OR</div>

            <label class="rxi-label">Upload JSON File</label>
            <div class="rxi-dropzone" id="dropzone" onclick="document.getElementById('resumeFile').click();">
                <div class="rxi-dropzone-icon"><i class="fas fa-file-upload"></i></div>
                <p>Click to choose a <strong>.json</strong> file, or drag &amp; drop here</p>
                <div class="rxi-file-name" id="fileName" style="display:none;"></div>
            </div>
            <input type="file" id="resumeFile" name="resume_file" accept=".json,application/json" style="display:none;">
        </div>

        <!-- Step 3: Template picker (full grid matching create.php) -->
        <div class="rxi-card" style="padding:28px 28px 24px;">
            <h2 style="margin-bottom:16px;"><span class="step">3</span> Choose a Template</h2>
            <p style="font-size:0.875rem;color:var(--text-secondary);margin:0 0 20px;">Pick a design — you can change it later in the editor.</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;">
                <?php $first = true; foreach ($allThemes as $tKey => $tTheme):
                    $tBg  = htmlspecialchars($tTheme['backgroundColor'] ?? '#111');
                    $tPri = htmlspecialchars($tTheme['primaryColor'] ?? '#00f0ff');
                    $tSec = htmlspecialchars($tTheme['secondaryColor'] ?? $tTheme['primaryColor'] ?? '#00f0ff');
                    $tIsPro = !empty($tTheme['_is_pro']);
                    $tName  = htmlspecialchars($tTheme['name']);
                    $tCat   = htmlspecialchars(ucfirst($tTheme['category'] ?? 'other'));
                ?>
                <label class="rxi-tpl-card" style="cursor:pointer;border-radius:12px;overflow:hidden;border:2px solid <?= $first ? 'rgba(0,240,255,0.6)' : 'var(--border-color)' ?>;transition:border-color 0.2s,box-shadow 0.2s;position:relative;display:block;">
                    <input type="radio" name="template" value="<?= htmlspecialchars($tKey) ?>"
                           <?= $first ? 'checked' : '' ?> style="display:none;"
                           onchange="(function(inp){document.querySelectorAll('.rxi-tpl-card').forEach(c=>{c.style.borderColor='var(--border-color)';c.style.boxShadow='none';});inp.closest('.rxi-tpl-card').style.borderColor='rgba(0,240,255,0.6)';inp.closest('.rxi-tpl-card').style.boxShadow='0 0 0 2px rgba(0,240,255,0.15)';})(this)">
                    <!-- Thumbnail -->
                    <div style="height:100px;background:<?= $tBg ?>;display:flex;align-items:center;justify-content:center;position:relative;">
                        <svg width="64" height="50" viewBox="0 0 64 50">
                            <rect width="64" height="50" fill="<?= $tBg ?>"/>
                            <rect x="0" y="0" width="64" height="13" fill="<?= $tPri ?>44"/>
                            <rect x="4" y="3" width="30" height="3" rx="1.5" fill="<?= $tPri ?>"/>
                            <rect x="4" y="8" width="18" height="2" rx="1" fill="<?= $tSec ?>88"/>
                            <rect x="4" y="18" width="56" height="2" rx="1" fill="<?= $tPri ?>44"/>
                            <rect x="4" y="22" width="48" height="1.5" rx="0.75" fill="<?= $tPri ?>22"/>
                            <rect x="4" y="26" width="52" height="1.5" rx="0.75" fill="<?= $tPri ?>22"/>
                            <rect x="4" y="32" width="20" height="2.5" rx="1.25" fill="<?= $tPri ?>"/>
                            <rect x="4" y="37" width="56" height="1.5" rx="0.75" fill="<?= $tPri ?>33"/>
                            <rect x="4" y="41" width="44" height="1" rx="0.5" fill="<?= $tPri ?>22"/>
                        </svg>
                        <?php if ($tIsPro): ?>
                        <span style="position:absolute;top:6px;right:6px;padding:2px 7px;border-radius:8px;font-size:0.6rem;font-weight:700;background:rgba(245,158,11,0.9);color:#000;letter-spacing:0.3px;">
                            ★ PRO
                        </span>
                        <?php endif; ?>
                    </div>
                    <!-- Info -->
                    <div style="padding:8px 10px;background:var(--bg-secondary);">
                        <div style="font-size:0.78rem;font-weight:700;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $tName ?></div>
                        <div style="font-size:0.68rem;color:var(--text-secondary);margin-top:1px;"><?= $tCat ?></div>
                    </div>
                </label>
                <?php $first = false; endforeach; ?>
            </div>
        </div>

        <button type="submit" class="rxi-submit">
            <i class="fas fa-file-import"></i> Import &amp; Open Editor
        </button>
    </form>
</div>

<script>
(function () {
    var fileInput = document.getElementById('resumeFile');
    var fileName  = document.getElementById('fileName');
    var dropzone  = document.getElementById('dropzone');

    fileInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            fileName.textContent = this.files[0].name;
            fileName.style.display = 'block';
        }
    });

    dropzone.addEventListener('dragover', function (e) {
        e.preventDefault();
        dropzone.classList.add('drag-over');
    });
    dropzone.addEventListener('dragleave', function () {
        dropzone.classList.remove('drag-over');
    });
    dropzone.addEventListener('drop', function (e) {
        e.preventDefault();
        dropzone.classList.remove('drag-over');
        var files = e.dataTransfer.files;
        if (files && files[0]) {
            var dt = new DataTransfer();
            dt.items.add(files[0]);
            fileInput.files = dt.files;
            fileName.textContent = files[0].name;
            fileName.style.display = 'block';
        }
    });

    // Highlight first template card by default
    var firstCard = document.querySelector('.rxi-tpl-card');
    if (firstCard) firstCard.style.borderColor = 'rgba(0,240,255,0.6)';
})();
</script>

<?php View::endSection(); ?>
