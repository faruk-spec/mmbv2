<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

.subscribe-page {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
    padding: 60px 0;
}

.hero-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px;
    padding: 40px;
    margin-bottom: 30px;
    box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3);
}

.hero-card h1 {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 15px;
}

.hero-card p {
    font-size: 1.1rem;
    opacity: 0.95;
}

.plan-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 30px;
    transition: transform 0.3s;
}

.plan-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

.plan-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    text-align: center;
}

.plan-price {
    font-size: 3.5rem;
    font-weight: 800;
    line-height: 1;
    margin: 20px 0;
}

.plan-price .currency {
    font-size: 2rem;
    vertical-align: super;
}

.plan-price-period {
    font-size: 1rem;
    opacity: 0.9;
}

.feature-list {
    padding: 30px;
}

.feature-item {
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    font-size: 1rem;
}

.feature-item:last-child {
    border-bottom: none;
}

.feature-item i {
    color: #48bb78;
    margin-right: 12px;
    font-size: 1.2rem;
}

.feature-item strong {
    color: #667eea;
}

.subscription-form-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    overflow: hidden;
}

.form-header {
    background: linear-gradient(135deg, #48bb78 0%, #2f855a 100%);
    color: white;
    padding: 30px;
    text-align: center;
}

.form-header h3 {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0;
}

.form-body {
    padding: 40px;
}

.form-control-modern {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 20px;
    font-size: 1rem;
    transition: all 0.3s;
}

.form-control-modern:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-subscribe-now {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 10px;
    padding: 15px 40px;
    font-size: 1.1rem;
    font-weight: 600;
    color: white;
    transition: all 0.3s;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
}

.btn-subscribe-now:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
    color: white;
}

.savings-badge {
    background: linear-gradient(135deg, #ffd700 0%, #ffaa00 100%);
    color: #000;
    padding: 15px 25px;
    border-radius: 15px;
    font-weight: 700;
    box-shadow: 0 5px 20px rgba(255, 170, 0, 0.4);
    margin-top: 20px;
}

.info-alert {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border: none;
    border-radius: 15px;
    padding: 20px;
    border-left: 4px solid #2196f3;
}

.custom-checkbox-modern .custom-control-label {
    font-size: 0.95rem;
    padding-left: 10px;
}

.breadcrumb-custom {
    background: transparent;
    padding: 0;
    margin-bottom: 20px;
}

.breadcrumb-custom .breadcrumb-item a {
    color: white;
    text-decoration: none;
}

.breadcrumb-custom .breadcrumb-item.active {
    color: rgba(255,255,255,0.8);
}
</style>

<div class="subscribe-page">
    <div class="container">
        <!-- Hero Section -->
        <div class="hero-card">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-custom">
                    <li class="breadcrumb-item"><a href="/projects/mail"><i class="fas fa-home"></i> Mail Hosting</a></li>
                    <li class="breadcrumb-item"><a href="/projects/mail">Plans</a></li>
                    <li class="breadcrumb-item active">Subscribe</li>
                </ol>
            </nav>
            <h1><i class="fas fa-rocket"></i> Subscribe to <?= htmlspecialchars($plan['plan_name']) ?></h1>
            <p>Get started with professional email hosting in minutes. No credit card required for free plan.</p>
        </div>

        <div class="row">
            <!-- Plan Details -->
            <div class="col-lg-5 mb-4">
                <div class="plan-card">
                    <div class="plan-header">
                        <h2 class="mb-3"><?= htmlspecialchars($plan['plan_name']) ?> Plan</h2>
                        <div class="plan-price">
                            <?php if ($plan['price_monthly'] == 0): ?>
                            <span style="color: #ffd700;">Free</span>
                            <?php else: ?>
                            <span class="currency">$</span><?= number_format($plan['price_monthly'], 0) ?>
                            <?php endif; ?>
                        </div>
                        <div class="plan-price-period"><?= $plan['price_monthly'] == 0 ? 'Forever' : 'per month' ?></div>
                        
                        <?php if (isset($plan['price_yearly']) && $plan['price_yearly'] > 0): ?>
                        <div class="savings-badge mt-3">
                            <i class="fas fa-star"></i> Save <?= round((1 - ($plan['price_yearly'] / ($plan['price_monthly'] * 12))) * 100) ?>% with yearly billing!
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="feature-list">
                        <h5 class="mb-4"><i class="fas fa-check-circle text-success"></i> Plan Features</h5>
                        
                        <div class="feature-item">
                            <i class="fas fa-users"></i>
                            <span><strong><?= $plan['max_users'] ?></strong> User<?= $plan['max_users'] > 1 ? 's' : '' ?></span>
                        </div>
                        
                        <div class="feature-item">
                            <i class="fas fa-globe"></i>
                            <span><strong><?= $plan['max_domains'] ?? 0 ?></strong> Domain<?= ($plan['max_domains'] ?? 0) > 1 ? 's' : '' ?></span>
                        </div>
                        
                        <div class="feature-item">
                            <i class="fas fa-hdd"></i>
                            <span><strong><?= $plan['storage_per_user_gb'] ?>GB</strong> Storage per User</span>
                        </div>
                        
                        <div class="feature-item">
                            <i class="fas fa-paper-plane"></i>
                            <span><strong><?= number_format($plan['daily_send_limit'] ?? 0) ?></strong> Emails per Day</span>
                        </div>
                        
                        <div class="feature-item">
                            <i class="fas fa-at"></i>
                            <span><strong><?= $plan['max_aliases'] ?></strong> Email Alias<?= $plan['max_aliases'] > 1 ? 'es' : '' ?></span>
                        </div>
                        
                        <div class="feature-item">
                            <i class="fas fa-paperclip"></i>
                            <span><strong><?= $plan['max_attachment_size_mb'] ?>MB</strong> Max Attachment Size</span>
                        </div>
                        
                        <?php if (!empty($features)): ?>
                        <h6 class="mt-4 mb-3"><i class="fas fa-plus-circle text-primary"></i> Additional Features</h6>
                        <?php foreach ($features as $feature): ?>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>
                                <?= htmlspecialchars($feature['feature_name']) ?>
                                <?php if ($feature['feature_value']): ?>
                                <small class="text-muted">(<?= htmlspecialchars($feature['feature_value']) ?>)</small>
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Subscription Form -->
            <div class="col-lg-7">
                <div class="subscription-form-card">
                    <div class="form-header">
                        <h3><i class="fas fa-user-check"></i> Complete Your Subscription</h3>
                        <p class="mb-0 mt-2">Just a few steps to get started</p>
                    </div>
                    
                    <div class="form-body">
                        <form method="POST" action="/projects/mail/subscribe" id="subscribeForm">
                            <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                            
                            <div class="form-group">
                                <label for="account_name" class="font-weight-bold">
                                    <i class="fas fa-building text-primary"></i> Account Name *
                                </label>
                                <input type="text" 
                                       class="form-control form-control-modern" 
                                       id="account_name" 
                                       name="account_name" 
                                       placeholder="e.g., Acme Corporation" 
                                       required>
                                <small class="form-text text-muted">
                                    This will be used to identify your mail hosting account
                                </small>
                            </div>
                            
                            <div class="form-group">
                                <label for="billing_cycle" class="font-weight-bold">
                                    <i class="fas fa-calendar-alt text-success"></i> Billing Cycle *
                                </label>
                                <select class="form-control form-control-modern" id="billing_cycle" name="billing_cycle" required>
                                    <option value="monthly">
                                        Monthly - $<?= number_format($plan['price_monthly'], 2) ?>/month
                                    </option>
                                    <?php if (isset($plan['price_yearly']) && $plan['price_yearly'] > 0): ?>
                                    <option value="yearly">
                                        Yearly - $<?= number_format($plan['price_yearly'], 2) ?>/year 
                                        (Save <?= round((1 - ($plan['price_yearly'] / ($plan['price_monthly'] * 12))) * 100) ?>%!)
                                    </option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="info-alert">
                                <strong><i class="fas fa-info-circle"></i> Important Information</strong>
                                <ul class="mb-0 mt-2">
                                    <?php if ($plan['price_monthly'] == 0): ?>
                                    <li>This is a free plan with no payment required</li>
                                    <li>You can upgrade to a paid plan anytime</li>
                                    <?php else: ?>
                                    <li>Payment will be processed after subscription creation</li>
                                    <li>You can cancel or change your plan anytime</li>
                                    <li>We accept major credit cards and PayPal</li>
                                    <?php endif; ?>
                                    <li>Your data is encrypted and secure</li>
                                </ul>
                            </div>
                            
                            <div class="form-group mt-4">
                                <div class="custom-control custom-checkbox custom-checkbox-modern">
                                    <input type="checkbox" class="custom-control-input" id="terms" required>
                                    <label class="custom-control-label" for="terms">
                                        I agree to the <a href="/terms" target="_blank">Terms of Service</a> and 
                                        <a href="/privacy" target="_blank">Privacy Policy</a>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-subscribe-now btn-block">
                                    <i class="fas fa-check-circle"></i> Complete Subscription
                                </button>
                                <a href="/projects/mail" class="btn btn-outline-secondary btn-block mt-3" style="border-radius: 10px; padding: 12px;">
                                    <i class="fas fa-arrow-left"></i> Back to Plans
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Trust Badges -->
                <div class="text-center mt-4">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt text-success"></i> Secure Payment &nbsp;&nbsp;
                        <i class="fas fa-lock text-success"></i> SSL Encrypted &nbsp;&nbsp;
                        <i class="fas fa-headset text-success"></i> 24/7 Support
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php View::endSection(); ?>
