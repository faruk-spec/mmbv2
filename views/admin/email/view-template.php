<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content">
    <h1><?= $title ?></h1>
    <?php if (Helpers::hasFlash('success')): ?>
        <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
    <?php endif; ?>
    <?php if (Helpers::hasFlash('error')): ?>
        <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
    <?php endif; ?>

    <div class="card">
        <?php if (!empty($isEditable)): ?>
            <form method="POST" action="/admin/email/templates/update">
                <?= Security::csrfField() ?>
                <input type="hidden" name="template" value="<?= View::e($templateName) ?>">
                <textarea name="content" class="form-input" style="min-height:500px;font-family:monospace;"><?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8') ?></textarea>
                <div style="margin-top:12px;display:flex;gap:8px;">
                    <button type="submit" class="btn btn-primary">Save Template</button>
                    <a href="/admin/email/templates/view?template=<?= urlencode($templateName) ?>" class="btn btn-secondary">Preview Source</a>
                </div>
            </form>
        <?php else: ?>
            <pre><code><?= htmlspecialchars($content) ?></code></pre>
            <div style="margin-top:12px;">
                <a href="/admin/email/templates/edit?template=<?= urlencode($templateName) ?>" class="btn btn-primary">Edit Template</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php View::endSection(); ?>
