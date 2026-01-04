<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <!-- Plan Details Card -->
            <div class="card shadow-lg mb-4">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">
                        <i class="fas fa-shopping-cart"></i> Subscribe to <?= htmlspecialchars($plan['plan_name']) ?>
                    </h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="text-primary">Plan Details</h3>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-users text-primary"></i> <strong><?= $plan['max_users'] ?></strong> User<?= $plan['max_users'] > 1 ? 's' : '' ?></li>
                                <li class="mb-2"><i class="fas fa-globe text-primary"></i> <strong><?= $plan['max_domains'] ?></strong> Domain<?= $plan['max_domains'] > 1 ? 's' : '' ?></li>
                                <li class="mb-2"><i class="fas fa-hdd text-primary"></i> <strong><?= $plan['storage_per_user_gb'] ?>GB</strong> Storage per User</li>
                                <li class="mb-2"><i class="fas fa-paper-plane text-primary"></i> <strong><?= number_format($plan['daily_send_limit']) ?></strong> Emails/Day</li>
                                <li class="mb-2"><i class="fas fa-at text-primary"></i> <strong><?= $plan['max_aliases'] ?></strong> Email Alias<?= $plan['max_aliases'] > 1 ? 'es' : '' ?></li>
                                <li class="mb-2"><i class="fas fa-paperclip text-primary"></i> <strong><?= $plan['max_attachment_size_mb'] ?>MB</strong> Max Attachment</li>
                            </ul>
                            
                            <?php if (!empty($features)): ?>
                            <h4 class="mt-4">Additional Features</h4>
                            <ul class="list-unstyled">
                                <?php foreach ($features as $feature): ?>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success"></i> <?= htmlspecialchars($feature['feature_name']) ?>
                                    <?php if ($feature['feature_value']): ?>
                                    <small class="text-muted">(<?= htmlspecialchars($feature['feature_value']) ?>)</small>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h3 class="text-primary">Pricing</h3>
                            <div class="pricing-box p-4 bg-light rounded">
                                <div class="text-center mb-3">
                                    <h2 class="display-4 text-primary"><?= $plan['price_monthly'] == 0 ? 'Free' : '$' . number_format($plan['price_monthly'], 2) ?></h2>
                                    <p class="text-muted"><?= $plan['price_monthly'] == 0 ? 'Forever' : 'per month' ?></p>
                                </div>
                                
                                <?php if ($plan['price_yearly'] > 0): ?>
                                <div class="alert alert-success text-center">
                                    <strong>Save <?= round((1 - ($plan['price_yearly'] / ($plan['price_monthly'] * 12))) * 100) ?>%</strong><br>
                                    $<?= number_format($plan['price_yearly'], 2) ?>/year
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription Form -->
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0"><i class="fas fa-user-check"></i> Complete Your Subscription</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="/projects/mail/subscribe">
                        <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                        
                        <div class="form-group">
                            <label for="account_name">Account Name *</label>
                            <input type="text" class="form-control form-control-lg" id="account_name" name="account_name" 
                                   placeholder="Your Company Name" required>
                            <small class="form-text text-muted">This will be used to identify your mail hosting account</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="billing_cycle">Billing Cycle *</label>
                            <select class="form-control form-control-lg" id="billing_cycle" name="billing_cycle" required>
                                <option value="monthly">Monthly - $<?= number_format($plan['price_monthly'], 2) ?>/month</option>
                                <?php if ($plan['price_yearly'] > 0): ?>
                                <option value="yearly">Yearly - $<?= number_format($plan['price_yearly'], 2) ?>/year (Save <?= round((1 - ($plan['price_yearly'] / ($plan['price_monthly'] * 12))) * 100) ?>%)</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> <strong>Note:</strong> 
                            <?php if ($plan['price_monthly'] == 0): ?>
                            This is a free plan. You can upgrade anytime.
                            <?php else: ?>
                            Payment will be processed after subscription creation. You can cancel anytime.
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="terms" required>
                                <label class="custom-control-label" for="terms">
                                    I agree to the <a href="/terms" target="_blank">Terms of Service</a> and 
                                    <a href="/privacy" target="_blank">Privacy Policy</a>
                                </label>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success btn-lg btn-block">
                                <i class="fas fa-check-circle"></i> Complete Subscription
                            </button>
                            <a href="/projects/mail" class="btn btn-secondary btn-lg btn-block mt-2">
                                <i class="fas fa-arrow-left"></i> Back to Plans
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.pricing-box {
    border: 2px solid #28a745;
}
</style>

<?php View::endSection(); ?>
