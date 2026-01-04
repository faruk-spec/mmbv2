<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<!-- Subscription Plans Management -->
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>
                    <i class="fas fa-layer-group mr-2"></i>
                    Subscription Plans
                </h2>
                <div>
                    <a href="/admin/projects/mail" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Overview
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Plans List -->
    <div class="row">
        <?php foreach ($plans as $plan): ?>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-outline card-<?= getPlanCardColor($plan['plan_name']) ?> h-100">
                <div class="card-header text-center">
                    <h3 class="card-title">
                        <strong><?= View::e($plan['plan_name']) ?></strong>
                    </h3>
                    <?php if ($plan['plan_type'] === 'free'): ?>
                    <span class="badge badge-secondary">Free Plan</span>
                    <?php else: ?>
                    <span class="badge badge-success">Paid Plan</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <!-- Pricing -->
                    <div class="text-center mb-4">
                        <h2 class="display-4">
                            $<?= number_format($plan['price_monthly'], 2) ?>
                        </h2>
                        <p class="text-muted">per month</p>
                        <?php if ($plan['price_yearly'] > 0): ?>
                        <p class="small text-success">
                            <i class="fas fa-check"></i> 
                            $<?= number_format($plan['price_yearly'], 2) ?>/year
                            (Save <?= round((1 - ($plan['price_yearly'] / ($plan['price_monthly'] * 12))) * 100) ?>%)
                        </p>
                        <?php endif; ?>
                    </div>

                    <!-- Plan Limits -->
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-users text-primary"></i>
                            <strong><?= $plan['max_users'] ?></strong> users
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-hdd text-info"></i>
                            <strong><?= $plan['storage_per_user_gb'] ?>GB</strong> per user
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-paper-plane text-success"></i>
                            <strong><?= number_format($plan['daily_send_limit']) ?></strong> emails/day
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-paperclip text-warning"></i>
                            <strong><?= $plan['max_attachment_size_mb'] ?>MB</strong> attachments
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-globe text-cyan"></i>
                            <strong><?= $plan['max_domains'] ?></strong> domains
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-at text-purple"></i>
                            <strong><?= $plan['max_aliases'] ?></strong> aliases
                        </li>
                    </ul>

                    <!-- Active Subscriptions Badge -->
                    <div class="text-center mt-3">
                        <span class="badge badge-info badge-lg px-3 py-2">
                            <i class="fas fa-check-circle"></i>
                            <?= $plan['active_subscriptions'] ?? 0 ?> active subscriptions
                        </span>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="/admin/projects/mail/plans/<?= $plan['id'] ?>/edit" 
                       class="btn btn-primary btn-block">
                        <i class="fas fa-edit"></i> Edit Plan
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Plan Features Comparison Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table mr-2"></i>
                        Features Comparison
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Feature</th>
                                    <?php foreach ($plans as $plan): ?>
                                    <th class="text-center"><?= View::e($plan['plan_name']) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $featuresList = [
                                    'webmail' => 'Webmail Access',
                                    'smtp' => 'SMTP Access',
                                    'imap' => 'IMAP/POP3 Access',
                                    'api' => 'API Access',
                                    'domain' => 'Custom Domain',
                                    'alias' => 'Email Aliases',
                                    '2fa' => 'Two-Factor Auth',
                                    'threads' => 'Threaded Conversations',
                                    'scheduled_send' => 'Scheduled Sending',
                                    'read_receipts' => 'Read Receipts'
                                ];
                                
                                foreach ($featuresList as $key => $label):
                                ?>
                                <tr>
                                    <td><strong><?= $label ?></strong></td>
                                    <?php foreach ($plans as $plan): ?>
                                    <td class="text-center">
                                        <?php
                                        // Check if this plan has this feature enabled
                                        $hasFeature = false;
                                        // You would query this from database in real implementation
                                        // For now, show based on plan type
                                        if ($plan['plan_name'] === 'Free') {
                                            $hasFeature = in_array($key, ['webmail', 'domain', 'alias']);
                                        } elseif ($plan['plan_name'] === 'Starter') {
                                            $hasFeature = !in_array($key, ['api']);
                                        } else {
                                            $hasFeature = true;
                                        }
                                        ?>
                                        <?php if ($hasFeature): ?>
                                            <i class="fas fa-check-circle text-success fa-lg"></i>
                                        <?php else: ?>
                                            <i class="fas fa-times-circle text-danger"></i>
                                        <?php endif; ?>
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php View::endSection(); ?>

<?php
function getPlanCardColor($planName) {
    $colors = [
        'Free' => 'secondary',
        'Starter' => 'info',
        'Business' => 'warning',
        'Developer' => 'success'
    ];
    return $colors[$planName] ?? 'primary';
}
?>
