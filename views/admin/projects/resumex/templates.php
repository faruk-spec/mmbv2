<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1><i class="fas fa-layer-group" style="color:var(--cyan);"></i> ResumeX — Manage Templates</h1>
        <p style="color:var(--text-secondary);">Upload, review, and delete custom resume template files.</p>
    </div>
    <div style="display:flex;gap:8px;">
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
        Template <strong><?= htmlspecialchars($uploadedName ?? '') ?></strong> uploaded and is now active.
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger" style="margin-bottom:20px;">
    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1.4fr;gap:24px;margin-bottom:32px;">

    <!-- Upload Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-upload"></i> Upload Template File</h3>
        </div>
        <div style="padding:20px;">
            <form method="POST" action="/admin/projects/resumex/templates/upload" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div style="border:2px dashed var(--border-color);border-radius:10px;padding:28px 20px;text-align:center;position:relative;cursor:pointer;transition:border-color 0.2s;" id="adminDropzone">
                    <input type="file" name="template_file" id="adminFile" accept=".php"
                           style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;">
                    <i class="fas fa-file-code" style="font-size:2rem;color:var(--text-secondary);margin-bottom:8px;display:block;"></i>
                    <p style="margin:0 0 4px;font-size:0.875rem;color:var(--text-primary);">
                        <strong style="color:var(--cyan);">Click to browse</strong> or drag &amp; drop
                    </p>
                    <p style="margin:0;font-size:0.8rem;color:var(--text-secondary);">.php file · max 512 KB</p>
                    <div id="adminFileSelected" style="display:none;margin-top:8px;font-size:0.8rem;color:var(--cyan);font-weight:600;"></div>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:14px;justify-content:center;">
                    <i class="fas fa-cloud-upload-alt"></i> Upload Template
                </button>
            </form>
        </div>
    </div>

    <!-- Validation Requirements -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-clipboard-check"></i> Template Requirements &amp; Validation</h3>
        </div>
        <div style="padding:20px;max-height:420px;overflow-y:auto;">

            <p style="font-size:0.85rem;color:var(--text-secondary);margin-bottom:16px;">
                A template file is a <code>.php</code> file that must <strong>return an array</strong>. The file must not output anything — only <code>return [...];</code>.
            </p>

            <!-- Required Fields Table -->
            <table class="table" style="font-size:0.8rem;">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Type</th>
                        <th>Allowed Values / Rules</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td><code>key</code></td><td>string</td><td>Unique slug: <code>a-z</code>, <code>0-9</code>, <code>-</code> only · max 100 chars · must not clash with built-in keys</td></tr>
                    <tr><td><code>name</code></td><td>string</td><td>Display name shown in picker · max 255 chars · non-empty</td></tr>
                    <tr><td><code>category</code></td><td>string</td><td><code>professional</code> | <code>academic</code> | <code>dark</code> | <code>light</code> | <code>creative</code> | <code>custom</code></td></tr>
                    <tr><td><code>primaryColor</code></td><td>string</td><td>Hex color: <code>#rrggbb</code> or <code>#rgb</code></td></tr>
                    <tr><td><code>secondaryColor</code></td><td>string</td><td>Hex color: <code>#rrggbb</code> or <code>#rgb</code></td></tr>
                    <tr><td><code>backgroundColor</code></td><td>string</td><td>Hex color: <code>#rrggbb</code></td></tr>
                    <tr><td><code>surfaceColor</code></td><td>string</td><td>Hex color: <code>#rrggbb</code></td></tr>
                    <tr><td><code>textColor</code></td><td>string</td><td>Hex color: <code>#rrggbb</code></td></tr>
                    <tr><td><code>textMuted</code></td><td>string</td><td>Hex color: <code>#rrggbb</code></td></tr>
                    <tr><td><code>borderColor</code></td><td>string</td><td>Any CSS color string (hex or rgba)</td></tr>
                    <tr><td><code>fontFamily</code></td><td>string</td><td><code>Inter</code> | <code>Merriweather</code> | <code>Fira Code</code> | <code>Georgia</code> | <code>Arial</code> | <code>Roboto</code></td></tr>
                    <tr><td><code>fontSize</code></td><td>string</td><td>Base size in px as string: <code>"13"</code> or <code>"14"</code></td></tr>
                    <tr><td><code>fontWeight</code></td><td>string</td><td>CSS font-weight: <code>"400"</code> or <code>"500"</code></td></tr>
                    <tr><td><code>headerStyle</code></td><td>string</td><td><code>gradient</code> | <code>underline</code> | <code>minimal</code> | <code>solid</code> | <code>banner</code></td></tr>
                    <tr><td><code>buttonStyle</code></td><td>string</td><td><code>pill</code> | <code>square</code> | <code>rounded</code></td></tr>
                    <tr><td><code>cardStyle</code></td><td>string</td><td><code>bordered</code> | <code>flat</code> | <code>shadow</code> | <code>glass</code></td></tr>
                    <tr><td><code>spacing</code></td><td>string</td><td><code>compact</code> | <code>normal</code> | <code>spacious</code></td></tr>
                    <tr><td><code>layoutMode</code></td><td>string</td><td><code>two-column</code> | <code>single</code></td></tr>
                    <tr><td><code>iconStyle</code></td><td>string</td><td><code>filled</code> | <code>outline</code></td></tr>
                    <tr><td><code>accentHighlights</code></td><td>bool</td><td><code>true</code> or <code>false</code></td></tr>
                    <tr><td><code>animations</code></td><td>bool</td><td><code>true</code> or <code>false</code></td></tr>
                    <tr><td><code>layoutStyle</code></td><td>string</td><td><code>sidebar-dark</code> | <code>minimal</code> | <code>academic</code> | <code>timeline</code> | <code>banner</code></td></tr>
                    <tr>
                        <td><code>colorVariants</code></td>
                        <td>array</td>
                        <td>
                            1–4 items, each:<br>
                            <code>['label'=&gt;string, 'primary'=&gt;'#hex', 'secondary'=&gt;'#hex']</code>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div style="margin-top:16px;padding:12px;background:rgba(0,240,255,0.05);border:1px solid rgba(0,240,255,0.2);border-radius:8px;font-size:0.8rem;color:var(--text-secondary);">
                <strong style="color:var(--cyan);">File-level rules:</strong>
                <ul style="margin:8px 0 0 16px;padding:0;">
                    <li>Extension must be <code>.php</code></li>
                    <li>File must <code>return</code> an array (no output, no side-effects)</li>
                    <li>Maximum file size: <strong>512 KB</strong></li>
                    <li><code>key</code> must be globally unique (built-ins + existing custom templates)</li>
                    <li>Built-in template keys cannot be overridden by uploads</li>
                </ul>
            </div>

            <div style="margin-top:12px;padding:12px;background:rgba(245,158,11,0.07);border:1px solid rgba(245,158,11,0.2);border-radius:8px;font-size:0.8rem;color:var(--text-secondary);">
                <strong style="color:#f59e0b;">Built-in template keys (reserved — cannot be reused):</strong>
                <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:6px;">
                    <?php foreach (array_keys($builtinTemplates) as $bk): ?>
                        <code style="background:rgba(245,158,11,0.1);padding:2px 8px;border-radius:4px;"><?= htmlspecialchars($bk) ?></code>
                    <?php endforeach; ?>
                </div>
            </div>
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
            No custom templates uploaded yet.
        </p>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Key</th>
                    <th>Category</th>
                    <th>File</th>
                    <th>Uploaded</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customTemplates as $t): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['name']) ?></strong></td>
                    <td><code style="font-size:0.8rem;color:var(--cyan);"><?= htmlspecialchars($t['key']) ?></code></td>
                    <td>
                        <span style="display:inline-block;padding:2px 10px;border-radius:999px;font-size:0.75rem;font-weight:600;background:rgba(0,240,255,0.1);color:var(--cyan);border:1px solid rgba(0,240,255,0.25);">
                            <?= htmlspecialchars($t['category']) ?>
                        </span>
                    </td>
                    <td style="font-size:0.78rem;color:var(--text-secondary);"><?= htmlspecialchars($t['file_name']) ?></td>
                    <td style="font-size:0.78rem;color:var(--text-secondary);"><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                    <td>
                        <form method="POST" action="/admin/projects/resumex/templates/delete"
                              onsubmit="return confirm('Delete ' + <?= json_encode($t['name']) ?> + '? This cannot be undone.')">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
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
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-cubes"></i> Built-in Templates (<?= count($builtinTemplates) ?>) — Read Only</h3>
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
                    <th>Primary Color</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($builtinTemplates as $key => $t): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['name']) ?></strong></td>
                    <td><code style="font-size:0.78rem;color:#a78bfa;"><?= htmlspecialchars($key) ?></code></td>
                    <td>
                        <span style="display:inline-block;padding:2px 10px;border-radius:999px;font-size:0.75rem;font-weight:600;background:rgba(139,92,246,0.1);color:#a78bfa;border:1px solid rgba(139,92,246,0.25);">
                            <?= htmlspecialchars($t['category']) ?>
                        </span>
                    </td>
                    <td style="font-size:0.8rem;color:var(--text-secondary);"><?= htmlspecialchars($t['layoutMode'] ?? '—') ?></td>
                    <td style="font-size:0.8rem;color:var(--text-secondary);"><?= htmlspecialchars($t['layoutStyle'] ?? '—') ?></td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:6px;">
                            <span style="width:14px;height:14px;border-radius:50%;background:<?= htmlspecialchars($t['primaryColor']) ?>;display:inline-block;border:1px solid rgba(255,255,255,0.15);"></span>
                            <code style="font-size:0.75rem;"><?= htmlspecialchars($t['primaryColor']) ?></code>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    var dz   = document.getElementById('adminDropzone');
    var inp  = document.getElementById('adminFile');
    var lbl  = document.getElementById('adminFileSelected');

    inp.addEventListener('change', function () {
        if (this.files.length) {
            lbl.textContent = '✓ ' + this.files[0].name;
            lbl.style.display = 'block';
            dz.style.borderColor = 'var(--cyan)';
        }
    });

    ['dragenter', 'dragover'].forEach(ev => dz.addEventListener(ev, e => {
        e.preventDefault(); dz.style.borderColor = 'var(--cyan)';
    }));
    ['dragleave', 'drop'].forEach(ev => dz.addEventListener(ev, e => {
        e.preventDefault(); dz.style.borderColor = '';
    }));
    dz.addEventListener('drop', e => {
        const files = e.dataTransfer.files;
        if (files.length) {
            inp.files = files;
            lbl.textContent = '✓ ' + files[0].name;
            lbl.style.display = 'block';
        }
    });
}());
</script>

<?php View::endSection(); ?>
