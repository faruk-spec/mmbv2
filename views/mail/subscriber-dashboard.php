<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <!-- Current Plan Card -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h2 class="mb-0">
                        <i class="fas fa-check-circle"></i> Your Current Plan: <?= htmlspecialchars($currentPlan['plan_name']) ?>
                    </h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center border-right">
                            <h3 class="text-success">$<?= number_format($currentPlan['price_monthly'], 2) ?></h3>
                            <p class="text-muted">per month</p>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-4">
                                    <p><i class="fas fa-users text-primary"></i> <strong><?= $currentPlan['max_users'] ?></strong> Users</p>
                                    <p><i class="fas fa-hdd text-info"></i> <strong><?= $currentPlan['storage_per_user_gb'] ?>GB</strong> Storage/User</p>
                                </div>
                                <div class="col-md-4">
                                    <p><i class="fas fa-paper-plane text-success"></i> <strong><?= number_format($currentPlan['daily_send_limit'] ?? 0) ?></strong> Emails/Day</p>
                                    <p><i class="fas fa-globe text-cyan"></i> <strong><?= $currentPlan['max_domains'] ?? 0 ?></strong> Domains</p>
                                </div>
                                <div class="col-md-4">
                                    <a href="/projects/mail/subscriber/dashboard" class="btn btn-primary btn-block">
                                        <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                                    </a>
                                    <a href="/projects/mail/webmail" class="btn btn-info btn-block">
                                        <i class="fas fa-envelope"></i> Access Webmail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upgrade Options -->
            <h3 class="mb-4">
                <i class="fas fa-arrow-up"></i> Upgrade Your Plan
            </h3>
            <p class="text-muted mb-4">Need more features? Check out our other plans and upgrade anytime.</p>

            <div class="row">
                <?php foreach ($plans as $plan): ?>
                <?php 
                $isCurrent = ($plan['id'] == $currentPlan['plan_id']);
                $isUpgrade = ($plan['price_monthly'] > $currentPlan['price_monthly']);
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 <?= $isCurrent ? 'border-success' : ($isUpgrade ? 'border-primary' : 'border-secondary') ?>">
                        <div class="card-header <?= $isCurrent ? 'bg-success text-white' : ($isUpgrade ? 'bg-primary text-white' : 'bg-light') ?> text-center">
                            <h4><?= htmlspecialchars($plan['plan_name']) ?></h4>
                            <?php if ($isCurrent): ?>
                            <span class="badge badge-light">Current Plan</span>
                            <?php elseif ($isUpgrade): ?>
                            <span class="badge badge-warning">Upgrade</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body text-center">
                            <h2 class="<?= $isUpgrade ? 'text-primary' : 'text-muted' ?>">
                                $<?= number_format($plan['price_monthly'], 2) ?>
                                <small class="text-muted">/month</small>
                            </h2>
                            <ul class="list-unstyled mt-3 text-left">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success"></i> <?= $plan['max_users'] ?> Users
                                    <?php if ($plan['max_users'] > $currentPlan['max_users']): ?>
                                    <span class="badge badge-success">+<?= $plan['max_users'] - $currentPlan['max_users'] ?></span>
                                    <?php endif; ?>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success"></i> <?= $plan['max_domains'] ?? 0 ?> Domains
                                    <?php if (isset($currentPlan['max_domains']) && ($plan['max_domains'] ?? 0) > $currentPlan['max_domains']): ?>
                                    <span class="badge badge-success">+<?= ($plan['max_domains'] ?? 0) - $currentPlan['max_domains'] ?></span>
                                    <?php endif; ?>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success"></i> <?= $plan['storage_per_user_gb'] ?>GB Storage/User
                                    <?php if ($plan['storage_per_user_gb'] > $currentPlan['storage_per_user_gb']): ?>
                                    <span class="badge badge-success">+<?= $plan['storage_per_user_gb'] - $currentPlan['storage_per_user_gb'] ?>GB</span>
                                    <?php endif; ?>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success"></i> <?= number_format($plan['daily_send_limit']) ?> Emails/Day
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success"></i> <?= $plan['max_aliases'] ?> Aliases
                                </li>
                            </ul>
                        </div>
                        <div class="card-footer text-center">
                            <?php if ($isCurrent): ?>
                            <button class="btn btn-success btn-block" disabled>
                                <i class="fas fa-check"></i> Current Plan
                            </button>
                            <?php elseif ($isUpgrade): ?>
                            <a href="/projects/mail/subscriber/upgrade?plan=<?= $plan['id'] ?>" class="btn btn-primary btn-block">
                                <i class="fas fa-arrow-up"></i> Upgrade Now
                            </a>
                            <?php else: ?>
                            <button class="btn btn-secondary btn-block" disabled>
                                Lower Tier
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php View::endSection(); ?>
