<?php use Core\View; use Core\Security; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<style>
.form-container {
    max-width: 800px;
    margin: 0 auto;
}

.form-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 30px;
}

.form-group {
    margin-bottom: 24px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-primary);
}

.form-label .required {
    color: #dc3545;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-card);
    color: var(--text-primary);
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #25D366;
    box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.1);
}

.form-control[type="checkbox"] {
    width: auto;
    margin-right: 8px;
}

.form-text {
    display: block;
    margin-top: 6px;
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 30px;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #25D366;
    color: white;
}

.btn-primary:hover {
    background: #1da851;
}

.btn-secondary {
    background: var(--bg-card);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}

.btn-secondary:hover {
    background: var(--bg-primary);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="form-container">
    <div class="page-header mb-4">
        <h1 class="page-title">
            <i class="fas fa-<?= $plan ? 'edit' : 'plus' ?>"></i> 
            <?= $plan ? 'Edit' : 'Create' ?> Subscription Plan
        </h1>
        <p class="text-muted">
            <?= $plan ? 'Update the subscription plan details' : 'Create a new subscription plan' ?>
        </p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?= View::e($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" action="<?= $plan ? '/admin/whatsapp/subscription-plans/update/' . $plan['id'] : '/admin/whatsapp/subscription-plans/create' ?>">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
            
            <!-- Plan Name -->
            <div class="form-group">
                <label class="form-label">
                    Plan Name <span class="required">*</span>
                </label>
                <input type="text" name="name" class="form-control" 
                       value="<?= View::e($plan['name'] ?? '') ?>" required>
                <small class="form-text">Enter a descriptive name for the plan (e.g., "Premium Plan")</small>
            </div>
            
            <!-- Description -->
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= View::e($plan['description'] ?? '') ?></textarea>
                <small class="form-text">Brief description of what this plan includes</small>
            </div>
            
            <!-- Price & Currency -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">
                        Price <span class="required">*</span>
                    </label>
                    <input type="number" name="price" class="form-control" step="0.01" min="0"
                           value="<?= View::e($plan['price'] ?? '0.00') ?>" required>
                    <small class="form-text">Plan price (use 0 for free plans)</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Currency</label>
                    <select name="currency" class="form-control">
                        <option value="USD" <?= ($plan['currency'] ?? 'USD') === 'USD' ? 'selected' : '' ?>>USD</option>
                        <option value="EUR" <?= ($plan['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR</option>
                        <option value="GBP" <?= ($plan['currency'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP</option>
                        <option value="INR" <?= ($plan['currency'] ?? '') === 'INR' ? 'selected' : '' ?>>INR</option>
                    </select>
                </div>
            </div>
            
            <!-- Limits -->
            <div class="form-group">
                <label class="form-label">Messages Limit</label>
                <input type="number" name="messages_limit" class="form-control" min="0"
                       value="<?= View::e($plan['messages_limit'] ?? '0') ?>">
                <small class="form-text">Maximum messages per period (0 = unlimited)</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Sessions Limit</label>
                <input type="number" name="sessions_limit" class="form-control" min="0"
                       value="<?= View::e($plan['sessions_limit'] ?? '0') ?>">
                <small class="form-text">Maximum concurrent sessions (0 = unlimited)</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">API Calls Limit</label>
                <input type="number" name="api_calls_limit" class="form-control" min="0"
                       value="<?= View::e($plan['api_calls_limit'] ?? '0') ?>">
                <small class="form-text">Maximum API calls per period (0 = unlimited)</small>
            </div>
            
            <!-- Duration -->
            <div class="form-group">
                <label class="form-label">Duration (Days)</label>
                <input type="number" name="duration_days" class="form-control" min="1"
                       value="<?= View::e($plan['duration_days'] ?? '30') ?>">
                <small class="form-text">Number of days the plan is valid for</small>
            </div>
            
            <!-- Active Status -->
            <div class="form-group">
                <label class="form-label">
                    <input type="checkbox" name="is_active" value="1" 
                           <?= ($plan['is_active'] ?? 1) ? 'checked' : '' ?>>
                    Active
                </label>
                <small class="form-text">Only active plans can be assigned to users</small>
            </div>
            
            <!-- Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <?= $plan ? 'Update Plan' : 'Create Plan' ?>
                </button>
                <a href="/admin/whatsapp/subscription-plans" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php View::endSection(); ?>
