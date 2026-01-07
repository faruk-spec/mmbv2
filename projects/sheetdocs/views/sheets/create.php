<?php use Core\View; use Core\Security; use Core\Helpers; ?>
<?php View::extend('sheetdocs:app'); ?>

<?php View::section('content'); ?>
<div class="header">
    <div>
        <h1>Create New Spreadsheet</h1>
        <p style="color: var(--text-secondary);">Start a new spreadsheet</p>
    </div>
</div>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?= View::e(Helpers::getFlash('error')) ?>
    </div>
<?php endif; ?>

<div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 30px; max-width: 800px;">
    <form method="POST" action="/projects/sheetdocs/sheets" style="display: flex; flex-direction: column; gap: 20px;">
        <?= Security::csrfField() ?>
        
        <div>
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Spreadsheet Title</label>
            <input type="text" name="title" placeholder="Enter spreadsheet title" required
                   style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); font-size: 14px;">
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
                Create Spreadsheet
            </button>
            <a href="/projects/sheetdocs/sheets" class="btn" style="background: var(--bg-secondary); color: var(--text-primary);">
                Cancel
            </a>
        </div>
    </form>
</div>
<?php View::endSection(); ?>
