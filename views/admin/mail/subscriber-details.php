<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<!-- Subscriber Details -->
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>
                    <i class="fas fa-user-circle mr-2"></i>
                    <?= View::e($subscriber['account_name']) ?>
                </h2>
                <div>
                    <a href="/admin/projects/mail/subscribers" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Subscribers
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscriber Information & Actions -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Subscriber Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Account Name:</strong><br>
                            <?= View::e($subscriber['account_name']) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Company:</strong><br>
                            <?= View::e($subscriber['company_name'] ?? 'N/A') ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Owner:</strong><br>
                            <?= View::e($subscriber['username']) ?> (<?= View::e($subscriber['email']) ?>)
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Billing Email:</strong><br>
                            <?= View::e($subscriber['billing_email'] ?? $subscriber['email']) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Current Plan:</strong><br>
                            <span class="badge badge-<?= getPlanBadgeColor($subscriber['plan_name']) ?> px-3 py-2">
                                <?= View::e($subscriber['plan_name']) ?>
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Status:</strong><br>
                            <span class="badge badge-<?= getStatusBadgeColor($subscriber['status']) ?> px-3 py-2">
                                <?= ucfirst($subscriber['status']) ?>
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Member Since:</strong><br>
                            <?= date('F d, Y', strtotime($subscriber['created_at'])) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Subscription Period:</strong><br>
                            <?= date('M d, Y', strtotime($subscriber['current_period_start'])) ?> - 
                            <?= date('M d, Y', strtotime($subscriber['current_period_end'])) ?>
                        </div>
                    </div>

                    <?php if ($subscriber['status'] === 'suspended' && $subscriber['suspension_reason']): ?>
                    <div class="alert alert-warning mt-3">
                        <strong><i class="fas fa-exclamation-triangle"></i> Suspension Reason:</strong><br>
                        <?= View::e($subscriber['suspension_reason']) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-2"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <?php if ($subscriber['status'] === 'active'): ?>
                    <button class="btn btn-warning btn-block mb-2" onclick="suspendSubscriber(<?= $subscriber['id'] ?>)">
                        <i class="fas fa-pause"></i> Suspend Account
                    </button>
                    <?php else: ?>
                    <button class="btn btn-success btn-block mb-2" onclick="activateSubscriber(<?= $subscriber['id'] ?>)">
                        <i class="fas fa-play"></i> Activate Account
                    </button>
                    <?php endif; ?>

                    <button class="btn btn-info btn-block mb-2" onclick="showChangePlanModal(<?= $subscriber['id'] ?>)">
                        <i class="fas fa-exchange-alt"></i> Change Plan
                    </button>

                    <button class="btn btn-secondary btn-block mb-2" onclick="showFeatureOverrideModal(<?= $subscriber['id'] ?>)">
                        <i class="fas fa-toggle-on"></i> Override Features
                    </button>

                    <hr>

                    <a href="/admin/projects/mail/subscribers/<?= $subscriber['id'] ?>/billing" class="btn btn-outline-primary btn-block mb-2">
                        <i class="fas fa-credit-card"></i> View Billing
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Usage Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="info-box bg-gradient-info">
                <span class="info-box-icon"><i class="fas fa-envelope"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Emails Sent</span>
                    <span class="info-box-number"><?= number_format($usageStats['total_sent'] ?? 0) ?></span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="info-box bg-gradient-success">
                <span class="info-box-icon"><i class="fas fa-inbox"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Emails Received</span>
                    <span class="info-box-number"><?= number_format($usageStats['total_received'] ?? 0) ?></span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="info-box bg-gradient-warning">
                <span class="info-box-icon"><i class="fas fa-hdd"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Storage Used</span>
                    <span class="info-box-number"><?= formatBytes($usageStats['total_storage'] ?? 0) ?></span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="info-box bg-gradient-primary">
                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Revenue</span>
                    <span class="info-box-number">$<?= number_format(array_sum(array_column($payments, 'amount')), 2) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs: Domains, Mailboxes, Payments -->
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline card-tabs">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="pill" href="#domains" role="tab">
                                <i class="fas fa-globe"></i> Domains (<?= count($domains) ?>)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#mailboxes" role="tab">
                                <i class="fas fa-envelope"></i> Mailboxes (<?= count($mailboxes) ?>)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#payments" role="tab">
                                <i class="fas fa-credit-card"></i> Payments (<?= count($payments) ?>)
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Domains Tab -->
                        <div class="tab-pane fade show active" id="domains" role="tabpanel">
                            <?php if (empty($domains)): ?>
                            <p class="text-center text-muted py-4">No domains added yet</p>
                            <?php else: ?>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Domain Name</th>
                                        <th>Verification Status</th>
                                        <th>Active</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($domains as $domain): ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-globe text-primary"></i>
                                            <strong><?= View::e($domain['domain_name']) ?></strong>
                                        </td>
                                        <td>
                                            <?php if ($domain['is_verified']): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> Verified
                                            </span>
                                            <?php else: ?>
                                            <span class="badge badge-warning">
                                                <i class="fas fa-clock"></i> Pending
                                            </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($domain['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($domain['created_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>

                        <!-- Mailboxes Tab -->
                        <div class="tab-pane fade" id="mailboxes" role="tabpanel">
                            <?php if (empty($mailboxes)): ?>
                            <p class="text-center text-muted py-4">No mailboxes created yet</p>
                            <?php else: ?>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Email</th>
                                        <th>Domain</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mailboxes as $mailbox): ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-envelope text-info"></i>
                                            <strong><?= View::e($mailbox['email']) ?></strong>
                                        </td>
                                        <td><?= View::e($mailbox['domain_name']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= $mailbox['role_type'] === 'domain_admin' ? 'warning' : 'info' ?>">
                                                <?= ucwords(str_replace('_', ' ', $mailbox['role_type'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($mailbox['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                            <span class="badge badge-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($mailbox['created_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>

                        <!-- Payments Tab -->
                        <div class="tab-pane fade" id="payments" role="tabpanel">
                            <?php if (empty($payments)): ?>
                            <p class="text-center text-muted py-4">No payment history</p>
                            <?php else: ?>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?= date('M d, Y H:i', strtotime($payment['created_at'])) ?></td>
                                        <td><strong>$<?= number_format($payment['amount'], 2) ?></strong></td>
                                        <td>
                                            <span class="badge badge-<?= getPaymentStatusColor($payment['status']) ?>">
                                                <?= ucfirst($payment['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= View::e($payment['description'] ?? 'Subscription payment') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Plan Modal -->
<div class="modal fade" id="changePlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt"></i> Change Subscription Plan
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="changePlanForm">
                    <input type="hidden" id="change_subscriber_id" name="subscriber_id">
                    <div class="form-group">
                        <label for="new_plan_id">Select New Plan *</label>
                        <select class="form-control" id="new_plan_id" name="plan_id" required>
                            <option value="">Choose a plan...</option>
                            <?php if (isset($plans) && !empty($plans)): ?>
                                <?php foreach ($plans as $plan): ?>
                                <option value="<?= $plan['id'] ?>" 
                                        <?= $plan['id'] == $subscriber['plan_id'] ? 'selected disabled' : '' ?>>
                                    <?= View::e($plan['plan_name']) ?> 
                                    (<?= $plan['billing_cycle'] == 'monthly' ? '$' . $plan['price_monthly'] . '/mo' : '$' . $plan['price_yearly'] . '/yr' ?>)
                                </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="override_reason">Reason for Change</label>
                        <textarea class="form-control" id="override_reason" name="reason" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info" onclick="confirmChangePlan()">
                    <i class="fas fa-check"></i> Change Plan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function suspendSubscriber(id) {
    if (!confirm('Are you sure you want to suspend this subscriber?')) return;
    
    const reason = prompt('Please provide a reason for suspension:');
    if (!reason) return;
    
    fetch('/admin/projects/mail/subscribers/suspend', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `subscriber_id=${id}&reason=${encodeURIComponent(reason)}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) location.reload();
        else alert('Error: ' + d.message);
    });
}

function activateSubscriber(id) {
    if (!confirm('Are you sure you want to activate this subscriber?')) return;
    
    fetch('/admin/projects/mail/subscribers/activate', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `subscriber_id=${id}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) location.reload();
        else alert('Error: ' + d.message);
    });
}

function showChangePlanModal(id) {
    document.getElementById('change_subscriber_id').value = id;
    $('#changePlanModal').modal('show');
}

function confirmChangePlan() {
    const subscriberId = document.getElementById('change_subscriber_id').value;
    const planId = document.getElementById('new_plan_id').value;
    const reason = document.getElementById('override_reason').value;
    
    if (!planId) {
        alert('Please select a plan');
        return;
    }
    
    fetch('/admin/projects/mail/subscribers/override-plan', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `subscriber_id=${subscriberId}&plan_id=${planId}&reason=${encodeURIComponent(reason)}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            $('#changePlanModal').modal('hide');
            location.reload();
        } else {
            alert('Error: ' + d.message);
        }
    });
}
</script>

<?php View::endSection(); ?>

<?php
function getPlanBadgeColor($planName) {
    $colors = ['Free' => 'secondary', 'Starter' => 'info', 'Business' => 'warning', 'Developer' => 'success'];
    return $colors[$planName] ?? 'primary';
}

function getStatusBadgeColor($status) {
    $colors = ['active' => 'success', 'suspended' => 'danger', 'cancelled' => 'secondary', 'grace_period' => 'warning'];
    return $colors[$status] ?? 'info';
}

function getPaymentStatusColor($status) {
    $colors = ['completed' => 'success', 'pending' => 'warning', 'failed' => 'danger', 'refunded' => 'info'];
    return $colors[$status] ?? 'secondary';
}

function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>
