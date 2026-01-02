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
        <div class="stat-label">⚠️ Failed Logins (24h)</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['blocked_ips']) ?></div>
        <div class="stat-label">🚫 Blocked IPs</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['suspicious_activities']) ?></div>
        <div class="stat-label">🔍 Suspicious Activities</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['unique_attackers']) ?></div>
        <div class="stat-label">👤 Unique Attackers (7d)</div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">⚠️ Recent Failed Login Attempts</h3>
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
        <h3 class="card-title">🚫 Blocked IP Addresses</h3>
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
