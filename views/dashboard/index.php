<?php use Core\View; use Core\Helpers; use Core\Auth; $currentUser = Auth::user(); ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success animate-fade-in">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span><?= View::e(Helpers::getFlash('success')) ?></span>
    </div>
<?php endif; ?>

<!-- Welcome Section with AI Insights -->
<div class="card mb-xl animate-fade-in" style="background: linear-gradient(135deg, rgba(0, 217, 255, 0.05) 0%, rgba(0, 102, 255, 0.05) 100%); border: 1px solid rgba(0, 217, 255, 0.2);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--space-lg);">
        <div>
            <h2 class="mb-sm" style="font-size: var(--font-size-2xl); font-weight: var(--font-bold);">
                Welcome back, <?= View::e($currentUser['username']) ?>
            </h2>
            <p class="text-secondary" style="font-size: var(--font-size-sm);">
                Here's what's happening with your applications today
            </p>
        </div>
        <div style="display: flex; gap: var(--space-md);">
            <a href="/profile" class="btn btn-secondary btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Profile
            </a>
            <a href="/settings" class="btn btn-secondary btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M12 1v6m0 6v6M5.64 5.64l4.24 4.24m4.24 4.24l4.24 4.24M1 12h6m6 0h6M5.64 18.36l4.24-4.24m4.24-4.24l4.24-4.24"/>
                </svg>
                Settings
            </a>
        </div>
    </div>
</div>

<!-- Applications Grid -->
<div class="card animate-fade-in" style="animation-delay: 0.1s;">
    <div class="card-header">
        <h3 class="card-title" style="display: flex; align-items: center; gap: var(--space-sm);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
            Your Applications
        </h3>
    </div>
    
    <div class="card-body" style="padding: var(--space-xl);">
        <?php if (empty($projects)): ?>
            <div style="text-align: center; padding: var(--space-3xl);">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--text-tertiary)" stroke-width="1.5" style="margin: 0 auto var(--space-lg);">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                </svg>
                <p class="text-secondary">No applications available</p>
            </div>
        <?php else: ?>
            <div class="applications-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: var(--space-lg);">
                <?php foreach ($projects as $key => $project): ?>
                    <a href="<?= $project['url'] ?>" class="application-card card-interactive" style="display: block; background: var(--bg-secondary); border-radius: var(--radius-lg); border: 1px solid var(--border-color); padding: var(--space-lg); transition: all var(--transition); text-align: center; text-decoration: none;">
                        <div style="width: 56px; height: 56px; background: <?= $project['color'] ?>20; border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-lg);">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="<?= $project['color'] ?>" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                            </svg>
                        </div>
                        <div class="font-semibold mb-xs" style="font-size: var(--font-size-lg); color: var(--text-primary);"><?= View::e($project['name']) ?></div>
                        <div class="text-secondary mb-lg" style="font-size: var(--font-size-sm); line-height: var(--leading-normal);"><?= View::e($project['description']) ?></div>
                        <div style="padding-top: var(--space-md); border-top: 1px solid var(--divider-color);">
                            <button style="width: 100%; padding: var(--space-sm); background: <?= $project['color'] ?>; color: var(--text-inverse); border: none; border-radius: var(--radius-md); font-weight: var(--font-semibold); cursor: pointer; transition: all var(--transition-fast); font-size: var(--font-size-sm); font-family: inherit;">
                                Launch Application
                            </button>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .application-card:hover {
        background: var(--bg-elevated) !important;
        border-color: var(--cyan) !important;
        transform: translateY(-3px);
        box-shadow: var(--shadow-glow);
    }
    
    .application-card:hover button {
        transform: scale(1.02);
        filter: brightness(1.1);
    }
    
    .quick-action-btn {
        display: flex;
        align-items: center;
        gap: var(--space-md);
        padding: var(--space-lg);
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        transition: all var(--transition-fast);
        cursor: pointer;
        text-decoration: none;
        color: var(--text-primary);
    }
    
    .quick-action-btn:hover {
        background: var(--bg-elevated);
        border-color: var(--cyan);
        transform: translateY(-2px);
        box-shadow: 0 0 0 1px var(--cyan);
    }
    
    /* Collapsible section styles */
    .collapsible-content {
        max-height: 1000px;
        overflow: hidden;
        opacity: 1;
        transition: max-height var(--transition-slow), opacity var(--transition-slow);
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
