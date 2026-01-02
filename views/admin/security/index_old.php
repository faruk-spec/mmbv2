<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1>Security Center</h1>
        <p style="color: var(--text-secondary);">Monitor and manage platform security</p>
    </div>
</div>

<div class="grid grid-3 mb-3">
    <div class="card" style="text-align: center;">
        <div style="font-size: 2.5rem; font-weight: 700; color: var(--red);"><?= $stats['blocked_ips'] ?></div>
        <div style="color: var(--text-secondary);">Blocked IPs</div>
    </div>
    
    <div class="card" style="text-align: center;">
        <div style="font-size: 2.5rem; font-weight: 700; color: var(--orange);"><?= $stats['failed_logins_today'] ?></div>
        <div style="color: var(--text-secondary);">Failed Logins Today</div>
    </div>
    
    <div class="card" style="text-align: center;">
        <div style="font-size: 2.5rem; font-weight: 700; color: var(--green);"><?= $stats['active_sessions'] ?></div>
        <div style="color: var(--text-secondary);">Active Sessions</div>
    </div>
</div>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Quick Actions</h3>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 15px;">
            <a href="/admin/security/blocked-ips" style="display: flex; align-items: center; gap: 15px; padding: 15px; background: var(--bg-secondary); border-radius: 8px; color: var(--text-primary);">
                <div style="width: 40px; height: 40px; background: rgba(255, 107, 107, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M4.93 4.93l14.14 14.14"/>
                    </svg>
                </div>
                <div>
                    <div style="font-weight: 500;">Blocked IPs</div>
                    <div style="font-size: 13px; color: var(--text-secondary);">Manage IP blocking rules</div>
                </div>
            </a>
            
            <a href="/admin/security/failed-logins" style="display: flex; align-items: center; gap: 15px; padding: 15px; background: var(--bg-secondary); border-radius: 8px; color: var(--text-primary);">
                <div style="width: 40px; height: 40px; background: rgba(255, 170, 0, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2">
                        <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <div style="font-weight: 500;">Failed Logins</div>
                    <div style="font-size: 13px; color: var(--text-secondary);">View failed login attempts</div>
                </div>
            </a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Failed Logins</h3>
            <a href="/admin/security/failed-logins" class="btn btn-sm btn-secondary">View All</a>
        </div>
        
        <?php if (empty($recentFailedLogins)): ?>
            <p style="color: var(--text-secondary); text-align: center; padding: 20px;">No failed login attempts</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>IP</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentFailedLogins as $login): ?>
                        <tr>
                            <td><?= View::e($login['username']) ?></td>
                            <td><?= View::e($login['ip_address']) ?></td>
                            <td><?= Helpers::timeAgo($login['attempted_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php View::endSection(); ?>
