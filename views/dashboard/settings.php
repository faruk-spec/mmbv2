<?php use Core\View; use Core\Helpers; use Core\Auth; $currentUser = Auth::user(); ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="margin-bottom: 16px;">
    <h1 style="font-size: 1rem; margin-bottom: 4px; font-weight: 700;">Settings</h1>
    <p style="color: var(--text-secondary); font-size: 0.8rem;">Customize your experience and preferences</p>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success" style="margin-bottom: 12px; padding: 10px; background: rgba(0, 255, 136, 0.1); border: 1px solid var(--green); border-radius: 6px; color: var(--green); font-size: 0.8rem;">
        ✓ <?= View::e(Helpers::getFlash('success')) ?>
    </div>
<?php endif; ?>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error" style="margin-bottom: 12px; padding: 10px; background: rgba(255, 107, 107, 0.1); border: 1px solid var(--red); border-radius: 6px; color: var(--red); font-size: 0.8rem;">
        ✗ <?= View::e(Helpers::getFlash('error')) ?>
    </div>
<?php endif; ?>

<div class="grid grid-2" style="gap: 16px;">
    <!-- Theme Preferences -->
    <div class="card" style="border-radius: 10px; overflow: hidden; border: 1px solid var(--border-color);">
        <div class="card-header" style="background: linear-gradient(135deg, rgba(0, 240, 255, 0.1) 0%, rgba(255, 46, 196, 0.1) 100%); border-bottom: 1px solid var(--border-color); padding: 12px;">
            <h3 class="card-title" style="font-size: 0.9rem; display: flex; align-items: center; gap: 6px; margin: 0; font-weight: 600;">
                🎨 Theme
            </h3>
        </div>
        
        <div style="padding: 16px;">
            <form method="POST" action="/settings" id="themeForm">
                <?= \Core\Security::csrfField() ?>
                <input type="hidden" name="setting_type" value="theme">
                
                <div style="margin-bottom: 12px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.85rem;">Choose Theme</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <div class="theme-option" data-theme="dark" style="padding: 12px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer; text-align: center; transition: all 0.3s ease;">
                            <div style="font-size: 20px; margin-bottom: 4px;">🌙</div>
                            <div style="font-weight: 600; font-size: 0.85rem;">Dark</div>
                        </div>
                        
                        <div class="theme-option" data-theme="light" style="padding: 12px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer; text-align: center; transition: all 0.3s ease;">
                            <div style="font-size: 20px; margin-bottom: 4px;">☀️</div>
                            <div style="font-weight: 600; font-size: 0.85rem;">Light</div>
                        </div>
                    </div>
                    <input type="hidden" name="theme" id="themeInput" value="">
                </div>
                
                <button type="submit" class="btn btn-primary" style="padding: 8px 16px; background: linear-gradient(135deg, var(--cyan), var(--magenta)); border: none; border-radius: 6px; color: #06060a; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 0.85rem; width: 100%;">
                    Save Theme
                </button>
            </form>
        </div>
    </div>
    
    <!-- Notification Preferences -->
    <div class="card" style="border-radius: 10px; overflow: hidden; border: 1px solid var(--border-color);">
        <div class="card-header" style="background: linear-gradient(135deg, rgba(153, 69, 255, 0.1) 0%, rgba(255, 46, 196, 0.1) 100%); border-bottom: 1px solid var(--border-color); padding: 12px;">
            <h3 class="card-title" style="font-size: 0.9rem; display: flex; align-items: center; gap: 6px; margin: 0; font-weight: 600;">
                🔔 Notifications
            </h3>
        </div>
        
        <div style="padding: 16px;">
            <form method="POST" action="/settings">
                <?= \Core\Security::csrfField() ?>
                <input type="hidden" name="setting_type" value="notifications">
                
                <div style="margin-bottom: 8px;">
                    <label style="display: flex; align-items: center; justify-content: space-between; padding: 10px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 6px; cursor: pointer; font-size: 0.85rem;">
                        <span>📧 Email Notifications</span>
                        <input type="checkbox" name="email_notifications" value="1" checked style="width: 16px; height: 16px; cursor: pointer;">
                    </label>
                </div>
                
                <div style="margin-bottom: 8px;">
                    <label style="display: flex; align-items: center; justify-content: space-between; padding: 10px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 6px; cursor: pointer; font-size: 0.85rem;">
                        <span>🛡️ Security Alerts</span>
                        <input type="checkbox" name="security_alerts" value="1" checked style="width: 16px; height: 16px; cursor: pointer;">
                    </label>
                </div>
                
                <div style="margin-bottom: 12px;">
                    <label style="display: flex; align-items: center; justify-content: space-between; padding: 10px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 6px; cursor: pointer; font-size: 0.85rem;">
                        <span>📦 Product Updates</span>
                        <input type="checkbox" name="product_updates" value="1" checked style="width: 16px; height: 16px; cursor: pointer;">
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary" style="padding: 8px 16px; background: linear-gradient(135deg, var(--cyan), var(--magenta)); border: none; border-radius: 6px; color: #06060a; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 0.85rem; width: 100%;">
                    Save Preferences
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Display & Project Settings -->
<div class="grid grid-2" style="gap: 16px; margin-top: 16px;">
    <!-- Display Preferences -->
    <div class="card" style="border-radius: 10px; overflow: hidden; border: 1px solid var(--border-color);">
        <div class="card-header" style="background: linear-gradient(135deg, rgba(0, 255, 136, 0.1) 0%, rgba(0, 240, 255, 0.1) 100%); border-bottom: 1px solid var(--border-color); padding: 12px;">
            <h3 class="card-title" style="font-size: 0.9rem; display: flex; align-items: center; gap: 6px; margin: 0; font-weight: 600;">
                💻 Display
            </h3>
        </div>
        
        <div style="padding: 16px;">
            <form method="POST" action="/settings">
                <?= \Core\Security::csrfField() ?>
                <input type="hidden" name="setting_type" value="display">
                
                <div style="margin-bottom: 12px;">
                    <label class="form-label" for="items_per_page" style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.85rem;">
                        Items Per Page
                    </label>
                    <select id="items_per_page" name="items_per_page" class="form-input" style="width: 100%; padding: 8px 12px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-primary); font-size: 0.85rem;">
                        <option value="10">10 items</option>
                        <option value="20" selected>20 items</option>
                        <option value="30">30 items</option>
                        <option value="50">50 items</option>
                    </select>
                </div>
                
                <div style="margin-bottom: 12px;">
                    <label class="form-label" for="date_format" style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.85rem;">
                        Date Format
                    </label>
                    <select id="date_format" name="date_format" class="form-input" style="width: 100%; padding: 8px 12px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-primary); font-size: 0.85rem;">
                        <option value="M d, Y">Jan 01, 2024</option>
                        <option value="d/m/Y">01/01/2024</option>
                        <option value="Y-m-d">2024-01-01</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary" style="padding: 8px 16px; background: linear-gradient(135deg, var(--cyan), var(--magenta)); border: none; border-radius: 6px; color: #06060a; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 0.85rem; width: 100%;">
                    Save Settings
                </button>
            </form>
        </div>
    </div>
    
    <!-- Project Settings -->
    <div class="card" style="border-radius: 10px; overflow: hidden; border: 1px solid var(--border-color);">
        <div class="card-header" style="background: linear-gradient(135deg, rgba(255, 170, 0, 0.1) 0%, rgba(255, 46, 196, 0.1) 100%); border-bottom: 1px solid var(--border-color); padding: 12px;">
            <h3 class="card-title" style="font-size: 0.9rem; display: flex; align-items: center; gap: 6px; margin: 0; font-weight: 600;">
                📁 Projects
            </h3>
        </div>
        
        <div style="padding: 16px;">
            <form method="POST" action="/settings">
                <?= \Core\Security::csrfField() ?>
                <input type="hidden" name="setting_type" value="projects">
                
                <div style="margin-bottom: 8px;">
                    <label style="display: flex; align-items: center; justify-content: space-between; padding: 10px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 6px; cursor: pointer; font-size: 0.85rem;">
                        <span>💾 Auto-Save</span>
                        <input type="checkbox" name="auto_save_enabled" value="1" checked style="width: 16px; height: 16px; cursor: pointer;">
                    </label>
                </div>
                
                <div style="margin-bottom: 12px;">
                    <label class="form-label" for="default_project_view" style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.85rem;">
                        Default View
                    </label>
                    <select id="default_project_view" name="default_project_view" class="form-input" style="width: 100%; padding: 8px 12px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-primary); font-size: 0.85rem;">
                        <option value="grid">Grid View</option>
                        <option value="list">List View</option>
                        <option value="compact">Compact View</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary" style="padding: 8px 16px; background: linear-gradient(135deg, var(--cyan), var(--magenta)); border: none; border-radius: 6px; color: #06060a; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 0.85rem; width: 100%;">
                    Save Settings
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    .theme-option {
        position: relative;
    }
    
    .theme-option:hover {
        border-color: var(--cyan);
        transform: translateY(-2px);
    }
    
    .theme-option.active {
        border-color: var(--cyan);
        background: rgba(0, 240, 255, 0.05);
    }
    
    .theme-option.active::after {
        content: '✓';
        position: absolute;
        top: 6px;
        right: 6px;
        width: 16px;
        height: 16px;
        background: var(--cyan);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: #06060a;
    }
    
    .form-input:focus, select:focus {
        outline: none;
        border-color: var(--cyan);
        box-shadow: 0 0 0 2px rgba(0, 240, 255, 0.1);
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 240, 255, 0.4);
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
