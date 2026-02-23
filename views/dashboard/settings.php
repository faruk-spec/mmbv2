<?php use Core\View; use Core\Helpers; use Core\Auth; $currentUser = Auth::user(); ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
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
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="13.5" cy="6.5" r=".5"/>
                    <circle cx="17.5" cy="10.5" r=".5"/>
                    <circle cx="8.5" cy="7.5" r=".5"/>
                    <circle cx="6.5" cy="12.5" r=".5"/>
                    <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/>
                </svg>
                Theme
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
                            <div style="margin-bottom: 4px;">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                                </svg>
                            </div>
                            <div style="font-weight: 600; font-size: 0.85rem;">Dark</div>
                        </div>
                        
                        <div class="theme-option" data-theme="light" style="padding: 12px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer; text-align: center; transition: all 0.3s ease;">
                            <div style="margin-bottom: 4px;">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="5"/>
                                    <line x1="12" y1="1" x2="12" y2="3"/>
                                    <line x1="12" y1="21" x2="12" y2="23"/>
                                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                                    <line x1="1" y1="12" x2="3" y2="12"/>
                                    <line x1="21" y1="12" x2="23" y2="12"/>
                                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                                </svg>
                            </div>
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
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                Notifications
            </h3>
        </div>
        
        <div style="padding: 16px;">
            <form method="POST" action="/settings">
                <?= \Core\Security::csrfField() ?>
                <input type="hidden" name="setting_type" value="notifications">
                
                <div style="margin-bottom: 8px;">
                    <label style="display: flex; align-items: center; justify-content: space-between; padding: 10px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 6px; cursor: pointer; font-size: 0.85rem;">
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            Email Notifications
                        </span>
                        <input type="checkbox" name="email_notifications" value="1" checked style="width: 16px; height: 16px; cursor: pointer;">
                    </label>
                </div>
                
                <div style="margin-bottom: 8px;">
                    <label style="display: flex; align-items: center; justify-content: space-between; padding: 10px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 6px; cursor: pointer; font-size: 0.85rem;">
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            Security Alerts
                        </span>
                        <input type="checkbox" name="security_alerts" value="1" checked style="width: 16px; height: 16px; cursor: pointer;">
                    </label>
                </div>
                
                <div style="margin-bottom: 12px;">
                    <label style="display: flex; align-items: center; justify-content: space-between; padding: 10px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 6px; cursor: pointer; font-size: 0.85rem;">
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                            </svg>
                            Product Updates
                        </span>
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
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
                Display
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
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                </svg>
                Projects
            </h3>
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

<!-- ── Timezone Card ──────────────────────────────────────────────────────── -->
<div style="margin-top:16px;">
    <div class="card" style="border-radius:10px;border:1px solid var(--border-color);overflow:hidden;">
        <div class="card-header" style="background:linear-gradient(135deg,rgba(0,255,136,.1),rgba(0,240,255,.1));border-bottom:1px solid var(--border-color);padding:12px;">
            <h3 class="card-title" style="font-size:.9rem;display:flex;align-items:center;gap:6px;margin:0;font-weight:600;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                Timezone
            </h3>
        </div>
        <div style="padding:16px;">
            <form method="POST" action="/settings">
                <?= \Core\Security::csrfField() ?>
                <input type="hidden" name="setting_type" value="timezone">
                <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:8px;color:var(--text-secondary);">Your Display Timezone</label>
                <select name="timezone" style="width:100%;padding:9px 12px;border-radius:7px;border:1px solid var(--border-color);background:var(--bg-secondary);color:var(--text-primary);font-size:.85rem;margin-bottom:12px;">
                    <?php foreach (($tzGroups ?? \Core\Timezone::getGroupedTimezones()) as $region => $zones): ?>
                    <optgroup label="<?= htmlspecialchars($region, ENT_QUOTES, 'UTF-8') ?>">
                        <?php foreach ($zones as $tz): ?>
                        <option value="<?= htmlspecialchars($tz, ENT_QUOTES, 'UTF-8') ?>"
                            <?= ($userTimezone ?? 'UTC') === $tz ? 'selected' : '' ?>>
                            <?= htmlspecialchars(str_replace('_', ' ', $tz), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endforeach; ?>
                </select>
                <p style="font-size:.75rem;color:var(--text-secondary);margin-bottom:12px;">
                    Dates and times throughout the QR dashboard will be shown in your selected timezone.
                    The system timezone is <code style="background:rgba(0,240,255,.1);padding:1px 5px;border-radius:3px;"><?= htmlspecialchars(\Core\Timezone::getSystemTz(), ENT_QUOTES, 'UTF-8') ?></code>.
                </p>
                <button type="submit" style="width:100%;padding:9px;background:linear-gradient(135deg,var(--green),var(--cyan));border:none;border-radius:7px;color:#000;font-weight:700;font-size:.85rem;cursor:pointer;">
                    Save Timezone
                </button>
            </form>
        </div>
    </div>
</div>

<?php View::endSection(); ?>
