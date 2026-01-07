<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<!-- Subscribers Management -->
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>
                    <i class="fas fa-users mr-2"></i>
                    Manage Subscribers
                </h2>
                <div>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createSubscriptionModal">
                        <i class="fas fa-plus"></i> Create Subscription
                    </button>
                    <a href="/admin/projects/mail" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Overview
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscribers List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Subscribers</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search subscribers...">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Account Name</th>
                                    <th>Owner</th>
                                    <th>Plan</th>
                                    <th>Status</th>
                                    <th>Domains</th>
                                    <th>Mailboxes</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($subscribers)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="fas fa-users fa-3x mb-3"></i>
                                        <p class="h5">No subscribers found</p>
                                        <p>Subscribers will appear here once they register</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($subscribers as $subscriber): ?>
                                    <tr>
                                        <td><?= $subscriber['id'] ?></td>
                                        <td>
                                            <strong><?= View::e($subscriber['account_name']) ?></strong>
                                            <?php if ($subscriber['company_name']): ?>
                                            <br><small class="text-muted"><?= View::e($subscriber['company_name']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= View::e($subscriber['username']) ?>
                                            <br><small class="text-muted"><?= View::e($subscriber['email']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= getPlanBadgeColor($subscriber['plan_name']) ?> px-3 py-1">
                                                <?= View::e($subscriber['plan_name'] ?? 'No Plan') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $statusBadge = match($subscriber['status']) {
                                                'active' => 'success',
                                                'suspended' => 'danger',
                                                'cancelled' => 'secondary',
                                                'grace_period' => 'warning',
                                                default => 'info'
                                            };
                                            ?>
                                            <span class="badge badge-<?= $statusBadge ?>">
                                                <?= ucfirst($subscriber['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info">
                                                <?= $subscriber['domains_count'] ?? 0 ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-primary">
                                                <?= $subscriber['mailboxes_count'] ?? 0 ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?= date('M d, Y', strtotime($subscriber['created_at'])) ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/admin/projects/mail/subscribers/<?= $subscriber['id'] ?>" 
                                                   class="btn btn-info" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($subscriber['status'] === 'active'): ?>
                                                <button class="btn btn-warning" 
                                                        onclick="suspendSubscriber(<?= $subscriber['id'] ?>)" 
                                                        title="Suspend">
                                                    <i class="fas fa-pause"></i>
                                                </button>
                                                <?php else: ?>
                                                <button class="btn btn-success" 
                                                        onclick="activateSubscriber(<?= $subscriber['id'] ?>)" 
                                                        title="Activate">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                                <?php endif; ?>
                                                <button class="btn btn-danger" 
                                                        onclick="deleteSubscription(<?= $subscriber['id'] ?>)" 
                                                        title="Delete Subscription">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if (isset($totalPages) && $totalPages > 1): ?>
                <div class="card-footer clearfix">
                    <ul class="pagination pagination-sm m-0 float-right">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                    <div class="float-left mt-2">
                        <small class="text-muted">
                            Page <?= $currentPage ?> of <?= $totalPages ?>
                        </small>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Suspend Modal -->
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Suspend Subscriber
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="suspendForm">
                    <input type="hidden" id="suspend_subscriber_id" name="subscriber_id">
                    <div class="form-group">
                        <label for="suspend_reason">Reason for Suspension *</label>
                        <textarea class="form-control" id="suspend_reason" name="reason" 
                                  rows="3" required 
                                  placeholder="Provide a reason for suspending this subscriber..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="confirmSuspend()">
                    <i class="fas fa-pause"></i> Suspend Subscriber
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput')?.addEventListener('keyup', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Suspend subscriber
function suspendSubscriber(id) {
    document.getElementById('suspend_subscriber_id').value = id;
    $('#suspendModal').modal('show');
}

function confirmSuspend() {
    const subscriberId = document.getElementById('suspend_subscriber_id').value;
    const reason = document.getElementById('suspend_reason').value;
    
    if (!reason.trim()) {
        alert('Please provide a reason for suspension');
        return;
    }
    
    fetch('/admin/projects/mail/subscribers/suspend', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `subscriber_id=${subscriberId}&reason=${encodeURIComponent(reason)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#suspendModal').modal('hide');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while suspending the subscriber');
    });
}

// Activate subscriber
function activateSubscriber(id) {
    if (!confirm('Are you sure you want to activate this subscriber?')) {
        return;
    }
    
    fetch('/admin/projects/mail/subscribers/activate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `subscriber_id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while activating the subscriber');
    });
}

// Delete subscription
function deleteSubscription(id) {
    if (!confirm('Are you sure you want to delete this subscription? This action cannot be undone and will remove all associated data including domains, mailboxes, and emails.')) {
        return;
    }
    
    const confirmText = prompt('Type "DELETE" to confirm deletion:');
    if (confirmText !== 'DELETE') {
        alert('Deletion cancelled. You must type DELETE to confirm.');
        return;
    }
    
    fetch('/admin/projects/mail/subscribers/delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `subscriber_id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Subscription deleted successfully');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the subscription');
    });
}
</script>

<!-- Create Subscription Modal -->
<div class="modal fade" id="createSubscriptionModal" tabindex="-1" role="dialog" aria-labelledby="createSubscriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="/admin/projects/mail/subscribers/create">
                <div class="modal-header">
                    <h5 class="modal-title" id="createSubscriptionModalLabel">
                        <i class="fas fa-plus"></i> Create New Subscription
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="mmb_user_id">User ID *</label>
                        <input type="number" class="form-control" id="mmb_user_id" name="mmb_user_id" required>
                        <small class="form-text text-muted">Enter the main MMB user ID</small>
                    </div>
                    <div class="form-group">
                        <label for="account_name">Account Name *</label>
                        <input type="text" class="form-control" id="account_name" name="account_name" required>
                    </div>
                    <div class="form-group">
                        <label for="plan_id">Plan *</label>
                        <select class="form-control" id="plan_id" name="plan_id" required>
                            <option value="">Select a plan...</option>
                            <?php
                            $db = Core\Database::getInstance();
                            $plans = $db->fetchAll("SELECT id, plan_name, price_monthly FROM mail_subscription_plans WHERE is_active = 1 ORDER BY sort_order");
                            foreach ($plans as $plan):
                            ?>
                                <option value="<?= $plan['id'] ?>">
                                    <?= htmlspecialchars($plan['plan_name']) ?> - $<?= number_format($plan['price_monthly'], 2) ?>/month
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="billing_cycle">Billing Cycle *</label>
                        <select class="form-control" id="billing_cycle" name="billing_cycle" required>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Create Subscription
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php View::endSection(); ?>

<?php
function getPlanBadgeColor($planName) {
    $colors = [
        'Free' => 'secondary',
        'Starter' => 'info',
        'Business' => 'warning',
        'Developer' => 'success'
    ];
    return $colors[$planName] ?? 'primary';
}
?>
