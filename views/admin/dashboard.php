<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
    .stat-card {
        background: linear-gradient(135deg, var(--bg-card), var(--bg-secondary));
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: radial-gradient(circle, var(--cyan) 0%, transparent 70%);
        opacity: 0.1;
    }
    
    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .stat-label {
        color: var(--text-secondary);
        font-size: 14px;
    }
    
    .chart-container {
        height: 200px;
        display: flex;
        align-items: flex-end;
        gap: 8px;
        padding: 20px 0;
    }
    
    .chart-bar {
        flex: 1;
        background: linear-gradient(180deg, var(--cyan), var(--magenta));
        border-radius: 4px 4px 0 0;
        min-height: 10px;
        position: relative;
        transition: var(--transition);
    }
    
    .chart-bar:hover {
        opacity: 0.8;
    }
    
    .chart-bar::after {
        content: attr(data-value);
        position: absolute;
        top: -25px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 12px;
        color: var(--text-secondary);
    }
    
    .chart-bar::before {
        content: attr(data-label);
        position: absolute;
        bottom: -25px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 11px;
        color: var(--text-secondary);
        white-space: nowrap;
    }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>

<div class="grid grid-4 mb-3">
    <div class="stat-card">
        <div class="stat-value" style="color: var(--cyan);"><?= $stats['total_users'] ?></div>
        <div class="stat-label">Total Users</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value" style="color: var(--green);"><?= $stats['active_users'] ?></div>
        <div class="stat-label">Active Users</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value" style="color: var(--magenta);"><?= $stats['new_users_today'] ?></div>
        <div class="stat-label">New Today</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value" style="color: var(--orange);"><?= $stats['total_logins_today'] ?></div>
        <div class="stat-label">Logins Today</div>
    </div>
</div>

<!-- New Projects Stats -->
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title" style="font-size: 1.3rem;">📦 New Projects Overview</h3>
    </div>
    
    <div class="grid grid-3">
        <!-- CodeXPro Stats -->
        <div style="padding: 20px; border-right: 1px solid var(--border-color);">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                <div style="width: 45px; height: 45px; background: linear-gradient(135deg, var(--cyan), var(--purple)); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-code" style="font-size: 20px;"></i>
                </div>
                <div>
                    <h4 style="font-size: 1.1rem; margin-bottom: 3px;">CodeXPro</h4>
                    <p style="font-size: 12px; color: var(--text-secondary);">Live Code Editor</p>
                </div>
            </div>
            <div class="grid grid-2" style="gap: 15px;">
                <div>
                    <div style="font-size: 1.8rem; font-weight: 700; color: var(--cyan);"><?= $projectStats['codexpro']['projects'] ?? 0 ?></div>
                    <div style="font-size: 12px; color: var(--text-secondary);">Projects</div>
                </div>
                <div>
                    <div style="font-size: 1.8rem; font-weight: 700; color: var(--green);"><?= $projectStats['codexpro']['snippets'] ?? 0 ?></div>
                    <div style="font-size: 12px; color: var(--text-secondary);">Snippets</div>
                </div>
            </div>
            <a href="/admin/projects/codexpro" class="btn btn-secondary mt-2" style="width: 100%; justify-content: center;">
                <i class="fas fa-chart-line"></i> View Details
            </a>
        </div>
        
        <!-- ImgTxt Stats -->
        <div style="padding: 20px; border-right: 1px solid var(--border-color);">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                <div style="width: 45px; height: 45px; background: linear-gradient(135deg, var(--green), var(--cyan)); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-image" style="font-size: 20px;"></i>
                </div>
                <div>
                    <h4 style="font-size: 1.1rem; margin-bottom: 3px;">ImgTxt</h4>
                    <p style="font-size: 12px; color: var(--text-secondary);">OCR Tool</p>
                </div>
            </div>
            <div class="grid grid-2" style="gap: 15px;">
                <div>
                    <div style="font-size: 1.8rem; font-weight: 700; color: var(--green);"><?= $projectStats['imgtxt']['total_jobs'] ?? 0 ?></div>
                    <div style="font-size: 12px; color: var(--text-secondary);">OCR Jobs</div>
                </div>
                <div>
                    <div style="font-size: 1.8rem; font-weight: 700; color: var(--orange);"><?= $projectStats['imgtxt']['completed'] ?? 0 ?></div>
                    <div style="font-size: 12px; color: var(--text-secondary);">Completed</div>
                </div>
            </div>
            <a href="/admin/projects/imgtxt" class="btn btn-secondary mt-2" style="width: 100%; justify-content: center;">
                <i class="fas fa-chart-line"></i> View Details
            </a>
        </div>
        
        <!-- ProShare Stats -->
        <div style="padding: 20px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                <div style="width: 45px; height: 45px; background: linear-gradient(135deg, var(--magenta), var(--orange)); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-share-alt" style="font-size: 20px;"></i>
                </div>
                <div>
                    <h4 style="font-size: 1.1rem; margin-bottom: 3px;">ProShare</h4>
                    <p style="font-size: 12px; color: var(--text-secondary);">Secure Sharing</p>
                </div>
            </div>
            <div class="grid grid-2" style="gap: 15px;">
                <div>
                    <div style="font-size: 1.8rem; font-weight: 700; color: var(--magenta);"><?= $projectStats['proshare']['files'] ?? 0 ?></div>
                    <div style="font-size: 12px; color: var(--text-secondary);">Files</div>
                </div>
                <div>
                    <div style="font-size: 1.8rem; font-weight: 700; color: var(--cyan);"><?= $projectStats['proshare']['texts'] ?? 0 ?></div>
                    <div style="font-size: 12px; color: var(--text-secondary);">Texts</div>
                </div>
            </div>
            <a href="/admin/projects/proshare" class="btn btn-secondary mt-2" style="width: 100%; justify-content: center;">
                <i class="fas fa-chart-line"></i> View Details
            </a>
        </div>
    </div>
</div>

<div class="grid grid-2 mb-3">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">User Registrations (Last 7 Days)</h3>
        </div>
        
        <div class="chart-container">
            <?php 
            $maxCount = max(array_column($chartData, 'count')) ?: 1;
            foreach ($chartData as $data): 
                $height = ($data['count'] / $maxCount) * 150;
            ?>
                <div class="chart-bar" 
                     style="height: <?= max($height, 10) ?>px;" 
                     data-value="<?= $data['count'] ?>" 
                     data-label="<?= $data['date'] ?>"></div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Projects</h3>
            <a href="/admin/projects" class="btn btn-sm btn-secondary">Manage</a>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($projects as $key => $project): ?>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px; background: var(--bg-secondary); border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 35px; height: 35px; background: <?= $project['color'] ?>20; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="<?= $project['color'] ?>" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                            </svg>
                        </div>
                        <span style="font-weight: 500;"><?= $project['name'] ?></span>
                    </div>
                    <span class="badge <?= $project['enabled'] ? 'badge-success' : 'badge-danger' ?>">
                        <?= $project['enabled'] ? 'Active' : 'Disabled' ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Activity</h3>
            <a href="/admin/logs/activity" class="btn btn-sm btn-secondary">View All</a>
        </div>
        
        <?php if (empty($recentActivity)): ?>
            <p style="color: var(--text-secondary); text-align: center; padding: 20px;">No recent activity</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($recentActivity as $log): ?>
                    <div style="display: flex; align-items: center; gap: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color);">
                        <div style="width: 32px; height: 32px; background: var(--bg-secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                            </svg>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-size: 14px;">
                                <strong><?= View::e($log['name'] ?? 'Unknown') ?></strong> - <?= View::e($log['action']) ?>
                            </div>
                            <div style="font-size: 12px; color: var(--text-secondary);">
                                <?= Helpers::timeAgo($log['created_at']) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Users</h3>
            <a href="/admin/users" class="btn btn-sm btn-secondary">View All</a>
        </div>
        
        <?php if (empty($recentUsers)): ?>
            <p style="color: var(--text-secondary); text-align: center; padding: 20px;">No users found</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsers as $u): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 500;"><?= View::e($u['name']) ?></div>
                                <div style="font-size: 12px; color: var(--text-secondary);"><?= View::e($u['email']) ?></div>
                            </td>
                            <td><span class="badge badge-info"><?= $u['role'] ?></span></td>
                            <td>
                                <span class="badge <?= $u['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>">
                                    <?= ucfirst($u['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php View::endSection(); ?>
