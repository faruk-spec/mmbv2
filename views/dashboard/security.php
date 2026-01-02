<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="margin-bottom: 12px;">
    <h1 style="font-size: 1rem; font-weight: 700; margin-bottom: 8px; background: linear-gradient(135deg, var(--cyan), var(--magenta)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Security Settings</h1>
    <p style="color: var(--text-secondary); font-size: 0.875rem;">Protect your account with strong security measures</p>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success" style="margin-bottom: 12px; padding: 16px; background: rgba(0, 255, 136, 0.1); border: 1px solid var(--green); border-radius: 8px; color: var(--green);">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <?= View::e(Helpers::getFlash('success')) ?>
    </div>
<?php endif; ?>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error" style="margin-bottom: 12px; padding: 16px; background: rgba(255, 107, 107, 0.1); border: 1px solid var(--red); border-radius: 8px; color: var(--red);">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
        <?= View::e(Helpers::getFlash('error')) ?>
    </div>
<?php endif; ?>

<div class="grid grid-2" style="gap: 24px;">
    <div class="card" style="border-radius: 10px; overflow: hidden; border: 1px solid var(--border-color);">
        <div class="card-header" style="background: linear-gradient(135deg, rgba(0, 240, 255, 0.1) 0%, rgba(255, 46, 196, 0.1) 100%); border-bottom: 1px solid var(--border-color); padding: 12px;">
            <h3 class="card-title" style="font-size: 0.9rem; display: flex; align-items: center; gap: 10px; margin: 0;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                Change Password
            </h3>
        </div>
        
        <div style="padding: 16px;">
            <form method="POST" action="/security/password">
                <?= \Core\Security::csrfField() ?>
                
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label" for="current_password" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary); font-size: 0.95rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        Current Password
                    </label>
                    <input type="password" id="current_password" name="current_password" 
                           class="form-input" style="width: 100%; padding: 12px 16px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary); font-size: 0.95rem;" required>
                    <?php if (View::hasError('current_password')): ?>
                        <div class="form-error" style="color: var(--red); font-size: 0.875rem; margin-top: 6px;"><?= View::error('current_password') ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label" for="password" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary); font-size: 0.95rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                        New Password
                    </label>
                    <input type="password" id="password" name="password" 
                           class="form-input" style="width: 100%; padding: 12px 16px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary); font-size: 0.95rem;" required minlength="8">
                    <?php if (View::hasError('password')): ?>
                        <div class="form-error" style="color: var(--red); font-size: 0.875rem; margin-top: 6px;"><?= View::error('password') ?></div>
                    <?php endif; ?>
                    <small style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 6px; display: block;">Minimum 8 characters</small>
                </div>
                
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" for="password_confirmation" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary); font-size: 0.95rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Confirm New Password
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" 
                           class="form-input" style="width: 100%; padding: 12px 16px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary); font-size: 0.95rem;" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="padding: 12px 32px; background: linear-gradient(135deg, var(--cyan), var(--magenta)); border: none; border-radius: 10px; color: #06060a; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 0.875rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Update Password
                </button>
            </form>
        </div>
    </div>
    
    <div class="card" style="border-radius: 10px; overflow: hidden; border: 1px solid var(--border-color);">
        <div class="card-header" style="background: linear-gradient(135deg, rgba(153, 69, 255, 0.1) 0%, rgba(255, 46, 196, 0.1) 100%); border-bottom: 1px solid var(--border-color); padding: 12px;">
            <h3 class="card-title" style="font-size: 0.9rem; display: flex; align-items: center; gap: 10px; margin: 0;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
                Two-Factor Authentication
            </h3>
        </div>
        
        <div style="text-align: center; padding: 40px 30px;">
            <?php if ($twoFactorEnabled): ?>
                <div style="width: 80px; height: 80px; background: rgba(0, 255, 136, 0.1); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 30px rgba(0, 255, 136, 0.2);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="M9 12l2 2 4-4"/>
                    </svg>
                </div>
                <h4 style="color: var(--green); margin-bottom: 12px; font-size: 0.85rem;">2FA Enabled</h4>
                <p style="color: var(--text-secondary); margin-bottom: 12px; line-height: 1.6;">Your account is protected with two-factor authentication. An additional code is required for login.</p>
                <a href="/2fa/setup" class="btn btn-secondary" style="padding: 10px 24px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary); font-weight: 600; text-decoration: none; display: inline-block; transition: all 0.3s ease;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6m5.2-13.2l-4.2 4.2m-2 2l-4.2 4.2m13.2-5.2l-4.2-4.2m-2 2l-4.2-4.2"></path>
                    </svg>
                    Manage 2FA
                </a>
            <?php else: ?>
                <div style="width: 80px; height: 80px; background: rgba(255, 170, 0, 0.1); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 30px rgba(255, 170, 0, 0.2);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <h4 style="color: var(--orange); margin-bottom: 12px; font-size: 0.85rem;">2FA Disabled</h4>
                <p style="color: var(--text-secondary); margin-bottom: 12px; line-height: 1.6;">Add an extra layer of security to your account by enabling two-factor authentication.</p>
                <a href="/2fa/setup" class="btn btn-primary" style="padding: 10px 24px; background: linear-gradient(135deg, var(--cyan), var(--magenta)); border: none; border-radius: 10px; color: #06060a; font-weight: 600; text-decoration: none; display: inline-block; transition: all 0.3s ease;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    Enable 2FA
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card mt-3" style="border-radius: 10px; overflow: hidden; border: 1px solid var(--border-color); margin-top: 24px;">
    <div class="card-header" style="background: linear-gradient(135deg, rgba(0, 255, 136, 0.1) 0%, rgba(0, 240, 255, 0.1) 100%); border-bottom: 1px solid var(--border-color); padding: 12px;">
        <h3 class="card-title" style="font-size: 0.9rem; display: flex; align-items: center; gap: 10px; margin: 0;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            </svg>
            Active Sessions
        </h3>
    </div>
    
    <div style="padding: 12px;">
        <?php if (empty($devices)): ?>
            <p style="color: var(--text-secondary); text-align: center; padding: 40px 20px;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="1.5" style="display: block; margin: 0 auto 16px; opacity: 0.5;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                No active sessions found
            </p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: separate; border-spacing: 0;">
                    <thead>
                        <tr style="background: var(--bg-secondary);">
                            <th style="padding: 16px; text-align: left; border-bottom: 1px solid var(--border-color); font-weight: 600; color: var(--text-primary);">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                </svg>
                                Device
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
                                Created
                            </th>
                            <th style="padding: 16px; text-align: left; border-bottom: 1px solid var(--border-color); font-weight: 600; color: var(--text-primary);">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                Expires
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($devices as $device): 
                            $deviceInfo = json_decode($device['device_info'], true);
                        ?>
                            <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.3s ease;">
                                <td style="padding: 16px; color: var(--text-primary);">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 40px; height: 40px; background: rgba(0, 240, 255, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div style="font-weight: 500;"><?= View::e(Helpers::truncate($deviceInfo['browser'] ?? 'Unknown', 50)) ?></div>
                                            <div style="font-size: 0.85rem; color: var(--text-secondary);"><?= View::e($deviceInfo['platform'] ?? '') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 16px; color: var(--text-secondary); font-family: monospace; font-size: 0.9rem;"><?= View::e($deviceInfo['ip'] ?? 'Unknown') ?></td>
                                <td style="padding: 16px; color: var(--text-secondary);"><?= Helpers::formatDate($device['created_at']) ?></td>
                                <td style="padding: 16px; color: var(--text-secondary);"><?= Helpers::formatDate($device['expires_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .form-input:focus {
        outline: none;
        border-color: var(--cyan);
        box-shadow: 0 0 0 3px rgba(0, 240, 255, 0.1);
    }
    
    .btn-primary:hover, .btn-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 240, 255, 0.4);
    }
    
    table tbody tr:hover {
        background: rgba(0, 240, 255, 0.03);
    }
    
    @media (max-width: 768px) {
        .grid-2 {
            grid-template-columns: 1fr !important;
        }
        
        table {
            font-size: 0.85rem;
        }
        
        table th, table td {
            padding: 12px 8px !important;
        }
    }
</style>
<?php View::endSection(); ?>
