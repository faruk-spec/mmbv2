<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1><i class="fas fa-layer-group" style="color:var(--cyan);"></i> ResumeX — Manage Templates</h1>
        <p style="color:var(--text-secondary);">Upload and manage custom resume templates for all users.</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="/admin/projects/resumex/designer" class="btn btn-primary">
            <i class="fas fa-magic"></i> Visual Designer
        </a>
        <a href="/admin/projects/resumex" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Overview</a>
    </div>
</div>

<?php if (!empty($success)): ?>
<div class="alert alert-success" style="margin-bottom:20px;">
    <i class="fas fa-check-circle"></i>
    <?php if ($success === 'deleted'): ?>
        Template deleted successfully.
    <?php else: ?>
        Template <strong><?= htmlspecialchars($uploadedName ?? '') ?></strong> uploaded and is now active.
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger" style="margin-bottom:20px;">
    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<!-- ── Two upload paths ───────────────────────────────────────────────────── -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:32px;">

    <!-- Option A: Full Resume Template (complete renderer) -->
    <div class="card" style="border:2px solid rgba(0,240,255,0.25);">
        <div class="card-header" style="background:rgba(0,240,255,0.05);">
            <h3 class="card-title">
                <i class="fas fa-file-code" style="color:var(--cyan);"></i>
                Upload Full Resume Template
                <span style="font-size:0.7rem;background:rgba(0,240,255,0.15);color:var(--cyan);border:1px solid rgba(0,240,255,0.3);border-radius:4px;padding:1px 7px;margin-left:6px;vertical-align:middle;">Recommended</span>
            </h3>
        </div>
        <div style="padding:20px;">
            <p style="font-size:0.875rem;color:var(--text-secondary);margin-bottom:10px;">
                Upload a <strong>complete PHP file</strong> that renders the entire resume page —
                full control over HTML, CSS, and layout. The file receives
                <code>$resumeData</code>, <code>$resume</code>, and <code>$themeSettings</code>.
            </p>
            <p style="font-size:0.8rem;color:var(--text-secondary);margin-bottom:16px;">
                <a href="/admin/projects/resumex/templates/sample-full-download" style="color:var(--cyan);">
                    <i class="fas fa-download"></i> Download sample-full-template.php
                </a>
                — fully annotated starter with all sections implemented.
            </p>
            <form method="POST" action="/admin/projects/resumex/templates/upload-full" enctype="multipart/form-data" id="fullUploadForm">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label class="form-label">Key <span style="color:#f87171;">*</span>
                            <span style="font-size:0.72rem;font-weight:400;color:var(--text-secondary);"> (slug, e.g. my-resume)</span>
                        </label>
                        <input class="form-input" type="text" name="tpl_key" required
                               pattern="[a-z0-9\-]+" maxlength="100"
                               placeholder="my-custom-resume">
                    </div>
                    <div>
                        <label class="form-label">Display Name <span style="color:#f87171;">*</span></label>
                        <input class="form-input" type="text" name="tpl_name" required maxlength="255"
                               placeholder="My Custom Resume">
                    </div>
                    <div>
                        <label class="form-label">Category</label>
                        <select class="form-input" name="tpl_category">
                            <?php foreach (['custom','professional','academic','dark','light','creative','warm'] as $cat): ?>
                            <option value="<?= $cat ?>"><?= ucfirst($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Card Background Colour
                            <span style="font-size:0.72rem;font-weight:400;color:var(--text-secondary);"> (for template picker thumbnail)</span>
                        </label>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input type="color" value="#1e1e2e" id="fullBgPick"
                                   oninput="document.getElementById('tpl_bg').value=this.value"
                                   style="width:34px;height:34px;border:none;background:none;cursor:pointer;padding:0;">
                            <input class="form-input" type="text" name="tpl_bg" id="tpl_bg" value="#1e1e2e"
                                   pattern="#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?"
                                   placeholder="#1e1e2e"
                                   oninput="document.getElementById('fullBgPick').value=this.value"
                                   style="flex:1;">
                        </div>
                    </div>
                    <div style="grid-column:2;">
                        <label class="form-label">Accent Colour
                            <span style="font-size:0.72rem;font-weight:400;color:var(--text-secondary);"> (for thumbnail dot)</span>
                        </label>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input type="color" value="#00f0ff" id="fullPriPick"
                                   oninput="document.getElementById('tpl_pri').value=this.value"
                                   style="width:34px;height:34px;border:none;background:none;cursor:pointer;padding:0;">
                            <input class="form-input" type="text" name="tpl_pri" id="tpl_pri" value="#00f0ff"
                                   pattern="#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?"
                                   placeholder="#00f0ff"
                                   oninput="document.getElementById('fullPriPick').value=this.value"
                                   style="flex:1;">
                        </div>
                    </div>
                </div>

                <div style="border:2px dashed var(--border-color);border-radius:10px;padding:20px;text-align:center;position:relative;cursor:pointer;margin-bottom:14px;" id="fullDropzone">
                    <input type="file" name="full_template_file" id="fullFile" accept=".php" required
                           style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;">
                    <i class="fas fa-file-php" style="font-size:1.8rem;color:var(--text-secondary);margin-bottom:8px;display:block;"></i>
                    <p style="margin:0 0 3px;font-size:0.85rem;"><strong style="color:var(--cyan);">Click or drag</strong> your .php template file here</p>
                    <p style="margin:0;font-size:0.78rem;color:var(--text-secondary);">max 2 MB · complete resume renderer</p>
                    <div id="fullFileSelected" style="display:none;margin-top:8px;font-size:0.82rem;color:var(--cyan);font-weight:600;"></div>
                    <div id="fullFileError" style="display:none;margin-top:8px;font-size:0.82rem;color:#f87171;font-weight:600;"></div>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                    <i class="fas fa-cloud-upload-alt"></i> Upload Full Template
                </button>
            </form>
        </div>
    </div>

    <!-- Option B: Theme Preset (property array) -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-sliders-h"></i>
                Upload Theme Preset
            </h3>
        </div>
        <div style="padding:20px;">
            <p style="font-size:0.875rem;color:var(--text-secondary);margin-bottom:10px;">
                Upload a <code>.php</code> file that <code>return</code>s a property array
                (colors, fonts, layout style). Applied by the built-in renderer — only
                change the <em>look</em>, not the HTML structure.
            </p>
            <p style="font-size:0.8rem;color:var(--text-secondary);margin-bottom:16px;">
                <a href="/admin/projects/resumex/templates/sample-download" style="color:var(--cyan);">
                    <i class="fas fa-download"></i> Download sample-template.php
                </a>
                — all required fields documented.
            </p>
            <form method="POST" action="/admin/projects/resumex/templates/upload" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <div style="border:2px dashed var(--border-color);border-radius:10px;padding:20px;text-align:center;position:relative;cursor:pointer;margin-bottom:14px;" id="presetDropzone">
                    <input type="file" name="template_file" id="presetFile" accept=".php"
                           style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;">
                    <i class="fas fa-file-code" style="font-size:1.6rem;color:var(--text-secondary);margin-bottom:6px;display:block;"></i>
                    <p style="margin:0 0 3px;font-size:0.85rem;"><strong style="color:var(--cyan);">Click or drag</strong> a .php preset file here</p>
                    <p style="margin:0;font-size:0.78rem;color:var(--text-secondary);">max 512 KB · must return [ ] array</p>
                    <div id="presetFileSelected" style="display:none;margin-top:6px;font-size:0.8rem;color:var(--cyan);font-weight:600;"></div>
                </div>
                <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center;">
                    <i class="fas fa-cloud-upload-alt"></i> Upload Theme Preset
                </button>
            </form>
        </div>
    </div>

</div>

<!-- ── Custom Templates Table ─────────────────────────────────────────────── -->
<div class="card" style="margin-bottom:24px;">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-puzzle-piece"></i> Custom Templates (<?= count($customTemplates) ?>)</h3>
    </div>
    <?php if (empty($customTemplates)): ?>
        <p style="text-align:center;color:var(--text-secondary);padding:40px 20px;">
            <i class="fas fa-inbox" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:8px;"></i>
            No custom templates yet. Upload a full template or theme preset above.
        </p>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Preview</th>
                    <th>Name</th>
                    <th>Key</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customTemplates as $t): ?>
                <tr>
                    <td style="width:80px;">
                        <?php if (!empty($t['preview_image'])): ?>
                            <img src="<?= htmlspecialchars($t['preview_image']) ?>" alt="preview"
                                 style="width:64px;height:44px;object-fit:cover;border-radius:5px;border:1px solid var(--border-color);">
                        <?php else: ?>
                            <div id="previewUploadArea-<?= (int)$t['id'] ?>"
                                 style="width:64px;height:44px;border:1px dashed var(--border-color);border-radius:5px;display:flex;align-items:center;justify-content:center;cursor:pointer;gap:4px;flex-direction:column;"
                                 onclick="triggerPreviewUpload(<?= (int)$t['id'] ?>)" title="Upload preview image">
                                <i class="fas fa-image" style="font-size:1rem;color:var(--text-secondary);"></i>
                                <span style="font-size:0.6rem;color:var(--text-secondary);">Add preview</span>
                            </div>
                            <input type="file" id="previewFile-<?= (int)$t['id'] ?>"
                                   accept="image/jpeg,image/png,image/gif,image/webp"
                                   style="display:none;"
                                   onchange="uploadPreview(<?= (int)$t['id'] ?>, this)">
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($t['name']) ?></strong>
                        <?php if (!empty($t['is_override'])): ?>
                        <br><span style="font-size:0.7rem;color:#f59e0b;"><i class="fas fa-exclamation-triangle"></i> Overrides built-in</span>
                        <?php endif; ?>
                    </td>
                    <td><code style="font-size:0.8rem;color:var(--cyan);"><?= htmlspecialchars($t['key']) ?></code></td>
                    <td>
                        <span style="display:inline-block;padding:2px 10px;border-radius:999px;font-size:0.75rem;font-weight:600;background:rgba(0,240,255,0.1);color:var(--cyan);border:1px solid rgba(0,240,255,0.25);">
                            <?= htmlspecialchars($t['category']) ?>
                        </span>
                    </td>
                    <td>
                        <?php $type = $t['template_type'] ?? 'preset'; ?>
                        <?php if ($type === 'full'): ?>
                        <span style="display:inline-block;padding:2px 10px;border-radius:999px;font-size:0.72rem;font-weight:600;background:rgba(139,92,246,0.1);color:#a78bfa;border:1px solid rgba(139,92,246,0.3);">
                            <i class="fas fa-file-code"></i> Full
                        </span>
                        <?php elseif ($type === 'designer'): ?>
                        <span style="display:inline-block;padding:2px 10px;border-radius:999px;font-size:0.72rem;font-weight:600;background:rgba(0,240,255,0.08);color:var(--cyan);border:1px solid rgba(0,240,255,0.25);">
                            <i class="fas fa-magic"></i> Designer
                        </span>
                        <?php else: ?>
                        <span style="display:inline-block;padding:2px 10px;border-radius:999px;font-size:0.72rem;font-weight:600;background:rgba(14,165,233,0.08);color:#38bdf8;border:1px solid rgba(14,165,233,0.25);">
                            <i class="fas fa-sliders-h"></i> Preset
                        </span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:0.78rem;color:var(--text-secondary);"><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                    <td>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <?php if (($t['template_type'] ?? '') === 'designer'): ?>
                            <a href="/admin/projects/resumex/designer/<?= (int)$t['id'] ?>" class="btn btn-secondary btn-sm" title="Edit in Visual Designer">
                                <i class="fas fa-magic"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (empty($t['preview_image'])): ?>
                            <button type="button" class="btn btn-secondary btn-sm"
                                    onclick="triggerPreviewUpload(<?= (int)$t['id'] ?>)"
                                    title="Upload preview image">
                                <i class="fas fa-image"></i>
                            </button>
                            <?php endif; ?>
                            <button type="button"
                                    class="btn btn-sm btn-toggle-pro"
                                    data-id="<?= (int)$t['id'] ?>"
                                    data-is-pro="<?= (int)($t['is_pro'] ?? 0) ?>"
                                    title="Toggle PRO status"
                                    style="<?= ($t['is_pro'] ?? 0) ? 'background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.4);' : 'background:rgba(255,255,255,0.05);color:var(--text-secondary);border:1px solid var(--border-color);' ?>border-radius:6px;padding:3px 9px;font-size:0.7rem;font-weight:700;cursor:pointer;">
                                <?php if ($t['is_pro'] ?? 0): ?>
                                <i class="fas fa-star"></i> PRO
                                <?php else: ?>
                                <i class="far fa-star"></i> Free
                                <?php endif; ?>
                            </button>
                            <form method="POST" action="/admin/projects/resumex/templates/delete"
                                  onsubmit="return confirm('Delete ' + <?= json_encode($t['name']) ?> + '? This cannot be undone.')">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- ── Built-in Templates Reference ──────────────────────────────────────── -->
<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h3 class="card-title"><i class="fas fa-cubes"></i> Built-in Templates (<?= count($builtinTemplates) ?>)</h3>
        <span style="font-size:0.8rem;color:var(--text-secondary);">Upload a theme preset with the same key to override a built-in</span>
    </div>
    <div style="margin:0 16px 12px;padding:10px 14px;background:rgba(245,158,11,0.07);border:1px solid rgba(245,158,11,0.2);border-radius:8px;font-size:0.82rem;color:var(--text-secondary);">
        <i class="fas fa-exclamation-triangle" style="color:#f59e0b;"></i>
        <strong style="color:#f59e0b;"> Note:</strong>
        To replace a built-in template, upload a <em>theme preset</em> PHP file with the same key.
        The override is stored as a custom template and can be deleted to restore the original.
    </div>
    <div style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Key</th>
                    <th>Category</th>
                    <th>Layout</th>
                    <th>Colors</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($builtinTemplates as $key => $t):
                    if (!empty($t['_full_template'])) continue; // custom full templates are already listed in the Custom Templates table above; skip here
                    $isOverridden = in_array($key, array_column($customTemplates, 'key'), true);
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['name']) ?></strong></td>
                    <td><code style="font-size:0.78rem;color:#a78bfa;"><?= htmlspecialchars($key) ?></code></td>
                    <td>
                        <span style="display:inline-block;padding:2px 10px;border-radius:999px;font-size:0.75rem;font-weight:600;background:rgba(139,92,246,0.1);color:#a78bfa;border:1px solid rgba(139,92,246,0.25);">
                            <?= htmlspecialchars($t['category']) ?>
                        </span>
                    </td>
                    <td style="font-size:0.8rem;color:var(--text-secondary);"><?= htmlspecialchars($t['layoutStyle'] ?? '—') ?></td>
                    <td>
                        <div style="display:flex;gap:4px;align-items:center;">
                            <?php foreach (($t['colorVariants'] ?? []) as $v): ?>
                            <span title="<?= htmlspecialchars($v['label']) ?>"
                                  style="width:14px;height:14px;border-radius:50%;background:<?= htmlspecialchars($v['primary']) ?>;display:inline-block;border:1px solid rgba(255,255,255,0.15);"></span>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td>
                        <?php if ($isOverridden): ?>
                        <span style="font-size:0.75rem;color:#f59e0b;"><i class="fas fa-layer-group"></i> Overridden</span>
                        <?php else: ?>
                        <span style="font-size:0.75rem;color:#4ade80;"><i class="fas fa-check-circle"></i> Active</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// ── Drag & drop for full template upload ──────────────────────────────────────
(function () {
    var dz   = document.getElementById('fullDropzone');
    var inp  = document.getElementById('fullFile');
    var lbl  = document.getElementById('fullFileSelected');
    var err  = document.getElementById('fullFileError');
    var form = document.getElementById('fullUploadForm');
    var btn  = form ? form.querySelector('button[type="submit"]') : null;
    if (!dz) return;

    function validateFile(file) {
        if (!file) return false;
        var name = file.name || '';
        var ok = /\.php$/i.test(name);
        if (ok) {
            lbl.textContent = '✓ ' + name; lbl.style.display = 'block';
            err.style.display = 'none';
            dz.style.borderColor = 'var(--cyan)';
        } else {
            err.textContent = '✗ Only .php files are accepted (got .' + name.split('.').pop() + ')';
            err.style.display = 'block';
            lbl.style.display = 'none';
            dz.style.borderColor = '#f87171';
        }
        return ok;
    }

    inp.addEventListener('change', function () {
        if (this.files.length) validateFile(this.files[0]);
    });

    // Add loading state on submit
    if (form && btn) {
        form.addEventListener('submit', function (e) {
            if (!inp.files.length) {
                e.preventDefault();
                err.textContent = '✗ Please select a .php file first.';
                err.style.display = 'block';
                return;
            }
            if (!validateFile(inp.files[0])) {
                e.preventDefault();
                return;
            }
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading…';
        });
    }

    ['dragenter','dragover'].forEach(function(ev) { dz.addEventListener(ev, function(e) { e.preventDefault(); dz.style.borderColor='var(--cyan)'; }); });
    ['dragleave'].forEach(function(ev) { dz.addEventListener(ev, function(e) { e.preventDefault(); if (!inp.files.length) dz.style.borderColor=''; }); });
    dz.addEventListener('drop', function(e) {
        e.preventDefault();
        var f = e.dataTransfer.files;
        if (f.length) {
            try { inp.files = f; } catch(_) {}
            validateFile(f[0]);
        }
        dz.style.borderColor = '';
    });
}());

// ── Drag & drop for preset upload ─────────────────────────────────────────────
(function () {
    var dz  = document.getElementById('presetDropzone');
    var inp = document.getElementById('presetFile');
    var lbl = document.getElementById('presetFileSelected');
    if (!dz) return;
    inp.addEventListener('change', function () {
        if (this.files.length) { lbl.textContent = '✓ ' + this.files[0].name; lbl.style.display = 'block'; dz.style.borderColor = 'var(--cyan)'; }
    });
    ['dragenter','dragover'].forEach(function(ev) { dz.addEventListener(ev, function(e) { e.preventDefault(); dz.style.borderColor='var(--cyan)'; }); });
    ['dragleave','drop'].forEach(function(ev) { dz.addEventListener(ev, function(e) { e.preventDefault(); dz.style.borderColor=''; }); });
    dz.addEventListener('drop', function(e) {
        var f = e.dataTransfer.files;
        if (f.length) { inp.files = f; lbl.textContent = '✓ ' + f[0].name; lbl.style.display='block'; }
    });
}());

// ── Preview image upload ─────────────────────────────────────────────────────
var csrfToken = <?= json_encode($csrfToken) ?>;

function triggerPreviewUpload(id) {
    document.getElementById('previewFile-' + id).click();
}

function uploadPreview(id, input) {
    var file = input.files[0];
    if (!file) return;
    var fd = new FormData();
    fd.append('_token', csrfToken);
    fd.append('template_id', id);
    fd.append('preview_image', file);
    fetch('/admin/projects/resumex/templates/preview-image', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                var area = document.getElementById('previewUploadArea-' + id);
                if (area) {
                    var img = document.createElement('img');
                    img.src = d.url;
                    img.style.cssText = 'width:64px;height:44px;object-fit:cover;border-radius:5px;border:1px solid var(--border-color);';
                    area.replaceWith(img);
                }
            } else {
                alert('Preview upload failed: ' + (d.error || 'Unknown error'));
            }
        })
        .catch(function() { alert('Network error during preview upload.'); });
}

document.addEventListener('DOMContentLoaded', function () {
    var csrfToken = <?= json_encode($csrfToken) ?>;

    document.querySelectorAll('.btn-toggle-pro').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id    = parseInt(btn.dataset.id, 10);
            var isPro = parseInt(btn.dataset.isPro, 10);
            var newVal = isPro ? '0' : '1';

            var fd = new FormData();
            fd.append('_token', csrfToken);
            fd.append('id', id);
            fd.append('is_pro', newVal);

            fetch('/admin/projects/resumex/templates/toggle-pro', { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (d.success) {
                        btn.dataset.isPro = d.is_pro;
                        if (d.is_pro) {
                            btn.innerHTML = '<i class="fas fa-star"></i> PRO';
                            btn.style.cssText = 'background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.4);border-radius:6px;padding:3px 9px;font-size:0.7rem;font-weight:700;cursor:pointer;';
                        } else {
                            btn.innerHTML = '<i class="far fa-star"></i> Free';
                            btn.style.cssText = 'background:rgba(255,255,255,0.05);color:var(--text-secondary);border:1px solid var(--border-color);border-radius:6px;padding:3px 9px;font-size:0.7rem;font-weight:700;cursor:pointer;';
                        }
                    } else {
                        alert('Failed to update PRO status: ' + (d.error || 'Unknown error'));
                    }
                })
                .catch(function () { alert('Network error toggling PRO status.'); });
        });
    });
});
</script>

<?php View::endSection(); ?>
