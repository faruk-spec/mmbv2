<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-box mr-2"></i>
                    Subscription Details
                </h2>
                <div>
                    <a href="/projects/mail/subscriber/billing" class="btn btn-info mr-2">
                        <i class="fas fa-receipt"></i> Billing History
                    </a>
                    <a href="/projects/mail/subscriber/dashboard" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($subscription) && $subscription): ?>
    <!-- Current Subscription Card -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Current Subscription
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Plan Name:</strong><br>
                            <span class="h4">
                                <span class="badge badge-primary p-2">
                                    <?= View::e($subscription['plan_name']) ?>
                                </span>
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Status:</strong><br>
                            <?php
                            $statusClass = match($subscription['status']) {
                                'active' => 'success',
                                'cancelled' => 'danger',
                                'expired' => 'secondary',
                                'past_due' => 'warning',
                                'trialing' => 'info',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge badge-<?= $statusClass ?> p-2">
                                <?= ucfirst($subscription['status']) ?>
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Billing Cycle:</strong><br>
                            <?= ucfirst($subscription['billing_cycle'] ?? 'monthly') ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Price:</strong><br>
                            <?php if ($subscription['billing_cycle'] == 'yearly'): ?>
                                $<?= number_format($subscription['price_yearly'], 2) ?> / year
                            <?php else: ?>
                                $<?= number_format($subscription['price_monthly'], 2) ?> / month
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Current Period Start:</strong><br>
                            <?= isset($subscription['current_period_start']) ? date('F d, Y', strtotime($subscription['current_period_start'])) : 'N/A' ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Current Period End:</strong><br>
                            <?= isset($subscription['current_period_end']) ? date('F d, Y', strtotime($subscription['current_period_end'])) : 'N/A' ?>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="/projects/mail/subscriber/upgrade" class="btn btn-success">
                            <i class="fas fa-arrow-up"></i> Upgrade Plan
                        </a>
                        <?php if ($subscription['status'] == 'active'): ?>
                        <button class="btn btn-warning" onclick="cancelSubscription()">
                            <i class="fas fa-times"></i> Cancel Subscription
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Plan Features
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <strong><?= $subscription['max_users'] ?></strong> Users
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <strong><?= $subscription['storage_per_user_gb'] ?>GB</strong> per user
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <strong><?= $subscription['daily_send_limit'] ?></strong> emails/day
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <strong><?= $subscription['max_domains'] ?></strong> Custom domains
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <strong><?= $subscription['max_aliases'] ?></strong> Email aliases
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Available Plans -->
    <?php if (isset($plans) && !empty($plans)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-th"></i> Available Plans
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($plans as $plan): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 <?= $plan['id'] == ($subscription['plan_id'] ?? 0) ? 'border-primary' : '' ?>">
                                <div class="card-header text-center <?= $plan['id'] == ($subscription['plan_id'] ?? 0) ? 'bg-primary text-white' : '' ?>">
                                    <h5><?= View::e($plan['plan_name']) ?></h5>
                                    <?php if ($plan['id'] == ($subscription['plan_id'] ?? 0)): ?>
                                    <small class="badge badge-light">Current Plan</small>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body text-center">
                                    <div class="h3 mb-3">
                                        $<?= number_format($plan['price_monthly'], 0) ?>
                                        <small class="text-muted">/mo</small>
                                    </div>
                                    <ul class="list-unstyled text-left small">
                                        <li class="mb-2"><i class="fas fa-check text-success"></i> <?= $plan['max_users'] ?> Users</li>
                                        <li class="mb-2"><i class="fas fa-check text-success"></i> <?= $plan['storage_per_user_gb'] ?>GB Storage</li>
                                        <li class="mb-2"><i class="fas fa-check text-success"></i> <?= $plan['daily_send_limit'] ?> Emails/day</li>
                                        <li class="mb-2"><i class="fas fa-check text-success"></i> <?= $plan['max_domains'] ?> Domains</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function cancelSubscription() {
    if (!confirm('Are you sure you want to cancel your subscription? You will lose access at the end of your billing period.')) {
        return;
    }
    
    // Implement cancellation logic here
    alert('Cancellation feature will be implemented soon.');
}
</script>

<?php View::endSection(); ?>
