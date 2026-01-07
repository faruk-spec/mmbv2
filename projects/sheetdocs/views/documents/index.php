<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('sheetdocs:app'); ?>

<?php View::section('content'); ?>
<div class="header">
    <div>
        <h1>My Documents</h1>
        <p style="color: var(--text-secondary);">View and manage all your documents</p>
    </div>
    <a href="/projects/sheetdocs/documents/new" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        New Document
    </a>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?= View::e(Helpers::getFlash('success')) ?>
    </div>
<?php endif; ?>

<?php if (!empty($documents)): ?>
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
    <?php foreach ($documents as $doc): ?>
    <a href="/projects/sheetdocs/documents/<?= $doc['id'] ?>/edit" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; cursor: pointer; transition: all 0.3s; text-decoration: none; color: inherit; display: block;">
        <div style="font-size: 18px; font-weight: 600; margin-bottom: 8px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-file-alt" style="color: var(--cyan);"></i>
            <?= View::e($doc['title']) ?>
        </div>
        <div style="color: var(--text-secondary); font-size: 13px; margin-bottom: 8px;">
            Updated <?= Helpers::timeAgo($doc['updated_at']) ?>
        </div>
        <div style="display: flex; gap: 12px; margin-top: 12px;">
            <span style="background: rgba(0, 212, 170, 0.1); color: var(--cyan); padding: 4px 12px; border-radius: 6px; font-size: 12px;">
                <?= ucfirst($doc['visibility']) ?>
            </span>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">
    <i class="fas fa-file-alt" style="font-size: 64px; opacity: 0.3; margin-bottom: 20px;"></i>
    <h3 style="margin-bottom: 12px;">No documents yet</h3>
    <p>Create your first document to get started!</p>
    <a href="/projects/sheetdocs/documents/new" class="btn btn-primary" style="margin-top: 20px;">
        <i class="fas fa-plus"></i>
        Create Document
    </a>
</div>
<?php endif; ?>
<?php View::endSection(); ?>
