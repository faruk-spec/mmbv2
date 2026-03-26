<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1><i class="fas fa-layer-group" style="color:var(--cyan);"></i> ResumeX — Manage Templates</h1>
        <p style="color:var(--text-secondary);">Create, upload, review, and delete custom resume templates.</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="/admin/projects/resumex/templates/create" class="btn btn-primary"><i class="fas fa-paint-brush"></i> Template Designer</a>
        <a href="/admin/projects/resumex/templates/sample-download" class="btn btn-secondary"><i class="fas fa-download"></i> Download Sample</a>
        <a href="/admin/projects/resumex" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Overview</a>
    </div>
</div>

<?php if (!empty($success)): ?>
<div class="alert alert-success" style="margin-bottom:20px;">
    <i class="fas fa-check-circle"></i>
    <?php if ($success === 'deleted'): ?>
        Template deleted successfully.
    <?php else: ?>
        Template <strong><?= htmlspecialchars($uploadedName ?? '') ?></strong> created and is now active.
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger" style="margin-bottom:20px;">
    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<!-- Design or Upload -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:32px;">

    <!-- Option A: Visual Designer -->
    <div class="card" style="border:2px solid rgba(0,240,255,0.2);">
        <div class="card-header" style="background:rgba(0,240,255,0.05);">
            <h3 class="card-title"><i class="fas fa-paint-brush" style="color:var(--cyan);"></i> Option A — Visual Designer</h3>
        </div>
        <div style="padding:20px;">
            <p style="font-size:0.875rem;color:var(--text-secondary);margin-bottom:16px;">
                Use the Template Designer to create a new template using a form with color pickers, dropdowns,
                and a live preview — <strong>no PHP coding required</strong>.
            </p>
            <a href="/admin/projects/resumex/templates/create" class="btn btn-primary" style="width:100%;text-align:center;justify-content:center;">
                <i class="fas fa-paint-brush"></i> Open Template Designer
            </a>
        </div>
    </div>

    <!-- Option B: PHP File Upload -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-upload"></i> Option B — Upload PHP File</h3>
        </div>
        <div style="padding:20px;">
            <p style="font-size:0.875rem;color:var(--text-secondary);margin-bottom:14px;">
                Upload a hand-crafted <code>.php</code> file that <code>return</code>s the template array.
                <a href="/admin/projects/resumex/templates/sample-download" style="color:var(--cyan);">Download sample</a> for the exact format.
            </p>
            <form method="POST" action="/admin/projects/resumex/templates/upload" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <div style="border:2px dashed var(--border-color);border-radius:10px;padding:20px;text-align:center;position:relative;cursor:pointer;" id="adminDropzone">
                    <input type="file" name="template_file" id="adminFile" accept=".php"
                           style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;">
                    <i class="fas fa-file-code" style="font-size:1.6rem;color:var(--text-secondary);margin-bottom:6px;display:block;"></i>
                    <p style="margin:0 0 3px;font-size:0.85rem;"><strong style="color:var(--cyan);">Click or drag</strong> a .php file here</p>
                    <p style="margin:0;font-size:0.78rem;color:var(--text-secondary);">max 512 KB</p>
                    <div id="adminFileSelected" style="display:none;margin-top:6px;font-size:0.8rem;color:var(--cyan);font-weight:600;"></div>
                </div>
                <button type="submit" class="btn btn-secondary" style="width:100%;margin-top:12px;justify-content:center;">
                    <i class="fas fa-cloud-upload-alt"></i> Upload Template File
                </button>
            </form>
        </div>
    </div>

</div>

<!-- Custom Templates Table -->
<div class="card" style="margin-bottom:24px;">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h3 class="card-title"><i class="fas fa-puzzle-piece"></i> Custom Templates (<?= count($customTemplates) ?>)</h3>
    </div>
    <?php if (empty($customTemplates)): ?>
        <p style="text-align:center;color:var(--text-secondary);padding:40px 20px;">
            <i class="fas fa-inbox" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:8px;"></i>
            No custom templates yet. Use the Template Designer or upload a PHP file.
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
                    <td style="width:70px;">
                        <?php if (!empty($t['preview_image'])): ?>
                            <img src="<?= htmlspecialchars($t['preview_image']) ?>" alt="preview"
                                 style="width:60px;height:40px;object-fit:cover;border-radius:5px;border:1px solid var(--border-color);">
                        <?php else: ?>
                            <div id="previewUploadArea-<?= (int)$t['id'] ?>" style="width:60px;height:40px;border:1px dashed var(--border-color);border-radius:5px;display:flex;align-items:center;justify-content:center;cursor:pointer;"
                                 onclick="triggerPreviewUpload(<?= (int)$t['id'] ?>)" title="Upload preview image">
                                <i class="fas fa-image" style="font-size:1rem;color:var(--text-secondary);"></i>
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
                    <td style="font-size:0.78rem;color:var(--text-secondary);">
                        <?= empty($t['file_name']) ? 'DB' : 'PHP file' ?>
                    </td>
                    <td style="font-size:0.78rem;color:var(--text-secondary);"><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                    <td>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <?php if (empty($t['preview_image'])): ?>
                            <button type="button" class="btn btn-secondary btn-sm"
                                    onclick="triggerPreviewUpload(<?= (int)$t['id'] ?>)"
                                    title="Upload preview image">
                                <i class="fas fa-image"></i>
                            </button>
                            <?php endif; ?>
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

<!-- Built-in Templates Reference -->
<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h3 class="card-title"><i class="fas fa-cubes"></i> Built-in Templates (<?= count($builtinTemplates) ?>)</h3>
        <span style="font-size:0.8rem;color:var(--text-secondary);">Click <strong>Edit / Override</strong> to create a customised version</span>
    </div>
    <div style="margin:0 16px 12px;padding:10px 14px;background:rgba(245,158,11,0.07);border:1px solid rgba(245,158,11,0.2);border-radius:8px;font-size:0.82rem;color:var(--text-secondary);">
        <i class="fas fa-exclamation-triangle" style="color:#f59e0b;"></i>
        <strong style="color:#f59e0b;"> Warning:</strong>
        Overriding a built-in template replaces it globally for all users. The original can be restored by deleting the custom override from the table above.
    </div>
    <div style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Key</th>
                    <th>Category</th>
                    <th>Layout Mode</th>
                    <th>Layout Style</th>
                    <th>Colors</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($builtinTemplates as $key => $t):
                    // Skip if already overridden (shown as custom above)
                    $isOverridden = in_array($key, array_column($customTemplates, 'key'), true);
                ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($t['name']) ?></strong>
                        <?php if ($isOverridden): ?>
                        <br><span style="font-size:0.7rem;color:#f59e0b;"><i class="fas fa-layer-group"></i> Has custom override</span>
                        <?php endif; ?>
                    </td>
                    <td><code style="font-size:0.78rem;color:#a78bfa;"><?= htmlspecialchars($key) ?></code></td>
                    <td>
                        <span style="display:inline-block;padding:2px 10px;border-radius:999px;font-size:0.75rem;font-weight:600;background:rgba(139,92,246,0.1);color:#a78bfa;border:1px solid rgba(139,92,246,0.25);">
                            <?= htmlspecialchars($t['category']) ?>
                        </span>
                    </td>
                    <td style="font-size:0.8rem;color:var(--text-secondary);"><?= htmlspecialchars($t['layoutMode'] ?? '—') ?></td>
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
                        <a href="/admin/projects/resumex/templates/create?prefill=<?= urlencode($key) ?>"
                           class="btn btn-secondary btn-sm <?= $isOverridden ? 'btn-warning' : '' ?>"
                           title="<?= $isOverridden ? 'Edit existing override' : 'Create override of this built-in template' ?>">
                            <i class="fas fa-edit"></i> <?= $isOverridden ? 'Edit Override' : 'Edit / Override' ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Validation Reference -->
<div class="card" style="margin-top:24px;">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-clipboard-check"></i> Template File Validation Reference</h3>
    </div>
    <div style="padding:20px;max-height:340px;overflow-y:auto;">
        <p style="font-size:0.85rem;color:var(--text-secondary);margin-bottom:14px;">
            Uploaded <code>.php</code> files must <code>return [...];</code> containing these fields:
        </p>
        <table class="table" style="font-size:0.8rem;">
            <thead><tr><th>Field</th><th>Type</th><th>Allowed Values / Rules</th></tr></thead>
            <tbody>
                <tr><td><code>key</code></td><td>string</td><td>Lowercase slug: <code>a-z 0-9 -</code> · max 100 chars · must be unique</td></tr>
                <tr><td><code>name</code></td><td>string</td><td>Display name · max 255 chars · non-empty</td></tr>
                <tr><td><code>category</code></td><td>string</td><td><code>professional | academic | dark | light | creative | custom | warm</code></td></tr>
                <tr><td><code>primaryColor</code></td><td>string</td><td>Hex: <code>#rrggbb</code> or <code>#rgb</code></td></tr>
                <tr><td><code>secondaryColor</code></td><td>string</td><td>Hex</td></tr>
                <tr><td><code>backgroundColor</code></td><td>string</td><td>Hex</td></tr>
                <tr><td><code>surfaceColor</code></td><td>string</td><td>Hex</td></tr>
                <tr><td><code>textColor</code></td><td>string</td><td>Hex</td></tr>
                <tr><td><code>textMuted</code></td><td>string</td><td>Hex</td></tr>
                <tr><td><code>borderColor</code></td><td>string</td><td>Any CSS color (hex or <code>rgba()</code>)</td></tr>
                <tr><td><code>fontFamily</code></td><td>string</td><td><code>Inter | Merriweather | Fira Code | Georgia | Arial | Roboto | Poppins</code></td></tr>
                <tr><td><code>fontSize</code></td><td>string</td><td><code>"12" | "13" | "14" | "15"</code></td></tr>
                <tr><td><code>fontWeight</code></td><td>string</td><td><code>"300" | "400" | "500"</code></td></tr>
                <tr><td><code>headerStyle</code></td><td>string</td><td><code>gradient | underline | minimal | solid | banner | neon | classic | bold</code></td></tr>
                <tr><td><code>buttonStyle</code></td><td>string</td><td><code>pill | square | rounded</code></td></tr>
                <tr><td><code>cardStyle</code></td><td>string</td><td><code>bordered | flat | shadow | glass</code></td></tr>
                <tr><td><code>spacing</code></td><td>string</td><td><code>compact | normal | comfortable | spacious</code></td></tr>
                <tr><td><code>layoutMode</code></td><td>string</td><td><code>two-column | single</code></td></tr>
                <tr><td><code>iconStyle</code></td><td>string</td><td><code>filled | outline</code></td></tr>
                <tr><td><code>accentHighlights</code></td><td>bool</td><td><code>true | false</code></td></tr>
                <tr><td><code>animations</code></td><td>bool</td><td><code>true | false</code></td></tr>
                <tr><td><code>layoutStyle</code></td><td>string</td><td><code>sidebar-dark | minimal | academic | timeline | banner | developer | full-header | classic | bold</code></td></tr>
                <tr><td><code>colorVariants</code></td><td>array</td><td>1–4 items: <code>['label'=>'...','primary'=>'#hex','secondary'=>'#hex']</code></td></tr>
            </tbody>
        </table>
        <div style="margin-top:12px;padding:10px 14px;background:rgba(245,158,11,0.07);border:1px solid rgba(245,158,11,0.2);border-radius:8px;font-size:0.8rem;color:var(--text-secondary);">
            <strong style="color:#f59e0b;">Reserved built-in keys (cannot be re-used in new custom templates):</strong>
            <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:6px;">
                <?php foreach (array_keys($builtinTemplates) as $bk): ?>
                    <code style="background:rgba(245,158,11,0.1);padding:2px 8px;border-radius:4px;"><?= htmlspecialchars($bk) ?></code>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var dz  = document.getElementById('adminDropzone');
    var inp = document.getElementById('adminFile');
    var lbl = document.getElementById('adminFileSelected');
    if (!dz) return;
    inp.addEventListener('change', function () {
        if (this.files.length) { lbl.textContent = '✓ ' + this.files[0].name; lbl.style.display = 'block'; dz.style.borderColor = 'var(--cyan)'; }
    });
    ['dragenter','dragover'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.style.borderColor='var(--cyan)'; }));
    ['dragleave','drop'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.style.borderColor=''; }));
    dz.addEventListener('drop', e => {
        const f = e.dataTransfer.files;
        if (f.length) { inp.files = f; lbl.textContent = '✓ ' + f[0].name; lbl.style.display='block'; }
    });
}());

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
                // Replace the upload area with the image
                var area = document.getElementById('previewUploadArea-' + id);
                if (area) {
                    var img = document.createElement('img');
                    img.src = d.url;
                    img.style.cssText = 'width:60px;height:40px;object-fit:cover;border-radius:5px;border:1px solid var(--border-color);';
                    area.replaceWith(img);
                }
            } else {
                alert('Preview upload failed: ' + (d.error || 'Unknown error'));
            }
        })
        .catch(function() { alert('Network error during preview upload.'); });
}
</script>

<?php View::endSection(); ?>
