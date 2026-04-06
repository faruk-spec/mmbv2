<?php 
use Core\Auth;
use Core\Database;

$user = Auth::user();
$isLoggedIn = $user !== null;

// Fetch navbar settings from database with error handling
$navbarSettings = null;
$settingsFetchError = null;
try {
    $db = Database::getInstance();
    $navbarSettings = $db->fetch("SELECT * FROM navbar_settings WHERE id = 1");
    
    // Debug: Log if settings were fetched
    if (defined('APP_DEBUG') && APP_DEBUG) {
        error_log("Navbar settings fetched: " . ($navbarSettings ? json_encode($navbarSettings) : 'NULL'));
    }
} catch (\Exception $e) {
    // Log error and use defaults
    $settingsFetchError = $e->getMessage();
    error_log("Navbar settings fetch error: " . $e->getMessage());
    error_log("Error trace: " . $e->getTraceAsString());
}

// Set defaults if no settings exist or fetch failed
if (!$navbarSettings) {
    // Define APP_NAME if not defined (only when using defaults)
    if (!defined('APP_NAME')) {
        define('APP_NAME', 'MyMultiBranch');
    }
    
    $navbarSettings = [
        'logo_type' => 'text',
        'logo_text' => APP_NAME,
        'logo_image_url' => null,
        'show_home_link' => 1,
        'show_dashboard_link' => 1,
        'show_profile_link' => 1,
        'show_admin_link' => 1,
        'show_projects_dropdown' => 1,
        'show_theme_toggle' => 1,
        'default_theme' => 'dark',
        'navbar_bg_color' => null,
        'navbar_text_color' => null,
        'navbar_border_color' => null,
        'custom_css' => null,
        'custom_links' => null
    ];
} else {
    // Ensure all expected keys exist with defaults for backwards compatibility
    // Define APP_NAME if not defined (for merge defaults only)
    if (!defined('APP_NAME')) {
        define('APP_NAME', 'MyMultiBranch');
    }
    
    $navbarSettings = array_merge([
        'logo_type' => 'text',
        'logo_text' => APP_NAME,  // This is only used if logo_text is missing from DB
        'logo_image_url' => null,
        'show_home_link' => 1,
        'show_dashboard_link' => 1,
        'show_profile_link' => 1,
        'show_admin_link' => 1,
        'show_projects_dropdown' => 1,
        'show_theme_toggle' => 1,
        'default_theme' => 'dark',
        'navbar_bg_color' => null,
        'navbar_text_color' => null,
        'navbar_border_color' => null,
        'custom_css' => null,
        'custom_links' => null
    ], $navbarSettings);
}

// Decode custom links if exists
$customLinks = [];
if (!empty($navbarSettings['custom_links'])) {
    $customLinks = json_decode($navbarSettings['custom_links'], true) ?? [];
}
?>
<!-- Universal Navbar Component -->
<!-- Debug: Settings loaded from <?= $navbarSettings ? 'DATABASE' : 'DEFAULTS' ?> 
<?php if ($settingsFetchError): ?>
Error: <?= htmlspecialchars($settingsFetchError) ?>
<?php endif; ?>
Logo: <?= htmlspecialchars($navbarSettings['logo_text'] ?? 'N/A') ?> -->
<?php 
// Build header inline styles
$headerStyles = [];
if (!empty($navbarSettings['navbar_bg_color'])) {
    $headerStyles[] = 'background-color: ' . htmlspecialchars($navbarSettings['navbar_bg_color']);
}
if (!empty($navbarSettings['navbar_border_color'])) {
    $headerStyles[] = 'border-bottom-color: ' . htmlspecialchars($navbarSettings['navbar_border_color']);
}
// Add sticky positioning inline if enabled (highest priority)
if (!isset($navbarSettings['navbar_sticky']) || $navbarSettings['navbar_sticky']) {
    $headerStyles[] = 'position: -webkit-sticky';
    $headerStyles[] = 'position: sticky';
    $headerStyles[] = 'top: 0';
    $headerStyles[] = 'z-index: 9999';
}
$headerStyleAttr = !empty($headerStyles) ? ' style="' . implode('; ', $headerStyles) . ';"' : '';
?>
<?php if (!empty($navbarSettings['custom_css'])): ?>
<!-- Custom CSS from admin settings (admin-controlled, sanitized on save) -->
<style><?= $navbarSettings['custom_css'] ?></style>
<?php endif; ?>
<header class="universal-header"<?= $headerStyleAttr ?>>
    <div class="container header-content">
        <?php if ($navbarSettings['logo_type'] === 'image' && !empty($navbarSettings['logo_image_url'])): ?>
            <a href="/" class="logo">
                <img src="<?= htmlspecialchars($navbarSettings['logo_image_url']) ?>" alt="Logo" style="max-height: 40px;">
            </a>
        <?php else: ?>
            <a href="/" class="logo" <?php if ($navbarSettings['navbar_text_color']): ?>style="color: <?= htmlspecialchars($navbarSettings['navbar_text_color']) ?>;"<?php endif; ?>><?= htmlspecialchars($navbarSettings['logo_text']) ?></a>
        <?php endif; ?>
        
        <nav class="universal-nav" id="mainNav">
            <?php if ($navbarSettings['show_home_link']): ?>
            <a href="/" class="nav-link">Home</a>
            <?php endif; ?>
            
            <!-- Custom Links (moved after Home) -->
            <?php if (!empty($customLinks)): ?>
                <?php 
                // Sort custom links by position
                usort($customLinks, function($a, $b) {
                    return ($a['position'] ?? 0) - ($b['position'] ?? 0);
                });
                foreach ($customLinks as $link): 
                    // Check if this is a dropdown link (has is_dropdown flag)
                    $isDropdown = !empty($link['is_dropdown']) && !empty($link['dropdown_items']);
                ?>
                    <?php if ($isDropdown): ?>
                        <!-- Dropdown Custom Link -->
                        <div class="dropdown nav-item">
                            <button class="nav-link dropdown-toggle">
                                <?php if (!empty($link['icon'])): ?>
                                    <i class="<?= htmlspecialchars($link['icon']) ?>"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($link['title']) ?>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 9l6 6 6-6"/>
                                </svg>
                            </button>
                            <div class="dropdown-menu">
                                <?php foreach ($link['dropdown_items'] as $subLink): ?>
                                    <a href="<?= htmlspecialchars($subLink['url']) ?>" class="dropdown-item">
                                        <?php if (!empty($subLink['icon'])): ?>
                                            <i class="<?= htmlspecialchars($subLink['icon']) ?>"></i>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($subLink['title']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Regular Custom Link -->
                        <a href="<?= htmlspecialchars($link['url']) ?>" class="nav-link">
                            <?php if (!empty($link['icon'])): ?>
                                <i class="<?= htmlspecialchars($link['icon']) ?>"></i>
                            <?php endif; ?>
                            <?= htmlspecialchars($link['title']) ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if ($isLoggedIn): ?>
                <!-- Projects Dropdown -->
                <?php if ($navbarSettings['show_projects_dropdown']): ?>
                <div class="dropdown nav-item" id="projectsDropdown">
                    <button class="nav-link dropdown-toggle">
                        Projects
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>
                    <div class="dropdown-menu">
                        <?php 
                        // Define BASE_PATH if not defined
                        if (!defined('BASE_PATH')) {
                            define('BASE_PATH', dirname(dirname(__DIR__)));
                        }
                        
                        try {
                            // Query DB for enabled projects; fall back to config for projects
                            // that have no DB row yet (e.g. before a migration is run).
                            $db = \Core\Database::getInstance();
                            $allDbRows = $db->fetchAll(
                                "SELECT * FROM home_projects ORDER BY sort_order ASC"
                            );
                            $_navDbKeys = [];
                            $_navProjects = [];
                            foreach ($allDbRows as $_row) {
                                $_navDbKeys[] = $_row['project_key'];
                                if ((int) $_row['is_enabled'] === 1) {
                                    $_navProjects[] = [
                                        'name'  => $_row['name'],
                                        'url'   => $_row['url'] ?? '/projects/' . $_row['project_key'],
                                        'color' => $_row['color'] ?? '#00f0ff',
                                    ];
                                }
                            }
                            // Merge config-only projects not yet in DB
                            $_configProjects = require BASE_PATH . '/config/projects.php';
                            foreach ($_configProjects as $_key => $_cfg) {
                                if (!empty($_cfg['enabled']) && !in_array($_key, $_navDbKeys, true)) {
                                    $_navProjects[] = [
                                        'name'  => $_cfg['name'],
                                        'url'   => $_cfg['url'] ?? '/projects/' . $_key,
                                        'color' => $_cfg['color'] ?? '#00f0ff',
                                    ];
                                }
                            }
                        } catch (\Exception $e) {
                            // DB unavailable — fall back to config (show all config-enabled projects)
                            $_navProjects = [];
                            try {
                                $_configProjects = require BASE_PATH . '/config/projects.php';
                                foreach ($_configProjects as $_key => $_cfg) {
                                    if (!empty($_cfg['enabled'])) {
                                        $_navProjects[] = [
                                            'name'  => $_cfg['name'],
                                            'url'   => $_cfg['url'] ?? '/projects/' . $_key,
                                            'color' => $_cfg['color'] ?? '#00f0ff',
                                        ];
                                    }
                                }
                            } catch (\Exception $e2) {
                                // config also unavailable
                            }
                        }
                        foreach ($_navProjects as $_p):
                        ?>
                            <a href="<?= htmlspecialchars($_p['url']) ?>" class="dropdown-item">
                                <div class="project-icon" style="background: <?= htmlspecialchars($_p['color']) ?>20; color: <?= htmlspecialchars($_p['color']) ?>">
                                    <?= htmlspecialchars(strtoupper(substr($_p['name'], 0, 1))) ?>
                                </div>
                                <?= htmlspecialchars($_p['name']) ?>
                            </a>
                        <?php 
                        endforeach;
                        if (empty($_navProjects)):
                            echo '<div class="dropdown-item" style="color: var(--text-secondary); font-size: 13px;">No projects available</div>';
                        endif;
                        ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($navbarSettings['show_dashboard_link']): ?>
                <a href="/dashboard" class="nav-link">Dashboard</a>
                <?php endif; ?>
                
                <?php if ($navbarSettings['show_admin_link'] && Auth::isAdmin()): ?>
                    <a href="/admin" class="nav-link">Admin</a>
                <?php endif; ?>

            <?php else: ?>
                <a href="/login" class="nav-link">Login</a>
                <a href="/register" class="nav-link">Register</a>
            <?php endif; ?>
        </nav>

        <!-- Always-visible header actions: theme icon, notification, profile, hamburger -->
        <div class="header-end-actions">
            <!-- Theme Toggle (icon only) — visible to all users -->
            <?php if ($navbarSettings['show_theme_toggle']): ?>
            <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">
                <svg id="themeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </button>
            <?php endif; ?>

            <?php if ($isLoggedIn):
                try {
                    $notifUnreadCount = \Core\Notification::getUnreadCount($user['id']);
                } catch (\Exception $e) {
                    $notifUnreadCount = 0;
                }
            ?>
            <!-- Notification Bell -->
            <div class="notif-bell-wrap" id="notifDropdown">
                <button class="notif-bell-btn" id="notifBellBtn" aria-label="Notifications" title="Notifications">
                    <svg class="notif-bell-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <span class="notif-badge<?= $notifUnreadCount > 0 ? ' has-unread' : '' ?>" id="notifBadge"
                          style="<?= $notifUnreadCount > 0 ? '' : 'display:none' ?>">
                        <?= min($notifUnreadCount, 99) ?>
                    </span>
                </button>
                <div class="dropdown-menu notif-panel" id="notifPanel">
                    <div class="notif-panel-header">
                        <div class="notif-panel-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                            </svg>
                            <strong>Notifications</strong>
                        </div>
                        <button class="notif-mark-all-btn" id="notifMarkAll" title="Mark all as read">Mark all read</button>
                    </div>
                    <div class="notif-panel-list" id="notifPanelList">
                        <div style="padding:20px;text-align:center;color:var(--text-secondary);font-size:13px;">Loading…</div>
                    </div>
                    <div class="notif-panel-footer">
                        <a href="/notifications" class="notif-view-all">
                            View All Notifications
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Profile Dropdown — always shown for logged-in users -->
            <div class="dropdown" id="profileDropdown">
                <button class="nav-link dropdown-toggle">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <?php
                    // Show full name: first word only, max 10 characters
                    $displayName = $user['name'] ?? $user['username'] ?? 'User';
                    $firstName = explode(' ', trim($displayName))[0];
                    if (strlen($firstName) > 10) {
                        $firstName = substr($firstName, 0, 10) . '…';
                    }
                    ?>
                    <span class="profile-username"><?= htmlspecialchars($firstName) ?></span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 9l6 6 6-6"/>
                    </svg>
                </button>
                <div class="dropdown-menu">
                    <a href="/profile" class="dropdown-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        Profile
                    </a>
                    <a href="/settings" class="dropdown-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 1v6m0 6v6M5.64 5.64l4.24 4.24m4.24 4.24l4.24 4.24M1 12h6m6 0h6M5.64 18.36l4.24-4.24m4.24-4.24l4.24-4.24"/>
                        </svg>
                        Settings
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item" id="navbarLogoutBtn" onclick="openLogoutModal(event)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Hamburger (mobile only) -->
            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle menu">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>
</header>

<script>
// Universal Navbar JavaScript
(function() {
    // Debug: Log navbar load
    console.log('Universal navbar loaded');
    
    // Theme Toggle
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const themeText = document.getElementById('themeText');
    const html = document.documentElement;
    
    // Load saved theme or use default from settings
    const defaultTheme = '<?= $navbarSettings['default_theme'] ?? 'dark' ?>';
    const savedTheme = localStorage.getItem('theme') || defaultTheme;
    html.setAttribute('data-theme', savedTheme);
    updateThemeUI(savedTheme);
    updateNavbarColors(savedTheme);
    
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeUI(newTheme);
            updateNavbarColors(newTheme);
            
            // Dispatch custom event for other components that need to react to theme changes
            document.dispatchEvent(new CustomEvent('themeChanged', { 
                detail: { theme: newTheme } 
            }));
        });
    }
    
    function updateThemeUI(theme) {
        if (!themeIcon) return;
        if (theme === 'light') {
            themeIcon.innerHTML = '<circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>';
            if (themeText) themeText.textContent = 'Dark';
        } else {
            themeIcon.innerHTML = '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>';
            if (themeText) themeText.textContent = 'Light';
        }
    }
    
    function updateNavbarColors(theme) {
        // Force browser to recalculate CSS variables by triggering a reflow
        const navbar = document.querySelector('.universal-header');
        if (navbar) {
            // Trigger reflow to ensure CSS variables are updated
            void navbar.offsetHeight;
        }
    }
    
    // Mobile menu elements (declared early so dropdown handlers can reference them)
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mainNav = document.getElementById('mainNav');

    // Dropdown functionality
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        if (toggle) {
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                // Close other dropdowns, notification bell, and mobile menu
                dropdowns.forEach(d => {
                    if (d !== dropdown) d.classList.remove('active');
                });
                document.querySelectorAll('.notif-bell-wrap.active').forEach(w => w.classList.remove('active'));
                // Only close the mobile menu when the toggle is NOT inside it
                // (avoids closing the menu when expanding a sub-dropdown within it)
                if (mainNav && mainNav.classList.contains('active') && !mainNav.contains(toggle)) {
                    mainNav.classList.remove('active');
                    document.body.classList.remove('mobile-menu-open');
                }
                dropdown.classList.toggle('active');
            });
        }
    });
    
    // Close dropdowns when clicking outside (but allow navigation on dropdown items)
    document.addEventListener('click', (e) => {
        // Check if we clicked inside a dropdown menu
        const clickedInsideDropdown = e.target.closest('.dropdown-menu');
        
        // If clicked inside dropdown menu, allow navigation but close after brief delay
        if (clickedInsideDropdown) {
            // Only close if it's a link click
            if (e.target.closest('a.dropdown-item')) {
                // Let the link navigate, then close dropdown
                setTimeout(() => {
                    dropdowns.forEach(d => d.classList.remove('active'));
                }, 100);
            }
        } else {
            // Clicked outside, close all dropdowns
            dropdowns.forEach(d => d.classList.remove('active'));
        }
    });
    
    // Mobile menu toggle
    if (mobileMenuBtn && mainNav) {
        // Toggle menu on button click — close all other panels first
        mobileMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const opening = !mainNav.classList.contains('active');
            if (opening) {
                // Close all dropdowns and notification bell before opening the menu
                dropdowns.forEach(d => d.classList.remove('active'));
                document.querySelectorAll('.notif-bell-wrap.active').forEach(w => w.classList.remove('active'));
            }
            mainNav.classList.toggle('active');
            document.body.classList.toggle('mobile-menu-open');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (mainNav.classList.contains('active')) {
                const isClickInsideNav = mainNav.contains(e.target);
                const isClickOnButton = mobileMenuBtn.contains(e.target);
                
                if (!isClickInsideNav && !isClickOnButton) {
                    mainNav.classList.remove('active');
                    document.body.classList.remove('mobile-menu-open');
                }
            }
        });
        
        // Close menu when clicking on nav links (for better UX on navigation)
        mainNav.querySelectorAll('.nav-link:not(.dropdown-toggle)').forEach(link => {
            link.addEventListener('click', () => {
                mainNav.classList.remove('active');
                document.body.classList.remove('mobile-menu-open');
            });
        });
    }
})();
</script>
<script>
// Notification Bell Widget
(function() {
    const bell   = document.getElementById('notifBellBtn');
    const panel  = document.getElementById('notifPanel');
    const list   = document.getElementById('notifPanelList');
    const badge  = document.getElementById('notifBadge');
    const markAllBtn = document.getElementById('notifMarkAll');
    if (!bell) return;

    let loaded = false;
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta ? csrfMeta.content : '';

    function formatTimeAgo(dateStr) {
        const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
        if (diff < 60) return 'just now';
        if (diff < 3600) return Math.floor(diff/60) + 'm ago';
        if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
        return Math.floor(diff/86400) + 'd ago';
    }

    function renderNotifications(items) {
        if (!items || items.length === 0) {
            list.innerHTML = '<div class="notif-empty">No notifications yet</div>';
            return;
        }
        list.innerHTML = items.map(n => `
            <div class="notif-item ${n.is_read === 1 ? 'read' : 'unread'}" data-id="${n.id}">
                <div class="notif-item-dot"></div>
                <div class="notif-item-body">
                    <div class="notif-item-msg">${n.message}</div>
                    <div class="notif-item-time">${formatTimeAgo(n.created_at)}</div>
                </div>
            </div>`).join('');
        // Mark as read on click
        list.querySelectorAll('.notif-item.unread').forEach(el => {
            el.addEventListener('click', function() {
                const id = this.dataset.id;
                fetch('/api/notifications/mark-read', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: '_csrf_token=' + encodeURIComponent(csrf) + '&id=' + id
                }).then(r => r.json()).then(data => {
                    this.classList.replace('unread', 'read');
                    updateBadge(data.unread_count);
                }).catch(() => {});
            });
        });
    }

    function updateBadge(count) {
        if (badge) {
            badge.textContent = Math.min(count, 99);
            badge.style.display = count > 0 ? '' : 'none';
        }
    }

    function loadNotifications() {
        list.innerHTML = '<div style="padding:20px;text-align:center;color:var(--text-secondary,#888);font-size:13px;">Loading…</div>';
        fetch('/api/notifications')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    renderNotifications(data.notifications);
                    updateBadge(data.unread_count);
                }
            }).catch(() => {
                list.innerHTML = '<div class="notif-empty">Could not load notifications.</div>';
            });
    }

    // Toggle panel on bell click
    bell.addEventListener('click', function(e) {
        e.stopPropagation();
        const drop = bell.closest('.dropdown, .notif-bell-wrap');
        const isOpen = drop && drop.classList.contains('active');
        // Close other dropdowns and mobile menu
        document.querySelectorAll('.dropdown.active').forEach(d => d.classList.remove('active'));
        const mobileNav = document.getElementById('mainNav');
        if (mobileNav && mobileNav.classList.contains('active')) {
            mobileNav.classList.remove('active');
            document.body.classList.remove('mobile-menu-open');
        }
        if (!isOpen) {
            drop && drop.classList.add('active');
            if (!loaded) { loadNotifications(); loaded = true; }
        }
    });

    // Mark all read
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            fetch('/api/notifications/mark-all-read', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: '_csrf_token=' + encodeURIComponent(csrf)
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    updateBadge(0);
                    list.querySelectorAll('.notif-item.unread').forEach(el => el.classList.replace('unread','read'));
                }
            }).catch(() => {});
        });
    }

    // Close notification panel when clicking outside
    document.addEventListener('click', function(e) {
        const drop = bell.closest('.notif-bell-wrap');
        if (drop && drop.classList.contains('active') && !drop.contains(e.target)) {
            drop.classList.remove('active');
        }
    });

    // Reload every 60s when tab is visible
    setInterval(function() {
        if (!document.hidden) {
            fetch('/api/notifications')
                .then(r => r.json())
                .then(data => { if (data.success) updateBadge(data.unread_count); })
                .catch(() => {});
        }
    }, 60000);
})();
</script>

<style>
/* Universal Navbar Styles */
html {
    scroll-behavior: smooth;
}

body {
    overflow-x: hidden;
}

.universal-header {
    background: rgba(12, 12, 18, 0.98) !important;
    backdrop-filter: blur(20px) !important;
    -webkit-backdrop-filter: blur(20px) !important;
    border-bottom: 1px solid var(--border-color) !important;
    <?php if (!isset($navbarSettings['navbar_sticky']) || $navbarSettings['navbar_sticky']): ?>
    /* Sticky positioning enabled (default) */
    position: -webkit-sticky !important;
    position: sticky !important;
    top: 0 !important;
    <?php else: ?>
    /* Sticky positioning disabled */
    position: relative !important;
    <?php endif; ?>
    left: 0 !important;
    right: 0 !important;
    z-index: 9999 !important;
    width: 100% !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3) !important;
    transition: all 0.3s ease !important;
    will-change: transform !important;
}

/* Force sticky on all browsers (only if enabled) */
<?php if (!isset($navbarSettings['navbar_sticky']) || $navbarSettings['navbar_sticky']): ?>
@supports (position: sticky) or (position: -webkit-sticky) {
    .universal-header {
        position: -webkit-sticky !important;
        position: sticky !important;
    }
}
<?php endif; ?>

[data-theme="light"] .universal-header {
    background: rgba(255, 255, 255, 0.98) !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1) !important;
}

.universal-header .header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    max-width: 1400px;
    margin: 0 auto;
    padding-left: 20px;
    padding-right: 20px;
}

.universal-header .logo {
    font-size: 1.3rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--cyan), var(--magenta));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-decoration: none;
}

.universal-nav {
    display: flex;
    gap: 20px;
    align-items: center;
    margin-left: auto;  /* push nav (and everything after it) to the right */
}

.nav-item {
    position: relative;
}

/* Always-visible header end actions (notif + profile + theme + hamburger) */
.header-end-actions {
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Theme toggle: icon-only (no text) in the header */
.header-end-actions .theme-toggle {
    padding: 8px 10px;
    gap: 0;
}

/* On desktop, show username in profile button; hide on mobile to save space */
.profile-username {
    display: inline;
}

/* Notification bell & profile dropdown position relative to their wrappers */
.notif-bell-wrap,
.header-end-actions .dropdown {
    position: relative;
}

.universal-nav .nav-link {
    color: var(--text-secondary);
    font-weight: 500;
    padding: 8px 12px;
    position: relative;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    cursor: pointer;
    border-radius: 6px;
    transition: var(--transition);
    text-decoration: none;
    background: none;
    border: none;
    font-family: inherit;
}

.universal-nav .nav-link:hover,
.universal-nav .nav-link.active {
    color: var(--text-primary);
    background: var(--hover-bg);
}

/* Dropdown Menu */
.dropdown {
    position: relative;
}

.dropdown-toggle {
    background: none;
    border: none;
    cursor: pointer;
    font-family: inherit;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: var(--bg-card, #1c1c2a);
    border: 1px solid var(--border-color, rgba(255,255,255,0.15));
    border-radius: 8px;
    min-width: 200px;
    margin-top: 8px;
    padding: 8px 0;
    box-shadow: 0 8px 32px rgba(0,0,0,0.55), 0 0 0 1px rgba(255,255,255,0.06);
    display: none;
    z-index: 10001;
}

.dropdown.active .dropdown-menu {
    display: block;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    color: var(--text-primary, #e8eefc);
    text-decoration: none;
    transition: var(--transition, all 0.3s ease);
    font-size: 14px;
}

.dropdown-item:hover {
    background: var(--hover-bg, rgba(0,240,255,0.08));
    color: var(--cyan, #00f0ff);
}

.dropdown-divider {
    height: 1px;
    background: var(--border-color);
    margin: 8px 0;
}

/* ── Universal-header dropdown: forced visible in dark mode ────────────────
   These rules use the .universal-header scope + !important to win over ANY
   project-specific CSS that might inherit or override colours on the navbar.
   Light-mode rules are kept separate so they still apply correctly.          */

/* Dark mode: dropdown toggle buttons (profile, projects) text & icon.
   Intentionally NOT targeting .nav-link broadly so the notification bell
   keeps its own colour styling. */
html:not([data-theme="light"]) .universal-header .dropdown-toggle {
    color: #e8eefc !important;
}

/* Dark mode: dropdown panel */
html:not([data-theme="light"]) .universal-header .dropdown-menu {
    background: #1c1c2a !important;
    border-color: rgba(255,255,255,0.15) !important;
    box-shadow: 0 8px 32px rgba(0,0,0,0.65), 0 0 0 1px rgba(255,255,255,0.08) !important;
}

/* Dark mode: dropdown items (links inside the panel) */
html:not([data-theme="light"]) .universal-header .dropdown-item {
    color: #e8eefc !important;
    background: transparent !important;
}

html:not([data-theme="light"]) .universal-header .dropdown-item:hover {
    background: rgba(0, 240, 255, 0.10) !important;
    color: #00f0ff !important;
}

/* Light mode: ensure contrast on white panel */
[data-theme="light"] .universal-header .dropdown-menu {
    background: #ffffff !important;
    border-color: rgba(0,0,0,0.12) !important;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
}

[data-theme="light"] .universal-header .dropdown-item {
    color: #1a1a1a !important;
}

[data-theme="light"] .universal-header .dropdown-item:hover {
    background: rgba(0, 100, 200, 0.08) !important;
    color: #0369a1 !important;
}

.project-icon {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
}

/* ── Notification Bell ─────────────────────────────────────────────────── */
.notif-bell-wrap { position: relative; }
/* Show the panel when the wrapper has the .active class */
.notif-bell-wrap.active .dropdown-menu { display: block !important; }

/* Bell button: icon-button style, never inherits wrong colours */
.notif-bell-btn {
    position: relative;
    width: 38px; height: 38px; padding: 0;
    display: flex; align-items: center; justify-content: center;
    background: none; border: none; border-radius: 10px;
    color: var(--text-secondary, #8892a4);
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
    flex-shrink: 0;
}
.notif-bell-btn:hover {
    background: var(--hover-bg, rgba(0,240,255,0.08));
    color: var(--cyan, #00f0ff);
    box-shadow: 0 0 0 1px rgba(0,240,255,0.18);
}
.notif-bell-btn .notif-bell-icon {
    transition: transform 0.25s cubic-bezier(0.34,1.56,0.64,1);
    flex-shrink: 0;
}
.notif-bell-btn:hover .notif-bell-icon {
    transform: rotate(-14deg) scale(1.12);
}
/* Active/open state */
.notif-bell-wrap.active .notif-bell-btn {
    background: rgba(0,240,255,0.10);
    color: var(--cyan, #00f0ff);
    box-shadow: 0 0 0 1px rgba(0,240,255,0.22);
}
/* Light mode overrides */
[data-theme="light"] .notif-bell-btn { color: #5a6785; }
[data-theme="light"] .notif-bell-btn:hover { background: rgba(0,100,200,0.08); color: #0369a1; box-shadow: 0 0 0 1px rgba(0,100,200,0.15); }
[data-theme="light"] .notif-bell-wrap.active .notif-bell-btn { background: rgba(0,100,200,0.10); color: #0369a1; box-shadow: 0 0 0 1px rgba(0,100,200,0.20); }

/* Badge */
.notif-badge {
    position: absolute; top: 3px; right: 3px;
    background: #e53e3e; color: #fff; font-size: 10px; font-weight: 700;
    min-width: 17px; height: 17px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    padding: 0 3px; pointer-events: none;
    border: 2px solid var(--bg-secondary, #12121a);
    box-shadow: 0 2px 5px rgba(229,62,62,0.45);
    transition: transform 0.2s ease;
}
.notif-badge.has-unread { animation: notif-badge-pop 0.4s cubic-bezier(0.34,1.56,0.64,1), notif-badge-pulse 2.5s 0.4s ease-in-out infinite; }
@keyframes notif-badge-pop  { from { transform: scale(0); } to { transform: scale(1); } }
@keyframes notif-badge-pulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(229,62,62,0.45), 0 2px 5px rgba(229,62,62,0.45); }
    55%      { box-shadow: 0 0 0 5px rgba(229,62,62,0),  0 2px 5px rgba(229,62,62,0.45); }
}
[data-theme="light"] .notif-badge { border-color: #f0f4ff; }

/* Panel */
.notif-panel { min-width: 320px; padding: 0; overflow: hidden; }
.notif-panel-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 13px 16px 12px;
    border-bottom: 1px solid var(--border-color);
    background: var(--bg-card, #1c1c2a);
}
.notif-panel-title {
    display: flex; align-items: center; gap: 8px;
    font-size: 14px; font-weight: 600; color: var(--text-primary);
}
.notif-panel-title svg { color: var(--cyan, #00f0ff); flex-shrink: 0; }
.notif-mark-all-btn {
    background: none; border: none; cursor: pointer; font-size: 12px;
    color: var(--cyan, #00f0ff); font-family: inherit; padding: 3px 8px;
    border-radius: 4px; transition: background 0.15s;
}
.notif-mark-all-btn:hover { background: rgba(0,240,255,0.10); text-decoration: none; }
[data-theme="light"] .notif-mark-all-btn:hover { background: rgba(0,100,200,0.08); }

.notif-panel-list { max-height: 340px; overflow-y: auto; }
.notif-item {
    display: flex; gap: 10px; align-items: flex-start;
    padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,0.05);
    cursor: pointer; transition: background 0.15s; font-size: 13px;
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: var(--hover-bg, rgba(255,255,255,0.05)); }
.notif-item.unread { background: rgba(0,240,255,0.05); }
.notif-item-dot {
    width: 8px; height: 8px; border-radius: 50%; background: var(--cyan, #00f0ff);
    flex-shrink: 0; margin-top: 5px; transition: background 0.2s;
}
.notif-item.read .notif-item-dot { background: transparent; border: 1.5px solid var(--border-color); }
.notif-item-body { flex: 1; min-width: 0; }
.notif-item-msg { color: var(--text-primary); line-height: 1.45; }
.notif-item.unread .notif-item-msg { font-weight: 500; }
.notif-item-time { color: var(--text-secondary); font-size: 11px; margin-top: 3px; }
.notif-empty {
    padding: 32px 24px; text-align: center; color: var(--text-secondary); font-size: 13px;
    display: flex; flex-direction: column; align-items: center; gap: 10px;
}
.notif-panel-footer {
    padding: 10px 16px; border-top: 1px solid var(--border-color);
    background: var(--bg-card, #1c1c2a);
    text-align: center;
}
.notif-view-all {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 12px; color: var(--cyan, #00f0ff); text-decoration: none;
    padding: 4px 10px; border-radius: 5px; transition: background 0.15s;
}
.notif-view-all:hover { background: rgba(0,240,255,0.08); text-decoration: none; }
[data-theme="light"] .notif-view-all:hover { background: rgba(0,100,200,0.07); }
[data-theme="light"] .notif-item.unread { background: rgba(0,120,255,0.05); }
[data-theme="light"] .notif-panel-header,
[data-theme="light"] .notif-panel-footer { background: #f8faff; }

/* Theme Toggle */
.theme-toggle {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    transition: var(--transition);
    font-family: inherit;
}

.theme-toggle:hover {
    background: var(--hover-bg);
    border-color: var(--cyan);
}

.mobile-menu-btn {
    display: none;
    background: none;
    border: none;
    color: var(--text-primary);
    font-size: 24px;
    cursor: pointer;
    padding: 8px;
    transition: transform 0.3s ease, opacity 0.2s ease;
}

.mobile-menu-btn:hover {
    transform: scale(1.1);
    opacity: 0.8;
}

.mobile-menu-btn:active {
    transform: scale(0.95);
}

.mobile-menu-btn svg {
    transition: transform 0.3s ease;
}

.universal-nav.active ~ .mobile-menu-btn svg {
    transform: rotate(90deg);
}

@media (max-width: 768px) {
    .universal-nav {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--bg-secondary);
        border-bottom: 1px solid var(--border-color);
        padding: 20px;
        flex-direction: column;
        gap: 0;
        /* Smooth animations */
        opacity: 0;
        transform: translateY(-10px);
        transition: opacity 0.3s ease, transform 0.3s ease;
        pointer-events: none;
    }
    
    .universal-nav.active {
        display: flex;
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }
    
    .universal-nav .nav-item,
    .universal-nav .nav-link {
        width: 100%;
        justify-content: flex-start;
        /* Stagger animation for menu items */
        animation: slideInFromLeft 0.3s ease forwards;
    }
    
    @keyframes slideInFromLeft {
        from {
            opacity: 0;
            transform: translateX(-10px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    /* Overlay for better mobile menu experience */
    body.mobile-menu-open::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(2px);
        z-index: 999;
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .universal-header {
        position: relative;
        z-index: 1000;
    }
    
    /* Only items inside the collapsible nav get static full-width dropdowns */
    .universal-nav .dropdown-menu {
        position: static;
        margin-top: 8px;
        box-shadow: none;
        border: none;
        border-left: 2px solid var(--cyan);
        padding-left: 20px;
    }

    /* header-end-actions: push to the right (nav is display:none so no auto margin from it) */
    .header-end-actions {
        margin-left: auto;
        gap: 2px;
    }

    /* Hide username in profile button on mobile — icon only */
    .profile-username {
        display: none;
    }

    /* Notif panel on mobile: fixed to viewport so body overflow-x:hidden can't clip it */
    .notif-panel {
        position: fixed !important;
        top: 64px !important;
        right: 8px !important;
        left: 8px !important;
        width: auto !important;
        min-width: 0 !important;
        max-height: 70vh;
        overflow-y: auto;
        z-index: 10000;
    }

    /* Profile dropdown on mobile: constrained width, fixed so it can't be clipped */
    .header-end-actions .dropdown .dropdown-menu {
        position: fixed !important;
        top: 58px !important;
        right: 8px !important;
        left: auto !important;
        width: min(220px, calc(100vw - 20px));
        border-left: none;
        padding-left: 0;
        z-index: 10002 !important;
    }
    
    .mobile-menu-btn {
        display: block;
    }
}

/* iPad portrait (769px–1024px) — prevent profile/notif dropdowns from overflowing the viewport */
@media (min-width: 769px) and (max-width: 1024px) {
    /* Tighten nav link padding so everything fits */
    .universal-nav .nav-link {
        padding: 6px 8px;
        font-size: 13px;
        gap: 4px;
    }

    /* Profile and notif dropdowns: fixed so overflow-x:hidden on body can't clip them */
    .header-end-actions .dropdown .dropdown-menu {
        position: fixed !important;
        top: 58px !important;
        right: 8px !important;
        left: auto !important;
        width: min(240px, calc(100vw - 20px));
        z-index: 10002 !important;
    }

    .notif-panel {
        position: fixed !important;
        top: 64px !important;
        right: 8px !important;
        left: auto !important;
        width: min(340px, calc(100vw - 16px)) !important;
        max-height: 70vh;
        overflow-y: auto;
        z-index: 10001 !important;
    }

    /* Hide username text on mid-sized tablets to save header space */
    .profile-username {
        display: none;
    }
}
</style>

<!-- ── Logout Confirmation Modal ───────────────────────────────────────────── -->
<div id="logoutModal" style="display:none;position:fixed;inset:0;z-index:99999;align-items:center;justify-content:center;">
    <!-- Backdrop -->
    <div id="logoutBackdrop" onclick="closeLogoutModal()"
         style="position:absolute;inset:0;background:rgba(6,6,10,0.75);backdrop-filter:blur(6px);opacity:0;transition:opacity 0.3s ease;"></div>
    <!-- Card -->
    <div id="logoutCard"
         style="position:relative;z-index:1;background:var(--bg-card);border:1px solid var(--border-color);border-radius:20px;padding:40px 36px 32px;max-width:400px;width:calc(100% - 40px);text-align:center;transform:translateY(24px) scale(0.96);opacity:0;transition:transform 0.35s cubic-bezier(.34,1.56,.64,1),opacity 0.3s ease;box-shadow:0 24px 80px rgba(0,0,0,0.5);">
        <!-- Icon -->
        <div style="width:64px;height:64px;border-radius:50%;background:rgba(255,107,107,0.12);border:1px solid rgba(255,107,107,0.3);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ff6b6b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
        </div>
        <h3 style="font-size:1.25rem;font-weight:700;color:var(--text-primary);margin:0 0 8px;">Sign Out?</h3>
        <p style="font-size:0.9rem;color:var(--text-secondary);margin:0 0 28px;line-height:1.55;">You're about to sign out of your account. You can always log back in at any time.</p>
        <div style="display:flex;gap:12px;justify-content:center;">
            <button onclick="closeLogoutModal()"
                    style="flex:1;max-width:140px;padding:11px 20px;border-radius:10px;border:1px solid var(--border-color);background:var(--bg-secondary);color:var(--text-primary);font-size:0.9rem;font-weight:600;cursor:pointer;transition:background 0.2s;">
                Cancel
            </button>
            <a href="/logout" id="logoutConfirmBtn"
               style="flex:1;max-width:140px;padding:11px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,#ff6b6b,#ff2ec4);color:#fff;font-size:0.9rem;font-weight:600;cursor:pointer;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px;transition:opacity 0.2s;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Sign Out
            </a>
        </div>
    </div>
</div>
<script>
(function () {
    function openLogoutModal(e) {
        e && e.preventDefault();
        var modal   = document.getElementById('logoutModal');
        var backdrop = document.getElementById('logoutBackdrop');
        var card    = document.getElementById('logoutCard');
        modal.style.display = 'flex';
        // Force reflow then animate in
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                backdrop.style.opacity = '1';
                card.style.transform  = 'translateY(0) scale(1)';
                card.style.opacity    = '1';
            });
        });
    }
    function closeLogoutModal() {
        var modal   = document.getElementById('logoutModal');
        var backdrop = document.getElementById('logoutBackdrop');
        var card    = document.getElementById('logoutCard');
        backdrop.style.opacity = '0';
        card.style.transform   = 'translateY(24px) scale(0.96)';
        card.style.opacity     = '0';
        setTimeout(function () { modal.style.display = 'none'; }, 320);
    }
    // ESC key closes modal
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeLogoutModal();
    });
    window.openLogoutModal  = openLogoutModal;
    window.closeLogoutModal = closeLogoutModal;
})();
</script>
