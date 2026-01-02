<?php use Core\View; use Core\Helpers; use Core\Auth; $currentUser = Auth::user(); ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div class="dashboard-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 1rem; font-weight: 700; margin-bottom: 8px;">Welcome back, <?= View::e($currentUser['name']) ?>! 👋</h1>
            <p style="color: var(--text-secondary); font-size: 0.875rem;">Discover powerful tools designed to streamline your workflow.</p>
        </div>
    </div>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success" style="margin-bottom: 30px;"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>

<!-- Applications Grid -->
<div class="card" style="border-radius: 12px; overflow: hidden; margin-bottom: 24px;">
    <div class="card-header" style="background: linear-gradient(135deg, rgba(0, 240, 255, 0.1) 0%, rgba(255, 46, 196, 0.1) 100%); border-bottom: 1px solid var(--border-color); padding: 16px;">
        <h3 class="card-title" style="font-size: 0.95rem; display: flex; align-items: center; gap: 8px; font-weight: 600;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
            Your Applications
        </h3>
    </div>
    
    <div style="padding: 20px;">
        <?php if (empty($projects)): ?>
            <p style="color: var(--text-secondary); text-align: center; padding: 32px 16px; font-size: 0.875rem;">No applications available</p>
        <?php else: ?>
            <div class="applications-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px;">
                <?php foreach ($projects as $key => $project): ?>
                    <a href="<?= $project['url'] ?>" class="application-card" style="display: block; background: var(--bg-secondary); border-radius: 10px; border: 1px solid var(--border-color); padding: 16px; transition: all 0.3s ease; text-align: center;">
                        <div style="width: 48px; height: 48px; background: <?= $project['color'] ?>20; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="<?= $project['color'] ?>" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                            </svg>
                        </div>
                        <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 6px;"><?= View::e($project['name']) ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary); line-height: 1.4;"><?= View::e($project['description']) ?></div>
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border-color);">
                            <button style="width: 100%; padding: 8px; background: <?= $project['color'] ?>; color: #06060a; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 0.85rem;">
                                Access Application
                            </button>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Account Information -->
<div class="card collapsible-section" style="border-radius: 16px; overflow: hidden; margin-bottom: 30px;">
    <div class="card-header collapsible-header" style="background: linear-gradient(135deg, rgba(0, 255, 136, 0.1) 0%, rgba(0, 240, 255, 0.1) 100%); border-bottom: 1px solid var(--border-color); padding: 20px; cursor: pointer;">
        <h3 class="card-title" style="font-size: 1.3rem; display: flex; align-items: center; gap: 10px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>
            Account Information
            <svg class="chevron-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2" style="margin-left: auto; transition: transform 0.3s ease;">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </h3>
    </div>
    
    <div class="collapsible-content" style="padding: 24px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div style="padding: 16px; background: var(--bg-secondary); border-radius: 10px; border: 1px solid var(--border-color);">
                <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 8px;">Email Address</div>
                <div style="font-size: 1rem; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    <?= View::e($currentUser['email']) ?>
                </div>
            </div>
            
            <div style="padding: 16px; background: var(--bg-secondary); border-radius: 10px; border: 1px solid var(--border-color);">
                <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 8px;">Account Role</div>
                <div style="font-size: 1rem; font-weight: 500; display: flex; align-items: center; gap: 8px; text-transform: capitalize;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--magenta)" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                        <path d="M2 17l10 5 10-5"></path>
                        <path d="M2 12l10 5 10-5"></path>
                    </svg>
                    <?= View::e($currentUser['role']) ?>
                </div>
            </div>
            
            <div style="padding: 16px; background: var(--bg-secondary); border-radius: 10px; border: 1px solid var(--border-color);">
                <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 8px;">Account Status</div>
                <div style="font-size: 1rem; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span style="color: var(--green);">Active</span>
                </div>
            </div>
            
            <div style="padding: 16px; background: var(--bg-secondary); border-radius: 10px; border: 1px solid var(--border-color);">
                <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 8px;">Member Since</div>
                <div style="font-size: 1rem; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <?= date('M d, Y', strtotime($currentUser['created_at'])) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .application-card:hover {
        background: var(--bg-card) !important;
        border-color: var(--cyan) !important;
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0, 240, 255, 0.3);
    }
    
    .application-card:hover button {
        transform: scale(1.05);
    }
    
    .quick-action-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 18px;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        color: var(--text-primary);
    }
    
    .quick-action-btn:hover {
        background: var(--bg-card);
        border-color: var(--cyan);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 240, 255, 0.2);
    }
    
    /* Collapsible section styles */
    .collapsible-content {
        max-height: 1000px;
        overflow: hidden;
        opacity: 1;
        transition: max-height 0.4s ease, opacity 0.4s ease;
    }
    
    .collapsible-section.collapsed .collapsible-content {
        max-height: 0;
        opacity: 0;
    }
    
    .collapsible-section:not(.collapsed) .chevron-icon {
        transform: rotate(180deg);
    }
    
    @media (max-width: 768px) {
        .applications-grid {
            grid-template-columns: 1fr !important;
        }
        
        .quick-actions-grid {
            grid-template-columns: 1fr !important;
        }
        
        /* Make sections collapsible on mobile */
        .collapsible-section .collapsible-header {
            cursor: pointer;
        }
    }
    
    @media (min-width: 769px) {
        /* On desktop, hide chevron icons and ensure content is always visible */
        .collapsible-header .chevron-icon {
            display: none;
        }
        
        .collapsible-header {
            cursor: default !important;
        }
    }
</style>

<script>
// Collapsible sections for mobile only
(function() {
    function isMobile() {
        return window.innerWidth <= 768;
    }
    
    // Store click handlers to prevent duplicates
    const clickHandlers = new WeakMap();
    
    function initCollapsible() {
        const collapsibleSections = document.querySelectorAll('.collapsible-section');
        
        collapsibleSections.forEach(section => {
            const header = section.querySelector('.collapsible-header');
            
            // Set initial state for mobile
            if (isMobile()) {
                section.classList.add('collapsed');
            } else {
                section.classList.remove('collapsed');
            }
            
            // Only add event listener if not already added
            if (!clickHandlers.has(header)) {
                const handler = function() {
                    // Only allow toggling on mobile
                    if (isMobile()) {
                        section.classList.toggle('collapsed');
                    }
                };
                clickHandlers.set(header, handler);
                header.addEventListener('click', handler);
            }
        });
    }
    
    // Initialize on page load
    initCollapsible();
    
    // Re-initialize on window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            initCollapsible();
        }, 250);
    });
})();
</script>
<?php View::endSection(); ?>
