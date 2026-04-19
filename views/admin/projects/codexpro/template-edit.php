<?php use Core\View; use Core\Security; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
.edit-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 30px;
    max-width: 780px;
}
.edit-card h2 { color: var(--cyan); margin: 0 0 24px; font-size: 1.35rem; }
.form-group { margin-bottom: 18px; }
.form-group label { display: block; margin-bottom: 6px; color: var(--text-secondary); font-size: 13px; font-weight: 600; }
.form-group input[type=text],
.form-group select,
.form-group textarea {
    width: 100%;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 9px 12px;
    color: var(--text-primary);
    font-size: 14px;
    box-sizing: border-box;
    transition: border-color .2s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus { outline: none; border-color: var(--cyan); }
.form-group textarea.code-area {
    font-family: 'Fira Mono', 'Consolas', monospace;
    min-height: 130px;
    resize: vertical;
    font-size: 13px;
}
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
.form-check { display: flex; align-items: center; gap: 10px; }
.form-check input[type=checkbox] { width: 16px; height: 16px; accent-color: var(--cyan); }
.form-actions { display: flex; gap: 12px; align-items: center; margin-top: 24px; }
.btn { padding: 9px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; border: none; cursor: pointer; text-decoration: none; display: inline-block; transition: opacity .2s; }
.btn:hover { opacity: .82; }
.btn-primary { background: var(--cyan); color: var(--bg-primary); }
.btn-secondary { background: var(--bg-secondary); color: var(--text-primary); border: 1px solid var(--border-color); }
.flash-success { background: rgba(0,240,100,.12); border: 1px solid #00f064; color: #00f064; padding: 12px 18px; border-radius: 8px; margin-bottom: 18px; }
.flash-error   { background: rgba(231,76,60,.12);  border: 1px solid #e74c3c; color: #e74c3c; padding: 12px 18px; border-radius: 8px; margin-bottom: 18px; }
@media (max-width: 640px) { .form-row { grid-template-columns: 1fr; } }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>

<?php
$sessionFlash = $_SESSION['flash'] ?? [];
if (!empty($sessionFlash['success'])): ?>
    <div class="flash-success">✓ <?= htmlspecialchars($sessionFlash['success']) ?></div>
<?php elseif (!empty($sessionFlash['error'])): ?>
    <div class="flash-error">✗ <?= htmlspecialchars($sessionFlash['error']) ?></div>
<?php endif;
unset($_SESSION['flash']); ?>

<div class="edit-card">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
        <h2 style="margin:0;">Edit Template <span style="color:var(--text-secondary); font-weight:400; font-size:.9em;">#<?= (int)($template['id'] ?? 0) ?></span></h2>
        <a href="/admin/projects/codexpro/templates" class="btn btn-secondary" style="font-size:13px;">← Back to Templates</a>
    </div>

    <form method="POST" action="/admin/projects/codexpro/templates/<?= (int)($template['id'] ?? 0) ?>/update">
        <input type="hidden" name="_csrf_token" value="<?= Security::generateCsrfToken() ?>">

        <div class="form-group">
            <label>Name <span style="color:#e74c3c">*</span></label>
            <input type="text" name="name" required maxlength="120"
                   value="<?= htmlspecialchars($template['name'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Description</label>
            <input type="text" name="description" maxlength="500"
                   value="<?= htmlspecialchars($template['description'] ?? '') ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Category</label>
                <select name="category">
                    <?php foreach (['basic' => 'Basic', 'html' => 'HTML', 'css' => 'CSS', 'javascript' => 'JavaScript', 'php' => 'PHP', 'react' => 'React', 'other' => 'Other'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($template['category'] ?? '') === $val ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Language</label>
                <select name="language">
                    <?php foreach (['html' => 'HTML', 'css' => 'CSS', 'javascript' => 'JavaScript', 'php' => 'PHP'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($template['language'] ?? '') === $val ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>HTML Content</label>
            <textarea name="html_content" class="code-area"><?= htmlspecialchars($template['html_content'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label>CSS Content</label>
            <textarea name="css_content" class="code-area"><?= htmlspecialchars($template['css_content'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label>JS Content</label>
            <textarea name="js_content" class="code-area"><?= htmlspecialchars($template['js_content'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <div class="form-check">
                <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                       <?= !empty($template['is_active']) ? 'checked' : '' ?>>
                <label for="edit_is_active" style="margin:0; cursor:pointer; color:var(--text-primary);">
                    Active (visible to users)
                </label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="/admin/projects/codexpro/templates" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php View::endSection(); ?>
