<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: linear-gradient(135deg, var(--bg-card), var(--bg-secondary));
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 5px;
        color: var(--red);
    }
    .stat-label {
        color: var(--text-secondary);
        font-size: 14px;
    }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['failed_logins_24h']) ?></div>
        <div class="stat-label">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            Failed Logins (24h)
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['blocked_ips']) ?></div>
        <div class="stat-label">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
            </svg>
            Blocked IPs
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['suspicious_activities']) ?></div>
        <div class="stat-label">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            Suspicious Activities
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['unique_attackers']) ?></div>
        <div class="stat-label">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            Unique Attackers (7d)
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            Recent Failed Login Attempts
        </h3>
    </div>
    <div class="card-body">
        <?php if (!empty($failedLogins)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>IP Address</th>
                            <th>Attempted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($failedLogins, 0, 20) as $login): ?>
                            <tr>
                                <td><?= htmlspecialchars($login['username']) ?></td>
                                <td><?= htmlspecialchars($login['ip_address']) ?></td>
                                <td><?= date('Y-m-d H:i:s', strtotime($login['attempted_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-secondary">No failed login attempts recorded.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
            </svg>
            Blocked IP Addresses
        </h3>
    </div>
    <div class="card-body">
        <?php if (!empty($blockedIps)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>IP Address</th>
                            <th>Reason</th>
                            <th>Blocked By</th>
                            <th>Expires At</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blockedIps as $ip): ?>
                            <tr>
                                <td><?= htmlspecialchars($ip['ip_address']) ?></td>
                                <td><?= htmlspecialchars($ip['reason'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($ip['blocked_by_name'] ?? 'System') ?></td>
                                <td><?= $ip['expires_at'] ? date('Y-m-d H:i:s', strtotime($ip['expires_at'])) : 'Never' ?></td>
                                <td><?= date('Y-m-d H:i:s', strtotime($ip['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-secondary">No blocked IP addresses.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">�� Suspicious Activities</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($suspiciousActivities)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Resource</th>
                            <th>IP Address</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suspiciousActivities as $activity): ?>
                            <tr>
                                <td><span class="badge badge-danger"><?= htmlspecialchars($activity['action']) ?></span></td>
                                <td><?= htmlspecialchars($activity['resource_type']) ?> #<?= $activity['resource_id'] ?? 'N/A' ?></td>
                                <td><?= htmlspecialchars($activity['ip_address'] ?? 'N/A') ?></td>
                                <td><?= date('Y-m-d H:i:s', strtotime($activity['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-secondary">No suspicious activities detected.</p>
        <?php endif; ?>
    </div>
</div>

<?php View::endSection(); ?>
