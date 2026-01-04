<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">
                        <i class="fas fa-envelope"></i> Mail Hosting - Getting Started
                    </h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h4><i class="fas fa-info-circle"></i> Welcome to Mail Hosting</h4>
                        <p>You don't have an active mail subscription yet. Choose a plan below to get started with professional email hosting.</p>
                    </div>

                    <h3 class="mt-4">Choose Your Plan</h3>
                    
                    <?php if (isset($plans) && count($plans) > 0): ?>
                    <div class="row mt-4">
                        <?php foreach ($plans as $plan): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border-primary">
                                <div class="card-header bg-primary text-white text-center">
                                    <h4><?= htmlspecialchars($plan['plan_name']) ?></h4>
                                </div>
                                <div class="card-body text-center">
                                    <h2 class="text-primary">
                                        $<?= number_format($plan['monthly_price'], 2) ?>
                                        <small class="text-muted">/month</small>
                                    </h2>
                                    <ul class="list-unstyled mt-3">
                                        <li><i class="fas fa-check text-success"></i> <?= $plan['max_users'] ?> Users</li>
                                        <li><i class="fas fa-check text-success"></i> <?= $plan['max_domains'] ?> Domains</li>
                                        <li><i class="fas fa-check text-success"></i> <?= $plan['storage_per_user_gb'] ?>GB Storage/User</li>
                                        <li><i class="fas fa-check text-success"></i> <?= $plan['daily_send_limit'] ?> Emails/Day</li>
                                    </ul>
                                </div>
                                <div class="card-footer text-center">
                                    <a href="/projects/mail/subscribe?plan=<?= $plan['id'] ?>" class="btn btn-primary btn-block">
                                        <i class="fas fa-shopping-cart"></i> Subscribe Now
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <p>No plans are currently available. Please contact support for more information.</p>
                    </div>
                    <?php endif; ?>

                    <div class="mt-5">
                        <h3>Features</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Professional Email Hosting</li>
                                    <li><i class="fas fa-check text-success"></i> Custom Domain Support</li>
                                    <li><i class="fas fa-check text-success"></i> Webmail Interface</li>
                                    <li><i class="fas fa-check text-success"></i> IMAP/SMTP Access</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Spam Protection</li>
                                    <li><i class="fas fa-check text-success"></i> Email Aliases</li>
                                    <li><i class="fas fa-check text-success"></i> Auto-responders</li>
                                    <li><i class="fas fa-check text-success"></i> 24/7 Support</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php View::endSection(); ?>
