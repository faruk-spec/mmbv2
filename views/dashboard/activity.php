<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="margin-bottom: 12px;">
    <h1 style="font-size: 1rem; font-weight: 700; margin-bottom: 8px; background: linear-gradient(135deg, var(--cyan), var(--magenta)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Activity Log</h1>
    <p style="color: var(--text-secondary); font-size: 0.875rem;">View your recent account activity and security events</p>
</div>

<div class="card" style="border-radius: 10px; overflow: hidden; border: 1px solid var(--border-color);">
    <div class="card-header" style="background: linear-gradient(135deg, rgba(0, 240, 255, 0.1) 0%, rgba(255, 46, 196, 0.1) 100%); border-bottom: 1px solid var(--border-color); padding: 12px;">
        <h3 class="card-title" style="font-size: 0.9rem; display: flex; align-items: center; gap: 10px; margin: 0;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
            Recent Activities
        </h3>
    </div>
    
    <div style="padding: 12px;">
        <?php if (empty($activity)): ?>
            <div style="text-align: center; padding: 60px 20px;">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="1.5" style="display: block; margin: 0 auto 20px; opacity: 0.4;">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
                <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 8px;">No activity recorded yet</p>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">Your account activities will appear here</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: separate; border-spacing: 0;">
                    <thead>
                        <tr style="background: var(--bg-secondary);">
                            <th style="padding: 16px; text-align: left; border-bottom: 1px solid var(--border-color); font-weight: 600; color: var(--text-primary);">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                                </svg>
                                Action
                            </th>
                            <th style="padding: 16px; text-align: left; border-bottom: 1px solid var(--border-color); font-weight: 600; color: var(--text-primary);">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="2" y1="12" x2="22" y2="12"></line>
                                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                </svg>
                                IP Address
                            </th>
                            <th style="padding: 16px; text-align: left; border-bottom: 1px solid var(--border-color); font-weight: 600; color: var(--text-primary);">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                Date & Time
                            </th>
                            <th style="padding: 16px; text-align: left; border-bottom: 1px solid var(--border-color); font-weight: 600; color: var(--text-primary);">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                                Details
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activity as $log): 
                            // Determine icon and color based on action
                            $actionType = strtolower($log['action']);
                            $iconColor = 'var(--cyan)';
                            $bgColor = 'rgba(0, 240, 255, 0.1)';
                            
                            if (strpos($actionType, 'login') !== false) {
                                $iconColor = 'var(--green)';
                                $bgColor = 'rgba(0, 255, 136, 0.1)';
                            } elseif (strpos($actionType, 'logout') !== false) {
                                $iconColor = 'var(--orange)';
                                $bgColor = 'rgba(255, 170, 0, 0.1)';
                            } elseif (strpos($actionType, 'password') !== false || strpos($actionType, 'security') !== false) {
                                $iconColor = 'var(--magenta)';
                                $bgColor = 'rgba(255, 46, 196, 0.1)';
                            } elseif (strpos($actionType, 'profile') !== false || strpos($actionType, 'update') !== false) {
                                $iconColor = 'var(--purple)';
                                $bgColor = 'rgba(153, 69, 255, 0.1)';
                            } elseif (strpos($actionType, 'failed') !== false || strpos($actionType, 'error') !== false) {
                                $iconColor = 'var(--red)';
                                $bgColor = 'rgba(255, 107, 107, 0.1)';
                            }
                        ?>
                            <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.3s ease;">
                                <td style="padding: 16px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 45px; height: 45px; background: <?= $bgColor ?>; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="<?= $iconColor ?>" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"/>
                                                <polyline points="12 6 12 12 16 14"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="badge" style="display: inline-block; padding: 5px 12px; background: <?= $bgColor ?>; color: <?= $iconColor ?>; border: 1px solid <?= $iconColor ?>; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
                                                <?= View::e($log['action']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 16px; color: var(--text-secondary); font-family: monospace; font-size: 0.9rem;"><?= View::e($log['ip_address']) ?></td>
                                <td style="padding: 16px; color: var(--text-primary); font-weight: 500;">
                                    <div><?= Helpers::formatDate($log['created_at'], 'M d, Y') ?></div>
                                    <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 400;"><?= Helpers::formatDate($log['created_at'], 'H:i:s') ?></div>
                                </td>
                                <td style="padding: 16px;">
                                    <?php if (!empty($log['data'])): ?>
                                        <small style="color: var(--text-secondary);"><?= View::e(Helpers::truncate($log['data'], 50)) ?></small>
                                    <?php else: ?>
                                        <small style="color: var(--text-secondary); opacity: 0.5;">-</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($pagination['total'] > 1): ?>
                <div style="margin-top: 30px; display: flex; justify-content: center; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <?php if ($pagination['current'] > 1): ?>
                        <a href="?page=<?= $pagination['current'] - 1 ?>" class="btn btn-sm btn-secondary" style="padding: 10px 20px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-weight: 600; text-decoration: none; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 6px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                            Previous
                        </a>
                    <?php endif; ?>
                    
                    <span style="padding: 10px 20px; color: var(--text-primary); font-weight: 600; background: var(--bg-secondary); border-radius: 8px; border: 1px solid var(--border-color);">
                        Page <?= $pagination['current'] ?> of <?= $pagination['total'] ?>
                    </span>
                    
                    <?php if ($pagination['current'] < $pagination['total']): ?>
                        <a href="?page=<?= $pagination['current'] + 1 ?>" class="btn btn-sm btn-secondary" style="padding: 10px 20px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-weight: 600; text-decoration: none; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 6px;">
                            Next
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    table tbody tr:hover {
        background: rgba(0, 240, 255, 0.03);
    }
    
    .btn-sm:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 240, 255, 0.3);
        border-color: var(--cyan);
        color: var(--cyan);
    }
    
    @media (max-width: 768px) {
        table {
            font-size: 0.85rem;
        }
        
        table th, table td {
            padding: 12px 8px !important;
        }
        
        table th svg, table td svg {
            display: none;
        }
        
        .badge {
            font-size: 0.75rem !important;
            padding: 4px 8px !important;
        }
    }
</style>
<?php View::endSection(); ?>
