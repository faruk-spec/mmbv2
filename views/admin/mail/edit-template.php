<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1 style="margin:0;"><?= View::e($title) ?></h1>
        <p style="color:var(--text-secondary);margin:4px 0 0;">Slug: <code><?= View::e($template['slug']) ?></code></p>
    </div>
    <a href="/admin/mail/templates" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<?php if (Helpers::hasFlash('success')): ?>
<div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
<div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<!-- Variable hints -->
<div class="card" style="margin-bottom:16px;padding:12px 16px;">
    <strong style="font-size:13px;">Available variables:</strong>
    <?php $vars = json_decode($template['variables'] ?? '[]', true) ?? []; ?>
    <?php foreach ($vars as $v): ?>
    <code onclick="insertVar('{{<?= View::e($v) ?>}}')" style="cursor:pointer;margin:0 4px;font-size:12px;background:rgba(0,240,255,.1);padding:2px 6px;border-radius:4px;">{{<?= View::e($v) ?>}}</code>
    <?php endforeach; ?>
    <span style="font-size:12px;color:var(--text-secondary);margin-left:8px;">Click a variable to insert at cursor</span>
</div>

<form method="POST" action="/admin/mail/templates/update">
    <?= Security::csrfField() ?>
    <input type="hidden" name="id" value="<?= (int)$template['id'] ?>">

    <div class="card" style="margin-bottom:16px;">
        <div class="grid grid-2" style="gap:16px;">
            <div class="form-group">
                <label class="form-label">Subject <span style="color:var(--red);">*</span></label>
                <input type="text" name="subject" class="form-input" required
                       value="<?= View::e($template['subject']) ?>" id="subjectField">
            </div>
            <div class="form-group" style="display:flex;align-items:center;gap:8px;padding-top:28px;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="checkbox" name="is_enabled" value="1" <?= $template['is_enabled'] ? 'checked' : '' ?>>
                    <span>Enabled</span>
                </label>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <label class="form-label" style="margin:0;">HTML Body</label>
            <div style="display:flex;gap:8px;">
                <button type="button" class="btn btn-sm btn-secondary" onclick="togglePreview()">
                    <i class="fas fa-eye" id="previewIcon"></i> Preview
                </button>
            </div>
        </div>
        <textarea name="body" id="bodyField" class="form-input" rows="18" style="font-family:monospace;font-size:13px;resize:vertical;"><?= htmlspecialchars($template['body'], ENT_QUOTES, 'UTF-8') ?></textarea>
        <div id="previewPanel" style="display:none;margin-top:12px;border:1px solid var(--border-color);border-radius:8px;overflow:hidden;">
            <div style="padding:8px 12px;background:var(--bg-secondary);font-size:12px;color:var(--text-secondary);">Preview (variables shown as-is)</div>
            <iframe id="previewFrame" style="width:100%;height:400px;border:none;background:#fff;"></iframe>
        </div>
    </div>

    <div style="display:flex;gap:12px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Template</button>
        <a href="/admin/mail/templates" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<script>
let previewVisible = false;

function togglePreview() {
    const panel = document.getElementById('previewPanel');
    const icon  = document.getElementById('previewIcon');
    previewVisible = !previewVisible;
    panel.style.display = previewVisible ? 'block' : 'none';
    icon.className = previewVisible ? 'fas fa-eye-slash' : 'fas fa-eye';
    if (previewVisible) {
        const frame = document.getElementById('previewFrame');
        frame.contentDocument.open();
        frame.contentDocument.write(document.getElementById('bodyField').value);
        frame.contentDocument.close();
    }
}

function insertVar(variable) {
    const ta = document.getElementById('bodyField');
    const start = ta.selectionStart;
    const end   = ta.selectionEnd;
    const val   = ta.value;
    ta.value = val.slice(0, start) + variable + val.slice(end);
    ta.selectionStart = ta.selectionEnd = start + variable.length;
    ta.focus();
}
</script>

<?php View::endSection(); ?>
