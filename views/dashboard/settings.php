<?php use Core\View; use Core\Helpers; use Core\Auth; $currentUser = Auth::user(); ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2rem; margin-bottom: 8px; background: linear-gradient(135deg, var(--cyan), var(--magenta)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Settings</h1>
    <p style="color: var(--text-secondary); font-size: 1rem;">Customize your experience and preferences</p>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success" style="margin-bottom: 25px; padding: 16px; background: rgba(0, 255, 136, 0.1); border: 1px solid var(--green); border-radius: 12px; color: var(--green);">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <?= View::e(Helpers::getFlash('success')) ?>
    </div>
<?php endif; ?>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error" style="margin-bottom: 25px; padding: 16px; background: rgba(255, 107, 107, 0.1); border: 1px solid var(--red); border-radius: 12px; color: var(--red);">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
        <?= View::e(Helpers::getFlash('error')) ?>
    </div>
<?php endif; ?>

<div class="grid grid-2" style="gap: 24px;">
    <!-- Theme Preferences -->
    <div class="card" style="border-radius: 16px; overflow: hidden; border: 1px solid var(--border-color);">
        <div class="card-header" style="background: linear-gradient(135deg, rgba(0, 240, 255, 0.1) 0%, rgba(255, 46, 196, 0.1) 100%); border-bottom: 1px solid var(--border-color); padding: 24px;">
            <h3 class="card-title" style="font-size: 1.3rem; display: flex; align-items: center; gap: 10px; margin: 0;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                    <circle cx="12" cy="12" r="5"></circle>
                    <line x1="12" y1="1" x2="12" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="23"></line>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                    <line x1="1" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="12" x2="23" y2="12"></line>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                </svg>
                Theme Preferences
            </h3>
        </div>
        
        <div style="padding: 30px;">
            <form method="POST" action="/settings" id="themeForm">
                <?= \Core\Security::csrfField() ?>
                <input type="hidden" name="setting_type" value="theme">
                
                <div style="margin-bottom: 24px;">
                    <label style="display: block; margin-bottom: 12px; font-weight: 600; color: var(--text-primary); font-size: 0.95rem;">Choose Theme</label>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px;">
                        <div class="theme-option" data-theme="dark" style="padding: 20px; border: 2px solid var(--border-color); border-radius: 12px; cursor: pointer; text-align: center; transition: all 0.3s ease;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2" style="margin-bottom: 10px;">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                            </svg>
                            <div style="font-weight: 600; color: var(--text-primary);">Dark</div>
                            <small style="color: var(--text-secondary); font-size: 0.85rem;">Low light</small>
                        </div>
                        
                        <div class="theme-option" data-theme="light" style="padding: 20px; border: 2px solid var(--border-color); border-radius: 12px; cursor: pointer; text-align: center; transition: all 0.3s ease;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2" style="margin-bottom: 10px;">
                                <circle cx="12" cy="12" r="5"></circle>
                                <line x1="12" y1="1" x2="12" y2="3"></line>
                                <line x1="12" y1="21" x2="12" y2="23"></line>
                                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                                <line x1="1" y1="12" x2="3" y2="12"></line>
                                <line x1="21" y1="12" x2="23" y2="12"></line>
                                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                            </svg>
                            <div style="font-weight: 600; color: var(--text-primary);">Light</div>
                            <small style="color: var(--text-secondary); font-size: 0.85rem;">Bright mode</small>
                        </div>
                    </div>
                    <input type="hidden" name="theme" id="themeInput" value="">
                </div>
                
                <button type="submit" class="btn btn-primary" style="padding: 12px 32px; background: linear-gradient(135deg, var(--cyan), var(--magenta)); border: none; border-radius: 10px; color: #06060a; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 1rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Save Theme
                </button>
            </form>
        </div>
    </div>
    
    <!-- Notification Preferences -->
    <div class="card" style="border-radius: 16px; overflow: hidden; border: 1px solid var(--border-color);">
        <div class="card-header" style="background: linear-gradient(135deg, rgba(153, 69, 255, 0.1) 0%, rgba(255, 46, 196, 0.1) 100%); border-bottom: 1px solid var(--border-color); padding: 24px;">
            <h3 class="card-title" style="font-size: 1.3rem; display: flex; align-items: center; gap: 10px; margin: 0;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                Notification Preferences
            </h3>
        </div>
        
        <div style="padding: 30px;">
            <form method="POST" action="/settings">
                <?= \Core\Security::csrfField() ?>
                <input type="hidden" name="setting_type" value="notifications">
                
                <div style="margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; justify-content: space-between; padding: 16px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 10px; cursor: pointer; transition: all 0.3s ease;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <div>
                                <div style="font-weight: 600; color: var(--text-primary);">Email Notifications</div>
                                <small style="color: var(--text-secondary);">Receive updates via email</small>
                            </div>
                        </div>
                        <input type="checkbox" name="email_notifications" value="1" checked style="width: 20px; height: 20px; cursor: pointer;">
                    </label>
                </div>
                
                <div style="margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; justify-content: space-between; padding: 16px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 10px; cursor: pointer; transition: all 0.3s ease;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--magenta)" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            <div>
                                <div style="font-weight: 600; color: var(--text-primary);">Security Alerts</div>
                                <small style="color: var(--text-secondary);">Important security notifications</small>
                            </div>
                        </div>
                        <input type="checkbox" name="security_alerts" value="1" checked style="width: 20px; height: 20px; cursor: pointer;">
                    </label>
                </div>
                
                <div style="margin-bottom: 30px;">
                    <label style="display: flex; align-items: center; justify-content: space-between; padding: 16px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 10px; cursor: pointer; transition: all 0.3s ease;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <div>
                                <div style="font-weight: 600; color: var(--text-primary);">Product Updates</div>
                                <small style="color: var(--text-secondary);">News and feature announcements</small>
                            </div>
                        </div>
                        <input type="checkbox" name="product_updates" value="1" checked style="width: 20px; height: 20px; cursor: pointer;">
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary" style="padding: 12px 32px; background: linear-gradient(135deg, var(--cyan), var(--magenta)); border: none; border-radius: 10px; color: #06060a; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 1rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Save Preferences
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Display Preferences -->
<div class="card" style="border-radius: 16px; overflow: hidden; border: 1px solid var(--border-color); margin-top: 24px;">
    <div class="card-header" style="background: linear-gradient(135deg, rgba(0, 255, 136, 0.1) 0%, rgba(0, 240, 255, 0.1) 100%); border-bottom: 1px solid var(--border-color); padding: 24px;">
        <h3 class="card-title" style="font-size: 1.3rem; display: flex; align-items: center; gap: 10px; margin: 0;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                <line x1="8" y1="21" x2="16" y2="21"></line>
                <line x1="12" y1="17" x2="12" y2="21"></line>
            </svg>
            Display Preferences
        </h3>
    </div>
    
    <div style="padding: 30px;">
        <form method="POST" action="/settings">
            <?= \Core\Security::csrfField() ?>
            <input type="hidden" name="setting_type" value="display">
            
            <div class="grid grid-2" style="gap: 24px;">
                <div>
                    <label class="form-label" for="items_per_page" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary); font-size: 0.95rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                        Items Per Page
                    </label>
                    <select id="items_per_page" name="items_per_page" class="form-input" style="width: 100%; padding: 12px 16px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary); font-size: 0.95rem;">
                        <option value="10">10 items</option>
                        <option value="20" selected>20 items</option>
                        <option value="30">30 items</option>
                        <option value="50">50 items</option>
                    </select>
                </div>
                
                <div>
                    <label class="form-label" for="date_format" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary); font-size: 0.95rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        Date Format
                    </label>
                    <select id="date_format" name="date_format" class="form-input" style="width: 100%; padding: 12px 16px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary); font-size: 0.95rem;">
                        <option value="M d, Y">Jan 01, 2024</option>
                        <option value="d/m/Y">01/01/2024</option>
                        <option value="Y-m-d">2024-01-01</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="padding: 12px 32px; background: linear-gradient(135deg, var(--cyan), var(--magenta)); border: none; border-radius: 10px; color: #06060a; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 1rem; margin-top: 24px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                Save Settings
            </button>
        </form>
    </div>
</div>

<style>
    .theme-option {
        position: relative;
    }
    
    .theme-option:hover {
        border-color: var(--cyan);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 240, 255, 0.2);
    }
    
    .theme-option.active {
        border-color: var(--cyan);
        background: rgba(0, 240, 255, 0.05);
    }
    
    .theme-option.active::after {
        content: '';
        position: absolute;
        top: 10px;
        right: 10px;
        width: 20px;
        height: 20px;
        background: var(--cyan);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .form-input:focus, select:focus {
        outline: none;
        border-color: var(--cyan);
        box-shadow: 0 0 0 3px rgba(0, 240, 255, 0.1);
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 240, 255, 0.4);
    }
    
    label:has(input[type="checkbox"]):hover {
        border-color: var(--cyan);
        background: rgba(0, 240, 255, 0.03);
    }
    
    @media (max-width: 768px) {
        .grid-2 {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Theme selection
    const themeOptions = document.querySelectorAll('.theme-option');
    const themeInput = document.getElementById('themeInput');
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
    
    // Set initial active theme
    themeOptions.forEach(option => {
        if (option.dataset.theme === currentTheme) {
            option.classList.add('active');
            themeInput.value = currentTheme;
        }
        
        option.addEventListener('click', function() {
            themeOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            themeInput.value = this.dataset.theme;
            
            // Preview theme change
            document.documentElement.setAttribute('data-theme', this.dataset.theme);
            localStorage.setItem('theme', this.dataset.theme);
        });
    });
});
</script>
<?php View::endSection(); ?>
