<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<style>
.tu-wrap {
    padding: 36px 24px 60px;
}
.tu-back {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: var(--text-secondary);
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    margin-bottom: 28px;
    transition: color 0.2s;
}
.tu-back:hover { color: var(--cyan); text-decoration: none; }
.tu-header {
    margin-bottom: 32px;
}
.tu-header h1 {
    font-size: clamp(1.6rem, 3.5vw, 2.2rem);
    font-weight: 800;
    letter-spacing: -0.5px;
    background: linear-gradient(135deg, var(--cyan) 0%, var(--purple) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0 0 8px;
}
.tu-header p { color: var(--text-secondary); font-size: 0.95rem; margin: 0; }

/* ── Alert banners ──────────────────────────────────────────────── */
.tu-alert {
    padding: 14px 18px;
    border-radius: 10px;
    font-size: 0.9rem;
    margin-bottom: 24px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}
.tu-alert-success { background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.35); color: #34d399; }
.tu-alert-error   { background: rgba(239,68,68,0.12);  border: 1px solid rgba(239,68,68,0.35);  color: #f87171; }
.tu-alert svg { flex-shrink: 0; margin-top: 1px; }

/* ── Grid ───────────────────────────────────────────────────────── */
.tu-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
@media (max-width: 680px) { .tu-grid { grid-template-columns: 1fr; } }

/* ── Card ───────────────────────────────────────────────────────── */
.tu-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 24px;
}
.tu-card h2 {
    font-size: 1rem;
    font-weight: 700;
    margin: 0 0 16px;
    color: var(--text-primary);
}

/* ── Upload form ─────────────────────────────────────────────────── */
.tu-dropzone {
    border: 2px dashed var(--border-color);
    border-radius: 10px;
    padding: 32px 20px;
    text-align: center;
    transition: border-color 0.2s, background 0.2s;
    cursor: pointer;
    position: relative;
}
.tu-dropzone:hover,
.tu-dropzone.drag-over {
    border-color: var(--cyan);
    background: rgba(0,240,255,0.04);
}
.tu-dropzone input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
}
.tu-dropzone-icon {
    font-size: 2rem;
    margin-bottom: 8px;
    color: var(--text-secondary);
}
.tu-dropzone p {
    margin: 0;
    font-size: 0.875rem;
    color: var(--text-secondary);
}
.tu-dropzone strong { color: var(--cyan); }
.tu-file-selected {
    margin-top: 10px;
    font-size: 0.8rem;
    color: var(--cyan);
    font-weight: 600;
    display: none;
}

.tu-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.15s;
    border: none;
    text-decoration: none;
}
.tu-btn:hover { opacity: 0.88; transform: translateY(-1px); text-decoration: none; }
.tu-btn-primary { background: linear-gradient(135deg, var(--cyan), var(--purple)); color: #fff; }
.tu-btn-danger  { background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3); }
.tu-btn-sm { padding: 6px 14px; font-size: 0.8rem; }
.tu-btn-block { width: 100%; justify-content: center; margin-top: 14px; }

/* ── Info box ────────────────────────────────────────────────────── */
.tu-info {
    background: rgba(0,240,255,0.06);
    border: 1px solid rgba(0,240,255,0.2);
    border-radius: 10px;
    padding: 16px;
    font-size: 0.825rem;
    color: var(--text-secondary);
    line-height: 1.6;
}
.tu-info a { color: var(--cyan); text-decoration: none; }
.tu-info a:hover { text-decoration: underline; }
.tu-info code {
    font-size: 0.78rem;
    background: rgba(255,255,255,0.06);
    padding: 1px 5px;
    border-radius: 4px;
    color: var(--cyan);
    font-family: 'Fira Code', monospace;
}

/* ── Template table ──────────────────────────────────────────────── */
.tu-section { margin-top: 36px; }
.tu-section h2 {
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0 0 16px;
    color: var(--text-primary);
}
.tu-table-wrap { overflow-x: auto; border-radius: 12px; border: 1px solid var(--border-color); }
.tu-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}
.tu-table th {
    background: var(--bg-secondary, #1a1a2e);
    color: var(--text-secondary);
    font-weight: 600;
    text-align: left;
    padding: 12px 16px;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.tu-table td {
    padding: 12px 16px;
    border-top: 1px solid var(--border-color);
    color: var(--text-primary);
    vertical-align: middle;
}
.tu-table tr:hover td { background: rgba(255,255,255,0.03); }
.tu-badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    background: rgba(0,240,255,0.12);
    color: var(--cyan);
    border: 1px solid rgba(0,240,255,0.25);
}
.tu-badge-builtin {
    background: rgba(139,92,246,0.12);
    color: #a78bfa;
    border-color: rgba(139,92,246,0.25);
}
.tu-empty {
    text-align: center;
    padding: 40px 20px;
    color: var(--text-secondary);
    font-size: 0.9rem;
}
.tu-empty svg { opacity: 0.3; margin-bottom: 8px; }
</style>

<div class="tu-wrap">
    <a href="/projects/resumex/templates" class="tu-back">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Back to Templates
    </a>

    <div class="tu-header">
        <h1>Manage Resume Templates</h1>
        <p>Upload custom template files to make them available to all users.</p>
    </div>

    <?php if (!empty($success)): ?>
        <div class="tu-alert tu-alert-success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <?php if ($success === 'deleted'): ?>
                Template deleted successfully.
            <?php else: ?>
                Template <strong><?= htmlspecialchars($uploadedName ?? '') ?></strong> uploaded successfully and is now available in the template picker.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="tu-alert tu-alert-error">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="tu-grid">
        <!-- Upload Form -->
        <div class="tu-card">
            <h2>📤 Upload Template File</h2>
            <form method="POST" action="/projects/resumex/templates/upload" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div class="tu-dropzone" id="tuDropzone">
                    <input type="file" name="template_file" id="tuFile" accept=".php">
                    <div class="tu-dropzone-icon">📄</div>
                    <p><strong>Click to browse</strong> or drag & drop</p>
                    <p>PHP template file · Max 512 KB</p>
                    <div class="tu-file-selected" id="tuFileSelected"></div>
                </div>

                <button type="submit" class="tu-btn tu-btn-primary tu-btn-block">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                    Upload Template
                </button>
            </form>
        </div>

        <!-- Instructions -->
        <div class="tu-card">
            <h2>📋 Template File Format</h2>
            <div class="tu-info">
                <p>A template file is a <code>.php</code> file that <strong>returns an array</strong> with color, font, and layout settings.</p>
                <br>
                <p><strong>Required fields include:</strong><br>
                <code>key</code>, <code>name</code>, <code>category</code>, <code>primaryColor</code>, <code>secondaryColor</code>, <code>backgroundColor</code>, <code>surfaceColor</code>, <code>textColor</code>, <code>textMuted</code>, <code>borderColor</code>, <code>fontFamily</code>, <code>fontSize</code>, <code>layoutMode</code>, <code>layoutStyle</code>, <code>colorVariants</code>, and more.</p>
                <br>
                <p>📥 <a href="/projects/resumex/templates/sample-download">Download sample template</a> for the complete format with inline documentation.</p>
                <br>
                <p><strong>To add a profile photo / image to a resume</strong>, use the <em>Profile Photo</em> section inside the Resume Editor. The editor will call the upload API automatically.</p>
            </div>
        </div>
    </div>

    <!-- Custom Templates Table -->
    <div class="tu-section">
        <h2>Custom Templates (<?= count($customTemplates) ?>)</h2>
        <div class="tu-table-wrap">
            <?php if (empty($customTemplates)): ?>
                <div class="tu-empty">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M9 9h6M9 12h6M9 15h4"/></svg>
                    <p>No custom templates uploaded yet.</p>
                </div>
            <?php else: ?>
                <table class="tu-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Key</th>
                            <th>Category</th>
                            <th>Uploaded</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customTemplates as $t): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($t['name']) ?></strong></td>
                                <td><code style="font-size:0.8rem;color:var(--cyan)"><?= htmlspecialchars($t['key']) ?></code></td>
                                <td><span class="tu-badge"><?= htmlspecialchars($t['category']) ?></span></td>
                                <td style="color:var(--text-secondary);font-size:0.8rem"><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                                <td>
                                    <form method="POST" action="/projects/resumex/templates/delete"
                                          onsubmit="return confirm('Delete template ' + <?= json_encode($t['name']) ?> + '? This cannot be undone.')">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                                        <button type="submit" class="tu-btn tu-btn-danger tu-btn-sm">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Built-in Templates Reference -->
    <div class="tu-section">
        <h2>Built-in Templates (<?= count($builtinTemplates) ?>)</h2>
        <div class="tu-table-wrap">
            <table class="tu-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Key</th>
                        <th>Category</th>
                        <th>Layout</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($builtinTemplates as $key => $t): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($t['name']) ?></strong></td>
                            <td><code style="font-size:0.8rem;color:#a78bfa"><?= htmlspecialchars($key) ?></code></td>
                            <td><span class="tu-badge tu-badge-builtin"><?= htmlspecialchars($t['category']) ?></span></td>
                            <td style="color:var(--text-secondary);font-size:0.8rem"><?= htmlspecialchars($t['layoutMode'] ?? '') ?> / <?= htmlspecialchars($t['layoutStyle'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function () {
    const dz   = document.getElementById('tuDropzone');
    const inp  = document.getElementById('tuFile');
    const lbl  = document.getElementById('tuFileSelected');

    inp.addEventListener('change', function () {
        if (this.files.length) {
            lbl.textContent = '✓ ' + this.files[0].name;
            lbl.style.display = 'block';
        }
    });

    ['dragenter', 'dragover'].forEach(ev => {
        dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.add('drag-over'); });
    });
    ['dragleave', 'drop'].forEach(ev => {
        dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.remove('drag-over'); });
    });
    dz.addEventListener('drop', e => {
        const files = e.dataTransfer.files;
        if (files.length) {
            inp.files = files;
            lbl.textContent = '✓ ' + files[0].name;
            lbl.style.display = 'block';
        }
    });
})();
</script>
<?php View::endSection(); ?>
