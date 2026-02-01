<?php use Core\View; use Core\Security; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<style>
.filters-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 6px;
}

.filter-control {
    padding: 8px 12px;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    background: var(--bg-card);
    color: var(--text-primary);
}

.data-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: rgba(37, 211, 102, 0.1);
    padding: 12px 16px;
    text-align: left;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
}

.data-table td {
    padding: 16px;
    border-bottom: 1px solid var(--border-color);
}

.data-table tr:hover {
    background: var(--hover-bg);
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-success {
    background: rgba(40, 199, 111, 0.2);
    color: #28c76f;
}

.badge-danger {
    background: rgba(234, 84, 85, 0.2);
    color: #ea5455;
}

.badge-warning {
    background: rgba(255, 159, 67, 0.2);
    color: #ff9f43;
}

.badge-secondary {
    background: rgba(130, 134, 139, 0.2);
    color: #82868b;
}

.progress-bar {
    width: 100px;
    height: 8px;
    background: var(--bg-card);
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #25D366, #128C7E);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.progress-fill.danger {
    background: linear-gradient(90deg, #dc3545, #c82333);
}

.progress-fill.warning {
    background: linear-gradient(90deg, #ffc107, #ff9800);
}

.action-dropdown {
    position: relative;
    display: inline-block;
}

.action-btn {
    padding: 6px 12px;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    background: var(--bg-card);
    color: var(--text-primary);
    cursor: pointer;
    font-size: 0.875rem;
}

.action-btn:hover {
    background: var(--hover-bg);
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 24px;
}

.page-link {
    padding: 8px 16px;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    background: var(--bg-secondary);
    color: var(--text-primary);
    text-decoration: none;
    transition: all 0.3s ease;
}

.page-link:hover {
    background: var(--hover-bg);
    border-color: #25D366;
}

.page-link.active {
    background: #25D366;
    color: white;
    border-color: #25D366;
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 600;
    color: var(--text-primary);
}

.user-email {
    font-size: 0.875rem;
    color: var(--text-secondary);
}
</style>

<div class="page-header mb-4">
    <div>
        <h1 class="page-title">
            <i class="fas fa-users"></i> User Subscriptions
        </h1>
        <p class="text-muted">Manage all WhatsApp API user subscriptions</p>
    </div>
    <a href="/admin/whatsapp/user-subscriptions/assign" class="btn btn-primary">
        <i class="fas fa-plus"></i> Assign Subscription
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

<!-- Filters -->
<div class="filters-card">
    <form method="GET" action="/admin/whatsapp/user-subscriptions">
        <div class="filters-grid">
            <div class="filter-group">
                <label class="filter-label">Status</label>
                <select name="status" class="filter-control">
                    <option value="">All Status</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="expired" <?= ($filters['status'] ?? '') === 'expired' ? 'selected' : '' ?>>Expired</option>
                    <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Plan Type</label>
                <select name="plan_type" class="filter-control">
                    <option value="">All Plans</option>
                    <option value="free" <?= ($filters['plan_type'] ?? '') === 'free' ? 'selected' : '' ?>>Free</option>
                    <option value="basic" <?= ($filters['plan_type'] ?? '') === 'basic' ? 'selected' : '' ?>>Basic</option>
                    <option value="premium" <?= ($filters['plan_type'] ?? '') === 'premium' ? 'selected' : '' ?>>Premium</option>
                    <option value="enterprise" <?= ($filters['plan_type'] ?? '') === 'enterprise' ? 'selected' : '' ?>>Enterprise</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">User ID</label>
                <input type="number" name="user_id" class="filter-control" 
                       value="<?= View::e($filters['user_id'] ?? '') ?>" placeholder="Search by User ID">
            </div>
            
            <div class="filter-group">
                <label class="filter-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Subscriptions Table -->
<div class="data-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Plan</th>
                <th>Status</th>
                <th>Period</th>
                <th>Usage</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subscriptions as $sub): ?>
                <tr>
                    <td>
                        <div class="user-info">
                            <span class="user-name"><?= View::e($sub['user_name']) ?></span>
                            <span class="user-email"><?= View::e($sub['user_email']) ?></span>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-<?= $sub['plan_type'] === 'enterprise' ? 'success' : ($sub['plan_type'] === 'premium' ? 'warning' : 'secondary') ?>">
                            <?= strtoupper($sub['plan_type']) ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        $statusClass = 'secondary';
                        if ($sub['status'] === 'active') $statusClass = 'success';
                        elseif ($sub['status'] === 'expired') $statusClass = 'danger';
                        elseif ($sub['status'] === 'cancelled') $statusClass = 'warning';
                        ?>
                        <span class="badge badge-<?= $statusClass ?>">
                            <?= strtoupper($sub['status']) ?>
                        </span>
                    </td>
                    <td>
                        <div style="font-size: 0.875rem;">
                            <div><?= date('M d, Y', strtotime($sub['start_date'])) ?></div>
                            <div style="color: var(--text-secondary);">
                                to <?= date('M d, Y', strtotime($sub['end_date'])) ?>
                            </div>
                            <?php if ($sub['days_remaining'] !== null): ?>
                                <div style="margin-top: 4px;">
                                    <strong style="color: <?= $sub['days_remaining'] < 7 ? '#dc3545' : '#25D366' ?>">
                                        <?= $sub['days_remaining'] ?> days left
                                    </strong>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div style="font-size: 0.75rem;">
                            <div style="margin-bottom: 8px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                    <span>Messages:</span>
                                    <strong><?= number_format($sub['messages_used']) ?> / <?= $sub['messages_limit'] == 0 ? '∞' : number_format($sub['messages_limit']) ?></strong>
                                </div>
                                <?php if ($sub['messages_limit'] > 0): ?>
                                    <div class="progress-bar">
                                        <div class="progress-fill <?= $sub['messages_usage_percent'] > 90 ? 'danger' : ($sub['messages_usage_percent'] > 75 ? 'warning' : '') ?>" 
                                             style="width: <?= min($sub['messages_usage_percent'], 100) ?>%"></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="margin-bottom: 8px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                    <span>Sessions:</span>
                                    <strong><?= $sub['sessions_used'] ?> / <?= $sub['sessions_limit'] == 0 ? '∞' : $sub['sessions_limit'] ?></strong>
                                </div>
                                <?php if ($sub['sessions_limit'] > 0): ?>
                                    <div class="progress-bar">
                                        <div class="progress-fill <?= $sub['sessions_usage_percent'] > 90 ? 'danger' : ($sub['sessions_usage_percent'] > 75 ? 'warning' : '') ?>" 
                                             style="width: <?= min($sub['sessions_usage_percent'], 100) ?>%"></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                    <span>API Calls:</span>
                                    <strong><?= number_format($sub['api_calls_used']) ?> / <?= $sub['api_calls_limit'] == 0 ? '∞' : number_format($sub['api_calls_limit']) ?></strong>
                                </div>
                                <?php if ($sub['api_calls_limit'] > 0): ?>
                                    <div class="progress-bar">
                                        <div class="progress-fill <?= $sub['api_calls_usage_percent'] > 90 ? 'danger' : ($sub['api_calls_usage_percent'] > 75 ? 'warning' : '') ?>" 
                                             style="width: <?= min($sub['api_calls_usage_percent'], 100) ?>%"></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                            <a href="/admin/whatsapp/user-subscriptions/edit/<?= $sub['id'] ?>" class="action-btn" title="Edit subscription">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            
                            <form method="POST" action="/admin/whatsapp/user-subscriptions/update/<?= $sub['id'] ?>" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
                                <input type="hidden" name="action" value="extend">
                                <input type="hidden" name="days" value="30">
                                <button type="submit" class="action-btn" title="Extend by 30 days">
                                    <i class="fas fa-calendar-plus"></i> Extend
                                </button>
                            </form>
                            
                            <form method="POST" action="/admin/whatsapp/user-subscriptions/update/<?= $sub['id'] ?>" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
                                <input type="hidden" name="action" value="reset_usage">
                                <button type="submit" class="action-btn" title="Reset usage">
                                    <i class="fas fa-redo"></i> Reset
                                </button>
                            </form>
                            
                            <?php if ($sub['status'] === 'active'): ?>
                                <form method="POST" action="/admin/whatsapp/user-subscriptions/cancel/<?= $sub['id'] ?>" 
                                      onsubmit="return confirm('Cancel this subscription?')" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
                                    <button type="submit" class="action-btn" title="Cancel subscription" style="color: #dc3545;">
                                        <i class="fas fa-ban"></i> Cancel
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php if (empty($subscriptions)): ?>
        <div style="padding: 40px; text-align: center; color: var(--text-secondary);">
            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 16px; opacity: 0.3;"></i>
            <p>No subscriptions found</p>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($currentPage > 1): ?>
            <a href="?page=<?= $currentPage - 1 ?><?= !empty($_GET['status']) ? '&status=' . $_GET['status'] : '' ?><?= !empty($_GET['plan_type']) ? '&plan_type=' . $_GET['plan_type'] : '' ?>" class="page-link">
                <i class="fas fa-chevron-left"></i> Previous
            </a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
            <a href="?page=<?= $i ?><?= !empty($_GET['status']) ? '&status=' . $_GET['status'] : '' ?><?= !empty($_GET['plan_type']) ? '&plan_type=' . $_GET['plan_type'] : '' ?>" 
               class="page-link <?= $i === $currentPage ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
        
        <?php if ($currentPage < $totalPages): ?>
            <a href="?page=<?= $currentPage + 1 ?><?= !empty($_GET['status']) ? '&status=' . $_GET['status'] : '' ?><?= !empty($_GET['plan_type']) ? '&plan_type=' . $_GET['plan_type'] : '' ?>" class="page-link">
                Next <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php View::endSection(); ?>
