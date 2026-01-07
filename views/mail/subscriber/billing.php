<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-credit-card mr-2"></i>
                    Billing History
                </h2>
                <a href="/projects/mail/subscriber/dashboard" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Current Subscription Info -->
    <?php if (isset($subscription) && $subscription): ?>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-box"></i> Current Subscription</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Plan:</strong>
                        <span class="badge badge-primary ml-2"><?= View::e($subscription['plan_name']) ?></span>
                    </div>
                    <div class="mb-3">
                        <strong>Billing Cycle:</strong> <?= ucfirst($subscription['billing_cycle'] ?? 'monthly') ?>
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong>
                        <span class="badge badge-<?= $subscription['status'] == 'active' ? 'success' : 'warning' ?>">
                            <?= ucfirst($subscription['status']) ?>
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Current Period:</strong><br>
                        <?= isset($subscription['current_period_start']) ? date('M d, Y', strtotime($subscription['current_period_start'])) : 'N/A' ?> -
                        <?= isset($subscription['current_period_end']) ? date('M d, Y', strtotime($subscription['current_period_end'])) : 'N/A' ?>
                    </div>
                    <div class="mt-4">
                        <a href="/projects/mail/subscriber/upgrade" class="btn btn-success btn-block">
                            <i class="fas fa-arrow-up"></i> Upgrade Plan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Billing History Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Transaction History</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($billingHistory)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No billing history found.</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Transaction ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($billingHistory as $item): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($item['created_at'])) ?></td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?= ucfirst($item['transaction_type']) ?>
                                        </span>
                                    </td>
                                    <td><?= View::e($item['description'] ?? 'N/A') ?></td>
                                    <td>
                                        $<?= number_format($item['amount'], 2) ?>
                                        <?= $item['currency'] ?? 'USD' ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = match($item['payment_status'] ?? 'completed') {
                                            'completed' => 'success',
                                            'pending' => 'warning',
                                            'failed' => 'danger',
                                            'refunded' => 'secondary',
                                            default => 'info'
                                        };
                                        ?>
                                        <span class="badge badge-<?= $statusClass ?>">
                                            <?= ucfirst($item['payment_status'] ?? 'completed') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= $item['transaction_id'] ? View::e($item['transaction_id']) : 'N/A' ?>
                                        </small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php View::endSection(); ?>
