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

.plan-preview {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 20px;
    margin-top: 12px;
    display: none;
}

.plan-preview.active {
    display: block;
}

.plan-preview h4 {
    font-size: 1.1rem;
    margin-bottom: 16px;
    color: var(--text-primary);
}

.plan-feature {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid var(--border-color);
}

.plan-feature:last-child {
    border-bottom: none;
}

.feature-name {
    color: var(--text-secondary);
}

.feature-value {
    color: var(--text-primary);
    font-weight: 600;
}
</style>

<div class="form-container">
    <div class="page-header mb-4">
        <h1 class="page-title">
            <i class="fas fa-user-plus"></i> Assign Subscription
        </h1>
        <p class="text-muted">Manually assign a subscription to a user</p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?= View::e($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" action="/admin/whatsapp/user-subscriptions/assign" id="assignForm">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
            
            <!-- Select User -->
            <div class="form-group">
                <label class="form-label">
                    Select User <span class="required">*</span>
                </label>
                <select name="user_id" class="form-control" required>
                    <option value="">-- Select a user --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>">
                            <?= View::e($user['name']) ?> (<?= View::e($user['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text">Choose the user to assign the subscription to</small>
            </div>
            
            <!-- Select Plan -->
            <div class="form-group">
                <label class="form-label">
                    Select Plan <span class="required">*</span>
                </label>
                <select name="plan_id" class="form-control" id="planSelect" required>
                    <option value="">-- Select a plan --</option>
                    <?php foreach ($plans as $plan): ?>
                        <option value="<?= $plan['id'] ?>" 
                                data-name="<?= View::e($plan['name']) ?>"
                                data-price="<?= $plan['price'] ?>"
                                data-currency="<?= $plan['currency'] ?>"
                                data-messages="<?= $plan['messages_limit'] ?>"
                                data-sessions="<?= $plan['sessions_limit'] ?>"
                                data-api="<?= $plan['api_calls_limit'] ?>"
                                data-duration="<?= $plan['duration_days'] ?>">
                            <?= View::e($plan['name']) ?> - <?= $plan['currency'] ?> <?= number_format($plan['price'], 2) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text">Choose the subscription plan</small>
                
                <!-- Plan Preview -->
                <div id="planPreview" class="plan-preview">
                    <h4><i class="fas fa-info-circle"></i> Plan Details</h4>
                    <div class="plan-feature">
                        <span class="feature-name">Price:</span>
                        <span class="feature-value" id="previewPrice">-</span>
                    </div>
                    <div class="plan-feature">
                        <span class="feature-name">Messages Limit:</span>
                        <span class="feature-value" id="previewMessages">-</span>
                    </div>
                    <div class="plan-feature">
                        <span class="feature-name">Sessions Limit:</span>
                        <span class="feature-value" id="previewSessions">-</span>
                    </div>
                    <div class="plan-feature">
                        <span class="feature-name">API Calls Limit:</span>
                        <span class="feature-value" id="previewApi">-</span>
                    </div>
                    <div class="plan-feature">
                        <span class="feature-name">Duration:</span>
                        <span class="feature-value" id="previewDuration">-</span>
                    </div>
                </div>
            </div>
            
            <!-- Custom Duration -->
            <div class="form-group">
                <label class="form-label">
                    Duration (Days) <span class="required">*</span>
                </label>
                <input type="number" name="duration_days" class="form-control" 
                       id="durationInput" min="1" value="30" required>
                <small class="form-text">Override the default plan duration if needed</small>
            </div>
            
            <!-- Info Box -->
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Note:</strong> If the user already has an active subscription, it will be deactivated and replaced with the new one.
            </div>
            
            <!-- Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i>
                    Assign Subscription
                </button>
                <a href="/admin/whatsapp/user-subscriptions" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('planSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const preview = document.getElementById('planPreview');
    
    if (this.value) {
        const messages = selectedOption.dataset.messages;
        const sessions = selectedOption.dataset.sessions;
        const api = selectedOption.dataset.api;
        const duration = selectedOption.dataset.duration;
        
        document.getElementById('previewPrice').textContent = 
            selectedOption.dataset.currency + ' ' + parseFloat(selectedOption.dataset.price).toFixed(2);
        document.getElementById('previewMessages').textContent = 
            messages == 0 ? 'Unlimited' : parseInt(messages).toLocaleString();
        document.getElementById('previewSessions').textContent = 
            sessions == 0 ? 'Unlimited' : parseInt(sessions).toLocaleString();
        document.getElementById('previewApi').textContent = 
            api == 0 ? 'Unlimited' : parseInt(api).toLocaleString();
        document.getElementById('previewDuration').textContent = 
            duration + ' days';
        
        // Update duration input
        document.getElementById('durationInput').value = duration;
        
        preview.classList.add('active');
    } else {
        preview.classList.remove('active');
    }
});
</script>

<?php View::endSection(); ?>
