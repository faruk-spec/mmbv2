<?php use Core\View; use Core\Security; use Core\Helpers; ?>
<?php View::extend('sheetdocs:app'); ?>

<?php View::section('content'); ?>
<div class="header">
    <div>
        <h1>Create New Document</h1>
        <p style="color: var(--text-secondary);">Start a new document or use a template</p>
    </div>
</div>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?= View::e(Helpers::getFlash('error')) ?>
    </div>
<?php endif; ?>

<div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 30px; max-width: 800px;">
    <form method="POST" action="/projects/sheetdocs/documents/store" style="display: flex; flex-direction: column; gap: 20px;">
        <?= Security::csrfField() ?>
        
        <div>
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Document Title</label>
            <input type="text" name="title" placeholder="Enter document title" required
                   style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); font-size: 14px;">
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Content (Optional)</label>
            <textarea name="content" rows="10" placeholder="Start typing your document content..."
                      style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); font-size: 14px; resize: vertical;"></textarea>
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Visibility</label>
            <select name="visibility" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); font-size: 14px;">
                <option value="private">Private - Only you can see this</option>
                <option value="shared">Shared - People with link can view</option>
                <option value="public">Public - Anyone can view</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 20px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Create Document
            </button>
            <a href="/projects/sheetdocs/documents" class="btn" style="background: var(--bg-secondary); color: var(--text-primary);">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php if (!empty($templates)): ?>
<div style="margin-top: 40px;">
    <h2 style="margin-bottom: 20px; font-size: 24px;">Or Start from a Template</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
        <?php foreach ($templates as $template): ?>
        <div onclick="window.location.href='/projects/sheetdocs/templates/<?= $template['id'] ?>/use'" 
             style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; cursor: pointer; transition: all 0.3s;">
            <div style="font-size: 16px; font-weight: 600; margin-bottom: 8px; color: var(--cyan);">
                <?= View::e($template['title']) ?>
            </div>
            <div style="color: var(--text-secondary); font-size: 13px;">
                <?= View::e($template['description'] ?? 'No description') ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
<?php View::endSection(); ?>
