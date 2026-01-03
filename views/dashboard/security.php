<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
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

<!-- Google OAuth Connection -->
<?php 
$db = \Core\Database::getInstance();
$googleConnection = null;
if (\Core\GoogleOAuth::isEnabled()) {
    try {
        $googleConnection = $db->fetch(
            "SELECT ouc.*, op.id as provider_id, op.display_name as provider_display_name 
             FROM oauth_user_connections ouc
             JOIN oauth_providers op ON ouc.provider_id = op.id
             WHERE ouc.user_id = ? AND op.name = 'google'",
            [\Core\Auth::id()]
        );
    } catch (\Exception $e) {}
}
?>

<?php if (\Core\GoogleOAuth::isEnabled()): ?>
<div class="card" style="border-radius: 10px; overflow: hidden; border: 1px solid var(--border-color); margin-top: 24px;">
    <div class="card-header" style="background: linear-gradient(135deg, rgba(66, 133, 244, 0.1) 0%, rgba(234, 67, 53, 0.1) 100%); border-bottom: 1px solid var(--border-color); padding: 12px;">
        <h3 class="card-title" style="font-size: 0.9rem; display: flex; align-items: center; gap: 10px; margin: 0;">
            <svg width="16" height="16" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                <g fill="none" fill-rule="evenodd">
                    <path d="M17.64 9.205c0-.639-.057-1.252-.164-1.841H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
                    <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
                    <path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
                    <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
                </g>
            </svg>
            Google Account
        </h3>
    </div>
    
    <div style="text-align: center; padding: 40px 30px;">
        <?php if ($googleConnection): ?>
            <div style="width: 80px; height: 80px; background: rgba(66, 133, 244, 0.1); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 30px rgba(66, 133, 244, 0.2);">
                <svg width="24" height="24" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                    <g fill="none" fill-rule="evenodd">
                        <path d="M17.64 9.205c0-.639-.057-1.252-.164-1.841H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
                        <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
                        <path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
                        <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
                    </g>
                </svg>
            </div>
            <h4 style="color: #4285F4; margin-bottom: 12px; font-size: 0.85rem;">Google Account Connected</h4>
            <p style="color: var(--text-secondary); margin-bottom: 8px; line-height: 1.6;">
                <strong><?= View::e($googleConnection['provider_display_name'] ?? 'Google') ?></strong><br>
                <small><?= View::e($googleConnection['provider_email']) ?></small>
            </p>
            <p style="color: var(--text-secondary); margin-bottom: 16px; font-size: 0.85rem;">
                Connected <?= Helpers::timeAgo(strtotime($googleConnection['created_at'])) ?>
            </p>
            <form method="POST" action="/auth/google/unlink" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to unlink your Google account?');">
                <?= \Core\Security::csrfField() ?>
                <button type="submit" class="btn btn-secondary" style="padding: 10px 24px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary); font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    Unlink Google Account
                </button>
            </form>
        <?php else: ?>
            <div style="width: 80px; height: 80px; background: rgba(100, 116, 139, 0.1); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 30px rgba(100, 116, 139, 0.2);">
                <svg width="24" height="24" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                    <g fill="none" fill-rule="evenodd" opacity="0.5">
                        <path d="M17.64 9.205c0-.639-.057-1.252-.164-1.841H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
                        <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
                        <path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
                        <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
                    </g>
                </svg>
            </div>
            <h4 style="color: var(--text-secondary); margin-bottom: 12px; font-size: 0.85rem;">Google Account Not Connected</h4>
            <p style="color: var(--text-secondary); margin-bottom: 16px; line-height: 1.6;">Link your Google account for easier sign-in and enhanced security.</p>
            <a href="/auth/google/link" class="btn btn-primary" style="padding: 10px 24px; background: linear-gradient(135deg, #4285F4, #34A853); border: none; border-radius: 10px; color: white; font-weight: 600; text-decoration: none; display: inline-block; transition: all 0.3s ease;">
                <svg width="16" height="16" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                    <g fill="none" fill-rule="evenodd">
                        <path d="M17.64 9.205c0-.639-.057-1.252-.164-1.841H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="white"/>
                        <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="white"/>
                        <path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="white"/>
                        <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="white"/>
                    </g>
                </svg>
                Link Google Account
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

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
