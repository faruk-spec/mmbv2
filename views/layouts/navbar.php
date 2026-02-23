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
        
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle menu">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
        
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
                            $projects = require BASE_PATH . '/config/projects.php';
                            foreach ($projects as $key => $project): 
                        ?>
                            <a href="<?= $project['url'] ?>" class="dropdown-item">
                                <div class="project-icon" style="background: <?= $project['color'] ?>20; color: <?= $project['color'] ?>">
                                    <?= strtoupper(substr($project['name'], 0, 1)) ?>
                                </div>
                                <?= $project['name'] ?>
                            </a>
                        <?php 
                            endforeach;
                        } catch (\Exception $e) {
                            error_log("Projects config error: " . $e->getMessage());
                            // Show fallback message
                            echo '<div class="dropdown-item" style="color: var(--text-secondary); font-size: 13px;">Projects unavailable</div>';
                        }
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
                
                <!-- Notification Bell -->
                <?php if ($isLoggedIn):
                    try {
                        $notifUnreadCount = \Core\Notification::getUnreadCount($user['id']);
                    } catch (\Exception $e) {
                        $notifUnreadCount = 0;
                    }
                ?>
                <div class="notif-bell-wrap nav-item" id="notifDropdown">
                    <button class="nav-link notif-bell-btn" id="notifBellBtn" aria-label="Notifications" title="Notifications">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <span class="notif-badge<?= $notifUnreadCount > 0 ? ' has-unread' : '' ?>" id="notifBadge"
                              style="<?= $notifUnreadCount > 0 ? '' : 'display:none' ?>">
                            <?= min($notifUnreadCount, 99) ?>
                        </span>
                    </button>
                    <div class="dropdown-menu notif-panel" id="notifPanel" style="min-width:320px;padding:0;">
                        <div class="notif-panel-header">
                            <span><strong>Notifications</strong></span>
                            <button class="notif-mark-all-btn" id="notifMarkAll" title="Mark all as read">Mark all read</button>
                        </div>
                        <div class="notif-panel-list" id="notifPanelList">
                            <div style="padding:20px;text-align:center;color:var(--text-secondary);font-size:13px;">Loading…</div>
                        </div>
                        <div class="notif-panel-footer">
                            <a href="/dashboard" style="font-size:12px;color:var(--cyan);">View Dashboard</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Profile Dropdown -->
                <?php if ($navbarSettings['show_profile_link']): ?>
                <div class="dropdown nav-item" id="profileDropdown">
                    <button class="nav-link dropdown-toggle">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <?= htmlspecialchars($user['username'] ?? 'User') ?>
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
                        <a href="/logout" class="dropdown-item">
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
            <?php else: ?>
                <a href="/login" class="nav-link">Login</a>
                <a href="/register" class="nav-link">Register</a>
            <?php endif; ?>
            
            <!-- Theme Toggle -->
            <?php if ($navbarSettings['show_theme_toggle']): ?>
            <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">
                <svg id="themeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
                <span id="themeText">Light</span>
            </button>
            <?php endif; ?>
        </nav>
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
        if (!themeIcon || !themeText) return;
        if (theme === 'light') {
            themeIcon.innerHTML = '<circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>';
            themeText.textContent = 'Dark';
        } else {
            themeIcon.innerHTML = '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>';
            themeText.textContent = 'Light';
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
    
    // Dropdown functionality
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        if (toggle) {
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                // Close other dropdowns
                dropdowns.forEach(d => {
                    if (d !== dropdown) d.classList.remove('active');
                });
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
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mainNav = document.getElementById('mainNav');
    if (mobileMenuBtn && mainNav) {
        // Toggle menu on button click
        mobileMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // Prevent immediate close
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
        // Close other dropdowns
        document.querySelectorAll('.dropdown.active').forEach(d => d.classList.remove('active'));
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
}

.nav-item {
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
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    min-width: 200px;
    margin-top: 8px;
    padding: 8px 0;
    box-shadow: var(--shadow);
    display: none;
    z-index: 1000;
}

.dropdown.active .dropdown-menu {
    display: block;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    color: var(--text-primary);
    text-decoration: none;
    transition: var(--transition);
    font-size: 14px;
}

.dropdown-item:hover {
    background: var(--hover-bg);
    color: var(--cyan);
}

.dropdown-divider {
    height: 1px;
    background: var(--border-color);
    margin: 8px 0;
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

/* Notification Bell */
.notif-bell-wrap { position: relative; }
.notif-bell-btn { position: relative; padding: 8px 10px !important; }
.notif-badge {
    position: absolute; top: 2px; right: 2px;
    background: #e53e3e; color: #fff; font-size: 10px; font-weight: 700;
    min-width: 16px; height: 16px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    padding: 0 3px; pointer-events: none;
}
.notif-panel-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 12px 16px; border-bottom: 1px solid var(--border-color);
    font-size: 14px;
}
.notif-mark-all-btn {
    background: none; border: none; cursor: pointer; font-size: 12px;
    color: var(--cyan); font-family: inherit; padding: 0;
}
.notif-mark-all-btn:hover { text-decoration: underline; }
.notif-panel-list { max-height: 340px; overflow-y: auto; }
.notif-item {
    display: flex; gap: 10px; align-items: flex-start;
    padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,0.05);
    cursor: pointer; transition: background 0.15s; font-size: 13px;
}
.notif-item:hover { background: var(--hover-bg, rgba(255,255,255,0.05)); }
.notif-item.unread { background: rgba(0,240,255,0.04); }
.notif-item-dot {
    width: 8px; height: 8px; border-radius: 50%; background: var(--cyan);
    flex-shrink: 0; margin-top: 5px;
}
.notif-item.read .notif-item-dot { background: transparent; }
.notif-item-body { flex: 1; min-width: 0; }
.notif-item-msg { color: var(--text-primary); line-height: 1.4; }
.notif-item-time { color: var(--text-secondary); font-size: 11px; margin-top: 2px; }
.notif-empty { padding: 24px; text-align: center; color: var(--text-secondary); font-size: 13px; }
.notif-panel-footer { padding: 10px 16px; border-top: 1px solid var(--border-color); text-align: center; }
[data-theme="light"] .notif-item.unread { background: rgba(0,120,255,0.04); }

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
    
    .nav-item, .universal-nav .nav-link {
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
    
    .nav-item, .universal-nav .nav-link {
        width: 100%;
        justify-content: flex-start;
    }
    
    .dropdown-menu {
        position: static;
        margin-top: 8px;
        box-shadow: none;
        border: none;
        border-left: 2px solid var(--cyan);
        padding-left: 20px;
    }
    
    .mobile-menu-btn {
        display: block;
    }
}
</style>
