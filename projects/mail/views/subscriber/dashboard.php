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
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Subscription Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Account Name:</strong><br>
                            <?= View::e($subscriber['account_name']) ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Current Plan:</strong><br>
                            <span class="badge badge-<?= getPlanBadgeColor($subscriber['plan_name']) ?> px-3 py-2">
                                <?= View::e($subscriber['plan_name']) ?>
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Status:</strong><br>
                            <span class="badge badge-<?= getStatusBadgeColor($subscriber['status']) ?> px-3 py-2">
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
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-info">
                <div class="inner">
                    <h3><?= $stats['users_count'] ?> / <?= $subscriber['max_users'] ?></h3>
                    <p>Users</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="/projects/mail/subscriber/users" class="small-box-footer">
                    Manage Users <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Domains -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-success">
                <div class="inner">
                    <h3><?= $stats['domains_count'] ?> / <?= $subscriber['max_domains'] ?></h3>
                    <p>Domains</p>
                </div>
                <div class="icon">
                    <i class="fas fa-globe"></i>
                </div>
                <a href="/projects/mail/subscriber/domains" class="small-box-footer">
                    Manage Domains <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Aliases -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-warning">
                <div class="inner">
                    <h3><?= $stats['aliases_count'] ?> / <?= $subscriber['max_aliases'] ?></h3>
                    <p>Email Aliases</p>
                </div>
                <div class="icon">
                    <i class="fas fa-at"></i>
                </div>
                <a href="/projects/mail/aliases" class="small-box-footer">
                    Manage Aliases <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Emails Today -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-primary">
                <div class="inner">
                    <h3><?= $stats['emails_today'] ?></h3>
                    <p>Emails Sent Today</p>
                </div>
                <div class="icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <a href="/projects/mail/mailbox/sent" class="small-box-footer">
                    View Sent Mail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <!-- Recent Users -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-plus mr-2"></i>
                        Recent Users
                    </h3>
                    <div class="card-tools">
                        <a href="/projects/mail/subscriber/users" class="btn btn-tool">
                            <i class="fas fa-list"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentUsers)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <p>No users yet</p>
                        <a href="/projects/mail/subscriber/users/add" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Your First User
                        </a>
                    </div>
                    <?php else: ?>
                    <table class="table table-hover mb-0">
                        <tbody>
                            <?php foreach ($recentUsers as $user): ?>
                            <tr>
                                <td>
                                    <i class="fas fa-envelope text-primary"></i>
                                    <strong><?= View::e($user['email']) ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-globe"></i> <?= View::e($user['domain_name']) ?>
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
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Domains -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-globe mr-2"></i>
                        Your Domains
                    </h3>
                    <div class="card-tools">
                        <a href="/projects/mail/subscriber/domains" class="btn btn-tool">
                            <i class="fas fa-list"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentDomains)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-globe fa-3x mb-3"></i>
                        <p>No domains added yet</p>
                        <a href="/projects/mail/subscriber/domains/add" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Your First Domain
                        </a>
                    </div>
                    <?php else: ?>
                    <table class="table table-hover mb-0">
                        <tbody>
                            <?php foreach ($recentDomains as $domain): ?>
                            <tr>
                                <td>
                                    <i class="fas fa-globe text-success"></i>
                                    <strong><?= View::e($domain['domain_name']) ?></strong>
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-2"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="/projects/mail/subscriber/users/add" class="btn btn-primary btn-block">
                                <i class="fas fa-user-plus"></i> Add New User
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="/projects/mail/subscriber/domains/add" class="btn btn-success btn-block">
                                <i class="fas fa-globe"></i> Add Domain
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="/projects/mail/mailbox/inbox" class="btn btn-info btn-block">
                                <i class="fas fa-inbox"></i> Check Mail
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="/projects/mail/subscriber/billing" class="btn btn-warning btn-block">
                                <i class="fas fa-credit-card"></i> View Billing
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
