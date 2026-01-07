<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-arrow-up mr-2"></i>
                    Upgrade Your Plan
                </h2>
                <a href="/projects/mail/subscriber/subscription" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- Current Plan -->
    <?php if (isset($currentSubscription) && $currentSubscription): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <h5 class="alert-heading">
                    <i class="fas fa-info-circle"></i> Current Plan
                </h5>
                <p class="mb-0">
                    You are currently on the <strong><?= View::e($currentSubscription['plan_name']) ?></strong> plan.
                    Upgrade to unlock more features and higher limits!
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Available Upgrade Plans -->
    <?php if (isset($plans) && !empty($plans)): ?>
    <div class="row">
        <?php foreach ($plans as $plan): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-gradient-primary text-white text-center">
                    <h4 class="mb-0"><?= View::e($plan['plan_name']) ?></h4>
                </div>
                <div class="card-body">
                    <!-- Price -->
                    <div class="text-center mb-4">
                        <h2 class="display-4 mb-0">
                            $<?= number_format($plan['price_monthly'], 0) ?>
                        </h2>
                        <p class="text-muted">per month</p>
                        <?php if ($plan['price_yearly'] > 0): ?>
                        <p class="text-success small">
                            <strong>Save <?= round((1 - ($plan['price_yearly'] / 12) / $plan['price_monthly']) * 100) ?>%</strong> 
                            with annual billing
                        </p>
                        <?php endif; ?>
                    </div>

                    <!-- Features -->
                    <ul class="list-unstyled mb-4">
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <strong><?= $plan['max_users'] ?></strong> Mailbox Users
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <strong><?= $plan['storage_per_user_gb'] ?>GB</strong> Storage per user
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <strong><?= number_format($plan['daily_send_limit']) ?></strong> Emails per day
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <strong><?= $plan['max_domains'] ?></strong> Custom Domains
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <strong><?= $plan['max_aliases'] ?></strong> Email Aliases
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            Max <strong><?= $plan['max_attachment_size_mb'] ?>MB</strong> Attachments
                        </li>
                    </ul>

                    <?php if (!empty($plan['description'])): ?>
                    <p class="text-muted small">
                        <?= View::e($plan['description']) ?>
                    </p>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-center bg-transparent border-0 pb-4">
                    <button class="btn btn-success btn-block btn-lg" 
                            onclick="upgradeToPlan(<?= $plan['id'] ?>, '<?= View::e($plan['plan_name']) ?>')">
                        <i class="fas fa-arrow-up"></i> Upgrade to <?= View::e($plan['plan_name']) ?>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning">
                <h5 class="alert-heading">
                    <i class="fas fa-exclamation-triangle"></i> No Upgrades Available
                </h5>
                <p class="mb-0">
                    You're already on the highest available plan or there are no other plans to upgrade to at this time.
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="upgradeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-arrow-up"></i> Confirm Plan Upgrade
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to upgrade to the <strong id="planNameDisplay"></strong> plan?</p>
                <p class="text-muted small">
                    Your billing will be prorated for the remainder of your current billing period.
                </p>
                <input type="hidden" id="selectedPlanId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmUpgrade()">
                    <i class="fas fa-check"></i> Confirm Upgrade
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function upgradeToPlan(planId, planName) {
    document.getElementById('selectedPlanId').value = planId;
    document.getElementById('planNameDisplay').textContent = planName;
    $('#upgradeModal').modal('show');
}

function confirmUpgrade() {
    const planId = document.getElementById('selectedPlanId').value;
    
    // Show loading state
    const btn = event.target;
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    fetch('/projects/mail/subscriber/upgrade', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `plan_id=${planId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Plan upgraded successfully!');
            if (data.data && data.data.redirect) {
                window.location.href = data.data.redirect;
            } else {
                window.location.href = '/projects/mail/subscriber/dashboard';
            }
        } else {
            alert('Error: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while upgrading your plan. Please try again.');
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}
</script>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}
</style>

<?php View::endSection(); ?>
