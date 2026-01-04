<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<div class="container mt-5">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-4 mb-3">
                <i class="fas fa-envelope text-primary"></i> Professional Email Hosting
            </h1>
            <p class="lead text-muted">
                Get started with powerful, reliable email hosting for your business
            </p>
        </div>
    </div>

    <!-- Plans Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Choose Your Plan</h2>
            
            <?php if (isset($plans) && count($plans) > 0): ?>
            <div class="row">
                <?php foreach ($plans as $index => $plan): ?>
                <?php 
                $cardColors = ['info', 'primary', 'warning', 'success'];
                $cardColor = $cardColors[$index % count($cardColors)];
                $isPopular = $plan['plan_name'] === 'Business' || stripos($plan['plan_name'], 'business') !== false;
                ?>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card h-100 border-<?= $cardColor ?> shadow-sm <?= $isPopular ? 'border-3' : '' ?>">
                        <?php if ($isPopular): ?>
                        <div class="ribbon ribbon-top-right">
                            <span class="bg-warning text-dark">Popular</span>
                        </div>
                        <?php endif; ?>
                        <div class="card-header bg-<?= $cardColor ?> text-white text-center">
                            <h4 class="mb-0"><?= htmlspecialchars($plan['plan_name']) ?></h4>
                        </div>
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <h2 class="display-4 mb-0">
                                    <?php if ($plan['price_monthly'] == 0): ?>
                                    <span class="text-success">Free</span>
                                    <?php else: ?>
                                    $<?= number_format($plan['price_monthly'], 0) ?>
                                    <?php endif; ?>
                                </h2>
                                <p class="text-muted"><?= $plan['price_monthly'] == 0 ? 'Forever' : 'per month' ?></p>
                            </div>
                            
                            <?php if ($plan['price_yearly'] > 0): ?>
                            <div class="alert alert-success py-2 mb-3">
                                <small>
                                    <strong>Save <?= round((1 - ($plan['price_yearly'] / ($plan['price_monthly'] * 12))) * 100) ?>%</strong> with annual billing
                                </small>
                            </div>
                            <?php endif; ?>

                            <ul class="list-unstyled text-left mb-4">
                                <li class="mb-3">
                                    <i class="fas fa-users text-<?= $cardColor ?>"></i>
                                    <strong><?= $plan['max_users'] ?></strong> User<?= $plan['max_users'] > 1 ? 's' : '' ?>
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-globe text-<?= $cardColor ?>"></i>
                                    <strong><?= $plan['max_domains'] ?></strong> Custom Domain<?= $plan['max_domains'] > 1 ? 's' : '' ?>
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-hdd text-<?= $cardColor ?>"></i>
                                    <strong><?= $plan['storage_per_user_gb'] ?>GB</strong> Storage per User
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-paper-plane text-<?= $cardColor ?>"></i>
                                    <strong><?= number_format($plan['daily_send_limit']) ?></strong> Emails/Day
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-at text-<?= $cardColor ?>"></i>
                                    <strong><?= $plan['max_aliases'] ?></strong> Email Alias<?= $plan['max_aliases'] > 1 ? 'es' : '' ?>
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-paperclip text-<?= $cardColor ?>"></i>
                                    <strong><?= $plan['max_attachment_size_mb'] ?>MB</strong> Attachments
                                </li>
                            </ul>
                        </div>
                        <div class="card-footer text-center bg-white">
                            <a href="/projects/mail/subscribe?plan=<?= $plan['id'] ?>" 
                               class="btn btn-<?= $cardColor ?> btn-block btn-lg">
                                <i class="fas fa-check-circle"></i> 
                                <?= $plan['price_monthly'] == 0 ? 'Get Started Free' : 'Subscribe Now' ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-warning text-center">
                <i class="fas fa-exclamation-triangle"></i>
                <p class="mb-0">No plans are currently available. Please contact support for more information.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Features Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Why Choose Our Email Hosting?</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-shield-alt fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title">Enterprise Security</h5>
                            <p class="card-text text-muted">
                                Advanced spam filtering, virus protection, and encryption keep your emails secure.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-bolt fa-3x text-warning"></i>
                            </div>
                            <h5 class="card-title">Lightning Fast</h5>
                            <p class="card-text text-muted">
                                High-performance servers ensure quick email delivery and instant access to your inbox.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-headset fa-3x text-success"></i>
                            </div>
                            <h5 class="card-title">24/7 Support</h5>
                            <p class="card-text text-muted">
                                Our expert support team is always available to help you with any questions.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- All Features List -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-list-check"></i> All Features Included</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check text-success"></i> Professional Email Hosting</li>
                                <li class="mb-2"><i class="fas fa-check text-success"></i> Custom Domain Support</li>
                                <li class="mb-2"><i class="fas fa-check text-success"></i> Webmail Interface</li>
                                <li class="mb-2"><i class="fas fa-check text-success"></i> IMAP/SMTP Access</li>
                                <li class="mb-2"><i class="fas fa-check text-success"></i> Mobile Device Support</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check text-success"></i> Advanced Spam Protection</li>
                                <li class="mb-2"><i class="fas fa-check text-success"></i> Email Aliases</li>
                                <li class="mb-2"><i class="fas fa-check text-success"></i> Auto-responders</li>
                                <li class="mb-2"><i class="fas fa-check text-success"></i> Email Forwarding</li>
                                <li class="mb-2"><i class="fas fa-check text-success"></i> 99.9% Uptime Guarantee</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.ribbon {
    position: absolute;
    right: -5px; top: -5px;
    z-index: 1;
    overflow: hidden;
    width: 75px; height: 75px;
    text-align: right;
}
.ribbon span {
    font-size: 10px;
    font-weight: bold;
    color: #FFF;
    text-transform: uppercase;
    text-align: center;
    line-height: 20px;
    transform: rotate(45deg);
    -webkit-transform: rotate(45deg);
    width: 100px;
    display: block;
    box-shadow: 0 3px 10px -5px rgba(0, 0, 0, 1);
    position: absolute;
    top: 19px; right: -21px;
}
.ribbon span::before {
    content: "";
    position: absolute; left: 0px; top: 100%;
    z-index: -1;
    border-left: 3px solid transparent;
    border-right: 3px solid transparent;
    border-top: 3px solid #ffc107;
}
.ribbon span::after {
    content: "";
    position: absolute; right: 0px; top: 100%;
    z-index: -1;
    border-left: 3px solid transparent;
    border-right: 3px solid transparent;
    border-top: 3px solid #ffc107;
}
.border-3 {
    border-width: 3px !important;
}
</style>

<?php View::endSection(); ?>
