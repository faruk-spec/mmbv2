<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="margin-bottom: 12px;">
    <h1 style="font-size: 1rem; font-weight: 700; margin-bottom: 8px; background: linear-gradient(135deg, var(--cyan), var(--magenta)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Profile Settings</h1>
    <p style="color: var(--text-secondary); font-size: 0.875rem;">Manage your personal information and preferences</p>
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

<div class="grid grid-3" style="gap: 24px;">
    <div style="grid-column: span 2;">
        <div class="card" style="border-radius: 10px; overflow: hidden; border: 1px solid var(--border-color);">
            <div class="card-header" style="background: linear-gradient(135deg, rgba(0, 240, 255, 0.1) 0%, rgba(255, 46, 196, 0.1) 100%); border-bottom: 1px solid var(--border-color); padding: 12px;">
                <h3 class="card-title" style="font-size: 0.9rem; display: flex; align-items: center; gap: 10px; margin: 0;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    Personal Information
                </h3>
            </div>
            
            <div style="padding: 16px;">
                <form method="POST" action="/profile" enctype="multipart/form-data">
                    <?= \Core\Security::csrfField() ?>
                    
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" for="name" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary); font-size: 0.95rem;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            Full Name
                        </label>
                        <input type="text" id="name" name="name" class="form-input" 
                               style="width: 100%; padding: 12px 16px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary); font-size: 0.95rem; transition: all 0.3s ease;"
                               value="<?= View::e($user['name']) ?>" required>
                        <?php if (View::hasError('name')): ?>
                            <div class="form-error" style="color: var(--red); font-size: 0.875rem; margin-top: 6px;"><?= View::error('name') ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" for="email" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary); font-size: 0.95rem;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            Email Address
                        </label>
                        <input type="email" id="email" class="form-input" 
                               style="width: 100%; padding: 12px 16px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-secondary); font-size: 0.95rem; cursor: not-allowed;"
                               value="<?= View::e($user['email']) ?>" disabled>
                        <small style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 6px; display: block;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                            Email cannot be changed for security reasons
                        </small>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" for="phone" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary); font-size: 0.95rem;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            Phone Number
                        </label>
                        <input type="tel" id="phone" name="phone" class="form-input" 
                               style="width: 100%; padding: 12px 16px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary); font-size: 0.95rem; transition: all 0.3s ease;"
                               value="<?= View::e($user['phone'] ?? '') ?>" placeholder="+1 234 567 8900">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" for="bio" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary); font-size: 0.95rem;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            Bio
                        </label>
                        <textarea id="bio" name="bio" class="form-input" rows="4" 
                                  style="width: 100%; padding: 12px 16px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary); font-size: 0.95rem; resize: vertical; transition: all 0.3s ease;"
                                  placeholder="Tell us about yourself..."><?= View::e($user['bio'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label class="form-label" for="avatar" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary); font-size: 0.95rem;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                                <rect x="3" y="3" width="14" height="14" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                            Profile Picture
                        </label>
                        <input type="file" id="avatar" name="avatar" class="form-input" accept="image/*"
                               style="width: 100%; padding: 12px 16px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary); font-size: 0.95rem;">
                        <small style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 6px; display: block;">JPG, PNG, GIF up to 2MB</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="padding: 12px 32px; background: linear-gradient(135deg, var(--cyan), var(--magenta)); border: none; border-radius: 10px; color: #06060a; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 0.875rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div>
        <div class="card" style="text-align: center; border-radius: 10px; overflow: hidden; border: 1px solid var(--border-color); padding: 16px;">
            <div style="width: 120px; height: 120px; background: linear-gradient(135deg, var(--cyan), var(--magenta)); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 600; box-shadow: 0 8px 30px rgba(0, 240, 255, 0.3);">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            
            <h3 style="font-size: 0.9rem; margin-bottom: 6px;"><?= View::e($user['name']) ?></h3>
            <p style="color: var(--text-secondary); margin-bottom: 16px; font-size: 0.95rem;"><?= View::e($user['email']) ?></p>
            
            <span class="badge badge-success" style="display: inline-block; padding: 6px 16px; background: rgba(0, 255, 136, 0.2); color: var(--green); border: 1px solid var(--green); border-radius: 20px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><?= ucfirst($user['role']) ?></span>
            
            <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--border-color);">
                <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Member Since</div>
                <div style="font-size: 0.875rem; font-weight: 600; color: var(--text-primary);"><?= Helpers::formatDate($user['created_at']) ?></div>
            </div>
        </div>
        
        <div class="card" style="margin-top: 20px; border-radius: 10px; overflow: hidden; border: 1px solid var(--border-color); padding: 12px;">
            <h4 style="margin-bottom: 12px; font-size: 0.875rem; display: flex; align-items: center; gap: 8px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                Quick Links
            </h4>
            <a href="/dashboard" style="display: flex; align-items: center; gap: 12px; padding: 14px 0; border-bottom: 1px solid var(--border-color); text-decoration: none; color: var(--text-primary); transition: all 0.3s ease;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                Dashboard
            </a>
            <a href="/security" style="display: flex; align-items: center; gap: 12px; padding: 14px 0; border-bottom: 1px solid var(--border-color); text-decoration: none; color: var(--text-primary); transition: all 0.3s ease;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--magenta)" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                Security Settings
            </a>
            <a href="/activity" style="display: flex; align-items: center; gap: 12px; padding: 14px 0; border-bottom: 1px solid var(--border-color); text-decoration: none; color: var(--text-primary); transition: all 0.3s ease;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
                Activity Log
            </a>
            <a href="/settings" style="display: flex; align-items: center; gap: 12px; padding: 14px 0; border-bottom: 1px solid var(--border-color); text-decoration: none; color: var(--text-primary); transition: all 0.3s ease;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M12 1v6m0 6v6m5.2-13.2l-4.2 4.2m-2 2l-4.2 4.2m13.2-5.2l-4.2-4.2m-2 2l-4.2-4.2"></path>
                </svg>
                Settings
            </a>
            <a href="/2fa/setup" style="display: flex; align-items: center; gap: 12px; padding: 14px 0; text-decoration: none; color: var(--text-primary); transition: all 0.3s ease;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
                Two-Factor Authentication
            </a>
        </div>
    </div>
</div>

<style>
    .form-input:focus {
        outline: none;
        border-color: var(--cyan);
        box-shadow: 0 0 0 3px rgba(0, 240, 255, 0.1);
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 240, 255, 0.4);
    }
    
    .card a:hover {
        background: rgba(0, 240, 255, 0.05);
        color: var(--cyan);
    }
    
    @media (max-width: 768px) {
        .grid-3 > div:first-child {
            grid-column: span 1 !important;
        }
    }
</style>
<?php View::endSection(); ?>
