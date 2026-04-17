<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-header" style="margin-bottom: 30px;">
    <h1 style="font-size: 1.8rem; margin-bottom: 10px;">Two-Factor Authentication Management</h1>
    <p style="color: var(--text-secondary);">Manage 2FA settings for all users</p>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <?= View::e(Helpers::getFlash('success')) ?>
    </div>
<?php endif; ?>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error" style="margin-bottom: 20px;">
        <?= View::e(Helpers::getFlash('error')) ?>
    </div>
<?php endif; ?>

<!-- Statistics -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;">
    <div class="stat-card" style="background: linear-gradient(135deg, rgba(0, 240, 255, 0.1) 0%, rgba(255, 46, 196, 0.1) 100%); padding: 20px; border-radius: 12px; border: 1px solid var(--border-color);">
        <div style="font-size: 2rem; font-weight: 700; margin-bottom: 8px;"><?= $stats['total_users'] ?></div>
        <div style="color: var(--text-secondary); font-size: 0.9rem;">Total Users</div>
    </div>
    
    <div class="stat-card" style="background: linear-gradient(135deg, rgba(0, 255, 136, 0.1) 0%, rgba(0, 240, 255, 0.1) 100%); padding: 20px; border-radius: 12px; border: 1px solid var(--border-color);">
        <div style="font-size: 2rem; font-weight: 700; margin-bottom: 8px; color: var(--green);"><?= $stats['2fa_enabled'] ?></div>
        <div style="color: var(--text-secondary); font-size: 0.9rem;">2FA Enabled</div>
    </div>
    
    <div class="stat-card" style="background: linear-gradient(135deg, rgba(255, 170, 0, 0.1) 0%, rgba(255, 46, 196, 0.1) 100%); padding: 20px; border-radius: 12px; border: 1px solid var(--border-color);">
        <div style="font-size: 2rem; font-weight: 700; margin-bottom: 8px; color: var(--orange);"><?= $stats['2fa_disabled'] ?></div>
        <div style="color: var(--text-secondary); font-size: 0.9rem;">2FA Disabled</div>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px; padding: 20px; border-radius: 12px; border: 1px solid var(--border-color);">
    <form method="GET" action="/admin/2fa" style="display: grid; grid-template-columns: 1fr 200px auto; gap: 15px; align-items: end;">
        <div>
            <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem;">Search Users</label>
            <input type="text" name="search" value="<?= View::e($search) ?>" 
                   placeholder="Name or email..." 
                   class="form-input" 
                   style="width: 100%; padding: 10px 15px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary);">
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem;">Status</label>
            <select name="status" class="form-input" style="width: 100%; padding: 10px 15px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary);">
                <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Users</option>
                <option value="enabled" <?= $status === 'enabled' ? 'selected' : '' ?>>2FA Enabled</option>
                <option value="disabled" <?= $status === 'disabled' ? 'selected' : '' ?>>2FA Disabled</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary" style="padding: 10px 24px; background: linear-gradient(135deg, var(--cyan), var(--magenta)); border: none; border-radius: 8px; color: #06060a; font-weight: 600; cursor: pointer;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            Filter
        </button>
    </form>
</div>

<!-- Users Table -->
<div class="card" style="padding: 0; border-radius: 12px; border: 1px solid var(--border-color); overflow: hidden;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-secondary); border-bottom: 1px solid var(--border-color);">
                    <th style="padding: 15px; text-align: left; font-weight: 600; font-size: 0.85rem; color: var(--text-secondary);">User</th>
                    <th style="padding: 15px; text-align: left; font-weight: 600; font-size: 0.85rem; color: var(--text-secondary);">Email</th>
                    <th style="padding: 15px; text-align: center; font-weight: 600; font-size: 0.85rem; color: var(--text-secondary);">2FA Status</th>
                    <th style="padding: 15px; text-align: left; font-weight: 600; font-size: 0.85rem; color: var(--text-secondary);">Enabled At</th>
                    <th style="padding: 15px; text-align: left; font-weight: 600; font-size: 0.85rem; color: var(--text-secondary);">Last Login</th>
                    <th style="padding: 15px; text-align: right; font-weight: 600; font-size: 0.85rem; color: var(--text-secondary);">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" style="padding: 40px; text-align: center; color: var(--text-secondary);">
                            No users found matching your criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 15px;">
                                <div style="font-weight: 600;"><?= View::e($user['name']) ?></div>
                            </td>
                            <td style="padding: 15px;">
                                <div style="color: var(--text-secondary); font-size: 0.9rem;"><?= View::e($user['email']) ?></div>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <?php if ($user['two_factor_enabled']): ?>
                                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(0, 255, 136, 0.1); color: var(--green); border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                            <path d="M9 12l2 2 4-4"></path>
                                        </svg>
                                        Enabled
                                    </span>
                                <?php else: ?>
                                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(255, 107, 107, 0.1); color: var(--red); border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="15" y1="9" x2="9" y2="15"></line>
                                            <line x1="9" y1="9" x2="15" y2="15"></line>
                                        </svg>
                                        Disabled
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px;">
                                <?php if ($user['two_factor_enabled_at']): ?>
                                    <div style="font-size: 0.9rem;"><?= Helpers::timeAgo($user['two_factor_enabled_at']) ?></div>
                                <?php else: ?>
                                    <div style="color: var(--text-secondary); font-size: 0.9rem;">Never</div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px;">
                                <?php if ($user['last_login_at']): ?>
                                    <div style="font-size: 0.9rem;"><?= Helpers::timeAgo($user['last_login_at']) ?></div>
                                <?php else: ?>
                                    <div style="color: var(--text-secondary); font-size: 0.9rem;">Never</div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px; text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <?php if ($user['two_factor_enabled']): ?>
                                        <form method="POST" action="/admin/2fa/<?= $user['id'] ?>/reset" style="display: inline;" onsubmit="return confirm('Reset 2FA for this user? They will need to set it up again.');">
                                            <?= \Core\Security::csrfField() ?>
                                            <button type="submit" class="btn btn-sm" style="padding: 6px 12px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.85rem; cursor: pointer;">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                                                    <polyline points="1 4 1 10 7 10"></polyline>
                                                    <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                                                </svg>
                                                Reset
                                            </button>
                                        </form>
                                        
                                        <form method="POST" action="/admin/2fa/<?= $user['id'] ?>/toggle" style="display: inline;" onsubmit="return confirm('Disable 2FA for this user?');">
                                            <?= \Core\Security::csrfField() ?>
                                            <button type="submit" class="btn btn-sm btn-danger" style="padding: 6px 12px; background: rgba(255, 107, 107, 0.1); color: var(--red); border: 1px solid var(--red); border-radius: 6px; font-size: 0.85rem; cursor: pointer;">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                                </svg>
                                                Disable
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: var(--text-secondary); font-size: 0.85rem; font-style: italic;">User must enable 2FA</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php View::endSection(); ?>
