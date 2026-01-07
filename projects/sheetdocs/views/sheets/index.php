<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('sheetdocs:app'); ?>

<?php View::section('content'); ?>
<div class="header">
    <div>
        <h1>My Spreadsheets</h1>
        <p style="color: var(--text-secondary);">View and manage all your spreadsheets</p>
    </div>
    <a href="/projects/sheetdocs/sheets/new" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        New Spreadsheet
    </a>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?= View::e(Helpers::getFlash('success')) ?>
    </div>
<?php endif; ?>

<?php if (!empty($sheets)): ?>
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
    <?php foreach ($sheets as $sheet): ?>
    <a href="/projects/sheetdocs/sheets/<?= $sheet['id'] ?>/edit" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; cursor: pointer; transition: all 0.3s; text-decoration: none; color: inherit; display: block;">
        <div style="font-size: 18px; font-weight: 600; margin-bottom: 8px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-table" style="color: var(--cyan);"></i>
            <?= View::e($sheet['title']) ?>
        </div>
        <div style="color: var(--text-secondary); font-size: 13px; margin-bottom: 8px;">
            Updated <?= Helpers::timeAgo($sheet['updated_at']) ?>
        </div>
        <div style="display: flex; gap: 12px; margin-top: 12px;">
            <span style="background: rgba(0, 212, 170, 0.1); color: var(--cyan); padding: 4px 12px; border-radius: 6px; font-size: 12px;">
                <?= ucfirst($sheet['visibility']) ?>
            </span>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">
    <i class="fas fa-table" style="font-size: 64px; opacity: 0.3; margin-bottom: 20px;"></i>
    <h3 style="margin-bottom: 12px;">No spreadsheets yet</h3>
    <p>Create your first spreadsheet to get started!</p>
    <a href="/projects/sheetdocs/sheets/new" class="btn btn-primary" style="margin-top: 20px;">
        <i class="fas fa-plus"></i>
        Create Spreadsheet
    </a>
</div>
<?php endif; ?>
<?php View::endSection(); ?>
