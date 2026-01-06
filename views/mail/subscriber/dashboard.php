<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">
                <i class="fas fa-tachometer-alt mr-2"></i>
                Subscriber Dashboard
            </h2>
            <p class="text-muted">Manage your mail hosting subscription</p>
        </div>
    </div>

    <!-- Subscription Info -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-info-circle mr-2"></i>
                        Subscription Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Account Name:</strong><br>
                            <?= htmlspecialchars($subscriber['account_name']) ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Current Plan:</strong><br>
                            <span class="badge badge-<?= getPlanBadgeColor($subscriber['plan_name']) ?> badge-lg px-3 py-2">
                                <?= htmlspecialchars($subscriber['plan_name']) ?>
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Status:</strong><br>
                            <span class="badge badge-<?= getStatusBadgeColor($subscriber['status']) ?> badge-lg px-3 py-2">
                                <?= ucfirst($subscriber['status']) ?>
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Member Since:</strong><br>
                            <?= date('F d, Y', strtotime($subscriber['created_at'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Users -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['users_count'] ?> / <?= $subscriber['max_users'] ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="/projects/mail/subscriber/users" class="btn btn-sm btn-info btn-block">
                            Manage Users <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Domains -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Domains</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['domains_count'] ?> / <?= $subscriber['max_domains'] ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-globe fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="/projects/mail/subscriber/domains" class="btn btn-sm btn-success btn-block">
                            Manage Domains <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aliases -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Email Aliases</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['aliases_count'] ?> / <?= $subscriber['max_aliases'] ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-at fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="/projects/mail/subscriber/aliases" class="btn btn-sm btn-warning btn-block">
                            Manage Aliases <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emails Today -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Emails Sent Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['emails_today'] ?> / <?= $subscriber['daily_send_limit'] ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="/projects/mail/webmail" class="btn btn-sm btn-primary btn-block">
                            View Webmail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <!-- Recent Users -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-plus mr-2"></i>
                        Recent Users
                    </h6>
                    <a href="/projects/mail/subscriber/users" class="btn btn-sm btn-primary">
                        <i class="fas fa-list"></i> View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentUsers)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <p class="mb-3">No users yet</p>
                        <a href="/projects/mail/subscriber/users/add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Your First User
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <tbody>
                                <?php foreach ($recentUsers as $user): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-envelope text-primary mr-2"></i>
                                        <strong><?= htmlspecialchars($user['email']) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-globe"></i> <?= htmlspecialchars($user['domain_name']) ?>
                                        </small>
                                    </td>
                                    <td class="text-right">
                                        <span class="badge badge-<?= $user['role_type'] === 'domain_admin' ? 'warning' : 'info' ?>">
                                            <?= ucwords(str_replace('_', ' ', $user['role_type'])) ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('M d, Y', strtotime($user['created_at'])) ?>
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

        <!-- Recent Domains -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-globe mr-2"></i>
                        Your Domains
                    </h6>
                    <a href="/projects/mail/subscriber/domains" class="btn btn-sm btn-success">
                        <i class="fas fa-list"></i> View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentDomains)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-globe fa-3x mb-3"></i>
                        <p class="mb-3">No domains added yet</p>
                        <a href="/projects/mail/subscriber/domains/add" class="btn btn-success">
                            <i class="fas fa-plus"></i> Add Your First Domain
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <tbody>
                                <?php foreach ($recentDomains as $domain): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-globe text-success mr-2"></i>
                                        <strong><?= htmlspecialchars($domain['domain_name']) ?></strong>
                                    </td>
                                    <td class="text-right">
                                        <?php if ($domain['is_verified']): ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Verified
                                        </span>
                                        <?php else: ?>
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('M d, Y', strtotime($domain['created_at'])) ?>
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

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt mr-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-2">
                            <a href="/projects/mail/subscriber/users/add" class="btn btn-primary btn-block btn-lg">
                                <i class="fas fa-user-plus"></i><br>Add New User
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <a href="/projects/mail/subscriber/domains/add" class="btn btn-success btn-block btn-lg">
                                <i class="fas fa-globe"></i><br>Add Domain
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <a href="/projects/mail/webmail" class="btn btn-info btn-block btn-lg">
                                <i class="fas fa-inbox"></i><br>Check Mail
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <a href="/projects/mail/subscriber/billing" class="btn btn-warning btn-block btn-lg">
                                <i class="fas fa-credit-card"></i><br>View Billing
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.badge-lg {
    font-size: 0.95rem;
}
</style>

<?php View::endSection(); ?>

<?php
function getPlanBadgeColor($planName) {
    $planName = strtolower($planName);
    $colors = [
        'free' => 'secondary',
        'starter' => 'info',
        'business' => 'warning',
        'developer' => 'success',
        'professional' => 'primary',
        'enterprise' => 'dark'
    ];
    foreach ($colors as $plan => $color) {
        if (stripos($planName, $plan) !== false) {
            return $color;
        }
    }
    return 'primary';
}

function getStatusBadgeColor($status) {
    $colors = [
        'active' => 'success',
        'suspended' => 'danger',
        'cancelled' => 'secondary',
        'grace_period' => 'warning'
    ];
    return $colors[$status] ?? 'info';
}
?>
