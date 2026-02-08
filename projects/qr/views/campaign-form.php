<?php
/**
 * Campaign Form View - Create/Edit Campaign
 */
$isEdit = isset($campaign);
?>

<div class="glass-card">
    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 30px;">
        <a href="/projects/qr/campaigns" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <h3 class="section-title" style="margin: 0;">
            <i class="fas fa-bullhorn"></i> <?= $isEdit ? 'Edit Campaign' : 'New Campaign' ?>
        </h3>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" style="max-width: 600px;">
        <div class="form-group">
            <label class="form-label">Campaign Name *</label>
            <input type="text" name="name" value="<?= $isEdit ? htmlspecialchars($campaign['name']) : '' ?>" 
                   required class="form-control" placeholder="Enter campaign name">
        </div>
        
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" 
                      placeholder="Describe the purpose of this campaign"><?= $isEdit ? htmlspecialchars($campaign['description']) : '' ?></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" <?= ($isEdit && $campaign['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                <option value="paused" <?= ($isEdit && $campaign['status'] == 'paused') ? 'selected' : '' ?>>Paused</option>
                <option value="archived" <?= ($isEdit && $campaign['status'] == 'archived') ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>
        
        <div class="form-actions" style="margin-top: 30px; display: flex; gap: 10px;">
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> <?= $isEdit ? 'Update Campaign' : 'Create Campaign' ?>
            </button>
            <a href="/projects/qr/campaigns" class="btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<style>
.form-control, .form-select {
    width: 100%;
    padding: 12px 15px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    outline: none;
    border-color: var(--purple);
    box-shadow: 0 0 0 3px rgba(153, 69, 255, 0.1);
}

textarea.form-control {
    resize: vertical;
    font-family: inherit;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    color: var(--text-secondary);
    font-weight: 500;
    font-size: 14px;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: rgba(46, 213, 115, 0.1);
    color: #2ed573;
    border: 1px solid rgba(46, 213, 115, 0.3);
}

.alert-error {
    background: rgba(255, 71, 87, 0.1);
    color: #ff4757;
    border: 1px solid rgba(255, 71, 87, 0.3);
}
</style>
