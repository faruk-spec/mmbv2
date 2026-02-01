<?php use Core\View; use Core\Security; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<style>
.plan-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.plan-card {
    background: var(--bg-secondary);
    border: 2px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    transition: all 0.3s ease;
    position: relative;
}

.plan-card:hover {
    border-color: #25D366;
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(37, 211, 102, 0.2);
}

.plan-card.inactive {
    opacity: 0.6;
}

.plan-header {
    margin-bottom: 20px;
}

.plan-name {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.plan-price {
    font-size: 2rem;
    font-weight: 700;
    color: #25D366;
}

.plan-price small {
    font-size: 1rem;
    color: var(--text-secondary);
}

.plan-description {
    color: var(--text-secondary);
    margin-bottom: 20px;
    line-height: 1.6;
}

.plan-features {
    list-style: none;
    padding: 0;
    margin: 20px 0;
}

.plan-features li {
    padding: 10px 0;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.plan-features li:last-child {
    border-bottom: none;
}

.feature-label {
    color: var(--text-secondary);
}

.feature-value {
    color: var(--text-primary);
    font-weight: 600;
}

.plan-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.plan-actions button,
.plan-actions a {
    flex: 1;
    padding: 10px;
    border-radius: 8px;
    text-align: center;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-edit {
    background: rgba(0, 123, 255, 0.1);
    color: #007bff;
    border: 1px solid #007bff;
}

.btn-edit:hover {
    background: #007bff;
    color: white;
}

.btn-delete {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
    border: 1px solid #dc3545;
}

.btn-delete:hover {
    background: #dc3545;
    color: white;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-box {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 20px;
    text-align: center;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: #25D366;
    margin-bottom: 8px;
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-secondary);
    text-transform: uppercase;
}

.badge-active {
    background: rgba(40, 199, 111, 0.2);
    color: #28c76f;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    position: absolute;
    top: 15px;
    right: 15px;
}

.badge-inactive {
    background: rgba(234, 84, 85, 0.2);
    color: #ea5455;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    position: absolute;
    top: 15px;
    right: 15px;
}
</style>

<div class="page-header mb-4">
    <div>
        <h1 class="page-title">
            <i class="fas fa-tags"></i> Subscription Plans
        </h1>
        <p class="text-muted">Manage WhatsApp API subscription plans</p>
    </div>
    <a href="/admin/whatsapp/subscription-plans/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create New Plan
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> <?= View::e($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle"></i> <?= View::e($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-box">
        <div class="stat-value"><?= $stats['totalPlans'] ?></div>
        <div class="stat-label">Total Plans</div>
    </div>
    <div class="stat-box">
        <div class="stat-value"><?= $stats['activePlans'] ?></div>
        <div class="stat-label">Active Plans</div>
    </div>
    <div class="stat-box">
        <div class="stat-value"><?= $stats['totalSubscriptions'] ?></div>
        <div class="stat-label">Total Subscriptions</div>
    </div>
    <div class="stat-box">
        <div class="stat-value"><?= $stats['activeSubscriptions'] ?></div>
        <div class="stat-label">Active Subscriptions</div>
    </div>
</div>

<!-- Plans -->
<div class="plan-grid">
    <?php foreach ($plans as $plan): ?>
        <div class="plan-card <?= $plan['is_active'] ? '' : 'inactive' ?>">
            <span class="<?= $plan['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                <?= $plan['is_active'] ? 'Active' : 'Inactive' ?>
            </span>
            
            <div class="plan-header">
                <div class="plan-name"><?= View::e($plan['name']) ?></div>
                <div class="plan-price">
                    <?= $plan['currency'] ?> <?= number_format($plan['price'], 2) ?>
                    <small>/ <?= $plan['duration_days'] ?> days</small>
                </div>
            </div>
            
            <?php if ($plan['description']): ?>
                <div class="plan-description">
                    <?= View::e($plan['description']) ?>
                </div>
            <?php endif; ?>
            
            <ul class="plan-features">
                <li>
                    <span class="feature-label">Messages</span>
                    <span class="feature-value">
                        <?= $plan['messages_limit'] == 0 ? 'Unlimited' : number_format($plan['messages_limit']) ?>
                    </span>
                </li>
                <li>
                    <span class="feature-label">Sessions</span>
                    <span class="feature-value">
                        <?= $plan['sessions_limit'] == 0 ? 'Unlimited' : number_format($plan['sessions_limit']) ?>
                    </span>
                </li>
                <li>
                    <span class="feature-label">API Calls</span>
                    <span class="feature-value">
                        <?= $plan['api_calls_limit'] == 0 ? 'Unlimited' : number_format($plan['api_calls_limit']) ?>
                    </span>
                </li>
            </ul>
            
            <div class="plan-actions">
                <a href="/admin/whatsapp/subscription-plans/edit/<?= $plan['id'] ?>" class="btn-edit">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form method="POST" action="/admin/whatsapp/subscription-plans/delete/<?= $plan['id'] ?>" 
                      onsubmit="return confirm('Are you sure you want to delete this plan?')" style="flex: 1;">
                    <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
                    <button type="submit" class="btn-delete" style="width: 100%; border: 1px solid #dc3545;">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if (empty($plans)): ?>
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No subscription plans available. Create your first plan!
            </div>
        </div>
    <?php endif; ?>
</div>

<?php View::endSection(); ?>
