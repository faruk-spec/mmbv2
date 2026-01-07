<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('sheetdocs:app'); ?>

<?php View::section('content'); ?>
<div class="header">
    <div>
        <h1>Templates</h1>
        <p style="color: var(--text-secondary);">Choose from pre-built templates to get started quickly</p>
    </div>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?= View::e(Helpers::getFlash('success')) ?>
    </div>
<?php endif; ?>

<?php if (!empty($templates)): ?>
<?php
// Group templates by category
$groupedTemplates = [];
foreach ($templates as $template) {
    $category = $template['category'] ?? 'Other';
    if (!isset($groupedTemplates[$category])) {
        $groupedTemplates[$category] = [];
    }
    $groupedTemplates[$category][] = $template;
}
?>

<?php foreach ($groupedTemplates as $category => $categoryTemplates): ?>
<div style="margin-bottom: 40px;">
    <h2 style="margin-bottom: 20px; font-size: 20px; color: var(--cyan);">
        <i class="fas fa-folder" style="margin-right: 8px;"></i>
        <?= View::e($category) ?>
    </h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        <?php foreach ($categoryTemplates as $template): ?>
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; transition: all 0.3s;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                <div style="font-size: 18px; font-weight: 600; color: var(--text-primary);">
                    <?= View::e($template['title']) ?>
                </div>
                <?php if ($template['tier'] !== 'free'): ?>
                <span style="background: rgba(255, 170, 0, 0.1); color: #ffaa00; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                    PRO
                </span>
                <?php endif; ?>
            </div>
            
            <div style="color: var(--text-secondary); font-size: 14px; margin-bottom: 16px; line-height: 1.6;">
                <?= View::e($template['description'] ?? 'No description available') ?>
            </div>
            
            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px;">
                <span style="background: rgba(0, 212, 170, 0.1); color: var(--cyan); padding: 4px 12px; border-radius: 6px; font-size: 12px;">
                    <?= ucfirst($template['type']) ?>
                </span>
                <?php if ($template['usage_count'] > 0): ?>
                <span style="color: var(--text-secondary); font-size: 12px; padding: 4px 12px;">
                    <i class="fas fa-users"></i> Used <?= $template['usage_count'] ?> times
                </span>
                <?php endif; ?>
            </div>
            
            <button onclick="window.location.href='/projects/sheetdocs/templates/<?= $template['id'] ?>'" 
                    class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-plus"></i>
                Use Template
            </button>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<?php else: ?>
<div style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">
    <i class="fas fa-clone" style="font-size: 64px; opacity: 0.3; margin-bottom: 20px;"></i>
    <h3 style="margin-bottom: 12px;">No templates available</h3>
    <p>Templates will appear here when they become available.</p>
</div>
<?php endif; ?>
<?php View::endSection(); ?>
